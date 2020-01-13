<?php
/* ----------------------------------------------------------------------------------------------

TO DO
- should I move the schedule information into the event/series structures DONE
- rationalise cfg content - remove unused parameters
- convert to a script + class (& remove relevant functions into util.lib)
- add validation tests
- report validation errors
*/

/*
 DATA MODEL

 pg --- programmable days/events (may be more than one event per day).  This is the array that the import file is
        created from.

        status     - programmed status (X - not programmed, S - scheduled event, P - protected date (not schedule-able), U - not scheduled
        start_time - start time (hh:mm)
        event_name" => $series['name'],
        series_code" => $series['type']['code']."-".year
        type       - type of event - must match one of racemanager even codes - set in cfg.json file
        format     - event format - typically the race format defined in racemanager - set in cfg.json file
        entry_type - how do people enter the event (<blank>|on/retire|ood) - set in cfg.json file
        restricted - who is the event accessible to (club|open) - currently not used
        notes      - notes that will appear in programme - currently not used
        code       - index code for this event
        weblink    - url associated with event - currently not used

Assumes that tidal data is held in table t_tide and HW times have been converted to local time

 */

$loc  = "..";
$page = "programmeMaker";
$scriptname = basename(__FILE__);
$today = date("Y-m-d");

require_once ("{$loc}/common/lib/util_lib.php");

session_start();
unset($_SESSION);

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
        error_log(date('H:i:s')." -- PMAKER --------------------".PHP_EOL, 3, $_SESSION['syslog']);
    }
    else
    {
        u_exitnicely($scriptname, 0, "initialisation failure", "one or more problems with pmaker initialisation");
    }
}

require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/tide_class.php");
require_once ("{$loc}/common/classes/template_class.php");

// connect to database
$db_o = new DB();
$tide_o = new TIDE($db_o);

// set templates
$tmpl_o = new TEMPLATE(array("$loc/templates/general_tm.php","$loc/templates/utils/layouts_tm.php", "$loc/templates/utils/programmemaker_tm.php"));

$_SESSION['pagefields'] = array(
    "loc" => $loc,
    "theme" => "flatly_",
    "stylesheet" => "$loc/style/rm_utils.css",
    "title" => "programmemaker",
    "header-left" => "raceManager",
    "header-right" => "programmeMaker",
    "body" => "",
    "instructions" => "Create a json configuration file for your annual programme as described in the manual. 
                       A template for this file is provided - see <code>pmaker_config.json</code> in the
                        <code>install/import_templates</code> directory.  
                       It is a good idea to check the file for valid json using <code>https://jsonlint.com/</code>.",
    "file-types" => ".json",
    "footer-left" => "",
    "footer-center" => "",
    "footer-right" => "",
);


if (empty($_REQUEST['pagestate'])) { $_REQUEST['pagestate'] = "init"; }

/* ------------ file selection page ---------------------------------------------*/

if ($_REQUEST['pagestate'] == "init")
{
    // setup debug
    array_key_exists("debug", $_REQUEST) ? $params['debug'] = $_REQUEST['debug'] : $params['debug'] = "off" ;

    // present form to select json file for processing (general template)
    $_SESSION['pagefields']['body'] =  $tmpl_o->get_template("upload_pmaker_file", $_SESSION['pagefields'], $params);

    // render page
    echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields'] );
}


/* ------------ submit page ---------------------------------------------*/

elseif (strtolower($_REQUEST['pagestate']) == "submit")
{
    $bufr = "";
    $json_err = array();
    $json_file = $_FILES['pmakerfile']['name'];
    if (array_key_exists("debug", $_REQUEST))
    {
        $_REQUEST['debug'] == "on" ? $_SESSION['debug'] = true : $_SESSION['debug'] = false ;
    }

    $cfg = json_decode(file_get_contents($_FILES['pmakerfile']['tmp_name']), true);
    if ($cfg)
    {
        $cfg['settings']['year'] = date("Y", strtotime($_REQUEST['date-start']));
        $_SESSION['types'] = explode("|", $cfg['settings']['types']);
        //echo "<pre>".print_r($cfg,true)."</pre>";
        $json_err = pg_config_validation();
    }
    else
    {
        $json_err[] = "json configuration file not read<br><br>file detail:<pre>".print_r($_FILES['pmakerfile'],true)."</pre> ";
    }

    if (!empty($json_err))
    {
        //report errors and stop
        $bufr.= $tmpl_o->get_template("report_pmaker_cfg_err", $_SESSION['pagefields'], $json_err);
    }
    else
    {
        // create list of events to be scheduled that are relevant to the period to be programmed
        $cfgevent = pg_get_event_list($_REQUEST['date-start'], $_REQUEST['date-end']);

        // create list of days in week that can have events scheduled
        $days_of_week = pg_get_schedule_days($_REQUEST['date-start'], $_REQUEST['date-end']);

        $pg = array();
        // get days which will potentially have scheduled events and create empty records
        pg_get_days($_REQUEST['date-start'], $_REQUEST['date-end'], $days_of_week);


        // add days for one off events on fixed days if they don't exist
        pg_get_fixed_dates($_REQUEST['date-start'], $_REQUEST['date-end'], $cfg);


        if ($_SESSION['debug']) {echo "--- BLANK PROGRAMME CREATED -----------------------------------<br>";}
        // allocate fixed (open) events
        if ($_SESSION['debug']) {echo "--- OPEN MEETINGS / SPECIAL EVENTS ADDED ----------------------<br>";}
        pg_allocate_open_events();

        // allocated series races to programme days
        if ($_SESSION['debug']) {echo "--- SERIES EVENTS ADDED ----------------------------------------<br>";}
        pg_allocate_series_events();

        // now allocate individual events - replacing series events if necessary
        if ($_SESSION['debug']) {echo "--- SINGLE EVENTS (RACING) ADDED -------------------------------<br>";}
        pg_allocate_fixed_events();

        if ($_SESSION['debug']) {echo "--- SINGLE EVENTS (NON_RACING) ADDED ---------------------------<br>";}
        pg_allocate_fixed_nonracing();
        if ($_SESSION['debug']) {echo "<pre>".print_r($pg,true)."</pre>";}

        // now check if any days with just a series event could have more than one event
        if ($cfg['settings']['multiple_races']['include'] == "yes")
        {
            if ($_SESSION['debug']) {echo "--- ADDING EXTRA SERIES EVENTS ON GOOD TIDE DAYS -----------<br>";}
            $races_added = pg_allocate_multi_events();
            if ($_SESSION['debug']) {echo " - races added - $races_added<br>";}
        }

        // add tides for unscheduled event days (settings['fixed_days]
        if ($_SESSION['debug']) {echo "--- ADDING MISSING TIDE DATA -----------------------------------<br>";}
        pg_add_missing_tides($cfg['settings']['fixed_days']);

        // create csv file
        if ($_SESSION['debug']) {echo "--- CREATING CSV FILE ------------------------------------------<br>";}
        $csv_status = pg_create_csv();

        // produce table of events for review
        if ($_SESSION['debug']) {echo "--- CREATING REPORT --------------------------------------------<br>";}
        $bufr .= pg_display_events($_REQUEST['date-start'], $_REQUEST['date-end'], $json_file, $csv_status);

        // finally check which events have been scheduled
        foreach ($pg as $k => $event)
        {
            if ($event['state'] != "X")
            {
                foreach ($cfgevent as $j => $cevent) {
                    if ($cevent['code'] == $event['code']) {
                        $cfgevent[$j]['scheduled'] = $cfgevent[$j]['scheduled'] + 1;
                        $cfgevent[$j]['dates'] = $cfgevent[$j]['dates'] . $event['date'] . " | ";
                    }
                }
            }
        }
        foreach ($cfgevent as $j => $cevent)
        {
            $cfgevent[$j]['dates'] = rtrim($cfgevent[$j]['dates'], " |");
        }
        $bufr .= pg_display_event_summary($cfgevent);

        if ($_SESSION['debug'])
        {
            $bufr .= "<pre>Config Events:<br>".print_r($cfgevent,true)."</pre>";
            $bufr .= "<pre>Config Details:<br>".print_r($cfg,true)."</pre>";
            $bufr .= "<pre>Programme Details:<br>".print_r($pg,true)."</pre>";
        }
    }

    // render page
    $_SESSION['pagefields']['body'] = $bufr;
    echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);

}

