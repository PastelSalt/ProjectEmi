<?php
/**
 * Terms and Conditions Page
 * RaketGo - Job Matching Platform
 * Created and managed by Moesoft (Moeko Software)
 */
$page_title = 'Terms and Conditions';
define('BASE_PATH', __DIR__ . '/');
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - RaketGo</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Noto+Sans+JP:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="index.php">
                    <span class="accent">✿</span> Raket<span class="accent">G</span>o
                </a>
            </div>
            <nav class="nav-menu">
                <a href="index.php">Home</a>
                <a href="skill-learn.php">Learn</a>
                <a href="login.php">Login</a>
                <a href="signup.php" class="btn btn-primary btn-small">Sign Up</a>
            </nav>
        </div>
    </header>

    <main class="main-content">

<div class="container">
    <div class="panel">
        <div class="section-header">
            <span class="header-square"></span>
            TERMS AND CONDITIONS
        </div>
        <div class="panel-body">
            <div class="terms-content">
                <div class="terms-header">
                    <h1>RaketGo Terms and Conditions</h1>
                    <p class="terms-subtitle">Last updated: May 2026</p>
                    <p class="terms-intro">
                        Welcome to RaketGo, a localized job matching platform connecting employers and workers in the Philippines. 
                        By accessing or using our platform, you agree to be bound by these Terms and Conditions.
                    </p>
                </div>

                <div class="terms-section">
                    <h2>1. Acceptance of Terms</h2>
                    <p>By accessing and using RaketGo, you accept and agree to be bound by the terms and provision of this agreement. 
                    If you do not agree to abide by the above, please do not use this service.</p>
                </div>

                <div class="terms-section">
                    <h2>2. Platform Description</h2>
                    <p>RaketGo is a job matching platform that connects verified employers with job seekers in the Philippines. 
                    Our platform facilitates:</p>
                    <ul>
                        <li>Job posting and application management</li>
                        <li>Worker portfolio and skill verification</li>
                        <li>In-platform messaging and communication</li>
                        <li>Digital contract generation</li>
                        <li>Payment processing and escrow services</li>
                        <li>Trust scoring and rating system</li>
                    </ul>
                </div>

                <div class="terms-section">
                    <h2>3. User Accounts</h2>
                    <h3>3.1 Account Registration</h3>
                    <p>To use RaketGo, you must register for an account. You agree to:</p>
                    <ul>
                        <li>Provide accurate, current, and complete information</li>
                        <li>Maintain and update your information regularly</li>
                        <li>Keep your password secure and confidential</li>
                        <li>Accept responsibility for all activities under your account</li>
                    </ul>

                    <h3>3.2 Account Types</h3>
                    <p>RaketGo supports three account types:</p>
                    <ul>
                        <li><strong>Workers:</strong> Job seekers seeking employment opportunities</li>
                        <li><strong>Employers:</strong> Businesses or individuals posting jobs</li>
                        <li><strong>Admins:</strong> Platform administrators and moderators</li>
                    </ul>

                    <h3>3.3 Account Suspension</h3>
                    <p>We reserve the right to suspend or terminate accounts that violate these terms or engage in fraudulent activities.</p>
                </div>

                <div class="terms-section">
                    <h2>4. User Responsibilities</h2>
                    <h3>4.1 General Responsibilities</h3>
                    <p>All users must:</p>
                    <ul>
                        <li>Provide truthful and accurate information</li>
                        <li>Respect other users and maintain professional conduct</li>
                        <li>Comply with applicable Philippine laws and regulations</li>
                        <li>Not use the platform for illegal or harmful activities</li>
                    </ul>

                    <h3>4.2 Worker Responsibilities</h3>
                    <p>Workers must:</p>
                    <ul>
                        <li>Only apply for jobs they are qualified to perform</li>
                        <li>Complete agreed-upon work professionally and on time</li>
                        <li>Maintain accurate portfolio and skill information</li>
                        <li>Respond promptly to employer communications</li>
                    </ul>

                    <h3>4.3 Employer Responsibilities</h3>
                    <p>Employers must:</p>
                    <ul>
                        <li>Post accurate and truthful job descriptions</li>
                        <li>Pay agreed-upon compensation promptly</li>
                        <li>Provide clear work instructions and expectations</li>
                        <li>Treat workers with respect and professionalism</li>
                    </ul>
                </div>

                <div class="terms-section">
                    <h2>5. Job Postings and Applications</h2>
                    <h3>5.1 Job Postings</h3>
                    <p>Employers are responsible for ensuring job postings are:</p>
                    <ul>
                        <li>Accurate and not misleading</li>
                        <li>Compliant with labor laws</li>
                        <li>Appropriately categorized and compensated</li>
                        <li>Not discriminatory or illegal</li>
                    </ul>

                    <h3>5.2 Applications</h3>
                    <p>Workers should only apply for positions they are genuinely interested in and qualified for. 
                    Misrepresentation in applications may result in account suspension.</p>
                </div>

                <div class="terms-section">
                    <h2>6. Payments and Financial Transactions</h2>
                    <h3>6.1 Payment Processing</h3>
                    <p>RaketGo facilitates payment processing between employers and workers. Payment terms include:</p>
                    <ul>
                        <li>Advance payments may be required for certain jobs</li>
                        <li>Final payments are released upon work completion</li>
                        <li>Platform fees may apply to transactions</li>
                        <li>All payments are processed in Philippine Pesos (PHP)</li>
                    </ul>

                    <h3>6.2 Refund Policy</h3>
                    <p>Refunds are handled on a case-by-case basis and depend on:</p>
                    <ul>
                        <li>Work completion status</li>
                        <li>Mutual agreement between parties</li>
                        <li>Platform dispute resolution outcomes</li>
                    </ul>
                </div>

                <div class="terms-section">
                    <h2>7. Trust Score and Rating System</h2>
                    <p>RaketGo maintains a trust scoring system based on:</p>
                    <ul>
                        <li>Completed jobs and successful transactions</li>
                        <li>Peer ratings and reviews</li>
                        <li>Profile completeness and verification</li>
                        <li>Platform activity and engagement</li>
                    </ul>
                    <p>Trust scores help users make informed decisions but do not guarantee performance or reliability.</p>
                </div>

                <div class="terms-section">
                    <h2>8. Privacy and Data Protection</h2>
                    <h3>8.1 Data Collection</h3>
                    <p>We collect information necessary to provide our services, including:</p>
                    <ul>
                        <li>Personal identification information</li>
                        <li>Professional qualifications and work history</li>
                        <li>Communication and transaction records</li>
                        <li>Usage analytics and platform interactions</li>
                    </ul>

                    <h3>8.2 Data Usage</h3>
                    <p>Your data is used to:</p>
                    <ul>
                        <li>Provide and improve our services</li>
                        <li>Facilitate job matching and communications</li>
                        <li>Ensure platform security and trust</li>
                        <li>Comply with legal requirements</li>
                    </ul>

                    <h3>8.3 Data Protection</h3>
                    <p>We implement appropriate security measures to protect your personal information in accordance with the Philippine Data Privacy Act of 2012.</p>
                </div>

                <div class="terms-section">
                    <h2>9. Intellectual Property</h2>
                    <h3>9.1 Platform Content</h3>
                    <p>RaketGo and its content, features, and functionality are owned by Moesoft (Moeko Software) and are protected by copyright, trademark, and other intellectual property laws.</p>

                    <h3>9.2 User Content</h3>
                    <p>Users retain ownership of content they upload to the platform but grant RaketGo a license to use, display, and distribute such content for platform operations.</p>
                </div>

                <div class="terms-section">
                    <h2>10. Prohibited Activities</h2>
                    <p>Users are prohibited from:</p>
                    <ul>
                        <li>Posting false or misleading information</li>
                        <li>Engaging in fraudulent or scam activities</li>
                        <li>Harassing, threatening, or abusing other users</li>
                        <li>Violating applicable laws and regulations</li>
                        <li>Attempting to hack or disrupt platform operations</li>
                        <li>Using the platform for illegal purposes</li>
                        <li>Spamming or sending unsolicited communications</li>
                    </ul>
                </div>

                <div class="terms-section">
                    <h2>11. Dispute Resolution</h2>
                    <h3>11.1 Platform Mediation</h3>
                    <p>RaketGo provides mediation services for disputes between employers and workers. Our mediation process includes:</p>
                    <ul>
                        <li>Review of transaction records and communications</li>
                        <li>Facilitated discussion between parties</li>
                        <li>Binding decisions based on platform policies</li>
                    </ul>

                    <h3>11.2 Final Decisions</h3>
                    <p>Platform mediation decisions are final and binding. Users agree to comply with dispute resolution outcomes.</p>
                </div>

                <div class="terms-section">
                    <h2>12. Limitation of Liability</h2>
                    <p>RaketGo is not liable for:</p>
                    <ul>
                        <li>The quality of work performed by workers</li>
                        <li>Payment defaults by employers</li>
                        <li>Losses arising from user interactions</li>
                        <li>Third-party service interruptions</li>
                        <li>Consequential or punitive damages</li>
                    </ul>
                    <p>Our total liability shall not exceed the fees paid by the affected user in the preceding 12 months.</p>
                </div>

                <div class="terms-section">
                    <h2>13. Service Availability</h2>
                    <p>We strive to maintain high service availability but do not guarantee uninterrupted access. 
                    The platform may be temporarily unavailable for maintenance, updates, or technical issues.</p>
                </div>

                <div class="terms-section">
                    <h2>14. Term and Termination</h2>
                    <h3>14.1 Term</h3>
                    <p>These terms remain in effect as long as you use the RaketGo platform.</p>

                    <h3>14.2 Termination</h3>
                    <p>We may terminate your account for:</p>
                    <ul>
                        <li>Violation of these terms</li>
                        <li>Fraudulent or illegal activities</li>
                        <li>Extended account inactivity</li>
                        <li>Request for account closure</li>
                    </ul>
                </div>

                <div class="terms-section">
                    <h2>15. Changes to Terms</h2>
                    <p>We reserve the right to modify these terms at any time. Changes will be effective immediately upon posting. 
                    Continued use of the platform constitutes acceptance of modified terms.</p>
                </div>

                <div class="terms-section">
                    <h2>16. Contact Information</h2>
                    <p>For questions about these terms and conditions, please contact us:</p>
                    <ul>
                        <li><strong>Email:</strong> legal@raketgo.com</li>
                        <li><strong>Platform:</strong> Through our in-platform messaging system</li>
                        <li><strong>Address:</strong> Available upon request for legal matters</li>
                    </ul>
                </div>

                <div class="terms-section">
                    <h2>17. Governing Law</h2>
                    <p>These terms and conditions are governed by and construed in accordance with the laws of the Republic of the Philippines. 
                    Any disputes shall be resolved in accordance with Philippine law and regulations.</p>
                </div>

                <div class="terms-footer">
                    <p><strong>Important:</strong> By using RaketGo, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions.</p>
                    <p class="terms-date">Effective Date: May 1, 2026</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.terms-content {
    max-width: 800px;
    margin: 0 auto;
    font-family: 'Poppins', 'Noto Sans JP', sans-serif;
}

