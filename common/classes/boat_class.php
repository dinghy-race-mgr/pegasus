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
    
    public function boat_getclasslist()
    {
        $query = "SELECT id, classname FROM t_class WHERE active = 1 ORDER BY classname";
        $detail = $this->db->db_get_rows( $query );

        if (empty($detail))
        {
            return 0;
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

    public function boat_racealloc($db, $class, $eventtype)
    {
        // NOTE this is a rm9 conversion it does not have all the required functionality
        $debug = false;
        // initialise parameters
        $allocation = array('eligible'=>false, 'start'=>0, 'race'=>0);
        
        // get class config details
        $classcfg = $this->boat_getdetail($class);
        if ($debug) { echo "Processing $class<br>"; }
        //echo "<pre>".print_r($classcfg,true)."</pre>";

	    // get fleet details for races in event from database
        $query = "SELECT * FROM `t_cfgfleet` WHERE eventcfgid='$eventtype' ORDER BY defaultfleet ASC, fleet_num ASC";
        //echo "<pre>".$query."</pre>";
        $result = $db->db_get_rows( $query );
    	$count = count($result);

    	if ($count > 0)
    	{					
    	    // loop over each fleet
    		foreach ($result as $racecfg)
    		{
                if ($debug) { echo "Checking fleet:  {$racecfg['fleet_name']}<br>"; }

                $classexc = array_map("trim", explode(",", strtolower($racecfg['classexc'])));
                $classinc = array_map("trim", explode(",", strtolower($racecfg['classinc'])));
                if (in_array($classcfg['classname'], $classexc))  // check for exclusions
    			{
                    if ($debug) { echo "specifically excluded<br>"; }
    			    continue; 	// specifically excluded from this race - continue to next race
    			}
    			else
    			{    			
    				if ($racecfg['onlyinc'])   // only include fleets in classinc
    				{
    					if (in_array($classcfg['classname'], $classinc))
                        {
                            if ($debug) { echo "specifically included opt 1<br>"; }
                            $allocation['eligible'] = true;
    						$allocation['start']    = $racecfg['start_num'];
    						$allocation['race']     = $racecfg['fleet_num'];
    						break;
    					}
    				}
    				else
    				{	
    					if (in_array($classcfg['classname'], $classinc)) // check if class is in included list
                        {
                            if ($debug) { echo "specifically included opt 2<br>"; }
                            $allocation['eligible'] = true;
    						$allocation['start']    = $racecfg['start_num'];
    						$allocation['race']     = $racecfg['fleet_num'];
    						break;
    					}				
    				
    					else  // if not allocated by class name then check other characteristics match
    					{
    						$pyok   = false;
    						$crewok = false;
    						$spinok = false;
    						$hullok = false;
                            if ($racecfg['py_type']=="local")
                            {  $py = $classcfg['local_py']; }
                            else 
                            {  $py = $classcfg['nat_py']; }
    						
                            // PY check  (passes if lies within range)     
    					    if($py>=$racecfg['min_py'] and $py<=$racecfg['max_py'])
    						  {   if ($debug) { echo "matches PY<br>"; }
    						      $pyok = true;   }
    						
                            // crew check (passes if 'any' or correct number)                   
    						if(empty($racecfg['crew']) or strtolower($racecfg['crew'])=="any")
    						  {   if ($debug) { echo "matches any crew<br>"; }
    						      $crewok = true; }
    						else
    						  {
    							if($classcfg['crew']==$racecfg['crew'])
    						      {   if ($debug) { echo "matches specific crew<br>"; }
    						          $crewok = true; }
    						  }
                            
                            // spinnaker type check (passes if 'any' or specified spinnaker type)                      
    						if(empty($racecfg['spintype']) or strtolower($racecfg['spintype'])=="any")
    						  {   if ($debug) { echo "matches any spin<br>"; }
    						      $spinok = true; }
    						else
    						  {
    							if(strtolower($classcfg['spinnaker'])==strtolower($racecfg['spintype']))
    		                      {   if ($debug) { echo "matches specific spin<br>"; }
    		                          $spinok = true;     }
    						  }
          					
                            // hull type check ()passes if 'any' or specified hull type)							
    						if(empty($racecfg['hulltype']) or strtolower($racecfg['hulltype'])=="any")
    						  {   if ($debug) { echo "matches any hull type<br>"; }
    						      $hullok = true;     }
    						else
    						  {   
    						      if(strtolower($classcfg['category'])==strtolower($racecfg['hulltype']))
    							    {   if ($debug) { echo "matches specific hull type<br>"; }
    							        $hullok = true;     }
    						  }
                            
                            // if all checks pass then allocate to this race
                            if ($debug) { echo "matching |$pyok|$crewok|$spinok|$hullok|<br>"; }
    						if ($pyok AND $crewok AND $spinok AND $hullok)
    						{
                                if ($debug) { echo "ALL matches|<br>"; }
    						    $allocation['eligible'] = true;
    							$allocation['start']    = $racecfg['start_num'];
    							$allocation['race']     = $racecfg['fleet_num'];
    							break;
    						}
    					}			
    				}			
    			}
    		}
    	}
        
        return $allocation;
    }
    
}



