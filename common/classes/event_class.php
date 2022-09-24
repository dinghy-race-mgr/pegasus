<?php
/*------------------------------------------------------------------------------
** File:		event_class.php
** Class:       xxxxx
** Description:	xxxxxxxx 
** Version:		1.0
** Updated:     19-May-2014
** Author:		Mark Elkington
** HomePage:    www.pegasus.co.uk 
**------------------------------------------------------------------------------
** COPYRIGHT (c) %!date!% MARK ELKINGTON
**
** The source code included in this package is free software; you can
** redistribute it and/or modify it under the terms of the GNU General Public
** License as published by the Free Software Foundation. This license can be
** read at:
**
** http://www.opensource.org/licenses/gpl-license.php
**
** This program is distributed in the hope that it will be useful, but WITHOUT 
** ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
** FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. 
**------------------------------------------------------------------------------ */

/* refactor plans

// find event methods

event_find_id                       event_getevent($eventid)
event_find_next                     event_getnextevent($date)
event_find_future                   geteventsfromdate($date)
event_search                        event_getevents($fields, $mode)
event_search_opens                  event_getopenevents($fields, $mode)

// event format methods
event_list_formats                   event_geteventformats($db, $active)
event_racecfg_get                    event_getracecfg($db, $eventid, $racecfgid)
event_fleetcfg_get                   event_getfleetcfg($db, $racecfgid)

// event fleet methods
--> move to race_class               event_getfleetstatus($eventid)

// event result methods
--> move to results_class            event_addresultitem($db, $fields)

// event methods
event_count
event_add                            event_addevent($fields, $duties)
event_update                         event_changedetail($eventid, $fields)
event_close                          event_eventclose($eventid)
event_reset                          event_eventreset($eventid)
event_delete
event_publish

// event misc methods
event_wind_set                       event_seteventdetail($detail)
--> review this                      event_changeresults($eventid, $notes, $ws_start, $wd_start, $ws_end, $wd_end)

// event status methods
event_state_get                      event_getracestate ($currentstatus)
event_state_update                   event_updatestatus($eventid, $status)

// duty methods  MIGHT GO INTO ROAT CLASS
duty_event_get                       event_geteventduties($eventid, $dutycode="")
duty_person_get                      event_getdutyperson($db, $eventid, $dutycode)
duty_person_add                      event_addduty($eventid, $fields)

// entry methods
entry_clear_all                      event_clearentries($eventid)
entry_event_get                      event_getentries($eventid, $type="entries")
entry_event_count                    event_countrecordstoload($eventid, $type="entries")
entry_event_confirm                  event_confirmentry($entryid, $raceid, $code)

// series methods
series_info_get                      event_getseries($code)
series_list_get                      event_getseriescodes()
series_eventlist

// message methods
message_event_add                    event_addmessage($eventid, $fields
*/

class EVENT
{
    private $db;
    
    //Method: construct class object
    public function __construct(DB $db)
	{
	    $this->db = $db;

        /* FIXME - is theis the best way to do this - should I have a PROGRAMME class  which act
           on all events in the programme, and an EVENT class which acts on a specific event */
	}

    public function count_events($constraint)
    {
        $where = " 1=1 ";
        if ($constraint)
        {
            $clause = array();
            foreach( $constraint as $field => $value )
            { $clause[] = "`$field` = '$value'"; }
            $where = implode(' AND ', $clause);
        }

        $query = "SELECT id  FROM t_event WHERE $where AND active = 1 ";
        $detail = $this->db->db_get_rows( $query );

        return count($detail);
    }
    
    public function get_event_formats($active, $code=false)
    {
        $formats = array();

        $where = "WHERE 1=1 ";
        if ($active) { $where.= " AND active = '1' "; } 
        
        $query = "SELECT id, race_code, race_name FROM t_cfgrace $where ORDER BY race_name";
        $results = $this->db->db_get_rows( $query );

        foreach ($results as $value)
        {
            if ($code)
            {
                $formats["{$value['id']}"] = $value['race_code'];
            }
            else
            {
                $formats["{$value['id']}"] = $value['race_name'];
            }
        }
        return $formats;
    }
    

    public function get_event_byid($eventid, $type = false)
    {
        $constraints = array("id"=>$eventid);
        if ($type) { $constraints['event_type'] = $type; }

        $events = $this->get_events("not_noevent", "all", array(), $constraints);
        if (empty($events)) {
            $detail = false;
        } else {
            $detail = $events[0];
        }

        return $detail;       
    }
    
