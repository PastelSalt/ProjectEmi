# Job Types Enhancement Documentation

## Overview
The job type system has been comprehensively enhanced to provide better organization, validation, and user experience throughout the application.

## Configuration System

### Job Types Config (`config/config.php`)

```php
$JOB_TYPES_CONFIG = [
    'full_time' => [
        'label' => 'Full Time',
        'icon' => 'fa-briefcase',
        'color' => 'blue',
        'suggested_pay_types' => ['monthly', 'fixed'],
        'default_pay_type' => 'monthly',
        'min_duration_days' => 30,
        'description' => 'Long-term employment with regular hours'
    ],
    'part_time' => [
        'label' => 'Part Time',
        'icon' => 'fa-clock',
        'color' => 'pink',
        'suggested_pay_types' => ['hourly', 'daily', 'monthly'],
        'default_pay_type' => 'hourly',
        'min_duration_days' => 7,
        'description' => 'Flexible hours, less than full-time'
    ],
    'contractual' => [
        'label' => 'Contractual',
        'icon' => 'fa-file-contract',
        'color' => 'orange',
        'suggested_pay_types' => ['fixed', 'monthly'],
        'default_pay_type' => 'fixed',
        'min_duration_days' => 1,
        'description' => 'Project-based work with defined deliverables'
    ],
    'one_time' => [
        'label' => 'One-time / Gig',
        'icon' => 'fa-bolt',
        'color' => 'green',
        'suggested_pay_types' => ['fixed'],
        'default_pay_type' => 'fixed',
        'min_duration_days' => 1,
        'max_duration_days' => 7,
        'description' => 'Single task or short-term gig'
    ],
    'internship' => [
        'label' => 'Internship',
        'icon' => 'fa-graduation-cap',
        'color' => 'purple',
        'suggested_pay_types' => ['fixed', 'monthly', 'hourly'],
        'default_pay_type' => 'monthly',
        'min_duration_days' => 30,
        'description' => 'Learning opportunity for students/entry-level'
    ]
];
```

### Pay Types Config

```php
$PAY_TYPES_CONFIG = [
    'fixed' => ['label' => 'Fixed Price', 'icon' => 'fa-money-bill-wave'],
    'hourly' => ['label' => 'Per Hour', 'icon' => 'fa-clock'],
    'daily' => ['label' => 'Per Day', 'icon' => 'fa-calendar-day'],
    'monthly' => ['label' => 'Per Month', 'icon' => 'fa-calendar-alt']
];
```

## Helper Functions Added

### `isValidJobPayCombination($jobType, $payType)`
Validates if a job type and pay type combination is appropriate.

### `getJobTypeInfo($jobType)`
Returns full configuration array for a job type.

### `getPayTypeInfo($payType)`
Returns full configuration array for a pay type.

### `validateJobDuration($jobType, $startDate, $endDate)`
Validates if job duration matches the job type requirements.
- Returns `['valid' => true]` on success
- Returns `['valid' => false, 'error' => '...']` on failure

## Features Implemented

### 1. Dynamic Job Type Selection (post-job.php)
- Job type dropdown uses configuration-based options
- Shows job type description below selector
- Pay type options dynamically update based on job type
- Recommended pay types shown first with "(Recommended)" label
- Non-recommended pay types available but clearly marked
- Duration validation in real-time (JavaScript)

**JavaScript Features:**
- `updatePayTypeOptions()` - Rebuilds pay type dropdown on job type change
- Real-time duration validation based on job type min/max days
- Visual hints showing suggested pay types

### 2. Job Type Validation (post-job.php)
- Server-side validation for job type/pay type combinations
- Duration validation against job type requirements
- Clear error messages suggesting appropriate pay types
- Example error: "For Full Time positions, we suggest using: Per Month, Fixed Price"

### 3. Job Type Filtering (index.php)
- Sidebar widget with all job types as filter buttons
- Icons displayed for each job type
- Active filter highlighted
- Click to toggle filter on/off
- Integrates with existing filters (region, category, etc.)

### 4. Job Type Display (index.php & job-details.php)
- Color-coded badges with appropriate icons
- Full Time = Blue
- Part Time = Pink  
- Contractual = Orange
- One-time/Gig = Green
- Internship = Purple

**Listing Display (index.php):**
```html
<span class="tag job-pill job-pill-type" style="background: var(--blue-light); color: var(--blue-dark);">
    <i class="fas fa-briefcase"></i>
    Full Time
</span>
```

**Detail Display (job-details.php):**
```html
<span class="tag" style="background: var(--blue-light); color: var(--blue-dark);">
    <i class="fas fa-briefcase"></i>
    Full Time
</span>
```

### 5. URL Building Updates
All URL building functions updated to preserve job_type filter:
- `$buildRegionUrl()` - Includes job_type in URLs
- `$buildJobsPageUrl()` - Includes job_type in URLs
- `buildFilterUrl()` - New helper for filter widgets

## Database
No schema changes required - uses existing `job_type` column in `job_posts` table.

## CSS Variables Required
Add these to your CSS for job type colors:

```css
:root {
    --blue-light: #e3f2fd;
    --blue-dark: #1565c0;
    --pink-light: #fce4ec;
    --pink-dark: #c2185b;
    --orange-light: #fff3e0;
    --orange-dark: #e65100;
    --green-light: #e8f5e9;
    --green-dark: #2e7d32;
    --purple-light: #f3e5f5;
    --purple-dark: #7b1fa2;
}
```

## Usage Examples

### Posting a Job
1. Select job type from dropdown
2. Description appears below explaining the job type
3. Pay type options update to show recommended options first
4. Set start/end dates - validation occurs in real-time
5. Submit - server validates combination is appropriate

### Filtering Jobs
1. Click job type filter in sidebar
2. Jobs list filters to show only selected type
3. URL updates to include `job_type` parameter
4. Pagination and other filters preserved

### Viewing Job Details
- Job type displayed as color-coded badge with icon
- Helps workers quickly identify the type of opportunity

## Validation Rules Summary

| Job Type | Suggested Pay Types | Min Duration | Max Duration |
|----------|---------------------|--------------|--------------|
| Full Time | Monthly, Fixed | 30 days | None |
| Part Time | Hourly, Daily, Monthly | 7 days | None |
| Contractual | Fixed, Monthly | 1 day | None |
| One-time | Fixed | 1 day | 7 days |
| Internship | Fixed, Monthly, Hourly | 30 days | None |

## Testing Checklist

- [ ] Post job with each job type
- [ ] Verify pay type options update dynamically
- [ ] Try invalid combinations (should show error)
- [ ] Verify duration validation works
- [ ] Filter jobs by each job type
- [ ] Verify job type badges display correctly
- [ ] Check URL parameters preserve filters
- [ ] Test with multiple filters combined
