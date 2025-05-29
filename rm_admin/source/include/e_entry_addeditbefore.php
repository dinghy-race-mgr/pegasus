<?php
error_log("<pre>VALUES".print_r($values,true)."</pre>\n", 3, $_SESSION['dbglog']);
include ("../../common/lib/rm_event_lib.php");

// initialise
$msg = "";
$commit = true;

// set eventid
$values['eid'] = $_SESSION[$strTableName."_masterkey1"];

// set class name [note:  this routine is only a stub at the moment - doesn't handle PY limits]
$values['b-class'] = get_class_name($values['b-class']);

// set updby
$values['updby'] = Security::getUserName();

// check if class is known to racemanager
$rs = DB::Query("select * from t_class where classname = '{$values['b-class']}' LIMIT 1 ");
if ($rs)
{
    $class = $rs->fetchAssoc();
}
error_log("<pre>CLASS".print_r($class,true)."</pre>\n", 3, $_SESSION['dbglog']);

// check if competitor is known to racemanager - first on class, sailnum and helm name
$names = explode(" ", $values['h-name']);
$surname = $names[1];
$competitorid = 0;
$rs = DB::Query("select id from t_competitor WHERE classid = {$class['id']} and sailnum = {$values['b-sailno']} and 
                     (helm LIKE '%{$values['h-name']}%' or helm LIKE '%$surname%') ORDER BY createdate DESC LIMIT 1");
error_log("<pre>TRY 1".print_r($rs,true)."</pre>\n", 3, $_SESSION['dbglog']);
if (empty($rs))
{
    // try on just class and helm
    $rs = DB::Query("select id from t_competitor WHERE classid = {$class['id']} and sailnum = {$values['b-sailno']} and
                     (helm LIKE '%{$values['h-name']}%' or helm LIKE '%$surname%') ORDER BY createdate DESC LIMIT 1");
    error_log("<pre>TRY 2".print_r($rs,true)."</pre>\n", 3, $_SESSION['dbglog']);
    if (!empty($rs))
    {
        $values['e-racemanager'] = $rs;
    }
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
    $rs = DB::Query("select * from t_class where classname = '{$values['b-class']}'");
    while( $row = $rs->fetchAssoc() )
    {
        if (!empty($class['fleets']))
        {
            $msg.="- WARNING - this class has fleet divisions defined ({$data['fleets']}) - do you need to specify one?</br>";
        }
    }

}

// set club name - switching to YC and SC as required
$values['h-club'] = get_club($values['h-club']);
$values['c-club'] = get_club($values['c-club']);

// set helm and crew
$values['h-name'] = ucwords(strtolower($values['h-name']));
$values['c-name'] = ucwords(strtolower($values['c-name']));

//------------------------------------------------------------------------------------------------------------------------------

// get boat handicap from t_class using handicap-type field ine_event
$rs = DB::Query("select * from e_event where eid = '{$values['eid']}' LIMIT 1");
$event = $rs->fetchAssoc();
if (!empty($event))
{
    if ($event['handicap-type'] == "personal")
    {
        $values['b-pn'] = $class['nat_py'];

        if (!empty($values['e-racemanager']))
        {
            $rs = DB::Query("select personal_py from t_competitor where id = {$values['e-racemanager']} LIMIT 1");
            if (!empty($rs['personal_py']))
            {
                $values['b-personalpn'] = $rs['personal_py'];
            }
            else
            {
                $msg.="- could not set personal handicap for entered boat</br>";
            }
        }
        else
        {
            $msg.="- could not set personal handicap for entered boat</br>";
        }
    }
    elseif ($event['handicap-type'] == "local")
    {
        $values['b-pn'] = $class['local_py'];
    }
    else
    {
        $values['b-pn'] = $class['nat_py'];
    }
}
else
{
    $msg.="- could not set event handicap for entered boat</br>";
}

// set guid for future updates
if($mode == "add") { $values['e-guid'] = get_guid(); }

// get entry sequence no.
$rs = DB::Query("SELECT MAX(`e-entryno`) FROM e_entry WHERE `eid` = {$values['eid']}");
//$max_id = $db_o->run("SELECT MAX(`e-entryno`) FROM e_entry WHERE `eid` = {$values['eid']}", array($eid) )->fetchColumn();
$values['e-entryno'] = $rs + 1;

// determine if entry will be on waiting list
$values['e-waiting'] = 0;
if ($event['entry-limit'] > 0)
{
    // get no. of current entries in this event
    $rs = DB::Query("SELECT COUNT(*) as count FROM e_entry WHERE eid = {$values['eid']} and `e-exclude` = 0 GROUP BY eid");
 //   $numentries = $db_o->run("SELECT COUNT(*) as count FROM e_entry WHERE eid = ? and `e-exclude` = 0 GROUP BY eid", array($eid) )->fetchColumn();
    if ( $rs >= $event['entry-limit'] ) { $values['e-waiting'] = 1; }
}

$waiting_chk = check_waiting_list ( $event['entry-limit'], $eid);
$waiting_chk ? $entry['e-waiting'] = 1 : $entry['e-waiting'] = 0;

// determine if a junior consent form is required
$junior_chk = check_junior_consent( $entry['h-age'], $entry['c-age']);

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





