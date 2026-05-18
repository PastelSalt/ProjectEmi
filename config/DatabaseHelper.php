<?php
/**
 * Database Helper Class
 * Standardizes common database operations and query patterns
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */

class DatabaseHelper {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Get user by ID with optional fields
     */
    public function getUserById($userId, $fields = '*') {
        $sql = "SELECT $fields FROM users WHERE user_id = ? AND account_status = 'active'";
        return fetchOne($this->conn, $sql, [$userId], 'i');
    }
    
    /**
     * Get user by mobile number
     */
    public function getUserByMobile($mobile) {
        $sql = "SELECT * FROM users WHERE mobile_number = ? AND account_status = 'active'";
        return fetchOne($this->conn, $sql, [$mobile], 's');
    }
    
    /**
     * Get job with employer details
     */
    public function getJobWithEmployer($jobId) {
        $sql = "SELECT j.*, u.full_name as employer_name, u.mobile_number as employer_phone, 
                       u.email as employer_email, u.region, u.province, u.trust_score as employer_trust_score
                FROM job_posts j 
                JOIN users u ON j.employer_id = u.user_id 
                WHERE j.job_id = ?";
        return fetchOne($this->conn, $sql, [$jobId], 'i');
    }
    
    /**
     * Check if user has interacted with job
     */
    public function getUserJobInteraction($userId, $jobId, $interactionType) {
        $sql = "SELECT interaction_id FROM user_interactions 
                WHERE user_id = ? AND job_id = ? AND interaction_type = ? LIMIT 1";
        return fetchOne($this->conn, $sql, [$userId, $jobId, $interactionType], 'iss');
    }
    
    /**
     * Create user job interaction
     */
    public function createUserJobInteraction($userId, $jobId, $interactionType) {
        $sql = "INSERT INTO user_interactions (user_id, interaction_type, job_id) VALUES (?, ?, ?)";
        return executeQuery($this->conn, $sql, [$userId, $interactionType, $jobId], 'iss');
    }
    
    /**
     * Get job application
     */
    public function getJobApplication($userId, $jobId) {
        $sql = "SELECT * FROM job_applications WHERE worker_id = ? AND job_id = ?";
        return fetchOne($this->conn, $sql, [$userId, $jobId], 'ii');
    }
    
    /**
     * Create job application
     */
    public function createJobApplication($userId, $jobId, $coverLetter = '') {
        $sql = "INSERT INTO job_applications (worker_id, job_id, cover_letter) VALUES (?, ?, ?)";
        return executeQuery($this->conn, $sql, [$userId, $jobId, $coverLetter], 'iss');
    }
    
    /**
     * Update job application status
     */
    public function updateApplicationStatus($applicationId, $status) {
        $sql = "UPDATE job_applications SET application_status = ? WHERE application_id = ?";
        return executeQuery($this->conn, $sql, [$status, $applicationId], 'si');
    }
    
    /**
     * Get user skills
     */
    public function getUserSkills($userId) {
        $sql = "SELECT * FROM user_skills WHERE user_id = ? ORDER BY skill_name";
        return fetchAll($this->conn, $sql, [$userId], 'i');
    }
    
    /**
     * Add user skill
     */
    public function addUserSkill($userId, $skillName, $proficiency = 'intermediate') {
        $sql = "INSERT INTO user_skills (user_id, skill_name, proficiency_level) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE proficiency_level = ?";
        return executeQuery($this->conn, $sql, [$userId, $skillName, $proficiency, $proficiency], 'isss');
    }
    
    /**
     * Remove user skill
     */
    public function removeUserSkill($userId, $skillName) {
        $sql = "DELETE FROM user_skills WHERE user_id = ? AND skill_name = ?";
        return executeQuery($this->conn, $sql, [$userId, $skillName], 'is');
    }
    
    /**
     * Get user ratings
     */
    public function getUserRatings($userId, $ratingType = null) {
        $sql = "SELECT jr.*, u.full_name as rater_name 
                FROM job_ratings jr 
                JOIN users u ON jr.rater_id = u.user_id 
                WHERE jr.ratee_id = ?";
        $params = [$userId];
        $types = 'i';
        
        if ($ratingType) {
            $sql .= " AND jr.rating_type = ?";
            $params[] = $ratingType;
            $types .= 's';
        }
        
        $sql .= " ORDER BY jr.created_at DESC";
        return fetchAll($this->conn, $sql, $params, $types);
    }
    
