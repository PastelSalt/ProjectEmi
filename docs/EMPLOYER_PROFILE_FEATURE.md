# Employer Profile & Subtype Feature

## Overview

This feature adds two major enhancements to RaketGo:

1. **Employer Subtype System** - Distinguishes between companies/businesses and individual employers
2. **Public Employer Profile Page** - A dedicated public page for each employer showing their history, ratings, and active jobs

## Why This Feature?

RaketGo serves both:
- **Companies/Businesses** - Construction firms, restaurants, retail stores hiring for ongoing roles
- **Individuals** - Homeowners needing a sink fixed, event organizers needing day-of help, etc.

The platform's goal includes helping people find quick, simple jobs (one-day gigs) alongside traditional employment. This distinction helps workers understand who they're working for.

---

## Database Changes

### Schema Updates

```sql
-- Added to users table
ALTER TABLE users 
ADD COLUMN employer_subtype ENUM('company', 'individual') DEFAULT NULL 
AFTER user_type;

ALTER TABLE users 
ADD COLUMN bio TEXT DEFAULT NULL 
AFTER social_links;

-- New table for public employer reviews
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
```

### Migration File
Run `database/migrate_employer_subtype.sql` on existing databases.

---

## Configuration

### Employer Subtypes (`config/config.php`)

```php
$EMPLOYER_SUBTYPES = [
    'company' => [
        'label' => 'Company / Business',
        'icon' => 'fa-building',
        'color' => 'blue',
        'description' => 'Registered businesses, companies, and organizations hiring for various roles',
        'examples' => ['Construction Company', 'Retail Store', 'Tech Startup', 'Restaurant']
    ],
    'individual' => [
        'label' => 'Individual / Personal',
        'icon' => 'fa-user',
        'color' => 'green',
        'description' => 'Individuals who need help with personal tasks, one-time jobs, or home services',
        'examples' => ['Homeowner needs plumbing', 'Event organizer', 'Personal assistant needed', 'Moving help']
    ]
];
```

Helper functions:
- `getEmployerSubtypeInfo($subtype)` - Returns full config array
- `getEmployerSubtypeLabel($subtype)` - Returns display label

---

## New Page: employer-profile.php

### URL Structure
```
/employer-profile.php?id={employer_id}
```

### Public Display Elements

**Header Section:**
- Profile picture (or initial avatar)
- Employer name
- Type badge (Company/Individual with icon)
- Verification badge (if verified)
- Location (city, province)
- Member since date
- Trust score with star rating
- Bio/description

**Statistics Cards:**
- Active Jobs
- Completed Jobs  
- Total Hires
- Jobs Posted

**Active Jobs Section:**
- List of currently open positions
- Pay amount and type
- Location
- Pending application count
- Required skills

**Job History Section:**
- Completed jobs
- Number of workers hired
- Completion date

**Reviews Sidebar:**
- Worker reviews with ratings (1-5 stars)
- Review text
- Reviewer avatar and name
- Date posted
- "Write a Review" button (for qualified workers only)

**Action Buttons:**
- Message employer (for logged-in workers)
- View all jobs from this employer
- Dashboard link (if viewing own profile)

---

## Updated Pages

### 1. dashboard-employer.php

**New Features:**
- Employer type selection (Company vs Individual)
- Bio/description textarea (500 char limit)
- Link to view public profile
- Character counter for bio

**Form:**
```html
<form method="POST" action="?action=update_profile_info">
    <input type="radio" name="employer_subtype" value="company"> Company/Business
    <input type="radio" name="employer_subtype" value="individual"> Individual/Personal
    <textarea name="bio" maxlength="500" placeholder="Describe your company...">
    <button type="submit">Save Profile Info</button>
    <a href="employer-profile.php?id=<?php echo $user_id; ?>" target="_blank">View Public Profile</a>
</form>
```

### 2. job-details.php

