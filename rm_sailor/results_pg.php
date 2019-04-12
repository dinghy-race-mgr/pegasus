<?php
/**
 * mob_pg_results - allows sailor to view results from selected race
 * 
 * 
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 *   
 * 
 */
$loc        = "..";       
$page       = "results";   
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_sailor_lib.php");

u_initpagestart(0,"results_pg",false);   // starts session and sets error reporting

// libraries
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/entry_class.php");
require_once ("{$loc}/common/classes/racestate_class.php");
require_once ("{$loc}/common/classes/result_class.php");
require_once ("{$loc}/common/classes/template_class.php");

$db_o = new DB();
$tmpl_o = new TEMPLATE(array("../templates/sailor/layouts_tm.php", "../templates/sailor/results_tm.php"));

// get mode and event from $_REQUEST if they are set
$eventid = 0;
$mode = "list";
if (isset($_REQUEST['mode']))
{
    if ($_REQUEST['mode'] == "full")
    {
        $mode = "full";
    }
    if (isset($_REQUEST['event']))
    {
        if (ctype_digit($_REQUEST['event']))  // check for integer
        {
            $eventid = $_REQUEST['event'];
        }
    }
}

// display results page - either no event , or a list of events , or the detailed results
if ($_SESSION['events']['numevents'] <= 0)
{
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("noevents", array(), $_SESSION['events']);
}
else
{
    // get results data (with position for sailor in each event and full data with all positions)
    $_SESSION['result'] = set_result_data($_SESSION['sailor']['id'], $_SESSION['events']['details']);
    $result_arr = $_SESSION['result']['list'];

    // set up boat information
    $boat_fields = set_boat_details();

    if ($mode == "list") // list position of boat in each race
    {
        $boat_fields["boat-label"] = $tmpl_o->get_template("boat_label", $boat_fields, array("change" => false));
        //$_SESSION['pagefields']['body'] = "<pre>".print_r($_SESSION['result'],true)."</pre>";
        $_SESSION['pagefields']['body'] = $tmpl_o->get_template("result_list", $boat_fields, $result_arr);
    }
    elseif  ($mode == "full")  // display full results for one event
    {
        if (key_exists($eventid, $result_arr) and key_exists($eventid, $_SESSION['result']['data']))
        // check I have race dtails and results data
        {
            $result_arr[$eventid]['event-date'] = date("jS M",strtotime($result_arr[$eventid]['event-date']));
            $_SESSION['pagefields']['body'] = $tmpl_o->get_template("result_data", $result_arr[$eventid],
                array("type"=> $result_arr[$eventid]['race-type'], "sailorid" => $_SESSION['sailor']['id'],
                    "data" => $_SESSION['result']['data'][$eventid]));
        }
        else    // report can't find results
        {
            $boat_fields["boat-label"] = $tmpl_o->get_template("boat_label", $boat_fields, array("change" => false));
            // $_SESSION['pagefields']['body'] = "<pre>"."event: $eventid"."</pre>";
            $_SESSION['pagefields']['body'] = $tmpl_o->get_template("result_none", $boat_fields);

        }
    }
    else         // report system error
    {
        $error_fields = array(
            "error"=>"The results page mode is not valid |{value}|",
            "detail"=>"value was - $mode",
            "action" =>"Please report this to your system administrator"
        );
        $_SESSION['pagefields']['body'] = $tmpl_o->get_template("error_msg", $error_fields);
    }
}
echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
exit();

?>