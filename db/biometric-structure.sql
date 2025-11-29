/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 100432
 Source Host           : localhost:3306
 Source Schema         : biometric

 Target Server Type    : MySQL
 Target Server Version : 100432
 File Encoding         : 65001

 Date: 18/10/2025 03:51:15
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for admin
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin`  (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `role` enum('super_admin','admin','student_leader','sas_director','sas_adviser','chairperson') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'admin',
  `department` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `date_created` datetime(0) NULL DEFAULT current_timestamp(0),
  PRIMARY KEY (`admin_id`) USING BTREE,
  UNIQUE INDEX `username`(`username`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 58 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for admin_attendance
-- ----------------------------
DROP TABLE IF EXISTS `admin_attendance`;
CREATE TABLE `admin_attendance`  (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `check_in_time` datetime(0) NOT NULL,
  `check_out_time` datetime(0) NULL DEFAULT NULL,
  `status` enum('present','late','absent','excused') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'present',
  `minutes_late` smallint(6) NULL DEFAULT 0,
  `scanner_id` int(11) NULL DEFAULT NULL COMMENT 'Which scanner was used',
  PRIMARY KEY (`record_id`) USING BTREE,
  INDEX `student_id`(`student_id`) USING BTREE,
  INDEX `event_id`(`event_id`) USING BTREE,
  INDEX `fk_attendance_scanner`(`scanner_id`) USING BTREE,
  CONSTRAINT `admin_attendance_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `admin_event` (`event_id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `fk_attendance_scanner` FOREIGN KEY (`scanner_id`) REFERENCES `scanners` (`scanner_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for admin_courses
-- ----------------------------
DROP TABLE IF EXISTS `admin_courses`;
CREATE TABLE `admin_courses`  (
  `course_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `course_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `department` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `duration_years` tinyint(4) NULL DEFAULT 4,
  PRIMARY KEY (`course_id`) USING BTREE,
  UNIQUE INDEX `course_code`(`course_code`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 81 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for admin_event
-- ----------------------------
DROP TABLE IF EXISTS `admin_event`;
CREATE TABLE `admin_event`  (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `date` date NOT NULL,
  `start_time` time(0) NOT NULL,
  `end_time` time(0) NOT NULL,
  `location` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `is_mandatory` tinyint(1) NULL DEFAULT 1,
  `created_by` int(11) NULL DEFAULT NULL,
  `date_created` datetime(0) NULL DEFAULT current_timestamp(0),
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `fine_amount` decimal(10, 2) NULL DEFAULT NULL,
  `fine_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `year_level` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`event_id`) USING BTREE,
  INDEX `created_by`(`created_by`) USING BTREE,
  CONSTRAINT `admin_event_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 185 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for admin_fines
-- ----------------------------
DROP TABLE IF EXISTS `admin_fines`;
CREATE TABLE `admin_fines`  (
  `fine_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `event_id` int(11) NULL DEFAULT NULL,
  `attendance_id` int(11) NULL DEFAULT NULL,
  `fine_type` enum('late','absent','damage','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `amount` decimal(10, 2) NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `date_issued` date NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('unpaid','paid','waived') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'unpaid',
  `paid_date` date NULL DEFAULT NULL,
  `receipt_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `issued_by` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`fine_id`) USING BTREE,
  INDEX `event_id`(`event_id`) USING BTREE,
  INDEX `attendance_id`(`attendance_id`) USING BTREE,
  INDEX `issued_by`(`issued_by`) USING BTREE,
  CONSTRAINT `admin_fines_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `admin_event` (`event_id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `admin_fines_ibfk_3` FOREIGN KEY (`attendance_id`) REFERENCES `admin_attendance` (`record_id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `admin_fines_ibfk_4` FOREIGN KEY (`issued_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 18001 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for logs
-- ----------------------------
DROP TABLE IF EXISTS `logs`;
CREATE TABLE `logs`  (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NULL DEFAULT NULL,
  `student_id` int(11) NULL DEFAULT NULL,
  `action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `timestamp` datetime(0) NULL DEFAULT current_timestamp(0),
  PRIMARY KEY (`log_id`) USING BTREE,
  INDEX `admin_id`(`admin_id`) USING BTREE,
  CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for register_student
-- ----------------------------
DROP TABLE IF EXISTS `register_student`;
CREATE TABLE `register_student`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `student_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `department` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `course` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `year_level` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fingerprint_data` longblob NULL,
  `registration_date` timestamp(0) NOT NULL DEFAULT current_timestamp(0),
  `last_updated` timestamp(0) NOT NULL DEFAULT current_timestamp(0) ON UPDATE CURRENT_TIMESTAMP(0),
  `template_size` int(11) NULL DEFAULT 0,
  `created_at` timestamp(0) NOT NULL DEFAULT current_timestamp(0),
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0000',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uid`(`uid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 19359 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for scanners
-- ----------------------------
DROP TABLE IF EXISTS `scanners`;
CREATE TABLE `scanners`  (
  `scanner_id` int(11) NOT NULL AUTO_INCREMENT,
  `scanner_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `location` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `mac_address` varchar(17) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `last_active` datetime(0) NULL DEFAULT NULL,
  `is_online` tinyint(1) NULL DEFAULT 0,
  `installed_by` int(11) NULL DEFAULT NULL,
  `installation_date` datetime(0) NULL DEFAULT current_timestamp(0),
  PRIMARY KEY (`scanner_id`) USING BTREE,
  UNIQUE INDEX `mac_address`(`mac_address`) USING BTREE,
  INDEX `fk_scanners_admin`(`installed_by`) USING BTREE,
  CONSTRAINT `fk_scanners_admin` FOREIGN KEY (`installed_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for settings
-- ----------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings`  (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `setting_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `is_public` tinyint(1) NULL DEFAULT 0,
  `course_id` int(11) NULL DEFAULT NULL,
  `last_updated_by` int(11) NULL DEFAULT NULL,
  `last_updated` datetime(0) NULL DEFAULT current_timestamp(0) ON UPDATE CURRENT_TIMESTAMP(0),
  `event_id` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`setting_id`) USING BTREE,
  UNIQUE INDEX `setting_name`(`setting_name`) USING BTREE,
  INDEX `fk_settings_admin`(`last_updated_by`) USING BTREE,
  INDEX `fk_settings_courses`(`course_id`) USING BTREE,
  INDEX `fk_settings_events`(`event_id`) USING BTREE,
  CONSTRAINT `fk_settings_admin` FOREIGN KEY (`last_updated_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `fk_settings_courses` FOREIGN KEY (`course_id`) REFERENCES `admin_courses` (`course_id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `fk_settings_events` FOREIGN KEY (`event_id`) REFERENCES `admin_event` (`event_id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for students_events
-- ----------------------------
DROP TABLE IF EXISTS `students_events`;
CREATE TABLE `students_events`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `attendance_status` enum('present','absent','late','excused') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'absent',
  `time_in` datetime(0) NULL DEFAULT NULL,
  `time_out` datetime(0) NULL DEFAULT NULL,
  `points_earned` int(11) NULL DEFAULT 0,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `recorded_by` int(11) NULL DEFAULT NULL,
  `date_recorded` datetime(0) NULL DEFAULT current_timestamp(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `student_event`(`student_id`, `event_id`) USING BTREE,
  INDEX `event_id`(`event_id`) USING BTREE,
  INDEX `recorded_by`(`recorded_by`) USING BTREE,
  CONSTRAINT `students_events_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `admin_event` (`event_id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `students_events_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 37030 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Triggers structure for table admin_event
-- ----------------------------
DROP TRIGGER IF EXISTS `after_admin_event_insert`;
delimiter ;;
CREATE TRIGGER `after_admin_event_insert` AFTER INSERT ON `admin_event` FOR EACH ROW BEGIN
    -- Insert a record for each active student into students_events
    INSERT INTO students_events (student_id, event_id, attendance_status, recorded_by, date_recorded)
    SELECT s.id, NEW.event_id, 'absent', NEW.created_by, NOW()
    FROM register_student s;
END
;;
delimiter ;

SET FOREIGN_KEY_CHECKS = 1;
