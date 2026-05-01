# Website Logic and Backend Documentation

This is the backend source of truth for the current RaketGo codebase. It is written to be a memory-safe reference so you can quickly recall:
- what each backend page does
- what data each flow reads/writes
- how recommendation and score-like values are calculated
- what fields are required to generate those values
- where the current implementation has gaps

## 0. Scope and Coverage

Backend behavior documented here comes from:
- `config/config.php`
- `config/database.php`
- `login.php`, `signup.php`, `logout.php`
- `post-job.php`, `job-details.php`
- `for-you.php`
- `dashboard-worker.php`, `dashboard-employer.php`, `dashboard-admin.php`
- `messages.php`, `notifications.php`
- `index.php`, `skill-learn.php`
- `includes/header.php`
- `database/schema.sql`
- `.htaccess`, `uploads/.htaccess`
- `js/main.js`

If code changes, this file must be updated together.

## 1. Runtime Bootstrap and Shared Backend Helpers

## 1.1 Bootstrap chain

Most pages include `config/config.php` first. That file:
1. Detects environment (`APP_ENV`) and debug mode (`APP_DEBUG`).
2. Detects HTTPS.
3. Configures and starts secure PHP sessions.
4. Defines constants (`SITE_URL`, `BASE_PATH`, upload directories).
5. Applies security headers.
6. Loads helper functions (sanitize, auth, CSRF, rate-limit, etc.).
7. Loads DB helper file via `require_once 'database.php'`.
8. Calls `enforceActiveSessionUser()` on every request where session exists.

## 1.2 Session and cookie security

Configured at runtime:
- `session.use_strict_mode = 1`
- `session.use_only_cookies = 1`
- `session.cookie_httponly = 1`
- `session.cookie_secure = 1` only on HTTPS
- `SameSite=Lax`
- session name is `raketgo_session`

## 1.3 Security headers

