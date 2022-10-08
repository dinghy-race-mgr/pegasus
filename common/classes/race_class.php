<?php

/**
 *  RACE class
 * 
 *  Handles interaction with t_race, t_racestate and t_lap
 * 
 *  METHODS
 *     __construct
 * 
 *     racestate_delete        - deletes racestate record(s) for all or one fleet in an event
 *     racestate_get           - gets racestate record(s) for all or one fleet in an event
 *     racestate_update        - updates one or more records in racestate for this event
 *     racestate_updateentries - updates entry count for a fleet in racestate
 * 
 *     race_getentries     - gets entries in event for use on enter page (with search constraint)
 *     race_getstarters    - gets starter in event for user on start page (with search constraint)
 *     race_getresults     - gets results in event for user on results page (with search constraint)
 *     race_gettimings     - gets starter in event for user on timings page (with search constraint)
 *     race_entry_get      - get entries for race (private))
 *      
 *     race_delete         - deletes race record(s) for all or one fleet in an event
 *     race_laps_delete    - deletes lap record(s) for all or one fleet in an event
 *     race_laps_set       - sets finish lap for all or one fleet in event
 *     race_times_init     - resets time related fields to initial values for all or one fleet in an event
 *     race_codes_init     - clears all codes except those excluded for all or one fleet in an event
 *     race_entry_counts   - gets entry numbers for each fleet and overall event
 * 
 *     entry_add             - adds entry to event 
 *     entry_get             - gets entry record in event by id or competitor id       
 *     entry_delete          - deletes entry in event
 *     entry_duty_set        - sets an entry as doing a duty
 *     entry_duty_unset      - unsets duty status for an entry
 *     entry_code_set        - sets code (and status) for an entry
 *     entry_code_unset      - removes code from an entry
 *     entry_update          - updates an entry 
 *     entry_declare         - handles result of declaration/retirement/protest   
 *     entry_declaration_set - sets declaration code for entry
 *     entry_resultsstatus   - gets results display status for an entry
 *     entry_time            - times an entry at a lap or the finish
 *     entry_time_undo       - removes the last lap/finish timing
 * 
 *     entry_lap_get         - retrieves one or all lap records for an entry
 *     entry_lap_add         - adds a lap record for an entry
 *     entry_lap_delete      - deletes one or all lap records for a fleet or an entry
 *     entry_lap_update      - updates a lap record
 *     entry_lap_last        - gets lap record for last timing in an event

*/

class RACE
{
    private $db;
    
    //Method: construct class object
    //public function __construct(DB $db, $eventid)
    public function __construct($db, $eventid)
    {
	    $this->db = $db;
	    $this->eventid = $eventid;
        $this->scoring = array();
        //u_writedbg("<pre>session".print_r($_SESSION["e_{$eventid}"],true)."</pre>", __FILE__, __FUNCTION__, __LINE__); //debug:

        for ( $i = 1; $i <= $_SESSION["e_$eventid"]['rc_numfleets']; $i++ )
        {
            $this->scoring[$i] = $_SESSION["e_{$eventid}"]["fl_$i"]['scoring'];
        }
        if (isset($_SESSION["e_$eventid"]['pursuit']))
        {
           $this->pursuit = $_SESSION["e_$eventid"]['pursuit'];
        }
        //u_writedbg("<pre>eventid:{$this->eventid}|pursuit:{$this->pursuit}<br>scoring".print_r($this->scoring,true)."</pre>", __FILE__, __FUNCTION__, __LINE__); //debug:
    }


/**
 *   ---- RACESTATE methods ---------------------------------------- 
 * 
 *     racestate_delete        - deletes racestate record(s) for all or one fleet in an event
 *     racestate_get           - gets racestate record(s) for all or one fleet in an event
 *     racestate_update        - updates one or more records in racestate for this event
 *     racestate_updateentries - updates entry count for a fleet in racestate

*/

//    public function racestate_delete($fleetnum=0)
//    {
//        $constraint = array("eventid"=>$this->eventid);
//        if ($fleetnum != 0)
//        {
//            //$constraint[] = array("race"=>$fleetnum);
//            $constraint[] = array("fleet"=>$fleetnum);
//        }
//
//        $numrows = $this->db->db_delete("t_racestate", $constraint);
//
//        return $numrows;
//    }

    public function racestate_analyse($fleetnum, $fleetstarttime, $racetype)
    {
        $result = array(
            "starttime"  => $fleetstarttime,
            "maxlap"     => 0,
            "currentlap" => 0,
            "entries"    => 0,
            "status"     => "unknown"
        );

        $current_et = $this->entry_calc_et(time(), $fleetstarttime);

        // initialise counts (R = racing, F = finished after sailing all laps, X = excluded (e.g. NCS),
        // FF = force finished in a average lap race before the leader has finished)
        $status_counts = array("R" => 0, "F" => 0, "X" => 0, "FF" => 0);

        $race = $this->race_getresults($fleetnum);  // get race data for this fleet
        $result['entries'] = count($race);

        $maxlap = 0;
        $currentlap = 0;
        foreach ($race as $entry)
        {
            if ($racetype == "average")
            {
                if ($entry['status'] == "F")
                {
                    $status_counts["F"]++;
                    if ($entry['lap'] < $entry['finishlap']) { $status_counts["FF"]++; }  // count as force finished by OOD
                }
                else
                {
                    $status_counts["{$entry['status']}"]++;
                }
            }
            else
            {
                $status_counts["{$entry['status']}"]++;
            }

            if ($entry['finishlap'] > $maxlap) { $maxlap = $entry['finishlap']; }
            if ($entry['lap'] > $currentlap)   { $currentlap = $entry['lap']; }
        }


        if ($race)
        {
            //u_writedbg("$fleetnum|$fleetstarttime|$current_et|$racetype", __CLASS__, __FUNCTION__, __LINE__);
//            u_writedbg("<pre>".print_r($status_counts,true)."</pre>", __CLASS__, __FUNCTION__, __LINE__);
            if ($current_et <= 0)          // race not started yet
            {
                $result['status'] = "notstarted";
//                u_writedbg("<pre>step1: {$result['status']}</pre>", __CLASS__, __FUNCTION__, __LINE__);
            }

            elseif ($status_counts['R'] == 0)                                                // nobody racing (all finished or excluded)
            {
                $result['status'] = "allfinished";
//                u_writedbg("<pre>step2: {$result['status']}</pre>", __CLASS__, __FUNCTION__, __LINE__);
            }

            elseif ($status_counts['R'] > 0 and $status_counts['F'] > 0)                      // some people finished
            {
                if ($status_counts['F'] == $status_counts['FF'])                              // only forced finishes so far
                {
                    $result['status'] = "inprogress";
                }
                else                                                                          // race is finishing
                {
                    $result['status'] = "finishing";
                }
            }

            else                                                                             // race must be in progress (no finishers)
            {
                $result['status'] = "inprogress";
//                u_writedbg("<pre>step5: {$result['status']}</pre>", __CLASS__, __FUNCTION__, __LINE__);
            }
            $result['maxlap'] = $maxlap;
            $result['currentlap'] = $currentlap;
        }
//        u_writedbg("<pre>step6: {$result['status']}</pre>", __CLASS__, __FUNCTION__, __LINE__);
//        u_writedbg("<pre>RESULT: ".print_r($result,true)."</pre>", __CLASS__, __FUNCTION__, __LINE__);

        return $result;
    }
    
    
    public function racestate_get($fleetnum=0)
    {
        $racestates = array();

        $where = "eventid = ".$this->eventid;
        if ($fleetnum != 0) { $where.= " AND fleet = $fleetnum"; }
        
        $query = "SELECT * FROM t_racestate WHERE $where order by fleet";
        $result = $this->db->db_get_rows($query);
        
        if ($result) 
        { 
           foreach ($result as $row)
           {
               //$racestates[$row['race']] = $row;
               $racestates[$row['fleet']] = $row;
           }
        }
        return $racestates;
    }
    
    
    public function racestate_update($update, $constraint)
    {
        $constraint['eventid'] = $this->eventid;
        //u_writedbg("<pre>".print_r($update,true)."<br>".print_r($constraint,true)."</pre>",__FILE__,__FUNCTION__,__LINE__);
        $result = $this->db->db_update("t_racestate", $update, $constraint);  
               
        return $result;
    }
    
    
//    public function racestate_updateentries($fleetnum, $change)
//    {
//        $result = $this->db->db_query("UPDATE t_racestate SET entries = entries $change
//                                       WHERE eventid = {$this->eventid} and fleet = $fleetnum");
//        $_SESSION["e_{$this->eventid}"]['result_status'] = "invalid";
//
//        return $result;
//    }

