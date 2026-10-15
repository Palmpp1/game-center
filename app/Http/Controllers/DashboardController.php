<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Seat;
use App\Models\SeatSession;
use App\Models\User;
use App\Models\UserPackage;
use App\Models\WalletTransaction;
use App\Services\EndSeatSession;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        protected EndSeatSession $endSeatSession
    ) {}

    public function index()
    {
        $user = Auth::user();

        $this->autoEndExpiredSessions();
        $user->refresh();

        $activeSession = SeatSession::with(['seat.zone', 'userPackage.package'])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        $stats = $this->calculateSessionStats($activeSession, $user);
        $elapsedMinutes = $stats['elapsedMinutes'];
        $estimatedCost = $stats['estimatedCost'];
        $sessionRemainingMinutes = $stats['sessionRemainingMinutes'];

        $availableBalance = $this->calculateAvailableBalance($user, $activeSession);

        $userPackages = UserPackage::with('package.zone')
            ->has('package')
            ->where('user_id', $user->id)
            ->where('remaining_minutes', '>', 0)
            ->where(function ($query) {
                $query->whereNull('expired_at')
                    ->orWhere('expired_at', '>', Carbon::now());
            })
            ->latest()
            ->get();

        $transactions = WalletTransaction::where('user_id', $user->id)->latest()->take(5)->get();
        $recentOrders = Order::with('seat')->where('user_id', $user->id)->latest()->take(5)->get();

        return view('pages.customer.dashboard', compact(
            'user',
            'activeSession',
            'elapsedMinutes',
            'estimatedCost',
            'availableBalance',
            'userPackages',
            'transactions',
            'recentOrders',
            'sessionRemainingMinutes'
        ));
    }

    public function checkOut(Request $request)
    {
        $user = Auth::user();

        $session = SeatSession::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($session === null) {
            return redirect()->back()->with('error', 'ไม่พบเครื่องที่กำลังใช้งานอยู่');
        }

        $this->endSeatSession->handle($session);

        return redirect()->back()->with('success', 'เช็คเอาท์เรียบร้อย');
    }

    // คำนวณสถิติเวลาและค่าใช้จ่ายของเซสชันปัจจุบัน
    private function calculateSessionStats(?SeatSession $activeSession, User $user): array
    {
        $elapsedMinutes = 0;
        $estimatedCost = 0.00;
        $sessionRemainingMinutes = null;

        if ($activeSession === null) {
            return compact('elapsedMinutes', 'estimatedCost', 'sessionRemainingMinutes');
        }

        $elapsedMinutes = $this->usedMinutes($activeSession);
        $hourlyRate = (float) $activeSession->rate_snapshot;

        // กรณีใช้แบบแพ็กเกจ (ค่าบริการเป็น 0.00 เสมอ)
        if ($activeSession->user_package_id !== null && $activeSession->userPackage !== null) {
            $packageMins = (int) $activeSession->userPackage->remaining_minutes;
            $sessionRemainingMinutes = max(0, $packageMins - $elapsedMinutes);
            $estimatedCost = 0.00;
        // กรณีใช้แบบเติมเงินแล้วหักตามจริง (Pay as you go)
        } else {
            $estimatedCost = round(($elapsedMinutes / 60) * $hourlyRate, 2);
            if ($hourlyRate > 0) {
                $totalAffordableMins = (int) floor(((float) $user->balance / $hourlyRate) * 60);
                $sessionRemainingMinutes = max(0, $totalAffordableMins - $elapsedMinutes);
            }
        }

        return compact('elapsedMinutes', 'estimatedCost', 'sessionRemainingMinutes');
    }

    // คำนวณยอดเงินที่ใช้ได้จริงหลังหักค่าเครื่องที่กำลังเล่นอยู่
    private function calculateAvailableBalance(User $user, ?SeatSession $activeSession = null): float
    {
        $balance = (float) $user->balance;

        // ถ้าไม่ได้เปิดเครื่อง หรือเล่นด้วยแพ็กเกจเวลา เงินในกระเป๋าจะใช้ได้เต็มจำนวน
        if ($activeSession === null || $activeSession->user_package_id !== null) {
            return max(0.00, $balance);
        }

        $usedMinutes = $this->usedMinutes($activeSession);
        $hourlyRate = (float) $activeSession->rate_snapshot;
        $estimatedCost = round(($usedMinutes / 60) * $hourlyRate, 2);

        return max(0.00, round($balance - $estimatedCost, 2));
    }

    // คำนวณจำนวนนาทีที่เซสชันใช้งานไปแล้วจนถึงปัจจุบัน
    private function usedMinutes(SeatSession $session): int
    {
        $startTime = Carbon::parse($session->start_time);
        return max(1, (int) ceil($startTime->diffInSeconds(Carbon::now()) / 60));
    }

    // คำนวณจำนวนนาทีสูงสุดที่ผู้ใช้เล่นได้ตามแพ็กเกจหรือยอดเงินคงเหลือ
    private function getMaxAllowedMinutes(SeatSession $session, User $user): int
    {
        // กรณีแพ็กเกจ: เล่นได้ตามเวลาแพ็กเกจที่มี หมดเวลาแล้วตัดจบ
        if ($session->user_package_id !== null && $session->userPackage !== null) {
            return (int) $session->userPackage->remaining_minutes;
        }

        // กรณีคิดตามจริง: เล่นได้จนกว่าเงินในกระเป๋าจะหมด
        $hourlyRate = (float) $session->rate_snapshot;
        $balance = max(0.0, (float) $user->balance);
        return $hourlyRate > 0 ? (int) floor(($balance / $hourlyRate) * 60) : 0;
    }

    // ตรวจสอบและตัดจบเซสชันอัตโนมัติเมื่อเวลาแพ็กเกจหรือยอดเงินหมด
    private function autoEndExpiredSessions(): void
    {
        $activeSessions = SeatSession::where('status', 'active')
            ->with(['user', 'userPackage'])
            ->get();

        foreach ($activeSessions as $session) {
            $user = $session->user;
            if ($user === null) {
                continue;
            }

            $usedMinutes = $this->usedMinutes($session);
            $maxAllowedMinutes = $this->getMaxAllowedMinutes($session, $user);

            if ($usedMinutes >= $maxAllowedMinutes) {
                // คำนวณเวลาสิ้นสุดที่เวลาหรือเงินหมดจริง (ไม่เกินเวลาปัจจุบัน)
                $endTime = Carbon::parse($session->start_time)->addMinutes(max(1, $maxAllowedMinutes));
                if ($endTime->isAfter(Carbon::now())) {
                    $endTime = Carbon::now();
                }

                $this->endSeatSession->handle($session, $endTime);
            }
        }
    }
}

