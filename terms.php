<?php
/**
 * Terms and Conditions Page
 * 
 * TODO: Add CSS styling later - currently using platform default styles
 * The terms page had animation issues, so CSS has been removed for now
 * Add custom styling in css/style.css under "Terms and Conditions Page Styling" section
 */
$page_title = 'Terms and Conditions';
define('BASE_PATH', __DIR__ . '/');
require_once 'config/config.php';
require_once 'includes/header.php';
?>

<div class="container">
    <div class="panel">
        <div class="section-header">
            <span class="header-square"></span>
            TERMS AND CONDITIONS
        </div>
        <div class="panel-body">
            <h1>RaketGo Terms and Conditions</h1>
            <p><strong>Last updated: May 2026</strong></p>
            <p>Welcome to RaketGo, a localized job matching platform connecting employers and workers in the Philippines. 
            By accessing or using our platform, you agree to be bound by these Terms and Conditions.</p>
            
            <h2>1. Acceptance of Terms</h2>
            <p>By accessing and using RaketGo, you accept and agree to be bound by the terms and provision of this agreement. 
            If you do not agree to abide by the above, please do not use this service.</p>

            <h2>2. Platform Description</h2>
            <p>RaketGo is a job matching platform that connects verified employers with job seekers in the Philippines. 
            Our platform facilitates job posting and application management, worker portfolio and skill verification, 
            in-platform messaging and communication, digital contract generation, payment processing and escrow services, 
            and trust scoring and rating system.</p>

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

            <h2>7. Trust Score and Rating System</h2>
            <p>RaketGo maintains a trust scoring system based on:</p>
            <ul>
                <li>Completed jobs and successful transactions</li>
                <li>Peer ratings and reviews</li>
                <li>Profile completeness and verification</li>
                <li>Platform activity and engagement</li>
            </ul>
            <p>Trust scores help users make informed decisions but do not guarantee performance or reliability.</p>

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

            <h2>9. Intellectual Property</h2>
            <h3>9.1 Platform Content</h3>
            <p>RaketGo and its content, features, and functionality are owned by Moesoft (Moeko Software) and are protected by copyright, trademark, and other intellectual property laws.</p>

            <h3>9.2 User Content</h3>
            <p>Users retain ownership of content they upload to the platform but grant RaketGo a license to use, display, and distribute such content for platform operations.</p>

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

            <h2>13. Service Availability</h2>
            <p>We strive to maintain high service availability but do not guarantee uninterrupted access. 
            The platform may be temporarily unavailable for maintenance, updates, or technical issues.</p>

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

            <h2>15. Changes to Terms</h2>
            <p>We reserve the right to modify these terms at any time. Changes will be effective immediately upon posting. 
            Continued use of the platform constitutes acceptance of modified terms.</p>

            <h2>16. Contact Information</h2>
            <p>For questions about these terms and conditions, please contact us:</p>
            <ul>
                <li><strong>Email:</strong> legal@raketgo.com</li>
                <li><strong>Platform:</strong> Through our in-platform messaging system</li>
                <li><strong>Address:</strong> Available upon request for legal matters</li>
            </ul>

            <h2>17. Governing Law</h2>
            <p>These terms and conditions are governed by and construed in accordance with the laws of the Republic of the Philippines. 
            Any disputes shall be resolved in accordance with Philippine law and regulations.</p>

            <p><strong>Important:</strong> By using RaketGo, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions.</p>
            <p><strong>Effective Date: May 1, 2026</strong></p>
        </div>
    </div>
</div>



<?php require_once 'includes/footer.php'; ?>
