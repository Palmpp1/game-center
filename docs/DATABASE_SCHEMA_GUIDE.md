# คู่มือโครงสร้างฐานข้อมูลและข้อกำหนดทางเทคนิค (Database Technical Guide)
**โปรเจกต์:** ระบบบริหารจัดการร้านเกมคาเฟ่ (LETSPLAY GAMING)  
**สแตกเทคโนโลยี:** PHP 8.4, Laravel 12, SQLite (`database/database.sqlite`)

---

## 1. ภาพรวมระบบฐานข้อมูล (Database Overview)
ระบบฐานข้อมูลประกอบด้วย **11 ตารางหลัก** ที่ใช้ในการดำเนินงานของร้านเกมคาเฟ่ แบ่งออกเป็น 4 กลุ่มฟังก์ชันหลัก:
1. **กลุ่มผู้ใช้และกระเป๋าเงิน (User & Wallet):** `users`, `wallet_transactions`
2. **กลุ่มโซนและเครื่องคอมพิวเตอร์ (Zones, Seats & Sessions):** `zones`, `seats`, `seat_sessions`
3. **กลุ่มแพ็กเกจชั่วโมง (Time Packages):** `packages`, `user_packages`
4. **กลุ่มอาหาร เครื่องดื่ม และสต็อกสินค้า (Food, Orders & Products):** `categories`, `products`, `orders`, `order_items`

---

## 2. รายละเอียด 11 ตารางหลัก (Tables Specification)

### กลุ่มที่ 1: ผู้ใช้งานและกระเป๋าเงิน (User & Wallet)

#### 1. ตาราง `users`
เก็บข้อมูลบัญชีผู้ใช้งานระบบทั้งหมด ทั้งลูกค้า (Customer), พนักงานร้าน (Staff) และผู้ดูแลระบบ (Admin)
- **Model:** `App\Models\User`
- **Fillable Fields:** `name`, `username`, `email`, `phone`, `role`, `balance`, `password`
- **โครงสร้างคอลัมน์:**
  - `id` (bigint, PK): รหัสผู้ใช้งาน
  - `name` (string): ชื่อ-นามสกุล หรือชื่อเรียก
  - `username` (string 50, unique): ชื่อบัญชีสำหรับล็อกอิน
  - `email` (string, nullable, unique): อีเมล
  - `phone` (string 20, nullable): เบอร์โทรศัพท์
  - `role` (string 20, default: `customer`): สิทธิ์การใช้งาน (`customer`, `staff`, `admin`)
  - `balance` (decimal 10,2, default: 0.00): ยอดเงินคงเหลือในกระเป๋าเงินดิจิทัล (Wallet)
  - `password` (string): รหัสผ่านที่เข้ารหัสแบบ Hashed
  - `remember_token` (string 100, nullable): โทเค็นจดจำการล็อกอิน
  - `created_at`, `updated_at` (timestamps)
- **ความสัมพันธ์ (Relationships):**
  - `hasMany(WalletTransaction::class)`: ผู้ใช้ 1 คน มีประวัติธุรกรรมกระเป๋าเงินได้หลายรายการ
  - `hasMany(UserPackage::class)`: ผู้ใช้ 1 คน มีแพ็กเกจชั่วโมงที่ซื้อไว้ได้หลายแพ็กเกจ
  - `belongsToMany(Package::class, 'user_packages')`: ผู้ใช้เชื่อมกับแพ็กเกจผ่านตาราง pivot `user_packages`
  - `hasMany(SeatSession::class)`: ผู้ใช้ 1 คน มีประวัติการเปิดเครื่องเล่นเกมได้หลายครั้ง
  - `hasOne(SeatSession::class)->where('status', 'active')`: ดึงเซสชันเครื่องที่กำลังเล่นอยู่ปัจจุบัน
  - `hasMany(Order::class)`: ผู้ใช้ 1 คน มีประวัติสั่งอาหารได้หลายออเดอร์

---

