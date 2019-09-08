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

        status     - programmed status (X - not programmed, S - scheduled event, P - protected date (not schedule-able
        start_time - start time (hh:mm)
        event_name" => $series['name'],
        series_code" => $series['type']['code']."-".year
        type       - type of event - must match one of racemanager even codes - set in cfg.json file
        format     - event format - typically the race format defined in racemanager - set in cfg.json file
        entry_type - how do people enter the event (<blank>|on/retire|ood) - set in cfg.json file
        restricted - who is the event accessible to (club|open) - currently not used
        notes      - notes that will appear in programme - currently not used
        code       - index code for his event
        weblink    - url associated with event - currently not used

Assumes that tidal data is held in table t_tide and HW times have been converted to local time

Bugs
 - FIXED can't process if you are trying to do next years programme - needs to get year from start/end date
 - rounding of start times is not working well (see 28/7/19)
 - FIXED not getting good tide for 3rd 17th - wednesday
 - needs to put tide details into $pg array even if its not a good tide (do I include non-scheduled days in programme import)
 - add config check to see that there are no duplicate event code values
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
    // present form to select json file for processing (general template)
    $_SESSION['pagefields']['body'] =  $tmpl_o->get_template("upload_pmaker_file", $_SESSION['pagefields']);

    // render page
    echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
}


/* ------------ submit page ---------------------------------------------*/

elseif (strtolower($_REQUEST['pagestate']) == "submit")
{
    $bufr = "";
    $json_err = array();
    $json_file = $_FILES['pmakerfile']['name'];

    $cfg = json_decode(file_get_contents($_FILES['pmakerfile']['tmp_name']), true);
    if ($cfg)
    {
        $cfg['settings']['year'] = date("Y", strtotime($_REQUEST['date-start']));
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

        // allocated series races to programme days
        pg_allocate_series_events();

        // now allocate individual events - replacing series events if necessary
        pg_allocate_fixed_events();

        // now check if any days with just a series event could have more than one event
        if ($cfg['settings']['multiple_races']['include'] == "yes") {
            $races_added = pg_allocate_multi_events();
        }

        // create csv file
        $csv_status = pg_create_csv();

        // produce table of events for review
        $bufr .= pg_display_events($_REQUEST['date-start'], $_REQUEST['date-end'], $json_file, $csv_status);

        // finally check which events have been scheduled
        foreach ($pg as $k => $event) {
            foreach ($cfgevent as $j => $cevent) {
                if ($cevent['code'] == $event['code']) {
                    $cfgevent[$j]['scheduled'] = $cfgevent[$j]['scheduled'] + 1;
                    $cfgevent[$j]['dates'] = $cfgevent[$j]['dates'] . $event['date'] . " | ";
                }
            }
        }
        $bufr .= pg_display_event_summary($cfgevent);


    }


    // FIXME present form to select csv file for processing (general template)
    $_SESSION['pagefields']['body'] = $bufr;

    // render page
    echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
    echo "<pre>Config Events:<br>".print_r($cfgevent,true)."</pre>";
    echo "<pre>Config Details:<br>".print_r($cfg,true)."</pre>";
    echo "<pre>Programme Details:<br>".print_r($pg,true)."</pre>";
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


    return $error;
}

function pg_get_event_list($start_date, $end_date)
{
    global $cfg;
    $types = array("single_events", "series_events", "special_events", "open_events");
    $el = array();

    foreach ($types as $type)
    {
        foreach($cfg[$type] as $k=>$event)
        {
            $str_arr = explode("_", $type);
            $type_code = $str_arr[0];
            if ($event['status'] == "schedule" or $event == "report")
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

//    foreach($cfg['series_events'] as $k=>$event)
//    {
//        $set = false;
//
//        if ( !empty($event['date']['value']) and
//            in_period($start_date, $end_date, $event['date']['value']))
//        {
//            $set = true;
//        }
//
//        if ( !empty($event['date']['earliest']) and !empty($event['date']['latest']) and
//            period_overlap($start_date, $end_date, $event['date']['earliest'], $event['date']['latest']))
//        {
//            $set = true;
//        }
//
//        if ($set)
//        {
//            $el[] = array("name"=>$event['name'], "code"=>$k, "type"=>"series",
//                "category"=>$event['type']['category'], "scheduled"=>0, "dates"=>"");
//        }
//    }

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

//    foreach ($cfg['schedule'] as $k=>$day)
//    {
//        $days[$k] = strtolower($day['day']);
//    }

    return $days;
}

function pg_get_days($start_date, $end_date, $days)
{
    global $pg;
    global $cfg;

    $blank = array(
        "date"        =>"",
        "event_name"  => "",
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
                if (in_period($event['date']['earliest'], $event['date']['latest'], $date)) { $create_record = true; }
            }

            foreach ($cfg['single_events'] as $event)
            {
                if (!empty($event['date']['value']))
                {
                    if (strtotime($date) == strtotime($event['date']['value'])) { $create_record = true; }
                }
                else
                {
                    if (in_period($event['date']['earliest'], $event['date']['latest'], $date)) { $create_record = true; }
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

function pg_allocate_series_events()
{
    global $cfg;
    global $pg;

    // FIXME how does this work for non-tidal series

    foreach ($pg as $k=>$event)                              // loop over each day to be scheduled
    {
        foreach ($cfg['series_events'] as $j => $series)     // loop over each series in cfg
        {
            if (strtolower($series['status']) == "schedule")
            {
                if (in_period($series['date']['earliest'],
                              $series['date']['latest'], $event['date']))  // is this day within the series start/end
                {
                    if (in_series($series, $event['date']))      // does this day match the series day requirement in cfg
                    {
                        $tidal = assess_tide($series, $event['date']);  // assess tide

                        if (!empty($tidal) and
                            tide_better_than($event['date'], "X", $tidal['status']))  // check is useful tide
                        {
                            $event_data = allocate_event("series", $series, $tidal, $j);
                            if ($event_data) { $pg[$k] = array_merge($pg[$k], $event_data);}
                        }
                        else  // just add tidal details
                        {
                            $upd = array(
                                "tidal_time"   => $tidal['tide_time'],
                                "tidal_height" => $tidal['tide_height'],
                                "tidal_status" => $tidal['status'],
                                "tidal_num"    => $tidal['tide_num'],
                            );
                            $pg[$k] = array_merge($pg[$k], $upd);
                        }
                    }
                }
            }
        }
    }
    return;
}

function pg_allocate_fixed_events()
{
    global $cfg;

    // deal with fixed date events
    foreach ($cfg['single_events'] as $j => $event)
    {
        if (strtolower($event['status'] == "schedule") and $event['date']['type'] == "fixed")
        {
            $date = $event['date']['value'];
            if (in_period($_REQUEST['date-start'], $_REQUEST['date-end'], $date))
            {
                $allocated = allocated_fixed($event, $date, "X", $j);
            }
        }
    }
    sort_programme_by_date(false);   // resort programme - don't remove duplicates

    // now deal with 'nearest' and 'float' events
    foreach ($cfg['single_events'] as $j => $event)
    {
        if (strtolower($event['status'] == "schedule") and $event['date']['type'] == "nearest")
            // tries to allocate nearest relevant sailing day - if that is not a good tide tries the next nearest day
            // if still bad tide - give up
        {
            $date = $event['date']['value'];
            if (in_period($_REQUEST['date-start'], $_REQUEST['date-end'], $date))
            {
                $weekday = $event['date']['day'];
                $nearest_day = get_nearest_day($weekday, $date);   // get nearest day

                $allocated = allocated_fixed($event, $nearest_day, "M", $j);

                if (!$allocated)  // tide not ok - look at next nearest day
                {
                    if (strtotime($nearest_day) <= $event['date']['value'])
                    {
                        $nearest_day = get_next_day($weekday, $nearest_day);
                    }
                    else
                    {
                        $nearest_day = get_previous_day($weekday, $nearest_day);
                    }

                    $allocated = allocated_fixed($event, $nearest_day, "M", $j);
                }
            }
        }

        elseif (strtolower($event['status'] == "schedule") and $event['date']['type'] == "float")
            // allocates floating even to first suitable day after the 'earliest' date specified
        {
            if (period_overlap($_REQUEST['date-start'], $_REQUEST['date-end'], $event['date']['earliest'], $event['date']['latest']))
            {
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

        // check that tide meets criteria for multiple even
        if (tide_better_than($event['date'], $cfg['settings']['multiple_races']['tide']))
        {
            // check that only one race is currently scheduled for this date, it is a race and a series event
            $rs = all_events_for_date_in_programme($event['date']);
            // FIXME how do I know this is a single or series event
//            if (count($rs) < $cfg['single_events']["{$event['code']}"]['time']['max_starts'])
//            {
                $num_events = count($rs);
                foreach ($rs as $j=>$ev)
                {
                    // must be a racing event and a series event
                    if ($ev['type'] == "racing" and !empty($ev['series_code']))
                    {
                        // must not exceed max number of races per day and not be a fixed start time series
                        if ($num_events < $cfg['series_events']["{$event['code']}"]['time']['max_starts'] and
                            $cfg['series_events']["{$event['code']}"]['time']['type'] != "fixed")
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
                            $new_start_time_1 = u_roundminutes($s->format("H:i"), $cfg['settings']['round_start_mins']);
                            $pg[$k]['start_time'] = $new_start_time_1;

                            // add another race (settings: interval)  after the first race)

                            $dtime = decode_delta_time($cfg['settings']['multiple_races']['interval']);
                            $s = (new DateTime($new_start_time_1))->add(new DateInterval("PT{$dtime['hrs']}H{$dtime['min']}M"));
                            $new_start_time_2 = u_roundminutes($s->format("H:i"), $cfg['settings']['round_start_mins']);
                            $ev['start_time'] = $new_start_time_2;

                            // create new record
                            $pg[] = $ev;
                            $num_events++;
                            $new_races++;
                        }
                    }
                }
 //           }
        }
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
    $hdr_arr = array("id","date","time","name","series","type","format","entry_type",
        "restricted","tide_time","tide_height","notes","weblink");
    $write = fputcsv($file, $hdr_arr);
    if ( $write === false ) { $status ="2"; }  // header error

    // write data
    foreach ($pg as $k=>$event)
    {
        $out_arr = array (
            "id"         => "",
            "date"       => $event['date'],
            "time"       => $event['start_time'],
            "name"       => $event['event_name'],
            "series"     => $event['series_code'],
            "type"       => $event['type'],
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
        $csv_file = $_SESSION['basepath']."/tmp/{$cfg['file']}";
        $bufr.=<<<EOT

        <div class="pull-right"><a class="btn btn-primary btn-lg" href="$file" role="button">Create Import File</a></div>
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
            <th>Event</th>
            <th>Type</th>
            <th>No. Scheduled</th>
            <th>Dates</th>
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
                $pg[] = array(
                    "date"=>date("Y-m-d",strtotime($series['date']['value'])),
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
//        $et = explode(":",$cfg['settings']['tidal']['before_hw']);
//        $lt = explode(":",$cfg['settings']['tidal']['after_hw']);

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

        if ($date == "2020-07-19") {
            echo "$date: $tide_1_rs $tide_2_rs<br>";
            echo "tide: {$tide['hw1_time']} {$tide['hw2_time']}<br>";
            echo "e/l start: " . $event_cfg['time']['earliest'] . " " . $event_cfg['time']['latest'] . "<br>";
            echo "t1: " . $t1_early->format("H:i") . " " . $t1_late->format("H:i") . "<br>";
            echo "t2: " . $t2_early->format("H:i") . " " . $t2_late->format("H:i") . "<br>";
            echo "t3: " . $t3_early->format("H:i") . " " . $t3_late->format("H:i") . "<br>";
        }
        // get best tide

        if ($tide_1_rs >= $tide_2_rs)
        {
            if ($date == "2020-07-19")
            {
                echo "tide 1<br>";
            }
            $tidal = array("status" => $tide_code[$tide_1_rs], "tide_num" => 1, "tide_height" => $tide['hw1_height'],
                "tide_time" => $tide['hw1_time'], "start_time" => "");
        }
        else
        {
            if ($date == "2020-07-19") {
                echo "tide 2<br>";
            }
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
// note this returns all dates on a particular day
    global $pg;

    $rs = array();
    foreach ($pg as $k=>$event)
    {
        if (strtotime($event['date']) == strtotime($date)) { $rs[] = $event; }
    }

    return $rs;
}

function tide_better_than ($date, $required, $tide_status="")
{
    global $pg;

    $tide_status_val = array(1=>"X", 2=>"M", 3=>"G", 4=>"E");
    $rs = false;
    $k = is_date_in_programme($date);

    if ($k !== false)
    {
        //echo "$date: $tide_status|{$pg[$k]['tidal_status']}<br>";
        if(empty($tide_status)) { $tide_status = $pg[$k]['tidal_status']; }
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

function get_start_time($tide_hw, $data)
{
    global $cfg;
    $round     = $cfg['settings']['round_start_mins'];
    $earliest  = $data['time']['earliest'];
    $latest    = $data['time']['latest'];
    $tide_time = decode_delta_time($cfg['settings']['tidal']['preferred']);

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

function allocate_event($type, $data, $tidal, $event_code)
{
    global $cfg;

    $start = $tidal['start_time'];
    if (empty($start)) { $start = get_start_time($tidal['tide_time'], $data); }

    $event = array(
        "state"      => "S",
        "start_time"  => $start,
        "event_name"  => $data['name'],
        "tidal_time"  => $tidal['tide_time'],
        "tidal_height"=> $tidal['tide_height'],
        "tidal_status"=> $tidal['status'],
        "tidal_num"   => $tidal['tide_num'],
        "type"        => $data['type']['category'],
        "format"      => $data['type']['format'],
        "entry_type"  => $cfg['settings']['signon'],
        "restricted"  => $data['type']['access'],
        "notes"       => "",
        "weblink"     => "",
        "code"        => $event_code
    );   // array doesn't have date allocated here as this may be an update to an existing record

    $type != "series" ? $event["series_code"] = "" : $event["series_code"] = $data['type']['code']."-".substr($cfg['settings']['year'], -2);

    return $event;
}

function allocated_fixed($event, $date, $requested_tide, $cfg_code)
{
    global $pg;

    $allocated = false;
    $key = is_date_in_programme($date);
    $tidal = assess_tide($event, $date);  // assess tide for date
    if (!empty($tidal) and tide_better_than($date, $requested_tide, $tidal['status']))  // check is useful tide
    {
        $event_data = allocate_event("event", $event, $tidal, $cfg_code);
        if ($event_data)
        {

            if ($key)                                     // update existing programme record
            {
                $pg[$key] = array_merge($pg[$key], $event_data);
                $allocated = true;
            }
            else                                          // create new programme record
            {
                $event_data['date'] = date("Y-m-d", strototime($date));
                $pg[] = $event_data;
                $allocated = true;
            }
        }
    }
    else
    {
        $upd = array(
            "tidal_time"  => $tidal['tide_time'],
            "tidal_height"=> $tidal['tide_height'],
            "tidal_status"=> $tidal['status'],
            "tidal_num"   => $tidal['tide_num'],
        );
        $pg[$key] = array_merge($pg[$key], $upd);
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

    $pg = array_values($pg);   // reindex
}