function pg_add_missing_tides($days)
{
    global $pg;
    global $tide_o;

    foreach ($days as $day => $val)
    {
        foreach ($pg as $k => $event)
        {
            if (strtolower(date('l', strtotime($event['date']))) == strtolower($day))     // matching day found
            {
                if (empty($event['tidal_time']))                      // add tide data
                {
                    $tide = $tide_o->get_tide_by_date($event['date'], false);
                    $ref_time = $val['start'];
                    $time_diff_1 = abs(strtotime($ref_time) - strtotime($tide['hw1_time']));
                    $time_diff_2 = abs(strtotime($ref_time) - strtotime($tide['hw2_time']));
                    if ($time_diff_1 <= $time_diff_2) {
                        $tdata = array(
                            "tidal_time"   => $tide['hw1_time'],
                            "tidal_height" => $tide['hw1_height'],
                            "tidal_num"    => "1",
                            "tidal_status" => ""
                        );
                    }
                    else
                    {
                        $tdata = array(
                            "tidal_time"   => $tide['hw2_time'],
                            "tidal_height" => $tide['hw2_height'],
                            "tidal_num"    => "2",
                            "tidal_status" => ""
                        );
                    }
                    $pg[$k] = array_merge($pg[$k], $tdata);
                }
            }
        }
    }
}

function pg_config_validation()
{
    //FIXME add check to see that there are no duplicate event code values

    global $cfg;

    $error = array();

    // test 1: check start and end dates are same year
    $msg = "";
    if (date("Y", strtotime($_REQUEST['date-start']))!= date("Y", strtotime($_REQUEST['date-end'])))
    {
        $msg.= "- start and end dates for programme must be in same calendar year";
    }

    if (strtotime($_REQUEST['date-start']) >= strtotime($_REQUEST['date-end']))
    {
        $msg.= "- end date for programme is before start date [ {$_REQUEST['date-start']} - {$_REQUEST['date-end']} ]";
    }
    if ($msg) { $error[1] = $msg; }


    // test 2: check all "settings" have values
    $msg = "";
    if (empty($cfg['settings']['signon'])) { $msg.="signon|"; }
    if (empty($cfg['settings']['round_start_mins'])) { $msg.="round_start_mins|"; }
    if (empty($cfg['settings']['tidal'])) { $msg.="tidal|"; }
    if (empty($cfg['settings']['multiple_races'])) { $msg.="tidal|"; }
    if (!empty($msg))
    {
        $msg = rtrim($msg, "|");
        $error[2] = "- some configuration values in the settings section are missing [$msg]";
    }

    // test 3: check we have at the single or series event stuctures defined
    $msg = "";
    if (!array_key_exists('single_events', $cfg) and !array_key_exists('series_events', $cfg))
    {
        $msg.= "- The configuration file does not include either single events or series events";
    }
    if ($msg) { $error[3] = $msg; }

    // test 4: check content of single events
    $msg = "";
    foreach ($cfg['single_events'] as $event)
    {
        $id = $event['name'];

        if ($event['type']['category'] == "racing")
        {
            if (empty($event['type']['format']))
            {
                $msg.= "- The format for a racing event must be defined [$id]<br>";
            }

            // FIXME add test here to check if format is known and active
        }

        if ($event['type']['access'] != "club" and $event['type']['access'] != "open")
        {
            $msg.= "- The access parameter must either be 'club' or 'open' [$id]<br>";
        }

        if ($event['date']['type'] == "fixed" )
        {
            if (empty($event['date']['value']))
            {
                $msg.= "- If the event has a 'fixed' date - the date must be defined in the 'value' parameter [$id]<br>";
            }
        }
        elseif ($event['date']['type'] == "nearest")
        {
            if (empty($event['date']['value']))
            {
                $msg.= "- If the event has a 'nearest' date - the date must be defined in the 'value' parameter [$id]<br>";
            }
            if (empty($event['date']['day']))
            {
                $msg.= "- The week day this event can be programmed must be defined [$id]<br>";
            }
        }
        elseif($event['date']['type'] == "float")
        {
            if (empty($event['date']['earliest']) or empty($event['date']['latest']))
            {
                $msg.= "- If the event has a float date - the earliest and latest dates must be defined [$id]<br>";
            }
            if (empty($event['date']['day']))
            {
                $msg.= "- The week day this event can be programmed must be defined [$id]<br>";
            }
        }
        else
        {
            $msg.= "- The event date type must be either 'fixed', 'nearest' or 'tidal' [$id]<br>";
        }

        if ($event['time']['type'] == "fixed")
        {
            if (empty($event['time']['start']))
            {
                $msg.= "- If the event has a fixed start time - the time must be defined in the 'value' parameter [$id]<br>";
            }
        }
        elseif($event['time']['type'] == "tidal")
        {
            if (empty($event['time']['earliest']) or empty($event['time']['latest']))
            {
                $msg.= "- If the event must be programmed based on a tidal time - the earliest and latest possible times must be defined [$id]<br>";
            }
        }
        else
        {
            $msg.= "- The event time type must be either 'fixed' or 'tidal' [$id]<br>";
        }
    }
    if ($msg) { $error[4] = $msg; }

    // test 5: check content of series events
    $msg = "";
    foreach ($cfg['series_events'] as $event)
    {
        $id = $event['name'];
        if ($event['type']['category'] == "racing")
        {
            if (empty($event['type']['format']))
            {
                $msg.= "- The format for a racing event must be defined [$id]<br>";
            }

            // FIXME add test here to check if format is known and active
        }

        if ($event['type']['access'] != "club" and $event['type']['access'] != "open")
        {
            $msg.= "- The access parameter must either be 'club' or 'open' [$id]<br>";
        }

        if ($event['date']['type'] == "fixed")
        {
            if(empty($event['date']['value']))
            {
                $msg.= "- If the series has a fixed/nearest date - the date must be defined in the 'value' parameter [$id]<br>";
            }
        }
        elseif($event['date']['type'] == "float")
        {
            if (empty($event['date']['earliest']) or empty($event['date']['latest']))
            {
                $msg.= "- If the series has a float date - the earliest and latest dates must be defined [$id]<br>";
            }
        }
        else
        {
            $msg.= "- The event date type must be either 'fixed' or 'tidal' [$id]<br>";
        }

        if (empty($event['date']['day']))
        {
            $msg.= "- The week day this event can be programmed must be defined [$id]<br>";
        }

        if ($event['time']['type'] == "fixed")
        {
            if (empty($event['time']['start']))
            {
                $msg.= "- If the event has a fixed start time - the time must be defined in the 'value' parameter [$id]<br>";
            }
        }
        elseif($event['time']['type'] == "tidal")
        {
            if (empty($event['time']['earliest']) or empty($event['time']['latest']))
            {
                $msg.= "- If the event must be programmed based on a tidal time - the earliest and latest possible times must be defined [$id]<br>";
            }
        }
        else
        {
            $msg.= "- The event time type must be either 'fixed' or 'tidal' [$id]<br>";
        }
    }
    if ($msg) { $error[5] = $msg; }

    // test 6 - check for unique identifiers
    $msg = "";

    $arr = array();
    foreach ($_SESSION['types'] as $type)
    {
        $arr = array_merge($arr, $cfg[$type]);
    }
//    $arr = array_merge($cfg['single_events'], $cfg['series_events'], $cfg['special_events'], $cfg['open_events']);
    $keys = array_keys($arr);
    $keys_u = array_unique($keys);
    if (count($keys) != count($keys_u))
    {
        $msg.= "- the configuration file has a duplicate event identifier ";
    }
    if ($msg) { $error[6] = $msg; }

    return $error;
}

