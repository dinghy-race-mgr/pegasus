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

    public function get_tide($date_str)
    {
        $query = "SELECT * FROM t_tide WHERE date = '$date_str'";
        $detail = $this->db->db_get_row($query);

        return $detail;
    }

    public function get_tides($constraint)
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
        $detail = $this->db->db_get_rows($query);

        return $detail;
    }

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
