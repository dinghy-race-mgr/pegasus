<?php
/**
 *  ENTRY class
 *
 *  Handles entry processing - interacts with t_entry, t_competitor, t_race, t_racestate, t_laps and t_finish
 *
 *  METHODS
 *     __construct
 *
 *  T_ENTRY
 *
 *     add_signon     - adds signon for specific event
 *     chk_signon     - check competitor hasn't already signed on
 *
 *     get_by_event   - retrieve entries for specific event
 *     get_by_compid  - retrieve existing entry from t_race using competitor id
 *     get_by_raceid  - retrieve existing entry from t_race using t_race id
 *
 *     delete_signons - deletes all signons in t_entry for specified event
 *     reset_signons  - resets records in t_entry to not entered for specified event
 *     get_signons    - gets unprocessed signons from t_entry table for specified event
 *     count_signons  - counts unprocessed signons in t_entry table for specified event
 *
 *     allocate       - get allocation of entry to start/fleet
 *     set_entry      - inserts an entry into t_race
 *     confirm_entry  - confirms load to t_entry
 *
 * T_RACE
 *     delete           - deletes entry from t_race based on t_race.id (also t_lap, t_finish, t_racestate)
 *     delete_by_compid - deletes entry from t_race based on competitor id (also t_lap, t_finish, t_racestate)
 *     update           - updates entry in t_race
 *     duty_set         - adds duty code to entry in t_race
 *     duty_unset       - removes duty code from entry in t_race
 *     code_set         - adds a race code to entry e.g. OCS
 *     code_unset       - removes a race code from an entry
 *
 * T_COMPETITOR
 *     get_regulars   - get regular competitors from t_competitor
 *     get_previous   - get competitors from previous races today
 *     get_competitor - get competitor
 *     upd_lastrace   - records last entry for competitor
 *
 **/

// FIXME - test add_signon
// FIXME - test chk_signon
// FIXME - doesn't deal with entry having a different helm
// FIXME - fleet allocation doesn't deal with all options

class ENTRY
{
    private $db;

    //Method: construct class object
    public function __construct(DB $db, $eventid, $event_detail)
    {
        $this->db = $db;
        $this->eventid = $eventid;
        $this->fleets = $event_detail['fleetcfg'];
        $this->numfleets = count($this->fleets);
    }


    public function add_signon($competitorid, $allocate, $helm, $crew, $sailnum)
        /*
         * Adds entry record to t_entry
         * $crew and/or $sailnum only set if temporary change required
         */
    {
        $status = "fail";
        if (empty($competitorid) OR !is_numeric($competitorid))  // check we have a competitor id - if not return error
        {
            $status =  "invalid competitor";
        }
        else
        {
            if ($allocate)                                         // - check if the competitor is eligible for this race
            {
                $this->chk_signon($this->eventid, $competitorid) ? $action_type = "update" : $action_type = "enter";

                $fields = array(
                    "action"         => $action_type,
                    "status"         => "N",
                    "eventid"        => $this->eventid,
                    "competitorid"   => $competitorid,
                    "memberid"       => "",               // future use
                    "change_helm"    => $helm,            // only required for temp change
                    "change_crew"    => $crew,            // only required for temp change
                    "change_sailnum" => $sailnum,         // only required for temp change
                );
                $insert_rs = $this->db->db_insert("t_entry", $fields);
                if ($insert_rs) { $status = $action_type; }
            }
            else
            {
                $status = "ineligible";
            }
        }
        return $status;
    }


    public function chk_signon($eventid, $competitorid)
    {
        $status = false;
        $detail = array();
        $query = "SELECT * FROM `t_entry` WHERE eventid = '$eventid' AND competitorid = '$competitorid'";
        $detail = $this->db->db_get_rows( $query );
        if (!empty($detail)) { $status = true; }
        return $status;
    }

    public function get_signon($eventid, $competitorid)
    {
        $detail = array();
        $query = "SELECT * FROM `t_entry` WHERE eventid = '$eventid' AND competitorid = '$competitorid' ORDER BY upddate ASC";
        $detail = $this->db->db_get_rows( $query );
        if (empty($detail))
        {
            return false;
        }
        else
        {
            return $detail;
        }
    }

