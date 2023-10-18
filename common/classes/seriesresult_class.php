<?php
/**
 *  SERIES RESULT class
 * 
 *  Handles production of series result - based on data in t_results.
 *  Supports merging of classes (e.g. lasers) as an option
 *  Supports three different average point schemes
 *  Supports tie breaking as defined in Appendix A.8 of the RRS
 *  Produces unformatted and formatted output
 * 
 *  METHODS
 *     __construct
 * 
 *
*/

/*
ISSUES
-   MERGING
        - store merged lists (t_series field) - if noting in field no merge             DONE
        - setup config in $this->series_data                                            DONE
        - turn merging on/off in series_result (but how to deal with event_list)        DONE
        - implement merge_sailors                                                       ****

- TIE RESOLUTION
        - test                                                                          DONE
        - make sure the points can include decimal values                               DONE
          (because race ties/ avg points could result in position being non-integer)

- DATA SETUP
        - have test data option - read in from json file                                 DONE
        - implement getting same date structure from database                            DONE
        - include new public function to generate output json file                       DONE
        - add validation of data                                                         DONE
        - implement getting events list                                                  DONE
        - add info data structure (stats on races) set_statsinfo                         DONE


- HTML OUTPUT
        - check get_series_name                                                          DONE
        - in result_class the template class needs to be defined externally              ****   <<<<<<<<<<<<<<
              - is this right (adopt the same solution for series)
        - implement BASIC OUTPUT                                                         DONE
        - implement STYLED output                                                        ****
        - deal with options - racemanagre.ini or t_ini or series                         ****

TESTING
        - check read in of json                                                          DONE
        - check data validation                                                          ----   << will do when I run system testing
        - test series result production with test data and debug output                  DONE
        - test special cases
             - race after abandoned race - check dnc                                     DONE
             - data for more than one fleet (different no. of races)                     DONE
             - two way tie with 8.1 resolution                                           DONE
             - two way tie with 8.2 resolution                                           DONE
             - three way tie with 8.2 and 8.1 resolution                                 DONE
             - one competitor in a race                                                  DONE
             - check average score calc - three options (races/competed/counting)        DONE
             - include non-integer penalty                                               DONE
             - merged classes (two types)                                                ****

TIDY CODE
- move json input to url parameter
- tidy up debug code
- tidy up obsolete code
- sort out $LOC issue
- output JSON files to ../../testing/results/ - and also read from there

*/


/**
 * Class SERIES
 */

/*
// for test and results debugging purposes
$loc        = "..";       // <--- relative path from script to top level folder
$page       = "seriesresult";     //
$scriptname = basename(__FILE__);
require_once ("$loc/lib/util_lib.php");
require_once ("$loc/classes/db_class.php");
require_once ("$loc/classes/template_class.php");
require_once ("$loc/classes/event_class.php");
require_once ("$loc/classes/raceresult_class.php");

session_start();

if (!array_key_exists('db_host', $_SESSION))
{
    u_initconfigfile("../../config/common.ini");
}
$_SESSION['sql_debug'] = false;
$_SESSION['app_name'] = "racebox";
$_SESSION['dbglog'] = "../../logs/dbg/" . $_SESSION['app_name'] . "_" . date("Y-m-d") . ".log";
$_SESSION['result_public_url'] = "http://localhost/results_archive";

//echo "<pre>".print_r($_SESSION, true)."</pre>";
//exit();

$db_o = new DB;

// establish object with data held in test file
$series_code = "SUMMER-22";
$opts = array(
    "inc-pagebreak" => false,                                                // page break after each fleet
    "inc-codes"     => true,                                                 // include key of codes used
    "inc-club"      => false,                                                // include club name for each competitor
    "inc-turnout"   => true,                                                 // include turnout statistics
    "race-label"    => "number",                                             // use race number or date for labelling races
    "club-logo"     => $_SESSION['baseurl']."/config/images/club_logo.jpg",  // if set include club logo
    "styles" => $_SESSION['baseurl']."/config/style/result_std.css"          // styles to be used
);
//$rs_o = new SERIES_RESULT($db_o, $series_code, $opts, false, "../../testing/results/testseriesresults_1.json");
$rs_o = new SERIES_RESULT($db_o, $series_code, $opts, false);

echo "Set data ...<br>";
$err = $rs_o->set_series_data();
if ($err)
{
    $err_detail = $rs_o->get_err();
    echo "</br>FATAL ERROR - SET DATA: </br>";
    echo "<table>";
    foreach ($err_detail as $detail)
    {
        echo <<<EOT
        <tr><td>code: {$detail['code']}</td><td>type: {$detail['type']}</td><td>{$detail['msg']}</td></tr>
EOT;
    }
    echo "</table>";
    exit("exiting on set data");
}

//debug echo "<pre>".print_r($rs_o->rst[29], true)."</pre>";

echo "Calc data ...<br>";
$err = $rs_o->calc_series_result();
if ($err)
{
    $err_detail = $rs_o->get_err();
    echo "</br>FATAL ERROR - CALC DATA: </br>";
    echo "<table>";
    foreach ($err_detail as $detail)
    {
        echo <<<EOT
        <tr><td>code: {$detail['code']}</td><td>type: {$detail['type']}</td><td>{$detail['msg']}</td></tr>
EOT;
    }
    echo "</table>";
    exit("exiting on calc data");
}
else  // results ok
{
    $filename = $rs_o->get_series_filename();
    $sys_detail = array(
        "sys_name" => "raceManager",                                          // system name
        "sys_release" => "Pegasus",                                           // release name
        "sys_version" => "10.1",                                              // code version
        "sys_copyright" => "Elmswood Software " . date("Y"),                  // copyright
        "sys_website" => "http://dinghyracemanager.wordpress.com/"
    );
    $series_status = "final";                                                 // draft|provisional|final
    $styles = file_get_contents($_SESSION['baseurl']."/config/style/result_classic.css");
    // website
    //$htm = $rs_o->series_render_basic();
    $tmpl_o = new TEMPLATE(array("../templates/general_tm.php", "../templates/series_results_tm.php"));
    $htm = $rs_o->series_render_styled($sys_detail, $series_status, $styles );
    echo $htm;
}
*/


class SERIES_RESULT
{
    private $db;

