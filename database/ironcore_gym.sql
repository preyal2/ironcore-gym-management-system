-- ==========================================================
-- IRONCORE GYM MANAGEMENT SYSTEM - COMBINED SQL DUMP
-- Import this file into MySQL / phpMyAdmin to create database and demo data.
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
    `height` DECIMAL(5,2) DEFAULT NULL,
    `weight` DECIMAL(5,2) DEFAULT NULL,
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
    `duration` VARCHAR(50) NOT NULL,
    `duration_days` INT NOT NULL DEFAULT 30,
    `price` DECIMAL(10,2) NOT NULL,
    `description` TEXT,
    `features` TEXT,
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
    `weight` DECIMAL(5,2) NOT NULL,
    `waist` DECIMAL(5,2) DEFAULT NULL,
    `chest` DECIMAL(5,2) DEFAULT NULL,
    `arms` DECIMAL(5,2) DEFAULT NULL,
    `legs` DECIMAL(5,2) DEFAULT NULL,
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

-- ==========================================================
-- SEED DATA
-- ==========================================================

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `status`, `created_at`) VALUES
(1, 'Administrator', 'admin@ironcore.com', '$2y$10$zQBKYz9FXKahVQxk6pLBLepimPkDc0gpgxbUZHdeCptU8oMVDpqI.', 'admin', 'active', '2025-01-01 10:00:00'),
(2, 'Rahul Patel', 'trainer@ironcore.com', '$2y$10$0PGepMPw8Qs/eoTwsy/UCuwSj/gS/cjEbBDGW/TO1h0nB1MBQU/w.', 'trainer', 'active', '2025-01-02 10:00:00'),
(3, 'Aarav Shah', 'aarav.shah@ironcore.com', '$2y$10$0PGepMPw8Qs/eoTwsy/UCuwSj/gS/cjEbBDGW/TO1h0nB1MBQU/w.', 'trainer', 'active', '2025-01-03 10:00:00'),
(4, 'Meera Joshi', 'meera.joshi@ironcore.com', '$2y$10$0PGepMPw8Qs/eoTwsy/UCuwSj/gS/cjEbBDGW/TO1h0nB1MBQU/w.', 'trainer', 'active', '2025-01-04 10:00:00'),
(5, 'Karan Desai', 'karan.desai@ironcore.com', '$2y$10$0PGepMPw8Qs/eoTwsy/UCuwSj/gS/cjEbBDGW/TO1h0nB1MBQU/w.', 'trainer', 'active', '2025-01-05 10:00:00'),
(6, 'Riya Mehta', 'riya.mehta@ironcore.com', '$2y$10$0PGepMPw8Qs/eoTwsy/UCuwSj/gS/cjEbBDGW/TO1h0nB1MBQU/w.', 'trainer', 'active', '2025-01-06 10:00:00'),
(7, 'Preyal Modi', 'member@ironcore.com', '$2y$10$nFRemh8Qg/B86uDKe3VLTescEk6gcps4NM38MhxwQ6j6zLAeidrIi', 'member', 'active', '2025-01-10 10:00:00'),
(8, 'Aarav Patel', 'aarav.patel@ironcore.com', '$2y$10$nFRemh8Qg/B86uDKe3VLTescEk6gcps4NM38MhxwQ6j6zLAeidrIi', 'member', 'active', '2025-01-11 10:00:00'),
(9, 'Rahul Shah', 'rahul.shah@ironcore.com', '$2y$10$nFRemh8Qg/B86uDKe3VLTescEk6gcps4NM38MhxwQ6j6zLAeidrIi', 'member', 'active', '2025-01-12 10:00:00'),
(10, 'Meera J', 'meera.j@ironcore.com', '$2y$10$nFRemh8Qg/B86uDKe3VLTescEk6gcps4NM38MhxwQ6j6zLAeidrIi', 'member', 'active', '2025-01-13 10:00:00'),
(11, 'Karan D', 'karan.d@ironcore.com', '$2y$10$nFRemh8Qg/B86uDKe3VLTescEk6gcps4NM38MhxwQ6j6zLAeidrIi', 'member', 'active', '2025-01-14 10:00:00'),
(12, 'Riya Patel', 'riya.p@ironcore.com', '$2y$10$nFRemh8Qg/B86uDKe3VLTescEk6gcps4NM38MhxwQ6j6zLAeidrIi', 'member', 'active', '2025-01-15 10:00:00'),
(13, 'Vikram Singh', 'vikram.singh@ironcore.com', '$2y$10$nFRemh8Qg/B86uDKe3VLTescEk6gcps4NM38MhxwQ6j6zLAeidrIi', 'member', 'active', '2025-01-16 10:00:00'),
(14, 'Ananya Sharma', 'ananya.sharma@ironcore.com', '$2y$10$nFRemh8Qg/B86uDKe3VLTescEk6gcps4NM38MhxwQ6j6zLAeidrIi', 'member', 'active', '2025-01-17 10:00:00'),
(15, 'Rohit Verma', 'rohit.verma@ironcore.com', '$2y$10$nFRemh8Qg/B86uDKe3VLTescEk6gcps4NM38MhxwQ6j6zLAeidrIi', 'member', 'active', '2025-01-18 10:00:00'),
(16, 'Sneha Reddy', 'sneha.reddy@ironcore.com', '$2y$10$nFRemh8Qg/B86uDKe3VLTescEk6gcps4NM38MhxwQ6j6zLAeidrIi', 'member', 'active', '2025-01-19 10:00:00'),
(17, 'Aditya Nair', 'aditya.nair@ironcore.com', '$2y$10$nFRemh8Qg/B86uDKe3VLTescEk6gcps4NM38MhxwQ6j6zLAeidrIi', 'member', 'active', '2025-01-20 10:00:00'),
(18, 'Pooja Iyer', 'pooja.iyer@ironcore.com', '$2y$10$nFRemh8Qg/B86uDKe3VLTescEk6gcps4NM38MhxwQ6j6zLAeidrIi', 'member', 'active', '2025-01-21 10:00:00'),
(19, 'Manish Gupta', 'manish.gupta@ironcore.com', '$2y$10$nFRemh8Qg/B86uDKe3VLTescEk6gcps4NM38MhxwQ6j6zLAeidrIi', 'member', 'active', '2025-01-22 10:00:00'),
(20, 'Tanvi Deshmukh', 'tanvi.deshmukh@ironcore.com', '$2y$10$nFRemh8Qg/B86uDKe3VLTescEk6gcps4NM38MhxwQ6j6zLAeidrIi', 'member', 'active', '2025-01-23 10:00:00'),
(21, 'Varun Kapoor', 'varun.kapoor@ironcore.com', '$2y$10$nFRemh8Qg/B86uDKe3VLTescEk6gcps4NM38MhxwQ6j6zLAeidrIi', 'member', 'active', '2025-01-24 10:00:00'),
(22, 'Simran Kaur', 'simran.kaur@ironcore.com', '$2y$10$nFRemh8Qg/B86uDKe3VLTescEk6gcps4NM38MhxwQ6j6zLAeidrIi', 'member', 'active', '2025-01-25 10:00:00'),
(23, 'Arjun Malhotra', 'arjun.malhotra@ironcore.com', '$2y$10$nFRemh8Qg/B86uDKe3VLTescEk6gcps4NM38MhxwQ6j6zLAeidrIi', 'member', 'active', '2025-01-26 10:00:00'),
(24, 'Divya Menon', 'divya.menon@ironcore.com', '$2y$10$nFRemh8Qg/B86uDKe3VLTescEk6gcps4NM38MhxwQ6j6zLAeidrIi', 'member', 'active', '2025-01-27 10:00:00'),
(25, 'Siddharth Rao', 'siddharth.rao@ironcore.com', '$2y$10$nFRemh8Qg/B86uDKe3VLTescEk6gcps4NM38MhxwQ6j6zLAeidrIi', 'member', 'active', '2025-01-28 10:00:00'),
(26, 'Neha Bose', 'neha.bose@ironcore.com', '$2y$10$nFRemh8Qg/B86uDKe3VLTescEk6gcps4NM38MhxwQ6j6zLAeidrIi', 'member', 'active', '2025-01-29 10:00:00');