    public function add_signoff($competitorid, $action_type, $protest)
    {
        $status = false;
        $fields = array(
            "action"         => $action_type,
            "status"         => "N",
            "eventid"        => $this->eventid,
            "competitorid"   => $competitorid,
            "memberid"       => "",               // future use
            "protest"        => $protest
        );
        $insert_rs = $this->db->db_insert("t_entry", $fields);
        if ($insert_rs) { $status = $action_type; }

        return $status;
    }


    public function get_by_event($fields, $where, $order, $fleet_sort)
    {
        empty($order) ? $order_clause = "" : $order_clause = " ORDER BY $order " ;
        $rs = $this->db->db_get_rows("SELECT $fields FROM t_race WHERE eventid = {$this->eventid} $where $order_clause");

        if ($fleet_sort)
        {
            $entry = array();
            for ($i = 1; $i <= $_SESSION["e_{$this->eventid}"]['rc_numfleets']; $i++)
            {
                $entry[$i] = array_values(array_filter($rs, function ($ar) use ($i){ return ($ar['fleet'] == $i); }));
            }
        }
        else
        {
            $entry = $rs;
        }
        return $entry;
    }


    public function get_by_compid($id)
    {
        $entry = $this->db->db_get_row("SELECT * FROM t_race WHERE eventid = {$this->eventid} AND competitorid = $id");
        return $entry;
    }


    public function get_by_raceid($id)
    {
        $entry = $this->db->db_get_row("SELECT * FROM t_race WHERE eventid = {$this->eventid} AND id = $id");
        return $entry;
    }


    public function delete_signons($eventid)
    {
        $rows = $this->db->db_delete("t_enter", array("eventid"=>$eventid));
        return $rows;
    }


    public function reset_signons($eventid)
    {
        $rows = $this->db->db_update("t_enter", array("status"=>"N", "entryid"=>""), array("eventid"=>$eventid));
        return $rows;
    }


    public function get_signons($type="entries")
    {
        $where_options  = array(
            "entries"      => " AND action IN ('enter', 'delete', 'update', 'replace') ",
            "retirements"  => " AND action = 'retire' ",
            "declarations" => " AND action IN ('retire', 'declare') ",
        );
        $type ? $where = $where_options["$type"] : $where = "";

        $query = "SELECT a.id as id, classid, boatnum, sailnum, classname, acronym, helm as helmname, helm_dob,
                         helm_email, a.crew as crewname, crew_dob, crew_email, club, nat_py, local_py, personal_py,
                         skill_level, flight, last_entry, last_event, a.active as active, grouplist, category,
                         b.crew as crew, rig, spinnaker, keel, engine, change_crew, change_sailnum,
                         x.id as t_entry_id, action
                  FROM t_entry as x
                  JOIN t_competitor as a ON x.competitorid = a.id
                  JOIN t_class as b ON a.classid=b.id
                  WHERE status IN ('N','F') AND eventid = {$this->eventid} $where ORDER BY x.id";
        $entries = $this->db->db_get_rows($query);

        // make requested changes
        foreach($entries as $k=>$entry)
        {
            if ($entry['change_crew'] != "" )
            {
                $entries[$k]['crewname'] = $entry['change_crew'];
                unset($entries[$k]['change_crew']);
            }
            if ($entry['change_sailnum'] != "" )
            {
                $entries[$k]['sailnum'] = $entry['change_sailnum'];
                unset($entries[$k]['change_sailnum']);
            }
        }
        return $entries;
    }


    public function count_signons($type="entries")
    {
        $where_options  = array(
            "entries"      => " AND action IN ('enter', 'delete', 'update', 'replace') ",
            "retirements"  => " AND action = 'retire' ",
            "declarations" => " AND action IN ('retire', 'declare') ",
        );
        $type ? $where = $where_options["$type"] : $where = "";
        $query = "SELECT * FROM t_entry WHERE status IN ('N','F') AND eventid = {$this->eventid} $where";
        $num_signons = $this->db->db_num_rows($query);
        return $num_signons;
    }


