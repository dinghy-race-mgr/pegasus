<?php
$loc  = "..";
$page = "eventcard";     //
$scriptname = basename(__FILE__);
$today = date("Y-m-d");

require_once ("{$loc}/common/lib/util_lib.php");

session_start();

// initialise session if this is first call
if (!isset($_SESSION['app_init']) OR ($_SESSION['app_init'] === false))
{
    $init_status = u_initialisation("$loc/config/racemanager_cfg.php", "$loc/config/rm_utils_cfg.php", $loc, $scriptname);

    if ($init_status)
    {
        // set timezone
        if (array_key_exists("timezone", $_SESSION)) { date_default_timezone_set($_SESSION['timezone']); }

        // start log
        $_SESSION['syslog'] = "$loc/logs/adminlogs/".$_SESSION['syslog'];
        error_log(date('H:i:s')." -- EVENT CARD --------------------".PHP_EOL, 3, $_SESSION['syslog']);
    }
    else
    {
        u_exitnicely($scriptname, 0, "initialisation failure", "one or more problems with script initialisation");
    }
}

require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");

// connect to database
$db_o = new DB();

// set templates
$tmpl_o = new TEMPLATE(array("$loc/templates/general_tm.php","$loc/templates/utils/layouts_tm.php",
                             "$loc/templates/utils/eventcard_tm.php"));


if (empty($_REQUEST['pagestate'])) { $_REQUEST['pagestate'] = "init"; }

/* ------------ file selection page ---------------------------------------------*/
$state = 0;
if ($_REQUEST['pagestate'] == "init")
{
    // setup debug
    array_key_exists("debug", $_REQUEST) ? $params['debug'] = $_REQUEST['debug'] : $params['debug'] = "off" ;

    $formfields = array(
        "instructions" => "Create a print-friendly display of the current event programme</br>
                       <small>The header and footer content are held in html files which can be edited to
                       display any information you want to add to the event card - please see User Guide: Event Programme</br>
                       Please select the start and end date for the programme and the fields you want to include.</small>",
        "script" => "eventcard.php?pagestate=submit",
    );

    // present form to select json file for processing (general template)
    $pagefields = array(
        "loc" => $loc,
        "theme" => "flatly_",
        "stylesheet" => "$loc/style/rm_utils.css",
        "title" => "eventcard",
        "header-left" => "raceManager",
        "header-right" => "Event Card",
        "body" => $tmpl_o->get_template("event_card_form", $formfields, $params),
        "footer-left" => "",
        "footer-center" => "",
        "footer-right" => "",
    );

    // render page
    echo $tmpl_o->get_template("basic_page", $pagefields );
}

/* ------------ submit page ---------------------------------------------*/

elseif (strtolower($_REQUEST['pagestate']) == "submit")
{
    if (strtotime($_REQUEST['date-start']) > strtotime($_REQUEST['date-end']))
    {
        $state = 3;
    }
    else
    {
        require_once("{$loc}/common/classes/event_class.php");
        require_once("{$loc}/common/classes/rota_class.php");
        $event_o = new EVENT($db_o);
        $rota_o = new ROTA($db_o);

        $duty_inc = get_default_duty_display($_SESSION['event_card_duties']);

        $constraints = array(
            "notes"       => process_bool_parameter("notes", true),
            "tide"        => process_bool_parameter("tide", true),
            "race_duty"   => process_bool_parameter("race_duty", $duty_inc['race']),
            "safety_duty" => process_bool_parameter("safety_duty", $duty_inc['safety']),
            "club_duty"   => process_bool_parameter("club_duty", $duty_inc['house']),
        );

        $_REQUEST['scope'] == "1" ? $scope = array("active" => "1") : $scope = array() ;

        $events = $event_o->get_events_inperiod($scope, $_REQUEST['date-start'], $_REQUEST['date-end'], "live", false);

        if ($events !== false) {
            $i = 0;
            foreach ($events as $k => $event) {
                if (!empty($event['event_name'])) {
                    $i++;
                    $data[$i] = array(
                        "date" => date("d-M", strtotime($event['event_date'])),
                        "time" => $event['event_start'],
                        "event" => $event['event_name'],
                        "tide" => "{$event['tide_time']} [{$event['tide_height']}m] ",
                        "notes" => $event['event_notes'],
                        "race_duty" => "",
                        "safety_duty" => "",
                        "club_duty" => ""
                    );

                    if ($i == 1) {$year = date("Y", strtotime($event['event_date'])); }

                    // get duties
                    $duties = $rota_o->get_event_duties($event['id']);
                    if ($duties)
                    {
                        // map them into correct output fields
                        foreach ($duties as $duty) {
                            $dutybin = $_SESSION['rotamap']["{$duty['dutycode']}"];
                            $data[$i][$dutybin].= "{$duty['person']}|";
                        }
                        $data[$i]['race_duty']   = rtrim($data[$i]['race_duty'], "| ");
                        $data[$i]['safety_duty'] = rtrim($data[$i]['safety_duty'], "| ");
                        $data[$i]['club_duty']   = rtrim($data[$i]['club_duty'], "| ");
                    }
                }
            }
            $pagefields = array(
                "title" => "Programme",
                "header" => file_get_contents("$loc/config/html/eventcard_hdr.htm"),
                "footer" => file_get_contents("$loc/config/html/eventcard_ftr.htm"),
                "date" => date("Y-m-d"),
                "year" => $year
            );
            echo $tmpl_o->get_template("event_card", $pagefields,
                array("fields" => $_SESSION['eventcard_fields'], "constraints" => $constraints, "data" => $data));
        }
        else
        {
            // error no published events in period
            $state = 1;
        }
    }
}
else
{
    // error pagestate not recognised
    $state = 2;
}

if ($state > 0)
{
    $pagefields = array(
        "loc" => $loc,
        "theme" => "flatly_",
        "stylesheet" => "$loc/style/rm_utils.css",
        "title" => "eventcard",
        "header-left" => "raceManager",
        "header-right" => "Event Card",
        "body" => "",
        "footer-left" => "",
        "footer-center" => "",
        "footer-right" => ""
    );
    $pagefields['body'] = $tmpl_o->get_template("eventcard_state", array(), array("state"=>$state));

    // render page
    echo $tmpl_o->get_template("basic_page", $pagefields, array());
}


function process_bool_parameter($key, $default)
{
    if (empty($_REQUEST[$key]))
    {
        $val = false;
    }
    elseif($_REQUEST[$key]=="true" or $_REQUEST[$key]=="false")
    {
        $_REQUEST[$key] =="true"?  $val = true : $val = false ;
    }
    else
    {
        $default ? $val = true : $val = false ;
    }

    return $val;
}

function get_default_duty_display($setting)
{
    $duty = array("ood"=>false, "race"=>false, "safety"=>false, "house"=>false,);
    $values = explode("|", $setting);
    foreach ($values as $val)
    {
        if ($val == "race" or $val == "ood")
        {
            $duty['race'] = true;
        }
        elseif($val == "safety")
        {
            $duty['safety'] = true;
        }
        elseif($val == "house")
        {
            $duty['house'] = true;
        }
    }
    return $duty;
}



