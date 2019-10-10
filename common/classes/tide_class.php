<?php
/*------------------------------------------------------------------------------
** File:		tide_class.php
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


class TIDE
{
    private $db;

    //Method: construct class object
    public function __construct(DB $db)
    {
        $this->db = $db;

    }

    public function tide_count($constraint)
    {
        $where = " 1=1 ";
        if ($constraint) {
            $clause = array();
            foreach ($constraint as $field => $value) {
                $clause[] = "`$field` = '$value'";
            }
            $where = implode(' AND ', $clause);
        }

        $query = "SELECT id FROM t_tide WHERE $where";
        $detail = $this->db->db_get_rows($query);

        return count($detail);
    }

    public function get_tide_by_date($date_str, $local = false)
    {
        $date = date("Y-m-d", strtotime($date_str));
        $query = "SELECT * FROM t_tide WHERE date = '$date'";
        $detail = $this->db->db_get_row($query);

        // $local is for future use (converts to local time)
        // for now assumes ide date is in local time

        return $detail;
    }

    public function get_tides($constraint, $local = false)
    {
        $where = " 1=1 ";
        if ($constraint) {
            $clause = array();
            foreach ($constraint as $field => $value) {
                $clause[] = "`$field` = '$value'";
            }
            $where = implode(' AND ', $clause);
        }

        $query = "SELECT * FROM t_tide WHERE $where ORDER BY date ASC";
        $details = $this->db->db_get_rows($query);

//        if ($local != false) // convert to local time
//        {
//            foreach ($details as $k=>$detail)
//            {
//                $details[$k]['hw1_time'] = $this->convert_to_local_time($detail['date'], $detail['hw1_time'], $local);
//                if (!empty($detail['hw2_time']))
//                {
//                    $details[$k]['hw2_time'] = $this->convert_to_local_time($detail['date'], $detail['hw2_time'], $local);
//                }
//            }
//        }

        return $details;
    }

//    private function convert_to_local_time($date_str, $time_str, $local)
//    {
//        // get start and end of daylight saving time
//        $year = date("Y", strtotime($date_str));
//        $dst_start_date = str_replace("YYYY", $year, $local['start_ref']);
//        $dst_end_date   = str_replace("YYYY", $year, $local['end_ref']);
//        $dst_start = strtotime("$dst_start_date {$local['start_delta']}");
//        $dst_end   = strtotime("$dst_end_date {$local['end_delta']}");
//
//        if (strtotime($date_str) >= $dst_start and strtotime($date_str) <= $dst_end)
//        {
//            $t = (new DateTime($time_str))->add(new DateInterval("PT{$local['time_diff']}H0M"));
//            $time_str = $t->format("H:i");
//        }
//
//        return $time_str;
//    }

    public function set_tide($fields)
    {
        $insert = $this->db->db_insert('t_class', $fields);

        return $insert;
    }

    public function delete_tide($constraint)
    {
        $where = " 1=1 ";
        if ($constraint) {
            $clause = array();
            foreach ($constraint as $field => $value) {
                $clause[] = "`$field` = '$value'";
            }
            $where = implode(' AND ', $clause);
        }
        $status['rows'] = $this->db->db_delete('t_class', $where);
    }
}
