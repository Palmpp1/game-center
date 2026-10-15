<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seat;
use App\Models\SeatSession;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FoodOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $selectedCategoryId = $request->input('category_id');

        $categories = Category::withCount('products')->get();

        $productsQuery = Product::with('category')->where('stock_quantity', '>', 0);
        if ($selectedCategoryId) {
            $productsQuery->where('category_id', $selectedCategoryId);
        }
        $products = $productsQuery->get();

        $activeSession = SeatSession::with('seat.zone')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        $availableBalance = $this->calculateAvailableBalance($user, $activeSession);
        $activeSeat = $activeSession ? $activeSession->seat : null;

        return view('pages.customer.food-order', compact(
            'user',
            'categories',
            'products',
            'activeSession',
            'activeSeat',
            'selectedCategoryId',
            'availableBalance'
        ));
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'seat_id' => 'required|exists:seats,id',
            'items' => 'required|array|min:1',
            'payment_method' => 'required|in:wallet,promptpay,cash',
        ]);

        $user = Auth::user();
        $seatId = (int) $request->input('seat_id');
        $paymentMethod = $request->input('payment_method');
        $items = $this->parseOrderItems($request->input('items', []));

        if (empty($items)) {
            return redirect()->back()->with('error', 'กรุณาเลือกรายการสินค้าอย่างน้อย 1 รายการ');
        }

        $seat = Seat::find($seatId);
        if ($seat === null) {
            return redirect()->back()->with('error', 'ไม่พบข้อมูลเครื่อง');
        }

        $session = SeatSession::where('user_id', $user->id)
            ->where('seat_id', $seat->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if ($session === null) {
            return redirect()->back()->with('error', 'คุณยังไม่ได้เปิดใช้งานเครื่องนี้ กรุณาเปิดเครื่องก่อนทำการสั่งอาหาร');
        }

        try {
            $order = DB::transaction(function () use ($user, $seat, $session, $items, $paymentMethod) {
                $freshUser = User::where('id', $user->id)->lockForUpdate()->first();
                $orderData = $this->prepareOrderItems($items);
                $totalAmount = $orderData['totalAmount'];
                $orderItemsList = $orderData['items'];

                $paymentStatus = 'pending_payment';
                if ($paymentMethod === 'wallet') {
                    $availableBalance = $this->calculateAvailableBalance($freshUser, $session);

                    if ($availableBalance < $totalAmount) {
                        throw new \Exception('ยอดเงินที่ใช้ได้ไม่เพียงพอสำหรับการสั่งอาหาร (ต้องกันเงินไว้จ่ายค่าเครื่อง)');
                    }

                    $freshUser->balance = (float) $freshUser->balance - $totalAmount;
                    $freshUser->save();

                    $paymentStatus = 'paid';
                } elseif ($paymentMethod === 'promptpay') {
                    $paymentStatus = 'paid';
                }

                $order = Order::create([
                    'user_id' => $freshUser->id,
                    'seat_id' => $seat->id,
                    'session_id' => $session->id,
                    'total_amount' => $totalAmount,
                    'payment_method' => $paymentMethod,
                    'payment_status' => $paymentStatus,
                    'order_status' => 'pending',
                ]);

                foreach ($orderItemsList as $orderItem) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $orderItem['product']->id,
                        'quantity' => $orderItem['quantity'],
                        'unit_price' => $orderItem['unit_price'],
                        'subtotal' => $orderItem['subtotal'],
                    ]);

                    $productItem = $orderItem['product'];
                    $productItem->stock_quantity -= $orderItem['quantity'];
                    $productItem->save();
                }

                if ($paymentMethod === 'wallet') {
                    WalletTransaction::create([
                        'user_id' => $freshUser->id,
                        'type' => 'deduct',
                        'amount' => $totalAmount,
                        'ref_type' => 'food_order',
                        'ref_id' => $order->id,
                    ]);
                }

                return $order;
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'สั่งอาหารสำเร็จ บิล #' . $order->id);
    }

    // ตรวจสอบสต็อกสินค้าและคำนวณราคารวมของแต่ละรายการ
    private function prepareOrderItems(array $items): array
    {
        $totalAmount = 0.00;
        $orderItemsList = [];

        foreach ($items as $item) {
            $productId = (int) $item['product_id'];
            $quantity = (int) $item['quantity'];

            $product = Product::where('id', $productId)->lockForUpdate()->first();
            if ($product === null) {
                throw new \Exception('ไม่พบข้อมูลสินค้า');
            }

            if ($product->stock_quantity < $quantity) {
                throw new \Exception('สินค้า ' . $product->name . ' มีไม่พอ (เหลือ ' . $product->stock_quantity . ' ชิ้น)');
            }

            $unitPrice = (float) $product->price;
            $subtotal = round($unitPrice * $quantity, 2);
            $totalAmount += $subtotal;

            $orderItemsList[] = [
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ];
        }

        return [
            'totalAmount' => $totalAmount,
            'items' => $orderItemsList,
        ];
    }

    // คำนวณยอดเงินที่สามารถใช้สั่งอาหารได้จริง (กันเงินไว้สำหรับค่าเครื่องในเซสชันปัจจุบัน)
    private function calculateAvailableBalance(User $user, ?SeatSession $session): float
    {
        $balance = (float) $user->balance;

        if ($session === null || $session->user_package_id !== null) {
            return max(0.00, $balance);
        }

        $startTime = Carbon::parse($session->start_time);
        $usedMinutes = max(1, (int) ceil($startTime->diffInSeconds(Carbon::now()) / 60));
        $estimatedCost = round(($usedMinutes / 60) * (float) $session->rate_snapshot, 2);

        return max(0.00, round($balance - $estimatedCost, 2));
    }

    // แปลงรูปแบบ items ให้ยืดหยุ่นรองรับทั้งแบบ array of objects และแบบ key-value
    private function parseOrderItems(array $rawItems): array
    {
        $items = [];
        foreach ($rawItems as $key => $val) {
            if (is_array($val)) {
                $pId = $val['product_id'] ?? $key;
                $qty = $val['quantity'] ?? 0;
            } else {
                $pId = $key;
                $qty = (int) $val;
            }
            if ($qty > 0) {
                $items[] = [
                    'product_id' => (int) $pId,
                    'quantity' => (int) $qty,
                ];
            }
        }
        return $items;
    }
}