**Change:**
- Employer name is now a clickable link to their profile
- Added "View Profile" link next to employer name

### 3. index.php

**Change:**
- Employer name in job listings links to profile
- Uses `event.stopPropagation()` to prevent triggering job card click

### 4. rate-employer.php

**Change:**
- Now saves reviews to both `job_ratings` (internal) and `employer_reviews` (public)
- Notification link updated to point to employer profile
- Uses `ON DUPLICATE KEY UPDATE` for idempotent reviews

---

## Employer Type Display

### Visual Badges

**Company:**
```
[fa-building] Company / Business  (blue badge)
```

**Individual:**
```
[fa-user] Individual / Personal  (green badge)
```

**Unspecified:**
```
[fa-briefcase] Employer  (gray badge)
```

### Color Coding

| Type | Background | Text |
|------|------------|------|
| Company | --blue-light | --blue-dark |
| Individual | --green-light | --green-dark |
| Unknown | --gray-light | --gray-dark |

---

## Trust Score Display

On the employer profile, trust scores are contextualized:

| Score Range | Badge |
|-------------|-------|
| 4.5+ | 🏆 Top Rated Employer |
| 4.0-4.49 | ✓ Highly Trusted |
| 3.0-3.99 | 👍 Good Standing |
| < 3.0 | ℹ️ Building Reputation |

---

## Review System

### Who Can Review?

Only workers who:
1. Were approved for a job with this employer
2. Both parties confirmed completion (`worker_confirmed = 1` AND `employer_confirmed = 1`)
3. Haven't already reviewed this specific job

### Review Flow

1. Worker completes job → both confirm
2. Worker visits `rate-employer.php?app_id={id}`
3. Rating (1-5 stars) and optional text review submitted
4. Saved to both `job_ratings` and `employer_reviews` tables
5. Employer trust score recalculated
6. Employer receives notification with link to their profile

---

## Integration Points

### For Workers
- View employer profile before applying
- See employer's job history and ratings
- Read reviews from other workers
- Rate employers after completing jobs

### For Employers  
- Choose type during registration/edit profile
- Write bio to attract workers
- Build reputation through ratings
- Public profile showcases reliability

### For Public
- Browse employer profiles without login
- See job history and ratings
- Transparent hiring record

---

## Future Enhancements

Potential additions:

1. **Employer Verification** - Document upload for company verification
2. **Portfolio/Photos** - Employers can showcase past work
3. **Response Time Metrics** - Track how quickly employers respond
4. **Hire Rate** - Percentage of posted jobs that get filled
5. **Repeat Worker Rate** - How often workers return for more jobs
6. **Social Proof** - "Trusted by X workers" badges
7. **Employer Categories** - Tag employers by industry
8. **Featured Employers** - Highlight top-rated employers

---

## Testing Checklist

- [ ] Select employer type in dashboard
- [ ] Write and save bio
- [ ] View public profile as owner
- [ ] View public profile as other user
- [ ] View public profile while logged out
- [ ] Click employer name in job listing → goes to profile
- [ ] Click employer name in job details → goes to profile
- [ ] Submit review as qualified worker
- [ ] Verify review appears on profile
- [ ] Check trust score updates after review
- [ ] Verify character counter on bio field
- [ ] Test mobile responsiveness of profile page

---

## Files Modified/Created

### New Files:
- `employer-profile.php` - Public employer profile page
- `database/migrate_employer_subtype.sql` - Database migration

### Modified Files:
- `config/config.php` - Added employer subtype config
- `database/schema.sql` - Added employer_subtype, bio columns and employer_reviews table
- `dashboard-employer.php` - Added profile editing form
- `job-details.php` - Linked employer name to profile
- `index.php` - Linked employer names to profiles
- `rate-employer.php` - Saves to employer_reviews table

---

**Version**: 1.0  
**Created**: RaketGo Platform  
**Feature**: Employer Profiles & Subtypes  
**© Moesoft (Moeko Software)**
