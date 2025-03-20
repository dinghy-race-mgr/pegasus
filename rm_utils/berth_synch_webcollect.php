<?php

/**
 * berth_synch_webcollect.php
 *
 * script to download berth information from webcollect in a csv format for use in other systems
 * this will only work with Starcross YC configuration of webcollect
 * 
 */

$loc  = "..";
$page = "Berth Synch";
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
$styletheme = "flatly_";
$stylesheet = "./style/rm_utils.css";

session_id("sess-rmutil-".str_replace("_", "", strtolower($page)));
session_start();

// webcollect interface
define('BASE', dirname(__FILE__) . '/');
require BASE . 'lib/WebCollectRestapiClient.php';

// classes
require_once("$loc/common/lib/util_lib.php");
require_once("$loc/common/classes/db_class.php");
require_once("{$loc}/common/classes/template_class.php");

// set templates
$tmpl_o = new TEMPLATE(array("$loc/common/templates/general_tm.php","./templates/layouts_tm.php", "./templates/webcollect_tm.php"));

// initialise session if this is first call
//if (!isset($_SESSION['util_app_init']) OR ($_SESSION['util_app_init'] === false))
//{
    $init_status = u_initialisation("$loc/config/rm_utils_cfg.php", $loc, $scriptname);

    if ($init_status)
    {
        // set timezone
        if (array_key_exists("timezone", $_SESSION)) { date_default_timezone_set($_SESSION['timezone']); }

        // start log
        error_log(date('H:i:s')." -- rm_util BERTH SYNCH -------------------- [session: ".session_id()."]".PHP_EOL, 3, $_SESSION['syslog']);

        // set initialisation flag
        $_SESSION['util_app_init'] = true;
    }
    else
    {
        u_exitnicely($scriptname, 0, "one or more problems with script initialisation",
            "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
    }
//}

// arguments
if (empty($_REQUEST['pagestate'])) { $_REQUEST['pagestate'] = "init"; }
$report = true;
if (!empty($_REQUEST['report']))
{
    if ($_REQUEST['report'] == "off") {$report = false;}
}

$pagefields = array(
    "loc"           => $loc,
    "theme"         => $styletheme,
    "stylesheet"    => $stylesheet,
    "title"         => "Berth Synchronisation",
    "header-left"   => "raceManager",
    "header-right"  => "download berth info from webcollect ...",
    "body"          => "",
    "confirm"       => "Create File",
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
        "instructions"  => "Exports membership berth information from webcollect in a CSV file.  <br><br>
                       <b>This may take a few minutes</b><br><br>
                       <small>Using server {$_SESSION['db_host']}/{$_SESSION['db_name']}</small><br>",
        "script"        => "berth_synch_webcollect.php?pagestate=submit",
    );
    $pagefields['body'] =  $tmpl_o->get_template("script_confirm", $formfields, $params);

    // render page
    echo $tmpl_o->get_template("basic_page", $pagefields, $params);
}

/* ------------ submit page ---------------------------------------------*/

elseif (trim(strtolower($_REQUEST['pagestate'])) == "submit")
{
    // get webcollect API parameters
    define("ORGANISATION_SHORT_NAME", $_SESSION['ORGANISATION_SHORT_NAME']);
    define("ACCESS_TOKEN", $_SESSION['ACCESS_TOKEN']);

    // connect to database
    $db_o = new DB();

    $output = array();      // used to collect data for sql insert
    $used_group = array();  // used to stop duplicate berth records being included
    $member_input    = 0;   // count for webcollect member records processed
    $member_output   = 0;   // count for berth records output

    $cols = array("member", "subscription", "start", "end", "berth", "reg_no",
        "class", "colour", "sail_no", "name", "features",
        "owner_type", "notes", "sort");

    // get the webcollect member records
    $client = new WebcollectRestapiClient();
    $member = $client->setOrganisationShortName(ORGANISATION_SHORT_NAME)  // webcollect short name for organisation
    ->setAccessToken(ACCESS_TOKEN)                                        // webcollect API key from the admin UI
    ->setEndPoint('member')                                               // look for member records
        ->setQuery()                                                      // query all members
        ->find('process_member');                                         // callback to process each member record


    //echo "<pre>".print_r($output,true)."</pre>";

    // sort output array
    u_array_sort_by_column($output, 'sort');

    // get printed output
    $print_data = "";
    foreach ($output as $row)
    {
        $print_data.= <<<EOT
           <tr><td>{$row['member']}</td><td>{$row['berth']}</td><td>{$row['class']} {$row['sail_no']}</td></tr>
EOT;
    }

    // create CSV file
    $file = $_SESSION['berth']['file'];
    $path = $_SESSION['berth']['loc'];
    $csv_file = $path."/".str_replace("date", date("YmdHi"), $file);
    $latest_file = $path."/berth_latest.csv";                         // used to provide consistent link to latest info

    $status = create_csv_file($csv_file, $cols, $output);

    if ($status == "0")
    {
        // delete/create 'latest' file
        foreach (GLOB($latest_file) AS $file) { unlink($file); }
        if (!copy($csv_file, $latest_file))
        {
            $status = "4";
        }
    }

    // get report content display
    $pagefields['body'] = $tmpl_o->get_template("berth_synch_report", array("member_input"=>$member_input,
        "member_output"=>$member_output, "file"=>"$csv_file", "berth_data"=>$print_data), array("status"=>$status));

    // render page
    if ($report)
    {
        echo $tmpl_o->get_template("basic_page", $pagefields, array() );
    }
    else
    {
        if ($status == "0")
        {
            echo "ok";
        }
        else
        {
            echo "fail $status";
        }
    }

    $db_o->db_disconnect();
    exit();
}
else
{
    if ($report)
    {
        // error pagestate not recognised
        $_SESSION['pagefields']['body'] = "<p>INTERNAL ERROR page status not recognised - please contact System Manager</p>";

        // render page
        echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields'], array() );
    }
    else
    {
        echo "fail -1";
    }

}

