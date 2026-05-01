-- RaketGo Database Schema - Unified Version
-- Created and managed by Moesoft (Moeko Software)
-- Last Updated: May 2026
-- 
-- This file contains the complete database schema including all tables and migrations.
-- Use this file to recreate the entire database from scratch.
--
-- For shared hosting (including InfinityFree), select your target database in phpMyAdmin before importing.
-- Example for local WAMP testing only:
-- CREATE DATABASE IF NOT EXISTS raketgo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE raketgo;

-- Drop existing tables if they exist (in correct order due to foreign keys)
DROP TABLE IF EXISTS trust_score_updates;
DROP TABLE IF EXISTS job_ratings;
DROP TABLE IF EXISTS employer_reviews;
DROP TABLE IF EXISTS worker_portfolio;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS user_interactions;
DROP TABLE IF EXISTS auth_rate_limits;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS skill_posts;
DROP TABLE IF EXISTS digital_contracts;
DROP TABLE IF EXISTS job_applications;
DROP TABLE IF EXISTS job_posts;
DROP TABLE IF EXISTS user_skills;
DROP TABLE IF EXISTS users;

-- Users table (for all three account types)
-- Includes company profile fields for enhanced employer profiles
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    mobile_number VARCHAR(15) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    user_type ENUM('admin', 'employer', 'worker') NOT NULL,
    -- Employer subtype: 'company' for businesses, 'individual' for personal/simple jobs
    employer_subtype ENUM('company', 'individual') DEFAULT NULL,
    full_name VARCHAR(100) NOT NULL,
    profile_picture VARCHAR(255),
    region VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    social_links TEXT,
    -- Employer profile bio/description
    bio TEXT,
    trust_score DECIMAL(3,2) DEFAULT 0.00,
    current_balance DECIMAL(10,2) DEFAULT 0.00,
    payment_method VARCHAR(255),
    payment_details TEXT,
    is_verified BOOLEAN DEFAULT FALSE,
    -- Company profile fields (for employer_subtype = 'company')
    company_name VARCHAR(255) DEFAULT NULL,
    company_size ENUM('1-10', '11-50', '51-200', '201-500', '500+') DEFAULT NULL,
    industry VARCHAR(100) DEFAULT NULL,
    company_website VARCHAR(255) DEFAULT NULL,
    year_founded YEAR DEFAULT NULL,
    company_logo VARCHAR(255) DEFAULT NULL,
    -- Onboarding and preferences
    onboarding_completed BOOLEAN DEFAULT FALSE,
    onboarding_completed_at TIMESTAMP NULL,
    theme_preference ENUM('light', 'dark', 'auto') DEFAULT 'auto',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    account_status ENUM('active', 'suspended', 'deleted') DEFAULT 'active',
    INDEX idx_user_type (user_type),
    INDEX idx_employer_subtype (employer_subtype),
    INDEX idx_location (region, province, city),
    INDEX idx_trust_score (trust_score),
    INDEX idx_industry (industry)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User skills table (many-to-many relationship with skill tags)