    public function get_nextevent($date, $requiredtype = "")
    /*
        Finds the next event from today in next 2 months
    */
    {
        empty($requiredtype) ? $type = "not_noevent" : $type = $requiredtype;
        $period = array("start" => $date, "end" => date('Y-m-d', strtotime('+60 days', strtotime($date))));

        $events = $this->get_events($type, "active", $period, array());

        if (empty($events))       // nothing found
        {
            $detail = false;
        } else {                  // return first event
            $detail = $events[0];
        }

        return $detail;       
    }

// NOT USED ANYWHERE
//    public function geteventsfromdate($date)
//    /*
//    Returns all events from specified event - ignores demo events
//    */
//    {
//        $formats = $this->event_geteventformats(true);    // get names for race formats
//
//        $where  = " WHERE event_name NOT LIKE '%DEMO%' AND event_date>='$date'
//                    AND event_type != 'noevent' AND active = 1";
//        $query = "SELECT * FROM t_event $where ORDER BY event_date ASC LIMIT 1";
//        //echo "<pre>$query</pre>";
//        $detail = $this->db->db_get_rows( $query );
//        if (empty($detail))       // nothing found
//        {
//            $detail = false;
//        }
//        else
//        {
//            foreach($detail as $k=>$row)
//            {
//                if (array_key_exists($row['event_format'], $formats))
//                {
//                    $detail[$k]['race_name'] = $formats[$row['event_format']];
//                }
//                else
//                {
//                    $detail[$k]['race_name'] = "";
//                }
//            }
//        }
//
//        return $detail;
//    }


    public function get_events($type, $status, $period = array(), $constraints = array())
        /*
         * returns an array of event records
         *    type   - 'all' or 'not_noevent' or specified 'event_type'
         *    status - 'active', 'not_active', 'demo', 'all'
         *    period - array with start and end keys for inclusive date period - if empty not applied - if end not specified with do events from
         *    constraints - array with field specific constraints
         *
         */
    {
        $select = "SELECT id, event_date, TIME_FORMAT(event_start, '%H:%i') AS event_start, event_order, event_name, series_code, event_type, event_format, 
                          event_entry, event_status, event_open, event_ood, tide_time, tide_height, start_scheme,
                          start_interval, timerstart, ws_start, wd_start, ws_end, wd_end, event_notes, 
                          result_notes, result_valid, result_publish, weblink, 
                          webname, display_code, active, upddate, updby FROM t_event";
        $order =  " ORDER BY event_date ASC, event_order ASC, event_start ASC  ";
        $where = "1=1";
        $where_period = "";
        $where_type = "";
        $where_status = "";
        $where_constraints = "";

        //echo "<pre".print_r($period, true)."| status: $status</pre>";
        if (!empty($period) and $status != "demo")
        {
            if (array_key_exists("start", $period))
            {
                $where_period.= " AND `event_date`>='".date("Y-m-d", strtotime($period['start']))."'";
            }
            if (array_key_exists("end", $period))
            {
                $where_period.= " AND `event_date`<='".date("Y-m-d", strtotime($period['end']))."'";
            }
        }

        if ($type != "all") {
            if ($type == "not_noevent") {
                $where_type = " AND `event_type` != 'noevent'";
            } elseif ($type == "racing") {
                $where_type = " AND event_format > 0 AND event_type ='racing'";
            } else {
                $where_type = " AND `event_type` = '$type'";
            }
        }

        if ($status != "all") {
            if ($status == "active") {
                $where_status = " AND `active` = 1 AND event_name NOT LIKE '%DEMO%'";
            } elseif ($status == "not_active") {
                $where_status = " AND `active` = 0 AND event_name NOT LIKE '%DEMO%'";
            } elseif ($status == "demo") {
                $where_status = " AND `event_name` LIKE '%DEMO%'";
            }
        }

        if ($constraints) {
            $clause = array();
            foreach ($constraints as $field => $value) {
                $clause[] = "`$field` = '$value'";
            }
            $where_constraints .= " AND ".implode(' AND ', $clause);
        }

        $query = $select." WHERE $where $where_period $where_type $where_status $where_constraints ".$order;
        //echo "<pre>$query</pre>";

        $detail = $this->db->db_get_rows( $query );
        
        if (empty($detail))       // nothing found
        {
            $detail = false;
        } else {
            $formats = $this->get_event_formats(true);    // get names for race formats
            foreach ($detail as $k => $row) {
                if (array_key_exists($row['event_format'], $formats)) {
                    $detail[$k]['race_name'] = $formats[$row['event_format']];
                } else {
                    $detail[$k]['race_name'] = "";
                }
            }
        }

        return $detail;
    }


