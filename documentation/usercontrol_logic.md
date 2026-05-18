# User Control Logic Index

This index categorizes all user authentication, authorization, and access control logic in the RaketGo job matching platform.

## Authentication Pages

### Login/Signup
- **login.php** - User authentication logic:
  - Mobile number and password validation
  - Philippine mobile number format validation
  - Password verification using bcrypt
  - Account status checking (active, suspended, deleted)
  - Session creation and management
  - Rate limiting for failed login attempts
  - Onboarding status checking
  - Role-based redirection (worker/employer/admin dashboards)
  - Last login timestamp update

- **signup.php** - User registration logic:
  - User type selection (worker/employer)
  - Mobile number uniqueness validation
  - Email validation (optional field)
  - Password strength validation (min 8 chars, letter + number)
  - Password confirmation matching
  - Region/province/city validation
  - Philippine regions data integration
  - Password hashing (bcrypt)
  - Initial skill entry for workers
  - Account creation with proper user type
  - Redirect to login after successful registration

- **logout.php** - Session termination logic:
  - Session destruction
  - Cookie cleanup
  - Redirect to home page

## Authentication Helper Class

### Session Management
- **config/AuthHelper.php** - Session control methods:
  - `requireLogin()` - Enforce authentication, redirect to login if not logged in
  - `isLoggedIn()` - Check if user has active session
  - `getCurrentUserId()` - Retrieve current user ID from session
  - `getCurrentUserType()` - Retrieve current user type from session
  - `getCurrentUser()` - Retrieve full user data with caching
  - `enforceActiveSessionUser()` - Verify user account is still active

### Role-Based Access Control
- **config/AuthHelper.php** - Role enforcement methods:
  - `requireUserType($type)` - Require specific user type (worker/employer/admin)
  - `requireUserTypes($types)` - Require one of multiple user types
  - `getUserPermissions($userId)` - Get permission matrix based on user type
  - `hasPermission($permission)` - Check if user has specific permission
  - `requirePermission($permission)` - Require specific permission or redirect

### Permission Matrix
- **config/AuthHelper.php** - Permission definitions by role:
  - **Worker permissions**: view_jobs, apply_jobs, view_profiles, send_messages, create_posts, rate_users
  - **Employer permissions**: view_jobs, post_jobs, manage_applications, view_profiles, send_messages, create_posts, rate_users
  - **Admin permissions**: All permissions plus manage_users, view_analytics

### Authentication Operations
- **config/AuthHelper.php** - Auth operation methods:
  - `login($userId, $userType, $remember)` - Create user session with optional remember me
  - `logout()` - Destroy session and cleanup
  - `getLoginUrl($returnUrl)` - Generate login URL with return parameter
  - `redirect($url)` - Secure URL redirection

### Rate Limiting
- **config/AuthHelper.php** - Rate limiting methods:
  - `checkLoginRateLimit($mobile)` - Check if login attempts exceeded limit
  - `recordLoginAttempt($mobile, $success)` - Record login attempt for tracking

## Resource Access Control

### Job Access Control
- **config/AuthHelper.php** - Job access methods:
  - `canAccessJob($jobId, $userId, $userType)` - Check if user can access job
  - Admins can access all jobs
  - Employers can access their own jobs
  - Workers can view all active jobs

### Profile Access Control
- **config/AuthHelper.php** - Profile access methods:
  - `canAccessProfile($profileUserId, $userId, $userType)` - Check profile access
  - Users can always access their own profile
  - Admins can access any profile
  - Other users can access public active profiles

### Application Access Control
- **config/AuthHelper.php** - Application access methods:
  - `canAccessApplication($applicationId, $userId, $userType)` - Check application access
  - Workers can access their own applications
  - Employers can access applications for their jobs
  - Admins can access all applications

### Message Access Control
- **config/AuthHelper.php** - Message access methods:
  - `canAccessMessage($messageId, $userId, $userType)` - Check message access
  - Users can only access messages they sent or received
  - Admins can access all messages

## Session Security Features

### Secure Session Configuration
- **config/config.php** - Session security settings:
  - Strict session mode enabled
  - HTTP-only cookies
  - Secure cookies (HTTPS only)
  - SameSite cookie policy (Lax)
  - Session name customization
  - Session regeneration on login

### CSRF Protection
- **config/config.php** - CSRF token methods:
  - `getCsrfToken()` - Generate cryptographically secure CSRF token
  - `verifyCsrfToken($token)` - Verify CSRF token using hash_equals
  - `regenerateCsrfToken()` - Regenerate token after use
  - `csrfField()` - Generate hidden CSRF form field

### Rate Limiting Implementation
- **config/database.php** - Rate limiting functions:
  - `isRateLimitExceeded($conn, $action, $identifier, $maxAttempts, $windowSeconds, $blockSeconds, &$retryAfter)` - Check rate limit
  - `registerRateLimitFailure($conn, $action, $identifier, $maxAttempts, $windowSeconds, $blockSeconds)` - Record failed attempt
  - `clearRateLimit($conn, $action, $identifier)` - Clear rate limit counter

