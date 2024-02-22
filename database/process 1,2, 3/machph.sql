-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 29, 2024 at 08:26 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `machph`
--

-- --------------------------------------------------------

--
-- Table structure for table `approvals`
--

CREATE TABLE `approvals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `approvals`
--

INSERT INTO `approvals` (`id`, `user_id`, `car_id`, `status`, `reason`, `timestamp`) VALUES
(53, 51, 50, 1, '', '2024-01-29 19:15:47'),
(54, 51, 51, 0, 'No available parts', '2024-01-29 19:16:25');

-- --------------------------------------------------------

--
-- Table structure for table `car`
--

CREATE TABLE `car` (
  `car_id` int(11) NOT NULL,
  `plateno` varchar(20) NOT NULL,
  `manufacturer` varchar(50) NOT NULL,
  `carmodel` varchar(50) NOT NULL,
  `year` int(11) NOT NULL,
  `bodyno` varchar(20) NOT NULL,
  `enginecc` int(11) NOT NULL,
  `gas` varchar(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `car`
--

INSERT INTO `car` (`car_id`, `plateno`, `manufacturer`, `carmodel`, `year`, `bodyno`, `enginecc`, `gas`, `user_id`) VALUES
(50, 'dwdwqd', 'Toyota', 'Fortuner', 2020, 'dcwqd', 1499, 'Diesel', 51),
(51, 'dfqwcqw', 'Honda', 'Civic', 2020, 'qr3q1r3qwdc', 1499, 'Premium', 51);

-- --------------------------------------------------------

--
-- Table structure for table `manager`
--

CREATE TABLE `manager` (
  `manager_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'manager',
  `user_id` int(11) DEFAULT NULL,
  `car_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `manager`
--

INSERT INTO `manager` (`manager_id`, `username`, `password`, `role`, `user_id`, `car_id`, `service_id`, `email`) VALUES
(1, 'slime', '$2y$10$TZAB3sN3DQvvXf/geRglu.DvNkW68pCZktKvQvlyzopUyaEmQS75S', 'manager', NULL, NULL, NULL, 'slime@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `mechadata`
--

CREATE TABLE `mechadata` (
  `mechaid` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `middlename` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `employment` enum('full time','part time','intern/temporary') DEFAULT NULL,
  `jobrole` set('General automotive mechanic','Brake and transmission technicians','Small engine mechanic','Tire mechanics') DEFAULT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mechanic`
--

