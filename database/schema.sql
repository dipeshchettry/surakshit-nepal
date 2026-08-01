-- ============================================================
-- Surakshit Nepal — Database Schema
-- Run this in phpMyAdmin or via: mysql -u root surakshit_nepal < schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS `surakshit_nepal`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `surakshit_nepal`;

-- ----------------------------------------------------------
-- 1. cached_weather
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cached_weather` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `lat`          DECIMAL(10,6) NOT NULL,
  `lon`          DECIMAL(10,6) NOT NULL,
  `location_key` VARCHAR(30) NOT NULL COMMENT 'lat_lon truncated to 2dp',
  `data`         LONGTEXT NOT NULL COMMENT 'JSON payload from API',
  `source`       VARCHAR(50) NOT NULL DEFAULT 'openweathermap',
  `cached_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at`   DATETIME NOT NULL,
  INDEX `idx_location_key` (`location_key`),
  INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 2. alerts
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `alerts` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `alert_id`     VARCHAR(100) NOT NULL UNIQUE COMMENT 'external ID from USGS/GDACS',
  `type`         ENUM('earthquake','flood','landslide','lightning','storm',
                      'snowfall','heatwave','cold_wave','heavy_rain','other')
                 NOT NULL DEFAULT 'other',
  `title`        VARCHAR(255) NOT NULL,
  `severity`     ENUM('green','yellow','orange','red') NOT NULL DEFAULT 'yellow',
  `description`  TEXT,
  `affected_area` VARCHAR(255),
  `lat`          DECIMAL(10,6),
  `lon`          DECIMAL(10,6),
  `source`       VARCHAR(50) NOT NULL,
  `source_url`   VARCHAR(500),
  `expected_time` DATETIME,
  `safety_tips`  TEXT,
  `is_active`    TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_type` (`type`),
  INDEX `idx_severity` (`severity`),
  INDEX `idx_is_active` (`is_active`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 3. emergency_contacts
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `emergency_contacts` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category`     ENUM('police','ambulance','fire','disaster','hospital',
                      'electricity','water','search_rescue','other')
                 NOT NULL DEFAULT 'other',
  `name`         VARCHAR(150) NOT NULL,
  `name_ne`      VARCHAR(150) COMMENT 'Nepali name',
  `phone`        VARCHAR(30) NOT NULL,
  `phone_alt`    VARCHAR(30),
  `district`     VARCHAR(100) DEFAULT 'National',
  `address`      VARCHAR(255),
  `is_active`    TINYINT(1) NOT NULL DEFAULT 1,
  INDEX `idx_category` (`category`),
  INDEX `idx_district` (`district`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 4. locations
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `locations` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`         VARCHAR(150) NOT NULL,
  `name_ne`      VARCHAR(150),
  `district`     VARCHAR(100),
  `province`     VARCHAR(100),
  `lat`          DECIMAL(10,6) NOT NULL,
  `lon`          DECIMAL(10,6) NOT NULL,
  `risk_level`   ENUM('safe','low','medium','high') DEFAULT 'safe',
  `risk_types`   SET('flood','landslide','earthquake','avalanche') DEFAULT '',
  INDEX `idx_district` (`district`),
  INDEX `idx_risk_level` (`risk_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 5. settings (per-browser session; keyed by session token)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `session_key`  VARCHAR(64) NOT NULL UNIQUE,
  `language`     ENUM('en','ne') NOT NULL DEFAULT 'en',
  `theme`        ENUM('dark','light') NOT NULL DEFAULT 'dark',
  `units`        ENUM('metric','imperial') NOT NULL DEFAULT 'metric',
  `notifications_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `onesignal_player_id`   VARCHAR(100),
  `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 6. ai_logs
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ai_logs` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `location_key` VARCHAR(30) NOT NULL,
  `prompt`       TEXT NOT NULL,
  `response`     LONGTEXT,
  `risk_level`   ENUM('safe','low','medium','high') DEFAULT 'safe',
  `model`        VARCHAR(80) DEFAULT 'gemini-1.5-flash',
  `tokens_used`  INT UNSIGNED DEFAULT 0,
  `cached_until` DATETIME,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_location_key` (`location_key`),
  INDEX `idx_cached_until` (`cached_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Seed: Emergency Contacts (Nepal)
-- ----------------------------------------------------------
INSERT INTO `emergency_contacts`
  (`category`,`name`,`name_ne`,`phone`,`phone_alt`,`district`) VALUES
('police',    'Nepal Police Emergency',       'नेपाल प्रहरी',        '100', NULL,  'National'),
('ambulance', 'Ambulance Service',            'एम्बुलेन्स सेवा',     '102', NULL,  'National'),
('fire',      'Fire Brigade',                 'दमकल सेवा',           '101', NULL,  'National'),
('disaster',  'NDRRMA (Disaster Authority)',  'राष्ट्रिय विपद् जोखिम न्यूनीकरण','1149','01-4211954','National'),
('hospital',  'Bir Hospital Kathmandu',       'वीर अस्पताल',         '01-4221119','01-4221988','Kathmandu'),
('hospital',  'TUTH (Teaching Hospital)',     'शिक्षण अस्पताल',      '01-4412303', NULL,'Kathmandu'),
('police',    'Tourist Police',               'पर्यटन प्रहरी',       '1144', NULL, 'National'),
('search_rescue','Armed Police Force',        'सशस्त्र प्रहरी बल',   '103',  NULL, 'National'),
('electricity','Nepal Electricity Auth.',     'नेपाल विद्युत प्राधिकरण','1150',NULL,'National'),
('disaster',  'Red Cross Nepal',              'रातो क्रस नेपाल',     '01-4270650','01-4283864','National');
