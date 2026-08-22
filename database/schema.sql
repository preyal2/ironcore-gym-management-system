-- ==========================================================
-- IRONCORE GYM MANAGEMENT SYSTEM - DATABASE SCHEMA
-- Compatible with MySQL 5.7+ / 8.0+ and MariaDB
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `ironcore_gym` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ironcore_gym`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. USERS TABLE
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'trainer', 'member') NOT NULL DEFAULT 'member',
    `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_role` (`role`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TRAINERS TABLE
DROP TABLE IF EXISTS `trainers`;
CREATE TABLE `trainers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `specialization` VARCHAR(150) NOT NULL,
    `experience` VARCHAR(50) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `bio` TEXT,
    `profile_image` VARCHAR(255) DEFAULT 'assets/images/trainers/default.jpg',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. MEMBERS TABLE
DROP TABLE IF EXISTS `members`;
CREATE TABLE `members` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `member_code` VARCHAR(20) NOT NULL UNIQUE,
    `phone` VARCHAR(20) NOT NULL,
    `gender` ENUM('Male', 'Female', 'Other') DEFAULT 'Male',
    `date_of_birth` DATE,
    `address` TEXT,
    `height` DECIMAL(5,2) DEFAULT NULL COMMENT 'in cm',
    `weight` DECIMAL(5,2) DEFAULT NULL COMMENT 'in kg',
    `fitness_goal` VARCHAR(100) DEFAULT 'General Fitness',
    `fitness_level` ENUM('Beginner', 'Intermediate', 'Advanced', 'Athlete') DEFAULT 'Beginner',
    `trainer_id` INT DEFAULT NULL,
    `profile_image` VARCHAR(255) DEFAULT 'assets/images/trainers/default.jpg',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`trainer_id`) REFERENCES `trainers`(`id`) ON DELETE SET NULL,
    INDEX `idx_member_code` (`member_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. MEMBERSHIP PLANS TABLE
DROP TABLE IF EXISTS `membership_plans`;
CREATE TABLE `membership_plans` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `duration` VARCHAR(50) NOT NULL COMMENT 'e.g., 1 Month, 3 Months, 6 Months, 1 Year',
    `duration_days` INT NOT NULL DEFAULT 30,
    `price` DECIMAL(10,2) NOT NULL,
    `description` TEXT,
    `features` TEXT COMMENT 'JSON or comma-separated benefits',
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. MEMBERSHIPS TABLE
DROP TABLE IF EXISTS `memberships`;
CREATE TABLE `memberships` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `member_id` INT NOT NULL,
    `plan_id` INT NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `status` ENUM('active', 'expiring_soon', 'expired', 'cancelled') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`plan_id`) REFERENCES `membership_plans`(`id`) ON DELETE CASCADE,
    INDEX `idx_member_status` (`member_id`, `status`),
    INDEX `idx_end_date` (`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. PAYMENTS TABLE
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `receipt_number` VARCHAR(50) NOT NULL UNIQUE,
    `member_id` INT NOT NULL,
    `membership_id` INT DEFAULT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `payment_method` ENUM('Cash', 'UPI', 'Card', 'Bank Transfer') NOT NULL DEFAULT 'UPI',
    `payment_status` ENUM('Completed', 'Pending', 'Failed', 'Refunded') NOT NULL DEFAULT 'Completed',
    `transaction_reference` VARCHAR(100) DEFAULT NULL,
    `notes` TEXT,
    `payment_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`membership_id`) REFERENCES `memberships`(`id`) ON DELETE SET NULL,
    INDEX `idx_payment_date` (`payment_date`),
    INDEX `idx_receipt_number` (`receipt_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. ATTENDANCE TABLE
DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `member_id` INT NOT NULL,
    `attendance_date` DATE NOT NULL,
    `check_in_time` TIME NOT NULL,
    `check_out_time` TIME DEFAULT NULL,
    `status` ENUM('Present', 'Absent', 'Late') NOT NULL DEFAULT 'Present',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_member_date` (`member_id`, `attendance_date`),
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE,
    INDEX `idx_attendance_date` (`attendance_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. EXERCISES TABLE
DROP TABLE IF EXISTS `exercises`;
CREATE TABLE `exercises` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `category` ENUM('Chest', 'Back', 'Shoulders', 'Biceps', 'Triceps', 'Legs', 'Core', 'Cardio', 'Full Body') NOT NULL,
    `muscle_group` VARCHAR(100) NOT NULL,
    `difficulty` ENUM('Beginner', 'Intermediate', 'Advanced') NOT NULL DEFAULT 'Beginner',
    `sets` INT NOT NULL DEFAULT 3,
    `reps` VARCHAR(50) NOT NULL DEFAULT '10-12',
    `rest_time` VARCHAR(50) NOT NULL DEFAULT '60 sec',
    `instructions` TEXT,
    `image` VARCHAR(255) DEFAULT 'assets/images/exercises/default.jpg',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. WORKOUT PLANS TABLE
DROP TABLE IF EXISTS `workout_plans`;
CREATE TABLE `workout_plans` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `trainer_id` INT DEFAULT NULL,
    `name` VARCHAR(100) NOT NULL,
    `goal` VARCHAR(100) NOT NULL,
    `fitness_level` ENUM('Beginner', 'Intermediate', 'Advanced') NOT NULL DEFAULT 'Beginner',
    `duration` VARCHAR(50) NOT NULL DEFAULT '4 Weeks',
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`trainer_id`) REFERENCES `trainers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. WORKOUT EXERCISES TABLE
DROP TABLE IF EXISTS `workout_exercises`;
CREATE TABLE `workout_exercises` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `workout_plan_id` INT NOT NULL,
    `exercise_id` INT NOT NULL,
    `day_name` ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    `sets` INT NOT NULL DEFAULT 3,
    `reps` VARCHAR(50) NOT NULL DEFAULT '10-12',
    `rest_time` VARCHAR(50) NOT NULL DEFAULT '60 sec',
    `order_number` INT NOT NULL DEFAULT 1,
    FOREIGN KEY (`workout_plan_id`) REFERENCES `workout_plans`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`exercise_id`) REFERENCES `exercises`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. WORKOUT PROGRESS TABLE
DROP TABLE IF EXISTS `workout_progress`;
CREATE TABLE `workout_progress` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `member_id` INT NOT NULL,
    `workout_plan_id` INT NOT NULL,
    `workout_exercise_id` INT DEFAULT NULL,
    `completion_date` DATE NOT NULL,
    `status` ENUM('Completed', 'Skipped', 'Partial') NOT NULL DEFAULT 'Completed',
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`workout_plan_id`) REFERENCES `workout_plans`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`workout_exercise_id`) REFERENCES `workout_exercises`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. DIET PLANS TABLE
DROP TABLE IF EXISTS `diet_plans`;
CREATE TABLE `diet_plans` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `trainer_id` INT DEFAULT NULL,
    `name` VARCHAR(100) NOT NULL,
    `goal` VARCHAR(100) NOT NULL,
    `target_calories` INT DEFAULT 2000,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`trainer_id`) REFERENCES `trainers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. DIET MEALS TABLE
