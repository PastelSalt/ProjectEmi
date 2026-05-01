# RaketGo - Job Matching Platform

Created and managed by Moesoft (Moeko Software)

RaketGo is a PHP and MySQL job-matching web platform focused on the Philippines. It supports workers, employers, and admins with role-specific dashboards, recommendation feeds, messaging, notifications, and a learning hub.

## Current Project State (May 2026)

This repository contains a working web app with:
- Production-oriented security hardening (session security, CSRF, login throttling, safe headers, hardened Apache rules)
- Region-first home discovery with an interactive Philippines block map
- Recommendation logic for both workers and employers using MatchScore algorithm
- Saved jobs, withdraw and reapply flow, and employer pause/reopen controls
- Remote work policy support for job postings (On-site, Hybrid, Remote)
- Rating availability with cooling-off period for long-term jobs
- Site-wide UI refresh, animation consistency, and mobile compatibility fixes
- Pagination on major listing pages
- Sample seed dataset for realistic testing
- Multibyte character support (UTF-8) for international users

## Role Capabilities

### Worker
- Create account and maintain profile
- Add and remove skills with proficiency levels
- Browse jobs, filter, sort, and paginate
- Apply to jobs with optional cover letter
- Withdraw pending applications and reapply to withdrawn ones
- Save and unsave jobs
- View personalized recommendations on For You
- Message employers and receive notifications
- Rate employers after job completion (5-star + feedback)
- Track personal trust score and rating history

### Employer
- Create account and post jobs
- View and manage posted jobs
- Review pending applications
- Approve and reject applications (with slot-safe checks)
- Pause active jobs and reopen paused jobs (if slots are still open)
- View recommended workers on For You
- Message workers and receive notifications
- Rate workers after job completion (5-star + feedback)
- Track personal trust score and rating history
- View completed jobs and rate workers

### Admin
- Access platform summary metrics and operational dashboard
- Manage overview of users, jobs, and applications from dashboard data views
- View trending/jobs context on For You
- Monitor trust score metrics and user ratings distribution
- Review audit logs of trust score changes
- Manage user accounts, skill posts, and analytics dashboards

## Core User Flows

### Registration and Login
1. User signs up as worker or employer.
2. Password is hashed with bcrypt (`password_hash`).
3. Login validates CSRF and applies brute-force throttle controls.
4. Successful login regenerates session ID and CSRF token.
5. Session is continuously validated against account status (`active`).

### Job Lifecycle
1. Employer posts job with location, pay model, slots, skills, and remote policy.
2. Worker applies (or reapplies if previous application was withdrawn).
3. Employer approves/rejects pending applications.
4. Approval increments `slots_filled` and can move a filled job to `in_progress`.
5. Both worker and employer confirm job completion.
6. After completion, both parties can rate each other (with cooling-off period for long-term jobs).
7. Notifications are generated for key state changes.

### Messaging and Notifications
1. Users open conversations with account-status checks.
2. Sending a message creates a message record and a notification for receiver.
3. Reading conversation marks unread incoming messages as read.
4. Notifications page supports mark-one and mark-all read actions.

## Recommendation and Matching

RaketGo includes logic for both worker and employer recommendation views.

### Worker Job Recommendations (`for-you.php`)
The worker feed computes `match_score` from:
- Required-skill overlap x 3
- Region match bonus +2
- City match bonus +1
- Worker trust score bonus + trust_score

The feed excludes:
- Jobs not in `active` status
- Worker's own jobs
- Jobs already applied to

Results are sorted by highest `match_score`, then newest jobs.

### Employer Worker Recommendations (`for-you.php`)
The employer feed:
- Looks at required skills from up to 5 recent active employer jobs
- Finds workers with overlapping skills
- Ranks by verified skills count first, then trust score

### Trending Jobs (`for-you.php`)
Trending jobs are based on interaction volume in the last 7 days from `user_interactions`.

## Trust Score System

### Overview
`trust_score` is a dynamic value in `users.trust_score` (range 0.00–5.00) calculated from 5-star mutual ratings between workers and employers after job completion. The system enables transparent peer-to-peer accountability and builds trust in the platform.

### Rating Workflow
1. After a job is completed (both `worker_confirmed = 1` AND `employer_confirmed = 1`):
   - Employer can rate worker via `rate-worker.php` (1–5 stars + optional feedback)
   - Worker can rate employer via `rate-employer.php` (1–5 stars + optional feedback)
   - Long-term jobs have a 3-day cooling-off period before ratings can be submitted
   - Duplicate ratings are prevented

