# Documentation Form A

## Identify a Problem and SDG

1. What problem do you want to solve with this project?

   - Answer: Improve access to verified, localized decent-work opportunities and relevant, affordable upskilling for Filipino jobseekers—especially youth, informal and underemployed workers, and micro/small enterprises. The site addresses poor job discovery, skills mismatch, geographic barriers, and prevalence of unverified/scam job postings.

2. Who is affected by this problem?

   - Answer: Youth (18–35), recent graduates, underemployed and informal workers, returning OFWs looking for local work, micro- and small-business owners with limited HR resources, and jobseekers in provincial/rural areas.

3. Why is this problem important?

   - Answer: Sustained underemployment and informal work reduce incomes, perpetuate poverty, and weaken sustainable economic growth. Improving access to decent work increases household stability, expands economic opportunity, and directly supports SDG 8 (Decent Work and Economic Growth).

4. How can a website help solve this problem?

   - Answer: A website centralizes and verifies local job opportunities, connects employers and jobseekers, and links openings to short, targeted upskilling resources. Platform features (role dashboards, job posting, messaging, notifications, profile/document uploads, skill-learning pages and admin verification) streamline matching, reduce scams, enable micro-gigs, and help formalize work.

5. What SDG does your idea relate to?


   - Answer: SDG 8 — Decent Work and Economic Growth (promotes productive employment, decent jobs, entrepreneurship, and skills development)

a. Problem Statement:

   - Many Filipino jobseekers—particularly youth and workers in provincial and rural areas—lack reliable access to verified local job postings and affordable, relevant skills training. This leads to prolonged underemployment, reliance on informal work, and missed opportunities for stable income and upskilling.

b. Proposed Website Idea:

   - A localized employment and skills platform for the Philippines that connects verified employers and jobseekers, offers micro-credentials and skill-learning pathways, supports short-term/micro-gig and part-time hiring, and provides tools to formalize hiring for micro/small employers. Core features:

     - Localized job feed with advanced filters (region/province, sector, contract type, remote/hybrid, tags).
     - Role-based dashboards for `workers`, `employers`, and `admins` to manage profiles, postings, applications, and moderation.
     - Verified employer profiles and admin verification workflow to reduce scam postings.
     - In-platform messaging, notifications, and application tracking to speed hiring (maps to `messages.php`, `notifications.php`, `dashboard-*` pages).
     - Profile and document uploads for resumes, certificates, and IDs (use `uploads/profiles` and `uploads/documents` folders) to support verification and showcase micro-credentials.
     - Integrated skills hub (interlinked with `skill-learn.php`) offering micro-courses or links to partner training (TESDA/NGOs), and badges/micro-credentials to improve match signals.
     - Simple admin tools for moderation, analytics, and localized outreach to partner barangay or LGU job desks.

   - Philippine-specific design considerations: mobile-first and low-bandwidth UI, Tagalog/English copy, support for common e-wallets or bank transfer for paid micro-gigs, and integration options for local training centers and labor offices.

---

**Note:** The concept is based on SDG Goal 8 (Decent Work and Economic Growth), with a focus on the Philippines context.

## Suggested Example (Philippines / SDG 8)

- Problem: High youth underemployment and prevalence of informal work in many regions of the Philippines, plus limited access to verified local job opportunities and upskilling resources.
- Who is affected: Young jobseekers, informal workers, micro/small enterprises, and rural communities.
- Why important: Improving job matching and access to skills increases incomes, reduces poverty, and supports sustainable economic growth.
- How a website helps: Provide a centralized, localized job board, verified employer profiles, skills training resources, resume builders, and tools for micro-employers to post paid gigs and short-term contracts.
- SDG relation: Directly advances SDG 8 by promoting productive employment, decent work opportunities, and support for small businesses.

**Example Problem Statement:** Many young Filipinos in provincial areas lack reliable access to local job postings and affordable skills training, which leads to underemployment and informal work.

**Example Proposed Website Idea:** A localized employment platform connecting verified local employers and jobseekers, offering micro-credentials, remote and gig listings, and tools for employers to post short-term or part-time work aimed at formalizing and improving livelihoods.

## Success indicators (suggested)

- Number of verified job postings per month.
- Monthly active jobseekers and employers.
- Job placement rate (applications → hires) within target regions.
- Number of micro-credentials issued and skill-course completions.
- Reduction in user-reported scam postings and fraudulent ads.

## Quick mapping to existing site pages (repo)

- Job posting & details: `post-job.php`, `job-details.php`
- Role dashboards: `dashboard-worker.php`, `dashboard-employer.php`, `dashboard-admin.php`
- Messaging & notifications: `messages.php`, `notifications.php`
- Skills hub: `skill-learn.php`
- Personalized feed: `for-you.php`, `index.php`
- File uploads: `uploads/profiles`, `uploads/documents`
