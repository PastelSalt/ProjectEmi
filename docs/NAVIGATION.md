# RaketGo — Codebase Navigation Guide

> **Quick reference for developers.**  
> This file maps every route, config, and data flow so you can jump straight to the code you need without reading the entire repo.

---

## 1. Project at a Glance

| Item | Value |
|------|-------|
| **Name** | RaketGo |
| **Stack** | PHP 8.0+, MySQL 5.7+, Apache |
| **Author** | Moesoft (Moeko Software) |
| **Root** | `c:/wamp64/www/ProjectEmi` |
| **Entry** | `index.php` |
| **Config** | `config/config.php` + `config/database.php` |
| **Schema** | `database/schema.sql` |
| **Seed** | `database/sample_data.sql` |

---

## 2. Route → File Map

### 2.1 Public Pages (no login required)

| Route | File | What it does |
|-------|------|--------------|
| `/` | `index.php` | Job discovery, search, filters (region/city/category/**remote policy**), region map, pagination (12/page) |
| `/login.php` | `login.php` | Login with CSRF + brute-force throttle (`auth_rate_limits`) |
| `/signup.php` | `signup.php` | Registration (worker/employer), mobile validation, bcrypt hash |
| `/skill-learn.php` | `skill-learn.php` | Learning hub, filter by type/category, pagination (10/page) |
| `/job-details.php?id=X` | `job-details.php` | Job detail, apply/withdraw/save, employer approve/reject |

### 2.2 Authenticated Pages (all roles)

| Route | File | What it does |
|-------|------|--------------|
| `/logout.php` | `logout.php` | POST-only logout, CSRF check, session destroy |
| `/for-you.php` | `for-you.php` | **Recommendations** — role-aware feed (worker jobs / employer workers / trending) |
| `/messages.php` | `messages.php` | Conversations, send message, mark read |
| `/notifications.php` | `notifications.php` | Notification list, mark one/all read |
| `/rate-worker.php` | `rate-worker.php` | Employer rates worker after job completion |
| `/rate-employer.php` | `rate-employer.php` | Worker rates employer after job completion |

### 2.3 Role-Specific Dashboards

| Role | Route | File | Key Actions |
|------|-------|------|-------------|
| **Worker** | `/dashboard-worker.php` | `dashboard-worker.php` | Update profile, add/delete skills, view saved jobs, metrics |
| **Employer** | `/dashboard-employer.php` | `dashboard-employer.php` | View posted jobs, pause/reopen jobs, review applicants, metrics |
| **Admin** | `/dashboard-admin.php` | `dashboard-admin.php` | Platform stats, user/job counts, trust score audits, quick actions |

### 2.4 Admin-Only Management

| Route | File | What it does |
|-------|------|--------------|
| `/post-job.php` | `post-job.php` | **Employer only** — create new job listing (includes remote policy: on-site / hybrid / fully remote) |
| `/add-skill-post.php` | `add-skill-post.php` | **Admin only** — create learning hub content |
| `/manage-users.php` | `manage-users.php` | **Admin only** — search, filter, manage user accounts |
| `/analytics.php` | `analytics.php` | **Admin only** — user stats, job analytics, skill metrics, trust audits |

---

## 3. Core Includes & Assets

| File | Purpose |
|------|---------|
| `includes/header.php` | HTML head, navbar, active-link logic, notification badge query, CSRF field helper |
| `includes/footer.php` | Closing tags, footer markup |
| `config/config.php` | **Bootstrap** — env detection, session hardening, security headers, helpers (auth, CSRF, rate-limit, sanitize, trust score) |
| `config/database.php` | DB connection, `executeQuery()`, `fetchOne()`, `fetchAll()` |
| `config/prod_database.php` | Production DB credentials *(sensitive — do not commit plaintext)* |
| `css/style.css` | All styles (single file) |
| `js/main.js` | Frontend JS (references missing API endpoints — see Gaps) |

---

## 4. Database Quick Reference

### 4.1 Tables (in dependency order)

```
users
├── user_skills
├── job_posts
│   ├── job_applications
│   │   ├── digital_contracts
│   │   ├── job_ratings
│   │   └── transactions
│   └── user_interactions
├── messages
├── notifications
├── skill_posts
├── auth_rate_limits
└── trust_score_updates
```

### 4.2 Enum Values

| Table.Column | Allowed Values |
|--------------|----------------|
| `users.user_type` | `admin`, `employer`, `worker` |
| `users.account_status` | `active`, `suspended`, `deleted` |
| `job_posts.pay_type` | `hourly`, `daily`, `fixed`, `monthly` |
| `job_posts.job_status` | `draft`, `active`, `in_progress`, `completed`, `cancelled` |
| `job_posts.remote_policy` | `on_site`, `hybrid`, `fully_remote` |
| `job_applications.application_status` | `pending`, `approved`, `rejected`, `withdrawn` |
| `skill_posts.post_type` | `certification`, `training`, `course`, `workshop` |
| `user_interactions.interaction_type` | `view`, `apply`, `save`, `like`, `share`, `click` |
| `transactions.transaction_type` | `deposit`, `withdrawal`, `payment`, `refund`, `advance` |
| `transactions.status` | `pending`, `completed`, `failed`, `cancelled` |
| `job_ratings.rating_type` | `employer_to_worker`, `worker_to_employer` |

### 4.3 Key Indexes for Performance

- `job_posts`: `(job_status, location_region, created_at)`, `(job_status, job_category, created_at)`, `(job_status, pay_amount, created_at)`
- `user_skills`: `(user_id, skill_name)` UNIQUE
- `job_applications`: `(job_id, worker_id)` UNIQUE
- `messages`: `(receiver_id, is_read)`, `(sender_id, receiver_id)`
- `notifications`: `(user_id, is_read, created_at)`
- `user_interactions`: `(user_id, job_id, interaction_type)`
- `skill_posts`: `(post_type, is_featured, created_at)`, `(category, is_featured, created_at)`

---

## 5. Business Logic Cheat Sheet

### 5.1 Worker Job Match Score (`for-you.php`)

```
match_score = (skill_overlap × 3)
            + (region_match ? 2 : 0)
            + (city_match ? 1 : 0)
            + trust_score
```

- **Skill overlap**: `COUNT(worker_skills WHERE job.required_skills LIKE %skill%)`
- **Filters**: `job_status='active'`, exclude own jobs, exclude already applied
- **Sort**: `match_score DESC`, then newest
- **Limit**: 15 results

### 5.2 Employer Worker Ranking (`for-you.php`)

1. Collect `required_skills` from up to 5 recent active jobs
2. Find active workers with at least one matching skill
3. **Sort**: `verified_skills_count DESC`, then `trust_score DESC`

### 5.3 Trending Jobs (`for-you.php`)

```
interaction_count = COUNT(user_interactions)
                    WHERE created_at >= NOW() - INTERVAL 7 DAY
```

- Group by `job_id`, require `interaction_count > 0`
- Sort by highest count

### 5.4 Trust Score

```
trust_score = AVG(rating_stars) FROM job_ratings WHERE ratee_id = ?
```

- Range: `0.00` – `5.00`
- Updated in real-time after each rating
- Audit logged to `trust_score_updates`

### 5.5 Application Status Flow

```
pending ──► approved  (employer action + slot check)
pending ──► rejected  (employer action)
pending ──► withdrawn (worker action)
withdrawn ──► pending (worker reapply)
```

### 5.6 Job Status Flow

```
active ──► cancelled   (employer pause)
cancelled ──► active   (employer reopen, if slots open)
active ──► in_progress (auto when slots_filled >= slots_available)
```

---

## 6. Security Controls

| Control | Location | Details |
|---------|----------|---------|
| **CSRF** | `config/config.php` | `getCsrfToken()`, `verifyCsrfToken()`, `csrfField()` — used on all mutating forms |
| **SQL Injection** | `config/database.php` | All queries use prepared statements (`executeQuery()`) |
| **XSS** | Output layer | `htmlspecialchars()` on all dynamic echo in views |
| **Session Hardening** | `config/config.php` | `HttpOnly`, `SameSite=Lax`, secure on HTTPS, strict mode, regenerate on login |
| **Rate Limiting** | `config/config.php` | Login: 6 attempts / 15 min window, 15 min lockout |
| **Account Enforcement** | `config/config.php` | `enforceActiveSessionUser()` — kills session if account not `active` |
| **Input Sanitization** | `config/config.php` | `sanitizeInput()`, `sanitizeMultilineInput()`, mobile/region validators |
| **URL Safety** | `config/config.php` | `sanitizeInternalUrl()` blocks open redirects & path traversal |
| **Apache Hardening** | `.htaccess` | Blocks `config/`, `database/`, hidden files, `.env`, `.sql` |
| **Upload Safety** | `uploads/.htaccess` | Denies script execution, removes PHP handlers |

---

## 7. Notification Event Map

| Trigger | Recipient | Type | Created In |
|---------|-----------|------|------------|
| Worker applies/reapplies | Employer | `new_application` | `job-details.php` |
| Worker withdraws | Employer | `application_status` | `job-details.php` |
| Employer approves | Worker | `application_status` | `job-details.php` |
| Employer rejects | Worker | `application_status` | `job-details.php` |
| User sends message | Receiver | `new_message` | `messages.php` |
| Rating submitted | Rated user | `trust_score_update` | `rate-worker.php` / `rate-employer.php` |

---

## 8. Known Implementation Gaps

> Copied from backend docs for visibility. These are **expected** missing pieces in this snapshot.

1. **Missing API endpoints** (referenced in `js/main.js`):
   - `api/search.php`
   - `api/check-notifications.php`
   - `api/track-interaction.php`

2. **Admin quick-action links** reference pages that may be stubbed:
   - `add-skill-post.php`
   - `manage-users.php`
   - `analytics.php`

3. **Schema fields not fully wired** in current page set:
   - `payment_released`, `contract_sent`, `work_end_time`, etc.

4. **Skill post engagement**: `views_count` / `likes_count` displayed but no local API to increment them.

5. **Production credential risk**: `config/prod_database.php` contains plaintext credentials.

---

## 9. Developer Quick-Start

### 9.1 "I want to change how recommendations work"

1. Edit SQL in **`for-you.php`** (worker score / employer ranking / trending)
2. Check dependent fields in `users`, `user_skills`, `job_posts`, `user_interactions`
3. Update dashboard aggregates if business meaning changes
4. Update this file + `website logic and backend documentation.md`

### 9.2 "I want to add a new job status transition"

1. Add guard logic in **`job-details.php`** or **`dashboard-employer.php`**
2. Ensure `slots_filled` stays consistent
3. Add notification event if user-facing
4. Re-check dashboard aggregate queries

### 9.3 "I want to add a new page"

1. Create `.php` file in root
2. Include `config/config.php` at the top
3. Use `requireUserType('worker|employer|admin')` if role-gated
4. Include `includes/header.php` and `includes/footer.php`
5. Add nav link in `includes/header.php` if needed
6. Document here in Section 2

### 9.4 "I need to check the database schema"

```bash
# Full schema
database/schema.sql

# Sample data (test credentials inside)
database/sample_data.sql
```

---

## 10. File Tree (Simplified)

```
ProjectEmi/
├── .htaccess                          # Apache hardening
├── index.php                          # Home / job discovery
├── login.php                          # Auth
├── signup.php                         # Registration
├── logout.php                         # Session termination
├── for-you.php                        # Recommendations feed
├── job-details.php                    # Job detail + applications
├── post-job.php                       # Employer job creation
├── dashboard-worker.php               # Worker dashboard
├── dashboard-employer.php             # Employer dashboard
├── dashboard-admin.php                # Admin dashboard
├── messages.php                       # Chat
├── notifications.php                  # Alerts
├── skill-learn.php                    # Learning hub
├── rate-worker.php                    # Employer → worker rating
├── rate-employer.php                  # Worker → employer rating
├── add-skill-post.php                 # Admin content creation
├── manage-users.php                   # Admin user management
├── analytics.php                      # Admin analytics
├── config/
│   ├── config.php                     # Bootstrap + helpers
│   ├── database.php                   # DB layer
│   └── prod_database.php              # Production credentials
├── includes/
│   ├── header.php                     # Nav + head
│   └── footer.php                     # Footer
├── css/
│   └── style.css                      # All styles
├── js/
│   └── main.js                        # Frontend scripts
├── database/
│   ├── schema.sql                     # Full DDL
│   └── sample_data.sql                # Seed data
├── uploads/
│   ├── .htaccess                      # Upload security
│   ├── documents/
│   ├── posts/
│   └── profiles/
├── README.md                          # Project overview
├── website logic and backend documentation.md  # Deep backend reference
├── Documentation Form A.md            # Concept doc
├── Documentation Form B.md            # Feature doc
└── NAVIGATION.md                      # ← You are here
```

---

## 11. Test Credentials (from seed data)

| Role | Mobile | Password |
|------|--------|----------|
| Admin | `09560618349` | `matsuzakasatou` |
| Other | (any seeded user) | `password` |

---

*Last updated: March 2026*  
*If you add/remove pages or change core flows, update this file so the next developer doesn't get lost.*