    //Method: construct class object
    public function __construct(DB $db, $series_code, $opts, $report = false, $testfile="")
    {

        $this->db = $db;                        // database connection
        $this->testfile = $testfile;            // file containing series data in RM format
        $this->report = $report;                // option to report to terminal (for debugging)
        //$this->report = true;
        $this->series_code = $series_code;      // series code e.g. SPRING-21

        $this->opts = $opts;                    // results display options

        $this->err_fatal = false;               // fatal error flag
        $this->err = array();                   // array of errors and warnings
                                                // error code, type (warning:fatal error), msg
                                                // 1 - missing input data
                                                // 2 - invalid input data

        
        $this->status_run = array("completed", "abandoned", "cancelled");   // fixme check that this is complete
        $this->status_complete = array ("completed");                       // fixme ditto

        $this->event_arr = array();              // array wih eventids in sequential order - indexed from 1

        $this->series = array();                // series information
        $this->fleets = array();                // information for each fleet in series
        $this->races  = array();                // information for each race in series
        $this->sailor = array();                // information for each sailor in series
        $this->sailor_fleet = array();          // information for each sailor in single fleet
        $this->rst    = array();                // result information for each sailor/race
        $this->codes_used = array();            // array of codes used in series results
        $this->merge_classes = array();         // groupings of boats with different rigs which can be scored as one
        $this->results = array();               // calculated series results

        $this->races_num = 0;                   // num races that have been attempted in series so far
        $this->races_complete = 0;              // races for which results exist

        $this->event_o = new EVENT($this->db);
    }



// --------- SET DATA METHODS ---------------------------------
    public function set_series_data($event_list="")
    {
        /*
         * Puts result data into class data arrays
         *
         * Data can be set in one of three ways
         *
         * If $_SESSION['testfile'] is set with a path/filename then will get data from a pre-saved json file.
         *
         * If just $series_code (e.g. SPRING-21) is provided - the list of events will be obtained from t_event with matching series_code
         *
         * If $series_code and $event_list is provided the series definition is taken from t_series with the events listed in $event_list
         *
         */


        if (empty($this->testfile))
        {
            if ($this->report) { u_writedbg("getting series data from database for {$this->series_code}",__FILE__,__FUNCTION__,__LINE__); }

            // get array of event ids in series and collect some basic information on races completed and turnouts
            $fatalerr = $this->set_eventarr($this->series_code, $event_list);
            if ($fatalerr) {
                return $fatalerr;
            } else {
                if ($this->report) { u_writedbg(".... event list". print_r($this->event_arr,true),__FILE__,__FUNCTION__,__LINE__); }
            }


            // get club detail and series configuration information
            $this->series = $this->set_seriesinfo($this->series_code);
            if ($this->err_fatal) {
                return $this->err_fatal;
            } else {
                if ($this->report) { u_writedbg(".... series config". print_r($this->series,true),__FILE__,__FUNCTION__,__LINE__); }
            }

            
            // get fleet configuration information
            $this->fleets = $this->set_fleetinfo($this->series['raceformat']);
            if ($this->err_fatal) {
                return $this->err_fatal;
            } else {
                if ($this->report) { u_writedbg(".... fleet config". print_r($this->fleets,true),__FILE__,__FUNCTION__,__LINE__); }
            }

            // get merge class data
            $this->merge_classes = $this->set_mergeclasses($this->series['merge']);
            if ($this->report and !empty($this->merge_classes))
            { u_writedbg(".... merge classes". print_r($this->merge_classes,true),__FILE__,__FUNCTION__,__LINE__);}

            // get races data
            $this->races = $this->set_raceinfo();
            if ($this->err_fatal) {
                return $this->err_fatal;
            } else {
                if ($this->report) {
                    u_writedbg(".... series race info". print_r($this->races,true),__FILE__,__FUNCTION__,__LINE__);}
            }

            // get competitor data
            $this->sailor = $this->set_sailorinfo();
            if ($this->err_fatal) {
                return $this->err_fatal;
            } else {
                if ($this->report) { u_writedbg(".... competitor info". print_r($this->sailor,true),__FILE__,__FUNCTION__,__LINE__); }
            }

            // get race results data
            $this->rst = $this->set_resultsinfo();
            if ($this->err_fatal) {
                return $this->err_fatal;
            } else {
                if ($this->report) { u_writedbg(".... results info". print_r($this->rst,true),__FILE__,__FUNCTION__,__LINE__); }
            }
        }
        else
        {
            // get data from input file
            if ($this->report) { u_writedbg(".... reading data from file ". $this->testfile,__FILE__,__FUNCTION__,__LINE__); }
            $this->set_datafromfile();
        }

        // get race counts
        $this->races_num = 0;
        $this->races_complete = 0;
        foreach ($this->races as $race)
        {
            $this->races_num++;
            if ($race['status'] == "sailed" or $race['status'] == "completed" or $race['status'] == "running")
            {
                $this->races_complete++;
            }
        }

        if ($this->races_num == 0)
        {
            $this->err_fatal = true;
            $this->err[] = array("code" => "2", "type" => "fatal",
                "msg" => "no races have been run for this series - cannot produce series result");
        }
        $this->series["races_num"] = $this->races_num;
        $this->series["races_complete"] = $this->races_complete;

        // get array of scoring codes used in this series
        $this->codes_used = $this->set_scoringcodes();
        if ($this->report and !empty($this->codes_used))
        {
            u_writedbg(".... scoring codes". print_r($this->codes_used,true),__FILE__,__FUNCTION__,__LINE__);
        }

        return $this->err_fatal;
    }

    private function set_eventarr($series_code, $event_list)
    {
        if (!empty($series_code))
        {
            if (empty($event_list))
            {
                // get events from t_event and series code
                $this->event_arr = $this->event_o->series_eventarr($series_code);
            }
            else
            {
                // get events from event_list
                $this->event_arr = explode(",", $event_list);
            }

            // check we have events
            $num_events = count($this->event_arr);
            if ($num_events > 0)
            {
                // reindex array to start at 1
                $this->event_arr = array_combine(range(1, $num_events), array_values($this->event_arr));
            }
            else
            {
                // fatal error - no events
                $this->err_fatal = true;
                $this->err[] = array("code"=>"3", "type"=>"fatal",
                    "msg"=>"no completed races found for series - cannot produce series result");
            }
        }
        else
        {
            // fatal error - no series definition
            $this->err_fatal = true;
            $this->err[] = array("code"=>"1", "type"=>"fatal",
                "msg"=>"no definition record for series $series_code - cannot produce series result");
        }

        return $this->err_fatal;
    }

    private function set_seriesinfo($series_code)
    {
        // get club detail and options
        $club = $this->db->db_getinivalues(true);

        // get series detail
        $detail = $this->event_o->event_getseries($series_code);

        if (empty($detail)) {
            // fatal error - no series definition
            $this->err_fatal = true;
            $this->err[] = array("code" => "1", "type" => "fatal",
                "msg" => "no definition record for series $series_code - cannot produce series result");
        }

        // convert discard string into array with discard indexed by race sequence
        $discard_arr = array();
        if (!empty($detail['discard'])) {
            $arr = explode(",", $detail['discard']);
            foreach ($arr as $k => $v) {
                $discard_arr[$k + 1] = $v;
            }
        }

        // convert nondiscard string into array with nondiscard indexed by race sequence
//        $nodiscard_arr = array();
////        if (!empty($detail['nodiscard'])) {
////            $arr = explode(",", $detail['nodiscard']);
////            foreach ($arr as $k => $v) {
////                $nodiscard_arr[$k + 1] = $v;
////            }
////        }

//        // convert score multiplier string into array with multiplier indexed by race sequence
//        $multiplier_arr = array();
//        if (!empty($detail['discard'])) {
//            $arr = explode(",", $detail['multiplier']);
//            foreach ($arr as $k => $v) {
//                $multiplier_arr[$k + 1] = $v;
//            }
//        }

        $data =  array(
            "clubname"     => $club['clubname'],
            "clubcode"     => $club['clubcode'],
            "cluburl"      => $club['clubweb'],
            "name"         => ucwords(strtolower($detail['seriesname'])),
            "code"         => $series_code,
            "type"         => $detail['seriestypename'],
            "notes"        => $detail['notes'],
            "raceformat"   => $detail['race_format'],
            "merge"        => $detail['merge'],
            "classresults" => $detail['classresults'],
            "avgscheme"    => $detail['avgscheme'],
            "discard"      => $discard_arr,
            "nodiscard"    => $detail['nodiscard'],
            "multiplier"   => $detail['multiplier'],
            "maxduty"      => $detail['maxduty'],
            "dutypoints"   => $detail['dutypoints'],
        );
        return $data;
    }