    public function racestate_updatestatus_all($numfleets, $page)
    {
        $dbgmsg = "";

        for ($i = 1; $i <= $numfleets; $i++)
        {
            $status_change = false;
            $racestatus = $this->racestate_analyse($i,
                $_SESSION["e_{$this->eventid}"]["fl_$i"]['starttime'], $_SESSION["e_{$this->eventid}"]["fl_$i"]['scoring']);

            $dbgmsg.= $_SESSION["e_{$this->eventid}"]["fl_$i"]['code']." - ".$racestatus['status']." | ";

            if ($racestatus['status'] == "unknown")
            {
                // send message to eventlog
                u_writelog("$page - fleet $i - UNKNOWN status [".__CLASS__." : ".__FUNCTION__." : ".__LINE__, $this->eventid);
            }
            else
            {
                if ($racestatus['status'] != $_SESSION["e_{$this->eventid}"]["fl_$i"]['status'])
                {
                    $status_change = true;
                    $racestatus['prevstatus'] = $_SESSION["e_{$this->eventid}"]["fl_$i"]['status'];
                }

                $upd = $this->racestate_update($racestatus, array("fleet"=>$i));
                if ($upd >= 0)
                {
                    $_SESSION["e_{$this->eventid}"]["fl_$i"]['starttime'] = $racestatus['starttime'];
                    $_SESSION["e_{$this->eventid}"]["fl_$i"]['maxlap'] = $racestatus['maxlap'];
                    $_SESSION["e_{$this->eventid}"]["fl_$i"]['currentlap'] = $racestatus['currentlap'];
                    $_SESSION["e_{$this->eventid}"]["fl_$i"]['entries'] = $racestatus['entries'];
                    $_SESSION["e_{$this->eventid}"]["fl_$i"]['status'] = $racestatus['status'];
                }

                if ($status_change)
                {
                    u_writelog("race status change: ".$_SESSION["e_{$this->eventid}"]["fl_$i"]['code']." fleet to ".$racestatus['status'], $this->eventid);
                }
            }
        }
        //u_writedbg($dbgmsg, __CLASS__, __FUNCTION__, __LINE__);
    }
    
    

/**
 *   ---- RACE methods --------------------------------------------------------- 
 *
 *     race_getentries     - gets entries in event for use on enter page (with search constraint)
 *     race_getstarters    - gets starter in event for user on start page (with search constraint)
 *     race_getresults     - gets results in event for user on results page (with search constraint)
 *     race_gettimings     - gets starter in event for user on timings page (with search constraint)
 *     race_entry_get      - get entries for race (private))
 *      
 *     race_delete         - deletes race record(s) for all or one fleet in an event
 *     race_laps_delete    - deletes lap record(s) for all or one fleet in an event
 *     race_laps_set       - sets finish lap for all or one fleet in event
 *     race_laps_max       - gets max number of laps for current entries
 *     race_times_init     - resets time related fields to initial values for all or one fleet in an event
 *     race_codes_init     - clears all codes except those excluded for all or one fleet in an event
 *     race_entry_counts   - gets entry numbers for each fleet and overall event
 *
 *     race_score          - score inividual race

*/


    public function race_getentries($constraint)
    {
        $fields = "id, class, sailnum, pn, helm, crew, club, code";
        
        if (empty($constraint)) 
        { 
            $where = "";
        }
        else
        {
            foreach( $constraint as $field => $value )
            {
                $clause[] = "`$field` = '$value'";
            }
            $where =  "AND ".implode(' AND ', $clause);
        }
        
        $order = "class, sailnum * 1";
        
        $entries = $this->race_entry_get($fields, $where, $order, false);

        return $entries;
    }
    
    
    public function race_getstarters($constraint)
    {
        $fields  = "id, fleet, class, sailnum, helm, code, status, declaration, lap, finishlap";
        
        if (empty($constraint)) 
        { 
            $where = "";
        }
        else
        {
            foreach( $constraint as $field => $value )
            {
                $clause[] = "`$field` = '$value'";
            }
            $where =  "AND ".implode(' AND ', $clause);
        }
              
        $order = "class, sailnum * 1";
        
        $starters = $this->race_entry_get($fields, $where, $order, false);
        
        return $starters;
    }
    
    
    public function race_getresults($fleetnum=0)
    {                
        $fields = "id, fleet, class, sailnum, helm, crew, club, pn, lap, finishlap, etime, ctime, atime, 
                   penalty, points, code, declaration, protest, status, note";

        $where = " ";
        if ($fleetnum > 0) { $where.= " AND fleet = $fleetnum "; }
        
        if  ($this->pursuit)
        {
            $order  = "fleet ASC, field(status, 'F','R','X'), points ASC, pn DESC, class, sailnum * 1";
        }
        else
        {
            $order  = "fleet ASC, field(status, 'F','R','X'), points ASC, pn ASC, class, sailnum * 1";
        }
        $results = $this->race_entry_get($fields, $where, $order, true);
        
        return $results;
    }
    
    
    public function race_gettimings($listorder = "", $fleetnum = 0)
    {
        $cfg = array(
            "class"    => array(
                "fields" => "id, fleet, start, class, sailnum, helm, pn, lap, finishlap, etime, code, status, declaration",
                "order"  => "fleet ASC, class, sailnum ASC",
            ),
            "class-list"    => array(
                "fields" => "id, fleet, start, class, sailnum, helm, pn, lap, finishlap, etime, code, status, declaration",
                "order"  => "class, CAST(sailnum AS unsigned)",
            ),
            "sailnum-list"    => array(
                "fields" => "id, fleet, start, class, sailnum, helm, pn, lap, finishlap, etime, code, status, declaration",
                "order"  => "CAST(sailnum AS unsigned), sailnum ASC",
            ),
            "fleet-list"    => array(
                "fields" => "id, fleet, start, class, sailnum, helm, pn, lap, finishlap, etime, code, status, declaration",
                "order"  => "fleet, class, CAST(sailnum AS unsigned)",
            ),
            "position" => array(
                "fields" => "id, fleet, start, class, sailnum, helm, pn, lap, finishlap, etime, code, status, declaration",
                "order"  => "fleet ASC, position ASC, pn DESC, class, sailnum ASC",
            ),
            "pn"       => array(
                "fields" => "id, fleet, start, class, sailnum, helm, pn, lap, finishlap, etime, code, status, declaration",
                "order"  => "fleet ASC, pn ASC, class, sailnum AS",
            ),
            "ptime"    => array(
                "fields" => "id, fleet, start, class, sailnum, helm, pn, lap, finishlap, etime, code, status, declaration",
                "order"  => "fleet ASC, ptime ASC, pn DESC, class, sailnum ASC",
            )
        );

        $where = "";
        if (!empty($fleetnum)) { $where.= " AND fleet = $fleetnum "; }
//        if (!empty($entryid)) { $where.= " AND id = $entryid "; }

        if (isset($listorder))
        {
            $fields = $cfg[$listorder]['fields'];
            $order  = $cfg[$listorder]['order'];
        }
        else
        {
            $fields = $cfg["class"]['fields'];
            $order  = $cfg["class"]['order'];
        }

        if ($this->pursuit) { $order = "fleet ASC, lap DESC, etime DESC, pn DESC, class, sailnum ASC"; }
        
        $rs = $this->race_entry_get($fields, $where, $order, true);
        if (empty($fleetnum) and strpos($listorder, "-list") === false)
        {
            $timings = array();
            for ($i = 1; $i <= $_SESSION["e_{$this->eventid}"]['rc_numfleets']; $i++)
            {
                $timings[$i] = array_values(array_filter($rs, function ($ar) use ($i){ return ($ar['fleet'] == $i); }));
            }
        }
        else
        {
            $timings = $rs;
        }
        
        return $timings;
    }
    
    
    private function race_entry_get($fields, $where, $order, $laptimes=false)
    {
        // ignores competitors that are marked as deleted
        if ($laptimes)
        {
            $sql =  "SELECT $fields, (SELECT GROUP_CONCAT(b.etime ORDER BY b.lap ASC SEPARATOR \",\") FROM t_lap as b WHERE b.entryid=a.id and a.eventid = {$this->eventid}
                     GROUP BY b.entryid) AS laptimes
                     FROM t_race as a
                     WHERE a.eventid = {$this->eventid} and status != 'D' $where
                     ORDER BY $order";
        }
        else
        {
            $sql = "SELECT $fields FROM t_race WHERE eventid = {$this->eventid} and status != 'D' $where ORDER BY $order";
        }
        
