# Gaming Cafe Management System: Architecture & Workflow Specification

เอกสารสรุปสถาปัตยกรรม โครงสร้างฐานข้อมูล และ Flow การทำงานของระบบจัดการร้านเกม (PC Bang & Korean Cafe) สไตล์ Pure Laravel MVC

---

## 1. ข้อมูลภาพรวมและเทคโนโลยี (Tech Stack)

- **Framework:** Laravel 13.x (PHP 8.4)
- **Database:** SQLite (`database/database.sqlite`)
- **Frontend:** Pure Blade Templates, Flux UI, Tailwind CSS, Standard HTML Forms
- **Design System:** Letsplay Gaming Cafe Theme (Dark Slate, Accent Red `#dc2626`, Currency Amber `#f59e0b`)
- **Architecture Pattern:** Pure Laravel MVC (Model-View-Controller)
  - **Routes (`routes/web.php`):** จัดการเส้นทาง HTTP GET/POST ชี้ตรงไปยัง Controller
  - **Controllers (`app/Http/Controllers/`):** จัดการ Business Logic ของระบบแยกตามโมดูลการทำงาน
  - **Models (`app/Models/`):** จัดการข้อมูลและความสัมพันธ์ Eloquent
  - **Views (`resources/views/pages/`):** Pure Blade templates ทำงานผ่าน Standard HTML Forms (`<form method="POST"> @csrf`)

---

## 2. การแบ่งโมดูลการทำงานสำหรับสมาชิก 5 คน

| สมาชิก | โมดูลที่รับผิดชอบ | Controller หลัก | ไฟล์ View (Pure Blade) |
| :--- | :--- | :--- | :--- |
| **คนที่ 1** | Customer Dashboard & Profile | `DashboardController` | `resources/views/pages/customer/dashboard.blade.php` |
| **คนที่ 2** | Seat Map & PC Session Management | `SeatController` | `resources/views/pages/customer/seat-map.blade.php` |
| **คนที่ 3** | Food & Drink POS (Kitchen) | `FoodOrderController` | `resources/views/pages/customer/food-order.blade.php` |
| **คนที่ 4** | Wallet, Top-up & Packages | `WalletController` | `resources/views/pages/customer/topup.blade.php` |
| **คนที่ 5** | Staff Monitor, Kitchen Queue & Admin | `StaffController`, `AdminController` | `resources/views/pages/staff/*`, `resources/views/pages/admin/*` |

---

## 3. โครงสร้างฐานข้อมูล (Database Schema - 11 ตาราง)

1. **`users`**: จัดการบัญชีผู้ใช้
   - `id`, `name`, `username` (unique), `email` (nullable), `phone`, `role` (customer, staff, admin), `balance` (decimal 10,2), `password`, timestamps
2. **`zones`**: โซนคอมพิวเตอร์ในร้าน
   - `id`, `name`, `hourly_rate` (decimal 10,2), `description`, timestamps
3. **`seats`**: เครื่องคอมพิวเตอร์แต่ละเครื่อง
   - `id`, `zone_id`, `seat_number` (unique, เช่น `STD-01`, `VIP-01`), `status` (available, occupied, maintenance), timestamps
4. **`packages`**: แพ็กเกจชั่วโมงราคาประหยัด
   - `id`, `zone_id` (nullable), `name`, `duration_hours`, `price` (decimal 10,2), timestamps
5. **`user_packages`**: แพ็กเกจที่ผู้ใช้ซื้อสะสมไว้
   - `id`, `user_id`, `package_id`, `remaining_minutes` (int), `purchased_at`, `expired_at`, timestamps
6. **`seat_sessions`**: ประวัติและสถานะการนั่งใช้งานเครื่องคอมพิวเตอร์
   - `id`, `user_id`, `seat_id`, `user_package_id` (nullable), `rate_snapshot` (decimal 10,2), `start_time`, `end_time`, `total_cost`, `status` (active, paused, completed), timestamps
7. **`wallet_transactions`**: บันทึกประวัติการเงิน (Audit Trail)
   - `id`, `user_id`, `type` (topup, deduct, refund), `amount` (decimal 10,2), `ref_type` (cash_topup, qr_topup, session, session_overtime, food_order, package_purchase), `ref_id`, timestamps
8. **`categories`**: หมวดหมู่อาหาร/เครื่องดื่ม
   - `id`, `name`, timestamps
9. **`products`**: รายการอาหาร/เครื่องดื่มในคาเฟ่
   - `id`, `category_id`, `name`, `description`, `price`, `stock_quantity`, timestamps
