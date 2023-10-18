<?php
/**
 * Deals with user selection of boat - setting details of selected boat into $_SESSION
 *
 * This script finds the relevant competitor details, sets them as session
 * variable entry and then either redirects to the relevant cruise or race process page
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 */
$loc        = "..";       
$page       = "pick";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");      
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_sailor_lib.php");

// start session
session_id('sess-rmsailor');
session_start();

// initialise page
u_initpagestart(0,$scriptname,false);

require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/comp_class.php");
require_once ("{$loc}/common/classes/cruise_class.php");

$tmpl_o = new TEMPLATE(array("./templates/layouts_tm.php"));

// test argument is
$sailorid = u_checkarg("sailor", "checkint", "");

// check if competitor id has been passed
if ($sailorid)
{
    // connect to database to get competitor information
    $db_o = new DB();
    $comp_o = new COMPETITOR($db_o);

// get details for competitor id - can only return one as this is primary key
    $competitors = $comp_o->comp_findcompetitor(array("id"=>$sailorid));
    $competitors ? $numcompetitors = count($competitors) : $numcompetitors = 0;

    if ($numcompetitors==1) {                                                           // found
        u_writelog($_SESSION['app_name']." $scriptname : boat selected -> [id: $sailorid]  ","");
        $_SESSION['sailor'] = $competitors[0];
        $_SESSION['sailor']['change'] = false;

        if ($_SESSION['mode'] == "cruise") {
            // check if boat already registered in t_cruise
            $cruise_o = new CRUISE($db_o, date("Y-m-d"));
            $chk_cruise = $cruise_o->get_latest_changes($_SESSION['sailor']['id']);

            // update sailor info with custom fields
            foreach ($_SESSION['change_fm'] as $field => $spec) {
                if (!empty($chk_cruise[$field])) {$_SESSION['sailor']['change'] = true;}
                $_SESSION['sailor'][$field] = $chk_cruise[$field];
            }

        } elseif ($_SESSION['mode'] == "race") {

            $chk_race = get_race_entries($_SESSION['sailor']['id'], date("Y-m-d"));

            // update sailor info with custom fields
            foreach ($_SESSION['change_fm'] as $field => $spec) {
                if (!empty($chk_race[$field])) {$_SESSION['sailor']['change'] = true;}
                $_SESSION['sailor'][$field] = $chk_race[$field];
            }
        }

        sailor_redirect($_SESSION['mode'], "race_pg.php?state=init", "cruise_pg.php?state=init" );
        exit();

    } else {                                                                                // single match not found - report error
        u_writelog($_SESSION['app_name']." $scriptname : boat selected not found -> [id: $sailorid]  ","");
        $error_fields = array(
            "error" => "Fatal Error: Boat selected not found",
            "detail" => "Boat (id: $sailorid) selected from list cannot be retrieved (pickboat_sc.php: records found $numcompetitors)",
            "action" => "Please report error to your raceManager administrator",
            "url" => "index.php"
        );
        $_SESSION['pagefields']['body'] = $tmpl_o->get_template("error_msg", $error_fields, array("restart"=>true));

        echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
        exit();
    }
} else {
    // id passed is not valid
    u_writelog("Fatal Error: selected boat from search list not recognised", "");
    $error_fields = array(
        "error" => "Fatal Error: selected boat not recognised",
        "detail" => "Boat does not have a valid identifier (pickboat_sc.php)",
        "action" => "Please report error to your raceManager administrator",
        "url" => "index.php"
    );
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("error_msg", $error_fields, array("restart"=>true));

    echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
    exit();
}

