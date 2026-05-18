# RaketGo — Codebase Navigation Guide

> **Quick reference for developers.**  
> This file maps every route, config, and data flow so you can jump straight to the code you need without reading the entire repo.

---

## 1. Project at a Glance

| Item | Value |
|------|-------|
| **Name** | RaketGo + RaketKo |
| **Stack** | PHP 8.0+, MySQL 5.7+, Apache |
| **Author** | Moesoft (Moeko Software) |
| **Entry** | `index.php` |
| **Config** | `config/config.php` + `config/database.php` |
| **Schema** | `database/schema.sql` |
| **Seed** | `database/sample_data.sql` |

---

## 2. Route → File Map

### 2.1 Public Pages (no login required)

| Route | File | What it does |
|-------|------|--------------|
| `/` | `index.php` | Job discovery, search, filters, region map, pagination |
| `/login.php` | `login.php` | Login with CSRF + brute-force throttle |
| `/signup.php` | `signup.php` | Registration (worker/employer), mobile validation, bcrypt hash |
| `/skill-learn.php` | `skill-learn.php` | Learning hub, filter by type/category, pagination |
| `/job-details.php?id=X` | `job-details.php` | Job detail, apply/withdraw/save, employer approve/reject |
| `/terms.php` | `terms.php` | Terms & Conditions page |

### 2.2 Authenticated Pages (all roles)

| Route | File | What it does |
|-------|------|--------------|
| `/logout.php` | `logout.php` | POST-only logout, CSRF check, session destroy |
| `/for-you.php` | `for-you.php` | **Recommendations** — role-aware feed (worker jobs / employer workers / trending) |
| `/messages.php` | `messages.php` | Conversations, send message, mark read |
| `/notifications.php` | `notifications.php` | Notification list, mark one/all read |
| `/notification-settings.php` | `notification-settings.php` | Notification preferences management |
| `/rate-worker.php` | `rate-worker.php` | Employer rates worker after job completion |
| `/rate-employer.php` | `rate-employer.php` | Worker rates employer after job completion |

### 2.3 Profile Pages

| Role | Route | File | Key Actions |
|------|-------|------|-------------|
| **Worker** | `/worker-profile.php?id=X` | `worker-profile.php` | View comprehensive profile, portfolio, skills, employment history |
| **Employer** | `/employer-profile.php?id=X` | `employer-profile.php` | View employer profile, company info, reviews, job history |
| **Admin** | N/A | N/A | Uses dashboard-admin.php (no public profile) |

### 2.4 Role-Specific Dashboards

| Role | Route | File | Key Actions |
|------|-------|------|-------------|
| **Worker** | `/dashboard-worker.php` | `dashboard-worker.php` | View applications, saved jobs, skills management, metrics |
| **Employer** | `/dashboard-employer.php` | `dashboard-employer.php` | View posted jobs, pause/reopen jobs, review applicants, metrics |
| **Admin** | `/dashboard-admin.php` | `dashboard-admin.php` | Platform stats, user/job counts, trust score audits, quick actions |

### 2.5 Admin-Only Management

| Route | File | What it does |
|-------|------|--------------|
| `/post-job.php` | `post-job.php` | **Employer only** — create new job listing |
| `/add-skill-post.php` | `add-skill-post.php` | **Admin only** — create learning hub content |
| `/manage-users.php` | `manage-users.php` | **Admin only** — search, filter, manage user accounts |
| `/analytics.php` | `analytics.php` | **Admin only** — user stats, job analytics, skill metrics |

### 2.6 RaketKo Social Features

| Route | File | What it does |
|-------|------|--------------|
| `/raketko-feed.php` | `raketko-feed.php` | Social media feed with posts, likes, comments |
| `/raketko-profile.php?id=X` | `raketko-profile.php` | Social profile view, posts, followers, following |
| `/worker-portfolio.php` | `worker-portfolio.php` | Enhanced portfolio management with projects |

---

## 3. Core Includes & Assets

| File | Purpose |
|------|---------|
| `includes/header.php` | HTML head, navbar, active-link logic, notification badge query, CSRF field helper |
| `includes/footer.php` | Closing tags, footer markup |
| `config/config.php` | **Bootstrap** — env detection, session hardening, security headers, helpers (auth, CSRF, rate-limit, sanitize, trust score) |
| `config/database.php` | DB connection, `executeQuery()`, `fetchOne()`, `fetchAll()` |
| `css/style.css` | All styles (single file) |
| `js/main.js` | Frontend JavaScript |

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
├── social_profiles
├── social_posts
├── social_likes
├── social_follows
├── auth_rate_limits
└── trust_score_updates
```

### 4.2 Key Enum Values

| Table.Column | Allowed Values |
|--------------|----------------|
| `users.user_type` | `admin`, `employer`, `worker` |
| `users.account_status` | `active`, `suspended`, `deleted` |
| `job_posts.pay_type` | `hourly`, `daily`, `fixed`, `monthly` |
| `job_posts.job_status` | `draft`, `active`, `in_progress`, `completed`, `cancelled` |
| `job_posts.remote_policy` | `on_site`, `hybrid`, `fully_remote` |
| `job_applications.application_status` | `pending`, `approved`, `rejected`, `withdrawn` |
| `skill_posts.post_type` | `certification`, `training`, `course`, `workshop` |
| `social_posts.post_type` | `career_update`, `achievement`, `insight`, `professional_tip` |

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

### 5.2 Trust Score

```
trust_score = AVG(rating_stars) FROM job_ratings WHERE ratee_id = ?
```

- Range: `0.00` – `5.00`
- Updated in real-time after each rating
- Audit logged to `trust_score_updates`

### 5.3 Application Status Flow

```
pending ──► approved  (employer action + slot check)
pending ──► rejected  (employer action)
pending ──► withdrawn (worker action)
withdrawn ──► pending (worker reapply)
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

---

## 7. Developer Quick-Start

### 7.1 "I want to change how recommendations work"

1. Edit SQL in **`for-you.php`** (worker score / employer ranking / trending)
2. Check dependent fields in `users`, `user_skills`, `job_posts`, `user_interactions`
3. Update dashboard aggregates if business meaning changes
4. Update this file

### 7.2 "I want to add a new page"

1. Create `.php` file in root
2. Include `config/config.php` at the top
3. Use `requireUserType('worker|employer|admin')` if role-gated
4. Include `includes/header.php` and `includes/footer.php`
5. Add nav link in `includes/header.php` if needed
6. Document here in Section 2

### 7.3 "I need to check the database schema"

```bash
# Full schema
database/schema.sql

# Sample data (test credentials inside)
database/sample_data.sql
```

---

## 8. File Tree (Simplified)

```
ProjectEmi/
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
├── worker-profile.php                 # Worker public profile
├── employer-profile.php               # Employer public profile
├── raketko-feed.php                   # Social media feed
├── raketko-profile.php                # Social profile
├── worker-portfolio.php               # Portfolio management
├── terms.php                          # Terms & Conditions
├── config/
│   ├── config.php                     # Bootstrap + helpers
│   └── database.php                   # DB layer
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
└── uploads/                           # User uploads
```

---

## 9. Test Credentials

| Role | Mobile | Password |
|------|--------|----------|
| Admin | `09560618349` | `matsuzakatou` |
| Other | (any seeded user) | `password` |

---

*Last updated: May 2026*  
*If you add/remove pages or change core flows, update this file so the next developer doesn't get lost.*