#### 2. ตาราง `wallet_transactions`
ทำหน้าที่เป็น Audit Log บันทึกประวัติการเปลี่ยนแปลงยอดเงินในกระเป๋าเงินทุกบาททุกสตางค์ เพื่อความโปร่งใสและตรวจสอบย้อนหลังได้
- **Model:** `App\Models\WalletTransaction`
- **Fillable Fields:** `user_id`, `type`, `amount`, `ref_type`, `ref_id`
- **โครงสร้างคอลัมน์:**
  - `id` (bigint, PK): รหัสรายการธุรกรรม
  - `user_id` (foreignId -> `users.id`, Restrict): บัญชีผู้ใช้ที่ทำรายการ
  - `type` (string 20): ประเภทธุรกรรม ได้แก่ `topup` (เติมเงิน), `deduct` (หักเงิน), `refund` (คืนเงิน)
  - `amount` (decimal 10,2): จำนวนเงินของธุรกรรม
  - `ref_type` (string 50, nullable): ที่มาของรายการ เช่น `session` (ค่าเครื่อง), `order` (ค่าอาหาร), `package_purchase` (ซื้อแพ็กเกจ), `cash_topup` (เติมเงินสด), `qr_topup` (เติมผ่าน QR)
  - `ref_id` (unsignedBigInteger, nullable): ไอดีอ้างอิงจากตารางต้นทาง เช่น `session_id` หรือ `order_id`
  - `created_at`, `updated_at` (timestamps)
- **ความสัมพันธ์ (Relationships):**
  - `belongsTo(User::class)`: บัญชีผู้ใช้เจ้าของธุรกรรม

---

### กลุ่มที่ 2: โซนและเครื่องคอมพิวเตอร์ (Zones, Seats & Sessions)

#### 3. ตาราง `zones`
เก็บข้อมูลโซนเครื่องคอมพิวเตอร์ในร้าน และกำหนดราคาค่าบริการต่อชั่วโมงแยกตามโซน
- **Model:** `App\Models\Zone`
- **Fillable Fields:** `name`, `hourly_rate`, `description`
- **โครงสร้างคอลัมน์:**
  - `id` (bigint, PK): รหัสโซน
  - `name` (string 100): ชื่อโซน (เช่น Standard Zone จอ 165Hz, VIP High-End Zone จอ 240Hz, Duo Private Room)
  - `hourly_rate` (decimal 10,2): อัตราค่าบริการต่อชั่วโมง (เช่น 40.00, 60.00, 100.00 บาท)
  - `description` (text, nullable): รายละเอียดสเปกเครื่องหรือบรรยากาศของโซน
  - `created_at`, `updated_at` (timestamps)
- **ความสัมพันธ์ (Relationships):**
  - `hasMany(Seat::class)`: 1 โซน มีเครื่องคอมพิวเตอร์ตั้งอยู่หลายเครื่อง
  - `hasMany(Package::class)`: 1 โซน มีแพ็กเกจชั่วโมงเฉพาะโซนได้หลายแพ็กเกจ

---

#### 4. ตาราง `seats`
เก็บข้อมูลตำแหน่งที่นั่งและเครื่องคอมพิวเตอร์แต่ละเครื่องในร้าน
- **Model:** `App\Models\Seat`
- **Fillable Fields:** `zone_id`, `seat_number`, `status`
- **โครงสร้างคอลัมน์:**
  - `id` (bigint, PK): รหัสเครื่อง
  - `zone_id` (foreignId -> `zones.id`, Restrict): โซนที่เครื่องนี้ตั้งอยู่
  - `seat_number` (string 20, unique): หมายเลขเครื่อง เช่น STD-01, VIP-02, DUO-01
  - `status` (string 20, default: `available`): สถานะเครื่อง ได้แก่ `available` (ว่าง), `occupied` (กำลังใช้งาน), `maintenance` (ปิดซ่อมบำรุง)
  - `created_at`, `updated_at` (timestamps)
