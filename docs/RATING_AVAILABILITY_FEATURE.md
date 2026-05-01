# Rating Availability by Job Type Feature

## Overview

This feature implements job-type-specific rating availability rules:

| Job Type | Rating Availability |
|----------|---------------------|
| **One-time / Gig** | Immediate after both parties confirm completion |
| **Full-time, Part-time, Contractual, Internship** | 3-day delay after both parties confirm |

## Why This Feature?

Different job types require different rating timelines:

- **One-time jobs** (fixing a sink, moving help, event setup) are completed quickly. Workers can rate immediately after the single task is done.

- **Long-term jobs** need time for proper assessment. A 3-day cooling-off period allows:
  - Final quality checks
  - Post-completion follow-ups
  - Proper evaluation of the full employment experience
  - Prevention of premature emotional ratings

## How It Works

### Flow Diagram

```
Job Completion
     ↓
Worker confirms completion
     ↓
Employer confirms completion
     ↓
System checks job type
     ↓
┌─────────────────┬─────────────────┐
│   One-time Job  │  Long-term Job  │
│  Rating: NULL   │  Rating: NOW()+3d│
│  (immediate)    │  (3-day delay)  │
└─────────────────┴─────────────────┘
     ↓
rating_available_at stored in DB
     ↓
Worker sees rating button OR wait message
```

## Database Schema

### New Columns in `job_applications` table

```sql
-- Track when both parties confirmed (for audit and delay calculation)
both_confirmed_at TIMESTAMP NULL,

-- Rating availability timestamp
-- NULL = available immediately
-- TIMESTAMP = available after this datetime
rating_available_at TIMESTAMP NULL,

-- Track payment status (for future payment-gating feature)
payment_completed BOOLEAN DEFAULT FALSE
```

### Indexes

```sql
CREATE INDEX idx_rating_available ON job_applications(rating_available_at);
CREATE INDEX idx_both_confirmed ON job_applications(both_confirmed_at);
```

## Implementation Details

### 1. Job Type Classification

```php
$immediateRatingTypes = ['one_time'];
$delayedRatingTypes = ['full_time', 'part_time', 'contractual', 'internship'];
```

### 2. Setting Rating Availability

When **employer** confirms (and worker already confirmed):
```php
$jobType = $appData['job_type'];

if (in_array($jobType, ['one_time'])) {
    $ratingAvailableAt = null; // Immediate
    $ratingMsg = 'Rating is now available immediately.';
} elseif (in_array($jobType, ['full_time', 'part_time', 'contractual', 'internship'])) {
    $ratingAvailableAt = date('Y-m-d H:i:s', strtotime('+3 days'));
    $ratingMsg = 'Rating will be available in 3 days.';
}
```

When **worker** confirms (and employer already confirmed):
- Same logic applies
- The second confirmer triggers the rating availability calculation

### 3. Checking Rating Availability

```php
$isRatingAvailable = true;

if (!empty($application['rating_available_at'])) {
    $ratingAvailableTime = strtotime($application['rating_available_at']);
    $currentTime = time();

    if ($currentTime < $ratingAvailableTime) {
        $isRatingAvailable = false;
        // Calculate remaining time
        $hoursRemaining = ceil(($ratingAvailableTime - $currentTime) / 3600);
        $daysRemaining = ceil($hoursRemaining / 24);
    }
}
```

## UI Updates

### 1. Job Details Page (`job-details.php`)

**When rating is NOT yet available:**
```
┌─────────────────────────────────────┐
│  ⏱ Rating will be available in    │
│     2 days (cooling-off period    │
│     for long-term jobs)            │
│                                    │
│  Long-term jobs have a 3-day       │
│  assessment period.                │
└─────────────────────────────────────┘
```

**When rating IS available (one-time job):**
```
┌─────────────────────────────────────┐
│  [⭐ Rate This Employer]            │
│  One-time job: Rating available     │
│  immediately                        │
└─────────────────────────────────────┘
```

### 2. Worker Dashboard (`dashboard-worker.php`)

**Application History Table:**

| Job | Employer | Status | Rating |
|-----|----------|--------|--------|
| Plumbing Fix | Maria Santos | Completed | ⭐ Rate |
| Store Clerk | Retail Inc | Completed | ⏱ In 2 days |
| Web Dev | Tech Startup | Completed | ⭐ Rated |

### 3. Rate Employer Page (`rate-employer.php`)

If worker tries to access rating page before availability:
- Redirects to dashboard with flash message
- Shows: "Rating will be available in X days. Long-term jobs have a 3-day cooling-off period."

## Migration

Run the migration script:
```bash
mysql -u username -p database_name < database/migrate_employer_subtype.sql
```

The migration adds:
- `rating_available_at` column
- `both_confirmed_at` column  
- `payment_completed` column
- Supporting indexes

## Testing Scenarios

### Scenario 1: One-time Job (Immediate Rating)
1. Post a "one_time" job
2. Worker applies, gets approved
3. Both confirm completion
4. **Expected:** Rating button appears immediately
5. Click rating button → Rating form loads successfully

### Scenario 2: Full-time Job (3-Day Delay)
1. Post a "full_time" job
2. Worker applies, gets approved
3. Both confirm completion
4. **Expected:** Wait message shows "Rating will be available in 3 days"
5. Try to access rate-employer.php → Redirected with info message
6. Wait 3 days → Rating button appears

### Scenario 3: Worker Confirms First
1. Worker confirms completion first
2. **Expected:** No rating availability set yet (waiting for employer)
3. Employer confirms
4. **Expected:** Rating availability calculated based on job type

### Scenario 4: Employer Confirms First
1. Employer confirms completion first
2. **Expected:** No rating availability set yet (waiting for worker)
3. Worker confirms
4. **Expected:** Rating availability calculated based on job type

## Notifications

The system sends contextual notifications:

**One-time job confirmation:**
> "The employer has confirmed the work is complete. Rating is now available immediately."

**Long-term job confirmation:**
> "The employer has confirmed the work is complete. Rating will be available in 3 days (allowing time for final assessment)."

## Future Enhancements

Potential improvements:

1. **Configurable delays** - Admin setting for each job type's rating delay
2. **Payment gating** - Only allow rating after payment is confirmed
3. **Mutual rating window** - Both parties must rate within X days of availability
4. **Grace period editing** - Allow rating edits within 24 hours
5. **Dispute hold** - Delay ratings if a dispute is filed

## Files Modified

- `database/schema.sql` - Added columns and indexes
- `database/migrate_employer_subtype.sql` - Migration script
- `job-details.php` - Confirmation logic and rating display
- `rate-employer.php` - Rating availability validation
- `dashboard-worker.php` - Rating status in application history

---

**Version**: 1.0  
**Created**: RaketGo Platform  
**Feature**: Rating Availability by Job Type  
**© Moesoft (Moeko Software)**
