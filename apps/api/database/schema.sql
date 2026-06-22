-- apps/api/database/schema.sql
-- Database: yii2advanced (or any other configured database)

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `areas`;
DROP TABLE IF EXISTS `sensors`;
DROP TABLE IF EXISTS `actuators`;
DROP TABLE IF EXISTS `sensor_logs`;
DROP TABLE IF EXISTS `area_aggregations`;
DROP TABLE IF EXISTS `alerts`;
DROP TABLE IF EXISTS `area_condition_rules`;
DROP TABLE IF EXISTS `area_schedule_rules`;
DROP TABLE IF EXISTS `water_usage_logs`;
DROP TABLE IF EXISTS `data_requests`;
DROP TABLE IF EXISTS `plants`;

-- 1. Users
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(255) NOT NULL UNIQUE,
  `auth_key` VARCHAR(32) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `password_reset_token` VARCHAR(255) UNIQUE,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `status` SMALLINT NOT NULL DEFAULT 10,
  `created_at` INT NOT NULL,
  `updated_at` INT NOT NULL,
  `verification_token` VARCHAR(255) DEFAULT NULL,
  `role` VARCHAR(50) DEFAULT 'viewer',
  `full_name` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 2. Areas
CREATE TABLE `areas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `plant` VARCHAR(100),
  `description` VARCHAR(500),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 3. Sensors
CREATE TABLE `sensors` (
  `id` VARCHAR(50) PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `data_type` VARCHAR(100) NOT NULL,
  `min_threshold` FLOAT,
  `max_threshold` FLOAT,
  `is_online` BOOLEAN DEFAULT TRUE,
  `area_id` INT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`area_id`) REFERENCES `areas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 4. Actuators
CREATE TABLE `actuators` (
  `id` VARCHAR(50) PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `valve_status` VARCHAR(20) DEFAULT 'OFF',
  `is_auto_enabled` BOOLEAN DEFAULT TRUE,
  `flow_rate_per_sec` FLOAT DEFAULT 0.0,
  `area_id` INT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`area_id`) REFERENCES `areas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 5. Sensor Logs
CREATE TABLE `sensor_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sensor_id` VARCHAR(50),
  `date` DATE NOT NULL,
  `time` TIME NOT NULL,
  `reading` FLOAT NOT NULL,
  `anomalies` BOOLEAN DEFAULT FALSE,
  `status` VARCHAR(50) DEFAULT 'Normal',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`sensor_id`) REFERENCES `sensors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 6. Area Aggregations
CREATE TABLE `area_aggregations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `area_id` INT,
  `date` DATE NOT NULL,
  `time` TIME NOT NULL,
  `data_type` VARCHAR(100) NOT NULL,
  `min_value` FLOAT,
  `max_value` FLOAT,
  `avg_value` FLOAT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`area_id`) REFERENCES `areas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 7. Alerts
CREATE TABLE `alerts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sensor_id` VARCHAR(50),
  `alert_type` VARCHAR(50) NOT NULL,
  `message` VARCHAR(255) NOT NULL,
  `value` FLOAT,
  `threshold_exceeded` FLOAT,
  `is_read` BOOLEAN DEFAULT FALSE,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` DATETIME,
  FOREIGN KEY (`sensor_id`) REFERENCES `sensors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 8. Area Condition Rules (Irrigation Automation)
CREATE TABLE `area_condition_rules` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `area_id` INT,
  `data_type` VARCHAR(100) NOT NULL,
  `operator` VARCHAR(10) NOT NULL,
  `value` FLOAT NOT NULL,
  `action` VARCHAR(20) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`area_id`) REFERENCES `areas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 9. Area Schedule Rules (Irrigation Scheduling)
CREATE TABLE `area_schedule_rules` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `area_id` INT,
  `time` TIME NOT NULL,
  `action` VARCHAR(20) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`area_id`) REFERENCES `areas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 10. Water Usage Logs
CREATE TABLE `water_usage_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `actuator_id` VARCHAR(50),
  `timestamp` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `water_discharged` FLOAT NOT NULL,
  `water_remaining` FLOAT NOT NULL,
  FOREIGN KEY (`actuator_id`) REFERENCES `actuators`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 11. Data Requests
CREATE TABLE `data_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tracking_code` VARCHAR(50) UNIQUE NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `nim_nip` VARCHAR(50),
  `reason` VARCHAR(500) NOT NULL,
  `document_path` VARCHAR(255) NOT NULL,
  `data_type` VARCHAR(50) NOT NULL,
  `requested_sensors` JSON,
  `date_start` DATE NOT NULL,
  `date_end` DATE NOT NULL,
  `status` VARCHAR(20) DEFAULT 'pending',
  `admin_notes` VARCHAR(500),
  `download_token` VARCHAR(100),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 12. Plants
CREATE TABLE `plants` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `image_path` VARCHAR(255),
  `description` VARCHAR(500),
  `optimal_temperature` FLOAT,
  `optimal_moisture` FLOAT,
  `optimal_light` FLOAT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;