        //u_writedbg($sql, __CLASS__, __FUNCTION__, __LINE__); // debug:
        $result = $this->db->db_get_rows($sql);
        return $result;
    }
    
    
    public function race_delete($fleetnum=0)
    {
        $constraint = array("eventid"=>$this->eventid);
        if ($fleetnum != 0) { $constraint[] = array("fleet"=>$fleetnum); }
        
        $numrows = $this->db->db_delete("t_race", $constraint);
        
        return $numrows;
    }

    public function race_update($update, $fleetnum=0)
    {
        $constraint = array("eventid"=>$this->eventid);
        if ($fleetnum != 0) { $constraint['fleet'] = $fleetnum; }

        $result = $this->db->db_update("t_race", $update, $constraint);

        return $result;
    }
    
    
    public function race_laps_delete($fleetnum=0)
    {
        $constraint = array("eventid"=>$this->eventid);
        if ($fleetnum != 0) { $constraint[] = array("race"=>$fleetnum); } 
        
        $numrows = $this->db->db_delete("t_lap", $constraint);
        
        return $numrows;
    }


    public function race_finish_delete()
    {
        $constraint = array("eventid"=>$this->eventid);
        $numrows = $this->db->db_delete("t_finish", $constraint);

        return $numrows;
    }
    
    public function race_laps_set($mode, $fleetnum, $scoring, $laps)
    {
        // sets laps for fleet if a change is valid

        // can't change laps if:
        //   - a pursuit race
        //   - race has started and leading boat has already finished
        //   - race has started and requested lap is less than current leaders lap
        //   - requested lap is already set

        $change_lap = false;  // default is for laps not to be changed
        $update = array("result" =>"", "finishlap" => 0, "currentlap" => 0 );
        $fleet_data = $_SESSION["e_{$this->eventid}"]["fl_$fleetnum"];
        $current_lap = $this->race_laps_current($fleetnum);  // current leader lap for this fleet

        if ($fleet_data['scoring'] == "pursuit")  // pursuit race
        {
            $update = array("result" =>"pursuit_race", "finishlap" => $fleet_data['maxlap'], "currentlap" => $current_lap );
        }
        elseif ($fleet_data['entries'] <= 0)      // no entries in t_race
        {
            $_SESSION["e_{$this->eventid}"]["fl_$fleetnum"]['maxlap'] = $laps;
            $update = array("result" =>"ok", "finishlap" => $laps, "currentlap" => $current_lap );
            $upd = $this->racestate_update(array('maxlap'=>$laps), array("fleet"=>$fleetnum));
        }
        else
        {
            if ($fleet_data['status'] == "notstarted")   // race not started - so laps can be changed
            {
                //u_writedbg("fleet not started", __CLASS__, __FUNCTION__, __LINE__);

                $change_lap = true;
            }
            else  // race sequence started - check if laps can be changed
            {
                if ($fleet_data['status'] == "finishing" or $fleet_data['status'] == "allfinished")      // some boats aleady fininshed - laps cannot change
                {
                    //u_writedbg("fleet finishing", __CLASS__, __FUNCTION__, __LINE__);
                    // lap can't be changed - boats are already finishing in this fleet
                    $update = array("result" =>"finishing", "finishlap" => $fleet_data['maxlap'], "currentlap" => $current_lap );
                }
                else                                                                                     // no finishers yet - laps could change
                {
                    if ($laps < $current_lap)
                    {
                        //u_writedbg("laps requested less than current", __CLASS__, __FUNCTION__, __LINE__);
                        // lap can't be changed - lap requested is less than current leaders lap
                        $update = array("result" =>"less_than_current", "finishlap" => $fleet_data['maxlap'], "currentlap" => $current_lap );
                    }
                    elseif ($laps == $fleet_data['maxlap'])
                    {
                        //u_writedbg("laps requested is same as current", __CLASS__, __FUNCTION__, __LINE__);
                        // no change requested
                        $update = array("result" =>"already_set", "finishlap" => $fleet_data['maxlap'], "currentlap" => $current_lap );
                    }
                    else
                    {
                        //u_writedbg("lap change is permitted", __CLASS__, __FUNCTION__, __LINE__);
                        // lap change is permitted
                        $change_lap = true;
                    }
                }
            }
        }

        //u_writedbg("changing to $change_lap", __CLASS__, __FUNCTION__, __LINE__);
        if ($change_lap)
        {
            if ($mode == "set")
            {
                $upd_race = $this->race_update(array("finishlap"=>$laps), $fleetnum);
                //u_writedbg("updating t_race for fleet $fleetnum: |laps: $laps |result: $upd_race|", __CLASS__, __FUNCTION__, __LINE__);
            }
            else      // mode must be shorten
            {
                if ($scoring == "average")        // need to set each lap individually - to be boat's current lap + 1
                {
                    // get race data for each boat in fleet
                    $boats = $this->race_getstarters(array("fleet"=>$fleetnum));
                    foreach ($boats as $boat)
                    {
                        // update record to finish on next lap
                        $new_finish_lap = $boat['lap'] + 1;
                        $upd_race =  $this->entry_update($boat['id'], array("finishlap"=>$new_finish_lap));
                    }
                }
                else                             // fleet or normal hanicap race - can set the same finish lap for everyone
                {
                    $upd_race = $this->race_update(array("finishlap"=>$laps), $fleetnum);
                    //u_writedbg("updating t_race for fleet $fleetnum: |laps: $laps |result: $upd_race|", __CLASS__, __FUNCTION__, __LINE__);
                }
            }

            if ($upd_race >= 0)
            {
                $update = array("result" => "ok", "finishlap" => $laps, "currentlap" => $current_lap);
            }
            else
            {
                $update = array("result" => "failed", "finishlap" => $fleet_data['maxlap'], "currentlap" => $current_lap);
            }
            //u_writedbg("fleet $fleetnum: |result: {$update['result']} |flap: {$update['finishlap']}|clap: {$update['currentlap']}", __CLASS__, __FUNCTION__, __LINE__);
        }

        return $update;
    }

