-- Migration: Add Employer Subtype and Bio Fields
-- Run this if you have an existing database and need to add the new fields

-- Add employer_subtype column to users table
ALTER TABLE users 
ADD COLUMN employer_subtype ENUM('company', 'individual') DEFAULT NULL 
AFTER user_type;

-- Add bio column for employer profile
ALTER TABLE users 
ADD COLUMN bio TEXT DEFAULT NULL 
AFTER social_links;

-- Add index for employer_subtype
CREATE INDEX idx_employer_subtype ON users(employer_subtype);

-- Create employer_reviews table for public profile reviews
CREATE TABLE IF NOT EXISTS employer_reviews (
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

-- Optional: Set default employer_subtype for existing employers
-- You may want to run a separate query to categorize existing employers
-- UPDATE users SET employer_subtype = 'individual' WHERE user_type = 'employer' AND employer_subtype IS NULL;

-- Add rating availability tracking columns to job_applications (if not exists)
ALTER TABLE job_applications 
ADD COLUMN IF NOT EXISTS payment_completed BOOLEAN DEFAULT FALSE 
AFTER payment_released;

ALTER TABLE job_applications 
ADD COLUMN IF NOT EXISTS rating_available_at TIMESTAMP NULL 
AFTER payment_completed;

ALTER TABLE job_applications 
ADD COLUMN IF NOT EXISTS both_confirmed_at TIMESTAMP NULL 
AFTER rating_available_at;

-- Add index for rating availability queries
CREATE INDEX IF NOT EXISTS idx_rating_available ON job_applications(rating_available_at);
CREATE INDEX IF NOT EXISTS idx_both_confirmed ON job_applications(both_confirmed_at);

-- Migration completed successfully
-- Remember to update your PHP config and pages to use the new fields