    public function get_events_inperiod($fields, $start, $end, $mode, $race = false)
    {
        $race ? $type = "racing" : $type = "not_noevent";
        if ($mode == "demo")
        {
            $status = "demo";
        }
        elseif ($mode == "all")
        {
            $status = "all";
        }
        else
        {
            $status = "active";
        }
        $period = array("start"=>$start, "end"=>$end);
        $events = $this->get_events($type, $status, $period, $fields);
        empty($events) ? $detail = false : $detail = $events;

        return $detail;
    }


    //Method: get events 
    public function get_events_bydate($date, $mode, $requiredtype = "")
        /*
         * gets events on specified day.
         * If mode set to demo - only looks for demo events and ignores date
         * required types can define which type of events are include - specified as delimited string (e.g. "dcruise|freesail"
         */
    {
        $mode == "demo" ? $status = "demo" : $status = "active";
        $events = $this->get_events("not_noevent", $status, array(), array("event_date" => $date));

        if (!empty($requiredtype) and !empty($events))
        {
            foreach ($events as $k=>$row)
            {
                if (strpos($requiredtype, $row['event_type']) === false)
                {
                    unset($events[$k]);
                }
            }
        }
        empty($events) ? $detail = false : $detail = $events;

        return $detail;       
    }
    
//    public function get_events_inprogress($fields, $mode)
//    /*
//        only returns events that are not complete, cancelled or abandoned
//    */
//    {

//        $formats = $this->get_eventformats(true);    // get names for race formats
//
//        if ($mode=="demo")
//        {
//            $where = " WHERE event_name LIKE '%DEMO%' AND event_status NOT IN ('completed', 'cancelled', 'abandoned') ";
//        }
//        else
//        {
//            $where = " WHERE event_name NOT LIKE '%DEMO%' AND event_status NOT IN ('completed', 'cancelled', 'abandoned') ";
//            if ($fields)
//            {
//                $where.= " AND ";
//                foreach( $fields as $field => $value )
//                {
//                    if ($field == "event_date")
//                        { $clause[] = "`$field` = '$value'"; }
//                    else
//                        { $clause[] = "`$field` = '$value'"; }
//                }
//                $where.= implode(' AND ', $clause);
//            }
//        }
//        $query = "SELECT * FROM t_event $where AND event_type != 'noevent' AND active = 1 ORDER BY event_date ASC, event_order ASC, event_start ASC  ";
//
//        $detail = $this->db->db_get_rows( $query );
//        if (empty($detail))       // nothing found
//        {
//            $detail = false;
//        }
//        else
//        {
//            foreach($detail as $k=>$row)
//            {
//                if (array_key_exists($row['event_format'], $formats))
//                {
//                    $detail[$k]['race_name'] = $formats[$row['event_format']];
//                }
//                else
//                {
//                    $detail[$k]['race_name'] = "";
//                }
//            }
//        }
//
//        return $detail;
//    }

//// FIXME - moved to rota class (currently only used #418 on result_class.php
//
//    public function event_geteventduties($eventid, $dutycode = "")
//    {
//        $duties = array();
//        $query = "SELECT * FROM t_eventduty WHERE eventid = $eventid ";
//        if (!empty($dutycode))
//        {
//            $query.= " AND dutycode = '$dutycode' ";
//        }
//        $duties = $this->db->db_get_rows( $query );
//
//        if (empty($duties))
//        {
//            $duties = false;
//        }
//        return $duties;
//    }
//
//    // FIXME - moved to rota class - only used in #81 pickrace.php
//    public function event_getdutyperson($eventid, $dutycode)
//    {
//        $duty_person = "";
//        $duties = $this->db->db_get_rows("SELECT * FROM t_eventduty WHERE eventid = $eventid AND dutycode='$dutycode'");
//
//        if (!empty($duties)) {
//            $duty_person = $duties[0]['person'];
//        }
//        return $duty_person;
//    }
    