    public function allocate($entry)
    {
        /* FIXME:
            Still doesn't handle:'
                - check on personal_py if personal PY race
                - skill level limits
                - group restrictions      (constraints not in t_cfgrace)
                - age restrictions        (limits not in t_cfgrace)
                - flight restrictions     (limits not in t_cfgrace)
        */

        // get race configuration  - make sure default fleet is last in array
        $fleets = array();
        $default_fleet = 0;
        for ($i=1; $i<=$this->numfleets; $i++)        // FIXME - could be a foreach
        {
            if ($this->fleets[$i]['defaultfleet'] != "1" )
            {
                $fleets[] = $this->fleets[$i];
            }
            else
            {
                $default_fleet = $i;
            }
        }
        if ($default_fleet)
        {
            $fleets[] = $this->fleets[$default_fleet];
        }
        // debug:u_writedbg(u_check($fleets, "FLEETS"),__FILE__,__FUNCTION__,__LINE__);  // debug:

        // check which fleet this competitor is allocated too
        $alloc['status'] = false;
        if ($fleets)
        {
            // try each fleet - default last
            foreach ($fleets as $fleetcfg)
            {
                $classexc = array_map("trim", explode(",", strtolower($fleetcfg['classexc'])));
                $classinc = array_map("trim", explode(",", strtolower($fleetcfg['classinc'])));

                // debug:u_writedbg(u_check($fleetcfg, "FLEETCFG"),__FILE__,__FUNCTION__,__LINE__);
                // debug:u_writedbg(u_check($classexc, "EXCLUDES"),__FILE__,__FUNCTION__,__LINE__);
                // debug:u_writedbg(u_check($entry, "ENTRY"),__FILE__,__FUNCTION__,__LINE__);

                if (in_array(strtolower($entry['classname']), $classexc, true))  // check for exclusions
                {
                    // debug:u_writedbg("fleet: {$fleetcfg['fleetnum']} - excluded ",__FILE__,__FUNCTION__,__LINE__);  // debug:
                    continue; 	// this class is specifically excluded from this race - continue to next fleet
                }
                else
                {
                    if ($fleetcfg['onlyinc'])   // only include fleets in classinc
                    {
                        if (in_array(strtolower($entry['classname']), $classinc))
                        {
                            // debug:u_writedbg("fleet: {$fleetcfg['fleetnum']} - only included ",__FILE__,__FUNCTION__,__LINE__);  // debug:
                            $alloc = array("status"=>true, "alloc_code"=>"", "start"=>$fleetcfg['startnum'], "fleet"=>$fleetcfg['fleetnum']);
                            break;
                        }
                    }
                    else
                    {
                        if (in_array(strtolower($entry['classname']), $classinc)) // check if class is in included list
                        {
                            // debug:u_writedbg("fleet: {$fleetcfg['fleetnum']} - included ",__FILE__,__FUNCTION__,__LINE__);  // debug:
                            $alloc = array("status"=>true, "alloc_code"=>"", "start"=>$fleetcfg['startnum'], "fleet"=>$fleetcfg['fleetnum']);
                            break;
                        }
                        else  // if not allocated by class name then check if other class based characteristics match
                        {
                            $py_ok   = false;
                            $crew_ok = false;
                            $spin_ok = false;
                            $hull_ok = false;

                            // PY check  (passes if lies within range)
                            $fleetcfg['pytype']=="local" ? $py = $entry['local_py'] : $py = $entry['nat_py'];
                            // debug:u_writedbg("fleet: {$fleetcfg['fleetnum']} - PY comparison $py|{$fleetcfg['minpy']}|{$fleetcfg['maxpy']} ",__FILE__,__FUNCTION__,__LINE__);  // debug:
                            if( (empty($fleetcfg['minpy']) AND empty($fleetcfg['maxpy']))
                                OR ($py >= $fleetcfg['minpy'] AND $py <= $fleetcfg['maxpy']) )
                            {
                                $py_ok = true;
                            }

                            // crew check (passes if 'any' or correct number)
                            // debug:u_writedbg("fleet: {$fleetcfg['fleetnum']} - crew comparison {$entry['crew']}|{$fleetcfg['crew']} ",__FILE__,__FUNCTION__,__LINE__);  // debug:
                            if( empty($fleetcfg['crew'])
                                OR $entry['crew'] == $fleetcfg['crew'] )
                            {
                                $crew_ok = true;
                            }

                            // spinnaker type check (passes if 'any' or specified spinnaker type)
                            // debug:u_writedbg("fleet: {$fleetcfg['fleetnum']} - spin comparison {$entry['spinnaker']}|{$fleetcfg['spintype']} ",__FILE__,__FUNCTION__,__LINE__);  // debug:
                            if( empty($fleetcfg['spintype'])
                                OR strtolower($entry['spinnaker']) == strtolower($fleetcfg['spintype']) )
                            {
                                $spin_ok = true;
                            }

                            // hull type check ()passes if 'any' or specified hull type)
                            // debug:u_writedbg("fleet: {$fleetcfg['fleetnum']} - hull comparison {$entry['category']}|{$fleetcfg['hulltype']} ",__FILE__,__FUNCTION__,__LINE__);  // debug:
                            if( empty($fleetcfg['hulltype'])
                                OR strtolower($entry['category']) == strtolower($fleetcfg['hulltype']) )
                            {
                                $hull_ok = true;
                            }

                            // if all checks pass then allocate to this race
                            // debug:u_writedbg("fleet: {$fleetcfg['fleetnum']} - comparison summary $py_ok|$crew_ok|$spin_ok|$hull_ok ",__FILE__,__FUNCTION__,__LINE__); // debug:
                            if ($py_ok AND $crew_ok AND $spin_ok AND $hull_ok)
                            {
                                // debug:u_writedbg("fleet: {$fleetcfg['fleetnum']} - all match ",__FILE__,__FUNCTION__,__LINE__);
                                $alloc = array( "status" => true, "alloc_code" => "", "start" => $fleetcfg['startnum'], "fleet" => $fleetcfg['fleetnum']);
                                break;
                            }
                        }
                    }
                }
            }
            if (!$alloc['status'])   // did not find fleet to allocate it too
            {
                // debug:u_writedbg(" - not allocated to any fleet ",__FILE__,__FUNCTION__,__LINE__);  // debug:
                $alloc = array("status" => false, "alloc_code" => "E", "start" => "", "fleet" => ""); // E - ineligible (not allocated)
            }
        }
        else                         // did not find fleet configuration data to check
        {
            // debug:u_writedbg(" - no configuration found ",__FILE__,__FUNCTION__,__LINE__);  // debug:
            $alloc = array("status" => false, "alloc_code" => "X", "start" => "", "fleet" => ""); //  X - no configuration
        }

        return $alloc;
    }


