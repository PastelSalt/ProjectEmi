# Recommendation Logic Index

This index categorizes all recommendation system logic and the RaketGo MatchScore™ algorithm in the RaketGo job matching platform.

## Overview

The RaketGo platform uses a sophisticated recommendation system called **MatchScore™** to provide personalized job recommendations for workers and worker recommendations for employers. The algorithm combines multiple scoring components to calculate a 0-100 match score.

## Algorithm Configuration

### Weight Distribution
- **config/config.php** - Algorithm weights configuration:
  - `skill_match`: 35 points (35%) - Direct skill matching
  - `behavioral`: 25 points (25%) - User's viewing/applying patterns
  - `collaborative`: 15 points (15%) - Similar users' preferences
  - `trust_compatibility`: 10 points (10%) - Trust score alignment
  - `location_proximity`: 10 points (10%) - Geographic proximity
  - `compensation_fit`: 5 points (5%) - Pay rate appropriateness
  - `job_type_preference`: 5 points (5%) - Job type alignment
  - `recency`: 3 points (3%) - Job freshness
  - `diversity_boost`: 2 points (2%) - Novelty factor

### Skill Relevance Levels
- **config/config.php** - Fuzzy matching levels:
  - `exact`: 1.0 - Exact match
  - `synonym`: 0.8 - Known synonym (e.g., "Carpentry" ≈ "Woodworking")
  - `related`: 0.5 - Related skill (e.g., "JavaScript" ≈ "React")
  - `category`: 0.3 - Same category (e.g., both programming languages)
  - `none`: 0 - No relation

### Skill Synonyms Database
- **config/config.php** - Predefined skill synonyms:
  - Carpentry: woodworking, furniture making, cabinet making
  - Welding: metalwork, fabrication, ironwork
  - Plumbing: pipe fitting, sanitation
  - Electrical: electrician, wiring, electrical work
  - JavaScript: js, react, vue, angular, node.js
  - Python: django, flask, data science
  - Photoshop: photo editing, graphic design, adobe
  - Excel: spreadsheets, data entry, microsoft office
  - Driving: driver, chauffeur, delivery
  - Cooking: chef, kitchen, food preparation, culinary
  - Cleaning: housekeeping, janitorial, maintenance
  - Gardening: landscaping, lawn care, horticulture

## Scoring Components

### 1. Skill Match Score (0-35 points)
- **Function**: `calculateSkillAffinityScore($userSkills, $jobRequiredSkills, $jobPreferredSkills)`
- **Location**: config/config.php
- **Logic**:
  - Required skills have 2x weight
  - Preferred skills have 1x weight
  - Uses fuzzy matching with skill similarity calculation
  - Calculates coverage percentage
  - Scales to 0-35 range
- **Factors**:
  - Exact matches get full points
  - Synonym matches get 80% credit
  - Related skills get 50% credit
  - Category matches get 30% credit
  - Levenshtein distance for typos/variations

### 2. Behavioral Score (0-25 points)
- **Function**: `calculateBehavioralScore($conn, $userId, $jobId, $jobCategory, $jobType, $employerId)`
- **Location**: config/config.php
- **Logic**:
  - Check if user viewed this job before (+5 points)
  - Check if user saved similar jobs (+8 points)
  - Check if user applied to jobs from this employer (+7 points)
  - Check messaging history with employer (+5 points)
- **Data Sources**:
  - user_interactions table (view, save actions)
  - job_applications table (application history)
  - messages table (communication history)

### 3. Collaborative Score (0-15 points)
- **Function**: `calculateCollaborativeScore($conn, $userId, $jobId)`
- **Location**: config/config.php
- **Logic**:
  - Find users with similar skills who liked this job
  - Scale: 1 similar user = 3 points, max 15 points
- **Data Sources**:
  - user_skills table (skill similarity)
  - user_interactions table (save, view actions)

### 4. Trust Compatibility Score (0-10 points)
- **Function**: `calculateTrustCompatibilityScore($userTrustScore, $employerTrustScore, $jobSlotsAvailable)`
- **Location**: config/config.php
- **Logic**:
  - Base score from user's trust (0-5 points)
  - Employer trust bonus (0-3 points)
  - Urgency bonus based on slot availability (0-2 points)
- **Factors**:
  - User trust score > 4.0: +3 points
  - User trust score > 3.0: +2 points
  - User trust score > 2.0: +1 point
  - 1 slot available: +2 points (very competitive)
  - 2-3 slots available: +1 point

### 5. Location Proximity Score (0-10 points)
- **Function**: `calculateLocationProximityScore($userRegion, $userProvince, $userCity, $jobRegion, $jobProvince, $jobCity)`
- **Location**: config/config.php
- **Logic**:
  - Same city: 10 points (perfect match)
  - Same province: 7 points
  - Same region: 4 points
  - Adjacent region: 2 points
  - Different region: 0 points
- **Adjacency Data**:
  - NCR adjacent to: Region IV-A, Region III
  - Region IV-A adjacent to: NCR, Region IV-B, Region V
  - Region III adjacent to: NCR, Region II, Region I

### 6. Compensation Fit Score (0-5 points)
- **Function**: `calculateCompensationFitScore($userId, $jobPayAmount, $jobPayType, $conn)`
- **Location**: config/config.php
- **Logic**:
  - Compare job pay to user's historical average
  - 20%+ above average: 5 points
  - At or above average: 4 points
  - Within 20% below: 3 points
  - 20-40% below: 2 points
  - Significantly below: 1 point
  - No history: 3 points (neutral)
