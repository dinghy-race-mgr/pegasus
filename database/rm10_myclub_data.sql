-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 12, 2022 at 03:01 PM
-- Server version: 10.1.38-MariaDB
-- PHP Version: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `myclub`
--

--
-- Dumping data for table `rm_admin_uggroups`
--

INSERT INTO `rm_admin_uggroups` (`GroupID`, `Label`) VALUES
(1, 'config'),
(2, 'programme'),
(3, 'rota'),
(4, 'results'),
(5, 'opens');

--
-- Dumping data for table `rm_admin_ugmembers`
--

INSERT INTO `rm_admin_ugmembers` (`UserName`, `GroupID`) VALUES
('rmgrAdmin', -1);

--
-- Dumping data for table `rm_admin_ugrights`
--

INSERT INTO `rm_admin_ugrights` (`TableName`, `GroupID`, `AccessMask`) VALUES
('o_openmeeting', -1, 'ADESPIM'),
('o_openmeeting', 1, 'S'),
('o_openmeeting', 2, 'S'),
('o_openmeeting', 3, 'S'),
('o_openmeeting', 4, 'S'),
('o_openmeeting', 5, 'AEDSP'),
('py_analysis', -1, 'ADESPIM'),
('py_analysis', 1, 'S'),
('py_analysis', 2, 'S'),
('py_analysis', 3, 'S'),
('py_analysis', 4, 'S'),
('py_analysis', 5, 'S'),
('rm_admin_uggroups', -1, 'ADESPIM'),
('rm_admin_ugmembers', -1, 'ADESPIM'),
('rm_admin_ugrights', -1, 'ADESPIM'),
('rm_admin_users', -1, 'ADESPIM'),
('t_cfgfleet', -1, 'ADESPIM'),
('t_cfgfleet', 1, 'AEDS'),
('t_cfgfleet', 2, 'S'),
('t_cfgfleet', 3, 'S'),
('t_cfgfleet', 4, 'AEDS'),
('t_cfgfleet', 5, 'S'),
('t_cfgrace', -1, 'ADESPIM'),
('t_cfgrace', 1, 'AEDS'),
('t_cfgrace', 2, 'S'),
('t_cfgrace', 3, 'S'),
('t_cfgrace', 4, 'S'),
('t_cfgrace', 5, 'S'),
('t_cfgseries', -1, 'ADESPIM'),
('t_cfgseries', 1, 'AEDS'),
('t_cfgseries', 2, 'S'),
('t_cfgseries', 3, 'S'),
('t_cfgseries', 4, 'S'),
('t_cfgseries', 5, 'S'),
('t_class', -1, 'ADESPIM'),
('t_class', 1, 'S'),
('t_class', 2, 'S'),
('t_class', 3, 'S'),
('t_class', 4, 'S'),
('t_class', 5, 'S'),
('t_code_result', -1, 'ADESPIM'),
('t_code_result', 1, 'AEDS'),
('t_code_result', 2, 'S'),
('t_code_result', 3, 'S'),
('t_code_result', 4, 'AEDS'),
('t_code_result', 5, 'S'),
('t_code_system', -1, 'ADESPIM'),
('t_code_system', 1, 'AEDS'),
('t_code_system', 2, 'S'),
('t_code_system', 3, 'S'),
('t_code_system', 4, 'S'),
('t_code_system', 5, 'S'),
('t_code_type', -1, 'ADESPIM'),
('t_code_type', 1, 'AES'),
('t_code_type', 2, 'S'),
('t_code_type', 3, 'S'),
('t_code_type', 4, 'S'),
('t_code_type', 5, 'S'),
('t_competitor', -1, 'ADESPIM'),
('t_competitor', 1, 'S'),
('t_competitor', 2, 'S'),
('t_competitor', 3, 'S'),
('t_competitor', 4, 'AEDSPI'),
('t_competitor', 5, 'S'),
('t_event', -1, 'ADESPIM'),
('t_event', 1, 'S'),
('t_event', 2, 'ADESP'),
('t_event', 3, 'SP'),
('t_event', 4, 'S'),
('t_event', 5, 'S'),
('t_eventduty', -1, 'ADESPIM'),
('t_eventduty', 1, 'S'),
('t_eventduty', 2, 'ADES'),
('t_eventduty', 3, 'AEDS'),
('t_eventduty', 4, 'S'),
('t_eventduty', 5, 'S'),
('t_event_results', -1, 'ADESPIM'),
('t_event_results', 1, 'S'),
('t_event_results', 2, 'S'),
('t_event_results', 3, 'S'),
('t_event_results', 4, 'S'),
('t_event_results', 5, 'S'),
('t_help', -1, 'ADESPIM'),
('t_help', 1, 'AEDSP'),
('t_help', 2, 'S'),
('t_help', 3, 'S'),
('t_help', 4, 'S'),
('t_help', 5, 'S'),
('t_ini', -1, 'ADESPIM'),
('t_ini', 1, 'ESP'),
('t_ini', 2, 'S'),
('t_ini', 3, 'S'),
('t_ini', 4, 'S'),
('t_ini', 5, 'S'),
('t_link', -1, 'ADESPIM'),
('t_link', 1, 'AEDSP'),
('t_link', 2, 'S'),
('t_link', 3, 'S'),
('t_link', 4, 'S'),
('t_link', 5, 'S'),
('t_message', -1, 'ADESPIM'),
('t_message', 1, 'S'),
('t_message', 2, 'S'),
('t_message', 3, 'S'),
('t_message', 4, 'S'),
('t_message', 5, 'S'),
('t_result', -1, 'ADESPIM'),
('t_result', 1, 'S'),
('t_result', 2, 'S'),
('t_result', 3, 'S'),
('t_result', 4, 'AEDSPI'),
('t_result', 5, 'S'),
('t_resultfile', -1, 'ADESPIM'),
('t_resultfile', 1, 'S'),
('t_resultfile', 2, 'S'),
('t_resultfile', 3, 'S'),
('t_resultfile', 4, 'AEDS'),
('t_resultfile', 5, 'S'),
('t_rotamember', -1, 'ADESPIM'),
('t_rotamember', 1, 'S'),
('t_rotamember', 2, 'SP'),
('t_rotamember', 3, 'AESP'),
('t_rotamember', 4, 'S'),
('t_rotamember', 5, 'S'),
('t_series', -1, 'ADESPIM'),
('t_series', 1, 'AEDS'),
('t_series', 2, 'S'),
('t_series', 3, 'S'),
('t_series', 4, 'AEDS'),
('t_series', 5, 'S'),
('t_tide', -1, 'ADESPIM'),
('t_tide', 1, 'AESP'),
('t_tide', 2, 'ADESP'),
('t_tide', 3, 'SP'),
('t_tide', 4, 'S'),
('t_tide', 5, 'S');

--
-- Dumping data for table `rm_admin_users`
--

INSERT INTO `rm_admin_users` (`ID`, `username`, `password`, `email`, `fullname`, `groupid`, `active`, `reset_token`, `reset_date`, `reset_token1`, `reset_date1`) VALUES
(11, 'rmgrAdmin', '$2y$10$8GY7ZZeGEqkVXMtLmOrbwu6V1wC.yozAXTZp2Wg0al8PDxR5FI/g2', '', 'admin user', '', 1, NULL, NULL, NULL, NULL);

--
-- Dumping data for table `t_cfgfleet`
--

INSERT INTO `t_cfgfleet` (`id`, `eventcfgid`, `start_num`, `fleet_num`, `fleet_code`, `fleet_name`, `fleet_desc`, `scoring`, `py_type`, `warn_signal`, `prep_signal`, `timelimit_abs`, `timelimit_rel`, `defaultlaps`, `defaultfleet`, `classinc`, `onlyinc`, `classexc`, `groupinc`, `min_py`, `max_py`, `crew`, `spintype`, `hulltype`, `min_helmage`, `max_helmage`, `min_skill`, `max_skill`, `upddate`, `updby`, `createdate`) VALUES
(1, 1, 1, 1, 'FHCAP', 'Fast', 'Fast Handicap (PN <1000)', 'average', 'national', 'ics~1.gif', 'ics~p.gif', 0, 0, 4, 0, 'Waszp', 0, '', '', 1, 999, '', '', 'D', '', '', '', '', '2022-11-11 18:31:30', 'admin', '2020-07-21 11:12:22'),
(2, 1, 2, 2, 'MHCAP', 'Medium', 'Medium dinghies (PN 1000 - 1099) ', 'average', 'national', 'ics~2.gif', 'ics~p.gif', 0, 0, 3, 0, '', 0, '', '', 1000, 1099, '', '', 'D', '', '', '', '', '2022-11-11 18:31:39', 'admin', '2020-07-21 11:12:22'),
(3, 1, 3, 3, 'SHCAP', 'Slow', 'Slow dinghies with PN 1099 - 1500 + k1 keelboat', 'average', 'national', 'ics~3.gif', 'ics~p.gif', 0, 0, 2, 0, 'K1', 0, '', '', 1100, 1500, '', '', 'D', '', '', '', '', '2022-11-11 18:31:53', 'admin', '2020-07-21 11:12:22');

--
-- Dumping data for table `t_cfgrace`
--

INSERT INTO `t_cfgrace` (`id`, `race_code`, `race_name`, `race_desc`, `pursuit`, `numstarts`, `start_scheme`, `start_interval`, `active`, `comp_pick`, `upddate`, `updby`, `createdate`) VALUES
(1, 'CS', 'myclub-series', 'myclub sdemo eries format - with three fleets each with their own start', 0, 3, '6-3-0', 3, 1, 0, '2022-11-11 18:23:23', 'admin', '2020-07-21 11:13:18');

--
-- Dumping data for table `t_cfgseries`
--

INSERT INTO `t_cfgseries` (`id`, `seriestype`, `discard`, `nodiscard`, `multiplier`, `avgscheme`, `dutypoints`, `maxduty`, `notes`, `active`, `upddate`, `updby`, `createdate`) VALUES
(1, 'myclub series', '0,0,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8,9,9,10', '0', '0', 'all_competed', '0', 3, 'demo myclub series format', 1, '2022-11-11 18:21:08', 'admin', '2020-07-21 11:13:18');

--
-- Dumping data for table `t_class`
--

