<?php

/**
 * rota_synch_webcollect.php
 *
 * script to upload rota information from webcollect to raceManager
 * 
 */

$loc  = "..";
$page = "Rota Synch";
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
$styletheme = "flatly_";
$stylesheet = "./style/rm_utils.css";

session_start();

define('BASE', dirname(__FILE__) . '/');
require BASE . 'lib/WebCollectRestapiClient.php';

require_once("$loc/common/lib/util_lib.php");
require_once("$loc/common/classes/db_class.php");
require_once("{$loc}/common/classes/template_class.php");

// set templates
$tmpl_o = new TEMPLATE(array("$loc/common/templates/general_tm.php","./templates/layouts_tm.php", "./templates/webcollect_tm.php"));

// initialise session if this is first call
if (!isset($_SESSION['util_app_init']) OR ($_SESSION['util_app_init'] === false))
{
    $init_status = u_initialisation("$loc/config/rm_utils_cfg.php", $loc, $scriptname);

    if ($init_status)
    {
        // set timezone
        if (array_key_exists("timezone", $_SESSION)) { date_default_timezone_set($_SESSION['timezone']); }

        // start log
        error_log(date('H:i:s')." -- ROTA SYNCH --------------------".PHP_EOL, 3, $_SESSION['syslog']);

        // set initialisation flag
        $_SESSION['util_app_init'] = true;
    }
    else
    {
        u_exitnicely($scriptname, 0, "one or more problems with script initialisation",
            "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
    }
}

// arguments
if (empty($_REQUEST['pagestate'])) { $_REQUEST['pagestate'] = "init"; }
if (empty($_REQUEST['contacts']))  { $_REQUEST['contacts'] = "true"; }
if (empty($_REQUEST['notes']))     { $_REQUEST['notes'] = "true"; }
if (empty($_REQUEST['dryrun']))    { $_REQUEST['dryrun'] = "false"; }

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
        "script"        => "rota_synch_webcollect.php?pagestate=submit&dryrun={$_REQUEST['dryrun']}&contacts={$_REQUEST['contacts']}&notes={$_REQUEST['notes']}",
    );
    $pagefields['body'] =  $tmpl_o->get_template("script_confirm", $formfields, $params);

    // render page
    echo $tmpl_o->get_template("basic_page", $pagefields, $params);
}

/* ------------ submit page ---------------------------------------------*/