    public function set_entry($entry)
        /*
         *
         * sets entry into t_race
         *
         */
    {
        if (!empty($entry))
        {
            $result = array(
                "class"   => $entry['classname'],
                "sailnum" => $entry['sailnum'],
                "helm"    => $entry['helmname'],
                "exists"  => false,
                "raceid"  => 0,
                "status"  => false,
                "problem" => ""
            );

            if ($entry['status'])  // ok to add entry (start/fleet allocated)
            {
                // if entry exists - delete it
                $exists = $this->get_by_compid($entry['id']);
                if ($exists)
                {
                    $delete = $this->delete_by_compid($entry['id']);
                    $result['exists'] = true;
                }

                // get PN for this race
                $pn = $entry['local_py'];
                $pytype = $this->fleets[$entry['fleet']]['pytype'];
                if ($pytype == "personal")
                {
                    $pn = $entry['personal_py'];
                }
                elseif ($pytype == "national")
                {
                    $pn = $entry['nat_py'];
                }

                // create insert record
                $record = array(
                    "eventid"      => $this->eventid,
                    "start"        => $entry['start'],
                    "fleet"        => $entry['fleet'],
                    "competitorid" => $entry['id'],
                    "helm"         => $entry['helmname'],
                    "crew"         => $entry['crewname'],
                    "club"         => $entry['club'],
                    "class"        => $entry['classname'],
                    "classcode"    => $entry['acronym'],
                    "sailnum"      => $entry['sailnum'],
                    "pn"           => $pn,
                    "status"       => "R",
                );

                // insert record
                $insert = $this->db->db_insert("t_race", $record);
                if ($insert)
                {
                    $result['problem']  = "entered";
                    $result['status']   = true;
                    $result['raceid']   = $this->db->db_lastid();                     // get record id for return message

                    $upd = $this->upd_lastrace($entry['id'], $this->eventid);    // record latest event in competitor record

                    // set entry counts and flags
                    $fnum = $record['fleet'];
                    $rs = $this->db->db_query("UPDATE t_racestate SET entries = entries + 1 WHERE eventid = {$this->eventid} and race = $fnum");
                }
                else    //  entry to t_race failed
                {
                    $result['problem']  = "entry failed";
                }
            }
            else    //  competitor is not eligible
            {
                $result['problem']  = "not allocated";
            }
        }
        else    // competitor not known
        {
            $result = array( "status" => false, "class" => "unknown", "sailnum" => "", "helm" => "",
                             "problem" => "not registered", "raceid" => 0);
        }

        return $result;   // if status is "entered" update $_SESSION to mark result status invalid and add 1 to no. of entries
    }


