-- =================================================================
-- RAKETGO + RAKETKO UNIFIED DATABASE SCHEMA
-- Complete Job Matching + Social Media Platform
-- Created and managed by Moesoft (Moeko Software)
-- Last Updated: May 2026
-- =================================================================

CREATE DATABASE raketgo;
use raketgo;

-- Set up database character set
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =================================================================
-- DROP EXISTING TABLES (in correct order due to foreign keys)
-- =================================================================

DROP TABLE IF EXISTS unified_user_analytics;
DROP TABLE IF EXISTS trending_content;
DROP TABLE IF EXISTS cross_platform_shares;
DROP TABLE IF EXISTS career_milestones;
DROP TABLE IF EXISTS job_social_engagement;
DROP TABLE IF EXISTS user_unified_skills;
DROP TABLE IF EXISTS unified_skills;
DROP TABLE IF EXISTS cross_platform_activities;
DROP TABLE IF EXISTS unified_notifications;
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
DROP TABLE IF EXISTS social_comment_likes;
DROP TABLE IF EXISTS social_post_shares;
DROP TABLE IF EXISTS social_post_comments;
DROP TABLE IF EXISTS social_post_likes;
DROP TABLE IF EXISTS social_post_hashtags;
DROP TABLE IF EXISTS social_posts;
DROP TABLE IF EXISTS social_connections;
DROP TABLE IF EXISTS social_profiles;
DROP TABLE IF EXISTS social_notifications;
DROP TABLE IF EXISTS social_analytics;
DROP TABLE IF EXISTS user_activity_feed;
DROP TABLE IF EXISTS content_moderation;
DROP TABLE IF EXISTS trending_topics;
DROP TABLE IF EXISTS users;

-- =================================================================
-- CORE USER TABLE (Enhanced for both platforms)
-- =================================================================

CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    mobile_number VARCHAR(15) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    user_type ENUM('admin', 'employer', 'worker') NOT NULL,
    employer_subtype ENUM('company', 'individual') DEFAULT NULL,
    full_name VARCHAR(100) NOT NULL,
    profile_picture VARCHAR(255),
    region VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    social_links TEXT,
    bio TEXT,
    trust_score DECIMAL(3,2) DEFAULT 0.00,
    current_balance DECIMAL(10,2) DEFAULT 0.00,
    payment_method VARCHAR(255),
    company_logo VARCHAR(255),
    company_name VARCHAR(255),
    company_website VARCHAR(500),
    company_description TEXT,
    company_industry VARCHAR(100),
    company_size VARCHAR(50),
    hq_address TEXT,
    -- Social media integration fields
    social_profile_id INT NULL,
    social_bio TEXT NULL,
    social_headline VARCHAR(255) NULL,
    social_location VARCHAR(255) NULL,
    social_website VARCHAR(500) NULL,
    social_linkedin_url VARCHAR(500) NULL,
    social_skills JSON NULL,
    social_interests JSON NULL,
    social_followers_count INT DEFAULT 0,
    social_following_count INT DEFAULT 0,
    social_posts_count INT DEFAULT 0,
    social_verified BOOLEAN DEFAULT FALSE,
    social_verification_badge VARCHAR(50) NULL,
    social_privacy_settings JSON NULL,
    social_notification_settings JSON NULL,
    social_last_activity TIMESTAMP NULL,
    social_engagement_rate DECIMAL(5,2) DEFAULT 0.00,
    -- Account management
    account_status ENUM('active', 'suspended', 'deleted') DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    mobile_verified BOOLEAN DEFAULT FALSE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_mobile (mobile_number),
    INDEX idx_email (email),
    INDEX idx_user_type (user_type),
    INDEX idx_location (region, province, city),
    INDEX idx_account_status (account_status),
    INDEX idx_trust_score (trust_score),
    INDEX idx_created_at (created_at),
    INDEX idx_social_profile (social_profile_id)
);

-- =================================================================
-- RAKETKO SOCIAL MEDIA TABLES
-- =================================================================

-- Social Profiles Table
CREATE TABLE social_profiles (
    profile_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    bio TEXT,
    headline VARCHAR(255),
    location VARCHAR(255),
    website VARCHAR(500),
    linkedin_url VARCHAR(500),
    skills JSON,
    interests JSON,
    experience JSON,
    education JSON,
    certifications JSON,
    followers_count INT DEFAULT 0,
    following_count INT DEFAULT 0,
    posts_count INT DEFAULT 0,
    is_verified BOOLEAN DEFAULT FALSE,
    verification_badge VARCHAR(50),
    privacy_settings JSON,
    notification_settings JSON,
    -- Integration with RaketGo
    raketgo_profile_sync BOOLEAN DEFAULT TRUE,
    job_seeking_status ENUM('active', 'passive', 'not_seeking') DEFAULT 'passive',
    current_job_title VARCHAR(255) NULL,
    current_company VARCHAR(255) NULL,
    career_goals TEXT NULL,
    industry_focus JSON NULL,
    work_preference ENUM('remote', 'onsite', 'hybrid', 'flexible') NULL,
    salary_expectation_min DECIMAL(10,2) NULL,
    salary_expectation_max DECIMAL(10,2) NULL,
    availability_status ENUM('immediately', '2_weeks', '1_month', '3_months', 'not_available') NULL,
    cover_photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_verification (is_verified, verification_badge),
    INDEX idx_followers_count (followers_count DESC),
    INDEX idx_job_seeking (job_seeking_status, availability_status)
);