- **ความสัมพันธ์ (Relationships):**
  - `belongsTo(Zone::class)`: เครื่องนี้อยู่ในโซนใด
  - `hasMany(SeatSession::class)`: ประวัติการเปิดใช้งานเครื่องทั้งหมด
  - `hasOne(SeatSession::class)->where('status', 'active')`: เซสชันปัจจุบันที่กำลังใช้งานเครื่องนี้อยู่
  - `hasMany(Order::class)`: รายการสั่งอาหารที่ระบุให้มาส่งที่เครื่องนี้

---

#### 5. ตาราง `seat_sessions`
บันทึกรอบการเปิดใช้งานเครื่องคอมพิวเตอร์ (Check-in จนถึง Check-out)
- **Model:** `App\Models\SeatSession`
- **Fillable Fields:** `user_id`, `seat_id`, `user_package_id`, `rate_snapshot`, `start_time`, `end_time`, `total_cost`, `status`
- **โครงสร้างคอลัมน์:**
  - `id` (bigint, PK): รหัสเซสชันการใช้งาน
  - `user_id` (foreignId -> `users.id`, Restrict): ลูกค้าผู้เปิดใช้งานเครื่อง
  - `seat_id` (foreignId -> `seats.id`, Restrict): เครื่องที่เปิดใช้งาน
  - `user_package_id` (foreignId -> `user_packages.id`, nullable, Restrict): ไอดีแพ็กเกจที่นำมาใช้หักเวลา (หากเป็น null คือระบบคิดตามจริง Pay As You Go)
  - `rate_snapshot` (decimal 10,2): อัตราค่าบริการต่อชั่วโมง ณ วินาทีที่เปิดเครื่อง (Snapshot ค่าไว้ป้องกันกรณีร้านปรับราคาโซนในภายหลัง)
  - `start_time` (timestamp): เวลาเริ่มเปิดเครื่อง
  - `end_time` (timestamp, nullable): เวลาสิ้นสุดการใช้งาน (เช็คเอาท์)
  - `total_cost` (decimal 10,2, default: 0.00): ยอดเงินที่หักจริงเมื่อจบการใช้งาน (หากใช้แพ็กเกจจะเป็น 0.00)
  - `status` (string 20, default: `active`): สถานะเซสชัน ได้แก่ `active` (กำลังเล่นอยู่), `completed` (สิ้นสุดแล้ว)
  - `created_at`, `updated_at` (timestamps)
- **ความสัมพันธ์ (Relationships):**
  - `belongsTo(User::class)`: ใครเป็นผู้ใช้งาน
  - `belongsTo(Seat::class)`: ใช้งานเครื่องใด
  - `belongsTo(UserPackage::class)`: หักเวลาจากแพ็กเกจใด (ถ้ามี)
  - `hasMany(Order::class, 'session_id')`: ออเดอร์อาหารที่สั่งระหว่างใช้งานรอบนี้

---

### กลุ่มที่ 3: แพ็กเกจชั่วโมงเล่นเกม (Time Packages)

#### 6. ตาราง `packages`
เก็บข้อมูลรูปแบบแพ็กเกจชั่วโมงที่ร้านเปิดขายให้ลูกค้าซื้อล่วงหน้าในราคาโปรโมชัน
- **Model:** `App\Models\Package`
- **Fillable Fields:** `zone_id`, `name`, `duration_hours`, `price`
- **โครงสร้างคอลัมน์:**
  - `id` (bigint, PK): รหัสแพ็กเกจ
  - `zone_id` (foreignId -> `zones.id`, nullable, Restrict): โซนที่แพ็กเกจนี้สามารถนำไปใช้ได้ (หากเป็น null คือใช้ได้ทุกโซน)
  - `name` (string 100): ชื่อแพ็กเกจ เช่น Standard 3 ชม., VIP 5 ชม.
  - `duration_hours` (integer): จำนวนชั่วโมงการเล่นที่ได้รับ
  - `price` (decimal 10,2): ราคาขายของแพ็กเกจ
  - `created_at`, `updated_at` (timestamps)
- **ความสัมพันธ์ (Relationships):**
  - `belongsTo(Zone::class)`: ผูกกับโซนใด
  - `hasMany(UserPackage::class)`: รายการที่ลูกค้าซื้อแพ็กเกจนี้ไป

