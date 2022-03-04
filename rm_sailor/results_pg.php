<?php
/**
 * Allows sailor to view results from selected race - if results are complete
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 *
 */
$loc        = "..";       
$page       = "results";   
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/lib/rm_lib.php");
require_once ("./include/rm_sailor_lib.php");

u_initpagestart(0,"results_pg",false);   // starts session and sets error reporting

// libraries
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/entry_class.php");
require_once ("{$loc}/common/classes/race_class.php");
require_once("{$loc}/common/classes/raceresult_class.php");
require_once ("{$loc}/common/classes/template_class.php");

$db_o = new DB();
$tmpl_o = new TEMPLATE(array("./templates/layouts_tm.php", "./templates/results_tm.php"));

// check arguments
$eventid = check_argument("state", "checkint", "");
$display = check_argument("mode", "set", "", "list");

if ($eventid) {
    if ($_SESSION['events']['numevents'] <= 0) {
        $_SESSION['pagefields']['body'] = $tmpl_o->get_template("noevents", array(), $_SESSION['events']);

    } else {
        // get results data (with position for sailor in each event and full data with all positions)
        $_SESSION['result'] = set_result_data($_SESSION['sailor']['id'], $_SESSION['events']['details']);
        $result_arr = $_SESSION['result']['list'];

        // set up boat information
        $boat_fields = set_boat_details();
        $boat_fields["boat-label"] = $tmpl_o->get_template("boat_label", $boat_fields, array("change" => false));

        $no_results = false;
        if ($display == "list") // list position of boat in each race
        {
            if (!empty($_SESSION['result']['list'])) {
                $_SESSION['pagefields']['body'] = $tmpl_o->get_template("result_list", $boat_fields, $result_arr);

            } else {
                $no_results = true;

            }

        } elseif ($display == "full") {
            if (key_exists($eventid, $result_arr) and key_exists($eventid, $_SESSION['result']['data'])) {
                $result_arr[$eventid]['event-date'] = date("jS M", strtotime($result_arr[$eventid]['event-date']));
                $_SESSION['pagefields']['body'] = $tmpl_o->get_template("result_data", $result_arr[$eventid],
                    array("type" => $result_arr[$eventid]['race-type'], "sailorid" => $_SESSION['sailor']['id'],
                        "data" => $_SESSION['result']['data'][$eventid]));

            } else {
                $no_results = true;

            }

        } else {
            $error_fields = array(
                "error"=>"The results page mode is not valid |{value}|",
                "detail"=>"value was - $display",
                "action" =>"Please report this to your system administrator",
                "url" => "index.php"
            );
            $_SESSION['pagefields']['body'] = $tmpl_o->get_template("error_msg", $error_fields, array("restart"=>true));
        }

        if ($no_results) {
            $_SESSION['pagefields']['body'] = $tmpl_o->get_template("result_none", $boat_fields);
        }
    }
} else {
    $error_fields = array(
        "error"=>"No event specified for the results",
        "detail"=>"",
        "action" =>"Please report this to your system administrator",
        "url" => "index.php"
    );
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("error_msg", $error_fields, array("restart"=>true));
}

$_SESSION['pagefields']['header-center'] = $_SESSION['option_cfg'][$page]['pagename'];
$_SESSION['pagefields']['header-right'] = $tmpl_o->get_template("options_hamburger", array(),
    array("page" => $page, "options" => set_page_options($page)));
echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
exit();