CREATE TABLE user_skills (
    skill_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    is_verified BOOLEAN DEFAULT FALSE,
    verification_document VARCHAR(255),
    verified_by INT,
    verified_at TIMESTAMP NULL,
    proficiency_level ENUM('beginner', 'intermediate', 'advanced', 'expert') DEFAULT 'beginner',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(user_id) ON DELETE SET NULL,
    UNIQUE KEY uniq_user_skill (user_id, skill_name),
    INDEX idx_user_skills (user_id),
    INDEX idx_skill_name (skill_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Worker portfolio table (for showcasing work samples)
CREATE TABLE worker_portfolio (
    portfolio_id INT PRIMARY KEY AUTO_INCREMENT,
    worker_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    image_path VARCHAR(255),
    project_url VARCHAR(500),
    skills_used VARCHAR(255),
    is_featured BOOLEAN DEFAULT FALSE,
    views_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (worker_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_worker (worker_id),
    INDEX idx_featured (is_featured, created_at),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Job posts table
CREATE TABLE job_posts (
    job_id INT PRIMARY KEY AUTO_INCREMENT,
    employer_id INT NOT NULL,
    job_title VARCHAR(200) NOT NULL,
    job_description TEXT NOT NULL,
    location_region VARCHAR(100) NOT NULL,
    location_province VARCHAR(100) NOT NULL,
    location_city VARCHAR(100) NOT NULL,
    specific_address TEXT,
    pay_amount DECIMAL(10,2) NOT NULL,
    pay_type ENUM('hourly', 'daily', 'fixed', 'monthly') NOT NULL,
    required_skills TEXT,
    preferred_skills TEXT,
    job_category VARCHAR(100),
    job_type ENUM('full_time','part_time','contractual','one_time','internship') NOT NULL DEFAULT 'one_time',
    start_date DATE,
    end_date DATE,
    slots_available INT DEFAULT 1,
    slots_filled INT DEFAULT 0,
    advance_payment_amount DECIMAL(10,2) DEFAULT 0.00,
    job_image VARCHAR(255),
    job_status ENUM('draft', 'active', 'in_progress', 'completed', 'cancelled') DEFAULT 'active',
    remote_policy ENUM('on_site','hybrid','fully_remote') DEFAULT 'on_site',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_employer (employer_id),
    INDEX idx_status (job_status),
    INDEX idx_location (location_region, location_province, location_city),
    INDEX idx_created (created_at),
    INDEX idx_status_region_created (job_status, location_region, created_at),
    INDEX idx_status_category_created (job_status, job_category, created_at),
    INDEX idx_status_pay_created (job_status, pay_amount, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Job applications table (includes resume_file for PDF uploads)
CREATE TABLE job_applications (
    application_id INT PRIMARY KEY AUTO_INCREMENT,
    job_id INT NOT NULL,
    worker_id INT NOT NULL,
    employer_id INT NOT NULL,
    application_status ENUM('pending', 'approved', 'rejected', 'withdrawn') DEFAULT 'pending',
    cover_letter TEXT,
    resume_file VARCHAR(255) NULL,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    worker_confirmed BOOLEAN DEFAULT FALSE,
    employer_confirmed BOOLEAN DEFAULT FALSE,
    work_start_time TIMESTAMP NULL,
    work_end_time TIMESTAMP NULL,
    contract_sent BOOLEAN DEFAULT FALSE,
    payment_released BOOLEAN DEFAULT FALSE,
    payment_completed BOOLEAN DEFAULT FALSE,
    -- Rating availability: NULL = immediate, TIMESTAMP = available after this date
    -- One-time jobs: immediate after both confirm + payment
    -- Long-term jobs: 3 days after both confirm + payment
    rating_available_at TIMESTAMP NULL,
    both_confirmed_at TIMESTAMP NULL,
    FOREIGN KEY (job_id) REFERENCES job_posts(job_id) ON DELETE CASCADE,
    FOREIGN KEY (worker_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (employer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_application (job_id, worker_id),
    INDEX idx_worker (worker_id),
    INDEX idx_employer (employer_id),
    INDEX idx_status (application_status),
    INDEX idx_employer_status (employer_id, application_status),
    INDEX idx_job_status (job_id, application_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Digital contracts table
CREATE TABLE digital_contracts (
    contract_id INT PRIMARY KEY AUTO_INCREMENT,
    application_id INT NOT NULL,
    contract_content TEXT NOT NULL,
    contract_terms TEXT,
    instructions TEXT,
    meeting_location TEXT,
    meeting_time TIMESTAMP NULL,
    worker_signed BOOLEAN DEFAULT FALSE,
    employer_signed BOOLEAN DEFAULT FALSE,
    worker_signed_at TIMESTAMP NULL,
    employer_signed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES job_applications(application_id) ON DELETE CASCADE,
    INDEX idx_application (application_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Skill learning posts (social media style)
CREATE TABLE skill_posts (
    post_id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    post_title VARCHAR(255) NOT NULL,
    post_content TEXT NOT NULL,
    post_type ENUM('certification', 'training', 'course', 'workshop') NOT NULL,
    link_url VARCHAR(500),
    thumbnail_image VARCHAR(255),
    category VARCHAR(100),
    tags TEXT,
    likes_count INT DEFAULT 0,
    views_count INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_admin (admin_id),
    INDEX idx_created (created_at),
    INDEX idx_type (post_type),
    INDEX idx_featured_created (is_featured, created_at),
    INDEX idx_type_featured_created (post_type, is_featured, created_at),
    INDEX idx_category_featured_created (category, is_featured, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Messages table
CREATE TABLE messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    job_id INT,
    message_content TEXT NOT NULL,
    attachment VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES job_posts(job_id) ON DELETE SET NULL,
    INDEX idx_sender (sender_id),
    INDEX idx_receiver (receiver_id),
    INDEX idx_conversation (sender_id, receiver_id),
    INDEX idx_receiver_unread (receiver_id, is_read),
    INDEX idx_sent (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications table
CREATE TABLE notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    notification_type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    related_id INT,
    related_type VARCHAR(50),
    is_read BOOLEAN DEFAULT FALSE,
    action_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_read (is_read),
    INDEX idx_user_read_created (user_id, is_read, created_at),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Authentication rate limits (used for login brute-force protection)
CREATE TABLE auth_rate_limits (
    rate_limit_id INT PRIMARY KEY AUTO_INCREMENT,
    throttle_key CHAR(64) NOT NULL,
    scope VARCHAR(50) NOT NULL,
    attempts INT NOT NULL DEFAULT 0,
    window_started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_attempt_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    locked_until TIMESTAMP NULL,
    UNIQUE KEY uniq_throttle_key (throttle_key),
    INDEX idx_scope (scope),
    INDEX idx_locked_until (locked_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User interactions table (for algorithm-based recommendations)
CREATE TABLE user_interactions (
    interaction_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    interaction_type ENUM('view', 'apply', 'save', 'like', 'share', 'click') NOT NULL,
    job_id INT,
    post_id INT,
    skill_tag VARCHAR(100),
    interaction_weight DECIMAL(3,2) DEFAULT 1.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES job_posts(job_id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES skill_posts(post_id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_type (interaction_type),
    INDEX idx_user_job_type (user_id, job_id, interaction_type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transactions table
CREATE TABLE transactions (
    transaction_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    transaction_type ENUM('deposit', 'withdrawal', 'payment', 'refund', 'advance') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    job_id INT,
    application_id INT,
    description TEXT,
    status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES job_posts(job_id) ON DELETE SET NULL,
    FOREIGN KEY (application_id) REFERENCES job_applications(application_id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_type (transaction_type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Job Ratings table (for 5-star mutual ratings between workers and employers)
CREATE TABLE job_ratings (
    rating_id INT PRIMARY KEY AUTO_INCREMENT,
    application_id INT NOT NULL UNIQUE,
    rater_id INT NOT NULL,
    ratee_id INT NOT NULL,
    rating_type ENUM('employer_to_worker', 'worker_to_employer') NOT NULL,
    rating_stars INT NOT NULL CHECK (rating_stars >= 1 AND rating_stars <= 5),
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES job_applications(application_id) ON DELETE CASCADE,
    FOREIGN KEY (rater_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (ratee_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_application (application_id),
    INDEX idx_ratee (ratee_id),
    INDEX idx_rater (rater_id),
    INDEX idx_rating_type (rating_type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trust Score Updates Audit table (for tracking all trust score changes)
CREATE TABLE trust_score_updates (
    update_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    old_score DECIMAL(3,2),
    new_score DECIMAL(3,2) NOT NULL,
    reason VARCHAR(100) NOT NULL,
    related_rating_id INT,
    triggered_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (related_rating_id) REFERENCES job_ratings(rating_id) ON DELETE SET NULL,
    FOREIGN KEY (triggered_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_reason (reason),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notification Preferences table
CREATE TABLE notification_preferences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    email_notifications BOOLEAN DEFAULT TRUE,
    sms_notifications BOOLEAN DEFAULT FALSE,
    job_alerts BOOLEAN DEFAULT TRUE,
    message_notifications BOOLEAN DEFAULT TRUE,
    marketing_emails BOOLEAN DEFAULT FALSE,
    quiet_hours_start TIME NULL,
    quiet_hours_end TIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Employer Reviews table (for public employer profile reviews)
CREATE TABLE employer_reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    employer_id INT NOT NULL,
    worker_id INT NOT NULL,
    job_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    is_public BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (worker_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES job_posts(job_id) ON DELETE CASCADE,
    UNIQUE KEY uniq_worker_employer_job (worker_id, employer_id, job_id),
    INDEX idx_employer (employer_id),
    INDEX idx_worker (worker_id),
    INDEX idx_rating (rating),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- END OF SCHEMA
-- ============================================================================

-- Optional: create your first admin account manually after import.
-- IMPORTANT: generate your own strong password hash and never keep default credentials in production.
-- Example:
-- INSERT INTO users (mobile_number, email, password_hash, user_type, full_name, region, province, city)
-- VALUES ('09XXXXXXXXX', 'admin@example.com', '$2y$10$replace_with_real_hash', 'admin', 'Platform Administrator', 'NCR', 'Metro Manila', 'Manila');

-- ============================================================================
-- NOTES FOR DEVELOPERS
-- ============================================================================
--
-- This unified schema includes:
-- 1. All original tables from the base schema
-- 2. worker_portfolio table - for worker portfolio items (May 2026)
-- 3. Company profile columns in users table - for enhanced employer profiles (May 2026):
--    - company_name
--    - company_size
--    - industry
--    - company_website
--    - year_founded
--    - company_logo
--
-- Upload directories (auto-created by config/config.php):
-- - uploads/profiles/ - User profile pictures
-- - uploads/jobs/ - Job images
-- - uploads/documents/ - General documents
-- - uploads/posts/ - Skill post thumbnails
-- - uploads/resumes/ - Worker resume PDFs
-- - uploads/portfolio/ - Worker portfolio images
-- - uploads/company_logos/ - Company logos
--
-- To use this schema:
-- 1. Select your database in phpMyAdmin or your MySQL client
-- 2. Import this file
-- 3. The tables will be created with proper indexes and foreign keys
-- 4. No need to run separate migration files
