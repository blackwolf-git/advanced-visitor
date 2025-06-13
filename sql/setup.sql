-- جدول الزوار الأساسي
CREATE TABLE `visitors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `visitor_id` varchar(32) NOT NULL,
  `first_visit` datetime NOT NULL,
  `last_visit` datetime NOT NULL,
  `visit_count` int(11) NOT NULL DEFAULT 1,
  `user_agent` text NOT NULL,
  `platform` varchar(255) DEFAULT NULL,
  `device_type` varchar(50) DEFAULT NULL,
  `screen_resolution` varchar(20) DEFAULT NULL,
  `language` varchar(10) DEFAULT NULL,
  `timezone` varchar(50) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `org` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `visitor_id` (`visitor_id`),
  KEY `last_visit` (`last_visit`),
  KEY `country` (`country`),
  KEY `device_type` (`device_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- جدول الجلسات
CREATE TABLE `sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `visitor_id` int(11) NOT NULL,
  `session_start` datetime NOT NULL,
  `session_end` datetime NOT NULL,
  `page_url` text NOT NULL,
  `page_title` text DEFAULT NULL,
  `referrer` text DEFAULT NULL,
  `active_duration` int(11) NOT NULL DEFAULT 0 COMMENT 'in milliseconds',
  `inactive_duration` int(11) NOT NULL DEFAULT 0 COMMENT 'in milliseconds',
  `page_load_time` int(11) DEFAULT NULL COMMENT 'in milliseconds',
  `dom_ready_time` int(11) DEFAULT NULL COMMENT 'in milliseconds',
  `network_latency` int(11) DEFAULT NULL COMMENT 'in milliseconds',
  PRIMARY KEY (`id`),
  KEY `visitor_id` (`visitor_id`),
  KEY `session_start` (`session_start`),
  CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`visitor_id`) REFERENCES `visitors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- جدول بصمات الأجهزة
CREATE TABLE `device_fingerprints` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `visitor_id` int(11) NOT NULL,
  `hardware_concurrency` int(11) DEFAULT NULL,
  `device_memory` int(11) DEFAULT NULL COMMENT 'in GB',
  `color_depth` int(11) DEFAULT NULL,
  `pixel_depth` int(11) DEFAULT NULL,
  `cookie_enabled` tinyint(1) DEFAULT NULL,
  `java_enabled` tinyint(1) DEFAULT NULL,
  `do_not_track` varchar(10) DEFAULT NULL,
  `touch_support` tinyint(1) DEFAULT NULL,
  `canvas_fingerprint` text DEFAULT NULL,
  `webgl_vendor` varchar(255) DEFAULT NULL,
  `webgl_renderer` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `visitor_id` (`visitor_id`),
  CONSTRAINT `device_fingerprints_ibfk_1` FOREIGN KEY (`visitor_id`) REFERENCES `visitors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- جدول حركات الماوس
CREATE TABLE `mouse_movements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `visitor_id` int(11) NOT NULL,
  `movement_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`movement_data`)),
  `recorded_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `visitor_id` (`visitor_id`),
  CONSTRAINT `mouse_movements_ibfk_1` FOREIGN KEY (`visitor_id`) REFERENCES `visitors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- جدول النقرات
CREATE TABLE `clicks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `visitor_id` int(11) NOT NULL,
  `click_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`click_data`)),
  `recorded_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `visitor_id` (`visitor_id`),
  CONSTRAINT `clicks_ibfk_1` FOREIGN KEY (`visitor_id`) REFERENCES `visitors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- جدول التمرير
CREATE TABLE `scrolls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `visitor_id` int(11) NOT NULL,
  `scroll_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`scroll_data`)),
  `recorded_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `visitor_id` (`visitor_id`),
  CONSTRAINT `scrolls_ibfk_1` FOREIGN KEY (`visitor_id`) REFERENCES `visitors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- جدول ضغطات المفاتيح
CREATE TABLE `key_presses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `visitor_id` int(11) NOT NULL,
  `key_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`key_data`)),
  `recorded_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `visitor_id` (`visitor_id`),
  CONSTRAINT `key_presses_ibfk_1` FOREIGN KEY (`visitor_id`) REFERENCES `visitors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
