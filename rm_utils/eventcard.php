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

if ($_REQUEST['pagestate'] == "init")
{
    // setup debug
    array_key_exists("debug", $_REQUEST) ? $params['debug'] = $_REQUEST['debug'] : $params['debug'] = "off" ;

    $formfields = array(
        "instructions" => "Create a print-friendly display of the current event programme</br></br>
                       The header and footer content are held in html files which can be edited to
                       display any information you want to add to the event card - please see User Guide: Event Programme</br></br>
                       Please select the start and end date for the programme and the fields you want to include.",
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
    require_once ("{$loc}/common/classes/event_class.php");
    $event_o = new EVENT($db_o);

    // deal with parameters
    $fields = array("date"=>"Date", "time"=>"Time", "event"=>"Event", "tide"=>"Tide", "notes"=>"Notes",
        "race_duty"=>"Race Duties", "safety_duty"=>"Safety Duties", "club_duty"=>"Clubhouse Duties");

    $constraints = array(
         "notes"      => process_bool_parameter("notes", true),
         "tide"       => process_bool_parameter("tide", true),
         "race_duty"  => process_bool_parameter("race_duty", true),
         "safety_duty"=> process_bool_parameter("safety_duty", true),
        "club_duty"   => process_bool_parameter("club_duty", true)
    );

    $events = $event_o->getevents_inperiod(array("active"=>"1"), $_REQUEST['date-start'], $_REQUEST['date-end'],
                                           "live", false);
    $i = 0;
    foreach ($events as $k=>$event)
    {
        if (!empty($event['event_name']))
        {
            $i++;
            $data[$i] = array(
                "date" => date("d-m-Y", strtotime($event['event_date'])),
                "time" => $event['event_start'],
                "name" => $event['event_name'],
                "tide" => "{$event['tide_time']} [{$event['tide_height']} m] ",
                "notes" => $event['event_notes'],
                "race_duty"   => "",
                "safety_duty" => "",
                "club_duty"   => ""
            );
            if (!$notes_inc) { $data[$i]['notes'] = ""; }
            if (!$tides_inc) { $data[$i]['tide'] = ""; }
            if ($duties_inc)
            {
                // get duties
                $duties = $event_o->event_geteventduties($event['id']);
                // map them into correct output fields
                foreach ($duties as $duty)
                {
                    $dutybin = $_SESSION['rotamap']["{$duty['dutycode']}"];
                    $data[$i]["$dutybin"].= "{$duty['person']} | ";
                }
                $data[$i]['race_duty'] = rtrim(" |", $data[$i]['race_duty']);
                $data[$i]['safety_duty'] = rtrim(" |", $data[$i]['safety_duty']);
                $data[$i]['club_duty'] = rtrim(" |", $data[$i]['club_duty']);
            }
        }
    }
    $pagefields = array(
        "header"  => file_get_contents("$loc/config/eventcard_hdr.htm"),
        "footer"  => file_get_contents("$loc/config/eventcard_ftr.htm"),
        "date"    => date("Y-m-d"),
    );
    echo $tmpl_o->get_template("event_card", $pagefields, array("fields" => $fields, "data" => $data) );
}
else
{
    // error pagestate not recognised
    $_SESSION['pagefields']['body'] = "<p>INTERNAL ERROR page status not recognised - please contact System Manager</p>";

    // render page
    echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields'], array() );
}

function process_bool_parameter($key, $default)
{
    if (empty($_REQUEST[$key]))
    {
        $default ? $val = true : $val = false ;
    }
    elseif ($_REQUEST[$key]=="true" or $_REQUEST[$key]=="false")
    {
        $_REQUEST[$key] =="true"?  $val = true : $val = false ;
    }
    else
    {
        $val = true;
    }

    return $val;
}