---

#### 7. ตาราง `user_packages`
เก็บข้อมูลแพ็กเกจที่ลูกค้าซื้อเก็บไว้ในบัญชี และจำนวนนาทีคงเหลือที่ยังเล่นได้ (Rich Pivot Table)
- **Model:** `App\Models\UserPackage`
- **Fillable Fields:** `user_id`, `package_id`, `remaining_minutes`, `purchased_at`, `expired_at`
- **โครงสร้างคอลัมน์:**
  - `id` (bigint, PK): รหัสแพ็กเกจของผู้ใช้
  - `user_id` (foreignId -> `users.id`, Restrict): ลูกค้าเจ้าของแพ็กเกจ
  - `package_id` (foreignId -> `packages.id`, Restrict): แพ็กเกจที่ซื้อ
  - `remaining_minutes` (integer): จำนวนเวลาคงเหลือที่ยังเล่นได้ (หน่วยเป็นนาที)
  - `purchased_at` (timestamp, default: current timestamp): เวลาที่ซื้อ
  - `expired_at` (timestamp, nullable): วันและเวลาหมดอายุของแพ็กเกจ
  - `created_at`, `updated_at` (timestamps)
- **ความสัมพันธ์ (Relationships):**
  - `belongsTo(User::class)`: เป็นของลูกค้าคนใด
  - `belongsTo(Package::class)`: ชนิดของแพ็กเกจต้นฉบับ
  - `hasMany(SeatSession::class)`: เซสชันการใช้งานเครื่องที่ตัดเวลาจากแพ็กเกจนี้

---

### กลุ่มที่ 4: สั่งอาหาร เครื่องดื่ม และสต็อกสินค้า (Food, Orders & Products)

#### 8. ตาราง `categories`
เก็บหมวดหมู่อาหารและเครื่องดื่มสำหรับแสดงในหน้าสั่งอาหาร (POS)
- **Model:** `App\Models\Category`
- **Fillable Fields:** `name`
- **โครงสร้างคอลัมน์:**
  - `id` (bigint, PK): รหัสหมวดหมู่
  - `name` (string 100): ชื่อหมวดหมู่ เช่น อาหารจานเดียว, ของทานเล่น, เครื่องดื่ม
  - `created_at`, `updated_at` (timestamps)
- **ความสัมพันธ์ (Relationships):**
  - `hasMany(Product::class)`: 1 หมวดหมู่ มีสินค้าได้หลายรายการ

---

#### 9. ตาราง `products`
เก็บรายการอาหาร ขนม เครื่องดื่ม และจำนวนสต็อกสินค้า
- **Model:** `App\Models\Product`
- **Fillable Fields:** `category_id`, `name`, `description`, `price`, `stock_quantity`, `image`
- **โครงสร้างคอลัมน์:**
  - `id` (bigint, PK): รหัสสินค้า
  - `category_id` (foreignId -> `categories.id`, Restrict): หมวดหมู่สินค้า
  - `name` (string 150): ชื่อสินค้า
  - `description` (text, nullable): รายละเอียดสินค้า
  - `price` (decimal 10,2): ราคาต่อหน่วย
  - `stock_quantity` (integer, default: 0): จำนวนสินค้าคงเหลือในสต็อก (ตัดสต็อกอัตโนมัติเมื่อมีการสั่งซื้อ)
  - `image` (string 255, nullable): ที่อยู่ไฟล์รูปภาพสินค้า
  - `created_at`, `updated_at` (timestamps)
- **ความสัมพันธ์ (Relationships):**
  - `belongsTo(Category::class)`: อยู่ในหมวดหมู่ใด
  - `hasMany(OrderItem::class)`: ประวัติการสั่งสินค้านี้ในออเดอร์ต่างๆ

---