.terms-header {
    text-align: center;
    margin-bottom: 3rem;
    padding-bottom: 2rem;
    border-bottom: 2px solid var(--primary-blue-light);
}

.terms-header h1 {
    color: var(--primary-blue-dark);
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.terms-subtitle {
    color: var(--text-muted);
    font-size: 1rem;
    margin-bottom: 1.5rem;
}

.terms-intro {
    font-size: 1.1rem;
    line-height: 1.7;
    color: var(--text-dark);
    max-width: 600px;
    margin: 0 auto;
}

.terms-section {
    margin-bottom: 2.5rem;
    padding: 1.5rem;
    background: var(--off-white);
    border-radius: 8px;
    border-left: 4px solid var(--primary-blue);
}

.terms-section h2 {
    color: var(--primary-blue-dark);
    font-size: 1.4rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.terms-section h3 {
    color: var(--text-dark);
    font-size: 1.1rem;
    font-weight: 600;
    margin: 1.5rem 0 0.75rem 0;
}

.terms-section p {
    color: var(--text-dark);
    line-height: 1.6;
    margin-bottom: 1rem;
}

.terms-section ul {
    margin: 1rem 0;
    padding-left: 2rem;
}

.terms-section li {
    color: var(--text-dark);
    line-height: 1.6;
    margin-bottom: 0.5rem;
}

.terms-section strong {
    color: var(--primary-blue-dark);
    font-weight: 600;
}

.terms-footer {
    margin-top: 3rem;
    padding: 2rem;
    background: var(--primary-blue-light);
    border-radius: 8px;
    text-align: center;
}

.terms-footer p {
    margin-bottom: 0.5rem;
}

.terms-footer strong {
    color: var(--primary-blue-dark);
}

.terms-date {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin-top: 1rem;
}

@media (max-width: 768px) {
    .terms-content {
        padding: 0 1rem;
    }
    
    .terms-header h1 {
        font-size: 2rem;
    }
    
    .terms-section {
        padding: 1rem;
    }
    
    .terms-section h2 {
        font-size: 1.2rem;
    }
}
</style>

    </main>
    
    <footer class="footer">
        <div class="container">
            <div class="footer-main footer-main-compact">
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
                        <a href="login.php">Login</a>
                        <a href="signup.php">Sign Up</a>
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
                <a href="terms.php" style="color: inherit; text-decoration: none;">Terms & Conditions</a>
            </div>
        </div>
    </footer>

    <button id="back-to-top" class="back-to-top" type="button" aria-label="Back to top">
        <i class="fas fa-arrow-up"></i>
    </button>
    
    <script src="js/main.js"></script>
</body>
</html>
