<?php
/*
 *  Simple script to load help text held in xml format
 */
require_once ("../common/lib/util_lib.php");
require_once ("../common/classes/db_class.php");

session_start();
unset($_SESSION);

// data files
$common_ini_file = "../config/common.ini";              // database access information
$xmlfile = "../config/help_text.xml";                   // file holding help information

// initialise database access
$_SESSION = parse_ini_file($common_ini_file, false);
$_SESSION['sql_debug'] = false;
$db_o = new DB();

// truncate t_help table
$rst = $db_o->db_truncate(array("t_help"));

// decode xml import file
$xmldata = simplexml_load_file($xmlfile) or die("Failed to load file");

// load each help topic
$fields = "INSERT INTO t_help(`category`, `question`, `answer`, `notes`, `author`, `rank`, `active`)";
$i = 0;
foreach($xmldata->children() as $topic) {

    if (!empty($topic->answer))             // don't load topics without answers
    {
        $i++;
        $values = "VALUES ('$topic->category', '$topic->question', '$topic->answer', '$topic->notes', '$topic->author', '$topic->rank', '$topic->active')";
    }

    $sql = $fields.$values;
    $insert = $db_o->db_query($sql);
    $error = "";
    if ($insert === false)
    {
        $error = "insert failed";
    }

    echo "$i: {$topic->question} - $error <br>";
}

// confirm number of topics loaded
echo "$i topics inserted into t_help<br>";
exit("done");
