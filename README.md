# RaketGo + RaketKo - Unified Career Platform

Created and managed by Moesoft (Moeko Software)

RaketGo + RaketKo is a unified PHP and MySQL career platform for the Philippines that combines **job matching** (RaketGo) with **professional social networking** (RaketKo). It supports workers, employers, and admins with role-specific dashboards, recommendation feeds, messaging, notifications, social features, and a learning hub.

## Quick Start

### Requirements
- PHP 8.0+
- MySQL 5.7+
- Apache with mod_rewrite

### Installation
1. Clone the repository
2. Import `database/schema.sql`
3. Import `database/sample_data.sql` (optional)
4. Configure `config/database.php`
5. Set up Apache virtual host
6. Visit the platform

### Test Credentials
| Role | Mobile | Password |
|------|--------|----------|
| Admin | `09560618349` | `matsuzakatou` |
| Other | (any seeded user) | `password` |

## Key Features

### Core Platform
- **Unified Architecture** - Job matching + social networking
- **Production Security** - CSRF protection, rate limiting, secure sessions
- **Regional Discovery** - Philippines-focused job discovery with interactive map
- **Smart Recommendations** - MatchScore™ algorithm for jobs and connections
- **Public Access** - Browse content without login (read-only)

### RaketGo (Job Matching)
- Job posting and discovery with advanced filtering
- Application management with approval workflow
- Employer dashboard with analytics
- Worker portfolios and skill management
- Rating system for both workers and employers
- Remote work policy support (On-site, Hybrid, Remote)

### RaketKo (Social Networking)
- Professional posts and career updates
- Like, comment, and share functionality
- Follow/unfollow system
- Trending topics and recommendations
- Direct messaging between users
- Modern social media UI (X.com/Facebook.com style)

## Role Capabilities

### Worker
- Create account and maintain comprehensive profile
- **Portfolio management** - Upload work samples, projects, and showcase skills
- **Employment history tracking** - View complete job history and ratings
- Add and remove skills with proficiency levels and verification
- Browse jobs, filter, sort, and paginate
- Apply to jobs with optional cover letter
- Withdraw pending applications and reapply
- Save and manage saved jobs
- View personalized recommendations
- Message employers and receive notifications
- Rate employers after job completion
- **RaketKo Social Features**: Create posts, connect with professionals, share career updates

### Employer
- Create and manage company profile
- Post jobs with detailed requirements and remote policies
- Review and manage applications
- Approve/reject applications with slot management
- Pause/reopen job postings
- Message workers and manage communications
- View analytics and platform insights
- Rate workers after job completion
- **RaketKo Social Features**: Share company updates, discover talent

### Admin
- Platform management and user administration
- Analytics and reporting dashboard
- Content moderation and trust score audits
- Learning hub content management
- System configuration and security oversight

## Documentation

- **[NAVIGATION.md](NAVIGATION.md)** - Complete codebase navigation guide
- **[MATCHSCORE_ALGORITHM.md](MATCHSCORE_ALGORITHM.md)** - Smart recommendation algorithm documentation
- **[CHANGELOG.md](CHANGELOG.md)** - Version history and updates

## File Structure

```
ProjectEmi/
├── README.md                    # This file
├── NAVIGATION.md                # Codebase navigation guide
├── MATCHSCORE_ALGORITHM.md      # Recommendation algorithm
├── CHANGELOG.md                 # Version history
├── index.php                    # Home page
├── login.php / signup.php       # Authentication
├── dashboard-*.php              # Role-specific dashboards
├── raketko-*.php                # Social networking features
├── messages.php                 # Messaging system
├── config/                      # Configuration files
├── includes/                    # Header/footer templates
├── css/style.css                # All styles
├── database/                    # Schema and sample data
└── uploads/                     # User uploads
```

## Security Features

- **CSRF Protection** - All forms use secure tokens
- **SQL Injection Prevention** - Prepared statements throughout
- **XSS Protection** - Output sanitization
- **Session Security** - HttpOnly, SameSite, secure cookies
- **Rate Limiting** - Login attempt throttling
- **File Upload Security** - Safe file handling
- **Apache Hardening** - Security headers and access controls

## License

Created and managed by Moesoft (Moeko Software) - © 2026
