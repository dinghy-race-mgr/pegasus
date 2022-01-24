<?php
/**
 *  RESULT class
 * 
 *  Handles interaction with t_race, t_racestate and t_lap
 * 
 *  METHODS
 *     __construct
 * 
 *     get_filename    -  gets filename for event results
 *     clear_results   -  clears event results from t_result
 *     create_raceresult - creates the output file for a race result
 *     set_tablecols_level - column configuration for level race (private)
 *     set_tablecols_average - column configuration for level race (private)
 *     set_tablecols_handicap - column configuration for handicap race (private)
 *     set_tablecols_pursuit - column configuration for pursuit race (private)
 *     format_raceresult  - produces output format (private)
 *     format_resultcodes - produces output for codes display
 *     get_resultcodes
 *     get_raceresults
 *     get_resultfiles
 *     transfer_resultfiles
 *     add_resultfile
 *     create_seriesresult
 *     create_resultinventory
*/

/*
    - organise names and refactor names internally
    - remove eventid as argument
    - check club and boatname are optional

Methods
  files
    get_race_filename            creates filename for race rsult
    get_series_filename          derives filename for series result
    add_result_file


    clear_results                clears event results stored in t_result, a_result and a_lap

    race_copy_results            copy race results from t_race to t_results
    race_archive_results         archives race results from t_race/t_lap to a_race/a_lap




*     clear_results   -  clears event results from t_result
 *     create_raceresult - creates the output file for a race result
 *     set_tablecols_level - column configuration for level race (private)
 *     set_tablecols_average - column configuration for level race (private)
 *     set_tablecols_handicap - column configuration for handicap race (private)
 *     set_tablecols_pursuit - column configuration for pursuit race (private)
 *     format_raceresult  - produces output format (private)
 *     format_resultcodes - produces output for codes display
 *     get_resultcodes
 *     get_raceresults
 *     get_resultfiles
 *     transfer_resultfiles
 *     add_resultfile
 *     create_seriesresult
 *     create_resultinventory



*/

class RESULT
{
    private $db;

    //Method: construct class object
    public function __construct(DB $db, $eventid)
    {
        $this->db = $db;
        $this->eventid = $eventid;

        // get event details
        $event_arr = $this->db->db_get_row("SELECT * FROM t_event WHERE `id` = {$this->eventid} and `event_type` = 'racing'");
        if ($event_arr)
        {
            $event_cfg = $this->db->db_get_row("SELECT * FROM t_cfgrace WHERE `id` = {$event_arr['event_format']}");
            $fleet_cfg = $this->db->db_get_rows("SELECT * FROM t_cfgfleet WHERE `eventcfgid` = {$event_arr['event_format']}");

            if ($event_cfg and $fleet_cfg)
            {
                $this->eventname = $event_arr['event_name'];
                $this->eventdate = $event_arr['event_date'];
                $this->rc_code   = $event_cfg['race_code'];
                $this->pursuit   = $event_cfg['pursuit'];

                $this->fleet = array();
                foreach ($fleet_cfg as $fleet)
                {
                    $this->fleet["{$fleet['fleet_num']}"] = $fleet;
                }
            }
            else
            {
                // exit nicely
                echo "<pre> FAILED to initiate RESULT object: race configuration missing<br>".print_r($this,true)."</pre>";
            }

        }
        else
        {
            // exit nicely
            echo "<pre> FAILED to initiate RESULT object: event details missing or not race event<br>".print_r($this,true)."</pre>";
        }

        //echo "<pre>".print_r($this,true)."</pre>";

    }

/* -------------- results files functions ---------------------------------------------------------- */
    public function get_race_filename()
    {
         // race filename is eventname (bad characters removed) + event date + race configuration code + eventid
        $eventname = preg_replace('/[^a-zA-Z0-9\-\._]/', '', $this->eventname);
        $filename = sprintf("%s_%s_%s_%s.htm", $eventname, $this->eventdate, $this->rc_code, $this->eventid);

        return $filename;
    }


    public function get_inventory_filename($year)
    {
        $filename = "inventory_".$year.".json";
        return $filename;
    }

