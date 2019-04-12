<?php
/**
 * dbadmin_lib.php
 * 
 * Library functions for handling database adminstration and maintenance
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 * 
 * Functions:
 *      clear_archive        -  empties archive tables
 *      copy_racetoarchive   -  archives detailed results information to the archive tables
 *      copy_racetoresults   -  copies the race results to the t_result file
 * 
 */
 
 
/**
 * clear_archive()
 * 
 * @param mixed   $db_o        database object
 * @param mixed   $eventid     event id
 * @param integer $fleetnum    optional race no. if only clearing one race
 * @return
 */
function clear_archive($db_o, $eventid, $fleetnum=0)
{
    $constraint = array("eventid"=>$eventid);
    $numrows = $db_o->db_delete("a_finish", $constraint);

    if ($fleetnum!=0)
    {
        $constraint[] = array("race"=>$fleetnum);
    }      

    $numrows = $db_o->db_delete("a_lap", $constraint);  
    $numrows = $db_o->db_delete("a_race", $constraint);

    return $numrows;
}
 
/**
 * copy_racetoarchive()
 * 
 * copies data in t_race, t_lap and t_finish into associated archive tables
 * 
 * @param  mixed $db_o      database object
 * @param  mixed $eventid   event id
 * @param  bool  $pursuit   true if a pursuit race
 * @return bool
 */
function copy_racetoarchive($db_o, $eventid, $pursuit=false)
{
    $status = false;
    
    // first remove any previous archives of this event
    $delete = $db_o->db_delete("a_race",   array("eventid"=>$eventid));
    $delete = $db_o->db_delete("a_lap",    array("eventid"=>$eventid));
    $delete = $db_o->db_delete("a_finish", array("eventid"=>$eventid));
    
    // copy the race data to the archive 
    $query = <<<EOT
          INSERT INTO a_race (eventid, start, fleet, competitorid, helm, crew, club, class, classcode, sailnum, pn, starttime, clicktime, lap, finishlap, etime, ctime, atime, ptime, code, penalty, points, declaration, note, status)
          SELECT eventid, start, fleet, competitorid, helm, crew, club, class, classcode, sailnum, pn, starttime, clicktime, lap, finishlap, etime, ctime, atime, ptime, code, penalty, points, declaration, note, status
          FROM t_race 
          WHERE eventid=$eventid
EOT;
    $copyrace = $db_o->db_query($query);
    
    if ($copyrace)
    {
        // copy the lap data to the archive
        $query = <<<EOT
          INSERT INTO a_lap (eventid, entryid, race, lap, position, etime, ctime, clicktime, status)
          SELECT eventid, entryid, race, lap, position, etime, ctime, clicktime, status 
          FROM t_lap 
          WHERE eventid=$eventid
EOT;
        $copylap = $db_o->db_query($query); 
        
        // if a pursuit race then copy the information in the finish tables
        if ($pursuit)
        {
            $query = <<<EOT
                INSERT INTO a_finish (eventid, entryid, finish1, finish2, finish3, finish4, finish5, finish6, forder, place, status, state)
                SELECT eventid, entryid, finish1, finish2, finish3, finish4, finish5, finish6, forder, place, status, state 
                FROM t_finish 
                WHERE eventid=$eventid
EOT;
            $copyfinish = $db_o->db_query($query); 
        }
    }
    
    if ($copyrace and $copylap)
    {
        if ($pursuit)
        {
            if ($copyfinish)
            {
                $status = true;
            }
        }
        else
        {
            $status = true;
        }           
    }
           
    return $status;
}
        
/**
 * copy_racetoresults()
 * 
 * copies results from t_race into t_result 
 * 
 * @param mixed $db_o      database object
 * @param mixed $eventid   event id
 * @return
 */
function copy_racetoresults($db_o, $eventid)
{
    // first remove any previous copying of this event to the result table
    $delete = $this->db->db_delete("t_result", array("eventid"=>$eventid));
    
    // get data from this event
    $select = $this->db->db_get_rows("SELECT * FROM t_race WHERE `eventid` = $eventid ORDER BY fleet ASC, points ASC");
    
    // build multi-record insert query
    $query = <<<EOT
       INSERT INTO `t_result` (`eventid`, `fleet`, `race_type`, `competitorid`, `class`, `sailnum`, `pn`, `helm`, `crew`, `club`, `lap`, `etime`, `ctime`, `atime`, `code`, `penalty`, `points`, `declaration`, `note`, `updby`) VALUES
EOT;
    foreach($select as $key=>$row)
    {
        $racetype = $_SESSION["e_$eventid"]["fl_{$row['fleet']}"]['scoring'];
        $query.= <<<EOT
        ($eventid, {$row['fleet']}, '$racetype', {$row['competitorid']}, '{$row['class']}', '{$row['sailnum']}', {$row['pn']}, '{$row['helm']}', '{$row['crew']}', '{$row['club']}', {$row['lap']}, {$row['etime']}, {$row['ctime']}, {$row['atime']}, '{$row['code']}', {$row['penalty']}, {$row['points']}, {$row['declaration']}, '{$row['note']}', 'published'),
EOT;
    }
    
    $query = rtrim($query,",").";";
    $insert = $db_o->db_query($query);

    return $insert;
}
?>