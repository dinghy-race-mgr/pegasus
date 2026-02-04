<?php
//error_log("<pre>VALUES".print_r($values,true)."</pre>\n", 3, $_SESSION['dbglog']);
include ("../../common/lib/rm_event_lib.php");

// initialise
$msg = "";
$commit = true;

// set/check simple fields
$values['eid'] = $_SESSION[$strTableName."_masterkey1"];            // event id
$values['updby'] = Security::getUserName();                         // update account
$values['h-club'] = get_club($values['h-club']);                    // helm club
$values['c-club'] = get_club($values['c-club']);                    // crew club
$values['h-name'] = ucwords(strtolower($values['h-name']));         // helm name
$values['c-name'] = ucwords(strtolower($values['c-name']));         // helm age


// check if class has changed
$change_class = true;
if ($mode == "edit" and (strtolower($values['b-class']) == strtolower($oldvalues['b-class']))) { $change_class = false;}

// if class has changed set class name and information for that class
if ($change_class)
{
    $rs = db_query("SELECT * FROM t_class WHERE classname = '{$values['b-class']}' LIMIT 1", $conn);
    $class = db_fetch_array($rs);
    error_log("<pre>CLASS".print_r($class,true)."</pre>\n", 3, $_SESSION['dbglog']);

    $rs = db_query("SELECT * FROM e_event WHERE eid = '{$values['eid']}' LIMIT 1", $conn);
//$rs = DB::Query("select * from e_event where eid = '{$values['eid']}' LIMIT 1");
    $event = db_fetch_array($rs);
    if (!empty($event))
    {
        $values['b-pn'] = $class['nat_py'];               // default to national_py
        if ($event['handicap-type'] == "personal")
        {
            if (!empty($values['e-racemanager']))
            {
                $rs = db_query("SELECT personal_py FROM t_competitor WHERE id = {$values['e-racemanager']} LIMIT 1", $conn);
                $comp = db_fetch_array($rs);
                //$rs = DB::Query("select personal_py from t_competitor where id = {$values['e-racemanager']} LIMIT 1");
                if (!empty($comp['personal_py']))
                { $values['b-personalpn'] = $comp['personal_py']; }
                else
                { $msg.="- could not set personal handicap for entered boat - set to RYA value</br>"; }
            }
            else
            {  $msg.="- could not set personal handicap for entered boat - set to RYA value</br>"; }
        }
        elseif ($event['handicap-type'] == "local")
        { $values['b-pn'] = $class['local_py']; }
        else
        { $values['b-pn'] = $class['nat_py']; }
    }
    else
    { $msg.="- could not set event handicap for entered boat - please add manually</br>"; }

}

// check if competitor is known to racemanager - first on class, sailnum and helm name
$names = explode(" ", $values['h-name']);
$surname = $names[1];
$competitorid = 0;

$rs = db_query("SELECT id FROM t_competitor WHERE classid = {$class['id']} AND sailnum = {$values['b-sailno']} AND 
                 (helm LIKE '%{$values['h-name']}%' OR helm LIKE '%$surname%') ORDER BY createdate DESC LIMIT 1", $conn);
error_log("<pre>TRY 1".print_r($rs,true)."</pre>\n", 3, $_SESSION['dbglog']);

//    $rs = DB::Query("select id from t_competitor WHERE classid = {$class['id']} and sailnum = {$values['b-sailno']} and
//                     (helm LIKE '%{$values['h-name']}%' or helm LIKE '%$surname%') ORDER BY createdate DESC LIMIT 1");
//error_log("<pre>TRY 1".print_r($rs,true)."</pre>\n", 3, $_SESSION['dbglog']);
if (empty($rs))
{
    // try on just class and helm
    $rs = db_query("SELECT id from t_competitor WHERE classid = {$class['id']} and
                    (helm LIKE '%{$values['h-name']}%' or helm LIKE '%$surname%') ORDER BY createdate DESC LIMIT 1", $conn);
error_log("<pre>TRY 2".print_r($rs,true)."</pre>\n", 3, $_SESSION['dbglog']);
//        $rs = DB::Query("select id from t_competitor WHERE classid = {$class['id']} and sailnum = {$values['b-sailno']} and
//                     (helm LIKE '%{$values['h-name']}%' or helm LIKE '%$surname%') ORDER BY createdate DESC LIMIT 1");

    if (!empty($rs)) { $values['e-racemanager'] = $rs; }
}
else
{
    $values['e-racemanager'] = $rs;
}
error_log("<pre>competitorid = {$entry['e-racemanager']}</pre>\n", 3, $_SESSION['dbglog']);

// check we have at least one emergency contact
if (empty($values['h-emergency']) and empty($values['c-emergency']))
{
    $msg.="- must have at least one emergency contact phone number for helm or crew</br>";
    $commit = false;
}

// warning if division field is blank but class has division's defined
if (empty($values['h-division']))
{
    // check if class has divisions defined
    if (!empty($class['fleets']))
    {
        $msg.="- WARNING - this class has fleet divisions defined ({$class['fleets']}) - do you need to specify one?</br>";
    }
}

//------------------------------------------------------------------------------------------------------------------------------

// processing for add mode only
if( $mode == "add" )
{
    // set guid for future updates
    $values['e-guid'] = get_guid();

// get entry sequence no.
    $rs = db_query("SELECT MAX(`e-entryno`) as 'numentries' FROM e_entry WHERE `eid` = {$values['eid']}", $conn);
    $entries = db_fetch_array($rs);
//    $rs = DB::Query("SELECT MAX(`e-entryno`) FROM e_entry WHERE `eid` = {$values['eid']}");
//$max_id = $db_o->run("SELECT MAX(`e-entryno`) FROM e_entry WHERE `eid` = {$values['eid']}", array($eid) )->fetchColumn();
    $values['e-entryno'] = $entries['numentries'] + 1;

// determine if entry will be on waiting list
    $values['e-waiting'] = 0;
    if ($event['entry-limit'] > 0)
    {
        // get no. of current entries in this event
        $rs = db_query("SELECT COUNT(*) as count FROM e_entry WHERE eid = {$values['eid']} and `e-exclude` = 0 GROUP BY eid", $conn);
        //$rs = DB::Query("SELECT COUNT(*) as count FROM e_entry WHERE eid = {$values['eid']} and `e-exclude` = 0 GROUP BY eid");
        //   $numentries = $db_o->run("SELECT COUNT(*) as count FROM e_entry WHERE eid = ? and `e-exclude` = 0 GROUP BY eid", array($eid) )->fetchColumn();
        $chk = db_fetch_array($rs);
        $chk('count' >= $event['entry-limit']) ? $entry['e-waiting'] = 1 : $entry['e-waiting'] = 0;
    }
}
error_log("<pre>OUTPUT".print_r($values,true)."</pre>\n", 3, $_SESSION['dbglog']);

// field checks complete
if ($commit)
{
    $values['updby']      = $_SESSION['UserID'];
    $values['upddate']    = NOW();
    $message = "<span style=\"white-space: normal\">ENTRY ADDED:<br>$msg</span>";;
}
else
{
    $message = "<span style=\"white-space: normal\">NOTICE ISSUES:<br>$msg </span>";
}