10. **`orders`**: บิลคำสั่งซื้ออาหาร
    - `id`, `user_id`, `seat_id`, `session_id` (nullable), `total_amount`, `payment_method` (wallet, promptpay, cash), `payment_status` (pending_payment, paid, cancelled), `order_status` (pending, preparing, served, cancelled), timestamps
11. **`order_items`**: รายการอาหารย่อยในแต่ละคำสั่งซื้อ
    - `id`, `order_id`, `product_id`, `quantity`, `unit_price`, `subtotal`, timestamps

---

## 4. Workflows การทำงานของแต่ละระบบ (Detailed Flows)

### Flow 1: การเข้าสู่ระบบและการตรวจสอบสิทธิ์ (Authentication & Roles)
1. ผู้ใช้เข้าหน้า `/login` หรือ `/register`
2. ระบบกำหนด Role เป็น `customer` โดยอัตโนมัติสำหรับการลงทะเบียนใหม่
3. ระบบป้องกันเส้นทางผ่าน `auth` middleware ใน `routes/web.php`
4. หน้าบ้านแสดงเมนูตาม Role:
   - `customer`: ใช้งานแดชบอร์ด, ผังที่นั่ง, สั่งอาหาร, เติมเงิน
   - `staff`: เข้าถึงมอนิเตอร์ที่นั่งหน้าร้าน, คิวห้องครัว
   - `admin`: จัดการสต็อกสินค้า และรายงานรายได้ร้าน

---

### Flow 2: การเติมเงินและการซื้อแพ็กเกจ (Wallet & Package Purchase)
1. **เติมเงินเข้า Wallet (`WalletController::topUp`):**
   - ผู้ใช้เลือกจำนวนเงิน (Preset 50, 100, 200, 300, 500, 1000 บาท หรือกรอกเอง)
   - ส่งข้อมูลผ่านฟอร์ม POST `/topup` พร้อม `@csrf`
   - เพิ่มยอดเงิน `$user->balance += $amount`
   - สร้างประวัติ `WalletTransaction` (type: `topup`, ref_type: `cash_topup` หรือ `qr_topup`)
2. **ซื้อแพ็กเกจชั่วโมง (`WalletController::buyPackage`):**
   - ตรวจสอบยอดเงินคงเหลือในกระเป๋าว่าเพียงพอกับราคาแพ็กเกจหรือไม่
   - หักเงินจากกระเป๋าและบันทึก `WalletTransaction` (ref_type: `package_purchase`)
   - สร้างเรคคอร์ดใน `user_packages` กำหนด `remaining_minutes = duration_hours * 60`

---

### Flow 3: การ Check-in เข้าเครื่องคอมพิวเตอร์ (`SeatController::checkIn`)
1. ผู้ใช้เปิดหน้า `/seat-map` เลือกเครื่องคอมพิวเตอร์ที่ต้องการ
2. เลือกว่าจะใช้งานโหมดใด:
   - **โหมดแพ็กเกจ (Package):** ต้องมี `UserPackage` ที่มี `remaining_minutes > 0`
   - **โหมดคิดตามจริง (Pay-as-you-go):** ผู้ใช้ต้องมียอดเงินใน Wallet มากกว่า 0 บาท
3. ตรวจสอบความถูกต้อง:
   - ผู้ใช้ต้องไม่มี active session อยู่ที่เครื่องอื่น
   - ที่นั่งเป้าหมายต้องมีสถานะเป็น `available`
4. ปรับสถานะ `seat.status = 'occupied'`
5. สร้าง `seat_sessions` บันทึก `rate_snapshot` (ดึงราคาตามโซน ณ วินาทีที่เริ่มเล่น) และตั้ง `start_time = now()`
6. Redirect ไปหน้า `/dashboard` พร้อมข้อความแจ้งเตือน

---

### Flow 4: กลไกการคำนวณเงินและ Available Balance (ป้องกัน Double Spending)
1. **การคำนวณค่าบริการขณะเล่น:**
   - เวลาที่เล่นไป `$usedMinutes = ceil(diffInSeconds / 60)`
   - กรณี Pay-as-you-go: `$estimatedCost = round(($usedMinutes / 60) * rate_snapshot, 2)`
   - กรณี Package: คิดเงินเฉพาะส่วนเกิน (Overtime) เมื่อ `$usedMinutes > remaining_minutes`