INSERT INTO `trainers` (`id`, `user_id`, `specialization`, `experience`, `phone`, `bio`, `profile_image`) VALUES
(1, 2, 'Strength & Hypertrophy Coach', '8 Years', '+91 98765 43210', 'Certified CSCS Strength Coach specializing in compound powerlifting and progressive overload techniques.', 'assets/images/trainers/trainer1.jpg'),
(2, 3, 'Functional & Cross-Training Specialist', '6 Years', '+91 98765 43211', 'Expert in athletic conditioning, mobility, high-intensity kettlebell and functional endurance.', 'assets/images/trainers/trainer2.jpg'),
(3, 4, 'Mobility & Posture Specialist', '5 Years', '+91 98765 43212', 'Focuses on functional movement screens, core stability, rehabilitation and holistic yoga conditioning.', 'assets/images/trainers/trainer3.jpg'),
(4, 5, 'Cardio & Fat Loss Specialist', '7 Years', '+91 98765 43213', 'HIIT and metabolic conditioning expert with hundreds of successful body recomposition transformations.', 'assets/images/trainers/trainer4.jpg'),
(5, 6, 'Body Recomposition & Nutrition Coach', '4 Years', '+91 98765 43214', 'Certified sports nutritionist and contest prep specialist focusing on sustainable lifestyle diets.', 'assets/images/trainers/trainer5.jpg');

INSERT INTO `membership_plans` (`id`, `name`, `duration`, `duration_days`, `price`, `description`, `features`, `status`) VALUES
(1, 'Basic Monthly', '1 Month', 30, 1000.00, 'Standard gym floor access with general locker room facilities.', 'Gym Floor Access,Locker Access,Standard Shower,1 Assessment', 'active'),
(2, 'Standard 3-Months', '3 Months', 90, 2500.00, 'Popular choice for consistent beginners building healthy workout habits.', 'Full Floor Access,Locker & Shower,1 Personal Training Session,Diet Consultation,Body Composition Scan', 'active'),
(3, 'Premium 6-Months', '6 Months', 180, 4500.00, 'Comprehensive package with personal training perks and diet support.', 'All Floor Access,Locker & Steam Bath,3 PT Sessions,Monthly Diet Charts,Free Guest Passes (2/mo)', 'active'),
(4, 'Pro Annual', '1 Year', 365, 8000.00, 'The ultimate all-inclusive fitness VIP pass for dedicated athletes.', 'Unlimited 24/7 Access,All Amenities & Sauna,6 PT Sessions,Custom Monthly Nutrition,Free Gym Merchandise Kit', 'active');

