<?php
/*
 * dtm_confirm_check
 *
 * Design
 *  - need a simple generic template useful for other dtm issues
 *  - process runs once per n days (or on certain day of week) - via daily_cron_manager process
 *  - identifies all unconfirmed duties grouped by member ***
 *  - converts data into content for brevo (still not sure whether to do minimal or maximal)
 *         - if already 2 reminders sent - change the message
 *  - sends emails
 *  - updates count of reminders
 *  - identifies all members who have been sent more than a threshold value (5) reminders to confirm
 *  - prepares report for each rota_manager for over threshold duties
 *  - sends email with report to rota_managers
 *
 *  - dryrun - doesn't send the emails
 */

require_once("../common/lib/util_lib.php");
require_once("../common/classes/db.php");

$dbg = false;
$loc  = "..";
$page = "duty_confirm_check";     //
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
$enddate = "2026-02-28";                        // fixme debugging purpose only - normally end of year

$today = "2026-01-23"; //fixme
$dryrun = false;
if (key_exists("dryrun", $_REQUEST))
{
    $dryrun = filter_var($_REQUEST['dryrun'], FILTER_VALIDATE_BOOLEAN);
}

$cfg = u_set_config("../config/common.ini", array(), false);
//echo "<pre>".print_r($cfg,true)."</pre>";

// logging - start process (appending to cronlog)
u_cronlog("\n--- DUTYMAN CONFIRM CHECK - start");

// access to database
$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host']);

// get rotas lookup from t_system_codes
$rotas = get_rota_lookup();
//echo "<pre>".print_r($rotas,true)."</pre>";

// get unique member details from t_rotamember  // fixme is this the best way to do this
$rota_members = get_rota_members();
//echo "<pre>".print_r($rota_members,true)."</pre>";

// get unconfirmed duties per member for future events from t_eventduty
$unconfirmed = get_unconfirmed_duties($today);

if (count($unconfirmed) <= 0)
{
    u_cronlog("\n- no unconfirmed duties identified ");
    u_cronlog("\n--- DUTYMAN CONFIRM CHECK - stop");
}
else
{
    // need to send emails
    echo "<pre>".print_r($unconfirmed,true)."</pre>";
}
exit();

// get list of unconfirmed duties for each members
$max_reminders = 0;
foreach ($members as $k => $member)
{
    $txt = "";
    $i = 0;
    foreach ($member as $md)
    {
        $i++;
        if ($md['reminders'] > $max_reminders) {$max_reminders = $md['reminders'] ;}
        $txt.= "<span style='padding: 20px;'>- {$md['date']} {$md['rota']}, {$md['event']} (event start time: {$md['start']})</span><br>";
    }

    $members[$k]['dutylist'] = $txt;
    $members[$k]['maxreminders'] = $max_reminders;

}

    


if ($members[$k]['person'] == "Dave Lee")
{
    $params = array(
        "num_unconfirmed" => "$i",
        "txt_unconfirmed" => $members[$k]['dutylist'],
        "dutyman_link" => "t_rotamember/dtm_login",
        "signatory" => "Andy Hohl - Vice Commodore",
        "rota_mgr_list" => "<span style=\"padding:20px;\"> Race Officer, Assistant Race Officer, Cruise Officer - Matt Holmes</span><br>
                                <span style=\"padding:20px;\"> Safety Boat Driver - John Allen</span><br>
                                <span style=\"padding:20px;\"> Bar, Galley - Mathew Tanner</span><br>",
    );

    $example = $tmpl_o->get_template("dtm_confirm_email_1", array(), array("state"=>2, "pagestate"=>$_REQUEST['pagestate']));
    echo $tmpl_o->get_template("print_page", $pagefields, array() );
    echo $example;
}




// common settings
$fromName   = 'Mark Elkington';
$fromEmail  = 'markeb14.762@gmail.com';
$subject    = 'Starcross Yacht Club - Duty Confirmation';

// member specific settings
foreach ($members as $k => $member)
{

}
$toName     = 'recipient';
$toEmail    = 'mark.elkington@blueyonder.co.uk';

