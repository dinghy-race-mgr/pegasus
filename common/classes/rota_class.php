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

    public function rota_countmembers($constraint)
    {
        $where = " 1=1 ";
        if ($constraint)
        {
            $clause = array();
            foreach( $constraint as $field => $value )
            { $clause[] = "`$field` = '$value'"; }
            $where = implode(' AND ', $clause);
        }

        $query = "SELECT id  FROM t_rotamember WHERE $where AND active = 1 ";
        $detail = $this->db->db_get_rows( $query );
        return count($detail);
    }

    
}

    

?>
