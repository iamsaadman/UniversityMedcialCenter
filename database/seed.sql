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