    private function set_fleetinfo($race_format)
    {

        $detail = $this->event_o->event_getfleetcfg($race_format);

        if (empty($detail)) {
            // fatal error - no fleet configuration info
            $this->err_fatal = true;
            $this->err[] = array("code" => "1", "type" => "fatal",
                "msg" => "no fleet definition records race format used in series - cannot produce series result");
        }

        $data = array();
        foreach ($detail as $fleet)
        {
            $data[$fleet['fleet_num']] =  array(
                "name"         => ucwords(strtolower($fleet['fleet_name'])),
                "py"           => $fleet['py_type'],
                "scoring"      => $fleet['scoring'],
                "races_run"    => 0,
                "races_scored" => 0,
                "num_entries"  => 0,
                "turnout_avg"  => 0,                       // average turnout for this fleet
                "turnout_max"  => 0,                       // max turnout for this fleet
                "turnout_min"  => 0,                       // min turnout for this fleet
            );
        }

        return $data;
    }

    private function set_mergeclasses($merge_str)
    {
        /* creates 2d array of groups of classes to be merged
           t_series merge field: laser,laser 4.7,laser radial|rs100 8.4,rs100 10.2

           $merge_classes = array(
                    "1" => array ("laser", "laser 4.7", "laser radial")
                    "2" => array ("rs100 8.4", "rs100 10.2")
                    )
       */
        $merge = array();
        $i = 0;
        $data = explode("|", $merge_str);
        foreach ($data as $list)
        {
            if (!empty($list))
            {
                $i++;
                $items = explode(",", $list);
                if (count($items) > 1)
                {
                    $merge[$i] = array();
                    foreach ($items as $class)
                    {
                        $merge[$i][] = strtolower(trim($class));
                    }
                }
            }
        }
        return $merge;
    }

    private function set_raceinfo()
    {
        $data = array();
        $format = 0;
        $i = 0;


        foreach($this->event_arr as $eventid)
        {
            $i++;
            // get event details
            $event = $this->event_o->get_event_byid($eventid);

            $result_o = new RACE_RESULT($this->db, $eventid);

            $rfile = $result_o->get_result_files(array("eventid"=>$eventid, "folder"=>"races"));

//            u_writedbg("RFILE: ".print_r($rfile,true), __FILE__, __FUNCTION__, __LINE__);
//            u_writedbg($_SESSION['result_public_url'], __FILE__, __FUNCTION__, __LINE__);
//            u_writedbg("count rfile: ".count($rfile), __FILE__, __FUNCTION__, __LINE__);

            if (empty($rfile[0]))
            {
                $event['url'] = "";
            }
            else
            {
                $event['url'] = $_SESSION['result_public_url']."/".$rfile[0]['eventyear']."/".$rfile[0]['folder']."/".$rfile[0]['filename'];
            }
//            u_writedbg("eventurl: ".$event['url'], __FILE__, __FUNCTION__, __LINE__);

            // event format consistency check
            if ($i == 1 )
            {
                $format = $event['event_format'];
            }
            elseif ($i > 1)
            {
                if ($format != $event['event_format'])
                {
                    $this->err_fatal = true;
                    $this->err[] = array("code" => "2", "type" => "fatal",
                        "msg" => "the race format is not consistent for all races in the series - cannot produce series result");
                }
            }

//            // check if non discardable race
//            if (array_key_exists($i, $this->series['nodiscard']))
//            {
//                $no_discard = $this->series['nodiscard'][$i];
//            }
//
//            // check if race has multiple score
//            if (array_key_exists($i, $this->series['multiplier']))
//            {
//                $multiplier = $this->series['multiplier'][$i];
//            }

            // FIXME need to handle nodiscard and multiplier for last race correctly - just dummy set for now

            $data[$i] =  array(

                "eventid"      => $event['id'],
                "date"         => $event['event_date'],
                "name"         => $event['event_name'],
                "no_discard"   => false,
                "multiplier"   => false,
                'status'       => $event['event_status'],
                'notes'        => $event['result_notes'],
                'url'          => $event['url']
            );
        }

        return $data;
    }

    private function set_sailorinfo()
    {
        // get unique competitors for series
        $list = implode(",", $this->event_arr);

        $query = "SELECT a.competitorid, b.helm, b.crew, b.club, c.classname as class, b.sailnum, max(fleet) as fleet, max(pn) as pn
                  FROM `t_result` as a JOIN t_competitor as b ON a.competitorid=b.id JOIN t_class as c ON b.classid=c.id 
                  WHERE eventid IN ($list) GROUP BY a.competitorid";

        // u_writedbg("$query", __FILE__, __FUNCTION__, __LINE__); //debug:);
        $sailors = $this->db->db_get_rows($query);

        if (empty($sailors))
        {
            $this->err_fatal = true;
            $this->err[] = array("code" => "1", "type" => "fatal",
                "msg" => "no competitors found for this series - cannot produce series result");
        }

        $data = array();
        foreach ($sailors as $i=>$sailor)
        {
            $data[$i] =  array(
                "id"      => $sailor['competitorid'],
                "helm"    => $sailor['helm'],
                "crew"    => $sailor['crew'],
                "club"    => $sailor['club'],
                "class"   => $sailor['class'],
                'sailno'  => $sailor['sailnum'],
                'fleet'   => $sailor['fleet'],
                'pn'      => $sailor['pn'],
            );
        }

        return $data;
    }

    private function set_resultsinfo()
    {
        $data = array();

        foreach ($this->sailor as $k=>$comp)
        {
            $results = $this->get_results_sailor($comp['id']);

            $sailorkey = $k;
            $i = 0;
            foreach ($this->races as $race)
            {
                $i++;
                $racekey = "r$i";
                //u_writedbg("<pre>RACE : $racekey|{$race['status']}</pre>", __FILE__, __FUNCTION__, __LINE__);

                // check event has been sailed
                if ($race['status']  == "completed" OR $race['status'] == "sailed" OR $race['status'] == "running")
                {
                    //u_writedbg("<pre>race included</pre>", __FILE__, __FUNCTION__, __LINE__);

                    // get data for this result
                    //$col = array_column($results, 'eventid');
                    $eventkey = array_search($race['eventid'], array_column($results, 'eventid'));
                    if ($eventkey !== false)
                    {
                        $points = $results[$eventkey]['points'];
                        $code = $results[$eventkey]['code'];
                    }
                    else
                    {
                        $points = "0.0";
                        $code = "DNC";
                    }

                    $data[$sailorkey][$racekey] = array(
                        "pts" => number_format((float)$points, 1, '.', ''),
                        "code" => $code,
                        "discard" => false,
                        "sort" => "",
                        "exclude" => false
                    );
                    //u_writedbg("<pre>{$data[$sailorkey][$racekey]}</pre>", __FILE__, __FUNCTION__, __LINE__);
                }
            }
        }

        if (empty($data))
        {
            $this->err_fatal = true;
            $this->err[] = array("code" => "1", "type" => "fatal",
                "msg" => "no race results found for this series - cannot produce series result");
        }

        return $data;

    }

    private function get_results_sailor($competitorid)
    {
        $list = implode(",", $this->event_arr);
        $query = "SELECT eventid, code, penalty, points, declaration, note  FROM `t_result` as a JOIN `t_event` as b ON b.id=a.eventid 
                      WHERE eventid IN ($list) AND `competitorid` = $competitorid 
                      ORDER BY event_date, event_start";
        // u_writedbg("$query", __FILE__, __FUNCTION__, __LINE__); //debug:);
        return $this->db->db_get_rows($query);
    }

// --------- CALCULATE RESULTS METHODS ------------------------

