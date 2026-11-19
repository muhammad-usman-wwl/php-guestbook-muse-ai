-- SDCC Guestbook — database schema
-- Import with:
--   mariadb -h 127.0.0.1 -P 3307 -u root < schema.sql

CREATE DATABASE IF NOT EXISTS v5_guestbook;
USE v5_guestbook;

CREATE TABLE IF NOT EXISTS entries (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(50),
    message    VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
