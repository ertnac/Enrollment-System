-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 08, 2025 at 01:35 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `edutrack`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_code` varchar(20) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `units` int(11) NOT NULL,
  `prerequisites` varchar(100) DEFAULT NULL,
  `program_name` varchar(100) NOT NULL,
  `year_level` enum('1st Year','2nd Year','3rd Year','4th Year') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_code`, `course_name`, `units`, `prerequisites`, `program_name`, `year_level`) VALUES
('ACC101', 'Financial Accounting and Reporting', 3, NULL, 'Accountancy', '1st Year'),
('ACC102', 'Cost Accounting', 3, 'ACC101', 'Accountancy', '2nd Year'),
('ACC103', 'Auditing Theory', 3, 'ACC101', 'Accountancy', '2nd Year'),
('ACC104', 'Management Accounting', 3, 'ACC101', 'Accountancy', '3rd Year'),
('ACC105', 'Taxation', 3, 'ACC101', 'Accountancy', '3rd Year'),
('ACC106', 'Accounting Information Systems', 3, 'ACC101', 'Accountancy', '3rd Year'),
('AIS102', 'Database Management for Accountants', 3, 'ACC101', 'Accounting Information System', '2nd Year'),
('AIS103', 'Accounting Software Applications', 3, 'AIS102', 'Accounting Information System', '2nd Year'),
('AIS104', 'Systems Analysis and Design', 3, 'AIS102', 'Accounting Information System', '3rd Year'),
('AIS105', 'IT Governance and Control', 3, 'AIS102', 'Accounting Information System', '3rd Year'),
('AIS106', 'E-Commerce for Accountants', 3, 'AIS102', 'Accounting Information System', '3rd Year'),
('AT102', 'Automotive Engine Systems', 3, NULL, 'Automotive Technology', '1st Year'),
('AT103', 'Electrical Systems', 3, 'AT102', 'Automotive Technology', '2nd Year'),
('AT104', 'Transmission Systems', 3, 'AT102', 'Automotive Technology', '2nd Year'),
('AT105', 'Automotive Maintenance', 3, 'AT102', 'Automotive Technology', '3rd Year'),
('AT106', 'Teaching Automotive Technology', 3, 'AT102', 'Automotive Technology', '3rd Year'),
('COMM101', 'Introduction to Communication', 3, NULL, 'Communication', '1st Year'),
('COMM102', 'Media and Society', 3, 'COMM101', 'Communication', '2nd Year'),
('COMM103', 'Public Relations', 3, 'COMM101', 'Communication', '2nd Year'),
('COMM104', 'Broadcast Journalism', 3, 'COMM101', 'Communication', '3rd Year'),
('COMM105', 'Digital Media Production', 3, 'COMM101', 'Communication', '3rd Year'),
('COMM106', 'Communication Research', 3, 'COMM101', 'Communication', '3rd Year'),
('CP102', 'Advanced Programming', 3, 'IT102', 'Computer Programming', '2nd Year'),
('CP103', 'Database Systems', 3, 'CP102', 'Computer Programming', '2nd Year'),
('CP104', 'Web Development', 3, 'CP102', 'Computer Programming', '3rd Year'),
('CP105', 'Software Engineering', 3, 'CP102', 'Computer Programming', '3rd Year'),
('CP106', 'Teaching Computer Programming', 3, 'CP102', 'Computer Programming', '3rd Year'),
('CPE101', 'Introduction to Computer Engineering', 3, NULL, 'Computer Engineering', '1st Year'),
('CPE102', 'Digital Logic Design', 3, 'CPE101', 'Computer Engineering', '2nd Year'),
('CPE103', 'Computer Organization', 3, 'CPE101', 'Computer Engineering', '2nd Year'),
('CPE104', 'Embedded Systems', 3, 'CPE101', 'Computer Engineering', '3rd Year'),
('CPE105', 'VLSI Design', 3, 'CPE101', 'Computer Engineering', '3rd Year'),
('CPE106', 'Computer Architecture', 3, 'CPE101', 'Computer Engineering', '3rd Year'),
('ECON102', 'Microeconomic Theory', 3, NULL, 'Economics', '1st Year'),
('ECON103', 'Macroeconomic Theory', 3, 'ECON102', 'Economics', '2nd Year'),
('ECON104', 'Econometrics', 3, 'ECON102', 'Economics', '2nd Year'),
('ECON105', 'Development Economics', 3, 'ECON102', 'Economics', '3rd Year'),
('ECON106', 'International Economics', 3, 'ECON102', 'Economics', '3rd Year'),
('EDUC101', 'Foundations of Education', 3, NULL, 'Education', '1st Year'),
('EDUC102', 'Child and Adolescent Development', 3, 'EDUC101', 'Education', '1st Year'),
('EDUC103', 'Principles of Teaching', 3, 'EDUC101', 'Education', '2nd Year'),
('EDUC104', 'Assessment of Learning', 3, 'EDUC103', 'Education', '2nd Year'),
('EDUC105', 'Educational Technology', 3, 'EDUC103', 'Education', '3rd Year'),
('EDUC106', 'Foundations of Special Education', 3, 'EDUC101', 'Education', '3rd Year'),
('ENG101', 'English for Academic Purposes', 3, NULL, 'English', '1st Year'),
('ENG102', 'Introduction to Linguistics', 3, 'ENG101', 'English', '2nd Year'),
('ENG103', 'Teaching English as a Second Language', 3, 'ENG101', 'English', '2nd Year'),
('ENG104', 'World Literature', 3, 'ENG101', 'English', '3rd Year'),
('ENG105', 'Creative Writing', 3, 'ENG101', 'English', '3rd Year'),
('ENG106', 'Speech and Oral Communication', 3, 'ENG101', 'English', '3rd Year'),
('ENT102', 'Entrepreneurial Finance', 3, NULL, 'Entrepreneurship', '1st Year'),
('ENT103', 'Small Business Management', 3, 'ENT102', 'Entrepreneurship', '2nd Year'),
('ENT104', 'Business Planning', 3, 'ENT102', 'Entrepreneurship', '2nd Year'),
('ENT105', 'Innovation Management', 3, 'ENT102', 'Entrepreneurship', '3rd Year'),
('ENT106', 'Social Entrepreneurship', 3, 'ENT102', 'Entrepreneurship', '3rd Year'),
('FIL102', 'Panitikang Filipino', 3, NULL, 'Filipino', '1st Year'),
('FIL103', 'Retorika', 3, 'FIL102', 'Filipino', '2nd Year'),
('FIL104', 'Pagtuturo ng Filipino', 3, 'FIL102', 'Filipino', '2nd Year'),
('FIL105', 'Wika at Kulturang Filipino', 3, 'FIL102', 'Filipino', '3rd Year'),
('FIL106', 'Malikhaing Pagsulat', 3, 'FIL102', 'Filipino', '3rd Year'),
('FM101', 'Financial Accounting', 3, NULL, 'Financial Management', '1st Year'),
('FM102', 'Financial Management', 3, 'FM101', 'Financial Management', '2nd Year'),
('FM103', 'Investment Analysis', 3, 'FM101', 'Financial Management', '2nd Year'),
('FM104', 'Risk Management', 3, 'FM101', 'Financial Management', '3rd Year'),
('FM105', 'International Finance', 3, 'FM101', 'Financial Management', '3rd Year'),
('FM106', 'Financial Markets', 3, 'FM101', 'Financial Management', '3rd Year'),
('FSM102', 'Food Preparation', 3, NULL, 'Food Service Management', '1st Year'),
('FSM103', 'Beverage Management', 3, 'FSM102', 'Food Service Management', '2nd Year'),
('FSM104', 'Restaurant Operations', 3, 'FSM102', 'Food Service Management', '2nd Year'),
('FSM105', 'Culinary Arts', 3, 'FSM102', 'Food Service Management', '3rd Year'),
('FSM106', 'Teaching Food Service', 3, 'FSM102', 'Food Service Management', '3rd Year'),
('HM101', 'Introduction to Hospitality Management', 3, NULL, 'Hospitality Management', '1st Year'),
('HM102', 'Front Office Operations', 3, 'HM101', 'Hospitality Management', '2nd Year'),
('HM103', 'Housekeeping Management', 3, 'HM101', 'Hospitality Management', '2nd Year'),
('HM104', 'Food and Beverage Service', 3, 'HM101', 'Hospitality Management', '3rd Year'),
('HM105', 'Hospitality Law', 3, 'HM101', 'Hospitality Management', '3rd Year'),
('HM106', 'Event Management', 3, 'HM101', 'Hospitality Management', '3rd Year'),
('HRD102', 'Human Resource Development', 3, NULL, 'Human Resource Development', '1st Year'),
('HRD103', 'Training and Development', 3, 'HRD102', 'Human Resource Development', '2nd Year'),
('HRD104', 'Organizational Behavior', 3, 'HRD102', 'Human Resource Development', '2nd Year'),
('HRD105', 'Compensation Management', 3, 'HRD102', 'Human Resource Development', '3rd Year'),
('HRD106', 'Labor Relations', 3, 'HRD102', 'Human Resource Development', '3rd Year'),
('IE102', 'Operations Research', 3, NULL, 'Industrial Engineering', '1st Year'),
('IE103', 'Quality Engineering', 3, 'IE102', 'Industrial Engineering', '2nd Year'),
('IE104', 'Supply Chain Management', 3, 'IE102', 'Industrial Engineering', '2nd Year'),
('IE105', 'Facilities Planning', 3, 'IE102', 'Industrial Engineering', '3rd Year'),
('IE106', 'Human Factors Engineering', 3, 'IE102', 'Industrial Engineering', '3rd Year'),
('IS102', 'Business Intelligence', 3, NULL, 'Information Systems', '1st Year'),
('IS103', 'Data Mining', 3, 'IS102', 'Information Systems', '2nd Year'),
('IS104', 'Predictive Analytics', 3, 'IS102', 'Information Systems', '2nd Year'),
('IS105', 'Data Visualization', 3, 'IS102', 'Information Systems', '3rd Year'),
('IS106', 'Big Data Technologies', 3, 'IS102', 'Information Systems', '3rd Year'),
('IS202', 'Information Security', 3, NULL, 'Information Security', '1st Year'),
('IS203', 'Ethical Hacking', 3, 'IS202', 'Information Security', '2nd Year'),
('IS204', 'Cryptography', 3, 'IS202', 'Information Security', '2nd Year'),
('IS205', 'Digital Forensics', 3, 'IS202', 'Information Security', '3rd Year'),
('IS206', 'Security Compliance', 3, 'IS202', 'Information Security', '3rd Year'),
('IS302', 'Project Management Fundamentals', 3, NULL, 'IT Project Management', '1st Year'),
('IS303', 'Agile Methodologies', 3, 'IS302', 'IT Project Management', '2nd Year'),
('IS304', 'Risk Management', 3, 'IS302', 'IT Project Management', '3rd Year'),
('IS305', 'Quality Assurance', 3, 'IS302', 'IT Project Management', '3rd Year'),
('IS306', 'IT Service Management', 3, 'IS302', 'IT Project Management', '3rd Year'),
('IT101', 'Introduction to Computing', 3, NULL, 'Information Technology', '1st Year'),
('IT102', 'Computer Programming 1', 3, 'IT101', 'Information Technology', '1st Year'),
('IT103', 'Web Development Fundamentals', 3, 'IT102', 'Information Technology', '2nd Year'),
('IT104', 'Game Design Principles', 3, 'IT102', 'Information Technology', '2nd Year'),
('IT105', 'User Interface Design', 3, 'IT102', 'Information Technology', '3rd Year'),
('IT106', 'Interactive Media', 3, 'IT102', 'Information Technology', '3rd Year'),
('IT107', 'Mobile Application Development', 3, 'IT102', 'Information Technology', '3rd Year'),
('IT201', 'Data Structures and Algorithms', 3, 'IT102', 'Information Technology', '2nd Year'),
('IT202', 'Advanced Programming Concepts', 3, 'IT201', 'Information Technology', '3rd Year'),
('IT203', 'Software Testing', 3, 'IT201', 'Information Technology', '3rd Year'),
('IT204', 'Software Project Management', 3, 'IT201', 'Information Technology', '3rd Year'),
('IT205', 'Human-Computer Interaction', 3, 'IT201', 'Information Technology', '3rd Year'),
('IT206', 'Cloud Computing', 3, 'IT201', 'Information Technology', '3rd Year'),
('MA102', 'Strategic Management Accounting', 3, 'ACC101', 'Management Accounting', '2nd Year'),
('MA103', 'Performance Management', 3, 'MA102', 'Management Accounting', '2nd Year'),
('MA104', 'Business Valuation', 3, 'MA102', 'Management Accounting', '3rd Year'),
('MA105', 'Corporate Finance', 3, 'MA102', 'Management Accounting', '3rd Year'),
('MA106', 'Risk Management in Accounting', 3, 'MA102', 'Management Accounting', '3rd Year'),
('MATH102', 'Calculus 1', 3, NULL, 'Mathematics', '1st Year'),
('MATH103', 'Linear Algebra', 3, 'MATH102', 'Mathematics', '2nd Year'),
('MATH104', 'Probability and Statistics', 3, 'MATH102', 'Mathematics', '2nd Year'),
('MATH105', 'Geometry', 3, 'MATH102', 'Mathematics', '3rd Year'),
('MATH106', 'Teaching Mathematics in Secondary Schools', 3, 'MATH102', 'Mathematics', '3rd Year'),
('MM101', 'Principles of Marketing', 3, NULL, 'Marketing Management', '1st Year'),
('MM102', 'Consumer Behavior', 3, 'MM101', 'Marketing Management', '2nd Year'),
('MM103', 'Marketing Research', 3, 'MM101', 'Marketing Management', '2nd Year'),
('MM104', 'Advertising and Promotion', 3, 'MM101', 'Marketing Management', '3rd Year'),
('MM105', 'Sales Management', 3, 'MM101', 'Marketing Management', '3rd Year'),
('MM106', 'Digital Marketing', 3, 'MM101', 'Marketing Management', '3rd Year'),
('NT102', 'Network Administration', 3, NULL, 'Network Technology', '1st Year'),
('NT103', 'Network Security', 3, 'NT102', 'Network Technology', '2nd Year'),
('NT104', 'Wireless Networks', 3, 'NT102', 'Network Technology', '2nd Year'),
('NT105', 'Network Protocols', 3, 'NT102', 'Network Technology', '3rd Year'),
('NT106', 'Virtualization Technologies', 3, 'NT102', 'Network Technology', '3rd Year'),
('NURS101', 'Fundamentals of Nursing Practice', 3, NULL, 'Nursing', '1st Year'),
('NURS102', 'Anatomy and Physiology', 3, 'NURS101', 'Nursing', '2nd Year'),
('NURS103', 'Microbiology and Parasitology', 3, 'NURS101', 'Nursing', '2nd Year'),
('NURS104', 'Pharmacology', 3, 'NURS101', 'Nursing', '3rd Year'),
('NURS105', 'Community Health Nursing', 3, 'NURS101', 'Nursing', '3rd Year'),
('NURS106', 'Nursing Research', 3, 'NURS101', 'Nursing', '3rd Year'),
('OA102', 'Office Procedures', 3, NULL, 'Office Administration', '1st Year'),
('OA103', 'Records Management', 3, 'OA102', 'Office Administration', '2nd Year'),
('OA104', 'Business Communication', 3, 'OA102', 'Office Administration', '2nd Year'),
('OA105', 'Office Technology', 3, 'OA102', 'Office Administration', '3rd Year'),
('OA106', 'Administrative Management', 3, 'OA102', 'Office Administration', '3rd Year'),
('PA102', 'Public Policy Analysis', 3, NULL, 'Public Administration', '1st Year'),
('PA103', 'Local Governance', 3, 'PA102', 'Public Administration', '2nd Year'),
('PA104', 'Public Fiscal Administration', 3, 'PA102', 'Public Administration', '2nd Year'),
('PA105', 'Human Resource in Public Sector', 3, 'PA102', 'Public Administration', '3rd Year'),
('PA106', 'Ethics and Accountability', 3, 'PA102', 'Public Administration', '3rd Year'),
('PE101', 'Movement Enhancement', 3, NULL, 'Physical Education', '1st Year'),
('PE102', 'Sports Psychology', 3, 'PE101', 'Physical Education', '2nd Year'),
('PE103', 'Exercise Physiology', 3, 'PE101', 'Physical Education', '2nd Year'),
('PE104', 'Coaching Methods', 3, 'PE101', 'Physical Education', '3rd Year'),
('PE105', 'Sports Management', 3, 'PE101', 'Physical Education', '3rd Year'),
('PE106', 'Kinesiology', 3, 'PE101', 'Physical Education', '3rd Year'),
('POLSCI102', 'Political Theory', 3, NULL, 'Political Science', '1st Year'),
('POLSCI103', 'Comparative Politics', 3, 'POLSCI102', 'Political Science', '2nd Year'),
('POLSCI104', 'International Relations', 3, 'POLSCI102', 'Political Science', '2nd Year'),
('POLSCI105', 'Philippine Politics and Governance', 3, 'POLSCI102', 'Political Science', '3rd Year'),
('POLSCI106', 'Public Administration', 3, 'POLSCI102', 'Political Science', '3rd Year'),
('PSY101', 'General Psychology', 3, NULL, 'Psychology', '1st Year'),
('PSY102', 'Developmental Psychology', 3, 'PSY101', 'Psychology', '2nd Year'),
('PSY103', 'Abnormal Psychology', 3, 'PSY101', 'Psychology', '2nd Year'),
('PSY104', 'Social Psychology', 3, 'PSY101', 'Psychology', '3rd Year'),
('PSY105', 'Cognitive Psychology', 3, 'PSY101', 'Psychology', '3rd Year'),
('PSY106', 'Psychological Testing', 3, 'PSY101', 'Psychology', '3rd Year'),
('SCI102', 'General Biology', 3, NULL, 'Science', '1st Year'),
('SCI103', 'General Chemistry', 3, 'SCI102', 'Science', '2nd Year'),
('SCI104', 'Earth Science', 3, 'SCI102', 'Science', '2nd Year'),
('SCI105', 'Physics', 3, 'SCI102', 'Science', '3rd Year'),
('SCI106', 'Teaching Science in Secondary Schools', 3, 'SCI102', 'Science', '3rd Year'),
('SNED101', 'Introduction to Special Needs Education', 3, NULL, 'Special Needs Education', '1st Year'),
('SNED102', 'Assessment in Special Education', 3, 'SNED101', 'Special Needs Education', '2nd Year'),
('SNED103', 'Behavior Management', 3, 'SNED101', 'Special Needs Education', '2nd Year'),
('SNED104', 'Curriculum Adaptation', 3, 'SNED102', 'Special Needs Education', '3rd Year'),
('SNED105', 'Inclusive Education', 3, 'SNED101', 'Special Needs Education', '3rd Year'),
('SS102', 'World History', 3, NULL, 'Social Studies', '1st Year'),
('SS103', 'Philippine Government and Constitution', 3, 'SS102', 'Social Studies', '2nd Year'),
('SS104', 'Economics', 3, 'SS102', 'Social Studies', '2nd Year'),
('SS105', 'Sociology', 3, 'SS102', 'Social Studies', '3rd Year'),
('SS106', 'Teaching Social Studies', 3, 'SS102', 'Social Studies', '3rd Year'),
('TCP102', 'Teaching Methods', 3, NULL, 'Teacher Certificate Program', '1st Year'),
('TCP103', 'Classroom Management', 3, 'TCP102', 'Teacher Certificate Program', '2nd Year'),
('TCP104', 'Educational Psychology', 3, 'TCP102', 'Teacher Certificate Program', '2nd Year'),
('TCP105', 'Curriculum Development', 3, 'TCP102', 'Teacher Certificate Program', '3rd Year'),
('TCP106', 'Assessment Techniques', 3, 'TCP102', 'Teacher Certificate Program', '3rd Year'),
('TM102', 'Tourism Planning and Development', 3, NULL, 'Tourism Management', '1st Year'),
('TM103', 'Tourism Marketing', 3, 'TM102', 'Tourism Management', '2nd Year'),
('TM104', 'Ecotourism', 3, 'TM102', 'Tourism Management', '2nd Year'),
('TM105', 'Cultural Tourism', 3, 'TM102', 'Tourism Management', '3rd Year'),
('TM106', 'Tour Operations', 3, 'TM102', 'Tourism Management', '3rd Year');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`) VALUES
(4, 'College of Accountancy'),
(3, 'College of Arts and Sciences'),
(2, 'College of Business Administration'),
(1, 'College of Computer Studies and Technology'),
(8, 'College of Engineering'),
(9, 'College of Human Kinetics'),
(6, 'College of Nursing and Allied Health Sciences'),
(7, 'College of Teacher Education'),
(5, 'College of Tourism and Hospitality Management');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `program_name` varchar(100) NOT NULL,
  `date_enrolled` date NOT NULL,
  `status` enum('Active','Pending','Completed','Dropped') NOT NULL,
  `instructor_id` varchar(20) DEFAULT NULL,
  `schedule_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`enrollment_id`, `student_id`, `course_code`, `program_name`, `date_enrolled`, `status`, `instructor_id`, `schedule_id`) VALUES
