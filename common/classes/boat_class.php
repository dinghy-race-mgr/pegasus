<?php
/*------------------------------------------------------------------------------
** File:		boat_class.php
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


class BOAT
{
    private $db;
    
    //Method: construct class object
    public function __construct(DB $db)
	{
	    $this->db = $db;
	}

    public function boat_count($constraint)
    {
        $where = " 1=1 ";
        if ($constraint)
        {
            $clause = array();
            foreach( $constraint as $field => $value )
            { $clause[] = "`$field` = '$value'"; }
            $where = implode(' AND ', $clause);
        }

        $query = "SELECT id FROM t_class WHERE $where AND active = 1 ";
        $detail = $this->db->db_get_rows( $query );

        return count($detail);
    }
    
    public function boat_getclasscodes()
    {
        $classcodes = array();
        
        $classcodes['category']  = $this->db->db_getsystemcodes("class_category");
        $classcodes['crew']      = $this->db->db_getsystemcodes("class_crew");
        $classcodes['rig']       = $this->db->db_getsystemcodes("class_rig");
        $classcodes['spinnaker'] = $this->db->db_getsystemcodes("class_spinnaker");
        $classcodes['keel']      = $this->db->db_getsystemcodes("class_keel");
        $classcodes['engine']    = $this->db->db_getsystemcodes("class_engine");
        
        return $classcodes;
    }
    
    public function boat_getclasslist($popular=false)
    {
        $popular ? $order = "popular DESC, classname ASC" : $order = "classname ASC";

        $query = "SELECT id, classname FROM t_class WHERE active = 1 ORDER BY $order";
        $detail = $this->db->db_get_rows( $query );

        if (empty($detail))
        {
            return array();
        }
        else
        {
            $rows = array();
            foreach ($detail as $row)
            {
                $rows["{$row['id']}"] = $row['classname'];
            }
        }
        return $rows;
    }
    
    public function getclasses($sort="")
    {
        $query = "SELECT * FROM t_class WHERE 1=1 and active = 1";

        if (!empty($sort))
        {
            $query.= " ORDER BY $sort";
        }

        $detail = $this->db->db_get_rows( $query );
        if (empty($detail)) { $detail = false;}
        return $detail;
    }

//    public function get_mergeclasses($merge_str)
//        //  FIXME THIS IS A DUPLICATE OF FUNCTION IN SERIESRESULT.PHP #575
//    {
//        /* 2d array of groups of classes to be merged
//           t_series merge field: laser,laser 4.7,laser radial|rs100 8.4,rs100 10.2
//              $merge_classes = array(
//                    "1" => array ("laser", "laser 4.7", "laser radial")
//                    "2" => array ("rs100 8.4", "rs100 10.2")
//                    )
//       */
//        $merge = array();
//        $i = 0;
//        $data = explode("|", $merge_str);
//        foreach ($data as $list)
//        {
//            if (!empty($list))
//            {
//                $i++;
//                $items = explode(",", $list);
//                if (count($items) > 1)
//                {
//                    $merge[$i] = array();
//                    foreach ($items as $class)
//                    {
//                        $merge[$i][] = strtolower(trim($class));
//                    }
//                }
//            }
//        }
//        return $merge;
//    }
//


    public function boat_getdetail($classname, $classid=0)
	{
	    $detail = array();
        if ($classid != 0)               // id specified - use that
        {
            $query = "SELECT * FROM t_class WHERE id = $classid and active = 1";
        }
        elseif (!empty($classname))
        {
            $query = "SELECT * FROM t_class WHERE classname LIKE '$classname' and active = 1";
        }
        else
        {
            return false;
        }

        //echo "get boat query: ".$query."<br>";
        $detail = $this->db->db_get_row( $query );
        if (empty($detail)) { $detail = false;}
        return $detail;       
	}
    
    
    public function boat_getclassname($classid)
    {
        $query = "SELECT classname FROM t_class WHERE id = $classid and active = 1";
        $row = $this->db->db_get_row($query);
        if ($row) {
            $classname = $row['classname'];
        } else {
            $classname = false;
        }
        return $classname;       
    }
	
    
    public function boat_classexists($classname, $active=true)
    {
        $exists = false;
        if ($active)
        {
            $query = "SELECT * FROM t_class WHERE classname LIKE '$classname' and active = 1 ";
        }
        else
        {
            $query = "SELECT * FROM t_class WHERE classname LIKE '$classname'";
        }
        $rs = $this->db->db_get_row($query);
        if ($rs)
        { 
            return $rs;
        }
        return $exists;       
    }
    

    public function boat_addclass($fields)
    {
        $status = array();

        $missing = "";
        foreach ($fields as $key => $value) {
            $value = trim($value);
            if (empty($value)) { $missing .= "$key, "; }
        }
        $missing = rtrim($missing, ", ");
        
        // check for missing mandatory fields        
        if (!empty($missing))
        {
            $status['msg'] = "missing field(s) [$missing]";
        }
        else
        {
            // check if class already exists
            $exists = $this->boat_classexists($fields['classname']);
            if ($exists)
            {
                $status['msg'] = "class already exists";
            }
            else // class doesn't exist - so check that it is OK to enter it
            {
                // check if all codes are valid
                $valid = array();
                $valid['category']  = $this->db->db_checksystemcode("class_category", $fields['category']);
                $valid['crew']      = $this->db->db_checksystemcode("class_crew", $fields['crew']);
                $valid['rig']       = $this->db->db_checksystemcode("class_rig", $fields['rig']);
                $valid['spinnaker'] = $this->db->db_checksystemcode("class_spinnaker", $fields['spinnaker']);
                $valid['engine']    = $this->db->db_checksystemcode("class_engine", $fields['engine']);
                $valid['keel']      = $this->db->db_checksystemcode("class_keel", $fields['keel']);
                
                if (in_array(false, $valid, true) === false)  // all valid - ok to add class
                {
                    // make string fields database friendly
                    $fields['classname'] = ucwords($fields['classname']);

                    // create acronym if not provided
                    if (empty($fields['acronym'])) {
                        $fields['acronym'] = strtoupper(substr($fields['classname'], 0, 6));  // take first 6 chars
                    }

                    // create rya_id if not provided
                    if (empty($fields['rya_id'])) {
                        $fields['rya_id'] = strtolower(substr($fields['classname'], 0, 3)).$fields['crew'].$fields['rig'].$fields['spinnaker'];
                    }
                    
                    // create local PN if not provided
                    if (empty($fields['local_py'])) {
                        $fields['local_py'] = $fields['nat_py'];  // make same as national py
                    }
                
                    // insert class record
                    $insert = $this->db->db_insert( 't_class', $fields );
                    if ($insert)
                    {
                        $status['msg'] = "ok";
                        $status['id'] = $this->db->db_lastid();
                    }
                    else
                    {
                        $status['msg'] = "database insert failed";
                    }
                }
                else  // code not valid
                {
                    $invalid = "";
                    foreach ($valid as $key => $value) {
                        if (!$value) { $invalid .= "$key, "; }
                    }
                    $invalid = rtrim($invalid, ", ");

                    $status['msg'] = "category code(s) invalid [$invalid]";
                }
            }
        }         
        return $status;       
    }
    
    
	// Method: delete class
    public function boat_deleteclass($db, $classname)
	{
        if (!empty($classname))
        {
            $where = array("classname"=>"$classname", "active" => "1");
            $status['rows'] = $db->db_delete( 't_class', $where );
            if ($status['rows']>0)
            {
               $status['success'] = true;
               $status['error'] = "";
            }
            else
            {
               $status = array("success"=>false, "error"=>"boat004", "rows"=>0); 
            }
        }
        else
        {
            $status = array("success"=>false, "error"=>"boat001", "rows"=>0);
        }
        return $status;      
	}
    
    
    // Method:  update class configuration
    public function boat_updateclasscfg($db, $classname, $category, $numcrew, $rig, $spin, $engine, $keel)
	{
        if (!empty($classname))
        {
            $variables = array();
            if (!empty($category)){ $variables['category'] = $category; }
            if (!empty($numcrew)) { $variables['numcrew'] = $numcrew; }
            if (!empty($rig))     { $variables['rig'] = $rig; }
            if (!empty($spin))    { $variables['spin'] = $spin; }    
            if (!empty($engine))  { $variables['engine'] = $engine; }
            if (!empty($keel))    { $variables['keel'] = $keel; } 
                 
            $where = array("classname"=>"$classname", "active" => "1");
            $status['rows'] = $db->db_update( 't_class', $variables, $where );
            if ($status['rows']>0)
            {
               $status['success'] = true;
               $status['error'] = "";
            }
            else
            {
                $status = array("success"=>false, "error"=>"boat005", "rows"=>0);
            }
        }
        else
        {
            $status = array("success"=>false, "error"=>"boat001", "rows"=>0);
        }
            
        return $status;
           
	}

    
    public function boat_updatepy($db, $classname, $nat_py, $local_py)
    {
        $variables = array();
        
        if (!empty($nat_py) AND !empty($local_py) ) {$both = true;}
        
        // check that at least one PN has been provided
        if (!empty($nat_py) OR !empty($local_py) )
        {
            if (!empty($nat_py))   // national PN provided 
            {
                // check integer and in range
                if (( !is_int($nat_py) ? (ctype_digit($nat_py)) : true ) AND $nat_py > 400 AND $nat_py < 2000 )
                {
                    $variables['nat_py'] = $nat_py;
                }                
            }
            if (!empty($local_py))  // local PN provided
            {
                // check integer and in range
                if (( !is_int($local_py) ? (ctype_digit($local_py)) : true ) AND $local_py > 400 AND $local_py < 2000 )
                {
                    $variables['local_py'] = $local_py;
                }  
            }
            
            if ($both AND (empty($variables['local_py']) OR empty($variables['nat_py'])))
            
            {
                // both have to be good if both are provided - don't send
                $status = array("success"=>false, "error"=>"boat006", "rows"=>0);
            }
            else
            {
                // update record
                $where = array("classname"=>"$classname", "active"=>"1");
                $status['rows']  = $db->db_update( 't_class', $variables, $where );
                if ($status['rows']>0)
                {
                   $status['success'] = true;
                   $status['error'] = "";
                }
                else
                {
                    $status = array("success"=>false, "error"=>"boat006", "rows"=>0);
                }
            }           
        }
        else
        {
            $status = array("success"=>false, "error"=>"boat006", "rows"=>0);
        }
        return $status;
    }

}



