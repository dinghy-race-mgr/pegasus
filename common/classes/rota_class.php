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

/*
rota_countmembers()
*/

class ROTA
{
    private $db;

    //Method: construct class object
    public function __construct(DB $db)
    {
        $this->db = $db;
    }

//    public function countmembers($constraint)
//    {
//        $where = " 1=1 ";
//        if ($constraint)
//        {
//            $clause = array();
//            foreach( $constraint as $field => $value )
//            { $clause[] = "`$field` = '$value'"; }
//            $where = implode(' AND ', $clause);
//        }
//
//        $query = "SELECT id  FROM t_rotamember WHERE $where AND active = 1 ";
//        $detail = $this->db->db_get_rows( $query );
//        return count($detail);
//    }

    public function get_rota_members($rota_list, $duplicates = true)
    {
        // returns all members in t_rotamember that match constraint
        // if duplicates parameter is false then it will remove duplicate names

        if (empty($rota_list))    // null where clause
        {
            $where = " 1=1 ";
        }
        else                      // build where clause rota IN (rota1, rota2)
        {
            $where = "rota IN (";
            foreach ($_REQUEST['rotas'] as $rota) { $where .= "'$rota', "; }
            $where = rtrim($where, ", " ) . ") ";
        }

        if ($duplicates)
        {
            $query = "SELECT * FROM t_rotamember WHERE $where AND active = 1 ORDER BY familyname ASC, firstname ASC";
        }
        else
        {
            $query = "SELECT * FROM t_rotamember WHERE $where AND active = 1 GROUP BY familyname, firstname ORDER BY familyname ASC, firstname ASC";
        }
        //echo "<pre>$query</pre>";
        $detail = $this->db->db_get_rows($query);

        if (empty($detail))
        {
            $detail = false;
        }

        return $detail;
    }

    public function get_event_duty($eventid, $dutycode)
    {
        $query = "SELECT * FROM t_eventduty WHERE eventid = $eventid  AND dutycode = '$dutycode' ";
        $duty = $this->db->db_get_rows( $query );
        return $duty;
    }
    public function get_event_duties($eventid, $dutycode = "")
    {
        $duty_codes = $this->db->db_getsystemcodes("rota_type");
        if ($duty_codes)
        {
            $codes = array();
            foreach ($duty_codes as $row) { $codes["{$row['code']}"] = $row['label']; }
        }

        $duties = array();
        $query = "SELECT * FROM t_eventduty WHERE eventid = $eventid ";
        if (!empty($dutycode)) { $query.= " AND dutycode = '$dutycode' "; }

        $duties = $this->db->db_get_rows( $query );

        if (empty($duties))
        {
            $duties = false;
        }
        else
        {
            foreach ($duties as $k=>$duty)
            {
                if (!empty($codes["{$duty['dutycode']}"]))
                {
                    $duties[$k]['dutyname'] = $codes["{$duty['dutycode']}"];
                }
                else
                {
                    $duties[$k]['dutyname'] = "UNKNOWN ({$duty['dutycode']})";
                }
            }
        }
        return $duties;
    }

    public function get_duty_person($eventid, $dutycode)
    {
        $duty_person = "";
        $duties = $this->db->db_get_rows("SELECT * FROM t_eventduty WHERE eventid = $eventid AND dutycode='$dutycode'");

        if (!empty($duties)) {
            $duty_person = $duties[0]['person'];
        }
        return $duty_person;
    }

    public function get_duties_inperiod($fields, $start, $end)
    {
        $where = " 1=1 ";

        // get duty codes
        $rs = $this->db->db_getsystemcodes("rota_type");
        $codes =array();
        foreach($rs as $row) { $codes["{$row['code']}"] = $row['label']; }

        // deal with dates
        $start_date = date("Y-m-d", strtotime($start));
        $end_date = date("Y-m-d", strtotime($end));
        $where.= "AND b.event_date>='$start_date' AND b.event_date<='$end_date' ";

        // deal with other constraints
        $clause = array();
        if (!empty($fields))
        {
            $where.= " AND ";
            foreach($fields as $field => $value ) { $clause[] = "`$field` = '$value'"; }
            $where.= implode(' AND ', $clause);
        }

        $query = "SELECT id, dutycode, person, phone, email, notes, b.event_name, b.event_date FROM t_eventduty as a 
                  JOIN t_event as b ON a.eventid=b.id  
                  WHERE $where ORDER BY event_date ASC, event_order ASC, event_start ASC  ";

        $detail = $this->db->db_get_rows( $query );
        if (empty($detail))       // nothing found
        {
            $detail = false;
        }
        else
        {
            foreach ($detail as $k=>$row)
            {
                $detail[$k]['dutyname'] = $codes[$row['dutycode']];
            }
        }

//        if ($fields['person'] == "Mary Elkington")
//        {
//            echo "<pre>".print_r($detail,true)."</pre>";
//        }

        return $detail;
    }

    public function get_rota_member($fullname, $id = "")
    {
        $fullname= trim($fullname);

        if (!empty($id))
        {
            $where = "id = $id AND active = 1";
        }
        elseif (empty($fullname))
        {
            $where = "";
        }
        else
        {
            $pos = strpos($fullname, " ");
            if ($pos === false)
            {
                $where = "familyname = '$fullname' AND active = 1";
            }
            else
            {
                $first = substr($fullname, 0, $pos);
                $last = substr($fullname, $pos + 1);
                $where = "firstname = '$first' AND familyname = '$last' AND active = 1";
            }
        }
        if ($where)
        {
            $detail = $this->db->db_get_row("SELECT * FROM t_rotamember WHERE $where");
        }
        else
        {
            $detail = false;
        }
        return $detail;
    }

    public function swap_duty($dutyid, $rotaid)
    {
        $status = true;
        if (!empty($eventid) and !empty($rotaid))
        {
            // get rota record data
            $rota = get_rota_member("", $rotaid);
            if ($rota)
            {
                $upd_var = array(
                    "person" => ucfirst(trim($rota['firstname']))." ".ucwords(trim($rota['familyname'])),
                    "phone"  => $rota['phone'],
                    "email"  => $rota['email'],
                    "notes"  => $rota['notw'],
                );
                $upd = $this->db->db_update( "t_eventduty", $upd_var, array("id"=>$dutyid) );
                if ($upd < 1) {$status = false;}
            }
            else
            {
                $status = false;
            }
        }
        else
        {
            $status = false;
        }
        return $status;
    }

}