    private function set_datafromfile()
    {
        $string = file_get_contents($this->testfile);

        if ($string === false)
        {
            $this->err_fatal = true;
            $this->err[] = array("code"=>"1", "type"=>"fatal",
                "msg"=>"failed to find or read specified result file {$this->testfile}");
        }

        $testdata = json_decode($string, true);
        if ($testdata === null)
        {
            $this->err_fatal = true;
            $this->err[] = array("code"=>"1", "type"=>"fatal",
                "msg"=>"unable to interpret specified result file {$this->testfile}");
        }
        else
        {
            if ($this->report) { echo "<pre>SERIES DATA READ: {$this->testfile}".print_r($testdata,true)."</pre>"; }
        }

        // add data to class arrays
        $this->series = $testdata['series'];
        $this->fleets = $testdata['fleets'];
        $this->races  = $testdata['races'];
        $this->sailor = $testdata['sailor'];
        $this->rst    = $testdata['rst'];
        $this->merge_classes = $this->set_mergeclasses($testdata['series']['merge']);
        $this->codes_used = $this->set_scoringcodes();

        if (!array_key_exists("code", $this->series)) { $this->series['code'] = $this->series_code; }

        return;
    }

    private function set_scoringcodes()
    {
        // get code used in results
        $codes = array();
        foreach($this->rst as $sailor)
        {
            foreach ($sailor as $row)
            {
                if (!empty($row['code'])) { $codes[] = $row['code']; }
            }
        }
        sort($codes);

        return $codes;
    }

    public function calc_series_result()
    {
        //if ($this->report) { echo "<pre>RST - initial [races: {$this->races_complete}] : ".print_r($this->rst,true)."</pre>";; }
        if ($this->report) { u_writedbg(".... calc step 1 [check some races complete]",__FILE__,__FUNCTION__,__LINE__); }

        // check that we have some completed races in the series
        if ($this->races_num <= 0)
        {
            $this->err_fatal = true;
            $this->err[] = array("code"=>"3", "type"=>"fatal",
                "msg"=>"no completed races found for series - cannot produce series result");
            return $this->err_fatal;
        }

        if ($this->report) { u_writedbg(".... calc step 2 [merge classes]",__FILE__,__FUNCTION__,__LINE__); }

        // if we have classes to be merged - merge results if same helm is sailing a merged class
//        if (!empty($this->merge_classes))
//        {
//            $this->merge_sailors($this->merge_classes);
//        }
//        if ($this->report) { echo "<pre>MERGES: ".print_r($this->sailors,true)."</pre>";; }

        // get number of sailors
        if ($this->report) { u_writedbg(".... calc step 3 [count sailors]",__FILE__,__FUNCTION__,__LINE__); }
        $this->count_sailors();
        //if ($this->report) { echo "<pre>FLEETS: ".print_r($this->fleets,true)."</pre>";; }

        // add DNC scores
        if ($this->report) { u_writedbg(".... calc step 4 [add DNC]",__FILE__,__FUNCTION__,__LINE__); }
        $this->score_dnc();
        //u_writedbg("<pre>RST after DNC: ".print_r($this->rst,true)."</pre>",__FILE__,__FUNCTION__,__LINE__);
        //if ($this->report) { echo "<pre>RST: ".print_r($this->rst,true)."</pre>";; }

        // apply series discards
        if ($this->report) { u_writedbg(".... calc step 5 [apply discards]",__FILE__,__FUNCTION__,__LINE__); }
        $this->series_discard();
        //if ($this->report) { echo "<pre>RST: ".print_r($this->rst,true)."</pre>";; }

        // apply average scores
        if ($this->report) { u_writedbg(".... calc step 6 [apply average scores]",__FILE__,__FUNCTION__,__LINE__); }
        //u_writedbg("<pre>RST BEFOE AVG: ".print_r($this->rst,true)."</pre>",__FILE__,__FUNCTION__,__LINE__);
        $this->score_avg();
        //if ($this->report) { echo "<pre>RST: ".print_r($this->rst,true)."</pre>";; }

        // redo discards in case average score changes it
        if ($this->report) { u_writedbg(".... calc step 7 [redo discards]",__FILE__,__FUNCTION__,__LINE__); }
        $this->series_discard();                            // FIXME is there a better way of doing this - it only applies to one AVG option
        //if ($this->report) { echo "<pre>RST: ".print_r($this->rst,true)."</pre>";; }

        if ($this->report) { u_writedbg(".... calc step 8 [set series points]",__FILE__,__FUNCTION__,__LINE__);  }
        $this->series_points();
        //if ($this->report) { echo "<pre>RST: ".print_r($this->rst,true)."</pre>";; }

        $this->results = $this->series_score();

        if ($this->report) { u_writedbg(".... calc step 9 [final results]".print_r($this->results,true) ,__FILE__,__FUNCTION__,__LINE__); }

        if ($this->report) { u_writedbg(".... calc step 10 [get turnout stats]",__FILE__,__FUNCTION__,__LINE__); }
        $this->set_turnoutstats($this->series["races_complete"]);

        return $this->err_fatal;

    }

    public function get_results_data()
    {
        return $this->results;
    }

    private function count_sailors()
    {
        // gets number of competitors in each fleet and adds to $this->fleets

        $count = array_count_values(array_column($this->sailor, 'fleet'));

        foreach($this->fleets as $k=>$fleet)
        {
            if (isset($count[$k]))
            {
                $this->fleets[$k]['nsailors'] = $count[$k];
            }

            else
            {
                $this->fleets[$k]['nsailors'] = 0;
            }
        }
    }

    private function score_dnc()
    {
        /**
         * for each fleet calculate points for unscored races as (DNC) - and add scores to result records
         * uses code expression for DNC - if no code or default to numsailors + 1
         */
        $code_func = "";
        $code = $this->db->db_getresultcode("DNC");
        if (!empty($code['scoring']))
        {
            // check func only contains allowed characters (S, 0-9, arithmetic operators, white space)
            if (preg_match("/[^0-9().S\\s +\\-+*\\/]/", $code['scoring']) != 1) { $code_func = $code['scoring']; }
        }

        if (empty($code_func))
        {
            $code_func = "S + 1";
        }

        $dnc_score = array();

        foreach ($this->fleets as $k=>$fleet)
        {
            $code_func_e = str_replace("S", $fleet['nsailors'], $code_func);

            $this->fleets[$k]['dnc_score'] = eval("return ".$code_func_e.";");
        }

        // loop through sailors setting the DNC values to the appropriate value
        foreach ($this->sailor as $s_id=>$sailor)
        {
            // loop through races for this sailor
            foreach ($this->races as $r => $race)
            {
                if ($race['status'] == "sailed" or $race['status'] == "completed" or $race['status'] == "running")
                {
                    if (array_key_exists("r$r", $this->rst[$s_id]))
                    {
                        if ($this->rst[$s_id]["r$r"]['code'] == "DNC") {
                            $this->rst[$s_id]["r$r"]['pts'] = $this->fleets[$sailor['fleet']]['dnc_score'];
                            $this->rst[$s_id]["r$r"]['sort'] = $this->fleets[$sailor['fleet']]['dnc_score'];
                        }
                    }
                    else
                    {
                        $this->rst[$s_id]["r$r"] = array(
                            "pts" => $this->fleets[$sailor['fleet']]['dnc_score'],
                            "code" => "DNC",
                            "discard" => false,
                            "sort" => $this->fleets[$sailor['fleet']]['dnc_score'],
                            "exclude" => false
                        );
                    }
                }
            }

        }
    }