2. Ratings are stored in the `job_ratings` table with:
   - `application_id` (FK): Links to the completed job
   - `rater_id` / `ratee_id`: Tracks who rated whom
   - `rating_type`: Either `'employer_to_worker'` or `'worker_to_employer'`
   - `rating_stars`: 1–5 integer value
   - `feedback`: Optional text (max 500 characters)

3. Each rating submission triggers:
   - Recalculation of the rated user's trust score
   - Update to `users.trust_score` 
   - Audit log entry in `trust_score_updates` table
   - Notification to the rated user

### Calculation Algorithm
Trust score is calculated as:
```
trust_score = AVG(all rating_stars from job_ratings WHERE ratee_id = user_id)
```
- Minimum: 0.00 (no ratings yet)
- Maximum: 5.00 (all 5-star ratings)
- Updated in real-time after each new rating submission

### Audit & Transparency
All trust score changes are logged in `trust_score_updates` for:
- Debugging and dispute resolution
- Historical tracking of score changes
- Admin review in analytics dashboard

### Usage
Trust score is used in:
- **Recommendation weighting**: Higher scores prioritized in worker/employer feeds
- **Ranking**: Displayed on worker profiles and applicant cards as "★ X.XX (N ratings)"
- **UI display**: Shows individual rating on dashboard; prevents re-rating the same job
- **Admin analytics**: Summary stats in analytics.php

### Key Tables
- `job_ratings`: Stores all 5-star ratings and feedback
- `trust_score_updates`: Audit log of all score recalculations

## Home Discovery Experience

Home (`index.php`) includes:
- Search (`q`), region, city, category filters
- Sort options: newest, pay high->low, pay low->high
- Pagination (`pageSize = 12`)
- Region-first map UX (PH block map + region chips)
- Sticky "continue with last region" using session state
- Featured learning announcements panel

## Learning Hub

Skill Learn (`skill-learn.php`) provides:
- Filter by type and category
- Featured-first ordering
- Pagination (`pageSize = 10`)
- Safe outbound resource links through URL sanitization

## Rating and Trust Management

### Rating Pages
- `rate-worker.php`: Employer rates worker after job completion
- `rate-employer.php`: Worker rates employer after job completion
- Both pages feature:
  - 5-star interactive rating input
  - Optional feedback (max 500 characters)
  - CSRF protection and input validation
  - Duplicate-rating prevention
  - Real-time trust score calculation

### Admin Features
- `add-skill-post.php`: Create educational skill posts (admin only)
- `manage-users.php`: Search, filter, and manage user accounts (admin only)
- `analytics.php`: Dashboard with user stats, job analytics, skill metrics, and trust score audits (admin only)

## Security Model

Implemented security controls include:
- Prepared statements for database operations (`executeQuery` helpers)
- Input sanitization and multiline sanitization helpers
- Output escaping with `htmlspecialchars` in views
- CSRF tokens on mutating forms/actions
- Session hardening (`HttpOnly`, `SameSite`, secure on HTTPS, strict mode)
- Session fixation defense (`session_regenerate_id(true)` on login)
- Environment-aware debug mode (`APP_ENV`, `APP_DEBUG`)
- Account status enforcement on active sessions
- Login brute-force throttling (`auth_rate_limits`)
- Safe internal/external URL sanitizers
- Security headers in app + Apache hardening in `.htaccess`
- Script execution blocked inside `uploads/`
- Database transactions for critical operations (prevents race conditions)
- Multibyte character support (UTF-8) with mbstring
- Explicit error handling (no error suppression operators)

## Database

Main schema file: `database/schema.sql`

Primary tables:
- `users`
- `user_skills`
- `job_posts`
- `job_applications`
- `digital_contracts`
- `skill_posts`
- `messages`
- `notifications`
- `auth_rate_limits`
- `user_interactions`
- `transactions`
- `job_ratings` (stores 5-star mutual ratings between workers and employers)
- `trust_score_updates` (audit log of all trust score recalculations)
- `employer_reviews` (public employer profile reviews)

Performance indexes are included for common filtering and pagination patterns (job status/region/category/pay and skill post featured/type/category sorting).

## Seed/Test Data

Sample data file: `database/sample_data.sql`