//    public function race_getlap_etime($data, $switch_lap)
//    {
//        $lap = $this->entry_lap_get($data['id'], "lap", $switch_lap);
//        if ($lap)
//        {
//            $lapdata = array("etime" => $lap['etime'], "ctime" => $lap['ctime'], "clicktime" => $lap['clicktime']);
//        }
//        else
//        {
//            $lapdata = array("etime" => 0, "ctime" => 0, "clicktime" => 0);
//        }
//
//        return $lapdata;
//    }
    
    public function race_laps_current($fleetnum)
    {
        $sql = "SELECT MAX(lap) AS maxlaps FROM `t_race` WHERE eventid= {$this->eventid} and fleet=$fleetnum GROUP BY fleet";
        //u_writedbg("db_update: query: $sql ",__FILE__,__FUNCTION__,__LINE__);
        $result = $this->db->db_get_row($sql);
        return $result['maxlaps'];        
    }

    
    public function race_times_init($fleetnum=0)
    {
        $constraint = array("eventid"=>$this->eventid);
        if ($fleetnum != 0)
        {
            $constraint[] = array("fleet"=>$fleetnum);
        }
        
        $time_update = array( "clicktime" => "null", "lap" => 0, "etime" => 0,
                              "ctime" => 0, "atime" => 0, "ptime" => 0, "penalty" => 0, "points"  => 0
        );

        $numrows = $this->db->db_update("t_race", $time_update, $constraint);             // update timing info for all entries
        $this->race_laps_delete($fleetnum);                                               // remove all lap times
        $this->race_finish_delete();                                                      // remove all finish line data for pursuit race

        return $numrows;
    }

    
    
    public function race_codes_init($excludes, $fleetnum=0)
    {
        $where = "eventid = ".$this->eventid;
        if ($fleetnum != 0) { $where.= " AND race = $fleetnum"; }
        
        if (!empty($excludes))
        {
            foreach ($excludes as $exclude)
            {
                $where.= " and code!='$exclude' ";
            } 
        }
 
        $sql = "UPDATE t_race SET code='' WHERE $where ";
        $result = $this->db->db_query($sql);
        
        return $result;    
    }
    
    
    public function race_entry_counts()
    {
        $sql = "SELECT fleet, count(*) as numentries FROM t_race WHERE eventid = '{$this->eventid}' GROUP BY fleet ORDER BY fleet";
        //echo "<pre>$sql</pre>";
        $entrycount = $this->db->db_query($sql);
        
        $count[0] = 0;  // holds total entry count
        foreach ($entrycount as $row)
        {
            $count[$row['fleet']] = $row['numentries'];
            $count[0] = $count[0] + $count[$row['fleet']];
        }
        
        //echo "<pre>".print_r($count,true)."</pre>";
        
        return $count;
    }

    public function fleet_race_stillracing($fleetnum)
    {
        $sql = "SELECT id, class, sailnum, lap, code, status FROM t_race WHERE eventid = '{$this->eventid}' and fleet = '$fleetnum' AND status != 'D'";
        $rs = $this->db->db_get_rows($sql);

        $still_racing = 0;
        foreach ($rs as $comp)
        {
            if ($comp['status'] == "R")
            {
                if (!empty($comp['code']))
                {
                    $code_arr = $this->db->db_getresultcode($comp['code']);
                    if ($code_arr['scoringtype'] == "penalty")
                    {
                        $still_racing++;
                    }
                }
                else
                {
                    $still_racing++;
                }
            }
        }

        $still_racing == 0 ? $stillracing = false : $stillracing = true;

        return $stillracing;
    }

    public function boat_stillracing($entryid, $code_arr)
    {

        $sql = "SELECT id, class, sailnum, lap, code, status FROM t_race WHERE eventid = '{$this->eventid}' and id = '$entryid'";
        $comp = $this->db->db_get_row($sql);

        $still_racing = false;
        if ($comp['status'] == "R")
        {
            if (!empty($code_arr))
            {
                if ($code_arr['scoringtype'] == "penalty")
                {
                    $still_racing = true;
                }
            }
            else
            {
                $still_racing = true;
            }
        }

        return $still_racing;
    }


    public function fleet_score($eventid, $fleetnum, $scoring, $rs_data)
    {
        // FIXME issues:  this won't work for pursuit - that needs to be done by finish pursuit
        // FIXME issues: how do I handle declarations (not here - in display + button to mark all non-decl as rtd)
        // FIXME issues: why are $eventid and $fleetnum arguments

        $warnings = array();

        if ($rs_data)
        {
            $race_entries = $this->get_race_entries($rs_data);                                     // get number of entries in this fleet
            $maxscore = $this->resultcode_points($_SESSION['resultcodes']['DNF'], $race_entries);  // get points for DNF (max score)
            $maxlap = max(array_column($rs_data, 'lap'));                                          // get max no. of laps

            // error_log("entries: $race_entries - maxscore: $maxscore - maxlap: $maxlap\n",3, $_SESSION['dbg_file']);

            $still_racing = 0;
            $lap_arr    = array();  // sorting array for laps
            $atime_arr  = array();  // sorting array for corrected time
            $points_arr = array();  // sorting array for points

            foreach ($rs_data as $k => $row)
            {
                // error_log("boat $k: {$rs_data[$k]['class']} {$rs_data[$k]['sailnum']}\n",3, $_SESSION['dbg_file']);

                $boat = $row['class']." ".$row['sailnum'];
                empty($row['code']) ? $code_arr = array() : $code_arr = $_SESSION['resultcodes'][$row['code']]; // set code details

                // check if still racing
                if ($this->boat_stillracing($row['id'], $code_arr))
                {
                    $still_racing++;
                    $rs_data[$k]['stillracing'] = "Y";
                }
                else
                {
                    $rs_data[$k]['stillracing'] = "N";
                }

                if ($row['status'] == "F")                      // finished - check if correct no. of laps
                {
                    if ($scoring == "level" OR $scoring == "handicap")
                    {
                        if ($row['lap'] != $maxlap and (empty($row['code']) or $code_arr['scoringtype'] == "penalty")) {
                            $warnings[] = array("type"=>"warning", "msg"=>"$boat has not completed all laps");
                        }
                    }
                }

                // get corrected time and aggregate time
                $rs_data[$k]['ctime'] = $this->entry_calc_ct($row['etime'], $row['pn'], $scoring);
                if ($scoring == "average")
                {
                    $rs_data[$k]['atime'] = $this->entry_calc_at($row['etime'], $row['pn'], $scoring, $row['lap'], $maxlap);
                }
                else
                {
                    $rs_data[$k]['atime'] = $row['etime'];
                }

                // set initial points to 0 unless it has a non-penalty scoring code (e.g. DNF, OCS, NCS)
                empty($row['code']) ? $code_arr = array() : $code_arr = $_SESSION['resultcodes'][$row['code']];
                if (!empty($code_arr))
                {
                    if ($code_arr['scoringtype'] == "penalty")    // this is a series or penalty code - don't process yet
                    {
                        $rs_data[$k]['points'] = 0;
                        //error_log("penalty code {$row['code']} - points {$rs_data[$k]['points']}\n",3, $_SESSION['dbg_file']);
                    }
                    elseif ($code_arr['scoringtype'] == "series")
                    {
                        $rs_data[$k]['points'] = 999;
                    }
                    elseif ($code_arr['scoringtype'] == "race")   // this is a race code
                    {
                        $rs_data[$k]['points'] = $this->resultcode_points($code_arr, $race_entries);
                        //error_log("code {$row['code']} - points {$rs_data[$k]['points']}\n",3, $_SESSION['dbg_file']);
                    }
                    else                                          // this is not a valid code for a race
                    {
                        $rs_data[$k]['points'] = 0;
                        $warnings[] = array("type"=>"warning", "msg"=>"$boat has an invalid result code");
                        //error_log("invalid code {$row['code']} \n",3, $_SESSION['dbg_file']);
                    }
                }
                else  // no code
                {
                    $rs_data[$k]['points'] = 0;
                    //error_log("no code - points {$rs_data[$k]['points']}\n",3, $_SESSION['dbg_file']);
                }

                $atime_arr[]  = $rs_data[$k]['atime'];        // arrays for sorting results
                $points_arr[] = $rs_data[$k]['points'];
            }


            if ($still_racing > 0)  // set warning if boats still racing
            {
                $warnings[] = array("type"=>"danger", "msg"=>"there are $still_racing boat(s) still racing in this fleet");
            }

            // sort array on points then aggregate time
            //echo "<pre>FIRST SORT ".print_r($points_arr,true)."</pre>";
            array_multisort($points_arr, SORT_ASC, $atime_arr, SORT_ASC, $rs_data);

            // end dbg chk
            //foreach ($rs_data as $r){error_log("PRE CHK: {$r['class']} {$r['sailnum']} {$r['atime']} {$r['penalty']} {$r['code']} {$r['points']} \n",3, $_SESSION['dbg_file']);}

            // loop over sorted array setting position and points - including handling ties
            $pos = 0;
            $atime = 0;
            $prevpos = 0;
            $tie = 0;
            $sum = 0;
            $points_arr = array();
            foreach ($rs_data as $k => $row)
            {
                //error_log("PROCESSING $k: {$row['class']} {$row['sailnum']} {$row['atime']} {$row['code']} {$row['points']} \n",3, $_SESSION['dbglog']);
                if ($row['status'] != "R")
                {
                    if ($row['points'] == 0)
                    {
                        empty($row['code']) ? $code_arr = array() : $code_arr = $_SESSION['resultcodes'][$row['code']];

                        // apply points - checking for ties
                        if ($row['atime'] != $atime)           // not a tie
                        {
                            //error_log("- not a tie [$tie] \n",3, $_SESSION['dbglog']);
                            if ($tie > 0)                      // end of tie - reset allocated points to tie points
                            {
                                $tie++;
                                $score = round(($sum + $prevpos) / $tie, 1);
                                for ($i = $tie; $i > 0; $i--) {
                                    //error_log("- allocating tie points ($score) to {$row['class']} {$row['sailnum']} \n",3, $_SESSION['dbglog']);
                                    $rs_data[$k - $i]['points'] = $score;
                                }
                                $tie = 0;                      // reset tie counts
                                $sum = 0;
                            }
                            $pos++;
                            //error_log("- allocating points ($pos) to id [$k] \n",3, $_SESSION['dbglog']);
                            $rs_data[$k]['points'] = $pos;
                        }
                        else                                   // is a tie - record and move on
                        {
                            //error_log("- a tie \n",3, $_SESSION['dbglog']);
                            $tie++;
                            $pos++;
                            $sum = $sum + $prevpos;
                        }
                        $prevpos = $pos;
                        $atime = $row['atime'];

                        // add any penalties applied
                        if (!empty($code_arr) and $code_arr['scoringtype'] == "penalty") {
                            //error_log("- checking penalties for id [$k] \n",3, $_SESSION['dbglog']);
                            $rs_data[$k]['penalty'] = $this->penaltycode_points($code_arr, $race_entries, $rs_data[$k]['penalty']);
                            if ($rs_data[$k]['penalty'] > 0) {
                                $rs_data[$k]['points'] = $rs_data[$k]['points'] + $rs_data[$k]['penalty'];
                                if ($rs_data[$k]['points'] > $maxscore) { $rs_data[$k]['points'] = $maxscore; }
                            }
                        }
                    }
                    //error_log("points for $k: {$rs_data[$k]['points']} \n",3, $_SESSION['dbglog']);
//                    $points_arr[] = $rs_data[$k]['points'];  // sort array for points
//                    $status_arr[] = $rs_data[$k]['status'];  // sort array for status
                }
                else
                {
//                    $points_arr[] = $rs_data[$k]['points'];  // sort array for points
//                    $status_arr[] = $rs_data[$k]['status'];  // sort array for status
                }
                $points_arr[] = $rs_data[$k]['points'];  // sort array for points
                $status_arr[] = $rs_data[$k]['status'];  // sort array for status
                $pn_arr[] = $rs_data[$k]['pn']; // sort array for PN
                $sailnum_arr[] = $rs_data[$k]['sailnum']; // sort array for sailnumber
            }

            array_multisort($status_arr, SORT_ASC, $points_arr, SORT_ASC, $pn_arr, SORT_ASC, $sailnum_arr, SORT_NUMERIC, $rs_data);
        }

        // end dbg chk
        //foreach ($rs_data as $r) { error_log("END CHK: {$r['class']} {$r['sailnum']} {$r['atime']} {$r['penalty']} {$r['code']} {$r['points']} \n",3, $_SESSION['dbg_file']);}

        $fleet_rs['warning'] = $warnings;
        $fleet_rs['data']    = $rs_data;
        return $fleet_rs;
    }

    public function race_score($eventid, $fleetnum, $racetype, $rs_data, $table = "t_race")
    {
        // FIXME issues:  this won't work for pursuit - that needs to be done by finish pursuit
        // FIXME issues: how do I handle declarations (not here - in display + button to mark all non-decl as rtd)
        // FIXME issues: why are $eventid and $fleetnum arguments

        $fleet_rs = $this->fleet_score($eventid, $fleetnum, $racetype, $rs_data);
        //echo "<pre>FLEET SCORE".print_r($fleet_rs,true)."</pre>";

        $rs_data  = $fleet_rs['data'];

        if ($table == "t_race")
        {
            foreach($rs_data as $k => $row)
            {
                $update_arr = array(                               // update t_race record
                    "ctime"   => $row['ctime'],
                    "atime"   => $row['atime'],
                    "penalty" => $row['penalty'],
                    "points"  => $row['points']
                );
                $update = $this->entry_update($row['id'], $update_arr);

                $rs_row = array(                                  // for return data
                    "entryid"    => $row['id'],
                    "fleet"      => $row['fleet'],
                    "class"      => $row['class'],
                    "sailnum"    => $row['sailnum'],
                    "boat"       => $row['class']." ".$rs_data[$k]['sailnum'],
                    "helm"       => $row['helm'],
                    "crew"       => $row['crew'],
                    "competitor" => rtrim($row['helm'] . "/" . $row['crew'], "/ "),
                    "club"       => $row['club'],
                    "pn"         => $row['pn'],
                    "lap"        => $row['lap'],
                    "finishlap"  => $row['finishlap'],
                    "et"         => $row['etime'],
                    "ct"         => $row['atime'],
                    "code"       => $row['code'],
                    "points"     => $row['points'],
                    "penalty"    => $row['penalty'],
                    "note"       => $row['note'],
                    "status"     => $row['status'],
                    "declaration"=> $row['declaration'],
                    "laptimes"   => $row['laptimes'],
                    "stillracing"=> $row['stillracing'],
                    "status_flag"=> $this->entry_resultstatus($row['status'], $row['code'], $row['declaration'], $row['protest'], $row['stillracing'], $this->eventid)
                );

                $fleet_rs['data'][$k] = $rs_row;
            }

        }
        elseif ($table == "t_result")
        {
            foreach($rs_data as $k => $row)
            {

                // update t_result record
                $rs_row = array(
                    "ctime"   => $rs_data[$k]['ctime'],
                    "atime"   => $rs_data[$k]['atime'],
                    "penalty" => $rs_data[$k]['penalty'],
                    "points"  => $rs_data[$k]['points']
                );

                $numrows = $this->db->db_update("t_result", $rs_row, array("id"=>$rs_data[$k]['id']));
                //error_log("UPD: {$row['class']} {$row['sailnum']} - $numrows \n",3, $_SESSION['dbg_file']);
            }
        }

        return $fleet_rs;
    }

