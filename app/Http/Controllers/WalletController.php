<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\User;
use App\Models\UserPackage;
use App\Models\WalletTransaction;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $zones = Zone::with('packages')->get();
        $packages = $zones->flatMap->packages;

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

        $transactions = WalletTransaction::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        $myPackages = $userPackages;

        return view('pages.customer.topup', compact('user', 'zones', 'packages', 'userPackages', 'myPackages', 'transactions'));
    }

    public function topUp(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:20|max:10000',
            'topup_method' => 'required|in:qr,cash',
        ]);

        $amount = (float) $request->input('amount');
        $topupMethod = $request->input('topup_method');
        $refType = ($topupMethod === 'qr') ? 'qr_topup' : 'cash_topup';
        $user = Auth::user();

        DB::transaction(function () use ($user, $amount, $refType) {
            $freshUser = User::where('id', $user->id)->lockForUpdate()->first();
            $freshUser->balance = (float) $freshUser->balance + $amount;
            $freshUser->save();

            WalletTransaction::create([
                'user_id' => $freshUser->id,
                'type' => 'topup',
                'amount' => $amount,
                'ref_type' => $refType,
                'ref_id' => null,
            ]);
        });

        return redirect()->back()->with('success', 'เติมเงินสำเร็จ ฿' . number_format($amount, 2));
    }

    public function buyPackage(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        $package = Package::with('zone')->findOrFail((int) $request->input('package_id'));
        $user = Auth::user();

        if ((float) $user->balance < (float) $package->price) {
            return redirect()->back()->with('error', 'ยอดเงินในกระเป๋าไม่พอซื้อแพ็กเกจนี้ กรุณาเติมเงินก่อน');
        }

        try {
            DB::transaction(function () use ($user, $package) {
                $freshUser = User::where('id', $user->id)->lockForUpdate()->first();
                $packagePrice = (float) $package->price;

                if ((float) $freshUser->balance < $packagePrice) {
                    throw new \Exception('ยอดเงินในกระเป๋าไม่พอซื้อแพ็กเกจนี้ กรุณาเติมเงินก่อน');
                }

                $freshUser->balance = (float) $freshUser->balance - $packagePrice;
                $freshUser->save();

                WalletTransaction::create([
                    'user_id' => $freshUser->id,
                    'type' => 'deduct',
                    'amount' => $packagePrice,
                    'ref_type' => 'package_purchase',
                    'ref_id' => $package->id,
                ]);

                $totalMinutes = $package->duration_hours * 60;
                UserPackage::create([
                    'user_id' => $freshUser->id,
                    'package_id' => $package->id,
                    'remaining_minutes' => $totalMinutes,
                    'purchased_at' => Carbon::now(),
                    'expired_at' => Carbon::now()->addDays(30),
                ]);
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $zoneName = $package->zone ? $package->zone->name : 'ทุกโซน';
        $totalMinutes = $package->duration_hours * 60;
        $formattedDuration = UserPackage::formatMinutes($totalMinutes);

        return redirect()->back()->with('success', "ซื้อแพ็กเกจ {$package->name} สำเร็จ ได้รับเวลา {$formattedDuration} สำหรับใช้งานในโซน {$zoneName}");
    }
}
