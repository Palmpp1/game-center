<?php

namespace App\Services;

use App\Models\Seat;
use App\Models\SeatSession;
use App\Models\User;
use App\Models\UserPackage;
use App\Models\WalletTransaction;
use Illuminate\Support\Carbon;

class EndSeatSession
{

    public function handle(SeatSession $session, ?Carbon $customEndTime = null): float
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($session, $customEndTime) {
            $user = User::where('id', $session->user_id)->lockForUpdate()->first();
            $seat = Seat::where('id', $session->seat_id)->lockForUpdate()->first();
            $endTime = $customEndTime ?? Carbon::now();
            $usedSeconds = Carbon::parse($session->start_time)->diffInSeconds($endTime);
            $usedMinutes = max(1, (int) ceil($usedSeconds / 60));

            $totalCost = $this->chargeSession($user, $session, $usedMinutes);

            $session->end_time = $endTime;
            $session->total_cost = $totalCost;
            $session->status = 'completed';
            $session->save();

            if ($seat !== null) {
                $seat->status = 'available';
                $seat->save();
            }

            return $totalCost;
        });
    }

    private function chargeSession(?User $user, SeatSession $session, int $usedMinutes): float
    {
        // pay as you go
        if ($session->user_package_id === null) {
            $totalCost = round(($usedMinutes / 60) * (float) $session->rate_snapshot, 2);
            $this->deductFromWallet($user, $session, $totalCost, 'session');
            return $totalCost;
        }

        // package
        $userPackage = UserPackage::where('id', $session->user_package_id)->lockForUpdate()->first();
        if ($userPackage === null) {
            return 0.00;
        }

        $userPackage->remaining_minutes = max(0, $userPackage->remaining_minutes - $usedMinutes);
        $userPackage->save();

        return 0.00;
    }

    private function deductFromWallet(?User $user, SeatSession $session, float $amount, string $referenceType): void
    {
        if ($amount <= 0 || $user === null) {
            return;
        }

        $user->balance = max(0.00, round((float) $user->balance - $amount, 2));
        $user->save();

        WalletTransaction::create([
            'user_id' => $user->id,
            'type' => 'deduct',
            'amount' => $amount,
            'ref_type' => $referenceType,
            'ref_id' => $session->id,
        ]);
    }
}