    public function event_addevent($fields, $duties)
    {       
        //u_writedbg("<pre>".print_r($fields,true)."</pre><pre>".print_r($duties,true)."</pre>","addrace","event_addevent", 333);

        // check for missing mandatory event fields
        if (empty($fields['event_date']) OR empty($fields['event_name'])
            OR empty($fields['event_type']) OR empty($fields['event_format'])
            OR empty($fields['event_entry']))
        {
            return "missingfield";
        }
        
        // check event_type code
        if (!$this->db->db_checksystemcode("event_type", $fields['event_type']))
        {
            return "codeinvalid";
        }
                
        // check event_format is recognised
        $racecfg = $this->event_getracecfg($fields['event_format']);
        if (empty($racecfg))
        {
            return "noraceformat";
        }
        
        // check event_entry code
        if (!$this->db->db_checksystemcode("entry_type", $fields['event_entry']))
        {
            return "codeinvalid";
        }
                
        // check event_status code
        if (empty($fields['event_status'])) 
        { 
            $fields['event_status'] = "scheduled"; 
        }
        else
        {
            if (!$this->db->db_checksystemcode("event_status", $fields['event_status']))
            {
                return "statusinvalid";
            }
        }
        
        // get event order if not provided (find events on same day)
        if (empty($fields['event_order']))
        {
            $eventstoday = $this->get_events_bydate($fields['event_date'], $_SESSION['mode'], "racing");
            $maxeventnum = 0;
            foreach($eventstoday as $key=>$event)
            {
                $eventnum = intval($event['event_order']);
                if ($eventnum > $maxeventnum) { $maxeventnum = $eventnum; }
            }
            $fields['event_order'] = $maxeventnum + 1;
        }
                
        
        // check series code is known
        if (!empty($fields['series_code']))
        {
            $serieslist = $this->event_getseriescodes();
            if (!array_key_exists(u_stripseriesname($fields['series_code']), $serieslist))
            {
                return "seriesinvalid";
            }
        }

        $insert = $this->db->db_insert( 't_event', $fields );
        if ($insert)
        {
            $status = "ok";
            $eventid = $this->db->db_lastid();                         // get event record id
            $addduty = $this->event_addduty($eventid, $duties);        // now add duties
            
            if (!$addduty)
            {
                $status = "dutyfailed";
            }            
        }
        else
        {
            $status = "insertfailed";
        }
                
        return $status;
    }
    
    //Method: add duty
    public function event_addduty($eventid, $fields)
    {       
        $status = false;

        foreach ($fields as $key => $duty)      // loop over duties in array
        {            
            $duty['eventid'] = $eventid;
            // check mandatory fields
            if (!empty($duty['eventid']) AND !empty($duty['person']) AND !empty($duty['dutycode']))
            {
                // check duty code
                //u_writedbg($duty['dutycode'], "addrace", "event_addduty", 311);
                if ($this->db->db_checksystemcode("rota_type", $duty['dutycode']))
                {
                    $duty['person']  = ucwords($duty['person']);
                    $status = $this->db->db_insert( 't_eventduty', $duty );
                }
            }
        }
        return $status;                
    }
    
    //Method: add message 
    public function event_addmessage($eventid, $fields)
    {       
        $fields['eventid'] = $eventid;
        // check for mandatory fields
        if (empty($fields['name']) OR empty($fields['subject']) OR empty($fields['message']) OR empty($fields['status']))
        {
            return false;
        }        
        $status = $this->db->db_insert( 't_message', $fields );        
        return $status;
    }
    
