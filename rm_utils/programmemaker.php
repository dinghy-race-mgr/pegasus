<?php
/* ----------------------------------------------------------------------------------------------

TO DO
- should I move the schedule information into the event/series structures
- rationalise cfg content - remove unused parameters
- convert to a script + class (& remove relevant functions into util.lib)
- add validation tests
- report validation errors


*/

/*
 DATA MODEL

 pg --- programmable days/events (may be more than one event per day).  This is the array that the import file is
        created from.

        status     - programmed status (X - not programmed, S - scheduled event, P - protected date (not schedule-able
        start_time - start time (hh:mm)
        event_name" => $series['name'],
        series_code" => $series['type']['code']."-".substr($cfg['settings']['year'], -2),
        type       - type of event - must match one of racemanager even codes - set in cfg.json file
        format     - event format - typically the race format defined in racemanager - set in cfg.json file
        entry_type - how do people enter the event (<blank>|on/retire|ood) - set in cfg.json file
        restricted - who is the event accessible to (club|open) - currently not used
        notes      - notes that will appear in programme - currently not used
        weblink    - url associated with event - currently not used
 */

$loc  = "..";
$page = "programmeMaker";     //
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
        error_log(date('H:i:s')." -- IMPORT --------------------".PHP_EOL, 3, $_SESSION['syslog']);
    }
    else
    {
        u_exitnicely($scriptname, 0, "initialisation failure", "one or more problems with import initialisation");
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
    // present form to select csv file for processing (general template)
    $_SESSION['pagefields']['body'] =  $tmpl_o->get_template("upload_pmaker_file", $_SESSION['pagefields']);

    // render page
    echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
}


/* ------------ submit page ---------------------------------------------*/

elseif (strtolower($_REQUEST['pagestate']) == "submit")
{
    $bufr = "";
    $json_err = array();
    //$json_content = file_get_contents($_FILES['pmakerfile']['tmp_name']);
    $cfg = json_decode(file_get_contents($_FILES['pmakerfile']['tmp_name']), true);
    if ($cfg)
    {
        $cfg['settings']['year'] = date("Y", strtotime($_REQUEST['date-start']));
        $json_err = pg_config_validation($cfg);
        //$bufr.= "<pre>".print_r($cfg,true)."</pre>";
    }
    else
    {
        $json_err[] = "json configuration file not read<br><br>file detail:<pre>".print_r($_FILES['pmakerfile'],true)."</pre> ";
    }

    if (!empty($json_err))
    {
        //report errors and stop  // FIXME
        echo "</pre>".print_r($json_err,true)."</pre>";
        exit("configuration error");
    }

    // create list of events to be scheduled that are relevant to the period to be programmed
    $el = pg_get_event_list($_REQUEST['date-start'], $_REQUEST['date-end'], $cfg);
    //$bufr.= "<pre>".print_r($el,true)."</pre>";

    // create list of scheduled days
    $sched_days = pg_get_schedule_days($cfg);
    //echo "<pre> ---- sched days -----".print_r($sched_days, true)."</pre>";

    $pg = array();
    // get number of days which will potentially have scheduled events
    pg_get_days($_REQUEST['date-start'], $_REQUEST['date-end'], $cfg, $sched_days);
    //echo "<pre> ---- pg 1 -----".print_r($pg, true)."</pre>";

    // add days for one off events on fixed days if they don't exist
    pg_get_fixed_dates($_REQUEST['date-start'], $_REQUEST['date-end'], $cfg);
    //echo "<pre> ---- pg 2 -----".print_r($pg, true)."</pre>";

    // assess best tide for each day scheduled
    foreach ($pg as $k=>$event)
    {
        $tidal = assess_tide($event['date']);
        // update programme records
        if (!empty($tidal))
        {
            $pg[$k]['tidal_status'] = $tidal['status'];
            $pg[$k]['tidal_num']    = $tidal['tide_num'];
            $pg[$k]['tidal_height'] = $tidal['tide_height'];
            $pg[$k]['tidal_time']   = $tidal['tide_time'];
            $pg[$k]['start_time']   = $tidal['start_time'];
        }
    }

    // now allocate series events
    foreach ($pg as $k=>$event)
    {
        pg_allocate_series($k, $event);
    }

    // now allocate individual events
    pg_allocate_events();
//
//    // finally deal with multiple event days
//    if ($cfg['settings']['race_times'] == "tidal" and  $cfg['settings']['multiple_races']["num_per_day"] > 1)
//    {
//        pg_allocate_multi_events();
//    }



    // produce table of events for review
    $bufr.= pg_display_events($pg, $_REQUEST['date-start'], $_REQUEST['date-end'], $_FILES['pmakerfile']['name']);


    // FIXME present form to select csv file for processing (general template)
    //$bufr.= "<pre>LAST<br>".print_r($pg,true)."</pre>";
    $_SESSION['pagefields']['body'] = $bufr;

    // render page
    echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);

}

