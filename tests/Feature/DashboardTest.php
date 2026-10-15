<?php

use App\Models\Package;
use App\Models\Seat;
use App\Models\SeatSession;
use App\Models\User;
use App\Models\UserPackage;
use App\Models\Zone;
use Illuminate\Support\Carbon;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('ประกาศร้าน');
    $response->assertSee('โปรเหมาดึก');
    $response->assertSee('ยอดเงินในกระเป๋า');
    $response->assertSee('สถานะการใช้งานเครื่องคอมพิวเตอร์ปัจจุบัน');
});

test('dashboard displays floating HUD when session is active', function () {
    $user = User::factory()->create(['balance' => 100.00]);
    $zone = Zone::create(['name' => 'VIP Zone', 'hourly_rate' => 30.00]);
    $seat = Seat::create(['zone_id' => $zone->id, 'seat_number' => 'V01', 'status' => 'occupied']);

    $session = SeatSession::create([
        'user_id' => $user->id,
        'seat_id' => $seat->id,
        'start_time' => Carbon::now()->subMinutes(10),
        'rate_snapshot' => 30.00,
        'status' => 'active',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('LETSPLAY CLIENT HUD');
    $response->assertSee('V01');
    $response->assertSee('สั่งอาหาร');
    $response->assertSee('เช็คเอาท์');
});

test('dashboard displays 15-minute time low alert when time is almost exhausted', function () {
    $user = User::factory()->create(['balance' => 0.00]);
    $zone = Zone::create(['name' => 'Standard Zone', 'hourly_rate' => 20.00]);
    $seat = Seat::create(['zone_id' => $zone->id, 'seat_number' => 'S01', 'status' => 'occupied']);
    $package = Package::create(['zone_id' => $zone->id, 'name' => 'Quick 1 Hr', 'price' => 20.00, 'duration_hours' => 1]);

    $userPackage = UserPackage::create([
        'user_id' => $user->id,
        'package_id' => $package->id,
        'remaining_minutes' => 60,
        'purchased_at' => Carbon::now(),
    ]);

    // เล่นไปแล้ว 58 นาที เหลือ 2 นาที (<= 3 นาที)
    $session = SeatSession::create([
        'user_id' => $user->id,
        'seat_id' => $seat->id,
        'user_package_id' => $userPackage->id,
        'start_time' => Carbon::now()->subMinutes(58),
        'rate_snapshot' => 20.00,
        'status' => 'active',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('แจ้งเตือนเวลาการใช้งาน: เหลือเวลาอีกประมาณ');
    $response->assertSee('TIME LOW');
    $response->assertSee('เวลาใช้งานของคุณใกล้จะหมดแล้ว');
});

test('autoEndExpiredSessions automatically completes session and deducts wallet for pay-as-you-go', function () {
    $user = User::factory()->create(['balance' => 30.00]);
    $zone = Zone::create(['name' => 'Standard Zone', 'hourly_rate' => 60.00]);
    $seat = Seat::create(['zone_id' => $zone->id, 'seat_number' => 'S02', 'status' => 'occupied']);

    // ผู้ใช้มี 30 บาท ค่าเครื่อง 60 บาท/ชม. (เล่นได้ 30 นาที) แต่เล่นไปแล้ว 35 นาที
    $session = SeatSession::create([
        'user_id' => $user->id,
        'seat_id' => $seat->id,
        'start_time' => Carbon::now()->subMinutes(35),
        'rate_snapshot' => 60.00,
        'status' => 'active',
    ]);

    $this->actingAs($user);
    $response = $this->get(route('dashboard'));
    $response->assertOk();

    $session->refresh();
    $seat->refresh();
    $user->refresh();

    expect($session->status)->toBe('completed');
    expect($seat->status)->toBe('available');
    expect((float) $user->balance)->toBe(0.00);
    $this->assertDatabaseHas('wallet_transactions', [
        'user_id' => $user->id,
        'type' => 'deduct',
        'ref_type' => 'session',
        'ref_id' => $session->id,
    ]);
});