    /**
     * Create rating
     */
    public function createRating($raterId, $rateeId, $ratingType, $stars, $feedback) {
        $sql = "INSERT INTO job_ratings (rater_id, ratee_id, rating_type, rating_stars, feedback) 
                VALUES (?, ?, ?, ?, ?)";
        return executeQuery($this->conn, $sql, [$raterId, $rateeId, $ratingType, $stars, $feedback], 'iisis');
    }
    
    /**
     * Get user trust score
     */
    public function getUserTrustScore($userId) {
        $sql = "SELECT AVG(rating_stars) as avg_rating, COUNT(*) as rating_count 
                FROM job_ratings WHERE ratee_id = ?";
        $result = fetchOne($this->conn, $sql, [$userId], 'i');
        return $result ? (float)$result['avg_rating'] : 0.0;
    }
    
    /**
     * Get user notifications
     */
    public function getUserNotifications($userId, $limit = 50, $unreadOnly = false) {
        $sql = "SELECT * FROM notifications WHERE user_id = ?";
        $params = [$userId];
        $types = 'i';
        
        if ($unreadOnly) {
            $sql .= " AND is_read = 0";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        $types .= 'i';
        
        return fetchAll($this->conn, $sql, $params, $types);
    }
    
    /**
     * Mark notifications as read
     */
    public function markNotificationsRead($userId, $notificationId = null) {
        if ($notificationId) {
            $sql = "UPDATE notifications SET is_read = 1, read_at = NOW() 
                    WHERE user_id = ? AND notification_id = ?";
            return executeQuery($this->conn, $sql, [$userId, $notificationId], 'ii');
        } else {
            $sql = "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ?";
            return executeQuery($this->conn, $sql, [$userId], 'i');
        }
    }
    
    /**
     * Get user messages
     */
    public function getUserConversations($userId) {
        $sql = "SELECT DISTINCT CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END as contact_id,
                       u.full_name, u.user_type, u.profile_picture,
                       (SELECT content FROM messages
                        WHERE (sender_id = ? AND receiver_id = contact_id) OR 
                              (sender_id = contact_id AND receiver_id = ?)
                        ORDER BY created_at DESC LIMIT 1) as last_message,
                       (SELECT created_at FROM messages
                        WHERE (sender_id = ? AND receiver_id = contact_id) OR 
                              (sender_id = contact_id AND receiver_id = ?)
                        ORDER BY created_at DESC LIMIT 1) as last_message_time,
                       (SELECT COUNT(*) FROM messages
                        WHERE sender_id = contact_id AND receiver_id = ? AND is_read = 0) as unread_count
                FROM messages m
                JOIN users u ON u.user_id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END
                WHERE ? IN (m.sender_id, m.receiver_id)
                GROUP BY contact_id
                ORDER BY last_message_time DESC";
        
        return fetchAll($this->conn, $sql, [$userId, $userId, $userId, $userId, $userId, $userId, $userId, $userId], 'iiiiiiii');
    }
    
    /**
     * Get conversation messages
     */
    public function getConversationMessages($userId, $contactId) {
        $sql = "SELECT m.*, sender.full_name as sender_name, receiver.full_name as receiver_name
                FROM messages m
                JOIN users sender ON m.sender_id = sender.user_id
                JOIN users receiver ON m.receiver_id = receiver.user_id
                WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
                ORDER BY m.created_at ASC";
        return fetchAll($this->conn, $sql, [$userId, $contactId, $contactId, $userId], 'iiii');
    }
    
    /**
     * Send message
     */
    public function sendMessage($senderId, $receiverId, $content) {
        $sql = "INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)";
        return executeQuery($this->conn, $sql, [$senderId, $receiverId, $content], 'iis');
    }
    
    /**
     * Mark messages as read
     */
    public function markMessagesRead($senderId, $receiverId) {
        $sql = "UPDATE messages SET is_read = 1 
                WHERE sender_id = ? AND receiver_id = ? AND is_read = 0";
        return executeQuery($this->conn, $sql, [$senderId, $receiverId], 'ii');
    }
    
    /**
     * Get social profile
     */
    public function getSocialProfile($userId) {
        $sql = "SELECT * FROM social_profiles WHERE user_id = ?";
        return fetchOne($this->conn, $sql, [$userId], 'i');
    }
    
    /**
     * Create or update social profile
     */
    public function upsertSocialProfile($userId, $bio, $headline, $location) {
        $sql = "INSERT INTO social_profiles (user_id, bio, headline, location) 
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE bio = ?, headline = ?, location = ?";
        return executeQuery($this->conn, $sql, [$userId, $bio, $headline, $location, $bio, $headline, $location], 'issssss');
    }
    
    /**
     * Get social posts
     */
    public function getSocialPosts($limit = 20, $userId = null) {
        $sql = "SELECT sp.*, u.full_name, u.profile_picture, 
                       (SELECT COUNT(*) FROM social_likes WHERE post_id = sp.post_id) as likes_count,
                       (SELECT COUNT(*) FROM social_comments WHERE post_id = sp.post_id) as comments_count
                FROM social_posts sp
                JOIN users u ON sp.user_id = u.user_id";
        
        $params = [];
        $types = '';
        
        if ($userId) {
            $sql .= " WHERE sp.user_id = ?";
            $params[] = $userId;
            $types .= 'i';
        }
        
        $sql .= " ORDER BY sp.created_at DESC LIMIT ?";
        $params[] = $limit;
        $types .= 'i';
        
        return fetchAll($this->conn, $sql, $params, $types);
    }
    
    /**
     * Create social post
     */
    public function createSocialPost($userId, $content, $title = '', $postType = 'career_update') {
        $sql = "INSERT INTO social_posts (user_id, content, title, post_type) VALUES (?, ?, ?, ?)";
        return executeQuery($this->conn, $sql, [$userId, $content, $title, $postType], 'isss');
    }
    
    /**
     * Toggle social post like
     */
    public function togglePostLike($userId, $postId) {
        // Check if already liked
        $existing = fetchOne($this->conn, "SELECT like_id FROM social_likes WHERE user_id = ? AND post_id = ?", [$userId, $postId], 'ii');
        
        if ($existing) {
            // Unlike
            executeQuery($this->conn, "DELETE FROM social_likes WHERE user_id = ? AND post_id = ?", [$userId, $postId], 'ii');
            return false;
        } else {
            // Like
            executeQuery($this->conn, "INSERT INTO social_likes (user_id, post_id) VALUES (?, ?)", [$userId, $postId], 'ii');
            return true;
        }
    }
    
    /**
     * Check if user liked post
     */
    public function userLikedPost($userId, $postId) {
        $sql = "SELECT like_id FROM social_likes WHERE user_id = ? AND post_id = ?";
        return fetchOne($this->conn, $sql, [$userId, $postId], 'ii') !== false;
    }
    
    /**
     * Follow/unfollow user
     */
    public function toggleFollow($followerId, $followingId) {
        // Check if already following
        $existing = fetchOne($this->conn, "SELECT follow_id FROM social_follows WHERE follower_id = ? AND following_id = ?", [$followerId, $followingId], 'ii');
        
        if ($existing) {
            // Unfollow
            executeQuery($this->conn, "DELETE FROM social_follows WHERE follower_id = ? AND following_id = ?", [$followerId, $followingId], 'ii');
            return false;
        } else {
            // Follow
            executeQuery($this->conn, "INSERT INTO social_follows (follower_id, following_id) VALUES (?, ?)", [$followerId, $followingId], 'ii');
            return true;
        }
    }
    
    /**
     * Check if user is following another user
     */
    public function isFollowing($followerId, $followingId) {
        $sql = "SELECT follow_id FROM social_follows WHERE follower_id = ? AND following_id = ?";
        return fetchOne($this->conn, $sql, [$followerId, $followingId], 'ii') !== false;
    }
    
    /**
     * Get follower/following counts
     */
    public function getFollowCounts($userId) {
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM social_follows WHERE follower_id = ?) as following_count,
                    (SELECT COUNT(*) FROM social_follows WHERE following_id = ?) as followers_count";
        return fetchOne($this->conn, $sql, [$userId, $userId], 'ii');
    }
}
