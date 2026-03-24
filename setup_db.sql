CREATE DATABASE IF NOT EXISTS malaban_db;
USE malaban_db;

CREATE TABLE IF NOT EXISTS users (
    school_id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    school VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS user_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id VARCHAR(50),
    story_id VARCHAR(50),
    retakes INT DEFAULT 0,
    completed TINYINT(1) DEFAULT 0,
    last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES users(school_id) ON DELETE CASCADE,
    UNIQUE KEY unique_prog (school_id, story_id)
);
