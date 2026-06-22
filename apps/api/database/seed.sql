-- apps/api/database/seed.sql

-- Clear existing data
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `water_usage_logs`;
TRUNCATE TABLE `area_schedule_rules`;
TRUNCATE TABLE `area_condition_rules`;
TRUNCATE TABLE `alerts`;
TRUNCATE TABLE `area_aggregations`;
TRUNCATE TABLE `sensor_logs`;
TRUNCATE TABLE `actuators`;
TRUNCATE TABLE `sensors`;
TRUNCATE TABLE `areas`;
TRUNCATE TABLE `data_requests`;
TRUNCATE TABLE `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Users (Password for all is 'password123')
INSERT INTO `users` (`username`, `email`, `password_hash`, `auth_key`, `status`, `created_at`, `updated_at`, `full_name`, `role`) VALUES
('admin', 'admin@isurf.local', '$2y$13$GpSZwptusVqUqjURiwKO.edMcGWQw1kgqY/Mlj/RIstaXWgAeGXtW', 'auth_key_admin_1', 10, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'System Administrator', 'admin'),
('operator1', 'operator@isurf.local', '$2y$13$GpSZwptusVqUqjURiwKO.edMcGWQw1kgqY/Mlj/RIstaXWgAeGXtW', 'auth_key_operator_2', 10, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Greenhouse Operator', 'operator'),
('viewer1', 'viewer@isurf.local', '$2y$13$GpSZwptusVqUqjURiwKO.edMcGWQw1kgqY/Mlj/RIstaXWgAeGXtW', 'auth_key_viewer_3', 10, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Guest Viewer', 'viewer');

-- 2. Areas
INSERT INTO `areas` (`id`, `name`, `plant`, `description`) VALUES
(1, 'Greenhouse A (Hidroponik NFT)', 'Selada Air, Pakcoy, Kangkung', 'Fokus pada sayuran daun dengan sistem Nutrient Film Technique (NFT). Terdiri dari 5 rak utama.'),
(2, 'Greenhouse B (Soil-based)', 'Tomat Cherry, Paprika, Cabai', 'Budidaya sayuran buah dengan media tanah konvensional dan sistem irigasi tetes (Drip Irrigation).'),
(3, 'Greenhouse C (Aeroponik)', 'Kentang Granola, Mint', 'Sistem budidaya aeroponik untuk umbi-umbian dan herbal. Akar menggantung dan disemprot nutrisi bertekanan.');

-- 3. Sensors
INSERT INTO `sensors` (`id`, `area_id`, `name`, `data_type`, `min_threshold`, `max_threshold`, `is_online`) VALUES
('DHT-GH1-01', 1, 'Sensor Suhu & Kelembaban Udara 1', 'Suhu Udara', 18.0, 28.0, 1),
('TDS-GH1-01', 1, 'Sensor Nutrisi TDS/EC Tandon', 'TDS Nutrisi', 800.0, 1200.0, 1),
('PH-GH1-01', 1, 'Sensor pH Air Tandon', 'pH Air', 5.5, 6.5, 1),
('MST-GH2-01', 2, 'Sensor Kelembaban Tanah A', 'Kelembaban Tanah', 40.0, 80.0, 1),
('TMP-GH3-01', 3, 'Sensor Suhu Ruang Akar', 'Suhu Akar', 15.0, 22.0, 1);

-- 4. Actuators
INSERT INTO `actuators` (`id`, `area_id`, `name`, `valve_status`, `is_auto_enabled`, `flow_rate_per_sec`) VALUES
('PMP-GH1-01', 1, 'Pompa Sirkulasi NFT Utama', 'ON', 1, 1.5),
('PMP-GH2-01', 2, 'Pompa Irigasi Tetes', 'OFF', 1, 2.0),
('PMP-GH3-01', 3, 'Pompa High-Pressure Aeroponik', 'ON', 1, 0.8);

-- 5. Sample Sensor Logs
INSERT INTO `sensor_logs` (`sensor_id`, `date`, `time`, `reading`, `status`) VALUES
('DHT-GH1-01', CURDATE(), CURTIME(), 24.5, 'Normal'),
('TDS-GH1-01', CURDATE(), CURTIME(), 950.0, 'Normal'),
('PH-GH1-01', CURDATE(), CURTIME(), 6.2, 'Normal'),
('MST-GH2-01', CURDATE(), CURTIME(), 65.0, 'Normal'),
('TMP-GH3-01', CURDATE(), CURTIME(), 20.5, 'Normal');

-- 6. Sample Alerts
INSERT INTO `alerts` (`sensor_id`, `alert_type`, `message`, `value`, `threshold_exceeded`, `is_read`) VALUES
('MST-GH2-01', 'Critical', 'Kelembaban Tanah turun drastis di bawah 40%.', 35.5, 4.5, 0),
('PH-GH1-01', 'Warning', 'pH Air naik mencapai 6.8', 6.8, 0.3, 1);