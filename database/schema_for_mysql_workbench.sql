-- ==============================================================================
-- LETSPLAY Gaming Cafe Management System
-- Database Schema for MySQL Workbench Reverse Engineering
-- ==============================================================================
-- Description:
-- ไฟล์นี้ถอดแบบมาจาก Laravel Migration ของโปรเจกต์ 100%
-- สำหรับ Import เข้า MySQL Workbench:
-- ไปที่เมนู: File -> Import -> Reverse Engineer MySQL Create Script...
-- ติ๊กถูกที่: "Place imported objects on a new diagram"
-- MySQL Workbench จะวาด ER Diagram (EER Model) พร้อมเส้นเชื่อม Foreign Key ให้อัตโนมัติทันที
-- ==============================================================================

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

CREATE SCHEMA IF NOT EXISTS `gaming_cafe_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `gaming_cafe_db`;

-- ------------------------------------------------------------------------------
-- Table 1: users (บัญชีผู้ใช้งาน ลูกค้า พนักงาน แอดมิน)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL COMMENT 'ชื่อ-นามสกุล หรือชื่อเรียก',
  `username` VARCHAR(50) NOT NULL COMMENT 'ชื่อบัญชีสำหรับล็อกอิน',
  `email` VARCHAR(255) NULL COMMENT 'อีเมลสำหรับติดต่อ/รีเซ็ตรหัสผ่าน',
  `phone` VARCHAR(20) NULL COMMENT 'เบอร์โทรศัพท์',
  `role` VARCHAR(20) NOT NULL DEFAULT 'customer' COMMENT 'สิทธิ์การใช้งาน (customer, staff, admin)',
  `balance` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'ยอดเงินคงเหลือในกระเป๋าเงิน (Wallet)',
  `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL COMMENT 'รหัสผ่านที่ผ่านการแฮช',
  `remember_token` VARCHAR(100) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `users_username_unique` (`username` ASC) VISIBLE,
  UNIQUE INDEX `users_email_unique` (`email` ASC) VISIBLE
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'ข้อมูลผู้ใช้งานระบบ';

-- ------------------------------------------------------------------------------
-- Table 2: zones (โซนเครื่องคอมพิวเตอร์ในร้าน)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `zones`;
CREATE TABLE IF NOT EXISTS `zones` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL COMMENT 'ชื่อโซน เช่น Standard, VIP, Duo Room',
  `hourly_rate` DECIMAL(10,2) NOT NULL COMMENT 'อัตราค่าบริการต่อชั่วโมง (บาท)',
  `description` TEXT NULL COMMENT 'รายละเอียดสเปกคอมพิวเตอร์และอุปกรณ์',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'โซนและอัตราค่าบริการ';

-- ------------------------------------------------------------------------------
-- Table 3: seats (เครื่องคอมพิวเตอร์/ที่นั่ง)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `seats`;
CREATE TABLE IF NOT EXISTS `seats` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `zone_id` BIGINT UNSIGNED NOT NULL COMMENT 'โซนที่ตั้งของเครื่อง',
  `seat_number` VARCHAR(20) NOT NULL COMMENT 'หมายเลขเครื่อง เช่น STD-01, VIP-02',
  `status` VARCHAR(20) NOT NULL DEFAULT 'available' COMMENT 'สถานะเครื่อง (available, occupied, maintenance)',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `seats_seat_number_unique` (`seat_number` ASC) VISIBLE,
  INDEX `fk_seats_zone_idx` (`zone_id` ASC) VISIBLE,
  CONSTRAINT `fk_seats_zone`
    FOREIGN KEY (`zone_id`)
    REFERENCES `zones` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'ข้อมูลเครื่องคอมพิวเตอร์';

-- ------------------------------------------------------------------------------
-- Table 4: packages (แพ็กเกจเวลาที่ร้านเปิดขาย)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `packages`;
CREATE TABLE IF NOT EXISTS `packages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `zone_id` BIGINT UNSIGNED NULL COMMENT 'โซนที่แพ็กเกจนี้ใช้ได้ (NULL คือใช้ได้ทุกโซน)',
  `name` VARCHAR(100) NOT NULL COMMENT 'ชื่อแพ็กเกจ',
  `duration_hours` INT NOT NULL COMMENT 'จำนวนชั่วโมงที่ได้รับ',
  `price` DECIMAL(10,2) NOT NULL COMMENT 'ราคาขายของแพ็กเกจ',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_pkg_zone_idx` (`zone_id` ASC) VISIBLE,
  CONSTRAINT `fk_pkg_zone`
    FOREIGN KEY (`zone_id`)
    REFERENCES `zones` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'แพ็กเกจชั่วโมงสำหรับเล่นเกม';