#### 10. ตาราง `orders`
เก็บข้อมูลคำสั่งซื้ออาหารและเครื่องดื่มที่ลูกค้าสั่งจากหน้าเครื่องคอมพิวเตอร์
- **Model:** `App\Models\Order`
- **Fillable Fields:** `user_id`, `seat_id`, `session_id`, `total_amount`, `payment_method`, `payment_status`, `order_status`
- **โครงสร้างคอลัมน์:**
  - `id` (bigint, PK): รหัสออเดอร์
  - `user_id` (foreignId -> `users.id`, Restrict): ลูกค้าผู้สั่ง
  - `seat_id` (foreignId -> `seats.id`, Restrict): หมายเลขเครื่องที่ต้องการให้นำอาหารไปส่ง
  - `session_id` (foreignId -> `seat_sessions.id`, nullable, Restrict): เซสชันการใช้งานเครื่องขณะกดสั่งอาหาร
  - `total_amount` (decimal 10,2, default: 0.00): ยอดรวมค่าอาหารทั้งหมดในออเดอร์นี้
  - `payment_method` (string 20): วิธีการชำระเงิน (`wallet` กระเป๋าเงิน, `promptpay` สแกนจ่าย, `cash` เงินสด)
  - `payment_status` (string 20, default: `pending`): สถานะการจ่ายเงิน (`pending`, `paid`, `cancelled`)
  - `order_status` (string 20, default: `pending`): สถานะคิวครัว (`pending` รอดำเนินการ, `preparing` กำลังทำอาหาร, `served` เสิร์ฟเรียบร้อย, `cancelled` ยกเลิก)
  - `created_at`, `updated_at` (timestamps)
- **ความสัมพันธ์ (Relationships):**
  - `belongsTo(User::class)`: ลูกค้าที่สั่ง
  - `belongsTo(Seat::class)`: ส่งที่เครื่องไหน
  - `belongsTo(SeatSession::class, 'session_id')`: ผูกกับรอบเปิดเครื่องใด
  - `hasMany(OrderItem::class)`: รายการอาหารแต่ละเมนูในออเดอร์นี้

---

#### 11. ตาราง `order_items`
เก็บรายการย่อยของสินค้าแต่ละรายการที่อยู่ในออเดอร์ (Line Items)
- **Model:** `App\Models\OrderItem`
- **Fillable Fields:** `order_id`, `product_id`, `quantity`, `unit_price`, `subtotal`
- **โครงสร้างคอลัมน์:**
  - `id` (bigint, PK): รหัสรายการย่อย
  - `order_id` (foreignId -> `orders.id`, Cascade): เชื่อมกับออเดอร์หลัก (ลบออเดอร์แล้วรายการย่อยลบตาม)
  - `product_id` (foreignId -> `products.id`, Restrict): สินค้าที่สั่งซื้อ
  - `quantity` (integer): จำนวนชิ้นที่สั่ง
  - `unit_price` (decimal 10,2): ราคาต่อหน่วย ณ เวลาที่สั่งซื้อ (บันทึกราคาไว้เพื่อไม่ให้กระทบเมื่อร้านปรับราคาสินค้า)
  - `subtotal` (decimal 10,2): ราคารวมของรายการนี้ (`quantity` x `unit_price`)
  - `created_at`, `updated_at` (timestamps)
- **ความสัมพันธ์ (Relationships):**
  - `belongsTo(Order::class)`: อยู่ในออเดอร์ใด
  - `belongsTo(Product::class)`: เป็นสินค้าชนิดใด

---

## 3. ข้อกำหนดทางเทคนิคและหลักการออกแบบที่ควรรู้ (Technical Architecture)

### 1. นโยบาย Foreign Key Constraints
- **`restrictOnDelete()` (ความสัมพันธ์ส่วนใหญ่):** ใช้เพื่อป้องกันการลบข้อมูลต้นทางที่มีข้อมูลการเงินหรือประวัติการใช้งานผูกอยู่ (Data Integrity) เช่น ไม่อนุญาตให้ลบ `User` หากยังมีประวัติใน `wallet_transactions`, `orders` หรือ `seat_sessions`
- **`cascadeOnDelete()` (เฉพาะ `orders` -> `order_items`):** ใช้เนื่องจาก `order_items` เป็นส่วนประกอบย่อยของ `orders` ถ้าลบหัวบิล รายการในบิลต้องถูกลบตามทันที

