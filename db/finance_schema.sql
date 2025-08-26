-- School Management System - Finance Module Schema
-- version 1.0
--

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

-- ----------------------------
-- Table structure for fee_structures
-- ----------------------------
DROP TABLE IF EXISTS `fee_structures`;
CREATE TABLE `fee_structures` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fee_name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fee_name` (`fee_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for class_fees
-- ----------------------------
DROP TABLE IF EXISTS `class_fees`;
CREATE TABLE `class_fees` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_id` INT UNSIGNED NOT NULL,
  `class_id` INT UNSIGNED NOT NULL,
  `fee_structure_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(10, 2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_class_fee_structure` (`session_id`, `class_id`, `fee_structure_id`),
  CONSTRAINT `class_fees_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_fees_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_fees_ibfk_3` FOREIGN KEY (`fee_structure_id`) REFERENCES `fee_structures` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for student_fee_records
-- ----------------------------
DROP TABLE IF EXISTS `student_fee_records`;
CREATE TABLE `student_fee_records` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `class_fee_id` INT UNSIGNED NOT NULL,
  `total_amount` DECIMAL(10, 2) NOT NULL,
  `amount_paid` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `due_date` DATE DEFAULT NULL,
  `status` ENUM('Pending', 'Partially Paid', 'Paid', 'Overdue') NOT NULL DEFAULT 'Pending',
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `class_fee_id` (`class_fee_id`),
  CONSTRAINT `student_fee_records_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_fee_records_ibfk_2` FOREIGN KEY (`class_fee_id`) REFERENCES `class_fees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for payments
-- ----------------------------
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_fee_record_id` INT UNSIGNED NOT NULL,
  `amount_paid` DECIMAL(10, 2) NOT NULL,
  `payment_date` DATE NOT NULL,
  `payment_method` ENUM('Cash', 'Card', 'Bank Transfer', 'Online') NOT NULL DEFAULT 'Cash',
  `receipt_number` VARCHAR(100) NOT NULL,
  `notes` TEXT,
  `processed_by_user_id` INT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `receipt_number` (`receipt_number`),
  KEY `student_fee_record_id` (`student_fee_record_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`student_fee_record_id`) REFERENCES `student_fee_records` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`processed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for expenses
-- ----------------------------
DROP TABLE IF EXISTS `expenses`;
CREATE TABLE `expenses` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `expense_category` VARCHAR(255) NOT NULL,
  `amount` DECIMAL(10, 2) NOT NULL,
  `expense_date` DATE NOT NULL,
  `description` TEXT,
  `vendor` VARCHAR(255) DEFAULT NULL,
  `created_by_user_id` INT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  KEY `created_by_user_id` (`created_by_user_id`),
  CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET foreign_key_checks = 1;
