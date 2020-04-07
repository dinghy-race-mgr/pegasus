<?php
/**
 *  CRUISE class
 *
 *  Handles cruise records - interacts with t_cruise
 *
 *  METHODS
 *     __construct
 *
 *  T_CRUISE
 *
 *     add_cruiser     - adds/updates cruiser record
 *     end_cruiser     - records time ashore for cruiser record
 *     get_cruiser     - get cruise record for boat and cruise type
 *
 **/


class CRUISE
{
    private $db;

    //Method: construct class object
    public function __construct(DB $db, $today)
    {
        $this->db = $db;
        $this->today = $today;
    }


    public function add_cruiser($cruise_type, $boatid, $helm, $crew, $sailnum)
        /*
         * Adds entry record to t_cruise
         * $helm, $crew and/or $sailnum only set if temporary change required
         */
    {
        $status = "fail";
        if (empty($boatid) OR !is_numeric($boatid))  // check we have a competitor id - if not return error
        {
            $status =  false;
        }
        else
        {
            $chk_cruise = $this->get_cruiser($cruise_type, $boatid);

            if (!$chk_cruise)
            {
                $action_type = "register";
            }
            else
            {
                $action_type = "update";
                $delete_rs = $this->db->db_delete( "t_cruise", $where = array("id" => $chk_cruise[0]['id'] ) );
            }

            $fields = array(
                "cruise_type"    => $cruise_type,
                "cruise_date"    => $this->today,
                "time_in"        => date("H:i"),
                "boatid"         => $boatid,
                "action"         => $action_type,
                "change_helm"    => $helm,            // only required for temp change
                "change_crew"    => $crew,            // only required for temp change
                "change_sailnum" => $sailnum,         // only required for temp change
                "updby"          => "rm_sailor"
            );

            $insert_rs = $this->db->db_insert("t_cruise", $fields);
            if ($insert_rs) { $status = $action_type; }
        }
        return $status;
    }


    public function end_cruiser($personid, $type)
    {
        $detail = array();
        $set = array("time_out" => date("H:i"), "action" => "declare");
        $where = array("cruise_date" => $this->today, "boatid" => $personid, "cruise_type" => $type);
        $num_records = $this->db->db_update( "t_cruise", $set, $where, 1 );
        $num_records > 0 ? $status = true : $status = false;
        return $status;
    }

    public function get_cruiser($cruise_type, $boatid)
    {
        $detail = array();
        $query = "SELECT * FROM `t_cruise` WHERE cruise_date = '{$this->today}' AND cruise_type = '$cruise_type' 
                  AND boatid = '$boatid' ORDER BY upddate ASC";
        //echo "<pre>$query</pre>";
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


}