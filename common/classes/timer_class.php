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

    
    public function start($starttime, $adjust = false)
    {
        $_SESSION["e_$this->eventid"]['timerstart'] = $starttime;

        // set timerstart in event
        $this->db->db_update( 't_event', array("timerstart"=>$starttime), array("id"=>$this->eventid) );

        // set start times in session
        $this->set_start_times("start", $starttime, $_SESSION["e_{$this->eventid}"]['rc_startscheme'], $_SESSION["e_{$this->eventid}"]['rc_startint']);

        // set start times in t_racestate
        $this->set_fleet_times("inprogress", $starttime);

        if ($adjust)
        {
            u_writelog("timer - adjusted start time to ".gmdate("H:i:s",$starttime), $this->eventid);
        }
        else
        {
            u_writelog("timer started at ".gmdate("H:i:s",$starttime), $this->eventid);
        }
    }

    
    public function stop($stoptime)
    {
        $event = "e_$this->eventid";
        $_SESSION["$event"]['timerstart'] = 0;

        // initialise timerstart in t_event
        $this->db->db_update( 't_event', array("timerstart"=>0), array("id"=>$this->eventid) );

        // reset time delays for each start and each fleet based on race format definition (in case where they have been
        // rest as a result of a general recall or an adjusttimer action
        $this->reset_start_delays();

        // initialise start times in session
        $this->set_start_times("stop", 0, $_SESSION["e_{$this->eventid}"]['rc_startscheme'], $_SESSION["e_{$this->eventid}"]['rc_startint']);

        // initialise start times in t_racestate
        $this->set_fleet_times("notstarted", $stoptime);

        u_writelog("timer stopped at ".gmdate("H:i:s",$stoptime), $this->eventid);
    }

    private function reset_start_delays()
    {
        // sets startdelay information for each fleet (and start) back to values specified in the race format configuration
        for ($i=1; $i<=$_SESSION["e_{$this->eventid}"]['rc_numfleets']; $i++)
        {
            $start_num = $_SESSION["e_{$this->eventid}"]["fl_$i"]['startnum'];
            $start_delay = r_getstartdelay($start_num, $_SESSION["e_{$this->eventid}"]['rc_startscheme'], $_SESSION["e_{$this->eventid}"]['rc_startint']);
            $_SESSION["e_{$this->eventid}"]["st_$start_num"]['startdelay'] = $start_delay;
            $_SESSION["e_{$this->eventid}"]["fl_$i"]['startdelay'] = $start_delay;
        }
    }

    private function set_start_times($status, $time, $scheme, $start_interval)
    {
        for ($j=1; $j<=$_SESSION["e_{$this->eventid}"]['rc_numstarts']; $j++)
        {
            // set start clock time in hh:mm:ss
            if ($status === "start")   // timer started
            {
                $_SESSION["e_{$this->eventid}"]["st_$j"]['starttime'] = gmdate("H:i:s", $time + $_SESSION["e_{$this->eventid}"]["st_$j"]['startdelay']);
            }
            else                       // timer stopped
            {
                $_SESSION["e_{$this->eventid}"]["st_$j"]['starttime'] = 0;
            }
        }
    }


    private function set_fleet_times($status, $time)
    {
        $event = "e_$this->eventid";
        
        for ($i=1; $i <= $_SESSION["$event"]['rc_numfleets']; $i++)    // loop over each fleet
        {
            $start_delay = $_SESSION["$event"]["st_{$_SESSION["$event"]["fl_$i"]['startnum']}"]['startdelay'];

            // set actual start time depending on timer status (started | stopped)
            $status === "inprogress" ?  $fleet_start = $time + $start_delay : $fleet_start = 0;

            // set starttime and status in t_racestate
            $rsupdate = array(
                "starttime"  => gmdate("H:i:s",$fleet_start),
                "status"     => $status, 
                "startdelay" => $start_delay,
                "prevstatus" => $_SESSION["$event"]["fl_$i"]['status'],
                "currentlap" => 0
            );

            $update = $this->db->db_update("t_racestate", $rsupdate, array("eventid"=>"$this->eventid", "fleet"=>"$i"));
            $_SESSION["$event"]["fl_$i"]['status']     = $status;
            $_SESSION["$event"]["fl_$i"]['starttime']  = $fleet_start;
            $_SESSION["$event"]["fl_$i"]['startdelay'] = $start_delay;

        }      
    }
    
    
    public function setrecall($startnum, $restarttime)
    {
        $event = "e_{$this->eventid}";
        $newstartdelay = strtotime($restarttime) - $_SESSION["e_{$this->eventid}"]['timerstart'];

        // update starttime and delay
        $_SESSION["e_{$this->eventid}"]["st_$startnum"]['startdelay'] = $newstartdelay;
        $_SESSION["e_{$this->eventid}"]["st_$startnum"]['starttime']  = date("H:i:s", $_SESSION["e_{$this->eventid}"]['timerstart'] + $newstartdelay);

        for ($i=1; $i<=$_SESSION["e_{$this->eventid}"]['rc_numfleets']; $i++)
        {
            if ($_SESSION["e_{$this->eventid}"]["fl_$i"]['startnum'] == $startnum)
            {
                $_SESSION["e_{$this->eventid}"]["fl_$i"]['startdelay'] = $newstartdelay;
                $_SESSION["e_{$this->eventid}"]["fl_$i"]['starttime'] =  $_SESSION["e_{$this->eventid}"]['timerstart'] + $newstartdelay;
            }
        }
        
        // update t_racestate
        //$update = $this->db->db_update("t_race", array("starttime"=>$restarttime), array("eventid"=>"$this->eventid", "start"=>"$startnum"));
        $update = $this->db->db_update("t_racestate", array("starttime"=>$restarttime, "startdelay"=>$newstartdelay), array("eventid"=>"$this->eventid", "start"=>"$startnum"));
                
        u_writelog("general recall - start $startnum - restart at ".gmdate("H:i:s", strtotime($restarttime)), $this->eventid);
    }
}