    //Method: change event detail
    public function event_changedetail($eventid, $fields)
    {
        $change = false;

        unset($fields['result_status']);
        unset($fields['include_club']);

        $success = $this->db->db_update( 't_event', $fields, array("id"=>$eventid) );
        if ($success>=0)
        {
            // update session
            if (!empty($fields['event_ood']))
            {
                $_SESSION["e_$eventid"]['ev_ood'] = $fields['event_ood'];
            }
            if (!empty($fields['event_start']))
            {
                $_SESSION["e_$eventid"]['ev_starttime'] = $fields['event_start'];
            }
            if (!empty($fields['event_entry']))
            {
                $_SESSION["e_$eventid"]['ev_entry'] = $fields['event_entry'];
            }
            if (!empty($fields['start_scheme']))
            {
                $_SESSION["e_$eventid"]['ev_startscheme'] = $fields['start_scheme'];
                $_SESSION["e_$eventid"]['rc_startscheme'] = $_SESSION["e_$eventid"]['ev_startscheme'];
            }
            if (!empty($fields['start_interval']))
            {
                $_SESSION["e_$eventid"]['ev_startint'] = $fields['start_interval'];
                $_SESSION["e_$eventid"]['rc_startint'] = $_SESSION["e_$eventid"]['ev_startint'];
            }

            $wind = array();
            empty($fields['ws_start']) ? $wind['ws_start'] = "" : $wind['ws_start'] = $fields['ws_start'];
            empty($fields['ws_end'])   ? $wind['ws_end'] = "" : $wind['ws_end'] = $fields['ws_end'];
            empty($fields['wd_start']) ? $wind['wd_start'] = "" : $wind['wd_start'] = $fields['wd_start'];
            empty($fields['wd_end'])   ? $wind['wd_end'] = "" : $wind['wd_end'] = $fields['wd_end'];
            if (!empty($wind))
            {
                $_SESSION["e_$eventid"]['ev_wind'] = u_getwind_str($wind);
            }

            if (!empty($fields['event_notes']))
            {
                $_SESSION["e_$eventid"]['ev_notes']       = $fields['event_notes'];
            }
            if (!empty($fields['result_notes']))
            {
                $_SESSION["e_$eventid"]['ev_resultnotes'] = $fields['result_notes'];
            }
            u_writelog("changed race details", $eventid);
            $change = true;
        }
        else
        {
           u_writelog("attempt to change race details FAILED", $eventid);    // update event log
           $change = false;
        }
        return $change;
    } 
    
//    public function event_addnotes($eventid, $notes)
//    {
//        $upd = $this->db->db_update('t_event', array("result_notes"=>$notes), array("id"=>$eventid));
//        if ($upd)
//        {
//            $_SESSION["e_$eventid"]['ev_resultnotes'] = $notes;
//        }
//        return $upd;
//    }

//    public function event_addwind($eventid, $ws_start, $wd_start, $ws_end, $wd_end)
//    {
//        $fields = array(
//           "ws_start"     => $ws_start,
//           "wd_start"     => $wd_start,
//           "ws_end"       => $ws_end,
//           "wd_end"       => $wd_end
//        );
//        $upd = $this->db->db_update( 't_event', $fields, array("id"=>$eventid));
//
//        if ($upd)
//        {
//            $wind_str = "";
//            if (!empty($wd_start)) { $wind_str.= $wd_start." "; }
//            if (!empty($ws_start)) { $wind_str.= $ws_start." mph "; }
//            $wind_str.= "-";
//            if (!empty($detail['wd_end'])) { $wind_str.= $wd_end." "; }
//            if (!empty($detail['ws_end'])) { $wind_str.= $ws_end." mph "; }
//
//
//            $fields = $this->event_seteventdetail($fields);     // FIXME this is an ugly function make it wind only and a different return
//            $_SESSION["e_$eventid"]['ev_wind'] = vsprintf("%s %s mph - ");
//        }
//        return $success;
//    }
    
    // Method: update event status
    public function event_updatestatus($eventid, $status)
    {
       //u_writedbg("status:s_status:s_p_status - $status|{$_SESSION["e_$eventid"]['ev_status']}|{$_SESSION["e_$eventid"]['ev_prevstatus']}", __FILE__, __FUNCTION__,__LINE__); // debug:

       $success  = false;
       $status_ok = (in_array($status, $_SESSION['race_states']))? true : false;  // status is known

       if ($status_ok )
       {
           if ($_SESSION["e_$eventid"]['ev_status'] != $status)   // is update required
           {
               $success = $this->db->db_update( 't_event', array("event_status"=>"$status"), array("id"=>$eventid) );    
               if (is_int($success))
               {
                    $success = true;
                    // update session status
                    $_SESSION["e_$eventid"]['ev_prevstatus'] = $_SESSION["e_$eventid"]['ev_status'];
                    $_SESSION["e_$eventid"]['ev_status']     = $status; 
                    $msg = "event status changed:  {$_SESSION["e_$eventid"]['ev_prevstatus']} -> {$_SESSION["e_$eventid"]['ev_status']}";
                    u_writelog($msg, $eventid);
//                    u_writedbg($msg, __FILE__, __FUNCTION__,__LINE__); // debug:
               }               
           }
           else
           {
               $success = true;
           }           
       }
       else
       {
           //u_writedbg("status:s_status:s_p_status - $status|{$_SESSION["e_$eventid"]['ev_status']}|{$_SESSION["e_$eventid"]['ev_prevstatus']}", __FILE__, __FUNCTION__,__LINE__); // debug:
       }

       return $success;
    }
    
    
    // Method: add result item to event
    public function event_addresultitem($db, $fields)
    {       
        // adds a reference to a result item
    }
    
