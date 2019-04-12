<?php
/**
 *  RACESTATE class
 *
 *  Handles interactions with t_racestate and associated $_SESSION variables
 *
 *  METHODS
 *     __construct
 *
 *
*/

// FIXME - doesn't deal with entry having a different helm
// FIXME - fleet allocation should be in this class

class RACESTATE
{
    private $db;

    //Method: construct class object
    public function __construct(DB $db, $eventid)
    {
        $this->db = $db;
        $this->eventid = $eventid;
    }

    public function racestate_delete($fleetnum=0)
    {
        $constraint = array("eventid"=>$this->eventid);
        if ($fleetnum != 0) { $constraint[] = array("race"=>$fleetnum); }

        $numrows = $this->db->db_delete("t_racestate", $constraint);

        return $numrows;
    }


    public function racestate_get($fleetnum=0)
    {
        $racestates = array();

        $where = "eventid = ".$this->eventid;
        if ($fleetnum != 0) { $where.= " AND race = $fleetnum"; }

        $query = "SELECT * FROM t_racestate WHERE $where order by race";
        $result = $this->db->db_get_rows($query);

        if ($result)
        {
            foreach ($result as $row) { $racestates[$row['race']] = $row; }
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


    public function racestate_updateentries($fleetnum, $change)
    {
        $result = $this->db->db_query("UPDATE t_racestate SET entries = entries $change WHERE eventid = {$this->eventid} and race = $fleetnum");
        // FIXME - not a good idea to set session inn common class
        $_SESSION["e_{$this->eventid}"]['result_status'] = "invalid";

        return $result;
    }

    public function update_entries($fleetnum, $change)
    {
        $change >= 0 ? $set = "entries + $change" : $set = "entries - $change";
        $result = $this->db->db_query("UPDATE t_racestate SET entries = $set WHERE eventid = {$this->eventid} and race = $fleetnum");
        // FIXME - not a good idea to set session inn common class
        $_SESSION["e_{$this->eventid}"]["fl_$fleetnum"]['entries'] = $_SESSION["e_{$this->eventid}"]["fl_$fleetnum"]['entries'] + $change;
        $_SESSION["e_{$this->eventid}"]['result_status'] = "invalid";

        return $result;
    }
 }