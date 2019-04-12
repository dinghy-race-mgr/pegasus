-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Sep 07, 2015 at 02:39 PM
-- Server version: 5.5.32
-- PHP Version: 5.4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `rm_v10`
--
CREATE DATABASE IF NOT EXISTS `rm_v10` DEFAULT CHARACTER SET latin1 COLLATE latin1_general_ci;
USE `rm_v10`;

-- --------------------------------------------------------

--
-- Table structure for table `a_lap`
--

CREATE TABLE IF NOT EXISTS `a_lap` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'identifier',
  `eventid` int(11) NOT NULL COMMENT 'event record id from t_event',
  `entryid` int(11) NOT NULL COMMENT 'entry id from rtblrace',
  `race` int(2) NOT NULL COMMENT 'race number in event',
  `lap` int(3) NOT NULL COMMENT 'lap number',
  `position` int(3) NOT NULL COMMENT 'position at end of this lap',
  `etime` int(6) NOT NULL COMMENT 'elapsed time at end of lap (secs)',
  `ctime` int(6) DEFAULT NULL COMMENT 'corrected time at end of lap (secs)',
  `clicktime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time lap recorded',
  `status` tinyint(1) DEFAULT '1' COMMENT 'field used to indicate whether this lap to be included in results (1 = include)'')',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=805 ;

-- --------------------------------------------------------

--
-- Table structure for table `a_race`
--

CREATE TABLE IF NOT EXISTS `a_race` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key',
  `eventid` int(11) NOT NULL DEFAULT '0' COMMENT 'event record id from t_event',
  `start` int(2) NOT NULL COMMENT 'start number',
  `fleet` int(2) NOT NULL COMMENT 'fleet  number',
  `competitorid` int(11) NOT NULL COMMENT 'competitor record id from t_competitor',
  `helm` varchar(40) COLLATE latin1_general_ci NOT NULL DEFAULT '' COMMENT 'helm name',
  `crew` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'crew name',
  `club` varchar(60) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'club name',
  `class` varchar(40) COLLATE latin1_general_ci NOT NULL DEFAULT '' COMMENT 'boat class name',
  `classcode` varchar(6) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'class acronym',
  `sailnum` varchar(20) COLLATE latin1_general_ci NOT NULL DEFAULT '' COMMENT 'sail number used in race',
  `pn` int(5) NOT NULL DEFAULT '0' COMMENT 'portsmouth number used in this race',
  `starttime` time DEFAULT NULL COMMENT 'start time stamp',
  `clicktime` time DEFAULT NULL COMMENT 'date/time of last timing click',
  `lap` int(3) NOT NULL DEFAULT '0' COMMENT 'completed laps',
  `finishlap` int(3) DEFAULT NULL COMMENT 'lap boat will finish on (this is only required for average lap racing - can I find a better way)',
  `etime` int(6) NOT NULL DEFAULT '0' COMMENT 'last elapsed time recorded (secs)',
  `ctime` int(6) NOT NULL DEFAULT '0' COMMENT 'last corrected time (secs)',
  `atime` int(6) NOT NULL DEFAULT '0' COMMENT 'aggregate time (pro-rata time for all boats doing the same number of laps in average lap race)',
  `ptime` int(6) NOT NULL DEFAULT '0' COMMENT 'predicted elapsed time for next lap (secs)',
  `code` varchar(10) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'result code for competitor (e.g. DNF)',
  `penalty` int(3) NOT NULL DEFAULT '0',
  `points` int(3) NOT NULL DEFAULT '0' COMMENT 'last points calculated',
  `declaration` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'declaration status ',
  `note` varchar(200) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'notes field',
  `status` enum('E','R','F','X') COLLATE latin1_general_ci DEFAULT NULL COMMENT 'status field (E = entered, R = racing, F = finished, X = excluded',
  PRIMARY KEY (`id`),
  UNIQUE KEY `competitorID` (`competitorid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='current state for each competitor in races today' AUTO_INCREMENT=7487 ;

-- --------------------------------------------------------

--
-- Table structure for table `rm_admin_uggroups`
--

