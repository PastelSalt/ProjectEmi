# Operations Logic Index

This index categorizes all operational logic, business processes, and workflow operations in the RaketGo job matching platform.

## Job Operations

### Job Posting
- **post-job.php** - Job creation logic:
  - Job title and description validation
  - Location data entry (region, province, city, specific address)
  - Pay amount and type configuration
  - Job type selection (full_time, part_time, contractual, one_time, internship)
  - Remote work policy (on_site, hybrid, fully_remote)
  - Required and preferred skills entry
  - Job category selection
  - Start and end date configuration
  - Slot availability management
  - Advance payment amount
  - Job image upload (optional, 5MB limit)
  - Job type/pay type combination validation
  - Job duration validation based on type
  - Employer ID association
  - Job status set to 'active' by default

### Job Management
- **dashboard-employer.php** - Job control operations:
  - Pause job posting (status: active → cancelled)
  - Reopen job posting (status: cancelled → active)
  - Job status validation before operations
  - Slot availability checking for reopen
  - Application count tracking
  - Pending application counting

### Job Search & Filtering
- **index.php** - Job discovery operations:
  - Full-text search (title, description, skills)
  - Region-based filtering
  - City-based filtering
  - Job category filtering
  - Job type filtering
  - Remote policy filtering
  - Pay-based sorting (high to low, low to high)
  - Recency sorting (newest first)
  - Pagination (12 jobs per page)
  - Total count calculation
  - Region code to name conversion

### Job Details & Viewing
- **job-details.php** - Job viewing operations:
  - Job retrieval with employer details
  - View tracking (user_interactions table)
  - Application status checking
  - Job status validation
  - Employer information display
  - Required/preferred skills display
  - Location information display
  - Pay information display
  - Slot availability display

## Application Operations

### Job Application
- **job-details.php** - Application submission logic:
  - Cover letter entry
  - Resume file upload (optional)
  - Application status set to 'pending'
  - Worker ID association
  - Job ID association
  - Employer ID tracking
  - Application timestamp
  - Duplicate application prevention
  - Notification to employer

### Application Management
- **dashboard-employer.php** - Application review operations:
  - Pending application listing
  - Application status updates
  - Worker information display
  - Trust score display
  - Resume file access
  - Application count tracking

- **dashboard-worker.php** - Application tracking operations:
  - Application history display
  - Application status tracking
  - Rating availability checking
  - Cooling-off period enforcement (3 days for long-term jobs)
  - Confirmation status display

### Application Withdrawal
- **job-details.php** - Withdrawal logic:
  - Status change: pending → withdrawn
  - Timestamp update
  - Notification to employer
  - Validation (only pending applications)

### Application Confirmation
- **job-details.php** - Work confirmation logic:
  - Worker confirmation (worker_confirmed = 1)
  - Employer confirmation (employer_confirmed = 1)
  - Both confirmed timestamp (both_confirmed_at)
  - Rating availability calculation based on job type
  - Transaction-based updates
  - Notification on confirmation

## Messaging Operations

### Message Sending
- **messages.php** - Message sending logic:
  - Message content validation (max 2000 chars)
  - Receiver validation (active account)
  - Self-messaging prevention
  - Message storage with timestamp
  - Notification to receiver
  - CSRF token verification

### Message Retrieval
- **messages.php** - Message display operations:
  - Conversation list retrieval
  - Last message per conversation
  - Unread count calculation
  - Full conversation history
  - Message read status tracking
  - Sender/receiver information
  - Timestamp ordering (oldest first)

### Message Read Status
- **messages.php** - Read status operations:
  - Mark messages as read
  - Read timestamp update
  - Unread count recalculation

### User Search for Messaging
- **messages.php** - User search operations:
  - Name-based search
  - User type-based search
  - Active account filtering
  - Self-exclusion from results
  - Result limiting (20 users)

## Notification Operations

### Notification Creation
- **job-details.php** - Notification generation:
  - New application notifications
  - Application status change notifications
  - Work confirmation notifications
  - New message notifications
  - Related entity tracking
  - Action URL generation

