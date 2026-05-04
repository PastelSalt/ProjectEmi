<?php
/**
 * Messages / Chat Page
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */
$page_title = 'Messages';
require_once 'config/config.php';
requireLogin();
require_once 'includes/header.php';

$conn = getDBConnection();
$user_id = getCurrentUserId();

$conversation_user_id = isset($_GET['user']) ? (int)$_GET['user'] : 0;
if ($conversation_user_id === $user_id) {
    $conversation_user_id = 0;
}

// Handle sending message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isValidCsrf = verifyCsrfToken($_POST['csrf_token'] ?? '');
    if (!$isValidCsrf) {
        $conversation_user_id = 0;
    }

    $receiver_id = (int)($_POST['receiver_id'] ?? 0);
    $message_content = sanitizeMultilineInput($_POST['message_content'] ?? '');
    
    if ($isValidCsrf && !empty($message_content) && $receiver_id > 0 && $receiver_id !== $user_id) {
        if (strlen($message_content) > 2000) {
            $message_content = substr($message_content, 0, 2000);
        }

        $receiver = fetchOne($conn, "SELECT user_id FROM users WHERE user_id = ? AND account_status = 'active'", [$receiver_id], 'i');
        if ($receiver) {
            $insertSql = "INSERT INTO messages (sender_id, receiver_id, message_content) VALUES (?, ?, ?)";
            if (executeQuery($conn, $insertSql, [$user_id, $receiver_id, $message_content], 'iis')) {
                $notifSql = "INSERT INTO notifications (user_id, notification_type, title, message, related_id, related_type, action_url)
                            VALUES (?, 'new_message', 'New Message', 'You have a new message', ?, 'message', ?)";
                executeQuery($conn, $notifSql, [$receiver_id, $user_id, "messages.php?user={$user_id}"], 'iis');
                $conversation_user_id = $receiver_id;
            }
        }
    }
}

// Handle user search
$search_query = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$search_results = [];

if (!empty($search_query)) {
    $searchSql = "SELECT user_id, full_name, user_type, profile_picture, account_status 
                   FROM users 
                   WHERE (full_name LIKE ? OR user_type LIKE ?) 
                   AND user_id != ? AND account_status = 'active'
                   ORDER BY full_name LIMIT 20";
    $search_param = "%{$search_query}%";
    $search_results = fetchAll($conn, $searchSql, [$search_param, $search_param, $user_id], 'ssi');
}

// Get all conversations
$conversationsSql = "SELECT DISTINCT
                     CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END as contact_id,
                     u.full_name, u.user_type, u.profile_picture,
                     (SELECT message_content FROM messages
                      WHERE (sender_id = ? AND receiver_id = contact_id) OR (sender_id = contact_id AND receiver_id = ?)
                      ORDER BY sent_at DESC LIMIT 1) as last_message,
                     (SELECT sent_at FROM messages
                      WHERE (sender_id = ? AND receiver_id = contact_id) OR (sender_id = contact_id AND receiver_id = ?)
                      ORDER BY sent_at DESC LIMIT 1) as last_message_time,
                     (SELECT COUNT(*) FROM messages
                      WHERE sender_id = contact_id AND receiver_id = ? AND is_read = 0) as unread_count
                     FROM messages m
                     JOIN users u ON u.user_id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END
                     WHERE ? IN (m.sender_id, m.receiver_id)
                     GROUP BY contact_id
                     ORDER BY last_message_time DESC";
$conversations = fetchAll($conn, $conversationsSql, [$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id], 'iiiiiiii');

$messages = [];
$conversation_partner = null;

if ($conversation_user_id > 0) {
    $conversation_partner = fetchOne($conn, "SELECT user_id, full_name, user_type, mobile_number, profile_picture FROM users WHERE user_id = ? AND account_status = 'active'", [$conversation_user_id], 'i');
    
    if ($conversation_partner) {
        $messagesSql = "SELECT m.*, sender.full_name as sender_name, receiver.full_name as receiver_name
                        FROM messages m
                        JOIN users sender ON m.sender_id = sender.user_id
                        JOIN users receiver ON m.receiver_id = receiver.user_id
                        WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
                        ORDER BY m.sent_at ASC";
        $messages = fetchAll($conn, $messagesSql, [$user_id, $conversation_user_id, $conversation_user_id, $user_id], 'iiii');
        
        executeQuery($conn, "UPDATE messages SET is_read = 1, read_at = NOW() WHERE sender_id = ? AND receiver_id = ? AND is_read = 0", [$conversation_user_id, $user_id], 'ii');
    }
}
?>