(4, 'S2025-001', 'ACC101', 'Accountancy', '2025-06-08', 'Active', 'I2023-001', 3);

-- --------------------------------------------------------

--
-- Table structure for table `instructors`
--

CREATE TABLE `instructors` (
  `instructor_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `courses_count` int(11) DEFAULT 0,
  `status` enum('Active','On Leave','Inactive') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructors`
--

INSERT INTO `instructors` (`instructor_id`, `name`, `email`, `department`, `courses_count`, `status`) VALUES
('I2023-001', 'Dr. Robert Smith', 'robert.smith@university.com', 'College of Science', 3, 'Active'),
('I2023-002', 'Prof. Emily Davis', 'emily.davis@university.com', 'College of Business', 2, 'Active'),
('I2023-003', 'Dr. Michael Brown', 'michael.brown@university.com', 'College of Engineering', 4, 'On Leave'),
('I2023-004', 'Prof. Lisa Wong', 'lisa.wong@university.com', 'College of Arts and Sciences', 3, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `pending_applications`
--

CREATE TABLE `pending_applications` (
  `application_id` varchar(20) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `program_name` varchar(100) NOT NULL,
  `year_level` enum('1st Year','2nd Year','3rd Year','4th Year') NOT NULL,
  `date_submitted` date NOT NULL,
  `status` enum('Pending','Accepted','Rejected') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pending_applications`
--

INSERT INTO `pending_applications` (`application_id`, `student_id`, `student_name`, `program_name`, `year_level`, `date_submitted`, `status`) VALUES
('APP2025-001', 'S2025-001', 'James Clarence  Dela Cruz Cantre', 'Accountancy', '1st Year', '2025-06-08', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `program_name` varchar(100) NOT NULL,
  `department_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`program_name`, `department_id`) VALUES
('Information Security', 1),
('Information Systems', 1),
('Information Technology', 1),
('IT Project Management', 1),
('Network Technology', 1),
('Entrepreneurship', 2),
('Financial Management', 2),
('Human Resource Development', 2),
('Marketing Management', 2),
('Office Administration', 2),
('Communication', 3),
('Economics', 3),
('Political Science', 3),
('Psychology', 3),
('Public Administration', 3),
('Accountancy', 4),
('Accounting Information System', 4),
('Management Accounting', 4),
('Hospitality Management', 5),
('Tourism Management', 5),
('Nursing', 6),
('Automotive Technology', 7),
('Computer Programming', 7),
('Education', 7),
('English', 7),
('Filipino', 7),
('Food Service Management', 7),
('Mathematics', 7),
('Science', 7),
('Social Studies', 7),
('Special Needs Education', 7),
('Teacher Certificate Program', 7),
('Computer Engineering', 8),
('Industrial Engineering', 8),
('Physical Education', 9);

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `schedule_id` int(11) NOT NULL,
  `schedule_description` varchar(100) NOT NULL,
  `days` varchar(50) NOT NULL,
  `time_start` time NOT NULL,
  `time_end` time NOT NULL,
  `room` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`schedule_id`, `schedule_description`, `days`, `time_start`, `time_end`, `room`) VALUES
(1, 'Morning Class MWF', 'MWF', '09:00:00', '10:00:00', 'Room 101'),
(2, 'Afternoon Class TTh', 'TTh', '13:00:00', '14:30:00', 'Room 102'),
(3, 'Evening Class MWF', 'MWF', '18:00:00', '19:00:00', 'Room 103'),
(4, 'Morning Class TTh', 'TTh', '10:00:00', '11:30:00', 'Room 104');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` varchar(20) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `sex` enum('Male','Female','Other') NOT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `date_created` date NOT NULL,
  `program_name` varchar(100) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `year_level` varchar(50) DEFAULT NULL,
  `enrollment_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `last_name`, `first_name`, `middle_name`, `suffix`, `age`, `date_of_birth`, `sex`, `nationality`, `mobile_number`, `email`, `date_created`, `program_name`, `department_name`, `year_level`, `enrollment_id`) VALUES