-- ------------------------------------------------------------------------------
-- Table 5: user_packages (แพ็กเกจเวลาที่ลูกค้าซื้อสะสมไว้ - Rich Pivot)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `user_packages`;
CREATE TABLE IF NOT EXISTS `user_packages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'ลูกค้าเจ้าของแพ็กเกจ',
  `package_id` BIGINT UNSIGNED NOT NULL COMMENT 'แพ็กเกจที่ซื้อ',
  `remaining_minutes` INT NOT NULL COMMENT 'จำนวนเวลาคงเหลือที่ยังเล่นได้ (นาที)',
  `purchased_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'เวลาที่ซื้อ',
  `expired_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'วันและเวลาหมดอายุของแพ็กเกจ',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_upkg_user_idx` (`user_id` ASC) VISIBLE,
  INDEX `fk_upkg_pkg_idx` (`package_id` ASC) VISIBLE,
  CONSTRAINT `fk_upkg_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_upkg_pkg`
    FOREIGN KEY (`package_id`)
    REFERENCES `packages` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'แพ็กเกจชั่วโมงของลูกค้า';

-- ------------------------------------------------------------------------------
-- Table 6: seat_sessions (ประวัติและรอบการเข้าใช้งานเครื่อง)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `seat_sessions`;
CREATE TABLE IF NOT EXISTS `seat_sessions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'ลูกค้าผู้เปิดเครื่อง',
  `seat_id` BIGINT UNSIGNED NOT NULL COMMENT 'เครื่องที่เปิดใช้งาน',
  `user_package_id` BIGINT UNSIGNED NULL COMMENT 'แพ็กเกจที่ใช้หักเวลา (NULL คือโหมด Pay-as-you-go)',
  `rate_snapshot` DECIMAL(10,2) NOT NULL COMMENT 'ราคาต่อชั่วโมง ณ วินาทีเปิดเครื่อง (Price Snapshot)',
  `start_time` TIMESTAMP NOT NULL COMMENT 'เวลาเริ่มเปิดเครื่อง',
  `end_time` TIMESTAMP NULL DEFAULT NULL COMMENT 'เวลาเช็คเอาท์ปิดเครื่อง',
  `total_cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'ค่าบริการทั้งหมดที่หักจริงเมื่อปิดเครื่อง',
  `status` VARCHAR(20) NOT NULL DEFAULT 'active' COMMENT 'สถานะเซสชัน (active, paused, completed)',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_ss_user_idx` (`user_id` ASC) VISIBLE,
  INDEX `fk_ss_seat_idx` (`seat_id` ASC) VISIBLE,
  INDEX `fk_ss_upkg_idx` (`user_package_id` ASC) VISIBLE,
  CONSTRAINT `fk_ss_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_ss_seat`
    FOREIGN KEY (`seat_id`)
    REFERENCES `seats` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_ss_upkg`
    FOREIGN KEY (`user_package_id`)
    REFERENCES `user_packages` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'ประวัติและรอบการเปิดใช้งานเครื่องคอมพิวเตอร์';

