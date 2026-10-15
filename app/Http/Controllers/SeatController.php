<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use App\Models\SeatSession;
use App\Models\User;
use App\Models\UserPackage;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class SeatController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $zones = Zone::with(['seats' => function ($query) {
            $query->orderBy('seat_number');
        }])->get();

        $activeSession = SeatSession::with('seat.zone')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        $availablePackages = UserPackage::with('package.zone')
            ->has('package')
            ->where('user_id', $user->id)
            ->where('remaining_minutes', '>', 0)
            ->where(function ($query) {
                $query->whereNull('expired_at')
                    ->orWhere('expired_at', '>', Carbon::now());
            })
            ->get();

        return view('pages.customer.seat-map', compact('zones', 'activeSession', 'availablePackages'));
    }

    public function checkIn(Request $request)
    {
        $request->validate([
            'seat_id' => 'required|exists:seats,id',
            'billing_mode' => 'required|in:pay_as_you_go,package',
            'user_package_id' => 'nullable|exists:user_packages,id',
        ]);

        $user = Auth::user();
        $seatId = $request->input('seat_id');
        $billingMode = $request->input('billing_mode');
        $userPackageId = $request->input('user_package_id');

        $hasActiveSession = SeatSession::where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if ($hasActiveSession) {
            return redirect()->back()->with('error', 'คุณมีเครื่องที่กำลังใช้งานอยู่แล้ว');
        }

        $seat = Seat::with('zone')->find($seatId);
        if ($seat == null) {
            return redirect()->back()->with('error', 'ไม่พบข้อมูลที่นั่ง');
        }

        if ($seat->status != 'available') {
            return redirect()->back()->with('error', 'ที่นั่งนี้ไม่ว่าง หรืออยู่ระหว่างการซ่อมบำรุง');
        }

        $selectedPackageId = null;

        if ($billingMode == 'package') {
            if (! $userPackageId) {
                return redirect()->back()->with('error', 'กรุณาเลือกแพ็กเกจที่ต้องการใช้งาน');
            }

            $userPackage = UserPackage::with('package.zone')->where('id', $userPackageId)
                ->where('user_id', $user->id)
                ->first();

            if ($userPackage == null) {
                return redirect()->back()->with('error', 'ไม่พบแพ็กเกจนี้');
            }

            if ($userPackage->remaining_minutes <= 0) {
                return redirect()->back()->with('error', 'แพ็กเกจนี้เวลาหมดแล้ว');
            }

            if ($userPackage->isExpired()) {
                return redirect()->back()->with('error', 'แพ็กเกจนี้หมดอายุแล้ว');
            }

            $package = $userPackage->package;
            if ($package != null && $package->zone_id != null) {
                if ($package->zone_id != $seat->zone_id) {
                    return redirect()->back()->with('error', 'แพ็กเกจนี้ไม่สามารถใช้กับโซนนี้ได้');
                }
            }

            $selectedPackageId = $userPackage->id;
        } else {
            $freshUser = User::find($user->id);
            if ((float) $freshUser->balance <= 0.00) {
                return redirect()->back()->with('error', 'ยอดเงินในกระเป๋าของคุณหมดแล้ว กรุณาเติมเงินก่อนเปิดเครื่อง');
            }
        }

        $hourlyRate = $seat->zone->hourly_rate;

        // atomic update ป้องกันคนกดพร้อมกัน
        $updated = Seat::where('id', $seat->id)
            ->where('status', 'available')
            ->update(['status' => 'occupied']);

        if (! $updated) {
            return redirect()->back()->with('error', 'ที่นั่งนี้ถูกผู้อื่นเช็คอินไปก่อนหน้าแล้ว');
        }

        SeatSession::create([
            'user_id' => $user->id,
            'seat_id' => $seat->id,
            'user_package_id' => $selectedPackageId,
            'rate_snapshot' => $hourlyRate,
            'start_time' => Carbon::now(),
            'status' => 'active',
            'total_cost' => 0.00,
        ]);

        return redirect()->route('dashboard')->with('success', 'เปิดเครื่องสำเร็จ');
    }
}
