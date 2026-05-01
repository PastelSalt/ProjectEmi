# TODO — Resume PDF Upload for Job Applications

- [ ] 1. Update `database/schema.sql` — add `resume_file` to `job_applications`
- [ ] 2. Update `job-details.php` — add file upload form input + multipart enctype
- [ ] 3. Update `job-details.php` — add server-side PDF upload handler (validation, move file)
- [ ] 4. Update `job-details.php` — update INSERT and UPDATE queries to include `resume_file`
- [ ] 5. Update `job-details.php` — add resume download link for employer view
- [ ] 6. Update `dashboard-employer.php` — add resume download links for employer dashboard
- [ ] 7. Update `config/config.php` — add `RESUMES_DIR` constant (optional enhancement)

# TODO — Remote Policy Implementation


- [x] 1. Update `database/schema.sql` — add `remote_policy` to `job_posts`
- [x] 2. Update `database/sample_data.sql` — add `remote_policy` values to seed data
- [x] 3. Update `post-job.php` — form field, validation, INSERT
- [x] 4. Update `job-details.php` — display remote policy badge
- [x] 5. Update `index.php` — filter dropdown + job card badge
- [x] 6. Update `dashboard-employer.php` — show in job list
- [x] 7. Update `for-you.php` — show on recommended cards
- [x] 8. Update `NAVIGATION.md` — document new enum and flows

# BUG FIXES — March 2026

- [x] 1. Fix `rate-worker.php` — SQL CONCAT() error in notification INSERT, corrected param types
- [x] 2. Fix `rate-employer.php` — same SQL CONCAT() error in notification INSERT
- [x] 3. Fix `add-skill-post.php` — schema mismatch: mapped form fields to actual `skill_posts` columns