    private function series_discard()
    {
        // get number to count
        //$profile = explode(",", $this->series['discard']);
        //$num_to_count = $this->races_complete - $this->series['discard'][$this->races_complete - 1];
        $num_to_count = $this->races_complete - $this->series['discard'][$this->races_complete];

        // apply discards for each sailor's results
        foreach ($this->sailor as $s_id=>$sailor)
        {
            // sort results in ascending order based on sort attribute
            $data = $this->rst[$s_id];
            $sort = array();
            foreach ($data as $key=>$row)
            {
                $sort[$key]  = $row['sort'];
            }
            array_multisort($sort, SORT_ASC, $data);

            // mark discarded races with discard flag
            $count = 0;
            foreach ($data as $race=>$detail) // FIXME check this works with DNE
            {
                $count++;
                if (strtoupper($detail['code']) != "DNE")
                {
                    if ($count > $num_to_count)
                    {
                        $this->rst[$s_id][$race]['discard'] = true;
                    }
                }
            }
        }
    }

    private function score_avg()
    {
        /** Average points options
        all_races - average of all races
        all_competed - average of all non-dnc races
        avg_counting - average of all non-discarded races (tricky as the race may then replace a discarded one)
         */

        // calculate average score for each sailor according to scheme configured for this series
        foreach ($this->sailor as $s_id=>$sailor)
        {
            // loop through results for this sailor
            $score_sum = 0;
            $score_count = 0;

            foreach ($this->rst[$s_id] as $k=>$result)
            {
                if ($this->series['avgscheme'] == "all_races")
                {
                    if ( $result['code'] != "AVG" AND $result['pts'] != 999 )
                    {
                        $score_count++;
                        $score_sum = $score_sum + $result['pts'];
                    }
                }

                elseif ($this->series['avgscheme'] == "all_competed")
                {
                    if ( $result['code'] != "DNC" AND  $result['code'] != "DNS"
                         AND $result['code'] != "AVG" AND $result['pts'] != 999 )
                    {
                        $score_count++;
                        $score_sum = $score_sum + $result['pts'];
                    }
                }

                elseif  ($this->series['avgscheme'] == "all_counting")
                {
                    if (!$result['discard'] AND $result['pts'] != 999 )
                    {
                        $score_count++;
                        $score_sum = $score_sum + $result['pts'];
                    }
                }
            }

            // set average for this sailor
            if ($score_count == 0)
            {
                $average = 0;
            }
            else
            {
                $average = round($score_sum/$score_count, 1);
            }
            $this->sailor[$s_id]['average'] = $average;

            // apply this score to relevant results
            $average_set = 0;
            foreach ($this->rst[$s_id] as $k=>$result)
            {
                if ($result['code'] == "AVG" OR $result['pts'] == 999)
                {
                    if($average != 0)
                    {
                        $this->rst[$s_id][$k]['pts'] = $average;
                        $average_set++;
                    }
                    else  // only scoring race is AVG - score is set to DNC
                    {
                        $this->rst[$s_id][$k]['pts'] = $this->fleets[$this->sailor[$s_id]['fleet']]['dnc_score'];
                    }

                    // undo discard flag if set
                    $this->rst[$s_id][$k]['discard'] = 0;
                }
            }
            //debug if ($s_id == 29) { echo "<pre> AFTER".print_r($this->rst[$s_id],true)."</pre>"; }
        }
    }

    private function series_points()
    {
        foreach ($this->sailor as $s_id=>$sailor)
        {
            $series_total_pts = 0.0;
            $series_net_pts = 0.0;
            foreach ($this->rst[$s_id] as $k=>$result)
            {
                $series_total_pts = $series_total_pts + $result['pts'];
                if (!$result['discard'])
                {
                    $series_net_pts = $series_net_pts + $result['pts'];
                }
            }
            $this->sailor[$s_id]['net_pts'] = $series_net_pts;
            $this->sailor[$s_id]['tot_pts'] = $series_total_pts;
        }
    }

    private function series_score()
    {
        $output = array();

        // set series information
        $output['series'] = $this->series;
        $output['series']['results-date'] = date("Y-m-d H:s");

        // set races information
        foreach ($this->races as $k=>$race)
        {
            $output['races'][$k] = array(
                "race-name" => "R$k",
                "race-status" => $this->races[$k]['status'],
                "race-full-date" => $this->races[$k]['date'],
                "race-short-date" => date("m/d", strtotime($this->races[$k]['date'])),
                "race-url" => $this->races[$k]['url']
            );
        }


        // loop over fleets
        foreach($this->fleets as $k=>$v)
        {
            // select sailors in this fleet
            $this->sailor_fleet = array_filter($this->sailor, function ($ar) use ($k){
                return ($ar['fleet'] == $k);
            });

            // sort by net points ASC
            uasort($this->sailor_fleet, function ($a, $b) {
                return $a['net_pts'] - $b['net_pts'];
            });

            // allocate position for each sailor (maintain ties)
            $posn = 0;
            foreach($this->sailor_fleet as $i=>$row)
            {
                $posn++;
                $this->sailor_fleet[$i]['posn'] = $posn;
            }

            //echo "<pre>BEFORE TIES".print_r($this->sailor_fleet,true)."</pre>";

            // resolve ties in this fleet
            $num_switches = $this->resolve_ties("isaf_a8");

            //echo "<pre>AFTER TIES".print_r($this->sailor_fleet,true)."</pre>";

            if ($num_switches > 0)
            {
                // sort by position
                uasort($this->sailor_fleet, function ($a, $b) {
                    return $a['posn'] - $b['posn'];
                });
            }

            // create output array (class, sailnum, helm/crew, club, r1 ... rn, total, net, posn, notes)
            $output['fleets'][$k] = array(
                "fleet-name" => $this->fleets[$k]['name'],
                "num-competitors" => $this->fleets[$k]['nsailors']
            );
            $rownum = 0;
            $output['fleets'][$k]['sailors'] = array();
            foreach($this->sailor_fleet as $i=>$row)
            {
                // add information back to object array
                $this->sailor[$i] = $row;

                // create output array
                $rownum++;
                $output['fleets'][$k]['sailors'][$rownum] = array(
                    "class"   => $row['class'],
                    "sailnum" => $row['sailno'],
                    "compid"  => $row['id'],
                    "team"    => u_getteamname($row['helm'], $row['crew']),
                    "club"    => $row['club'],
                    "total"   => $row['tot_pts'],
                    "net"     => $row['net_pts'],
                    "posn"    => $row['posn'],
                    //"note"    => $row['note'],
                );
                if (!$this->opts['inc-club']) { unset($output['fleets'][$k]['sailors'][$rownum]['club']); }


                foreach($this->rst[$i] as $j=>$result)
                {
                    $output['fleets'][$k]['sailors'][$rownum]['rst'][$j]= array(
                        "result"  => $result['pts'],
                        "code"    => $result['code'],
                        "discard" => $result['discard'],
                    );
                }


            }
        }
        return $output;
    }

