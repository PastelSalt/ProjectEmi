# RaketGo MatchScore™ Recommendation Algorithm

## Overview

The **RaketGo MatchScore™** is a proprietary multi-factor recommendation algorithm designed specifically for the RaketGo job matching platform. It goes beyond simple keyword matching to provide intelligent, personalized job and worker recommendations.

## Algorithm Philosophy

MatchScore™ operates on 9 core dimensions with weighted scoring:

| Factor | Weight | Range | Description |
|--------|--------|-------|-------------|
| Skill Match | 35% | 0-35 pts | Fuzzy skill matching with synonyms |
| Behavioral | 25% | 0-25 pts | User's interaction patterns |
| Collaborative | 15% | 0-15 pts | Similar users' preferences |
| Trust Compatibility | 10% | 0-10 pts | Trust score alignment |
| Location Proximity | 10% | 0-10 pts | Geographic distance scoring |
| Compensation Fit | 5% | 0-5 pts | Pay rate appropriateness |
| Job Type Preference | 5% | 0-5 pts | Preferred job type matching |
| Recency | 3% | 0-3 pts | Job freshness boost |
| Diversity Boost | 2% | 0-2 pts | Novelty/variety factor |

**Total Score Range: 0-100**

## Match Tiers

Based on the normalized score, jobs/workers are classified into tiers:

| Tier | Score Range | Label | Icon |
|------|-------------|-------|------|
| Excellent | 85-100 | Excellent Match | ⭐ fa-star |
| Very Good | 70-84 | Very Good Match | 👍 fa-thumbs-up |
| Good | 55-69 | Good Match | ✓ fa-check |
| Fair | 40-54 | Fair Match | − fa-minus |
| Low | 0-39 | Low Match | ↓ fa-arrow-down |

## Core Components

### 1. SkillAffinityScore (0-35 points)

**Fuzzy Matching Logic:**
```
Exact Match: 100% (e.g., "Carpentry" = "Carpentry")
Synonym: 80% (e.g., "Carpentry" ≈ "Woodworking")
Related: 50% (e.g., "JavaScript" ≈ "React")
Partial: 50% (substring match)
Levenshtein: 50% (typo tolerance <30% difference)
None: 0%
```

**Weighting:**
- Required skills: 2x multiplier
- Preferred skills: 1x multiplier

**Example:**
```php
$userSkills = ['Carpentry', 'Woodworking', 'Furniture Making'];
$jobRequired = 'Carpentry, Cabinet Making';
$jobPreferred = 'Wood Finishing';

// Scoring:
// - Carpentry (exact match) × 2 = 2.0
// - Cabinet Making (synonym of Carpentry) × 2 × 0.8 = 1.6
// - Wood Finishing (partial match) × 1 × 0.5 = 0.5
// Total: 4.1/6.0 = 68.3% coverage = 24 points (out of 35)
```

### 2. BehavioralScore (0-25 points)

**Tracks user behavior patterns:**
- Job viewed before: +5 pts (shows interest)
- Saved similar jobs (same category/type): +8 pts
- Applied to this employer before: +7 pts
- Messaged this employer: +5 pts

**Rationale:** Users who engage with similar content or specific employers are more likely to be interested in related opportunities.

### 3. CollaborativeScore (0-15 points)

**Social proof scoring:**
- Finds users with similar skill sets
- Checks if they viewed/saved the job
- Score = min(15, similar_users × 3)

**Formula:**
```
If 5+ similar users liked the job: 15 points
If 3 similar users liked it: 9 points
If 1 similar user liked it: 3 points
```

### 4. TrustCompatibilityScore (0-10 points)

**User Trust Component (0-5 pts):**
- Score = min(5, user_trust_score / 2)
- Trust score 5.0 → 2.5 pts
- Trust score 3.0 → 1.5 pts

**Employer Trust Component (0-3 pts):**
- Trust > 4.0: 3 pts
- Trust > 3.0: 2 pts
- Trust > 2.0: 1 pt

**Urgency Bonus (0-2 pts):**
- 1 slot available: 2 pts (very competitive)
- 2-3 slots: 1 pt
- 4+ slots: 0 pts

### 5. LocationProximityScore (0-10 points)

**Geographic hierarchy:**
- Same city: 10 pts (perfect match)
- Same province: 7 pts
- Same region: 4 pts
- Adjacent region: 2 pts
- Different region: 0 pts

**Adjacent regions defined:**
- NCR ↔ Region IV-A, Region III
- Region IV-A ↔ NCR, Region IV-B, Region V
- Region III ↔ NCR, Region II, Region I

### 6. CompensationFitScore (0-5 points)

**Historical comparison:**
```
Job Pay / User's Average Pay:
≥ 1.20 (20%+ above): 5 pts
≥ 1.00 (at or above): 4 pts
≥ 0.80 (within 20% below): 3 pts
≥ 0.60 (20-40% below): 2 pts
< 0.60 (significantly below): 1 pt
```

**No history:** Neutral 3 pts

### 7. JobTypePreferenceScore (0-5 points)

**Based on application history:**
- Base score: 2 pts (neutral)
- +1 pt per previous application of same type
- Max: 5 pts

**Example:**
```
User applied to 3 "part_time" jobs before → 5 pts for part_time jobs
Never applied to "contractual" → 2 pts
```

### 8. RecencyScore (0-3 points)

