CREATE DATABASE IF NOT EXISTS user_dashboard_test
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON user_dashboard_test.* TO 'dashboard'@'%';

FLUSH PRIVILEGES;
