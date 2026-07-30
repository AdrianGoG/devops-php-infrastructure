CREATE DATABASE IF NOT EXISTS filemanager_test
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON filemanager_test.* TO 'filemanager'@'%';

FLUSH PRIVILEGES;