INSERT INTO `members` (`id`, `user_id`, `member_code`, `phone`, `gender`, `date_of_birth`, `address`, `height`, `weight`, `fitness_goal`, `fitness_level`, `trainer_id`, `profile_image`) VALUES
(1, 7, 'IC-1001', '+91 91234 56701', 'Male', '1998-05-14', 'B-402 Marvel Heights, Ahmedabad', 175.00, 68.50, 'Muscle Building', 'Intermediate', 1, 'assets/images/trainers/default.jpg'),
(2, 8, 'IC-1002', '+91 91234 56702', 'Male', '1995-11-20', '12 Riverfront Apts, Ahmedabad', 180.00, 84.00, 'Fat Loss', 'Intermediate', 4, 'assets/images/trainers/default.jpg'),
(3, 9, 'IC-1003', '+91 91234 56703', 'Male', '2000-03-08', '405 Sapphire Enclave, Surat', 172.00, 62.00, 'Increase Strength', 'Beginner', 1, 'assets/images/trainers/default.jpg'),
(4, 10, 'IC-1004', '+91 91234 56704', 'Female', '1997-09-15', '77 Gulmohar Park, Vadodara', 165.00, 58.00, 'General Fitness', 'Beginner', 3, 'assets/images/trainers/default.jpg'),
(5, 11, 'IC-1005', '+91 91234 56705', 'Male', '1993-01-28', '210 Sun City, Rajkot', 178.00, 76.50, 'Improve Endurance', 'Advanced', 2, 'assets/images/trainers/default.jpg'),
(6, 12, 'IC-1006', '+91 91234 56706', 'Female', '2001-07-19', '89 Royal Residency, Ahmedabad', 160.00, 52.00, 'Improve Mobility', 'Beginner', 3, 'assets/images/trainers/default.jpg'),
(7, 13, 'IC-1007', '+91 91234 56707', 'Male', '1991-12-05', '54 Titanium Square, Gandhinagar', 182.00, 90.00, 'Muscle Building', 'Advanced', 1, 'assets/images/trainers/default.jpg'),
(8, 14, 'IC-1008', '+91 91234 56708', 'Female', '1999-04-22', '102 Green Valley, Ahmedabad', 168.00, 64.00, 'Fat Loss', 'Intermediate', 5, 'assets/images/trainers/default.jpg'),
(9, 15, 'IC-1009', '+91 91234 56709', 'Male', '1996-08-30', '15 Orchid Boulevard, Surat', 174.00, 73.00, 'Increase Strength', 'Intermediate', 1, 'assets/images/trainers/default.jpg'),
(10, 16, 'IC-1010', '+91 91234 56710', 'Female', '2002-02-14', '60 Galaxy Avenue, Ahmedabad', 162.00, 55.00, 'General Fitness', 'Beginner', 5, 'assets/images/trainers/default.jpg'),
(11, 17, 'IC-1011', '+91 91234 56711', 'Male', '1994-06-11', '33 Sky Deck Flats, Vadodara', 176.00, 79.00, 'Muscle Building', 'Intermediate', 2, 'assets/images/trainers/default.jpg'),
(12, 18, 'IC-1012', '+91 91234 56712', 'Female', '1998-10-03', '74 Crystal Palms, Ahmedabad', 163.00, 59.00, 'Improve Endurance', 'Intermediate', 4, 'assets/images/trainers/default.jpg'),
(13, 19, 'IC-1013', '+91 91234 56713', 'Male', '1989-03-18', '90 Horizon Tower, Rajkot', 170.00, 85.00, 'Fat Loss', 'Beginner', 4, 'assets/images/trainers/default.jpg'),
(14, 20, 'IC-1014', '+91 91234 56714', 'Female', '2000-11-25', '18 Silver Crest, Gandhinagar', 166.00, 61.00, 'General Fitness', 'Beginner', 3, 'assets/images/trainers/default.jpg'),
(15, 21, 'IC-1015', '+91 91234 56715', 'Male', '1995-05-09', '44 Palm Meadows, Ahmedabad', 183.00, 88.00, 'Increase Strength', 'Advanced', 1, 'assets/images/trainers/default.jpg'),
(16, 22, 'IC-1016', '+91 91234 56716', 'Female', '1997-08-14', '62 Sterling Park, Surat', 158.00, 50.00, 'Improve Mobility', 'Beginner', 3, 'assets/images/trainers/default.jpg'),
(17, 23, 'IC-1017', '+91 91234 56717', 'Male', '1992-12-21', '81 Harmony Gardens, Vadodara', 179.00, 77.00, 'Muscle Building', 'Intermediate', 2, 'assets/images/trainers/default.jpg'),
(18, 24, 'IC-1018', '+91 91234 56718', 'Female', '2001-01-05', '29 Ashwamegh Heights, Ahmedabad', 164.00, 56.00, 'Fat Loss', 'Beginner', 5, 'assets/images/trainers/default.jpg'),
(19, 25, 'IC-1019', '+91 91234 56719', 'Male', '1994-09-17', '51 Heritage Villa, Rajkot', 177.00, 82.00, 'Increase Strength', 'Intermediate', 1, 'assets/images/trainers/default.jpg'),
(20, 26, 'IC-1020', '+91 91234 56720', 'Female', '1996-04-12', '101 Shivalik Avenue, Ahmedabad', 167.00, 63.00, 'General Fitness', 'Intermediate', 4, 'assets/images/trainers/default.jpg');

INSERT INTO `memberships` (`id`, `member_id`, `plan_id`, `start_date`, `end_date`, `status`, `created_at`) VALUES
(1, 1, 4, '2025-01-10', '2026-01-10', 'active', '2025-01-10 10:00:00'),
(2, 2, 3, '2025-01-11', '2025-07-11', 'active', '2025-01-11 10:00:00'),
(3, 3, 2, '2025-01-12', '2025-04-12', 'active', '2025-01-12 10:00:00'),
(4, 4, 1, '2025-01-13', '2025-02-13', 'expiring_soon', '2025-01-13 10:00:00'),
(5, 5, 4, '2025-01-14', '2026-01-14', 'active', '2025-01-14 10:00:00'),
(6, 6, 2, '2024-10-15', '2025-01-15', 'expired', '2024-10-15 10:00:00'),
(7, 7, 3, '2025-01-16', '2025-07-16', 'active', '2025-01-16 10:00:00'),
(8, 8, 4, '2025-01-17', '2026-01-17', 'active', '2025-01-17 10:00:00'),
(9, 9, 2, '2025-01-18', '2025-04-18', 'active', '2025-01-18 10:00:00'),
(10, 10, 1, '2025-01-19', '2025-02-19', 'expiring_soon', '2025-01-19 10:00:00'),
(11, 11, 3, '2025-01-20', '2025-07-20', 'active', '2025-01-20 10:00:00'),
(12, 12, 4, '2025-01-21', '2026-01-21', 'active', '2025-01-21 10:00:00'),
(13, 13, 1, '2024-11-22', '2024-12-22', 'expired', '2024-11-22 10:00:00'),
(14, 14, 2, '2025-01-23', '2025-04-23', 'active', '2025-01-23 10:00:00'),
(15, 15, 4, '2025-01-24', '2026-01-24', 'active', '2025-01-24 10:00:00'),
(16, 16, 2, '2024-09-25', '2024-12-25', 'expired', '2024-09-25 10:00:00'),
(17, 17, 3, '2025-01-26', '2025-07-26', 'active', '2025-01-26 10:00:00'),
(18, 18, 1, '2025-01-27', '2025-02-27', 'active', '2025-01-27 10:00:00'),
(19, 19, 4, '2025-01-28', '2026-01-28', 'active', '2025-01-28 10:00:00'),
(20, 20, 3, '2025-01-29', '2025-07-29', 'active', '2025-01-29 10:00:00');