-- Social Posts Table
CREATE TABLE social_posts (
    post_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    post_type ENUM('career_update', 'achievement', 'insight', 'job_posting', 'company_news', 'professional_tip', 'industry_news', 'question') NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    media_urls JSON,
    hashtags JSON,
    mentions JSON,
    visibility ENUM('public', 'connections', 'private') DEFAULT 'public',
    is_pinned BOOLEAN DEFAULT FALSE,
    is_featured BOOLEAN DEFAULT FALSE,
    likes_count INT DEFAULT 0,
    comments_count INT DEFAULT 0,
    shares_count INT DEFAULT 0,
    views_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_posts (user_id, created_at),
    INDEX idx_post_type (post_type, created_at),
    INDEX idx_visibility (visibility, created_at),
    INDEX idx_featured (is_featured, created_at),
    INDEX idx_hashtags (hashtags(255)),
    FULLTEXT idx_content_search (title, content)
);

-- Post Likes Table
CREATE TABLE social_post_likes (
    like_id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (post_id) REFERENCES social_posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_post_like (post_id, user_id),
    INDEX idx_post_likes (post_id),
    INDEX idx_user_likes (user_id)
);

-- Post Comments Table
CREATE TABLE social_post_comments (
    comment_id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    parent_comment_id INT NULL,
    content TEXT NOT NULL,
    media_urls JSON,
    mentions JSON,
    likes_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (post_id) REFERENCES social_posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (parent_comment_id) REFERENCES social_post_comments(comment_id) ON DELETE CASCADE,
    INDEX idx_post_comments (post_id, created_at),
    INDEX idx_user_comments (user_id, created_at),
    INDEX idx_parent_comment (parent_comment_id)
);