    public function racecfg_findbyname($name, $active = true)
    {
        if ($active)
        {
            $query  = "SELECT * FROM t_cfgrace WHERE race_name = '$name' AND active=1";
        }
        else
        {
            $query  = "SELECT * FROM t_cfgrace WHERE race_name = '$name'";
        }
        $detail = $this->db->db_get_row( $query );
        if (empty($detail))
        {
            $detail = false;
        }
        return $detail;
    }

    public function event_getracecfg($racecfgid, $eventid = 0)
    {
        // return race cfg details as array
        $query  = "SELECT * FROM t_cfgrace WHERE id = $racecfgid AND active=1";       
        $detail = $this->db->db_get_row( $query );
        if (empty($detail)) 
        { 
            $detail = false; 
        } 
        else  // check if a change has been made to start scheme/intervals
        {
            if ($eventid > 0)
            {
                if (!empty($_SESSION["e_$eventid"]['ev_startscheme']))
                {
                    $detail['start_scheme'] = $_SESSION["e_$eventid"]['ev_startscheme'];
                }

                if (!empty($_SESSION["e_$eventid"]['ev_startint']))
                {
                    $detail['start_interval'] = $_SESSION["e_$eventid"]['ev_startint'];
                }
            }

        }       
        return $detail;
    }
    
    public function event_getfleetcfg($racecfgid)
    {
        // return fleet cfg details as array
        $detail = array();
        $query  = "SELECT * FROM t_cfgfleet WHERE eventcfgid = $racecfgid ORDER BY start_num, fleet_num";        
        // echo "<pre>$query</pre>";
              
        $detail = $this->db->db_get_rows( $query );
        if (empty($detail)) 
        { 
            $detail = false; 
        }        
        return $detail;
    }
    
    public function get_fleetstatus($eventid)
    {
        // FIXME - how does this know if the race has started - there is no user event for that

        // return fleet cfg details as array
        $query = "SELECT a.id, a.eventid, a.racename, a.fleet, a.start, a.racetype, a.startdelay, a.starttime, a.maxlap,
                         a.currentlap, a.entries, a.status, a.prevstatus, a.upddate,
                         (SELECT count(*) FROM t_race as b WHERE b.eventid = $eventid and b.fleet = a.fleet and status = 'R'
                          GROUP BY b.fleet) as num_racing
                  FROM t_racestate as a
                  WHERE a.eventid = $eventid
                  ORDER BY fleet";

        $detail = $this->db->db_get_rows( $query );
        if (empty($detail)) 
        { 
            $detail = false; 
        }
        else    // decode race status
        {
            $display = array(
                "notstarted"  => "not started",
                "inprogress"  => "in progress",
                "finishing"   => "finishing",
                "allfinished" => "all finished",
            );

            foreach ($detail as $k=>$fleet)
            {
                $detail["$k"]['status'] = $display["{$fleet['status']}"];
            }
        }
        return $detail;
    }


    public function get_eventstate ($currentstatus)
    {
        // This returns the state of the entire event
        if (in_array($currentstatus, array("completed","cancelled","abandoned")))
        {
            return "complete";
        }
        elseif(in_array($currentstatus, array("running","sailed")))
        {
            return "running";
        }
        elseif(in_array($currentstatus, array("scheduled", "selected")))
        {
            return "notstarted";
        }
        else
        {
            return "unknown";
        }
    }

    public function get_event_state_sequence($current_status)
    {
        $num = 0;
        if ($current_status == "scheduled")     { $num = 1; }
        elseif ($current_status == "selected")  { $num = 2; }
        elseif ($current_status == "running")   { $num = 3; }
        elseif ($current_status == "sailed")    { $num = 4; }
        elseif ($current_status == "complete")  { $num = 5; }
        elseif ($current_status == "abandoned") { $num = 6; }
        elseif ($current_status == "cancelled") { $num = 7; }

        return $num;
    }
    


