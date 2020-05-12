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


    public function add_cruise($cruise_type, $sailor)
        /*
         * Adds entry record to t_cruise
         * incorporates configured 'change' fields for events on this day
         */
    {
        $status = "fail";
        if (empty($sailor['id']) OR !is_numeric($sailor['id']))  // if no sailor id - return error
        {
            $status = false;

        } else {                                                 // we have sailor
            // check if they have all registered for this event
            $chk_cruise = $this->get_cruise($cruise_type, $sailor['id']);
            if (!$chk_cruise) {
                $action_type = "register";
            } else {
                $action_type = "update";
                // delete existing record
                $rs = $this->db->db_delete("t_cruise", $where = array("id" => $chk_cruise['id']));
            }

            // fixed fields
            $fields = array(
                "cruise_type" => $cruise_type,
                "cruise_date" => $this->today,
                "time_in" => date("H:i"),
                "boatid" => $sailor['id'],
                "action" => $action_type,
                "updby" => "rm_sailor"
            );

            // change fields
            foreach ($_SESSION['change_fm'] as $field => $spec) {
                if ($spec['status']) {
                    $fields[$field] = $sailor[$field];
                }
            }

            $insert_rs = $this->db->db_insert("t_cruise", $fields);
            if ($insert_rs) {
                $status = $action_type;
            }
        }
        return $status;
    }


    public function end_cruise($boatid, $type)
    {
        $detail = array();
        $set = array("time_out" => date("H:i"), "action" => "declare");
        $where = array("cruise_date" => $this->today, "boatid" => $boatid, "cruise_type" => $type);
        $num_records = $this->db->db_update( "t_cruise", $set, $where, 1 );
        $num_records > 0 ? $status = true : $status = false;
        return $status;
    }

    public function get_cruise($cruise_type, $boatid)
    {
        $detail = array();
        $query = "SELECT * FROM `t_cruise` WHERE cruise_date = '{$this->today}' AND cruise_type = '$cruise_type' AND boatid = '$boatid'";
        $detail = $this->db->db_get_row($query);

        if (empty($detail)) {
            return false;
        } else {
            return $detail;
        }
    }

    public function get_cruises($boatid)
    {
        $detail = array();
        $query = "SELECT * FROM `t_cruise` WHERE cruise_date = '{$this->today}' AND boatid = '$boatid' ORDER BY upddate DESC";
        $detail = $this->db->db_get_rows($query);

        if (empty($detail)) {
            return false;
        } else {
            return $detail;
        }
    }

    public function get_latest_changes($boatid)
    {
        $detail = array();
        $query = "SELECT * FROM `t_cruise` WHERE cruise_date = '{$this->today}' AND boatid = '$boatid' ORDER BY upddate DESC LIMIT 1";
        $detail = $this->db->db_get_row($query);

        if (empty($detail)) {
            return false;
        } else {
            return $detail;
        }
    }


}