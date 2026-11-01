CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jamb_reg` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('applicant','admin') DEFAULT 'applicant',
  PRIMARY KEY (`id`),
  UNIQUE KEY `jamb_reg` (`jamb_reg`)
);

CREATE TABLE `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `faculty` varchar(100) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `cut_off` int(11) NOT NULL,
  `jamb_req` varchar(255) NOT NULL, 
  `olevel_req` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `jamb_score` int(11) NOT NULL,
  `jamb_subjects` text NOT NULL,
  `olevel_results` text NOT NULL,
  `first_choice_id` int(11) NOT NULL,
  `status` enum('Eligible','Not Eligible') NOT NULL,
  `recommendations` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
);

-- Insert Data from SRS (Sample subset for demonstration)
INSERT INTO `courses` (`faculty`, `course_name`, `cut_off`, `jamb_req`, `olevel_req`) VALUES
('Science and Computing', 'Computer Science', 170, 'English,Mathematics,Physics,Biology|Chemistry|Geography', 'English,Mathematics,Physics,Biology,Chemistry'),
('Science and Computing', 'Cyber Security', 170, 'English,Mathematics,Physics,Biology|Chemistry|Geography', 'English,Mathematics,Physics,Biology,Chemistry'),
('Science and Computing', 'Information System', 160, 'English,Mathematics,Physics,Biology|Chemistry|Geography', 'English,Mathematics,Physics,Biology,Chemistry'),
('Science and Computing', 'Microbiology', 170, 'English,Biology,Chemistry,Physics|Mathematics', 'English,Mathematics,Biology,Chemistry'),
('Science and Computing', 'Physics', 150, 'English,Physics,Mathematics,Chemistry|Biology', 'English,Mathematics,Physics,Biology,Chemistry'),
('Science and Computing', 'Mathematics', 150, 'English,Mathematics,Physics|Chemistry|Biology|Geography', 'English,Mathematics,Physics,Biology,Chemistry'),
('Agriculture', 'Agricultural Economics & Extension', 150, 'English,Chemistry,Biology|Agric Science,Mathematics|Physics', 'English,Mathematics,Chemistry,Biology'),
('Arts and Social Sciences', 'Mass Communication', 160, 'English,Literature in English,Arts|Social Science,Arts|Social Science', 'English,Literature in English,Arts,Social Science'),
('Administration', 'Accounting', 150, 'English,Mathematics,Economics,Social Science', 'English,Mathematics,Economics,Social Science');

-- Create Default Admin Account (Password: admin123)
INSERT INTO `users` (`jamb_reg`, `name`, `password`, `role`) VALUES 
('ADMIN001', 'Admissions Officer', '$2y$10$e.w1wW1wW1wW1wW1wW1wWuK.jK.jK.jK.jK.jK.jK.jK.jK.jK.jK', 'admin');
-- Clear existing users for a clean test
TRUNCATE TABLE `users`;

-- Insert Admin (JAMB Reg: ADMIN001)
INSERT INTO `users` (`jamb_reg`, `name`, `password`, `role`) VALUES 
('ADMIN001', 'Admissions Officer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert Applicant (JAMB Reg: 20241234)
INSERT INTO `users` (`jamb_reg`, `name`, `password`, `role`) VALUES 
('20241234', 'Innocent Kpanah', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'applicant');