<?php

/**
 * name_match_webcollect.php
 *
 * script to match names in t_competitor with member names in webcollect.
 * The webcollect data needs to be extracted with the test api and converted to json - named in a file data/members/dtm_members.json
 *
 * The utility script has a several processing options which are selected by a number code as one of the script arguments
 *
 * name_match_webcollect.php?option=1&file=member_list.json
 *
 * arguments
 *   option  - no. of processing option to be used
 *   file    - file name of json members file in data/members directory.  The file should have as a minimum
 *        "memberid" : { "name":"firstname lastname" . . . + any other data elements required for the processing
 *   dryrun  - print resuklts rather than update database - on/off
 * 
 */

$loc  = "..";
$page = "Name Match Webcollect";
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
$styletheme = "flatly_";
$stylesheet = "./style/rm_utils.css";

session_id("sess-rmutil-".str_replace("_", "", strtolower($page)));
session_start();

// classes
require_once("$loc/common/lib/util_lib.php");
require_once("$loc/common/classes/db.php");
require_once("{$loc}/common/classes/template_class.php");

$cfg = u_set_config("../config/common.ini", array(), false);

// make PDO database connection for racemanager database
$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host']);

// get dutyman member data from json file
$string = file_get_contents("../data/members/dtm_members.json");
if ($string) {
    $arr = json_decode($string, true);
}

foreach ($arr as $k=>$row)
{
    $name_arr = explode(" ",$row['name']);
    $arr[$k]['first'] = strtolower($name_arr[0]);
    $arr[$k]['last']  = strtolower($name_arr[1]);
}


//$list = "";
//$i = 0;
//foreach ($members as $k=>$row)
//{
//    $i++;
//    $list.= "$i - $k: {$row['name']} {$row['dob']} {$row['year']} {$row['first']} {$row['last']}<br>";
//}
//echo $list;
//exit();
//
//$i = 0;
//$arr = array();
//foreach($members as $row)
//{
//    echo "<pre>".print_r($row,true)."</pre>";
//    exit();
//
//    $i++;
//    $arr[$i] = array(
//        "fullname"    => $row['First Name'].' '.$row['Last Name'],
//        "lastname"    => $row['Last Name'],
//        "firstname"   => $row['First Name'],
//        "email"       => $row['Email Address'],
//        "mobile"      => $row['Phone 2'],
//        "phone"       => $row['Phone'],
//        "address"     => $row['Address 1'],
//        "memberid"    => $row['Member UID']
//    );
//}

if (count($arr) > 0)
{
    // processing option #1 - find year of birth and add it to competitor record
    if ($_REQUEST['option'] == "1")
    {
        $status = processing_option_1($arr);
    }
    else
    {
        echo "<pre>Requested processing option [ {$_REQUEST['option']} ] - NOT FOUND</pre>";
    }
    echo "<pre>".count($arr)." - members in webcollect.</pre>";
}



function processing_option_1($members)
{
    global $db_o;

    $namex = array(
        "david"    => "david,dave",
        "robert"   => "robert,rob,bob",
        "russell"  => "russell,russ",
        "richard"  => "richard,dick",
        "oliver"   => "oliver,olly,oli",
        "james"    => "james,jamie,jim",
        "john"     => "john,johnny,jon",
        "jonathon" => "jonathon,jon,jonty",
        "matthew"  => "matthew,matt",
        "thomas"   => "thomas,tom",
        "michael"  => "michael,mike",
        "anthony"  => "anthony,tony",
        "andrew"   => "andrew,andy",
        "william"  => "william,will"
    );

    $num_matched = 0;
    foreach ($members as $k=>$row)
    {
        $firstname = addslashes($row['first']);
        $lastname = addslashes($row['last']);
        // build where constraint

        if (array_key_exists(strtolower($firstname), $namex))
        {
            $qarr = explode(',',$namex[$row['first']]);
            $where = "";
            foreach ($qarr as $r)
            {
                $where.= " `helm` = '$r $lastname' OR ";
            }
            $constraint = rtrim($where, "OR ");
        }
        else
        {
            $constraint = "`helm` = '$firstname $lastname'";
        }
        // search competitor database for name
        $query = "SELECT id, helm  FROM t_competitor WHERE $constraint";
        echo "<pre>$query</pre>";
        $rst = $db_o->run($query, array())->fetchall();
        
        if ($lastname == 'elkington')
        {
            echo "<pre>HITS - ".count($rst)."</pre>";
            echo "<pre>".print_r($rst,true)."</pre>";
        }

        if (count($rst) <= 0)
        {
            echo "<pre>{$row['name']} not found</pre>";
        }
        else
        {
            $num_matched++;
            echo "<pre>{$rst[0]['helm']} - ".count($rst)." - {$row['year']}</pre>";

            // if we have a year of birth add it to the competitor records for this helm
            if (!empty($row['year']) and is_numeric($row['year']))
            {
                foreach ($rst as $record)
                {
                    if ($lastname == 'elkington') { echo "<pre>processing $lastname - {$record['id']}</pre>"; }
                    $query = "UPDATE t_competitor SET helm_yob = {$row['year']} WHERE id = {$record['id']}";
                    echo "<pre>{$row['name']} - $query</pre>";
                    $upd = $db_o->run($query, array());
                }
            }
            else
            {
                echo "<pre>{$row['name']} - dutyman data does not contain DOB</pre>";
            }
        }
    }
    echo "<pre>total matches - $num_matched</pre>";
}





 