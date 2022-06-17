<?php
/*
 * duty_check.php
 * Creates report of duty allocations for each member in specified time period.
   Report can include all members or members of specified rotas
 */
$loc  = "..";
$page = "duty_check";     //
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
$styletheme = "flatly_";
$stylesheet = "./style/rm_utils.css";

require_once ("{$loc}/common/lib/util_lib.php");

session_start();

// initialise session if this is first call
if (!isset($_SESSION['util_app_init']) OR ($_SESSION['util_app_init'] === false))
{
    $init_status = u_initialisation("$loc/config/racemanager_cfg.php", "$loc/config/rm_utils_cfg.php", $loc, $scriptname);

    if ($init_status)
    {
        // set timezone
        if (array_key_exists("timezone", $_SESSION)) { date_default_timezone_set($_SESSION['timezone']); }

        // start log
        error_log(date('H:i:s')." -- EVENT CARD --------------------".PHP_EOL, 3, $_SESSION['syslog']);

        // set initialisation flag
        $_SESSION['util_app_init'] = true;
    }
    else
    {
        u_exitnicely($scriptname, 0, "one or more problems with script initialisation",
            "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
    }
}

require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/rota_class.php");
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/template_class.php");

// connect to database
$db_o = new DB();

// get duty type codes
$duty_codes = array();
$rs = $db_o->db_getsystemcodes("rota_type");
foreach ($rs as $row)
{
    $duty_codes[$row['code']] = $row['label'];
}

// set templates
$tmpl_o = new TEMPLATE(array("$loc/common/templates/general_tm.php","./templates/layouts_tm.php", "./templates/duty_check_tm.php"));

$pagefields = array(
    "loc" => $loc,
    "theme" => $styletheme,
    "stylesheet" => $stylesheet,
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

    // present form to select json file for processing (general template)
    $pagefields["body"] =$tmpl_o->get_template("duty_check_form", $formfields, $params=array("duty_types" => $duty_codes));

    // render page
    echo $tmpl_o->get_template("basic_page", $pagefields);
}

/* ------------ submit page ---------------------------------------------*/

elseif (strtolower($_REQUEST['pagestate']) == "submit")
{
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

    if ($state == 0)
    {
        // check which rotas specified - if empty or "all" included - assume all rotas
        if (!array_key_exists("rotas", $_REQUEST))
        {
            $rotas = array();
            $rota_str = "all rotas";
        }
        else
        {
            if (in_array("all", $_REQUEST['rotas']))
            {
                $rotas = array();
                $rota_str = "all rotas";
            }
            else
            {
                $rotas = $_REQUEST['rotas'];
                $rota_str = "";
                foreach ($rotas as $rota)
                {
                    $rota_str.= $duty_codes[$rota].", ";
                }
                $rota_str = rtrim($rota_str, ", ");
            }
        }

        // find people in t_rotamember that match the rotas specified - remove duplicates
        $persons = $rota_o->get_rota_members($rotas, $duplicates = false);

        if ($persons)
        {
            // loop through people getting duties they have been scheduled for - add information to person array
            foreach ($persons as $k => $person)
            {
                $name = $person['firstname'] . " " . $person['familyname'];
                $duties = $rota_o->get_duties_inperiod(array("person" => $name), $_REQUEST['date-start'], $_REQUEST['date-end']);

                if ($duties)
                {
                    $duty_list = "";
                    $duty_count = 0;
                    $ref_date = "01-01-1970";
                    foreach ($duties as $j => $duty) {
                        if (strtotime($ref_date) != strtotime($duty['event_date']))
                        {
                            $duty_count++;
                            $duty_list .= "|{$duty['dutyname']}: " . date("j M", strtotime($duty['event_date'])) . " ({$duty['event_name']})";
                            $ref_date = $duty['event_date'];
                        }
                    }
                    $persons[$k]['duties'] = ltrim($duty_list, "|");
                    $persons[$k]['numevents'] = count($duties);
                    $persons[$k]['numduties'] = $duty_count;
                }
                else
                {
                    $persons[$k]['duties'] = "";
                    $persons[$k]['numevents'] = 0;
                    $persons[$k]['numduties'] = 0;
                }
            }

            $pagefields["date"] = date("Y-m-d");
            $pagefields["year"] = date("Y", strtotime($_REQUEST['date-start']));
            $pagefields["rotas"] = $rota_str;
            $pagefields["body"] = $tmpl_o->get_template("duty_check_report", $pagefields, array("data" => $persons));
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
    $state = 2;  // error pagestate not recognised
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