$htmlMessage = '<p>Hello '.$toName.',</p><p>This is a test transactional email from Mark sent from Brevo.</p>';


$data = array(
    "sender" => array("email" => $fromEmail, "name" => $fromName),
    "to"     => array("email" => $toEmail, "name" => $toName),
    "subject" => $subject,
    "htmlContent" => "
<html><head></head><body><p>'.$htmlMessage.'</p></p></body></html>"
);


function get_rota_lookup()
{
    global $db_o;

    $rotas = $db_o->run("select `code`, `label` from t_code_system WHERE groupname='rota_type' ORDER BY code ASC", array() )->fetchall();
    foreach($rotas as $rota)
    {
        $dutycodes["{$rota['code']}"] = $rota['label'];
    }
    
    return $dutycodes;
}

function get_rota_members()
{
    global $db_o;

    $query = "SELECT id, concat(firstname,' ',familyname) as person, memberid, rota, phone, email, dtm_login
          FROM t_rotamember WHERE active = 1
          GROUP BY firstname, familyname ORDER BY person ASC LIMIT 10";
    $rota_members = $db_o->run($query, array() )->fetchall();
    return $rota_members;
}


function get_unconfirmed_duties($today, $enddate)
{
    global $db_o, $rota_members, $rotas;

    $query = "SELECT a.id, a.eventid, dutycode, person, swapable, email, confirmed, confirmed_reminders, swap_requested, 
          swap_request_date, event_name, event_date, event_start
          FROM t_eventduty as a JOIN t_event as b ON a.eventid=b.id
          WHERE event_date >= '$today' and event_date <= '$enddate' and confirmed = 'N' and person is not null 
                and person not like '%not specified%' and person != '' 
          ORDER BY person ASC, event_date ASC";

    $duties = $db_o->run($query, array())->fetchall();

    $members = array();
    $max_reminders = 0;
    $current = "";
    $i = 0;
    foreach ($duties as $duty)
    {
        $i++;
        $duty_name = str_replace(" ", "_", $duty['person']);
        $duty_rept_htm = <<<EOT
<span style='padding: 20px;'>- {$duty['event_date']} {$rotas["{$duty['dutycode']}"]}, {$duty['event_name']} (event start time: {$duty['event_start']})</span><br>
EOT;

        if ($current != $duty_name)
        {
            // create new member array
            $members[$duty_name] = array(
                "id" => $duty['id'],
                "person" => $duty['person'],
                "email" => $duty['email']
            );

            if ($i != 1)
            {
                $members[$current]['reminders'] = $max_reminders;
                echo "<pre>197: {$members[$current]['reminders']} $max_reminders</pre>";
                $max_reminders = 0;
            }



            // set counter for reminders
            $max_reminders = $duty['confirmed_reminders'];
            echo "<pre>204: $max_reminders {$duty['confirmed_reminders']} </pre>";

            // add details for this duty to output string
            $members[$duty_name]['dutylist'] = $duty_rept_htm;

            // get dtm_login from t_eventduty
            $dtm_login = get_dtm_login($duty['person']);
            $members[$duty_name]['dtm_login'] = $dtm_login;

        }
        else
        {
            $members[$duty_name]['dutylist'] .= $duty_rept_htm;
        }

        // need to get maximum reminders for this member
        if ($max_reminders < $duty['confirmed_reminders'])
        {
            $max_reminders = $duty['confirmed_reminders'];
        }
        echo "<pre>224: $max_reminders {$duty['confirmed_reminders']} </pre>";

        // set current name to duty name
        $current = $duty_name;
    }

    $members[$current]['reminders'] = $max_reminders;
    echo "<pre>231: {$members[$current]['reminders']} $max_reminders</pre>";

    return $members;
}

function get_dtm_login($person)
{
    global $db_o;
    $dtm_login = "";

    $names = explode(" ",$person);
    if (count($names) > 2) { echo "<pre>$person - ".count($names)." names</pre>";}
    $query = "SELECT dtm_login FROM t_rotamember WHERE firstname ='{$names[0]}' and familyname = '{$names[1]}'";
    $dtm_login = $db_o->run($query, array())->fetchColumn();
    return $dtm_login;
}