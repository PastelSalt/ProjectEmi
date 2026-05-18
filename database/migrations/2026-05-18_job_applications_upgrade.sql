-- Migration: align job_applications columns with app usage
-- Run once on existing databases created from older schema files.

ALTER TABLE job_applications
    ADD COLUMN employer_id INT NULL AFTER worker_id,
    ADD COLUMN resume_file VARCHAR(191) NULL AFTER cover_letter,
    ADD COLUMN applied_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER availability_date,
    ADD COLUMN reviewed_at TIMESTAMP NULL AFTER applied_at,
    ADD COLUMN worker_confirmed BOOLEAN DEFAULT FALSE AFTER reviewed_at,
    ADD COLUMN employer_confirmed BOOLEAN DEFAULT FALSE AFTER worker_confirmed,
    ADD COLUMN both_confirmed_at TIMESTAMP NULL AFTER employer_confirmed,
    ADD COLUMN rating_available_at TIMESTAMP NULL AFTER both_confirmed_at,
    ADD COLUMN work_start_time TIMESTAMP NULL AFTER rating_available_at,
    ADD COLUMN work_end_time TIMESTAMP NULL AFTER work_start_time,
    ADD COLUMN payment_completed BOOLEAN DEFAULT FALSE AFTER work_end_time;

UPDATE job_applications ja
JOIN job_posts jp ON ja.job_id = jp.job_id
SET ja.employer_id = jp.employer_id
WHERE ja.employer_id IS NULL;

UPDATE job_applications
SET applied_at = COALESCE(applied_at, updated_at)
WHERE applied_at IS NULL;

ALTER TABLE job_applications
    MODIFY employer_id INT NOT NULL,
    ADD INDEX idx_employer_applications (employer_id, application_status),
    ADD INDEX idx_applied_at (applied_at);

ALTER TABLE job_applications
    ADD CONSTRAINT fk_job_applications_employer
    FOREIGN KEY (employer_id) REFERENCES users(user_id) ON DELETE CASCADE;
