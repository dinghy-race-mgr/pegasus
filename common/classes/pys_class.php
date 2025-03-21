<?php

class PYS
{
    private $db;

    //Method: construct class object
    public function __construct(DB $db, $basepath, $baseurl)
    {
        $this->db       = $db;
        $this->admin    = array();
        $this->commands = array();
        $this->error    = array();
        $this->outfile  = "";
        $this->outpath  = "";
        $this->outurl   = "";
        $this->events   = array();
        $this->fleets   = array();
        $this->results  = array();
        $this->data     = array();
        $this->path     = $basepath;
        $this->baseurl  = $baseurl;
        $this->logfile  = "";
        $this->logurl   = "";
        $this->races    = array();
    }

    public function get_control_files()
    {
        $control_files = array();
        $files = scandir($this->path);
        foreach($files as $file)
        {
            if (is_file($this->path . "/" . $file)) {
                $info = pathinfo($this->path . "/" . $file);
                if ($info["extension"] == "json") {
                    $this->read_control_file($file);
                    $control_files[] = array(
                        "url" => $this->path . "/" . $file,
                        "name"=>str_replace(' ', '_', $this->admin['name'])
                    );
                }
            }
        }

        return $control_files;
    }

    public function read_control_file($file)
    {
        // reads json files with processing commands and returns as array
        $filepath = $this->path."/".$file;
        $str = file_get_contents($filepath);
        $jsonData = json_decode($str, true);
        $this->admin = $jsonData['admin'];
        $this->commands = $jsonData['commands'];

        if (empty($this->commands))
        {
            return false;
        }
        else
        {
            return $this->commands;
        }
    }

    public function get_admin_info()
    {
        return $this->admin;
    }

    public function get_commands_info()
    {
        return $this->commands;
    }
    
    public function swap_control_dates($start_date, $end_date)
    {
        // modifies command information to reflect the start and end dates specified by user

        $swapped = false;
        if (!empty($start_date) and !empty($end_date))
        {
            $swapped = true;

            // set date-year in admin
            $this->admin['data-year'] = date("Y", strtotime($start_date));

            // loop over commands
            foreach ($this->commands as $k => $command)
            {
                // remove year in series
                if (strtolower($command['mode']) == "series")
                {
                    $this->commands[$k]['attribute'] = strtok($command['attribute'],"-");
                }

                // change start-date and end-date
                $this->commands[$k]['start-date'] = date("Y-m-d", strtotime($start_date));
                $this->commands[$k]['end-date']   = date("Y-m-d", strtotime($end_date));

                // change description
                if (strpos($command['description'], date("Y", strtotime($start_date) )) === false)
                {
                    $this->commands[$k]['description'] = $command['description']." ".date("Y", strtotime($start_date));
                }
            }

            $swapped  = true;
        }

        return $swapped;
    }

    public function set_filename($command, $admin, $pysid, $file_type)
    {
        // gets path/filename for output data
        $str = "SYC_".date("Ymd")."_".str_replace(' ', '', $command['description']);
        if (!empty($pysid)) { $str.= "_$pysid"; }

        $this->outfile = $str.".$file_type";

        // get path
        $this->outpath = $this->path."/".$admin['data-year']."/".$this->outfile;
        $dirname = dirname($this->outpath);
        if (!is_dir($dirname))
        {
            mkdir($dirname, 0755, true);
        }

        // get url
        $this->outurl = $this->baseurl."/".$admin['data-year']."/".$this->outfile;

//        echo "<pre>base: {$this->baseurl}| file: {$this->outfile}| url:{$this->outurl}|path: {$this->outpath}|dir: $dirname|</pre>";
//        exit();

        return $this->outpath;
    }

    public function get_filename($mode)
    {
        if ($mode == "disk") {
            return $this->outpath;
        } else {
            return $this->outurl;
        }
    }

    public function set_log_filename($command)
    {
        $name = "SYC_".date("Ymd")."_".str_replace(' ', '', $command['description']).".log";
        $this->logfile = $this->path."/logs/".$name;

        $this->logurl = $this->baseurl."/logs/".$name;

        return $this->logfile;
    }

    public function get_log_filename($mode)
    {
        if ($mode == "disk") {
            return $this->logfile;
        } else {
            return $this->logurl;
        }
    }