CREATE TABLE `mechanic` (
  `mechanic_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mechanic`
--

INSERT INTO `mechanic` (`mechanic_id`, `name`, `specialization`, `contact_number`, `email`, `hire_date`, `active`) VALUES
(1, 'Hikar - General Mechanic', NULL, NULL, NULL, NULL, 1),
(2, 'Haruko Service Maintenance', NULL, NULL, NULL, NULL, 1),
(3, 'Kizaru - Small Engine', NULL, NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `mechanic_assignment`
--

CREATE TABLE `mechanic_assignment` (
  `assignment_id` int(11) NOT NULL,
  `serviceno` int(11) DEFAULT NULL,
  `mechanic_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mechanic_assignment`
--

INSERT INTO `mechanic_assignment` (`assignment_id`, `serviceno`, `mechanic_id`) VALUES
(10, NULL, 1),
(11, NULL, 2),
(12, NULL, 3),
(13, 14, 1),
(14, 16, 2),
(15, 15, 3),
(16, 20, 2);

-- --------------------------------------------------------

--
-- Table structure for table `repair`
--

CREATE TABLE `repair` (
  `repairid` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `plateno` varchar(255) DEFAULT NULL,
  `problem` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `repair`
--

INSERT INTO `repair` (`repairid`, `user_id`, `plateno`, `problem`, `diagnosis`, `remarks`) VALUES
(155, 51, '50', 'Engine Low Power', 'Mass air flow', NULL),
(156, 51, '50', 'Engine Low Power', 'Fuel system', NULL),
(157, 51, '51', 'Engine Overhaul', 'Piston ring', NULL),
(158, 51, '51', 'Engine Overhaul', 'Head gaskit', NULL),
(159, 51, '51', 'Engine Overhaul', 'Oil circulation', NULL),
(160, 51, '51', 'Engine Low Power', 'Mass air flow', NULL),
(161, 51, '51', 'Engine Low Power', 'Throttle body', NULL),
(162, 51, '51', 'Engine Low Power', 'Fuel system', NULL),
(163, 51, '51', 'Electrical Problem', 'Spark plugs', NULL),
(164, 51, '51', 'Electrical Problem', 'Ignition system', NULL),
(165, 51, '51', 'Electrical Problem', 'Electronic module', NULL),
(166, 51, '51', 'Battery', 'Battery voltage', NULL),
(167, 51, '51', 'Battery', 'Battery terminals', NULL),
(168, 51, '51', 'Light', 'Check bulbs', NULL),
(169, 51, '51', 'Light', 'Inspect fuses', NULL),
(170, 51, '51', 'Oil', 'Oil level', NULL),
(171, 51, '51', 'Oil', 'Change oil', NULL),
(172, 51, '51', 'Water', 'Colant level', NULL),
(173, 51, '51', 'Water', 'Radiator cap', NULL),
(174, 51, '51', 'Brake', 'Brake fluid', NULL),
(175, 51, '51', 'Brake', 'Brake pad', NULL),
(176, 51, '51', 'Air', 'Air intake system', NULL),
(177, 51, '51', 'Air', 'HVAC system', NULL),
(178, 51, '51', 'Air', 'Temperature control', NULL),
(179, 51, '51', 'Gas', 'Fuel system', NULL),
(180, 51, '51', 'Gas', 'Fuel injector', NULL),
(181, 51, '51', 'Tire', 'Wheel alignment', NULL),
(182, 51, '51', 'Tire', 'Tire repair', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `service`
--

CREATE TABLE `service` (
  `serviceno` int(11) NOT NULL,
  `eo` varchar(255) DEFAULT NULL,
  `elp` varchar(255) DEFAULT NULL,
  `ep` varchar(255) DEFAULT NULL,
  `battery` varchar(255) DEFAULT NULL,
  `light` varchar(255) DEFAULT NULL,
  `oil` varchar(255) DEFAULT NULL,
  `water` varchar(255) DEFAULT NULL,
  `brake` varchar(255) DEFAULT NULL,
  `air` varchar(255) DEFAULT NULL,
  `gas` varchar(255) DEFAULT NULL,
  `tire` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `car_id` int(11) DEFAULT NULL,
  `service_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service`
--

INSERT INTO `service` (`serviceno`, `eo`, `elp`, `ep`, `battery`, `light`, `oil`, `water`, `brake`, `air`, `gas`, `tire`, `user_id`, `car_id`, `service_name`) VALUES
(113, NULL, '2', NULL, '1', '1', '1', '1', '1', '1', '1', '1', 51, 50, NULL),
(114, '1', '2', '3', '3', '3', '2', '2', '2', '2', '2', '3', 51, 51, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `middlename` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `homeaddress` varchar(255) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `image` varchar(100) NOT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `municipality` varchar(255) DEFAULT NULL,
  `zipcode` varchar(255) DEFAULT NULL,
  `role` varchar(255) NOT NULL,
  `manager_role` varchar(20) DEFAULT 'none',
  `employment` enum('full time','part time','intern/temporary') DEFAULT NULL,
  `jobrole` set('General automotive mechanic','Brake and transmission technicians','Small engine mechanic','Tire mechanics') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `name`, `firstname`, `middlename`, `lastname`, `homeaddress`, `email`, `password`, `image`, `barangay`, `province`, `municipality`, `zipcode`, `role`, `manager_role`, `employment`, `jobrole`) VALUES
(48, 'jhon', 'jhon', 'lubiano', 'CABRERA', 'Purok 3A Lobugan', 'jhon@gmail.com', '4c25b32a72699ed712dfa80df77fc582', '3d82f7d50a2b767e9724871ef7ec2922.jpg', 'lubogan', 'DAVAO DEL SUR', 'Davao city', '8000', 'mechanic', '', '', 'Small engine mechanic'),
(50, 'slime', 'slime', 'af', 'fesafeq', 'Purok 3A Lobugan', 'slime@gmail.com', 'f79f0528037f833a5e9901a26490be94', '61a108e8b6ce09b5f52edebb64b53e5f.jpg', 'lubogan', 'DAVAO DEL SUR', 'Davao city', '8000', 'manager', 'auto_electrician', '', ''),
(51, 'lol', 'lol', 'dc', 'gwe', '8000', 'lol@gmail.com', '9cdfb439c7876e703e307864c9167a15', '3d82f7d50a2b767e9724871ef7ec2922.jpg', 'lubogan', 'Davao del sur', 'Davao city', '8000', 'user', '', '', ''),
(52, 'glorifel', 'glorifel', 'lubiano', 'cabrera', 'Purok 3a', 'glorifel@gmail.com', 'df61f9d1387a804b2cf40e3463e87731', '61a108e8b6ce09b5f52edebb64b53e5f.jpg', 'Lubogan', 'Davao del sur', 'Davao city', '8000', 'user', '', '', ''),
(53, 'dang', 'dang', 'dd', 'dang', 'Purok 3A Lobugan', 'dang@fgmail.com', '202cb962ac59075b964b07152d234b70', '', 'lubogan', 'DAVAO DEL SUR', 'Davao city', '9999', 'mechanic', '', '', ''),
(54, 'snow', 'snow', 'f', 'wfe', '8000', 'snow@gmail.com', '2b93fbdf27d43547bec8794054c28e00', '29badfa51eb5ac0040df9cef60be29e5.jpg', 'lubogan', 'DAVAO DEL SUR', 'Davao city', '8000', 'user', '', '', ''),
(55, 'rex', 'rex', 'adf', 'asf', '8000', 'rex@gmail.com', '6b4023d367b91c97f19597c4069337d3', '3d82f7d50a2b767e9724871ef7ec2922.jpg', 'lubogan', 'Davao del sur', 'Davao city', '8000', 'user', '', '', ''),
(56, 'erwin', 'erwin', 'R', 'acedillo', '8000', 'erwin@gmail.com', '785f0b13d4daf8eee0d11195f58302a4', '3d82f7d50a2b767e9724871ef7ec2922.jpg', 'lubogan', 'Davao del sur', 'Davao city', '8000', 'user', '', '', ''),
(57, 'hikaru', 'JHON REXEY', 'Lubiano', 'CABRERA', 'Purok 3A Lobugan', 'hikaru@gmail.com', 'ec0c02c2884ec60d59cb38ec711e34f4', '29badfa51eb5ac0040df9cef60be29e5.jpg', 'lubogan', 'DAVAO DEL SUR', 'DAVAO CITY', '8000', 'user', '', '', ''),
(58, 'laiza', 'laiza', 'Tecson', 'Cabrera', '8000', 'laiza@gmail.com', '1a9dac239a6f71029e2f769b882986b9', 'instamimi.png', 'Bago oshiro', 'Davao del sur', 'Davao city', '8000', 'user', '', '', ''),
(59, 'x', 'x', 'x', 'x', 'x', 'x@gmail.com', '9dd4e461268c8034f5c8564e155c67a6', '3d82f7d50a2b767e9724871ef7ec2922.jpg', 'lubogan', 'Davao del sur', 'Davao city', '8000', 'user', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `validation`
--

CREATE TABLE `validation` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `car_id` int(11) DEFAULT NULL,
  `status` enum('valid','invalid') DEFAULT 'valid',
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `validation`
--

INSERT INTO `validation` (`id`, `user_id`, `car_id`, `status`, `comment`, `created_at`) VALUES
(18, 51, 50, 'valid', 'null', '2024-01-29 19:20:23'),
(19, 51, 51, 'invalid', 'Already have a parts', '2024-01-29 19:21:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `approvals`
--
ALTER TABLE `approvals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_car` (`user_id`,`car_id`);

--
-- Indexes for table `car`
--
ALTER TABLE `car`
  ADD PRIMARY KEY (`car_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `manager`
--
ALTER TABLE `manager`
  ADD PRIMARY KEY (`manager_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `car_id` (`car_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `mechadata`
--
ALTER TABLE `mechadata`
  ADD PRIMARY KEY (`mechaid`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `mechanic`
--
ALTER TABLE `mechanic`
  ADD PRIMARY KEY (`mechanic_id`);

--
-- Indexes for table `mechanic_assignment`
--
ALTER TABLE `mechanic_assignment`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `serviceno` (`serviceno`),
  ADD KEY `mechanic_id` (`mechanic_id`);

--
-- Indexes for table `repair`
--
ALTER TABLE `repair`
  ADD PRIMARY KEY (`repairid`);

--
-- Indexes for table `service`
--
ALTER TABLE `service`
  ADD PRIMARY KEY (`serviceno`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `car_id` (`car_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `validation`
--
ALTER TABLE `validation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `car_id` (`car_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `approvals`
--
ALTER TABLE `approvals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `car`
--
ALTER TABLE `car`
  MODIFY `car_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `manager`
--
ALTER TABLE `manager`
  MODIFY `manager_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `mechadata`
--
ALTER TABLE `mechadata`
  MODIFY `mechaid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `mechanic`
--
ALTER TABLE `mechanic`
  MODIFY `mechanic_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `mechanic_assignment`
--
ALTER TABLE `mechanic_assignment`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `repair`
--
ALTER TABLE `repair`
  MODIFY `repairid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=183;

--
-- AUTO_INCREMENT for table `service`
--
ALTER TABLE `service`
  MODIFY `serviceno` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `validation`
--
ALTER TABLE `validation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `car`
--
ALTER TABLE `car`
  ADD CONSTRAINT `car_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `manager`
--
ALTER TABLE `manager`
  ADD CONSTRAINT `manager_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `manager_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `car` (`car_id`),
  ADD CONSTRAINT `manager_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `service` (`serviceno`);

--
-- Constraints for table `mechadata`
--
ALTER TABLE `mechadata`
  ADD CONSTRAINT `mechadata_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `mechanic_assignment`
--
ALTER TABLE `mechanic_assignment`
  ADD CONSTRAINT `mechanic_assignment_ibfk_1` FOREIGN KEY (`serviceno`) REFERENCES `service` (`serviceno`),
  ADD CONSTRAINT `mechanic_assignment_ibfk_2` FOREIGN KEY (`mechanic_id`) REFERENCES `mechanic` (`mechanic_id`);

--
-- Constraints for table `service`
--
ALTER TABLE `service`
  ADD CONSTRAINT `service_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `service_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `car` (`car_id`);

--
-- Constraints for table `validation`
--
ALTER TABLE `validation`
  ADD CONSTRAINT `validation_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `validation_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `car` (`car_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