- **Data Sources**:
  - job_applications table (historical pay rates)

### 7. Job Type Preference Score (0-5 points)
- **Function**: `calculateJobTypePreferenceScore($conn, $userId, $jobType)`
- **Location**: config/config.php
- **Logic**:
  - Check user's application history for this job type
  - 2 base points + 1 point per application
  - Max 5 points
  - No history: 2 points (neutral)
- **Data Sources**:
  - job_applications table (application history by job type)

### 8. Recency Score (0-3 points)
- **Function**: `calculateRecencyScore($createdAt)`
- **Location**: config/config.php
- **Logic**:
  - Less than 1 day: 3 points
  - Less than 3 days: 2 points
  - Less than 1 week: 1 point
  - Older than 1 week: 0 points

### 9. Diversity Boost (0-2 points)
- **Function**: `calculateDiversityBoost($conn, $userId, $jobCategory, $currentRecommendations)`
- **Location**: config/config.php
- **Logic**:
  - Penalize over-representation of categories
  - 5+ same category: 0 points
  - 3-4 same category: 1 point
  - 0-2 same category: 2 points (boost for novelty)

## Main Calculation Function

### calculateMatchScore()
- **Location**: config/config.php
- **Parameters**:
  - `$conn` - Database connection
  - `$userId` - Current user ID
  - `$job` - Job data array
  - `$userData` - User data array
  - `$currentRecommendations` - Already recommended jobs (for diversity)
- **Returns**: Array with:
  - `total` - Normalized score (0-100)
  - `raw_total` - Sum of component scores
  - `max_possible` - Maximum possible score
  - `breakdown` - Individual component scores
  - `match_tier` - Quality tier (excellent, very_good, good, fair, low)

## Match Tiers

### Tier Classification
- **Function**: `getMatchTier($score)`
- **Location**: config/config.php
- **Tiers**:
  - 85-100: excellent
  - 70-84: very_good
  - 55-69: good
  - 40-54: fair
  - 0-39: low

### Tier Display Info
- **Function**: `getMatchTierInfo($tier)`
- **Location**: config/config.php
- **Display Data**:
  - excellent: green color, star icon, "Excellent Match" label
  - very_good: blue color, thumbs-up icon, "Very Good Match" label
  - good: pink color, check icon, "Good Match" label
  - fair: orange color, minus icon, "Fair Match" label
  - low: gray color, arrow-down icon, "Low Match" label

## Recommendation Flows

### Worker Job Recommendations
- **Location**: for-you.php
- **Process**:
  1. Get user's skills from database
  2. Retrieve candidate jobs (broader set for ranking):
     - Active jobs only
     - Exclude user's own jobs
     - Exclude already applied jobs
     - Match by location OR skills
  3. Calculate MatchScore for each candidate
  4. Sort by match score descending
  5. Take top 15 recommendations
  6. Display with match tier and breakdown

### Employer Worker Recommendations
- **Location**: for-you.php
- **Process**:
  1. Get employer's active jobs
  2. Extract skills, categories, job types, locations from jobs
  3. Build search criteria from employer's posting patterns
  4. Find workers matching criteria
  5. Calculate match scores
  6. Sort and display top recommendations

## Data Sources

### User Data
- **users table**: Location, trust score, user type
- **user_skills table**: Skill names, proficiency levels
- **job_applications table**: Application history, pay rates
- **user_interactions table**: Job views, saves
- **messages table**: Communication history

### Job Data
- **job_posts table**: Job details, requirements, location, pay
- **users table**: Employer trust score, location

## Implementation Files

### Core Algorithm
- **config/config.php** - All MatchScore functions and configuration

### Recommendation Pages
- **for-you.php** - Personalized recommendations page
- **index.php** - Job listings with basic filtering
- **advanced-search.php** - Advanced search with filters

### Documentation
- **MATCHSCORE_ALGORITHM.md** - Detailed algorithm documentation
- **recommendation_logic.md** - This file

## Algorithm Features

### Fuzzy Matching
- Handles typos and variations
- Uses Levenshtein distance
- Synonym database for common skills
- Category-based matching

### Personalization
- Behavioral analysis of user actions
- Historical preference tracking
- Collaborative filtering from similar users
- Location-based preferences

### Diversity
- Prevents over-representation of categories
- Boosts under-represented categories
- Ensures varied recommendations

### Real-time Scoring
- Scores calculated on-demand
- Uses current user data
- Reflects recent interactions
- Updates with new applications

### Transparency
- Provides score breakdown to users
- Shows match tier classification
- Explains why jobs are recommended
- Helps users understand matching

## Performance Considerations

### Optimization
- Limits candidate set to 50 jobs for scoring
- Caches user data where possible
- Uses indexed database queries
- Pre-calculates common aggregations

### Scalability
- Algorithm is O(n) where n is candidate set size
- Database queries use proper indexes
- Skill matching uses efficient string operations
- Collaborative filtering limited to similar users

## Future Enhancements

### Potential Improvements
- Machine learning model for weight optimization
- Real-time feedback loop for score tuning
- A/B testing for algorithm variations
- More sophisticated skill taxonomy
- Time-decay for behavioral scores
- Geographic distance calculation (Haversine formula)
- Industry-specific matching rules
- Seasonal trend analysis
