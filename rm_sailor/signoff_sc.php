<?php
/**
 * signoff_sc.php - records declarations, retirements and protest notifications
 *
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 *   
 * 
 */
$loc        = "..";       
$page       = "signoff";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_sailor_lib.php");

u_initpagestart(0,"signoff_sc",false);   // starts session and sets error reporting

// libraries
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/entry_class.php");
require_once ("{$loc}/common/classes/event_class.php");

// connect to database to get event information
$db_o = new DB();
$tmpl_o = new TEMPLATE(array("../templates/sailor/layouts_tm.php", "../templates/sailor/signoff_tm.php"));

if (empty($_SESSION['sailor']['change']))
{
    $signoff_fields = array(
        "class"  => $_SESSION['sailor']['classname'],
        "sailnum"=> $_SESSION['sailor']['sailnum'],
        "helm"   => $_SESSION['sailor']['helmname'],
        "crew"   => $_SESSION['sailor']['crewname'],
    );
}
else
{
    $signoff_fields = array(
        "class"  => $_SESSION['sailor']['classname'],
        "sailnum" => u_pick($_SESSION['sailor']['chg-sailnum'], $_SESSION['sailor']['sailnum']),
        "helm"    => u_pick($_SESSION['sailor']['chg-helm'], $_SESSION['sailor']['helmname']),
        "crew"    => u_pick($_SESSION['sailor']['chg-crew'], $_SESSION['sailor']['crewname']),
    );
}
$signoff_fields['team'] = u_conv_team($signoff_fields['helm'], $signoff_fields['crew'], 0);

// process declarations and protest notifications
//echo print_r($_REQUEST, true)."</br>";
//echo print_r($_SESSION['entries'], true)."</br>";
$declare_bufr = "";
$overall_status = true;
$event_list = "";
$i = 0;

foreach ($_SESSION['entries'] as $eventid => $entry)
{
    if (isset($_REQUEST["declare{$eventid}"]))
    {
        $i++;
        if (isset($_REQUEST["protest{$eventid}"]))
        {
            if ($_REQUEST["protest{$eventid}"] == "on") { $_SESSION['entries'][$eventid]['protest'] = true; }
        }

        // update entry array
        $_SESSION['entries'][$eventid]['declare'] =  $_REQUEST["declare{$eventid}"];

        // add record to entry table to record declaration
        $entry_o = new ENTRY($db_o, $eventid, $_SESSION['events']['details'][$eventid]);
        $status = $entry_o->add_signoff($_SESSION['sailor']['id'],
                                        $_SESSION['entries'][$eventid]['declare'],
                                        $_SESSION['entries'][$eventid]['protest']);
        if ($status)
        {
            // create log record
            u_writelog("event $eventid | {$_SESSION['sailor']['classname']} 
                    | {$_SESSION['sailor']['sailnum']} -> {$_SESSION['sailor']['chg-sailnum']} 
                    | {$_SESSION['entries'][$eventid]['declare']}","");

            $declare_bufr.= $tmpl_o->get_template("signoff_race_confirm",
                                  array("name" => $entry['event-name'], "position" => $entry['position']),
                                  array('declare' => $entry['declare'], "protest" => $entry['protest']));
        }
        else
        {
            $overall_status = false;
            // create log record of failure
            u_writelog("event $eventid | {$_SESSION['sailor']['classname']} 
                    | {$_SESSION['sailor']['sailnum']} -> {$_SESSION['sailor']['chg-sailnum']} 
                    | failed to process declare/retire event","");
            $declare_bufr.= $tmpl_o->get_template("signoff_race_confirm",
                array("name" => $entry['event-name'], "position" => $entry['position']),
                array('declare' => "failed to register sign off - please contact Race Officer", "protest" => ""));

        }
    }
    else
    {
        $declare_bufr.= $tmpl_o->get_template("signoff_race_confirm",
            array("name" => $entry['event-name'], "position" => "not entered"),
            array('declare' => "", "protest" => ""));
    }
}
$signoff_fields['event-list'] = $declare_bufr;
// output result
$_SESSION['pagefields']['body'] = $tmpl_o->get_template("signoff_confirm", $signoff_fields,
    array('complete' => $overall_status, 'events' => $_SESSION['events']['details'], 'entries' => $_SESSION['entries']));

echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
flush();

// update information on entries
$_SESSION['entries'] = get_entry_information($_SESSION['sailor']['id'], $_SESSION['events']['details']);

// if script is being used for multiple signoff then go back to the start
if ($_SESSION['usage'] == "multi")
{
    sleep(4);
    echo <<<EOT
    <script> location.replace("boatsearch_pg.php"); </script>
EOT;
}


?>