<?php
/**
 *  RACESTATE class
 *
 *  Handles interactions with t_racestate
 *
 *
*/

class RACESTATE
{
    private $db;

    //Method: construct class object
    public function __construct(DB $db, $eventid)
    {
        $this->db = $db;
        $this->eventid = $eventid;
    }

//    public function racestate_delete($fleetnum = 0)
//    {
//        $constraint = array("eventid" => $this->eventid);
//        if ($fleetnum != 0) {
//            $constraint[] = array("fleet" => $fleetnum);
//        }
//
//        $numrows = $this->db->db_delete("t_racestate", $constraint);
//
//        return $numrows;
//    }


//    public function racestate_get($fleetnum = 0)
//    {
//        $racestates = array();
//
//        $where = "eventid = " . $this->eventid;
//        if ($fleetnum != 0) {
//            $where .= " AND fleet = $fleetnum";
//        }
//
//        $query = "SELECT * FROM t_racestate WHERE $where order by fleet";
//        $result = $this->db->db_get_rows($query);
//
//        if ($result) {
//            foreach ($result as $row) {
//                $racestates[$row['fleet']] = $row;
//            }
//        }
//
//        return $racestates;
//    }


//    public function racestate_update($update, $constraint)
//    {
//        $constraint['eventid'] = $this->eventid;
//        u_writedbg("<pre>" . print_r($update, true) . "<br>" . print_r($constraint, true) . "</pre>", __FILE__, __FUNCTION__, __LINE__);
//        $result = $this->db->db_update("t_racestate", $update, $constraint);
//
//        return $result;
//    }


//    public function update_entries($fleetnum, $change)
//    {
//        $change >= 0 ? $set = "entries + $change" : $set = "entries - $change";
//        $result = $this->db->db_query("UPDATE t_racestate SET entries = $set WHERE eventid = {$this->eventid} and fleet = $fleetnum");
//
//        return $result;
//    }

//    public function racestate_analyse($fleetnum, $starttime, $update = false)
//    {
//        $status_counts = array("R" => 0, "F" => 0, "X" => 0);
//        $race = $this->race_getresults($fleetnum);  // get race data for this fleet
//
//        foreach ($race as $entry)
//        {
//            $status_counts["{$entry['status']}"]++;
//        }
//
//        $status = "unknown";
//        if ($race)
//        {
//            if ($starttime == "00:00:00") {
//                $status = "notstarted";
//            } elseif ($status_counts['R'] == 0) {
//                $status = "allfinished";
//            } elseif ($status_counts['R'] > 0 and $status_counts['F'] > 0) {
//                $status = "finishing";
//            } else {
//                $status = "inprogress";
//            }
//        }
//
//        return $status;
//    }

}