    public function event_in_series($eventid)
    {
        $detail = $this->get_event_byid($eventid);
        if ($detail)
        {
            if ($detail['series_code'])
            {
                $series = $this->event_getseries($detail['series_code']);
            }
            else
            {
                $series = false;
            }
        }
        else
        {
            $series = false;
        }
        return $series;
    }

    public function event_getseries($code)
    {    
        // find root series code (i.e. code without year suffix)
        $pos = strrpos( $code, '-');
        if ($pos !== false)
        {
            $rootcode = substr($code, 0, $pos);
        }
        else
        {
            return false; // code not valid
        }

        $query = "SELECT a.id as id, seriescode, seriesname, a.seriestype as seriestype, b.seriestype as seriestypename,
                  race_format, startdate, enddate, merge, classresults, discard, nodiscard, multiplier, avgscheme, dutypoints, 
                  maxduty, opt_upload, opt_style, opt_turnout, opt_scorecode, opt_clubnames, opt_pagebreak, opt_racelabel, a.notes
                  FROM t_series as a JOIN t_cfgseries as b ON a.seriestype=b.id WHERE a.active=1
                  AND seriescode = '$rootcode'  ORDER BY seriesname ASC";
        // echo "SERIES QUERY: $query<br>";
        $detail = $this->db->db_get_row( $query );

        if (empty($detail))
        {
            $detail = false;
        }
        else
        {
            $detail['event_seriescode'] = $code;
        }
        return $detail;       
    } 
    
    public function event_getseriescodes()
    {    
        // this method gets all series that are active to create a codelist                                                            
        $query = "SELECT seriescode, seriesname FROM t_series WHERE active=1 ORDER BY seriesname ASC";
     
        $result = $this->db->db_get_rows( $query );

        if ($result)
        {
            foreach ($result as $row)
            {
                $detail["{$row['seriescode']}"] = $row['seriesname'];
            }
        } 
        else
        { 
            $detail = false; 
        }
        return $detail;       
    }


    public function series_eventarr($code)
    {
        // gets list of events that are part of the specified series
        $query = "SELECT id FROM t_event WHERE active=1
                  AND series_code = '$code' ORDER BY event_date ASC, event_start ASC";
        $detail = $this->db->db_get_rows( $query );

        if (empty($detail))
        {
            return false;
        }
        else
        {
            $rs = array();
            foreach($detail as $k=>$row)
            {
                $rs[$k] = $row['id'];
            }
        }
        return $rs;
    }


    public function event_close($eventid)
    {
        // check this is still an active event
        if (empty($_SESSION["e_$eventid"]['ev_name']))
        {
            return false;   
        }

        // archive entry data to table a_entry
        $num_del = $this->db->db_delete("a_lap", array("eventid" => $eventid));
        $detail = $this->db->db_get_rows( "SELECT * FROM t_entry WHERE eventid = $eventid" );
        if ($detail)
        {
            foreach ($detail as $row)
            {
                $insert = $this->db->db_insert("a_entry", $row );
            }
        }

        // clear tables
        $del = $this->db->db_delete("t_entry", array("eventid"=>$eventid));        // entries
        $del = $this->db->db_delete("t_race", array("eventid"=>$eventid));         // race details
        $del = $this->db->db_delete("t_lap", array("eventid"=>$eventid));          // lap times
        $del = $this->db->db_delete("t_finish", array("eventid"=>$eventid));       // pursuit finishing positions
        $del = $this->db->db_delete("t_racestate", array("eventid"=>$eventid));    // race state
        
        
        // set event status
        if ($_SESSION["e_$eventid"]['ev_status']!="cancelled" AND $_SESSION["e_$eventid"]['ev_status']!="abandoned" )
        {
            $setstate = $this->event_updatestatus($eventid, "completed");
        }
                
        // write to log
        u_writelog("event CLOSED - tables cleared", $eventid);
        
        // clear session
        unset($_SESSION["e_$eventid"]);
        
        return true;
    }
    
   
    public function event_reset($eventid, $mode, $prev_event_status="")
    {
        // method used to either initialise a new event, reset a running or rejoin a running event

        $status = true;

        // reset entries in entry table if a race 'reset' - enables them to be reloaded
        if ($mode == "reset")
        {
            $entry_o = NEW ENTRY ($this->db, $eventid);
            $entryrows = $entry_o->reset_signons($eventid);
            if ($entryrows < 0) { $status = false; }   // FIXME - should this be an exitnicely
        }

        if ($status)
        {

            if ($mode == "init" or $mode == "reset")    // if we are not rejoining do a full initialisation
            {
                // clear tables
                $del = $this->db->db_delete("t_race", array("eventid" => $eventid));          // race details
                $del = $this->db->db_delete("t_lap", array("eventid" => $eventid));           // lap times
                $del = $this->db->db_delete("t_finish", array("eventid" => $eventid));        // pursuit finish positions
                $del = $this->db->db_delete("t_racestate", array("eventid" => $eventid));     // racestate

                // set status
                $upd = $this->event_updatestatus($eventid, "selected");

                // reset other fields that need initialising in t_event
                $fields = array(
                    "timerstart" => "",
                    "ws_start" => "",
                    "ws_end" => "",
                    "wd_start" => "",
                    "wd_end" => "",
                    "result_valid" => 0,
                    "result_publish" => 0
                );
                $upd = $this->event_changedetail($eventid, $fields);
            }

            // now reinitialise event
            $status = r_initialiseevent($mode, $eventid, $prev_event_status);
        }

        return  $status;
    }

