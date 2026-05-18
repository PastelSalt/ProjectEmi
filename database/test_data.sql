-- =================================================================
-- RAKETGO + RAKETKO TEST DATA
-- Simple sample data for all web application functionalities
-- Use for testing and development purposes
-- =================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- =================================================================
-- SAMPLE USERS
-- =================================================================

-- Admin User
INSERT INTO users (user_id, mobile_number, email, password_hash, user_type, full_name, region, province, city, bio, trust_score, account_status, email_verified, mobile_verified) VALUES
(1, '09123456789', 'admin@raketgo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin User', 'Metro Manila', 'Manila', 'Manila', 'System administrator for RaketGo platform', 5.00, 'active', TRUE, TRUE);

-- Employer Users
INSERT INTO users (user_id, mobile_number, email, password_hash, user_type, employer_subtype, full_name, company_name, company_website, company_description, company_industry, company_size, region, province, city, bio, trust_score, account_status, email_verified, mobile_verified) VALUES
(2, '09123456780', 'techcorp@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employer', 'company', 'TechCorp Solutions', 'TechCorp Solutions', 'https://techcorp.com', 'Leading technology solutions provider specializing in web and mobile development', 'Technology', '50-100', 'Metro Manila', 'Makati', 'Makati', 'Innovative tech company looking for talented developers', 4.5, 'active', TRUE, TRUE),
(3, '09123456781', 'creativestudio@design.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employer', 'company', 'Creative Studio', 'Creative Studio PH', 'https://creativestudio.ph', 'Full-service creative agency offering design and marketing solutions', 'Design & Marketing', '10-25', 'Metro Manila', 'Quezon City', 'Quezon City', 'Creative agency seeking talented designers and marketers', 4.2, 'active', TRUE, TRUE),
(4, '09123456782', 'freelancebiz@work.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employer', 'individual', 'John Entrepreneur', 'John Freelance', '', 'Individual entrepreneur looking for skilled freelancers for various projects', 'Various', '1-10', 'Metro Manila', 'Pasig', 'Pasig', 'Freelance business owner seeking reliable partners', 3.8, 'active', TRUE, TRUE);