INSERT INTO `payments` (`id`, `receipt_number`, `member_id`, `membership_id`, `amount`, `payment_method`, `payment_status`, `transaction_reference`, `notes`, `payment_date`) VALUES
(1, 'REC-2025-001', 1, 1, 8000.00, 'UPI', 'Completed', 'UPI/501029384721', 'Annual Pro Membership full payment', '2025-01-10 10:15:00'),
(2, 'REC-2025-002', 2, 2, 4500.00, 'Card', 'Completed', 'CARD/992837418293', 'Premium 6-Months package', '2025-01-11 11:20:00'),
(3, 'REC-2025-003', 3, 3, 2500.00, 'UPI', 'Completed', 'UPI/882736192837', 'Standard 3-Months registration', '2025-01-12 09:30:00'),
(4, 'REC-2025-004', 4, 4, 1000.00, 'Cash', 'Completed', 'CASH/OFFLINE-104', 'Basic Monthly payment', '2025-01-13 16:45:00'),
(5, 'REC-2025-005', 5, 5, 8000.00, 'Bank Transfer', 'Completed', 'NEFT/HDFC0029381', 'Annual membership direct bank transfer', '2025-01-14 14:10:00'),
(6, 'REC-2025-006', 7, 7, 4500.00, 'UPI', 'Completed', 'UPI/663829103948', 'Premium 6-Months registration', '2025-01-16 10:00:00'),
(7, 'REC-2025-007', 8, 8, 8000.00, 'Card', 'Completed', 'CARD/554839201948', 'Pro Annual membership with locker upgrade', '2025-01-17 18:30:00'),
(8, 'REC-2025-008', 9, 9, 2500.00, 'UPI', 'Completed', 'UPI/332918274619', 'Standard 3-Months enrollment', '2025-01-18 12:00:00'),
(9, 'REC-2025-009', 10, 10, 1000.00, 'Cash', 'Completed', 'CASH/OFFLINE-109', 'Basic monthly fee', '2025-01-19 17:15:00'),
(10, 'REC-2025-010', 11, 11, 4500.00, 'UPI', 'Completed', 'UPI/991827364501', 'Premium 6-Months payment', '2025-01-20 11:45:00'),
(11, 'REC-2025-011', 12, 12, 8000.00, 'Card', 'Completed', 'CARD/881928374650', 'Pro Annual membership', '2025-01-21 15:20:00'),
(12, 'REC-2025-012', 14, 14, 2500.00, 'UPI', 'Completed', 'UPI/772839102948', 'Standard 3-Months payment', '2025-01-23 10:30:00'),
(13, 'REC-2025-013', 15, 15, 8000.00, 'Bank Transfer', 'Completed', 'NEFT/ICIC9928172', 'Annual Pro enrollment', '2025-01-24 16:50:00'),
(14, 'REC-2025-014', 17, 17, 4500.00, 'UPI', 'Completed', 'UPI/443920192837', 'Premium 6-Months payment', '2025-01-26 09:15:00'),
(15, 'REC-2025-015', 18, 18, 1000.00, 'UPI', 'Completed', 'UPI/112938475620', 'Basic Monthly payment', '2025-01-27 18:00:00'),
(16, 'REC-2025-016', 19, 19, 8000.00, 'Card', 'Completed', 'CARD/339281746201', 'Pro Annual fee', '2025-01-28 14:00:00'),
(17, 'REC-2025-017', 20, 20, 4500.00, 'UPI', 'Completed', 'UPI/229182736451', 'Premium 6-Months fee', '2025-01-29 11:30:00'),
(18, 'REC-2025-018', 1, NULL, 500.00, 'UPI', 'Completed', 'UPI/559281746302', 'Locker rental upgrade fee', '2025-02-01 10:00:00'),
(19, 'REC-2025-019', 2, NULL, 1500.00, 'Cash', 'Completed', 'CASH/OFFLINE-119', 'Personal Training add-on 5 sessions', '2025-02-03 16:30:00'),
(20, 'REC-2025-020', 3, NULL, 800.00, 'UPI', 'Completed', 'UPI/771928374651', 'IronCore merchandise hoodie & shaker', '2025-02-05 12:45:00'),
(21, 'REC-2025-021', 5, NULL, 1200.00, 'Card', 'Completed', 'CARD/662819384729', 'Nutrition & Macro consultation plan', '2025-02-07 15:10:00'),
(22, 'REC-2025-022', 7, NULL, 2000.00, 'UPI', 'Completed', 'UPI/441928374652', 'Advanced hypertrophy supplement pack', '2025-02-09 18:20:00'),
(23, 'REC-2025-023', 8, NULL, 1000.00, 'UPI', 'Pending', 'UPI/PENDING-8827', 'PT consultation pending verification', '2025-02-12 11:00:00'),
(24, 'REC-2025-024', 9, NULL, 500.00, 'Cash', 'Completed', 'CASH/OFFLINE-124', 'Gym towel & wrist straps', '2025-02-14 09:30:00'),
(25, 'REC-2025-025', 11, NULL, 1500.00, 'UPI', 'Completed', 'UPI/331928374653', 'Weekend bootcamp pass', '2025-02-16 17:00:00'),
(26, 'REC-2025-026', 12, NULL, 800.00, 'Card', 'Completed', 'CARD/221928374654', 'Gym gloves and belt accessories', '2025-02-18 13:15:00'),
(27, 'REC-2025-027', 15, NULL, 2500.00, 'UPI', 'Completed', 'UPI/111928374655', 'Quarterly guest access pass', '2025-02-20 10:45:00'),
(28, 'REC-2025-028', 17, NULL, 1000.00, 'UPI', 'Pending', 'UPI/PENDING-9912', 'Physiotherapy mobility assessment', '2025-02-21 16:00:00'),
(29, 'REC-2025-029', 19, NULL, 600.00, 'Cash', 'Completed', 'CASH/OFFLINE-129', 'Locker renewal monthly', '2025-02-22 08:30:00'),
(30, 'REC-2025-030', 20, NULL, 1200.00, 'UPI', 'Completed', 'UPI/999827364500', 'Yoga & Stretch weekend workshop', '2025-02-22 14:20:00');