    private function resolve_ties($mode)
    {
        if ($mode == "isaf_a8")
        {
            $num_switches = 0;
            $switches = true;
            while ($switches)   // iterate until no more ties
            {
                $switches = false;
                $prev_id = 0;                               # for previous sailor
                $prev_pts = 0;                              # net points for previous sailor
                $prev_non_discard = array();                # array of race results for previous sailor - no discards
                $prev_inc_discard = array();                # array of race results for previous sailor - inc discards



                //echo "<pre>BEFORE-SAILOR-RT".print_r($this->sailor_fleet,true)."</pre>";
                foreach($this->sailor_fleet as $i=>$row)
                {
                    //echo "<pre>SAILOR: $i</pre>";

                    $curr_non_discard = array();                # array of race results for current sailor - no discards
                    $curr_inc_discard = array();                # array of race results for current sailor - inc discards
                    foreach($this->rst[$i] as $k=>$result)
                    {
                        $curr_inc_discard[] = $result['pts'];
                        if (empty($result['discard'])) { $curr_non_discard[] = $result['pts']; }
                    }


                    //echo "<pre>POINTS: $prev_pts - {$row['net_pts']}</pre>";
                    if ($row['net_pts'] == $prev_pts)  // tie found
                    {
                        //echo "<pre>possible tie</pre>";
                        // check if ISAF A8.1 resolves it - returns -1 if CURRENT wins, +1 if PREVIOUS wins, 0 if still a tie
                        // checks no. of wins, 2nds, 3rds etc - ignoring discarded results
                        $tie = $this->resolve_tie_A81($prev_non_discard, $curr_non_discard);

                        // not resolved try ISAF A8.2 - returns -1 if CURRENT wins, +1 if PREVIOUS wins
                        if ($tie == 0)
                        {
                            $tie = $this->resolve_tie_A82($prev_inc_discard, $curr_inc_discard);
                        }

                        // change positions if boats in wrong order
                        if ($tie == -1)
                        {
                            $this->switch_positions($prev_id, $i);   // reorders array after switching
                            $switches = true;
                            $num_switches++;
                        }
                    }

                    //echo "<pre>AFTER-SAILOR-RT".print_r($this->sailor_fleet,true)."</pre>";

                    // set to check next pair
                    $prev_id = $i;
                    $prev_pts = $row['net_pts'];
                    $prev_non_discard = $curr_non_discard;
                    $prev_inc_discard = $curr_inc_discard;

                }
            }
        }
        else
        {
            // mode not recognised - return false
            return false;
        }
        //echo "<pre>num_switches = $num_switches</pre>";
        return $num_switches;
    }

    private function resolve_tie_A81($prev, $curr)
    {
        //echo "<pre>PREV".print_r($prev,true)."</pre>";
        //echo "<pre>CURR".print_r($curr,true)."</pre>";


        $result = 0;
        // sort in posn order (ASC) - change keys
        sort($prev);
        sort($curr);

        //echo "<pre>PREV-SORT".print_r($prev,true)."</pre>";
        //echo "<pre>CURR-SORT".print_r($curr,true)."</pre>";

        // loop through arrays and compare
        $i = 0;
        foreach ($prev as $k=>$posn)
        {
            $i++;

            if ($posn > $curr[$k])
            {
                $result = -1;  // need to switch
                break;
            }
            elseif ($curr[$k] > $posn)
            {
                $result = 1;   // in correct position
                break;
            }
        }

        //echo "<pre>RETURN: $result</pre>";
        return $result;
    }

    private function resolve_tie_A82($prev, $curr)
    {
        //echo "<pre>82_PREV".print_r($prev,true)."</pre>";
        //echo "<pre>82_CURR".print_r($curr,true)."</pre>";

        // put arrays in reverse order and find first mismatch
        $prev_r = array_reverse ( $prev , true );
        $curr_r =array_reverse ( $curr , true );
        //echo "<pre>82_PREV-SORT".print_r($prev_r,true)."</pre>";
        //echo "<pre>82_CURR-SORT".print_r($curr_r,true)."</pre>";

        foreach ($curr_r as $k=>$posn)
        {
            if ($posn < $prev_r[$k])
            {
                return -1;
            }
            elseif ($prev_r[$k] < $posn)
            {
                return 1;
            }
        }
        return 0;
    }


// --------- RENDER OUTPUT METHODS ---------------------------

    private function switch_positions($prev_id, $this_id)
    {
        //echo "<pre>BEFORE PREV: {$this->sailor_fleet[$prev_id]['posn']}</pre>";
        //echo "<pre>THIS: {$this->sailor_fleet[$this_id]['posn']}</pre>";

        $switch_posn = $this->sailor_fleet[$prev_id]['posn'];
        $this->sailor_fleet[$prev_id]['posn'] = $this->sailor_fleet[$this_id]['posn'];
        $this->sailor_fleet[$this_id]['posn'] = $switch_posn;

        // resort array on position
        uasort($this->sailor_fleet, function ($a, $b) {
            return $a['posn'] - $b['posn'];
        });

        //echo "<pre>AFTER PREV: {$this->sailor_fleet[$prev_id]['posn']}</pre>";
        //echo "<pre>THIS: {$this->sailor_fleet[$this_id]['posn']}</pre>";
    }
// --------- UTILITY METHODS ----------------------------------

    private function set_turnoutstats($races_complete)
    {
        $total_series = 0;
        $total_race = array();
        $total_fleet = array();

        foreach ($this->fleets as $k=>$fleet)
        {
            $total_fleet_race[$k] = array();
        }

        foreach ($this->results['fleets'] as $fleetnum => $fleet)
        {
            //if ($this->report) {echo "{$fleet['fleet-name']}"; echo"<pre>".print_r($fleet['sailors'],true)."</pre>";}
            foreach ($fleet['sailors'] as $s=>$sailor)
            {
                foreach($sailor["rst"] as $j=>$race)
                {
                    $racenum = substr($j, 1);
                    $status = $this->results['races'][$racenum]['race-status'];

                    if (!array_key_exists($racenum, $total_fleet_race[$fleetnum])) { $total_fleet_race[$fleetnum][$racenum] = 0; }

                    if ($race['code'] != "DNC")
                    {
                        $total_series++;

                        if (!array_key_exists($fleetnum, $total_fleet)) { $total_fleet[$fleetnum] = 0; }
                        $total_fleet[$fleetnum]++;

                        if ($status == "completed" or $status == "sailed")
                        {
                            if (!array_key_exists($racenum, $total_race)) { $total_race[$racenum] = 0; }
                            $total_race[$racenum]++;
                            $total_fleet_race[$fleetnum][$racenum]++;
                        }
                    }
                }
            }
        }

        // set stats into result data structure
        $this->results['series']['avg_turnout'] = round((float)($total_series/$races_complete),1);
        $this->results['series']['max_turnout'] = round(max($total_race),0,PHP_ROUND_HALF_UP);
        $this->results['series']['min_turnout'] = round(min($total_race),0,PHP_ROUND_HALF_UP);

        foreach ($this->fleets as $k=>$fleet)
        {
            if (empty($total_fleet[$k]))
            {
                $this->results['fleets'][$k]['avg_turnout'] = "n/a";
            }
            else
            {
                $this->results['fleets'][$k]['avg_turnout'] = round((float)($total_fleet[$k]/$races_complete),1);
            }

            if (empty($total_fleet_race[$k]))
            {
                $this->results['fleets'][$k]['max_turnout'] = "n/a";
                $this->results['fleets'][$k]['min_turnout'] = "n/a";
            }
            else
            {
                $this->results['fleets'][$k]['max_turnout'] = round(max($total_fleet_race[$k]),0,PHP_ROUND_HALF_UP);
                $this->results['fleets'][$k]['min_turnout'] = round(min($total_fleet_race[$k]),0,PHP_ROUND_HALF_UP);
            }
        }

    }

