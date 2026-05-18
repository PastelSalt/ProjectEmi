# Database Logic Index

This index categorizes all database-related files, schemas, and data operations in the RaketGo job matching platform.

## Database Schema Files

### Schema Definition
- **database/unified_schema.sql** - Complete database schema containing:
  - Users table (user accounts, profiles, trust scores)
  - User skills table (worker skills and proficiency)
  - Job posts table (job listings with all details)
  - Job applications table (application tracking and status)
  - Job ratings table (employer/worker ratings)
  - Messages table (messaging system)
  - Notifications table (notification system)
  - User interactions table (job views, saves, etc.)
  - Social profiles table (Raketko social profiles)
  - Social posts table (social feed posts)
  - Social likes table (post likes)
  - Social follows table (user following)
  - Auth rate limits table (login attempt tracking)
  - Skill posts table (skill learning content)
  - Index definitions for performance optimization

### Test Data
- **database/test_data.sql** - Sample data for testing:
  - Sample users (workers, employers, admins)
  - Sample job postings
  - Sample job applications
  - Sample skills
  - Sample messages
  - Sample notifications

### Documentation
- **database/README.md** - Database documentation and setup instructions

## Database Connection

### Connection Management
- **config/database.php** - Database connection setup:
  - MySQL connection parameters
  - Connection error handling
  - Connection pooling configuration
  - Character set configuration (utf8mb4)

## Database Query Functions

### Core Query Functions
- **config/database.php** - Basic query operations:
  - `executeQuery()` - Execute INSERT, UPDATE, DELETE queries
  - `fetchAll()` - Fetch multiple rows from SELECT queries
  - `fetchOne()` - Fetch single row from SELECT queries
  - `beginTransaction()` - Start database transaction
  - `commitTransaction()` - Commit transaction
  - `rollbackTransaction()` - Rollback transaction
  - `closeDBConnection()` - Close database connection
  - `getDBConnection()` - Get active database connection

## Database Helper Class Methods

### User Operations
- **config/DatabaseHelper.php** - User-related database methods:
  - `getUserById()` - Retrieve user by ID with optional field selection
  - `getUserByMobile()` - Retrieve user by mobile number

### Job Operations
- **config/DatabaseHelper.php** - Job-related database methods:
  - `getJobWithEmployer()` - Get job details with employer information

### User Interaction Tracking
- **config/DatabaseHelper.php** - Interaction tracking methods:
  - `getUserJobInteraction()` - Check if user interacted with job
  - `createUserJobInteraction()` - Record user interaction (view, save, etc.)

### Job Application Operations
- **config/DatabaseHelper.php** - Application management methods:
  - `getJobApplication()` - Get job application details
  - `createJobApplication()` - Create new job application
  - `updateApplicationStatus()` - Update application status

### Skills Management
- **config/DatabaseHelper.php** - Skills database methods:
  - `getUserSkills()` - Get all user skills
  - `addUserSkill()` - Add skill to user profile
  - `removeUserSkill()` - Remove skill from user profile

### Ratings System
- **config/DatabaseHelper.php** - Ratings database methods:
  - `getUserRatings()` - Get user ratings with optional type filter
  - `createRating()` - Create new rating
  - `getUserTrustScore()` - Calculate user trust score from ratings

### Notifications
- **config/DatabaseHelper.php** - Notification database methods:
  - `getUserNotifications()` - Get user notifications with pagination
  - `markNotificationsRead()` - Mark notifications as read

### Messaging System
- **config/DatabaseHelper.php** - Messaging database methods:
  - `getUserConversations()` - Get all user conversations
  - `getConversationMessages()` - Get messages in a conversation
  - `sendMessage()` - Send new message
  - `markMessagesRead()` - Mark messages as read

### Social Features
- **config/DatabaseHelper.php** - Social database methods:
  - `getSocialProfile()` - Get user social profile
  - `upsertSocialProfile()` - Create or update social profile
  - `getSocialPosts()` - Get social feed posts
  - `createSocialPost()` - Create new social post
  - `togglePostLike()` - Like/unlike post
  - `userLikedPost()` - Check if user liked post
  - `toggleFollow()` - Follow/unfollow user
  - `isFollowing()` - Check if user is following another user
  - `getFollowCounts()` - Get follower/following counts

## Database Tables

### Core Tables

#### users
- User account information
- Profile data (name, location, bio)
- Authentication credentials
- Trust score
- Account status
- User type (worker, employer, admin)
- Employer-specific fields (company info, logo)
- Worker-specific fields (payment methods, skills)

#### user_skills
- User skill associations
- Skill proficiency levels
- Verification status
- Skill metadata

#### job_posts
- Job posting details
- Job type and pay information
- Location data (region, province, city)
- Required and preferred skills
- Remote work policy
- Job status tracking
- Slot management
- Job images

#### job_applications
- Application tracking
- Application status (pending, approved, rejected, withdrawn)
- Cover letters
- Resume files
- Confirmation tracking (worker/employer)
- Rating availability timing
- Timestamps for all status changes

#### job_ratings
- Employer to worker ratings
- Worker to employer ratings
- Rating stars (1-5)
- Feedback text
- Rating type classification

### Communication Tables

#### messages
- Message content and metadata
- Sender/receiver relationships
- Read status tracking
- Timestamps

#### notifications
- Notification types (new_application, application_status, new_message, payment)
- Notification content
- Read status
- Related entity tracking
- Action URLs

### Interaction Tables

#### user_interactions
- Job views
- Job saves/bookmarks
- Interaction type classification
- Timestamps for analytics

### Social Tables

#### social_profiles
- User bio and headline
- Location display
- Social profile metadata

#### social_posts
- Social feed posts
- Post types (career_update, etc.)
- Post content and titles
- Engagement tracking

#### social_likes
- Post likes
- User-post relationships
- Like timestamps

#### social_follows
- User following relationships
- Follower/following tracking

### System Tables

#### auth_rate_limits
- Login attempt tracking
- Rate limiting data
- Success/failure tracking
- Timestamp-based cleanup

#### skill_posts
- Skill learning content
- Difficulty levels
- Featured status
- Learning resources

## Database Indexes

### Performance Indexes
- User indexes (mobile_number, email, user_type)
- Job indexes (employer_id, job_status, location fields)
- Application indexes (worker_id, job_id, application_status)
- Message indexes (sender_id, receiver_id, sent_at)
- Notification indexes (user_id, is_read, created_at)
- Interaction indexes (user_id, job_id, interaction_type)
- Social indexes (user_id, post_id, follower_id, following_id)

## Database Features

### Transaction Support
- Begin transaction for complex operations
- Commit on success
- Rollback on failure
- Used in job applications, confirmations, and multi-step operations

### Prepared Statements
- All queries use prepared statements
- Parameter binding for security
- Type specification for parameters
- SQL injection prevention

### Data Integrity
- Foreign key constraints
- Unique constraints (mobile_number, email)
- NOT NULL constraints on required fields
- ENUM constraints for status fields
- CHECK constraints for data validation

### Performance Optimization
- Strategic indexing on frequently queried columns
- Composite indexes for multi-column queries
- Subquery optimization
- JOIN optimization with proper indexes

### Data Relationships
- One-to-many: Users → Skills, Jobs, Applications
- Many-to-one: Applications → Jobs, Users
- Many-to-many: Social follows, Post likes
- Self-referencing: Messages (sender/receiver both reference users)