/**
 *   ----- ENTRY methods ---------------------------------------------------- 
 *     entry_add             - adds entry to event 
 *     entry_get             - gets entry record in event by id or competitor id       
 *     entry_delete          - deletes entry in event
 *     entry_duty_set        - sets an entry as doing a duty
 *     entry_duty_unset      - unsets duty status for an entry
 *     entry_code_set        - sets code (and status) for an entry
 *     entry_code_unset      - removes code from an entry
 *     entry_update          - updates an entry 
 *     entry_declare         - handles result of declaration/retirement/protest   
 *     entry_declaration_set - sets declaration code for entry 
 *     entry_resultsstatus   - gets results display status for an entry
 *     entry_time            - times an entry at a lap or the finish
 *     entry_time_undo       - removes the last lap/finish timing
 * 
 *     entry_lap_get         - retrieves one or all lap records for an entry
 *     entry_lap_add         - adds a lap record for an entry
 *     entry_lap_delete      - deletes one or all lap records for a fleet or an entry
 *     entry_lap_update      - updates a lap record
 *     

*/

// FIXME - OBSOLETE this is not used and is very similar to entry->set_entry
//    public function entry_add($comp, $change)  // enter|replace|delete
//    /*
//    loads competitor into t_race - allows standard values for sail number and crew to be overwritten
//    FIXME - need to be able to deal with salcombe issue of changing helm as well
//
//    */
//    {
//        if (!empty($comp))
//        {
//            $result = array(
//               "class"   => $comp['classname'],
//               "sailnum" => $comp['sailnum'],
//               "helm"    => $comp['helmname']
//            );
//
//            if ($comp['eligible'])
//            {
//                // create race record
//                $fields['eventid']      = $this->eventid;
//                $fields['start']        = $comp['start'];
//                $fields['fleet']        = $comp['fleet'];
//                $fields['competitorid'] = $comp['id'];
//                $fields['helm']         = $comp['helmname'];
//                $fields['crew']         = $comp['crewname'];
//                $fields['club']         = $comp['club'];
//                $fields['class']        = $comp['classname'];
//                $fields['classcode']    = $comp['acronym'];
//                $fields['sailnum']      = $comp['sailnum'];
//                $fields['pn']           = r_getcompetitorpn($this->eventid, $comp['fleet'], $comp['personal_py'], $comp['nat_py'], $comp['local_py']);
//                $fields['status']       = "R";
//
//                if (!empty($change))
//                {
//                    foreach ($change as $key=>$mod)
//                    {
//                        $fields[$key] = $mod;
//                    }
//                }
//
//                $exists = $this->entry_get($fields['competitorid'], "competitor");         // does entry exist for this event
//                if ($exists) { $delete = $this->entry_delete($fields['competitorid']); }   // delete it
//
//                $insert      = $this->db->db_insert("t_race", $fields);                   // insert record
//                $raceentryid = $this->db->db_lastid();
//
//                $result['status']  = "entered";                                           // set result status
//                $result['raceid']  = $raceentryid;
//
//                $this->racestate_updateentries($fields['fleet'], "+1");                    // updated entry count
//
//                u_writelog("race entry: {$result['class']} {$result['sailnum']}", $this->eventid);
//            }
//            else    //  competitor is not eligible
//            {
//                $result['status']  = "competitor not eligible";
//
//                u_writelog("race entry FAILED: {$result['class']} {$result['sailnum']} - {$result['status']}", $this->eventid);
//            }
//        }
//        else    // competitor not known
//        {
//            $result['class']   = "unknown";
//            $result['sailnum'] = "";
//            $result['helm']    = "";
//            $result['status']   = "competitor not registered";
//
//            u_writelog("race entry FAILED: {$result['class']} {$result['sailnum']} - {$result['status']}", $this->eventid);
//        }
//
//        return $result;
//    }
    
    
    public function entry_get($id, $idtype="race")
    {
        $where = " eventid = {$this->eventid} ";
        if ($idtype == "competitor")
        {
           $where.= " and competitorid = $id ";
        }
        else
        {
           $where.= " and id = $id ";
        }
        $sql = "SELECT * FROM t_race WHERE $where";
        //u_writedbg($sql,__FILE__,__FUNCTION__,__LINE__); //debug:
        
        $entry = $this->db->db_get_row($sql);
        
        return $entry;
    }
    
    public function entry_get_timings($id)
    {
        $sql = "SELECT id, fleet, start, class, sailnum, helm, crew, club, pn, clicktime, lap, finishlap, etime, code, status, penalty, note,
                (SELECT GROUP_CONCAT(b.etime ORDER BY b.lap ASC SEPARATOR \",\")
                FROM t_lap as b
                WHERE b.entryid=a.id and a.eventid = {$this->eventid}
                GROUP BY b.entryid) AS laptimes
                FROM t_race as a
                WHERE a.eventid = {$this->eventid} AND a.id = $id";
        //u_writedbg($sql,__FILE__,__FUNCTION__,__LINE__); //debug:
        
        $entry = $this->db->db_get_row($sql);
        
        return $entry;
    }

//    public function lapstr_toarray($lap_str)
//    {
//        // convert lap times string to an array with an index starting at 1
//        if (empty($lap_str))
//        {
//            $laptimes = false;
//        }
//        else
//        {
//            $laptimes = explode(",", $lap_str);
//            array_unshift($laptimes, null);
//            unset($laptimes[0]);
//        }
//        //echo "<pre>".print_r($laptimes,true)."</pre>";
//        return $laptimes;
//    }
    
    
    public function entry_delete($entryid)
    {    
        $status = true;
        $fields = $this->entry_get($entryid);
        $this->db->db_delete("t_finish", array("entryid"=>$entryid));        // delete pursuit finish records
        $this->db->db_delete("t_lap", array("entryid"=>$entryid));           // delete lap records
        
        $num_rows = $this->db->db_delete("t_race", array("id"=>$entryid));    // delete race record
        if (!$num_rows)
        { 
            $status = false; 
        }
        else
        {               
            $this->racestate_updateentries($fields['fleet'], "-1");           // update racestate entry count
        }

        return $status;
    }

     
     public function entry_duty_set($entryid, $status)
     {
        if ($status == "R")  { $status = "X"; }
        $numrows = $this->entry_update($entryid, array("code" => "DUT", "status" => $status));
        return $numrows;
     }
     
     public function entry_duty_unset($entryid, $status)
     {
        if ($status == "X")  { $status = "R"; }
        $numrows = $this->entry_update($entryid, array("code" => "", "status" => $status));
        return $numrows;
     }
     
     
     public function entry_code_set($entryid, $code, $finish_check = false)
     {
        $status = true;
        if (empty($entryid))
        {
            $status = -1;               // no entry_id
        }
        else
        {
            $code_arr = $this->db->db_getresultcode($code); // get timing flag for code

            if (!$code_arr)
            {
                $status = -2;                              // code specified not found
            }
            else
            {

                if ($code_arr['scoringtype'] == "series")                          // boat is 'excluded' with placeholder points
                {
                    $numrows = $this->entry_update($entryid, array("code" => $code, "points" => "999", "status" => "X"));
                }
                elseif ($code_arr['scoringtype'] == "race")                        // just set excluded
                {
                    $numrows = $this->entry_update($entryid, array("code" => $code, "status" => "X"));
                }
                else                                                               // just set code and whether boat is racing or finished
                {
                    $finish_check ? $state = "F" : $state = "R";
                    $numrows = $this->entry_update($entryid, array("code" => $code, "status" => $state));
                }

                if ($numrows<=0 ) {$status = -3; }         // database not updated";
            }
        }
        return $status;
     }

     
     public function entry_code_unset($entryid, $racestatus, $declaration, $finish_check = false)
     {
         $status = true;

         // change racing status to either racing or finished
         $finish_check ? $status = "F" : $status = "R";

         // change declaration back to none if previously retired
         if ($declaration == "R") { $declaration = "X"; }

         $numrows = $this->entry_update($entryid, array("code" => "", "status" => $status, "points" => "0", "declaration" => $declaration));

         if ($numrows<=0 ) {$status = -3; }         // database not updated";

         return $status;
     }
     
     
     public function entry_update($entryid, $update)
     {
         $numrows = $this->db->db_update("t_race", $update, array("id"=>$entryid));

         return $numrows;
     }


//    public function entry_declare($entryid, $declare_type)
//    {
//        $status = true;
////        $entry = $this->entry_get($competitorid, "competitor");
////        $entry_ref = "{$entry['class']} {$entry['sailnum']} {$entry['helm']}";
//
//        if ($declare_type == "declare")
//        {
//            $protest ? $declare_code = "DP" : $declare_code = "D";
//            // Note: not processing declarations
//        }
//        elseif ($declare_type == "retire")
//        {
//            $protest ? $declare_code = "RP" : $declare_code = "R";
//
//            // update code and status
//            $upd = $this->entry_code_set($entryid, "RET");
//            //$logmsg = "Retirement ";
//            echo "<pre>entry_code_set: $upd</pre>";
//            if ($upd != "code_set")
//            {
//                $status = false;
//            }
//        }
//        else
//        {
//            $status = false;
//        }
//
//        if ($status)
//        {
//            echo "<pre>entry_declaration_set: $declare_code</pre>";
//
//            $declare = $this->entry_declaration_set($entry['id'], $declare_code);
//            // update declaration code
//            if ($this->entry_declaration_set($entry['id'], $declare_code))
//            {
//                u_writelog("$logmsg: $entry_ref", $this->eventid);
//                $status = true;
//            }
//            else
//            {
//                u_writelog("$logmsg FAILED - (competitor: $competitorid) $entry_ref ", $this->eventid);
//                $status = false;
//            }
//        }
//
//        return $status;
//    }
//
//    public function entry_declaration_set($entryid, $declare_code)
//    {
//        $numrows = $this->entry_update($entryid, array("declaration" => $declare_code,));
//        return $numrows;
//    }

    public function entry_resultstatus($status, $code, $declaration, $protest, $stillracing, $eventid)
    {
        $decl    = "";

        if ($status == 'R' and $stillracing == "Y")          // racing
        {
            $status_arr = array("msg"=>"still racing", "color"=>"orange", "glyph"=>"glyphicon glyphicon-flag");
        }
        elseif ($status == 'R' and $stillracing == "N")
        {
            $status_arr = array("msg"=>"finished $code", "color"=>"black", "glyph"=>"glyphicon glyphicon-flag");
        }
        elseif ($status == 'F')      // finished
        {
            $status_arr = array("msg"=>"finished", "color"=>"mediumseagreen", "glyph"=>"glyphicon glyphicon-flag");
        }
        elseif ($status == 'X')      // non-finisher
        {
            $status_arr = array("msg"=>"non-finisher", "color"=>"black", "glyph"=>"glyphicon glyphicon-flag");
        }
        else
        {
            $status_arr = array("msg"=>"unknown", "color"=>"red", "glyph"=>"glyphicon glyphicon-flag");
        }

        if ($_SESSION["e_$eventid"]['ev_entry'] == "signon-retire" OR $_SESSION["e_$eventid"]['ev_entry'] == "signon-declare")
        {
            if (strpos($declaration,'R') !== false)     // retired
            {
                $decl_arr = array("msg"=>"retired", "color"=>"red", "glyph"=>"glyphicon glyphicon-pencil");
            }
        }

        if ($_SESSION["e_$eventid"]['ev_entry'] == "signon-declare" )
        {
            if (strpos($declaration, 'D') !== false)    // signed off
            {
                $decl_arr = array("msg" => "signed off", "color" => "mediumseagreen", "glyph" => "glyphicon glyphicon-pencil");
            }
        }

        if ($_SESSION['sailor_protest'])
        {
            if ($protest)  // protest submitted
            {
                $protest_arr = array("msg"=>"protesting", "color"=>"red", "glyph"=>"glyphicon glyphicon-certificate");
            }
        }

        // add tooltip if required
        if ($_SESSION["display_help"])
        {
            $status_bufr = "<span class='{$status_arr['glyph']}' style='color: {$status_arr['color']}; cursor: help' data-title='{$status_arr['msg']}' 
                            data-toggle='tooltip' data-delay='500' data-placement='bottom'></span>";
            if (!empty($decl_arr))
            {
                $status_bufr.= "&nbsp;<span class='{$decl_arr['glyph']}' style='color: {$decl_arr['color']}; cursor: help' data-title='{$decl_arr['msg']}' 
                                            data-toggle='tooltip' data-delay='500' data-placement='bottom'></span>";
            }
            if (!empty($protest_arr))
            {
                $status_bufr.= "&nbsp;<span class='{$protest_arr['glyph']}' style='color: {$protest_arr['color']}; cursor: help' data-title='{$protest_arr['msg']}' 
                                      data-toggle='tooltip' data-delay='500' data-placement='bottom'></span>";
            }
        }
        else
        {
            $status_bufr = "<span class='{$status_arr['glyph']}' style='color: {$status_arr['color']}; cursor: help'></span>";
            if (!empty($decl_arr))
            {
                $status_bufr.= "&nbsp;<span class='{$decl_arr['glyph']}' style='color: {$decl_arr['color']}; cursor: help'></span>";
            }
            if (!empty($protest_arr))
            {
                $status_bufr.= "&nbsp;<span class='{$protest_arr['glyph']}' style='color: {$protest_arr['color']}; cursor: help'></span>";
            }
        }

        return $status_bufr;
    }

   
    public function entry_time($entryid, $fleetnum, $currentlap, $pn, $clicktime, $status, $prev_et = 0, $force_finish = false)
    {
                            
        $event      = "e_".$this->eventid;                                                        // set fleet number
        $lap        = $currentlap + 1;                                                            // increment lap
        $racestatus = $_SESSION["$event"]["fl_$fleetnum"]['status'];
        $maxlap     = $_SESSION["$event"]["fl_$fleetnum"]['maxlap'];

        $et = $this->entry_calc_et($clicktime, $_SESSION["$event"]["fl_$fleetnum"]['starttime']);  // elapsed time
        $pt = $this->entry_calc_pt($et, $prev_et, $lap);                                           // predicted time for next lap
        if ($this->scoring["$fleetnum"] == "level" or $this->scoring["$fleetnum"] == "pursuit")
        {
            $ct = $et;                                                                             // corrected time = elapsed time
        }
        else
        {
            $ct = $this->entry_calc_ct($et, $pn, $this->scoring["$fleetnum"]);                     // corrected time
        }

        // set array for t_race update
        $update_race = array( "lap" => $lap, "clicktime" => $clicktime, "etime" => $et, "ctime" => $ct, "atime" => "", "ptime" => $pt);


        if ($force_finish)                                                                 // force finish by OOD
        {
            if ($status == "R") { $update_race['status'] = "F"; }
            $return = "force_finish";
            //u_writedbg("- force finish by OOD", __FILE__, __FUNCTION__, __LINE__);
        }
        elseif ($this->scoring["$fleetnum"] == 'average' AND $racestatus=="finishing")     // average lap race and we have already started finishing
        {
            if ($status == "R") { $update_race['status'] = "F"; }
            $return = "finish";
            //u_writedbg("- avg lap race but not first finisher - so finish anyway", __FILE__, __FUNCTION__, __LINE__);
        }
        elseif ($lap >= $maxlap)                                                           //  finish because boat has reached required number of laps
        {
            if ($status == "R") { $update_race['status'] = "F"; }
            if ($this->scoring["$fleetnum"] == 'average' AND $racestatus != "finishing" AND $update_race['status'] == "F")   // this is first finisher in average lap race
            {                
                $return = "first_finish";
                //u_writedbg("- avg lap race - first finisher", __FILE__, __FUNCTION__, __LINE__);
            }
            else                                                                           // normal finish
            {
                $return = "finish";
                //u_writedbg("- boat has completed final lap", __FILE__, __FUNCTION__, __LINE__);
            }
        }
        else  // not finishing this lap - don't change status (can either be R or X)
        {
            $return = "time";
            //u_writedbg("- not a finisher", __FILE__, __FUNCTION__, __LINE__);
        }
        
        // update t_race record
//        $update_race = array( "lap" => $lap, "clicktime" => $clicktime, "etime" => $et, "ctime" => $ct,
//                              "atime" => "", "ptime" => $pt, "status" => $status );
        //u_writedbg("<pre> update_race: ".print_r($update_race, true)."</pre>", __FILE__, __FUNCTION__, __LINE__); //debug:
        $numrows = $this->entry_update($entryid, $update_race);
        
        // add record to t_lap
        $add_lap = array( "clicktime" => $clicktime, "lap" => $lap, "etime" => $et,
                          "ctime" => $ct, "status" => 1, );
        //u_writedbg("<pre> add_lap: ".print_r($add_lap, true)."</pre>", __FILE__, __FUNCTION__, __LINE__); //debug:
        $numrows = $this->entry_lap_add($fleetnum, $entryid, $add_lap); 
        
        return $return;
    }
    
    
    public function entry_time_undo($entryid=0)
    {
        /*
        ?? does this work for a force finish ??
         - could be more efficient if I stored a stack with last N boats clicked - useful elsewhere
         - could I use lastclick session variables - but then I woul need to work out the new lastclick - still more efficient in most cases
        */

        $lap_rs = $this->entry_lap_last($entryid);        // if boat not specified as argument - find last boat timed
        if ($lap_rs) {
            $entryid = $lap_rs['entryid'];
            $lap = $lap_rs['lap'];

            $entry_rs = $this->entry_get($entryid, "all");    // get the entry details

            $del = $this->entry_lap_delete($entryid, $lap);   // remove the t_lap record of that timing


            $lap_num = intval($lap) - 1;                      // get the lap details from the new last timing for that entry
            if ($lap_num == 0) {
                // reset entry timings - no laps recorded
                $update = array("lap" => 0, "clicktime" => 0, "etime" => 0, "ctime" => 0, "atime" => 0, "ptime" => 0);
            } else {
                // get previous lap details and use them to update the entry record
                $lap_new = $this->entry_lap_get($entryid, "lap", $lap_num);
                $update = array(
                    "lap"   => $lap_new['lap'],
                    "clicktime" => $lap_new['clicktime'],
                    "etime" => $lap_new['etime'],
                    "ctime" => $lap_new['ctime'],
                    "atime" => 0,
                    "ptime" => $lap_new['etime'] + round($lap_new['etime'] / $lap_new['lap']),
                );
            }

            if ($entry_rs['status'] == "F")                  // reset status if boat has finished
            {
                $update['status'] = "R";
                if ($_SESSION["e_{$this->eventid}"]["fl_{$entry_rs['fleet']}"]['status'] == "finishing") // if finishing - check if any boats finished and reset if not
                {
                    $rs = $this->race_getentries(array("status" => "F"));
                    if ($rs) {
                        $_SESSION["e_{$this->eventid}"]["fl_{$entry_rs['fleet']}"]['status'] = "inprogress";
                    }
                }
            }

            $status = $this->entry_update($lap_rs['entryid'], $update);           // update the details in t_race

            // check to see what lap the leader is on and update the session variable
            $_SESSION["e_{$this->eventid}"]["fl_{$entry_rs['fleet']}"]['currentlap'] = $this->race_laps_current($entry_rs['fleet']);

            if ($status) {
                return $entry_rs;
            } else {
                return false;
            }
        }
        else
        {
            return 0;
        }
    }

    public function entry_laptimes_get($entryid, $field = "etime")
    {
        $laptimes = array();
        $laps = $this->entry_lap_get($entryid, "all");
        foreach ($laps as $lap)
        {
            $laptimes[$lap['lap']]= $lap[$field];
        }
        return $laptimes;
    }
    
    public function entry_lap_get($entryid, $mode, $lap=0)
    {
        if ($mode == "all")
        {
            $sql = "SELECT * FROM t_lap WHERE eventid = $this->eventid AND entryid = $entryid ORDER BY lap ASC";
            $rows = $this->db->db_get_rows($sql);
            return $rows;
        }
        elseif ($mode == "last")
        {
            $sql = "SELECT * FROM t_lap WHERE eventid = $this->eventid AND entryid = $entryid ORDER BY lap DESC LIMIT 1";
            $row = $this->db->db_get_row($sql);
            return $row;
        }
        else
        {
            $sql = "SELECT * FROM t_lap WHERE eventid = $this->eventid AND entryid = $entryid AND lap = $lap";
            $row = $this->db->db_get_row($sql);
            return $row;
        }
    }
    
    
    public function entry_lap_add($fleetnum, $entryid, $lapdetail)
    {
        $lapdetail['eventid'] = $this->eventid;
        $lapdetail['race']    = $fleetnum;
        $lapdetail['entryid'] = $entryid;        

        $numrows = $this->db->db_insert("t_lap", $lapdetail);
        return $numrows;
    }
    
    
    public function entry_lap_delete($entryid, $lap=NULL)
    {
        $where = array(
            "eventid" => $this->eventid,
            "entryid" => $entryid
        );
        if (!is_null($lap)) { $where['lap'] = $lap; }

        return $this->db->db_delete("t_lap", $where);
    }


    public function entry_lap_update($entryid, $fleetnum, $lap, $pn, $update)
    {
        $status = false;

        $where = array(
            "eventid" => $this->eventid,
            "entryid" => $entryid,
            "lap" => $lap
        );

        if (array_key_exists("etime", $update))                             // potentially updating lap time
        {
            $lap_rec = $this->entry_lap_get($entryid, "lap", $lap);         // get existing lap details
            if ($update['etime'] != $lap_rec['etime'])                      // lap time has changed - update ctime and clicktime
            {
                $update['ctime'] = $this->entry_calc_ct($update['etime'], $pn, $this->scoring["$fleetnum"]);
                $update['clicktime'] = $lap_rec['clicktime'] - ($lap_rec['etime'] - $update['etime']);
            }
        }

        $upd = $this->db->db_update("t_lap", $update, $where);
        if ($upd == 1)
        {
            $rst['status'] = true;
            $rst['msg'] = "updated lap $lap with elapsed time of ".gmdate("H:i:s", $update['etime'])."<br>";
            $rst['clicktime'] = $update['clicktime'];
            $rst['ctime'] = $update['ctime'];
        }
        else
        {
            $rst['status'] = false;
            $rst['msg'] = "failed to update lap record details for lap $lap <br>";
        }

        return $rst;
    }


    public function entry_laptime_check($laptimes)
    /*
    checks for problems with the lap time sequence presented as array with lap as index and etime in secs
    */
    {
        $rs = array(
//            "times" => array(),
            "err"   => false,
            "msg"   => "",
        );

        $prev = 0;
        foreach($laptimes as $lap=>$time)
        {

//          $rs['times'][$lap] = strtotime("1970-01-01 $time UTC");
//          $rs['times'][$lap] = $time;
            if ($time == 0)
            {
                $rs['msg'].= "<p><b>lap $lap</b> has an elapsed time of 0 secs</p>";
                $rs['err'] = true;
            }
            if ($lap > 1 and $prev >= $time)
            {
                $rs['msg'].= "<p><b>lap $lap</b> must have an elapsed greater than the previous lap</p>";
                $rs['err'] = true;
            }
            $prev = $time;
        }
        return $rs;
    }


    public function entry_lap_last($entryid = 0)
    { 
        $entryid==0 ? $entry_clause = "" : $entry_clause = "and entryid = $entryid";

        $row = $this->db->db_query("SELECT * FROM t_lap WHERE eventid={$this->eventid} $entry_clause ORDER BY clicktime DESC LIMIT 1 ");
        $lap_rs = $row->fetch_assoc();
        return $lap_rs;
    }


    public function entry_calc_et($time_click, $time_start)
    // calculates elapsed time
    {
        //u_writedbg("ARGS ***  time_click:$time_click|time_start:$time_start<br>", __FILE__, __FUNCTION__, __LINE__);
        return $et = $time_click - $time_start;
    }


    public function entry_calc_ct($et, $pn, $racetype)
    // calculates corrected time
    {
        //u_writedbg("ARGS ***  et:$et|pn:$pn|racetype:$racetype<br>", __FILE__, __FUNCTION__, __LINE__);
        if ($pn > 0 AND $et > 0)
        {
            if ($racetype == "level")
            {
                $ct = $et;
            }
            elseif ($racetype == "handicap" OR $racetype == "average")
            {
                $ct = round(($et * 1000)/$pn);
            }
            elseif ($racetype == "pursuit")
            {
                $ct = 0;
            }
            else
            {
                $ct = 0;
            }
        }
        else
        {
            $ct = 0;
        }
        return $ct;
    }

    public function entry_calc_at($et, $pn, $racetype, $lap, $maxlap)
    // calculates aggregate time
    {
        //u_writedbg("ARGS ***  et:$et|pn:$pn|racetype:$racetype|lap:$lap|maxlap:$maxlap<br>", __FILE__, __FUNCTION__, __LINE__);
        if ($pn > 0 AND $et > 0 AND $lap > 0 and $maxlap > 0)
        {
            if ($racetype == "level")
            {
                $at = $et;
            }
            elseif ($racetype == "handicap" OR $racetype == "average")
            {
                $at = round((($et * 1000)/$pn) * $maxlap/$lap);
            }
            elseif ($racetype == "pursuit")
            {
                $at = 0;
            }
            else
            {
                $at = 0;
            }
        }
        else
        {
            $at = 0;
        }
        return $at;
    }

    public function entry_calc_pt($et, $prev_et, $lap)
    {
        // calculates predicted time for next lap
        if ($et == 0) {
            $pt = 0;
        } else {
            if (!empty($prev_et)) {
                $pt = $et + ($et - $prev_et);
            } else {
                $pt = $et + round($et / $lap);
            }
        }

        return $pt;
    }

    public function get_race_entries($data)
    {
        // gets number of race starters (entries - DNC - DUT)

        $num_entries = count($data);  // num boats entered

        $num_dut = 0;
        $num_dnc = 0;
        foreach($data as $row)
        {
            if ($row['code'] == "DUT") {  $num_dut++; }   // num boats given duty points
            if ($row['code'] == "DNC") {  $num_dnc++; }   // num boats who did not compete
        }

        return $race_entries = $num_entries - $num_dut - $num_dnc;
    }

    public function penaltycode_points($code_arr, $race_entries, $penalty_score=0)
    {
        $penalty = 0;

        if ($code_arr['code'] == "DPI")  // special case when penalty is allocated by OOD
        {
            $penalty = $penalty_score;
        }
        else
        {
            // evaluate penalty score
            $expr = str_replace("N", $race_entries, $code_arr['scoring']);
            $penalty = eval("return $expr;");
        }

        return $penalty;
    }

//    public function seriescode_points($code_arr, $race_entries)
//    {
//        // FIXME still needs to be implemented
//    }


    public function resultcode_points($code_arr, $race_entries)
    {
        // currently only supports replacement of "N" for number in race
        if (empty($code_arr['scoring']))               // no scoring information for this code
        {
            $points = 0;
        }
        elseif(is_numeric($code_arr['scoring']))       // the scoring code is a fixed value
        {
            $points = $code_arr['scoring'];
        }
        else                                           // evaluate scoring expression (currently only handles "N")
        {
            if (strpos($code_arr['scoring'], "N") === false)
            {
                $points = 0;
            }
            else
            {
                $expr = str_replace("N", $race_entries, $code_arr['scoring']);
                $points = round(eval("return $expr;"), 0, PHP_ROUND_HALF_UP);
            }
        }
        return $points;
    }

}

