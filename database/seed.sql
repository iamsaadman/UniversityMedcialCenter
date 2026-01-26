-- University Medical Center - Auth Database Seed
-- Run this in MySQL (phpMyAdmin or CLI) to initialize the database

-- Create database
CREATE DATABASE IF NOT EXISTS `university_medical_center`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `university_medical_center`;

-- Users table to support signup and login
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fullname` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `institution_id` VARCHAR(50) NOT NULL,
  `role` ENUM('student','doctor','admin') NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_email` (`email`),
  UNIQUE KEY `uniq_institution_id` (`institution_id`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Appointments table
CREATE TABLE IF NOT EXISTS `appointments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `doctor_id` INT UNSIGNED NOT NULL,
  `appointment_date` DATE NOT NULL,
  `appointment_time` TIME NOT NULL,
  `reason_for_visit` VARCHAR(255) NOT NULL,
  `notes` TEXT,
  `status` ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  KEY `idx_student_date` (`student_id`, `appointment_date`),
  KEY `idx_doctor_date` (`doctor_id`, `appointment_date`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications table
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `message` VARCHAR(500) NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `reference_id` INT UNSIGNED,
  `is_read` BOOLEAN NOT NULL DEFAULT FALSE,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  KEY `idx_user_unread` (`user_id`, `is_read`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Medical test requests table
CREATE TABLE IF NOT EXISTS `test_requests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `doctor_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `appointment_id` INT UNSIGNED DEFAULT NULL,
  `test_type` VARCHAR(120) NOT NULL,
  `priority` ENUM('Normal','Urgent','Critical') NOT NULL DEFAULT 'Normal',
  `notes` TEXT,
  `status` ENUM('requested','in_progress','completed') NOT NULL DEFAULT 'requested',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  KEY `idx_student_status` (`student_id`, `status`),
  KEY `idx_doctor_created` (`doctor_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Prescriptions table
CREATE TABLE IF NOT EXISTS `prescriptions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `appointment_id` INT UNSIGNED DEFAULT NULL,
  `doctor_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(150) NOT NULL DEFAULT 'Prescription',
  `diagnosis` TEXT,
  `medications` TEXT NOT NULL,
  `instructions` TEXT,
  `follow_up_date` DATE DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  KEY `idx_student_created` (`student_id`, `created_at`),
  KEY `idx_doctor_created` (`doctor_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- INSERT INTO users (fullname, email, institution_id, role, password_hash) 
-- VALUES ('Admin', 'admin@gmail.com', 'admin1234', 'admin', '$2y$10$7VwOB4l2HrsOiXKqonT6SuZ5UE9.uc//aCRmbVZTu.QV4z5v7bxKC');