-- ------------------------------------------------------------------------------
-- Table 7: wallet_transactions (Audit Log บันทึกประวัติการเงิน)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `wallet_transactions`;
CREATE TABLE IF NOT EXISTS `wallet_transactions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'เจ้าของบัญชีกระเป๋าเงิน',
  `type` VARCHAR(20) NOT NULL COMMENT 'ประเภทธุรกรรม (topup, deduct, refund)',
  `amount` DECIMAL(10,2) NOT NULL COMMENT 'จำนวนเงิน',
  `ref_type` VARCHAR(50) NULL COMMENT 'ประเภทรายการอ้างอิง เช่น session, food_order, package_purchase',
  `ref_id` BIGINT UNSIGNED NULL COMMENT 'ไอดีอ้างอิงของตารางต้นทาง',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_wt_user_idx` (`user_id` ASC) VISIBLE,
  CONSTRAINT `fk_wt_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'บันทึกประวัติธุรกรรมกระเป๋าเงิน (Audit Trail)';

-- ------------------------------------------------------------------------------
-- Table 8: categories (หมวดหมู่อาหารและเครื่องดื่ม)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL COMMENT 'ชื่อหมวดหมู่ เช่น อาหารจานเดียว, เครื่องดื่ม, ของทานเล่น',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'หมวดหมู่สินค้าในคาเฟ่';

-- ------------------------------------------------------------------------------
-- Table 9: products (รายการสินค้าและอาหาร)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` BIGINT UNSIGNED NOT NULL COMMENT 'หมวดหมู่สินค้า',
  `name` VARCHAR(150) NOT NULL COMMENT 'ชื่อสินค้า',
  `description` TEXT NULL COMMENT 'รายละเอียดสินค้า',
  `price` DECIMAL(10,2) NOT NULL COMMENT 'ราคาขายต่อหน่วย',
  `stock_quantity` INT NOT NULL DEFAULT 0 COMMENT 'จำนวนสินค้าคงเหลือในสต็อก',
  `image` VARCHAR(255) NULL COMMENT 'ที่อยู่ไฟล์รูปภาพ',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_prod_cat_idx` (`category_id` ASC) VISIBLE,
  CONSTRAINT `fk_prod_cat`
    FOREIGN KEY (`category_id`)
    REFERENCES `categories` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'รายการอาหาร เครื่องดื่ม และสต็อกสินค้า';

-- ------------------------------------------------------------------------------
-- Table 10: orders (คำสั่งซื้ออาหารและเครื่องดื่มส่งถึงโต๊ะ)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'ลูกค้าผู้สั่ง',
  `seat_id` BIGINT UNSIGNED NOT NULL COMMENT 'เครื่องที่ระบุให้นำอาหารไปส่ง',
  `session_id` BIGINT UNSIGNED NULL COMMENT 'รอบการใช้งานเครื่องขณะกดสั่งอาหาร',
  `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'ยอดรวมค่าอาหารทั้งหมด',
  `payment_method` VARCHAR(20) NOT NULL COMMENT 'วิธีชำระเงิน (wallet, promptpay, cash)',
  `payment_status` VARCHAR(20) NOT NULL DEFAULT 'pending' COMMENT 'สถานะการชำระเงิน (pending, pending_payment, paid, cancelled)',
  `order_status` VARCHAR(20) NOT NULL DEFAULT 'pending' COMMENT 'สถานะการทำอาหารในครัว (pending, preparing, served, cancelled)',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_ord_user_idx` (`user_id` ASC) VISIBLE,
  INDEX `fk_ord_seat_idx` (`seat_id` ASC) VISIBLE,
  INDEX `fk_ord_ss_idx` (`session_id` ASC) VISIBLE,
  CONSTRAINT `fk_ord_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_ord_seat`
    FOREIGN KEY (`seat_id`)
    REFERENCES `seats` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_ord_ss`
    FOREIGN KEY (`session_id`)
    REFERENCES `seat_sessions` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'หัวบิลคำสั่งซื้ออาหารและเครื่องดื่ม';

-- ------------------------------------------------------------------------------
-- Table 11: order_items (รายการสินค้าแต่ละชิ้นในบิล - Line Items)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` BIGINT UNSIGNED NOT NULL COMMENT 'เชื่อมกับหัวบิลคำสั่งซื้อ',
  `product_id` BIGINT UNSIGNED NOT NULL COMMENT 'สินค้าที่สั่งซื้อ',
  `quantity` INT NOT NULL COMMENT 'จำนวนชิ้นที่สั่ง',
  `unit_price` DECIMAL(10,2) NOT NULL COMMENT 'ราคาต่อหน่วย ณ เวลาที่สั่งซื้อ (Price Snapshot)',
  `subtotal` DECIMAL(10,2) NOT NULL COMMENT 'ยอดรวมของรายการนี้ (quantity * unit_price)',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_oi_ord_idx` (`order_id` ASC) VISIBLE,
  INDEX `fk_oi_prod_idx` (`product_id` ASC) VISIBLE,
  CONSTRAINT `fk_oi_ord`
    FOREIGN KEY (`order_id`)
    REFERENCES `orders` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_oi_prod`
    FOREIGN KEY (`product_id`)
    REFERENCES `products` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'รายการย่อยสินค้าในแต่ละคำสั่งซื้อ';

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
