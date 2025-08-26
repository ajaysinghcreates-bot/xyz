-- School Management System - Exams Module Schema
-- version 1.0
--

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 1;

-- ----------------------------
-- Table structure for exam_types
-- ----------------------------
DROP TABLE IF EXISTS `exam_types`;
CREATE TABLE `exam_types` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for exams
-- ----------------------------
DROP TABLE IF EXISTS `exams`;
CREATE TABLE `exams` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_id` INT UNSIGNED NOT NULL,
  `exam_type_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL COMMENT 'E.g., Final Exams - Fall 2024',
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  KEY `exam_type_id` (`exam_type_id`),
  CONSTRAINT `exams_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exams_ibfk_2` FOREIGN KEY (`exam_type_id`) REFERENCES `exam_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for exam_schedule
-- ----------------------------
DROP TABLE IF EXISTS `exam_schedule`;
CREATE TABLE `exam_schedule` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `exam_id` INT UNSIGNED NOT NULL,
  `class_subject_id` INT UNSIGNED NOT NULL,
  `exam_date` DATE NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `max_marks` INT NOT NULL DEFAULT 100,
  `passing_marks` INT NOT NULL DEFAULT 33,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exam_class_subject` (`exam_id`, `class_subject_id`),
  CONSTRAINT `exam_schedule_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_schedule_ibfk_2` FOREIGN KEY (`class_subject_id`) REFERENCES `class_subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for student_marks
-- ----------------------------
DROP TABLE IF EXISTS `student_marks`;
CREATE TABLE `student_marks` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `exam_schedule_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `marks_obtained` INT,
  `grade` VARCHAR(10),
  `remarks` TEXT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exam_student` (`exam_schedule_id`, `student_id`),
  CONSTRAINT `student_marks_ibfk_1` FOREIGN KEY (`exam_schedule_id`) REFERENCES `exam_schedule` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_marks_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