Set in app layer (plus Apache level hardening):
- `X-Frame-Options: SAMEORIGIN`
- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: geolocation=(), microphone=(), camera=()`
- `Cross-Origin-Opener-Policy: same-origin`
- HSTS when HTTPS

## 1.4 Input and URL helpers

Key helpers in `config/config.php`:
- `sanitizeInput()`: trim + remove null bytes
- `sanitizeMultilineInput()`: normalize newlines + trim + remove null bytes
- `sanitizeInternalUrl()`: blocks open redirects, absolute URLs, path traversal
- `sanitizeExternalUrl()`: only valid `http`/`https`
- `normalizePhilippineMobile()`: converts `+63xxxxxxxxxx` to `09xxxxxxxxx`
- `isValidPhilippineMobile()`: regex `^09\d{9}$`
- `isValidRegionCode()`: checks against hardcoded region code map

## 1.5 Auth/session helpers

- `isLoggedIn()`, `getCurrentUserId()`, `getCurrentUserType()`
- `requireLogin()`
- `requireUserType($type)`
- `getCsrfToken()`, `verifyCsrfToken()`, `csrfField()`, `regenerateCsrfToken()`
- `enforceActiveSessionUser()`:
  - on each request, if session exists, fetches current user account status
  - if missing or not `active`, destroys session and redirects to login

## 1.6 Login rate-limiting internals

Rate limit table: `auth_rate_limits` (if table exists).

Throttle key formula:
- `throttle_key = sha256(lower(scope + '|' + identifier + '|' + client_ip))`

For login:
- `scope = 'login'`
- `identifier = normalized mobile number`
- `maxAttempts = 6`
- `windowSeconds = 900`
- `lockSeconds = 900`

Behavior:
1. If no table, app continues without throttling.
2. If key is locked and `locked_until > now`, login blocked.
3. If window expired, attempts reset.
4. On failed login, attempts incremented and lock is applied once attempts reach max.
5. On successful login, throttle entry is deleted.

## 2. Database Layer Behavior

`config/database.php` exposes:
- `getDBConnection()`
- `closeDBConnection()`
- `executeQuery()` for prepared statements
- `fetchOne()` and `fetchAll()` wrappers

Important mechanics:
- All query execution in app pages is prepared-statement based.
- `executeQuery()` validates that bind `types` length matches number of params.
- Errors are logged server-side; user sees generic failures.

## 3. Core Data Model (Schema Reference)

## 3.1 Primary tables and intent

1. `users`
- identity and auth (`mobile_number`, `password_hash`, `user_type`)
- profile/location (`full_name`, `region`, `province`, `city`)
- ranking/finance fields (`trust_score`, `current_balance`)
- lifecycle (`account_status`, `last_login`)

2. `user_skills`
- worker skills and proficiency
- verification metadata (`is_verified`, `verified_by`, `verified_at`)
- unique key `(user_id, skill_name)`

3. `job_posts`
- job details, location, compensation, skill text blobs
- slot tracking (`slots_available`, `slots_filled`)
- state (`job_status` enum)

4. `job_applications`
- worker applications per job
- unique key `(job_id, worker_id)`
- state (`application_status` enum)
- completion flags (`worker_confirmed`, `employer_confirmed`)

5. `messages`
- sender/receiver messages
- unread tracking (`is_read`, `read_at`)

6. `notifications`
- user-targeted notification records
- polymorphic relation fields (`related_id`, `related_type`, `action_url`)

7. `auth_rate_limits`
- stores login throttle counters and lock windows

8. `user_interactions`
- event tracking (`view`, `apply`, `save`, etc.)
- used for trending and saved-jobs behavior

9. `skill_posts`
- learning hub content
- includes `likes_count` and `views_count`

10. `digital_contracts`, `transactions`
- modeled in schema and sample data
- very limited direct runtime writes in current page set

## 3.2 Enum/state values from schema

`users.user_type`:
- `admin`, `employer`, `worker`

`users.account_status`:
- `active`, `suspended`, `deleted`

`job_posts.pay_type`:
- `hourly`, `daily`, `fixed`, `monthly`

`job_posts.job_status`:
- `draft`, `active`, `in_progress`, `completed`, `cancelled`

`job_applications.application_status`:
- `pending`, `approved`, `rejected`, `withdrawn`

`skill_posts.post_type`:
- `certification`, `training`, `course`, `workshop`

`user_interactions.interaction_type`:
- `view`, `apply`, `save`, `like`, `share`, `click`

## 4. Route and Action Matrix (Read/Write Contract)

## 4.1 Authentication routes

### `signup.php` (public)
POST required:
- `csrf_token`
- `mobile_number`, `password`, `confirm_password`
- `user_type` (`worker` or `employer` only)
- `full_name`, `region`, `province`, `city`

Optional:
- `email`
- `skills[]` (worker only)

Writes:
- inserts into `users`
- inserts into `user_skills` for worker skills

### `login.php` (public)
POST required:
- `csrf_token`, `mobile_number`, `password`

Reads:
- user record from `users`
- rate-limit row from `auth_rate_limits`

Writes:
- rate-limit fail/reset rows
- `users.last_login`
- session values (`user_id`, `user_type`, `full_name`)

### `logout.php` (authenticated expected)
POST required:
- `csrf_token`

Behavior:
- refuses non-POST or invalid-CSRF logout requests
- session destroyed and redirected to home

## 4.2 Employer job posting and management

### `post-job.php` (employer only)
POST action: create job

Key inputs:
- `job_title`, `job_description`
- `location_region`, `location_province`, `location_city`, `specific_address`
- `pay_amount`, `pay_type`
- `required_skills`, `preferred_skills`, `job_category`
- `start_date`, `end_date`
- `slots_available`
- `advance_payment_amount`

Writes:
- insert into `job_posts`

### `dashboard-employer.php` (employer only)
POST actions:
- `pause_job` + `job_id`: sets `job_status='cancelled'` only if currently `active`
- `reopen_job` + `job_id`: sets `job_status='active'` only if currently `cancelled` and slots remain

## 4.3 Job details and applications

### `job-details.php` worker actions
POST actions:
- `save_job`: insert `user_interactions` (`interaction_type='save'`) if not already saved
- `unsave_job`: delete saved interaction row(s)
- `apply`: new apply or reapply
- `withdraw`: only pending applications can withdraw

Apply flow writes:
- insert or update row in `job_applications`
- notify employer via `notifications`

Reapply behavior:
- updates existing withdrawn application back to `pending`
- resets `worker_confirmed` and `employer_confirmed` to `0`

Withdraw behavior:
- updates `application_status='withdrawn'`, sets `reviewed_at=NOW()`
- notifies employer

### `job-details.php` employer actions
POST actions:
- `approve` + `application_id`
- `reject` + `application_id`

Approve flow (single joined update):
- sets application to `approved`
- sets `reviewed_at=NOW()`
- increments `job_posts.slots_filled += 1`
- only if prior status is `pending` and slots still available

Then:
- if job is full (`slots_filled >= slots_available`), sets job to `in_progress`
- inserts approval notification for worker

Reject flow:
- sets application to `rejected` only if current status `pending`
- inserts rejection notification for worker

### Job view tracking
When logged in and viewing a job:
- inserts `user_interactions` row with `interaction_type='view'`

## 4.4 Worker dashboard

`dashboard-worker.php` POST actions:
- `update_profile`: updates `payment_method`, `payment_details`, `social_links` in `users`
- `add_skill`: inserts `user_skills` if not duplicate
- `delete_skill`: deletes a skill row
- `remove_saved_job`: deletes save interaction row

## 4.5 Messaging and notifications

### `messages.php`
POST required:
- `csrf_token`, `receiver_id`, `message_content`

Writes:
- insert into `messages`
- insert `new_message` notification for receiver
- marks unread incoming messages as read when conversation is opened

### `notifications.php`
POST actions:
- `mark_read` + `notification_id`
- `mark_all_read`

Writes:
- sets `is_read=1` and `read_at=NOW()`

## 5. Validation Rules (Server-Enforced)

## 5.1 Signup
- valid CSRF token required
- valid PH mobile format
- email format checked if provided
- region code must exist in region map
- password must:
  - match confirmation
  - be at least 8 chars
  - contain at least one letter and one number
- `user_type` must be `worker` or `employer`
- max lengths enforced for name/province/city

## 5.2 Login
- valid CSRF token required
- valid PH mobile format
- throttle check before password validation
- account must be `active`

## 5.3 Post job
- valid CSRF token required
- required: title, description, city, positive pay amount
- region must be valid
- pay type must be allowed enum value
- slots range: 1 to 200
- date format `YYYY-MM-DD` when provided
- end date cannot be earlier than start date
- max lengths on title/category/location fields

## 5.4 Apply and message limits
- cover letter capped at 3000 chars
- message content capped at 2000 chars

## 5.5 Route protection
- role gates via `requireUserType()`
- global active-account enforcement for logged-in sessions

## 6. Recommendation and Score Calculations

## 6.1 Worker job recommendation score (`for-you.php`)

Formula used in SQL:

$$
match\_score = 3 \times skill\_overlap + 2 \times region\_match + 1 \times city\_match + trust\_score
$$

Where:
- `skill_overlap` = count of worker skills where `job_posts.required_skills LIKE %skill_name%`
- `region_match` = 1 if worker region equals job region, else 0
- `city_match` = 1 if worker city equals job city, else 0
- `trust_score` = value from `users.trust_score` for current worker

Feed filters:
- only `job_status='active'`
- excludes own jobs (`employer_id != worker_id`)
- excludes jobs already applied to (`job_id NOT IN worker applications`)
- keeps only rows with `match_score > 0`
- returns top 15 by score desc, then newest

Required data to produce this score:
- `users.user_id`, `users.region`, `users.city`, `users.trust_score`
- `user_skills.user_id`, `user_skills.skill_name`
- `job_posts.required_skills`, `job_posts.location_region`, `job_posts.location_city`, `job_posts.job_status`
- `job_applications.worker_id`, `job_applications.job_id`

Important implementation note:
- skill matching is substring-based (`LIKE` on comma text), not tokenized exact matching.

## 6.2 Employer recommended workers (`for-you.php`)

This path does not compute one final numeric score. It ranks by an ordered tuple.

Step 1: collect skill pool
- read up to 5 recent active jobs of employer
- parse each `required_skills` CSV text into skill list

Step 2: candidate filtering
- include only active worker accounts
- include workers with at least one matching skill in pool

Step 3: ranking fields
- `verified_skills_count = COUNT(user_skills WHERE is_verified=1)` per worker
- secondary rank is `users.trust_score`

Sort order:
1. `verified_skills_count DESC`
2. `trust_score DESC`

Required data to produce this ranking:
- `job_posts.employer_id`, `job_posts.job_status`, `job_posts.required_skills`, `job_posts.created_at`
- `users.user_type`, `users.account_status`, `users.trust_score`
- `user_skills.user_id`, `user_skills.skill_name`, `user_skills.is_verified`

## 6.3 Trending jobs (`for-you.php`, admin view)

Formula:

$$
interaction\_count = COUNT(user\_interactions.interaction\_id)\;\text{within last 7 days}
$$

Query uses:
- left join interactions with condition `ui.created_at >= NOW() - INTERVAL 7 DAY`
- groups by job
- requires `interaction_count > 0`
- orders by highest count

Required data:
- `user_interactions.job_id`, `user_interactions.created_at`
- `job_posts.job_status='active'`

## 6.4 Dashboard computed metrics

### Worker dashboard
- `total_applications = COUNT(*)`
- `approved_jobs = SUM(application_status='approved')`
- `completed_jobs = SUM(application_status='approved' AND worker_confirmed=1 AND employer_confirmed=1)`
- `pending_applications = SUM(application_status='pending')`

### Employer dashboard
- total jobs, active jobs, total applications, pending applications through subqueries

### Admin dashboard
- user and job counts via aggregate `SUM(CASE ... )`
- `completed_work = SUM(worker_confirmed=1 AND employer_confirmed=1)`
  - note: this metric does not require `application_status='approved'` in the query

## 6.5 Other computed values shown in UI
- Available slots shown as: `slots_available - slots_filled`
- Notification unread badge count from header query

## 7. Data Requirements Checklist for Generating Scores

Use this checklist if recommendations or rankings are empty.

## 7.1 Worker recommendation checklist
1. Worker has `users` row with `region`, `city`, `trust_score`.
2. Worker has at least one `user_skills` row.
3. There are active jobs in `job_posts` with non-empty `required_skills`.
4. Worker has not already applied to those jobs.
5. At least one of these must be true for each candidate job:
   - skill overlap > 0
   - same region
   - same city
   - trust score > 0
6. `match_score` must end up > 0.

## 7.2 Employer recommendation checklist
1. Employer has at least one active job.
2. Those jobs have non-empty `required_skills`.
3. Workers exist with `account_status='active'`.
4. At least one worker skill matches one required skill token.
5. Optional quality data for better ranking:
   - verified skills (`is_verified=1`)
   - non-zero trust scores

## 7.3 Trending checklist
1. `user_interactions` rows exist with `job_id`.
2. Interaction timestamps are within last 7 days.
3. Jobs are still active.

## 8. State Machines and Transition Rules

## 8.1 Job status transitions

Observed transitions in current code:
- `active -> cancelled` via employer `pause_job`
- `cancelled -> active` via employer `reopen_job` if not filled
- `active -> in_progress` after approval fills all slots

Not implemented in shown pages:
- automatic `in_progress -> completed`
- explicit transitions to `draft`

## 8.2 Application status transitions

Observed transitions:
- `pending -> withdrawn` (worker withdraw)
- `pending -> approved` (employer approve with slot check)
- `pending -> rejected` (employer reject)
- `withdrawn -> pending` (worker reapply)

Transition guards:
- only pending rows can be approved/rejected/withdrawn
- approve also requires open job slot

## 8.3 Slot counter mutation
- only approval flow increments `job_posts.slots_filled`
- no decrement path exists for reversed approvals in current pages

## 9. Notification Event Map

Events that insert notifications in current page logic:
1. Worker applies/reapplies -> employer gets `new_application`
2. Worker withdraws -> employer gets `application_status`
3. Employer approves -> worker gets `application_status`
4. Employer rejects -> worker gets `application_status`
5. User sends message -> receiver gets `new_message`

Notification page supports marking individual/all as read.

## 10. Messaging Backend Details

Conversation behavior:
- conversation partner must be active
- sending message requires valid CSRF and non-empty body
- sender cannot message self
- when opening a conversation, incoming unread messages are set read

Conversation list query computes:
- `last_message`
- `last_message_time`
- `unread_count` per contact

## 11. Discovery, Filtering, and Pagination

## 11.1 Home jobs (`index.php`)
- base filter always `job_status='active'`
- optional filters: `region`, `city`, `q`, `category`
- sort: `newest`, `pay_high`, `pay_low`
- page size fixed at 12
- count query + data query pattern with `LIMIT/OFFSET`
- stores last selected region in session (`last_region`)
- computes `regionCounts` (active jobs per region)

## 11.2 Learning hub (`skill-learn.php`)
- optional filters: `type`, `category`
- post type allowlist enforced
- sort by featured first, then newest
- page size fixed at 10

## 12. Security and Infrastructure Hardening

## 12.1 CSRF protection
- hidden token generated via `csrfField()`
- verified on all mutating actions across auth/dashboard/job/messages/notifications/logout

## 12.2 SQL injection protection
- prepared statements used across page-level DB calls

## 12.3 Output safety
- dynamic output escaped with `htmlspecialchars`

## 12.4 Apache hardening

Root `.htaccess`:
- blocks direct access to `config/` and `database/`
- blocks hidden files
- denies sensitive extensions (`.env`, `.sql`, etc.)
- sets additional security headers

`uploads/.htaccess`:
- denies script execution in uploads directory
- removes PHP handlers for uploaded files

## 13. Known Implementation Gaps (Important)

1. Trust score is persisted but not auto-recalculated in current pages.
2. `interaction_weight` exists in schema but is not used by recommendation queries.
3. `skill_posts.views_count` and `likes_count` are displayed, but no local API implementation exists in this repository snapshot to update them.
4. `js/main.js` references missing endpoints:
   - `api/search.php`
   - `api/check-notifications.php`
   - `api/track-interaction.php`
5. Admin quick-action links reference missing pages:
   - `add-skill-post.php`
   - `manage-users.php`
   - `analytics.php`
6. Several schema fields are not fully wired in current page set (`payment_released`, `contract_sent`, `work_end_time`, etc.).
7. `config/prod_database.php` contains plaintext production credentials and should be treated as sensitive operational risk.

## 14. SQL Snippets to Reproduce Score Logic Quickly

## 14.1 Worker match score (single worker)

```sql
SELECT j.job_id, j.job_title,
       (
         (SELECT COUNT(*)
          FROM user_skills us
          WHERE us.user_id = :worker_id
            AND j.required_skills LIKE CONCAT('%', us.skill_name, '%')) * 3
         + CASE WHEN j.location_region = :worker_region THEN 2 ELSE 0 END
         + CASE WHEN j.location_city = :worker_city THEN 1 ELSE 0 END
         + :worker_trust_score
       ) AS match_score
