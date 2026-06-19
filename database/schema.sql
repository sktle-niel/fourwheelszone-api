-- ============================================================
--  Four Wheels Zone — reviews schema (import-ready for Hostinger)
--
--  On Hostinger the database is already created for you in hPanel
--  (with a prefixed name like u123456_fourwheelszone), so this file
--  does NOT run CREATE DATABASE / USE. Just select your database in
--  phpMyAdmin and import this file to create the table + seed rows.
-- ============================================================

CREATE TABLE IF NOT EXISTS `reviews` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(80)  NOT NULL,
  `vehicle`    VARCHAR(80)  DEFAULT NULL,
  `rating`     TINYINT UNSIGNED NOT NULL,
  `comment`    TEXT         NOT NULL,
  `status`     ENUM('approved','pending','hidden') NOT NULL DEFAULT 'approved',
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status_created` (`status`, `created_at`),
  CONSTRAINT `chk_rating` CHECK (`rating` BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- No seed/dummy data. The reviews table starts empty and is filled only by
-- real customer submissions through the API.
