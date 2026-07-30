CREATE DATABASE IF NOT EXISTS monitor_test
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON monitor_test.* TO 'monitor'@'%';

FLUSH PRIVILEGES;
