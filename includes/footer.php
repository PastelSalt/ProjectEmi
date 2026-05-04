    </main>
    
    <footer class="footer">
        <div class="container">
            <div class="footer-main">
                <!-- Brand Column -->
                <div class="footer-brand">
                    <div class="brand-name">
                        <span class="accent">✿</span> Raket<span class="accent">G</span>o
                    </div>
                    <div class="brand-sub">job matching platform</div>
                    <div class="brand-detail">
                        RaketGo helps workers and employers connect faster through clear job posts,
                        guided applications, and direct messaging.
                    </div>
                </div>

                <!-- Quick Links Column -->
                <div>
                    <div class="footer-section-title">Quick Links</div>
                    <div class="footer-links">
                        <a href="index.php">Home</a>
                        <a href="skill-learn.php">Learn</a>
                        <?php if (isLoggedIn()): ?>
                            <a href="for-you.php">For You</a>
                            <a href="messages.php">Messages</a>
                            <a href="notifications.php">Notifications</a>
                            <?php if (getCurrentUserType() === 'worker'): ?>
                                <a href="dashboard-worker.php">My Dashboard</a>
                            <?php elseif (getCurrentUserType() === 'employer'): ?>
                                <a href="dashboard-employer.php">Employer Dashboard</a>
                            <?php elseif (getCurrentUserType() === 'admin'): ?>
                                <a href="dashboard-admin.php">Admin Dashboard</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="login.php">Login</a>
                            <a href="signup.php">Sign Up</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Help Column -->
                <div>
                    <div class="footer-section-title">Need Help?</div>
                    <div class="footer-policy">
                        Start by browsing jobs, then open each job page for full requirements.
                        Employers can post jobs and review applicants from their dashboard.
                        <br><br>
                        New here?
                        <a href="signup.php">Create an account</a>
                        or
                        <a href="login.php">log in</a>.
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                &copy; <?php echo date('Y'); ?> RaketGo by Moesoft &middot; Job Matching Platform &middot; 
                <a href="terms.php">Terms & Conditions</a>
            </div>
        </div>
    </footer>

    <button id="back-to-top" class="back-to-top" type="button" aria-label="Back to top">
        <i class="fas fa-arrow-up"></i>
    </button>
    
    <?php $scriptVersion = @filemtime(BASE_PATH . 'js/main.js') ?: time(); ?>
    <script src="js/main.js?v=<?php echo $scriptVersion; ?>"></script>
</body>
</html>
