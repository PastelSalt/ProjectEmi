# Database Schema - RaketGo + RaketKo

This directory contains the complete database schema for the unified RaketGo (job matching) and RaketKo (social media) platform.

## Files

### Core Schema
- **`unified_schema.sql`** - Complete database schema for both platforms
  - **40+ unified tables** with proper relationships and constraints
  - Cross-platform integration tables for unified user experience
  - Database triggers for automated activity logging and updates
  - Performance-optimized indexes and views
  - No sample data (production-ready)
  - **Use this file to create the complete database from scratch**

### Test Data
- **`test_data.sql`** - Comprehensive test data for all web application functionalities
  - **10 sample users** (admin, employers, workers) with complete profiles
  - **6 job posts** with applications and ratings
  - **10 social media posts** with likes, comments, and shares
  - Cross-platform activities and unified notifications
  - Career milestones and analytics data
  - Use after importing the main schema for testing

## Recent Updates (May 2026)

### Schema Improvements
- **Fixed Regional Filtering** - Proper mapping between region codes and database names
- **Enhanced Cross-Platform Tables** - Unified user profiles and activity tracking
- **Optimized Indexes** - Better performance for complex queries
- **Fixed View References** - Corrected column names in database views
- **Streamlined Structure** - Removed redundant tables and consolidated data

### Fixed Issues
- **Regional Filter Bug** - Database stored region names but frontend sent codes
- **Schema Deployment Errors** - Fixed CHECK constraints and column references
- **Test Data Conflicts** - Resolved duplicate entries between schema and test data
- **Navigation References** - Fixed broken links to non-existent files

## Deployment Instructions

### For Production/Development Setup

1. **Create the database structure:**
   ```bash
   mysql -u username -p database_name < unified_schema.sql
   ```

2. **(Optional) Add test data for development:**
   ```bash
   mysql -u username -p database_name < test_data.sql
   ```

### Database Requirements

- MySQL 5.7+ or MariaDB 10.2+
- InnoDB storage engine
- utf8mb4 character set support
- JSON data type support
- Foreign key constraints enabled

## Schema Overview

### Core Tables (45+ total)

#### User Management
- **users** - Unified user accounts for both platforms
- **social_profiles** - Professional social media profiles

#### RaketGo (Job Matching)
- **job_posts** - Job listings and opportunities
- **job_applications** - Job applications and status tracking
- **job_ratings** - User ratings and feedback
- **employer_reviews** - Employer performance reviews
- **worker_portfolio** - Worker work samples and projects
- **messages** - User-to-user messaging
- **transactions** - Financial transactions and payments
- **digital_contracts** - Digital employment contracts

#### RaketKo (Social Media)
- **social_posts** - Professional social media posts
- **social_post_likes** - Post likes and reactions
- **social_post_comments** - Post comments and discussions
- **social_comment_likes** - Comment likes
- **social_post_shares** - Post sharing and reposts
- **social_connections** - User follows and connections
- **social_notifications** - Social media notifications
- **trending_topics** - Hashtag and topic tracking

#### Cross-Platform Integration
- **cross_platform_activities** - Activity tracking across platforms
- **unified_notifications** - Combined notification system
- **unified_skills** - Standardized skill taxonomy
- **user_unified_skills** - User skill relationships
- **career_milestones** - Professional achievements
- **job_social_engagement** - Job-social interactions
- **cross_platform_shares** - Content sharing between platforms
- **trending_content** - Combined trending analysis
- **unified_user_analytics** - Cross-platform metrics

#### Supporting Tables
- **user_skills** - RaketGo-specific skills
- **skill_posts** - Learning hub content
- **notifications** - RaketGo-specific notifications
- **user_interactions** - User activity tracking
- **trust_score_updates** - Trust score change history
- **auth_rate_limits** - Authentication rate limiting
- **content_moderation** - Content moderation and reports
- **user_activity_feed** - Activity feed data
- **social_analytics** - Social media analytics

### Key Features

#### Unified User System
- Single user account works for both platforms
- Profile synchronization between RaketGo and RaketKo
- Cross-platform activity tracking
- Unified skill management system

#### Performance Optimizations
- Strategic indexes for common queries
- Foreign key constraints for data integrity
- Database triggers for automated updates
- Views for complex data access patterns

#### Cross-Platform Integration
- Shared notifications system
- Unified analytics and metrics
- Content sharing between platforms
- Career milestone tracking

## Database Views

### Unified Views
- **unified_user_profile** - Complete user profile information
- **trending_combined_content** - Cross-platform trending content

## Database Triggers

### Automated Processes
- **Profile Sync** - Automatic user profile updates
- **Activity Logging** - Real-time activity tracking
- **Count Updates** - Automatic like/comment/share counts
- **Analytics Updates** - Real-time metric calculations

## Foreign Key Relationships

### Key Relationships
- Users → Social Profiles (one-to-one)
- Users → All activity tables (one-to-many)
- Job Posts → Applications, Ratings, Contracts
- Social Posts → Likes, Comments, Shares
- Cross-platform tables → Core platform tables

## Indexes

### Performance Indexes (100+)
- User-based queries: `idx_user_activities`
- Platform filtering: `idx_platform_activities`
- Trending content: `idx_trending_content`
- Search optimization: Full-text indexes
- Composite indexes for complex queries

## Data Types

### Modern MySQL Features
- **JSON** fields for flexible data storage
- **ENUM** for controlled values
- **DECIMAL** for financial data
- **TIMESTAMP** for time tracking
- **TEXT** for long content
- **VARCHAR** for strings

## Character Set

- **utf8mb4** for full Unicode support
- **utf8mb4_unicode_ci** for case-insensitive comparisons

## Storage Engine

- **InnoDB** for transaction support and foreign keys
- Row-level locking for better concurrency
- Crash recovery and data integrity

## Migration Notes

### From Previous Versions
- All old schema files have been consolidated into `unified_schema.sql`
- Sample data separated into `sample_data.sql`
- Integration logic moved to the unified schema

### Future Updates
- Always update the unified schema file
- Maintain backward compatibility where possible
- Test migrations thoroughly before deployment

## Maintenance

### Regular Tasks
- Monitor table sizes and performance
- Update statistics for query optimization
- Backup database regularly
- Review and optimize slow queries

### Security
- Use parameterized queries in application code
- Implement proper user permissions
- Regular security updates
- Monitor for unusual activity

## Support

For database-related issues:
1. Check the schema file for table definitions
2. Review foreign key constraints
3. Verify trigger logic
4. Check index usage for performance issues

---

**Last Updated**: May 2026  
**Version**: 1.0 (Unified Schema)  
**Platform**: RaketGo + RaketKo