    public function set_events($command)
    {
        // initialise event/fleet arrays
        $this->events = array();
        $this->fleets = array();

        // get event details for command
        $status = 0;
        $field_list  = "a.`id`, `event_name`, `event_date`, `event_start`, `event_order`, `event_format`, `tide_time`, `tide_height`, 
                        `ws_start`,` wd_start`, `ws_end`, `wd_end`";
        $order_list  = "`event_date` ASC, `event_start` ASC";

        // set selection where and join constraints
        $mode_arr = array(
            "series" => array( "subwhere" => "`series_code` LIKE '{$command['attribute']}%'", "join" => ""),
            "format" => array( "subwhere" => "`race_name` = '{$command['attribute']}'", "join" => " JOIN t_cfgrace as b ON a.`event_format`=b.`id` "),
            "list"   => array( "subwhere" => "`id` IN ({$command['attribute']})", "join" => ""),
            "name"   => array( "subwhere" => "`event_name` LIKE '%{$command['attribute']}%'", "join" => ""),
        );

        // date, event type and event status constraints
        $where_event = " event_type = 'racing' and a.`active` = 1 ";
        $where_date = "";
        if (!empty($command['start-date']) and !empty($command['end-date']))
        {
            $where_date  = " and `event_date` >= '{$command['start-date']}' and `event_date` <= '{$command['end-date']}' ";
        }
        empty($where_date) ? $where = $where_event : $where = $where_event.$where_date;

        // get list of matching events
        if (empty($command['attribute']))
        {
            $status = -1;          // missing information - not possible to process
        }
        else
        {
            if (array_key_exists($command['mode'], $mode_arr))
            {
                $subwhere = $mode_arr[$command['mode']]['subwhere'];
                $join = $mode_arr[$command['mode']]['join'];
            }
            else
            {
                $status = -2;  // mode not recognised
            }

            if ($status == 0)
            {
                // get list of events to process for this command
                $sql = "SELECT a.`id` FROM t_event as a $join WHERE $where and $subwhere ORDER BY `id` ASC";
                $rs = $this->db->db_get_rows($sql);
                $list = "";

                foreach ($rs as $eventid)
                {
                    $list.= $eventid['id'].",";
                }
                $list = rtrim($list,",");

                // now get event fields for all requested events
                $sql = "SELECT $field_list FROM t_event as a WHERE `id` in ($list) ORDER BY $order_list";
                $rs = $this->db->db_get_rows($sql);

                if ($rs)
                {
                    $status = count($rs);
                    $i = 0;
                    foreach ($rs as $row)
                    {
                        $i++;
                        $row['race-num'] = $i;
                        $this->events[$row['id']] = $row;
                    }

                    foreach ($this->events as $row)
                    {
                        $rs = $this->db->db_get_rows("SELECT `fleet_num`, `fleet_code`, `fleet_name`, `pursuit` FROM t_cfgrace as a 
                                              JOIN t_cfgfleet as b on a.`id`=b.`eventcfgid` WHERE a.`id` = {$row['event_format']} ORDER BY `fleet_num`");
                        $i = 0;
                        foreach($rs as $fleet)
                        {
                            $i++;
                            $this->fleets[$row['id']][$i] = $fleet;
                        }
                    }
                }
                else
                {
                    $status = -3;
                }
            }
        }

        return $status;
    }

    public function get_events()
    {
        // get fleet names for this event
        return $this->events;
    }

    public function get_fleet_names($eventid)
    {
        // get fleet names for this event
        return $this->fleets[$eventid];
    }

    public function set_fleet_results($eventid, $fleetnum)
    {
        // initialise results array
        $this->results[$eventid][$fleetnum] = array();

        // get results for fleet from t_result
        $event_name = $this->events[$eventid]['event_name'];
        $event_date = $this->events[$eventid]['event_date'];
        $event_start= $this->events[$eventid]['event_start'];
        $event_num = $this->events[$eventid]['race-num'];

        $sql = "SELECT '$event_name' as `event-name`, '$event_date' as `event-date`, '$event_start' as `event-start`,
                '$event_num' as `race-num`, fleet, race_type, competitorid, class, sailnum, helm, a.crew as crew, pn, lap, 
                etime, ctime, atime, code, points, penalty, b.category, b.rig, b.crew as crewnum, b.keel, b.spinnaker, b.engine 
                FROM t_result as a JOIN t_class as b ON  a.class=b.classname WHERE eventid = $eventid and fleet = $fleetnum ORDER BY points ASC";
        //echo "<pre>$sql</pre>";
        $rs = $this->db->db_get_rows($sql);

        if (count($rs) > 0)
        {
            $this->results[$eventid][$fleetnum] = $rs;
            $status = count($this->results[$eventid][$fleetnum]);
        }
        else
        {
            $status = false;
        }

        return $status;
    }


    public function check_valid_results($eventid, $fleetnum)
    {
        // check that results for this fleet/event are valid for PYS analysis
        $checks = array();
        $this->fleets[$eventid][$fleetnum]['include'] = 1;


        // check 0:  do we have any entries
        $check = 0;
        if (count($this->results[$eventid][$fleetnum]) > 0)
        {
            $checks[$check] = array("result" => true, "type" => "", "msg" => "");
        }
        else
        {
            $checks[$check] = array("result" => false, "type" => "0", "msg" => "failed entries check - no results");
            $this->fleets[$eventid][$fleetnum]['include'] = 0;
        }


        // check 1:  check that this is not a pursuit race
        $check = 1;
        if ($this->fleets[$eventid][$fleetnum]['pursuit'])
        {
            $checks[$check] = array("result" => false, "type" => "1", "msg" => "failed scoring check - race is a pursuit race");
            $this->fleets[$eventid][$fleetnum]['include'] = 0;
        }


        // check 2:  do we have three or more finishers
        if ($checks[0]['result'])                // only do this check if we have entries
        {
            $check = 2;
            $thresh_2 = 3;
            $i = 0;
            foreach ($this->results[$eventid][$fleetnum] as $rs)
            {
                if (empty($rs['code']) or (!empty($rs['code']) and $rs['penalty'] > 0.0))
                {
                    $i++;
                }

                if ($i >= $thresh_2)
                {
                    $checks[$check] = array("result" => true, "type" => "", "msg" => "");
                    break;
                }
            }
            if (empty($checks[$check]))
            {
                $checks[$check] = array("result" => false, "type" => "2", "msg" => "failed entries check - less than 3 entries");
                $this->fleets[$eventid][$fleetnum]['include'] = 0;
            }
        }


        // check 3:  do we have finishers from more than one class
        if ($checks[0]['result'])                // only do this check if we have entries
        {
            $check = 3;
            $thresh_3 = 2;
            $i = 0;
            $class = "";
            foreach ($this->results[$eventid][$fleetnum] as $rs)
            {
                if (empty($rs['code']) or (!empty($rs['code']) and $rs['penalty'] > 0.0))
                {
                    if ($rs['class'] != $class)
                    {
                        $i++;
                        $class = $rs['class'];
                    }
                }

                if ($i >= $thresh_3)
                {
                    $checks[$check] = array("result" => true, "type" => "", "msg" => "");
                    break;
                }
            }
            if (empty($checks[3]))
            {
                $checks[$check] = array("result" => false, "type" => "3", "msg" => "failed entries check - only one class");
                $this->fleets[$eventid][$fleetnum]['include'] = 0;
            }
        }


        // check 4:  race is longer than 20 minutes (1200 seconds)
        if ($checks[0]['result'])                // only do this check if we have entries
        {
            $check = 4;
            $thresh_4 = 1200;

            $max_et = max(array_column($this->results[$eventid][$fleetnum], 'etime'));

            if ($max_et >= $thresh_4)
            {
                $checks[$check] = array("result" => true, "type" => "", "msg" => "");
            }
            else
            {
                $checks[$check] = array("result" => false, "type" => "4", "msg" => "failed time check - race less than 20 minutes ");
                $this->fleets[$eventid][$fleetnum]['include'] = 0;
            }
        }

        return $checks;
    }


    public function process_result_data($eventid, $fleetnum, $fleetname, $status)
    {
        // generate results to go into output files

        // - find num finishers
        // - find average atime for <thresh = 66%> finishers
        // - find achieved PN for each finisher to et average atime + mark excluded if actual atime is > (Thresh = 105%) of average atime

        $thresh_finishers = 0.66;
        $thresh_exclude = 1.05;
        $num_records = 0;
        if ($status) {

            // get num finishers and max lap
            $i = 0;
            $max_lap = 0;
            foreach ($this->results[$eventid][$fleetnum] as $rs) {
                if (empty($rs['code']) or (!empty($rs['code']) and $rs['penalty'] > 0.0)) {
                    $i++;

                    if ($rs['lap'] > $max_lap) {
                        $max_lap = $rs['lap'];
                    }
                }
            }
            $num_finishers = $i;

            $j = ceil($num_finishers * $thresh_finishers);  // number of boats to derive average corrected time

            // get average corrected time for top 66% (Note using aggregate corrected time)
            $i = 0;
            $sum_atime = 0;
            foreach ($this->results[$eventid][$fleetnum] as $rs) {
                if (empty($rs['code']) or (!empty($rs['code']) and $rs['penalty'] > 0.0)) {
                    $sum_atime = $sum_atime + $rs['atime'];
                    $i++;
                }

                if ($i >= $j) {
                    break;
                }
            }
            $avg_atime = $sum_atime / $j;


            // get achieved PN for each finisher
            foreach ($this->results[$eventid][$fleetnum] as $k => $rs)
            {
                $num_records++;

                if (empty($rs['code']) or (!empty($rs['code']) and $rs['penalty'] > 0.0))
                {

                    // achieved PN
                    $aggregate_etime = ceil($rs['etime'] / $rs['lap']) * $max_lap;

                    $achieved_pn = round(($aggregate_etime * 1000) / $avg_atime);

                    $this->results[$eventid][$fleetnum][$k]['apn'] = $achieved_pn;

                    // included/excluded status

                    if ($rs['atime'] > ($avg_atime * $thresh_exclude)) {
                        $this->results[$eventid][$fleetnum][$k]['status'] = "exc";
                    } else {
                        $this->results[$eventid][$fleetnum][$k]['status'] = "inc";
                    }

                    // fleetname
                    $this->results[$eventid][$fleetnum][$k]['fleet-name'] = $fleetname;
                }
                else // do not include this result
                {
                    unset($this->results[$eventid][$fleetnum][$k]);
                }
            }
        }
        else
        {
            unset($this->results[$eventid][$fleetnum]);
        }

        return $num_records;
    }


    public function output_csv($data)
    {
        // outputs data into a RYA cvs format
        $fields = array("class", "race-num", "event-date", "fleet-name", "points", "sailnum", "helm", "crew", "pn",
            "lap", "etime", "atime", "crewnum", "rig", "spinnaker");

        $cols = array("Class", "Race Number", "Race Date", "Start Name", "Rank", "SailNo", "Helm", "Crew", "pn",
            "Laps", "Elapsed", "Corrected", "Persons", "Rig", "Spin");

        $rows = array();
        $i = 0;
        foreach ($data as $eventid => $event)
        {
            foreach ($event['fleets'] as $k => $fleet)
            {
                if ($fleet['include'] == 1)
                {
                    foreach ($fleet['entries'] as $j => $result)
                    {
                        $row = array();
                        foreach ($fields as $field)
                        {
                            $row["$field"] = $result["$field"];
                        }
                        $i++;
                        $rows[] = $row;
                    }
                }
            }
        }


        // create output file
        $status = "0";
        $fp = fopen($this->outpath, 'w');
        if ($fp)
        {
            $r = fputcsv($fp, $cols, ',');
            if (!$r) { $status = "-2"; }

            foreach ($rows as $row)
            {
                if ($status != "0") { break; }
                $r = fputcsv($fp, $row, ',');
                if (!$r) {$status = "-3"; }
            }
            fclose($fp);
        }
        else
        {
            $status = "-1";
        }

        return $status;
    }

    public function output_xml($xml)
    {
        // outputs xml data into a RYA xml format file
        $status = "0";
        $fp = fopen($this->outpath, 'w');
        if ($fp)
        {
            $r = fwrite($fp, $xml);
            if ($r === false)
            {
                $status = "-2";
            }
            elseif ($r == 0)
            {
                $status = "-3";
            }
            fclose($fp);
        }
        else
        {
            $status = "-1";
        }

        return $status;
    }

    public function get_results()
    {
        // outputs data into a RYA xml format file
        $races = $this->events;
        foreach ($races as $eventid=>$race)
        {
            // get fleets for this race
            $races[$eventid]['fleets'] = $this->fleets[$eventid];

            foreach ($races[$eventid]['fleets'] as $fleetnum=>$fleet)
            {
                // get results for this fleet
                $races[$eventid]['fleets'][$fleetnum]['entries'] = $this->results[$eventid][$fleetnum];
            }
        }

        return $races;
    }
}