    public function event_delete($eventid)
    {
        $status = true;

        // delete event
        $del = $this->db->db_delete("t_event", array("id"=>$eventid));
        if($del === false) { $status = false; }

        // delete associated duties
        $del = $this->db->db_delete("t_eventduty", array("eventid"=>$eventid));
        if($del === false) { $status = false; }

        return $status;
    }

    public function event_publish($eventid, $action)
    {
        $status = true;

        if (strtolower($action) == "publish")
        {
            $upd = $this->db->db_update("t_event", array("active" => 1), array("id"=>$eventid));
        }
        elseif(strtolower($action) == "unpublish")
        {
            $upd = $this->db->db_update("t_event", array("active" => 0), array("id"=>$eventid));
        }

        if ($upd < 1)
        {
            $status = false;
        }

        return $status;
    }
    
    
    
//    public function event_clearentries($eventid)
//    {
//        $rows = $this->db->db_delete("t_enter", array("eventid"=>$eventid));
//        return $rows;
//    }
//
//    public function event_resetentries($eventid)
//    {
//        $rows = $this->db->db_update("t_enter", array("status"=>"N"), array("eventid"=>$eventid));
//        return $rows;
//    }
//
//
//    public function event_getentries($eventid, $type="entries")
//    /* retries unprocessed entries, retirements and declarations from t_entry */
//    {
//        $where = "";
//        if ($type == "entries")
//        {
//            $where = " AND action IN ('enter', 'delete', 'update', 'replace') ";
//        }
//        elseif ($type == "retirements")
//        {
//            $where = " AND action = 'retire' ";
//        }
//        elseif ($type == "declarations")
//        {
//            $where = " AND action IN ('retire', 'declare') ";
//        }
//        $query = "SELECT * FROM t_entry WHERE status IN ('N','F') AND eventid = $eventid $where ORDER BY upddate";
//        $entries = $this->db->db_get_rows($query);
//
//        return $entries;
//    }
//
//    public function event_countrecordstoload($eventid, $type="entries")
//    {
//        $numrecords = 0;
//        if ($type == "entries")
//        {
//            $entries = $this->event_getentries($eventid, "entries");
//            $numrecords = count($entries);
//        }
//        elseif ($type == "retirements")
//        {
//            $retirements = $this->event_getentries($eventid, "retirements");
//            $numrecords = count($retirements);
//        }
//        elseif  ($type == "declarations")
//        {
//            $declarations = $this->event_getentries($eventid, "declarations");
//            $numrecords = count($declarations);
//        }
//
//        return $numrecords;
//    }
//
//    public function event_confirmentry($entryid, $raceid, $code)
//    {
//        $updatefields = array("status" => "$code");
//        if ($raceid) { $updatefields['entryid'] = $raceid; }
//
//        $update = $this->db->db_update("t_entry", $updatefields, array("id" => $entryid));
//        return $update;
//    }
//
}


