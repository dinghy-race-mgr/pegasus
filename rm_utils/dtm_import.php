<?php
/**
 *
 * dtm_import
 * Updates duty information in the rm_admin programme table from the latest information in dutyman via a csv file
 *
 * arguments:
 *  pagestate   str     page option [init | submit | apply]  default is init
 *  start date  str     start date in programme yyyy-mm-dd
 *  end date    str     end date in programme yyyy-mm-dd
 *  import file upload  csv file in normal dutyman export format
 *
 *
 * CURRENT STATUS
 * Check duties in programme database against dutyman csv export, and identifies possible swaps.
 * It reports this in dryrun mode.
 *
 * Current Issues
 *
 */

$loc  = "..";
$page = "dutyman_duty_import";
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
$styletheme = "flatly_";
$stylesheet = "./style/rm_utils.css";
$documentation = "./documentation/dutyman_utils.pdf";

session_id("sess-rmutil-".str_replace("_", "", strtolower($page)));
session_start();

// classes
require_once("$loc/common/lib/util_lib.php");
require_once("$loc/common/classes/db_class.php");
require_once("{$loc}/common/classes/template_class.php");
require_once("{$loc}/common/classes/event_class.php");
require_once("{$loc}/common/classes/rota_class.php");

// set templates
$tmpl_o = new TEMPLATE(array("$loc/common/templates/general_tm.php","./templates/layouts_tm.php", "./templates/dutyman_tm.php"));

