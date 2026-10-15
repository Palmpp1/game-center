<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SeatSession;
use App\Models\WalletTransaction;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // แสดงหน้าจัดการสต็อกและสินค้า
    public function stockManager()
    {
        $categories = Category::all();
        $products = Product::with('category')->orderBy('category_id')->get();

        return view('pages.admin.stock-manager', compact('categories', 'products'));
    }

    // ฟังก์ชันปรับเพิ่มหรือลดจำนวนสต็อกสินค้า
    public function adjustStock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'delta' => 'required|integer',
        ]);

        $productId = (int) $request->input('product_id');
        $delta = (int) $request->input('delta');

        // 1. ค้นหาสินค้าตาม ID
        $product = Product::find($productId);
        if ($product == null) {
            return redirect()->back()->with('error', 'ไม่พบสินค้า');
        }

        $newStock = max(0, $product->stock_quantity + $delta);
        $product->stock_quantity = $newStock;
        $product->save();

        return redirect()->back()->with('success', 'อัปเดตสต็อก ' . $product->name . ' เป็น ' . $newStock . ' ชิ้น');
    }

    // ฟังก์ชันเพิ่มสินค้าใหม่เข้าระบบ
    public function createProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        // บันทึกสินค้าใหม่ลงตาราง products
        $product = Product::create([
            'name' => $request->input('name'),
            'category_id' => $request->input('category_id'),
            'description' => $request->input('description', ''),
            'price' => (float) $request->input('price'),
            'stock_quantity' => (int) $request->input('stock_quantity'),
        ]);

        return redirect()->back()->with('success', 'เพิ่มสินค้า ' . $product->name . ' สำเร็จเรียบร้อยแล้ว');
    }

    // แสดงหน้ารายงานยอดขายและรายได้ (Financial & Sales Report)
    public function salesReport(Request $request)
    {
        $period = $request->input('period', 'all');

        $startDate = null;
        if ($period == 'today') {
            $startDate = Carbon::today();
        } elseif ($period == '7days') {
            $startDate = Carbon::now()->subDays(7);
        } elseif ($period == 'month') {
            $startDate = Carbon::now()->subDays(30);
        }

        // 1. รวมยอดขายค่าชั่วโมงเล่นเกม
        $sessionsQuery = SeatSession::where('status', 'completed');
        if ($startDate) {
            $sessionsQuery->where('created_at', '>=', $startDate);
        }
        $hourlyRevenue = (float) $sessionsQuery->sum('total_cost');

        // 2. รวมยอดขายแพ็กเกจเวลา
        $pkgTxQuery = WalletTransaction::where('ref_type', 'package_purchase');
        if ($startDate) {
            $pkgTxQuery->where('created_at', '>=', $startDate);
        }
        $packageRevenue = (float) $pkgTxQuery->sum('amount');

        // 3. รวมยอดขายอาหารและเครื่องดื่ม
        $ordersQuery = Order::where('payment_status', 'paid');
        if ($startDate) {
            $ordersQuery->where('created_at', '>=', $startDate);
        }
        $foodRevenue = (float) $ordersQuery->sum('total_amount');

        // 4. รวมรายได้ทั้งหมดของร้าน
        $totalGamingRevenue = $hourlyRevenue + $packageRevenue;
        $grandTotalRevenue = $totalGamingRevenue + $foodRevenue;

        // 5. รวมยอดเงินเติมเข้า Wallet
        $topupQuery = WalletTransaction::where('type', 'topup');
        if ($startDate) {
            $topupQuery->where('created_at', '>=', $startDate);
        }
        $totalTopup = (float) $topupQuery->sum('amount');

        // 6. จำนวนรายการต่างๆ
        $completedSessionsCount = (int) $sessionsQuery->count();
        $packageSalesCount = (int) $pkgTxQuery->count();
        $paidOrdersCount = (int) $ordersQuery->count();

        // 7. 5 อันดับเมนูขายดี
        $topProductsQuery = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.payment_status', 'paid');
        if ($startDate) {
            $topProductsQuery->where('orders.created_at', '>=', $startDate);
        }
        $topProducts = $topProductsQuery->select(
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.subtotal) as total_sales')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // 8. สถิติการใช้งานแยกตามโซน
        $zones = Zone::withCount('seats')->get();
        $zoneStats = [];
        foreach ($zones as $zone) {
            $seatIds = $zone->seats()->pluck('id');
            $zSessions = SeatSession::whereIn('seat_id', $seatIds)->where('status', 'completed');
            if ($startDate) {
                $zSessions->where('created_at', '>=', $startDate);
            }
            $zoneStats[] = [
                'zone' => $zone,
                'sessions_count' => $zSessions->count(),
                'revenue' => (float) $zSessions->sum('total_cost'),
            ];
        }

        return view('pages.admin.sales-report', compact(
            'period',
            'hourlyRevenue',
            'packageRevenue',
            'foodRevenue',
            'totalGamingRevenue',
            'grandTotalRevenue',
            'totalTopup',
            'completedSessionsCount',
            'packageSalesCount',
            'paidOrdersCount',
            'topProducts',
            'zoneStats'
        ));
    }
}