function pg_get_event_list($start_date, $end_date)
{
    global $cfg;
    $el = array();

    foreach ($_SESSION['types'] as $type)
    {
        foreach($cfg[$type] as $k=>$event)
        {
            $str_arr = explode("_", $type);
            $type_code = $str_arr[0];
            if ($event['status'] == "schedule" or $event['status'] == "report")
            {
                $set = false;
                if ( !empty($event['date']['value']))
                {
                    // convert date to the programme year
                    $event['date']['value'] = $event['date']['value']."-".$cfg['settings']['year'];
                    $cfg[$type][$k]['date']['value'] = $event['date']['value'];

                    if (in_period($start_date, $end_date, $event['date']['value']))
                    {
                        $set = true;
                    }
                }

                if ( !empty($event['date']['earliest']) and !empty($event['date']['latest']))
                {
                    // convert dates to the programme year
                    $event['date']['earliest'] = $event['date']['earliest']."-".$cfg['settings']['year'];
                    $cfg[$type][$k]['date']['earliest'] = $event['date']['earliest'];
                    $event['date']['latest'] = $event['date']['latest']."-".$cfg['settings']['year'];
                    $cfg[$type][$k]['date']['latest'] = $event['date']['latest'];

                    if (period_overlap($start_date, $end_date, $event['date']['earliest'], $event['date']['latest']))
                    {
                        $set = true;
                    }
                }

                if ($set)
                {
                    $el[] = array("name"=>$event['name'], "code"=>$k, "type"=>$type_code,
                        "category"=>$event['type']['category'], "scheduled"=>0, "dates"=>"");
                }
            }
        }
    }

    if (empty($el)) { return false; }

    return $el;
}

function pg_get_schedule_days($start_date, $end_date)
{
    global $cfg;

    $days = array();

    foreach($cfg['single_events'] as $k=>$event)
    {
        if ($event['date']['type'] != "fixed")
        {
            if ($event['date']['type'] == "float")
            {
                if (period_overlap($start_date, $end_date, $event['date']['earliest'], $event['date']['latest']))
                {
                    $d = explode("|", $event['date']['day']);
                    $days  = array_unique(array_merge($days, $d));
                }
            }
            elseif ($event['date']['type'] == "nearest")
            {
                if (in_period($start_date, $end_date, $event['date']['value']))
                {
                    $d = explode("|", $event['date']['day']);
                    $days  = array_unique(array_merge($days, $d));
                }
            }
        }
    }

    foreach($cfg['series_events'] as $k=>$event)
    {
        if (period_overlap($start_date, $end_date, $event['date']['earliest'], $event['date']['latest']))
        {
            if ($event['date']['type'] != "fixed")
            {
                $d = explode("|", $event['date']['day']);
                $days  = array_unique(array_merge($days, $d));
            }
        }
    }

    return $days;
}

function pg_get_days($start_date, $end_date, $days)
{
    global $pg;
    global $cfg;

    $blank = array(
        "date"        => "",
        "event_name"  => "",
        "event_cfg"   => "",
        "start_time"  => "",
        "tidal_time"  => "",
        "tidal_height"=> "",
        "tidal_num"   => "",
        "series_code" => "",
        "type"        => "",
        "format"      => "",
        "entry_type"  => "",
        "restricted"  => "",
        "tidal_status"=> "",
        "code"        => "",
        "state"       =>"X");

    $start = strtotime($start_date);
    $end   = strtotime($end_date);
    $current = $start;

    while ($current <= $end)
    {
        $date = date("Y-m-d", $current);
        // check if this week day is scheduled
        $day_of_week = strtolower(date("l", $current));

        $create_record = false;
        if (in_array($day_of_week, $days))
        {
            foreach ($cfg['series_events'] as $event)
            {
                if ($event['date']['day'] == $day_of_week and
                    in_period($event['date']['earliest'], $event['date']['latest'], $date))
                { $create_record = true; }
            }

            foreach ($cfg['single_events'] as $event)
            {
                if (!empty($event['date']['value']))
                {
                    if (strtotime($date) == strtotime($event['date']['value'])) { $create_record = true; }
                }
                else
                {
                    if ($event['date']['day'] == $day_of_week and
                        in_period($event['date']['earliest'], $event['date']['latest'], $date))
                    { $create_record = true; }
                }
            }

            if ($create_record)
            {
                $d['date'] = date("Y-m-d",$current);
                $pg["{$d['date']}"] = array_merge($blank, $d);
            }
        }
        $current = strtotime("+1 day", $current);
    }
    return;
}