### Notification Display
- **notifications.php** - Notification listing operations:
  - Unread notification retrieval
  - Read notification retrieval
  - Notification type classification
  - Icon assignment by type
  - Tag styling by type
  - Timestamp ordering (newest first)
  - Pagination (50 notifications)

### Notification Management
- **notifications.php** - Notification control operations:
  - Mark single notification as read
  - Mark all notifications as read
  - Read timestamp update
  - CSRF token verification

### Notification Settings
- **notification-settings.php** - Preference management:
  - Email notification toggles
  - Push notification toggles
  - Notification type preferences
  - User preference storage

## Recommendation Operations

### MatchScore Algorithm
- **for-you.php** - Recommendation logic:
  - Candidate job retrieval (broader set for ranking)
  - Skill match calculation (0-35 points)
  - Behavioral score calculation (0-25 points)
  - Collaborative filtering score (0-15 points)
  - Trust compatibility score (0-10 points)
  - Location proximity score (0-10 points)
  - Compensation fit score (0-5 points)
  - Job type preference score (0-5 points)
  - Recency score (0-3 points)
  - Diversity boost (0-2 points)
  - Total score calculation (0-100 normalized)
  - Match tier classification (excellent, very_good, good, fair, low)
  - Score breakdown storage
  - Sorting by match score descending
  - Top 15 selection

### Worker Recommendations (for Employers)
- **for-you.php** - Worker recommendation logic:
  - Employer job analysis
  - Skill extraction from job postings
  - Category extraction
  - Job type extraction
  - Location extraction
  - Worker matching based on criteria
  - Trust score consideration
  - Location proximity
  - Skill matching

## Rating Operations

### Employer Rating
- **rate-employer.php** - Worker rates employer:
  - Rating availability validation
  - Job completion confirmation check
  - Cooling-off period enforcement
  - Star rating (1-5) entry
  - Feedback text entry
  - Rating type: worker_to_employer
  - Application ID association
  - Employer trust score update
  - Rating timestamp

### Worker Rating
- **rate-worker.php** - Employer rates worker:
  - Rating availability validation
  - Job completion confirmation check
  - Star rating (1-5) entry
  - Feedback text entry
  - Rating type: employer_to_worker
  - Application ID association
  - Worker trust score update
  - Rating timestamp

### Rating Display
- **dashboard-employer.php** - Worker rating display:
  - Check if worker already rated
  - Display rating if exists
  - Show rate button if not rated

- **dashboard-worker.php** - Employer rating display:
  - Check if employer already rated
  - Display rating if exists
  - Show rate button if not rated
  - Display rating availability countdown

## Profile Operations

### Profile Picture Upload
- **dashboard-employer.php** - Employer profile picture:
  - File type validation (JPEG, PNG, WebP)
  - File size validation (2MB limit)
  - Unique filename generation
  - Old file deletion
  - Database path update
  - User ID association

- **dashboard-worker.php** - Worker profile picture:
  - Same validation and process as employer

### Profile Information Update
- **dashboard-employer.php** - Employer profile info:
  - Employer subtype selection (company/individual)
  - Bio entry (max 500 chars)
  - Company name entry (max 255 chars)
  - Company size selection
  - Industry entry (max 100 chars)
  - Company website URL validation
  - Year founded validation (1800-current year)
  - Database update

### Company Logo Upload
- **dashboard-employer.php** - Company logo operations:
  - File type validation (JPEG, PNG, WebP)
  - File size validation (2MB limit)
  - Unique filename generation
  - Old logo deletion
  - Database path update

### Payment Settings
- **dashboard-worker.php** - Payment configuration:
  - Payment method selection (GCash, PayMaya, Bank Transfer)
  - Account number entry
  - Social links entry
  - Database update

## Skills Operations

### Skill Addition
- **dashboard-worker.php** - Skill entry logic:
  - Skill name entry (max 100 chars)
  - Proficiency selection (beginner, intermediate, advanced, expert)
  - Duplicate prevention
  - Database insertion
  - User ID association