('S2025-001', 'Cantre', 'James Clarence ', 'Dela Cruz', '', 20, '2005-04-16', 'Male', 'Filipino', '09664178646', 'jamescantre15@gmail.com', '2025-06-08', 'Accountancy', 'College of Accountancy', '1st Year', 4);

-- --------------------------------------------------------

--
-- Table structure for table `student_courses`
--

CREATE TABLE `student_courses` (
  `student_id` varchar(20) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `date_enrolled` date NOT NULL,
  `status` enum('Active','Pending','Completed','Dropped') NOT NULL DEFAULT 'Pending',
  `instructor_id` varchar(20) DEFAULT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `enrollment_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_courses`
--

INSERT INTO `student_courses` (`student_id`, `course_code`, `date_enrolled`, `status`, `instructor_id`, `schedule_id`, `enrollment_id`) VALUES
('S2025-001', 'ACC101', '2025-06-08', 'Active', 'I2023-001', 3, 4);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_code`),
  ADD KEY `program_name` (`program_name`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `department_name` (`department_name`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_code` (`course_code`),
  ADD KEY `program_name` (`program_name`),
  ADD KEY `instructor_id` (`instructor_id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `instructors`
--
ALTER TABLE `instructors`
  ADD PRIMARY KEY (`instructor_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `pending_applications`
--
ALTER TABLE `pending_applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `program_name` (`program_name`),
  ADD KEY `pending_applications_ibfk_2` (`student_id`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`program_name`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`schedule_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `program_name` (`program_name`),
  ADD KEY `students_ibfk_2` (`department_name`),
  ADD KEY `enrollment_id` (`enrollment_id`);

--
-- Indexes for table `student_courses`
--
ALTER TABLE `student_courses`
  ADD PRIMARY KEY (`student_id`,`course_code`),
  ADD KEY `student_courses_ibfk_2` (`course_code`),
  ADD KEY `instructor_id` (`instructor_id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `enrollment_id` (`enrollment_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`program_name`) REFERENCES `programs` (`program_name`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_code`) REFERENCES `courses` (`course_code`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_3` FOREIGN KEY (`program_name`) REFERENCES `programs` (`program_name`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_4` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`),
  ADD CONSTRAINT `enrollments_ibfk_5` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`schedule_id`);

--
-- Constraints for table `pending_applications`
--
ALTER TABLE `pending_applications`
  ADD CONSTRAINT `pending_applications_ibfk_1` FOREIGN KEY (`program_name`) REFERENCES `programs` (`program_name`) ON DELETE CASCADE,
  ADD CONSTRAINT `pending_applications_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `programs`
--
ALTER TABLE `programs`
  ADD CONSTRAINT `programs_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`program_name`) REFERENCES `programs` (`program_name`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`department_name`) REFERENCES `departments` (`department_name`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_ibfk_3` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`enrollment_id`);

--
-- Constraints for table `student_courses`
--
ALTER TABLE `student_courses`
  ADD CONSTRAINT `student_courses_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_courses_ibfk_2` FOREIGN KEY (`course_code`) REFERENCES `courses` (`course_code`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_courses_ibfk_3` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`),
  ADD CONSTRAINT `student_courses_ibfk_4` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`schedule_id`),
  ADD CONSTRAINT `student_courses_ibfk_5` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`enrollment_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