function pg_config_validation($cfg)
{
    $error = array();

    // check all the values in settings are defined and valid

    // check that period doesn't extend over year boundary

    // check race_times are 'tidal' that we have the start and end of tidal window defined and that they are valid times

    // check if we have at least one day defined in schedule and all days defined have valid values

    // check we have at least one event (single or series defined)

    // check each single event is valid

    // check each series is valid

    //
    return $error;
}

function pg_get_schedule_days($cfg)
{
    $days = array();
    foreach ($cfg['schedule'] as $k=>$day)
    {
        $days[$k] = strtolower($day['day']);
    }

    return $days;
}

function pg_display_events($pg, $start, $end, $file)
{
    $today = date("jS M Y");
    $bufr = "";

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
            <th>Date</th>
            <th>Event</th>
            <th>Start</th>
            <th>Tide</th>
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
        $ev['state'] == "X" ? $tide_str = "" : $tide_str = "{$ev['tidal_time']} {$ev['tidal_height']}m";
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
            <td>{$ev['tidal_status']}</td>
        </tr>
EOT;
    }

    $bufr.= <<<EOT
    </tbody>
    </table>
    <p class="text-info">tide status codes: E: excellent, G: good, M: marginal, X-1: no tide data, X-2: racing not possible</p>
EOT;
    return $bufr;
}