### Skill Removal
- **dashboard-worker.php** - Skill deletion logic:
  - Skill name validation
  - Database deletion
  - User ID association
  - Confirmation prompt

### Skill Display
- **dashboard-worker.php** - Skill presentation:
  - Skill listing with proficiency
  - Verification status display
  - Tag styling
  - Remove button per skill

## Social Operations

### Social Profile Management
- **config/DatabaseHelper.php** - Social profile operations:
  - Bio entry
  - Headline entry
  - Location display
  - Upsert operation (create or update)

### Social Posting
- **config/DatabaseHelper.php** - Post creation logic:
  - Content entry
  - Title entry (optional)
  - Post type selection
  - User ID association
  - Timestamp

### Post Interactions
- **config/DatabaseHelper.php** - Social engagement:
  - Like/unlike toggle
  - Like status checking
  - Like count calculation
  - Comment count calculation

### User Following
- **config/DatabaseHelper.php** - Follow operations:
  - Follow/unfollow toggle
  - Follow status checking
  - Follower count calculation
  - Following count calculation

## Admin Operations

### User Management
- **manage-users.php** - User control operations:
  - User listing with pagination
  - Search by name or email
  - Filter by user type
  - Filter by account status
  - Suspend user accounts
  - Activate suspended accounts
  - Mark accounts as deleted
  - Action reason logging
  - User statistics display

### Analytics & Reporting
- **analytics.php** - Platform analytics operations:
  - User statistics (total, workers, employers, active)
  - Job statistics (total, active, completed, draft)
  - Application statistics (total, approved, rejected, confirmed)
  - Skill post statistics
  - Platform activity metrics
  - Recent signups tracking
  - Top employers identification
  - Top skills tracking
  - Time-series data (users per day, jobs per day)
  - Status distribution (applications by status)
  - Type distribution (users by type)

## File Upload Operations

### Profile Pictures
- **dashboard-employer.php** & **dashboard-worker.php**:
  - MIME type validation
  - File size validation (2MB)
  - Unique filename generation
  - Directory creation if needed
  - Old file cleanup
  - Move to uploads/profiles/

### Job Images
- **post-job.php**:
  - MIME type validation
  - File size validation (5MB)
  - Unique filename generation
  - Move to uploads/jobs/

### Company Logos
- **dashboard-employer.php**:
  - MIME type validation
  - File size validation (2MB)
  - Unique filename generation
  - Move to uploads/company_logos/

### Resumes
- **job-details.php**:
  - PDF file validation
  - File size validation
  - Unique filename generation
  - Move to uploads/resumes/

## Search Operations

### Advanced Search
- **advanced-search.php** - Complex search logic:
  - Multi-field search
  - Boolean operators
  - Range filters
  - Category filters
  - Location filters
  - Pay range filters
  - Job type filters
  - Remote policy filters
  - Sorting options
  - Result aggregation

### User Search
- **messages.php** - User lookup:
  - Name search
  - User type search
  - Active account filtering
  - Result limiting

## Onboarding Operations

### Onboarding Flow
- **onboarding.php** - New user setup:
  - Profile completion steps
  - Skills entry (workers)
  - Company info entry (employers)
  - Location confirmation
  - Profile picture upload
  - Bio/headline entry
  - Onboarding completion flag
  - Redirect to dashboard

## Operations Features Summary

### Transaction Management
- Begin transaction for multi-step operations
- Commit on success
- Rollback on failure
- Used in: job applications, confirmations, ratings

### Validation Layers
- Input validation (format, length, type)
- Business rule validation (job/pay type combinations)
- Permission validation (user can perform action)
- Resource validation (resource exists and accessible)
- Status validation (correct state for operation)

### Notification System
- Real-time notification generation
- Notification type classification
- Related entity tracking
- Action URL generation
- Read status tracking
- Preference-based delivery

### Error Handling
- CSRF token validation
- Database error handling
- File upload error handling
- Validation error display
- User-friendly error messages
- Operation rollback on failure

### Audit Trail
- Timestamps on all operations
- User ID tracking
- Action logging
- Status change history
- Rate limit tracking