-- Comment Likes Table
CREATE TABLE social_comment_likes (
    like_id INT PRIMARY KEY AUTO_INCREMENT,
    comment_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (comment_id) REFERENCES social_post_comments(comment_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_comment_like (comment_id, user_id),
    INDEX idx_comment_likes (comment_id),
    INDEX idx_user_comment_likes (user_id)
);

-- User Connections/Follows Table
CREATE TABLE social_connections (
    connection_id INT PRIMARY KEY AUTO_INCREMENT,
    follower_id INT NOT NULL,
    following_id INT NOT NULL,
    connection_type ENUM('follow', 'connect', 'mentor', 'colleague') DEFAULT 'follow',
    status ENUM('pending', 'accepted', 'blocked') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (follower_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_connection (follower_id, following_id),
    CHECK (follower_id != following_id),
    INDEX idx_following (following_id, status),
    INDEX idx_followers (follower_id, status)
);

-- Post Shares Table
CREATE TABLE social_post_shares (
    share_id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    share_type ENUM('repost', 'share_to_connections', 'share_external') DEFAULT 'repost',
    share_comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (post_id) REFERENCES social_posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_post_share (post_id, user_id),
    INDEX idx_post_shares (post_id),
    INDEX idx_user_shares (user_id)
);

-- Social Notifications Table
CREATE TABLE social_notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type ENUM('like', 'comment', 'share', 'follow', 'mention', 'comment_like', 'post_featured') NOT NULL,
    actor_id INT NOT NULL,
    target_id INT,
    target_type ENUM('post', 'comment', 'user') NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (actor_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_notifications (user_id, is_read, created_at),
    INDEX idx_type_notifications (type, created_at)
);

-- Trending Topics Table
CREATE TABLE trending_topics (
    topic_id INT PRIMARY KEY AUTO_INCREMENT,
    hashtag VARCHAR(100) NOT NULL UNIQUE,
    display_name VARCHAR(255),
    description TEXT,
    category VARCHAR(50),
    usage_count INT DEFAULT 0,
    trending_score DECIMAL(10,2) DEFAULT 0.00,
    is_trending BOOLEAN DEFAULT FALSE,
    last_trending_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_hashtag (hashtag),
    INDEX idx_trending (is_trending, trending_score DESC),
    INDEX idx_category (category, usage_count DESC)
);

-- Post Hashtag Relationships
CREATE TABLE social_post_hashtags (
    post_id INT NOT NULL,
    hashtag VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (post_id) REFERENCES social_posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (hashtag) REFERENCES trending_topics(hashtag) ON UPDATE CASCADE,
    PRIMARY KEY (post_id, hashtag),
    INDEX idx_hashtag_posts (hashtag, created_at)
);

-- Content Moderation Table
CREATE TABLE content_moderation (
    moderation_id INT PRIMARY KEY AUTO_INCREMENT,
    content_type ENUM('post', 'comment') NOT NULL,
    content_id INT NOT NULL,
    reporter_id INT NOT NULL,
    reason ENUM('spam', 'inappropriate', 'harassment', 'misinformation', 'copyright', 'other') NOT NULL,
    description TEXT,
    status ENUM('pending', 'reviewed', 'resolved', 'dismissed') DEFAULT 'pending',
    moderator_id INT NULL,
    moderator_action ENUM('remove_content', 'warn_user', 'suspend_user', 'no_action') NULL,
    action_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    
    FOREIGN KEY (reporter_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (moderator_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_content_moderation (content_type, content_id, status),
    INDEX idx_reporter_moderation (reporter_id, status),
    INDEX idx_moderator_review (moderator_id, status)
);

-- User Activity Feed Table
CREATE TABLE user_activity_feed (
    feed_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    activity_type ENUM('post', 'like', 'comment', 'share', 'follow') NOT NULL,
    actor_id INT NOT NULL,
    target_id INT,
    target_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (actor_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_feed (user_id, created_at DESC),
    INDEX idx_activity_type (activity_type, created_at DESC)
);

-- Social Analytics Table
CREATE TABLE social_analytics (
    analytics_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    posts_created INT DEFAULT 0,
    likes_received INT DEFAULT 0,
    comments_received INT DEFAULT 0,
    shares_received INT DEFAULT 0,
    new_followers INT DEFAULT 0,
    profile_views INT DEFAULT 0,
    engagement_rate DECIMAL(5,2) DEFAULT 0.00,
    reach_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_analytics (user_id, date),
    INDEX idx_date_analytics (date DESC),
    INDEX idx_user_analytics_date (user_id, date DESC)
);

-- =================================================================
-- RAKETGO JOB MATCHING TABLES
-- =================================================================

-- User Skills Table
CREATE TABLE user_skills (
    skill_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    proficiency_level ENUM('beginner', 'intermediate', 'advanced', 'expert') NOT NULL,
    years_experience DECIMAL(3,1) DEFAULT 0,
    verified BOOLEAN DEFAULT FALSE,
    verification_source ENUM('self_declared', 'employer_verified', 'certification', 'portfolio_proof') DEFAULT 'self_declared',
    last_used DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_skill (user_id, skill_name),
    INDEX idx_user_skills (user_id, proficiency_level),
    INDEX idx_skill_name (skill_name),
    INDEX idx_verified_skills (verified, verification_source)
);

-- Job Posts Table
CREATE TABLE job_posts (
    job_id INT PRIMARY KEY AUTO_INCREMENT,
    employer_id INT NOT NULL,
    job_title VARCHAR(255) NOT NULL,
    job_description TEXT NOT NULL,
    job_requirements TEXT,
    job_category VARCHAR(100) NOT NULL,
    job_type ENUM('full_time', 'part_time', 'contract', 'freelance', 'internship', 'temporary') NOT NULL,
    pay_type ENUM('hourly', 'daily', 'fixed', 'monthly', 'commission') NOT NULL,
    pay_amount DECIMAL(10,2) NOT NULL,
    pay_currency VARCHAR(3) DEFAULT 'PHP',
    location_region VARCHAR(100) NOT NULL,
    location_province VARCHAR(100) NOT NULL,
    location_city VARCHAR(100) NOT NULL,
    location_remote_policy ENUM('on_site', 'hybrid', 'fully_remote') DEFAULT 'on_site',
    slots_available INT NOT NULL DEFAULT 1,
    slots_filled INT NOT NULL DEFAULT 0,
    work_hours VARCHAR(100),
    work_days VARCHAR(100),
    duration_days INT,
    experience_required ENUM('entry_level', 'junior', 'mid_level', 'senior', 'executive') NOT NULL,
    education_required ENUM('high_school', 'college', 'bachelor', 'master', 'phd') DEFAULT 'high_school',
    skills_required TEXT,
    benefits_offered TEXT,
    application_deadline DATE,
    job_status ENUM('draft', 'active', 'in_progress', 'completed', 'cancelled') DEFAULT 'draft',
    is_urgent BOOLEAN DEFAULT FALSE,
    is_featured BOOLEAN DEFAULT FALSE,
    views_count INT DEFAULT 0,
    applications_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (employer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_employer_jobs (employer_id, job_status),
    INDEX idx_location (location_region, location_province, location_city),
    INDEX idx_category (job_category, job_status),
    INDEX idx_pay_range (pay_type, pay_amount),
    INDEX idx_status (job_status, created_at),
    INDEX idx_featured (is_featured, created_at),
    INDEX idx_urgent (is_urgent, created_at),
    INDEX idx_remote_policy (location_remote_policy),
    FULLTEXT idx_job_search (job_title, job_description, job_requirements)
);

-- Job Applications Table
CREATE TABLE job_applications (
    application_id INT PRIMARY KEY AUTO_INCREMENT,
    job_id INT NOT NULL,
    worker_id INT NOT NULL,
    application_status ENUM('pending', 'approved', 'rejected', 'withdrawn') DEFAULT 'pending',
    cover_letter TEXT,
    proposed_rate DECIMAL(10,2),
    availability_date DATE,
    worker_confirmed BOOLEAN DEFAULT FALSE,
    employer_confirmed BOOLEAN DEFAULT FALSE,
    work_start_time TIMESTAMP NULL,
    work_end_time TIMESTAMP NULL,
    payment_completed BOOLEAN DEFAULT FALSE,
    application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (job_id) REFERENCES job_posts(job_id) ON DELETE CASCADE,
    FOREIGN KEY (worker_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_application (job_id, worker_id),
    INDEX idx_job_applications (job_id, application_status),
    INDEX idx_worker_applications (worker_id, application_status),
    INDEX idx_application_date (application_date)
);

-- Worker Portfolio Table
CREATE TABLE worker_portfolio (
    portfolio_id INT PRIMARY KEY AUTO_INCREMENT,
    worker_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    work_type ENUM('project', 'job_site', 'certification', 'equipment_operation', 'construction', 'welding', 'craft', 'service', 'maintenance', 'installation', 'repair', 'assembly', 'manufacturing', 'transportation', 'agriculture', 'general_labor', 'other') NOT NULL DEFAULT 'project',
    project_url VARCHAR(500),
    image_path VARCHAR(255),
    site_photos JSON, -- Multiple photos for job sites, before/after, equipment operation
    client_company VARCHAR(255), -- For contractor work
    job_location VARCHAR(255), -- Physical location of work
    work_duration VARCHAR(100), -- e.g., "3 months", "2 weeks", "1 year"
    completion_date DATE,
    tools_equipment JSON, -- List of tools/equipment used
    certifications JSON, -- Relevant certifications for this work
    work_category VARCHAR(100), -- e.g., "Residential Construction", "Industrial Welding", "Automotive Repair"
    team_size INT, -- Number of people worked with
    supervisor_name VARCHAR(255), -- For reference
    skills_used JSON,
    is_featured BOOLEAN DEFAULT FALSE,
    views_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (worker_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_worker_portfolio (worker_id, is_featured),
    INDEX idx_featured_portfolio (is_featured, created_at),
    INDEX idx_work_type (work_type),
    INDEX idx_work_category (work_category),
    INDEX idx_completion_date (completion_date DESC)
);

-- Job Ratings Table
CREATE TABLE job_ratings (
    rating_id INT PRIMARY KEY AUTO_INCREMENT,
    job_id INT NOT NULL,
    rater_id INT NOT NULL,
    rated_id INT NOT NULL,
    rating_type ENUM('employer_to_worker', 'worker_to_employer') NOT NULL,
    rating_stars INT NOT NULL CHECK (rating_stars BETWEEN 1 AND 5),
    feedback TEXT,
    would_work_again BOOLEAN,
    communication_rating INT CHECK (communication_rating BETWEEN 1 AND 5),
    quality_rating INT CHECK (quality_rating BETWEEN 1 AND 5),
    professionalism_rating INT CHECK (professionalism_rating BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (job_id) REFERENCES job_posts(job_id) ON DELETE CASCADE,
    FOREIGN KEY (rater_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (rated_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_rating (job_id, rater_id, rated_id, rating_type),
    INDEX idx_job_ratings (job_id, rating_type),
    INDEX idx_rater_ratings (rater_id, rating_type),
    INDEX idx_rated_ratings (rated_id, rating_type),
    INDEX idx_rating_date (created_at)
);

-- Employer Reviews Table
CREATE TABLE employer_reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    employer_id INT NOT NULL,
    worker_id INT NOT NULL,
    job_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT,
    would_recommend BOOLEAN,
    communication_rating INT CHECK (communication_rating BETWEEN 1 AND 5),
    professionalism_rating INT CHECK (professionalism_rating BETWEEN 1 AND 5),
    payment_rating INT CHECK (payment_rating BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (employer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (worker_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES job_posts(job_id) ON DELETE CASCADE,
    UNIQUE KEY unique_employer_review (employer_id, worker_id, job_id),
    INDEX idx_employer_reviews (employer_id, rating),
    INDEX idx_worker_reviews (worker_id, rating),
    INDEX idx_job_reviews (job_id, rating)
);

-- Messages Table
CREATE TABLE messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    job_id INT NULL,
    subject VARCHAR(255),
    content TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    is_deleted_by_sender BOOLEAN DEFAULT FALSE,
    is_deleted_by_receiver BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES job_posts(job_id) ON DELETE SET NULL,
    INDEX idx_conversation (sender_id, receiver_id, created_at),
    INDEX idx_receiver_unread (receiver_id, is_read, created_at),
    INDEX idx_job_messages (job_id, created_at)
);

-- Notifications Table (RaketGo specific)
CREATE TABLE notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type ENUM('new_application', 'application_status', 'new_message', 'payment', 'trust_score_update') NOT NULL,
    actor_id INT,
    target_id INT,
    target_type ENUM('job_post', 'application', 'message', 'rating') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    action_url VARCHAR(500),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (actor_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_user_notifications (user_id, is_read, created_at),
    INDEX idx_type_notifications (type, created_at)
);

-- Skill Posts Table (Learning Hub)
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
    INDEX idx_admin_posts (admin_id, post_type),
    INDEX idx_post_type (post_type, is_featured),
    INDEX idx_category (category, is_featured),
    INDEX idx_featured_created (is_featured, created_at),
    INDEX idx_type_featured_created (post_type, is_featured, created_at),
    INDEX idx_category_featured_created (category, is_featured, created_at)
);

-- Digital Contracts Table
CREATE TABLE digital_contracts (
    contract_id INT PRIMARY KEY AUTO_INCREMENT,
    job_id INT NOT NULL,
    employer_id INT NOT NULL,
    worker_id INT NOT NULL,
    contract_terms TEXT NOT NULL,
    contract_amount DECIMAL(10,2) NOT NULL,
    contract_start_date DATE NOT NULL,
    contract_end_date DATE NOT NULL,
    contract_status ENUM('draft', 'pending', 'active', 'completed', 'terminated', 'cancelled') DEFAULT 'draft',
    signed_by_employer BOOLEAN DEFAULT FALSE,
    signed_by_worker BOOLEAN DEFAULT FALSE,
    employer_signature_data TEXT,
    worker_signature_data TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (job_id) REFERENCES job_posts(job_id) ON DELETE CASCADE,
    FOREIGN KEY (employer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (worker_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_job_contracts (job_id, contract_status),
    INDEX idx_employer_contracts (employer_id, contract_status),
    INDEX idx_worker_contracts (worker_id, contract_status),
    INDEX idx_contract_dates (contract_start_date, contract_end_date)
);

-- Transactions Table
CREATE TABLE transactions (
    transaction_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    job_id INT NULL,
    contract_id INT NULL,
    transaction_type ENUM('deposit', 'withdrawal', 'payment', 'refund', 'advance') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'PHP',
    description TEXT,
    status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(255),
    transaction_reference VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES job_posts(job_id) ON DELETE SET NULL,
    FOREIGN KEY (contract_id) REFERENCES digital_contracts(contract_id) ON DELETE SET NULL,
    INDEX idx_user_transactions (user_id, transaction_type, status),
    INDEX idx_transaction_status (status, created_at),
    INDEX idx_transaction_date (created_at)
);

-- User Interactions Table
CREATE TABLE user_interactions (
    interaction_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    target_type ENUM('job_post', 'user_profile', 'skill_post') NOT NULL,
    target_id INT NOT NULL,
    interaction_type ENUM('view', 'apply', 'save', 'like', 'share', 'click') NOT NULL,
    interaction_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_interactions (user_id, interaction_type, created_at),
    INDEX idx_target_interactions (target_type, target_id, interaction_type),
    INDEX idx_interaction_type (interaction_type, created_at)
);

-- Trust Score Updates Table
CREATE TABLE trust_score_updates (
    update_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    old_score DECIMAL(3,2) NOT NULL,
    new_score DECIMAL(3,2) NOT NULL,
    score_change DECIMAL(3,2) NOT NULL,
    update_reason VARCHAR(255) NOT NULL,
    related_rating_id INT NULL,
    updated_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (related_rating_id) REFERENCES job_ratings(rating_id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_user_score_updates (user_id, created_at),
    INDEX idx_score_changes (score_change, created_at)
);

-- Auth Rate Limits Table
CREATE TABLE auth_rate_limits (
    limit_id INT PRIMARY KEY AUTO_INCREMENT,
    identifier VARCHAR(255) NOT NULL,
    limit_type ENUM('login', 'signup', 'password_reset', 'api_call') NOT NULL,
    attempt_count INT DEFAULT 1,
    blocked_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_rate_limit (identifier, limit_type),
    INDEX idx_blocked_until (blocked_until),
    INDEX idx_limit_type (limit_type, created_at)
);

-- =================================================================
-- CROSS-PLATFORM INTEGRATION TABLES
-- =================================================================

-- Cross-Platform Activity Tracking
CREATE TABLE cross_platform_activities (
    activity_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    platform ENUM('raketgo', 'raketko', 'both') NOT NULL,
    activity_type ENUM('job_application', 'job_post', 'social_post', 'profile_update', 'connection', 'skill_update', 'portfolio_update') NOT NULL,
    target_id INT NULL,
    target_type ENUM('job_post', 'social_post', 'user_profile', 'skill', 'portfolio') NULL,
    activity_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_activities (user_id, created_at),
    INDEX idx_platform_activities (platform, activity_type, created_at),
    INDEX idx_target_activities (target_type, target_id, created_at)
);

-- Unified Notifications Table
CREATE TABLE unified_notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    platform ENUM('raketgo', 'raketko', 'both') NOT NULL,
    type ENUM('job_application', 'job_status', 'new_message', 'payment', 'social_like', 'social_comment', 'social_follow', 'social_mention', 'trust_score_update', 'profile_view') NOT NULL,
    actor_id INT NOT NULL,
    target_id INT NULL,
    target_type ENUM('job_post', 'social_post', 'user_profile', 'message', 'application') NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    action_url VARCHAR(500) NULL,
    is_read BOOLEAN DEFAULT FALSE,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (actor_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_notifications (user_id, is_read, created_at),
    INDEX idx_platform_notifications (platform, type, created_at),
    INDEX idx_unread_notifications (is_read, created_at)
);

-- Unified Skills System
CREATE TABLE unified_skills (
    skill_id INT PRIMARY KEY AUTO_INCREMENT,
    skill_name VARCHAR(100) NOT NULL UNIQUE,
    skill_category ENUM('technical', 'soft_skill', 'industry_specific', 'certification', 'tool') NOT NULL,
    description TEXT,
    demand_level ENUM('high', 'medium', 'low') DEFAULT 'medium',
    average_proficiency DECIMAL(3,2) DEFAULT 0.00,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_skill_name (skill_name),
    INDEX idx_skill_category (skill_category, demand_level),
    INDEX idx_skill_demand (demand_level, usage_count)
);

CREATE TABLE user_unified_skills (
    user_skill_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    skill_id INT NOT NULL,
    proficiency_level ENUM('beginner', 'intermediate', 'advanced', 'expert') NOT NULL,
    years_experience DECIMAL(3,1) DEFAULT 0,
    verified BOOLEAN DEFAULT FALSE,
    verification_source ENUM('self_declared', 'employer_verified', 'certification', 'portfolio_proof') DEFAULT 'self_declared',
    last_used DATE NULL,
    source ENUM('raketgo', 'raketko', 'both') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES unified_skills(skill_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_skill (user_id, skill_id),
    INDEX idx_user_skills (user_id, proficiency_level),
    INDEX idx_skill_users (skill_id, proficiency_level),
    INDEX idx_verified_skills (verified, verification_source)
);

-- Job-Social Engagement
CREATE TABLE job_social_engagement (
    engagement_id INT PRIMARY KEY AUTO_INCREMENT,
    job_id INT NOT NULL,
    social_post_id INT NULL,
    user_id INT NOT NULL,
    engagement_type ENUM('share', 'discuss', 'recommend', 'apply_from_social') NOT NULL,
    engagement_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (job_id) REFERENCES job_posts(job_id) ON DELETE CASCADE,
    FOREIGN KEY (social_post_id) REFERENCES social_posts(post_id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_job_engagement (job_id, engagement_type, created_at),
    INDEX idx_user_engagement (user_id, engagement_type, created_at),
    INDEX idx_social_engagement (social_post_id, created_at)
);

-- Career Milestones
CREATE TABLE career_milestones (
    milestone_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    milestone_type ENUM('job_start', 'job_end', 'promotion', 'skill_acquired', 'certification_earned', 'portfolio_added', 'social_achievement') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    related_job_id INT NULL,
    related_social_post_id INT NULL,
    milestone_date DATE NOT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (related_job_id) REFERENCES job_posts(job_id) ON DELETE SET NULL,
    FOREIGN KEY (related_social_post_id) REFERENCES social_posts(post_id) ON DELETE SET NULL,
    INDEX idx_user_milestones (user_id, milestone_date),
    INDEX idx_public_milestones (is_public, milestone_date),
    INDEX idx_milestone_types (milestone_type, created_at)
);

-- Cross-Platform Content Sharing
CREATE TABLE cross_platform_shares (
    share_id INT PRIMARY KEY AUTO_INCREMENT,
    source_platform ENUM('raketgo', 'raketko') NOT NULL,
    source_type ENUM('job_post', 'social_post', 'article', 'announcement') NOT NULL,
    source_id INT NOT NULL,
    target_platform ENUM('raketgo', 'raketko') NOT NULL,
    shared_by_user_id INT NOT NULL,
    share_context TEXT,
    engagement_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (shared_by_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_source_shares (source_platform, source_type, created_at),
    INDEX idx_target_shares (target_platform, created_at),
    INDEX idx_user_shares (shared_by_user_id, created_at)
);

-- Trending Content (Combined)
CREATE TABLE trending_content (
    trending_id INT PRIMARY KEY AUTO_INCREMENT,
    content_type ENUM('job_post', 'social_post') NOT NULL,
    content_id INT NOT NULL,
    trending_score DECIMAL(10,2) NOT NULL,
    engagement_rate DECIMAL(5,2) DEFAULT 0.00,
    view_count INT DEFAULT 0,
    share_count INT DEFAULT 0,
    like_count INT DEFAULT 0,
    comment_count INT DEFAULT 0,
    trending_period ENUM('hourly', 'daily', 'weekly', 'monthly') NOT NULL,
    period_start TIMESTAMP NOT NULL,
    period_end TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_trending_content (content_type, trending_score DESC),
    INDEX idx_trending_period (trending_period, period_start),
    INDEX idx_content_trending (content_id, trending_period)
);

-- Unified User Analytics
CREATE TABLE unified_user_analytics (
    analytics_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    -- RaketGo metrics
    jobs_viewed INT DEFAULT 0,
    jobs_applied INT DEFAULT 0,
    applications_received INT DEFAULT 0,
    messages_sent INT DEFAULT 0,
    messages_received INT DEFAULT 0,
    -- RaketKo metrics
    social_posts_created INT DEFAULT 0,
    social_likes_received INT DEFAULT 0,
    social_comments_received INT DEFAULT 0,
    social_shares_received INT DEFAULT 0,
    social_followers_gained INT DEFAULT 0,
    profile_views INT DEFAULT 0,
    -- Combined metrics
    total_engagement INT DEFAULT 0,
    network_size INT DEFAULT 0,
    career_progress_score DECIMAL(5,2) DEFAULT 0.00,
    platform_activity_score DECIMAL(5,2) DEFAULT 0.00,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_analytics (user_id, date),
    INDEX idx_user_analytics_date (user_id, date),
    INDEX idx_engagement_metrics (total_engagement DESC),
    INDEX idx_career_progress (career_progress_score DESC)
);

-- =================================================================
-- FOREIGN KEY CONSTRAINTS
-- =================================================================

-- Add foreign key relationship from users to social_profiles
ALTER TABLE users ADD CONSTRAINT fk_users_social_profile 
    FOREIGN KEY (social_profile_id) REFERENCES social_profiles(profile_id) ON DELETE SET NULL;

-- =================================================================
-- TRIGGERS FOR AUTOMATED PROCESSES
-- =================================================================

DELIMITER //

-- Trigger to update post likes count
CREATE TRIGGER update_post_likes_count 
AFTER INSERT ON social_post_likes
FOR EACH ROW
BEGIN
    UPDATE social_posts SET likes_count = likes_count + 1 WHERE post_id = NEW.post_id;
END//

CREATE TRIGGER update_post_likes_count_delete 
AFTER DELETE ON social_post_likes
FOR EACH ROW
BEGIN
    UPDATE social_posts SET likes_count = likes_count - 1 WHERE post_id = OLD.post_id;
END//

-- Trigger to update post comments count
CREATE TRIGGER update_post_comments_count 
AFTER INSERT ON social_post_comments
FOR EACH ROW
BEGIN
    UPDATE social_posts SET comments_count = comments_count + 1 WHERE post_id = NEW.post_id;
END//

CREATE TRIGGER update_post_comments_count_delete 
AFTER DELETE ON social_post_comments
FOR EACH ROW
BEGIN
    UPDATE social_posts SET comments_count = comments_count - 1 WHERE post_id = OLD.post_id;
END//

-- Trigger to update post shares count
CREATE TRIGGER update_post_shares_count 
AFTER INSERT ON social_post_shares
FOR EACH ROW
BEGIN
    UPDATE social_posts SET shares_count = shares_count + 1 WHERE post_id = NEW.post_id;
END//

-- Trigger to update comment likes count
CREATE TRIGGER update_comment_likes_count 
AFTER INSERT ON social_comment_likes
FOR EACH ROW
BEGIN
    UPDATE social_post_comments SET likes_count = likes_count + 1 WHERE comment_id = NEW.comment_id;
END//

CREATE TRIGGER update_comment_likes_count_delete 
AFTER DELETE ON social_comment_likes
FOR EACH ROW
BEGIN
    UPDATE social_post_comments SET likes_count = likes_count - 1 WHERE comment_id = OLD.comment_id;
END//

-- Trigger to sync user profile changes
CREATE TRIGGER sync_user_to_social_profile
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    IF NEW.social_profile_id IS NOT NULL THEN
        UPDATE social_profiles SET 
            bio = NEW.social_bio,
            headline = NEW.social_headline,
            location = NEW.social_location,
            website = NEW.social_website,
            linkedin_url = NEW.social_linkedin_url,
            skills = NEW.social_skills,
            interests = NEW.social_interests,
            updated_at = CURRENT_TIMESTAMP
        WHERE profile_id = NEW.social_profile_id;
    END IF;
END//

-- Trigger to log job application activity
CREATE TRIGGER log_job_application_activity
AFTER INSERT ON job_applications
FOR EACH ROW
BEGIN
    INSERT INTO cross_platform_activities (user_id, platform, activity_type, target_id, target_type, activity_data)
    VALUES (NEW.worker_id, 'raketgo', 'job_application', NEW.job_id, 'job_post', 
            JSON_OBJECT('application_id', NEW.application_id, 'status', NEW.application_status));
END//

-- Trigger to log social post activity
CREATE TRIGGER log_social_post_activity
AFTER INSERT ON social_posts
FOR EACH ROW
BEGIN
    INSERT INTO cross_platform_activities (user_id, platform, activity_type, target_id, target_type, activity_data)
    VALUES (NEW.user_id, 'raketko', 'social_post', NEW.post_id, 'social_post', 
            JSON_OBJECT('post_type', NEW.post_type, 'visibility', NEW.visibility));
END//

-- Trigger to update unified analytics
CREATE TRIGGER update_daily_analytics
AFTER INSERT ON unified_notifications
FOR EACH ROW
BEGIN
    INSERT INTO unified_user_analytics (user_id, date, messages_received)
    VALUES (NEW.user_id, CURDATE(), 1)
    ON DUPLICATE KEY UPDATE 
        messages_received = messages_received + 1,
        total_engagement = total_engagement + 1;
END//

DELIMITER ;

-- =================================================================
-- VIEWS FOR UNIFIED DATA ACCESS
-- =================================================================

-- Unified User Profile View
CREATE VIEW unified_user_profile AS
SELECT 
    u.user_id,
    u.full_name,
    u.user_type,
    u.email,
    u.mobile_number,
    u.profile_picture,
    u.region,
    u.province,
    u.city,
    u.bio as raketgo_bio,
    u.trust_score,
    -- Social profile fields
    sp.bio as social_bio,
    sp.headline as social_headline,
    sp.location as social_location,
    sp.website as social_website,
    sp.linkedin_url as social_linkedin_url,
    sp.skills as social_skills,
    sp.interests as social_interests,
    sp.followers_count,
    sp.following_count,
    sp.posts_count,
    sp.is_verified,
    sp.verification_badge,
    -- Career fields
    sp.job_seeking_status,
    sp.current_job_title,
    sp.current_company,
    sp.work_preference,
    sp.availability_status,
    -- Combined fields
    COALESCE(sp.bio, u.bio) as combined_bio,
    COALESCE(sp.headline, CONCAT(u.user_type, ' at ', COALESCE(sp.current_company, 'Independent'))) as combined_headline,
    COALESCE(sp.location, CONCAT(u.city, ', ', u.province)) as combined_location
FROM users u
LEFT JOIN social_profiles sp ON u.social_profile_id = sp.profile_id;

-- Trending Combined Content View
CREATE VIEW trending_combined_content AS
SELECT 
    'job_post' as content_type,
    job_id as content_id,
    job_title as title,
    created_at,
    views_count,
    (SELECT COUNT(*) FROM job_applications WHERE job_id = job_posts.job_id) as engagement_count,
    (SELECT AVG(rating_stars) FROM job_ratings WHERE job_id = job_posts.job_id) as avg_rating,
    pay_amount,
    job_category,
    'raketgo' as platform
FROM job_posts 
WHERE job_status = 'active'

UNION ALL

SELECT 
    'social_post' as content_type,
    post_id as content_id,
    title,
    created_at,
    views_count,
    (likes_count + comments_count + shares_count) as engagement_count,
    NULL as avg_rating,
    NULL as pay_amount,
    post_type as job_category,
    'raketko' as platform
FROM social_posts 
WHERE visibility = 'public'

ORDER BY engagement_count DESC, created_at DESC;

-- =================================================================
-- INITIAL DATA SETUP
-- =================================================================

-- Insert initial trending topics
INSERT INTO trending_topics (hashtag, display_name, description, category) VALUES
('#CareerGrowth', 'Career Growth', 'Professional development and career advancement tips', 'career'),
('#JobSeekingPH', 'Job Seeking Philippines', 'Job hunting tips and opportunities in the Philippines', 'career'),
('#TechSkills', 'Tech Skills', 'Technology skills and programming discussions', 'skills'),
('#RemoteWork', 'Remote Work', 'Remote work tips and opportunities', 'career'),
('#StartupLife', 'Startup Life', 'Startup culture and entrepreneurship', 'industry'),
('#FilipinoProfessionals', 'Filipino Professionals', 'Celebrating Filipino professional achievements', 'community'),
('#WorkLifeBalance', 'Work Life Balance', 'Balancing professional and personal life', 'career'),
('#SkillsDevelopment', 'Skills Development', 'Continuous learning and skill improvement', 'skills');

-- Insert initial unified skills
INSERT INTO unified_skills (skill_name, skill_category, description, demand_level) VALUES
('PHP', 'technical', 'Server-side scripting language for web development', 'high'),
('JavaScript', 'technical', 'Client-side scripting language for web development', 'high'),
('Python', 'technical', 'High-level programming language for various applications', 'high'),
('Java', 'technical', 'Object-oriented programming language', 'high'),
('Communication', 'soft_skill', 'Ability to convey information effectively', 'medium'),
('Leadership', 'soft_skill', 'Ability to lead and motivate teams', 'medium'),
('Teamwork', 'soft_skill', 'Ability to work effectively in teams', 'medium'),
('Problem Solving', 'soft_skill', 'Ability to analyze and solve problems', 'high'),
('Project Management', 'industry_specific', 'Planning and executing projects', 'high'),
('Digital Marketing', 'industry_specific', 'Online marketing and promotion', 'medium'),
('Data Analysis', 'technical', 'Analyzing and interpreting data', 'high'),
('Graphic Design', 'technical', 'Visual communication and design', 'medium');

-- =================================================================
-- PERFORMANCE OPTIMIZATION
-- =================================================================

-- Create composite indexes for better performance
CREATE INDEX idx_user_platform_activity ON cross_platform_activities(user_id, platform, activity_type, created_at);
CREATE INDEX idx_notification_priority ON unified_notifications(priority, is_read, created_at);
CREATE INDEX idx_trending_combined ON trending_content(content_type, trending_score DESC, created_at);
CREATE INDEX idx_unified_skills_search ON unified_skills(skill_name, skill_category, demand_level);

-- =================================================================
-- COMPLETION
-- =================================================================

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Unified RaketGo + RaketKo database schema created successfully!' as status,
       'All tables, indexes, triggers, and views have been established.' as details;