-- Worker Users
INSERT INTO users (user_id, mobile_number, email, password_hash, user_type, full_name, region, province, city, bio, trust_score, account_status, email_verified, mobile_verified) VALUES
(5, '09123456783', 'devmaria@worker.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'worker', 'Maria Developer', 'Metro Manila', 'Manila', 'Manila', 'Full-stack PHP developer with 5 years experience in Laravel and Vue.js', 4.3, 'active', TRUE, TRUE),
(6, '09123456784', 'designjames@worker.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'worker', 'James Designer', 'Metro Manila', 'Quezon City', 'Quezon City', 'UI/UX designer specializing in mobile app design and user research', 4.1, 'active', TRUE, TRUE),
(7, '09123456785', 'contentanna@worker.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'worker', 'Anna Writer', 'Metro Manila', 'Makati', 'Makati', 'Content writer and digital marketer with expertise in SEO and social media', 3.9, 'active', TRUE, TRUE),
(8, '09123456786', 'devcarlos@worker.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'worker', 'Carlos Developer', 'Metro Manila', 'Pasig', 'Pasig', 'Mobile app developer specializing in React Native and Flutter', 4.0, 'active', TRUE, TRUE),
(9, '09123456787', 'marketersarah@worker.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'worker', 'Sarah Marketer', 'Metro Manila', 'Manila', 'Manila', 'Digital marketing specialist with focus on social media and content strategy', 3.7, 'active', TRUE, TRUE),
(10, '09123456788', 'devroberto@worker.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'worker', 'Roberto Developer', 'Metro Manila', 'Quezon City', 'Quezon City', 'Backend developer specializing in Node.js and Python', 4.2, 'active', TRUE, TRUE);

-- =================================================================
-- SOCIAL PROFILES (for all users)
-- =================================================================

INSERT INTO social_profiles (user_id, bio, headline, location, skills, interests, job_seeking_status, current_job_title, work_preference, availability_status) VALUES
(1, 'Platform administrator helping Filipino professionals connect and grow', 'Platform Administrator', 'Manila, Philippines', '["Platform Management", "Community Building", "Technical Support"]', '["Technology", "Career Development", "Community"]', 'not_seeking', 'Platform Admin', 'remote', 'not_available'),
(2, 'Leading technology solutions provider seeking talented developers', 'CEO at TechCorp Solutions', 'Makati, Philippines', '["Business Strategy", "Technology Leadership", "Project Management"]', '["Innovation", "Team Building", "Digital Transformation"]', 'not_seeking', 'CEO', 'hybrid', 'not_available'),
(3, 'Creative agency owner passionate about design and marketing', 'Creative Director', 'Quezon City, Philippines', '["Creative Direction", "Design", "Marketing Strategy"]', '["Design Trends", "Art", "Branding"]', 'not_seeking', 'Creative Director', 'onsite', 'not_available'),
(4, 'Freelance business owner working with diverse clients', 'Freelance Entrepreneur', 'Pasig, Philippines', '["Project Management", "Business Development", "Client Relations"]', '["Entrepreneurship", "Freelancing", "Business Growth"]', 'not_seeking', 'Business Owner', 'remote', 'not_available'),
(5, 'Full-stack developer passionate about creating amazing web applications', 'Full-Stack PHP Developer', 'Manila, Philippines', '["PHP", "Laravel", "Vue.js", "MySQL", "API Design"]', '["Web Development", "Open Source", "Technology Trends"]', 'active', 'Full-Stack Developer', 'remote', 'immediately'),
(6, 'UI/UX designer focused on creating intuitive and beautiful interfaces', 'UI/UX Designer', 'Quezon City, Philippines', '["UI Design", "UX Research", "Figma", "Adobe XD", "Prototyping"]', '["Design Systems", "User Research", "Mobile Design"]', 'active', 'UI/UX Designer', 'hybrid', '2_weeks'),
(7, 'Content writer and digital marketer helping brands tell their stories', 'Content Writer & Digital Marketer', 'Makati, Philippines', '["Content Writing", "SEO", "Social Media Marketing", "Copywriting"]', '["Content Strategy", "Digital Marketing", "Storytelling"]', 'active', 'Content Writer', 'remote', 'immediately'),
(8, 'Mobile app developer creating cross-platform solutions', 'Mobile App Developer', 'Pasig, Philippines', '["React Native", "Flutter", "Mobile Development", "iOS", "Android"]', '["Mobile Technology", "App Development", "Cross-Platform"]', 'active', 'Mobile Developer', 'hybrid', '1_month'),
(9, 'Digital marketing specialist driving growth through social media', 'Digital Marketing Specialist', 'Manila, Philippines', '["Social Media Marketing", "Content Strategy", "Analytics", "Facebook Ads", "Google Ads"]', '["Marketing Trends", "Social Media", "Data Analytics"]', 'passive', 'Digital Marketer', 'remote', '3_months'),
(10, 'Backend developer building scalable server-side applications', 'Backend Developer', 'Quezon City, Philippines', '["Node.js", "Python", "API Development", "MongoDB", "PostgreSQL"]', '["Backend Development", "API Design", "Database Design"]', 'active', 'Backend Developer', 'remote', 'immediately');

-- Update users table with social profile IDs
UPDATE users SET social_profile_id = user_id WHERE user_id BETWEEN 1 AND 10;

-- =================================================================
-- USER SKILLS (RaketGo)
-- =================================================================

INSERT INTO user_skills (user_id, skill_name, proficiency_level, years_experience, verified) VALUES
(5, 'PHP', 'advanced', 5, TRUE),
(5, 'Laravel', 'advanced', 4, TRUE),
(5, 'Vue.js', 'intermediate', 3, FALSE),
(5, 'MySQL', 'advanced', 5, TRUE),
(6, 'UI Design', 'advanced', 4, TRUE),
(6, 'UX Research', 'intermediate', 3, FALSE),
(6, 'Figma', 'advanced', 3, TRUE),
(6, 'Adobe XD', 'intermediate', 2, FALSE),
(7, 'Content Writing', 'advanced', 4, TRUE),
(7, 'SEO', 'intermediate', 3, FALSE),
(7, 'Social Media Marketing', 'advanced', 3, TRUE),
(8, 'React Native', 'advanced', 3, TRUE),
(8, 'Flutter', 'intermediate', 2, FALSE),
(8, 'Mobile Development', 'advanced', 4, TRUE),
(9, 'Social Media Marketing', 'advanced', 3, TRUE),
(9, 'Facebook Ads', 'intermediate', 2, FALSE),
(9, 'Google Analytics', 'intermediate', 3, FALSE),
(10, 'Node.js', 'advanced', 4, TRUE),
(10, 'Python', 'intermediate', 3, FALSE),
(10, 'API Development', 'advanced', 3, TRUE);

-- =================================================================
-- UNIFIED SKILLS
-- =================================================================

INSERT INTO user_unified_skills (user_id, skill_id, proficiency_level, years_experience, source) VALUES
(5, 1, 'advanced', 5, 'raketgo'),
(5, 2, 'advanced', 4, 'raketgo'),
(6, 11, 'advanced', 4, 'raketgo'),
(7, 12, 'advanced', 4, 'raketgo'),
(8, 3, 'advanced', 3, 'raketgo'),
(9, 13, 'advanced', 3, 'raketgo'),
(10, 4, 'advanced', 4, 'raketgo');

-- =================================================================
-- JOB POSTS (RaketGo)
-- =================================================================

INSERT INTO job_posts (job_id, employer_id, job_title, job_description, job_requirements, job_category, job_type, pay_type, pay_amount, location_region, location_province, location_city, slots_available, experience_required, education_required, skills_required, job_status, views_count, applications_count) VALUES
(1, 2, 'Senior PHP Developer', 'We are looking for an experienced PHP developer to join our team. You will be working on large-scale web applications using Laravel framework.', '5+ years PHP experience, Laravel expertise, MySQL knowledge, RESTful API development', 'Technology', 'full_time', 'monthly', 45000.00, 'Metro Manila', 'Makati', 'Makati', 2, 'senior', 'bachelor', 'PHP, Laravel, MySQL, REST API', 'active', 156, 3),
(2, 2, 'Frontend Developer (Vue.js)', 'Join our frontend team to build amazing user interfaces using Vue.js. You will work closely with backend developers to create seamless user experiences.', '3+ years Vue.js experience, JavaScript expertise, CSS/SASS knowledge, responsive design', 'Technology', 'full_time', 'monthly', 40000.00, 'Metro Manila', 'Makati', 'Makati', 1, 'mid_level', 'bachelor', 'Vue.js, JavaScript, CSS, HTML', 'active', 98, 2),
(3, 3, 'UI/UX Designer', 'Creative agency looking for a talented UI/UX designer to create beautiful and functional designs for web and mobile applications.', '3+ years design experience, Figma proficiency, portfolio required, user research skills', 'Design', 'full_time', 'monthly', 35000.00, 'Metro Manila', 'Quezon City', 'Quezon City', 1, 'mid_level', 'bachelor', 'UI Design, UX Research, Figma, Adobe XD', 'active', 124, 2),
(4, 3, 'Content Writer', 'We need a creative content writer to produce engaging content for various clients including blog posts, social media, and marketing materials.', '2+ years writing experience, SEO knowledge, social media expertise, portfolio required', 'Marketing', 'full_time', 'monthly', 25000.00, 'Metro Manila', 'Quezon City', 'Quezon City', 1, 'junior', 'bachelor', 'Content Writing, SEO, Social Media, Copywriting', 'active', 87, 1),
(5, 4, 'Mobile App Developer', 'Freelance project requiring mobile app development for both iOS and Android platforms using React Native.', '2+ years React Native experience, mobile development knowledge, portfolio required', 'Technology', 'freelance', 'fixed', 50000.00, 'Metro Manila', 'Pasig', 'Pasig', 1, 'mid_level', 'bachelor', 'React Native, Mobile Development, iOS, Android', 'active', 67, 1),
(6, 4, 'Digital Marketing Specialist', 'Looking for a digital marketing specialist to manage social media campaigns and content strategy for multiple clients.', '2+ years digital marketing experience, social media expertise, analytics knowledge', 'Marketing', 'part_time', 'monthly', 20000.00, 'Metro Manila', 'Pasig', 'Pasig', 1, 'junior', 'bachelor', 'Digital Marketing, Social Media, Analytics, Content Strategy', 'active', 45, 1);

-- =================================================================
-- JOB APPLICATIONS
-- =================================================================

INSERT INTO job_applications (job_id, worker_id, employer_id, application_status, cover_letter, proposed_rate, availability_date) VALUES
(1, 5, 2, 'pending', 'I am very interested in this Senior PHP Developer position. With 5 years of experience in PHP and Laravel, I believe I have the skills you are looking for. I have worked on several large-scale projects and am confident in my ability to contribute to your team.', 45000.00, '2024-06-01'),
(1, 10, 2, 'pending', 'As a backend developer with strong PHP skills, I am excited about this opportunity. I have experience with Laravel and have built several RESTful APIs. I am available immediately and can start within 2 weeks.', 43000.00, '2024-05-20'),
(2, 5, 2, 'approved', 'I have strong experience with Vue.js and would love to join your frontend team. I have worked on several Vue.js projects and am confident in my ability to create great user interfaces.', 40000.00, '2024-06-01'),
(2, 8, 2, 'pending', 'As a mobile developer with frontend experience, I am interested in this Vue.js position. I have some experience with JavaScript and am eager to learn more about Vue.js.', 38000.00, '2024-06-15'),
(3, 6, 3, 'approved', 'I am excited about this UI/UX Designer position. I have 4 years of experience in UI design and am proficient in Figma. I have a strong portfolio and am passionate about creating great user experiences.', 35000.00, '2024-06-01'),
(4, 7, 3, 'pending', 'As a content writer with experience in SEO and social media, I am interested in this position. I have worked on various writing projects and can produce engaging content for different platforms.', 25000.00, '2024-06-01'),
(5, 8, 4, 'approved', 'I am perfect for this mobile app development project. I have 3 years of experience with React Native and have developed several cross-platform applications. I can start immediately.', 50000.00, '2024-05-15'),
(6, 9, 4, 'pending', 'As a digital marketing specialist with social media expertise, I am interested in this position. I have experience managing campaigns for various clients and can help grow your clients online presence.', 20000.00, '2024-06-01');

-- Update job posts application counts
UPDATE job_posts SET applications_count = 2 WHERE job_id = 1;
UPDATE job_posts SET applications_count = 2 WHERE job_id = 2;
UPDATE job_posts SET applications_count = 1 WHERE job_id = 3;
UPDATE job_posts SET applications_count = 1 WHERE job_id = 4;
UPDATE job_posts SET applications_count = 1 WHERE job_id = 5;
UPDATE job_posts SET applications_count = 1 WHERE job_id = 6;

-- =================================================================
-- SOCIAL POSTS (RaketKo)
-- =================================================================

INSERT INTO social_posts (post_id, user_id, post_type, title, content, hashtags, mentions, visibility, likes_count, comments_count, shares_count, views_count) VALUES
(1, 5, 'career_update', 'Excited to share my career journey!', 'Just completed 5 years as a full-stack PHP developer! 🎉 It\'s been an amazing journey of growth and learning. From my first PHP script to building large-scale applications with Laravel. Grateful for all the opportunities and challenges that shaped me as a developer. #CareerGrowth #PHP #WebDevelopment #DeveloperLife', '["CareerGrowth", "PHP", "WebDevelopment", "DeveloperLife"]', '[]', 'public', 12, 3, 2, 89),
(2, 6, 'insight', 'The Power of User-Centered Design', 'In today\'s digital world, user-centered design is not just a buzzword—it\'s essential. Here are 3 principles I always follow: 1. Start with user research 2. Test early and often 3. Iterate based on feedback Remember: great design solves real problems! #Design #UX #UserResearch #DesignThinking', '["Design", "UX", "UserResearch", "DesignThinking"]', '[]', 'public', 8, 2, 1, 67),
(3, 7, 'professional_tip', 'Content Marketing Tips for 2024', 'Content is king, but distribution is queen! Here are my top content marketing tips: • Focus on quality over quantity • Repurpose content across platforms • Use data to guide your strategy • Build authentic connections • Measure what matters What are your content marketing strategies? #ContentMarketing #DigitalMarketing #Strategy', '["ContentMarketing", "DigitalMarketing", "Strategy"]', '[]', 'public', 15, 4, 3, 123),
(4, 8, 'achievement', 'Launched My First React Native App!', 'Thrilled to announce that I\'ve successfully launched my first React Native app on both iOS and Android! 🚀 It\'s a fitness tracking app that helps users maintain their workout routines. This project taught me so much about cross-platform development and app store deployment. #MobileDev #ReactNative #AppLaunch #Developer', '["MobileDev", "ReactNative", "AppLaunch", "Developer"]', '[]', 'public', 20, 5, 4, 156),
(5, 9, 'industry_news', 'Social Media Trends to Watch in 2024', 'The social media landscape is constantly evolving! Here are the trends I\'m watching: • Short-form video dominance • AI-powered content creation • Authentic storytelling • Community-focused platforms • Social commerce integration What trends are you seeing? #SocialMedia #MarketingTrends #DigitalMarketing #2024Trends', '["SocialMedia", "MarketingTrends", "DigitalMarketing", "2024Trends"]', '[]', 'public', 10, 2, 2, 78),
(6, 10, 'question', 'Best Backend Framework for Startups?', 'Fellow developers, I need your advice! What\'s the best backend framework for a startup? I\'m considering Node.js vs Python vs PHP. What are your experiences with these? What factors should I consider when choosing? #Backend #Startups #WebDevelopment #DeveloperCommunity', '["Backend", "Startups", "WebDevelopment", "DeveloperCommunity"]', '[]', 'public', 6, 8, 1, 45),
(7, 2, 'company_news', 'TechCorp is Hiring! Join Our Team! 🚀', 'We\'re expanding our team and looking for talented developers to join us! Currently hiring: • Senior PHP Developer • Frontend Developer (Vue.js) • UI/UX Designer We offer competitive salaries, great work culture, and opportunities for growth. Apply now! #Hiring #TechJobs #CareerOpportunities #JoinUs', '["Hiring", "TechJobs", "CareerOpportunities", "JoinUs"]', '[]', 'public', 25, 6, 8, 234),
(8, 3, 'professional_tip', 'Design Systems: Why They Matter', 'Design systems are not just for large companies! Here\'s why every designer should consider building one: • Consistency across products • Faster development • Better collaboration • Scalable design • Improved user experience Start small, think big! #DesignSystems #UIDesign #UXDesign #ProductDesign', '["DesignSystems", "UIDesign", "UXDesign", "ProductDesign"]', '[]', 'public', 14, 3, 2, 98),
(9, 5, 'job_posting', 'Looking for Laravel Projects!', 'Hello everyone! I\'m currently available for Laravel projects. I have 5 years of experience building web applications using Laravel, Vue.js, and MySQL. If you have any projects or know someone who needs a reliable PHP developer, feel free to reach out! #Laravel #PHP #WebDevelopment #Freelance', '["Laravel", "PHP", "WebDevelopment", "Freelance"]', '[]', 'public', 8, 1, 1, 56),
(10, 6, 'achievement', 'Completed UX Design Certification! 🎓', 'Just completed my UX Design certification! 🎉 This 6-month journey deepened my understanding of user research, usability testing, and design thinking. Excited to apply these new skills to create even better user experiences. #Certification #UXDesign #ProfessionalDevelopment #Learning', '["Certification", "UXDesign", "ProfessionalDevelopment", "Learning"]', '[]', 'public', 18, 4, 3, 145);

-- =================================================================
-- SOCIAL POST LIKES
-- =================================================================

INSERT INTO social_post_likes (post_id, user_id) VALUES
(1, 2), (1, 3), (1, 6), (1, 7), (1, 8), (1, 9), (1, 10),
(2, 1), (2, 3), (2, 5), (2, 7), (2, 8),
(3, 1), (3, 2), (3, 5), (3, 6), (3, 8), (3, 9), (3, 10),
(4, 1), (4, 2), (4, 3), (4, 5), (4, 6), (4, 7), (4, 9), (4, 10),
(5, 1), (5, 2), (5, 3), (5, 5), (5, 6), (5, 7), (5, 8), (5, 10),
(6, 1), (6, 2), (6, 3), (6, 5), (6, 7), (6, 8),
(7, 1), (7, 3), (7, 4), (7, 5), (7, 6), (7, 8), (7, 9), (7, 10),
(8, 1), (8, 2), (8, 5), (8, 6), (8, 7), (8, 9), (8, 10),
(9, 2), (9, 3), (9, 4), (9, 6), (9, 7), (9, 8), (9, 10),
(10, 1), (10, 2), (10, 3), (10, 4), (10, 5), (10, 6), (10, 7), (10, 8);

-- =================================================================
-- SOCIAL POST COMMENTS
-- =================================================================

INSERT INTO social_post_comments (post_id, user_id, content, likes_count) VALUES
(1, 6, 'Congratulations on your 5-year milestone! 🎉 Your journey is truly inspiring!', 2),
(1, 7, 'Amazing achievement! Keep up the great work!', 1),
(1, 8, '5 years is a significant milestone. Cheers to many more!', 1),
(2, 5, 'Great insights! User-centered design is indeed crucial for success.', 1),
(2, 7, 'I totally agree with your principles. Testing early is so important!', 1),
(3, 5, 'Excellent tips! Quality over quantity is so important in content marketing.', 1),
(3, 9, 'Great advice! I especially agree with measuring what matters.', 1),
(4, 5, 'Congratulations on your app launch! That\'s a huge achievement! 🚀', 2),
(4, 6, 'Amazing work! The app looks great. Would love to try it!', 1),
(5, 7, 'Great analysis of social media trends! AI in content is definitely something to watch.', 1),
(6, 5, 'For startups, I\'d recommend Node.js for its scalability and ecosystem.', 1),
(6, 8, 'Node.js has been great for my projects. The community support is excellent.', 1),
(6, 10, 'I\'ve worked with all three. Each has its strengths depending on your use case.', 1),
(7, 5, 'Excited to see these opportunities! I\'ll be applying for the PHP position.', 1),
(7, 8, 'Great company culture! I\'d love to work with your team.', 1),
(8, 6, 'This is so helpful! I\'m currently building my first design system.', 1),
(8, 5, 'Design systems have transformed how we work. Great article!', 1),
(9, 2, 'We might have a Laravel project coming up. I\'ll reach out!', 1),
(10, 6, 'Congratulations on your certification! 🎓 That\'s fantastic!', 1);

-- =================================================================
-- SOCIAL POST SHARES
-- =================================================================

INSERT INTO social_post_shares (post_id, user_id, share_type, share_comment) VALUES
(1, 2, 'share_to_connections', 'Inspiring career journey worth sharing!'),
(1, 3, 'repost', 'Great example of career development!'),
(3, 5, 'share_to_connections', 'Excellent content marketing tips!'),
(4, 2, 'repost', 'Amazing app launch! Congratulations!'),
(5, 7, 'share_to_connections', 'Important social media trends for marketers'),
(7, 5, 'repost', 'Great job opportunities at TechCorp!'),
(8, 6, 'share_to_connections', 'Valuable insights on design systems'),
(10, 2, 'repost', 'Congratulations on the certification!');

-- =================================================================
-- SOCIAL CONNECTIONS (Follows)
-- =================================================================

INSERT INTO social_connections (follower_id, following_id, connection_type, status) VALUES
(5, 6, 'follow', 'accepted'),
(5, 7, 'follow', 'accepted'),
(5, 8, 'follow', 'accepted'),
(5, 9, 'follow', 'accepted'),
(6, 5, 'follow', 'accepted'),
(6, 7, 'follow', 'accepted'),
(6, 8, 'follow', 'accepted'),
(7, 5, 'follow', 'accepted'),
(7, 6, 'follow', 'accepted'),
(7, 9, 'follow', 'accepted'),
(8, 5, 'follow', 'accepted'),
(8, 6, 'follow', 'accepted'),
(8, 7, 'follow', 'accepted'),
(9, 5, 'follow', 'accepted'),
(9, 6, 'follow', 'accepted'),
(9, 7, 'follow', 'accepted'),
(10, 5, 'follow', 'accepted'),
(10, 6, 'follow', 'accepted'),
(10, 8, 'follow', 'accepted'),
(1, 2, 'follow', 'accepted'),
(1, 3, 'follow', 'accepted'),
(2, 1, 'follow', 'accepted'),
(3, 1, 'follow', 'accepted');

-- Update follower/following counts in social_profiles
UPDATE social_profiles SET followers_count = 4, following_count = 4 WHERE user_id = 5;
UPDATE social_profiles SET followers_count = 4, following_count = 3 WHERE user_id = 6;
UPDATE social_profiles SET followers_count = 4, following_count = 3 WHERE user_id = 7;
UPDATE social_profiles SET followers_count = 4, following_count = 3 WHERE user_id = 8;
UPDATE social_profiles SET followers_count = 3, following_count = 3 WHERE user_id = 9;
UPDATE social_profiles SET followers_count = 3, following_count = 3 WHERE user_id = 10;
UPDATE social_profiles SET followers_count = 2, following_count = 1 WHERE user_id = 1;
UPDATE social_profiles SET followers_count = 1, following_count = 1 WHERE user_id = 2;
UPDATE social_profiles SET followers_count = 1, following_count = 1 WHERE user_id = 3;

-- =================================================================
-- MESSAGES
-- =================================================================

INSERT INTO messages (sender_id, receiver_id, job_id, subject, content, is_read) VALUES
(2, 5, 1, 'Regarding your PHP Developer application', 'Thank you for your interest in our Senior PHP Developer position. We would like to schedule an interview with you. Are you available this week?', FALSE),
(5, 2, 1, 'Re: Regarding your PHP Developer application', 'Thank you for reaching out! I\'m available for an interview this week. Please let me know what time works best for you.', TRUE),
(3, 6, 3, 'UI/UX Designer Position', 'Hi! We reviewed your portfolio and we\'re impressed. Would you be interested in discussing the UI/UX Designer position further?', FALSE),
(6, 3, 3, 'Re: UI/UX Designer Position', 'Thank you! I\'m definitely interested in discussing the position. I\'m available for a call tomorrow afternoon.', TRUE),
(1, 5, NULL, 'Welcome to RaketGo!', 'Welcome to our platform! If you have any questions or need assistance, feel free to reach out. We\'re here to help you succeed.', TRUE),
(1, 2, NULL, 'Platform Update', 'Hi! We\'ve recently added new features to improve your experience. Check out the unified dashboard for better insights.', TRUE);

-- =================================================================
-- NOTIFICATIONS (RaketGo)
-- =================================================================

INSERT INTO notifications (user_id, type, actor_id, target_id, target_type, title, message, action_url, is_read) VALUES
(5, 'new_application', 2, 1, 'job_post', 'Application Received', 'Your application for Senior PHP Developer has been received by TechCorp Solutions.', 'job-details.php?id=1', FALSE),
(6, 'application_status', 3, 3, 'application', 'Application Approved', 'Congratulations! Your application for UI/UX Designer has been approved.', 'job-details.php?id=3', FALSE),
(8, 'application_status', 4, 5, 'application', 'Application Approved', 'Great news! Your application for Mobile App Developer has been approved.', 'job-details.php?id=5', FALSE),
(2, 'new_application', 5, 1, 'application', 'New Application Received', 'Maria Developer has applied for your Senior PHP Developer position.', 'applications.php', FALSE),
(3, 'new_application', 6, 3, 'application', 'New Application Received', 'James Designer has applied for your UI/UX Designer position.', 'applications.php', FALSE),
(4, 'new_application', 7, 4, 'application', 'New Application Received', 'Anna Writer has applied for your Content Writer position.', 'applications.php', FALSE);

-- =================================================================
-- SOCIAL NOTIFICATIONS (RaketKo)
-- =================================================================

INSERT INTO social_notifications (user_id, type, actor_id, target_id, target_type, message, is_read) VALUES
(5, 'like', 6, 1, 'post', 'James Designer liked your post "Excited to share my career journey!"', FALSE),
(5, 'comment', 6, 1, 'post', 'James Designer commented on your post "Excited to share my career journey!"', FALSE),
(5, 'follow', 6, 5, 'user', 'James Designer started following you', FALSE),
(6, 'like', 5, 2, 'post', 'Maria Developer liked your post "The Power of User-Centered Design"', FALSE),
(7, 'like', 5, 3, 'post', 'Maria Developer liked your post "Content Marketing Tips for 2024"', FALSE),
(8, 'like', 5, 4, 'post', 'Maria Developer liked your post "Launched My First React Native App!"', FALSE),
(2, 'like', 5, 7, 'post', 'Maria Developer liked your post "TechCorp is Hiring! Join Our Team! 🚀"', FALSE);

-- =================================================================
-- CROSS-PLATFORM ACTIVITIES
-- =================================================================

INSERT INTO cross_platform_activities (user_id, platform, activity_type, target_id, target_type, activity_data) VALUES
(5, 'raketgo', 'job_application', 1, 'job_post', '{"application_id": 1, "status": "pending"}'),
(5, 'raketko', 'social_post', 1, 'social_post', '{"post_type": "career_update", "visibility": "public"}'),
(6, 'raketgo', 'job_application', 3, 'job_post', '{"application_id": 3, "status": "approved"}'),
(6, 'raketko', 'social_post', 2, 'social_post', '{"post_type": "insight", "visibility": "public"}'),
(7, 'raketgo', 'job_application', 4, 'job_post', '{"application_id": 4, "status": "pending"}'),
(7, 'raketko', 'social_post', 3, 'social_post', '{"post_type": "professional_tip", "visibility": "public"}'),
(8, 'raketgo', 'job_application', 5, 'job_post', '{"application_id": 5, "status": "approved"}'),
(8, 'raketko', 'social_post', 4, 'social_post', '{"post_type": "achievement", "visibility": "public"}'),
(2, 'raketgo', 'job_post', 1, 'job_post', '{"job_title": "Senior PHP Developer", "status": "active"}'),
(2, 'raketko', 'social_post', 7, 'social_post', '{"post_type": "company_news", "visibility": "public"}');

-- =================================================================
-- UNIFIED NOTIFICATIONS
-- =================================================================

INSERT INTO unified_notifications (user_id, platform, type, actor_id, target_id, target_type, title, message, action_url, priority, is_read) VALUES
(5, 'raketgo', 'job_application', 2, 1, 'job_post', 'Application Received', 'Your application for Senior PHP Developer has been received', 'job-details.php?id=1', 'medium', FALSE),
(5, 'raketko', 'social_like', 6, 1, 'social_post', 'New Like', 'James Designer liked your career update post', 'raketko-feed.php', 'low', FALSE),
(5, 'raketko', 'social_follow', 6, 5, 'user_profile', 'New Follower', 'James Designer started following you', 'raketko-profile.php?id=6', 'low', FALSE),
(6, 'raketgo', 'job_application', 3, 3, 'job_post', 'Application Approved', 'Your UI/UX Designer application was approved', 'job-details.php?id=3', 'high', FALSE),
(6, 'raketko', 'social_like', 5, 2, 'social_post', 'New Like', 'Maria Developer liked your design insight post', 'raketko-feed.php', 'low', FALSE),
(2, 'raketgo', 'job_application', 5, 1, 'application', 'New Application', 'Maria Developer applied for your PHP position', 'applications.php', 'medium', FALSE);

-- =================================================================
-- CAREER MILESTONES
-- =================================================================

INSERT INTO career_milestones (user_id, milestone_type, title, description, related_job_id, related_social_post_id, milestone_date, is_public) VALUES
(5, 'job_start', 'Started Career as Developer', 'Began professional journey as a PHP developer', NULL, NULL, '2019-01-15', TRUE),
(5, 'skill_acquired', 'Mastered Laravel Framework', 'Achieved advanced proficiency in Laravel framework', NULL, NULL, '2020-06-01', TRUE),
(5, 'social_achievement', '5 Years Experience Milestone', 'Celebrated 5 years as a full-stack developer', NULL, 1, '2024-01-15', TRUE),
(6, 'certification_earned', 'UX Design Certification', 'Completed professional UX design certification program', NULL, 10, '2024-03-15', TRUE),
(8, 'portfolio_added', 'First Mobile App Launched', 'Successfully launched first React Native application', NULL, 4, '2024-02-20', TRUE),
(7, 'skill_acquired', 'SEO Expertise', 'Achieved advanced skills in search engine optimization', NULL, NULL, '2023-09-01', TRUE);

-- =================================================================
-- TRENDING TOPICS (Additional ones not in schema)
-- =================================================================

INSERT INTO trending_topics (hashtag, display_name, description, category, usage_count, is_trending) VALUES
('#DeveloperLife', 'Developer Life', 'Daily life and experiences of developers', 'technology', 8, TRUE),
('#DesignThinking', 'Design Thinking', 'Design methodology and problem-solving', 'design', 6, FALSE),
('#MarketingTrends', 'Marketing Trends', 'Latest trends in digital marketing', 'marketing', 7, FALSE),
('#2024Trends', '2024 Trends', 'Trending topics for 2024', 'general', 9, TRUE),
('#JoinUs', 'Join Us', 'Company hiring and recruitment', 'career', 11, TRUE);

-- =================================================================
-- POST HASHTAG RELATIONSHIPS
-- =================================================================

INSERT INTO social_post_hashtags (post_id, hashtag) VALUES
(1, '#CareerGrowth'), (1, '#PHP'), (1, '#WebDevelopment'), (1, '#DeveloperLife'),
(2, '#Design'), (2, '#UX'), (2, '#UserResearch'), (2, '#DesignThinking'),
(3, '#ContentMarketing'), (3, '#DigitalMarketing'), (3, '#Strategy'),
(4, '#MobileDev'), (4, '#ReactNative'), (4, '#AppLaunch'), (4, '#Developer'),
(5, '#SocialMedia'), (5, '#MarketingTrends'), (5, '#DigitalMarketing'), (5, '#2024Trends'),
(6, '#Backend'), (6, '#Startups'), (6, '#WebDevelopment'), (6, '#DeveloperCommunity'),
(7, '#Hiring'), (7, '#TechJobs'), (7, '#CareerOpportunities'), (7, '#JoinUs'),
(8, '#DesignSystems'), (8, '#UIDesign'), (8, '#UXDesign'), (8, '#ProductDesign'),
(9, '#Laravel'), (9, '#PHP'), (9, '#WebDevelopment'), (9, '#Freelance'),
(10, '#Certification'), (10, '#UXDesign'), (10, '#ProfessionalDevelopment'), (10, '#Learning');

-- =================================================================
-- UNIFIED USER ANALYTICS (Using default data from schema)
-- =================================================================

-- =================================================================
-- SAMPLE SKILL POSTS (Learning Hub)
-- =================================================================

INSERT INTO skill_posts (admin_id, post_title, post_content, post_type, link_url, category, tags, likes_count, views_count, is_featured) VALUES
(1, 'Advanced Laravel Techniques', 'Learn advanced Laravel techniques including Eloquent relationships, API development, and performance optimization. Perfect for developers looking to level up their Laravel skills.', 'course', 'https://laravel.com/docs', 'Technology', 'Laravel, PHP, Web Development, API', 45, 234, TRUE),
(1, 'UI/UX Design Fundamentals', 'Master the fundamentals of UI/UX design including user research, wireframing, prototyping, and usability testing. Essential skills for modern designers.', 'training', 'https://www.coursera.org/learn/ux-design-fundamentals', 'Design', 'UI Design, UX Research, Prototyping', 38, 189, TRUE),
(1, 'Digital Marketing Certification', 'Get certified in digital marketing with comprehensive training in SEO, social media marketing, content strategy, and analytics.', 'certification', 'https://digitalmarketinginstitute.com/', 'Marketing', 'Digital Marketing, SEO, Social Media, Analytics', 52, 312, TRUE);

-- =================================================================
-- SAMPLE WORKER PORTFOLIO (Diverse Work Types)
-- =================================================================

INSERT INTO worker_portfolio (worker_id, title, description, work_type, project_url, image_path, site_photos, client_company, job_location, work_duration, completion_date, tools_equipment, certifications, work_category, team_size, supervisor_name, skills_used, is_featured, views_count) VALUES
(5, 'E-commerce Platform', 'Built a full-featured e-commerce platform using Laravel and Vue.js with payment integration, inventory management, and admin dashboard.', 'project', 'https://github.com/mariadev/ecommerce', 'uploads/ecommerce.jpg', '["uploads/ecommerce-1.jpg", "uploads/ecommerce-2.jpg"]', 'TechCorp Solutions', 'Makati City', '3 months', '2024-03-15', '["PHPStorm", "Vue CLI", "MySQL Workbench"]', '["AWS Certified Developer"]', 'Web Development', 2, 'John TechLead', '["PHP", "Laravel", "Vue.js", "MySQL", "Payment Gateway"]', TRUE, 89),
(6, 'Mobile Banking App Design', 'Designed a complete mobile banking application with focus on security, usability, and accessibility. Includes user research and testing.', 'project', 'https://behance.net/jamesdesign/banking-app', 'uploads/banking-app.jpg', '["uploads/banking-mockup.jpg", "uploads/banking-ui.jpg"]', 'Creative Studio PH', 'Quezon City', '2 months', '2024-04-20', '["Figma", "Adobe XD", "UserTesting"]', '["Google UX Certificate"]', 'UI/UX Design', 1, 'Sarah Creative', '["UI Design", "UX Research", "Figma", "Mobile Design", "Security"]', TRUE, 67),
(7, 'Content Marketing Strategy', 'Developed and executed comprehensive content marketing strategy for tech startup, resulting in 300% increase in organic traffic.', 'project', 'https://portfolio.annawriter.com/content-strategy', 'uploads/content-strategy.jpg', '["uploads/content-analytics.jpg"]', 'TechStartup Inc', 'Makati City', '6 months', '2024-02-10', '["Google Analytics", "SEMrush", "Buffer"]', '["HubSpot Certification"]', 'Digital Marketing', 1, 'Mike Marketing', '["Content Writing", "SEO", "Social Media", "Analytics"]', TRUE, 45),
(8, 'Fitness Tracking App', 'Cross-platform mobile app for fitness tracking with social features, workout plans, and progress analytics. Built with React Native.', 'project', 'https://apps.apple.com/fitness-tracker', 'uploads/fitness-app.jpg', '["uploads/fitness-screens.jpg", "uploads/fitness-analytics.jpg"]', 'HealthTech Solutions', 'Pasig City', '4 months', '2024-01-25', '["React Native CLI", "Firebase", "Xcode"]', '["React Native Certification"]', 'Mobile Development', 2, 'Lisa DevLead', '["React Native", "Mobile Development", "Firebase", "Analytics"]', TRUE, 123),
(9, 'Residential Construction Project', 'Led the construction of a 2-story residential building from foundation to finishing. Coordinated with architects, suppliers, and subcontractors.', 'construction', NULL, 'uploads/construction-site.jpg', '["uploads/construction-before.jpg", "uploads/construction-during.jpg", "uploads/construction-after.jpg"]', 'Mendoza Construction', 'Quezon City', '8 months', '2024-03-30', '["Concrete Mixer", "Scaffolding", "Power Tools", "Measuring Equipment"]', '["Construction Safety Certification", "Building Permit"]', 'Residential Construction', 8, 'Engr. Reyes', '["Project Management", "Construction Planning", "Team Leadership", "Quality Control"]', TRUE, 95),
(10, 'Industrial Welding Work', 'Performed precision welding for industrial equipment including pressure vessels and structural steel components. Met all safety and quality standards.', 'welding', NULL, 'uploads/welding-work.jpg', '["uploads/welding-equipment.jpg", "uploads/welding-finished.jpg"]', 'SteelFab Industries', 'Mandaluyong', '3 weeks', '2024-04-15', '["TIG Welder", "MIG Welder", "Grinder", "Measuring Tools"]', '["AWS Welding Certification", "Safety Training"]', 'Industrial Welding', 3, 'Foreman Santos', '["TIG Welding", "MIG Welding", "Metal Fabrication", "Safety Protocols"]', TRUE, 78),
(8, 'Automotive Repair Service', 'Complete engine overhaul and transmission repair for heavy-duty trucks. Diagnosed complex mechanical issues and restored vehicles to operational condition.', 'repair', NULL, 'uploads/automotive-repair.jpg', '["uploads/automotive-engine.jpg", "uploads/automotive-transmission.jpg"]', 'FleetMaster Logistics', 'Pasay City', '2 weeks', '2024-02-20', '["Diagnostic Tools", "Hydraulic Lift", "Wrench Set", "Welding Equipment"]', '["Automotive Service Excellence", "Diesel Engine Certification"]', 'Automotive Repair', 2, 'Mechanic Cruz', '["Engine Repair", "Transmission Service", "Diagnostics", "Heavy Equipment"]', FALSE, 52),
(9, 'Electrical Installation', 'Complete electrical system installation for commercial building including wiring, panel setup, and safety inspections. All work passed municipal inspection.', 'installation', NULL, 'uploads/electrical-work.jpg', '["uploads/electrical-panel.jpg", "uploads/electrical-wiring.jpg"]', 'PowerGrid Electric', 'Makati City', '1 month', '2024-01-15', '["Multimeter", "Wire Strippers", "Conduit Bender", "Safety Equipment"]', '["Electrical License", "Safety Certification"]', 'Electrical Installation', 4, 'Supervisor Diaz', '["Electrical Installation", "Safety Compliance", "Blueprint Reading", "Troubleshooting"]', FALSE, 61);

-- =================================================================
-- SAMPLE JOB RATINGS
-- =================================================================

INSERT INTO job_ratings (job_id, rater_id, rated_id, rating_type, rating_stars, feedback, would_work_again, communication_rating, quality_rating, professionalism_rating) VALUES
(1, 2, 5, 'employer_to_worker', 5, 'Excellent work! Maria delivered high-quality code and was very professional throughout the project.', TRUE, 5, 5, 5),
(3, 3, 6, 'employer_to_worker', 4, 'James did great work on our UI/UX design project. Very creative and responsive to feedback.', TRUE, 4, 4, 4),
(5, 4, 8, 'employer_to_worker', 5, 'Carlos exceeded expectations with the mobile app. Delivered on time and the quality was outstanding.', TRUE, 5, 5, 5);

-- =================================================================
-- SAMPLE TRUST SCORE UPDATES
-- =================================================================

INSERT INTO trust_score_updates (user_id, old_score, new_score, score_change, update_reason, related_rating_id, updated_by) VALUES
(5, 4.00, 4.30, 0.30, 'Positive job rating received', 1, 1),
(6, 3.80, 4.10, 0.30, 'Positive job rating received', 2, 1),
(8, 3.70, 4.00, 0.30, 'Positive job rating received', 3, 1);

-- Update trust scores in users table
UPDATE users SET trust_score = 4.30 WHERE user_id = 5;
UPDATE users SET trust_score = 4.10 WHERE user_id = 6;
UPDATE users SET trust_score = 4.00 WHERE user_id = 8;

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Test data insertion completed successfully!' as status,
       'Sample data for all web application functionalities has been created.' as details;