function pg_allocate_open_events()
{
    global $cfg;
    global $pg;

    foreach ($cfg['open_events'] as $j => $open)
    {
        if ($_SESSION['debug']) {echo "++++ {$open['name']}<br>";}

        if (strtolower($open['status']) == "schedule") {
            if (!empty($open['date']['value'])) {
                $num_events = 0;
                $num_days = 0;
                $date = $open['date']['value'];   // set initial date

                if (in_period($_REQUEST['date-start'], $_REQUEST['date-end'], $date)) {
                    // loop over number of days for event
                    while ($num_days < $open['date']['num_days']) {
                        $tidal = assess_tide($open, $date);
                        //echo "<pre>".print_r($tidal,true)."</pre>";
                        $event_data = allocate_event("open", $open, $tidal, $j, $open['time']['start_delta']);
                        //echo "<pre>".print_r($event_data,true)."</pre>";

                        if ($event_data) {
                            $k = is_date_in_programme($date);

                            if ($k) {
                                if ($_SESSION['debug']) {
                                    echo " - overwriting $date<br>";
                                }
                                $pg[$k] = array_merge($pg[$k], $event_data);
                                $num_days++;
                                $num_events++;

                            } else {
                                if ($_SESSION['debug']) {
                                    echo " - creating new $date<br>";
                                }
                                $event_data['date'] = date("Y-m-d", strtotime($date));
                                $pg[] = $event_data;
                                $num_days++;
                                $num_events++;
                            }
                            // add additional events on that day
                            $start_time = $event_data['start_time'];
                            while ($num_events < $open['type']['num_events']) {
                                $event_data['date'] = date("Y-m-d", strtotime($date));
                                // change start time
                                $start_time = date('H:i', strtotime($start_time . '+1 hour'));
                                if ($_SESSION['debug']) {
                                    echo "start: $start_time <br>";
                                }
                                $event_data['start_time'] = $start_time;
                                $pg[] = $event_data;
                                $num_events++;
                                if ($_SESSION['debug']) {
                                    echo "adding extra event (events: $num_events, days: $num_days)<br>";
                                }
                            }

                            // get next date
                            $num_events = 0;
                            $date = date('Y-m-d', strtotime($date . ' +1 day'));
                        } else   // not getting event data so stop
                        {
                            break;
                        }
                    }
                }
            }
        }
    }
    return;
}

