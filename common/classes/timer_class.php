<?php
/**
 * timer_class.php
 * 
 * Class to handle race timer
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 * 
 * Methods:
 *      
 *     start     -  starts timer     (public)
 *     stop      -  stops timer      (public)
 *     settimes  -  deals with the internal changes when changing the state of the timer (private)
 *     setrecall -  sets start time for a fleet with a general recall (public))
 * 
 */

class TIMER
{
    private $db;
    private $eventid;
    
    //Method: construct class object
    public function __construct(DB $db, $eventid)
	{
	    $this->db = $db;
        $this->eventid = $eventid;
	}    

    
    public function start($starttime)
    {
        $event = "e_$this->eventid";
        $_SESSION["$event"]['timerstart'] = $starttime;

        $this->set_start_times($_SESSION["e_{$this->eventid}"]['rc_startscheme'], $_SESSION["e_{$this->eventid}"]['rc_startint']);

        $this->set_fleet_times("inprogress", $starttime);

        // debug: u_writedbg($logmsg, __FILE__, __FUNCTION__, __LINE__); // debug:
        u_writelog("timer started at ".gmdate("H:i:s",$starttime), $this->eventid);
    }

    
    public function stop($stoptime)
    {
        $event = "e_$this->eventid";
        $_SESSION["$event"]['timerstart'] = 0;

        $this->set_start_times($_SESSION["e_{$this->eventid}"]['rc_startscheme'], $_SESSION["e_{$this->eventid}"]['rc_startint']);
        
        $this->set_fleet_times("notstarted", $stoptime);

        // debug: u_writedbg($logmsg, __FILE__, __FUNCTION__, __LINE__); // debug:
        u_writelog("timer stopped at ".gmdate("H:i:s",$stoptime), $this->eventid);
    }


    private function set_start_times($scheme, $start_interval)
    {
        for ($j=1; $j<=$_SESSION["e_{$this->eventid}"]['rc_numstarts']; $j++)
        {
            $_SESSION["e_{$this->eventid}"]["st_$j"]['startdelay'] =  r_getstartdelay($j, $scheme, $start_interval);
            $_SESSION["e_{$this->eventid}"]["st_$j"]['starttime']  = gmdate("H:i:s",0);
        }
    }


    private function set_fleet_times($status, $time)
    {
        $event = "e_$this->eventid";
        
        for ($i=1; $i <= $_SESSION["$event"]['rc_numfleets']; $i++)    // loop over each fleet
        {
            $start_delay = $_SESSION["$event"]["st_{$_SESSION["$event"]["fl_$i"]['startnum']}"]['startdelay'];
            if ($status === "inprogress")   // timer started
            {                                   
                $fleet_start = $time + $start_delay;
            }
            else                         // timer stopped
            {
                $fleet_start = 0;  
            }
                        
            // set starttime and status in t_racestate
            $rsupdate = array(
                "starttime"  => gmdate("H:i:s",$fleet_start),
                "status"     => $status, 
                "startdelay" => $start_delay,
                "prevstatus" => $_SESSION["$event"]["fl_$i"]['status'],
                "currentlap" => 0
            );
            $update = $this->db->db_update("t_racestate", $rsupdate, array("eventid"=>"$this->eventid", "race"=>"$i"));
            $_SESSION["$event"]["fl_$i"]['status']    = $status;
            $_SESSION["$event"]["fl_$i"]['starttime'] = $fleet_start; 
            $_SESSION["$event"]["fl_$i"]['startdelay'] = $start_delay;
            //u_writedbg("<pre>".print_r($_SESSION["$event"]["fl_$i"],true)."</pre>", __FILE__, __FUNCTION__, __LINE__); // debug:
        }      
    }
    
    
    public function setrecall($startnum, $restarttime)
    {
        $event = "e_$this->eventid";
        $newstartdelay = strtotime($restarttime) - $_SESSION["$event"]['timerstart'];
 
        // update starttime and delay
        $_SESSION["$event"]["st_$startnum"]['startdelay'] = $newstartdelay;
        $_SESSION["$event"]["st_$startnum"]['starttime']  = $_SESSION["$event"]['timerstart'] + $newstartdelay;
        
//        u_writedbg("start reset: $startnum: time = |{$_SESSION["$event"]["st_$startnum"]['starttime']}| delay = |{$_SESSION["$event"]["st_$startnum"]['startdelay']}|", __FILE__, __FUNCTION__, __LINE__);
//        u_writedbg("general recall - start $startnum - restart at ".date("H:i:s", $restarttime), __FILE__, __FUNCTION__, __LINE__);
        
        // update t_racestate  and t_race 
        $update = $this->db->db_update("t_race", array("starttime"=>$restarttime), array("eventid"=>"$this->eventid", "start"=>"$startnum"));
        $update = $this->db->db_update("t_racestate", array("starttime"=>$restarttime, "startdelay"=>$newstartdelay), array("eventid"=>"$this->eventid", "start"=>"$startnum"));
                
        u_writelog("general recall - start $startnum - restart at ".date("H:i:s", $restarttime), $this->eventid);      
    }
}
?>    

