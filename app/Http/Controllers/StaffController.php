<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Seat;
use App\Models\SeatSession;
use App\Models\Zone;
use App\Services\EndSeatSession;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    // แสดงหน้าจอมอนิเตอร์สถานะเครื่องคอมพิวเตอร์หน้าร้าน
    public function seatMonitor()
    {
        // 1. ดึงข้อมูลโซนและเครื่องทั้งหมดพร้อมข้อมูลเซสชันที่กำลังเล่น
        $zones = Zone::with(['seats.zone', 'seats.seatSessions' => function ($q) {
            $q->where('status', 'active')->with(['user', 'userPackage.package']);
        }])->get();

        // 2. สรุปจำนวนเครื่องตามสถานะ
        $totalSeats = Seat::count();
        $occupiedSeats = Seat::where('status', 'occupied')->count();
        $availableSeats = Seat::where('status', 'available')->count();
        $maintenanceSeats = Seat::where('status', 'maintenance')->count();

        return view('pages.staff.seat-monitor', compact(
            'zones',
            'totalSeats',
            'occupiedSeats',
            'availableSeats',
            'maintenanceSeats'
        ));
    }

    // ฟังก์ชันบังคับปิดเครื่องและคิดเงิน (โดยพนักงาน)
    public function forceEnd(Request $request, EndSeatSession $endSeatSession)
    {
        $request->validate([
            'session_id' => 'required|exists:seat_sessions,id',
        ]);

        $session = SeatSession::find((int) $request->input('session_id'));
        if ($session === null) {
            return redirect()->back()->with('error', 'ไม่พบข้อมูลเซสชัน');
        }

        $seat = Seat::find($session->seat_id);
        $endSeatSession->handle($session);

        return redirect()->back()->with('success', 'ปิดเครื่อง ' . ($seat ? $seat->seat_number : '') . ' และคิดเงินสำเร็จเรียบร้อยแล้ว');
    }

    // ฟังก์ชันสลับสถานะเครื่องระหว่าง "พร้อมใช้งาน" กับ "ซ่อมบำรุง"
    public function toggleMaintenance(Request $request)
    {
        $request->validate([
            'seat_id' => 'required|exists:seats,id',
        ]);

        $seatId = (int) $request->input('seat_id');
        $seat = Seat::find($seatId);

        if ($seat == null) {
            return redirect()->back()->with('error', 'ไม่พบข้อมูลเครื่อง');
        }

        if ($seat->status == 'available') {
            $seat->status = 'maintenance';
            $seat->save();
            return redirect()->back()->with('success', 'ปรับสถานะเครื่อง ' . $seat->seat_number . ' เป็นซ่อมบำรุง');
        } elseif ($seat->status == 'maintenance') {
            $seat->status = 'available';
            $seat->save();
            return redirect()->back()->with('success', 'ปรับสถานะเครื่อง ' . $seat->seat_number . ' เป็นพร้อมใช้งาน');
        }

        return redirect()->back()->with('error', 'ไม่สามารถปรับสถานะเครื่องที่กำลังใช้งานอยู่ได้');
    }

    // แสดงหน้าคิวอาหารในห้องครัว (Kitchen Queue)
    public function kitchenQueue(Request $request)
    {
        $filterStatus = $request->input('status', 'active');

        $query = Order::with(['items.product', 'seat', 'user']);
        if ($filterStatus == 'active') {
            $query->whereIn('order_status', ['pending', 'preparing']);
        } elseif ($filterStatus == 'served') {
            $query->where('order_status', 'served');
        } elseif ($filterStatus == 'cancelled') {
            $query->where('order_status', 'cancelled');
        }
        $orders = $query->orderBy('created_at', 'asc')->get();

        $pendingCount = Order::where('order_status', 'pending')->count();
        $preparingCount = Order::where('order_status', 'preparing')->count();
        $activeOrders = $orders;

        return view('pages.staff.kitchen-queue', compact(
            'orders',
            'activeOrders',
            'filterStatus',
            'pendingCount',
            'preparingCount'
        ));
    }

    // ฟังก์ชันอัปเดตสถานะอาหารในครัว
    public function updateOrderStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'status' => 'required|in:pending,preparing,served,cancelled',
        ]);

        $orderId = (int) $request->input('order_id');
        $status = $request->input('status');
        $order = Order::find($orderId);

        if ($order == null) {
            return redirect()->back()->with('error', 'ไม่พบข้อมูลออเดอร์');
        }

        // ตรวจสอบ: หากยังไม่ได้รับการชำระเงิน ไม่อนุญาตให้เริ่มทำอาหารหรือเสิร์ฟอาหาร
        if (in_array($status, ['preparing', 'served']) && $order->payment_status !== 'paid') {
            $msg = $order->payment_method === 'cash'
                ? 'ไม่สามารถเปลี่ยนสถานะอาหารได้ เนื่องจากยังไม่ได้ยืนยันการรับเงินสดสำหรับออเดอร์ #' . $order->id . ' กรุณากดยืนยันรับเงินสดก่อน'
                : 'ไม่สามารถเปลี่ยนสถานะอาหารได้ เนื่องจากออเดอร์ #' . $order->id . ' ยังไม่ได้รับการชำระเงิน';
            return redirect()->back()->with('error', $msg);
        }

        // ถ้ากดยกเลิกออเดอร์ ให้คืนสต็อกสินค้า
        if ($status == 'cancelled' && $order->order_status != 'cancelled') {
            foreach ($order->items as $item) {
                $product = Product::find($item->product_id);
                if ($product != null) {
                    $product->stock_quantity = $product->stock_quantity + $item->quantity;
                    $product->save();
                }
            }
        }

        $order->order_status = $status;
        $order->save();

        return redirect()->back()->with('success', 'อัปเดตสถานะออเดอร์ #' . $order->id . ' เป็น ' . $status . ' เรียบร้อยแล้ว');
    }

    // ฟังก์ชันยืนยันการรับเงินสด
    public function confirmCashPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $orderId = (int) $request->input('order_id');
        $order = Order::find($orderId);

        if ($order == null) {
            return redirect()->back()->with('error', 'ไม่พบข้อมูลออเดอร์');
        }

        if ($order->payment_method != 'cash') {
            return redirect()->back()->with('error', 'ออเดอร์นี้ไม่ได้ชำระด้วยเงินสด');
        }

        $order->payment_status = 'paid';
        $order->save();

        return redirect()->back()->with('success', 'ยืนยันรับเงินสดออเดอร์ #' . $order->id . ' ยอด ฿' . number_format($order->total_amount, 2) . ' สำเร็จ');
    }
}