It seeds realistic linked data across the platform including users, jobs, applications, messages, notifications, interactions, and transactions.

Provided test credentials in seed file:
- Admin mobile number: `09560618349`
- Admin password: `matsuzakasatou`
- Non-admin test password: `password`

## Setup

### Prerequisites
- PHP 8.0+
- MySQL 5.7+ (or compatible)
- Apache (WAMP/XAMPP/MAMP supported)

### 1) Import schema

```sql
CREATE DATABASE raketgo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then import `database/schema.sql`.

### 2) (Optional) Seed realistic sample data

Import `database/sample_data.sql` after schema import.

### 3) Configure database and environment

Use environment variables where available:

```env
RAKETGO_DB_HOST=localhost
RAKETGO_DB_PORT=3306
RAKETGO_DB_USER=root
RAKETGO_DB_PASS=
RAKETGO_DB_NAME=raketgo
APP_ENV=local
SITE_URL=http://localhost/ProjectEmi
```

Primary config files:
- `config/config.php`
- `config/database.php`

Production-specific file present in repository:
- `config/prod_database.php`

Note: For public deployments, always use your own secure credentials and rotate secrets before release.

### 4) Web root placement (WAMP example)

Place project in:
- `C:\wamp64\www\ProjectEmi`

Open:
- `http://localhost/ProjectEmi`

## Deployment Notes (InfinityFree / Shared Hosting)

1. Create database in hosting panel first.
2. Import `database/schema.sql`.
3. Optionally import `database/sample_data.sql` in non-production environments.
4. Ensure DB credentials and `APP_ENV=production` are set.
5. Keep display errors disabled in production.
6. Keep HTTPS enabled and verify secure cookies/HSTS behavior.

## Repository Structure

```text
ProjectEmi/
├── .htaccess
├── README.md
├── config/
│   ├── config.php
│   ├── database.php
│   └── prod_database.php
├── css/
│   └── style.css
├── dashboard-admin.php
├── dashboard-employer.php
├── dashboard-worker.php
├── database/
│   ├── migrate_employer_subtype.sql
│   ├── sample_data.sql
│   └── schema.sql
├── docs/
│   ├── Documentation Form A.md
│   ├── Documentation Form B.md
│   ├── EMPLOYER_PROFILE_FEATURE.md
│   ├── IMAGE_UPLOAD_FEATURES.md
│   ├── JOB_TYPE_ENHANCEMENTS.md
│   ├── MATCHSCORE_ALGORITHM.md
│   ├── NAVIGATION.md
│   ├── RATING_AVAILABILITY_FEATURE.md
│   ├── SECURITY_FIXES.md
│   ├── TODO.md
│   └── website logic and backend documentation.md
├── employer-profile.php
├── for-you.php
├── includes/
│   ├── footer.php
│   └── header.php
├── index.php
├── job-details.php
├── js/
│   └── main.js
├── login.php
├── logout.php
├── manage-users.php
├── messages.php
├── notifications.php
├── post-job.php
├── rate-employer.php
├── rate-worker.php
├── signup.php
├── skill-learn.php
└── uploads/
    ├── documents/
    ├── jobs/
    ├── posts/
    └── profiles/
```

## Related Documentation

For detailed documentation, see the `docs/` folder:
- `website logic and backend documentation.md` - Full backend and logic-level documentation
- `MATCHSCORE_ALGORITHM.md` - Recommendation algorithm details
- `NAVIGATION.md` - Navigation flows and user journeys
- `SECURITY_FIXES.md` - Security improvements and fixes
- `TODO.md` - Pending features and completed items
- `EMPLOYER_PROFILE_FEATURE.md` - Employer profile functionality
- `RATING_AVAILABILITY_FEATURE.md` - Rating system with cooling-off periods
- `JOB_TYPE_ENHANCEMENTS.md` - Job type and remote policy details
- `IMAGE_UPLOAD_FEATURES.md` - Image upload functionality
- `Documentation Form A.md` & `Documentation Form B.md` - Project documentation

## Known Notes

- Front-end JS contains hooks for API endpoints (`api/search.php`, `api/check-notifications.php`, `api/track-interaction.php`) that are not included in this repository snapshot.
- Transactions are data-model level records; payment gateway integration is not implemented in this codebase.
- Resume PDF upload for job applications is a pending feature (see `docs/TODO.md`).

## License

Copyright (c) 2026 RaketGo by Moesoft. All rights reserved.