function pg_allocate_events()
{
    global $cfg;
    global $pg;

    // deal with fixed date events
    foreach ($cfg['single_events'] as $j => $event)
    {
        if ($event['date']['type'] == "fixed")
        {
            //echo "dealing with {$event['name']}<br>";
            if (in_period($_REQUEST['date-start'], $_REQUEST['date-end'], $event['date']['value']))
            {
                // check if we have tide data
                $key = is_date_in_programme($event['date']['value']);
                if (!empty($pg[$key]['tidal_status'])) // requested date already exists  - schedule it
                {
                    if (tide_better_than($event['date']['value'], "M"))
                    {
                        $event_data = allocate_events("event", $event, $pg[$key]['tidal_status'], $pg[$key]['tidal_time'], $event['date']['time']);
                        if ($event_data)
                        {
                            $pg[$key] = array_merge($pg[$key], $event_data);
                        }
                    }
                }
                else   // requested date doesn't exist - create it and schedule it
                {

                    // FIXME - this won't work for fixed start time
                    $tidal = pg_get_tidal_start_tide($cfg['settings'], date("Y-m-d",strtotime($event['date']['value'])));

                    // create initial record data
                    $temp = array(
                        "date"        =>date("Y-m-d", strtotime($event['date']['value'])),
                        "start_time"  => $tidal['start_time'],
                        "tidal_time"  => $tidal['tide_time'],
                        "tidal_height"=> $tidal['tide_height'],
                        "tidal_status"=> $tidal['status'],
                        "tidal_num"   => $tidal['tide_num'],
                        "state"=>"X"
                    );

                    // get event data and add to existing record
                    $event_data = allocate_events("event", $event, $tidal['status'], $tidal['tide_time'], $event['date']['time']);

                    if ($event_data) { $temp = array_merge($temp, $event_data); }

                    $pg[$key] = $temp;
                }
            }
        }
    }
    // resort programme data
    sort_programme_by_date(false);   // don't remove duplicates

    // now deal with 'nearest' and 'float' events
    foreach ($cfg['single_events'] as $j => $event)
    {
        if ($event['date']['type'] == "nearest")
            // tries to allocate nearest relevant sailing day - if that is not a good tide tries the next nearest day
            // if still bad tide - give up
        {
            if (in_period($_REQUEST['date-start'], $_REQUEST['date-end'], $event['date']['value']))
            {
                $weekday = $cfg['schedule']["{$event['schedule']}"]['day'];

                // get nearest day
                $nearest_day = get_nearest_day($weekday, $event['date']['value']);

                // if tide ok - then schedule it
                if (tide_better_than($nearest_day, "X"))
                {
                    $key = is_date_in_programme($nearest_day);

                    $event_data = allocate_events("event", $event, $pg[$key]['tidal_status'], $pg[$key]['tidal_time'],$event['date']['time']);
                    if ($event_data) { $pg[$key] = array_merge($pg[$key], $event_data); }
                }
                else   // tide not ok - look at next nearest day
                {
                    if (strtotime($nearest_day) <= $event['date']['value'])
                    {
                        $nearest_day = get_next_day($weekday, $nearest_day);
                    }
                    else
                    {
                        $nearest_day = get_previous_day($weekday, $nearest_day);
                    }

                    if (tide_better_than($nearest_day, "X"))
                    {
                        $key = is_date_in_programme($nearest_day);

                        $event_data = allocate_events("event", $event, $pg[$key]['tidal_status'], $pg[$key]['tidal_time'], $event['date']['time']);
                        if ($event_data) { $pg[$key] = array_merge($pg[$key], $event_data); }
                    }
                }
            }
        }

        elseif ($event['date']['type'] == "float")
        {
            if (period_overlap($_REQUEST['date-start'], $_REQUEST['date-end'], $event['date']['earliest'], $event['date']['latest']))
            {
                $weekday = $cfg['schedule']["{$event['schedule']}"]['day'];

                $scheduled = false;
                $sch_date = get_next_day($weekday, $event['date']['earliest']);
                while (!$scheduled)
                {
                    if (!in_period($event['date']['earliest'], $event['date']['latest'],$sch_date))
                    {
                        break;   // give up
                    }

                    if (tide_better_than($sch_date, "X"))
                    {
                        $key = is_date_in_programme($sch_date);

                        $event_data = allocate_events("event", $event, $pg[$key]['tidal_status'], $pg[$key]['tidal_time'], $event['date']['time']);
                        if ($event_data)
                        {
                            $pg[$key] = array_merge($pg[$key], $event_data);
                            $scheduled = true;
                        }
                    }
                    else
                    {
                        $sch_date = get_next_day($weekday, $sch_date);
                    }
                }
            }
        }
    }

    return;
}

function pg_allocate_series($k, $event)
{
    global $cfg;
    global $pg;

    foreach ($cfg['series_events'] as $j => $series)
    {
        if (in_period($series['date']['earliest'], $series['date']['latest'], $event['date']))
        {
            if (in_series($series, date('l', strtotime($event['date']))))
            {
                if (tide_better_than($event['date'], "X"))
                {
                    $event_data = allocate_events("series", $series, $event['tidal_status'], $event['tidal_time'], $event['start_time']);
                    if ($event_data) { $pg[$k] = array_merge($pg[$k], $event_data); }
                }
            }
        }
    }
    return;
}