INSERT INTO `t_class` (`id`, `acronym`, `classname`, `variant`, `info`, `popular`, `nat_py`, `local_py`, `rya_id`, `category`, `crew`, `rig`, `spinnaker`, `engine`, `keel`, `active`, `upddate`, `updby`, `createdate`) VALUES
(4, '29r', '29er', '', '', 0, 903, 903, '', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:28:33'),
(44, '4K', '4000', '', '', 0, 917, 917, '', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:28:46'),
(5, '420', '420', '', '', 0, 1105, 1105, '', 'D', '2', 'S', 'C', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:28:55'),
(1037, '49r', '49er', '', '', 0, 697, 697, '', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-10-10 16:47:10', 'rm9_transfer', '2022-08-29 23:29:06'),
(7, '505', '505', '', '', 0, 903, 903, '', 'D', '2', 'S', 'C', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:29:12'),
(8, 'ALB', 'Albacore', '', '', 0, 1040, 1040, '', 'D', '2', 'S', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:29:18'),
(9, 'B14', 'B14', '', '', 0, 860, 860, '', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:29:26'),
(10, 'BLZ', 'Blaze', '', '', 0, 1033, 1033, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:30:32'),
(1079, 'BLZ', 'Blaze Fire', 'Fire', 'smaller rig version of Blaze', 0, 1065, 1065, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-10-10 16:36:23', 'rm9_transfer', '2022-08-29 23:30:40'),
(1034, 'BLZ', 'Blaze Halo', 'Halo', '', 0, 987, 987, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-10-10 16:47:25', 'rm9_transfer', '2022-08-29 23:30:47'),
(1082, 'BUZZ', 'Buzz', '', 'estimated PN', 0, 1030, 1030, '', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-10-10 16:45:17', 'rm9_transfer', '2022-08-29 23:47:58'),
(90, 'BYT', 'Byte', '', '', 0, 1135, 1135, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:30:55'),
(1048, 'BYT', 'Byte Cii', 'Cii', '', 0, 1135, 1135, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-10-11 09:45:24', 'rm9_transfer', '2022-08-29 23:31:00'),
(91, 'CAD', 'Cadet', '', '', 0, 1430, 1430, '', 'D', '2', 'S', 'C', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:31:12'),
(15, 'CHB', 'Cherub', '', '', 0, 903, 903, '', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:31:23'),
(16, 'COM', 'Comet', '', '', 0, 1210, 1210, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:32:29'),
(94, 'COMD', 'Comet Duo', '', '', 0, 1178, 1178, '', 'D', '2', 'S', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:32:34'),
(17, 'COMT', 'Comet Trio', '', '', 0, 1104, 1104, '', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:32:39'),
(1064, 'COMT', 'Comet Trio 2', '', 'Comet Trio with updated rig', 0, 1052, 1052, '', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:32:44'),
(1017, 'COMV', 'Comet Versa', '', '', 0, 1165, 1165, '', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:32:52'),
(18, 'CON', 'Contender', '', '', 0, 969, 969, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:33:01'),
(1019, 'D1', 'D-One', '', '', 0, 948, 948, '', 'D', '1', 'U', 'A', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:33:08'),
(1055, 'DZ', 'D-Zero', '', '', 0, 1029, 1029, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:33:32'),
(1056, 'DZ', 'D-Zero Blue', 'Blue', '', 0, 1040, 1040, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-10-10 16:37:03', 'rm9_transfer', '2022-08-29 23:33:41'),
(99, 'D18', 'Dart 18', '', '', 0, 832, 832, '', 'M', '2', 'S', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:33:47'),
(102, 'DY', 'Devon Yawl', '', '', 0, 1192, 1192, '', 'D', '2', 'K', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-30 12:49:37'),
(1052, 'DD', 'Drascombe Dabber', '', '', 0, 1382, 1382, '', 'D', '2', 'S', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:34:09'),
(1042, 'ENT', 'Enterprise', '', '', 0, 1122, 1122, '', 'D', '2', 'S', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:34:14'),
(1069, 'EPS', 'EPS', '', '', 0, 1033, 1033, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:34:20'),
(105, 'EUR', 'Europe', '', '', 0, 1141, 1141, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:34:25'),
(1065, 'F37', 'Farr 3.7', '', '', 0, 1063, 1063, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-10-11 09:45:48', 'rm9_transfer', '2022-08-29 23:34:34'),
(26, 'FIN', 'Finn', '', '', 0, 1049, 1049, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:34:41'),
(27, 'FBL', 'Fireball', '', '', 0, 952, 952, '', 'D', '2', 'S', 'C', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:34:53'),
(1049, 'FFY', 'Firefly', '', '', 0, 1172, 1172, '', 'D', '2', 'S', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:35:27'),
(111, 'GP', 'GP 14', '', '', 0, 1130, 1130, '', 'D', '2', 'S', 'C', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:35:33'),
(1050, 'GRD', 'Graduate', '', '', 0, 1132, 1132, '', 'D', '2', 'S', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:35:39'),
(1053, 'GUL', 'Gull', '', '', 0, 1363, 1363, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:35:47'),
(1063, 'H2', 'Hadron H2', '', '', 0, 1034, 1034, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-10-10 16:56:02', 'rm9_transfer', '2022-08-29 23:36:08'),
(123, 'HOR', 'Hornet', '', '', 0, 955, 955, '', 'D', '2', 'S', 'C', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:36:18'),
(31, 'HUR', 'Hurricane 5.9', '', '', 0, 707, 707, '', 'M', '2', 'S', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:37:06'),
(125, 'HUR', 'Hurricane SX', '', 'asymmetric spinnaker', 0, 695, 695, '', 'M', '2', 'S', 'A', 'OB', 'D', 1, '2022-10-11 09:46:18', 'rm9_transfer', '2022-08-29 23:37:20'),
(1051, 'ICN', 'Icon', '', '', 0, 976, 976, '', 'D', '2', 'S', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:37:30'),
(1036, 'I14', 'International 14', '', '', 0, 758, 758, '', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-10-10 16:47:15', 'rm9_transfer', '2022-08-29 23:37:35'),
(33, 'IC', 'International Canoe', '', '', 0, 884, 884, '', 'D', '1', 'S', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:37:44'),
(32, 'IC', 'International Canoe A', 'asymmetric', 'Asymmetric spinnaker version of International Canoe', 0, 866, 866, '', 'D', '1', 'S', 'A', 'OB', 'D', 1, '2022-10-10 16:37:48', 'rm9_transfer', '2022-08-29 23:37:49'),
(34, 'ISO', 'ISO', '', '', 0, 922, 922, '', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:37:54'),
(1031, 'K1', 'K1', '', '', 0, 1064, 1064, '', 'D', '1', 'S', 'O', 'OB', 'D', 1, '2022-10-11 09:46:40', 'rm9_transfer', '2022-08-29 23:37:58'),
(35, 'KES', 'Kestrel', '', '', 0, 1038, 1038, '', 'D', '2', 'S', 'C', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:38:05'),
(37, 'LRK', 'Lark', '', '', 0, 1073, 1073, '', 'D', '2', 'S', 'C', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:38:10'),
(38, 'LAS', 'Laser', '', 'ICLA 7', 0, 1100, 1100, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-10-11 09:47:13', 'rm9_transfer', '2022-08-29 23:38:32'),
(42, 'LAS2', 'Laser 2', '', '', 0, 1085, 1085, '', 'D', '2', 'S', 'C', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:38:39'),
(43, '2K', '2000', '', '', 0, 1114, 1114, '', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-10-10 16:50:55', 'rm9_transfer', '2022-08-29 23:39:04'),
(135, 'L3K', 'Laser 3000', '', '', 0, 1085, 1085, '', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:39:24'),
(40, 'LAS', 'Laser 4.7', '', 'ICLA 4', 0, 1208, 1208, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-10-11 09:47:27', 'rm9_transfer', '2022-08-29 23:39:30'),
(1018, 'LAS', 'Laser 8.1', '', '', 0, 1051, 1051, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-10-10 16:40:51', 'rm9_transfer', '2022-08-29 23:39:34'),
(45, 'EPS', 'Laser EPS', '', '', 0, 1033, 1033, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:39:39'),
(137, 'PICO', 'Laser Pico', '', '', 0, 1330, 1330, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:39:52'),
(138, 'PICO', 'Laser Pico - DH', 'double handed', '', 0, 1265, 1265, '', 'D', '2', 'S', 'O', 'OB', 'D', 1, '2022-10-10 16:53:33', 'rm9_transfer', '2022-08-30 12:49:41'),
(39, 'LAS', 'Laser Radial', '', 'ICLA 6', 0, 1147, 1147, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-10-11 09:47:42', 'rm9_transfer', '2022-08-29 23:40:07'),
(41, 'STRA', 'Laser Stratos', '', '', 0, 1103, 1103, '', 'D', '2', 'S', 'C', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:40:27'),
(1035, 'VAGO', 'Laser Vago', '', '', 0, 1071, 1071, '', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-10-10 16:40:44', 'rm9_transfer', '2022-08-29 23:40:33'),
(46, 'L368', 'Lightning 368', '', '', 0, 1162, 1162, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:40:42'),
(47, 'MR', 'Merlin Rocket', '', '', 0, 980, 980, '', 'D', '2', 'S', 'C', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:40:50'),
(1061, 'MR', 'Merlin - wood', 'wood', 'older style - wood boats [3553 or earlier]', 0, 990, 990, '', 'D', '2', 'S', 'C', 'OB', 'D', 1, '2022-10-11 09:44:20', 'rm9_transfer', '2022-08-29 23:40:55'),
(1043, 'MIR', 'Miracle', '', '', 0, 1194, 1194, '', 'D', '2', 'S', 'C', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:41:01'),
(49, 'MIR', 'Mirror', '', '', 0, 1390, 1390, '', 'D', '2', 'S', 'C', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:41:18'),
(143, 'MIR', 'Mirror - SH', 'single handed', '', 0, 1380, 1380, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-10-10 16:53:26', 'rm9_transfer', '2022-08-29 23:41:22'),
(1045, 'MOTH', 'Moth - foiler', 'foiler', 'Foiling Int. Moth', 0, 570, 570, '', 'F', '1', 'U', 'O', 'OB', 'D', 1, '2022-10-10 16:47:00', 'rm9_transfer', '2022-08-29 23:41:34'),
(1080, 'MRX', 'MRX', '', 'estimated PN', 0, 993, 993, '', 'D', '2', 'S', 'C', 'OB', 'D', 1, '2022-10-10 16:45:43', 'rm9_transfer', '2022-08-29 23:41:40'),
(51, 'MSKF', 'Musto Skiff', '', '', 0, 849, 849, '', 'D', '1', 'S', 'A', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:41:57'),
(52, 'N12', 'National 12', '', '', 0, 1064, 1064, '', 'D', '2', 'S', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:42:07'),
(1066, 'N12', 'National 12 - DB', 'DB', 'National 12 with double floor', 0, 1089, 1089, '', 'D', '2', 'S', 'O', 'OB', 'D', 1, '2022-10-10 16:41:48', 'rm9_transfer', '2022-08-29 23:42:11'),
(151, 'OK', 'OK', '', '', 0, 1104, 1104, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:42:23'),
(53, 'OPT', 'Optimist', '', '', 0, 1642, 1642, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:42:30'),
(152, 'OSP', 'Osprey', '', '', 0, 930, 930, '', 'D', '2', 'S', 'C', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:42:34'),
(153, 'OTR', 'Otter', '', '', 0, 1275, 1275, '', 'D', '2', 'S', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:42:45'),
(156, 'PHA', 'Phantom', '', '', 0, 1004, 1004, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:42:50'),
(1060, 'RED', 'Redwing', '', 'Looe Redwing', 0, 1094, 1094, '', 'D', '2', 'S', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:42:59'),
(1057, 'ARO5', 'RS Aero 5', '', '', 0, 1136, 1136, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:43:14'),
(1054, 'ARO7', 'RS Aero 7', '', '', 0, 1065, 1065, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:43:20'),
(1059, 'ARO9', 'RS Aero 9', '', '', 0, 1014, 1014, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:43:26'),
(1007, 'FEVA', 'RS Feva', '', '', 0, 1240, 1240, '', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:43:40'),
(1067, 'FEVA', 'RS Feva XL', '', '', 0, 1244, 1244, '', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:43:45'),
(1081, 'TERA', 'RS Tera Mini', '', 'estimated PN', 0, 1499, 1499, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-10-10 16:45:31', 'rm9_transfer', '2022-08-29 23:43:54'),
(1068, 'TERA', 'RS Tera Pro', '', '', 0, 1359, 1359, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:43:58'),
(1027, 'TERA', 'RS Tera Sport', '', '', 0, 1445, 1445, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:44:03'),
(56, 'VAR', 'RS Vareo', '', '', 0, 1093, 1093, '', 'D', '1', 'S', 'A', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:44:20'),
(1006, 'VIS', 'RS Vision', '', '', 0, 1137, 1137, '', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:44:24'),
(1077, 'ZEST', 'RS Zest', '', '', 0, 1260, 1260, '', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:44:29'),
(1032, 'RS1', 'RS100 10.2', '10.2m rig', '', 0, 981, 981, '', 'D', '1', 'U', 'A', 'OB', 'D', 1, '2022-10-10 16:54:04', 'rm9_transfer', '2022-08-29 23:45:17'),
(1030, 'RS1', 'RS100 8.4', '8.4m rig', '', 0, 1004, 1004, '', 'D', '1', 'U', 'A', 'OB', 'D', 1, '2022-10-11 09:48:13', 'rm9_transfer', '2022-08-29 23:45:13'),
(57, 'RS2', 'RS200', '', '', 0, 1046, 1046, '', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:45:10'),
(58, 'RS3', 'RS300', '', '', 0, 970, 970, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:45:07'),
(59, 'RS4', 'RS400', '', '', 0, 942, 942, '', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:45:03'),
(60, 'RS5', 'RS500', '', '', 0, 966, 966, '', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:45:21'),
(61, 'RS6', 'RS600', '', '', 0, 920, 920, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:45:26'),
(62, 'RS7', 'RS700', '', '', 0, 845, 845, '', 'D', '1', 'U', 'A', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:45:31'),
(63, 'RS8', 'RS800', '', '', 0, 799, 799, '', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:45:48'),
(163, 'SY', 'Salcombe Yawl', '', '', 0, 1105, 1105, '', 'D', '2', 'K', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-30 12:49:59'),
(165, 'SCP', 'Scorpion', '', '', 0, 1041, 1041, '', 'D', '2', 'S', 'C', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:46:00'),
(65, 'SFY', 'Seafly', '', '', 0, 1071, 1071, '', 'D', '2', 'S', 'C', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:46:06'),
(1078, 'SKY', 'Skerry', '', 'lug sailed dinghy cruiser', 0, 2000, 2000, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:46:13'),
(68, 'SOLO', 'Solo', '', '', 0, 1142, 1142, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:46:19'),
(178, 'STR', 'Streaker', '', '', 0, 1128, 1128, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:46:23'),
(179, 'SNOV', 'Supernova', '', '', 0, 1077, 1077, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:46:32'),
(183, 'TAS', 'Tasar', '', '', 0, 1022, 1022, '', 'D', '2', 'S', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:46:36'),
(1076, 'TFD', 'TFD', '', 'foiling one design - estimated PN', 0, 700, 700, '', 'F', '1', 'U', 'O', 'OB', 'D', 1, '2022-10-11 09:48:49', 'rm9_transfer', '2022-08-29 23:46:40'),
(184, 'TWY', 'Tideway', '', '', 0, 1447, 1447, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-30 12:49:33'),
(1026, 'TPZ2', 'Topaz Duo', '', '', 0, 1190, 1190, '', 'D', '2', 'S', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:46:57'),
(72, 'TPZM', 'Topaz Magno', '', '', 0, 1207, 1207, '', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-30 12:49:49'),
(185, 'TPZU', 'Topaz Uno', '', '', 0, 1251, 1251, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:47:15'),
(74, 'TOP', 'Topper 5.3', '5.3m rig', '', 0, 1365, 1365, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-11-11 17:27:18', 'rm9_transfer', '2022-08-29 23:47:21'),
(1038, 'TOP', 'Topper 4.2', '4.2m rig', '', 0, 1409, 1409, '', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-10-10 16:53:52', 'rm9_transfer', '2022-08-29 23:47:26'),
(1015, 'V3K', 'V3000', '', 'lighter version of Laser 3000', 0, 1032, 1032, '', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-10-10 16:52:55', 'rm9_transfer', '2022-08-30 12:49:27'),
(76, 'VTX', 'Vortex Asymmetric', '', '', 0, 914, 914, '', 'D', '1', 'U', 'A', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:48:17'),
(77, 'WND', 'Wanderer', '', '', 0, 1193, 1193, '', 'D', '2', 'S', 'C', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:48:25'),
(1062, 'WASP', 'Waszp', '', 'foiling one design moth', 0, 555, 555, '', 'F', '1', 'U', 'O', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:48:36'),
(78, 'WAY', 'Wayfarer', '', '', 0, 1102, 1102, '', 'D', '2', 'S', 'C', 'OB', 'D', 1, '2022-08-30 14:13:24', 'rm9_transfer', '2022-08-29 23:48:43'),
(1008, 'XEN', 'Xenon', '', '', 0, 1079, 1079, 'XEN2SA', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-11-11 18:29:08', 'admin', '2022-08-30 12:49:23'),
(1083, 'HAD', 'Hadron H1', NULL, '', 0, 1065, 1065, 'HAD1UO', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-11-11 18:15:32', 'admin', '2022-11-11 17:14:52'),
(1084, 'LDR', 'Leader', NULL, '', 0, 1115, 1115, 'LDR2SA', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-11-11 18:21:42', 'admin', '2022-11-11 17:19:03'),
(1085, '5K', 'Laser 5000', NULL, '', 0, 846, 846, '5K2SA', 'D', '2', 'S', 'A', 'OB', 'D', 1, '2022-11-11 18:21:10', 'admin', '2022-11-11 17:20:16'),
(1086, 'NEO', 'RS Neo', NULL, '', 0, 1180, 1180, 'NEO1UO', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-11-11 18:23:17', 'admin', '2022-11-11 17:22:23'),
(1087, 'SOL', 'Solution', NULL, '', 0, 1092, 1092, 'SOL1UO', 'D', '1', 'U', 'O', 'OB', 'D', 1, '2022-11-11 18:25:06', 'admin', '2022-11-11 17:24:46');

--
-- Dumping data for table `t_code_result`
--

INSERT INTO `t_code_result` (`id`, `code`, `short`, `info`, `scoringtype`, `scoring`, `timing`, `startcode`, `timercode`, `resultcode`, `nonexclude`, `rank`, `active`, `upddate`, `updby`, `createdate`) VALUES
(1, 'DNF', 'did not finish', 'Competitor did not finish', 'race', 'N + 1', 1, 0, 1, 1, 0, 1, 1, '2022-01-28 09:52:20', 'admin', '2020-07-02 11:28:48'),
(3, 'AVG', 'average points', 'Competitor scores average points in series (e.g. for doing OOD or safety duty)', 'series', 'AVG', 0, 0, 0, 1, 0, 1, 1, '2022-01-28 09:50:16', 'admin', '2020-07-02 11:28:48'),
(4, 'DSQ', 'disqualified', 'boat disqualified - OOD should only use this if local SIs allow - otherwise only applied as a result of a protest', 'race', 'N + 1', 0, 0, 0, 1, 0, 1, 1, '2022-01-15 13:00:38', 'admin', '2020-07-02 11:28:48'),
(5, 'RET', 'retired', 'Competitor retired ', 'race', 'N + 1', 0, 0, 0, 1, 0, 1, 1, '2022-01-21 15:17:47', 'admin', '2020-07-02 11:28:48'),
(6, 'BFD', 'black flag OCS', 'Competitor black flagged at start - DSQ from all starts', 'race', 'N + 1', 1, 1, 1, 1, 0, 1, 1, '2022-06-20 11:07:30', 'admin', '2020-07-02 11:28:48'),
(7, 'DNC', 'did not launch', 'Competitor did not compete', 'series', 'S + 1', 0, 1, 1, 1, 0, 1, 1, '2022-01-28 09:50:59', 'admin', '2020-07-02 11:28:48'),
(8, 'DNS', 'did not start', 'Competitor did not come to start', 'race', 'N + 1', 1, 1, 1, 1, 0, 1, 1, '2022-01-28 09:53:29', 'admin', '2020-07-02 11:28:48'),
(9, 'OCS', 'over start line', 'Did not start; on the course side of the starting line at her starting signal and failed to start, or broke rule 30.1', 'race', 'N + 1', 1, 1, 1, 1, 0, 1, 1, '2022-01-28 09:52:26', 'admin', '2020-07-02 11:28:48'),
(10, 'RDG', 'redress', 'Competitor given redress following hearing', 'manual', '', 0, 0, 0, 0, 0, 1, 1, '2022-01-28 09:49:51', 'admin', '2020-07-02 11:28:48'),
(11, 'ZFP', 'Z flag OCS', 'Competitor OCS - Z flag in place - 20% penalty applied', 'penalty', 'N * 0.2', 1, 1, 1, 1, 0, 1, 1, '2022-01-15 13:00:28', 'admin', '2020-07-02 11:28:48'),
(12, 'SCP', 'scoring penalty', 'Took a Scoring Penalty under rule 44.3(a) in accordance with Sailing Instructions', 'penalty', 'N * 0.2', 1, 0, 0, 1, 0, 1, 1, '2022-01-15 13:00:28', 'admin', '2020-07-02 11:28:48'),
(13, 'DNE', 'non excludable DSQ', 'DNE  Disqualification (other than DGM) not excludable under rule 90.3(b) ', 'race', 'N + 1', 0, 0, 0, 1, 1, 1, 1, '2022-01-15 13:00:47', 'admin', '2020-07-02 11:28:48'),
(14, 'DUT', 'club duty', 'duty points as defined for the series type', 'series', 'AVG', 0, 0, 1, 1, 0, 1, 1, '2022-01-28 09:50:34', 'admin', '2020-07-02 11:28:48'),
(15, 'UFD', 'U flag OCS', 'Competitor U flagged at start  - DSQ from this start', 'race', 'N + 1', 1, 1, 1, 1, 0, 1, 1, '2022-01-28 09:51:35', 'admin', '2020-07-02 11:28:48'),
(16, 'NSC', 'course not sailed', 'Not sailing correct course (introduced in 2021 rules)', 'race', 'N + 1', 1, 0, 1, 1, 0, 1, 1, '2022-01-28 09:51:20', 'admin', '2020-10-07 18:18:22'),
(17, 'DPI', 'discretionary penalty', 'Discretionary fixed points penalty', 'penalty', '', 1, 0, 0, 1, 0, 1, 1, '2022-01-15 13:00:28', 'admin', '2020-07-02 11:28:48');

--
-- Dumping data for table `t_code_system`
--

INSERT INTO `t_code_system` (`id`, `groupname`, `code`, `label`, `rank`, `defaultval`, `deletable`, `upddate`, `updby`, `createdate`) VALUES
(2, 'class_category', 'M', 'multihull', 10, 0, 0, '2014-07-27 12:46:03', 'admin', '2020-07-21 11:15:35'),
(3, 'class_category', 'K', 'keelboat', 15, 0, 1, '2020-07-02 23:57:12', 'admin', '2020-07-21 11:15:35'),
(4, 'class_category', 'C', 'cruiser', 25, 0, 1, '2020-07-02 23:57:06', 'admin', '2020-07-21 11:15:35'),
(5, 'class_rig', 'U', 'una rig', 1, 1, 0, '2014-07-27 12:46:03', 'admin', '2020-07-21 11:15:35'),
(6, 'class_rig', 'S', 'sloop', 2, 0, 0, '2014-07-27 12:46:03', 'admin', '2020-07-21 11:15:35'),
(7, 'class_rig', 'K', 'ketch or yawl', 3, 0, 0, '2014-07-27 12:46:03', 'admin', '2020-07-21 11:15:35'),
(8, 'class_spinnaker', 'O', 'no spinnaker', 1, 1, 0, '2014-07-27 12:46:03', 'admin', '2020-07-21 11:15:35'),
(9, 'class_spinnaker', 'C', 'conventional spinnaker', 2, 0, 0, '2014-07-27 12:46:03', 'admin', '2020-07-21 11:15:35'),
(10, 'class_spinnaker', 'A', 'asymmetric spinnaker', 3, 0, 0, '2014-07-27 12:46:03', 'admin', '2020-07-21 11:15:35'),
(11, 'class_keel', 'D', 'centreboard', 5, 1, 0, '2014-07-27 12:46:03', 'admin', '2020-07-21 11:15:35'),
(12, 'class_keel', 'F', 'fixed', 10, 0, 0, '2014-07-27 12:46:03', 'admin', '2020-07-21 11:15:35'),
(13, 'class_keel', '2K', 'twin', 15, 0, 0, '2014-07-27 12:46:03', 'admin', '2020-07-21 11:15:35'),
(14, 'class_keel', '3K', 'triple', 20, 0, 0, '2014-07-27 12:46:03', 'admin', '2020-07-21 11:15:35'),
(15, 'class_engine', 'OB', 'none/outboard', 1, 1, 0, '2014-07-27 12:46:03', 'admin', '2020-07-21 11:15:35'),
(16, 'class_engine', 'IBF', 'inboard - folding prop', 10, 0, 0, '2014-07-27 12:46:03', 'admin', '2020-07-21 11:15:35'),
(17, 'class_engine', 'IB2', 'inboard - 2 bladed prop', 15, 0, 0, '2014-07-27 12:46:03', 'admin', '2020-07-21 11:15:35'),
(18, 'class_engine', 'IB3', 'inboard - 3 bladed prop', 20, 0, 0, '2014-07-27 12:46:03', 'admin', '2020-07-21 11:15:35'),
(19, 'class_category', 'F', 'foiler', 20, 0, 1, '2020-07-21 15:03:31', 'admin', '2020-07-21 11:15:35'),
(20, 'class_crew', '1', 'single handed', 5, 1, 0, '2014-07-27 13:43:36', 'admin', '2020-07-21 11:15:35'),
(21, 'class_crew', '2', 'double handed', 10, 0, 0, '2014-07-27 13:43:41', 'admin', '2020-07-21 11:15:35'),
(22, 'class_crew', 'N', 'more than 2', 12, 0, 0, '2014-07-27 13:43:45', 'admin', '2020-07-21 11:15:35'),
(23, 'entry_type', 'signon', 'sign on system (no sign off)', 2, 1, 0, '2015-04-25 15:18:33', 'admin', '2020-07-21 11:15:35'),
(24, 'entry_type', 'signon-retire', 'sign on system (and retire)', 2, 0, 0, '2015-04-25 15:17:51', 'admin', '2020-07-21 11:15:35'),
(25, 'entry_type', 'ood', 'OOD entry', 1, 0, 0, '2015-04-25 15:18:37', 'admin', '2020-07-21 11:15:35'),
(27, 'event_type', 'racing', 'racing', 1, 1, 0, '2015-04-27 07:24:04', 'admin', '2020-07-21 11:15:35'),
(28, 'event_type', 'training', 'training', 5, 0, 0, '2015-04-27 07:24:30', 'admin', '2020-07-21 11:15:35'),
(29, 'event_type', 'social', 'social', 10, 0, 0, '2015-04-27 07:25:04', 'admin', '2020-07-21 11:15:35'),
(30, 'event_status', 'running', 'race underway', 4, 0, 0, '2015-04-27 09:44:07', 'admin', '2020-07-21 11:15:35'),
(31, 'event_status', 'selected', 'race selected', 2, 0, 0, '2015-04-27 09:44:07', 'admin', '2020-07-21 11:15:35'),
(32, 'event_status', 'cancelled', 'race cancelled', 12, 0, 0, '2015-04-27 09:44:07', 'admin', '2020-07-21 11:15:35'),
(33, 'event_status', 'abandoned', 'race abandoned', 10, 0, 0, '2015-04-27 09:44:07', 'admin', '2020-07-21 11:15:35'),
(34, 'event_status', 'sailed', 'race complete', 6, 0, 0, '2015-04-27 09:44:07', 'admin', '2020-07-21 11:15:35'),
(35, 'event_status', 'completed', 'race closed', 8, 0, 0, '2015-04-27 09:44:07', 'admin', '2020-07-21 11:15:35'),
(36, 'event_status', 'scheduled', 'race scheduled', 1, 1, 0, '2015-04-27 09:44:07', 'admin', '2020-07-21 11:15:35'),
(168, 'event_access', 'open', 'club and visitors', 10, 1, 0, '2016-06-01 14:41:55', 'marke', '2020-07-21 11:15:35'),
(167, 'event_access', 'club', 'club members', 5, 1, 0, '2016-06-01 14:41:55', 'marke', '2020-07-21 11:15:35'),
(44, 'start_scheme', '6-3-0', '6-3-start', 1, 1, 0, '2015-05-08 08:51:52', 'admin', '2020-07-21 11:15:35'),
(45, 'start_scheme', '5-4-1-0', '5-4-1-start', 2, 0, 0, '2015-05-08 08:51:52', 'admin', '2020-07-21 11:15:35'),
(46, 'start_scheme', '10-5-0', '10-5-start', 3, 0, 0, '2015-05-08 08:51:52', 'admin', '2020-07-21 11:15:35'),
(47, 'race_format', 'fleet', 'fleet racing', 5, 1, 0, '2015-05-31 12:59:37', 'marke', '2020-07-21 11:15:35'),
(48, 'race_format', 'flight', 'flights', 10, 0, 0, '2015-05-31 13:00:22', 'marke', '2020-07-21 11:15:35'),
(49, 'race_type', 'handicap', 'handicap', 5, 1, 0, '2016-02-15 22:58:19', 'admin', '2020-07-21 11:15:35'),
(50, 'race_type', 'average', 'handicap (avg lap)', 10, 0, 0, '2021-08-07 17:26:08', 'admin', '2020-07-21 11:15:35'),
(51, 'race_type', 'level', 'level (no handicap)', 15, 0, 0, '2016-02-15 22:58:49', 'admin', '2020-07-21 11:15:35'),
(52, 'race_type', 'pursuit', 'pursuit', 20, 0, 0, '2015-05-31 13:04:56', 'admin', '2020-07-21 11:15:35'),
(53, 'wind_dir', 'N', 'north', 1, 0, 0, '2015-06-18 21:42:01', 'admin', '2020-07-21 11:15:35'),
(54, 'wind_dir', 'E', 'east', 3, 0, 0, '2015-06-18 21:42:01', 'admin', '2020-07-21 11:15:35'),
(55, 'wind_dir', 'W', 'west', 7, 0, 0, '2015-06-18 21:42:01', 'admin', '2020-07-21 11:15:35'),
(56, 'wind_dir', 'NW', 'north-west', 8, 0, 0, '2015-06-18 21:42:01', 'admin', '2020-07-21 11:15:35'),
(57, 'wind_dir', 'SW', 'south-west', 6, 0, 0, '2015-06-18 21:42:01', 'admin', '2020-07-21 11:15:35'),
(58, 'wind_dir', 'S', 'south', 5, 0, 0, '2015-06-18 21:42:01', 'admin', '2020-07-21 11:15:35'),
(59, 'wind_dir', 'SE', 'south-east', 4, 0, 0, '2015-06-18 21:42:01', 'admin', '2020-07-21 11:15:35'),
(60, 'wind_dir', 'NE', 'north-east', 2, 0, 0, '2015-06-18 21:42:01', 'admin', '2020-07-21 11:15:35'),
(61, 'wind_speed', '20+', '20 + knots', 5, 0, 0, '2015-06-18 21:42:01', 'admin', '2020-07-21 11:15:35'),
(62, 'wind_speed', '15-20', '15 - 20 knots', 4, 0, 0, '2015-06-18 21:42:01', 'admin', '2020-07-21 11:15:35'),
(63, 'wind_speed', '10-15', '10 - 15 knots', 3, 0, 0, '2015-06-18 21:42:01', 'admin', '2020-07-21 11:15:35'),
(64, 'wind_speed', '5-10', '5 - 10 knots', 2, 0, 0, '2015-06-18 21:42:01', 'admin', '2020-07-21 11:15:35'),
(65, 'wind_speed', '0-5', '0 - 5 knots', 1, 0, 0, '2015-06-18 21:42:01', 'admin', '2020-07-21 11:15:35'),
(66, 'competitor_status', 'current', 'current', 1, 1, 0, '2015-09-16 22:54:29', 'admin', '2020-07-21 11:15:35'),
(67, 'competitor_status', 'retired', 'not used', 10, 0, 0, '2015-09-16 22:55:22', 'admin', '2020-07-21 11:15:35'),
(68, 'competitor_status', 'review', 'review status', 5, 0, 0, '2015-09-16 22:55:42', 'admin', '2020-07-21 11:15:35'),
(69, 'competitor_list', 'junior', 'juniors (under 18)', 1, 0, 1, '2015-09-16 23:05:59', 'admin', '2020-07-21 11:15:35'),
(70, 'competitor_list', 'senior', 'seniors (over 60)', 5, 0, 1, '2015-09-16 23:06:12', 'admin', '2020-07-21 11:15:35'),
(71, 'competitor_list', 'starttorace', 'start to race', 10, 0, 1, '2015-09-16 23:06:55', 'admin', '2020-07-21 11:15:35'),
(72, 'competitor_skill', '1', 'beginner', 1, 0, 0, '2022-09-01 17:56:29', 'admin', '2020-07-21 11:15:35'),
(73, 'competitor_skill', '2', 'improver', 2, 0, 0, '2015-09-16 23:13:13', 'admin', '2020-07-21 11:15:35'),
(74, 'competitor_skill', '3', 'average club racer', 3, 1, 0, '2022-09-01 17:56:23', 'admin', '2020-07-21 11:15:35'),
(75, 'competitor_skill', '4', 'good club racer', 4, 0, 0, '2015-09-16 23:13:42', 'admin', '2020-07-21 11:15:35'),
(76, 'competitor_skill', '5', 'rockstar', 5, 0, 0, '2015-09-16 23:13:57', 'admin', '2020-07-21 11:15:35'),
(77, 'resultcode_eval', 'N+1', 'No. of entries + 1', 5, 0, 1, '2016-01-01 21:00:24', 'marke', '2020-07-21 11:15:35'),
(78, 'resultcode_eval', 'S+1', 'No. of series entries + 1', 10, 0, 0, '2016-01-01 21:02:34', 'marke', '2020-07-21 11:15:35'),
(79, 'resultcode_eval', '20%', 'penalty of 20% of entries', 0, 0, 0, '2016-01-01 21:03:16', 'marke', '2020-07-21 11:15:35'),
(80, 'resultcode_eval', 'AVG1', 'Average points - scheme 1', 20, 0, 0, '2016-01-01 21:04:24', 'marke', '2020-07-21 11:15:35'),
(81, 'resultcode_eval', 'AVG2', 'Average points - scheme 2', 22, 0, 0, '2016-01-01 21:04:24', 'marke', '2020-07-21 11:15:35'),
(82, 'prize_list', 'junior', 'under 18 prize', 1, 0, 1, '2016-01-17 19:40:07', 'admin', '2020-07-21 11:15:35'),
(83, 'prize_list', 'senior', 'over 60 prize', 2, 0, 1, '2016-01-17 19:40:35', 'admin', '2020-07-21 11:15:35'),
(84, 'prize_list', 'beginner', 'beginner prize', 3, 0, 1, '2016-01-17 19:40:58', 'admin', '2020-07-21 11:15:35'),
(85, 'prize_list', 'parentchild', 'parent and child prize', 4, 0, 1, '2016-01-17 19:41:52', 'admin', '2020-07-21 11:15:35'),
(86, 'flight_list', 'flight1', 'red', 1, 0, 1, '2016-01-17 19:43:27', 'admin', '2020-07-21 11:15:35'),
(87, 'flight_list', 'flight2', 'blue', 2, 0, 1, '2016-01-17 19:43:46', 'admin', '2020-07-21 11:15:35'),
(88, 'flight_list', 'flight3', 'green', 3, 0, 1, '2016-01-17 19:44:04', 'admin', '2020-07-21 11:15:35'),
(89, 'flight_list', 'flight4', 'black', 4, 0, 1, '2016-01-17 19:44:26', 'admin', '2020-07-21 11:15:35'),
(90, 'system_pages', 'race', 'status page', 1, 0, 0, '2022-07-19 16:40:50', 'admin', '2020-07-21 11:15:35'),
(91, 'system_pages', 'entries', 'entries page', 2, 0, 0, '2022-07-19 16:42:59', 'admin', '2020-07-21 11:15:35'),
(92, 'system_pages', 'start', 'start page', 3, 0, 0, '2016-02-16 11:54:34', 'admin', '2020-07-21 11:15:35'),
(93, 'system_pages', 'timer', 'timer page', 4, 0, 0, '2016-02-16 11:54:34', 'admin', '2020-07-21 11:15:35'),
(94, 'system_pages', 'results', 'results page', 5, 0, 0, '2022-07-19 16:43:01', 'admin', '2020-07-21 11:15:35'),
(95, 'event_display', 'W', 'website', 1, 1, 0, '2016-01-29 00:03:01', 'admin', '2020-07-21 11:15:35'),
(96, 'event_display', 'R', 'racebox', 2, 0, 0, '2016-01-29 00:03:23', 'admin', '2020-07-21 11:15:35'),
(97, 'event_display', 'S', 'sailor', 3, 0, 0, '2016-01-29 00:03:44', 'admin', '2020-07-21 11:15:35'),
(98, 'ordinal_number', '1', '1st', 1, 1, 0, '2016-02-09 14:15:09', 'admin', '2020-07-21 11:15:35'),
(99, 'ordinal_number', '2', '2nd', 2, 0, 0, '2016-02-09 14:15:18', 'admin', '2020-07-21 11:15:35'),
(100, 'ordinal_number', '3', '3rd', 3, 0, 0, '2016-02-09 14:16:31', 'admin', '2020-07-21 11:15:35'),
(101, 'ordinal_number', '4', '4th', 4, 0, 0, '2016-02-09 14:16:45', 'admin', '2020-07-21 11:15:35'),
(102, 'ordinal_number', '5', '5th', 5, 0, 0, '2016-02-09 14:16:58', 'admin', '2020-07-21 11:15:35'),
(103, 'ordinal_number', '6', '6th', 6, 0, 0, '2016-02-09 14:17:07', 'admin', '2020-07-21 11:15:35'),
(104, 'ordinal_number', '7', '7th', 7, 0, 0, '2016-02-09 14:17:17', 'admin', '2020-07-21 11:15:35'),
(105, 'ordinal_number', '8', '8th', 8, 0, 0, '2016-02-09 14:17:28', 'admin', '2020-07-21 11:15:35'),
(106, 'ordinal_number', '9', '9th', 9, 0, 0, '2016-02-09 14:17:38', 'admin', '2020-07-21 11:15:35'),
(107, 'ordinal_number', '10', '10th', 10, 0, 0, '2016-02-09 14:17:49', 'admin', '2020-07-21 11:15:35'),
(108, 'average_points', 'all_races', 'average of all races', 2, 0, 0, '2016-02-15 21:44:06', 'admin', '2020-07-21 11:15:35'),
(109, 'average_points', 'all_competed', 'average of races competed', 1, 1, 0, '2016-02-15 21:55:44', 'admin', '2020-07-21 11:15:35'),
(110, 'average_points', 'all_counting', 'average of non-discarded races', 3, 0, 0, '2016-02-15 21:55:53', 'admin', '2020-07-21 11:15:35'),
(138, 'signal_flags', 'ics~0.gif', 'number 0 flag', 100, 0, 1, '2020-07-21 14:07:01', 'admin', '2020-07-21 10:19:45'),
(155, 'duty_points', '0', 'average points', 1, 1, 0, '2016-03-31 12:33:14', 'admin', '2020-07-21 11:15:35'),
(178, 'signal_flags', 'ics~1s.gif', '1st Sub flag', 51, 0, 0, '2020-07-21 14:22:28', 'marke', '2020-07-21 14:21:48'),
(177, 'signal_flags', 'ics~ap.gif', 'AP flag', 50, 0, 0, '2020-07-21 14:21:48', 'marke', '2020-07-21 14:21:48'),
(137, 'signal_flags', 'ics~z.gif', 'Z flag', 43, 0, 0, '2020-07-21 14:06:48', 'admin', '2020-07-21 10:19:45'),
(136, 'signal_flags', 'ics~y.gif', 'Y flag', 42, 0, 1, '2016-03-26 17:54:47', 'admin', '2020-07-21 10:19:45'),
(135, 'signal_flags', 'ics~x.gif', 'X flag', 41, 0, 0, '2020-07-21 14:17:24', 'admin', '2020-07-21 10:19:45'),
(134, 'signal_flags', 'ics~w.gif', 'W flag', 40, 0, 1, '2016-03-26 17:54:47', 'admin', '2020-07-21 10:19:45'),
(133, 'signal_flags', 'ics~v.gif', 'V flag', 39, 0, 1, '2016-03-26 17:54:47', 'admin', '2020-07-21 10:19:45'),
(132, 'signal_flags', 'ics~u.gif', 'U flag', 38, 0, 0, '2020-07-21 14:16:53', 'admin', '2020-07-21 10:19:45'),
(131, 'signal_flags', 'ics~t.gif', 'T flag', 37, 0, 1, '2016-03-26 17:54:47', 'admin', '2020-07-21 10:19:45'),
(130, 'signal_flags', 'ics~s.gif', 'S flag', 36, 0, 0, '2020-07-21 14:06:33', 'admin', '2020-07-21 10:19:45'),
(129, 'signal_flags', 'ics~r.gif', 'R flag', 35, 0, 1, '2016-03-26 17:54:47', 'admin', '2020-07-21 10:19:45'),
(128, 'signal_flags', 'ics~q.gif', 'Q flag', 34, 0, 1, '2016-03-26 17:54:47', 'admin', '2020-07-21 10:19:45'),
(127, 'signal_flags', 'ics~p.gif', 'P flag', 33, 0, 0, '2020-07-21 14:05:25', 'admin', '2020-07-21 10:19:45'),
(126, 'signal_flags', 'ics~o.gif', 'O flag', 32, 0, 1, '2016-03-26 17:54:47', 'admin', '2020-07-21 10:19:45'),
(125, 'signal_flags', 'ics~n.gif', 'N flag', 31, 0, 0, '2020-07-21 14:05:38', 'admin', '2020-07-21 10:19:45'),
(124, 'signal_flags', 'ics~m.gif', 'M flag', 30, 0, 0, '2020-07-21 14:16:22', 'admin', '2020-07-21 10:19:45'),
(123, 'signal_flags', 'ics~l.gif', 'L flag', 29, 0, 0, '2020-07-21 14:05:29', 'admin', '2020-07-21 10:19:45'),
(122, 'signal_flags', 'ics~k.gif', 'K flag', 28, 0, 1, '2016-03-26 17:54:47', 'admin', '2020-07-21 10:19:45'),
(121, 'signal_flags', 'ics~j.gif', 'J flag', 27, 0, 1, '2016-03-26 17:54:47', 'admin', '2020-07-21 10:19:45'),
(120, 'signal_flags', 'ics~i.gif', 'I flag', 26, 0, 0, '2020-07-21 14:06:11', 'admin', '2020-07-21 10:19:45'),
(119, 'signal_flags', 'ics~h.gif', 'H flag', 25, 0, 0, '2020-07-21 14:15:54', 'admin', '2020-07-21 10:19:45'),
(118, 'signal_flags', 'ics~g.gif', 'G flag', 24, 0, 1, '2016-03-26 17:54:47', 'admin', '2020-07-21 10:19:45'),
(117, 'signal_flags', 'ics~f.gif', 'F flag', 23, 0, 1, '2016-03-26 17:54:47', 'admin', '2020-07-21 10:19:45'),
(116, 'signal_flags', 'ics~e.gif', 'E flag', 22, 0, 1, '2016-03-26 17:54:47', 'admin', '2020-07-21 10:19:45'),
(115, 'signal_flags', 'ics~d.gif', 'D flag', 21, 0, 1, '2016-03-26 17:54:47', 'admin', '2020-07-21 10:19:45'),
(114, 'signal_flags', 'ics~c.gif', 'C flag', 20, 0, 0, '2020-07-21 14:05:42', 'admin', '2020-07-21 10:19:45'),
(112, 'signal_flags', 'ics~b.gif', 'B flag', 19, 0, 1, '2016-03-26 17:55:45', 'admin', '2020-07-21 10:19:45'),
(111, 'signal_flags', 'ics~a.gif', 'A flag', 18, 0, 0, '2020-07-21 14:15:28', 'admin', '2020-07-21 10:19:45'),
(154, 'signal_flags', 'cf~yellow.gif', 'yellow flag', 16, 0, 0, '2020-07-21 14:15:03', 'admin', '2020-07-21 10:19:45'),
(153, 'signal_flags', 'cf~white.gif', 'white flag', 15, 0, 0, '2020-07-21 14:14:56', 'admin', '2020-07-21 10:19:45'),
(152, 'signal_flags', 'cf~red.gif', 'red flag', 14, 0, 1, '2016-03-26 17:54:47', 'admin', '2020-07-21 10:19:45'),
(151, 'signal_flags', 'cf~orange.gif', 'orange flag', 13, 0, 0, '2020-07-21 14:14:50', 'admin', '2020-07-21 10:19:45'),
(148, 'signal_flags', 'cf~black.gif', 'black flag', 10, 0, 1, '2016-03-26 17:54:47', 'admin', '2020-07-21 10:19:45'),
(149, 'signal_flags', 'cf~blue.gif', 'blue flag', 11, 0, 1, '2016-03-26 17:54:47', 'admin', '2020-07-21 10:19:45'),
(150, 'signal_flags', 'cf~green.gif', 'green flag', 12, 0, 0, '2020-07-21 14:14:42', 'admin', '2020-07-21 10:19:45'),
(142, 'signal_flags', 'ics~4.gif', 'number 4 flag', 104, 0, 1, '2020-07-21 14:07:29', 'admin', '2020-07-21 10:19:45'),
(141, 'signal_flags', 'ics~3.gif', 'number 3 flag', 103, 0, 0, '2020-07-21 14:17:47', 'admin', '2020-07-21 10:19:45'),
(140, 'signal_flags', 'ics~2.gif', 'number 2 flag', 102, 0, 0, '2020-07-21 14:17:42', 'admin', '2020-07-21 10:19:45'),
(139, 'signal_flags', 'ics~1.gif', 'number 1 flag', 101, 0, 0, '2020-07-21 14:09:27', 'admin', '2020-07-21 10:19:45'),
(156, 'duty_points', '1', '1 point', 2, 0, 1, '2016-03-31 12:29:34', 'admin', '2020-07-21 11:15:35'),
(157, 'duty_points', '2', '2 points', 3, 0, 1, '2016-03-31 12:29:24', 'admin', '2020-07-21 11:15:35'),
(158, 'duty_points', '3', '3 points', 4, 0, 1, '2016-03-31 12:29:31', 'admin', '2020-07-21 11:15:35'),
(159, 'duty_points', '4', '4 points', 5, 0, 1, '2016-03-31 12:29:38', 'admin', '2020-07-21 11:15:35'),
(160, 'event_type', 'dcruise', 'dinghy cruise', 15, 0, 1, '2019-06-17 14:20:51', 'new', '2020-07-21 11:15:35'),
(161, 'rota_type', 'ood_p', 'Race Officer', 2, 0, 0, '2016-04-05 15:37:28', 'admin', '2020-07-21 11:15:35'),
(162, 'rota_type', 'ood_a', 'Assistant Race Officer', 4, 0, 0, '2016-04-05 15:37:36', 'admin', '2020-07-21 11:15:35'),
(163, 'rota_type', 'safety_d', 'Safety Driver', 6, 0, 0, '2016-04-05 15:37:49', 'admin', '2020-07-21 11:15:35'),
(164, 'rota_type', 'safety_c', 'Safety Crew', 8, 0, 0, '2016-04-05 15:37:56', 'admin', '2020-07-21 11:15:35'),
(165, 'rota_type', 'galley', 'Galley', 10, 0, 0, '2016-04-01 13:47:49', 'admin', '2020-07-21 11:15:35'),
(166, 'rota_type', 'bar', 'Bar', 12, 0, 0, '2016-04-05 15:38:06', 'admin', '2020-07-21 11:15:35'),
(169, 'event_type', 'cruise', 'cruise', 20, 0, 1, '2019-06-17 14:18:18', 'new', '2020-07-21 11:15:35'),
(170, 'event_type', 'paddle', 'kayak', 25, 0, 1, '2019-06-17 14:20:57', 'new', '2020-07-21 11:15:35'),
(171, 'yes_no', '1', 'yes', 1, 1, 0, '2019-07-01 17:48:14', 'new', '2020-07-21 11:15:35'),
(172, 'yes_no', '0', 'no', 2, 0, 0, '2019-07-01 17:49:38', 'new', '2020-07-21 11:15:35'),
(173, 'event_type', 'freesail', 'water sports', 22, 0, 1, '2022-08-23 12:48:30', 'admin', '2020-07-21 11:15:35'),
(174, 'rota_type', 'ood_c', 'Dinghy Cruise Officer', 3, 0, 0, '2019-10-10 14:11:47', 'admin', '2020-07-21 11:15:35'),
(175, 'event_type', 'noevent', 'no event', 30, 0, 1, '2019-10-29 23:47:54', 'admin', '2020-07-21 11:15:35'),
(176, 'class_category', 'P', 'paddled craft', 30, 0, 1, '2020-07-02 23:56:53', 'admin', '2020-07-21 11:15:35'),
(179, 'class_category', 'D', 'dinghy', 5, 1, 0, '2020-07-21 15:02:49', 'admin', '2020-07-21 11:15:35'),
(143, 'signal_flags', 'ics~5.gif', 'number 5 flag', 105, 0, 1, '2020-07-21 14:07:38', 'admin', '2020-07-21 10:19:45'),
(144, 'signal_flags', 'ics~6.gif', 'number 6 flag', 106, 0, 1, '2020-07-21 14:07:46', 'admin', '2020-07-21 10:19:45'),
(145, 'signal_flags', 'ics~7.gif', 'number 7 flag', 107, 0, 1, '2020-07-21 14:07:53', 'admin', '2020-07-21 10:19:45'),
(146, 'signal_flags', 'ics~8.gif', 'number 8 flag', 108, 0, 1, '2020-07-21 14:08:01', 'admin', '2020-07-21 10:19:45'),
(147, 'signal_flags', 'ics~9.gif', 'number 9 flag', 109, 0, 1, '2020-07-21 14:08:09', 'admin', '2020-07-21 10:19:45'),
(180, 'class_keel', 'N', 'none', 30, 0, 0, '2020-10-08 12:50:23', 'admin', '2020-10-08 11:50:23'),
(181, 'class_rig', 'N', 'none', 5, 0, 0, '2020-10-08 12:50:50', 'admin', '2020-10-08 11:50:50'),
(182, 'system_pages', 'reminder', 'reminder page', 20, 0, 0, '2021-09-14 15:37:05', 'admin', '2021-09-14 15:36:03'),
(183, 'results_style', 'classic', 'classic', 1, 1, 0, '2021-10-13 17:48:14', 'admin', '2020-07-21 11:15:35'),
(184, 'system_pages', 'pursuit', 'pursuit page', 6, 0, 0, '2016-02-16 11:54:34', 'admin', '2020-07-21 11:15:35'),
(185, 'declaration_status', 'X', 'Not declared', 1, 1, 0, '2021-11-08 13:08:27', 'admin', '2021-11-08 12:08:27'),
(186, 'declaration_status', 'D', 'Declared', 5, 0, 0, '2021-11-08 13:08:27', 'admin', '2021-11-08 12:08:27'),
(187, 'declaration_status', 'R', 'Retired', 10, 0, 0, '2021-11-08 13:08:27', 'admin', '2021-11-08 12:08:27'),
(191, 'system_pages', 'pickrace', 'programme page', 0, 0, 0, '2022-07-19 16:41:27', 'admin', '2022-07-19 15:41:27'),
(192, 'class_category', 'W', 'windsurfer', 40, 0, 1, '2022-08-23 12:49:26', 'admin', '2022-08-23 11:49:26'),
(193, 'rota_type', 'ood_b', 'Beachmaster', 5, 0, 0, '2022-08-23 12:50:41', 'admin', '2022-08-23 11:50:41');

--
-- Dumping data for table `t_code_type`
--

INSERT INTO `t_code_type` (`id`, `groupname`, `label`, `info`, `rank`, `type`, `upddate`, `updby`, `createdate`) VALUES
(1, 'class_category', 'class type', 'general type of boat used in RYA classification scheme used when adding a new class', 1, 'system', '2020-07-02 11:18:17', NULL, '2020-07-02 11:28:48'),
(2, 'class_engine', 'class engine type', 'classification of engine type used in RYA scheme (only relevant to cruisers) used when adding a new class', 1, 'system', '2020-07-02 11:18:17', NULL, '2020-07-02 11:28:48'),
(3, 'class_keel', 'class keel type', 'classification of keel used when adding a new class', 1, 'system', '2020-07-02 11:18:17', NULL, '2020-07-02 11:28:48'),
(4, 'class_crew', 'class crew number', 'number of crew classification used when adding a new boat', 2, 'system', '2020-07-02 11:18:17', NULL, '2020-07-02 11:28:48'),
(5, 'class_rig', 'class rig type', 'rig type classification used when adding a new class', 1, 'system', '2020-07-02 11:18:17', NULL, '2020-07-02 11:28:48'),
(6, 'class_spinnaker', 'class spinnaker type', 'spinnaker type classification used when adding a new class', 1, 'system', '2020-07-02 11:18:17', NULL, '2020-07-02 11:28:48'),
(7, 'entry_type', 'entry type', 'options for entering competitors into a race', 1, 'system', '2020-07-02 11:18:17', NULL, '2020-07-02 11:28:48'),
(8, 'event_type', 'event type', 'type of event (e.g. racing, cruising, etc.)', 1, 'system', '2020-07-02 11:18:17', NULL, '2020-07-02 11:28:48'),
(9, 'event_status', 'race status', 'the system states for a programmed race', 1, 'system', '2020-07-02 11:18:17', NULL, '2020-07-02 11:28:48'),
(25, 'event_access', 'event access', 'who is the event for', 1, 'system', '2022-06-20 10:36:18', NULL, '2020-07-02 11:28:48'),
(11, 'start_scheme', 'start scheme', 'Timing schemes for starting sequence', 1, 'system', '2020-07-02 11:18:17', NULL, '2020-07-02 11:28:48'),
(12, 'competitor_status', 'competitor status', 'status of competitor record', 1, 'system', '2020-07-02 11:18:17', NULL, '2020-07-02 11:28:48'),
(13, 'competitor_list', 'competitor list', 'club defined group names for competitors', 1, 'club', '2020-07-02 11:18:17', NULL, '2020-07-02 11:28:48'),
(14, 'competitor_skill', 'competitor skill', 'simple skill level classification', 1, 'system', '2020-07-02 11:18:17', NULL, '2020-07-02 11:28:48'),
(15, 'prize_list', 'prize list groups', 'predefined prize eligibility groups', 1, 'club', '2020-07-02 11:18:17', NULL, '2020-07-02 11:28:48'),
(16, 'flight_list', 'flight names', 'flight names to be use in flight races', 1, 'club', '2020-07-02 11:18:17', NULL, '2020-07-02 11:28:48'),
(17, 'system_pages', 'system pages', 'allows different links and help text to be displayed on each page', 1, 'system', '2022-06-20 10:36:32', NULL, '2020-07-02 11:28:48'),
(18, 'event_display', 'event display type', 'determines where event is displayed - using WRS scheme for website, racebox and sailor', 1, 'system', '2022-06-20 10:36:37', NULL, '2020-07-02 11:28:48'),
(19, 'ordinal_number', 'ordinal number', 'list of ordinal numbers - recording integer equivalent', 1, 'system', '2020-07-02 11:18:17', NULL, '2020-07-02 11:28:48'),
(20, 'average_points', 'average points', 'options for calculating average points in series', 1, 'system', '2020-07-02 11:18:17', NULL, '2020-07-02 11:28:48'),
(21, 'race_type', 'race type', 'scoring system used for a race', 1, 'system', '2020-07-02 11:18:17', NULL, '2020-07-02 11:28:48'),
(22, 'signal_flags', 'signal flags', 'flags used to start races', 1, 'system', '2020-07-02 11:18:17', NULL, '2020-07-02 11:28:48'),
(23, 'duty_points', 'duty points', 'options for allocating points for doing a club duty ', 1, 'system', '2022-06-20 10:36:44', NULL, '2020-07-02 11:28:48'),
(24, 'rota_type', 'rota type', 'list of rota codes', 1, 'system', '2020-07-02 11:18:17', NULL, '2020-07-02 11:28:48'),
(26, 'yes_no', 'yes / no', 'Simple yes (=1), no (=0) option', 1, 'system', '2020-07-02 11:18:17', NULL, '2020-07-02 11:28:48'),
(27, 'declaration_status', 'declaration status', 'codes used to describe competitor declaration status for a race', 1, 'system', '2021-11-08 13:07:42', 'admin', '2021-11-08 12:07:42');

--
-- Dumping data for table `t_competitor`
--

INSERT INTO `t_competitor` (`id`, `classid`, `boatnum`, `sailnum`, `boatname`, `hullcolour`, `helm`, `helm_dob`, `helm_email`, `telephone`, `crew`, `crew_dob`, `crew_email`, `club`, `personal_py`, `skill_level`, `flight`, `regular`, `last_entry`, `last_event`, `active`, `prizelist`, `grouplist`, `memberid`, `trackerid`, `upddate`, `updby`, `createdate`) VALUES
(1, 38, '123456', '123456', 'rita', 'white', 'Ben Ainslie', NULL, NULL, NULL, NULL, NULL, NULL, 'MyClub SC', NULL, '1', NULL, 0, NULL, NULL, 1, NULL, 'olympians', NULL, NULL, '2022-11-11 19:06:52', 'admin', '2022-11-11 19:06:52'),
(2, 59, '1411', '1411', '', '', 'Ian Percy', NULL, NULL, NULL, 'Andrew Bart Simpson', NULL, NULL, 'MyClub SC', NULL, '1', NULL, 0, NULL, NULL, 1, NULL, 'olympians', NULL, NULL, '2022-11-11 19:06:52', 'admin', '2022-11-11 19:06:52'),
(3, 62, '742', '742', '', '', 'Chris Draper', NULL, NULL, NULL, '', NULL, NULL, 'MyClub SC', NULL, '1', NULL, 0, NULL, NULL, 1, NULL, 'olympians', NULL, NULL, '2022-11-11 19:06:52', 'admin', '2022-11-11 19:06:52'),
(4, 165, '2156', '2156', '', '', 'Hannah Mills', NULL, NULL, NULL, 'Saskia Clark', NULL, NULL, 'MyClub SC', NULL, '1', NULL, 0, NULL, NULL, 1, NULL, 'olympians', NULL, NULL, '2022-11-11 19:06:52', 'admin', '2022-11-11 19:06:52'),
(5, 105, '909', '909', '', '', 'Shirley Robertson', NULL, NULL, NULL, '', NULL, NULL, 'MyClub SC', NULL, '1', NULL, 0, NULL, NULL, 1, NULL, 'olympians', NULL, NULL, '2022-11-11 19:06:52', 'admin', '2022-11-11 19:06:52'),
(6, 1067, '163', '163', '', '', 'Rodney Pattisson', NULL, NULL, NULL, 'Iain MacDonald-Smith', NULL, NULL, 'MyClub SC', NULL, '1', NULL, 0, NULL, NULL, 1, NULL, 'olympians', NULL, NULL, '2022-11-11 19:06:52', 'admin', '2022-11-11 19:06:52');

--
-- Dumping data for table `t_event`
--

INSERT INTO `t_event` (`id`, `event_date`, `event_start`, `event_order`, `event_name`, `series_code`, `event_type`, `event_format`, `event_entry`, `event_status`, `event_open`, `event_ood`, `tide_time`, `tide_height`, `start_scheme`, `start_interval`, `timerstart`, `ws_start`, `wd_start`, `ws_end`, `wd_end`, `event_notes`, `result_notes`, `result_valid`, `result_publish`, `weblink`, `webname`, `display_code`, `active`, `upddate`, `updby`, `createdate`) VALUES
(1, '2023-04-02', '10:30', 1, 'myClub Series 1', 'MYCLUBSERIES-23', 'racing', 1, 'signon', 'scheduled', 'club', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'W,R,S', 0, '2022-11-12 10:43:34', 'admin', '2022-11-12 10:20:00'),
(2, '2023-04-02', '12:30', 2, 'myClub Series 2', 'MYCLUBSERIES-23', 'racing', 1, 'signon', 'scheduled', 'club', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'W,R,S', 0, '2022-11-12 10:43:29', 'admin', '2022-11-12 10:20:00'),
(3, '2023-04-09', '10:30', 1, 'myClub Series 3', 'MYCLUBSERIES-23', 'racing', 1, 'signon', 'scheduled', 'club', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'W,R,S', 0, '2022-11-12 10:43:47', 'admin', '2022-11-12 10:20:00'),
(4, '2023-04-09', '12:30', 2, 'myClub Series 4', 'MYCLUBSERIES-23', 'racing', 1, 'signon', 'scheduled', 'club', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'W,R,S', 0, '2022-11-12 10:45:48', 'admin', '2022-11-12 10:20:00'),
(5, '2023-04-16', '12:30', 1, 'myClub Series DEMO', 'MYCLUBSERIES-23', 'racing', 1, 'signon', 'scheduled', 'club', 'Elmer Fudd', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 'W,R,S', 0, '2022-11-12 10:43:47', 'admin', '2022-11-12 10:20:00');

--
-- Dumping data for table `t_eventduty`
--

INSERT INTO `t_eventduty` (`id`, `eventid`, `dutycode`, `person`, `swapable`, `phone`, `email`, `notes`, `memberid`, `upddate`, `updby`, `createdate`) VALUES
(1, 5, 'ood_p', 'Elmer Fudd', 1, '01626 555981', 'elmer.fudd@me.com', NULL, NULL, '2022-11-12 11:57:39', 'admin', '2022-11-12 11:57:39'),
(2, 5, 'ood_a', 'Bugs Bunny', 1, '01626 555981', 'bugs.bunny@me.com', NULL, NULL, '2022-11-12 11:57:39', 'admin', '2022-11-12 11:57:39'),
(3, 5, 'safety_d', 'Yosemite Sam', 1, '01626 555981', 'yosemite.sam@me.com', NULL, NULL, '2022-11-12 11:59:42', 'admin', '2022-11-12 11:57:39'),
(4, 5, 'safety_d', 'Foghorn Leghorn', 1, '01626 555981', 'foghorn.leghorn@me.com', NULL, NULL, '2022-11-12 11:59:42', 'admin', '2022-11-12 11:57:39'),
(5, 5, 'galley', 'Speedy Gonzales', 1, '01626 555981', 'speedy.gonzales@me.com', NULL, NULL, '2022-11-12 12:01:17', 'admin', '2022-11-12 11:57:39'),
(6, 5, 'bar', 'Daffy Duck', 1, '01626 555981', 'daffy.duck@me.com', NULL, NULL, '2022-11-12 12:01:45', 'admin', '2022-11-12 11:57:39');

--
-- Dumping data for table `t_help`
--

INSERT INTO `t_help` (`id`, `category`, `question`, `answer`, `notes`, `author`, `pursuit`, `eventname`, `format`, `startdate`, `enddate`, `multirace`, `rank`, `active`, `upddate`, `updby`, `createdate`) VALUES
(1, 'reminder', 'Using the SYC Course Guide', '\n            <p>The Sailing Committee has published suggested courses for each of the cardinal wind directions. The\n                courses also include options for light winds and low tide conditions. Each course is shown exactly as it\n                should be displayed on the course board and has some tips on how to position the inflatable buoys.\n            </p>\n\n            <p>A paper copy of the course guide should be on the OOD desk in the race box -\n                <a href=\"https://www.starcrossyc.org.uk/racing/apps/course-guide\" target=\"_blank\">there is an online\n                    version here\n                </a>\n            </p>\n            <p>An online copy of the buoy locations <a\n                    href=\"https://www.starcrossyc.org.uk/racing/essentials/buoy-chart\" target=\"_blank\">can be found here\n            </a>\n            </p>\n        ', '\n        <p>As OOD you are of course free to come up with your own race course - but in this case please try to check\n            with a regular sailor from each fleet that the proposed course is OK</p>\n        ', 'Mark Elkington - SYC', 0, NULL, NULL, NULL, NULL, 0, 5, 1, '2022-08-27 13:00:38', '', '2022-08-27 13:00:38'),
(2, 'reminder', 'Some reminders on running a pursuit race', 'TO BE COMPLETED', '', 'Mark Elkington - SYC', 1, '', NULL, NULL, NULL, 0, 5, 1, '2022-09-01 14:06:37', '', '2022-08-27 13:00:38'),
(3, 'reminder', 'Managing Multiple Races', '            <p>Some suggestions if you are scheduled to run more than one race in a day at Starcross YC.\r\n            <p>\r\n            <p>First of all when setting the course for the first race - consider what changes you will make to the\r\n                course for subsequent races to keep it interesting for the competitors. It is generally relatively easy\r\n                to adjust the amount of running / broad reaching /and close reaching by simply changing a single buoy or\r\n                alternatively moving the inflatable buoys. Making these small changes will make it fairer for all types\r\n                of boat racing.\r\n            </p>\r\n            <p>Organise a briefing for competitors before the first race. Explain the following:</p>\r\n            <ul>\r\n                <li>you will try to turn around the races quickly - the aim will be that the warning signal for the\r\n                    second race will be made within 5 minutes of the last boat finishing the first race. If this is not\r\n                    possible a postponement will be signalled - the postponement will be cancelled no less than 3\r\n                    minutes before the warning signal for the first start.\r\n                </li>\r\n                <li>you will likely change the course between races - watch for Flag C and a sound signal</li>\r\n            </ul>\r\n            <p>\r\n                <b>FIRST RACE</b> - run this as usual - selecting the relevant race on the raceManager racebox PROGRAMME page. Do not let\r\n                this race overrun and be ready to shorten course as necessary. While the final few boats are finishing -\r\n                ask the safety boats to adjust the course for the second race.\r\n            </p>\r\n            <p>\r\n            Once all the boats have finished, PUBLISH the results (on the results page) even if there are still some\r\n                issues to resolve (this has the effect of taking a backup ciopy of all the results for safety). Take\r\n                note of any outstanding issues - but leave them until all the races are complete. Do NOT close the race\r\n                at this stage.\r\n            </p>\r\n            <p>\r\n                <b>SECOND RACE</b> - go back to the PROGRAMME page and select the second race and run it as normal. To restart the lights\r\n                box turn the power off for 30 seconds and then power on. You can now start the race as usual. Once all\r\n                the boats have finished this race, PUBLISH the results but do NOT close the race\r\n            </p>\r\n            <p>Now return to the racebox PROGRAMME page and select the first race again. Resolve any outstanding\r\n                issues with the results for this race and republish if necessary. You can now CLOSE this race. Repeat\r\n                the process for race 2.\r\n            </p>\r\n        ', '            <p>Note: leaving outstanding results issues until all races have bee compleed takes the pressure off the OOD\r\n                team trying to sort out the result for Race 1 while starting Race 2. Publishing the interim results\r\n                immediately after each race is complete - before correcting any issues - has the effect of creating a\r\n                safety backup of the data. It is a good idea to select \"no upload to the website\" when you select the\r\n                interim publishing option.\r\n            </p>\r\n        ', 'Mark Elkington - SYC', 0, NULL, NULL, NULL, NULL, 1, 6, 1, '2022-09-01 13:47:17', '', '2022-08-27 13:00:38'),
(4, 'pickrace', 'what is the PROGRAMME page for?', '\n            <p>This page presents the race(s) to be run today and allows you to pick the race you want to deal with. To\n                access the race just click the <b>RUN RACE</b> button and it will be presented in the browser window.\n            </p>\n            <p>To switch to another race from within raceManager - you can go back to this page by clicking the green <b>\n                racebox: “racename”\n            </b> link at the top left of each page. To go back to the race click the <b>BACK TO RACE</b> button.\n            </p>\n            <p>To check the format of any race on today’s programme - click the black ‘list’ icon within the relevant\n                race display.\n            </p>\n            <p>If the race you want to run is not shown then you can use the <b>ADD NEW RACE</b> button to add the race\n                to the raceManager system.\n            </p>\n        ', '\n            <p>Note:<p></p>At Starcross YC it will be <u>very unusual</u> if you need to add a new race to the programme\n                - probably worth checking with a member of the Sailing Committee before you do this\n            </p>\n            ', 'Mark Elkington - SYC', 0, NULL, NULL, NULL, NULL, 0, 0, 1, '2022-08-27 13:00:38', '', '2022-08-27 13:00:38'),
(5, 'pickrace', 'Where can I find the Sailing Instructions?', '\n            <p>The Starcross YC Sailing Instructions for club racing can be found on the club website <a\n                href=\"https://www.starcrossyc.org.uk/racing/essentials/sailing-instructions-v3\" target=\"_BLANK\">on this\n            page\n        </a>\n        ', '\n            <p>If you are running an event open to non-club members then it may have specific sailing instructions which\n            may be found on the SYC events page <a href=\"https://www.starcrossyc.org.uk/open-events\" target=\"_BLANK\">on\n            this page\n        </a>\n        ', 'Mark Elkington - SYC', 0, NULL, NULL, NULL, NULL, 0, 5, 1, '2022-08-27 13:00:38', '', '2022-08-27 13:00:38'),
(6, 'pickrace', 'What course should I use for each fleet', '\n            <p>The SYC Course Guide gives suggestions for courses to use for dinghies and multihulls. There should be a\n                copy of the course guide on the OOD desk.\n            </p>\n            <p>In general you should try and avoid the dinghies using the same marks - especially in any breeze. If you\n                want to lay marks for the dinghy fleets and the multihulls in the same area - the multihull mark should\n                have a black band.\n            </p>\n            <p>The multihulls prefer a larger course and a windward/leeward configuration if the wind direction allows\n                it. Typically the dinghy courses will be \'inside\' the multihull course.\n            </p>\n        ', '', 'Mark Elkington - SYC', 0, NULL, NULL, NULL, NULL, 0, 10, 1, '2022-08-27 13:00:38', '', '2022-08-27 13:00:38'),
(7, 'race', 'what is the STATUS page for?', '\n            <p>This page gives you information on the race you are about to run - specifically the race format (start\n                sequence, signals etc.). It also allows you to change some details (e.g. name of race officer)\n            </p>\n            <p>It also allows you to:</p>\n            <ul>\n                <li>Set the number of laps for each fleet</li>\n                <li>Send an email to the team responsible for managing your raceManager system</li>\n                <li>Mark the race as ABANDONED or CANCELLED</li>\n                <li>CLOSE the race when you have accounted for all competitors</li>\n                <li>RESET the entire race if you want to start again</li>\n            </ul>\n            <p>You will normally use this page at the beginning of your race officer duty to check the race format and\n                set laps - and at the end of the race to indicate if it was abandoned / cancelled or to close a\n                successfully completed race. You might also wish to send a message to the raceManager team to explain\n                any problems you had with the software or issues with the results.</p>\n            ', '', 'Mark Elkington - SYC', 0, NULL, NULL, NULL, NULL, 0, 0, 1, '2022-08-27 13:00:38', '', '2022-08-27 13:00:38'),
(8, 'race', 'how do I reset a race?', '\n            <p>If you really want to start the entire race again in raceManager there is a reset option on the STATUS\n                page. Click the\n                RESET button and then enter the confirmation word and click the submit button to activate the reset\n            </p>\n            <p>You will lose all the entries and laptimes that you have entered. However if you are using the SAILOR\n                application for collecting entries these will still be available for you to reenter in the normal\n                way.\n            </p>\n\n        ', '\n            <p>\n                <b>DO NOT USE THIS APPROACH if you have already started a fleet</b>\n                - if you still want to start the entire race again do the following, signal abandonment (instruct the\n                Safety Boats to\n                inform the competitors) and\n            </p>\n            <ul>\n                <li>stop the start sequence on the lights box (power off the lights box and then on again)</li>\n                <li>signal abandonment - red and orange beacons (instruct the Safety Boats to inform the competitors)\n                </li>\n                <li>use the raceManager RESET button as described above - then reload entries</li>\n                <li>change the course if necessary - use Flag C + two sound signals</li>\n                <li>restart the start sequence on the lights system and on raceManager - and run the rest of the race as\n                    normal\n                </li>\n            </ul>\n        ', 'Mark Elkington - SYC', 0, NULL, NULL, NULL, NULL, 0, 5, 1, '2022-08-27 13:00:38', '', '2022-08-27 13:00:38'),
(9, 'race,timer,results', 'how do I ABANDON a race?', '\n            <p>You can abandon a race at any time from the STATUS page - just click the Abandon button. You can also change\n                your mind and unabandon the race as long as you haven\'t missed recording lap times\n            </p>\n            <p>Once the race is abandoned you can then CLOSE the race - do NOT publish the partial results.</p>\n        </answer>\n        <p>\n            <b>IMPORTANT ... at Starcross YC our Sailing Instructions allow us to CURTAIL a club race rather than\n                abandon it.\n            </b>\n        </p>\n        <p>You SHOULD use this approach if all the boats still racing in a fleet have finished at least one lap as\n            this allows us to salvage a race result from a race that would otherwise be lost. It is most often used\n            if the wind dies to nothing towards the end of a race. To CURTAIL a race go to the results page and\n            use the Change Finish Lap option - this will have the following effect:\n        </p>\n        <ul>\n            <li>average lap racing: set the finish lap to the last one that the leading boat in the fleet\n                completed\n            </li>\n            <li>handicap/class racing: set the finish lap to the one that all boats in the fleet have completed</li>\n        </ul>\n        ', '\n        <p>Note: This function can also be useful if you have forgotten to shorten the race in raceManager, but have\n            signalled a finish to competitors. Simply set the finish lap to the shortened number of laps.</p>\n            ', 'Mark Elkington - SYC', 0, NULL, NULL, NULL, NULL, 0, 10, 1, '2022-08-27 13:00:38', '', '2022-08-27 13:00:38'),
(10, 'entries', 'what is the ENTRIES page for?', '\n            <p>This page allows you to enter boats into the race and manage the entries.</p>\n            <p>If you are using the sailor signon system in raceManager then you can use the LOAD ENTRIES button to load\n                everybody who signs on. When the button flashes red it means that there are some entries waiting to be\n                loaded.\n            </p>\n            <p>Alternatively you can use the ENTER BOATS button to search the database for boats to enter. You can\n                search for the sail number, class name, or helm’s surname. The system will find all matches in the\n                database and then you can select the boats to enter.\n            </p>\n            <p>The entries will be shown for each fleet as a separate tab - click the tab to see a fleet entries. You\n                can edit an entry - changing the crew, sail number, or handicap for this race only. You can also mark a\n                competitor for doing a club duty - they will be allocated points for the race according to the way your\n                raceManager system is configured.\n            </p>\n            <p>If you have new boats (i.e not in the database), or a new class of boat you should use the REGISTER NEW\n                CLASS and/or REGISTER NEW BOAT buttons to add the details to the database. Once in the database you can\n                enter them in the normal way.\n            </p>\n            <p>Finally you can use the PRINT ENTRIES button to get a list of entries in a variety of formats.</p>\n        ', '\n            <p>Note:<p></p>When you press the timer start button on the START page it will collect any late entries from\n                the sailor signon system automatically.\n            </p>\n        ', 'Mark Elkington - SYC', 0, NULL, NULL, NULL, NULL, 0, 0, 1, '2022-08-27 13:00:38', '', '2022-08-27 13:00:38'),
(11, 'entries', 'I can\'t find the boat I want to enter', '\n            <p>This generally only occurs if your club does not use the raceManager SAILOR application to allow the\n                competitor to create their own entry or if you hae a new competitor that isn\'t familiar with the SAILOR application.\n            </p>\n\n            <p>The Enter Boat button on the ENTRIES page allows you to search using sail number, class name, or helm\n                surname. If you cannot find the boat you are looking for it is probably because it is not in the\n                raceManager database. In this case you will need to add the new boat using the Register New Boat button\n                - just fill in the form.\n            </p>\n            <p>Important - after registering the new boat you will still need to use the Enter Boat button to enter it in the normal\n                way.\n            </p>\n        ', '\n            <p>In very rare cases you may have a new boat which belongs to a class that isn\'t recognised by raceManager.\n                In this case use the Register Class button first to create the new class. Then use he Register New Boat\n                button to create the new boat in the new class, and finally use the Enter Boat button to ener it into\n                the race.\n            </p>\n            ', 'Mark Elkington - SYC', 0, NULL, NULL, NULL, NULL, 0, 5, 1, '2022-08-27 13:00:38', '', '2022-08-27 13:00:38'),
(12, 'start', 'what is the START page for?', '\n            <p>This page is used to start the race timer and manage the start sequence.</p>\n            <p>Use the START TIMER button to start the raceManager clock at the same time as you make the first WARNING\n                signal (e.g. 6 minute gun) for the first fleet in the start sequence. This will start the master\n                countdown clock - and the individual fleet start countdowns. Once the countdowns to each start is\n                complete the clocks will show the elapsed time for each fleet.\n            </p>\n            <p>If you want to stop the clock and reset the start sequence (e.g if you decide to change the course) -\n                click the Timer button again (it will ask you to confirm the action by typing “stop”).\n            </p>\n            <p>30 seconds before each fleet starts the “Start Infringements” and “General Recall” buttons will be\n                enabled. Use the START INFRINGEMENTS button to record start line infringements such as OCS, DNS, DNC\n                etc.. If you decide to have a general recall use the GENERAL RECALL button to enter the wall clock time\n                for the actual start time of the fleet in question</p>\n        ', '\n            <p>Note:</p>\n            <ul>\n                <li>If you forget to start the Timer when you begin the flags/lights start sequence - use the “Forgot to\n                    start the timer” button. This will allow you to enter the wall clock time that you actually began\n                    the start sequence.\n                </li>\n                <li>The timer displays for each fleet will flash 30 seconds before each start to alert you to an\n                    upcoming start .\n                </li>\n            </ul>\n            ', 'Mark Elkington - SYC', 0, NULL, NULL, NULL, NULL, 0, 0, 1, '2022-08-27 13:00:38', '', '2022-08-27 13:00:38'),
(13, 'start', 'I forgot to start the Timer', '\n            <p>This is a relatively common mistake.</p>\n            <p>use the \"Forgot to Start Timer\" button on the TIMER page. This will allow you to enter the clock time\n                (hh:mm:ss) for the time you should have started the timer - i.e. the time you made the first signal in\n                the start sequence.\n            </p>\n        ', '\n            <p>Obviously you should try to input an accurate time for the first signal - but it does not need to be\n                accurate to within a few seconds - small errors are unlikely to have a significant impact on the results.\n            </p>\n        ', 'Mark Elkington - SYC', 0, NULL, NULL, NULL, NULL, 0, 5, 1, '2022-08-27 13:00:38', '', '2022-08-27 13:00:38'),
(14, 'start', 'How do I handle a general recall?', '\n            <p>If you signal a general recall for a fleet - that fleet must restart after remaining fleets have\n                started.   Use the following procedure:\n            </p>\n            <ul>\n                <li>restart the lights system (power off / power on) - set the number of starts to 1 (or however many fleets you have recalled)</li>\n                <li>start the recalled start sequence on the light box (take a note of the time of the warning signal</li>\n                <li>if you want to use the black flag - hoist at the 3 minute sound signal</li>\n                <li>on the START page in raceManager click the General Recall option for the fleet(s) in question - enter the clocktime (hh:mm:ss) of the start gun for the recalled fleet (i.e. the time you notes + 6 minutes)</li>\n                <li>. . . continue to run the race as normal</li>\n            </ul>\n        ', '', 'Mark Elkington - SYC', 0, NULL, NULL, NULL, NULL, 0, 10, 1, '2022-08-27 13:00:38', '', '2022-08-27 13:00:38'),
(15, 'timer', 'what is the TIMER page for?', '\n            <p>This is the page where you record lap times for each boat - and you will spend most of the race on this\n                page.\n            </p>\n            <p>The default display uses the Tabbed fleet organisation - click the relevant tab to view a fleet. To time\n                a lap - just click the watch next to the relevant boat and the lap time will appear. Colour shading is\n                used to indicate which boats are no longer racing, or on their last lap, or finished.\n            </p>\n            <p>You can also do the following for each boat:</p>\n            <ul>\n                <li>Delete (UNDO) the last lap time recorded</li>\n                <li>Set a scoring code (e.g. DNF, NSC etc.)</li>\n                <li>Edit the lap times</li>\n                <li>Add the boat to the ‘bunch’ list to help manage</li>\n                <li>Finish the boat at a lap other than the finish lap - average lap racing only.</li>\n            </ul>\n            <p>To shorten the course for one fleet - click the blue button above the list of boats - the race will then\n                finish on the next lap. Alternatively you can click the SHORTEN ALL FLEETS button on the right of the\n                page. This will shorten all fleets at their next lap.\n            </p>\n            <p>If you have many fleets and many competitors you may benefit from using one of the alternative TIMER\n                views - click the CHANGE VIEW option on the black menu bar. This will then give you the option of\n                condensed views which will allow you to see all boats racing without having to scroll or change tabs.\n                The three condensed views are organised by:\n            </p>\n            <ul>\n                <li>\n                    <b>Sail Number</b>\n                    - best option for lots of boats (50+)\n                </li>\n                <li>\n                    <b>Class</b>\n                    - best option if you have relatively few classes\n                </li>\n                <li>\n                    <b>Fleet</b>\n                    - best option if fleets tend not to be mixed up on the race course\n                </li>\n            </ul>\n            <p>Most of the functions of the tabbed display are available. Time laps by clicking the relevant boat button\n                - or use the gear wheel menu to get the other functions. You can switch between the different views at\n                any time. If you have relatively few classes the class view is probably the best option.</p>\n            ', '\n            <p>Note:</p>\n            <ul>\n                <li>The BUNCH facility is very useful to deal with groups of boats crossing the line in a bunch -\n                    further details are available in the separate help topic\n                    <b>“how do I use the BUNCH facility?”</b>\n                </li>\n            </ul>\n        ', 'Mark Elkington - SYC', 0, NULL, NULL, NULL, NULL, 0, 0, 1, '2022-08-27 13:00:38', '', '2022-08-27 13:00:38'),
(16, 'timer', 'I forgot to Shorten the course?', '\n            <p>It is a common mistake to signal a shortened course but forget to shorten the course in raceManager. You\n                signal the boats finish with the hooter, but they are not finished by raceManager\n            </p>\n            <p>This can easily be corrected after the race. On the RESULTS page find the \"Change Finish Lap\" function on\n                the right hand side of the screen. Click this and it will show the finish lap set for each fleet. Simply\n                set the actual finish lap for the fleet(s) you forgot to shorten and raceManager will automatically set\n                the boats to finished.\n            </p>\n            <p>For example if you had originally set the finish lap for fleet 1 to lap 4, but actually finished the\n                boats on lap 3 - just change the finish lap to 3.\n            </p>\n        ', '', 'Mark Elkington - SYC', 0, NULL, NULL, NULL, NULL, 0, 5, 1, '2022-08-27 13:00:38', '', '2022-08-27 13:00:38'),
(17, 'timer', 'How do I correct a lap time?', '\n            <p>On the TIMER page - using the \'tabbed\' view find the boat for which you want to make the correction.\n                Click the edit icon (pencil) and the lap times recorded for that boat will be shown. Edit the incorrect\n                lap times by entering the correct elapsed time in hh:mm:ss format.\n            </p>\n            <p>When you have completed the corrections - click the Update Lap Times button to apply them.</p>\n            <p>If you want to add a missed lap - just click the boat record to get a new lap - then use the edit button\n                again to make the relevant corrections to each lap.\n            </p>\n        ', '\n            <p>If the error you want to correct is the boat\'s finishing time (i.e. the last lap) - the edit function on the\n                RESULTS page provides a more comprehensive mechanism to correct this time and make other changes (e.g. no. of laps, apply\n                penalty points, change PY number, add crew name etc.)\n            </p>\n        ', 'Mark Elkington - SYC', 0, NULL, NULL, NULL, NULL, 0, 10, 1, '2022-08-27 13:00:38', '', '2022-08-27 13:00:38'),
(18, 'pursuit', 'what is the PURSUIT page for?', 'TO BE COMPLETED', '', 'Mark Elkington - SYC', 0, NULL, NULL, NULL, NULL, 0, 0, 1, '2022-08-27 13:00:38', '', '2022-08-27 13:00:38'),
(19, 'results', 'what is the RESULTS page for?', '\n            <p>This page allows you to check the race results and correct any mistakes, before publishing them . Once\n                published you can print the race results (and updated series results where relevant). The results will\n                also be made available on the club website.\n            </p>\n            <p>The results for each fleet are accessed in the normal way using the relevant tab at the top of the page.\n                The tabs include an indication of whether the results are complete or have problems that need resolving\n                (normally one or more boats who haven’t been finished or given a scoring code such as DNF). When you\n                click a fleet tab the system, when relevant, will present a list of possible problems with the results.\n            </p>\n            <p>You can correct the results - setting scoring codes and using the edit option to update details about the\n                competitor and their finishing time and no. of laps completed.\n            </p>\n            <p>Once you are happy the results are complete and correct - use the PUBLISH button to produce the results\n                files that you can print and will be automatically posted to the club website. As part of this process\n                you will need to add information on the wind conditions during the race and select some options for the\n                results process.</p>\n            ', '\n            <p>Note:</p>\n            <ul>\n                <li>You can correct and publish the results multiple times as necessary until the race is CLOSED on the\n                    status page.\n                </li>\n                <li>You can publish the results even if there are still outstanding warnings - but the results files\n                    will not be posted to the website - use the REPORT ISSUE button to send a message to your\n                    raceManager team.\n                </li>\n                <li>If you want to correct any of the lap times other than the finishing lap go to the TIMER page and\n                    use the edit option for the competitor in question.\n                </li>\n                <li>The results page gives you an option to change the lap used to finish each fleet - this can be\n                    useful if you signalled a shortened course and finished the boats, but forgot to record that\n                    shortened course in raceManager. Further details are provided in the separate help topic “I forgot\n                    to shorten the course”.\n                </li>\n            </ul>\n            ', 'Mark Elkington - SYC', 0, NULL, NULL, NULL, NULL, 0, 0, 1, '2022-08-27 13:00:38', '', '2022-08-27 13:00:38');

--
-- Dumping data for table `t_ini`
--

INSERT INTO `t_ini` (`id`, `category`, `parameter`, `label`, `value`, `notes`, `upddate`, `updby`, `createdate`) VALUES
(1, 'club', 'clubname', 'club name', 'MyClub Sailing Club', 'enter full name of club (e.g Starcross Yacht Club)', '2022-11-11 11:10:45', 'admin', '2022-11-11 11:10:45'),
(2, 'club', 'clubcode', 'club short name', 'MSC', 'common acronym for club e.g. SYC', '2022-11-11 11:10:45', 'admin', '2022-11-11 11:10:45'),
(3, 'club', 'clubweb', 'website address', 'www.myclubsc.org.uk', 'web address without http: e.g. www.starcrossyc.org.uk', '2022-11-11 18:53:59', 'admin', '2020-07-21 11:19:45'),
(5, 'club', 'appsupport', 'support team', 'For help contact: Yosemite Sam [Merlin 3797] or Daffy Duck [Laser 176836]', 'who should the user contact if they have problem with the system - or a web address link to more information', '2022-11-11 18:56:29', 'marke', '2020-07-21 11:19:45');

--
-- Dumping data for table `t_link`
--

INSERT INTO `t_link` (`id`, `label`, `url`, `tip`, `category`, `rank`, `upddate`, `updby`, `createdate`) VALUES
(1, 'Course Guide', 'https://www.starcrossyc.org.uk/racing/apps/course-guide', 'Some diagrams with suggested courses for different wind directions.  If in doubt ask one of the experienced racers', 'racebox_main', 1, '2022-08-25 07:51:56', '', '2020-07-21 11:19:45'),
(2, 'Sailing Instructions', 'https://www.starcrossyc.org.uk/racing/essentials/sailing-instructions-v3', 'Latest SYC sailing instructions for club racing - if you have any doubts about the SYC rules please ask an experienced racer.<br><br>Different SIs usually apply to open meetings.', 'racebox_main', 2, '2022-08-25 22:25:28', '', '2020-07-21 11:19:45'),
(6, 'Wind - Met Office', 'https://www.metoffice.gov.uk/weather/forecast/gcj81wbfq#', 'Wind forecast for Exmouth from the Met Office.', 'racebox_main', 4, '2022-08-24 11:07:29', '', '2020-07-21 11:19:45');

--
-- Dumping data for table `t_rotamember`
--

INSERT INTO `t_rotamember` (`id`, `memberid`, `firstname`, `familyname`, `rota`, `phone`, `email`, `note`, `partner`, `active`, `updby`, `createdate`, `upddate`) VALUES
(1, NULL, 'Elmer', 'Fudd', 'ood_p', '01626 555981', 'elmer.fudd@me.com', 'dummy rota member', NULL, 1, 'admin', '2022-11-11 18:46:22', '2022-11-11 18:46:50'),
(2, NULL, 'Bugs', 'Bunny', 'ood_a', '01626 555981', 'bugs.bunny@me.com', 'dummy rota member', NULL, 1, 'admin', '2022-11-11 18:46:22', '2022-11-11 18:46:50'),
(3, NULL, 'Yosemite', 'Sam', 'safety_d', '01626 555981', 'yosemite.sam@me.com', 'dummy rota member', NULL, 1, 'admin', '2022-11-11 18:46:22', '2022-11-11 18:46:50'),
(4, NULL, 'Foghorn', 'Leghorn', 'safety_d', '01626 555981', 'foghorn.leghorn@me.com', 'dummy rota member', NULL, 1, 'admin', '2022-11-11 18:46:22', '2022-11-11 18:46:50'),
(5, NULL, 'Speedy', 'Gonzales', 'galley', '01626 555981', 'speedy.gonzales@me.com', 'dummy rota member', NULL, 1, 'admin', '2022-11-11 18:46:22', '2022-11-11 18:46:50'),
(6, NULL, 'Daffy', 'Duck', 'bar', '01626 555981', 'daffy.duck@me.com', 'dummy rota member', NULL, 1, 'admin', '2022-11-11 18:46:22', '2022-11-11 18:46:50');

--
-- Dumping data for table `t_series`
--

INSERT INTO `t_series` (`id`, `seriescode`, `seriesname`, `seriestype`, `startdate`, `enddate`, `race_format`, `merge`, `classresults`, `opt_style`, `opt_turnout`, `opt_scorecode`, `opt_clubnames`, `opt_pagebreak`, `opt_racelabel`, `opt_upload`, `notes`, `active`, `upddate`, `updby`, `createdate`) VALUES
(1, 'MYCLUBSERIES', 'MyClub Series', 1, '01/04', '30/09', 1, NULL, '', 'classic', 1, 1, 0, 0, 'number', 1, 'demo series definition', 1, '2022-11-11 18:20:30', 'admin', '2020-07-21 11:19:46');

--
-- Dumping data for table `z_entry`
--

INSERT INTO `z_entry` (`id`, `action`, `protest`, `status`, `eventid`, `competitorid`, `memberid`, `chg-helm`, `chg-crew`, `chg-sailnum`, `entryid`, `upddate`, `updby`, `createdate`) VALUES
(2, 'enter', 0, 'N', 999, 1, NULL, '', '', '', 0, '2022-11-12 13:56:42', 'admin', '2022-03-06 09:38:04'),
(4, 'enter', 0, 'N', 999, 2, NULL, '', '', '', 0, '2022-11-12 13:56:42', 'admin', '2022-03-06 09:38:04'),
(5, 'enter', 0, 'N', 999, 3, NULL, '', '', '', 0, '2022-11-12 13:56:42', 'admin', '2022-03-06 09:38:04'),
(9, 'enter', 0, 'N', 999, 4, NULL, '', '', '', 0, '2022-11-12 13:56:42', 'admin', '2022-03-06 09:38:04'),
(11, 'enter', 0, 'N', 999, 5, NULL, '', NULL, '', 0, '2022-11-12 13:56:42', 'admin', '2022-03-06 09:38:04'),
(12, 'enter', 0, 'N', 999, 6, NULL, '', '', '', 0, '2022-11-12 13:56:42', 'admin', '2022-03-06 09:38:04');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