CREATE TABLE IF NOT EXISTS `rm_admin_uggroups` (
  `GroupID` int(11) NOT NULL AUTO_INCREMENT,
  `Label` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`GroupID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `rm_admin_ugmembers`
--

CREATE TABLE IF NOT EXISTS `rm_admin_ugmembers` (
  `UserName` varchar(50) NOT NULL,
  `GroupID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`UserName`,`GroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `rm_admin_ugrights`
--

CREATE TABLE IF NOT EXISTS `rm_admin_ugrights` (
  `TableName` varchar(50) NOT NULL,
  `GroupID` int(11) NOT NULL DEFAULT '0',
  `AccessMask` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`TableName`,`GroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `rm_admin_users`
--

CREATE TABLE IF NOT EXISTS `rm_admin_users` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(300) DEFAULT NULL,
  `password` varchar(300) DEFAULT NULL,
  `email` varchar(300) DEFAULT NULL,
  `fullname` varchar(300) DEFAULT NULL,
  `groupid` varchar(300) DEFAULT NULL,
  `active` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_cfgfleet`
--

CREATE TABLE IF NOT EXISTS `t_cfgfleet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eventcfgid` int(11) NOT NULL,
  `start_num` int(2) NOT NULL COMMENT 'start number',
  `fleet_num` tinyint(4) NOT NULL COMMENT 'race fleet  number (in sequence)',
  `fleet_code` varchar(10) COLLATE latin1_general_ci NOT NULL COMMENT 'short code for fleet (e.g. SH)',
  `fleet_name` varchar(60) COLLATE latin1_general_ci NOT NULL COMMENT 'full name for fleet (e.g. slow handicap)',
  `fleet_desc` varchar(200) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'description of fleet',
  `scoring` enum('handicap','average','level','pursuit') COLLATE latin1_general_ci NOT NULL COMMENT 'type of race scoring (should this be integer code)',
  `py_type` enum('national','local','personal') COLLATE latin1_general_ci NOT NULL DEFAULT 'local' COMMENT 'type of PN number to be used for this race',
  `warn_signal` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'filename for signal flag for warning signal',
  `prep_signal` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'filename for signal flag used for preparatory signal',
  `timelimit_abs` int(4) DEFAULT NULL COMMENT 'absolute time limit for first finisher (minutes)',
  `timelimit_rel` int(4) DEFAULT NULL COMMENT 'time limit for subsequent finishers after leader (minutes)',
  `defaultlaps` int(2) NOT NULL DEFAULT '0' COMMENT 'default number of laps for this race',
  `defaultfleet` int(1) NOT NULL DEFAULT '0' COMMENT 'test for allocation to this race after checking all other races',
  `classinc` varchar(200) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'list of classes to be allocated to this race',
  `onlyinc` int(1) NOT NULL DEFAULT '0' COMMENT 'if set - only the listed included classes are allowed',
  `classexc` varchar(200) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'list of classes to be excluded from this race',
  `groupinc` varchar(200) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'groups to be eligible for this race',
  `min_py` int(4) DEFAULT NULL COMMENT 'minimum PY for this race',
  `max_py` int(4) DEFAULT NULL COMMENT 'maximum PY for this race',
  `crew` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'crew constraint for classes in this race',
  `spintype` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'spinnaker type constraints for classes in this race',
  `hulltype` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'hull type constraint for classes in this race',
  `min_helmage` int(3) DEFAULT NULL COMMENT 'minimum helm of age for inclusion in this race',
  `max_helmage` int(3) DEFAULT NULL COMMENT 'maximum age of helm for this race',
  `min_skill` int(2) DEFAULT NULL COMMENT 'minimum skill level for this race',
  `max_skill` int(2) DEFAULT NULL COMMENT 'maximum skill level for this race',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'date of last update',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=17 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_cfgrace`
--

CREATE TABLE IF NOT EXISTS `t_cfgrace` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `race_code` varchar(6) COLLATE latin1_general_ci NOT NULL,
  `race_name` varchar(30) COLLATE latin1_general_ci NOT NULL,
  `race_desc` varchar(500) COLLATE latin1_general_ci DEFAULT NULL,
  `pursuit` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'true if a pursuit race format',
  `numstarts` tinyint(3) NOT NULL,
  `start_scheme` varchar(20) COLLATE latin1_general_ci NOT NULL COMMENT 'start scheme for preparatory, warning and start signals (e.g 5,4,1,0 or 10,5,0)',
  `start_interval` int(4) NOT NULL COMMENT 'time interval (secs) between each start ',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'indicates if event type is still in use',
  `comp_pick` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if set user can choose their start',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'date of last update',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='Event configuration information (e.g club series).' AUTO_INCREMENT=16 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_cfgseries`
--

CREATE TABLE IF NOT EXISTS `t_cfgseries` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key',
  `seriestype` varchar(30) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'series type code (e.g. short, long etc)',
  `discard` varchar(60) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'discard profile usig Sailwave coding (e.g. 0,0,1,1,2 means in the five race series one discard is applied after three races and 2 discards if all five races are complete)',
  `nodiscard` varchar(40) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL COMMENT 'comma separated list of races that cannot be discarded in series',
  `doublepoint` varchar(40) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL COMMENT 'comma separated list of races that count for double points in series',
  `avgscheme` varchar(40) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'scheme for calculating average points',
  `dutypoints` int(2) NOT NULL COMMENT 'duty points (0 is average points, otherwise fixed points)',
  `dutynum` int(2) NOT NULL DEFAULT '10' COMMENT 'number of duties allowed to score',
  `pyused` varchar(20) NOT NULL COMMENT 'py to be used (local, national, personal)',
  `notes` varchar(500) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL COMMENT 'Notes on series - e.g. a description',
  `active` int(1) NOT NULL DEFAULT '1' COMMENT 'if true - can be used for events',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Defines each race series - includes parent series option (series of series)' AUTO_INCREMENT=73 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_class`
--

CREATE TABLE IF NOT EXISTS `t_class` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'identifier',
  `acronym` char(6) COLLATE latin1_general_ci NOT NULL DEFAULT 'xxx' COMMENT '3 letter acronym for class (e.g. FB -> fireball)',
  `classname` varchar(40) COLLATE latin1_general_ci NOT NULL COMMENT 'class name',
  `info` varchar(255) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'description',
  `popular` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'used to denote popular classes at the club - will be disoplayed before the other classes',
  `nat_py` int(5) unsigned NOT NULL COMMENT 'national PN',
  `local_py` int(5) unsigned NOT NULL COMMENT 'local PN',
  `rya_id` char(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'rya unique identifier',
  `category` enum('D','M','K','C','F') COLLATE latin1_general_ci NOT NULL DEFAULT 'D' COMMENT 'boat category [D=dinghy, M=multihull, K=keelboat, F=foiler]',
  `crew` enum('1','2','N','') COLLATE latin1_general_ci NOT NULL DEFAULT '1' COMMENT 'number of crew',
  `rig` enum('U','S','K','') COLLATE latin1_general_ci NOT NULL DEFAULT 'U' COMMENT 'rig type [U=una, S=sloop, M=multi]',
  `spinnaker` enum('O','C','A','') COLLATE latin1_general_ci NOT NULL DEFAULT 'O' COMMENT 'spinnaker type [C=conventional, A=asymmetric, O=none]',
  `engine` enum('OB','IBF','IB2','IB3') COLLATE latin1_general_ci NOT NULL DEFAULT 'OB' COMMENT 'engine [OB=outboard, IB2=2 bladed fixed propellor, IB3=3 bladed fixed propellor, IBF=folding propellor]',
  `keel` enum('D','F','2K','3K') COLLATE latin1_general_ci NOT NULL DEFAULT 'D' COMMENT 'keel type [D=drop keel, F=single fixed, 2K=twin keel, 3K=triple keel]',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  PRIMARY KEY (`id`),
  UNIQUE KEY `class` (`classname`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='class configuration details - including RYA coding' AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_code_result`
--

CREATE TABLE IF NOT EXISTS `t_code_result` (
  `id` int(6) NOT NULL AUTO_INCREMENT COMMENT 'code identifier',
  `code` char(6) COLLATE latin1_general_ci NOT NULL COMMENT 'result code (e.g. DNF)',
  `info` varchar(500) COLLATE latin1_general_ci NOT NULL COMMENT 'description',
  `scoring` varchar(20) COLLATE latin1_general_ci NOT NULL COMMENT 'scoring equation (using N= num in race, S=num in series)',
  `timing` int(1) NOT NULL COMMENT 'true if timing should continue after code is set',
  `visibility` int(1) NOT NULL COMMENT 'where code is used (0 anywhere, 1 OOD only)',
  `nonexclude` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'true if result cannot be excluded in series',
  `rank` int(2) NOT NULL DEFAULT '1' COMMENT 'order for display',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updby` varchar(40) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='list of result codes and actions to take' AUTO_INCREMENT=16 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_code_system`
--

CREATE TABLE IF NOT EXISTS `t_code_system` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key',
  `groupname` varchar(40) COLLATE latin1_general_ci NOT NULL COMMENT 'code group',
  `code` varchar(40) COLLATE latin1_general_ci NOT NULL COMMENT 'code value',
  `label` varchar(200) COLLATE latin1_general_ci NOT NULL COMMENT 'code label (for use in interface)',
  `rank` int(3) NOT NULL DEFAULT '0' COMMENT 'order in list',
  `defaultval` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'default value (if 1)',
  `deletable` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'if set code can be deleted',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'date of last update',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'updated by',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='codes used in the racemanager application' AUTO_INCREMENT=66 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_code_type`
--

CREATE TABLE IF NOT EXISTS `t_code_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'primary key for table',
  `groupname` varchar(40) COLLATE latin1_general_ci NOT NULL COMMENT 'name for code which is used to identify the codes belonging to this type',
  `label` varchar(40) COLLATE latin1_general_ci NOT NULL COMMENT 'label for the code type',
  `info` varchar(500) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'description for the code type',
  `rank` int(3) NOT NULL DEFAULT '1' COMMENT 'display order',
  `type` enum('system','club') COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_competitor`
--

CREATE TABLE IF NOT EXISTS `t_competitor` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'identifier',
  `classid` int(10) NOT NULL COMMENT 'class id in rtblclass',
  `boatnum` varchar(20) COLLATE latin1_general_ci NOT NULL COMMENT 'boat number',
  `sailnum` varchar(20) COLLATE latin1_general_ci NOT NULL COMMENT 'sail number used',
  `boatname` varchar(60) COLLATE latin1_general_ci DEFAULT NULL,
  `helm` varchar(40) COLLATE latin1_general_ci NOT NULL COMMENT 'helm name',
  `helm_dob` date DEFAULT NULL COMMENT 'helm date of birth',
  `helm_email` varchar(100) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'helm email address',
  `crew` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'crew name',
  `crew_dob` date DEFAULT NULL COMMENT 'crew date of birth',
  `crew_email` varchar(100) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'crew email address',
  `club` varchar(60) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'sailing club name',
  `personal_py` int(5) DEFAULT NULL COMMENT 'personal PN',
  `skill_level` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'skill coding',
  `flight` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'allocated flight for specific events',
  `regular` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if true this is a regular sailor who can be entered in bulk',
  `last_entry` date DEFAULT NULL COMMENT 'date of last entry',
  `last_event` int(11) DEFAULT NULL COMMENT 'last event (id) entered',
  `status` enum('current','retired','review','') COLLATE latin1_general_ci NOT NULL DEFAULT 'current' COMMENT 'status (current, retired, review) ',
  `grouplist` varchar(60) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'comma separated list of member groups (e.g. member, visitor, junior)',
  `memberid` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'member id/code',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  PRIMARY KEY (`id`),
  KEY `lastRaced` (`last_entry`),
  KEY `visibility` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='competitor details' AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_entry`
--

CREATE TABLE IF NOT EXISTS `t_entry` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key',
  `action` enum('enter','delete','update','retire','replace','declare') COLLATE latin1_general_ci NOT NULL COMMENT 'action for racemanager to take on entry',
  `status` enum('L','N','F') COLLATE latin1_general_ci NOT NULL DEFAULT 'N' COMMENT 'status (N=not loaded, L=loaded, F=failed)',
  `eventid` int(11) NOT NULL COMMENT 'event record id from t_event',
  `competitorid` int(11) NOT NULL COMMENT 'competitor record id from t_competitor',
  `memberid` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'memberid from t_competitor',
  `change_crew` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'temporary crew change for races today',
  `change_sailnum` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'temporary sailno change for today',
  `entryid` int(11) DEFAULT NULL COMMENT 'id of entry into race table (t_race)',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_event`
--

CREATE TABLE IF NOT EXISTS `t_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key - note this is the same as the event id in clubManager programme function',
  `event_date` date NOT NULL COMMENT 'event date',
  `event_start` varchar(10) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'time of start (as text HH:MM)',
  `event_order` int(2) DEFAULT '1' COMMENT 'order of event on day - used if start times not defined',
  `event_name` varchar(60) COLLATE latin1_general_ci NOT NULL COMMENT 'event name (e.g. Summer Series, Kathleen Trophy, etc.)',
  `seriescode` varchar(100) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'foreign key to series information in t_series',
  `event_type` varchar(20) CHARACTER SET utf8 NOT NULL COMMENT 'type of event (e.g. cruise, race, etc.)',
  `event_format` int(11) NOT NULL COMMENT 'race format for event - references event configuration record in t_eventcfg',
  `event_entry` varchar(20) COLLATE latin1_general_ci NOT NULL COMMENT 'entry type (e.g ood or electronic signon etc.) - codes in t_code',
  `event_status` varchar(40) COLLATE latin1_general_ci NOT NULL DEFAULT 'scheduled' COMMENT 'current status of event - codes in t_code',
  `event_open` enum('local','open','','') COLLATE latin1_general_ci NOT NULL DEFAULT 'local' COMMENT 'indicates if it is an open event',
  `tide_time` varchar(10) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'time of high water (as text HH:MM)',
  `tide_height` varchar(10) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'height of tide in metres',
  `start_scheme` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'field to change the default start scheme specified in event configuration',
  `start_interval` int(4) DEFAULT NULL COMMENT 'field to overwrite the default start interval defined in event configuration',
  `ws_start` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'wind speed at start of race (from coded range)',
  `wd_start` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'wind direction at start (from coded range)',
  `ws_end` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'wind speed at end of race (from coded range)',
  `wd_end` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'wind direction at end (from coded range)',
  `event_notes` varchar(500) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'notes field will appear in programme display',
  `result_notes` varchar(500) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'notes relating to the results',
  `display_code` char(1) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'flag to define display?',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` char(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='programmed events that racemanager will run' AUTO_INCREMENT=1114 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_eventduty`
--

CREATE TABLE IF NOT EXISTS `t_eventduty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eventid` int(11) NOT NULL,
  `dutycode` varchar(40) COLLATE latin1_general_ci NOT NULL,
  `person` varchar(40) COLLATE latin1_general_ci NOT NULL,
  `notes` varchar(500) COLLATE latin1_general_ci DEFAULT NULL,
  `memberid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=37 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_finish`
--

CREATE TABLE IF NOT EXISTS `t_finish` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key',
  `entryid` int(11) NOT NULL COMMENT 'record id from race table ',
  `finish_1` int(3) DEFAULT '0' COMMENT 'position at finish 1',
  `finish_2` int(3) DEFAULT '0' COMMENT 'position at finish 2',
  `finish_3` int(3) DEFAULT '0' COMMENT 'position at finish 3',
  `finish_4` int(3) DEFAULT '0' COMMENT 'position at finish 4',
  `finish_5` int(3) DEFAULT '0' COMMENT 'position at finish 5',
  `finish_6` int(3) DEFAULT '0' COMMENT 'position at finish 6',
  `finish_7` int(3) DEFAULT '0' COMMENT 'position at finish 7',
  `finish_8` int(3) DEFAULT '0' COMMENT 'position at finish 8',
  `finish_9` int(3) DEFAULT '0' COMMENT 'position at finish 9',
  `forder` int(3) NOT NULL DEFAULT '0' COMMENT 'sequential finish order',
  `place` int(3) DEFAULT '0' COMMENT 'place in race',
  `status` varchar(500) COLLATE latin1_general_ci DEFAULT NULL COMMENT '??????',
  `state` int(1) NOT NULL DEFAULT '0' COMMENT '??????',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='finish positions for multi finish line pursuit races' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_help`
--

CREATE TABLE IF NOT EXISTS `t_help` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key',
  `category` varchar(60) COLLATE latin1_general_ci NOT NULL COMMENT 'help category - comma separated',
  `question` varchar(500) COLLATE latin1_general_ci NOT NULL COMMENT 'question or help item heading',
  `answer` varchar(4000) COLLATE latin1_general_ci NOT NULL COMMENT 'answer text',
  `notes` varchar(4000) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'additional related information',
  `link1_url` varchar(150) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'URL for first link',
  `link1_label` varchar(60) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'label for first link',
  `link2_url` varchar(150) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'URL for second link',
  `link2_label` varchar(60) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'label for second link',
  `author` varchar(50) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'person creating help item',
  `rank` int(3) NOT NULL DEFAULT '0' COMMENT 'order in which they should be displayed within category',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='Holds configurable help information' AUTO_INCREMENT=51 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_ini`
--

CREATE TABLE IF NOT EXISTS `t_ini` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key',
  `category` varchar(40) COLLATE latin1_general_ci NOT NULL COMMENT 'configuration parameter category (club, interface, entry, results, admin]',
  `parameter` varchar(40) COLLATE latin1_general_ci NOT NULL COMMENT 'name of parameter',
  `label` varchar(40) COLLATE latin1_general_ci NOT NULL,
  `value` varchar(200) COLLATE latin1_general_ci NOT NULL COMMENT 'value of parameter',
  `notes` varchar(500) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'Information on what values can be used for this setting',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'date of last update',
  `updby` char(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='club configurable parameters used in racemanager' AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_lap`
--

CREATE TABLE IF NOT EXISTS `t_lap` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'identifier',
  `eventid` int(11) NOT NULL COMMENT 'event record id from t_event',
  `entryid` int(11) NOT NULL COMMENT 'entry id from rtblrace',
  `race` int(2) NOT NULL COMMENT 'race number in event',
  `lap` int(3) NOT NULL COMMENT 'lap number',
  `position` int(3) NOT NULL COMMENT 'position at end of this lap',
  `etime` int(6) NOT NULL COMMENT 'elapsed time at end of lap (secs)',
  `ctime` int(6) DEFAULT NULL COMMENT 'corrected time at end of lap (secs)',
  `clicktime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time lap recorded',
  `status` tinyint(1) DEFAULT '1' COMMENT 'field used to indicate whether this lap to be included in results (1 = include)'')',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=26 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_link`
--

CREATE TABLE IF NOT EXISTS `t_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key',
  `label` varchar(60) COLLATE latin1_general_ci NOT NULL COMMENT 'label for link label to appear in interface',
  `url` varchar(200) COLLATE latin1_general_ci NOT NULL COMMENT 'URL for link (e.g. http://....)',
  `tip` varchar(500) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'tooltip text for link',
  `category` varchar(20) COLLATE latin1_general_ci NOT NULL COMMENT 'link category (allows grouping of links for different pages in system) - only one category permitted so it may be necessary to duplicate links if they are to appear in more than one place.',
  `rank` tinyint(2) NOT NULL DEFAULT '0' COMMENT 'order in which to display links (within category)',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='Club specific links to additional information' AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_message`
--

CREATE TABLE IF NOT EXISTS `t_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eventid` int(11) NOT NULL,
  `name` varchar(60) COLLATE latin1_general_ci NOT NULL,
  `subject` varchar(60) COLLATE latin1_general_ci NOT NULL,
  `message` text COLLATE latin1_general_ci NOT NULL,
  `email` varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
  `response` text COLLATE latin1_general_ci,
  `status` enum('received','responded','closed') COLLATE latin1_general_ci NOT NULL DEFAULT 'received',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=339 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_race`
--

CREATE TABLE IF NOT EXISTS `t_race` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key',
  `eventid` int(11) NOT NULL DEFAULT '0' COMMENT 'event record id from t_event',
  `start` int(2) NOT NULL COMMENT 'start number',
  `fleet` int(2) NOT NULL COMMENT 'fleet  number',
  `competitorid` int(11) NOT NULL COMMENT 'competitor record id from t_competitor',
  `helm` varchar(40) COLLATE latin1_general_ci NOT NULL DEFAULT '' COMMENT 'helm name',
  `crew` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'crew name',
  `club` varchar(60) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'club name',
  `class` varchar(40) COLLATE latin1_general_ci NOT NULL DEFAULT '' COMMENT 'boat class name',
  `classcode` varchar(6) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'class acronym',
  `sailnum` varchar(20) COLLATE latin1_general_ci NOT NULL DEFAULT '' COMMENT 'sail number used in race',
  `pn` int(5) NOT NULL DEFAULT '0' COMMENT 'portsmouth number used in this race',
  `starttime` time DEFAULT NULL COMMENT 'start time stamp',
  `clicktime` time DEFAULT NULL COMMENT 'date/time of last timing click',
  `lap` int(3) NOT NULL DEFAULT '0' COMMENT 'completed laps',
  `finishlap` int(3) DEFAULT NULL COMMENT 'lap boat will finish on (this is only required for average lap racing - can I find a better way)',
  `etime` int(6) NOT NULL DEFAULT '0' COMMENT 'last elapsed time recorded (secs)',
  `ctime` int(6) NOT NULL DEFAULT '0' COMMENT 'last corrected time (secs)',
  `atime` int(6) NOT NULL DEFAULT '0' COMMENT 'aggregate time (pro-rata time for all boats doing the same number of laps in average lap race)',
  `ptime` int(6) NOT NULL DEFAULT '0' COMMENT 'predicted elapsed time for next lap (secs)',
  `code` varchar(10) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'result code for competitor (e.g. DNF)',
  `penalty` int(3) NOT NULL DEFAULT '0',
  `points` int(3) NOT NULL DEFAULT '0' COMMENT 'last points calculated',
  `declaration` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'declaration status ',
  `note` varchar(200) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'notes field',
  `status` enum('E','R','F','X') COLLATE latin1_general_ci DEFAULT NULL COMMENT 'status field (E = entered, R = racing, F = finished, X = excluded',
  PRIMARY KEY (`id`),
  UNIQUE KEY `competitorID` (`competitorid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='current state for each competitor in races today' AUTO_INCREMENT=99 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_racestate`
--

CREATE TABLE IF NOT EXISTS `t_racestate` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key',
  `race` smallint(2) NOT NULL COMMENT 'no. of race in event',
  `racename` varchar(40) COLLATE latin1_general_ci NOT NULL COMMENT 'name for fleet',
  `start` int(2) NOT NULL COMMENT 'start number',
  `eventid` int(11) NOT NULL COMMENT 'event record id in t_event',
  `racetype` enum('hcap','avglap','level','pursuit','flight') COLLATE latin1_general_ci NOT NULL COMMENT 'type of race (pursuit, fleet, handicap, etc.) - should this be integer code',
  `startdelay` int(6) NOT NULL COMMENT 'delay of start from timer initialisation (secs)',
  `starttime` time NOT NULL DEFAULT '00:00:00' COMMENT 'start time of this race',
  `maxlap` int(3) NOT NULL DEFAULT '0' COMMENT 'maximum no. of laps for this race (equivalent to finish lap for all except average lap racing)',
  `currentlap` int(2) NOT NULL DEFAULT '0' COMMENT 'Current lap for leading boat on water',
  `entries` int(3) NOT NULL DEFAULT '0' COMMENT 'No. of entries',
  `status` enum('notstarted','inprogress','finishing','allfinished') COLLATE latin1_general_ci DEFAULT NULL COMMENT 'current race status',
  `prevstatus` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'previous race status',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last updated by',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time of last update',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_result`
--

CREATE TABLE IF NOT EXISTS `t_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'primary key for this table',
  `eventid` int(11) NOT NULL COMMENT 'event record id (from t_event)',
  `fleet` int(2) NOT NULL COMMENT 'fleet number in event',
  `race_type` enum('handicap','average','level','pursuit','none') COLLATE latin1_general_ci NOT NULL COMMENT 'race type as defined in t_racecfg (stored here to indicate scoring mechanism used when race was run)',
  `competitorid` int(11) NOT NULL COMMENT 'competitor record id (from t_competitor)',
  `class` varchar(40) COLLATE latin1_general_ci NOT NULL COMMENT 'class of boat (text)',
  `sailnum` varchar(20) COLLATE latin1_general_ci NOT NULL COMMENT 'sail number used',
  `pn` int(4) NOT NULL COMMENT 'PN used for this race',
  `helm` varchar(40) COLLATE latin1_general_ci NOT NULL COMMENT 'helm name for this race',
  `crew` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'crew name for this race',
  `club` varchar(60) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'club name',
  `lap` int(2) NOT NULL,
  `etime` int(6) NOT NULL COMMENT 'elapsed time at finish (secs)',
  `ctime` int(6) NOT NULL DEFAULT '0' COMMENT 'corrected time at finish',
  `atime` int(6) NOT NULL DEFAULT '0' COMMENT 'aggregate time (pro-rata time for all boats doing the same number of laps in average lap race)''',
  `code` char(6) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'finish code (e.g. DNC, DNF)',
  `penalty` int(3) NOT NULL DEFAULT '0',
  `points` int(3) NOT NULL COMMENT 'points awarded for race',
  `declaration` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'declared (signd off)  if true',
  `note` varchar(200) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'notes',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last updated by',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=19457 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_resultfile`
--

CREATE TABLE IF NOT EXISTS `t_resultfile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eventid` int(11) NOT NULL,
  `result_type` enum('race','series') COLLATE latin1_general_ci NOT NULL,
  `result_format` enum('htm','csv','pdf','') COLLATE latin1_general_ci NOT NULL,
  `result_path` varchar(150) COLLATE latin1_general_ci NOT NULL COMMENT 'result path name in results file',
  `result_notes` varchar(500) COLLATE latin1_general_ci DEFAULT NULL,
  `result_status` enum('final','provisional','embargoed','') COLLATE latin1_general_ci NOT NULL DEFAULT 'final',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=147 ;

-- --------------------------------------------------------

--
-- Table structure for table `t_series`
--

CREATE TABLE IF NOT EXISTS `t_series` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key',
  `seriescode` varchar(40) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'e.g.  AUTUMN',
  `seriesname` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'name of series (e.g. Autumn Series)',
  `seriestype` int(11) NOT NULL COMMENT 'series type from t_cfgseries',
  `startdate` date DEFAULT NULL COMMENT 'earliest date on wjhich series race can be sailed',
  `enddate` date DEFAULT NULL COMMENT 'latest date on which series race can be sailed',
  `classresults` varchar(300) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL COMMENT 'holds comma separated list of classes for which class specific results are required',
  `notes` varchar(500) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL COMMENT 'Notes on series - e.g. a description',
  `active` int(1) NOT NULL DEFAULT '1' COMMENT 'if true - can be used for events',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  PRIMARY KEY (`id`),
  UNIQUE KEY `series_code` (`seriescode`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Defines each race series - includes parent series option (series of series)' AUTO_INCREMENT=73 ;

-- --------------------------------------------------------

--
-- Table structure for table `z_classcfg`
--

CREATE TABLE IF NOT EXISTS `z_classcfg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `classid` int(10) NOT NULL,
  `eventid` tinyint(4) NOT NULL,
  `startnum` tinyint(4) NOT NULL,
  `racenum` tinyint(4) NOT NULL,
  `userating` tinyint(4) NOT NULL DEFAULT '1',
  `lapdelta` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='do I need this - calc on fly' AUTO_INCREMENT=620399 ;

-- --------------------------------------------------------

--
-- Table structure for table `z_classtmpresults`
--

CREATE TABLE IF NOT EXISTS `z_classtmpresults` (
  `resultID` int(11) NOT NULL AUTO_INCREMENT,
  `seriesCode` char(20) COLLATE latin1_general_ci NOT NULL,
  `seriesYear` year(4) NOT NULL,
  `seriesRace` tinyint(4) NOT NULL,
  `fleet` varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  `race` int(2) NOT NULL,
  `competitorID` int(11) NOT NULL,
  `class` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `sailnum` varchar(6) COLLATE latin1_general_ci NOT NULL,
  `helm` varchar(30) COLLATE latin1_general_ci NOT NULL,
  `crew` varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  `eventID` int(11) NOT NULL,
  `racetype` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `elapsedTime` time NOT NULL,
  `laps` int(2) NOT NULL,
  `correctedTime` time DEFAULT NULL,
  `resultPY` int(4) NOT NULL,
  `resultCode` char(6) COLLATE latin1_general_ci DEFAULT NULL,
  `resultPoints` smallint(6) NOT NULL,
  `resultNote` varchar(200) COLLATE latin1_general_ci DEFAULT NULL,
  `updateDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updateBy` varchar(20) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`resultID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=16473 ;

-- --------------------------------------------------------

--
-- Table structure for table `z_pyanalysis`
--

CREATE TABLE IF NOT EXISTS `z_pyanalysis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `series` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'series code (e.g. FROSTBITE11)',
  `event` int(10) NOT NULL COMMENT 'event id (from tblfixture)',
  `race` int(2) NOT NULL COMMENT 'race number in event',
  `competitor` int(10) NOT NULL COMMENT 'competitor id (from tblcompetitors)',
  `helm` varchar(60) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'helm name (e.g. Barney Rubble)',
  `class` varchar(60) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'class name (e.g. Hornet)',
  `sailnum` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'sail number ',
  `ct` int(10) NOT NULL COMMENT 'corrected time (secs)',
  `py` int(6) NOT NULL COMMENT 'py used for the race',
  `lpy` int(6) NOT NULL COMMENT 'current locally adjusted py',
  `ppy` int(6) NOT NULL COMMENT 'personal py',
  `status` int(2) NOT NULL COMMENT 'status (-1 = unknown error, 0 = OK, 1 = outside performance limit, 2 = did not finish',
  `updatedate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `z_tempresults`
--

CREATE TABLE IF NOT EXISTS `z_tempresults` (
  `seriesResultID` int(11) NOT NULL AUTO_INCREMENT,
  `seriesYear` year(4) NOT NULL,
  `seriesCode` char(20) COLLATE latin1_general_ci NOT NULL,
  `competitorID` int(11) NOT NULL,
  `helmName` varchar(40) COLLATE latin1_general_ci NOT NULL,
  `crewName` varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  `class` varchar(40) COLLATE latin1_general_ci NOT NULL,
  `fleet` varchar(40) COLLATE latin1_general_ci NOT NULL,
  `seriesFleet` char(2) COLLATE latin1_general_ci NOT NULL,
  `sailno` char(10) COLLATE latin1_general_ci NOT NULL,
  `racePoints1` tinyint(4) NOT NULL,
  `racePoints2` tinyint(4) NOT NULL,
  `racePoints3` tinyint(4) NOT NULL,
  `racePoints4` tinyint(4) NOT NULL,
  `racePoints5` tinyint(4) NOT NULL,
  `racePoints6` tinyint(4) NOT NULL,
  `racePoints7` tinyint(4) NOT NULL,
  `racePoints8` tinyint(4) NOT NULL,
  `racePoints9` tinyint(4) NOT NULL,
  `racePoints10` tinyint(4) NOT NULL,
  `racePoints11` tinyint(4) NOT NULL,
  `racePoints12` tinyint(4) NOT NULL,
  `racePoints13` tinyint(4) NOT NULL,
  `racePoints14` tinyint(4) NOT NULL,
  `racePoints15` tinyint(4) NOT NULL,
  `racePoints16` tinyint(4) NOT NULL,
  `racePoints17` tinyint(4) NOT NULL,
  `racePoints18` tinyint(4) NOT NULL,
  `racePoints19` tinyint(4) NOT NULL,
  `racePoints20` tinyint(4) NOT NULL,
  `discard1` tinyint(1) NOT NULL DEFAULT '0',
  `discard2` tinyint(1) NOT NULL DEFAULT '0',
  `discard3` tinyint(1) NOT NULL DEFAULT '0',
  `discard4` tinyint(1) NOT NULL DEFAULT '0',
  `discard5` tinyint(1) NOT NULL DEFAULT '0',
  `discard6` tinyint(1) NOT NULL DEFAULT '0',
  `discard7` tinyint(1) NOT NULL DEFAULT '0',
  `discard8` tinyint(1) NOT NULL DEFAULT '0',
  `discard9` tinyint(1) NOT NULL DEFAULT '0',
  `discard10` tinyint(1) NOT NULL DEFAULT '0',
  `discard11` tinyint(1) NOT NULL DEFAULT '0',
  `discard12` tinyint(1) NOT NULL DEFAULT '0',
  `discard13` tinyint(1) NOT NULL DEFAULT '0',
  `discard14` tinyint(1) NOT NULL DEFAULT '0',
  `discard15` tinyint(1) NOT NULL DEFAULT '0',
  `discard16` tinyint(1) NOT NULL DEFAULT '0',
  `discard17` tinyint(1) NOT NULL DEFAULT '0',
  `discard18` tinyint(1) NOT NULL DEFAULT '0',
  `discard19` tinyint(1) NOT NULL DEFAULT '0',
  `discard20` tinyint(1) NOT NULL DEFAULT '0',
  `numPosn1` tinyint(4) DEFAULT NULL,
  `numPosn2` tinyint(4) DEFAULT NULL,
  `numPosn3` tinyint(4) DEFAULT NULL,
  `numPosn4` tinyint(4) DEFAULT NULL,
  `numPosn5` tinyint(4) DEFAULT NULL,
  `numPosn6` tinyint(4) DEFAULT NULL,
  `totalPoints` int(11) NOT NULL,
  `netPoints` int(11) NOT NULL,
  `position` tinyint(4) DEFAULT NULL,
  `tie` tinyint(1) NOT NULL DEFAULT '0',
  `seriesResultNote` varchar(200) COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`seriesResultID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='needs redesigning - one record per race' AUTO_INCREMENT=26 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
