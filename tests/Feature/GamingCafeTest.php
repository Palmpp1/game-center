<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Package;
use App\Models\Product;
use App\Models\Seat;
use App\Models\SeatSession;
use App\Models\User;
use App\Models\UserPackage;
use App\Models\WalletTransaction;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GamingCafeTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Zone $zone;
    protected Seat $seat;
    protected Package $package;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Player 1',
            'username' => 'player1',
            'email' => 'p1@test.com',
            'role' => 'customer',
            'balance' => 200.00,
            'password' => bcrypt('password'),
        ]);

        $this->zone = Zone::create([
            'name' => 'Standard Zone',
            'hourly_rate' => 60.00, // 1 THB per minute
        ]);

        $this->seat = Seat::create([
            'zone_id' => $this->zone->id,
            'seat_number' => 'PC-01',
            'status' => 'available',
        ]);

        $this->package = Package::create([
            'zone_id' => $this->zone->id,
            'name' => '2 Hours Promo',
            'duration_hours' => 2,
            'price' => 100.00,
        ]);

        $cat = Category::create(['name' => 'Food']);
        $this->product = Product::create([
            'category_id' => $cat->id,
            'name' => 'Ramyeon',
            'price' => 50.00,
            'stock_quantity' => 10,
        ]);
    }

    public function test_wallet_topup_and_deduct_creates_audit_log(): void
    {
        $this->actingAs($this->user);

        // 1. เติมเงินผ่าน Controller
        $response = $this->post(route('customer.do-topup'), [
            'amount' => 100.00,
            'topup_method' => 'cash',
        ]);
        $response->assertSessionHas('success');

        $this->user->refresh();
        $this->assertEquals(300.00, (float) $this->user->balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $this->user->id,
            'type' => 'topup',
            'amount' => 100.00,
        ]);
    }

    public function test_topup_rejects_invalid_amount(): void
    {
        $this->actingAs($this->user);

        // ทดสอบส่งค่าน้อยกว่า 20 (เช่น 15 บาท)
        $resLow = $this->post(route('customer.do-topup'), [
            'amount' => 15,
            'topup_method' => 'cash',
        ]);
        $resLow->assertSessionHasErrors('amount');

        // ทดสอบส่งค่าเกิน 100,000
        $resExcess = $this->post(route('customer.do-topup'), [
            'amount' => 150000,
            'topup_method' => 'cash',
        ]);
        $resExcess->assertSessionHasErrors('amount');
    }

    public function test_buy_package_and_checkin_checkout_flow(): void
    {
        $now = Carbon::create(2026, 9, 18, 12, 0, 0);
        Carbon::setTestNow($now);

        $this->actingAs($this->user);

        // 1. ซื้อแพ็กเกจผ่าน WalletController
        $buyRes = $this->post(route('customer.buy-package'), [
            'package_id' => $this->package->id,
        ]);
        $buyRes->assertSessionHas('success');

        $this->user->refresh();
        $this->assertEquals(100.00, (float) $this->user->balance);

        $userPkg = UserPackage::where('user_id', $this->user->id)->first();
        $this->assertNotNull($userPkg);
        $this->assertEquals(120, $userPkg->remaining_minutes);

        // 2. เช็คอินเปิดเครื่องด้วยแพ็กเกจผ่าน SeatController
        $checkInRes = $this->post(route('customer.check-in'), [
            'seat_id' => $this->seat->id,
            'billing_mode' => 'package',
            'user_package_id' => $userPkg->id,
        ]);
        $checkInRes->assertRedirect(route('dashboard'));

        $this->seat->refresh();
        $session = SeatSession::where('user_id', $this->user->id)->where('status', 'active')->first();
        $this->assertEquals('occupied', $this->seat->status);
        $this->assertNotNull($session);

        // 3. จำลองเวลาผ่านไป 30 นาที
        Carbon::setTestNow($now->copy()->addMinutes(30));

        // 4. เช็คเอาท์ออกจากเครื่องผ่าน DashboardController
        $checkOutRes = $this->post(route('customer.check-out'));
        $checkOutRes->assertSessionHas('success');

        $this->seat->refresh();
        $userPkg->refresh();
        $this->user->refresh();

        $this->assertEquals('available', $this->seat->status);
        $this->assertEquals(90, $userPkg->remaining_minutes); // 120 - 30 = 90
        $this->assertEquals(100.00, (float) $this->user->balance);

        Carbon::setTestNow();
    }

    public function test_order_food_with_atomic_stock_decrement(): void
    {
        $this->actingAs($this->user);

        // เปิดเครื่องก่อนสั่งอาหาร
        SeatSession::create([
            'user_id' => $this->user->id,
            'seat_id' => $this->seat->id,
            'start_time' => Carbon::now(),
            'rate_snapshot' => 60.00,
            'status' => 'active',
        ]);
        $this->seat->update(['status' => 'occupied']);

        // สั่งอาหารผ่าน FoodOrderController
        $res = $this->post(route('customer.place-order'), [
            'seat_id' => $this->seat->id,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 2],
            ],
            'payment_method' => 'wallet',
        ]);
        $res->assertSessionHas('success');

        $this->product->refresh();
        $this->user->refresh();

        $this->assertEquals(8, $this->product->stock_quantity); // 10 - 2 = 8
        $this->assertEquals(100.00, (float) $this->user->balance); // 200 - 100 = 100

        $order = Order::where('user_id', $this->user->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals(100.00, (float) $order->total_amount);
        $this->assertEquals('paid', $order->payment_status);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 50.00,
        ]);
    }

    public function test_cash_order_requires_staff_confirmation(): void
    {
        $this->actingAs($this->user);

        SeatSession::create([
            'user_id' => $this->user->id,
            'seat_id' => $this->seat->id,
            'start_time' => Carbon::now(),
            'rate_snapshot' => 60.00,
            'status' => 'active',
        ]);

        $res = $this->post(route('customer.place-order'), [
            'seat_id' => $this->seat->id,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1],
            ],
            'payment_method' => 'cash',
        ]);
        $res->assertSessionHas('success');

        $order = Order::where('user_id', $this->user->id)->latest()->first();
        $this->assertEquals('pending_payment', $order->payment_status);

        // พนักงานกดยืนยันรับเงินสด
        $staff = User::factory()->create(['role' => 'staff']);
        $confirmRes = $this->actingAs($staff)->post(route('staff.confirm-cash'), [
            'order_id' => $order->id,
        ]);
        $confirmRes->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
    }

    public function test_pay_as_you_go_billing_deducts_wallet(): void
    {
        $now = Carbon::create(2026, 9, 18, 14, 0, 0);
        Carbon::setTestNow($now);

        $this->actingAs($this->user);

        // เช็คอินแบบคิดตามจริง
        $this->post(route('customer.check-in'), [
            'seat_id' => $this->seat->id,
            'billing_mode' => 'pay_as_you_go',
        ]);

        // จำลองเวลาเล่น 60 นาที (60 บาท)
        Carbon::setTestNow($now->copy()->addMinutes(60));

        $this->post(route('customer.check-out'));
        $this->user->refresh();

        $this->assertEquals(140.00, (float) $this->user->balance); // 200 - 60 = 140
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $this->user->id,
            'type' => 'deduct',
            'amount' => 60.00,
            'ref_type' => 'session',
        ]);

        Carbon::setTestNow();
    }

    public function test_insufficient_balance_cannot_checkin_pay_as_you_go(): void
    {
        $this->actingAs($this->user);
        // มียอดเงิน 0.00 บาท
        $this->user->update(['balance' => 0.00]);

        $response = $this->from(route('customer.seat-map'))->post(route('customer.check-in'), [
            'seat_id' => $this->seat->id,
            'billing_mode' => 'pay_as_you_go',
        ]);

        $response->assertRedirect(route('customer.seat-map'));
        $response->assertSessionHas('error');

        $this->seat->refresh();
        $this->assertEquals('available', $this->seat->status);

        $session = SeatSession::where('user_id', $this->user->id)->where('status', 'active')->first();
        $this->assertNull($session);

        $this->user->refresh();
        $this->assertEquals(0.00, (float) $this->user->balance);
    }

    public function test_package_session_ends_without_deducting_wallet_balance(): void
    {
        $now = Carbon::create(2026, 9, 18, 16, 0, 0);
        Carbon::setTestNow($now);

        $this->actingAs($this->user);

        // ซื้อแพ็กเกจ (ราคา 100 บาท จากเงินเดิม 200 บาท เหลือ 100 บาท)
        $this->post(route('customer.buy-package'), [
            'package_id' => $this->package->id,
        ]);

        $userPkg = UserPackage::where('user_id', $this->user->id)->first();
        $userPkg->update(['remaining_minutes' => 30]); // เหลือ 30 นาที

        // เช็คอินด้วยแพ็กเกจ
        $this->post(route('customer.check-in'), [
            'seat_id' => $this->seat->id,
            'billing_mode' => 'package',
            'user_package_id' => $userPkg->id,
        ]);

        // จำลองเล่น 50 นาที (เกินเวลาแพ็กเกจ 30 นาที)
        Carbon::setTestNow($now->copy()->addMinutes(50));

        $this->post(route('customer.check-out'));
        $userPkg->refresh();
        $this->user->refresh();

        // เวลาในแพ็กเกจถูกตัดเหลือ 0
        $this->assertEquals(0, $userPkg->remaining_minutes);
        // เงินในกระเป๋าต้องคงเดิม 100 บาท ไม่มีการหักเงินเพิ่ม
        $this->assertEquals(100.00, (float) $this->user->balance);

        // ต้องไม่มีการหักเงินค่าล่วงเวลา session_overtime
        $this->assertDatabaseMissing('wallet_transactions', [
            'user_id' => $this->user->id,
            'ref_type' => 'session_overtime',
        ]);

        Carbon::setTestNow();
    }

    public function test_available_balance_prevents_double_spending_on_food_order(): void
    {
        $now = Carbon::create(2026, 9, 18, 18, 0, 0);
        Carbon::setTestNow($now);

        $this->user->update(['balance' => 50.00]);
        $this->actingAs($this->user);

        // เช็คอินเปิดเครื่อง
        $this->post(route('customer.check-in'), [
            'seat_id' => $this->seat->id,
            'billing_mode' => 'pay_as_you_go',
        ]);

        // เล่นไป 30 นาที (ค่าเครื่อง 30 บาท -> เงินใช้ได้เหลือ 20 บาท)
        Carbon::setTestNow($now->copy()->addMinutes(30));

        // สั่งอาหารราคา 50 บาท ซึ่งเกินกว่า 20 บาทที่เหลืออยู่
        $res = $this->post(route('customer.place-order'), [
            'seat_id' => $this->seat->id,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1],
            ],
            'payment_method' => 'wallet',
        ]);

        // ต้องแจ้งเตือน error และไม่หักเงิน
        $res->assertSessionHas('error');
        $this->user->refresh();
        $this->assertEquals(50.00, (float) $this->user->balance);

        Carbon::setTestNow();
    }

    public function test_customer_cannot_access_staff_or_admin_portal(): void
    {
        $this->actingAs($this->user);

        $responseStaff = $this->get(route('staff.seat-monitor'));
        $responseStaff->assertStatus(403);

        $responseAdmin = $this->get(route('admin.stock-manager'));
        $responseAdmin->assertStatus(403);
    }

    public function test_ordering_food_without_active_session_is_rejected(): void
    {
        $this->actingAs($this->user);

        // สั่งอาหารโดยที่ยังไม่ได้เปิดเครื่อง (ไม่มี active session)
        $res = $this->post(route('customer.place-order'), [
            'seat_id' => $this->seat->id,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1],
            ],
            'payment_method' => 'wallet',
        ]);

        $res->assertSessionHas('error', 'คุณยังไม่ได้เปิดใช้งานเครื่องนี้ กรุณาเปิดเครื่องก่อนทำการสั่งอาหาร');
        $this->assertEquals(10, $this->product->fresh()->stock_quantity);
    }

    public function test_auto_end_expired_package_session_deducts_and_frees_seat(): void
    {
        $this->actingAs($this->user);
        $this->user->update(['balance' => 0.00]);

        $userPkg = UserPackage::create([
            'user_id' => $this->user->id,
            'package_id' => $this->package->id,
            'remaining_minutes' => 60,
            'purchased_at' => Carbon::now()->subHours(2),
        ]);

        // เปิดเครื่องด้วยแพ็กเกจ 60 นาที แต่เล่นไปแล้ว 65 นาที และ balance = 0
        $session = SeatSession::create([
            'user_id' => $this->user->id,
            'seat_id' => $this->seat->id,
            'user_package_id' => $userPkg->id,
            'start_time' => Carbon::now()->subMinutes(65),
            'rate_snapshot' => 60.00,
            'status' => 'active',
        ]);
        $this->seat->update(['status' => 'occupied']);

        $res = $this->get(route('dashboard'));
        $res->assertOk();

        $session->refresh();
        $this->seat->refresh();
        $userPkg->refresh();

        $this->assertEquals('completed', $session->status);
        $this->assertEquals('available', $this->seat->status);
        $this->assertEquals(0, $userPkg->remaining_minutes);
    }

    public function test_buy_package_with_insufficient_balance_is_rejected(): void
    {
        $this->user->update(['balance' => 20.00]);
        $this->actingAs($this->user);

        // ราคาแพ็กเกจ 100 บาท แต่มีเงิน 20 บาท
        $res = $this->post(route('customer.buy-package'), [
            'package_id' => $this->package->id,
        ]);

        $res->assertSessionHas('error', 'ยอดเงินในกระเป๋าไม่พอซื้อแพ็กเกจนี้ กรุณาเติมเงินก่อน');
        $this->assertEquals(20.00, (float) $this->user->fresh()->balance);
        $this->assertDatabaseMissing('user_packages', [
            'user_id' => $this->user->id,
            'package_id' => $this->package->id,
        ]);
    }

    public function test_order_food_with_insufficient_stock_is_rejected(): void
    {
        $this->actingAs($this->user);

        SeatSession::create([
            'user_id' => $this->user->id,
            'seat_id' => $this->seat->id,
            'start_time' => Carbon::now(),
            'rate_snapshot' => 60.00,
            'status' => 'active',
        ]);

        // สั่ง 15 ชิ้น แต่สต็อกมีแค่ 10 ชิ้น
        $res = $this->post(route('customer.place-order'), [
            'seat_id' => $this->seat->id,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 15],
            ],
            'payment_method' => 'wallet',
        ]);

        $res->assertSessionHas('error');
        $this->assertEquals(10, $this->product->fresh()->stock_quantity);
        $this->assertEquals(200.00, (float) $this->user->fresh()->balance);
    }
}