<div class="container">
    <div class="messages-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="margin: 0; color: var(--dark-charcoal); font-size: 1.5rem;">
            <i class="fas fa-comments" style="margin-right: 0.5rem; color: var(--sana-red);"></i>
            Messages
        </h1>
        <button onclick="document.querySelector('.messages-search-section').scrollIntoView({behavior: 'smooth'})" 
                class="btn btn-primary">
            <i class="fas fa-user-plus"></i> New Message
        </button>
    </div>
    <div class="messages-layout">
        <!-- Conversations List -->
        <div class="panel messages-conversations-panel">
            <!-- Search Users -->
            <div class="messages-search-section">
                <div class="section-header">
                    <span class="header-square"></span>
                    FIND PEOPLE
                </div>
                <div class="messages-search-form">
                    <form method="GET" action="messages.php">
                        <div class="input-group" style="display: flex; gap: 8px;">
                            <input type="text" name="search" class="form-control" placeholder="Search by name or user type..." value="<?php echo htmlspecialchars($search_query); ?>" style="flex: 1;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                            <?php if (!empty($search_query)): ?>
                                <a href="messages.php" class="btn btn-outline">
                                    <i class="fas fa-times"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                
                <?php if (!empty($search_query)): ?>
                    <div class="messages-search-results">
                        <?php if (empty($search_results)): ?>
                            <div class="text-center text-muted" style="padding: 1rem; font-size: 0.85rem;">
                                <i class="fas fa-search" style="display: block; margin-bottom: 0.5rem;"></i>
                                No users found for "<?php echo htmlspecialchars($search_query); ?>"
                            </div>
                        <?php else: ?>
                            <div class="search-results-header" style="padding: 0.5rem 0.8rem; font-size: 0.75rem; color: var(--text-muted); border-bottom: 1px solid var(--border-light);">
                                Found <?php echo count($search_results); ?> user(s)
                            </div>
                            <?php foreach ($search_results as $user): ?>
                                <div class="search-result-item" style="display: flex; align-items: center; gap: 0.6rem; padding: 0.7rem 0.8rem; border-bottom: 1px solid var(--border-light);">
                                    <?php if (!empty($user['profile_picture'])): ?>
                                        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                                    <?php else: ?>
                                        <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--sana-red); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; flex-shrink: 0;">
                                            <?php echo mb_strtoupper(mb_substr($user['full_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <strong style="font-size: 0.82rem;"><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                            <span class="tag tag-<?php echo $user['user_type'] == 'worker' ? 'pink' : 'blue'; ?>" style="font-size: 0.6rem;"><?php echo ucfirst($user['user_type']); ?></span>
                                        </div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted);">
                                            <?php echo ucfirst($user['user_type']); ?> • Click to message
                                        </div>
                                    </div>
                                    <a href="messages.php?user=<?php echo $user['user_id']; ?>" class="btn btn-primary btn-sm" style="text-decoration: none;">
                                        <i class="fas fa-comment"></i> Message
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="section-header" style="margin-top: <?php echo !empty($search_query) ? '1rem' : '0'; ?>;">
                <span class="header-square"></span>
                CONVERSATIONS
            </div>
            <?php if (empty($conversations)): ?>
                <div class="panel-body text-center text-muted" style="padding: 2rem;">
                    <i class="fas fa-inbox" style="font-size: 1.5rem; display: block; margin-bottom: 0.5rem;"></i>
                    No conversations yet
                </div>
            <?php else: ?>
                <div class="messages-conversations-list">
                    <?php foreach ($conversations as $conv): ?>
                        <a href="messages.php?user=<?php echo $conv['contact_id']; ?>"
                           class="message-item <?php echo $conv['unread_count'] > 0 ? 'unread' : ''; ?> <?php echo $conversation_user_id == $conv['contact_id'] ? 'active' : ''; ?>"
                           style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 0.6rem; padding: 0.7rem 0.8rem; border-bottom: 1px solid var(--border-light); <?php echo $conversation_user_id == $conv['contact_id'] ? 'background: var(--off-white);' : ''; ?>">
                            <?php if (!empty($conv['profile_picture'])): ?>
                                <img src="<?php echo htmlspecialchars($conv['profile_picture']); ?>" alt="" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                            <?php else: ?>
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--primary-blue); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; flex-shrink: 0;">
                                    <?php echo mb_strtoupper(mb_substr($conv['full_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                            <div style="flex: 1; min-width: 0;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <strong style="font-size: 0.82rem;"><?php echo htmlspecialchars($conv['full_name']); ?></strong>
                                    <?php if ($conv['unread_count'] > 0): ?>
                                        <span class="tag tag-pink" style="font-size: 0.6rem; min-width: 18px; text-align: center;"><?php echo $conv['unread_count']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size: 0.72rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?php echo htmlspecialchars(mb_substr($conv['last_message'], 0, 40, 'UTF-8')); ?>
                                </div>
                                <div style="font-size: 0.65rem; color: var(--text-muted);"><?php echo timeAgo($conv['last_message_time']); ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Chat Window -->
        <div class="panel messages-chat-panel">
            <?php if ($conversation_partner): ?>
                <!-- Chat Header -->
                <div class="section-header section-header-pink" style="display: flex; align-items: center; gap: 0.6rem;">
                    <?php if (!empty($conversation_partner['profile_picture'])): ?>
                        <img src="<?php echo htmlspecialchars($conversation_partner['profile_picture']); ?>" alt="" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                    <?php else: ?>
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #fff; color: var(--primary-blue); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem;">
                            <?php echo mb_strtoupper(mb_substr($conversation_partner['full_name'], 0, 1, 'UTF-8'), 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                    <?php echo htmlspecialchars(strtoupper($conversation_partner['full_name'])); ?>
                    <span class="tag tag-<?php echo $conversation_partner['user_type'] == 'worker' ? 'pink' : 'blue'; ?>" style="font-size: 0.6rem; margin-left: auto;"><?php echo ucfirst($conversation_partner['user_type']); ?></span>
                </div>

                <!-- Messages Area -->
                <div id="chat-messages" class="messages-chat-body">
                    <?php if (empty($messages)): ?>
                        <p class="text-center text-muted" style="padding: 2rem; font-size: 0.85rem;">
                            No messages yet. Start the conversation!
                        </p>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <div style="margin-bottom: 0.8rem; display: flex; <?php echo $msg['sender_id'] == $user_id ? 'justify-content: flex-end;' : 'justify-content: flex-start;'; ?>">
                                <div style="max-width: 70%; padding: 0.6rem 0.8rem; border-radius: 8px; font-size: 0.85rem;
                                    <?php echo $msg['sender_id'] == $user_id 
                                        ? 'background: var(--primary-blue); color: #fff;' 
                                        : 'background: #fff; border: 1px solid var(--border-light);'; ?>">
                                    <div style="font-weight: 600; font-size: 0.72rem; margin-bottom: 2px; opacity: 0.8;">
                                        <?php echo $msg['sender_id'] == $user_id ? 'You' : htmlspecialchars($msg['sender_name']); ?>
                                    </div>
                                    <div><?php echo nl2br(htmlspecialchars($msg['message_content'])); ?></div>
                                    <div style="font-size: 0.65rem; opacity: 0.6; margin-top: 3px; text-align: right;">
                                        <?php echo timeAgo($msg['sent_at']); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Message Input -->
                <div class="messages-compose-wrap">
                    <form method="POST" class="messages-compose-form">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="receiver_id" value="<?php echo $conversation_partner['user_id']; ?>">
                        <textarea name="message_content" class="form-control" placeholder="Type your message..." rows="2" required></textarea>
                        <button type="submit" class="btn btn-primary" style="align-self: flex-end;">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="section-header section-header-gray">
                    <span class="header-square"></span>
                    MESSAGES
                </div>
                <div class="messages-empty-state">
                    <div>
                        <i class="fas fa-comments" style="font-size: 2.5rem; margin-bottom: 0.8rem; display: block; opacity: 0.4;"></i>
                        <strong>Select a conversation</strong>
                        <p style="font-size: 0.82rem;">Choose from the left panel to start messaging</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var chat = document.getElementById('chat-messages');
    if (chat) chat.scrollTop = chat.scrollHeight;
    
    // Enhanced search functionality
    var searchInput = document.querySelector('input[name="search"]');
    var searchForm = document.querySelector('.messages-search-form form');
    
    if (searchInput && searchForm) {
        // Add real-time search with debounce
        var searchTimeout;
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            var query = e.target.value.trim();
            
            searchTimeout = setTimeout(function() {
                if (query.length >= 2 || query.length === 0) {
                    // Update URL without page reload for better UX
                    var url = new URL(window.location);
                    if (query) {
                        url.searchParams.set('search', query);
                    } else {
                        url.searchParams.delete('search');
                    }
                    url.searchParams.delete('user'); // Clear conversation when searching
                    window.location.href = url.toString();
                }
            }, 300);
        });
        
        // Add clear search on ESC key
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.location.href = 'messages.php';
            }
        });
    }
    
    // Add click handlers for search results
    var searchResults = document.querySelectorAll('.search-result-item');
    searchResults.forEach(function(item) {
        item.addEventListener('click', function(e) {
            if (!e.target.closest('a')) {
                var link = item.querySelector('a[href*="messages.php?user="]');
                if (link) {
                    window.location.href = link.href;
                }
            }
        });
    });
    
    // Auto-scroll to latest message
    if (chat) {
        // Smooth scroll to bottom
        chat.scrollTo({
            top: chat.scrollHeight,
            behavior: 'smooth'
        });
    }
    
    // Add message sending feedback
    var messageForm = document.querySelector('.messages-compose-form');
    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {
            var submitBtn = messageForm.querySelector('button[type="submit"]');
            var originalText = submitBtn.innerHTML;
            
            // Show sending state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            submitBtn.disabled = true;
            
            // Reset after 2 seconds (in case of issues)
            setTimeout(function() {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 2000);
        });
    }
});
</script>

<?php
closeDBConnection($conn);
require_once 'includes/footer.php';
?>