FROM job_posts j
WHERE j.job_status = 'active';
```

## 14.2 Employer ranking helper fields

```sql
SELECT u.user_id,
       (SELECT COUNT(*)
        FROM user_skills us2
        WHERE us2.user_id = u.user_id AND us2.is_verified = 1) AS verified_skills_count,
       u.trust_score
FROM users u
WHERE u.user_type = 'worker' AND u.account_status = 'active'
ORDER BY verified_skills_count DESC, u.trust_score DESC;
```

## 14.3 Trending interactions

```sql
SELECT j.job_id, COUNT(ui.interaction_id) AS interaction_count
FROM job_posts j
LEFT JOIN user_interactions ui
  ON j.job_id = ui.job_id
 AND ui.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
WHERE j.job_status = 'active'
GROUP BY j.job_id
HAVING interaction_count > 0
ORDER BY interaction_count DESC;
```

## 15. Practical Backend Change Checklist

When changing recommendation/trust logic, update all of these together:
1. SQL in `for-you.php`
2. Any fields relied on in `users`, `user_skills`, `job_posts`, `user_interactions`
3. Dashboard aggregate formulas if business meaning changes
4. This documentation file
5. Seed data if examples must reflect new logic

When changing status flows:
1. Update transition guards in `job-details.php` and `dashboard-employer.php`
2. Ensure slot counters stay consistent
3. Update notification event creation
4. Re-check dashboard aggregates dependent on status values

## 16. One-Page Memory Summary

- Worker job recommendation score = skill overlap weight + location bonus + trust score.
- Employer worker ranking = verified skill count first, trust score second.
- Trending = interaction count in last 7 days.
- Application lifecycle is controlled mainly in `job-details.php`.
- Job lifecycle controls are split between `dashboard-employer.php` and `job-details.php`.
- Login throttling is active only when `auth_rate_limits` table exists.
- CSRF + prepared statements + session hardening are the core security controls.
