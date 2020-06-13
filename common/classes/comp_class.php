<?php
/*------------------------------------------------------------------------------
** File:		comp_class.php
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


class COMPETITOR
{
    private $db;

    //Method: construct class object
    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    public function get_competitor($id)
    {
        $query = "SELECT * FROM t_competitor WHERE id = $id AND active = 1 ";
        $detail = $this->db->db_get_row($query);
        return $detail;
    }

    public function comp_count($constraint)
    {
        $where = " 1=1 ";
        if ($constraint) {
            $clause = array();
            foreach ($constraint as $field => $value) {
                $clause[] = "`$field` = '$value'";
            }
            $where = implode(' AND ', $clause);
        }

        $query = "SELECT id, classid, sailnum, helm FROM t_competitor WHERE $where AND active = 1 ";
        $detail = $this->db->db_get_rows($query);

        return count($detail);
    }

//    //Method: get competitor detail - just contents of t_competitor
//    public function comp_getcompetitor($id)
//    {
//        $query = "SELECT a.id as id, classid, boatnum, sailnum, classname, acronym, helm as helmname, helm_dob, helm_email,
//                  a.crew as crewname, crew_dob, crew_email, club, nat_py, local_py, personal_py, skill_level, flight, last_entry,
//                  last_event, a.active as active, grouplist, category, b.crew as crew, rig, spinnaker, keel, engine
//                  FROM t_competitor as a
//                  JOIN t_class as b ON a.classid=b.id
//                  WHERE a.id = $id  AND a.active=1";
//        $detail = $this->db->db_get_row($query);
//
//        if (empty($detail)) {
//            $detail = false;
//        }
//        return $detail;
//    }

//    //Method: get competitors from list of ids   FIXME - no usages
//    public function comp_getcompetitorlist($list)
//    {
//        $inlist = implode(",", $list);
//        if (!empty($inlist))
//        {
//            $query = "SELECT a.id as id, classid, boatnum, sailnum, classname, acronym, helm as helmname, helm_dob, helm_email,
//                  a.crew as crewname, crew_dob, crew_email, club, nat_py, local_py, personal_py, skill_level, flight, last_entry,
//                  last_event, a.active as active, grouplist, category, b.crew as crew, rig, spinnaker, keel engine
//                  FROM t_competitor as a
//                  JOIN t_class as b ON a.classid=b.id
//                  WHERE a.id IN ($inlist) AND a.active=1";
//            $detail = $this->db->db_get_rows( $query );
//
//            if (empty($detail))
//            {
//                $detail = false;
//            }
//        }
//        else
//        {
//            return false;
//        }
//        return $detail;
//    }

   public function comp_searchcompetitor($searchstr)
   {
       /* free text search for competitors based on search string containing one or more of class name, helms name and sailnum */

       $words = explode (" ",$searchstr);
       $sailnum = "";
       $class   = "";
       $helm    = "";
       foreach($words as $word)
       {
           $word = trim($word);

           if ($word == "" or ctype_space($word)) // empty or whitespace
           {
               continue;
           }

           if (ctype_digit($word))       // its an integer - could be sailnumber or class
           {
               $result = $this->db->db_get_rows("SELECT id FROM t_class WHERE classname LIKE '$word%'");
               if ($result)               // number string is a known class name
               {
                   $class = $word;
                   continue;
               } else {
                   $sailnum = $word;
                   continue;
               }
           } else {
               $result = $this->db->db_get_rows("SELECT id FROM t_class WHERE classname LIKE '$word%'");
               if ($result)               // string is a known class name
               {
                   $class = $word;
                   continue;
               } else {
                   $result = $this->db->db_get_rows("SELECT id FROM t_competitor WHERE helm LIKE '%$word%'");
                   if ($result)           // string is known name in table containing helm's names
                   {
                       $helm = $word;
                       continue;
                   }
               }
           }

//           if ($sailnum OR $class OR $helm)
//           {
//               break;
//           }
       }

       // construct where clause for query
       $clause = array();
       if ($class) {
           $clause[] = " classname LIKE '$class%' ";
       }
       if ($sailnum) {
           $clause[] = " sailnum LIKE '$sailnum' or boatnum LIKE '$sailnum' ";
       }
       if ($helm) {
           $clause[] = " helm LIKE '%$helm%' ";
       }
       $where = implode(" AND ", $clause);


       if (empty($where)) {
           $result = array();
       } else {
           $query = "SELECT a.id, classname, sailnum, helm, a.crew
                  FROM `t_competitor` as a
                  JOIN t_class as b ON a.classid=b.id
                  WHERE 1=0 OR ( ($where) AND a.active = 1)
                  ORDER BY  classname, sailnum * 1";
//           echo "<pre>".$query."</pre>";
//           exit();
//           u_writedbg($query, __FILE__, __FUNCTION__, __LINE__, false);
           $result = $this->db->db_get_rows($query);
       }

       return $result;
   }


    //Method: search for competitors on supplied constraint
    public function comp_findcompetitor($constraint)
    {
        if ($constraint)
        {
            $clause = array();
            foreach ($constraint as $field => $value) {
                if ($field == "id")
                {
                    $field = "a.`id`";   // need to make sure we are dealing with competitor id
                } else
                    {
                    $field = "`$field`";
                }
                $clause[] = "$field = '$value'";
            }
            $where = implode(' AND ', $clause);
        }
        else
            {
            $where = " 1=1 ";
        }

        $detail = array();
        $query = "SELECT a.id as id, classid, boatnum, sailnum, classname, acronym, helm as helmname, helm_dob, helm_email,
                  a.crew as crewname, crew_dob, crew_email, club, nat_py, local_py, personal_py, skill_level, flight, last_entry,
                  last_event, a.active as active, grouplist, category, b.crew as crew, rig, spinnaker, keel, engine
                  FROM `t_competitor` as a
                  JOIN `t_class` as b ON a.classid=b.id
                  WHERE $where AND a.`active` = 1";
        // echo "<pre>".$query."</pre>";
        $detail = $this->db->db_get_rows($query);
        if (empty($detail)) {
            $detail = false;
        }
        return $detail;
    }

    public function comp_findbysailnum($sailnum)
    {
        $detail = array();
        if ($sailnum) {
            $query = "SELECT *
                      FROM `t_competitor` as a
                      JOIN `t_class` as b ON a.classid=b.id
                      WHERE (sailnum='$sailnum' OR boatnum='$sailnum') AND `active` = 1";
            //echo $query;
            $detail = $this->db->db_get_rows($query);
            if (empty($detail)) {
                $detail = false;
            }
        } else {
            $detail = false;
        }
        return $detail;
    }

    //Method: add competitor
    public function comp_addcompetitor($fields)
    {
        $status = array();

        // check all the mandatory fields are defined
        if ($fields['sailnum'] and !$fields['boatnum']) {
            $fields['boatnum'] = $fields['sailnum'];
        } elseif ($fields['boatnum'] and !$fields['sailnum']) {
            $fields['sailnum'] = $fields['boatnum'];
        }

        if (!$fields['active']) {
            $fields['active'] = "1";
        }

        $fields['club'] = trim(ucwords($fields['club']));
        $fields['club'] = str_ireplace("sailing club", "SC", $fields['club']);
        $fields['club'] = str_ireplace("yacht club", "YC", $fields['club']);

        $fields['helm'] = ucwords($fields['helm']);
        $fields['crew'] = ucwords($fields['crew']);

        if ($fields['classid'] and $fields['helm'] and $fields['boatnum'] and $fields['sailnum'] and $fields['active']) {
            $rs = $this->comp_findcompetitor(array("classid" => $fields['classid'], "sailnum" => $fields['sailnum'], "helm" => $fields['helm']));
            if (!$rs) // OK to add
            {
                $rs = $this->db->db_insert('t_competitor', $fields);       // insert competitor
                if ($rs) {
                    $status['id'] = $this->db->db_lastid();
                    $status['code'] = 0;
                    $status['msg'] = "ok";
                } else {
                    $status['code'] = 1;
                    $status['msg'] = "database insert failed";
                }
            } else {
                $status['code'] = 2;
                $status['msg'] = "competitor already exists";
            }
        }
        return $status;
    }

    //Method: delete competitor
    public function comp_deletecompetitor($id)
    {
        $fields = array("id" => "$id");
        $status['success'] = $this->db->db_delete('t_competitor', $fields);
        $status["success"] ? $status['error'] = "" : $status['error'] = "comp002";
        return $status;
    }


    public function comp_updatecompetitor($id, $fields, $updater = "")
    {
        $status = false;
        empty("$updater") ? $fields['updby'] = "unknown" : $fields['updby'] = $updater;

        if (array_key_exists('club', $fields))
        {
            $fields['club'] = trim(ucwords($fields['club']));
            $fields['club'] = str_ireplace("sailing club", "SC", $fields['club']);
            $fields['club'] = str_ireplace("yacht club", "YC", $fields['club']);
        }

        if (array_key_exists('helm', $fields))
        {
            $fields['helm'] = ucwords($fields['helm']);
        }

        if (array_key_exists('crew', $fields))
        {
            $fields['crew'] = ucwords($fields['crew']);
        }

        $numupdate = $this->db->db_update('t_competitor', $fields, array("id" => $id));
        if ($numupdate >= 1)
        {
            $status = "updated";
        }
        elseif ($numupdate == 0)
        {
            $status = "nochange";
        }
        else
        {
            $status = "failed";
        }
        return $status;
    }

    public function hide_competitor($id)
    {
        $fields['active'] = 0;
        $numupdate = $this->db->db_update('t_competitor', $fields, array("id" => $id));
        if ($numupdate >= 1)
        {
            $status = "updated";
        }
        elseif ($numupdate == 0)
        {
            $status = "nochange";
        }
        else
        {
            $status = "failed";
        }
        return $status;
    }

}