    public function series_render_styled($sys_detail, $series_status, $styles)
    {
        global $tmpl_o;

        // get system info
        if (empty($sys_detail))
        {
            $sys_detail['sys_name'] = "raceManager";                                   // system name
            $sys_detail['sys_release'] = "";                                           // release name
            $sys_detail['sys_version'] = "";                                           // code version
            $sys_detail['sys_copyright'] = "Elmswood Software " . date("Y");           // copyright
            $sys_detail['sys_website'] = "";                                           // website
        }

        // get information on codes used
        $result_codes = $this->db->db_getresultcodes("result");
        $codes_info = u_get_result_codes_info($result_codes, $this->codes_used);

        $data = array(
            "pagetitle"     => $this->series_code,
            "styles"        => $styles
        );

        $params = $this->results;
        $params['eventyear'] = date("Y", strtotime($this->results['races'][1]['race-full-date']));
        $params['opts'] = $this->opts;
        $params['codes'] = $codes_info;
        $params['sys'] = $sys_detail;

        $htm = $tmpl_o->get_template("series_sheet", $data, $params);

        return $htm;
    }

    public function export_seriesdata()
    {
        // exports series data in standard array format
        $data = array(
            "series"        => $this->series,
            "fleets"        => $this->fleets,
            "races"         => $this->races,
            "sailor"        => $this->sailor,
            "rst"           => $this->rst,
            "codes_used"    => $this->codes_used,
            "merge_classes" => $this->merge_classes,
        );

        return $data;
    }

    public function get_race_counts()
    {
        $data = array(
            "series_code"    => $this->series_code,         // code for series
            "races_num"      => $this->races_num,           // num races that have been attempted in series so far
            "races_complete" => $this->races_complete,      // races for which results exist
        );

        return $data;
    }

    public function get_series_filename()
    {
        // series file is the series code in the event record (i.e including year) with any
        // non-standard characters removed
        $filename = preg_replace('/[^a-zA-Z0-9\-\._]/', '', $this->series_code);
        return $filename.".htm";
    }

    public function get_err($type = "all")
    {
        if ($type = "all")
        {
            return $this->err;
        }
        else
        {
            $filtered_err = array();
            foreach ($this->err as $err)
            {
                if ($err['type'] == "fatal" and $type == "fatal")
                {
                    $filtered_err[] = $err;
                }
                elseif ($err['type'] == "warning" and $type == "warning")
                {
                    $filtered_err[] = $err;
                }
            }
            return $filtered_err;
        }
    }

    public function series_render_basic()
    {
        // produces html rendering of basic series data with dummy styles
        // designed to produce results for a club specific rendering

        $htm = <<<EOT
        <div style="font-family: verdana,sans-serif;">
        <div><span class="ds-club">{$this->results['series']['club-name']}</span>: <span class="ds-series">{$this->results['series']['series-name']}</span></div>
EOT;

        foreach ($this->results['fleets'] as $k => $fleet)
        {
            if ($this->results['fleets'][$k]['num-competitors'] <= 0)
            {
                $htm .= <<<EOT
                <div><span class="ds-fleet">{$this->results['fleets'][$k]['fleet-name']}</span></div>
                <div>
                    - no competitors in series  
                </div>
EOT;
            }
            else
            {

                $cols_htm = "";
                $cols_htm .= <<<EOT
                    <td class="ds-col">class</td>
                    <td class="ds-col">sail no.</td>
                    <td class="ds-col">team</td>
                    <td class="ds-col">club</td>
EOT;
                    foreach ($this->results['races'] as $j => $race) {
                        $cols_htm .= <<<EOT
                    <td class="ds-col" style='text-align: center;'>{$race['race-name']}</td>
EOT;
                    }

                    $cols_htm .= <<<EOT
                    <td class="ds-col">total</td>
                    <td class="ds-col">net</td>
                    <td class="ds-col">position</td>
EOT;
                    $rows_htm = "";


                    foreach ($fleet['sailors'] as $i => $sailor)
                    {
                        $race_rows = "";
                        foreach ($this->results['races'] as $j => $race) {
                            if ($race['race-status'] == "completed") {
                                if (array_key_exists("r$j", $sailor['rst'])) {
                                    // get score for this race and this sailor
                                    // if score includes code display as points/code
                                    // if score is discarded display score as in brackets
                                    if (empty($sailor['rst']["r$j"]['code'])) {
                                        $score = $sailor['rst']["r$j"]['result'];
                                    } else {
                                        $score = $sailor['rst']["r$j"]['result'] . "/" . $sailor['rst']["r$j"]['code'];
                                    }

                                    if ($sailor['rst']["r$j"]['discard']) {
                                        $score = "[$score]";
                                    }

                                    $race_rows .= "<td class='ds-race' style='text-align: center; min-width: 4em;'>$score</td>";
                                }
                            }
                            else
                            {
                                $race_rows .= "<td class='ds-race' style='text-align: center; min-width: 4em;'>&nbsp;</td>";
                            }
                        }

                        $rows_htm .= <<<EOT
                    <tr class="ds-row">
                        <td class="ds-class">{$sailor['class']}</td>
                        <td class="ds-sailnum">{$sailor['sailnum']}</td>
                        <td class="ds-team">{$sailor['team']}</td>
                        <td class="ds-club">{$sailor['club']}</td>
                        $race_rows
                        <td class="ds-total">{$sailor['total']}</td>
                        <td class="ds-net">{$sailor['net']}</td>
                        <td class="ds-position">{$sailor['posn']}</td>
                    </tr>
EOT;
                    }


                    $htm .= <<<EOT
                    <div><span class="ds-fleet">{$this->results['fleets'][$k]['fleet-name']}</span></div>
                    <div>
                        <table class="ds-table">
                            <thead><tr class="ds-thead">
                                $cols_htm
                            </tr>
                            </thead>
                            <tbody class="ds-tbody">
                                $rows_htm
                            </tbody>                
                        </table>   
                    </div>
                
EOT;
            }
        }

        return $htm."</div>";

    }

    private function merge_sailors($merge_classes)
        /**
         * for each merge class - looks through results and merges the results for each competitor sailing any
         * classes within the group in the same fleet.  The results are merged into the first class in the group
         */
    {
        //FIXME - still to be implemented

        foreach ($merge_classes as $merge_group)
        {
            $sailors = array();
            foreach ($this->sailor as $i=>$sailor)
            {
                if (in_arrayi($sailor['class'], $merge_group))
                {
                    $sailors[$i] = array("id" => $sailor['id'], "helm" => $sailor['helm'], "class" => $sailor['class'],
                        "sailno" => $sailor['sailno'], "fleet" => $sailor['fleet'], "swap"=> false);
                }
            }
        }

        $swaps = array();

        foreach ($sailors as $i=>$sailor)
        {
            if (!$sailor['swap'])
            {
                $swaplist = "$i";
                foreach ($sailors as $j=>$k)
                {
                    if ($i == $j or $k['swap'])
                    {
                        continue;
                    }
                    elseif (strtolower($sailor['name']) == strtolower($k['name'])
                        and $sailor['fleet'] == $k['fl'])
                    {
                        $swaplist.=",$j";
                    }
                }
            }
            if (strpos($swaplist, ',') !== false)            // we have a swap
            {
                $swaps[$i] = ($swaplist);
            }
        }

        foreach ($swaps as $swap)
        {

        }


        /*
        foreach group
            foreach sailor
                if sailing one of classes add sailor index to temp array (index,id,class, sailnumber, fleet)
            endloop
            foreach item in temp array


            endloop
            find matches in temp array - sailor name, sailnumber, fleet - create array of swaps
            foreach swap
                 get all 1st competitor results
                 get all 2nd competitor results
                 add race results for 2nd competitor if 1st competitor doesn't have a result
                 delete 2nd competitor results from rst array
            endloop




        endloop


        create list of all master classes to be merged - merge-list
        loop over competitors (I)
            if competitor is sailing a class in merge-list
                loop through all other competitors (J) (after this one)
                     merge results from (J) into (I)
                     delete competitor J
                end loop
                change class for competitor I to lead class in group
            endif
        end loop
        */

        return true;
    }