elseif (trim(strtolower($_REQUEST['pagestate'])) == "submit")
{
    // connect to database
    $db_o = new DB();

    // get webcollect API parameters
    $wc = $db_o->db_getinivalues(true, "webcollect");
    foreach ($wc as $param=>$val)
    {
        define("$param" , $val);
    }

    // empty database table with rotamembers
    if (trim($_REQUEST['dryrun']) == "true")
    {
        $empty = true;
    }
    else
    {
        $empty = $db_o->db_truncate(array("t_rotamember"));
    }

    if ($empty)
    {
        $sql_insert_data = "";  // used to collect data for sql insert
        $print_data      = "";  // used for dry run display
        $rota_map_err    = 0;   // count for number of rota codes not mapped
        $member_input    = 0;   // count for webcollect member records processed
        $member_output   = 0;   // count for members records output
        $rota_total      = 0;   // count for rota records created

        // get the webcollect member records
        $client = new WebcollectRestapiClient();
        $member = $client->setOrganisationShortName(ORGANISATION_SHORT_NAME)  // webcollect short name for organisation
        ->setAccessToken(ACCESS_TOKEN)                                        // webcollect API key from the admin UI
        ->setEndPoint('member')                                               // look for member records
            ->setQuery()                                                      // query all members
            ->find('process_member');                                         // callback to process each member record

        $bufr = "";

        // output totals
        $bufr.= $tmpl_o->get_template("rota_synch_totals", array("rota_map_err"=>$rota_map_err,
            "member_input"=>$member_input, "member_output"=>$member_output, "rota_total"=>$rota_total), array());

        if ($_REQUEST['dryrun'] == "true")   // just generate text output
        {
            $bufr.= $tmpl_o->get_template("rota_synch_dryrun", array("rota_data"=>$print_data), array());
        }
        else
        {
            // create and run sql query
            $sql_insert_data = rtrim($sql_insert_data,", ");
            $sql = "INSERT INTO t_rotamember (firstname, familyname, rota, phone, email, note, active, updby) VALUES $sql_insert_data";
            $insert = $db_o->db_query($sql);
            $insert ? $state = 0 : $state = 1;
        }
    }
    else
    {
        $state = 2;
    }
    if (isset($state)) {
        $bufr.= $tmpl_o->get_template("rota_synch_state", array(), array("state"=>$state)); }

    $pagefields['body'] =  $bufr;

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
    global $sql_insert_data, $print_data, $member_input, $member_output, $rota_total;

    $member_input++;
    $stop = false;

    $member = array(
      "firstname"  => "",
      "familyname" => "",
      "rota"       => "",
      "phone"      => "",
      "email"      => "",
      "note"       => ""
    );
  
    $array = json_decode(json_encode($resource), true);  // convert into array

    // check if member is to be ignored for rotas (e.g exempt or resigning)
    if (in_array(strtolower($array['form_data']["{$_SESSION['webcollect']['rota_status_fld']}"]),
        $_SESSION['webcollect']['rota_ignore_values']))
    {
        $member['rota'] = "";
    }
    else
    {
        $member['firstname'] = ucfirst(strtolower(trim($array["{$_SESSION['webcollect']['firstname_fld']}"])));
        $member['familyname'] = ucfirst(trim($array["{$_SESSION['webcollect']['familyname_fld']}"]));
        $member['rota_str'] = strtolower(trim($array['form_data']["{$_SESSION['webcollect']['rota_fld']}"]));

        // translate each webcollect rota code into a raceManager rota code
        $member['rota'] = map_rota_code($member['rota_str'], $stop);

        if (trim($_REQUEST['contacts']) == "true")
        {
            $member['phone'] = strtolower(trim($array["{$_SESSION['webcollect']['phone_fld']}"]));
            $member['email'] = strtolower(trim($array["{$_SESSION['webcollect']['email_fld']}"]));
        }

        if (trim($_REQUEST['notes']) == "true")
        {
            $restrictions = trim($array['form_data']["{$_SESSION['webcollect']['duty_restriction_fld']}"]);
            $availability = trim($array['form_data']["{$_SESSION['webcollect']['duty_availability_fld']}"]);
            $member['note'] = $restrictions;
            if (!empty($availability))
            {
                $member['note'].= " | ".$availability;
            }
        }
    }

    if (!empty($member['rota']))
    {
        $member_output++;

        $rotas = explode(",", $member['rota']);

        foreach ($rotas as $k=>$rota)
        {
            $rota_str = array_search($rota, $_SESSION['webcollect']['rota_code_map']);
            $rota_total++;
            if (trim($_REQUEST['dryrun']) == "true")
            {
                $print_data.= <<<EOT
                <tr>
                <td>{$member['firstname']} {$member['familyname']}</td>
                <td>$rota_str</td>
                <td>{$member['note']}</td>
                </tr>
EOT;
            }
            else
            {
                $sql_insert_data.= <<<EOT
	        ("{$member['firstname']}", "{$member['familyname']}", "$rota", "{$member['phone']}", "{$member['email']}", 
	         "{$member['note']}", "1", "rota_synch_wc"),
EOT;
            }
        }
    }
}

function map_rota_code($rota_str, $dbg)
{
    global $rota_map_err;

    $rotas = explode(",", $rota_str);
//    if ($dbg) {echo "<pre>ROTA STR ".$rota_str."</pre>";}
//    if ($dbg) {echo "<pre>ROTA ARR ".print_r($rotas, true)."</pre>";}
    foreach ($rotas as $k=>$rota)
    {
        $rota = trim($rota);
//        if ($dbg) {echo "processing: $k|$rota<br>";}
        if (array_key_exists("$rota", $_SESSION['webcollect']['rota_code_map']))
        {
//            if ($dbg) {echo "key exists<br>";}
            $rotas[$k] = $_SESSION['webcollect']['rota_code_map']["$rota"];
        }
        else
        {
//            if ($dbg) {echo "key does not exist<br>";}
//            echo "$rota could not be matched in ... $rota_str<br>";
            unset ($rotas[$k]);
            $rota_map_err++;
        }
    }
//    if ($dbg) {echo "<pre>ROTA ARR2 ".print_r($rotas, true)."</pre>";}
    $rota_str = implode(",", $rotas);
//    if ($dbg) {echo "<pre>ROTA STR2 ".$rota_str."</pre>";}
    return $rota_str;
}

function html_flush()
{
   echo str_pad('',4096)."\n";
   ob_flush();
   flush();
}

 