INSERT INTO `exercises` (`id`, `name`, `category`, `muscle_group`, `difficulty`, `sets`, `reps`, `rest_time`, `instructions`, `image`) VALUES
(1, 'Flat Barbell Bench Press', 'Chest', 'Pectoralis Major, Triceps, Anterior Deltoids', 'Intermediate', 4, '8-10', '90 sec', 'Lie flat on bench, retract scapula, grip slightly wider than shoulder width, lower bar smoothly to mid-chest, press upward explosively.', 'assets/images/exercises/bench_press.jpg'),
(2, 'Incline Dumbbell Press', 'Chest', 'Upper Pectoralis, Front Shoulders', 'Intermediate', 3, '10-12', '60 sec', 'Set bench to 30-45 degrees. Press dumbbells up and slightly inward over clavicles, controlling the eccentric descent.', 'assets/images/exercises/incline_press.jpg'),
(3, 'Push-Ups', 'Chest', 'Chest, Triceps, Core', 'Beginner', 3, '15-20', '45 sec', 'Maintain rigid plank posture, lower chest within 1 inch of floor, drive palms into ground keeping elbows at 45 degree angle.', 'assets/images/exercises/pushups.jpg'),
(4, 'Cable Chest Fly', 'Chest', 'Sternal Pectoralis', 'Beginner', 3, '12-15', '60 sec', 'Set pulleys to shoulder height. Bring handles together in a hugging motion, squeezing the chest hard at the peak contraction.', 'assets/images/exercises/cable_fly.jpg'),
(5, 'Barbell Back Squat', 'Legs', 'Quadriceps, Glutes, Hamstrings, Core', 'Advanced', 4, '6-8', '120 sec', 'Bar placed across upper traps. Unrack, hinge hips back and bend knees until thighs break parallel, drive feet through floor.', 'assets/images/exercises/squat.jpg'),
(6, 'Leg Press Machine', 'Legs', 'Quadriceps, Gluteus Maximus', 'Beginner', 4, '10-12', '90 sec', 'Place feet shoulder width on sled platform. Release safety locks, lower sled until 90 degree knee flexion, press without locking knees.', 'assets/images/exercises/leg_press.jpg'),
(7, 'Walking Dumbbell Lunges', 'Legs', 'Quadriceps, Glutes, Hamstrings', 'Intermediate', 3, '12 steps/leg', '60 sec', 'Step forward keeping torso tall. Lower rear knee toward ground, press through front heel to step forward into next lunge.', 'assets/images/exercises/lunges.jpg'),
(8, 'Conventional Deadlift', 'Back', 'Hamstrings, Glutes, Erector Spinae, Lats', 'Advanced', 4, '5-6', '150 sec', 'Stand with feet hip-width under bar. Grip outside shins, engage lats, push floor away while driving hips forward to lockout.', 'assets/images/exercises/deadlift.jpg'),
(9, 'Wide Grip Lat Pulldown', 'Back', 'Latissimus Dorsi, Biceps, Rear Delts', 'Beginner', 4, '10-12', '60 sec', 'Grip bar wider than shoulders. Pull bar down toward upper chest while driving elbows toward your back pockets.', 'assets/images/exercises/lat_pulldown.jpg'),
(10, 'Bodyweight Pull-Ups', 'Back', 'Latissimus Dorsi, Rhomboids, Biceps', 'Advanced', 3, '8-10', '90 sec', 'Overhand grip on bar. Depress shoulder blades, pull chest up toward bar until chin clears the bar, lower under control.', 'assets/images/exercises/pullups.jpg'),
(11, 'Bent-Over Barbell Row', 'Back', 'Mid Back, Latissimus Dorsi, Posterior Delts', 'Intermediate', 4, '8-10', '90 sec', 'Hinge at hips to 45 degrees. Pull bar toward lower ribs, keeping spine neutral and elbows close to the torso.', 'assets/images/exercises/barbell_row.jpg'),
(12, 'Standing Dumbbell Bicep Curl', 'Biceps', 'Biceps Brachii, Brachialis', 'Beginner', 3, '10-12', '60 sec', 'Hold dumbbells at sides with palms forward. Curl weights upward keeping elbows fixed at ribs, rotate wrists slightly outward.', 'assets/images/exercises/bicep_curl.jpg'),
(13, 'Hammer Curls', 'Biceps', 'Brachialis, Forearms, Biceps', 'Beginner', 3, '12-15', '45 sec', 'Hold dumbbells with neutral (palms facing each other) grip. Curl upward smoothly targeting arm thickness.', 'assets/images/exercises/hammer_curl.jpg'),
(14, 'Rope Tricep Pushdown', 'Triceps', 'Triceps Brachii Lateral & Medial Head', 'Beginner', 3, '12-15', '45 sec', 'Attach rope to high pulley. Extend elbows downward and spread the rope apart at bottom lockout for maximum tricep squeeze.', 'assets/images/exercises/tricep_pushdown.jpg'),
(15, 'Overhead Barbell Shoulder Press', 'Shoulders', 'Anterior & Lateral Deltoids, Triceps', 'Intermediate', 4, '8-10', '90 sec', 'Press barbell straight up overhead from collarbone to full arm extension, bracing core and glutes firmly.', 'assets/images/exercises/shoulder_press.jpg'),
(16, 'Dumbbell Lateral Raise', 'Shoulders', 'Lateral Deltoids', 'Beginner', 4, '12-15', '45 sec', 'Raise dumbbells out to sides with slight forward lean until arms are parallel with floor. Lead with elbows.', 'assets/images/exercises/lateral_raise.jpg'),
(17, 'Core Plank Hold', 'Core', 'Transverse Abdominis, Rectus Abdominis, Obliques', 'Beginner', 3, '60 sec hold', '45 sec', 'Forearms and toes on ground, maintain completely straight line from head to heels, tucking pelvis and pulling navel in.', 'assets/images/exercises/plank.jpg'),
(18, 'Hanging Knee Raises', 'Core', 'Lower Abs, Hip Flexors', 'Intermediate', 3, '15-20', '45 sec', 'Hang from pull-up bar, raise knees smoothly toward chest without swinging or utilizing momentum.', 'assets/images/exercises/knee_raises.jpg'),
(19, 'Treadmill Interval Running', 'Cardio', 'Cardiovascular, Quads, Calves', 'Beginner', 1, '20 mins', 'N/A', 'Alternate 1 minute fast run (speed 11 km/h) with 1 minute recovery walk (speed 5.5 km/h) for 10 intervals.', 'assets/images/exercises/running.jpg'),
(20, 'Stationary Cycling (HIIT)', 'Cardio', 'Cardiovascular, Lower Body', 'Beginner', 1, '20 mins', 'N/A', 'High resistance sprint for 30 seconds followed by 45 seconds moderate spinning for 12 total rounds.', 'assets/images/exercises/cycling.jpg');