    public function confirm_entry($entryid, $code, $raceid="")
    {
        $update = array("status" => "$code");
        empty($raceid) ? $update['entryid'] = "" : $update['entryid'] = $raceid;
        $update = $this->db->db_update("t_entry", $update, array("id" => $entryid));

        return $update;
    }


    public function delete($entryid)
    {
        $status = true;
        $fields = $this->get_by_raceid($entryid);
        $fleetnum = $fields['fleet'];
        $this->db->db_delete("t_finish", array("entryid"=>$entryid));           // delete pursuit finish records
        $this->db->db_delete("t_lap", array("entryid"=>$entryid));              // delete lap records
        $num_rows = $this->db->db_delete("t_race", array("id"=>$entryid));      // delete race record
        if (!$num_rows)
        {
            $status = false;
        }
        else
        {                                                                       // update racestate entry count
            $rs = $this->db->db_query("UPDATE t_racestate SET entries = entries -1
                                           WHERE eventid = {$this->eventid} and race = $fleetnum");
        }

        return $status;   // if status is "true" update $_SESSION to mark result status invalid and delete 1 to no. of entries
    }

    public function delete_by_compid($compid)
    {
        $entry = $this->get_by_compid($compid);
        $status = $this->delete($entry['id']);
        return $status;
    }


    public function update($entryid, $update)
    {
        $num_rows = $this->db->db_update("t_race", $update, array("id"=>$entryid));
        return $num_rows;
    }


    public function duty_set($entryid, $status)
    {
        if ($status == "R")  { $status = "X"; }
        $num_rows = $this->update($entryid, array("code" => "DUT", "status" => $status));
        return $num_rows;
    }


    public function duty_unset($entryid, $status)
    {
        if ($status == "X")  { $status = "R"; }
        $num_rows = $this->update($entryid, array("code" => "", "status" => $status));
        return $num_rows;
    }


    public function code_set($entryid, $code)
    {
        // get timing flag for code
        $code_arr = $this->db->db_getresultcode($code);
        if ($code_arr['timing']==0 and $code!="")   // finish if code indicates stop timing or code is blank
        {
            $num_rows = $this->update($entryid, array("code" => $code, "status" => "X"));
        }
        else
        {
            $num_rows = $this->update($entryid, array("code" => $code));
        }
        return $num_rows;
    }


    public function code_unset($entryid, $status)
    {
        if ($status == "X")  { $status = "R"; }  // change status back to racing if they were previously excluded
        $num_rows = $this->update($entryid, array("code" => "", "status" => $status));
        return $num_rows;
    }


    public function get_regulars()
    {
        $c_o = new COMPETITOR($this->db);
        // get competitors tagged as regular (return competitor and class info)
       return $c_o->comp_findcompetitor(array("regular"=>1));
    }


    public function get_previous($date)
    {
        $c_o = new COMPETITOR($this->db);
        // get competitors that have sailed a previous race today  (return competitor and class info)
        return $c_o->comp_findcompetitor(array("last_entry"=>"$date"));
    }


    public function get_competitor($comp_id)
    {
        $c_o = new COMPETITOR($this->db);
        // get competitor by id  (return competitor and class info)
        $rs = $c_o->comp_findcompetitor(array("id"=>"$comp_id"));
        if ($rs)
        {
            return $rs[0];
        }
        else
        {
            return false;
        }
    }


    public function upd_lastrace($competitorid, $eventid)
    {
        $status = false;
        $where  = array("id"=>$competitorid);
        $fields['last_entry'] = date("Y-m-d");
        $fields['last_event'] = $eventid;

        $rs = $this->db->db_update( 't_competitor', $fields, $where );
        if ($rs)  { $status = true; }

        return $status;
    }

}