## User Type-Specific Controls

### Worker Controls
- **dashboard-worker.php** - Worker-specific access:
  - Require worker user type
  - Access to job application history
  - Access to saved jobs
  - Access to skills management
  - Access to portfolio
  - Payment method configuration

### Employer Controls
- **dashboard-employer.php** - Employer-specific access:
  - Require employer user type
  - Access to job posting management
  - Access to application review
  - Access to worker hiring
  - Company profile management
  - Job status control (pause/reopen)

### Admin Controls
- **dashboard-admin.php** - Admin-specific access:
  - Require admin user type
  - Access to platform analytics
  - Access to user management
  - Access to system monitoring
  - Full platform oversight

## Onboarding Flow Control

### Onboarding Status
- **login.php** - Onboarding checking:
  - Check onboarding_completed flag
  - Redirect to onboarding if not completed
  - Skip onboarding for admins
  - Redirect to appropriate dashboard after onboarding

- **onboarding.php** - Onboarding process:
  - Step-by-step profile completion
  - Skills entry for workers
  - Company info for employers
  - Location confirmation
  - Mark onboarding as completed

## Password Security

### Password Requirements
- **signup.php** - Password validation:
  - Minimum 8 characters
  - At least one letter
  - At least one number
  - Password confirmation matching

### Password Storage
- **signup.php** - Password hashing:
  - Use PHP's password_hash() with PASSWORD_DEFAULT (bcrypt)
  - Automatic salt generation
  - Secure password storage

### Password Verification
- **login.php** - Password checking:
  - Use PHP's password_verify()
  - Secure comparison
  - Timing attack resistant

## Account Status Management

### Account Status Types
- **Active** - Full access to platform
- **Suspended** - Login blocked, account preserved
- **Deleted** - Soft delete, data retained but inaccessible

### Status Enforcement
- **login.php** - Status checking:
  - Prevent login for suspended accounts
  - Prevent login for deleted accounts
  - Display appropriate error messages

- **manage-users.php** - Admin status control:
  - Suspend user accounts
  - Activate suspended accounts
  - Mark accounts as deleted
  - Status change logging

## Trust Score System

### Trust Score Calculation
- **config/config.php** - Trust score logic:
  - `calculateUserTrustScore($conn, $user_id)` - Calculate from ratings
  - Average of all ratings received
  - Displayed on profiles
  - Used in MatchScore algorithm

### Rating System
- **rate-employer.php** - Worker rates employer:
  - Only after job completion
  - Both parties must confirm work complete
  - Cooling-off period for long-term jobs (3 days)
  - 1-5 star rating with feedback

- **rate-worker.php** - Employer rates worker:
  - Only after job completion
  - Both parties must confirm work complete
  - 1-5 star rating with feedback
  - Affects worker trust score

## Mobile Number Validation

### Philippine Mobile Format
- **config/config.php** - Mobile number logic:
  - `normalizePhilippineMobile($mobile)` - Convert to 09XXXXXXXXX format
  - `isValidPhilippineMobile($mobile)` - Validate format with regex
  - Handle +63 prefix conversion
  - Remove spaces and special characters

### Uniqueness Checking
- **signup.php** - Mobile uniqueness:
  - Check database for existing mobile number
  - Prevent duplicate registrations
  - Display error if already registered

## Geographic Validation

### Region Validation
- **config/config.php** - Region logic:
  - `isValidRegionCode($regionCode)` - Validate against Philippines regions
  - Region code to name mapping
  - Used in job location matching

### Location Data
- **config/config.php** - Philippines regions:
  - All 17 regions with codes
  - NCR (National Capital Region)
  - CAR, Region I-XIII, BARMM
  - Used for location-based recommendations

## Security Headers

### HTTP Security Headers
- **config/config.php** - Security header function:
  - `sendSecurityHeaders()` - Send all security headers
  - X-Frame-Options: SAMEORIGIN
  - X-Content-Type-Options: nosniff
  - Referrer-Policy: strict-origin-when-cross-origin
  - Permissions-Policy: restrict geolocation, microphone, camera
  - X-XSS-Protection: 0 (modern browsers handle XSS)
  - Cross-Origin-Opener-Policy: same-origin
  - Strict-Transport-Security (HTTPS only)

## User Control Features Summary

### Authentication Flow
1. User submits credentials
2. Mobile number normalized and validated
3. Rate limit checked
4. Password verified against hash
5. Account status validated
6. Session created with secure settings
7. Onboarding status checked
8. Redirected to appropriate dashboard

### Authorization Flow
1. User attempts to access resource
2. Session validity checked
3. User type verified
4. Resource ownership checked
5. Permission matrix consulted
6. Access granted or denied
7. Redirect if unauthorized

### Security Layers
1. Input validation and sanitization
2. CSRF token verification
3. Rate limiting
4. Secure session management
5. Role-based access control
6. Resource ownership verification
7. Permission matrix enforcement
8. Account status checking
9. HTTP security headers
