<?php
/*
 * dtm_confirm_check
 *
 * Design
 *  - loop through t_rotamember (or should we use dtm members) = alphabetically
 *  - for each member find duties for future events that have not been confirmed or not had a swap requested
 *  - create array for each members with unconfirmed message (but note if some have been confirmed) and brevo information for that member
 *  - send emails - content:
 *      - info on missing confirmations
 *      - reason we need them
 *      - how to do the confirm
 *      - who to contact
 *
 *  - dryrun option - doesn't send the emails - but provides a summary of unconfirmed numbers for each member
 */



$loc  = "..";
$page = "duty_confirm_check";
$styletheme = "flatly_";
$stylesheet = "./style/rm_utils.css";
$scriptname = basename(__FILE__);
$today = date("Y-m-d");

require_once("{$loc}/common/lib/util_lib.php");
require_once("{$loc}/common/classes/db.php");
require_once ("{$loc}/common/classes/template_class.php");

// arguments
date_default_timezone_set('Europe/London');
$today  = date("Y-m-d");
key_exists("dryrun", $_REQUEST) ?  $dryrun = filter_var($_REQUEST['dryrun'], FILTER_VALIDATE_BOOLEAN) : $dryrun = false;
$start_date = $today;                  // starts looking from today
$end_date = date("Y-12-31");           // ends looking at end of current year


// set config
$cfg = u_set_config("../config/common.ini", array(), false);
//echo "<pre>".print_r($cfg,true)."</pre>";

$email_props_arr = array(
    "credential" => $cfg['BREVO_API'],
    "email_from" => array("email" => "noreply@starcrossyc.org.uk", "name"=> "Starcross YC no reply"),

);

// logging - start process (appending to cronlog)
u_cronlog("******  DUTYMAN DUTY CONFIRM CHECK - start");

// set templates
$tmpl_o = new TEMPLATE(array("$loc/common/templates/general_tm.php","./templates/layouts_tm.php","./templates/dutyman_tm.php"));

// access to database
$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host']);

// get event types lookup from t_system_codes
$types = get_eventtype_lookup();
u_cronlog(" - Found ".count($types)." event types");
//echo "<pre>Found ".count($types)." event types</pre>";

// get rotas lookup from t_system_codes
$rotas = get_rota_lookup();
u_cronlog(" - Found ".count($rotas)." rota types");
//echo "<pre>Found ".count($rotas)." rota types</pre>";

// get unique member details from t_rotamember  // fixme is this the best way to do this
$rota_members = get_rota_members();
u_cronlog(" - Found ".count($rota_members)." unique rota members");
//echo "<pre>Found ".count($rota_members)." rota members</pre>";

// get unconfirmed duties for future events from t_eventduty
$reminders = get_unconfirmed_duties($start_date, $end_date, $rotas, $types);
u_cronlog(" - Found ".count($reminders)." members to get reminder email");
//echo "<pre>Found ".count($reminders)." members to be reminded</pre>";

if ($dryrun)                                               // create report
{
    $server_txt = "{$cfg['db_host']}/{$cfg['db_name']}";
    $pagefields = array(
        "loc"           => $loc,
        "theme"         => $styletheme,
        "stylesheet"    => $stylesheet,
        "title"         => "Confirm Check",
        "header-left"   => $cfg['sys_name'] . " <span style='font-size: 0.4em;'>[$server_txt]</span>",
        "header-right"  => "Unconfirmed Duties Check",
        "body"          => $tmpl_o->get_template("dtm_confirm_rept", array(), array("data" => $reminders)),
        "footer-left"   => "",
        "footer-center" => "",
        "footer-right"  => "",
    );

    echo $tmpl_o->get_template("basic_page", $pagefields);
}
else
{
    if (count($reminders) <= 0)                                // no reminders to be sent
    {
        u_cronlog("-- no unconfirmed duties identified ");
    }
    else
    {
        $i = 0;
        $j = 0;
        foreach ($reminders as $k => $reminder)
        {
            if ($reminder['send_reminders'])                               // check if member has asked for no emails
            {
                if ($reminder['num_reminders'] > 3)                        // escalate
                {
                    $template = "dtm_confirm_email_2";
                    $subject = "Starcross YC - URGENT please confirm duties";
                }
                else                                                       // initial email
                {
                    $template = "dtm_confirm_email_1";
                    $subject = "Starcross YC - URGENT please confirm duties";
                }

                $status_arr = send_email_reminder($reminder, "Starcross YC - reminder to confirm your duties", $template, $cfg['BREVO_API']);

                if ($status_arr['success'])
                {
                    $i++;
                    // update no. of reminders sent  // fixme - this needs to update t_eventduty for each duty that is being reminded
                    $tmp_arr = explode(",",rtrim($reminder['ids'], ","));
                    foreach ($tmp_arr as $id)
                    {
                        $query = "UPDATE t_eventduty SET `confirmed_reminders` = `confirmed_reminders` + 1 WHERE id = $id" ;
                        $upd = $db_o->run($query, array() );
                    }
                }
                else
                {
                    u_cronlog(" - email reminder for $k FAILED - (curl: {$status_arr['err']}  return: {$status_arr['return']})");
                    $j++;
                }
            }
            else
            {
                u_cronlog(" - email reminder for $k NOT SENT - members requested no emails");
            }
        }
        u_cronlog(" - email reminders sent to $i members ($j failed to send)");
    }
}

u_cronlog("-- DUTYMAN DUTY CONFIRM CHECK - stop");
exit();

// --------------------------------------------------------------------------------------------------


