CREATE DATABASE IF NOT EXISTS tickets_test
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON tickets_test.* TO 'tickets'@'%';

FLUSH PRIVILEGES;
