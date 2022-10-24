-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 11, 2022 at 06:59 PM
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
-- Database: `pegasus_stx`
--

-- --------------------------------------------------------

--
-- Table structure for table `a_entry`
--

CREATE TABLE `a_entry` (
  `id` int(11) NOT NULL,
  `action` enum('enter','delete','update','retire','replace','declare') COLLATE latin1_general_ci NOT NULL COMMENT 'action for racemanager to take on entry',
  `protest` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 if declaring protest',
  `status` enum('N','L','F','E','A') COLLATE latin1_general_ci NOT NULL DEFAULT 'N' COMMENT 'status (N=not loaded, L=loaded, F=failed. E=not eligible, A=not allocated)',
  `eventid` int(11) NOT NULL COMMENT 'event record id from t_event',
  `competitorid` int(11) NOT NULL COMMENT 'competitor record id from t_competitor',
  `memberid` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'memberid from t_competitor',
  `chg-helm` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'has value if helm name is changed',
  `chg-crew` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'temporary crew change for races today',
  `chg-sailnum` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'temporary sailno change for today',
  `entryid` int(11) DEFAULT '0' COMMENT 'id of entry into race table (t_race)',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `a_finish`
--

CREATE TABLE `a_finish` (
  `id` int(11) NOT NULL,
  `eventid` int(11) NOT NULL COMMENT 'event id',
  `entryid` int(11) NOT NULL COMMENT 'record id from race table ',
  `finish_1` int(3) DEFAULT '0' COMMENT 'position at finish 1',
  `finish_2` int(3) DEFAULT '0' COMMENT 'position at finish 2',
  `finish_3` int(3) DEFAULT '0' COMMENT 'position at finish 3',
  `finish_4` int(3) DEFAULT '0' COMMENT 'position at finish 4',
  `finish_5` int(3) DEFAULT '0' COMMENT 'position at finish 5',
  `finish_6` int(3) DEFAULT '0' COMMENT 'position at finish 6',
  `forder` int(3) NOT NULL DEFAULT '0' COMMENT 'sequential finish order',
  `place` int(3) DEFAULT '0' COMMENT 'place in race',
  `status` varchar(500) COLLATE latin1_general_ci DEFAULT NULL COMMENT '??????',
  `state` int(1) NOT NULL DEFAULT '0' COMMENT '??????',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time of last update',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='finish positions for multi finish line pursuit races';

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
  `status` tinyint(1) DEFAULT '1' COMMENT 'field used to indicate whether this lap to be included in results (1 = include)'')',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time of last update',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
  `trackerid` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'identifier for position tracker',
  `helm` varchar(40) COLLATE latin1_general_ci NOT NULL DEFAULT '' COMMENT 'helm name',
  `crew` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'crew name',
  `club` varchar(60) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'club name',
  `class` varchar(40) COLLATE latin1_general_ci NOT NULL DEFAULT '' COMMENT 'boat class name',
  `classcode` varchar(6) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'class acronym',
  `sailnum` varchar(20) COLLATE latin1_general_ci NOT NULL DEFAULT '' COMMENT 'sail number used in race',
  `pn` int(5) NOT NULL DEFAULT '0' COMMENT 'portsmouth number used in this race',
  `clicktime` int(20) DEFAULT NULL COMMENT 'date/time of last timing click',
  `lap` int(3) NOT NULL DEFAULT '0' COMMENT 'completed laps',
  `finishlap` int(3) DEFAULT NULL COMMENT 'lap boat will finish on (this is only required for average lap racing - can I find a better way)',
  `etime` int(6) NOT NULL DEFAULT '0' COMMENT 'last elapsed time recorded (secs)',
  `ctime` int(6) NOT NULL DEFAULT '0' COMMENT 'last corrected time (secs)',
  `atime` int(6) NOT NULL DEFAULT '0' COMMENT 'aggregate time (pro-rata time for all boats doing the same number of laps in average lap race)',
  `ptime` int(6) NOT NULL DEFAULT '0' COMMENT 'predicted elapsed time for next lap (secs)',
  `code` varchar(10) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'result code for competitor (e.g. DNF)',
  `penalty` decimal(4,1) NOT NULL DEFAULT '0.0',
  `points` decimal(4,1) NOT NULL DEFAULT '0.0' COMMENT 'last points calculated',
  `pos` decimal(4,1) DEFAULT NULL COMMENT 'position allocated',
  `declaration` enum('D','R','X') COLLATE latin1_general_ci NOT NULL DEFAULT 'X' COMMENT 'declaration status [X - not declared, D declared, R retired]',
  `protest` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'protest flag',
  `note` varchar(200) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'notes field',
  `status` enum('R','F','X','D') COLLATE latin1_general_ci DEFAULT NULL COMMENT 'status field (R = racing, F = finished, X = excluded, D = deleted',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time of last update',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='current state for each competitor in races today';

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `rm_admin_uggroups`
--

CREATE TABLE `rm_admin_uggroups` (
  `GroupID` int(11) NOT NULL,
  `Label` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `rm_admin_ugmembers`
--

CREATE TABLE `rm_admin_ugmembers` (
  `UserName` varchar(300) NOT NULL,
  `GroupID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `rm_admin_ugrights`
--

CREATE TABLE `rm_admin_ugrights` (
  `TableName` varchar(300) NOT NULL,
  `GroupID` int(11) NOT NULL,
  `AccessMask` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `reset_token` varchar(100) NOT NULL,
  `reset_date` date NOT NULL,
  `reset_token1` varchar(50) DEFAULT NULL,
  `reset_date1` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `t_cfgfleet`
--

CREATE TABLE `t_cfgfleet` (
  `id` int(11) NOT NULL,
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
  `defaultlaps` int(2) NOT NULL DEFAULT '1' COMMENT 'default number of laps for this race',
  `defaultfleet` int(1) NOT NULL DEFAULT '0' COMMENT 'test for allocation to this race after checking all other races',
  `classinc` varchar(200) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'list of classes to be allocated to this race',
  `onlyinc` int(1) NOT NULL DEFAULT '0' COMMENT 'if set - only the listed included classes are allowed',
  `classexc` varchar(200) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'list of classes to be excluded from this race',
  `groupinc` varchar(200) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'groups to be eligible for this race',
  `min_py` int(4) DEFAULT '0' COMMENT 'minimum PY for this race',
  `max_py` int(4) DEFAULT '2000' COMMENT 'maximum PY for this race',
  `crew` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'crew constraint for classes in this race',
  `spintype` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'spinnaker type constraints for classes in this race',
  `hulltype` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'hull type constraint for classes in this race',
  `min_helmage` char(3) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'minimum helm of age for inclusion in this race',
  `max_helmage` char(3) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'maximum age of helm for this race',
  `min_skill` char(2) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'minimum skill level for this race',
  `max_skill` char(2) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'maximum skill level for this race',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'date of last update',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='defines racing fleets';

-- --------------------------------------------------------

--
-- Table structure for table `t_cfgrace`
--

CREATE TABLE `t_cfgrace` (
  `id` int(6) NOT NULL,
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
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='defines event configuration for racing events (e.g club series).';

-- --------------------------------------------------------

--
-- Table structure for table `t_cfgseries`
--

CREATE TABLE `t_cfgseries` (
  `id` int(11) NOT NULL COMMENT 'table primary key',
  `seriestype` varchar(30) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'series type code (e.g. short, long etc)',
  `discard` varchar(60) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'discard profile usig Sailwave coding (e.g. 0,0,1,1,2 means in the five race series one discard is applied after three races and 2 discards if all five races are complete)',
  `nodiscard` varchar(40) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL COMMENT 'if set last race sailed cannot be discarded',
  `multiplier` varchar(40) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL COMMENT 'if set the score for the last race in series is multiplied by the value ',
  `avgscheme` varchar(40) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'scheme for calculating average points',
  `dutypoints` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'duty points',
  `maxduty` int(2) NOT NULL DEFAULT '10' COMMENT 'max number of duties allowed to score',
  `notes` varchar(500) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL COMMENT 'Notes on series - e.g. a description',
  `active` int(1) NOT NULL DEFAULT '1' COMMENT 'flag set if this event type is currently used',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Defines each race series - includes parent series option (series of series)';

-- --------------------------------------------------------

--
-- Table structure for table `t_class`
--

CREATE TABLE `t_class` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'identifier',
  `acronym` char(6) COLLATE latin1_general_ci NOT NULL DEFAULT 'xxx' COMMENT '3 letter acronym for class (e.g. FB -> fireball)',
  `classname` varchar(40) COLLATE latin1_general_ci NOT NULL COMMENT 'class name',
  `variant` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'name for class recognised variant',
  `info` varchar(255) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'description',
  `popular` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'used to denote popular classes at the club - will be disoplayed before the other classes',
  `nat_py` int(5) UNSIGNED NOT NULL COMMENT 'national PN',
  `local_py` int(5) UNSIGNED NOT NULL COMMENT 'local PN',
  `rya_id` char(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'rya unique identifier',
  `category` enum('D','M','K','C','F','P','W') COLLATE latin1_general_ci NOT NULL DEFAULT 'D' COMMENT 'boat category [D=dinghy, M=multihull, K=keelboat, F=foiler, P = paddled boat]',
  `crew` enum('1','2','N','') COLLATE latin1_general_ci NOT NULL DEFAULT '1' COMMENT 'number of crew',
  `rig` enum('U','S','K','N','') COLLATE latin1_general_ci NOT NULL DEFAULT 'U' COMMENT 'rig type [U=una, S=sloop, K=two masts]',
  `spinnaker` enum('O','C','A','') COLLATE latin1_general_ci NOT NULL DEFAULT 'O' COMMENT 'spinnaker type [C=conventional, A=asymmetric, O=none]',
  `engine` enum('OB','IBF','IB2','IB3') COLLATE latin1_general_ci NOT NULL DEFAULT 'OB' COMMENT 'engine [OB=outboard, IB2=2 bladed fixed propellor, IB3=3 bladed fixed propellor, IBF=folding propellor]',
  `keel` enum('D','F','2K','3K','N') COLLATE latin1_general_ci NOT NULL DEFAULT 'D' COMMENT 'keel type [D=drop keel, F=single fixed, 2K=twin keel, 3K=triple keel]',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'record active flag',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='class configuration details - including RYA coding';

-- --------------------------------------------------------

--
-- Table structure for table `t_code_result`
--

CREATE TABLE `t_code_result` (
  `id` int(6) NOT NULL COMMENT 'code identifier',
  `code` char(6) COLLATE latin1_general_ci NOT NULL COMMENT 'result code (e.g. DNF)',
  `short` varchar(60) COLLATE latin1_general_ci NOT NULL COMMENT 'short description',
  `info` varchar(500) COLLATE latin1_general_ci NOT NULL COMMENT 'description',
  `scoringtype` enum('penalty','race','series','manual') COLLATE latin1_general_ci NOT NULL COMMENT 'code to determine when scoring can be applied',
  `scoring` varchar(20) COLLATE latin1_general_ci NOT NULL COMMENT 'scoring equation (using N= num in race, S=num in series, )',
  `timing` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'true if timing should continue after code is set',
  `startcode` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Use on start page',
  `timercode` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 if used when timing',
  `resultcode` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'used on results page',
  `nonexclude` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'true if result cannot be excluded in series',
  `rank` int(2) NOT NULL DEFAULT '1' COMMENT 'order for display',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 if to be used',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updby` varchar(40) COLLATE latin1_general_ci NOT NULL,
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='list of result codes and actions to take';

-- --------------------------------------------------------

--
-- Table structure for table `t_code_system`
--

CREATE TABLE `t_code_system` (
  `id` int(11) NOT NULL COMMENT 'table primary key',
  `groupname` varchar(40) COLLATE latin1_general_ci NOT NULL COMMENT 'code group',
  `code` varchar(40) COLLATE latin1_general_ci NOT NULL COMMENT 'code value',
  `label` varchar(200) COLLATE latin1_general_ci NOT NULL COMMENT 'code label (for use in interface)',
  `rank` int(3) NOT NULL DEFAULT '0' COMMENT 'order in list',
  `defaultval` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'default value (if 1)',
  `deletable` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'if set code can be deleted',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'date of last update',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'updated by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='codes used in the racemanager application';

-- --------------------------------------------------------

--
-- Table structure for table `t_code_type`
--

CREATE TABLE `t_code_type` (
  `id` int(11) NOT NULL COMMENT 'primary key for table',
  `groupname` varchar(40) COLLATE latin1_general_ci NOT NULL COMMENT 'name for code which is used to identify the codes belonging to this type',
  `label` varchar(40) COLLATE latin1_general_ci NOT NULL COMMENT 'label for the code type',
  `info` varchar(500) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'description for the code type',
  `rank` int(3) NOT NULL DEFAULT '1' COMMENT 'display order',
  `type` enum('system','club') COLLATE latin1_general_ci NOT NULL,
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_competitor`
--

CREATE TABLE `t_competitor` (
  `id` int(10) NOT NULL COMMENT 'identifier',
  `classid` int(10) NOT NULL COMMENT 'class id in rtblclass',
  `boatnum` varchar(20) COLLATE latin1_general_ci NOT NULL COMMENT 'boat number',
  `sailnum` varchar(20) COLLATE latin1_general_ci NOT NULL COMMENT 'sail number used',
  `boatname` varchar(60) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'name (or sponsor) of boat',
  `hullcolour` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'colour of hull',
  `helm` varchar(40) COLLATE latin1_general_ci NOT NULL COMMENT 'helm name',
  `helm_dob` date DEFAULT NULL COMMENT 'helm date of birth',
  `helm_email` varchar(100) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'helm email address',
  `telephone` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'contact telephone',
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
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'set to 1 if active competitor',
  `prizelist` varchar(200) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'comma seperated prize eligibility',
  `grouplist` varchar(100) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'comma separated list of member groups (e.g. member, visitor, junior)',
  `memberid` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'member id/code',
  `trackerid` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'identifier for position tracker',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='competitor details';

-- --------------------------------------------------------

--
-- Table structure for table `t_competitor_save`
--

CREATE TABLE `t_competitor_save` (
  `id` int(10) NOT NULL COMMENT 'identifier',
  `classid` int(10) NOT NULL COMMENT 'class id in rtblclass',
  `boatnum` varchar(20) COLLATE latin1_general_ci NOT NULL COMMENT 'boat number',
  `sailnum` varchar(20) COLLATE latin1_general_ci NOT NULL COMMENT 'sail number used',
  `boatname` varchar(60) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'name (or sponsor) of boat',
  `hullcolour` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'colour of hull',
  `helm` varchar(40) COLLATE latin1_general_ci NOT NULL COMMENT 'helm name',
  `helm_dob` date DEFAULT NULL COMMENT 'helm date of birth',
  `helm_email` varchar(100) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'helm email address',
  `telephone` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'contact telephone',
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
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'set to 1 if active competitor',
  `prizelist` varchar(200) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'comma seperated prize eligibility',
  `grouplist` varchar(100) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'comma separated list of member groups (e.g. member, visitor, junior)',
  `memberid` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'member id/code',
  `trackerid` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'identifier for position tracker',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='competitor details';

-- --------------------------------------------------------

--
-- Table structure for table `t_cruise`
--

CREATE TABLE `t_cruise` (
  `id` int(11) NOT NULL COMMENT 'table primary key',
  `cruise_type` varchar(20) CHARACTER SET utf8 NOT NULL,
  `cruise_date` date NOT NULL COMMENT 'date of cruise',
  `time_in` time NOT NULL COMMENT 'time registered for cruise',
  `time_out` time DEFAULT NULL COMMENT 'time declared ashore',
  `boatid` int(11) NOT NULL COMMENT 'competitor record id from t_competitor',
  `action` enum('register','update','declare') COLLATE latin1_general_ci NOT NULL COMMENT 'action for racemanager to take on entry',
  `chg-helm` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'has value if helm name is changed',
  `chg-crew` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'temporary crew change for races today',
  `chg-sailnum` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'temporary sailno change for today',
  `chg-numcrew` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'total number of people on boad',
  `chg-contact` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'contact details - typically mobile number',
  `memberid` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'memberid from t_competitor',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_entry`
--

CREATE TABLE `t_entry` (
  `id` int(11) NOT NULL COMMENT 'table primary key',
  `action` enum('enter','delete','update','retire','replace','declare') COLLATE latin1_general_ci NOT NULL COMMENT 'action for racemanager to take on entry',
  `protest` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 if declaring protest',
  `status` enum('N','L','F','E','A') COLLATE latin1_general_ci NOT NULL DEFAULT 'N' COMMENT 'status (N=not loaded, L=loaded, F=failed. E=not eligible, A=not allocated)',
  `eventid` int(11) NOT NULL COMMENT 'event record id from t_event',
  `competitorid` int(11) NOT NULL COMMENT 'competitor record id from t_competitor',
  `memberid` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'memberid from t_competitor',
  `chg-helm` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'has value if helm name is changed',
  `chg-crew` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'temporary crew change for races today',
  `chg-sailnum` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'temporary sailno change for today',
  `entryid` int(11) DEFAULT '0' COMMENT 'id of entry into race table (t_race)',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_event`
--

CREATE TABLE `t_event` (
  `id` int(11) NOT NULL COMMENT 'table primary key - note this is the same as the event id in clubManager programme function',
  `event_date` date NOT NULL COMMENT 'event date',
  `event_start` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'time of start (as text HH:MM)',
  `event_order` int(2) DEFAULT '1' COMMENT 'order of event on day - used if start times not defined',
  `event_name` varchar(60) COLLATE latin1_general_ci NOT NULL COMMENT 'event name (e.g. Summer Series, Kathleen Trophy, etc.)',
  `series_code` varchar(100) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'foreign key to series information in t_series',
  `event_type` varchar(20) CHARACTER SET utf8 NOT NULL COMMENT 'type of event (e.g. cruise, race, etc.)',
  `event_format` int(11) DEFAULT NULL COMMENT 'race format for event - references event configuration record in t_eventcfg',
  `event_entry` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'entry type (e.g ood or electronic signon etc.) - codes in t_code',
  `event_status` varchar(40) COLLATE latin1_general_ci DEFAULT 'scheduled' COMMENT 'current status of event - codes in t_code',
  `event_open` enum('club','open') COLLATE latin1_general_ci NOT NULL DEFAULT 'club' COMMENT 'indicates if it is an open event',
  `event_ood` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'name of ood (alternate overrides name in t_eventduty)',
  `tide_time` varchar(10) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'time of high water (as text HH:MM)',
  `tide_height` varchar(10) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'height of tide in metres',
  `start_scheme` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'field to change the default start scheme specified in event configuration',
  `start_interval` int(4) DEFAULT NULL COMMENT 'field to overwrite the default start interval defined in event configuration',
  `timerstart` bigint(13) DEFAULT NULL COMMENT 'timerstart (secs from 1/1/1970)',
  `ws_start` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'wind speed at start of race (from coded range)',
  `wd_start` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'wind direction at start (from coded range)',
  `ws_end` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'wind speed at end of race (from coded range)',
  `wd_end` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'wind direction at end (from coded range)',
  `event_notes` varchar(500) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'notes field will appear in programme display',
  `result_notes` varchar(500) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'notes relating to the results',
  `result_valid` int(1) DEFAULT '0' COMMENT 'true if results calculation found no errors',
  `result_publish` int(1) DEFAULT '0' COMMENT 'true if results have been published',
  `weblink` varchar(200) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'url to web page with more info',
  `webname` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'text to use on url link',
  `display_code` char(10) COLLATE latin1_general_ci DEFAULT 'W,R,S' COMMENT 'code to define whether displayed on website, racemanager, and/or sailor - comma separated list W,R,S',
  `active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'true if part of published programme',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` char(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='programmed events that racemanager will run';

-- --------------------------------------------------------

--
-- Table structure for table `t_eventduty`
--

CREATE TABLE `t_eventduty` (
  `id` int(11) NOT NULL,
  `eventid` int(11) NOT NULL,
  `dutycode` varchar(40) COLLATE latin1_general_ci NOT NULL,
  `person` varchar(40) COLLATE latin1_general_ci NOT NULL,
  `swapable` int(1) NOT NULL DEFAULT '1' COMMENT 'if set to 1 duty can be swapped',
  `phone` varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
  `notes` varchar(500) COLLATE latin1_general_ci DEFAULT NULL,
  `memberid` int(11) DEFAULT NULL,
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_finish`
--

CREATE TABLE `t_finish` (
  `id` int(11) NOT NULL COMMENT 'table primary key',
  `eventid` int(11) NOT NULL COMMENT 'event id',
  `entryid` int(11) NOT NULL COMMENT 'record id from race table ',
  `finish_1` int(3) DEFAULT '0' COMMENT 'position at finish 1',
  `finish_2` int(3) DEFAULT '0' COMMENT 'position at finish 2',
  `finish_3` int(3) DEFAULT '0' COMMENT 'position at finish 3',
  `finish_4` int(3) DEFAULT '0' COMMENT 'position at finish 4',
  `finish_5` int(3) DEFAULT '0' COMMENT 'position at finish 5',
  `finish_6` int(3) DEFAULT '0' COMMENT 'position at finish 6',
  `forder` int(3) NOT NULL DEFAULT '0' COMMENT 'sequential finish order',
  `place` int(3) DEFAULT '0' COMMENT 'place in race',
  `status` varchar(500) COLLATE latin1_general_ci DEFAULT NULL COMMENT '??????',
  `state` int(1) NOT NULL DEFAULT '0' COMMENT '??????',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='finish positions for multi finish line pursuit races';

-- --------------------------------------------------------

--
-- Table structure for table `t_help`
--

CREATE TABLE `t_help` (
  `id` int(11) NOT NULL COMMENT 'table primary key',
  `category` varchar(60) COLLATE latin1_general_ci NOT NULL COMMENT 'help category - comma separated',
  `question` varchar(500) COLLATE latin1_general_ci NOT NULL COMMENT 'question or help item heading',
  `answer` varchar(4000) COLLATE latin1_general_ci NOT NULL COMMENT 'answer text',
  `notes` varchar(4000) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'additional related information',
  `author` varchar(50) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'person creating help item',
  `pursuit` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if set should only be displayed if it is a pursuit race',
  `eventname` varchar(60) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'event name constraint - can be name substring',
  `format` int(11) DEFAULT NULL COMMENT 'event format constraint',
  `startdate` date DEFAULT NULL COMMENT 'start date constraint',
  `enddate` date DEFAULT NULL COMMENT 'end date constraint',
  `multirace` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if set should only be displayed if multi race day',
  `rank` int(3) NOT NULL DEFAULT '1' COMMENT 'order in which they should be displayed within category',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'if set to 1 can be viewed by user',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'date of last update',
  `updby` varchar(20) COLLATE latin1_general_ci NOT NULL COMMENT 'user responsible for last update',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='Holds configurable help information';

-- --------------------------------------------------------

--
-- Table structure for table `t_ini`
--

CREATE TABLE `t_ini` (
  `id` int(11) NOT NULL COMMENT 'table primary key',
  `category` varchar(40) COLLATE latin1_general_ci NOT NULL COMMENT 'configuration parameter category (club, interface, entry, results, admin]',
  `parameter` varchar(40) COLLATE latin1_general_ci NOT NULL COMMENT 'name of parameter',
  `label` varchar(40) COLLATE latin1_general_ci NOT NULL,
  `value` varchar(200) COLLATE latin1_general_ci NOT NULL COMMENT 'value of parameter',
  `notes` varchar(500) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'Information on what values can be used for this setting',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'date of last update',
  `updby` char(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='club configurable parameters used in racemanager';

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
  `status` tinyint(1) DEFAULT '1' COMMENT 'field used to indicate whether this lap to be included in results (1 = include)'')',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_link`
--

CREATE TABLE `t_link` (
  `id` int(11) NOT NULL COMMENT 'table primary key',
  `label` varchar(60) COLLATE latin1_general_ci NOT NULL COMMENT 'label for link label to appear in interface',
  `url` varchar(200) COLLATE latin1_general_ci NOT NULL COMMENT 'URL for link (e.g. http://....)',
  `tip` varchar(500) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'tooltip text for link',
  `category` varchar(20) COLLATE latin1_general_ci NOT NULL COMMENT 'link category (allows grouping of links for different pages in system) - only one category permitted so it may be necessary to duplicate links if they are to appear in more than one place.',
  `rank` tinyint(2) NOT NULL DEFAULT '0' COMMENT 'order in which to display links (within category)',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='Club specific links to additional information';

-- --------------------------------------------------------

--
-- Table structure for table `t_message`
--

CREATE TABLE `t_message` (
  `id` int(11) NOT NULL,
  `eventid` int(11) DEFAULT NULL COMMENT 'id for event associated with message',
  `name` varchar(60) COLLATE latin1_general_ci NOT NULL COMMENT 'person / process creating message',
  `subject` varchar(60) COLLATE latin1_general_ci NOT NULL COMMENT 'message subject',
  `message` text COLLATE latin1_general_ci NOT NULL COMMENT 'message content',
  `email` varchar(100) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'email address for response',
  `response` text COLLATE latin1_general_ci COMMENT 'action taken',
  `status` enum('received','closed') COLLATE latin1_general_ci NOT NULL DEFAULT 'received' COMMENT 'current status of message',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updby` varchar(20) COLLATE latin1_general_ci NOT NULL COMMENT 'user responsible for update',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_protest`
--

CREATE TABLE `t_protest` (
  `id` int(11) NOT NULL,
  `protest_type` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `event_name` varchar(60) COLLATE latin1_general_ci NOT NULL,
  `event_date` date NOT NULL,
  `protestor_name` varchar(60) COLLATE latin1_general_ci NOT NULL,
  `protestor_class` varchar(40) COLLATE latin1_general_ci NOT NULL,
  `protestor_sailnum` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `protestee_name` varchar(60) COLLATE latin1_general_ci DEFAULT NULL,
  `protestee_class` varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  `protestee_sailnum` varchar(20) COLLATE latin1_general_ci DEFAULT NULL,
  `description` text COLLATE latin1_general_ci NOT NULL,
  `facts_found` text COLLATE latin1_general_ci,
  `decision` text COLLATE latin1_general_ci,
  `submitted` datetime NOT NULL,
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL,
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
  `trackerid` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'identifier for position tracker',
  `helm` varchar(40) COLLATE latin1_general_ci NOT NULL DEFAULT '' COMMENT 'helm name',
  `crew` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'crew name',
  `club` varchar(60) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'club name',
  `class` varchar(40) COLLATE latin1_general_ci NOT NULL DEFAULT '' COMMENT 'boat class name',
  `classcode` varchar(6) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'class acronym',
  `sailnum` varchar(20) COLLATE latin1_general_ci NOT NULL DEFAULT '' COMMENT 'sail number used in race',
  `pn` int(5) NOT NULL DEFAULT '0' COMMENT 'portsmouth number used in this race',
  `clicktime` int(20) DEFAULT NULL COMMENT 'date/time of last timing click',
  `lap` int(3) NOT NULL DEFAULT '0' COMMENT 'completed laps',
  `finishlap` int(3) NOT NULL DEFAULT '1' COMMENT 'lap boat will finish on (this is only required for average lap racing - can I find a better way)',
  `etime` int(6) NOT NULL DEFAULT '0' COMMENT 'last elapsed time recorded (secs)',
  `ctime` int(6) NOT NULL DEFAULT '0' COMMENT 'last corrected time (secs)',
  `atime` int(6) NOT NULL DEFAULT '0' COMMENT 'aggregate time (pro-rata time for all boats doing the same number of laps in average lap race)',
  `ptime` int(6) NOT NULL DEFAULT '0' COMMENT 'predicted elapsed time for next lap (secs)',
  `code` varchar(10) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'result code for competitor (e.g. DNF)',
  `penalty` decimal(4,1) NOT NULL DEFAULT '0.0',
  `points` decimal(4,1) NOT NULL DEFAULT '0.0' COMMENT 'last points calculated',
  `pos` decimal(4,1) DEFAULT NULL COMMENT 'position allocated',
  `declaration` enum('D','R','X') COLLATE latin1_general_ci NOT NULL DEFAULT 'X' COMMENT 'declaration status [X - not declared, D declared, R retired]',
  `protest` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'protest flag',
  `note` varchar(200) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'notes field',
  `status` enum('R','F','X','D') COLLATE latin1_general_ci DEFAULT NULL COMMENT 'status field (R = racing, F = finished, X = excluded, D = deleted',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='current state for each competitor in races today';

-- --------------------------------------------------------

--
-- Table structure for table `t_racestate`
--

CREATE TABLE `t_racestate` (
  `id` int(11) NOT NULL COMMENT 'table primary key',
  `eventid` int(11) NOT NULL,
  `racename` varchar(40) COLLATE latin1_general_ci NOT NULL,
  `fleet` smallint(2) NOT NULL COMMENT 'no. of fleet in event',
  `start` int(2) NOT NULL COMMENT 'start number',
  `racetype` enum('handicap','average','level','pursuit','flight') COLLATE latin1_general_ci NOT NULL COMMENT 'type of race (pursuit, fleet, handicap, etc.) - should this be integer code',
  `startdelay` int(6) NOT NULL COMMENT 'delay of start from timer initialisation (secs)',
  `starttime` time NOT NULL DEFAULT '00:00:00' COMMENT 'start time of this race',
  `maxlap` int(3) NOT NULL DEFAULT '0' COMMENT 'maximum no. of laps for this race (equivalent to finish lap for all except average lap racing)',
  `currentlap` int(2) NOT NULL DEFAULT '0' COMMENT 'Current lap for leading boat on water',
  `entries` int(3) NOT NULL DEFAULT '0' COMMENT 'No. of entries',
  `status` enum('notstarted','inprogress','finishing','allfinished') COLLATE latin1_general_ci DEFAULT NULL COMMENT 'current race status',
  `prevstatus` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'previous race status',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_result`
--

CREATE TABLE `t_result` (
  `id` int(11) NOT NULL COMMENT 'primary key for this table',
  `eventid` int(11) NOT NULL COMMENT 'event record id (from t_event)',
  `fleet` int(2) NOT NULL COMMENT 'fleet number in event',
  `race_type` enum('handicap','average','level','pursuit','none') COLLATE latin1_general_ci NOT NULL COMMENT 'race type as defined in t_racecfg (stored here to indicate scoring mechanism used when race was run)',
  `competitorid` int(11) NOT NULL COMMENT 'competitor record id (from t_competitor)',
  `class` varchar(40) COLLATE latin1_general_ci NOT NULL COMMENT 'class of boat (text)',
  `sailnum` varchar(20) COLLATE latin1_general_ci NOT NULL COMMENT 'sail number used',
  `pn` int(4) NOT NULL COMMENT 'PN used for this race',
  `apn` int(4) DEFAULT NULL COMMENT 'achieved PN (personal handicap)',
  `helm` varchar(40) COLLATE latin1_general_ci NOT NULL COMMENT 'helm name for this race',
  `crew` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'crew name for this race',
  `club` varchar(60) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'club name',
  `lap` int(2) NOT NULL,
  `etime` int(6) NOT NULL COMMENT 'elapsed time at finish (secs)',
  `ctime` int(6) NOT NULL DEFAULT '0' COMMENT 'corrected time at finish',
  `atime` int(6) NOT NULL DEFAULT '0' COMMENT 'aggregate time (pro-rata time for all boats doing the same number of laps in average lap race)''',
  `code` char(6) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'finish code (e.g. DNC, DNF)',
  `penalty` decimal(4,1) NOT NULL DEFAULT '0.0',
  `points` decimal(4,1) NOT NULL COMMENT 'points awarded for race',
  `declaration` char(3) COLLATE latin1_general_ci NOT NULL COMMENT 'declared - same codes as in t_race',
  `note` varchar(200) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'notes',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last updated by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_resultfile`
--

CREATE TABLE `t_resultfile` (
  `id` int(11) NOT NULL,
  `eventid` int(11) NOT NULL COMMENT 'id for event which results file is related to',
  `eventyear` year(4) NOT NULL COMMENT 'year in which event results files will be allocated',
  `folder` enum('races','series','class','special') COLLATE latin1_general_ci NOT NULL COMMENT 'type of result file (reflects structure of results directory)',
  `format` enum('htm','csv','pdf','jpg') COLLATE latin1_general_ci NOT NULL COMMENT 'data format of file',
  `filename` varchar(80) CHARACTER SET utf8 NOT NULL COMMENT 'name of file',
  `label` varchar(60) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'label for presenting the results file to users',
  `notes` varchar(500) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'supporting notes describing the file',
  `status` enum('final','provisional','embargoed','') COLLATE latin1_general_ci NOT NULL DEFAULT 'final' COMMENT 'status for result file',
  `rank` int(2) NOT NULL DEFAULT '1' COMMENT 'display order',
  `upload` datetime DEFAULT NULL COMMENT 'datetime when file was uploaded to website',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_rotamember`
--

CREATE TABLE `t_rotamember` (
  `id` int(11) NOT NULL,
  `memberid` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'id for this member in membership system',
  `firstname` varchar(20) COLLATE latin1_general_ci NOT NULL COMMENT 'first name',
  `familyname` varchar(40) COLLATE latin1_general_ci NOT NULL COMMENT 'famil nmae',
  `rota` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'rota name',
  `phone` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'phone number (mobile preferred)',
  `email` varchar(60) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'email',
  `note` varchar(500) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'rota notes (e.g. not on wednesdays)',
  `partner` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'name of duty partner',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'set to 1 if still active',
  `updby` varchar(20) COLLATE latin1_general_ci NOT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'date of last update'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='Holds basic information on who is on each rota';

-- --------------------------------------------------------

--
-- Table structure for table `t_series`
--

CREATE TABLE `t_series` (
  `id` int(11) NOT NULL COMMENT 'table primary key',
  `seriescode` varchar(40) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'e.g.  AUTUMN',
  `seriesname` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'name of series (e.g. Autumn Series)',
  `seriestype` int(11) NOT NULL COMMENT 'series type from t_cfgseries',
  `startdate` varchar(6) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL COMMENT 'earliest date on which series race can be sailed (e.g 1-jan)',
  `enddate` varchar(6) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL COMMENT 'latest date on which series race can be sailed (e.g 30-sep)',
  `race_format` int(11) DEFAULT NULL COMMENT 'default race format (id in t_cfgrace)',
  `merge` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL COMMENT 'lists of classes to be merged in series result -  in ,,|,, format',
  `classresults` varchar(300) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL COMMENT 'holds comma separated list of classes for which class specific results are required',
  `opt_style` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT 'classic' COMMENT 'report style to be used',
  `opt_turnout` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if true include turnout details',
  `opt_scorecode` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'if true include definition of scoring codes',
  `opt_clubnames` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if true include club name for each competitor',
  `opt_pagebreak` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if true insert a page break after each fleet',
  `opt_racelabel` enum('number','dates') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT 'dates' COMMENT 'defines what is used to label each race in the series (number or dates)',
  `opt_upload` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'if true upload series result to website',
  `notes` varchar(500) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL COMMENT 'Notes on series - e.g. a description',
  `active` int(1) NOT NULL DEFAULT '1' COMMENT 'if true - can be used for events',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Defines each race series - includes parent series option (series of series)';

-- --------------------------------------------------------

--
-- Table structure for table `t_tide`
--

CREATE TABLE `t_tide` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL COMMENT 'date for tide data',
  `hw1_time` char(5) CHARACTER SET utf8 DEFAULT NULL COMMENT 'first high water time (hh:mm)',
  `hw1_height` char(4) CHARACTER SET utf8 NOT NULL COMMENT 'first high water height (e.g 3.9)',
  `hw2_time` char(5) CHARACTER SET utf8 DEFAULT NULL COMMENT 'second high water time (hh:mm)',
  `hw2_height` char(4) CHARACTER SET utf8 DEFAULT NULL COMMENT 'second high water height',
  `time_reference` char(6) CHARACTER SET utf8 NOT NULL DEFAULT 'local',
  `height_units` char(3) CHARACTER SET utf8 NOT NULL DEFAULT 'm',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='holds tide data';

-- --------------------------------------------------------

--
-- Table structure for table `z_entry`
--

CREATE TABLE `z_entry` (
  `id` int(11) NOT NULL COMMENT 'table primary key',
  `action` enum('enter','delete','update','retire','replace','declare') COLLATE latin1_general_ci NOT NULL COMMENT 'action for racemanager to take on entry',
  `protest` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 if declaring protest',
  `status` enum('N','L','F','E','A') COLLATE latin1_general_ci NOT NULL DEFAULT 'N' COMMENT 'status (N=not loaded, L=loaded, F=failed. E=not eligible, A=not allocated)',
  `eventid` int(11) NOT NULL COMMENT 'event record id from t_event',
  `competitorid` int(11) NOT NULL COMMENT 'competitor record id from t_competitor',
  `memberid` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'memberid from t_competitor',
  `chg-helm` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'has value if helm name is changed',
  `chg-crew` varchar(40) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'temporary crew change for races today',
  `chg-sailnum` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'temporary sailno change for today',
  `entryid` int(11) DEFAULT '0' COMMENT 'id of entry into race table (t_race)',
  `upddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update date',
  `updby` varchar(20) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'last update by',
  `createdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'timestamp when record created'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Indexes for dumped tables
--

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
-- Indexes for table `t_competitor_save`
--
ALTER TABLE `t_competitor_save`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lastRaced` (`last_entry`),
  ADD KEY `visibility` (`active`);

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
-- Indexes for table `t_finish`
--
ALTER TABLE `t_finish`
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
-- AUTO_INCREMENT for table `t_competitor_save`
--
ALTER TABLE `t_competitor_save`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'identifier';

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
-- AUTO_INCREMENT for table `t_finish`
--
ALTER TABLE `t_finish`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'table primary key';

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
