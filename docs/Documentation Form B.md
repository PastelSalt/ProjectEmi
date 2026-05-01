# Documentation Form B

## Part A: Review of Documentation Form A

### 1) Problem Statement Clarity
The project addresses limited access to verified, localized, and decent-work opportunities for Filipino jobseekers, especially those in provincial and rural areas. It also addresses the gap between available jobs and the skills of applicants.

### 2) Target Users
The primary users are:
- Youth and recent graduates (especially ages 18-35)
- Underemployed and informal workers
- Returning OFWs seeking local opportunities
- Micro and small business employers with limited hiring resources
- Jobseekers in provincial and rural communities

### 3) SDG Alignment
The project aligns with **SDG 8: Decent Work and Economic Growth** by promoting productive employment, safer hiring through verification, skills development, and improved economic participation.

### 4) Proposed Idea Clarity
The proposed solution is a localized employment and skills platform (RaketGo) that connects workers and verified employers, supports job matching, and links users to upskilling opportunities through a learning hub.

---

## Part B: Defined Website Pages (Complete Site Pages)

### 1) Home Page (`index.php`) - Overview and Job Discovery
Purpose:
- Introduce the platform and its value proposition
- Display searchable and filterable local job opportunities
- Help users discover jobs by location, category, and pay preferences

Main content:
- Search and filter controls (region, city, category, keyword)
- Sort and pagination for job browsing
- Featured announcements and quick navigation to key sections

### 2) Signup Page (`signup.php`) - User Registration
Purpose:
- Register new worker or employer accounts
- Collect core profile and location details for matching

Main content:
- Account creation form (mobile number, password, user type)
- Basic profile details (name, region, province, city)
- Skill entry support for worker profiles

### 3) Login Page (`login.php`) - Secure Access
Purpose:
- Authenticate existing users and redirect by role
- Secure account access for workers, employers, and admins

Main content:
- Login form (mobile number and password)
- Session and CSRF-protected authentication flow
- Role-based redirect after successful login

### 4) Logout Page (`logout.php`) - Session Termination
Purpose:
- Safely end authenticated sessions
- Protect user accounts on shared/public devices

Main content:
- CSRF-validated logout action
- Session destruction and redirect to public page

### 5) Job Posting Page (`post-job.php`) - Employer Job Creation
Purpose:
- Allow employers to publish job opportunities
- Standardize job details for better applicant matching

Main content:
- Job creation form (title, description, location, pay, dates)
- Skills, category, and slots input fields
- Employer-side posting and management controls

### 6) Job Details Page (`job-details.php`) - Opportunity Details and Application
Purpose:
- Present full information of a selected job
- Enable workers to apply, withdraw, or reapply based on status

Main content:
- Job description, requirements, location, compensation, employer details
- Application action area and status indicators
- Related interaction points that feed engagement tracking

### 7) For You Page (`for-you.php`) - Personalized Recommendation Feed
Purpose:
- Provide tailored recommendations for workers and employers
- Improve match quality and hiring speed

Main content:
- Recommended jobs for workers (skill/location aligned)
- Recommended workers for employers (skill overlap aligned)
- Trending jobs and prioritized suggestions

### 8) Worker Dashboard (`dashboard-worker.php`) - Worker Operations Center
Purpose:
- Centralize worker activity, profile management, and applications
- Give workers visibility into progress and opportunities

Main content:
- Profile summary and skill indicators
- Application history and statuses
- Personalized metrics and quick actions

### 9) Employer Dashboard (`dashboard-employer.php`) - Employer Operations Center
Purpose:
- Centralize employer hiring workflows and posted jobs
- Support application review and decision-making

Main content:
- Posted job management (active/paused/reopened)
- Applicant review and approval/rejection actions
- Hiring activity summaries and shortcuts

### 10) Admin Dashboard (`dashboard-admin.php`) - Platform Administration
Purpose:
- Monitor platform operations, users, and jobs
- Support moderation and system-level oversight

Main content:
- Summary metrics and high-level platform statistics
- Operational monitoring panels
- Admin quick actions for governance and platform health

### 11) Messaging Page (`messages.php`) - In-Platform Communication
Purpose:
- Enable direct communication between workers and employers
- Reduce friction in coordination and follow-ups

Main content:
- Conversation list and threaded messages
- Send/read message flow
- Unread handling integrated with notifications

### 12) Notifications Page (`notifications.php`) - Activity Alerts Center
Purpose:
- Keep users updated on key actions and status changes
- Improve responsiveness across the hiring process

Main content:
- Notification feed for applications, messages, and updates
- Mark one/all notifications as read actions
- Quick links to related pages and records

### 13) Skill Learn Page (`skill-learn.php`) - Upskilling and Learning Hub
Purpose:
- Help users improve skills relevant to available jobs
- Support long-term employability and career growth

Main content:
- Learning resources (training, courses, workshops, certifications)
- Filters by type and category
- Featured learning opportunities with safe outbound links

---

## Part C: Core Features (At Least 3)

### 1) Verified Job Marketplace
- Employer verification and moderation flow
- Reduced scam/fake postings
- Safer environment for jobseekers

### 2) Smart Matching and Recommendation Engine
- Recommends jobs to workers based on skills and location
- Recommends suitable workers to employers
- Supports faster and more relevant hiring decisions

### 3) In-Platform Communication and Updates
- Direct messaging between workers and employers
- Real-time style notifications for key actions
- Better follow-through from application to hiring

### 4) Integrated Skills Learning Hub
- Training and certification resource listings
- Helps users improve qualifications for available jobs
- Supports long-term employability and career growth

---

## Part D
As instructed, this part is intentionally ignored.

---

## Part E: Revised Problem Statement
Many Filipino jobseekers, especially youth and workers in provincial and rural communities, struggle to access verified local job opportunities and affordable, relevant upskilling resources. At the same time, micro and small employers face difficulty finding suitable candidates quickly and safely. This mismatch leads to underemployment, informal work, and slower local economic growth. A localized, verification-focused job and skills platform can improve trust, matching quality, and access to decent work, directly supporting SDG 8.