    public function add_result_file($filespec)
    {
        $status = false;

        $filespec['eventid'] = $this->eventid;
        $exists = $this->db->db_num_rows("SELECT * FROM t_resultfile
                                          WHERE `eventid` = {$this->eventid}
                                          AND folder = '{$filespec['folder']}'
                                          AND format = '{$filespec['format']}'
                                          AND filename = '{$filespec['filename']}'");

        if ($exists > 0)
        {
            $update = $this->db->db_update("t_resultfile", $filespec,
                array("eventid" => $this->eventid, "folder" => $filespec['folder'], "format" => $filespec['format'], "filename" => $filespec['filename']));
            if ($update >= 0) { $status = true; }
        }
        else
        {
            $insert = $this->db->db_insert("t_resultfile", $filespec);
            if ($insert) { $status = true; }
        }

        return $status;
    }

    function transfer_results_files()
    {
        return true;   // FIXME
    }

    public function get_result_files($eventid, $type="")
    {
        $where = "eventid = $eventid ";
        if (!empty($type))
        {
            $where.= "folder = '".strtolower($type)."' ";
        }

        $files = $this->db->db_get_rows("SELECT * FROM t_resultfile WHERE $where");
        return $files;
    }


    /* -------------- results table functions ---------------------------------------------------------- */
    public function clear_results($fleet = 0)
    {
        $constraint = array("eventid" => $this->eventid);
        if ($fleet != 0) { $constraint[] = array("fleet" => $fleet); }
        $num_rows = $this->db->db_delete("t_result", $constraint);

        return $num_rows;
    }


    public function race_copy_results()
    {
        /* copies results from t_race into t_result */

        $this->clear_results();                      // first remove any previous result records for this event

        // get data from this event
        $select = $this->db->db_get_rows("SELECT * FROM t_race
                                          WHERE `eventid` = {$this->eventid}
                                          ORDER BY fleet ASC, points ASC");

        // build multi-record insert query
        $query = <<<EOT
           INSERT INTO `t_result` (`eventid`, `fleet`, `race_type`, `competitorid`, `class`, `sailnum`, `pn`,
                                   `helm`, `crew`, `club`, `lap`, `etime`, `ctime`, `atime`, `code`, `penalty`,
                                   `points`, `declaration`, `note`, `updby`) VALUES
EOT;
        foreach($select as $key=>$row)
        {
            $racetype = $this->fleet["{$row['fleet']}"]['scoring'];
            $query.= "\n($this->eventid, {$row['fleet']}, '$racetype', {$row['competitorid']}, '{$row['class']}', ".
                     "'{$row['sailnum']}', {$row['pn']}, '{$row['helm']}', '{$row['crew']}', '{$row['club']}', ".
                     "{$row['lap']}, {$row['etime']}, {$row['ctime']}, {$row['atime']}, '{$row['code']}', ".
                     "{$row['penalty']}, {$row['points']}, '{$row['declaration']}', '{$row['note']}', 'race_copy_results'),";
        }

        // u_writedbg("$query", __FILE__, __FUNCTION__, __LINE__); //debug:);
        $status = $this->db->db_query(rtrim($query,","));
        $status ? $msg = "copied race data to results table" : $msg = "FAILED to copy race data to results table" ;
        u_writelog($msg, $this->eventid);

        return $status;
    }


/* -------------- archive tables functions ---------------------------------------------------------- */

    public function clear_archives()
    {
        $constraint = array("eventid" => $this->eventid);
        $num_rows = $this->db->db_delete("a_lap", $constraint);
        $num_rows = $this->db->db_delete("a_finish", $constraint);
        $num_rows = $this->db->db_delete("a_race", $constraint);

        return $num_rows;
    }

    public function race_copy_archive()
    {
        $status = false;

        $this->clear_archives();         // first remove any previous archives of this event

        // copy the race data to the archive
        $t_race_query = "INSERT INTO a_race SELECT * FROM t_race WHERE eventid={$this->eventid}";
        if ($this->db->db_query($t_race_query))
        {
            $t_lap_query = "INSERT INTO a_lap SELECT * FROM t_lap WHERE eventid={$this->eventid}";
            if ($this->db->db_query($t_lap_query))
            {
                if ($this->pursuit)
                {
                    $t_finish_query = "INSERT INTO a_finish SELECT * FROM t_finish WHERE eventid={$this->eventid}";
                    if ($this->db->db_query($t_finish_query)) { $status = true; }
                }
                else
                {
                    $status = true;
                }
            }
        }

        $status ? $msg = "copied results to archive tables" : $msg = "FAILED to copy results to archive tables" ;
        u_writelog($msg, $this->eventid);

        return $status;
    }


/* ------------- utility methods ------------------------------------- */

  public function get_result_codes_used($codes_used = array())
    {
        $results = $this->db->db_getresultcodes("result");
        if (!empty($codes_used))
        {
            $output = array();
            foreach ($results as $code=>$detail)
            {
                if (in_array($code, $codes_used))
                {
                    $output[$code] = $detail;
                }
            }
            $results = $output;
        }
        return $results;
    }


    public function render_race_result($loc, $result_status, $include_club, $result_notes, $fleet_msg = array())
    {
        global $tmpl_o;

        // get system info in case not read from racemanager
        if (is_readable("$loc/config/racemanager_cfg.php"))   // set racemanager config file content into SESSION
        {
            include("$loc/config/racemanager_cfg.php");  // this info is all in the ini file
        }
        else
        {
            $_SESSION['sys_name'] = "raceManager";                                   // name of system
            $_SESSION['sys_release'] = "";                                           // release name
            $_SESSION['sys_version'] = "";                                           // code version
            $_SESSION['sys_copyright'] = "Elmswood Software " . date("Y");           // copyright
            $_SESSION['sys_website'] = "";                                           // website
        }

        // get club info
        $club = $this->db->db_getinivalues(true);

        // get event info
        $event = $this->db->db_get_row("SELECT * FROM t_event WHERE id = $this->eventid");
        $event_label = $event['event_name'];
        !empty($event['event_start']) ? $event_label.= " - ".date("H:i", strtotime($event['event_start'])) : $event_label.= " - ".$event['event_order'];
        //u_writedbg("<pre>".print_r($event,true)."</pre>", __FILE__, __FUNCTION__, __LINE__); //debug:);
        //echo "<pre>".print_r($event,true)."</pre>";

        // get OOD information
        $ood = $this->db->db_get_row("SELECT * FROM t_eventduty WHERE eventid = $this->eventid and dutycode = 'ood_p' ");
        //u_writedbg("<pre>".print_r($ood,true)."</pre>", __FILE__, __FUNCTION__, __LINE__); //debug:);
        //echo "<pre>".print_r($ood,true)."</pre>";

        // get fleet information and reindex
        $fleet = $this->db->db_get_rows(
            "SELECT * FROM t_cfgfleet WHERE eventcfgid = {$event['event_format']} ORDER BY start_num, fleet_num");
        array_unshift($fleet, null);
        unset($fleet[0]);
        $num_fleets = count($fleet);
        //u_writedbg("<pre>".print_r($fleet,true)."</pre>", __FILE__, __FUNCTION__, __LINE__); //debug:);
        //echo "<pre>".print_r($fleet,true)."</pre>";

        // get result information
        $codes_used = array();
        $result = array();
        for ($i = 1; $i <= $num_fleets; $i++) {
            // add fleet messages
            isset($fleet_msg[$i]) ? $fleet[$i]['msg'] = $fleet_msg[$i] : $fleet[$i]['msg'] = "";

            // get results for this fleet
            $result[$i] = $this->get_race_results($i);

            // check for codes used in the results
            foreach ($result[$i] as $row) {
                if ($row['code']) {
                    $codes_used[] = $row['code'];
                }
            }
        }

        // get code information for codes used
        $result_codes = $this->get_result_codes_used(array_unique($codes_used));
        $codes_info = u_get_result_codes_info($result_codes, $codes_used);

        $opts = array(
            "inc-pagebreak" => false,                                                // page break after each fleet
            "inc-codes"     => true,                                                 // include key of codes used
            "inc-club"      => $include_club,                                        // include club name for each competitor
            "inc-turnout"   => true,                                                 // include turnout statistics
            "race-label"    => "number",                                             // use race number or date for labelling races
            "club-logo"     => $_SESSION['baseurl']."/config/images/club_logo.jpg",  // if set include club logo
            "styles"        => file_get_contents($_SESSION['baseurl']."/config/style/result_classic.css")     // styles to be used
        );

        $fields = array(
            "club_name"     => $club['clubname'],
            "event_name"    => $event['event_name'],
            "event_date"    => $event['event_date'],
            "event_start"   => $event['event_start'],
            "event_wind"    => u_getwind_str(array("wd_start" => $event['wd_start'], "wd_end" => $event['wd_end'],
                                                   "ws_start" => $event['ws_start'], "ws_end" => $event['ws_end'])),
            "event_ood"     => $ood['person'],
            "result_notes"  => $result_notes,
            "result_status" => $result_status,
            "sys_name"      => $_SESSION['sys_name'],
            "sys_version"   => $_SESSION['sys_version'],
            "sys_release"   => $_SESSION['sys_release'],
            "sys_copyright" => "Elmswood Software " . date("Y"),
            "pagetitle"     => $event_label,
        );

        $params = array(
            "fleet"         => $fleet,
            "result"        => $result,
            "opts"          => $opts,
            "codes"         => $codes_info,
            "sys_website"   => $_SESSION['sys_website']
        );

        //echo "<pre>".print_r($params,true)."</pre>";
        $htm = $tmpl_o->get_template("race_sheet", $fields, $params);

        return $htm;
    }


    public function create_result_inventory($inventory_year, $target_filepath, $system_info)
    {
        // FIXME arguments in result_publish_pg/#219  and publish_results/#298

        // set default start date to first day of relevant year
        $startdate = "$inventory_year-01-01";

        // get duty codes
        $codes = array();
        $dutycodes = $this->db->db_getsystemcodes("rota_type");
        foreach ($dutycodes as $dutycode)
        {
            $codes["{$dutycode['code']}"] = $dutycode['label'];
        }

        $inventory = array();

        // FIXME - are these session variables set up
        $inventory["admin"] = array(
            "type"       => "event_inventory",
            "createdate" => date("Y-m-d H:i"),
            "source"     => $system_info['sys_name'] . "-" . $system_info['sys_version'],
            "club"       => $system_info['clubname'],
            "resultpath" => $system_info['result_path'],
            "resulturl"  => $system_info['result_url'],
        );
        
        //echo "<pre>".print_r($inventory["admin"],true)."</pre>";

        $event_o = new EVENT($this->db);
        $rota_o = new ROTA($this->db);

        // get all events from startdate
        $events = $event_o->get_events("racing", "active", array("start"=>$startdate));

        $inventory["events"] = array();
        foreach ($events as $event) {
            $inventory["events"][$event['id']] = array(
                "eventdate"    => $event['event_date'],
                "eventtime"    => $event['event_start'],
                "eventorder"   => $event['event_order'],
                "eventname"    => $event['event_name'],
                "eventtype"    => $event['event_type'],
                "eventfmt"     => $event['event_format'],
                "eventnotes"   => $event['event_notes'],
                "resultnotes"  => $event['result_notes'],
                "eventstatus"  => $event['event_status'],
                "tidetime"     => $event['tide_time'],
                "tideheight"   => $event['tide_height'],
                "eventdisplay" => $event['display_code']
            );

            // get duties
            $duties = $rota_o->get_event_duties($event['id']);
            $dutyarray = array();
            if ($duties)
            {
                foreach ($duties as $duty) {
                    $dutyarray[] = array(
                        "dutytype" => $codes["{$duty['dutycode']}"],
                        "dutyname" => $duty['person'],
                        "dutyphone"=> $duty['phone'],
                    );
                }
            }

            $inventory["events"][$event['id']]['duties'] = $dutyarray;

            // get results files for this event
            $files = $this->get_result_files($event['id']);

            $resultsfiles = array();
            foreach ($files as $file) {
                //echo "<pre>FILEDATA".print_r($file,true)."</pre>";
                $resultsfiles[] = array(
                    "type"   => $file["folder"],
                    "format" => $file["format"],
                    "file"   => $file["filename"],
                    "notes"  => $file["notes"],
                    "status" => $file["status"],
                    "rank"   => $file["rank"],
                    "update" => $file["upddate"],
                );
            }

            $inventory["events"][$event['id']]['resultsfiles'] = $resultsfiles;
        }

        // encode as json
        $jbufr = json_encode($inventory);
        // echo $jbufr;

        // create inventory file
        $status = file_put_contents($target_filepath, $jbufr);

        return $status;
    }

    public function get_race_results($fleetnum = 0)
    {
        empty($fleetnum) ? $where = "" : $where = " AND fleet = $fleetnum ";

        $query = "SELECT *, helm as team, points as result FROM t_result
                  WHERE eventid={$this->eventid} $where
                  ORDER BY fleet, points ASC, pn, class, sailnum+0";
        // u_writedbg("$query", __FILE__, __FUNCTION__, __LINE__); //debug:);
        $results = $this->db->db_get_rows($query);
        // u_writedbg("<pre>".print_r($results,true)."</pre>", __FILE__, __FUNCTION__, __LINE__); //debug:);
        foreach ($results as $key => $result)
        {
            $results[$key]['team'] = u_conv_team($result['helm'], $result['crew']);

            $inc_times = true;
            $inc_points = true;
            $code_info['scoringtype'] = "";
            if (!empty($result['code']))
            {
                $code_info = $this->db->db_getresultcode($result['code']);
                if ($code_info['scoringtype'] == "race" or $code_info['scoringtype'] == "series")
                {
                    $inc_times = false;
                }
            }

            if ($inc_times)
            {
                $results[$key]['etime']  = u_conv_secstotime($result['etime']);
                $results[$key]['ctime']  = u_conv_secstotime($result['ctime']);
                $results[$key]['atime']  = u_conv_secstotime($result['atime']);
            }
            else
            {
                $results[$key]['etime']  = " - ";
                $results[$key]['ctime']  = " - ";
                $results[$key]['atime']  = " - ";
            }


            $results[$key]['result'] = u_conv_result($result['code'], $code_info['scoringtype'], $result['points']);
        }

        return $results;
    }

//    public function get_result_codes_used($codes_used = array())
//    {
//        $results = $this->db->db_get_rows("SELECT * FROM t_code_result ORDER BY code");
//
//        if (!empty($codes_used))
//        {
//            $output = array();
//            foreach ($results as $result) {
//                if (in_array($result['code'], $codes_used))
//                {
//                    $output[] = $result;
//                }
//            }
//        }
//        else
//        {
//            $output = $results;
//        }
//
//        return $output;
//    }
}



