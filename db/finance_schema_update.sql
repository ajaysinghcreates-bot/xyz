-- School Management System - Finance Module Schema Update
-- This adds the student_enrollment table, which is critical for assigning fees.

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 1;

-- ----------------------------
-- Table structure for student_enrollment
-- ----------------------------
DROP TABLE IF EXISTS `student_enrollment`;
CREATE TABLE `student_enrollment` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `class_id` INT UNSIGNED NOT NULL,
  `session_id` INT UNSIGNED NOT NULL,
  `enrollment_date` DATE NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Is the student currently active in this class',
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_class_session_unique` (`student_id`, `class_id`, `session_id`),
  CONSTRAINT `student_enrollment_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_enrollment_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_enrollment_ibfk_3` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
