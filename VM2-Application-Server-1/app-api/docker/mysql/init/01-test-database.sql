CREATE DATABASE IF NOT EXISTS app_api_test
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON app_api_test.* TO 'api'@'%';

FLUSH PRIVILEGES;
