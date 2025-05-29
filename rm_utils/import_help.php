<?php
/*
 *  Simple script to load help text held in xml format
 */

$loc  = "..";
$page = "import_help";     //
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
$styletheme = "flatly_";
$stylesheet = "./style/rm_utils.css";

require_once ("../common/lib/util_lib.php");
require_once ("../common/classes/db_class.php");

session_id("sess-rmutil-".str_replace("_", "", strtolower($page)));
session_start();

// initialise session
$init_status = u_initialisation("$loc/config/rm_utils_cfg.php", $loc, $scriptname);

if ($init_status)
{
    // set timezone
    if (array_key_exists("timezone", $_SESSION)) { date_default_timezone_set($_SESSION['timezone']); }

    // start log
    error_log(date('d-M H:i:s')." -- rm_util IMPORT HELP TEXT ------- [session: ".session_id()."]".PHP_EOL, 3, $_SESSION['syslog']);

    // set initialisation flag
    $_SESSION['util_app_init'] = true;
}
else
{
    u_exitnicely($scriptname, 0, "one or more problems with script initialisation",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

// input help data file
$xmlfile = "../config/help_text.xml";                   // file holding help information

// initialise database access
$db_o = new DB();

// truncate t_help table
$rst = $db_o->db_truncate(array("t_help"));

// decode xml import file
$xmldata = simplexml_load_file($xmlfile) or die("Failed to load file");

// load each help topic
$fields = "INSERT INTO t_help(`category`, `question`, `answer`, `notes`, `author`, `listorder`, `active`, `pursuit`, `eventname`, `format`,  `multirace`)";
$i = 0;
foreach($xmldata->children() as $topic) {

    if (!empty($topic->answer))             // don't load topics without answers
    {
        $i++;
        //$topic->question = addslashes($topic->question);
        if (empty($topic->pursuit)) { $topic->pursuit = 0; }
        if (empty($topic->eventname)) { $topic->eventname = ""; }
        if (empty($topic->format)) { $topic->format = ""; }
        if (empty($topic->multirace)) { $topic->_multirace = 0; }
        // FIXME - this script is not handling the startdate / enddate options
        $values = "VALUES ('$topic->category', '$topic->question', '$topic->answer', '$topic->notes', '$topic->author', '$topic->listorder', '$topic->active',
                            '$topic->pursuit', '$topic->eventname', '$topic->format', '$topic->multirace')";
    }

    $sql = $fields.$values;
    $insert = $db_o->db_query($sql);
    $error = "";
    if ($insert === false)
    {
        $error = "insert failed";
    }

    echo "$i: {$topic->question} - $error [ {$topic->pursuit} {$topic->multirace} ] [ {$topic->startdate} {$topic->enddate} ]<br>";
}

// confirm number of topics loaded
echo "$i topics inserted into t_help<br>";
exit("import done");
