-- School Management System - Academics Module Schema
-- version 1.0
--

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

-- ----------------------------
-- Table structure for subjects
-- ----------------------------
DROP TABLE IF EXISTS `subjects`;
CREATE TABLE `subjects` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `subject_name` VARCHAR(150) NOT NULL,
  `subject_code` VARCHAR(20) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subject_name` (`subject_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for class_subjects (Pivot Table)
-- ----------------------------
DROP TABLE IF EXISTS `class_subjects`;
CREATE TABLE `class_subjects` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `class_id` INT UNSIGNED NOT NULL,
  `subject_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `class_subject_unique` (`class_id`, `subject_id`),
  KEY `class_id` (`class_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `class_subjects_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for teacher_assignments
-- ----------------------------
DROP TABLE IF EXISTS `teacher_assignments`;
CREATE TABLE `teacher_assignments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL COMMENT 'FK to users table (must be a Staff role)',
  `class_subject_id` INT UNSIGNED NOT NULL COMMENT 'FK to the specific class-subject link',
  `session_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `teacher_class_subject_session_unique` (`user_id`, `class_subject_id`, `session_id`),
  KEY `user_id` (`user_id`),
  KEY `class_subject_id` (`class_subject_id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `teacher_assignments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_assignments_ibfk_2` FOREIGN KEY (`class_subject_id`) REFERENCES `class_subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_assignments_ibfk_3` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET foreign_key_checks = 1;