function pg_allocate_multi_events ()
{
    global $cfg;
    global $pg;

    $new_races = 0;

    foreach ($pg as $k=>$event)
    {
        // check that tide meets criteria for multiple even
        if (tide_better_than($event['date'], $cfg['settings']['multiple_races']['tide']))
        {
            // check that only one race is currently scheduled for this date, it is a race and a series event
            $rs = all_events_for_date_in_programme($event['date']);
            if (count($rs) == 1)
            {
                foreach ($rs as $j=>$ev)
                {
                    if ($ev['type'] == "racing" and !empty($ev['series_code']))
                    {
                        // move start time of current race to (settings: first_start) before HW
                        $dtime = decode_delta_time($cfg['settings']['multiple_races']['first_start']);
                        if ($dtime['dir'] == "-")
                        {
                            $s = (new DateTime($ev['start_time']))->sub(new DateInterval("PT{$dtime['hrs']}H{$dtime['min']}M"));
                        }
                        else
                        {
                            $s = (new DateTime($ev['start_time']))->add(new DateInterval("PT{$dtime['hrs']}H{$dtime['min']}M"));
                        }
                        //  set into record
                        $new_start_time1 = u_roundminutes($s->format("H:i"), $cfg['settings']['round_minutes']);
                        $pg[$k]['start_time'] = $new_start_time1;

                        // add another race (settings: interval)  after the first race)
                        $dtime = decode_delta_time($cfg['settings']['multiple_races']['interval']);
                        $s = (new DateTime($new_start_time1))->add(new DateInterval("PT{$dtime['hrs']}H{$dtime['min']}M"));
                        $new_start_time2 = u_roundminutes($s->format("H:i"), $cfg['settings']['round_minutes']);
                        $ev['start_time'] = $new_start_time2;

                        // create new record
                        $pg[] = $ev;
                        $new_races++;
                    }
                }
            }
        }
    }
    sort_programme_by_date(false);    // don't remove duplicates
    return;
}

function pg_get_event_list($start_date, $end_date, $cfg)
{
    $el = array();

    foreach($cfg['single_events'] as $k=>$event)
    {
        if (in_period($start_date, $end_date, $event['date']['value']) or
            in_period($start_date, $end_date, $event['date']['earliest']) or
            in_period($start_date, $end_date, $event['date']['latest']))
        {
            $el[] = array("name"=>$event['name'], "code"=>$k, "type"=>"single",
                "category"=>$event['type']['category'], "scheduled"=>0);
        }
    }

    foreach($cfg['series_events'] as $k=>$event)
    {
        if (in_period($start_date, $end_date, $event['date']['value']) or
            in_period($start_date, $end_date, $event['date']['earliest']) or
            in_period($start_date, $end_date, $event['date']['latest']))
        {
            $el[] = array("name"=>$event['name'], "code"=>$k, "type"=>"series", "category"=>$event['type']['category'], "scheduled"=>0);
        }
    }

    if (empty($el)) { return false; }

    return $el;
}