INSERT INTO `workout_plans` (`id`, `trainer_id`, `name`, `goal`, `fitness_level`, `duration`, `description`) VALUES
(1, 1, 'IronCore Hypertrophy Split', 'Muscle Building', 'Intermediate', '8 Weeks', 'Classic Push-Pull-Legs 6-day split designed to pack on dense muscle mass with optimal recovery cycles.'),
(2, 4, 'Metabolic Shred & Fat Burn', 'Fat Loss', 'Beginner', '6 Weeks', 'High energy circuit workout pairing compound resistance movements with intense cardio bursts for rapid fat loss.'),
(3, 1, 'Pure Power & Strength Protocol', 'Increase Strength', 'Advanced', '12 Weeks', 'Periodized strength progression focused on squat, bench press, deadlift, and overhead press PRs.'),
(4, 3, 'Total Body Foundation & Mobility', 'General Fitness', 'Beginner', '4 Weeks', 'Full body starter routine establishing movement mechanics, joint flexibility, and base conditioning.'),
(5, 2, 'Athletic Conditioning & Endurance', 'Improve Endurance', 'Intermediate', '6 Weeks', 'Functional fitness programming incorporating plyometrics, tempo runs, kettlebells, and core stamina.'),
(6, 3, 'Desk Worker Posture Correction', 'Improve Mobility', 'Beginner', '4 Weeks', 'Targeted routine opening tight hips, strengthening posterior upper back, and revitalizing core stabilizers.'),
(7, 1, 'Upper / Lower Beast Split', 'Muscle Building', 'Intermediate', '8 Weeks', '4-day weekly frequency program allowing maximum intensity with 3 full days of active recovery.'),
(8, 5, 'Quick 45-Min Express Workout', 'General Fitness', 'Beginner', '4 Weeks', 'Time-effective superset routine tailored for busy working professionals who want maximum return in 45 mins.'),
(9, 4, 'HIIT Cardio Core Crusher', 'Fat Loss', 'Intermediate', '4 Weeks', 'Heart rate maximizing circuit combining sprints, rope pushdowns, lunges, and plank variations.'),
(10, 1, 'Chest & Arms Gun Club Special', 'Muscle Building', 'Advanced', '6 Weeks', 'Specialization routine adding arm circumference and chest thickness with high volume drop sets.');

