<?php

/**
 * rota_synch_webcollect.php
 *
 * script to upload rota member information from webcollect and dutyman to raceManager
 * 
 * The rota member information inserted in t_rotamember is:
 *    firstname      first name of member
 *    familyname     surname of member
 *    rota           rota type code (e.g. aood)
 *    phone          mobile phone number (home phone number if mobile not provided)
 *    email          email address from webcollect
 *    note           notes on duty restrictions and availability
 *    memberid       webcollect id for member (6 digit code)
 *    dtm_login      dutyman login token
 *
 *    Note that one member may have more than one rotamember record
 */

$loc  = "..";
$page = "Rota Synch";
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
$styletheme = "flatly_";
$stylesheet = "./style/rm_utils.css";

session_id("sess-rmutil-".str_replace("_", "", strtolower($page)));
session_start();

require_once("$loc/common/lib/util_lib.php");

$init_status = u_initialisation("$loc/config/rm_utils_cfg.php", $loc, $scriptname);

if ($init_status)
{
    // set timezone
    if (array_key_exists("timezone", $_SESSION)) { date_default_timezone_set($_SESSION['timezone']); }

    // start log
    error_log(date('d-M H:i:s')." -- rm_util IMPORT WEBCOLLECT ROTA LISTS ------- [session: ".session_id()."]".PHP_EOL, 3, $_SESSION['syslog']);

    // set initialisation flag
    $_SESSION['util_app_init'] = true;
}
else
{
    u_exitnicely($scriptname, 0, "one or more problems with script initialisation",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}


define('BASE', dirname(__FILE__) . '/');
require BASE . '../common/oss/webcollect/lib/WebCollectRestapiClient.php';
//require BASE . 'lib/WebCollectRestapiClient.php';

// classes
require_once("../common/classes/db_class.php");
require_once("../common/classes/template_class.php");

// set templates
$tmpl_o = new TEMPLATE(array("$loc/common/templates/general_tm.php","./templates/layouts_tm.php", "./templates/webcollect_tm.php"));

// arguments
if (empty($_REQUEST['pagestate'])) { $_REQUEST['pagestate'] = "init"; }
if (empty($_REQUEST['contacts']))  { $_REQUEST['contacts']  = $_SESSION['webcollect']['include_contacts']; }
if (empty($_REQUEST['notes']))     { $_REQUEST['notes']     = $_SESSION['webcollect']['include_notes']; }
if (empty($_REQUEST['dryrun']))    { $_REQUEST['dryrun']    = "false"; }

$pagefields = array(
    "loc"           => $loc,
    "theme"         => $styletheme,
    "stylesheet"    => $stylesheet,
    "title"         => "Rota Synchronisation",
    "header-left"   => "raceManager",
    "header-right"  => "synchronise rota info from webcollect ...",
    "body"          => "",
    "confirm"       => "Synchronise",
    "footer-left"   => "",
    "footer-center" => "",
    "footer-right"  => "",
);

/* ------------ confirm run script page ---------------------------------------------*/

if ($_REQUEST['pagestate'] == "init")
{
    // setup debug
    array_key_exists("debug", $_REQUEST) ? $params['debug'] = $_REQUEST['debug'] : $params['debug'] = "off" ;

    // present form to select json file for processing (general template)
    $formfields = array(
        "instructions"  => "Transfers membership duty rota information from webcollect into raceManager.  
                       The rota information in raceManager is completely replaced by the current information in 
                       webcollect.  This action does NOT change duties already allocated<br><br>
                       <b>This may take a few minutes</b><br><br>
                       Using server {$_SESSION['db_host']}/{$_SESSION['db_name']}<br>",
        "script"        => "rota_synch_webcollect.php?pagestate=submit&contacts={$_REQUEST['contacts']}&notes={$_REQUEST['notes']}",
    );
    $pagefields['body'] =  $tmpl_o->get_template("script_confirm", $formfields, array("dryrun" => true));

    // render page
    echo $tmpl_o->get_template("basic_page", $pagefields, $params);
}

/* ------------ submit page ---------------------------------------------*/

elseif (trim(strtolower($_REQUEST['pagestate'])) == "submit")
{
    // set webcollect API parameters
    define("ORGANISATION_SHORT_NAME", $_SESSION['ORGANISATION_SHORT_NAME']);
    define("ACCESS_TOKEN", $_SESSION['ACCESS_TOKEN']);

    // connect to database
    $db_o = new DB();

    // empty database table with rotamembers
    if (trim($_REQUEST['dryrun']) == "true")
    {
        $empty = true;
    }
    else
    {
        // remove entries that have been previously passed from webcollect
        $empty = $db_o->db_query("DELETE FROM t_rotamember WHERE updby = 'rota_synch_wc'");
    }

    if ($empty)
    {
        $sql_insert_data = "";  // used to collect data for sql insert
        $print_data      = array();  // used for dry run display
        $rota_map_err    = 0;   // count for number of rota codes not mapped
        $member_input    = 0;   // count for webcollect member records processed
        $member_output   = 0;   // count for members records output
        $rota_total      = 0;   // count for rota records created

        $num_missing = 0;

        // get the dutyman MEMBER information organised as "webcollect id" => "Member UID" value
        $dtm_links = array();

        $db = mysqli_connect($_SESSION['dtm_name'], $_SESSION['dtm_user'], $_SESSION['dtm_pass'], $_SESSION['dtm_host'], $_SESSION['dtm_port']);
        if(!$db) { die("Connection failed: " . mysqli_connect_error()); }

        // Address 1 is used to store webcollect id and member UID is the login link
        $records = mysqli_query($db,"SELECT `Address 1`, `Member UID`, `First Name`, `Last Name` FROM members WHERE 1=1");
        $i = 0;
        $j = 0;    // missing member id counter
        while($data = mysqli_fetch_array($records))
        {
            $i++;
            if (empty($data['Member UID']) or empty($data['Address 1']))
            {
                $j++;     // increment missing counter
            }
            else
            {
                $dtm_links["{$data['Address 1']}"] = $data['Member UID'];   // create lookup array
            }
        }

//        echo "<pre>processed: $i | missing: $j | output: ".count($dtm_links),"</pre>";   // fixme logging
//        //echo "<pre>".print_r($dtm_links,true)."</pre>";

        // process the webcollect member records - creating dry run info output OR SQL inset commands
        $client = new WebcollectRestapiClient();
        $member = $client->setOrganisationShortName(ORGANISATION_SHORT_NAME)  // webcollect short name for organisation
        ->setAccessToken(ACCESS_TOKEN)                                        // webcollect API key from the admin UI
        ->setEndPoint('member')                                               // look for member records
            ->setQuery()                                                      // query all members
            ->find('process_member');                                         // callback to process each member record


        // display output from process
        $bufr = "";

        // output totals
        u_writelog("rota_synch: member records retrieved - $member_input, member records with rota info - $member output, rota records created - $rota_total",0 );
        $bufr.= $tmpl_o->get_template("rota_synch_totals", array("rota_map_err"=>$rota_map_err,
            "member_input"=>$member_input, "member_output"=>$member_output, "rota_total"=>$rota_total),
            array("dryrun"=>trim($_REQUEST['dryrun']), "num_records"=>$rota_total));

        // print the rota records to be added
        $bufr.= $tmpl_o->get_template("rota_synch_records", array(), array("rota_data"=>$print_data));

        // transfer the record if not a dry run
        if (trim($_REQUEST['dryrun']) == "false")   // do the transfer
        {
            // create and run sql query
            $sql_insert_data = rtrim($sql_insert_data,", ");
            $sql = "INSERT INTO t_rotamember (`firstname`, `familyname`, `rota`, `phone`, `email`, `note`, `memberid`, `dtm_login`, `active`, `updby`) VALUES $sql_insert_data";

            $insert = $db_o->db_query($sql);
            $insert ? $state = 0 : $state = 1;    // 0 if records entered , 1 if failed

            // if insert completed then check for duplicates and remove them
            if ($insert)
            {
                $duplicates = $db_o->db_get_rows("SELECT firstname, familyname, rota FROM t_rotamember GROUP BY firstname, familyname, rota HAVING COUNT(*) > 1");
                if ($duplicates)
                {
                    foreach ($duplicates as $k=>$d)
                    {
                        $first = str_replace("'","''",$d['firstname']);  // fix quotes in names
                        $last = str_replace("'","''",$d['familyname']);

                        $query = "SELECT id, firstname, familyname, rota, updby FROM t_rotamember 
                              WHERE firstname = '$first' and familyname = '$last' and rota = '{$d['rota']}'
                              ORDER BY case when updby = 'rota_synch_wc' then 0 else 1 end";
                        $list = $db_o->db_query($query);
                        $i = 0;
                        foreach($list as $row)
                        {
                            $i++;
                            if ($i == 1) { continue;}  // keep first record
                            $del = $db_o->db_query("DELETE FROM t_rotamember WHERE id = {$row['id']}"); // delete other duplicate records
                        }
                    }
                }
            }
        }
    }
    else
    {
        $state = 2;
    }

    if (isset($state)) {
        $bufr.= $tmpl_o->get_template("rota_synch_state", array(), array("state"=>$state)); }

    $pagefields['body'] = $bufr;

    // render page
    echo $tmpl_o->get_template("basic_page", $pagefields, array() );

    $db_o->db_disconnect();
    exit();
}
else
{
    // error pagestate not recognised
    $_SESSION['pagefields']['body'] = "<p>INTERNAL ERROR page status not recognised - please contact System Manager</p>";

    // render page
    echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields'], array() );
}

function process_member(WebCollectResource $resource) 
// this is called once per member object returned from the api
{ 
    global $db_o;
    global $dtm_links;
    global $num_missing;
    global $sql_insert_data, $print_data, $member_input, $member_output, $rota_total;

    $member_input++;
    $stop = false;
    $field_map = $_SESSION['webcollect']['field_map'];
    $no_dtmlogin = false;

    $member = array(
        "firstname"  => "",
        "familyname" => "",
        "rota"       => "",
        "phone"      => "",
        "email"      => "",
        "note"       => "",
        "memberid"   => "",
        "dtm_login"  => "",
    );
  
    $array = json_decode(json_encode($resource), true);  // convert into array

    // check if member is to be ignored for rotas (e.g exempt or resigning)
    if (in_array(strtolower($array['form_data']["{$field_map['rota_status']}"]),
        $_SESSION['webcollect']['rota_ignore_values']))
    {
        $member['rota'] = "";
    }
    else
    {
        $member['firstname']  = ucfirst(strtolower(trim($array["{$field_map['firstname']}"])));
        $member['familyname'] = ucfirst(trim($array["{$field_map['familyname']}"]));
        $member['rota_str']   = strtolower(trim($array['form_data']["{$field_map['rota']}"]));

        // translate each webcollect rota code into a raceManager rota code
        $member['rota'] = map_rota_code($member['rota_str'], $stop);

        // add contact details if required
        if (trim($_REQUEST['contacts']) == "true")   // this is set in rm_utils_cfg.php
        {
            if (empty($array[$field_map['phone_1']])) // requested phone field not available
            {
                $member['phone'] = strtolower(trim($array["{$field_map['phone_1']}"]));
            } else {
                $member['phone'] = strtolower(trim($array["{$field_map['phone_2']}"]));   // set to home phone instead
            }
            $member['email'] = strtolower(trim($array["{$field_map['email']}"]));
        }

        // Add notes on restrictions and availability if required
        if (trim($_REQUEST['notes']) == "true")  // this is set in rm_utils_cfg.php
        {
            $restrictions = trim($array['form_data']["{$field_map['duty_restriction']}"]);
            $availability = trim($array['form_data']["{$field_map['duty_availability']}"]);
            $member['note'] = $restrictions;
            if (!empty($availability)) { $member['note'].= " | ".$availability; }
        }

        // get memberid
        $member['memberid'] = $array["{$field_map['memberid']}"];

        $m = $member['memberid'];

        $member['dtm_login'] = "";
        if ($dtm_links)
        {
            if (key_exists($m, $dtm_links))
            {
                $member['dtm_login'] = "https://".$_SESSION['dtm_name']."/dm/".$_SESSION['dtm_user']."/".$dtm_links[$m];
            }
            else
            {
                $num_missing++;
                $member['dtm_login'] = "--missing--";
            }
        }
        else
        {
            $member['dtm_login'] = "--missing--";
        }

    }

    // create either dryrun output or values string for database insert
    if (!empty($member['rota']))
    {
        $member_output++;
        $rotas = explode(",", $member['rota']);  // get all rotas for member

        foreach ($rotas as $k=>$rota)
        {
            $rota_str = array_search($rota, $_SESSION['webcollect']['rota_code_map']); // convert rota codes into full name
            if ($rota_str === false) { $rota_str = "unknown rota [$rota]"; }

            $rota_total++;

            // get data for screen isplay
            $print_data[] = array(
                "familyname" => $member['familyname'],
                "name"       => $member['firstname']." ".$member['familyname'],
                "rota"       => $rota_str,
                "phone"      => $member['phone'],
                "email"      => $member['email'],
                "memberid"   => $member['memberid'],
                "note"       => $member['note']
            );

            // create insert record for database
            $sql_insert_data.= <<<EOT
	        ("{$member['firstname']}", "{$member['familyname']}", "$rota", "{$member['phone']}", "{$member['email']}", 
	         "{$member['note']}", "{$member['memberid']}", "{$member['dtm_login']}","1", "rota_synch_wc"),
EOT;
        }
    }
}

function map_rota_code($rota_str, $dbg)
{
    global $rota_map_err;

    $rotas = explode(",", $rota_str);
    foreach ($rotas as $k=>$rota)
    {
        $rota = trim($rota);
        if (array_key_exists("$rota", $_SESSION['webcollect']['rota_code_map']))
        {
            $rotas[$k] = $_SESSION['webcollect']['rota_code_map']["$rota"];
        }
        else
        {
            unset ($rotas[$k]);
            $rota_map_err++;
        }
    }
    $rota_str = implode(",", $rotas);
    return $rota_str;
}


 