function pg_get_days($start_date, $end_date, $cfg, $sched_days)
{
    global $pg;

    $start = strtotime($start_date);
    $end = strtotime($end_date);
    $current = $start;

    // FIXME this doesn't take into account the start and end dates for each scheduled day
    while ($current <= $end)
    {
        // check if this week day is scheduled
        $day_of_week = strtolower(date("l", $current));

        foreach ($sched_days as $k => $dayname)
        {
            if ($dayname == $day_of_week)
            {
                if (in_period($cfg['schedule'][$k]['date']['start'], $cfg['schedule'][$k]['date']['end'], date("Y-m-d", $current)))
                {
                    $pg[] = array(
                        "date"=>date("Y-m-d",$current),
                        "event_name"=> "",
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
        $current = strtotime("+1 day", $current);
    }
    return;
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
                //echo "single-event: ".date("Y-m-d",$date_this_year)."</br>";
                $pg[] = array(
                    "date"=>date("Y-m-d",strtotime($event['date']['value'])),
                    "event_name"=> "",
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
                //echo "single-series: ".date("Y-m-d",$date_this_year)."</br>";
                $pg[] = array(
                    "date"=>date("Y-m-d",strtotime($event['date']['value'])),
                    "event_name"=> "",
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

    sort_programme_by_date(true);       //  remove duplicates

    return;
}

function pg_get_fixed_start_tide($schedule_day, $date)
{
    global $tide_o;
    global $cfg;

    $tidal = array();

    // get tides for today
    $tide = $tide_o->get_tide($date);
    if ($tide)
    {
        // check if tide is in period defined by fixed start time
        $tidal = in_tide_period($schedule_day['time']['start'], $cfg['tidal']['before_hw'], $cfg['tidal']['after_hw'], $tide);
    }
    else
    {
        // set no tide data error code
        $tidal = array("status" => "X-2", "tide_num"=> 0, "tide_height"=> "", "tide_time"=> "", "start_time"=> $schedule_day['time']['start']);
    }
    return $tidal;
}

function pg_get_tidal_start_tide($schedule_day, $date)
{
    global $tide_o;
    global $cfg;

    $tide_code = array("X-2", "M", "G", "E");

    // get tides for today
    $tide = $tide_o->get_tide($date);
    if ($tide)
    {
        $et = explode(":",$cfg['tidal']['before_hw']);
        $lt = explode(":",$cfg['tidal']['after_hw']);

        $t1_early = (new DateTime($schedule_day['time']['earliest']))->sub(new DateInterval("PT{$et[0]}H{$et[1]}M"));
        $t1_late  = (new DateTime($schedule_day['time']['latest']))->add(new DateInterval("PT{$lt[0]}H{$lt[1]}M"));

        $t2_early = (new DateTime($schedule_day['time']['earliest']));
        $t2_late  = (new DateTime($schedule_day['time']['latest']));

        $t3_early = (new DateTime($schedule_day['time']['earliest']))->add(new DateInterval("PT{$cfg['tidal']['t3_early']}H0M"));
        $t3_late  = (new DateTime($schedule_day['time']['latest']))->sub(new DateInterval("PT{$cfg['tidal']['t3_late']}H0M"));

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
            $tide_1_rs = 0;     // no tide
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
            $tidal = array("status" => $tide_code[$tide_1_rs], "tide_num"=> 1, "tide_height"=> $tide['hw1_height'],
                "tide_time"=> $tide['hw1_time'], "start_time"=> "");
        }
        else
        {
            $tidal = array("status" => $tide_code[$tide_2_rs], "tide_num"=> 2, "tide_height"=> $tide['hw2_height'],
                "tide_time"=> $tide['hw2_time'], "start_time"=> "");
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
    // note this only gets the first event on a particular day
{
    global $pg;

    $key = array_search(date("Y-m-d", strtotime($date)), array_column($pg, 'date'));
    return $key;
}

function all_events_for_date_in_programme($date)
    // note this returns all dates on a particular day
{
    global $pg;

    $rs = array();

    foreach ($pg as $k=>$event)
    {
        if (strtotime($event['date']) == strtotime($date))
        {
            $rs[] = $event;
        }
    }

    return $rs;
}

function tide_better_than ($date, $required)
{
    global $pg;

    $tide_status_val = array(1=>"X", 2=>"M", 3=>"G", 4=>"E");
    $rs = false;
    $k = is_date_in_programme($date);

    if ($k !== false)
    {
        $tide_status = $pg[$k]['tidal_status'];
        $i = array_search($required, $tide_status_val);
        $j = array_search($tide_status[0], $tide_status_val);

        if ( ($i and $j) and ($j > $i))
        {
            $rs = true;
        }
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

function get_next_day($day, $date)
{
    return date("Y-m-d", strtotime('next '.ucfirst($day), strtotime($date)));
}

function get_previous_day($day, $date)
{
    return date("Y-m-d", strtotime('last '.ucfirst($day), strtotime($date)));
}



function in_series($series_cfg, $weekday)
{
    global $cfg;

    $in_series = false;
    // correct day
    $days = explode("|", $series_cfg['schedule']);
    foreach ($days as $sched_day)
    {
        if (strtolower($cfg['schedule'][$sched_day]['day']) == strtolower($weekday))
        {
            $in_series = true;
        }
    }

    return $in_series;
}

function decode_delta_time($dtime)
    //  decodes a time difference expressed as +hh:mm:ss or -hh:mm:ss into dir(+-), hrs, min, secs
    //  mm and sss may be missing
    //  00 is converted to 0
{
    $t = explode(":", substr($dtime, 1));
    $time_arr = array();

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

function get_start_times($tide_category, $tide_hw, $data)
{
    global $cfg;

    // FIXME change this when I get rif of schedule structure
    if (empty($data['schedule']))
    {
        $earliest = $cfg['settings']['time']['earliest'];
        $latest = $cfg['settings']['time']['latest'];
    }
    else
    {
        $earliest = $cfg['schedule']["{$data['schedule']}"]['time']['earliest'];
        $latest = $cfg['schedule']["{$data['schedule']}"]['time']['latest'];
    }

    // get num starts for this day  FIXME - is this the best way to do this (either fixed times or multiple events)
    $tide_category == "E" ? $num_starts = $cfg['tidal']['max_starts'] : $num_starts = 1;

    $start_time = array();
    $tide_time = decode_delta_time($cfg['tidal']['preferred']);

    if ($tide_time['dir'] == "-")
    {
        $s = (new DateTime($tide_hw))->sub(new DateInterval("PT{$tide_time['hrs']}H{$tide_time['min']}M"));
    }
    else
    {
        $s = (new DateTime($tide_hw))->add(new DateInterval("PT{$tide_time['hrs']}H{$tide_time['min']}M"));
    }

    $start_time[] = u_roundminutes($s->format("H:i"), $cfg['settings']['round_minutes']);
    foreach ($start_time as $k => $start)
    {
        //echo "start_time: $start<br>";
        if (strtotime($start) < strtotime($earliest))
        {
            $start_time[$k] = u_roundminutes($earliest, $cfg['settings']['round_minutes']);
        }

        if (strtotime($start) > strtotime($latest))
        {
            $start_time[$k] = u_roundminutes($latest, $cfg['settings']['round_minutes']);
        }
    }
    return $start_time;
}

function allocate_events($type, $data, $tidal_status="", $tidal_time="", $start_time="")
{
    global $cfg;

    $event = false;

    $starts[] = $start_time;
    if (empty($start_time))
    {
        //echo "<pre>{$data['name']} start_time is empty: |$tidal_status|$tidal_time|".print_r($data,true)."</pre>";
        $starts = get_start_times($tidal_status, $tidal_time, $data);
    }

    foreach ($starts as $start)
    {
        $event = array(
            "state"      => "S",
            "start_time"  => $start,
            "event_name"  => $data['name'],
            "type"        => $data['type']['category'],
            "format"      => $data['type']['format'],
            "entry_type"  => $cfg['settings']['signon'],
            "restricted"  => "club",
            "notes"       => "",
            "weblink"     => ""
        );
        if ($type == "series")
        {
            $event["series_code"] = $data['type']['code']."-".substr($cfg['settings']['year'], -2);
        }
        else
        {
            $event["series_code"] = "";
        }
    }
    return $event;
}

function assess_tide($date)
//           checks if there is a suitable tide for event and adds to programmed schedule
//           records tide status as a coded value: X - error, N - no start possible, M - marginal, G - good
{
    global $cfg;


    $tidal = array();

    // FIXME this will needs some rework when I get rid of the schedule structure
    // find day matching this event day
    foreach ($cfg['schedule'] as $schedule_day)
    {
        if (strtolower($schedule_day['day']) == strtolower(date("l",strtotime($date))))
        {
            // FIXME - need to deal with the start and end in the year for each schedule day
            if ($schedule_day['time']['type'] == "fixed")
            {
                $tidal = pg_get_fixed_start_tide($schedule_day, $date);   // checks if fixed start time has usable tide
            }
            elseif($schedule_day['time']['type'] == "tidal" or $schedule_day['time']['type'] == "float")
            {
                $tidal = pg_get_tidal_start_tide($schedule_day, $date);   // checks if any start_time is possible within period
            }
            else
            {
                // record tidal error - schedule
                $tidal = array("status" => "X-1", "tide_num"=> 0, "tide_height"=> "", "tide_time"=> "", "start_time"=> "");
            }
        }
    }

    return $tidal;

}


function in_period($start, $end, $target)
    /*
     * Calculates if target date/time occurs within period defined by start/end
     * date/times provided as strings
     */
{
    if (strtotime($target) >= strtotime($start) and strtotime($target) <= strtotime($end))
    {
        return true;
    }
    return false;
}

function period_overlap($start_1, $end_1, $start_2, $end_2)
    /*
     * Calculates if period_1 overlaps period_2
     */
{
    $overlap = false;

    if (strtotime($start_2) <= strtotime($end_1) OR strtotime($end_2) >= strtotime($start_1))
    {
        $overlap = true;
    }
    return $overlap;
}

function in_tide_period($start, $before_hw, $after_hw, $tide)
    /*
     * Calculates if a start time lies within the earliest / latest tide times
     */
{

    $et = explode(":",$before_hw);
    $lt = explode(":",$after_hw);

    $earliest_tide = (new DateTime($start))->sub(new DateInterval("PT{$et[0]}H{$et[1]}M"));
    $latest_tide = (new DateTime($start))->add(new DateInterval("PT{$lt[0]}H{$lt[1]}M"));

    if (strtotime($tide['hw1_time']) >= strtotime($earliest_tide->format("H:i"))
        and strtotime($tide['hw1_time']) <= strtotime($latest_tide->format("H:i")))
    {
        $tidal = array("status" => "G", "tide_num"=> 1, "tide_height"=> $tide['hw1_height'], "tide_time"=> $tide['hw1_time'], "start_time"=> $start);
    }
    elseif (strtotime($tide['hw2_time']) >= strtotime($earliest_tide->format("H:i"))
        and strtotime($tide['hw2_time']) <= strtotime($latest_tide->format("H:i")))
    {
        $tidal = array("status" => "G", "tide_num"=> 2, "tide_height"=> $tide['hw2_height'], "tide_time"=> $tide['hw2_time'], "start_time"=> $start);
    }
    else
    {
        // set no useful tide code

        $tidal = array("status" => "X-2", "tide_num"=> 0, "tide_height"=> "", "tide_time"=> "", "start_time"=> $start);
    }

    return $tidal;
}

function sort_programme_by_date($remove_duplicates)
{
    global $pg;

    $keys = array_column($pg, 'date');
    array_multisort($keys, SORT_ASC, $pg);


    if ($remove_duplicates)
    {
        $current = "0000-00-00";
        foreach ($pg as $k=>$day)
        {
            if ($day['date'] == $current) { unset($pg[$k]); }
            $current = $day['date'];
        }
    }

    // reindex
    $pg = array_values($pg);
}


function find_nearest_day($days, $date)
{
//    $d = array();
//    // FIXME - this doesn't handle start and end periods for scheduled days
//    foreach ($days as $k=>$day)
//    {
//        $nearest = strtotime("next $day", strtotime("$date - 4 days"));
//        $d[$k] = abs(strtotime($date) - $nearest;
//    }
//    $min_key =  min(array_keys($d, min($d)));
//
//
//    return strtotime("next $day", strtotime("$date - 4 days"));
}











