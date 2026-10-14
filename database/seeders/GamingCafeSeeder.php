<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Package;
use App\Models\Product;
use App\Models\Seat;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GamingCafeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Users
        $usersData = [
            [
                'name' => 'ผู้จัดการร้าน (Admin)',
                'username' => 'admin',
                'email' => 'admin@g.com',
                'phone' => '0812345678',
                'role' => 'admin',
                'balance' => 1000.00,
                'password' => Hash::make('1'),
            ],
            [
                'name' => 'พนักงานประจำร้าน (Staff)',
                'username' => 'staff',
                'email' => 'staff@g.com',
                'phone' => '0823456789',
                'role' => 'staff',
                'balance' => 500.00,
                'password' => Hash::make('1'),
            ],
            [
                'name' => 'ลูกค้าประจำ 1 (Somchai)',
                'username' => 'customer1',
                'email' => 'cust1@pcbang.test',
                'phone' => '0834567890',
                'role' => 'customer',
                'balance' => 300.00,
                'password' => Hash::make('1'),
            ],
            [
                'name' => 'ลูกค้าใหม่ 2 (Somsri)',
                'username' => 'customer2',
                'email' => 'cust2@pcbang.test',
                'phone' => '0845678901',
                'role' => 'customer',
                'balance' => 50.00,
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($usersData as $data) {
            $user = User::create($data);
            // Record initial topup in audit log
            if ($user->balance > 0) {
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'topup',
                    'amount' => $user->balance,
                    'ref_type' => 'initial_seed',
                    'ref_id' => null,
                ]);
            }
        }

        // 2. Zones
        $zoneStd = Zone::create([
            'name' => 'Standard Zone (จอ 165Hz)',
            'hourly_rate' => 40.00,
            'description' => 'คอมพิวเตอร์สเปกมาตรฐาน i5 + RTX 4060 จอ 165Hz เก้าอี้เกมมิ่ง',
        ]);

        $zoneVip = Zone::create([
            'name' => 'VIP High-End Zone (จอ 240Hz)',
            'hourly_rate' => 60.00,
            'description' => 'คอมพิวเตอร์สเปกไฮเอนด์ i7 + RTX 4080 จอ 240Hz หูฟังพรีเมียม',
        ]);

        $zoneDuo = Zone::create([
            'name' => 'Duo Private Room (ห้องคู่)',
            'hourly_rate' => 100.00,
            'description' => 'ห้องกระจกส่วนตัว 2 ที่นั่ง โซฟานุ่ม เหมาะสำหรับมาเล่นกับเพื่อนหรือแฟน',
        ]);

        // 3. Seats
        for ($i = 1; $i <= 6; $i++) {
            Seat::create([
                'zone_id' => $zoneStd->id,
                'seat_number' => sprintf('STD-%02d', $i),
                'status' => 'available',
            ]);
        }

        for ($i = 1; $i <= 4; $i++) {
            Seat::create([
                'zone_id' => $zoneVip->id,
                'seat_number' => sprintf('VIP-%02d', $i),
                'status' => 'available',
            ]);
        }

        for ($i = 1; $i <= 2; $i++) {
            Seat::create([
                'zone_id' => $zoneDuo->id,
                'seat_number' => sprintf('DUO-%02d', $i),
                'status' => 'available',
            ]);
        }

        // 4. Packages (แยกตามโซนตามอัตราค่าบริการของแต่ละโซน)
        // 4.1 Standard Zone (อัตราปกติ ฿40/ชม.)
        Package::create([
            'zone_id' => $zoneStd->id,
            'name' => 'Standard 3 ชั่วโมง',
            'duration_hours' => 3,
            'price' => 100.00,
        ]);
        Package::create([
            'zone_id' => $zoneStd->id,
            'name' => 'Standard 6 ชั่วโมง',
            'duration_hours' => 6,
            'price' => 190.00,
        ]);
        Package::create([
            'zone_id' => $zoneStd->id,
            'name' => 'Standard 12 ชั่วโมง',
            'duration_hours' => 12,
            'price' => 350.00,
        ]);

        // 4.2 VIP High-End Zone (อัตราปกติ ฿60/ชม.)
        Package::create([
            'zone_id' => $zoneVip->id,
            'name' => 'VIP 3 ชั่วโมง',
            'duration_hours' => 3,
            'price' => 150.00,
        ]);
        Package::create([
            'zone_id' => $zoneVip->id,
            'name' => 'VIP 6 ชั่วโมง',
            'duration_hours' => 6,
            'price' => 280.00,
        ]);
        Package::create([
            'zone_id' => $zoneVip->id,
            'name' => 'VIP 12 ชั่วโมง',
            'duration_hours' => 12,
            'price' => 520.00,
        ]);

        // 4.3 Duo Private Room (อัตราปกติ ฿100/ชม.)
        Package::create([
            'zone_id' => $zoneDuo->id,
            'name' => 'Duo Room 3 ชั่วโมง',
            'duration_hours' => 3,
            'price' => 250.00,
        ]);
        Package::create([
            'zone_id' => $zoneDuo->id,
            'name' => 'Duo Room 6 ชั่วโมง',
            'duration_hours' => 6,
            'price' => 480.00,
        ]);

        // 5. Food Categories & Products
        $catNoodle = Category::create(['name' => 'บะหมี่กึ่งสำเร็จรูป & มาม่า (Instant Noodles)']);
        $catRice = Category::create(['name' => 'ข้าวและอาหารจานเดียว (Rice Dishes)']);
        $catSnack = Category::create(['name' => 'ขนมขบเคี้ยว & ของทานเล่น (Snacks)']);
        $catDrink = Category::create(['name' => 'นม & เครื่องดื่มเย็น (Drinks & Beverages)']);

        // Products
        Product::create([
            'category_id' => $catNoodle->id,
            'name' => 'มาม่าต้มยำหมูสับใส่ไข่',
            'description' => 'มาม่าต้มยำน้ำข้น เสิร์ฟร้อนๆ พร้อมหมูสับลวกนุ่ม และไข่ลวกเยิ้มๆ',
            'price' => 45.00,
            'stock_quantity' => 60,
        ]);

        Product::create([
            'category_id' => $catNoodle->id,
            'name' => 'มาม่าผัดขี้เมาเบคอนกรอบ',
            'description' => 'เส้นมาม่าผัดพริกกระเทียม ใบกะเพราและเบคอนกรอบ หอมเผ็ดจัดจ้าน',
            'price' => 55.00,
            'stock_quantity' => 40,
        ]);

        Product::create([
            'category_id' => $catRice->id,
            'name' => 'ข้าวกะเพราหมูสับไข่ดาวกรอบ',
            'description' => 'เมนูยอดฮิตร้านเกม กะเพราแท้รสเด็ด โปะไข่ดาวกรอบไข่แดงเยิ้ม',
            'price' => 59.00,
            'stock_quantity' => 45,
        ]);

        Product::create([
            'category_id' => $catRice->id,
            'name' => 'ข้าวหมูกระเทียมพริกไทยไข่ดาว',
            'description' => 'สันคอหมูผัดซอสกระเทียมพริกไทย หอมเจียวกรอบ โปะไข่ดาว',
            'price' => 59.00,
            'stock_quantity' => 40,
        ]);

        Product::create([
            'category_id' => $catSnack->id,
            'name' => 'มันฝรั่งทอด เลย์ (รสโนริสาหร่าย)',
            'description' => 'เลย์ซองใหญ่ กรอบอร่อย เคี้ยวเพลินระหว่างเล่นเกม',
            'price' => 30.00,
            'stock_quantity' => 50,
        ]);

        Product::create([
            'category_id' => $catSnack->id,
            'name' => 'ไส้กรอกแดงทอดในตำนาน',
            'description' => 'ไส้กรอกแดงบั้งทอดกรอบพอง เสิร์ฟคู่น้ำจิ้มมะขามรสเด็ด',
            'price' => 35.00,
            'stock_quantity' => 50,
        ]);

        Product::create([
            'category_id' => $catSnack->id,
            'name' => 'นักเก็ตไก่ทอดกรอบ (6 ชิ้น)',
            'description' => 'นักเก็ตไก่ทอดสีเหลืองทอง กรอบนอกนุ่มใน เสิร์ฟพร้อมซอสมะเขือเทศ',
            'price' => 49.00,
            'stock_quantity' => 35,
        ]);

        Product::create([
            'category_id' => $catDrink->id,
            'name' => 'ชาไทยเย็นเข้มข้น (แก้ว 22 oz)',
            'description' => 'ชาไทยชงสด หวานมันเข้มข้น ดับกระหายคลายง่วง',
            'price' => 35.00,
            'stock_quantity' => 80,
        ]);

        Product::create([
            'category_id' => $catDrink->id,
            'name' => 'นมสดเย็นคาราเมล (แก้ว 22 oz)',
            'description' => 'นมสดแท้ผสมซอสคาราเมลหอมหวาน เย็นชื่นใจ',
            'price' => 35.00,
            'stock_quantity' => 80,
        ]);

        Product::create([
            'category_id' => $catDrink->id,
            'name' => 'โค้กกระป๋องพร้อมน้ำแข็ง (325 มล.)',
            'description' => 'โค้กเย็นเจี๊ยบ เสิร์ฟพร้อมแก้วน้ำแข็งหลอดแน่นๆ',
            'price' => 25.00,
            'stock_quantity' => 120,
        ]);
    }
}