2. **ระบบกันวงเงิน (`calculateAvailableBalance`):**
   - คำนวณ `Available Balance = max(0, $user->balance - $estimatedCost)`
   - ค่าบริการคอมพิวเตอร์ที่เกิดขึ้นแล้วจะถูกกันไว้ชั่วคราว
   - เมื่อผู้ใช้ไปหน้าสั่งอาหาร ระบบจะไม่อนุญาตให้ใช้เงินใน Wallet เกิน `Available Balance` จึงไม่มีทางเกิดการใช้เงินซ้ำซ้อน

---

### Flow 5: การตัดจบอัตโนมัติ (Auto-Cut) และการ Check-out
1. **การตัดจบอัตโนมัติ (`DashboardController::autoEndExpiredSessions`):**
   - ทุกครั้งที่มีการเปิดหน้า Dashboard ระบบจะตรวจสอบและตัดจบเครื่องที่เงินหมดให้อัตโนมัติ
   - ตรวจสอบ:
     - โหมด Pay-as-you-go: หากเล่นเกินยอดเงินที่มี
     - โหมด Package: หากหมดเวลาแพ็กเกจและไม่มีเงินในกระเป๋าจ่ายส่วนเกิน
2. **การ Check-out (`DashboardController::checkOut`):**
   - ปิด session: `status = 'completed'`, `end_time = now()`
   - คืนที่นั่ง: `seat.status = 'available'`
   - หักเงินค่าบริการหรือตัดเวลาจากแพ็กเกจ และบันทึก `WalletTransaction`

---

### Flow 6: การสั่งอาหารและเครื่องดื่มจากที่นั่ง (`FoodOrderController::placeOrder`)
1. **ผูกที่นั่งอัตโนมัติ (Auto Seat Binding):**
   - ระบบตรวจสอบ active session ของผู้เล่น และผูก `seat_id` ให้อัตโนมัติโดยผู้ใช้ไม่ต้องเลือกเครื่องเอง
2. **การตัดสต็อกสินค้า:**
   - ตรวจสอบว่าสินค้ามีพอหรือไม่ หากพอ ให้ลดสต็อกสินค้าทันที
3. **การชำระเงิน:**
   - **Wallet:** ตรวจสอบกับ `availableBalance` หักเงินทันที สถานะบิลเป็น `paid`
   - **PromptPay (จำลอง):** สถานะบิลเป็น `paid`
   - **Cash:** เก็บเงินสดปลายทางเมื่อพนักงานนำไปเสิร์ฟ สถานะบิลเป็น `pending_payment`
4. สร้างคำสั่งซื้อใน `orders` และสร้างรายการย่อยใน `order_items`

---

### Flow 7: การทำงานของพนักงานหน้าร้านและครัว (`StaffController`)
1. **มอนิเตอร์ที่นั่งหน้าร้าน (`StaffController::seatMonitor`):**
   - ดูสถานะเครื่องทุกโซน สรุปจำนวนเครื่องว่าง/กำลังเล่น/ซ่อมบำรุง
   - สามารถกด "Force End" เพื่อบังคับปิดเครื่องและคิดเงินทันที
   - สามารถกดเปิด/ปิดสถานะ "ซ่อมบำรุง (Maintenance)" ได้
2. **คิวห้องครัว (`StaffController::kitchenQueue`):**
   - แสดงออเดอร์ที่สั่งเข้ามา เรียงตามเวลา
   - พนักงานสามารถกดเปลี่ยนสถานะ: `pending` -> `preparing` -> `served`
   - สามารถกดยกเลิกออเดอร์ (`cancelled`) เพื่อคืนสต็อกสินค้า
   - กรณีชำระเงินสด สามารถกดยืนยันรับเงินสด (`confirmCashPayment`) เพื่อปรับสถานะเป็น `paid`

---

### Flow 8: การจัดการสต็อกและรายงานยอดขาย (`AdminController`)
1. **จัดการสต็อกสินค้า (`AdminController::stockManager`):**
   - ปรับเพิ่ม/ลดจำนวนสินค้า (-10, -1, +1, +10)
   - เพิ่มรายการสินค้าใหม่ (ชื่อ, หมวดหมู่, ราคา, สต็อกเริ่มต้น, คำอธิบาย)
2. **รายงานสรุปยอดขาย (`AdminController::salesReport`):**
   - กรองตามช่วงเวลา: วันนี้, 7 วันล่าสุด, 30 วันล่าสุด, ทั้งหมด
   - รวมยอดรายได้ค่าชั่วโมงเล่นเกม, แพ็กเกจ, อาหาร/เครื่องดื่ม และยอดเติมเงิน
   - แสดง 5 อันดับเมนูขายดี
   - แสดงสถิติการใช้งานแยกตามโซนที่นั่ง