INSERT INTO `workout_exercises` (`workout_plan_id`, `exercise_id`, `day_name`, `sets`, `reps`, `rest_time`, `order_number`) VALUES
(1, 1, 'Monday', 4, '8-10', '90 sec', 1),
(1, 2, 'Monday', 3, '10-12', '60 sec', 2),
(1, 4, 'Monday', 3, '12-15', '60 sec', 3),
(1, 15, 'Monday', 3, '8-10', '90 sec', 4),
(1, 14, 'Monday', 3, '12-15', '45 sec', 5),
(1, 8, 'Tuesday', 4, '6-8', '120 sec', 1),
(1, 9, 'Tuesday', 4, '10-12', '60 sec', 2),
(1, 11, 'Tuesday', 3, '8-10', '90 sec', 3),
(1, 12, 'Tuesday', 3, '10-12', '60 sec', 4),
(1, 13, 'Tuesday', 3, '12-15', '45 sec', 5),
(1, 5, 'Thursday', 4, '6-8', '120 sec', 1),
(1, 6, 'Thursday', 4, '10-12', '90 sec', 2),
(1, 7, 'Thursday', 3, '12 steps', '60 sec', 3),
(1, 17, 'Thursday', 3, '60 sec', '45 sec', 4),
(1, 18, 'Thursday', 3, '15 reps', '45 sec', 5),
(2, 3, 'Monday', 3, '15 reps', '45 sec', 1),
(2, 7, 'Monday', 3, '12 steps', '45 sec', 2),
(2, 17, 'Monday', 3, '45 sec', '30 sec', 3),
(2, 19, 'Monday', 1, '20 mins', 'N/A', 4),
(2, 9, 'Wednesday', 3, '12 reps', '45 sec', 1),
(2, 6, 'Wednesday', 3, '15 reps', '45 sec', 2),
(2, 16, 'Wednesday', 3, '15 reps', '30 sec', 3),
(2, 20, 'Wednesday', 1, '20 mins', 'N/A', 4),
(2, 1, 'Friday', 3, '12 reps', '60 sec', 1),
(2, 5, 'Friday', 3, '10 reps', '60 sec', 2),
(2, 18, 'Friday', 3, '15 reps', '45 sec', 3),
(2, 19, 'Friday', 1, '20 mins', 'N/A', 4);

INSERT INTO `diet_plans` (`id`, `trainer_id`, `name`, `goal`, `target_calories`, `description`) VALUES
(1, 5, 'High Protein Lean Bulk Matrix', 'Muscle Building', 2600, 'Nutrient dense meal plan with high biological value proteins, complex carbs, and essential fats to support hypertrophy.'),
(2, 5, 'Keto Shred & Fat Elimination', 'Fat Loss', 1800, 'Low-carb, high-satiety nutrition template prioritizing lean meats, healthy avocado oils, leafy greens, and clean hydration.'),
(3, 4, 'Balanced Athlete Maintenance', 'General Fitness', 2200, 'Clean balanced nutrition providing steady sustained daily energy for hybrid strength and aerobic training.'),
(4, 3, 'Vegetarian High-Protein Fuel', 'Muscle Building', 2400, 'Wholesome vegetarian plan loaded with paneer, tofu, lentils, whey protein isolate, sprouted beans, and oats.');

INSERT INTO `diet_meals` (`id`, `diet_plan_id`, `meal_type`, `food_items`, `calories`, `protein_g`, `carbs_g`, `fats_g`, `notes`) VALUES
(1, 1, 'Breakfast', '4 Egg Whites + 2 Whole Eggs Scramble, 1 Bowl Rolled Oats with 1 Banana, Handful Almonds', 550, 32, 65, 18, 'Add cinnamon to oats for blood sugar stability.'),
(2, 1, 'Lunch', '200g Grilled Chicken Breast / Paneer, 1.5 Cups Brown Rice, Steamed Broccoli & Zucchini', 700, 50, 75, 16, 'Drizzle with 1 tsp extra virgin olive oil.'),
(3, 1, 'Snack', '1 Scoop Whey Isolate, 1 Apple, 2 Rice Cakes with Peanut Butter', 400, 30, 45, 10, 'Consume 60 minutes pre-workout.'),
(4, 1, 'Dinner', '200g White Fish / Tofu Stir-Fry, 2 Whole Wheat Rotis, Large Green Salad with Cucumber', 600, 42, 55, 14, 'Finish dinner at least 2 hours before bedtime.'),
(5, 4, 'Breakfast', 'Overnight Oats with Soy Milk, Chia Seeds, Whey Protein, Blueberries & Walnuts', 520, 30, 60, 16, 'Prep night before in a mason jar.'),
(6, 4, 'Lunch', '150g Low-Fat Paneer Bhurji, 1 Bowl Yellow Moong Dal, 1 Cup Quinoa / Brown Rice, Salad', 680, 38, 70, 20, 'Cook in cold-pressed mustard or olive oil.'),
(7, 4, 'Snack', 'Sprouted Moong & Chana Chaat with Lemon, 1 Glass Roasted Sattu Water', 350, 20, 48, 6, 'Rich in plant fiber and electrolytes.'),
(8, 4, 'Dinner', 'Soya Chunks Curry (50g dry), 2 Multigrain Rotis, Cucumber Tomato Raita', 550, 40, 52, 12, 'High protein vegetarian recovery meal.');

INSERT INTO `member_diets` (`id`, `member_id`, `diet_plan_id`, `assigned_date`, `status`) VALUES
(1, 1, 1, '2025-01-10', 'active'),
(2, 2, 2, '2025-01-11', 'active'),
(3, 3, 1, '2025-01-12', 'active'),
(4, 4, 3, '2025-01-13', 'active'),
(5, 7, 1, '2025-01-16', 'active'),
(6, 8, 2, '2025-01-17', 'active');

INSERT INTO `progress` (`id`, `member_id`, `weight`, `waist`, `chest`, `arms`, `legs`, `notes`, `record_date`) VALUES
(1, 1, 72.00, 34.00, 38.00, 13.50, 22.00, 'Baseline fitness intake assessment', '2025-01-10'),
(2, 1, 71.20, 33.50, 38.50, 13.80, 22.20, 'Strength improving on bench press and squat', '2025-01-24'),
(3, 1, 70.00, 33.00, 39.00, 14.00, 22.50, 'Body fat dropping, muscle definition visible', '2025-02-07'),
(4, 1, 68.50, 32.00, 39.50, 14.20, 23.00, 'Target recomposition goal hit smoothly!', '2025-02-21'),
(5, 2, 88.00, 38.00, 40.00, 14.50, 24.00, 'Starting fat loss protocol with coach Karan', '2025-01-11'),
(6, 2, 86.50, 37.00, 40.00, 14.50, 23.80, 'Cardio endurance up, waist reduced by 1 inch', '2025-01-25'),
(7, 2, 85.00, 36.20, 40.20, 14.60, 23.50, 'Consistent 5 days weekly gym attendance', '2025-02-08'),
(8, 2, 84.00, 35.50, 40.50, 14.80, 23.50, 'Down 4kg in 6 weeks with steady energy', '2025-02-22');

