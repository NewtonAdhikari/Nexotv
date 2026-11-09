-- ============================================================
-- IPTV Streaming Platform - Complete Database Schema
-- ============================================================
-- This file creates the iptv_db database with all 31 category
-- tables and the support_db database with support_messages table.
-- ============================================================

-- Create the main IPTV database
CREATE DATABASE IF NOT EXISTS `iptv_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `iptv_db`;

-- ============================================================
-- 1. ANIMATION category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `animation` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. AUTO category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `auto` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. BUSINESS category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `business` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. CLASSIC category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `classic` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. COMEDY category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `comedy` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. COOKING category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `cooking` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. CULTURE category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `culture` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. DOCUMENTARY category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `documentary` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9. EDUCATION category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `education` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 10. ENTERTAINMENT category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `entertainment` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 11. FAMILY category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `family` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 12. GENERAL category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `general` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 13. INTERACTIVE category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `interactive` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 14. KIDS category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `kids` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 15. LEGISLATIVE category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `legislative` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 16. LIFESTYLE category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `lifestyle` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 17. MOVIES category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `movies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 18. MUSIC category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `music` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 19. NEWS category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `news` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 20. OUTDOOR category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `outdoor` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 21. PREMIUM category table (note: 'premimum' in original spec)
-- ============================================================
CREATE TABLE IF NOT EXISTS `premimum` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 22. PUBLIC category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `public` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 23. RELAX category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `relax` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 24. RELIGIOUS category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `religious` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 25. SCIENCE category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `science` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 26. SERIES category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `series` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 27. SHOP category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `shop` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 28. SPORTS category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `sports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 29. TRAVEL category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `travel` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 30. UNDEFINED category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `undefined` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 31. WEATHER category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `weather` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 32. XXX category table
-- ============================================================
CREATE TABLE IF NOT EXISTS `xxx` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo` TEXT DEFAULT NULL,
  `stream_url` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Add indexes on name column for faster search
-- ============================================================
CREATE INDEX idx_animation_name ON `animation` (`name`);
CREATE INDEX idx_auto_name ON `auto` (`name`);
CREATE INDEX idx_business_name ON `business` (`name`);
CREATE INDEX idx_classic_name ON `classic` (`name`);
CREATE INDEX idx_comedy_name ON `comedy` (`name`);
CREATE INDEX idx_cooking_name ON `cooking` (`name`);
CREATE INDEX idx_culture_name ON `culture` (`name`);
CREATE INDEX idx_documentary_name ON `documentary` (`name`);
CREATE INDEX idx_education_name ON `education` (`name`);
CREATE INDEX idx_entertainment_name ON `entertainment` (`name`);
CREATE INDEX idx_family_name ON `family` (`name`);
CREATE INDEX idx_general_name ON `general` (`name`);
CREATE INDEX idx_interactive_name ON `interactive` (`name`);
CREATE INDEX idx_kids_name ON `kids` (`name`);
CREATE INDEX idx_legislative_name ON `legislative` (`name`);
CREATE INDEX idx_lifestyle_name ON `lifestyle` (`name`);
CREATE INDEX idx_movies_name ON `movies` (`name`);
CREATE INDEX idx_music_name ON `music` (`name`);
CREATE INDEX idx_news_name ON `news` (`name`);
CREATE INDEX idx_outdoor_name ON `outdoor` (`name`);
CREATE INDEX idx_premimum_name ON `premimum` (`name`);
CREATE INDEX idx_public_name ON `public` (`name`);
CREATE INDEX idx_relax_name ON `relax` (`name`);
CREATE INDEX idx_religious_name ON `religious` (`name`);
CREATE INDEX idx_science_name ON `science` (`name`);
CREATE INDEX idx_series_name ON `series` (`name`);
CREATE INDEX idx_shop_name ON `shop` (`name`);
CREATE INDEX idx_sports_name ON `sports` (`name`);
CREATE INDEX idx_travel_name ON `travel` (`name`);
CREATE INDEX idx_undefined_name ON `undefined` (`name`);
CREATE INDEX idx_weather_name ON `weather` (`name`);
CREATE INDEX idx_xxx_name ON `xxx` (`name`);

-- ============================================================
-- SUPPORT DATABASE (Separate)
-- ============================================================
CREATE DATABASE IF NOT EXISTS `support_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `support_db`;

-- ============================================================
-- Support messages table
-- ============================================================
CREATE TABLE IF NOT EXISTS `support_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_support_created ON `support_messages` (`created_at`);
