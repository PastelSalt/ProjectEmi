# Backend Logic Index

This index categorizes all backend business logic, helper functions, and configuration files in the RaketGo job matching platform.

## Configuration Files

### Main Configuration
- **config/config.php** - Core configuration file containing:
  - Environment detection (local/development/production)
  - Session management with secure cookie settings
  - Security headers (HSTS, XSS protection, etc.)
  - File upload directory configuration
  - Error reporting settings
  - Timezone configuration
  - Philippines regions data

### Job Configuration
- **config/config.php** - Job-related configuration:
  - Job types configuration (full_time, part_time, contractual, one_time, internship)
  - Pay types configuration (fixed, hourly, daily, monthly)
  - Employer subtypes (company, individual)
  - Job type/pay type validation rules
  - Job duration validation logic

### MatchScore Algorithm Configuration
- **config/config.php** - RaketGo MatchScore™ Recommendation Algorithm:
  - Algorithm weights configuration (skill_match, behavioral, collaborative, etc.)
  - Skill relevance levels for fuzzy matching
  - Skill synonyms/categories for matching
  - Match score calculation functions
  - Match tier classification logic

## Helper Classes

### Database Helper
- **config/DatabaseHelper.php** - Database operations class:
  - User retrieval methods (getUserById, getUserByMobile)
  - Job operations (getJobWithEmployer)
  - User interaction tracking (getUserJobInteraction, createUserJobInteraction)
  - Job application management (getJobApplication, createJobApplication, updateApplicationStatus)
  - Skills management (getUserSkills, addUserSkill, removeUserSkill)
  - Ratings system (getUserRatings, createRating, getUserTrustScore)
  - Notifications (getUserNotifications, markNotificationsRead)
  - Messaging (getUserConversations, getConversationMessages, sendMessage, markMessagesRead)
  - Social features (getSocialProfile, upsertSocialProfile, getSocialPosts, createSocialPost)
  - Social interactions (togglePostLike, userLikedPost, toggleFollow, isFollowing, getFollowCounts)

### Authentication Helper
- **config/AuthHelper.php** - Authentication and authorization class:
  - Login/session management (requireLogin, requireUserType, requireUserTypes)
  - User session checks (isLoggedIn, getCurrentUserId, getCurrentUserType, getCurrentUser)
  - Resource access control (canAccessResource, canAccessJob, canAccessProfile, canAccessApplication, canAccessMessage)
  - Active session enforcement (enforceActiveSessionUser)
  - Permission system (getUserPermissions, hasPermission, requirePermission)
  - Authentication operations (login, logout, getLoginUrl)
  - Rate limiting (checkLoginRateLimit, recordLoginAttempt)

### Error Handler
- **config/ErrorHandler.php** - Error handling and validation class:
  - CSRF token validation
  - Error message management
  - Success message management
  - Error/success rendering

## Helper Functions

### Input Sanitization
- **config/config.php** - Input validation functions:
  - `sanitizeInput()` - Basic input sanitization
  - `sanitizeMultilineInput()` - Multi-line text sanitization
  - `sanitizeInternalUrl()` - Internal URL validation
  - `sanitizeExternalUrl()` - External URL validation
  - `normalizePhilippineMobile()` - Mobile number normalization
  - `isValidPhilippineMobile()` - Mobile number validation
  - `isValidRegionCode()` - Region code validation

### Session & Authentication
- **config/config.php** - Session helpers:
  - `isLoggedIn()` - Check login status
  - `getCurrentUserId()` - Get current user ID
  - `getCurrentUserType()` - Get current user type
  - `requireLogin()` - Require authentication
  - `requireUserType()` - Require specific user type

### CSRF Protection
- **config/config.php** - CSRF helpers:
  - `getCsrfToken()` - Generate CSRF token
  - `verifyCsrfToken()` - Verify CSRF token
  - `regenerateCsrfToken()` - Regenerate CSRF token
  - `csrfField()` - Generate CSRF form field

### Utility Functions
- **config/config.php** - Utility helpers:
  - `redirect()` - URL redirection
  - `formatCurrency()` - Currency formatting
  - `timeAgo()` - Relative time formatting
  - `calculateUserTrustScore()` - Trust score calculation

### Job Configuration Helpers
- **config/config.php** - Job-related helpers:
  - `getEmployerSubtypeInfo()` - Get employer subtype information
  - `getEmployerSubtypeLabel()` - Get employer subtype label
  - `isValidJobPayCombination()` - Validate job/pay type combination
  - `getJobTypeInfo()` - Get job type information
  - `getPayTypeInfo()` - Get pay type information
  - `validateJobDuration()` - Validate job duration based on type

### MatchScore Algorithm Functions
- **config/config.php** - Recommendation algorithm functions:
  - `calculateSkillAffinityScore()` - Calculate skill match score (0-35)
  - `calculateSkillSimilarity()` - Calculate similarity between two skills
  - `calculateBehavioralScore()` - Calculate behavioral score based on history (0-25)
  - `calculateCollaborativeScore()` - Calculate collaborative filtering score (0-15)
  - `calculateTrustCompatibilityScore()` - Calculate trust compatibility (0-10)
  - `calculateLocationProximityScore()` - Calculate location proximity (0-10)
  - `calculateCompensationFitScore()` - Calculate compensation fit (0-5)
  - `calculateJobTypePreferenceScore()` - Calculate job type preference (0-5)
  - `calculateRecencyScore()` - Calculate recency score (0-3)
  - `calculateDiversityBoost()` - Calculate diversity boost (0-2)
  - `calculateMatchScore()` - Main MatchScore calculation function
  - `getMatchTier()` - Get match quality tier
  - `getMatchTierInfo()` - Get match tier display info

### Security Functions
- **config/config.php** - Security helpers:
  - `sendSecurityHeaders()` - Send security HTTP headers

## Database Connection
- **config/database.php** - Database connection setup and management

## Rate Limiting Functions
- **config/database.php** - Rate limiting helpers:
  - `isRateLimitExceeded()` - Check if rate limit is exceeded
  - `registerRateLimitFailure()` - Register failed attempt
  - `clearRateLimit()` - Clear rate limit counter

## Database Query Helpers
- **config/database.php** - Query helper functions:
  - `executeQuery()` - Execute SQL query
  - `fetchAll()` - Fetch multiple rows
  - `fetchOne()` - Fetch single row
  - `beginTransaction()` - Begin transaction
  - `commitTransaction()` - Commit transaction
  - `rollbackTransaction()` - Rollback transaction
  - `closeDBConnection()` - Close database connection
  - `getDBConnection()` - Get database connection

## API Endpoints
- **api/update-theme.php** - Theme preference update API endpoint

## Key Backend Features

### Security Features
- CSRF token generation and validation
- Input sanitization and validation
- SQL injection prevention (prepared statements)
- XSS protection headers
- Session security (secure cookies, strict mode)
- Rate limiting for login attempts
- Password hashing (bcrypt)

### Configuration Management
- Environment-based configuration
- Dynamic site URL detection
- Upload directory management
- Error reporting control
- Timezone handling

### Algorithm Implementation
- MatchScore recommendation algorithm with 9 scoring components
- Fuzzy skill matching with synonyms
- Collaborative filtering
- Behavioral analysis
- Location proximity calculation
- Trust score integration

### Business Logic Helpers
- Job type/pay type validation
- Employer subtype management
- Philippine regions data
- Currency formatting
- Relative time formatting
- Trust score calculation