// initialise session if this is first call
$init_status = u_initialisation("$loc/config/rm_utils_cfg.php", $loc, $scriptname);
if ($init_status)
{
    // set timezone
    if (array_key_exists("timezone", $_SESSION)) { date_default_timezone_set($_SESSION['timezone']); }

    // start log
    error_log(date('d-M H:i:s')." -- rm_util DUTYMAN SWAP SYNCH --------------------[session: ".session_id()."]".PHP_EOL, 3, $_SESSION['syslog']);

    // set initialisation flag
    $_SESSION['util_app_init'] = true;
}
else
{
    u_exitnicely($scriptname, 0, "one or more problems with script initialisation",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

// arguments
empty($_REQUEST['pagestate']) ?  $pagestate = "init" : $pagestate = $_REQUEST['pagestate'];
if ($pagestate == "apply")
{
    $pagestate = "submit";
    $dryrun = false;
    $mode = "apply";
}
elseif ($pagestate == "submit")
{
    $dryrun = true;
    $mode = "report";
}

// connect to database
$db_o = new DB();

$server_txt = "{$_SESSION['db_host']}/{$_SESSION['db_name']}";
$pagefields = array(
    "loc"           => $loc,
    "theme"         => $styletheme,
    "stylesheet"    => $stylesheet,
    "title"         => "Dutyman DUTY Import",
    "header-left"   => $_SESSION['sys_name']." <span style='font-size: 0.4em;'>[$server_txt]</span>",
    "header-right"  => "synchronise duty info from dutyman ...",
    "body"          => "",
    "confirm"       => "Synchronise",
    "footer-left"   => "",
    "footer-center" => "",
    "footer-right"  => "",
);

/* ------------ confirm run script page ---------------------------------------------*/

if ($pagestate == "init")
{
    $_SESSION['args'] = array();

    // present form to select json file for processing (general template)
    $formfields = array(
        "instructions"  => "Updates duty allocations in raceManager with duty swaps exported from dutyman in csv format.  
                       This is used to reflect duty swaps made in dutyman in the racemanager programme.  
                       See <a href='$documentation' target='_BLANK'>Detailed Instructions</a> for more details<br><br>
                       <span class='text-info'>After completing the update - use the Update Website option in the 
                       Administration application to make the changes visible on your website</span><br>",
    );
    $pagefields['body'] =  $tmpl_o->get_template("dtm_duty_import_form", $formfields, array());

    // render page
    echo $tmpl_o->get_template("basic_page", $pagefields, array());
}

/* ------------ submit page ---------------------------------------------*/

elseif ($pagestate == "submit")
{

    // create dutymap
    $dutymap = create_dutymap();

    // check arguments passed
    if (empty($_SESSION['args']))
    {
        $start = "";
        $end_date = "";

        // move file uploaded to raceManager tmp/uploads
        if ($_FILES["dutymanfile"]["error"] == 0)
        {
            $uploads_dir = "../tmp/uploads";
            $tmp_name = $_FILES["dutymanfile"]["tmp_name"];
            $name = basename($_FILES["dutymanfile"]["name"]);
            move_uploaded_file($tmp_name, "$uploads_dir/$name");
        }
        else
        {
            u_exitnicely($scriptname, 0, "Form Error - uploaded file supplied not present or invalid", "resubmit script with valid dutyman export file",
                array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
        }

        $_SESSION['args'] = array(
            "set"   => true,
            "start" => "",
            "end"   => "",
            "file"  => "$uploads_dir/$name"
        );
    }

    // read csv_file - get start and end dates for processing from the dutyman file
    $start_date = "";
    $end_date = "";
    $dtm_arr = read_csv_file($_SESSION['args']['file'], $scriptname);
    $_SESSION['args']['start'] = $start_date;
    $_SESSION['args']['end'] = $end_date;
    check_arguments($_REQUEST, $scriptname);

    /*
     * DTM array                      <-- data obtained from dutyman csv file
     * [date] => 2025-01-12
     * [start] => 15:30
     * [duty] => Race Officer
     * [dutycode] => ood_p
     * [event] => Frostbite Series
     * [swapable] => YES
     * [person] => Colin Lee
     * [serial] => ood_p colin lee    <-- added later - used for sorting
     */

    // get events and duties in period
    $event_o = new EVENT($db_o);
    $rota_o = new ROTA($db_o);
    $events = $event_o->get_events_inperiod(array("active"=>"1"), $_SESSION['args']['start'], $_SESSION['args']['end'], "live", $race = false);
    $prg_arr = $rota_o->get_duties_inperiod("", $_SESSION['args']['start'], $_SESSION['args']['end']);

    /*
     * PRG array                           <-- data obtained from programme t_event/t_eventduty
     * [id] => 3269
     * [dutycode] => galley
     * [person] => Bart Stockman
     * [phone] =>
     * [email] =>
     * [notes] => Duties with Lucinda Stockman
     * [event_name] => Winter Woolley Trophy Race
     * [event_date] => 2025-01-05
     * [eventid] => 11104
     * [dutyname] => Galley
     * [serial] => galley bart stockman    <-- added later - used for sorting
     */

    // loop through events comparing dutyman and programme duty information

    $events ? $num_events = count($events) : $num_events = 0;
    $i = 0;
    $report_bufr = "";
    $swaps = 0;
    $missing = 0;
    $crossdutyswap = 0;    // swap involving a change of duty
    $swap_list = "";       // list of ids that have been swapped
    if ($num_events > 0)
    {
        if ($mode == "apply")  // take a backup copy of t_eventduty to provide rollback option
        {
            $cols = array("id", "eventid", "dutycode", "person", "swapable", "phone", "email", "notes", "memberid", "upddate", "updby", "creatdate");
            $outfile = $_SESSION['basepath']."\data\dutyman\backups\\eventduty_".date("ymd-Hi").".csv";

            $rows = $db_o->db_query("SELECT `id`, `eventid`, `dutycode`, `person`, `swapable`, `phone`, `email`, `notes`, `memberid`, `upddate`, `updby`, `createdate` FROM t_eventduty");
            $err = create_csv_file($cols, $rows, $outfile);
            $err == 0 ? $result = "file created" : $result = "file not created [$err]";
        }

        foreach ($events as $k=>$event)
        {
            $event_bufr = "";
            $skip_event = false;
            $changes = false;
            $i++;

            $prg_duty = set_event_duties("prg", $prg_arr, $event);    // duties from raceManager programme
            empty($prg_duty) ? $num_prg = 0 : $num_prg = count($prg_duty);

            $dtm_duty = set_event_duties("dtm", $dtm_arr, $event);    // duties from dutyman
            empty($dtm_duty) ?  $num_dtm = 0 :  $num_dtm = count($dtm_duty);

            // deal with events having no duty records in either the racemanager programme and/or dutyman
            if ($num_dtm == 0)
            {
                $event_bufr.= "<li>no dutyman records available for this event</li>";
                $skip_event = true;
            }
            elseif ($num_prg == 0)
            {
                if ( $num_dtm > 0 )
                {
                    if ($mode == "report")
                    {
                        $event_bufr.= "<li>no duties recorded in raceManager - will create $num_dtm duty records from dutyman data</li>";
                    }
                    else
                    {
                        $event_bufr.= "<li>duties missing  in raceManager - created $num_dtm duty records from dutyman records</li>";
                    }

                    if (!$dryrun)
                    {
                        $ins = add_event_duties($event['id'], $dtm_duty);
                        $skip_event = true;
                        $changes = true;
                    }
                }
                else
                {
                    $event_bufr.= "<li>no duties recorded in raceManager</li>";
                    $skip_event = true;
                }
            }

            if (!$skip_event)                                          // skip detailed duty analysis if already processed
            {

                // tackle potential individual duty changes
                if ($num_prg == $num_dtm)                              // same no. of duties in both systems
                {
                    foreach ($prg_duty as $j => $prg) {
                        $continue = true;
                        $dtm = $dtm_duty[$j];

                        // check #1 missing data in dutyman
                        if ($dtm['person'] == "|no name|")
                        {
                            $event_bufr.= "<li>{$prg['dutyname']} duty:  missing information in dutyman</li>";
                            $missing++;
                            $continue = false;
                        }

                        // check #2 dutycodes don't match
                        if ($continue)                                  // handle situation where swap is change of duty type
                        {
                            if ($prg['dutycode'] != $dtm['dutycode']) {
                                $event_bufr.= "<li>{$prg['dutyname']} duty:  swapped duty not the same duty type [duty: {$dtm['duty']}]</li>";
                                $crossdutyswap++;
                                $continue = false;
                            }
                        }

                        // check #3 named duty person doesn't match
                        if ($continue)                                  // handle swap
                        {
                            if (strtolower($prg['person']) != strtolower($dtm['person']))       // difference between raceManager and Dutyman
                            {
                                if ($dtm['person'] != "|no name|")
                                {
                                    if ($mode == "report")
                                    {
                                        $event_bufr.= "<li>{$prg['dutyname']} duty: dutyman has swap of {$dtm['person']} replacing {$prg['person']}</li>";
                                    }
                                    else
                                    {
                                        $event_bufr.= "<li>{$prg['dutyname']} duty: swapped - {$dtm['person']} replacing {$prg['person']}</li>";
                                    }
                                    $swaps++;
                                    if (!$dryrun)
                                    {
                                        $ins = update_event_duty($prg['id'], $dtm['person'], $prg['person'], $prg['dutyname']);
                                        if ($ins)  // if successful update $prg record
                                        {
                                            $prg_duty[$j]['person'] = $dtm['person'];
                                            $swap_list.= $prg['id'].",";
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                else
                {
                    $event_bufr.= "<li>different number of duties in raceManager ($num_prg) and dutyman ($num_dtm)</li>";
                }
            }

            // create duty table
            $table_bufr = create_duty_table_rows($num_prg, $num_dtm, $prg_duty, $dtm_duty, $swap_list);


            if (empty($event_bufr))
            {
                $t_style = "font-size: 0.8em";
                $event_bufr_display = "";
            }
            else
            {
                $t_style = "color: darkred; font-size: 0.8em" ;
                $event_bufr_display = "<blockquote style='font-size: 0.9em'><ul>$event_bufr</ul></blockquote>";
            }
            $report_bufr.=<<<EOT
<div class="panel panel-default">
    <div class="panel-heading" role="tab" id="heading$i">
        <p class="panel-title" style="$t_style">
            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse$i" aria-expanded="true" aria-controls="collapse$i">
              {$event['event_name']} - {$event['event_date']} [{$event['event_start']}]
            </a>
        </p>
    </div>
    <div id="collapse$i" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading$1">
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-6">
                    $event_bufr_display
                </div>
                <div class="col-sm-6">
                    <table class="table table-condensed rm-text-xs">
                        <thead><tr><th>&nbsp;</th><th>Programme</th><th>Dutyman</th></tr></thead>
                        <tbody>$table_bufr</tbody>
                    </table>
                </div>           
            </div>           
        </div>
    </div>
</div>

EOT;
        }
    }


    if ($num_events == 0)
    {
        $pagefields['body'] = $tmpl_o->get_template("dtm_duty_import_report",
            array("report" => "", "start" => $_SESSION['args']['start'], "end" => $_SESSION['args']['end']),
            array("mode" => $mode, "dryrun" => $dryrun, "swaps" => 0, "missing" => 0, "crossswaps" => 0, "numevents" => 0) );
        echo $tmpl_o->get_template("basic_page", $pagefields, array() );
    }
    else
    {
        $pagefields['body'] = $tmpl_o->get_template("dtm_duty_import_report",
            array("report" => $report_bufr, "start" => $_SESSION['args']['start'], "end" => $_SESSION['args']['end']),
            array("mode" => $mode, "dryrun" => $dryrun, "swaps" => $swaps, "missing" => $missing, "crossswaps" => $crossdutyswap, "numevents" => $num_events) );
        echo $tmpl_o->get_template("basic_page", $pagefields, array() );
    }

    $db_o->db_disconnect();
    exit();
}
else
{
    u_exitnicely($scriptname, 0, "SYSTEM ERROR page state [{$_REQUEST['pagestate']}] not recognised - please contact System Manager",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}


function check_arguments($args, $scriptname)
{
    global $importfile;

    // start/end date
    if (empty($_SESSION['args']['start']) or empty($_SESSION['args']['end']))
    {
        u_exitnicely($scriptname, 0, "Form Error - Start and/or End date are missing", "resubmit script with valid date values",
            array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
    }
    elseif (strtotime($_SESSION['args']['start']) > strtotime($_SESSION['args']['end']))
    {
        u_exitnicely($scriptname, 0, "Form Error - Start date is after End date", "resubmit script with valid date values",
            array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
    }
    elseif (!strtotime($_SESSION['args']['start']))
    {
        u_exitnicely($scriptname, 0, "Form Error - Start date is not a valid date", "resubmit script with valid date values",
            array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
    }
    elseif (!strtotime($_SESSION['args']['end']))
    {
        u_exitnicely($scriptname, 0, "Form Error - End date is not a valid date", "resubmit script with valid date values",
            array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
    }


    // csv file
    if (empty($_SESSION['args']['file']))
    {
        u_exitnicely($scriptname, 0, "Argument Error - file supplied not present or invalid", "resubmit script with valid dutyman export file",
            array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
    }
    else
    {
        $importfile = $_SESSION['args']['file'];
    }

    return;

}


function create_dutymap()
{
    global $db_o;

    $dutymap = array();
    $rs = $db_o->db_get_rows("SELECT code, label  FROM `t_code_system` WHERE `groupname` LIKE 'rota_type';");
    foreach($rs as $row)
    {
        $dutymap[$row['code']] = $row['label'];
    }

    return $dutymap;
}
function create_csv_file($cols, $rows, $outfile)
{
   // create output file
    $err = 0;
    $fp = fopen($outfile, 'w');
    if ($fp)
    {
        $r = fputcsv($fp, $cols, ',');
        if (!$r) { $err = 2;}

        if ($err == 0)
        {
            foreach ($rows as $row)
            {
                $r = fputcsv($fp, $row, ',');
                if (!$r) {$err = 3; }
                if ($err != 0) { break; }
            }
        }
        fclose($fp);
    }
    else
    {
        $err = 1;
    }
    return $err;
}

function read_csv_file($file, $scriptname)
{
    global $dutymap, $start_date, $end_date;

    ini_set('auto_detect_line_endings', true);
    $arr = array();
    $i = 0;

    if ($handle = fopen($file, "r"))
    {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
        {

            if (strtolower($data[0]) != "duty date")                             // ignore first line (fields) if present
            {
                $i++;
                $date = str_replace('/', '-', $data[0]);                         // set time to YYYY-MM-DD format
                if ($i == 1) { $start_date = date("Y-m-d", strtotime($date)) ; } // get date for first record

                $dutycode = array_search($data[2],$dutymap);                     // get dutycode - empty if not found

                if (empty($data[12]) or empty($data[13]))      // format person name
                {
                    $person = "|no name|";
                }
                else
                {
                    $person = trim($data[12])." ".trim($data[13]);
                }

                $duty = array(
                    "event_date" => date('Y-m-d', strtotime($date)),
                    "start"    => $data[1],
                    "duty"     => $data[2],
                    "dutycode" => $dutycode,
                    "event"    => substr($data[3], 0, strpos($data[3], '[') - 1),
                    "swapable" => strtoupper($data[5]),
                    "person"   => $person
                );

                $duty_chk = check_duty_details($duty);      // check to see if missing data prevents this record being included
                if ($duty_chk == 0) { $arr[] = $duty; }

                $end_date = date("Y-m-d", strtotime($date));                       // set end date to be last record processed
            }
        }
        fclose($handle);
    }
    else
    {
        u_exitnicely($scriptname, 0, "File Error - Cannot read selected csv file [$file]",
            "check and correct csv file including access permissions and then resubmit the script",
            array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
    }

    return $arr;
}

function update_event_duty($id, $person, $swap, $duty)
{
    // changes person doing the duty in the raceManager programme
    global $db_o;
    $status = false;
    $dbg = false;

    if ($dbg) { echo "<pre>update_event_duty : $id)</pre>"; }

    $report = "";
    if ($id)
    {
        $person = addslashes($person);
        $swap = addslashes($swap);
        $sql = "update t_eventduty set `person` = '$person', `notes` = '| dutyman swap from $swap to $person' where `id` = $id ";
        $upd = $db_o->db_query($sql);
        $report.= "Updated $duty duty - allocated to $person<br>";
        if ($upd) { $status = true; }
    }

    if ($dbg)
    {
        echo $report;
        echo "|$upd|<br>";
        echo "$sql<br>";}

    return $status;
}


function add_event_duties($eventid, $arr)
{
    // uses dutyman duty information to update programme - assumes no records exist for programme
    global $db_o;
    $status = false;
    $dbg = false;

    if ($dbg) { echo "<pre>add_event_duties : $eventid)</pre>"; }
    $duty = array();
    $report = "";
    foreach ($arr as $row)
    {
        $duty[] = array(
            "eventid"  => $eventid,
            "dutycode" => $row['dutycode'],
            "person"   => $row['person'],
            "swapable" => 1,
            "updby"    => "dtm_duty_import"
        );
        $ins = $db_o->db_insert("t_eventduty", $duty);
        $report.= "Created {$row['dutycode']} : {$row['person']} : $ins<br>";
    }
    if ($dbg) { echo $report; }

    if ($ins) { $status = true; }

    return $status;
}

function check_duty_details($duty)
{
    $err = 0;

    if (empty($duty['dutycode']))                                 // dutycode not recognised
    {
        $err = 1;
    }
    elseif (empty($duty['person']) and empty($duty['dutycode']))  // dutycode and person missing
    {
        $err = 2;
    }
    elseif (empty($duty['event_date']) and empty($duty['start'])) // dutycode and start date mssing
    {
        $err = 3;
    }

    return $err;
}

function set_event_duties($mode, $arr, $event)
{
    $duty_arr = array();
    foreach($arr as $k => $row)
    {
        $found  = false;
        if ($mode == "prg")
        {
            if ($row['event_date'] == $event['event_date'] and $row['eventid'] == $event['id'])
            {
                $found = true;
            }
        }
        else
        {
            if ($row['event_date'] == $event['event_date'] and $row['start'] == $event['event_start'])
            {
                $found = true;
            }
        }

        if ($found)    // add serial as sort field
        {
            if (empty($row['person']))   { $row['person'] = "|no name|"; }
            if (empty($row['dutycode'])) { $row['dutycode'] = "|no duty|"; }
            $row['serial'] = strtolower($row['dutycode']." ".$row['person']);    // used for searching and sorting
            $duty_arr[] = $row;
        }
    }

    $num = 0;
    if (!empty($duty_arr))
    {
        $num = count($duty_arr);
        if ($num > 1) { usort($duty_arr, function($a, $b) { return $a['serial'] <=> $b['serial']; }); }
    }

    return $duty_arr;
}

function create_duty_table_rows($num_prg, $num_dtm, $prg_duty, $dtm_duty, $swap_list)
{
    $table_rows = "";
    $num_prg >= $num_dtm ? $lines = $num_prg : $lines = $num_dtm;
    for ($m=0; $m<$lines; $m++)
    {
        ($prg_duty[$m]['dutyname'] == "|no duty|" or empty($prg_duty[$m]['dutyname'])) ? $col1 = "<span style='color: darkred;'>|no duty|</span>": $col1 = $prg_duty[$m]['dutyname'];

        if ($prg_duty[$m]['person'] == "|no name|")
        {
            $col2 = "<span style='color: darkred;'>|no name|</span>";
        }
        else
        {
            if (strpos($swap_list, "{$prg_duty[$m]['id']},") !== false)
            {
                $col2 = "<span style='color: blue;'>{$prg_duty[$m]['person']}</span>";
            }
            else
            {
                $col2 = $prg_duty[$m]['person'];
            }
        }


        if (!key_exists($m, $dtm_duty))
        {
            $col3 = "<span style='color: darkred;'>|no name|</span>";
        }
        else
        {
            $dtm_duty[$m]['person'] == "|no name|" ? $col3 = "<span style='color: darkred;'>|no name|</span>": $col3 = $dtm_duty[$m]['person'];
        }

        $table_rows.= <<<EOT
<tr><td style="padding: 0px;width:25%;">$col1</td><td style="padding: 0px;width:30%;">$col2</td><td style="padding: 0px;width:30%;">$col3</td></tr>
EOT;
    }

    return $table_rows;
}

function html_flush()
{
    echo str_pad('',4096)."\n";
    ob_flush();
    flush();
}