    private function get_results_event($eventid)
    {
        $query = "SELECT code, penalty, points, declaration, note, FROM `t_result`
                  WHERE eventid = $eventid ORDER BY fleet, points ";
        // u_writedbg("$query", __FILE__, __FUNCTION__, __LINE__); //debug:);
        $results = $this->db->db_get_rows($query);

        return $results;
    }

// OBSOLETE
//    private function check_options($opts)
//    {
//        // set options in SESSION if not already set
//        $keys_arr = array_keys($opts);
//
//        if ($keys_arr)
//        {
//            foreach ($keys_arr as $key)
//            {
//                // for each option - if not already set in SESSION then set it
//                if (!array_key_exists($key, $_SESSION))
//                {
//                    $_SESSION[$key] = $opts[$key];
//                }
//            }
//        }
//    }


// --------TIE RESOLUTION----------------------------------------------------------



// --------NEEDS SORTING----------------------------------------------------------


//    public function create_resultcodes($mode, $resultcodes)
//    {
//        // FIXME needs looking at - should be series results template
//        // get codes into html bufr
//        $bufr = <<<EOT
//        <div style="margin-left:40px;"><table>
//            <thead>
//                <th class="lightshade" >Code</th>
//                <th class="lightshade" >Meaning</th>
//                <th class="lightshade" >Scoring</th>
//            </thead>
//            <tbody>
//EOT;
//        foreach ($resultcodes as $key => $row) {
//            $trans = array("N" => "race competitors", "S" => "series competitors", "P" => "position");
//            $scoring = strtr($row['scoring'], $trans);
//            $scoring = "[$scoring]";
//
//            $bufr .= <<<EOT
//            <tr style="vertical-align: top;" >
//                <td class="text-center text-alert" ><b>{$row['code']}</b></td>
//                <td>{$row['info']}</td>
//                <td class="text-grey"><i>$scoring</i></td>
//            </tr>
//EOT;
//        }
//        $bufr .= "</tbody></table></div>";
//
//        return $bufr;
//    }

//    private function get_raceresults($eventid, $fleetnum)
//    {
//        $where = "";
//        if ($fleetnum) {
//            $where = " AND fleet = $fleetnum ";
//        }
//
//        $results = $this->db->db_get_rows("SELECT *, helm as team, points as result FROM t_result WHERE eventid=$eventid $where ORDER BY fleet, points ASC, pn, class, sailnum+0");
//
//        foreach ($results as $key => $result) {
//            $results[$key]['team']   = u_conv_team($result['helm'], $result['crew']);
//            $results[$key]['result'] = u_conv_result($result['code'], $result['points']);
//            $results[$key]['etime']  = u_conv_secstotime($result['etime']);
//            $results[$key]['ctime']  = u_conv_secstotime($result['ctime']);
//            $results[$key]['atime']  = u_conv_secstotime($result['atime']);
//        }
//
//        return $results;
//    }

//    private function get_resultcodes($codes_used = array())
//    {
//        // FIXME needs looking at it - I aslo do this for race result - seems to be same as u_get_result_codes_info
//        $results = $this->db->db_get_rows("SELECT * FROM t_code_result ORDER BY code");
//        if (empty($codes_used))
//        {
//            return $results;
//        }
//        else  // only include result codes that are used
//        {
//            $output = array();
//            foreach ($results as $result) {
//                if (in_array($result['code'], $codes_used)) {
//                    $output[] = $result;
//                }
//            }
//            return $output;
//        }
//    }

// OBSOLETE
//    public function series_result($series_code, $options, $event_list = array())
//    {
//        # set default options if not set
//        if (!array_key_exists("race_cfg", $options) or !is_bool($options['race_cfg']))
//        {
//            $options['race_cfg'] = true;
//        }
//
//        if (!array_key_exists("merge", $options) or !is_bool($options['merge']))
//        {
//            $options['merge'] = false;
//        }
//
//        if (!array_key_exists("club", $options) or !is_bool($options['club']))
//        {
//            $options['club'] = true;
//        }
//        $this->opts = $options;
//
//
//        // FIXME - rethink this you might have a series code but only want to include some races (passing event_list)
//        // get series information
//        $event_o = new EVENT($this->db);
//        $series = $event_o->event_getseries($series_code);
//        if (empty($series))
//        {
//            $this->in_err_fatal = true;
//            $this->in_err = array("code"=>"1", "type"=>"fatal", "msg"=>"series code not found in database - cannot produce series result");
//
//        }
//        else
//        {
//            // get list of event ids to be included in series if not provided as argument
//            if (empty($event_list))
//            {
//                $event_list = $event_o->series_eventarr($series_code);
//            }
//            $this->races_inseries_num = count($event_list);
//
//            // check race_cfg is consistent for each event in list - if requested
//            if ($options['race_cfg'])
//            {
//                $i = 0;
//                $race_cfg = "";
//                foreach ($event_list as $event)
//                {
//                    $i++;
//                    $event_o->get_event_byid($event['id']);     // get event
//                    if ($i == 1)
//                    {
//                        $race_cfg = $event['event_format'];     // set standard configuration for this series
//                    }
//                    else
//                    {
//                        if ($race_cfg != $event['event_format'])
//                        {
//                            $this->result["err"].= " the events for series do not have a consistent race format<br>";
//                            $this->result["success"] = false;
//                        }
//                    }
//                }
//            }
//        }
//
//        // derive series result from event list
//        if (!empty($event_list))
//        {
//            $this->result['output'] = $this->calculate_series_result($event_list, $options['merge']);
//
//            $this->result["err"].= "";
//            $this->result["success"] = true;
//
//            // get display data structure together
//            $display = $this->get_seriesresult_output_info($this->result['output']);
//
//            // produce html file if requested
//
//            // else create json file
//
//        }
//        else
//        {
//            $this->result["err"].= " no races defined for requested series<br>";
//            $this->result["success"] = false;
//        }
//
//        return $this->result;
//    }



//    private function series_races_sailed()
//    {
//       foreach ($this->races as $race)
//       {
//           if (in_array($race['status'], $this->status_run))
//           {
//               $this->races_run_num++;
//           }
//           if (in_array($race['status'], $this->status_complete))
//           {
//               $this->races_complete_num++;
//           }
//       }
//    }



//    private function series_final_data()
//    {
//        $data = array();
//        return $data;
//    }

//    public function series_html($style, $data)
//    {
//        $bufr = "";
//        return $bufr;
//    }

//

    private function get_event_results_details($results)
    {
        $stats = array();

        if ($results)
        {
            $num_entries = count($results);
            $num_sailed = 0;
            foreach ($results as $result)
            {
                if ($result['code'] != "DNC")
                {
                    $num_sailed++ ;
                }
            }

            $stats['entries'] = $num_entries;
            $stats['sailed'] = $num_sailed;
        }

        return $stats;
    }

}