### 2. เทคนิค Snapshot Pattern (การบันทึกภาพราคา)
- **`rate_snapshot` ใน `seat_sessions`:** บันทึกราคาต่อชั่วโมง ณ วินาทีที่เปิดเครื่อง ป้องกันกรณีร้านปรับราคาโซนในภายหลังแล้วกระทบเซสชันเดิมที่เปิดอยู่
- **`unit_price` ใน `order_items`:** บันทึกราคาสินค้าต่อหน่วย ณ เวลาที่สั่งซื้อ ป้องกันไม่ให้ยอดบิลในอดีตเปลี่ยนแปลงเมื่อร้านปรับราคาสินค้าในอนาคต

### 3. โครงสร้าง Rich Pivot Table (`user_packages`)
- ตาราง `user_packages` ไม่ใช่แค่ pivot table จับคู่ไอดี แต่เป็นตารางที่มี `id` ของตัวเอง และเก็บสถานะ `remaining_minutes`, `purchased_at`, `expired_at` ทำให้ลูกค้ารายเดียวสามารถซื้อแพ็กเกจประเภทเดียวกันซ้ำได้หลายครั้ง

### 4. คุณสมบัติ ACID และ Database Transactions
- การทำรายการที่ส่งผลต่อหลายตาราง เช่น การสั่งอาหาร จะครอบด้วย `DB::transaction()`:
  1. ตรวจสอบยอดเงิน และหัก `users.balance`
  2. บันทึกประวัติลง `wallet_transactions`
  3. ตรวจสอบและตัดสต็อกสินค้า `products.stock_quantity`
  4. สร้าง `orders` และ `order_items`
  หากมีขั้นตอนใดล้มเหลว ระบบจะทำการ Rollback ข้อมูลทั้งหมดกลับสู่สถานะเดิมทันที

### 5. ตัวเลขและเงื่อนไขทางธุรกิจ (Business Rules)
- **กระเป๋าเงิน (Wallet):** เติมเงินขั้นต่ำ 20.00 บาท และสูงสุดไม่เกิน 10,000.00 บาทต่อครั้ง
- **เปิดเครื่องแบบจ่ายตามจริง (Pay As You Go):** ยอดเงินในกระเป๋าต้องมากกว่า 0.00 บาท (เล่นได้ตามเวลาที่ยอดเงินครอบคลุม)
- **อัตราค่าบริการแยกตามโซน:**
  - Standard Zone (จอ 165Hz): 40.00 บาท / ชั่วโมง
  - VIP High-End Zone (จอ 240Hz): 60.00 บาท / ชั่วโมง
  - Duo Private Room (ห้องคู่): 100.00 บาท / ชั่วโมง
- **บัญชีทดสอบใน Seeder:**
  - ผู้จัดการร้าน (Admin): `admin` (รหัสผ่าน: `password`) ยอดเงินเริ่มต้น 1,000 บาท
  - พนักงานร้าน (Staff): `staff` (รหัสผ่าน: `password`) ยอดเงินเริ่มต้น 500 บาท
  - ลูกค้าประจำ (Somchai): `customer1` (รหัสผ่าน: `1`)
  - ลูกค้าใหม่ (Somsri): `customer2` (รหัสผ่าน: `password`)

### 6. ตารางระบบภายในของ Laravel (System Tables)
- `migrations`: บันทึกประวัติการรันไฟล์ Migration ในระบบ
- `sessions`: บันทึกสถานะการล็อกอินของผู้ใช้ในเว็บ (เมื่อใช้ database session driver)
- `password_reset_tokens`: บันทึกโทเค็นชั่วคราวสำหรับรีเซ็ตรหัสผ่าน
- `cache` / `cache_locks`: เก็บข้อมูลแคชประสิทธิภาพสูง
- `jobs` / `failed_jobs`: เก็บประวัติคิวงานประมวลผลเบื้องหลัง