function process_member(WebCollectResource $resource)
// this is called once per member object returned from the api
{ 
    global $output, $used_group, $member_input, $member_output;

    $member_input++;
  
    $array = json_decode(json_encode($resource), true);  // convert into array

    $first = ucfirst(strtolower(trim($array["{$_SESSION['webcollect']['firstname_fld']}"])));
    $last  = ucfirst(strtolower(trim($array["{$_SESSION['webcollect']['familyname_fld']}"])));
    
//    if ($last == "Clarke")
//    {
//        echo "<pre>".print_r($array,true)."</pre>";
//    }

    $person = $first." ".$last;
    $phone = strtolower(trim($array["{$_SESSION['webcollect']['phone_fld']}"]));
    $email = strtolower(trim($array["{$_SESSION['webcollect']['email_fld']}"]));

    $sub_map = array (
        "berth" => "Dinghy Berth",
        "rack" => "Rack Storage - Canoes / Kayaks / Windsurfers / Paddleboards",
        "junior rack" => "Rack Storage - Toppers / Oppies",
        "junior berth" => "Junior Dinghy Berth",
        "tender" => "Rack Storage - Tenders",
        "cruiser" => "Cruiser Winter Storage"
    );

    // decide whether we want to process this record
    $get_it = false;
    if (empty($array['group_name'])) // not a group - process individual record
    {
        $get_it = true;
    }
    elseif (!empty($array['group_name']))  // its a group
    {
        if ($array['group_admin']) // is it a group admin - make sure we have the lead member [Issue can be more than one group admin]
        {
            $used = array_search($array['unique_group_id'], $used_group);   // have we processed this group already
            if (!$used) { $get_it = true; }
        }
    }

    if ($get_it)
    {
        if (!empty($array['unique_group_id'])) {
            $used_group[] = $array['unique_group_id'];
        }

        foreach ($array['subscriptions'] as $sub)
        {
            unset($member);
            if (strpos(strtolower($sub['description']), "berth") !== false OR
                strpos(strtolower($sub['description']), "rack") !== false OR
                strpos(strtolower($sub['description']), "storage") !== false) {


                $member_output++;

                $key = array_search($sub['description'], $sub_map);
                $key ? $sub_name = $key : $sub_name = $sub['description'];

                $member = array(
                    "member" => $person,
                    //"email"        => $email,
                    //"phone"        => $phone,
                    "subscription" => $sub_name,
                    "start" => $sub['start_date'],
                    "end" => $sub['end_date'],
                    //"membership"   => $sub['provides_membership'],
                    //"renew"        => $sub['renew'],
                    "berth" => $sub['form_data']['Berth_Number'],
                    "reg_no" => $sub['form_data']['SYC_Reg_No'],
                    "class" => $sub['form_data']['Boat_Class'],
                    "colour" => $sub['form_data']['Hull_Colour'],
                    "sail_no" => $sub['form_data']['Sail_No'],
                    "name" => $sub['form_data']['Boat_Name'],
                    "features" => $sub['form_data']['Any_other_identifying_features'],
                    //"alt_contact"  => $sub['form_data']['Alternative_Contact'],
                    //"alt_phone"    => $sub['form_data']['Alternative_Contact_Tel_No'],
                    //"alt_mobile"   => $sub['form_data']['Alternative_Contact_Tel_No_mob'],
                    "owner_type" => $sub['form_data']['Owner_type'],
                    "notes" => $sub['form_data']['Berth_Marshall_Notes'],
                    /*"sticker_19" => $sub['form_data']['Boat_Sticker_No_2019'],*/
                    "sort" => strtolower($last)
                );
                $output[] = $member;
            }
        }
    }
}

function create_csv_file($file, $cols, $rows)
{
    $status = "0";
    $fp = fopen($file, 'w');
    if (!$fp) { $status = "1"; }

    if ($fp)
    {
        $r = fputcsv($fp, $cols, ',');
        if (!$r) { $status = "2"; }

        foreach ($rows as $row)
        {
            if ($status != "0") { break; }
            $r = fputcsv($fp, $row, ',');
            if (!$r) {$status = "3"; }
        }
        fclose($fp);
    }

    return $status;
}


 