<?php
$loc  = "..";
$page = "duty_check";     //
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
require_once ("{$loc}/common/classes/rota_class.php");
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/template_class.php");

// connect to database
$db_o = new DB();

// set templates
$tmpl_o = new TEMPLATE(array("$loc/templates/general_tm.php","$loc/templates/utils/layouts_tm.php",
                             "$loc/templates/utils/duty_check_tm.php"));

$pagefields = array(
    "loc" => $loc,
    "theme" => "flatly_",
    "stylesheet" => "$loc/style/rm_utils.css",
    "title" => "duty_check",
    "header-left" => "raceManager",
    "header-right" => "Duty Check",
    "footer-left" => "",
    "footer-center" => "",
    "footer-right" => "",
);


if (empty($_REQUEST['pagestate'])) { $_REQUEST['pagestate'] = "init"; }

/* ------------ file selection page ---------------------------------------------*/
$state = 0;
if ($_REQUEST['pagestate'] == "init")
{
    // setup debug
    array_key_exists("debug", $_REQUEST) ? $params['debug'] = $_REQUEST['debug'] : $params['debug'] = "off" ;

    $formfields = array(
        "instructions" => "Create a report summarising duty allocations</br>
                       <small>Please select the start and end date for the period you want to analyse and the rotas you want to include.</small>",
        "script" => "duty_check.php?pagestate=submit",
    );

    $params["duty_types"] = array();
    $rs = $db_o->db_getsystemcodes("rota_type");
    //echo "<pre>".print_r($rs,true)."</pre>";
    foreach ($rs as $row)
    {
        $params["duty_types"][$row['code']] = $row['label'];
    }
    //echo "<pre>".print_r($params['duty_types'],true)."</pre>";

    // present form to select json file for processing (general template)
    $pagefields["body"] =$tmpl_o->get_template("duty_check_form", $formfields, $params);

    // render page
    echo $tmpl_o->get_template("basic_page", $pagefields);
}

/* ------------ submit page ---------------------------------------------*/

elseif (strtolower($_REQUEST['pagestate']) == "submit")
{
   // echo "<pre>".print_r($_REQUEST,true)."</pre>";

    $rota_o = NEW ROTA($db_o);
    $event_o = new EVENT($db_o);


    // check parameters from form
    $state = 0;
    $all = false;
    // dates valid
    if (strtotime($_REQUEST['date-start']) > strtotime($_REQUEST['date-end']))
    {
        $state = 3;    // end date is before start date
    }

    // check we have events in specified period
    $events = $event_o->get_events_inperiod(array(), $_REQUEST['date-start'], $_REQUEST['date-end'], "live", false);
    if (!$events)
    {
        $state = 1;   // no events in selected period
    }

    $duties = $rota_o->get_duties_inperiod(array(), $_REQUEST['date-start'], $_REQUEST['date-end']);
    // check we have duties in specified period
    if (!$duties)
    {
        $state = 4;   // no duties in period
    }

    //echo "state: $state<br>";

    if ($state == 0)
    {
        // check which rotas specified - if empty or "all" included - assume all rotas
        empty($_REQUEST['rotas']) ? $rotas = array() : $rotas = $_REQUEST['rotas'];

        // find people in t_rotamember that match the rotas specified - remove duplicates
        $persons = $rota_o->get_rota_members($rotas, $duplicates = false);
        //echo "<pre> persons" . print_r($persons, true) . "</pre>";

        if ($persons) {
            // loop through people getting duties they have been scheduled for - add information to person array
            foreach ($persons as $k => $person) {
                $name = $person['firstname'] . " " . $person['familyname'];
                $duties = $rota_o->get_duties_inperiod(array("person" => $name), $_REQUEST['date-start'], $_REQUEST['date-end']);

                if ($duties) {
                    $duty_list = "";
                    $duty_count = 0;
                    $ref_date = "01-01-1970";
                    foreach ($duties as $j => $duty) {
                        if (strtotime($ref_date) != strtotime($duty['event_date'])) {
                            $duty_count++;
                            $duty_list .= "|{$duty['dutyname']}: " . date("j M", strtotime($duty['event_date'])) . " ({$duty['event_name']})";
                            $ref_date = $duty['event_date'];
                        }

                    }
                    $persons[$k]['duties'] = ltrim($duty_list, "|");
                    $persons[$k]['numevents'] = count($duties);
                    $persons[$k]['numduties'] = $duty_count;
                } else {
                    $persons[$k]['duties'] = "";
                    $persons[$k]['numevents'] = 0;
                    $persons[$k]['numduties'] = 0;
                }

            }

            $pagefields["date"] = date("Y-m-d");
            $pagefields["year"] = date("Y", strtotime($_REQUEST['date-start']));
            $pagefields["body"] = $tmpl_o->get_template("duty_check_report", $pagefields, array("data" => $persons));
            // fixme complete report
            echo $tmpl_o->get_template("basic_page", $pagefields);
        }
        else
        {
            $state = 5;
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
        "title" => "duty_check",
        "header-left" => "raceManager",
        "header-right" => "Event Card",
        "body" => "",
        "footer-left" => "",
        "footer-center" => "",
        "footer-right" => ""
    );
    $pagefields['body'] = $tmpl_o->get_template("duty_check_state", array(), array("state"=>$state));

    // render page
    echo $tmpl_o->get_template("basic_page", $pagefields, array());
}







