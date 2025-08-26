-- School Management System - Attendance Module Schema

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 1;

-- ----------------------------
-- Table structure for attendance
-- ----------------------------
DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `class_id` INT UNSIGNED NOT NULL,
  `session_id` INT UNSIGNED NOT NULL,
  `attendance_date` DATE NOT NULL,
  `status` ENUM('Present', 'Absent', 'Late', 'Excused') NOT NULL DEFAULT 'Present',
  `taken_by_user_id` INT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_class_date` (`student_id`, `class_id`, `attendance_date`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_ibfk_3` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_ibfk_4` FOREIGN KEY (`taken_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