function pg_allocate_series_events()
{
    global $cfg;
    global $pg;

    // FIXME how does this work for non-tidal series
    foreach ($pg as $k=>$event)                              // loop over each day to be scheduled
    {
        foreach ($cfg['series_events'] as $j => $series)     // loop over each series in cfg
        {
            if (strtolower($series['status']) == "schedule" and $event['state'] == "X")
            {
                if (in_period($series['date']['earliest'],
                    $series['date']['latest'], $event['date']))  // is this day within the series start/end
                {
                    if (in_series($series, $event['date']))      // does this day match the series day requirement in cfg
                    {
                        $tidal = assess_tide($series, $event['date']);  // assess tide
                        if ($series['type']['category'] == "racing" )
                        {
                            if (!empty($tidal) and
                                tide_better_than($event['date'], "X", $tidal['status']))  // check is useful tide
                            {
                                $event_data = allocate_event("series", $series, $tidal, $j);
                                if ($event_data) { $pg[$k] = array_merge($pg[$k], $event_data);}
                            }
                        }
                        elseif ($series['type']['category'] == "freesail")
                        {
                            if (!empty($tidal) and
                                tide_better_than($event['date'], "M", $tidal['status']))  // check is useful tide
                            {
                                $event_data = allocate_event("freesail", $series, $tidal, $j);
                                if ($event_data)
                                {
                                    if ($event['state'] != "X")
                                    {
                                        $event_data['date'] = $event['date'];
                                        $pg[] = $event_data;
                                    }
                                    else
                                    {
                                        $pg[$k] = array_merge($pg[$k], $event_data);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    return;
}

function pg_allocate_fixed_nonracing()
{
    global $cfg;
    foreach ($cfg['single_events'] as $j => $event)
    {
        if ($_SESSION['debug']) {echo "++++ {$event['name']}<br>";}

        if (strtolower($event['status'] == "schedule") and
            ( $event['type']['category'] == "social" or $event['type']['category'] == "training"))
        {
            $date = $event['date']['value'];

            if ($event['date']['type'] == "fixed")
            {
                if (in_period($_REQUEST['date-start'], $_REQUEST['date-end'], $date))
                {
                    $allocated = allocated_fixed_nonracing($event, $date, $j);
                }
                if ($_SESSION['debug']) {echo " - fixed: $date allocated: $allocated<br>";}
            }
            elseif  ($event['date']['type'] == "nearest")
            {
                $weekday = $event['date']['day'];
                $nearest_day = get_nearest_day($weekday, $date);                 // get nearest day
                if (in_period($_REQUEST['date-start'], $_REQUEST['date-end'], $nearest_day)) {
                    $allocated = allocated_fixed_nonracing($event, $nearest_day, $j);     // try to allocate it
                    if ($_SESSION['debug']) {
                        echo " - nearest: $nearest_day allocated: $allocated<br>";
                    }
                }
            }
        }
    }
}

function pg_allocate_fixed_events()
{
    global $cfg;

    // deal with fixed date events
    foreach ($cfg['single_events'] as $j => $event)
    {
        if (strtolower($event['status'] == "schedule") and
            $event['date']['type'] == "fixed" and
            $event['type']['category'] == "racing")
        {
            $date = $event['date']['value'];
            if (in_period($_REQUEST['date-start'], $_REQUEST['date-end'], $date))
            {
                if ($_SESSION['debug']) {echo "++++ {$event['name']}<br>";}
                $allocated = allocated_fixed($event, $date, "X", $j);
            }
        }
    }
    sort_programme_by_date(false);   // resort programme - don't remove duplicates

    // now deal with 'nearest' and 'float' events
    foreach ($cfg['single_events'] as $j => $event)
    {
        if (strtolower($event['status'] == "schedule") and
            $event['date']['type'] == "nearest"  and
            $event['type']['category'] == "racing")
            // tries to allocate nearest relevant  day - if that is not a good tide tries the next nearest day
            // if still bad tide - give up
        {


            $date = $event['date']['value'];
            if (in_period($_REQUEST['date-start'], $_REQUEST['date-end'], $date))
            {
                if ($_SESSION['debug']) {echo "++++ {$event['name']}<br>";}
                $weekday = $event['date']['day'];
                $nearest_day = get_nearest_day($weekday, $date);                 // get nearest day
                $allocated = allocated_fixed($event, $nearest_day, "M", $j);     // try to allocate i

                strtotime($nearest_day) >= $date ? $delta_dir = "+" : $delta_dir = "-";
                $trial_date = $nearest_day;
                $delta = 1;
                $give_up = 6;
                while (!$allocated)  // tide not ok - iterate on previous and next matching days
                {
                    $trial_date = get_relative_day($weekday, $trial_date, "$delta_dir"."$delta weeks");

                    $allocated = allocated_fixed($event, $trial_date, "M", $j);
                    if ($_SESSION['debug']) {echo " - nearest: $nearest_day trial: $trial_date allocated: $allocated<br>";}

                    if (!$allocated)
                    {
                        $delta++;
                        if ($delta > $give_up) { break; }
                        $delta_dir == "-" ? $delta_dir = "+" : $delta_dir = "-";
                    }
                }
            }
        }

        elseif (strtolower($event['status'] == "schedule") and
                $event['date']['type'] == "float" and
                $event['type']['category'] == "racing")
            // allocates floating even to first suitable day after the 'earliest' date specified
        {
            if (period_overlap($_REQUEST['date-start'], $_REQUEST['date-end'], $event['date']['earliest'], $event['date']['latest']))
            {
                if ($_SESSION['debug']) {echo "++++ {$event['name']}<br>";}
                $weekday = $event['date']['day'];

                $allocated = false;
                $sch_date = get_next_day($weekday, $event['date']['earliest']);
                while (!$allocated)
                {
                    if (!in_period($event['date']['earliest'], $event['date']['latest'],$sch_date)) { break; } // give up

                    $allocated = allocated_fixed($event, $sch_date, "M", $j);

                    if (!$allocated) { $sch_date = get_next_day($weekday, $sch_date); }
                }
            }
        }
    }
    return;
}

function pg_allocate_multi_events ()
{
    // looks for days wih only 1 series race scheduled - if tide is suitable will
    // add another race and adjust start times accordingly
    // FIXME (future) - will only work up to two races per day and with a series event
    global $cfg;
    global $pg;

    $new_races = 0;

    foreach ($pg as $k=>$event)
    {

        // check that a race is currently scheduled for this date, it is a race and a series event
        $rs = all_events_for_date_in_programme($event['date']);
        $num_events = count($rs);

        foreach ($rs as $j=>$ev)
        {
            // must be a racing event and a series event
            if ($ev['type'] == "racing" and $ev['event_cfg'] == "series")
            {
                if (tide_better_than($event['date'], $cfg['settings']['multiple_races']['tide'], $ev['tidal_status']))
                {
                    // must not exceed max number of races per day and not be a fixed start time series
                    if ($num_events < $cfg['series_events']["{$event['code']}"]['time']['max_starts'] and
                        $cfg['series_events']["{$event['code']}"]['time']['type'] != "fixed")
                    {
                        // move start time of current race to (settings: first_start) before HW
                        $dtime = decode_delta_time($cfg['settings']['multiple_races']['first_start']);

                        if ($dtime['dir'] == "-")
                        {
                            $s = (new DateTime($ev['tidal_time']))->sub(new DateInterval("PT{$dtime['hrs']}H{$dtime['min']}M"));
                        }
                        else
                        {
                            $s = (new DateTime($ev['tidal_time']))->add(new DateInterval("PT{$dtime['hrs']}H{$dtime['min']}M"));
                        }

                        //  set into record
                        $start_time_r1 = u_roundminutes($s->format("H:i"), $cfg['settings']['round_start_mins']);
                        $pg[$k]['start_time'] = $start_time_r1;

                        // add another race with a start time(settings: interval) after the first race)
                        $dtime = decode_delta_time($cfg['settings']['multiple_races']['interval']);
                        $s = (new DateTime($start_time_r1))->add(new DateInterval("PT{$dtime['hrs']}H{$dtime['min']}M"));
                        $start_time_r2 = u_roundminutes($s->format("H:i"), $cfg['settings']['round_start_mins']);
                        $ev['start_time'] = $start_time_r2;

                        // create new record
                        $pg[] = $ev;
                        $num_events++;
                        $new_races++;
                    }
                }
            }
        }
//        }
    }
    sort_programme_by_date(false);    // don't remove duplicates
    return $new_races;
}

function pg_create_csv()
{
    global $pg;
    global $cfg;

    $status = 0;
    $filename = "../tmp/{$cfg['file']}";

    // open file - deleting old
    $file = fopen($filename, 'w');
    if ( !$file ) { $status ="1"; }  // file not opened

    // write header
//    $hdr_arr = array("id","date","time","name","series","type","format","entry_type",
//        "restricted","tide_time","tide_height","notes","weblink");
    $hdr_arr = array("id","event_date","event_start","event_name","series_code","event_type",
                      "event_format","event_entry","event_open","tide_time","tide_height","event_notes","weblink");
    $write = fputcsv($file, $hdr_arr);
    if ( $write === false ) { $status ="2"; }  // header error

    // write data
    foreach ($pg as $k=>$event)
    {
        // output if array record has tidal data
        if (!empty($event['tidal_time']))
        {
            empty($event['event_name']) ? $type = "noevent" : $type = $event['type'];

            $out_arr = array (
                "id"         => "",
                "date"       => $event['date'],
                "time"       => $event['start_time'],
                "name"       => $event['event_name'],
                "series"     => $event['series_code'],
                "type"       => $type,
                "format"     => $event['format'],
                "entry_type" => $event['entry_type'],
                "restricted" => $event['restricted'],
                "tide_time"  => $event['tidal_time'],
                "tide_height"=> sprintf('%0.1f', $event['tidal_height']),
                "notes"      => "",
                "weblink"    => ""
            );
            $write = fputcsv($file, $out_arr);
            if ( $write === false ) { $status ="3"; }  // data error
        }
    }
    fclose($file);

    return $status;
}

function pg_display_events($start, $end, $file, $csv_status)
{
    global $pg;
    global $cfg;

    $today = date("jS M Y H:i");
    $bufr = "";

    if ($csv_status == 0)
    {
        $file = str_replace("date", date("YmdHi"), $_SESSION['pmaker']['export_file']);
        $path = $_SESSION['pmaker']['loc'];
        $csv_file = $path."/".$file;
        $bufr.=<<<EOT
        <div class="pull-right"><a class="btn btn-primary btn-lg" href="$csv_file" role="button" >Create Import File</a></div>
EOT;

    }
    else
    {
        $csv_file = "";
        $message = "";
        if ($csv_status == 1) { $message = "ERROR: export file could not be created - please contact your system administrator"; }
        elseif ($csv_status == 2) { $message = "ERROR: writing header to export file - please contact your system administrator"; }
        elseif ($csv_status == 3) { $message = "ERROR: writing programme data to export file - please contact your system administrator";}

        if (!empty($message))
        {
            $bufr.= <<<EOT
            <div><p class="bg-danger">$message</p></div>
EOT;
        }
    }

    // header
    $bufr.= <<<EOT
    <h2>Draft Programme</h2>
    <p class="text-info">period: <b>$start - $end</b></p>
    <p class="text-info">config: <b>$file</b></p> 
    <p class="text-info">created: <b>$today</b></p>
    <table class="table table-striped table-condensed table-hover">
    <tbody>
    <thead>
        <tr>
            <th width="15%">Date</th>
            <th>Event</th>
            <th>Start</th>
            <th width="10%">Tide</th>
            <th>Series</th>
            <th>Type</th>
            <th>Format</th>
            <th>Entry</th>
            <th>Access</th>
            <th>Tide Status</th>
        </tr>
    </thead>
EOT;
    foreach ($pg as $ev)
    {
        $weekday = date("D", strtotime($ev['date']));
        empty($ev['tidal_time']) ? $tide_str = "" : $tide_str = "{$ev['tidal_time']} {$ev['tidal_height']}m";
        empty($ev['tidal_status']) ? $tidal_status = "" : $tidal_status = $ev['tidal_status'] ;
        $ev['state'] == "X" ? $start_str = "" : $start_str = $ev['start_time'];

        $bufr.= <<<EOT
        <tr>
            <td>{$ev['date']} <i>($weekday)</i></td>
            <td>{$ev['event_name']}</td>
            <td>$start_str</td>
            <td>$tide_str</td>
            <td>{$ev['series_code']}</td>
            <td>{$ev['type']}</td>
            <td>{$ev['format']}</td>
            <td>{$ev['entry_type']}</td>
            <td>{$ev['restricted']}</td>
            <td>$tidal_status</td>
        </tr>
EOT;
    }

    $bufr.= <<<EOT
    </tbody>
    </table>
    <p class="text-info">tide status codes: E: excellent, G: good, M: marginal, X-1: no tide data, X-2: racing not possible</p>
    <p class="text-info">Config file: $file &nbsp;&nbsp;&nbsp; Export file: $csv_file</p>
    <p class="text-info">Created: $today </p>
EOT;
    return $bufr;
}

function pg_display_event_summary($cfgevent)
{
    $bufr = "<br><br><hr><br>";

    // header
    $bufr.= <<<EOT
    <h2>Programming Check</h2>
    <table class="table table-condensed table-hover">
    <tbody>
    <thead>
        <tr>
            <th width="30%">Event</th>
            <th width="10%">Type</th>
            <th width="10%">No. Scheduled</th>
            <th width="50%">Dates</th>
        </tr>
    </thead>
EOT;
        foreach ($cfgevent as $ev)
        {
            $ev['scheduled']<1? $style = "danger" : $style = "";
            $bufr.= <<<EOT
        <tr class="$style">
            <td>{$ev['name']}</td>
            <td>{$ev['category']}</td>
            <td>{$ev['scheduled']}</td>
            <td>{$ev['dates']}</td>
        </tr>
EOT;
        }
        $bufr.= <<<EOT
    </tbody>
    </table>
EOT;
    return $bufr;
}

function pg_get_fixed_dates($start_date, $end_date, $cfg)
{
    global $pg;

    //single events
    foreach ($cfg['single_events'] as $k=>$event)
    {
        if ($event['date']['type'] == "fixed" and !empty($event['date']['value']))
        {
            if (in_period($start_date, $end_date, $event['date']['value']))
            {
                $pg[] = array(
                    "date"=>date("Y-m-d",strtotime($event['date']['value'])),
                    "event_name"=> "",
                    "event_cfg"=> "",
                    "start_time"=> "",
                    "tidal_time"=> "",
                    "tidal_height"=> "",
                    "series_code"=> "",
                    "type"=> "",
                    "format"=> "",
                    "entry_type"=> "",
                    "restricted"=> "",
                    "tidal_status"=> "",
                    "state"=>"X"
                );
            }
        }
    }

    //series events
    foreach ($cfg['series_events'] as $k=>$series)
    {
        if ($series['date']['type'] == "fixed" and !empty($series['date']['value']))
        {
            if (in_period($start_date, $end_date, $series['date']['value']))
            {
                $pg[] = array(
                    "date"=>date("Y-m-d",strtotime($series['date']['value'])),
                    "event_name"=> "",
                    "event_cfg"=> "",
                    "start_time"=> "",
                    "tidal_time"=> "",
                    "tidal_height"=> "",
                    "series_code"=> "",
                    "type"=> "",
                    "format"=> "",
                    "entry_type"=> "",
                    "restricted"=> "",
                    "tidal_status"=> "",
                    "state"=>"X"
                );
            }
        }
    }
    sort_programme_by_date(true);       //  sort by date and remove duplicates
    return;
}

function get_fixed_start_tide($event_cfg, $date)
{
    global $tide_o;
    global $cfg;

    // get tides for today
    $tide = $tide_o->get_tide_by_date($date, false);
    $start = $event_cfg['time']['start'];
    if ($tide)
    {
        // check if tide is in period defined by fixed start time
        $tidal = in_tide_period($start, $cfg['settings']['tidal']['before_hw'], $cfg['settings']['tidal']['after_hw'], $tide);
    }
    else
    {
        // set no tide data error code
        $tidal = array("status" => "X-2", "tide_num"=> 0, "tide_height"=> "", "tide_time"=> "", "start_time"=> $start);
    }
    return $tidal;
}

function get_tidal_start_tide($event_cfg, $date)
{
    global $tide_o;
    global $cfg;

    $tide_code = array("X-2", "M", "G", "E");

    // get tides for today
    $tide = $tide_o->get_tide_by_date($date, false);
    if ($tide)
    {
        $bhw = decode_delta_time($cfg['settings']['tidal']['before_hw']);
        $ahw = decode_delta_time($cfg['settings']['tidal']['after_hw']);
        $et3 = decode_delta_time($cfg['settings']['tidal']['t3_early']);
        $lt3 = decode_delta_time($cfg['settings']['tidal']['t3_late']);

        $t1_early = (new DateTime($event_cfg['time']['earliest']))->sub(new DateInterval("PT{$ahw['hrs']}H{$ahw['min']}M"));
        $t1_late  = (new DateTime($event_cfg['time']['latest']))->add(new DateInterval("PT{$bhw['hrs']}H{$bhw['min']}M"));

        $t2_early = (new DateTime($event_cfg['time']['earliest']));
        $t2_late  = (new DateTime($event_cfg['time']['latest']));

        $t3_early = (new DateTime($event_cfg['time']['earliest']))->add(new DateInterval("PT{$et3['hrs']}H{$et3['min']}M"));
        $t3_late  = (new DateTime($event_cfg['time']['latest']))->sub(new DateInterval("PT{$lt3['hrs']}H{$lt3['min']}M"));

        // check first tide
        // calculate start and end of period for tide
        if (in_period($t3_early->format("H:i"), $t3_late->format("H:i"), $tide['hw1_time']))
        {
            $tide_1_rs = 3;     // excellent tide
        }
        elseif (in_period($t2_early->format("H:i"), $t2_late->format("H:i"), $tide['hw1_time']))
        {
            $tide_1_rs = 2;     // good tide
        }
        elseif (in_period($t1_early->format("H:i"), $t1_late->format("H:i"), $tide['hw1_time']))
        {
            $tide_1_rs = 1;     // marginal tide
        }
        else
        {
            $tide_1_rs = 0;     // not a usable tide
        }

        // check second tide
        if (in_period($t3_early->format("H:i"), $t3_late->format("H:i"), $tide['hw2_time']))
        {
            $tide_2_rs = 3;     // excellent tide
        }
        elseif (in_period($t2_early->format("H:i"), $t2_late->format("H:i"), $tide['hw2_time']))
        {
            $tide_2_rs = 2;     // good tide
        }
        elseif (in_period($t1_early->format("H:i"), $t1_late->format("H:i"), $tide['hw2_time']))
        {
            $tide_2_rs = 1;     // marginal tide
        }
        else
        {
            $tide_2_rs = 0;     // no tide
        }

        // get best tide
        if ($tide_1_rs >= $tide_2_rs)
        {
            $tidal = array("status" => $tide_code[$tide_1_rs], "tide_num" => 1, "tide_height" => $tide['hw1_height'],
                "tide_time" => $tide['hw1_time'], "start_time" => "");
        }
        else
        {
            $tidal = array("status" => $tide_code[$tide_2_rs], "tide_num" => 2, "tide_height" => $tide['hw2_height'],
                "tide_time" => $tide['hw2_time'], "start_time" => "");
        }

    }
    else
    {
        // set no tide data error code
        $tidal = array("status" => "X-1", "tide_num"=> 0, "tide_height"=> "", "tide_time"=> "", "start_time"=> "");

    }
    return $tidal;
}

function is_date_in_programme($date)
{
// note this only gets the first event on a particular day
    global $pg;

    $key = array_search(date("Y-m-d", strtotime($date)), array_column($pg, 'date'));
    return $key;
}

function all_events_for_date_in_programme($date)
{
// note this returns all events on a particular day
    global $pg;

    $rs = array();
    foreach ($pg as $k=>$event)
    {
        if (strtotime($event['date']) == strtotime($date)) { $rs[] = $event; }
    }

    return $rs;
}

function tide_better_than ($date, $required, $tide_status)
{
    if (empty($required) or empty($date) or empty($tide_status))
    {
        return false;
    }

    $tide_status_val = array(1=>"X", 2=>"M", 3=>"G", 4=>"E");
    $rs = false;
    $k = is_date_in_programme($date);

    if ($k !== false)
    {
        $i = array_search($required, $tide_status_val);
        $j = array_search($tide_status[0], $tide_status_val);

        if ( ($i and $j) and ($j > $i)) { $rs = true; }
    }
    return $rs;
}

function get_nearest_day($day, $date)
{

    $day = ucfirst($day);
    if(date('l', strtotime($date)) == $day)
    {
        return date("Y-m-d", strtotime($date));
    }
    elseif(abs(strtotime($date)-strtotime('next '.$day, strtotime($date))) < abs(strtotime($date)-strtotime('last '.$day, strtotime($date))))
    {
        return date("Y-m-d", strtotime('next '.$day, strtotime($date)));
    }
    else
    {
        return date("Y-m-d", strtotime('last '.$day, strtotime($date)));
    }
}

function get_relative_day($day, $date, $delta)
{
    return date("Y-m-d", strtotime("$delta ".ucfirst($day), strtotime($date)));
}

function get_next_day($day, $date)
{
    return date("Y-m-d", strtotime('next '.ucfirst($day), strtotime($date)));
}

function get_previous_day($day, $date)
{
    return date("Y-m-d", strtotime('last '.ucfirst($day), strtotime($date)));
}

function in_series($series_cfg, $date)
{
    // checks if date is a day that a series race can be held on
    $in_series = false;

    $dayname = date('l', strtotime($date));
    $days = explode("|", $series_cfg['date']['day']);
    foreach ($days as $day)
    {
        if (strtolower($day) == strtolower($dayname)) { $in_series = true; }
    }
    return $in_series;
}

function decode_delta_time($dtime)
{
//  decodes a time difference expressed as +hh:mm:ss or -hh:mm:ss into dir(+-), hrs, min, secs
//  mm and sss may be missing
//  00 is converted to 0
    $time_arr = array();

    $t = explode(":", substr($dtime, 1));

    if (count($t) > 0)
    {
        $time_arr["dir"] = $dtime[0];
        $t[0] =="00" ? $time_arr['hrs'] = "0" : $time_arr['hrs'] = ltrim($t[0], "0");
        $time_arr['min'] = "0";
        $time_arr['sec'] = "0";
    }
    if (count($t) > 1)
    {
        $t[1] =="00" ? $time_arr['min'] = "0" : $time_arr['min'] = ltrim($t[1], "0");
    }
    if (count($t) > 2)
    {
        $t[2] =="00" ? $time_arr['sec'] = "0" : $time_arr['sec'] = ltrim($t[2], "0");
    }

    return $time_arr;
}

function get_start_time($tide_hw, $data, $preferred = "")
{
    global $cfg;
    $round     = $cfg['settings']['round_start_mins'];
    $earliest  = $data['time']['earliest'];
    $latest    = $data['time']['latest'];
    if (empty($preferred))
    {
        $tide_time = decode_delta_time($cfg['settings']['tidal']['preferred']);
    }
    else
    {
        $tide_time = decode_delta_time($preferred);
    }

    // get start time from HW time and preferred start delta  from HW
    if ($tide_time['dir'] == "-")
    {
        $s = (new DateTime($tide_hw))->sub(new DateInterval("PT{$tide_time['hrs']}H{$tide_time['min']}M"));
    }
    else
    {
        $s = (new DateTime($tide_hw))->add(new DateInterval("PT{$tide_time['hrs']}H{$tide_time['min']}M"));
    }

    // round to start time
    $start_time = u_roundminutes($s->format("H:i"), $round);

    // ensure start is between earliest and latest start limits
    if (strtotime($start_time) < strtotime($earliest)) { $start_time = $earliest; }
    if (strtotime($start_time) > strtotime($latest))   { $start_time = $latest; }

    return $start_time;
}

function allocate_event($type, $data, $tidal, $event_code, $start_delta = "")
{
    global $cfg;

    $state = "S";
    $series_code = "";

    if (empty($tidal))
    {
        $start = $data['time']['start'];
    }
    else
    {
        $start = $tidal['start_time'];
        if (empty($start)) { $start = get_start_time($tidal['tide_time'], $data); }
    }

    if ($type == "freesail")
    {
       $start = get_start_time($tidal['tide_time'], $data, "-01:00");
    }
    elseif ($type == "open")
    {
        empty($start_delta) ? $delta = "-01:30" : $delta = $start_delta;
        $start = get_start_time($tidal['tide_time'], $data, $delta);
        $state = "P";
    }

    if (!empty($data['type']['code']))
    {
        $series_code = $data['type']['code']."-".substr($cfg['settings']['year'], -2);
    }

    $event = array(
        "state"       => $state,
        "start_time"  => $start,
        "event_name"  => $data['name'],
        "event_cfg"   => $type,
        "type"        => $data['type']['category'],
        "format"      => $data['type']['format'],
        "entry_type"  => $cfg['settings']['signon'],
        "restricted"  => $data['type']['access'],
        "series_code" => $series_code,
        "notes"       => "",
        "weblink"     => "",
        "code"        => $event_code
    );   // array doesn't have date allocated here as this may be an update to an existing record

    if (!empty($tidal))
    {
        $event["tidal_time"] = $tidal['tide_time'];
        $event["tidal_height"] = $tidal['tide_height'];
        $event["tidal_status"] = $tidal['status'];
        $event["tidal_num"] = $tidal['tide_num'];
    }

    return $event;
}

function allocated_fixed_nonracing($event, $date, $event_code)
{
    global $pg;

    $allocated = false;
    if ($_SESSION['debug']) {echo " ++++++ {$event['name']}<br>";}
    $key = is_date_in_programme($date);
    $event_data = allocate_event("event", $event, array(), $event_code);
    if ($key and $pg[$key]['state']=="X")
    {
        $pg[$key] = array_merge($pg[$key], $event_data);
        $allocated = true;
        if ($_SESSION['debug']) {echo " - overwriting {$pg[$key]['date']}<br>";}
    }
    else
    {
        $event_data['date'] = date("Y-m-d", strtotime($date));
        if ($_SESSION['debug']) {echo " - creating new {$event_data['date']}<br>";}
        $pg[] = $event_data;
        $allocated = true;
    }
    return $allocated;
}

function allocated_fixed($event, $date, $requested_tide, $cfg_code)
{
    global $pg;

    $allocated = false;
    if ($_SESSION['debug']) {echo " ++++ {$event['name']}<br>";}
    $key = is_date_in_programme($date);
    $tidal = assess_tide($event, $date);  // assess tide for date

    if (!empty($tidal) and tide_better_than($date, $requested_tide, $tidal['status']))  // check is useful tide
    {
        $event_data = allocate_event("event", $event, $tidal, $cfg_code);
        if ($event_data)
        {
            if ($key !== false)    // update existing programme record if not already a fixed even
            {
                if ($pg[$key]['state'] == "S")
                {
                    if ($pg[$key]['event_cfg'] == "series" or $pg[$key]['event_cfg'] == "freesail" )
                    {
                        $pg[$key] = array_merge($pg[$key], $event_data);
                        $allocated = true;
                        if ($_SESSION['debug']) {echo " - overwriting {$pg[$key]['date']}<br>";}
                    }
                    else
                    {
                        $allocated = false;
                        if ($_SESSION['debug']) {echo " - blocked {$pg[$key]['date']}<br>";}
                    }
                }
                elseif ($pg[$key]['state'] == "X")
                {
                    $pg[$key] = array_merge($pg[$key], $event_data);
                    $allocated = true;
                    if ($_SESSION['debug']) {echo " - overwriting {$pg[$key]['date']}<br>";}
                }
            }
            else                                          // create new programme record
            {
                $event_data['date'] = date("Y-m-d", strtotime($date));
                if ($_SESSION['debug']) {echo " - creating new {$event_data['date']}<br>";}
                $pg[] = $event_data;
                $allocated = true;
            }
        }
    }
    return $allocated;
}

function assess_tide($event_cfg, $date)
{
    //  checks if there is a suitable tide for event and adds to programmed schedule
    //  records tide status as a coded value: X - error, N - no start possible, M - marginal, G - good

    if ($event_cfg['time']['type'] == "fixed")
    {
        $tidal = get_fixed_start_tide($event_cfg, $date);   // checks if fixed start time has usable tide
    }
    elseif($event_cfg['time']['type'] == "tidal" or $event_cfg['time']['type'] == "float")
    {
        $tidal = get_tidal_start_tide($event_cfg, $date);   // checks if any start_time is possible within period

    }
    else
    {
        // record tidal error - type is not known
        $tidal = array("status" => "X-1", "tide_num"=> 0, "tide_height"=> "", "tide_time"=> "", "start_time"=> "");
    }

    return $tidal;
}

function in_period($start, $end, $target)
{
// Calculates if target date/time occurs within period defined by start/end
// date/times provided as strings
    $in_period = false;
    if (strtotime($target) >= strtotime($start) and strtotime($target) <= strtotime($end))
    {
        $in_period = true;
    }
    return $in_period;
}

function period_overlap($start_1, $end_1, $start_2, $end_2)
{
// Calculates if period_1 overlaps period_2
    $overlap = false;
    if (strtotime($start_2) <= strtotime($end_1) and strtotime($end_2) >= strtotime($start_1))
    {
        $overlap = true;
    }
    return $overlap;
}

function in_tide_period($start, $before_hw, $after_hw, $tide)
{

// Calculates if a start time lies within the earliest / latest tide times
    $bhw = decode_delta_time($before_hw);
    $ahw = decode_delta_time($after_hw);

    $tidal = array();

    // try HW1
    $earliest_start = (new DateTime($tide['hw1_time']))->sub(new DateInterval("PT{$bhw['hrs']}H{$bhw['min']}M"));
    $latest_start = (new DateTime($tide['hw1_time']))->add(new DateInterval("PT{$ahw['hrs']}H{$ahw['min']}M"));

    if (strtotime($start) >= strtotime($earliest_start->format("H:i")) and
        strtotime($start) <= strtotime($latest_start->format("H:i")) )
    {
        $tidal = array("status" => "G", "tide_num"=> 1, "tide_height"=> $tide['hw1_height'],
                      "tide_time"=> $tide['hw1_time'], "start_time"=> $start);
    }

    // try HW2
    if (empty($tidal))
    {
        $earliest_start = (new DateTime($tide['hw2_time']))->sub(new DateInterval("PT{$bhw['hrs']}H{$bhw['min']}M"));
        $latest_start = (new DateTime($tide['hw2_time']))->add(new DateInterval("PT{$ahw['hrs']}H{$ahw['min']}M"));

        if (strtotime($start) >= strtotime($earliest_start->format("H:i")) and
            strtotime($start) <= strtotime($latest_start->format("H:i")) )
        {
            $tidal = array("status" => "G", "tide_num"=> 2, "tide_height"=> $tide['hw2_height'],
                "tide_time"=> $tide['hw2_time'], "start_time"=> $start);
        }
    }

    // tide no good
    if (empty($tidal))
    {
       //set no useful tide code
       $tidal = array("status" => "X-2", "tide_num"=> 0, "tide_height"=> "", "tide_time"=> "", "start_time"=> $start);
    }

    return $tidal;
}

function sort_programme_by_date($remove_duplicates)
{
    global $pg;

    array_multisort(array_column($pg, 'date'), SORT_ASC,
        array_column($pg, 'start_time'), SORT_ASC,
        $pg);

    if ($remove_duplicates)
    {
        $current = "0000-00-00";
        foreach ($pg as $k=>$day)
        {
            if ($day['date'] == $current) { unset($pg[$k]); }
            $current = $day['date'];
        }
    }

    $pg = array_values($pg);   // reindex
}