**Job freshness decay:**
- < 24 hours: 3 pts (hot job)
- < 72 hours: 2 pts (recent)
- < 1 week: 1 pt (fairly recent)
- > 1 week: 0 pts

### 9. DiversityBoost (0-2 points)

**Prevents filter bubbles:**
- Same category appears 0-2 times: 2 pts (boost diversity)
- Same category appears 3-4 times: 1 pt
- Same category appears 5+ times: 0 pts (penalize repetition)

## Implementation Example

### For Workers (Job Recommendations)

```php
// 1. Fetch candidate jobs (broad set)
$candidateJobs = fetchAll($conn, 
    "SELECT j.*, u.full_name as employer_name, u.trust_score as employer_trust_score
     FROM job_posts j
     JOIN users u ON j.employer_id = u.user_id
     WHERE j.job_status = 'active'
     AND j.employer_id != ?
     AND j.job_id NOT IN (SELECT job_id FROM job_applications WHERE worker_id = ?)
     ORDER BY j.created_at DESC LIMIT 50",
    [$user_id, $user_id], 'ii'
);

// 2. Calculate MatchScore™ for each
foreach ($candidateJobs as $job) {
    $matchResult = calculateMatchScore($conn, $user_id, $job, $user, $recommendedJobs);
    $job['match_score'] = $matchResult['total'];      // 0-100
    $job['match_tier'] = $matchResult['match_tier'];  // excellent, good, etc.
    $job['match_breakdown'] = $matchResult['breakdown']; // Component scores
    $recommendedJobs[] = $job;
}

// 3. Sort and take top recommendations
usort($recommendedJobs, function($a, $b) {
    return $b['match_score'] <=> $a['match_score'];
});
$topRecommendations = array_slice($recommendedJobs, 0, 15);
```

### For Employers (Worker Recommendations)

```php
// Similar process but scoring workers against employer's job requirements
// Uses calculateSkillAffinityScore() for skill matching
// Considers location proximity to job sites
// Factors in worker's trust score and verified skills
```

## Sample Breakdown Output

```php
[
    'total' => 87,
    'raw_total' => 87,
    'max_possible' => 100,
    'match_tier' => 'excellent',
    'breakdown' => [
        'skill_match' => 32,           // 91% of max 35
        'behavioral' => 18,            // 72% of max 25
        'collaborative' => 12,         // 80% of max 15
        'trust_compatibility' => 8,    // 80% of max 10
        'location_proximity' => 10,    // 100% of max 10
        'compensation_fit' => 4,       // 80% of max 5
        'job_type_preference' => 5,    // 100% of max 5
        'recency' => 3,                // 100% of max 3
        'diversity_boost' => 2         // 100% of max 2
    ]
]
```

## UI Integration

### Match Tier Badges

```html
<?php if ($job['match_score'] >= 70): ?>
    <span class="tag" style="background: var(--green-light); color: var(--green-dark);">
        <i class="fas fa-star"></i>
        Excellent Match (87%)
    </span>
<?php endif; ?>
```

### Color Coding

| Tier | Background | Text |
|------|------------|------|
| Excellent | --green-light | --green-dark |
| Very Good | --blue-light | --blue-dark |
| Good | --pink-light | --pink-dark |
| Fair | --orange-light | --orange-dark |
| Low | --gray-light | --gray-dark |

## Algorithm Advantages

1. **Fuzzy Skill Matching**: Recognizes "Carpentry" and "Woodworking" as related
2. **Behavioral Learning**: Adapts to user's viewing/saving patterns
3. **Social Proof**: Leverages what similar users liked
4. **Trust-Aware**: Prioritizes reliable employers and workers
5. **Location-Smart**: Understands city/province/region hierarchy
6. **Anti-Filter Bubble**: Diversity boost prevents repetitive recommendations
7. **Real-Time Scoring**: Recency factor highlights fresh opportunities

## Future Enhancements

Potential improvements for MatchScore™ 2.0:

1. **Machine Learning**: Train on successful job placements
2. **Time Decay**: Behavioral signals fade over time
3. **Seasonal Adjustments**: Boost in-demand skills by season
4. **Economic Factors**: Adjust pay expectations by market rates
5. **Completion Prediction**: ML model predicting job completion success
6. **A/B Testing Framework**: Continuously optimize weights

## Database Indexes Required

For optimal performance:

```sql
-- User interactions for behavioral scoring
CREATE INDEX idx_interactions_user_type ON user_interactions(user_id, interaction_type, job_id);

-- Skill matching
CREATE INDEX idx_user_skills_name ON user_skills(skill_name, user_id);

-- Job skills
CREATE INDEX idx_jobs_skills ON job_posts(required_skills(100), preferred_skills(100));
```

## Testing the Algorithm

```php
// Test with sample data
$testJob = [
    'job_id' => 123,
    'required_skills' => 'Carpentry, Welding',
    'preferred_skills' => 'Plumbing',
    'job_type' => 'contractual',
    'pay_amount' => 500,
    'pay_type' => 'daily',
    'employer_trust_score' => 4.5,
    'slots_available' => 2,
    'created_at' => date('Y-m-d H:i:s')
];

$result = calculateMatchScore($conn, $userId, $testJob, $userData, []);
print_r($result);
```

---

**Version**: 1.0  
**Created**: RaketGo Platform  
**Proprietary Algorithm**: © Moesoft (Moeko Software)
