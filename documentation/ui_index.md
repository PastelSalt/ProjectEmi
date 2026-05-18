# UI Logic Index

This index categorizes all frontend/presentation layer files in the RaketGo job matching platform.

## Page Templates

### Authentication Pages
- **login.php** - User login page with mobile number and password authentication
- **signup.php** - User registration page with account type selection (worker/employer)
- **logout.php** - Session logout handler

### Dashboard Pages
- **dashboard-employer.php** - Employer dashboard with job management, applications, and profile summary
- **dashboard-worker.php** - Worker dashboard with skills, applications, saved jobs, and portfolio
- **dashboard-admin.php** - Admin dashboard with platform overview and management tools
- **unified-dashboard.php** - Unified dashboard view

### Job Management Pages
- **index.php** - Home page with job listings, search, and filtering
- **post-job.php** - Job posting form for employers
- **job-details.php** - Detailed job view with application functionality
- **advanced-search.php** - Advanced job search with multiple filters

### Profile Pages
- **employer-profile.php** - Employer profile display
- **worker-profile.php** - Worker profile display
- **raketko-profile.php** - Raketko social profile
- **worker-portfolio.php** - Worker portfolio showcase

### Social/Feed Pages
- **raketko-feed.php** - Social feed for Raketko posts
- **for-you.php** - Personalized recommendations using MatchScore algorithm

### Communication Pages
- **messages.php** - Messaging/chat interface with conversation list
- **notifications.php** - Notifications center with read/unread states
- **notification-settings.php** - Notification preferences configuration

### Rating/Review Pages
- **rate-employer.php** - Worker rates employer after job completion
- **rate-worker.php** - Employer rates worker after job completion

### Learning Pages
- **skill-learn.php** - Skill learning and development page

### Admin Pages
- **manage-users.php** - User management interface for admins
- **analytics.php** - Platform analytics and reporting dashboard

### Onboarding
- **onboarding.php** - New user onboarding flow

### Legal/Info Pages
- **terms.php** - Terms of service page

## UI Components

### Layout Components
- **includes/header.php** - Site header with navigation and user menu
- **includes/footer.php** - Site footer with links and information

### Integration Components
- **includes/raketko_raketgo_integration.php** - Raketko/RaketGo social integration UI elements

## Static Assets

### Stylesheets
- **css/style.css** - Main stylesheet with pastel color scheme and responsive design

### JavaScript
- **js/main.js** - Frontend JavaScript for form validation, AJAX, and UI interactions

## API Endpoints
- **api/update-theme.php** - Theme preference update API

## Key UI Features

### Dashboard Features
- Stats cards with icons and metrics
- Job application tables with status indicators
- Profile summary panels with avatars
- Quick action buttons and forms

### Job Listing Features
- Grid/list view toggle
- Filter sidebar (location, job type, pay range)
- Search with autocomplete
- Pagination
- Job cards with match scores

### Messaging Features
- Conversation list with unread counts
- Real-time message display
- User search for new conversations
- Message read status indicators

### Notification Features
- Unread/read notification sections
- Notification type icons and badges
- Mark as read functionality
- Notification settings panel

### Profile Features
- Profile picture upload
- Skills tags with proficiency levels
- Portfolio image galleries
- Trust score display
- Social links integration

### Form Features
- CSRF token integration
- Client-side validation
- File upload handling
- Multi-step forms
- Dynamic field addition (skills, etc.)
