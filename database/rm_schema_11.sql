-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 13, 2025 at 11:27 AM
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
-- Database: `pegasus`
--

-- --------------------------------------------------------

--
-- Table structure for table `a_entry`
--

CREATE TABLE `a_entry` (
  `id` int(11) NOT NULL,
  `action` ENUM('enter','delete','update','retire','replace','declare') COLLATE utf8mb4_general_ci NOT NULL COMMENT 'action for racemanager to take on entry',
  `protest` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 if declaring protest',
  `status` ENUM('N','L','F','E','A') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'N' COMMENT 'status (N=not loaded, L=loaded, F=failed. E=not eligible, A=not allocated)',
  `eventid` int(11) NOT NULL COMMENT 'event record id from t_event',
  `competitorid` int(11) NOT NULL COMMENT 'competitor record id from t_competitor',
  `memberid` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'memberid from t_competitor',
  `chg-helm` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'has value if helm name is changed',
  `chg-crew` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'temporary crew change for races today',
  `chg-sailnum` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'temporary sailno change for today',
  `entryid` int(11) DEFAULT '0' COMMENT 'id of entry into race table (t_race)',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT 'archive table used to store race entry information in the t_entry structure';

-- --------------------------------------------------------

--
-- Table structure for table `a_lap`
--

CREATE TABLE `a_lap` (
  `id` int(11) NOT NULL,
  `eventid` int(11) NOT NULL COMMENT 'event record id from t_event',
  `race` int(2) NOT NULL,
  `entryid` int(11) NOT NULL COMMENT 'entry id from rtblrace',
  `lap` int(3) NOT NULL COMMENT 'lap number',
  `clicktime` int(20) NOT NULL DEFAULT '0',
  `etime` int(6) NOT NULL DEFAULT '0' COMMENT 'elapsed time at end of lap (secs)',
  `ctime` int(6) DEFAULT '0' COMMENT 'corrected time at end of lap (secs)',
  `status` tinyint(1) DEFAULT '1' COMMENT 'field used to indicate whether this lap to be included in results (1 = include)',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time of last update',
  `updby` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT 'archive table used to store lap information in the t_lap structure';

-- --------------------------------------------------------

--
-- Table structure for table `a_race`
--

CREATE TABLE `a_race` (
  `id` int(11) NOT NULL,
  `eventid` int(11) NOT NULL DEFAULT '0' COMMENT 'event record id from t_event',
  `start` int(2) NOT NULL COMMENT 'start number',
  `fleet` int(2) NOT NULL COMMENT 'fleet  number',
  `competitorid` int(11) NOT NULL COMMENT 'competitor record id from t_competitor',
  `trackerid` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'identifier for position tracker',
  `helm` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'helm name',
  `crew` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'crew name',
  `club` varchar(60) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'club name',
  `class` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'boat class name',
  `classcode` varchar(6) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'class acronym',
  `sailnum` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'sail number used in race',
  `pn` int(5) NOT NULL DEFAULT '0' COMMENT 'portsmouth number used in this race',
  `clicktime` int(20) DEFAULT NULL COMMENT 'date/time of last timing click',
  `lap` int(3) NOT NULL DEFAULT '0' COMMENT 'completed laps',
  `finishlap` int(3) NOT NULL DEFAULT '1' COMMENT 'lap boat will finish on (this is only required for average lap racing - can I find a better way)',
  `etime` int(6) NOT NULL DEFAULT '0' COMMENT 'last elapsed time recorded (secs)',
  `ctime` int(6) NOT NULL DEFAULT '0' COMMENT 'last corrected time (secs)',
  `atime` int(6) NOT NULL DEFAULT '0' COMMENT 'aggregate time (pro-rata time for all boats doing the same number of laps in average lap race)',
  `ptime` int(6) NOT NULL DEFAULT '0' COMMENT 'predicted elapsed time for next lap (secs)',
  `f_line` int(2) DEFAULT NULL COMMENT 'pursuit finish line number (1 is first finish line)  only used for pursuit races',
  `f_pos` int(3) DEFAULT NULL COMMENT 'pursuit finish position (1 is first boat at that finish line) - only used for pursuit races',
  `code` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'result code for competitor (e.g. DNF)',
  `penalty` decimal(4,1) NOT NULL DEFAULT '0.0',
  `points` decimal(4,1) NOT NULL DEFAULT '0.0' COMMENT 'last points calculated',
  `pos` decimal(4,1) DEFAULT NULL COMMENT 'position allocated',
  `declaration` enum('D','R','X') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'X' COMMENT 'declaration status [X - not declared, D declared, R retired]',
  `protest` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'protest flag',
  `note` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'notes field',
  `status` enum('R','F','X','D') COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'status field (R = racing, F = finished, X = excluded, D = deleted',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT 'archive table used to store race information in the t_race structure';

-- --------------------------------------------------------

--
-- Table structure for table `e_consent`
--

CREATE TABLE `e_consent` (
  `id` int(11) NOT NULL,
  `eid` int(11) NOT NULL COMMENT 'event record id',
  `entryid` int(11) NOT NULL COMMENT 'id for related entry record',
  `parent_name` varchar(60) NOT NULL COMMENT 'name of parent or guardian',
  `parent_phone` varchar(20) NOT NULL COMMENT 'parent phone number',
  `parent_email` varchar(60) NOT NULL COMMENT 'email for parent or guardian',
  `parent_address` varchar(500) NOT NULL COMMENT 'address for parent / guardian',
  `alt_contact_detail` varchar(100) DEFAULT NULL COMMENT 'alternate contact details - name and phone',
  `child_name` varchar(60) NOT NULL COMMENT 'name of child',
  `child_dob` varchar(30) NOT NULL COMMENT 'child date of birth',
  `child-gender` enum('female','male','other','not given') NOT NULL DEFAULT 'not given' COMMENT 'gender',
  `medical` varchar(500) NOT NULL COMMENT 'details on medical conditions - required to ener none if none',
  `dietary` varchar(500) NOT NULL COMMENT 'Description of any dietary requirements',
  `confirm-treatment` enum('YES','NO') NOT NULL DEFAULT 'NO' COMMENT 'confirms permission to provide treatment',
  `confirm-media` enum('YES','NO') NOT NULL DEFAULT 'NO' COMMENT 'confirms permission to include child in images and video',
  `confirm-confident` enum('YES','NO') NOT NULL DEFAULT 'NO' COMMENT 'confirms child is confident in water',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'date/time record created',
  `updby` varchar(20) NOT NULL COMMENT 'user responsible for last update',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'date/time of last update'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='information from junior consent forms';

-- --------------------------------------------------------

--
-- Table structure for table `e_contact`
--

CREATE TABLE `e_contact` (
  `id` int(11) NOT NULL COMMENT 'open event record id',
  `eid` int(11) NOT NULL COMMENT 'event record id in e_event',
  `name` varchar(60) NOT NULL COMMENT 'contact name',
  `job` varchar(60) DEFAULT NULL COMMENT 'contact role for this event',
  `email` varchar(60) DEFAULT NULL COMMENT 'email address',
  `phone` varchar(20) DEFAULT NULL COMMENT 'phone number',
  `image` varchar(100) DEFAULT NULL COMMENT 'link to picture of contact',
  `link` varchar(100) DEFAULT NULL COMMENT 'link to external contact form',
  `contact` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'flag to indicate if person is contactable by event competitors',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'date/time created',
  `updby` varchar(40) NOT NULL COMMENT 'account for last update',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'date/time of last update'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='contact information for an open event';

-- --------------------------------------------------------

--
-- Table structure for table `e_content`
--

CREATE TABLE `e_content` (
  `id` int(11) NOT NULL COMMENT 'open event record id',
  `open_type` varchar(40) NOT NULL COMMENT ' a repeating open meeting type e.g exeregatta - values in t_code_system',
  `eid` int(11) NOT NULL COMMENT 'event record id in e_event',
  `page` varchar(30) NOT NULL COMMENT 'name of page that uses this content',
  `name` varchar(40) NOT NULL COMMENT 'name of content as used in page template',
  `label` varchar(500) DEFAULT NULL COMMENT 'label for content - required if goinf to be used as topic oherwise can be null',
  `content` varchar(4000) NOT NULL COMMENT 'text content',
  `link` varchar(200) DEFAULT NULL COMMENT 'url to an external web page',
  `link-label` varchar(200) DEFAULT NULL COMMENT 'text label for link',
  `image` varchar(200) DEFAULT NULL COMMENT 'url to an image',
  `image-label` varchar(200) DEFAULT NULL COMMENT 'text label for image',
  `image_posn` enum('top','right','left','bottom') NOT NULL DEFAULT 'top' COMMENT 'defines position for image in relation to content',
  `reusable` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If set can be used as a topic',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'date/time created',
  `updby` varchar(40) NOT NULL COMMENT 'account for last update',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'date/time of last update'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='content snippets for an open event website';

-- --------------------------------------------------------

--
-- Table structure for table `e_document`
--

CREATE TABLE `e_document` (
  `id` int(11) NOT NULL COMMENT 'open event record id',
  `eid` int(11) NOT NULL COMMENT 'event record id in e_event',
  `category` enum('results','protest','race','social','other') NOT NULL COMMENT 'type of document',
  `name` varchar(60) NOT NULL COMMENT 'internal name for document',
  `title` varchar(100) NOT NULL COMMENT 'document title',
  `infotxt` varchar(300) DEFAULT NULL COMMENT 'summary description of document',
  `file-loc` enum('external','local','local-relative') NOT NULL DEFAULT 'local-relative' COMMENT 'defines how file location is provided in filename',
  `filename` varchar(250) NOT NULL COMMENT 'name of file - including suffix',
  `format` varchar(20) NOT NULL COMMENT 'file type',
  `version` int(11) DEFAULT '1' COMMENT 'version identifier',
  `status` enum('draft','final','embargoed') NOT NULL DEFAULT 'final' COMMENT 'document status - mainly used for results',
  `publish-start` datetime DEFAULT NULL COMMENT 'date/time document will first appear on event site',
  `publish-end` datetime DEFAULT NULL COMMENT 'date/time at which document will be removed',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'date/time created',
  `updby` varchar(40) NOT NULL COMMENT 'account for last update',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'date/time of last update'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='document information for an open event';

-- --------------------------------------------------------

--
-- Table structure for table `e_entry`
--

CREATE TABLE `e_entry` (
  `id` int(11) NOT NULL COMMENT 'open event record id',
  `eid` int(11) NOT NULL COMMENT 'event record id in e_event',
  `b-class` varchar(40) NOT NULL COMMENT 'boat class name',
  `b-sailno` varchar(10) NOT NULL COMMENT 'boat sail number or identifying characters',
  `b-altno` varchar(10) DEFAULT NULL COMMENT 'alternative id - e.. bow no. or short sail no.',
  `b-name` varchar(40) DEFAULT NULL COMMENT 'boat name or sponsor',
  `b-variant` varchar(40) DEFAULT NULL COMMENT 'boat class variant (e.g. pre-1951) - class specific',
  `b-fleet` varchar(40) DEFAULT NULL COMMENT 'event fleet allocated to this enry',
  `b-division` varchar(40) DEFAULT NULL COMMENT 'event division (e.g. gold, grandmaster) allocated to this boat',
  `b-pn` int(11) DEFAULT '0' COMMENT 'class yardstick no. allocated to this boat',
  `b-personalpn` int(11) DEFAULT '0' COMMENT 'personal yardstick no. allocated to this boat',
  `h-name` varchar(40) NOT NULL COMMENT 'helm name',
  `h-club` varchar(40) DEFAULT NULL COMMENT 'helm club',
  `h-age` varchar(10) DEFAULT NULL COMMENT 'helm age on day of event',
  `h-gender` enum('female','male','other','not given') DEFAULT 'not given' COMMENT 'helm gender',
  `h-email` varchar(40) DEFAULT NULL COMMENT 'helm email',
  `h-phone` varchar(20) DEFAULT NULL COMMENT 'helm phone no.',
  `h-emergency` varchar(60) DEFAULT 'not given' COMMENT 'helm emergency phone no.m',
  `h-country` varchar(40) DEFAULT NULL COMMENT 'helm nationality',
  `c-name` varchar(40) DEFAULT NULL COMMENT 'crew full name',
  `c-club` varchar(40) DEFAULT NULL COMMENT 'crew club',
  `c-age` varchar(10) DEFAULT NULL COMMENT 'crew age on day of event',
  `c-gender` enum('female','male','other','not given') DEFAULT 'not given' COMMENT 'crew gender',
  `c-email` varchar(40) DEFAULT NULL COMMENT 'crew email address',
  `c-phone` varchar(20) DEFAULT NULL COMMENT 'crew phone number',
  `c-emergency` varchar(60) DEFAULT NULL COMMENT 'crew emergency contact phone number',
  `c-country` varchar(40) DEFAULT NULL COMMENT 'crew nationality',
  `e-entryno` int(11) NOT NULL COMMENT 'order entry received for this event',
  `e-waiting` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if set - entry is on waiting list',
  `e-consentsms` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if set - entrant has agreed to accept sms messages',
  `e-consentpay` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if set - entrant has agreed to pay entry fee',
  `e-paid` varchar(10) NOT NULL DEFAULT '0' COMMENT 'if set - entrant has paid entry fee',
  `e-exclude` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if set - entrant has withdrawn from the event or been excluded from the event',
  `e-tally` int(11) DEFAULT NULL COMMENT 'entrant tally number for event',
  `e-notes` varchar(500) DEFAULT NULL COMMENT 'comment on entry that may appear in event related documents (including website pages)',
  `e-privatenotes` varchar(500) DEFAULT NULL COMMENT 'comment on entry that will not appear in event related documents (including website pages)',
  `e-guid` varchar(60) DEFAULT NULL COMMENT 'unique identified for this entry record which can be used to provide edit/delete access to entrant',
  `e-racemanager` int(11) DEFAULT '0' COMMENT 'competitor id in racemanager system',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'date/time created',
  `updby` varchar(40) NOT NULL COMMENT 'account for last update',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'date/time of last update'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='entry information for an open event';

-- --------------------------------------------------------

--
-- Table structure for table `e_event`
--

CREATE TABLE `e_event` (
  `id` int(11) NOT NULL COMMENT 'open event record id',
  `open_type` varchar(40) NOT NULL DEFAULT 'unique' COMMENT 'either a standard club open defined in the open_type code or unique for a one-off',
  `title` varchar(100) NOT NULL COMMENT 'event title (e.g. Merlin Rocket Open Meeting)',
  `nickname` varchar(40) NOT NULL COMMENT 'name used for folder - must be unique in each year',
  `sub-title` varchar(200) DEFAULT NULL COMMENT 'additional title (e.g. south west series)',
  `list-status-txt` varchar(300) NOT NULL COMMENT 'Text to appear as a status message on events list page',
  `date-start` date NOT NULL COMMENT 'first day of event',
  `date-end` date NOT NULL COMMENT 'last day of event',
  `pages-excluded` varchar(60) NOT NULL DEFAULT 'none' COMMENT 'pages to be excluded for this event',
  `topics` varchar(60) DEFAULT NULL COMMENT 'comma separated list of record ids from e_topic of topics that are associated with this event',
  `publish-status` enum('list','cancel','review','detail') NOT NULL DEFAULT 'list' COMMENT 'ddefines what is accessible for viewing: list - only appears on list page; cancel - event cancelled; publish - all requested event pages are accessible; review - only available for review by admin',
  `scoring-type` varchar(30) NOT NULL DEFAULT 'level' COMMENT 'type of racing (as defined by code race_type )',
  `handicap-type` enum('national','local','personal') NOT NULL DEFAULT 'national' COMMENT 'handicap data to be used',
  `entry-type` enum('event system','racemanager system','reception') NOT NULL DEFAULT 'event system' COMMENT 'method for entering the event',
  `entry-form` varchar(100) DEFAULT NULL COMMENT 'name of event system form to be used',
  `ignore-fields` varchar(100) DEFAULT NULL COMMENT 'form field ids to be ignored as a comma separated list',
  `entry-form-link` varchar(100) DEFAULT NULL COMMENT 'url to external entry form',
  `entry-reqd` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'indicates whether pre-entry is required',
  `entry-start` datetime DEFAULT NULL COMMENT 'start date / time for entry system availability',
  `entry-end` datetime DEFAULT NULL COMMENT 'end date / time for entry system availability',
  `entry-classes` varchar(200) DEFAULT NULL COMMENT 'comma separated list of eligible classes',
  `entry-limit` int(11) DEFAULT NULL COMMENT 'maximum no. of boats allowed',
  `entry-swap` enum('auto','manual') NOT NULL DEFAULT 'manual' COMMENT 'determines whether waiting list swaps are made automatically or manually',
  `results-mgr` enum('raceManager','Sailwave') NOT NULL COMMENT 'determines whether results will be created/processed by racemanager or sailwave',
  `races` varchar(100) DEFAULT NULL COMMENT 'comma separated list of raceManager race ids which are associated with this event in schedule order',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'date/time created',
  `updby` varchar(40) NOT NULL COMMENT 'account for last update',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'date/time of last update'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='details for open event';

-- --------------------------------------------------------

--
-- Table structure for table `e_form`
--

CREATE TABLE `e_form` (
  `id` int(11) NOT NULL COMMENT 'id for form record',
  `form-type` varchar(10) NOT NULL COMMENT 'defines form type (e.g. entry)',
  `form-label` varchar(40) NOT NULL COMMENT 'label used to select form',
  `form-file` varchar(60) NOT NULL COMMENT 'file containing form code',
  `instructions` varchar(1000) DEFAULT NULL COMMENT 'form instructions at top of form',
  `target-script` varchar(100) NOT NULL COMMENT 'script and params to be used as target for form submission'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='holds information for forms use within rm_event';

-- --------------------------------------------------------

--
-- Table structure for table `e_notice`
--

CREATE TABLE `e_notice` (
  `id` int(11) NOT NULL COMMENT 'open event record id',
  `eid` int(11) NOT NULL COMMENT 'event record id in e_event',
  `category` enum('competitor','protest','social') NOT NULL COMMENT 'type of notice - from code list',
  `publisher` varchar(60) NOT NULL COMMENT 'person or organisation publishing notice',
  `title` varchar(100) NOT NULL COMMENT 'descriptive title (not html)',
  `leadtxt` varchar(500) DEFAULT NULL COMMENT 'headline text (not html)',
  `txt` varchar(5000) DEFAULT NULL COMMENT 'main body text (not html)',
  `moreinfo` varchar(150) DEFAULT NULL COMMENT 'url to more information',
  `moreinfo-label` varchar(30) DEFAULT NULL COMMENT 'label for more information link',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'date/time created',
  `updby` varchar(40) NOT NULL COMMENT 'account for last update',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'date/time of last update'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='information for notices produced for an open event';

-- --------------------------------------------------------

--
-- Table structure for table `e_results`
--

CREATE TABLE `e_results` (
  `id` int(11) NOT NULL COMMENT 'open event record id',
  `eid` int(11) NOT NULL COMMENT 'event record id in e_event',
  `result_url` varchar(120) DEFAULT NULL COMMENT 'link to published results ',
  `result_label` varchar(200) DEFAULT NULL COMMENT 'label for results file',
  `result_order` int(11) NOT NULL DEFAULT '1' COMMENT 'order to display results files',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'date/time created',
  `updby` varchar(40) NOT NULL COMMENT 'account for last update',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'date/time of last update'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='information on individual results for an open event';

-- --------------------------------------------------------

--
-- Table structure for table `rm_admin_audit`
--

CREATE TABLE `rm_admin_audit` (
  `id` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `ip` varchar(40) NOT NULL,
  `user` varchar(300) DEFAULT NULL,
  `table` varchar(300) DEFAULT NULL,
  `action` varchar(250) NOT NULL,
  `description` mediumtext
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='audit information for each change made using rm_admin';

-- --------------------------------------------------------

--
-- Table structure for table `rm_admin_settings`
--

CREATE TABLE `rm_admin_settings` (
  `ID` int(11) NOT NULL,
  `TYPE` int(11) DEFAULT '1',
  `NAME` mediumtext,
  `USERNAME` mediumtext,
  `COOKIE` varchar(500) DEFAULT NULL,
  `SEARCH` mediumtext,
  `TABLENAME` varchar(300) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4  COMMENT='saved user setting used by rm_admin (e.g. saved searches)';

-- --------------------------------------------------------

--
-- Table structure for table `rm_admin_uggroups`
--

CREATE TABLE `rm_admin_uggroups` (
  `GroupID` int(11) NOT NULL,
  `Label` varchar(300) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='user groups used by rm_admin';

-- --------------------------------------------------------

--
-- Table structure for table `rm_admin_ugmembers`
--

CREATE TABLE `rm_admin_ugmembers` (
  `UserName` varchar(300) NOT NULL,
  `GroupID` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='user group members for rm_admin';

-- --------------------------------------------------------

--
-- Table structure for table `rm_admin_ugrights`
--

CREATE TABLE `rm_admin_ugrights` (
  `TableName` varchar(300) NOT NULL,
  `GroupID` int(11) NOT NULL,
  `AccessMask` varchar(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='access rights for rm_admin user groups for each table';

-- --------------------------------------------------------

--
-- Table structure for table `rm_admin_users`
--

CREATE TABLE `rm_admin_users` (
  `ID` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `groupid` varchar(255) DEFAULT NULL,
  `active` int(11) DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_date` date DEFAULT NULL,
  `reset_token1` varchar(50) DEFAULT NULL,
  `reset_date1` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='rm_admin user accounts';

-- --------------------------------------------------------

--
-- Table structure for table `t_cfgfleet`
--

CREATE TABLE `t_cfgfleet` (
  `id` int(11) NOT NULL,
  `eventcfgid` int(11) NOT NULL,
  `start_num` int(2) NOT NULL COMMENT 'start number',
  `fleet_num` tinyint(4) NOT NULL COMMENT 'race fleet  number (in sequence)',
  `fleet_code` varchar(10) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'short code for fleet (e.g. SH)',
  `fleet_name` varchar(60) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'full name for fleet (e.g. slow handicap)',
  `fleet_desc` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'description of fleet',
  `scoring` enum('handicap','average','level','pursuit') COLLATE utf8mb4_general_ci NOT NULL COMMENT 'type of race scoring (should this be integer code)',
  `py_type` enum('national','local','personal') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'local' COMMENT 'type of PN number to be used for this race',
  `warn_signal` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'filename for signal flag for warning signal',
  `prep_signal` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'filename for signal flag used for preparatory signal',
  `timelimit_abs` int(4) DEFAULT NULL COMMENT 'absolute time limit for first finisher (minutes)',
  `timelimit_rel` int(4) DEFAULT NULL COMMENT 'time limit for subsequent finishers after leader (minutes)',
  `defaultlaps` int(2) NOT NULL DEFAULT '1' COMMENT 'default number of laps for this race',
  `defaultfleet` int(1) NOT NULL DEFAULT '0' COMMENT 'test for allocation to this race after checking all other races',
  `classinc` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'list of classes to be allocated to this race',
  `onlyinc` int(1) NOT NULL DEFAULT '0' COMMENT 'if set - only the listed included classes are allowed',
  `classexc` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'list of classes to be excluded from this race',
  `groupinc` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'groups to be eligible for this race',
  `min_py` int(4) DEFAULT '0' COMMENT 'minimum PY for this race',
  `max_py` int(4) DEFAULT '2000' COMMENT 'maximum PY for this race',
  `crew` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'crew constraint for classes in this race',
  `spintype` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'spinnaker type constraints for classes in this race',
  `hulltype` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'hull type constraint for classes in this race',
  `min_helmage` char(3) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'minimum helm of age for inclusion in this race',
  `max_helmage` char(3) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'maximum age of helm for this race',
  `min_skill` char(2) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'minimum skill level for this race',
  `max_skill` char(2) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'maximum skill level for this race',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'date of last update',
  `updby` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='defines racing fleets for each race format';

-- --------------------------------------------------------

--
-- Table structure for table `t_cfgrace`
--

CREATE TABLE `t_cfgrace` (
  `id` int(6) NOT NULL,
  `race_code` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `race_name` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `race_desc` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pursuit` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'true if a pursuit race format',
  `numstarts` tinyint(3) NOT NULL,
  `start_scheme` varchar(20) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'start scheme for preparatory, warning and start signals (e.g 5,4,1,0 or 10,5,0)',
  `start_interval` int(4) NOT NULL COMMENT 'time interval (secs) between each start ',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'indicates if event type is still in use',
  `comp_pick` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if set user can choose their start',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'date of last update',
  `updby` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='defines event configuration in terms of individual races for racing events (e.g club series).';

-- --------------------------------------------------------

--
-- Table structure for table `t_cfgseries`
--

CREATE TABLE `t_cfgseries` (
  `id` int(11) NOT NULL COMMENT 'table primary key',
  `seriestype` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'series type code (e.g. short, long etc)',
  `discard` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'discard profile usig Sailwave coding (e.g. 0,0,1,1,2 means in the five race series one discard is applied after three races and 2 discards if all five races are complete)',
  `nodiscard` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'if set last race sailed cannot be discarded',
  `multiplier` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'if set the score for the last race in series is multiplied by the value ',
  `avgscheme` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'scheme for calculating average points',
  `dutypoints` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'duty points',
  `maxduty` int(2) NOT NULL DEFAULT '10' COMMENT 'max number of duties allowed to score',
  `notes` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Notes on series - e.g. a description',
  `active` int(1) NOT NULL DEFAULT '1' COMMENT 'flag set if this event type is currently used',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='Defines each race series - includes parent series option (series of series)';

-- --------------------------------------------------------

--
-- Table structure for table `t_class`
--

CREATE TABLE `t_class` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'identifier',
  `acronym` char(6) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'xxx' COMMENT '3 letter acronym for class (e.g. FB -> fireball)',
  `classname` varchar(40) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'class name',
  `variant` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'name for class recognised variant',
  `info` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'description',
  `popular` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'used to denote popular classes at the club - will be disoplayed before the other classes',
  `nat_py` int(5) UNSIGNED NOT NULL COMMENT 'national PN',
  `local_py` int(5) UNSIGNED NOT NULL COMMENT 'local PN',
  `rya_id` char(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'rya unique identifier',
  `upd_year` year(4) DEFAULT NULL COMMENT 'year of last update',
  `category` enum('D','M','K','C','F','P','W') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'D' COMMENT 'boat category [D=dinghy, M=multihull, K=keelboat, F=foiler, P = paddled boat]',
  `crew` enum('1','2','N') COLLATE utf8mb4_general_ci NOT NULL DEFAULT '1' COMMENT 'number of crew',
  `rig` enum('U','S','K','N') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'U' COMMENT 'rig type [U=una, S=sloop, K=two masts]',
  `spinnaker` enum('O','C','A') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'O' COMMENT 'spinnaker type [C=conventional, A=asymmetric, O=none]',
  `engine` enum('OB','IBF','IB2','IB3') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'OB' COMMENT 'engine [OB=outboard, IB2=2 bladed fixed propellor, IB3=3 bladed fixed propellor, IBF=folding propellor]',
  `keel` enum('D','F','2K','3K','N') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'D' COMMENT 'keel type [D=drop keel, F=single fixed, 2K=twin keel, 3K=triple keel]',
  `fleets` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'comma separated list of fleet categories (e.g. gold, silver, etc.)	',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'record active flag',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='class configuration details - includes RYA coding and yardstick information';

-- --------------------------------------------------------

--
-- Table structure for table `t_code_result`
--

CREATE TABLE `t_code_result` (
  `id` int(6) NOT NULL COMMENT 'code identifier',
  `code` char(6) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'result code (e.g. DNF)',
  `short` varchar(60) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'short description',
  `info` varchar(500) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'description',
  `scoringtype` enum('penalty','race','series','manual') COLLATE utf8mb4_general_ci NOT NULL COMMENT 'code to determine when scoring can be applied',
  `scoring` varchar(20) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'scoring equation (using N= num in race, S=num in series, )',
  `timing` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'true if timing should continue after code is set',
  `startcode` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Use on start page',
  `timercode` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 if used when timing',
  `resultcode` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'used on results page',
  `nonexclude` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'true if result cannot be excluded in series',
  `rank` int(2) NOT NULL DEFAULT '1' COMMENT 'order for display',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 if to be used',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updby` varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='list of codes used in result processing (e.g. DNF) and information on how they should be scored';

-- --------------------------------------------------------

--
-- Table structure for table `t_code_system`
--

CREATE TABLE `t_code_system` (
  `id` int(11) NOT NULL COMMENT 'table primary key',
  `groupname` varchar(40) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'code group',
  `code` varchar(40) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'code value',
  `label` varchar(200) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'code label (for use in interface)',
  `rank` int(3) NOT NULL DEFAULT '0' COMMENT 'order in list',
  `defaultval` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'default value (if 1)',
  `deletable` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'if set code can be deleted',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'date of last update',
  `updby` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'updated by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='codes used in the racemanager applications';

-- --------------------------------------------------------

--
-- Table structure for table `t_code_type`
--

CREATE TABLE `t_code_type` (
  `id` int(11) NOT NULL COMMENT 'primary key for table',
  `groupname` varchar(40) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'name for code which is used to identify the codes belonging to this type',
  `label` varchar(40) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'label for the code type',
  `info` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'description for the code type',
  `rank` int(3) NOT NULL DEFAULT '1' COMMENT 'display order',
  `type` enum('system','club') COLLATE utf8mb4_general_ci NOT NULL,
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT 'types of codes used in racemanager applications';

-- --------------------------------------------------------

--
-- Table structure for table `t_competitor`
--

CREATE TABLE `t_competitor` (
  `id` int(10) NOT NULL COMMENT 'identifier',
  `classid` int(10) NOT NULL COMMENT 'class id in rtblclass',
  `boatnum` varchar(20) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'boat number',
  `sailnum` varchar(20) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'sail number used',
  `boatname` varchar(60) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'name (or sponsor) of boat',
  `hullcolour` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'colour of hull',
  `helm` varchar(40) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'helm name',
  `helm_dob` date DEFAULT NULL COMMENT 'helm date of birth',
  `helm_email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'helm email address',
  `telephone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'contact telephone',
  `crew` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'crew name',
  `crew_dob` date DEFAULT NULL COMMENT 'crew date of birth',
  `crew_email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'crew email address',
  `club` varchar(60) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'sailing club name',
  `personal_py` int(5) DEFAULT NULL COMMENT 'personal PN',
  `skill_level` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'skill coding',
  `flight` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'allocated flight for specific events',
  `regular` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if true this is a regular sailor who can be entered in bulk',
  `last_entry` date DEFAULT NULL COMMENT 'date of last entry',
  `last_event` int(11) DEFAULT NULL COMMENT 'last event (id) entered',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'set to 1 if active competitor',
  `prizelist` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'comma seperated prize eligibility',
  `grouplist` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'comma separated list of member groups (e.g. member, visitor, junior)',
  `memberid` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'member id/code',
  `trackerid` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'identifier for position tracker',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='competitor details';

-- --------------------------------------------------------

--
-- Table structure for table `t_course`
--

CREATE TABLE `t_course` (
  `id` int(10) NOT NULL COMMENT 'course id',
  `category` varchar(30) NOT NULL DEFAULT 'category' COMMENT 'top level grouping for courses - typically wind direction',
  `sort` int(3) NOT NULL DEFAULT '1' COMMENT 'index to sort courses within a category',
  `name` varchar(100) NOT NULL DEFAULT 'name' COMMENT 'name of course',
  `blurb` varchar(300) DEFAULT NULL COMMENT 'short description of course',
  `info` varchar(5000) DEFAULT NULL COMMENT 'information about the course presented to OODs as a set of bullet points (points separated by |)',
  `buoy_url` varchar(200) DEFAULT NULL COMMENT 'URL to image displaying course definition',
  `info_url` varchar(200) DEFAULT NULL COMMENT 'URL to OOD information text (htm)',
  `other_url` varchar(200) DEFAULT NULL COMMENT 'URL to image of course track',
  `updby` varchar(20) NOT NULL COMMENT 'user account making most recent change',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'date of last update',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'date record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='course definitions';

-- --------------------------------------------------------

--
-- Table structure for table `t_coursedetail`
--

CREATE TABLE `t_coursedetail` (
  `id` int(10) NOT NULL COMMENT 'course id',
  `courseid` int(10) NOT NULL COMMENT 'id for course group',
  `sort` int(3) NOT NULL COMMENT 'sort index for the fleet courses',
  `name` varchar(40) NOT NULL COMMENT 'name for this course specification',
  `fleets` varchar(100) DEFAULT NULL COMMENT 'fleets associated with this course (| delimited)',
  `start` varchar(100) DEFAULT NULL COMMENT 'start option for this course',
  `buoys` varchar(200) NOT NULL COMMENT 'marker buoy details for this course',
  `laps` varchar(100) DEFAULT NULL COMMENT 'laps for the fleets using this course',
  `updby` varchar(20) NOT NULL COMMENT 'user account making most recent change',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'date of last update',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'date record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='course definition for each fleet';

-- --------------------------------------------------------

--
-- Table structure for table `t_cruise`
--

CREATE TABLE `t_cruise` (
  `id` int(11) NOT NULL COMMENT 'table primary key',
  `cruise_type` varchar(20) CHARACTER SET utf8mb4 NOT NULL,
  `cruise_date` date NOT NULL COMMENT 'date of cruise',
  `time_in` time NOT NULL COMMENT 'time registered for cruise',
  `time_out` time DEFAULT NULL COMMENT 'time declared ashore',
  `boatid` int(11) NOT NULL COMMENT 'competitor record id from t_competitor',
  `action` enum('register','update','declare') COLLATE utf8mb4_general_ci NOT NULL COMMENT 'action for racemanager to take on entry',
  `chg-helm` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'has value if helm name is changed',
  `chg-crew` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'temporary crew change for races today',
  `chg-hullcolour` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `chg-sailnum` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'temporary sailno change for today',
  `chg-numcrew` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'total number of people on boad',
  `chg-contact` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'contact details - typically mobile number',
  `vhfchannel` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telno` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `clubvhf` tinyint(4) DEFAULT '0',
  `destination` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `winch-used` tinyint(4) NOT NULL DEFAULT '0',
  `memberid` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'memberid from t_competitor',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT 'information on logged leisure sailing';

-- --------------------------------------------------------

--
-- Table structure for table `t_entry`
--

CREATE TABLE `t_entry` (
  `id` int(11) NOT NULL COMMENT 'table primary key',
  `action` enum('enter','delete','update','retire','replace','declare') COLLATE utf8mb4_general_ci NOT NULL COMMENT 'action for racemanager to take on entry',
  `protest` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 if declaring protest',
  `status` enum('N','L','F','E','A') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'N' COMMENT 'status (N=not loaded, L=loaded, F=failed. E=not eligible, A=not allocated)',
  `eventid` int(11) NOT NULL COMMENT 'event record id from t_event',
  `competitorid` int(11) NOT NULL COMMENT 'competitor record id from t_competitor',
  `memberid` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'memberid from t_competitor',
  `chg-helm` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'has value if helm name is changed',
  `chg-crew` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'temporary crew change for races today',
  `chg-sailnum` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'temporary sailno change for today',
  `entryid` int(11) DEFAULT '0' COMMENT 'id of entry into race table (t_race)',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT 'information on race entries';

-- --------------------------------------------------------

--
-- Table structure for table `z_entry_draft`
--

CREATE TABLE `z_entry_draft` (
  `id` int(11) NOT NULL COMMENT 'table primary key',
  `action` enum('enter','delete','update','retire','replace','declare') COLLATE utf8mb4_general_ci NOT NULL COMMENT 'action for racemanager to take on entry',
  `protest` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 if declaring protest',
  `status` enum('N','L','F','E','A') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'N' COMMENT 'status (N=not loaded, L=loaded, F=failed. E=not eligible, A=not allocated)',
  `eventid` int(11) NOT NULL COMMENT 'event record id from t_event',
  `competitorid` int(11) NOT NULL COMMENT 'competitor record id from t_competitor',
  `memberid` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'memberid from t_competitor',
  `chg-helm` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'has value if helm name is changed',
  `chg-crew` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'temporary crew change for races today',
  `chg-sailnum` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'temporary sailno change for today',
  `entryid` int(11) DEFAULT '0' COMMENT 'id of entry into race table (t_race)',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT 'temporary table to receive event entries from rm_event (e_entry)';

-- --------------------------------------------------------

--
-- Table structure for table `t_event`
--

CREATE TABLE `t_event` (
  `id` int(11) NOT NULL COMMENT 'table primary key - note this is the same as the event id in clubManager programme function',
  `event_date` date NOT NULL COMMENT 'event date',
  `event_start` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'time of start (as text HH:MM)',
  `event_order` int(2) DEFAULT '1' COMMENT 'order of event on day - used if start times not defined',
  `event_name` varchar(60) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'event name (e.g. Summer Series, Kathleen Trophy, etc.)',
  `series_code` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'foreign key to series information in t_series',
  `series_code_extra` varchar(100) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT 'additional series code links if event is part of more than one series - comma separated list',
  `event_type` varchar(20) CHARACTER SET utf8mb4 NOT NULL COMMENT 'type of event (e.g. cruise, race, etc.)',
  `event_format` int(11) DEFAULT NULL COMMENT 'race format for event - references event configuration record in t_eventcfg',
  `event_entry` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'entry type (e.g ood or electronic signon etc.) - codes in t_code',
  `event_status` varchar(40) COLLATE utf8mb4_general_ci DEFAULT 'scheduled' COMMENT 'current status of event - codes in t_code',
  `event_open` enum('club','open') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'club' COMMENT 'indicates if it is an open event',
  `event_ood` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'name of ood (alternate overrides name in t_eventduty)',
  `tide_time` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'time of high water (as text HH:MM)',
  `tide_height` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'height of tide in metres',
  `start_scheme` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'field to change the default start scheme specified in event configuration',
  `start_interval` int(4) DEFAULT NULL COMMENT 'field to overwrite the default start interval defined in event configuration',
  `timerstart` bigint(13) DEFAULT NULL COMMENT 'timerstart (secs from 1/1/1970)',
  `ws_start` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'wind speed at start of race (from coded range)',
  `wd_start` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'wind direction at start (from coded range)',
  `ws_end` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'wind speed at end of race (from coded range)',
  `wd_end` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'wind direction at end (from coded range)',
  `event_notes` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'notes field will appear in programme display',
  `result_notes` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'notes relating to the results',
  `result_valid` int(1) DEFAULT '0' COMMENT 'true if results calculation found no errors',
  `result_publish` int(1) DEFAULT '0' COMMENT 'true if results have been published',
  `weblink` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'url to web page with more info',
  `webname` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'text to use on url link',
  `display_code` char(10) COLLATE utf8mb4_general_ci DEFAULT 'W,R,S' COMMENT 'code to define whether displayed on website, racemanager, and/or sailor - comma separated list W,R,S',
  `active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'true if part of published programme',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` char(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='programmed events for racemanager';

-- --------------------------------------------------------

--
-- Table structure for table `t_eventduty`
--

CREATE TABLE `t_eventduty` (
  `id` int(11) NOT NULL,
  `eventid` int(11) NOT NULL,
  `dutycode` varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  `person` varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  `swapable` int(1) NOT NULL DEFAULT '1' COMMENT 'if set to 1 duty can be swapped',
  `phone` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `notes` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `memberid` int(11) DEFAULT NULL,
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT 'information allocated duties for each programmed event';

-- --------------------------------------------------------

--
-- Table structure for table `t_help`
--

CREATE TABLE `t_help` (
  `id` int(11) NOT NULL COMMENT 'table primary key',
  `category` varchar(60) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'help category - comma separated',
  `question` varchar(500) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'question or help item heading',
  `answer` varchar(4000) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'answer text',
  `notes` varchar(4000) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'additional related information',
  `author` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'person creating help item',
  `pursuit` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if set should only be displayed if it is a pursuit race',
  `eventname` varchar(60) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'event name constraint - can be name substring',
  `format` int(11) DEFAULT NULL COMMENT 'event format constraint',
  `startdate` date DEFAULT NULL COMMENT 'start date constraint',
  `enddate` date DEFAULT NULL COMMENT 'end date constraint',
  `multirace` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if set should only be displayed if multi race day',
  `rank` int(3) NOT NULL DEFAULT '1' COMMENT 'order in which they should be displayed within category',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'if set to 1 can be viewed by user',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'date of last update',
  `updby` varchar(20) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'user responsible for last update',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Holds help information which is configurable for each installation';

-- --------------------------------------------------------

--
-- Table structure for table `t_ini`
--

CREATE TABLE `t_ini` (
  `id` int(11) NOT NULL COMMENT 'table primary key',
  `category` varchar(40) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'configuration parameter category (club, interface, entry, results, admin]',
  `parameter` varchar(40) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'name of parameter',
  `label` varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  `value` varchar(200) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'value of parameter',
  `notes` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Information on what values can be used for this setting',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'date of last update',
  `updby` char(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='club specific configurable parameters used by racemanager';

-- --------------------------------------------------------

--
-- Table structure for table `t_lap`
--

CREATE TABLE `t_lap` (
  `id` int(11) NOT NULL COMMENT 'identifier',
  `eventid` int(11) NOT NULL COMMENT 'event record id from t_event',
  `race` int(2) NOT NULL,
  `entryid` int(11) NOT NULL COMMENT 'entry id from rtblrace',
  `lap` int(3) NOT NULL COMMENT 'lap number',
  `clicktime` int(20) NOT NULL DEFAULT '0',
  `etime` int(6) NOT NULL DEFAULT '0' COMMENT 'elapsed time at end of lap (secs)',
  `ctime` int(6) DEFAULT '0' COMMENT 'corrected time at end of lap (secs)',
  `status` tinyint(1) DEFAULT '1' COMMENT 'field used to indicate whether this lap to be included in results (1 = include)',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='information on each lap recorded';

-- --------------------------------------------------------

--
-- Table structure for table `t_link`
--

CREATE TABLE `t_link` (
  `id` int(11) NOT NULL COMMENT 'table primary key',
  `label` varchar(60) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'label for link label to appear in interface',
  `url_link` varchar(200) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'URL for link (e.g. http://....)',
  `tip` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'tooltip text for link',
  `category` varchar(20) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'link category (allows grouping of links for different pages in system) - only one category permitted so it may be necessary to duplicate links if they are to appear in more than one place.',
  `listorder` tinyint(2) NOT NULL DEFAULT '0' COMMENT 'order in which to display links (within category)',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Club specific links to additional information';

-- --------------------------------------------------------

--
-- Table structure for table `t_message`
--

CREATE TABLE `t_message` (
  `id` int(11) NOT NULL,
  `eventid` int(11) DEFAULT NULL COMMENT 'id for event associated with message',
  `name` varchar(60) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'person / process creating message',
  `subject` varchar(60) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'message subject',
  `message` text COLLATE utf8mb4_general_ci NOT NULL COMMENT 'message content',
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'email address for response',
  `response` text COLLATE utf8mb4_general_ci COMMENT 'action taken',
  `status` enum('received','closed') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'received' COMMENT 'current status of message',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updby` varchar(20) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'user responsible for update',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='messages created by user (or actions of user) in racemanager applications for review by system admin';

-- --------------------------------------------------------

--
-- Table structure for table `t_protest`
--

CREATE TABLE `t_protest` (
  `id` int(11) NOT NULL,
  `protest_type` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `event_name` varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  `event_date` date NOT NULL,
  `protestor_name` varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  `protestor_class` varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  `protestor_sailnum` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `protestee_name` varchar(60) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `protestee_class` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `protestee_sailnum` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `infotxt` varchar(4000) COLLATE utf8mb4_general_ci NOT NULL,
  `facts_found` varchar(4000) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `decision` varchar(2000) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `submitted` datetime NOT NULL,
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updby` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='information on submitted protests';

-- --------------------------------------------------------

--
-- Table structure for table `t_race`
--

CREATE TABLE `t_race` (
  `id` int(11) NOT NULL COMMENT 'table primary key',
  `eventid` int(11) NOT NULL DEFAULT '0' COMMENT 'event record id from t_event',
  `start` int(2) NOT NULL COMMENT 'start number',
  `fleet` int(2) NOT NULL COMMENT 'fleet  number',
  `competitorid` int(11) NOT NULL COMMENT 'competitor record id from t_competitor',
  `trackerid` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'identifier for position tracker',
  `helm` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'helm name',
  `crew` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'crew name',
  `club` varchar(60) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'club name',
  `class` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'boat class name',
  `classcode` varchar(6) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'class acronym',
  `sailnum` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'sail number used in race',
  `pn` int(5) NOT NULL DEFAULT '0' COMMENT 'portsmouth number used in this race',
  `clicktime` int(20) DEFAULT NULL COMMENT 'date/time of last timing click',
  `lap` int(3) NOT NULL DEFAULT '0' COMMENT 'completed laps',
  `finishlap` int(3) NOT NULL DEFAULT '1' COMMENT 'lap boat will finish on (this is only required for average lap racing - can I find a better way)',
  `etime` int(6) NOT NULL DEFAULT '0' COMMENT 'last elapsed time recorded (secs)',
  `ctime` int(6) NOT NULL DEFAULT '0' COMMENT 'last corrected time (secs)',
  `atime` int(6) NOT NULL DEFAULT '0' COMMENT 'aggregate time (pro-rata time for all boats doing the same number of laps in average lap race)',
  `ptime` int(6) NOT NULL DEFAULT '0' COMMENT 'predicted elapsed time for next lap (secs)',
  `f_line` int(2) DEFAULT NULL COMMENT 'pursuit finish line number (1 is first finish line)  only used for pursuit races',
  `f_pos` int(3) DEFAULT NULL COMMENT 'pursuit finish position (1 is first boat at that finish line) - only used for pursuit races',
  `code` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'result code for competitor (e.g. DNF)',
  `penalty` decimal(4,1) NOT NULL DEFAULT '0.0',
  `points` decimal(4,1) NOT NULL DEFAULT '0.0' COMMENT 'last points calculated',
  `pos` decimal(4,1) DEFAULT NULL COMMENT 'position allocated',
  `declaration` enum('D','R','X') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'X' COMMENT 'declaration status [X - not declared, D declared, R retired]',
  `protest` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'protest flag',
  `note` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'notes field',
  `status` enum('R','F','X','D') COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'status field (R = racing, F = finished, X = excluded, D = deleted',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='current state for each competitor in races today';

-- --------------------------------------------------------

--
-- Table structure for table `t_racestate`
--

CREATE TABLE `t_racestate` (
  `id` int(11) NOT NULL COMMENT 'table primary key',
  `eventid` int(11) NOT NULL,
  `racename` varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  `fleet` smallint(2) NOT NULL COMMENT 'no. of fleet in event',
  `start` int(2) NOT NULL COMMENT 'start number',
  `racetype` enum('handicap','average','level','pursuit','flight') COLLATE utf8mb4_general_ci NOT NULL COMMENT 'type of race (pursuit, fleet, handicap, etc.) - should this be integer code',
  `startdelay` int(6) NOT NULL COMMENT 'delay of start from timer initialisation (secs)',
  `starttime` time NOT NULL DEFAULT '00:00:00' COMMENT 'start time of this race',
  `maxlap` int(3) NOT NULL DEFAULT '0' COMMENT 'maximum no. of laps for this race (equivalent to finish lap for all except average lap racing)',
  `currentlap` int(2) NOT NULL DEFAULT '0' COMMENT 'Current lap for leading boat on water',
  `entries` int(3) NOT NULL DEFAULT '0' COMMENT 'No. of entries',
  `status` enum('notstarted','inprogress','finishing','allfinished') COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'current race status',
  `prevstatus` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'previous status',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='summary race status information for each race/fleet';

-- --------------------------------------------------------

--
-- Table structure for table `t_result`
--

CREATE TABLE `t_result` (
  `id` int(11) NOT NULL COMMENT 'primary key for this table',
  `eventid` int(11) NOT NULL COMMENT 'event record id (from t_event)',
  `fleet` int(2) NOT NULL COMMENT 'fleet number in event',
  `race_type` enum('handicap','average','level','pursuit','none') COLLATE utf8mb4_general_ci NOT NULL COMMENT 'race type as defined in t_racecfg (stored here to indicate scoring mechanism used when race was run)',
  `competitorid` int(11) NOT NULL COMMENT 'competitor record id (from t_competitor)',
  `class` varchar(40) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'class of boat (text)',
  `sailnum` varchar(20) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'sail number used',
  `pn` int(4) NOT NULL COMMENT 'PN used for this race',
  `apn` int(4) DEFAULT NULL COMMENT 'achieved PN (personal handicap)',
  `helm` varchar(40) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'helm name for this race',
  `crew` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'crew name for this race',
  `club` varchar(60) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'club name',
  `lap` int(2) NOT NULL,
  `etime` int(6) NOT NULL COMMENT 'elapsed time at finish (secs)',
  `ctime` int(6) NOT NULL DEFAULT '0' COMMENT 'corrected time at finish',
  `atime` int(6) NOT NULL DEFAULT '0' COMMENT 'aggregate time (pro-rata time for all boats doing the same number of laps in average lap race)',
  `code` char(6) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'finish code (e.g. DNC, DNF)',
  `penalty` decimal(4,1) NOT NULL DEFAULT '0.0',
  `points` decimal(4,1) NOT NULL COMMENT 'points awarded for race',
  `declaration` char(3) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'declared - same codes as in t_race',
  `note` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'notes',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'last updated by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='result information for each boat in each event';

-- --------------------------------------------------------

--
-- Table structure for table `t_resultfile`
--

CREATE TABLE `t_resultfile` (
  `id` int(11) NOT NULL,
  `eventid` int(11) NOT NULL COMMENT 'id for event which results file is related to',
  `eventyear` year(4) NOT NULL COMMENT 'year in which event results files will be allocated',
  `folder` enum('races','series','class','special') COLLATE utf8mb4_general_ci NOT NULL COMMENT 'type of result file (reflects structure of results directory)',
  `format` enum('htm','csv','pdf','jpg') COLLATE utf8mb4_general_ci NOT NULL COMMENT 'data format of file',
  `filename` varchar(80) CHARACTER SET utf8mb4 NOT NULL COMMENT 'name of file',
  `label` varchar(60) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'label for presenting the results file to users',
  `notes` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'supporting notes describing the file',
  `status` enum('final','provisional','embargoed') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'final' COMMENT 'status for result file',
  `rank` int(2) NOT NULL DEFAULT '1' COMMENT 'display order',
  `upload` datetime DEFAULT NULL COMMENT 'datetime when file was uploaded to website',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='information on results files created by racemanager or imported';

-- --------------------------------------------------------

--
-- Table structure for table `t_rotamember`
--

CREATE TABLE `t_rotamember` (
  `id` int(11) NOT NULL,
  `memberid` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'id for this member in membership system',
  `firstname` varchar(20) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'first name',
  `familyname` varchar(40) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'famil nmae',
  `rota` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'rota name',
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'phone number (mobile preferred)',
  `email` varchar(60) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'email',
  `note` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'rota notes (e.g. not on wednesdays)',
  `partner` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'name of duty partner',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'set to 1 if still active',
  `updby` varchar(20) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'date of last update'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Information on who is on each duty rota';

-- --------------------------------------------------------

--
-- Table structure for table `t_series`
--

CREATE TABLE `t_series` (
  `id` int(11) NOT NULL COMMENT 'table primary key',
  `seriescode` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'e.g.  AUTUMN',
  `seriesname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'name of series (e.g. Autumn Series)',
  `seriestype` int(11) NOT NULL COMMENT 'series type from t_cfgseries',
  `startdate` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'earliest date on which series race can be sailed (e.g 1-jan)',
  `enddate` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'latest date on which series race can be sailed (e.g 30-sep)',
  `race_format` int(11) DEFAULT NULL COMMENT 'default race format (id in t_cfgrace)',
  `merge` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'lists of classes to be merged in series result -  each class separated by ,,|,,',
  `classresults` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'holds comma separated list of classes for which class specific results are required',
  `opt_style` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'classic' COMMENT 'report style to be used',
  `opt_turnout` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if true include turnout details',
  `opt_scorecode` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'if true include definition of scoring codes',
  `opt_clubnames` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if true include club name for each competitor',
  `opt_pagebreak` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if true insert a page break after each fleet',
  `opt_racelabel` enum('number','dates') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'dates' COMMENT 'defines what is used to label each race in the series (number or dates)',
  `opt_upload` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'if true upload series result to website',
  `notes` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Notes on series - e.g. a description',
  `active` int(1) NOT NULL DEFAULT '1' COMMENT 'if true - can be used for events',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='Defines each race series - includes parent series option (series of series)';

-- --------------------------------------------------------

--
-- Table structure for table `t_tide`
--

CREATE TABLE `t_tide` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL COMMENT 'date for tide data',
  `hw1_time` char(5) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT 'first high water time (hh:mm)',
  `hw1_height` char(4) CHARACTER SET utf8mb4 NOT NULL COMMENT 'first high water height (e.g 3.9)',
  `hw2_time` char(5) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT 'second high water time (hh:mm)',
  `hw2_height` char(4) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT 'second high water height',
  `time_reference` char(6) CHARACTER SET utf8mb4 NOT NULL DEFAULT 'local',
  `height_units` char(3) CHARACTER SET utf8mb4 NOT NULL DEFAULT 'm',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='holds tide data';

-- --------------------------------------------------------

--
-- Table structure for table `z_entry`
--

CREATE TABLE `z_entry` (
  `id` int(11) NOT NULL COMMENT 'table primary key',
  `action` enum('enter','delete','update','retire','replace','declare') COLLATE utf8mb4_general_ci NOT NULL COMMENT 'action for racemanager to take on entry',
  `protest` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 if declaring protest',
  `status` enum('N','L','F','E','A') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'N' COMMENT 'status (N=not loaded, L=loaded, F=failed. E=not eligible, A=not allocated)',
  `eventid` int(11) NOT NULL COMMENT 'event record id from t_event',
  `competitorid` int(11) NOT NULL COMMENT 'competitor record id from t_competitor',
  `memberid` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'memberid from t_competitor',
  `chg-helm` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'has value if helm name is changed',
  `chg-crew` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'temporary crew change for races today',
  `chg-sailnum` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'temporary sailno change for today',
  `entryid` int(11) DEFAULT '0' COMMENT 'id of entry into race table (t_race)',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='A temporary copy of t_entry used to hold entries for the demo events';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `e_consent`
--
ALTER TABLE `e_consent`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `e_contact`
--
ALTER TABLE `e_contact`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `e_content`
--
ALTER TABLE `e_content`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `e_document`
--
ALTER TABLE `e_document`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `e_entry`
--
ALTER TABLE `e_entry`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `e_event`
--
ALTER TABLE `e_event`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- Indexes for table `e_form`
--
ALTER TABLE `e_form`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `e_notice`
--
ALTER TABLE `e_notice`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `e_results`
--
ALTER TABLE `e_results`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rm_admin_audit`
--
ALTER TABLE `rm_admin_audit`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rm_admin_settings`
--
ALTER TABLE `rm_admin_settings`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `rm_admin_uggroups`
--
ALTER TABLE `rm_admin_uggroups`
  ADD PRIMARY KEY (`GroupID`);

--
-- Indexes for table `rm_admin_ugmembers`
--
ALTER TABLE `rm_admin_ugmembers`
  ADD PRIMARY KEY (`UserName`(50),`GroupID`);

--
-- Indexes for table `rm_admin_ugrights`
--
ALTER TABLE `rm_admin_ugrights`
  ADD PRIMARY KEY (`TableName`(50),`GroupID`);

--
-- Indexes for table `rm_admin_users`
--
ALTER TABLE `rm_admin_users`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `t_cfgfleet`
--
ALTER TABLE `t_cfgfleet`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_cfgrace`
--
ALTER TABLE `t_cfgrace`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_cfgseries`
--
ALTER TABLE `t_cfgseries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_class`
--
ALTER TABLE `t_class`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `classname` (`classname`);

--
-- Indexes for table `t_code_result`
--
ALTER TABLE `t_code_result`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_code_system`
--
ALTER TABLE `t_code_system`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_code_type`
--
ALTER TABLE `t_code_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_competitor`
--
ALTER TABLE `t_competitor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lastRaced` (`last_entry`),
  ADD KEY `visibility` (`active`);

--
-- Indexes for table `t_course`
--
ALTER TABLE `t_course`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_coursedetail`
--
ALTER TABLE `t_coursedetail`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_cruise`
--
ALTER TABLE `t_cruise`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_entry`
--
ALTER TABLE `t_entry`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `z_entry_draft`
--
ALTER TABLE `z_entry_draft`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_event`
--
ALTER TABLE `t_event`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_eventduty`
--
ALTER TABLE `t_eventduty`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_help`
--
ALTER TABLE `t_help`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_ini`
--
ALTER TABLE `t_ini`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_lap`
--
ALTER TABLE `t_lap`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_link`
--
ALTER TABLE `t_link`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_message`
--
ALTER TABLE `t_message`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_protest`
--
ALTER TABLE `t_protest`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_race`
--
ALTER TABLE `t_race`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_racestate`
--
ALTER TABLE `t_racestate`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_result`
--
ALTER TABLE `t_result`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_resultfile`
--
ALTER TABLE `t_resultfile`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_rotamember`
--
ALTER TABLE `t_rotamember`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_series`
--
ALTER TABLE `t_series`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `series_code` (`seriescode`);

--
-- Indexes for table `t_tide`
--
ALTER TABLE `t_tide`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `z_entry`
--
ALTER TABLE `z_entry`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `e_consent`
--
ALTER TABLE `e_consent`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `e_contact`
--
ALTER TABLE `e_contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'open event record id';

--
-- AUTO_INCREMENT for table `e_content`
--
ALTER TABLE `e_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'open event record id';

--
-- AUTO_INCREMENT for table `e_document`
--
ALTER TABLE `e_document`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'open event record id';

--
-- AUTO_INCREMENT for table `e_entry`
--
ALTER TABLE `e_entry`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'open event record id';

--
-- AUTO_INCREMENT for table `e_event`
--
ALTER TABLE `e_event`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'open event record id';

--
-- AUTO_INCREMENT for table `e_form`
--
ALTER TABLE `e_form`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id for form recod';

--
-- AUTO_INCREMENT for table `e_notice`
--
ALTER TABLE `e_notice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'open event record id';

--
-- AUTO_INCREMENT for table `e_results`
--
ALTER TABLE `e_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'open event record id';

--
-- AUTO_INCREMENT for table `rm_admin_audit`
--
ALTER TABLE `rm_admin_audit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rm_admin_settings`
--
ALTER TABLE `rm_admin_settings`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rm_admin_uggroups`
--
ALTER TABLE `rm_admin_uggroups`
  MODIFY `GroupID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rm_admin_users`
--
ALTER TABLE `rm_admin_users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_cfgfleet`
--
ALTER TABLE `t_cfgfleet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_cfgrace`
--
ALTER TABLE `t_cfgrace`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_cfgseries`
--
ALTER TABLE `t_cfgseries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key';

--
-- AUTO_INCREMENT for table `t_class`
--
ALTER TABLE `t_class`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'identifier';

--
-- AUTO_INCREMENT for table `t_code_result`
--
ALTER TABLE `t_code_result`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT COMMENT 'code identifier';

--
-- AUTO_INCREMENT for table `t_code_system`
--
ALTER TABLE `t_code_system`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key';

--
-- AUTO_INCREMENT for table `t_code_type`
--
ALTER TABLE `t_code_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'primary key for table';

--
-- AUTO_INCREMENT for table `t_competitor`
--
ALTER TABLE `t_competitor`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'identifier';

--
-- AUTO_INCREMENT for table `t_course`
--
ALTER TABLE `t_course`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'course id';

--
-- AUTO_INCREMENT for table `t_coursedetail`
--
ALTER TABLE `t_coursedetail`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'course id';

--
-- AUTO_INCREMENT for table `t_cruise`
--
ALTER TABLE `t_cruise`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key';

--
-- AUTO_INCREMENT for table `t_entry`
--
ALTER TABLE `t_entry`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key';

--
-- AUTO_INCREMENT for table `z_entry_draft`
--
ALTER TABLE `z_entry_draft`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key';

--
-- AUTO_INCREMENT for table `t_event`
--
ALTER TABLE `t_event`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key - note this is the same as the event id in clubManager programme function';

--
-- AUTO_INCREMENT for table `t_eventduty`
--
ALTER TABLE `t_eventduty`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_help`
--
ALTER TABLE `t_help`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key';

--
-- AUTO_INCREMENT for table `t_ini`
--
ALTER TABLE `t_ini`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key';

--
-- AUTO_INCREMENT for table `t_lap`
--
ALTER TABLE `t_lap`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'identifier';

--
-- AUTO_INCREMENT for table `t_link`
--
ALTER TABLE `t_link`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key';

--
-- AUTO_INCREMENT for table `t_message`
--
ALTER TABLE `t_message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_protest`
--
ALTER TABLE `t_protest`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_race`
--
ALTER TABLE `t_race`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key';

--
-- AUTO_INCREMENT for table `t_racestate`
--
ALTER TABLE `t_racestate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key';

--
-- AUTO_INCREMENT for table `t_result`
--
ALTER TABLE `t_result`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'primary key for this table';

--
-- AUTO_INCREMENT for table `t_resultfile`
--
ALTER TABLE `t_resultfile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_rotamember`
--
ALTER TABLE `t_rotamember`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_series`
--
ALTER TABLE `t_series`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key';

--
-- AUTO_INCREMENT for table `t_tide`
--
ALTER TABLE `t_tide`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `z_entry`
--
ALTER TABLE `z_entry`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key';
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