DROP TABLE IF EXISTS `diet_meals`;
CREATE TABLE `diet_meals` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `diet_plan_id` INT NOT NULL,
    `meal_type` ENUM('Breakfast', 'Lunch', 'Snack', 'Dinner') NOT NULL,
    `food_items` TEXT NOT NULL,
    `calories` INT DEFAULT 400,
    `protein_g` INT DEFAULT 25,
    `carbs_g` INT DEFAULT 50,
    `fats_g` INT DEFAULT 15,
    `notes` TEXT,
    FOREIGN KEY (`diet_plan_id`) REFERENCES `diet_plans`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. MEMBER DIETS TABLE
DROP TABLE IF EXISTS `member_diets`;
CREATE TABLE `member_diets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `member_id` INT NOT NULL,
    `diet_plan_id` INT NOT NULL,
    `assigned_date` DATE NOT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`diet_plan_id`) REFERENCES `diet_plans`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. PROGRESS TABLE
DROP TABLE IF EXISTS `progress`;
CREATE TABLE `progress` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `member_id` INT NOT NULL,
    `weight` DECIMAL(5,2) NOT NULL COMMENT 'in kg',
    `waist` DECIMAL(5,2) DEFAULT NULL COMMENT 'in inches',
    `chest` DECIMAL(5,2) DEFAULT NULL COMMENT 'in inches',
    `arms` DECIMAL(5,2) DEFAULT NULL COMMENT 'in inches',
    `legs` DECIMAL(5,2) DEFAULT NULL COMMENT 'in inches',
    `notes` TEXT,
    `record_date` DATE NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE,
    INDEX `idx_progress_member_date` (`member_id`, `record_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. APPOINTMENTS TABLE
DROP TABLE IF EXISTS `appointments`;
CREATE TABLE `appointments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `member_id` INT NOT NULL,
    `trainer_id` INT NOT NULL,
    `appointment_date` DATE NOT NULL,
    `appointment_time` TIME NOT NULL,
    `purpose` VARCHAR(150) NOT NULL,
    `notes` TEXT,
    `status` ENUM('Pending', 'Confirmed', 'Rejected', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`trainer_id`) REFERENCES `trainers`(`id`) ON DELETE CASCADE,
    INDEX `idx_app_date` (`appointment_date`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. NOTIFICATIONS TABLE
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `message` TEXT NOT NULL,
    `type` VARCHAR(50) DEFAULT 'general',
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_notif_user` (`user_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. ANNOUNCEMENTS TABLE
DROP TABLE IF EXISTS `announcements`;
CREATE TABLE `announcements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `content` TEXT NOT NULL,
    `target_role` ENUM('all', 'trainer', 'member') NOT NULL DEFAULT 'all',
    `priority` ENUM('normal', 'high', 'urgent') NOT NULL DEFAULT 'normal',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. FEEDBACK TABLE
DROP TABLE IF EXISTS `feedback`;
CREATE TABLE `feedback` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `member_id` INT NOT NULL,
    `rating` TINYINT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `message` TEXT NOT NULL,
    `category` VARCHAR(50) DEFAULT 'General',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
