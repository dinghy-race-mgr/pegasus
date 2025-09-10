<?php

// imports
require_once("../common/classes/db.php");
require_once("../common/classes/template_class.php");

// establish template object
$tmpl_o = new TEMPLATE(array( "./layouts_tm.php"));

// establish database connection object  $dbname, $username = NULL, $password = NULL, $host = 'localhost', $port = 3306, $options = [])
$db_o = new DB("pegasus", "robuser", "wibble", "localhost", "3306");

// run class query
if ($db_o)
{
    $sql = "SELECT classname, nat_py, CONCAT (category, '/', crew, '/', spinnaker) AS spec FROM t_class WHERE classname LIKE ?";
    $args = array("b%");
    $classes = $db_o->run($sql, $args)->fetchall();

}
else
{
    exit("database not connected");
}

// pass returned data rows to table template using params


$fields = array(
    "page-title" => "robpage",
    "page-navbar" => "",   // fixme add simple navbar
    "page-main" => "",
    "page-footer" => "",  // fixme add simple footer
    "page-modals" => "",
    "page-js" => ""
);

$params = array("data"=>$classes);

// $fields['page-main'] =$tmpl_o->get_template("page", $fields, $params);
$text = "the time is ".date("H:i:s");
$fields['page-main'] = $tmpl_o->get_template("class_table", array("time"=>$text), $params);

echo $tmpl_o->get_template("page", $fields, $params);