INSERT INTO `attendance` (`id`, `member_id`, `attendance_date`, `check_in_time`, `check_out_time`, `status`) VALUES
(1, 1, '2025-02-17', '06:30:00', '07:45:00', 'Present'),
(2, 1, '2025-02-18', '06:25:00', '07:50:00', 'Present'),
(3, 1, '2025-02-19', '06:40:00', '08:00:00', 'Present'),
(4, 1, '2025-02-20', '06:30:00', '07:45:00', 'Present'),
(5, 1, '2025-02-21', '06:35:00', '07:40:00', 'Present'),
(6, 1, '2025-02-22', '06:30:00', NULL, 'Present'),
(7, 2, '2025-02-17', '18:15:00', '19:30:00', 'Present'),
(8, 2, '2025-02-18', '18:10:00', '19:25:00', 'Present'),
(9, 2, '2025-02-19', '18:30:00', '19:45:00', 'Present'),
(10, 2, '2025-02-21', '18:00:00', '19:15:00', 'Present'),
(11, 3, '2025-02-20', '07:00:00', '08:15:00', 'Present'),
(12, 3, '2025-02-21', '07:15:00', '08:30:00', 'Present'),
(13, 3, '2025-02-22', '07:05:00', NULL, 'Present'),
(14, 4, '2025-02-21', '08:00:00', '09:00:00', 'Present'),
(15, 5, '2025-02-22', '06:15:00', '07:30:00', 'Present'),
(16, 7, '2025-02-22', '07:30:00', NULL, 'Present'),
(17, 8, '2025-02-22', '08:15:00', NULL, 'Present'),
(18, 9, '2025-02-22', '09:00:00', NULL, 'Present');

INSERT INTO `appointments` (`id`, `member_id`, `trainer_id`, `appointment_date`, `appointment_time`, `purpose`, `notes`, `status`) VALUES
(1, 1, 1, '2025-02-24', '07:00:00', '1-on-1 Deadlift Technique & Heavy Squat Check', 'Member wants to review lockout mechanics and hip drive.', 'Confirmed'),
(2, 2, 4, '2025-02-24', '18:30:00', 'HIIT Circuit & Heart Rate Zone Tuning', 'Formulate new weekly HIIT sprint progression.', 'Confirmed'),
(3, 3, 1, '2025-02-25', '08:00:00', 'Workout Split Consultation', 'Transition from 3-day full body to 4-day upper/lower split.', 'Pending'),
(4, 4, 3, '2025-02-25', '09:30:00', 'Mobility & Hamstring Flexibility Screen', 'Postural evaluation for remote desk worker.', 'Pending'),
(5, 7, 1, '2025-02-20', '07:30:00', 'Initial Strength Assessment', 'Baseline maximums recorded for bench and squat.', 'Completed');

INSERT INTO `announcements` (`id`, `title`, `content`, `target_role`, `priority`, `created_at`) VALUES
(1, 'New Olympic Barbells & Bumper Plates Arrived', 'We have upgraded our powerlifting zone with 4 new Eleiko competition bars and calibrated bumper plates. Come test them out!', 'all', 'normal', '2025-02-15 10:00:00'),
(2, 'Gym Operating Hours on Sunday', 'Please note that the gym will open at 06:00 AM and close early at 06:00 PM this Sunday for scheduled deep sanitization and HVAC maintenance.', 'all', 'high', '2025-02-18 12:00:00'),
(3, 'Spring 60-Day Body Transformation Challenge', 'Registrations are officially open for our annual 60-Day Transformation Challenge! Win cash prizes, custom trophies, and 1 year free Pro membership.', 'member', 'high', '2025-02-20 09:00:00'),
(4, 'Trainer Staff Meeting This Friday', 'All trainers are requested to attend the monthly fitness curriculum and member review meeting at 02:00 PM in the conference room.', 'trainer', 'urgent', '2025-02-21 14:00:00');

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 7, 'Workout Assigned', 'Coach Rahul Patel has assigned you the "IronCore Hypertrophy Split" 8-week program.', 'workout', 1, '2025-01-10 10:30:00'),
(2, 7, 'Diet Chart Ready', 'Your custom "High Protein Lean Bulk Matrix" meal plan is now active.', 'diet', 1, '2025-01-10 11:00:00'),
(3, 7, 'Appointment Confirmed', 'Your 1-on-1 session with Coach Rahul on 24th Feb at 07:00 AM has been confirmed.', 'appointment', 0, '2025-02-21 15:00:00'),
(4, 7, 'Membership Status Active', 'Your Pro Annual membership is active with 320 days remaining.', 'membership', 0, '2025-02-22 06:00:00'),
(5, 2, 'New Appointment Request', 'Preyal Modi has booked a 1-on-1 coaching session for 24th Feb at 07:00 AM.', 'appointment', 1, '2025-02-21 14:30:00'),
(6, 1, 'Daily Revenue Milestone', 'IronCore crossed ₹1,25,000 in monthly collection today.', 'payment', 0, '2025-02-22 09:00:00');

INSERT INTO `feedback` (`id`, `member_id`, `rating`, `message`, `category`, `created_at`) VALUES
(1, 1, 5, 'IronCore has completely transformed my gym experience. The equipment quality is top notch and coach Rahul is exceptional!', 'Trainers & Staff', '2025-02-15 11:30:00'),
(2, 2, 5, 'Clean environment, state of the art machines, and great locker facilities. The mobile web app check-in is super smooth.', 'Facilities', '2025-02-18 19:40:00'),
(3, 4, 4, 'Very friendly atmosphere for beginners. Would love if evening yoga slots could be added on weekends.', 'Classes', '2025-02-20 10:15:00');
