-- RaketGo Sample Data Seed
-- Import after database/schema.sql
--
-- Admin test credential:
-- Mobile Number: 09560618349
-- Password: matsuzakasatou
--
-- All non-admin test accounts:
-- Password: password
--
-- NOTE: This script resets core tables before inserting realistic sample data.

DELETE FROM digital_contracts;
DELETE FROM transactions;
DELETE FROM user_interactions;
DELETE FROM notifications;
DELETE FROM messages;
DELETE FROM skill_posts;
DELETE FROM auth_rate_limits;
DELETE FROM job_applications;
DELETE FROM job_posts;
DELETE FROM user_skills;
DELETE FROM users;

INSERT INTO users
(user_id, mobile_number, email, password_hash, user_type, full_name, region, province, city, trust_score, current_balance, is_verified, created_at, last_login, account_status)
VALUES
(1, '09560618349', 'admin@raketgo.local', '$2y$10$nakdz1irgAGW68F8a2jLru9DkvEr7J3Wssn3aKIIqrsBWhNonKeSi', 'admin', 'Rika Matsuzaka', 'NCR', 'Metro Manila', 'Quezon City', 4.95, 0.00, 1, '2026-01-02 09:00:00', '2026-03-31 09:30:00', 'active'),
(2, '09170000002', 'maria.santos@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'employer', 'Maria Isabel Santos', 'NCR', 'Metro Manila', 'Manila', 4.62, 24500.00, 1, '2026-01-05 08:10:00', '2026-03-30 18:22:00', 'active'),
(3, '09170000003', 'carlo.reyes@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'employer', 'Carlo Miguel Reyes', 'NCR', 'Metro Manila', 'Quezon City', 4.51, 19780.50, 1, '2026-01-06 10:30:00', '2026-03-31 07:40:00', 'active'),
(4, '09170000004', 'jennifer.delacruz@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'employer', 'Jennifer Mae Dela Cruz', 'Region IV-A', 'Laguna', 'Santa Rosa', 4.33, 18320.75, 1, '2026-01-08 09:45:00', '2026-03-30 20:15:00', 'active'),
(5, '09170000005', 'raymond.villanueva@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'employer', 'Raymond Villanueva', 'Region III', 'Pampanga', 'Angeles City', 4.29, 22340.00, 1, '2026-01-10 07:50:00', '2026-03-31 08:55:00', 'active'),
(6, '09170000006', 'liza.bautista@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'employer', 'Liza Bautista', 'Region VII', 'Cebu', 'Cebu City', 4.71, 31400.00, 1, '2026-01-11 11:20:00', '2026-03-31 09:02:00', 'active'),
(7, '09170000007', 'noel.garcia@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'employer', 'Noel Garcia', 'Region XI', 'Davao del Sur', 'Davao City', 4.47, 16520.00, 1, '2026-01-12 13:40:00', '2026-03-30 21:10:00', 'active'),
(8, '09170000008', 'angela.mendoza@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'employer', 'Angela Mendoza', 'Region VI', 'Iloilo', 'Iloilo City', 4.54, 20600.30, 1, '2026-01-13 08:35:00', '2026-03-31 07:15:00', 'active'),
(9, '09170000009', 'patrick.flores@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'employer', 'Patrick Flores', 'Region I', 'Pangasinan', 'Dagupan City', 4.18, 14280.90, 1, '2026-01-14 09:25:00', '2026-03-30 16:45:00', 'active'),
(10, '09170000010', 'sharon.lim@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'employer', 'Sharon Lim', 'Region V', 'Camarines Sur', 'Naga City', 4.26, 18890.00, 1, '2026-01-15 10:10:00', '2026-03-31 08:05:00', 'active'),
(11, '09170000011', 'dennis.ramos@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'employer', 'Dennis Ramos', 'Region X', 'Misamis Oriental', 'Cagayan de Oro', 4.39, 17425.65, 1, '2026-01-16 12:55:00', '2026-03-31 07:52:00', 'active'),
(12, '09170000012', 'trisha.ong@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'employer', 'Trisha Ong', 'CAR', 'Benguet', 'Baguio City', 4.58, 25970.25, 1, '2026-01-18 08:45:00', '2026-03-30 19:34:00', 'active'),
(13, '09170000013', 'victor.aquino@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'employer', 'Victor Aquino', 'Region XII', 'South Cotabato', 'Koronadal City', 4.22, 16330.00, 1, '2026-01-19 09:15:00', '2026-03-31 08:26:00', 'active'),
(14, '09170000014', 'hazel.navarro@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'employer', 'Hazel Navarro', 'Region II', 'Isabela', 'Santiago City', 4.15, 11240.40, 1, '2026-01-20 07:30:00', '2026-03-30 15:12:00', 'active'),
(15, '09170000015', 'edwin.torres@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'employer', 'Edwin Torres', 'Region IX', 'Zamboanga del Sur', 'Pagadian City', 4.31, 13875.00, 1, '2026-01-21 14:05:00', '2026-03-30 20:47:00', 'active'),
(16, '09170000016', 'camille.mercado@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'employer', 'Camille Mercado', 'Region VIII', 'Leyte', 'Tacloban City', 4.44, 19210.00, 1, '2026-01-22 11:50:00', '2026-03-31 06:58:00', 'active'),
(17, '09170000017', 'john.rivera@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'John Paulo Rivera', 'NCR', 'Metro Manila', 'Manila', 4.12, 520.00, 1, '2026-01-24 09:00:00', '2026-03-30 18:10:00', 'active'),
(18, '09170000018', 'aira.castillo@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Aira Mae Castillo', 'NCR', 'Metro Manila', 'Pasig City', 3.86, 220.00, 1, '2026-01-24 09:20:00', '2026-03-31 07:35:00', 'active'),
(19, '09170000019', 'kevin.tan@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Kevin Dominic Tan', 'NCR', 'Metro Manila', 'Makati City', 4.06, 740.50, 1, '2026-01-25 08:12:00', '2026-03-30 21:11:00', 'active'),
(20, '09170000020', 'rochelle.peralta@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Rochelle Ann Peralta', 'Region IV-A', 'Cavite', 'Dasmarinas', 3.74, 155.00, 1, '2026-01-25 10:40:00', '2026-03-30 17:02:00', 'active'),
(21, '09170000021', 'mark.lopez@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Mark Anthony Lopez', 'Region IV-A', 'Batangas', 'Lipa City', 3.92, 350.00, 1, '2026-01-26 07:55:00', '2026-03-31 05:50:00', 'active'),
(22, '09170000022', 'sheena.bautista@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Sheena Marie Bautista', 'Region III', 'Bulacan', 'Malolos', 3.81, 210.00, 1, '2026-01-26 08:22:00', '2026-03-30 16:30:00', 'active'),
(23, '09170000023', 'jericho.alvarez@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Jericho Alvarez', 'Region III', 'Tarlac', 'Tarlac City', 4.08, 610.00, 1, '2026-01-26 09:05:00', '2026-03-30 20:12:00', 'active'),
(24, '09170000024', 'princess.santos@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Princess Joy Santos', 'Region VII', 'Cebu', 'Mandaue City', 3.73, 95.00, 1, '2026-01-27 10:30:00', '2026-03-31 08:42:00', 'active'),
(25, '09170000025', 'allan.dizon@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Allan Kim Dizon', 'Region VII', 'Bohol', 'Tagbilaran City', 3.90, 480.00, 1, '2026-01-27 13:15:00', '2026-03-31 07:11:00', 'active'),
(26, '09170000026', 'hannah.yu@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Hannah Grace Yu', 'Region VI', 'Negros Occidental', 'Bacolod City', 3.85, 260.00, 1, '2026-01-28 09:40:00', '2026-03-30 19:40:00', 'active'),
(27, '09170000027', 'ronel.guzman@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Ronel De Guzman', 'Region VI', 'Iloilo', 'Passi City', 4.14, 550.00, 1, '2026-01-29 08:05:00', '2026-03-30 18:26:00', 'active'),
(28, '09170000028', 'melvin.castro@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Melvin Castro', 'Region XI', 'Davao del Sur', 'Digos City', 4.05, 530.00, 1, '2026-01-29 11:00:00', '2026-03-30 17:18:00', 'active'),
(29, '09170000029', 'carla.abad@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Carla Denise Abad', 'Region XI', 'Davao del Norte', 'Tagum City', 3.88, 180.00, 1, '2026-01-30 10:50:00', '2026-03-31 06:40:00', 'active'),
(30, '09170000030', 'yvonne.salazar@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Yvonne Salazar', 'Region I', 'La Union', 'San Fernando City', 3.69, 140.00, 1, '2026-01-30 14:35:00', '2026-03-30 16:55:00', 'active'),
(31, '09170000031', 'kenneth.ong@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Kenneth Ong', 'Region I', 'Ilocos Norte', 'Laoag City', 3.97, 420.00, 1, '2026-01-31 09:55:00', '2026-03-31 07:24:00', 'active'),
(32, '09170000032', 'joana.villaflor@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Joana Villaflor', 'Region V', 'Albay', 'Legazpi City', 3.76, 210.00, 1, '2026-01-31 12:22:00', '2026-03-30 20:20:00', 'active'),
(33, '09170000033', 'rico.manalang@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Rico Manalang', 'Region V', 'Sorsogon', 'Sorsogon City', 3.91, 300.00, 1, '2026-02-01 08:45:00', '2026-03-30 15:57:00', 'active'),
(34, '09170000034', 'jessa.paragas@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Jessa Paragas', 'CAR', 'Benguet', 'La Trinidad', 3.83, 260.00, 1, '2026-02-01 10:10:00', '2026-03-31 08:14:00', 'active'),
(35, '09170000035', 'emil.cruz@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Emil Joseph Cruz', 'Region X', 'Bukidnon', 'Malaybalay City', 4.11, 630.00, 1, '2026-02-02 09:35:00', '2026-03-31 06:52:00', 'active'),
(36, '09170000036', 'lyka.manansala@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Lyka Manansala', 'Region X', 'Misamis Occidental', 'Ozamiz City', 3.72, 130.00, 1, '2026-02-02 11:11:00', '2026-03-30 18:42:00', 'active'),
(37, '09170000037', 'fredrick.paulino@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Fredrick Paulino', 'Region XII', 'Sultan Kudarat', 'Tacurong City', 3.95, 380.00, 1, '2026-02-03 07:48:00', '2026-03-31 07:05:00', 'active'),
(38, '09170000038', 'mary.ponce@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Mary Anne Ponce', 'Region XII', 'South Cotabato', 'Polomolok', 3.67, 95.00, 1, '2026-02-03 10:40:00', '2026-03-30 17:05:00', 'active'),
(39, '09170000039', 'nica.javier@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Nica Javier', 'Region II', 'Cagayan', 'Tuguegarao City', 3.89, 240.00, 1, '2026-02-04 09:02:00', '2026-03-30 19:58:00', 'active'),
(40, '09170000040', 'lawrence.rosario@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Lawrence Del Rosario', 'Region II', 'Nueva Vizcaya', 'Bayombong', 3.84, 210.00, 1, '2026-02-04 14:20:00', '2026-03-31 08:08:00', 'active'),
(41, '09170000041', 'jopet.morales@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Jopet Morales', 'Region IX', 'Zamboanga Sibugay', 'Ipil', 3.98, 420.00, 1, '2026-02-05 08:25:00', '2026-03-30 20:25:00', 'active'),
(42, '09170000042', 'rosemarie.cabalan@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Rosemarie Cabalan', 'Region IX', 'Zamboanga del Norte', 'Dipolog City', 3.71, 150.00, 1, '2026-02-05 11:07:00', '2026-03-31 06:36:00', 'active'),
(43, '09170000043', 'pauline.delatorre@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Pauline Dela Torre', 'Region VIII', 'Samar', 'Catbalogan City', 3.87, 260.00, 1, '2026-02-06 09:44:00', '2026-03-30 18:05:00', 'active'),
(44, '09170000044', 'gerald.sy@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Gerald Sy', 'Region VIII', 'Eastern Samar', 'Borongan City', 3.80, 185.00, 1, '2026-02-06 10:10:00', '2026-03-31 07:29:00', 'active'),
(45, '09170000045', 'ivan.magno@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Ivan Magno', 'Region XIII', 'Agusan del Norte', 'Butuan City', 4.09, 580.00, 1, '2026-02-07 08:50:00', '2026-03-30 17:45:00', 'active'),
(46, '09170000046', 'celine.panganiban@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Celine Panganiban', 'Region XIII', 'Surigao del Norte', 'Surigao City', 3.82, 200.00, 1, '2026-02-07 12:02:00', '2026-03-31 07:16:00', 'active'),
(47, '09170000047', 'abdul.macapagal@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Abdul Karim Macapagal', 'BARMM', 'Maguindanao', 'Cotabato City', 3.93, 350.00, 1, '2026-02-08 09:38:00', '2026-03-30 20:05:00', 'active'),
(48, '09170000048', 'amina.basman@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Amina Basman', 'BARMM', 'Lanao del Sur', 'Marawi City', 3.75, 120.00, 1, '2026-02-08 11:30:00', '2026-03-31 06:43:00', 'active'),
(49, '09170000049', 'marvin.solis@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Marvin Solis', 'NCR', 'Metro Manila', 'Caloocan City', 3.99, 460.00, 1, '2026-02-09 08:12:00', '2026-03-30 19:20:00', 'active'),
(50, '09170000050', 'lea.mendoza@samplemail.com', '$2y$10$So8mEmdXk2o8LMnxsEyAIu1UJWJdrSsdithkADpwkwdrapJ67vk5u', 'worker', 'Lea Mendoza', 'Region IV-B', 'Palawan', 'Puerto Princesa City', 3.78, 175.00, 1, '2026-02-09 10:45:00', '2026-03-31 08:18:00', 'active');

INSERT INTO skill_posts
(post_id, admin_id, post_title, post_content, post_type, link_url, thumbnail_image, category, tags, likes_count, views_count, is_featured, created_at)
VALUES
(1, 1, 'TESDA NC II Roadmap for Service Crew', 'This guide breaks down the complete path from enrollment to assessment for TESDA NC II in Food and Beverage Services. It includes practical preparation tips, required documents, and common mistakes first-timers should avoid.', 'certification', 'https://example.com/tesda-nc2-service-crew', 'uploads/posts/skill-post-1.jpg', 'Food Service', 'tesda,nc2,service crew', 44, 386, 1, '2026-03-05 09:10:00'),
(2, 1, 'Beginner Training: Basic Inventory Management', 'Learn how to monitor stock movement, avoid shrinkage, and prepare end-of-day inventory reports. The module uses small-store scenarios so workers can apply the process quickly during actual shifts.', 'training', 'https://example.com/basic-inventory-training', 'uploads/posts/skill-post-2.jpg', 'Retail', 'inventory,retail,stock control', 31, 275, 0, '2026-03-06 11:00:00'),
(3, 1, 'Free Course: Customer Service for Local Businesses', 'A structured course on handling inquiries, de-escalating complaints, and maintaining a positive customer tone. Includes role-play scripts in Filipino and English for daily frontliner situations.', 'course', 'https://example.com/customer-service-course', 'uploads/posts/skill-post-3.jpg', 'Customer Service', 'customer support,communication,frontliner', 52, 442, 1, '2026-03-07 08:25:00'),
(4, 1, 'Weekend Workshop: Mobile Phone Repair Basics', 'This hands-on workshop introduces safe disassembly, connector cleaning, and battery replacement workflow. Participants practice with real devices and receive a checklist they can use in shop work.', 'workshop', 'https://example.com/phone-repair-workshop', 'uploads/posts/skill-post-4.jpg', 'Repair', 'electronics,repair,phone technician', 29, 198, 0, '2026-03-08 14:40:00'),
(5, 1, 'Certification Prep: Bookkeeping Fundamentals', 'Designed for aspiring office assistants who need reliable bookkeeping habits. Topics include petty cash logs, receipt filing standards, and monthly reconciliation basics.', 'certification', 'https://example.com/bookkeeping-cert-prep', 'uploads/posts/skill-post-5.jpg', 'Admin Support', 'bookkeeping,office admin,finance basics', 37, 310, 0, '2026-03-09 10:15:00'),
(6, 1, 'Training Module: Safe Lifting and Warehouse Flow', 'Covers manual handling techniques, pallet organization, and loading-area safety checkpoints. The training also explains how to coordinate with dispatch to reduce turnaround delays.', 'training', 'https://example.com/warehouse-safety-training', 'uploads/posts/skill-post-6.jpg', 'Warehouse', 'warehouse,safety,logistics', 46, 350, 1, '2026-03-10 09:05:00'),
(7, 1, 'Course Pack: Intro to Social Media Selling', 'A beginner course for workers transitioning to online selling support roles. It teaches listing setup, response templates, and simple metrics to evaluate campaign performance.', 'course', 'https://example.com/social-selling-course', 'uploads/posts/skill-post-7.jpg', 'Digital Sales', 'social media,ecommerce,sales', 28, 244, 0, '2026-03-11 13:30:00'),
(8, 1, 'Workshop: Event Booth Operations and Crowd Flow', 'Participants learn booth setup sequence, customer queue handling, and fast product turnover strategies. Real event case studies are discussed to improve practical decision-making.', 'workshop', 'https://example.com/event-booth-workshop', 'uploads/posts/skill-post-8.jpg', 'Events', 'events,booth operations,customer flow', 24, 210, 0, '2026-03-12 16:00:00'),
(9, 1, 'Certification Brief: Caregiving Path in PH', 'A realistic overview of caregiver certification options, internship expectations, and hiring requirements. Includes interview tips for both home care and facility-based roles.', 'certification', 'https://example.com/caregiving-certification', 'uploads/posts/skill-post-9.jpg', 'Caregiving', 'caregiver,health support,certification', 41, 336, 1, '2026-03-13 09:45:00'),
(10, 1, 'Training Sprint: Housekeeping Standards', 'This compact training outlines room turnover standards, linen handling procedures, and sanitation checkpoints. It is ideal for entry-level applicants targeting hotel support roles.', 'training', 'https://example.com/housekeeping-training', 'uploads/posts/skill-post-10.jpg', 'Cleaning', 'housekeeping,sanitation,hotel support', 26, 188, 0, '2026-03-14 10:50:00'),
(11, 1, 'Course: Sales Talk for Market and Booth Sellers', 'A practical course on product pitching, upselling bundles, and handling price objections. Learners receive sample scripts that can be adapted for market stalls or mall kiosks.', 'course', 'https://example.com/sales-talk-course', 'uploads/posts/skill-post-11.jpg', 'Retail', 'sales,pitching,customer handling', 33, 295, 0, '2026-03-15 12:20:00'),
(12, 1, 'Workshop: Basic First Aid for Community Workers', 'This workshop teaches emergency response basics such as wound care, heat exhaustion response, and escalation protocol. Participants also practice incident reporting for workplace documentation.', 'workshop', 'https://example.com/first-aid-workshop', 'uploads/posts/skill-post-12.jpg', 'Community Health', 'first aid,safety,community work', 39, 318, 1, '2026-03-16 15:15:00');

INSERT INTO user_skills
(skill_id, user_id, skill_name, is_verified, verified_by, verified_at, proficiency_level, created_at)
VALUES
(1, 17, 'Carpentry', 1, 1, '2026-02-20 09:10:00', 'intermediate', '2026-02-12 09:10:00'),
(2, 17, 'Driving', 1, 1, '2026-02-20 09:15:00', 'advanced', '2026-02-12 09:15:00'),
(3, 18, 'Customer Service', 1, 1, '2026-02-20 10:00:00', 'intermediate', '2026-02-12 10:00:00'),
(4, 18, 'Data Entry', 0, NULL, NULL, 'beginner', '2026-02-12 10:05:00'),
(5, 19, 'Chat Support', 1, 1, '2026-02-20 10:20:00', 'advanced', '2026-02-12 10:20:00'),
(6, 19, 'Excel', 1, 1, '2026-02-20 10:25:00', 'intermediate', '2026-02-12 10:25:00'),
(7, 20, 'Housekeeping', 1, 1, '2026-02-20 10:40:00', 'intermediate', '2026-02-12 10:40:00'),
(8, 20, 'Cash Handling', 0, NULL, NULL, 'beginner', '2026-02-12 10:45:00'),
(9, 21, 'Sales', 1, 1, '2026-02-20 11:00:00', 'intermediate', '2026-02-12 11:00:00'),
(10, 21, 'Inventory Management', 1, 1, '2026-02-20 11:05:00', 'intermediate', '2026-02-12 11:05:00'),
(11, 22, 'Computer Literacy', 1, 1, '2026-02-20 11:20:00', 'intermediate', '2026-02-12 11:20:00'),
(12, 22, 'Customer Service', 0, NULL, NULL, 'beginner', '2026-02-12 11:25:00'),
(13, 23, 'Painting', 1, 1, '2026-02-20 11:40:00', 'advanced', '2026-02-12 11:40:00'),
(14, 23, 'Construction Safety', 1, 1, '2026-02-20 11:45:00', 'intermediate', '2026-02-12 11:45:00'),
(15, 24, 'Food Handling', 1, 1, '2026-02-20 12:00:00', 'intermediate', '2026-02-12 12:00:00'),
(16, 24, 'Cash Handling', 0, NULL, NULL, 'beginner', '2026-02-12 12:05:00'),
(17, 25, 'Kitchen Prep', 1, 1, '2026-02-20 12:20:00', 'intermediate', '2026-02-12 12:20:00'),
(18, 25, 'Cleaning', 0, NULL, NULL, 'beginner', '2026-02-12 12:25:00'),
(19, 26, 'Sewing', 1, 1, '2026-02-20 12:40:00', 'intermediate', '2026-02-12 12:40:00'),
(20, 26, 'Laundry Operations', 1, 1, '2026-02-20 12:45:00', 'intermediate', '2026-02-12 12:45:00'),
(21, 27, 'Forklift Operation', 0, NULL, NULL, 'beginner', '2026-02-12 13:00:00'),
(22, 27, 'Packing', 1, 1, '2026-02-20 13:05:00', 'advanced', '2026-02-12 13:05:00'),
(23, 28, 'Phone Repair', 1, 1, '2026-02-20 13:20:00', 'advanced', '2026-02-12 13:20:00'),
(24, 28, 'Soldering', 1, 1, '2026-02-20 13:25:00', 'intermediate', '2026-02-12 13:25:00'),
(25, 29, 'Social Media', 1, 1, '2026-02-20 13:40:00', 'intermediate', '2026-02-12 13:40:00'),
(26, 29, 'Video Editing', 0, NULL, NULL, 'beginner', '2026-02-12 13:45:00'),
(27, 30, 'Farm Labor', 1, 1, '2026-02-20 14:00:00', 'intermediate', '2026-02-12 14:00:00'),
(28, 30, 'Sorting', 1, 1, '2026-02-20 14:05:00', 'intermediate', '2026-02-12 14:05:00'),
(29, 31, 'Cashiering', 1, 1, '2026-02-20 14:20:00', 'advanced', '2026-02-12 14:20:00'),
(30, 31, 'Retail Sales', 1, 1, '2026-02-20 14:25:00', 'advanced', '2026-02-12 14:25:00'),
(31, 32, 'Barista', 1, 1, '2026-02-20 14:40:00', 'intermediate', '2026-02-12 14:40:00'),
(32, 32, 'Food Safety', 0, NULL, NULL, 'beginner', '2026-02-12 14:45:00'),
(33, 33, 'Outbound Calling', 1, 1, '2026-02-20 15:00:00', 'intermediate', '2026-02-12 15:00:00'),
(34, 33, 'CRM Usage', 0, NULL, NULL, 'beginner', '2026-02-12 15:05:00'),
(35, 34, 'Bookkeeping', 1, 1, '2026-02-20 15:20:00', 'intermediate', '2026-02-12 15:20:00'),
(36, 34, 'Scheduling', 0, NULL, NULL, 'beginner', '2026-02-12 15:25:00'),
(37, 35, 'Warehouse Operations', 1, 1, '2026-02-20 15:40:00', 'advanced', '2026-02-12 15:40:00'),
(38, 35, 'Inventory Counting', 1, 1, '2026-02-20 15:45:00', 'intermediate', '2026-02-12 15:45:00'),
(39, 36, 'Electrical Maintenance', 0, NULL, NULL, 'beginner', '2026-02-12 16:00:00'),
(40, 36, 'Plumbing Basics', 0, NULL, NULL, 'beginner', '2026-02-12 16:05:00'),
(41, 37, 'Tour Coordination', 1, 1, '2026-02-20 16:20:00', 'intermediate', '2026-02-12 16:20:00'),
(42, 38, 'Merchandising', 1, 1, '2026-02-20 16:25:00', 'intermediate', '2026-02-12 16:25:00'),
(43, 39, 'Auto Detailing', 1, 1, '2026-02-20 16:40:00', 'intermediate', '2026-02-12 16:40:00'),
(44, 40, 'Booth Sales', 1, 1, '2026-02-20 16:45:00', 'intermediate', '2026-02-12 16:45:00'),
(45, 41, 'Machine Operation', 1, 1, '2026-02-20 17:00:00', 'intermediate', '2026-02-12 17:00:00'),
(46, 42, 'Caregiving', 1, 1, '2026-02-20 17:05:00', 'intermediate', '2026-02-12 17:05:00'),
(47, 43, 'Dispatching', 0, NULL, NULL, 'beginner', '2026-02-12 17:20:00'),
(48, 44, 'Engine Maintenance', 1, 1, '2026-02-20 17:25:00', 'intermediate', '2026-02-12 17:25:00'),
(49, 45, 'Housekeeping', 1, 1, '2026-02-20 17:40:00', 'advanced', '2026-02-12 17:40:00'),
(50, 46, 'Audio Setup', 1, 1, '2026-02-20 17:45:00', 'intermediate', '2026-02-12 17:45:00');

INSERT INTO job_posts
(job_id, employer_id, job_title, job_description, location_region, location_province, location_city, specific_address, pay_amount, pay_type, required_skills, preferred_skills, job_category, start_date, end_date, slots_available, slots_filled, advance_payment_amount, job_status, created_at)
VALUES
(1, 2, 'Weekend Event Setup Assistant', 'Help our events team set up booth structures, lights, and inventory bins before mall activations. The role includes teardown and basic checklist reporting after every event day.', 'NCR', 'Metro Manila', 'Manila', 'Robinsons Place Manila, Adriatico Wing', 850.00, 'daily', 'physical fitness,teamwork,inventory handling', 'basic electrical knowledge', 'Events', '2026-04-06', '2026-06-30', 6, 2, 500.00, 'active', '2026-03-20 08:10:00'),
(2, 2, 'Retail Inventory Encoder', 'Maintain daily stock logs and encode item movement in our point-of-sale back office. Accuracy and end-of-day reconciliation are important to keep operations smooth.', 'NCR', 'Metro Manila', 'Manila', 'Quiapo Wholesale Center', 750.00, 'daily', 'computer literacy,data entry,attention to detail', 'excel reporting', 'Retail', '2026-04-03', '2026-08-03', 2, 0, 0.00, 'active', '2026-03-20 08:30:00'),
(3, 3, 'Online Seller Chat Support', 'Respond to buyer inquiries, follow shipping status, and coordinate returns through our online store inbox. You will use canned replies but must personalize responses for customer concerns.', 'NCR', 'Metro Manila', 'Quezon City', 'Timog Avenue, Quezon City', 16500.00, 'monthly', 'customer service,typing speed,chat support', 'ecommerce tools', 'Admin Support', '2026-04-08', '2026-10-08', 3, 1, 0.00, 'active', '2026-03-21 09:00:00'),
(4, 3, 'Delivery Rider for Same-Day Parcels', 'Deliver same-day parcels around Metro Manila using optimized route sheets and proof-of-delivery photos. Riders should be punctual, safety-conscious, and comfortable with app-based tracking.', 'NCR', 'Metro Manila', 'Quezon City', 'West Avenue Dispatch Hub', 900.00, 'daily', 'driving,route planning,time management', 'customer handling', 'Delivery', '2026-04-01', '2026-07-31', 5, 3, 1000.00, 'in_progress', '2026-03-18 13:00:00'),
(5, 4, 'Salon Reception and Cashier', 'Handle front-desk bookings, greet walk-in clients, and process cash and e-wallet payments. The role also requires basic upselling of treatment packages and refill products.', 'Region IV-A', 'Laguna', 'Santa Rosa', 'Balibago Commercial Complex', 14500.00, 'monthly', 'customer service,cash handling,scheduling', 'retail upselling', 'Beauty', '2026-04-05', '2026-10-05', 2, 1, 0.00, 'active', '2026-03-19 10:05:00'),
(6, 4, 'Weekend Promo Booth Staff', 'Promote household products at partner supermarkets every weekend and monitor promo stock levels. Staff should present confidently and keep daily sales tallies accurate.', 'Region IV-A', 'Laguna', 'Santa Rosa', 'SM City Santa Rosa Atrium', 820.00, 'daily', 'sales,presentation,product demo', 'basic inventory tracking', 'Retail', '2026-04-12', '2026-07-12', 4, 1, 300.00, 'active', '2026-03-19 10:22:00'),
(7, 5, 'Hardware Store Sales Associate', 'Assist customers in selecting tools, fittings, and paint supplies for small home projects. You will also maintain aisle organization and support weekly receiving tasks.', 'Region III', 'Pampanga', 'Angeles City', 'Nepo Mall Commercial Row', 15000.00, 'monthly', 'sales,product knowledge,customer service', 'hardware background', 'Retail', '2026-04-02', '2026-09-30', 3, 2, 0.00, 'active', '2026-03-17 08:35:00'),
(8, 5, 'Residential Painter Helper', 'Support wall preparation, masking, and paint mixing for residential projects in Angeles and nearby towns. The team values workers who can follow finish-quality standards and timelines.', 'Region III', 'Pampanga', 'Angeles City', 'Cutcut Service Yard', 950.00, 'daily', 'painting,surface prep,construction safety', 'ladder work', 'Construction', '2026-04-04', '2026-06-28', 5, 1, 600.00, 'active', '2026-03-17 09:10:00'),
(9, 6, 'Cafe Service Crew', 'Take customer orders, handle table turnover, and support basic drink assembly during peak hours. Teamwork and clean station habits are critical in this role.', 'Region VII', 'Cebu', 'Cebu City', 'Fuente Osmena Branch', 780.00, 'daily', 'food handling,customer service,teamwork', 'barista basics', 'Food Service', '2026-04-01', '2026-09-01', 4, 1, 0.00, 'active', '2026-03-16 11:50:00'),
(10, 6, 'Part-time Kitchen Prep Assistant', 'Prepare ingredients, portion sauces, and maintain sanitation logs before opening. Candidates should be organized, fast, and attentive to kitchen safety checks.', 'Region VII', 'Cebu', 'Cebu City', 'Colon Kitchen Commissary', 720.00, 'daily', 'kitchen prep,food safety,sanitation', 'line support', 'Food Service', '2026-04-03', '2026-08-31', 3, 2, 0.00, 'active', '2026-03-16 12:20:00'),
(11, 7, 'Office Utility and Messenger', 'Provide office support through filing, supply runs, and same-city document delivery. The role also includes simple admin errands and receiving desk coverage when needed.', 'Region XI', 'Davao del Sur', 'Davao City', 'JP Laurel Office Tower', 13800.00, 'monthly', 'organization,time management,driving', 'office support', 'Admin Support', '2026-04-07', '2026-10-07', 2, 0, 0.00, 'active', '2026-03-18 09:45:00'),
(12, 7, 'Mobile Phone Repair Assistant', 'Assist senior technicians in diagnostics, screen replacement prep, and quality checks before release. The role is ideal for workers who want to build practical repair experience quickly.', 'Region XI', 'Davao del Sur', 'Davao City', 'Roxas Night Market Tech Strip', 880.00, 'daily', 'phone repair,attention to detail,customer handling', 'soldering', 'Repair', '2026-04-06', '2026-07-30', 2, 1, 500.00, 'active', '2026-03-18 10:05:00'),
(13, 8, 'Laundry Shop Attendant', 'Receive garments, sort by service type, and track customer claims during pickup and release. Proper stain notes and clear customer communication are required each shift.', 'Region VI', 'Iloilo', 'Iloilo City', 'Jaro Commercial Arcade', 13200.00, 'monthly', 'customer service,sorting,laundry operations', 'stain treatment awareness', 'Cleaning', '2026-04-05', '2026-10-05', 3, 1, 0.00, 'active', '2026-03-19 07:58:00'),
(14, 8, 'Event Registration Desk Staff', 'Manage attendee check-in, issue IDs, and direct guests to designated halls during community events. Staff should be comfortable with crowd coordination and quick problem solving.', 'Region VI', 'Iloilo', 'Iloilo City', 'Iloilo Convention Hall B', 830.00, 'daily', 'customer service,registration,communication', 'event admin support', 'Events', '2026-03-10', '2026-03-25', 5, 5, 0.00, 'completed', '2026-03-01 08:44:00'),
(15, 9, 'Farm Produce Sorter and Packer', 'Sort vegetables by quality grade, weigh produce, and prepare shipment crates before dispatch. Attention to labeling and careful handling is important to reduce spoilage.', 'Region I', 'Pangasinan', 'Dagupan City', 'Magsaysay Market Packing Shed', 760.00, 'daily', 'sorting,packing,physical fitness', 'cold storage exposure', 'Warehouse', '2026-04-09', '2026-09-09', 6, 2, 0.00, 'active', '2026-03-20 06:55:00'),
(16, 9, 'Tricycle Spare Parts Sales Helper', 'Assist customers in finding compatible spare parts and support shelf replenishment every evening. The position also includes basic inventory counting during weekly audits.', 'Region I', 'Pangasinan', 'Dagupan City', 'Perez Boulevard Auto Row', 810.00, 'daily', 'sales,inventory handling,product matching', 'mechanical familiarity', 'Retail', '2026-04-02', '2026-08-02', 2, 1, 0.00, 'active', '2026-03-20 07:20:00'),
(17, 10, 'Home-based Telesales Caller', 'Handle outbound calls for local product campaigns and maintain lead status updates in the CRM sheet. Candidates should be clear communicators and comfortable with monthly target tracking.', 'Region V', 'Camarines Sur', 'Naga City', 'Panicuason Remote Hub', 15500.00, 'monthly', 'outbound calling,crm usage,communication', 'sales script handling', 'Admin Support', '2026-04-08', '2026-10-08', 4, 1, 0.00, 'active', '2026-03-21 09:18:00'),
(18, 10, 'School Canteen Assistant', 'Prepare basic food packs, assist in serving, and keep sanitation logs before and after lunch breaks. This listing was paused after schedule changes with the school admin office.', 'Region V', 'Camarines Sur', 'Naga City', 'San Felipe School Canteen', 700.00, 'daily', 'food handling,cleaning,time management', 'cash handling', 'Food Service', '2026-04-04', '2026-06-30', 3, 0, 0.00, 'cancelled', '2026-03-21 09:40:00'),
(19, 11, 'Warehouse Picker and Packer', 'Pick outbound orders, verify item quantities, and stage parcels for courier pickup every afternoon. Workers should be accurate and comfortable with repetitive movement.', 'Region X', 'Misamis Oriental', 'Cagayan de Oro', 'Puerto Logistics Strip', 870.00, 'daily', 'warehouse operations,packing,inventory counting', 'barcode scanning', 'Warehouse', '2026-04-05', '2026-09-05', 8, 3, 0.00, 'active', '2026-03-18 15:10:00'),
(20, 11, 'Building Maintenance Helper', 'Support preventive maintenance checks for electrical fixtures, pumps, and common area plumbing. The role includes daily ticket logs and coordination with the senior technician.', 'Region X', 'Misamis Oriental', 'Cagayan de Oro', 'Nazareth Mixed-Use Building', 980.00, 'daily', 'electrical maintenance,plumbing basics,safety', 'tools handling', 'Construction', '2026-04-01', '2026-07-20', 3, 2, 1000.00, 'in_progress', '2026-03-18 15:40:00'),
(21, 12, 'Tourist Van Booking Coordinator', 'Manage trip schedules, confirm bookings, and coordinate pick-up times with partner drivers. The role requires organized dispatch communication and accurate booking records.', 'CAR', 'Benguet', 'Baguio City', 'Session Road Booking Office', 16200.00, 'monthly', 'scheduling,customer service,dispatching', 'tourism operations', 'Admin Support', '2026-04-06', '2026-10-06', 2, 1, 0.00, 'active', '2026-03-19 09:00:00'),
(22, 12, 'Souvenir Store Cashier', 'Process cashier transactions, monitor fast-moving souvenir items, and prepare shift-end sales summaries. Workers should be attentive to small cash variances and customer requests.', 'CAR', 'Benguet', 'Baguio City', 'Burnham Park Souvenir Lane', 790.00, 'daily', 'cashiering,customer service,inventory', 'retail sales', 'Retail', '2026-04-07', '2026-08-31', 2, 0, 0.00, 'active', '2026-03-19 09:18:00'),
(23, 13, 'Auto Detailer Assistant', 'Support exterior wash, interior vacuum, and finishing polish procedures for daily car detailing jobs. The position values consistency in detailing sequence and quality checks.', 'Region XII', 'South Cotabato', 'Koronadal City', 'General Santos Drive Service Bay', 900.00, 'daily', 'auto detailing,cleaning,customer handling', 'buffing machine familiarity', 'Repair', '2026-04-03', '2026-08-03', 4, 1, 300.00, 'active', '2026-03-20 10:30:00'),
(24, 13, 'Weekend Market Booth Seller', 'Sell local snack products in weekend markets and monitor fast-moving inventory by schedule block. The role includes customer engagement and simple daily remittance reports.', 'Region XII', 'South Cotabato', 'Koronadal City', 'Public Market Weekend Zone', 760.00, 'daily', 'sales,customer engagement,cash handling', 'booth setup', 'Retail', '2026-04-12', '2026-07-12', 3, 1, 0.00, 'active', '2026-03-20 10:50:00'),
(25, 14, 'Rice Mill Operations Helper', 'Assist in loading, bagging, and machine cleanup during daily milling operations. Workers should follow safety guidelines and maintain clear production logs.', 'Region II', 'Isabela', 'Santiago City', 'Sinsayon Rice Mill Compound', 850.00, 'daily', 'machine operation,physical fitness,quality inspection', 'warehouse support', 'Warehouse', '2026-04-04', '2026-09-30', 5, 2, 0.00, 'active', '2026-03-21 06:40:00'),
(26, 14, 'Barangay Health Program Aide', 'Assist in community health drives, registration, and first-level patient guidance during outreach sessions. The role requires empathy, organization, and basic first aid awareness.', 'Region II', 'Isabela', 'Santiago City', 'Barangay District Health Unit', 14800.00, 'monthly', 'caregiving,community outreach,record keeping', 'first aid', 'Caregiving', '2026-04-08', '2026-10-08', 2, 0, 0.00, 'active', '2026-03-21 07:05:00'),
(27, 15, 'Restaurant Delivery Dispatcher', 'Coordinate rider assignments, monitor order status, and notify customers about delivery updates. The position needs quick decision-making during peak meal hours.', 'Region IX', 'Zamboanga del Sur', 'Pagadian City', 'Rizal Avenue Dispatch Desk', 15200.00, 'monthly', 'dispatching,route planning,communication', 'food delivery operations', 'Delivery', '2026-04-02', '2026-10-02', 3, 1, 0.00, 'active', '2026-03-22 08:30:00'),
(28, 15, 'Small Engine Mechanic Helper', 'Support tune-ups and routine repair for small gasoline engines used in tricycles and pumps. You will assist in parts preparation and post-repair test checks.', 'Region IX', 'Zamboanga del Sur', 'Pagadian City', 'Tiguma Mechanics Row', 930.00, 'daily', 'engine maintenance,hand tools,safety', 'parts identification', 'Repair', '2026-04-06', '2026-08-30', 2, 1, 400.00, 'active', '2026-03-22 08:55:00'),
(29, 16, 'Hotel Housekeeping Assistant', 'Perform room cleaning, linen turnover, and housekeeping checklist completion based on occupancy schedules. Standards emphasize cleanliness consistency and guest readiness timelines.', 'Region VIII', 'Leyte', 'Tacloban City', 'Downtown Bay Hotel', 14600.00, 'monthly', 'housekeeping,linen management,sanitation', 'hotel operations', 'Cleaning', '2026-04-05', '2026-10-05', 4, 2, 0.00, 'active', '2026-03-22 09:10:00'),
(30, 16, 'Event Sound Crew Helper', 'Assist in hauling speakers, arranging cabling, and performing line checks before events start. Workers should be physically fit and careful with equipment handling.', 'Region VIII', 'Leyte', 'Tacloban City', 'People Center Stage Area', 920.00, 'daily', 'audio setup,cable management,teamwork', 'basic mixer knowledge', 'Events', '2026-04-10', '2026-07-31', 5, 1, 500.00, 'active', '2026-03-22 09:25:00');

INSERT INTO job_applications
(application_id, job_id, worker_id, employer_id, application_status, cover_letter, applied_at, reviewed_at, worker_confirmed, employer_confirmed, work_start_time, work_end_time, contract_sent, payment_released)
VALUES
(1, 1, 17, 2, 'approved', 'I have worked as a setup assistant for barangay events and can handle heavy booth materials safely. I can commit on weekends and report early on-site.', '2026-03-23 08:10:00', '2026-03-24 10:00:00', 1, 1, '2026-04-06 07:30:00', NULL, 1, 0),
(2, 2, 18, 2, 'pending', 'I am comfortable with inventory encoding and can work full shifts with minimal supervision.', '2026-03-23 09:00:00', NULL, 0, 0, NULL, NULL, 0, 0),
(3, 3, 19, 3, 'approved', 'I have one year of online chat support experience and I can type quickly with clear responses.', '2026-03-23 10:15:00', '2026-03-24 11:20:00', 1, 1, '2026-04-08 09:00:00', NULL, 1, 0),
(4, 4, 20, 3, 'pending', 'I can drive around Metro Manila and I am familiar with map apps and delivery proof submission.', '2026-03-23 11:10:00', NULL, 0, 0, NULL, NULL, 0, 0),
(5, 5, 21, 4, 'rejected', 'I have customer-facing experience and can handle bookings and payment recording.', '2026-03-23 13:40:00', '2026-03-25 09:45:00', 0, 0, NULL, NULL, 0, 0),
(6, 6, 22, 4, 'pending', 'I enjoy promo work and I can confidently explain products to shoppers.', '2026-03-23 14:05:00', NULL, 0, 0, NULL, NULL, 0, 0),
(7, 7, 23, 5, 'approved', 'I previously worked in a hardware store and can help customers quickly find items they need.', '2026-03-23 15:20:00', '2026-03-24 16:00:00', 1, 1, '2026-04-02 08:00:00', NULL, 1, 0),
(8, 8, 24, 5, 'withdrawn', 'I can assist painters with prep work and cleanup and I am willing to work outdoors.', '2026-03-23 16:10:00', '2026-03-24 09:10:00', 0, 0, NULL, NULL, 0, 0),
(9, 9, 25, 6, 'pending', 'I can follow store procedures and maintain good service during busy meal times.', '2026-03-23 16:45:00', NULL, 0, 0, NULL, NULL, 0, 0),
(10, 10, 26, 6, 'approved', 'I have kitchen prep experience and I can maintain sanitation standards throughout the shift.', '2026-03-23 17:30:00', '2026-03-24 10:30:00', 1, 1, '2026-04-03 06:45:00', NULL, 1, 0),
(11, 11, 27, 7, 'pending', 'I can assist with filing and messenger tasks and I have basic driving experience.', '2026-03-24 08:00:00', NULL, 0, 0, NULL, NULL, 0, 0),
(12, 12, 28, 7, 'rejected', 'I am interested in phone repair work and eager to learn diagnostics and replacement procedures.', '2026-03-24 09:05:00', '2026-03-25 13:00:00', 0, 0, NULL, NULL, 0, 0),
(13, 13, 29, 8, 'approved', 'I worked in a laundry shop before and I can handle customer intake and sorting tasks.', '2026-03-24 09:55:00', '2026-03-25 10:10:00', 1, 1, '2026-04-05 08:00:00', NULL, 1, 0),
(14, 14, 30, 8, 'approved', 'I am experienced with event registration desks and can manage crowd lines calmly.', '2026-03-05 11:20:00', '2026-03-06 08:40:00', 1, 1, '2026-03-10 08:00:00', '2026-03-25 18:00:00', 1, 1),
(15, 15, 31, 9, 'pending', 'I can handle repetitive sorting work and I am comfortable with early morning schedules.', '2026-03-24 13:05:00', NULL, 0, 0, NULL, NULL, 0, 0),
(16, 16, 32, 9, 'rejected', 'I have retail experience and can assist buyers with item selection and stock checks.', '2026-03-24 13:45:00', '2026-03-25 15:10:00', 0, 0, NULL, NULL, 0, 0),
(17, 17, 33, 10, 'approved', 'I have worked in outbound calling and can maintain call logs and follow-up notes accurately.', '2026-03-24 14:30:00', '2026-03-25 11:35:00', 1, 1, '2026-04-08 09:00:00', NULL, 1, 0),
(18, 18, 34, 10, 'withdrawn', 'I am available for canteen shifts and can support prep and service operations.', '2026-03-24 15:00:00', '2026-03-25 09:20:00', 0, 0, NULL, NULL, 0, 0),
(19, 19, 35, 11, 'pending', 'I can work in warehouse operations and I am comfortable with packing and outbound staging.', '2026-03-24 15:35:00', NULL, 0, 0, NULL, NULL, 0, 0),
(20, 20, 36, 11, 'approved', 'I can support maintenance rounds and log completed tasks for electrical and plumbing issues.', '2026-03-24 16:10:00', '2026-03-25 12:20:00', 1, 1, '2026-04-01 08:30:00', NULL, 1, 0),
(21, 21, 37, 12, 'pending', 'I can coordinate schedules and communicate clearly with both customers and drivers.', '2026-03-24 16:45:00', NULL, 0, 0, NULL, NULL, 0, 0),
(22, 22, 38, 12, 'rejected', 'I have cashiering and merchandising experience and can handle customer transactions accurately.', '2026-03-24 17:15:00', '2026-03-25 14:12:00', 0, 0, NULL, NULL, 0, 0),
(23, 23, 39, 13, 'approved', 'I can support detailing steps and maintain quality checks before releasing finished vehicles.', '2026-03-24 17:40:00', '2026-03-25 16:00:00', 1, 1, '2026-04-03 08:00:00', NULL, 1, 0),
(24, 24, 40, 13, 'pending', 'I have weekend market selling experience and can monitor stock movement during shifts.', '2026-03-24 18:05:00', NULL, 0, 0, NULL, NULL, 0, 0),
(25, 25, 41, 14, 'approved', 'I am used to machine operation environments and can follow strict safety procedures.', '2026-03-25 08:20:00', '2026-03-26 09:30:00', 1, 1, '2026-04-04 07:00:00', NULL, 1, 0),
(26, 26, 42, 14, 'pending', 'I have community health volunteer experience and can support registration and basic triage flow.', '2026-03-25 08:55:00', NULL, 0, 0, NULL, NULL, 0, 0),
(27, 27, 43, 15, 'pending', 'I can coordinate riders and keep dispatch records updated in real time.', '2026-03-25 09:30:00', NULL, 0, 0, NULL, NULL, 0, 0),
(28, 28, 44, 15, 'rejected', 'I can assist mechanics with tools and cleanup and I have basic engine familiarity.', '2026-03-25 10:15:00', '2026-03-26 15:40:00', 0, 0, NULL, NULL, 0, 0),
(29, 29, 45, 16, 'approved', 'I have hotel housekeeping experience and can complete room checklists on schedule.', '2026-03-25 11:40:00', '2026-03-26 09:55:00', 1, 1, '2026-04-05 08:00:00', NULL, 1, 0),
(30, 30, 46, 16, 'pending', 'I can assist with stage setup and cable management during event days.', '2026-03-25 12:05:00', NULL, 0, 0, NULL, NULL, 0, 0),
(31, 1, 47, 2, 'pending', 'I can do heavy lifting and follow event setup floor plans correctly.', '2026-03-25 12:45:00', NULL, 0, 0, NULL, NULL, 0, 0),
(32, 2, 48, 2, 'rejected', 'I can encode inventory data and review stock sheets after every shift.', '2026-03-25 13:20:00', '2026-03-26 11:10:00', 0, 0, NULL, NULL, 0, 0),
(33, 3, 49, 3, 'pending', 'I have chat support background and can handle customer issues patiently.', '2026-03-25 13:55:00', NULL, 0, 0, NULL, NULL, 0, 0),
(34, 4, 50, 3, 'approved', 'I can manage multiple delivery drops and submit complete delivery proofs.', '2026-03-25 14:30:00', '2026-03-26 10:00:00', 1, 1, '2026-04-01 08:00:00', NULL, 1, 0),
(35, 5, 17, 4, 'pending', 'I am interested in salon front desk operations and can work flexible shifts.', '2026-03-25 15:05:00', NULL, 0, 0, NULL, NULL, 0, 0),
(36, 6, 18, 4, 'approved', 'I can assist in product demos and maintain clear promo inventory records.', '2026-03-25 15:40:00', '2026-03-26 11:55:00', 1, 1, '2026-04-12 09:00:00', NULL, 1, 0),
(37, 7, 19, 5, 'pending', 'I can support hardware sales and customer assistance in a fast-paced environment.', '2026-03-25 16:15:00', NULL, 0, 0, NULL, NULL, 0, 0),
(38, 8, 20, 5, 'rejected', 'I can assist painters with preparation and surface cleaning tasks.', '2026-03-25 16:50:00', '2026-03-26 16:20:00', 0, 0, NULL, NULL, 0, 0),
(39, 9, 21, 6, 'pending', 'I can do table service and support order flow during peak cafe hours.', '2026-03-25 17:30:00', NULL, 0, 0, NULL, NULL, 0, 0),
(40, 10, 22, 6, 'withdrawn', 'I can work in kitchen prep and maintain sanitation checklists every shift.', '2026-03-25 18:05:00', '2026-03-26 08:30:00', 0, 0, NULL, NULL, 0, 0),
(41, 11, 23, 7, 'approved', 'I can perform office errand tasks efficiently and keep document handling organized.', '2026-03-26 08:15:00', '2026-03-27 09:25:00', 1, 1, '2026-04-07 08:30:00', NULL, 1, 0),
(42, 12, 24, 7, 'pending', 'I am willing to train as a repair assistant and follow technician instructions.', '2026-03-26 09:05:00', NULL, 0, 0, NULL, NULL, 0, 0),
(43, 13, 25, 8, 'pending', 'I have previous laundry shop work and can handle customer claims carefully.', '2026-03-26 10:10:00', NULL, 0, 0, NULL, NULL, 0, 0),
(44, 14, 26, 8, 'pending', 'I have event desk experience and can process check-in quickly and accurately.', '2026-03-26 10:55:00', NULL, 0, 0, NULL, NULL, 0, 0),
(45, 15, 27, 9, 'approved', 'I can do produce sorting and packing while keeping labels and counts consistent.', '2026-03-26 11:40:00', '2026-03-27 10:30:00', 1, 1, '2026-04-09 06:30:00', NULL, 1, 0),
(46, 16, 28, 9, 'pending', 'I can assist in spare parts sales and support weekly inventory counting.', '2026-03-26 12:20:00', NULL, 0, 0, NULL, NULL, 0, 0),
(47, 17, 29, 10, 'rejected', 'I have outbound sales experience and can work with scripts and lead sheets.', '2026-03-26 13:10:00', '2026-03-27 14:45:00', 0, 0, NULL, NULL, 0, 0),
(48, 18, 30, 10, 'pending', 'I can support canteen prep and maintain cleanliness standards during shifts.', '2026-03-26 13:55:00', NULL, 0, 0, NULL, NULL, 0, 0),
(49, 19, 31, 11, 'approved', 'I can perform order picking and maintain proper packing quality standards.', '2026-03-26 14:35:00', '2026-03-27 10:55:00', 1, 1, '2026-04-05 08:00:00', NULL, 1, 0),
(50, 20, 32, 11, 'pending', 'I can assist maintenance teams and update work logs throughout the day.', '2026-03-26 15:25:00', NULL, 0, 0, NULL, NULL, 0, 0);

INSERT INTO digital_contracts
(contract_id, application_id, contract_content, contract_terms, instructions, meeting_location, meeting_time, worker_signed, employer_signed, worker_signed_at, employer_signed_at, created_at)
VALUES
(1, 1, 'Worker will assist event setup and teardown on scheduled weekends with assigned team lead supervision.', 'Daily rate paid every Saturday. Overtime requires prior approval.', 'Arrive 30 minutes before call time and check in with events supervisor.', 'Robinsons Place Manila Service Entrance', '2026-04-06 07:00:00', 1, 1, '2026-03-24 10:30:00', '2026-03-24 10:35:00', '2026-03-24 10:20:00'),
(2, 3, 'Worker will provide online chat support during assigned shifts and update order status records.', 'Monthly payout every 15th and 30th, based on completed shift logs.', 'Use approved response templates and escalate unresolved complaints.', 'Timog Avenue Main Office', '2026-04-08 08:30:00', 1, 1, '2026-03-24 11:40:00', '2026-03-24 11:48:00', '2026-03-24 11:30:00'),
(3, 7, 'Worker will handle front-floor hardware sales and shelving duties based on store assignment.', 'Monthly salary includes attendance bonus after full month completion.', 'Coordinate with head cashier before opening and closing.', 'Nepo Mall Store Office', '2026-04-02 07:30:00', 1, 1, '2026-03-24 16:20:00', '2026-03-24 16:25:00', '2026-03-24 16:10:00'),
(4, 10, 'Worker will support kitchen prep, sanitation, and opening checklist procedures.', 'Daily pay released every week through GCash.', 'Wear complete kitchen PPE and log prep quantities every shift.', 'Colon Kitchen Commissary', '2026-04-03 06:30:00', 1, 1, '2026-03-24 10:45:00', '2026-03-24 10:55:00', '2026-03-24 10:40:00'),
(5, 14, 'Worker completed event registration operations for assigned conference dates.', 'Payment includes base daily rate plus attendance completion bonus.', 'Submit final attendance reports before release date.', 'Iloilo Convention Hall B', '2026-03-10 07:30:00', 1, 1, '2026-03-06 09:00:00', '2026-03-06 09:05:00', '2026-03-06 08:50:00'),
(6, 17, 'Worker will conduct home-based telesales and maintain lead disposition updates.', 'Monthly salary with target-based incentive for verified conversions.', 'Join daily virtual huddle at 8:30 AM.', 'Naga Remote Operations Office', '2026-04-08 08:00:00', 1, 1, '2026-03-25 12:00:00', '2026-03-25 12:08:00', '2026-03-25 11:55:00'),
(7, 20, 'Worker will assist building maintenance rounds and issue tracking.', 'Daily pay released every Friday upon validated task logs.', 'Coordinate with senior technician for all electrical tasks.', 'Nazareth Building Admin Office', '2026-04-01 08:00:00', 1, 1, '2026-03-25 12:35:00', '2026-03-25 12:42:00', '2026-03-25 12:30:00'),
(8, 23, 'Worker will support daily detailing procedures and final quality checks.', 'Daily pay plus performance bonus based on customer ratings.', 'Follow detailing checklist in sequence without skipping steps.', 'Service Bay Control Desk', '2026-04-03 07:45:00', 1, 1, '2026-03-25 16:10:00', '2026-03-25 16:18:00', '2026-03-25 16:05:00'),
(9, 29, 'Worker will perform room cleaning and linen turnover under hotel housekeeping standards.', 'Monthly payout with weekly attendance allowance.', 'Report damaged items immediately to housekeeping supervisor.', 'Downtown Bay Hotel HR Office', '2026-04-05 07:30:00', 1, 1, '2026-03-26 10:15:00', '2026-03-26 10:20:00', '2026-03-26 10:10:00'),
(10, 34, 'Worker will perform same-day parcel deliveries based on route sequence assigned by dispatch.', 'Daily pay with fuel support allowance upon complete route submission.', 'Submit delivery proof photos and signed receipts before end of day.', 'West Avenue Dispatch Hub', '2026-04-01 07:30:00', 1, 1, '2026-03-26 10:20:00', '2026-03-26 10:28:00', '2026-03-26 10:15:00');

INSERT INTO messages
(message_id, sender_id, receiver_id, job_id, message_content, attachment, is_read, sent_at, read_at)
VALUES
(1, 2, 17, 1, 'Hi John, please bring gloves and wear closed shoes for the event setup shift.', NULL, 1, '2026-03-24 11:05:00', '2026-03-24 11:18:00'),
(2, 17, 2, 1, 'Received, I will arrive before 7:00 AM and confirm at the service entrance.', NULL, 1, '2026-03-24 11:20:00', '2026-03-24 11:24:00'),
(3, 3, 19, 3, 'Please prepare for a short skills check on chat handling this Friday.', NULL, 1, '2026-03-24 12:10:00', '2026-03-24 12:45:00'),
(4, 19, 3, 3, 'Thank you, I will be available and I can share sample responses during the check.', NULL, 1, '2026-03-24 12:50:00', '2026-03-24 12:55:00'),
(5, 6, 26, 10, 'Kitchen orientation starts at 6:30 AM. Please bring your own non-slip shoes.', NULL, 1, '2026-03-24 13:20:00', '2026-03-24 13:33:00'),
(6, 26, 6, 10, 'Noted. I will be there on time and ready for opening prep tasks.', NULL, 1, '2026-03-24 13:36:00', '2026-03-24 13:40:00'),
(7, 8, 30, 14, 'Great work during the event run. Final attendance was submitted today.', NULL, 1, '2026-03-25 09:10:00', '2026-03-25 09:15:00'),
(8, 30, 8, 14, 'Thank you for the opportunity. Please let me know if there are future events.', NULL, 1, '2026-03-25 09:20:00', '2026-03-25 09:30:00'),
(9, 11, 36, 20, 'Bring your own hand tools for day one while we process site-issued kits.', NULL, 1, '2026-03-25 13:00:00', '2026-03-25 13:12:00'),
(10, 36, 11, 20, 'Understood. I have complete basic tools and safety gear ready.', NULL, 1, '2026-03-25 13:15:00', '2026-03-25 13:21:00'),
(11, 13, 39, 23, 'Please arrive at 7:45 AM for detailing bay orientation and checklist briefing.', NULL, 0, '2026-03-26 08:50:00', NULL),
(12, 39, 13, 23, 'Confirmed. I will be at the service bay before orientation starts.', NULL, 0, '2026-03-26 08:55:00', NULL),
(13, 16, 45, 29, 'Uniform fitting is scheduled on Friday afternoon at the HR office.', NULL, 1, '2026-03-26 10:40:00', '2026-03-26 10:48:00'),
(14, 45, 16, 29, 'Thanks, I will attend the fitting and submit remaining requirements.', NULL, 1, '2026-03-26 10:50:00', '2026-03-26 10:52:00'),
(15, 3, 50, 4, 'Dispatch orientation for riders starts tomorrow at 7:30 AM sharp.', NULL, 1, '2026-03-26 11:05:00', '2026-03-26 11:16:00'),
(16, 50, 3, 4, 'Noted. I will complete my route app setup tonight before orientation.', NULL, 1, '2026-03-26 11:18:00', '2026-03-26 11:21:00'),
(17, 4, 18, 6, 'Please send your updated ID so we can finalize your promo booth contract.', NULL, 0, '2026-03-26 12:40:00', NULL),
(18, 18, 4, 6, 'I will send a clear copy this afternoon. Thank you for the reminder.', NULL, 0, '2026-03-26 12:44:00', NULL),
(19, 10, 33, 17, 'Call scripts and lead sheet templates are now in your onboarding folder.', NULL, 1, '2026-03-27 08:20:00', '2026-03-27 08:30:00'),
(20, 33, 10, 17, 'Received. I will review all scripts before my first production calls.', NULL, 1, '2026-03-27 08:32:00', '2026-03-27 08:37:00');

INSERT INTO notifications
(notification_id, user_id, notification_type, title, message, related_id, related_type, is_read, action_url, created_at, read_at)
VALUES
(1, 17, 'application', 'Application Approved', 'Your application for Weekend Event Setup Assistant was approved.', 1, 'application', 1, 'job-details.php?id=1', '2026-03-24 10:05:00', '2026-03-24 10:20:00'),
(2, 2, 'application', 'New Applicant', 'John Paulo Rivera accepted your offer and signed the contract.', 1, 'application', 1, 'dashboard-employer.php', '2026-03-24 10:08:00', '2026-03-24 10:30:00'),
(3, 19, 'application', 'Application Approved', 'Your application for Online Seller Chat Support was approved.', 3, 'application', 1, 'job-details.php?id=3', '2026-03-24 11:25:00', '2026-03-24 11:45:00'),
(4, 3, 'application', 'Offer Accepted', 'Kevin Dominic Tan confirmed the chat support role.', 3, 'application', 1, 'dashboard-employer.php', '2026-03-24 11:28:00', '2026-03-24 11:50:00'),
(5, 21, 'application', 'Application Update', 'Your application for Salon Reception and Cashier was not selected.', 5, 'application', 1, 'job-details.php?id=5', '2026-03-25 09:50:00', '2026-03-25 10:15:00'),
(6, 23, 'application', 'Application Approved', 'You were approved for Hardware Store Sales Associate.', 7, 'application', 1, 'job-details.php?id=7', '2026-03-24 16:05:00', '2026-03-24 16:15:00'),
(7, 24, 'application', 'Application Withdrawn', 'You withdrew your Residential Painter Helper application.', 8, 'application', 1, 'job-details.php?id=8', '2026-03-24 09:12:00', '2026-03-24 09:15:00'),
(8, 26, 'application', 'Application Approved', 'You were approved for Part-time Kitchen Prep Assistant.', 10, 'application', 1, 'job-details.php?id=10', '2026-03-24 10:35:00', '2026-03-24 10:50:00'),
(9, 28, 'application', 'Application Update', 'Your application for Mobile Phone Repair Assistant was not selected.', 12, 'application', 0, 'job-details.php?id=12', '2026-03-25 13:05:00', NULL),
(10, 29, 'application', 'Application Approved', 'You were selected for Laundry Shop Attendant.', 13, 'application', 1, 'job-details.php?id=13', '2026-03-25 10:15:00', '2026-03-25 10:30:00'),
(11, 30, 'application', 'Completed Job', 'Your Event Registration Desk assignment has been marked completed.', 14, 'application', 1, 'dashboard-worker.php', '2026-03-25 18:10:00', '2026-03-25 18:15:00'),
(12, 33, 'application', 'Application Approved', 'You were approved for Home-based Telesales Caller.', 17, 'application', 1, 'job-details.php?id=17', '2026-03-25 11:38:00', '2026-03-25 12:00:00'),
(13, 34, 'application', 'Application Withdrawn', 'Your School Canteen Assistant application was withdrawn.', 18, 'application', 1, 'job-details.php?id=18', '2026-03-25 09:23:00', '2026-03-25 09:30:00'),
(14, 36, 'application', 'Application Approved', 'You were approved for Building Maintenance Helper.', 20, 'application', 1, 'job-details.php?id=20', '2026-03-25 12:25:00', '2026-03-25 12:40:00'),
(15, 38, 'application', 'Application Update', 'Your Souvenir Store Cashier application was not selected.', 22, 'application', 0, 'job-details.php?id=22', '2026-03-25 14:15:00', NULL),
(16, 39, 'application', 'Application Approved', 'You were approved for Auto Detailer Assistant.', 23, 'application', 0, 'job-details.php?id=23', '2026-03-25 16:05:00', NULL),
(17, 41, 'application', 'Application Approved', 'You were approved for Rice Mill Operations Helper.', 25, 'application', 1, 'job-details.php?id=25', '2026-03-26 09:35:00', '2026-03-26 09:50:00'),
(18, 44, 'application', 'Application Update', 'Your Small Engine Mechanic Helper application was not selected.', 28, 'application', 0, 'job-details.php?id=28', '2026-03-26 15:45:00', NULL),
(19, 45, 'application', 'Application Approved', 'You were approved for Hotel Housekeeping Assistant.', 29, 'application', 1, 'job-details.php?id=29', '2026-03-26 10:00:00', '2026-03-26 10:15:00'),
(20, 50, 'application', 'Application Approved', 'You were approved for Delivery Rider for Same-Day Parcels.', 34, 'application', 1, 'job-details.php?id=4', '2026-03-26 10:05:00', '2026-03-26 10:22:00'),
(21, 4, 'message', 'New Message', 'You received a message from Aira Mae Castillo.', 17, 'message', 0, 'messages.php', '2026-03-26 12:41:00', NULL),
(22, 3, 'message', 'New Message', 'You received a message from Lea Mendoza about rider onboarding.', 15, 'message', 1, 'messages.php', '2026-03-26 11:20:00', '2026-03-26 11:25:00'),
(23, 1, 'system', 'High Engagement Post', 'Your latest skill workshop post reached over 300 views.', 12, 'post', 1, 'skill-learn.php', '2026-03-30 09:00:00', '2026-03-30 09:10:00'),
(24, 17, 'system', 'Profile Reminder', 'Add one more verified skill to improve job matching results.', NULL, NULL, 0, 'dashboard-worker.php', '2026-03-30 12:15:00', NULL),
(25, 2, 'system', 'Posting Tip', 'Jobs with complete schedules and clear pay details receive more applications.', NULL, NULL, 0, 'post-job.php', '2026-03-30 13:22:00', NULL);

INSERT INTO user_interactions
(interaction_id, user_id, interaction_type, job_id, post_id, skill_tag, interaction_weight, created_at)
VALUES
(1, 17, 'view', 1, NULL, 'events setup', 1.00, '2026-03-23 08:00:00'),
(2, 17, 'apply', 1, NULL, 'events setup', 1.50, '2026-03-23 08:10:00'),
(3, 18, 'view', 2, NULL, 'data entry', 1.00, '2026-03-23 08:40:00'),
(4, 18, 'save', 6, NULL, 'retail sales', 1.20, '2026-03-23 12:10:00'),
(5, 19, 'view', 3, NULL, 'chat support', 1.00, '2026-03-23 09:55:00'),
(6, 19, 'apply', 3, NULL, 'chat support', 1.50, '2026-03-23 10:15:00'),
(7, 20, 'view', 4, NULL, 'delivery', 1.00, '2026-03-23 11:00:00'),
(8, 20, 'apply', 4, NULL, 'delivery', 1.50, '2026-03-23 11:10:00'),
(9, 21, 'view', 5, NULL, 'front desk', 1.00, '2026-03-23 13:20:00'),
(10, 21, 'apply', 5, NULL, 'beauty operations', 1.50, '2026-03-23 13:40:00'),
(11, 22, 'view', 6, NULL, 'promo sales', 1.00, '2026-03-23 13:50:00'),
(12, 22, 'apply', 6, NULL, 'promo sales', 1.50, '2026-03-23 14:05:00'),
(13, 23, 'view', 7, NULL, 'hardware sales', 1.00, '2026-03-23 15:00:00'),
(14, 23, 'apply', 7, NULL, 'hardware sales', 1.50, '2026-03-23 15:20:00'),
(15, 24, 'view', 8, NULL, 'painting', 1.00, '2026-03-23 15:45:00'),
(16, 24, 'apply', 8, NULL, 'painting', 1.50, '2026-03-23 16:10:00'),
(17, 25, 'view', 9, NULL, 'service crew', 1.00, '2026-03-23 16:20:00'),
(18, 25, 'apply', 9, NULL, 'service crew', 1.50, '2026-03-23 16:45:00'),
(19, 26, 'view', 10, NULL, 'kitchen prep', 1.00, '2026-03-23 17:05:00'),
(20, 26, 'apply', 10, NULL, 'kitchen prep', 1.50, '2026-03-23 17:30:00'),
(21, 27, 'view', 11, NULL, 'messenger', 1.00, '2026-03-24 07:45:00'),
(22, 28, 'view', 12, NULL, 'phone repair', 1.00, '2026-03-24 08:45:00'),
(23, 29, 'apply', 13, NULL, 'laundry operations', 1.50, '2026-03-24 09:55:00'),
(24, 30, 'apply', 14, NULL, 'event registration', 1.50, '2026-03-05 11:20:00'),
(25, 31, 'view', 15, NULL, 'packing', 1.00, '2026-03-24 12:40:00'),
(26, 32, 'view', 16, NULL, 'retail sales', 1.00, '2026-03-24 13:30:00'),
(27, 33, 'apply', 17, NULL, 'telesales', 1.50, '2026-03-24 14:30:00'),
(28, 34, 'apply', 18, NULL, 'canteen operations', 1.50, '2026-03-24 15:00:00'),
(29, 35, 'view', 19, NULL, 'warehouse operations', 1.00, '2026-03-24 15:20:00'),
(30, 36, 'apply', 20, NULL, 'maintenance', 1.50, '2026-03-24 16:10:00'),
(31, 37, 'view', 21, NULL, 'booking coordination', 1.00, '2026-03-24 16:30:00'),
(32, 38, 'view', 22, NULL, 'cashiering', 1.00, '2026-03-24 17:00:00'),
(33, 39, 'apply', 23, NULL, 'auto detailing', 1.50, '2026-03-24 17:40:00'),
(34, 40, 'view', 24, NULL, 'market sales', 1.00, '2026-03-24 17:55:00'),
(35, 41, 'apply', 25, NULL, 'machine operation', 1.50, '2026-03-25 08:20:00'),
(36, 42, 'apply', 26, NULL, 'caregiving', 1.50, '2026-03-25 08:55:00'),
(37, 43, 'save', 27, NULL, 'dispatching', 1.20, '2026-03-25 09:31:00'),
(38, 44, 'apply', 28, NULL, 'mechanic helper', 1.50, '2026-03-25 10:15:00'),
(39, 45, 'apply', 29, NULL, 'housekeeping', 1.50, '2026-03-25 11:40:00'),
(40, 46, 'apply', 30, NULL, 'sound setup', 1.50, '2026-03-25 12:05:00');

INSERT INTO transactions
(transaction_id, user_id, transaction_type, amount, job_id, application_id, description, status, created_at, completed_at)
VALUES
(1, 17, 'advance', 500.00, 1, 1, 'Advance payout for event setup onboarding', 'completed', '2026-03-24 10:45:00', '2026-03-24 11:00:00'),
(2, 19, 'advance', 2000.00, 3, 3, 'First month partial advance for chat support role', 'completed', '2026-03-24 11:55:00', '2026-03-24 12:05:00'),
(3, 23, 'deposit', 1500.00, NULL, NULL, 'Wallet top-up for transport and mobile load', 'completed', '2026-03-24 16:30:00', '2026-03-24 16:35:00'),
(4, 26, 'advance', 400.00, 10, 10, 'Kitchen prep onboarding allowance', 'completed', '2026-03-24 10:58:00', '2026-03-24 11:02:00'),
(5, 30, 'payment', 13280.00, 14, 14, 'Final payout for completed event registration assignment', 'completed', '2026-03-26 18:10:00', '2026-03-26 18:20:00'),
(6, 36, 'advance', 1000.00, 20, 20, 'Building maintenance starter allowance', 'completed', '2026-03-25 12:50:00', '2026-03-25 13:00:00'),
(7, 41, 'advance', 500.00, 25, 25, 'Rice mill job transport allowance', 'completed', '2026-03-26 09:40:00', '2026-03-26 09:44:00'),
(8, 45, 'advance', 700.00, 29, 29, 'Housekeeping uniform and transport support', 'completed', '2026-03-26 10:25:00', '2026-03-26 10:31:00'),
(9, 50, 'advance', 1000.00, 4, 34, 'Delivery rider fuel allowance', 'completed', '2026-03-26 10:35:00', '2026-03-26 10:40:00'),
(10, 2, 'withdrawal', 5000.00, NULL, NULL, 'Employer wallet withdrawal to bank account', 'completed', '2026-03-27 09:20:00', '2026-03-27 09:45:00'),
(11, 11, 'refund', 700.00, 18, 18, 'Refund due to cancelled canteen listing', 'completed', '2026-03-27 10:10:00', '2026-03-27 10:20:00'),
(12, 33, 'deposit', 1000.00, NULL, NULL, 'Wallet top-up for internet and call credits', 'pending', '2026-03-31 08:45:00', NULL);
