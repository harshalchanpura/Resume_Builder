CREATE DATABASE IF NOT EXISTS resume_builder CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE resume_builder;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS resumes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    resume_name VARCHAR(160) NOT NULL DEFAULT 'My Resume',
    full_name VARCHAR(160) DEFAULT '',
    headline VARCHAR(200) DEFAULT '',
    email VARCHAR(180) DEFAULT '',
    phone VARCHAR(50) DEFAULT '',
    address VARCHAR(255) DEFAULT '',
    location VARCHAR(120) DEFAULT '',
    target_job VARCHAR(150) DEFAULT '',
    region VARCHAR(20) DEFAULT 'india',
    layout VARCHAR(30) DEFAULT 'split',
    accent VARCHAR(20) DEFAULT '#d97706',
    font_family VARCHAR(120) DEFAULT 'DM Sans',
    font_size DECIMAL(4,1) DEFAULT 10.5,
    sidebar_font_size DECIMAL(4,1) DEFAULT 10.5,
    main_font_size DECIMAL(4,1) DEFAULT 10.5,
    font_weight VARCHAR(10) DEFAULT '400',
    font_style VARCHAR(20) DEFAULT 'normal',
    line_height DECIMAL(3,2) DEFAULT 1.45,
    letter_spacing DECIMAL(4,2) DEFAULT 0,
    summary TEXT,
    career_objective TEXT,
    academic_qualification TEXT,
    education TEXT,
    skills TEXT,
    experience TEXT,
    projects TEXT,
    certifications TEXT,
    achievements TEXT,
    languages TEXT,
    hobbies TEXT,
    linkedin VARCHAR(255) DEFAULT '',
    github VARCHAR(255) DEFAULT '',
    portfolio VARCHAR(255) DEFAULT '',
    photo VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_resumes_user_id (user_id),
    CONSTRAINT fk_resume_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tab_sessions (
    token VARCHAR(64) PRIMARY KEY,
    user_id INT NOT NULL,
    expires_at DATETIME NOT NULL,
    INDEX(user_id),
    INDEX(expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS job_alerts (id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NULL,email VARCHAR(180) NOT NULL,role VARCHAR(150) NOT NULL,location VARCHAR(150) DEFAULT '',frequency VARCHAR(30) DEFAULT 'weekly',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(email),INDEX(user_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cover_letters (id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,recipient VARCHAR(180) DEFAULT '',company VARCHAR(180) DEFAULT '',role VARCHAR(180) DEFAULT '',opening TEXT,body TEXT,closing TEXT,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,CONSTRAINT fk_cover_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
