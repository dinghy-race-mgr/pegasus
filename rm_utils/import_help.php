<?php
require_once ("../common/lib/util_lib.php");
require_once ("../common/classes/db_class.php");

session_start();
unset($_SESSION);
$_SESSION = parse_ini_file("../config/common.ini", false);
$common_ini_file = "../config/common.ini";
$_SESSION['sql_debug'] = false;
echo "<pre>".print_r($_SESSION,true)."</pre>";


// truncate t_help
$db_o = new DB();
$rst = $db_o->db_truncate(array("t_help"));

// truncate table
// xml import file
$file = "../config/help_text.xml";
$xmldata = simplexml_load_file($file) or die("Failed to load file");
$i = 0;
$values = "";
foreach($xmldata->children() as $topic) {

    if (!empty($topic->answer))             // don't load topics without answers
    {
        echo $topic->question."</br>";
        $i++;
        $values.= "('$topic->category', '$topic->question', '$topic->answer', '$topic->notes', '$topic->author', '$topic->rank', '$topic->active'),";
    }
}

$first_part = "INSERT INTO t_help(`category`, `question`, `answer`, `notes`, `author`, `rank`, `active`) VALUES ";
$sql = $first_part.rtrim($values, ',').";";
echo "<pre>$sql</pre>";
$insert = $db_o->db_query($sql);
echo "$i topics inserted into t_help<br>";
exit("done");
