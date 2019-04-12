<?php
/**
 * signon_sc.php - adds entry for selected races
 * 
 *
 * 
 */
$loc        = "..";       
$page       = "signon";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_sailor_lib.php");

u_initpagestart(0,"signon_sc",false);   // starts session and sets error reporting

// libraries
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/entry_class.php");
require_once ("{$loc}/common/classes/event_class.php");

// connect to database to get event information
$db_o = new DB();
$tmpl_o = new TEMPLATE(array("../templates/sailor/layouts_tm.php", "../templates/sailor/signon_tm.php"));

// set boat details
$boat = set_boat_details();

//
//

// check that the user has selected at least one race to enter
// $numentered = num_entries_requested($_REQUEST);

//if ($numentered == 0)   // no races selected to enter
//{
//    header("Location: signon_pg.php?state=noentries");
//}
//else                   // we have entries - add them to table t_signon
//{

// loop over races for today and create entry
$event_bufr = "";
$overall_success = true;     // flag for overall entry success across all races for which competitor is eligible

foreach ($_SESSION['events']['details'] as $eventid => $race)
{
    //$confirm_fields = array( "name" => $race['event_name'], "start-time" => $race['event_start']);
    $entry_success = false;  // flag for specific race entry success

    if (isset($_REQUEST["entry$eventid"]))  // a selection has been made
    {
        //echo "<pre>event selected $eventid</pre>";
        if ($_REQUEST["entry$eventid"]=="on")  // this race has been selected
        {
            $entry = $_SESSION["entries"][$eventid];

            $chgsailnum = u_change($_SESSION['sailor']['chg-sailnum'], $_SESSION['sailor']['sailnum']);
            $chghelm = u_change($_SESSION['sailor']['chg-helm'], $_SESSION['sailor']['helmname']);
            $chgcrew = u_change($_SESSION['sailor']['chg-crew'], $_SESSION['sailor']['crewname']);

            // add to entry table  $eventid, $event_detail
            $entry_o = new ENTRY($db_o, $eventid, $_SESSION['events']['details'][$eventid]);
            $status = $entry_o->add_signon($_SESSION['sailor']['id'], $entry['allocate']['status'], $chghelm, $chgcrew, $chgsailnum);

            //echo "<pre>insert status - $status</pre>";
            if ($status == "update" OR $status == "enter")
            {
                 u_writelog("event $eventid | {$_SESSION['sailor']['classname']} 
                 | {$_SESSION['sailor']['sailnum']} -> $chgsailnum | {$_SESSION['sailor']['helmname']} -> $chghelm  
                 | {$_SESSION['sailor']['crewname']} -> $chgcrew | $status","");

                 //$entry_success = true;
                 //$confirm_fields['text'] = "Start {$entry["allocate"]["start"]}";
            }
            else
            {
                 u_writelog("event $eventid | {$_SESSION['sailor']['classname']} 
                 | {$_SESSION['sailor']['sailnum']} | entry failed [reason: $status]", "");

                 //$overall_success = false;
                 //$entry_success   = false;
                 //$confirm_fields['text'] = "not entered: $status";
            }
        }
        elseif ($_REQUEST["entry$eventid"]=="off")
            // not entering this race
        {
            $dummy = "1";
            //$entry_success = false;
            //$confirm_fields['text'] = "not entered";
        }
    }
    else
    {
        $dummy = "2";
        //$entry_success = false;
        //$confirm_fields['text'] = "not selected";
    }

    // render entry result for each race
    //$event_bufr.= $tmpl_o->get_template( "signon_race_confirm", $confirm_fields, array('status'=>$entry_success) );
}
//// render page body
//$signon_fields['boat-label'] = $tmpl_o->get_template( "boat_label", $boat, array("change"=>false));
//$signon_fields['event-list'] = $event_bufr;
//$_SESSION['pagefields']['body'] = $tmpl_o->get_template( "signon_confirm", $signon_fields, array('status'=>$overall_success));
////}
//
//echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
//flush();

// update information on entries
$_SESSION['entries'] = get_entry_information($_SESSION['sailor']['id'], $_SESSION['events']['details']);

// return to
header("Location: signon_pg.php?mode=entryset");
exit();

//// if script is being used for multiple signon then go back to the start
//if ($_SESSION['usage'] == "multi")
//{
//    sleep(4);
//    echo <<<EOT
//    <script> location.replace("boatsearch_pg.php"); </script>
//EOT;
//}

?>