<?php


/* rm_event.php

    Main script for rm_evet application


*/

// start session
session_id('sess-rmweb');
session_start();
// error_reporting(E_ERROR);  // turn off warnings for live operation
require_once("include/rm_event_lib.php");
require_once("classes/pages.php");
require_once("classes/template.php");
require_once("classes/db.php");

// initialise application
$cfg = parse_ini_file("config.ini", true);
$_SESSION['logfile'] = str_replace("<date>", date("Y"), $cfg['rm_event']['logfile']);

// fixme - need to sort out configuration
// basic cfg read from ini file
// database might contain some values that overwrite the ini file (e.g default events contact)
// do any values need to go into session


// arguments
empty($_REQUEST['page']) ? $page = "list" : $page = $_REQUEST['page'];
empty($_REQUEST['year']) ? $year = date("Y") : $year = $_REQUEST['year'];
empty($_REQUEST['eid']) ? $eid = 0 : $eid = $_REQUEST['eid'];

$if_o = new PAGES($cfg);
$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass']);

if ($page == "list")                        // events list page
{
    $if_o->pg_list($db_o, $year);
}
else                                        // specific event page
{
    $if_o->pg_event($db_o, $page, $eid);
}




// testing PDO
/*
require_once("classes/db.php");

$db = new DB("pegasus", "rmuser", "pegasus");

$eid = 12;
$category = "protest";
$title = "Merlin 3797 won!";
$arr = array("eid"=>12, "category"=>"protest", "title"=>"Merlin 3797! won", "updby"=>"mark; DELETE FROM e_notice");

$db->run("INSERT INTO e_notice (eid, category, title, updby) VALUES (:eid,:category,:title,:updby)", $arr);
$id = $db->pdo->lastInsertId();

echo "inserted record: $id <br>";

$data = $db->run("SELECT * FROM e_notice", array() )->fetchall();
echo "<br><pre>".print_r($data, true)."</pre>";

//// getting the number of rows in the table
//$count = pdo($pdo, "SELECT count(*) FROM users")->fetchColumn();
//
//// the user data based on email
//$user = pdo($pdo, "SELECT * FROM users WHERE email=?", [$email])->fetch();
//
//// getting many rows from the table
//$data = pdo($pdo, "SELECT * FROM users WHERE salary > ?", [$salary])->fetchAll();
//
//// getting the number of affected rows from DELETE/UPDATE/INSERT
//$deleted = pdo($pdo, "DELETE FROM users WHERE id=?", [$id])->rowCount();
//
//// insert
//pdo($pdo, "INSERT INTO users VALUES (null, ?,?,?)", [$name, $email, $password]);
//
//// named placeholders are also welcome though I find them a bit too verbose
//pdo($pdo, "UPDATE users SET name=:name WHERE id=:id", ['id'=>$id, 'name'=>$name]);
//
//// using a sophisticated fetch mode, indexing the returned array by id
//$indexed = pdo($pdo, "SELECT id, name FROM users")->fetchAll(PDO::FETCH_KEY_PAIR);

*/