function get_eventtype_lookup()
{
    global $db_o;

    $types = $db_o->run("select `code`, `label` from t_code_system WHERE groupname='event_type' ORDER BY code ASC", array() )->fetchall();
    foreach($types as $type)
    {
        $eventtypes["{$type['code']}"] = $type['label'];
    }

    return $eventtypes;
}

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

    $query = "SELECT id, concat(firstname,' ',familyname) as person, memberid, rota, phone, email, dtm_login, reminders
              FROM t_rotamember WHERE active = 1
              GROUP BY firstname, familyname ORDER BY familyname ASC, firstname ASC";
    $rota_members = $db_o->run($query, array() )->fetchall();

    return $rota_members;
}


function get_unconfirmed_duties($start, $end, $rotas, $types)
{
    global $db_o;
    global $rota_members;

    // need to get unconfirmed duties for each rotamember in surname order
    $query = "SELECT a.id, eventid, b.event_date, b.event_name, b.event_type, dutycode, memberid, person, 
              substring_index(person, ' ', 1) as firstname, substring_index(person, ' ', -1) as lastname, 
              swapable, confirmed, confirmed_date, confirmed_reminders, swap_requested, swap_request_date, email
              FROM `t_eventduty` as a JOIN t_event as b on a.eventid=b.id
              WHERE substring_index(person, ' ', -1) NOT IN ('MBE', 'OBE') AND `confirmed` = 0 AND `swap_requested` != 1 AND b.event_date >= '$start' AND b.event_date <= '$end'
              ORDER BY lastname LIMIT 20";

    $duties = $db_o->run($query, array())->fetchall();

    $output = array();
    $person = "";
    foreach ($duties as $duty)
    {
        // first check that we have a rota_member record for this person - if we don't skip this duty as we can't send email
        $rota_key = array_search($duty['person'], array_column($rota_members, 'person'));
        if ($rota_key === false)
        {
            u_cronlog(" - email for {$duty['person']} not found - reminder FAILED");
        }
        else
        {
            $displaydate = date("jS F", strtotime($duty['event_date']));
            if (strtolower($person) != strtolower($duty['person']))                    // new person
            {
                // create new element
                $output[$duty['person']] = array();
                $rota_mbr_data = $rota_members[$rota_key];

                // add login link, email details, and no. of reminders already sent
                $output[$duty['person']]['firstname']      = $duty['firstname'];
                $output[$duty['person']]['lastname']       = $duty['lastname'];
                $output[$duty['person']]['login']          = get_dtm_login($duty['person']);
                $output[$duty['person']]['num_reminders']  = $duty['confirmed_reminders'];
                $output[$duty['person']]['send_reminders'] = $rota_mbr_data['reminders'];
                $output[$duty['person']]['email']          = array("emailto" => $rota_mbr_data['email'], "name"=> $duty['person']);
                $output[$duty['person']]['unconfirmed']    = 0;
                $output[$duty['person']]['ids']            = "";

                // add duty detail
                $dutyname = $rotas[$duty['dutycode']];
                $eventtype = $types[$duty['event_type']];
                $output[$duty['person']]['detail'] = "$displaydate : {$duty['event_name']} ($eventtype)  ".strtoupper($dutyname)."<br>";
                $output[$duty['person']]['unconfirmed'] = $output[$duty['person']]['unconfirmed'] + 1;
                $output[$duty['person']]['ids'] = $output[$duty['person']]['ids'].$duty['id'].",";


                // reset current person
                $person = $duty['person'];
            }
            else                                                                       // another event for existing person
            {
                $dutyname = $rotas[$duty['dutycode']];
                $eventtype = $types[$duty['event_type']];
                $output[$duty['person']]['detail'].= "$displaydate : {$duty['event_name']} ($eventtype)  ".strtoupper($dutyname)."<br>";
                $output[$duty['person']]['unconfirmed'] = $output[$duty['person']]['unconfirmed'] + 1;
                $output[$duty['person']]['ids'] = $output[$duty['person']]['ids'].$duty['id'].",";
            }
        }
    }

    return $output;
}

function send_email_reminder($arr, $subject, $template, $api_key)
{
    global $tmpl_o;

    $status_arr = array("success"=>true, "err"=>"", "return"=>"");

    $display_date = date("l jS F Y H:m");

    $fields = array("firstname"=>$arr['firstname'], "year"=>date("Y"), "num_unconfirmed"=>$arr['unconfirmed'], "txt_unconfirmed"=>$arr['detail'], "dtm_login"=>$arr['login']);
    $params = array("num_unconfirmed"=>$arr['unconfirmed']);


    $htm = $tmpl_o->get_template($template, $fields, $params);

    $data = array(
        "sender"      => array("email" => "noreply@starcrossyc.org.uk", "name"=> "Starcross YC no reply"),
//        "to"          => array("email" => $arr['email']['emailto'], "name"=> $person),                      // fixme to be tested
        "to"          => array(
                          "0" => array("email" => "markelkington640@gmail.com", "name"=> "Mark Elkington"),
        ),
        "subject"     => $subject,
        "htmlContent" => "<html><head></head><body>$htm</body></html>"
    );
//    echo "<pre>".print_r($arr,true)."</pre>";
//    echo "<pre>".print_r($data,true)."</pre>";
//    echo $htm;

    // do not send if email is empty
    if (!empty($data['to']))
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.sendinblue.com/v3/smtp/email');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $headers = array();
        $headers[] = 'Accept: application/json';
        $headers[] = "Api-Key: $api_key";
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        $status_arr['return'] = $result;

        if (curl_errno($ch))
        {
            $status_arr['err'] = 'Error:' . curl_error($ch);
            $status_arr['success'] = false;
        }

        curl_close($ch);
    }
    else
    {
            $status_arr['err'] = 'Error: no email created';
            $status_arr['success'] = false;
    }

    return $status_arr;
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

