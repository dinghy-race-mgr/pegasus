<?php
/**
 * Allows sailor to select whether to use the cruise or race version of rm_sailor
 *
 */
$loc        = "..";
$page       = "index";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_sailor_lib.php");

require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");

$tmpl_o = new TEMPLATE(array("./templates/layouts_tm.php"));

// check arguments
$demo = check_argument("demo", "checkset", "demo", "live");
$mode = check_argument("mode", "set", "", "");
$usage= check_argument("usage", "checkset", "single", "multi");
$debug= check_argument("debug", "checkint", "", "0");

$race_option = "rm_sailor.php?mode=race&demo=$demo&usage=$usage&debug=$debug";
$cruise_option = "rm_sailor.php?mode=cruise&demo=$demo&usage=$usage&debug=$debug";

if ($mode == "cruise") {  // go straight to cruise option
    header("Location: $cruise_option");

} elseif ($mode == "race") { // go straight to race option
    header("Location: $race_option");

} else { // let the user pick which option they want

    $params['items'][] = array(
        "color" => "background-color: #00a2b4 !important; color: #ffffff !important; ",
        "label" => "Leisure Sailing",
        "text" => "Click here to record going afloat for an individual or organised cruise",
        "link" => $cruise_option,
        "icon" => "glyphicon glyphicon-sunglasses"
    );

    $params['items'][] = array(
        "color" => "background-color: #B9107C !important;; color: #ffffff !important ;",
        "label" => "Racing",
        "text" => "Click here to participate in the racing today",
        "link" => $race_option,
        "icon" => "glyphicon glyphicon-list-alt"
    );

    $pagefields = array(
        "title" => "rm_sailor",
        "theme" => "flatly_",
        "background" => "bg-primary",
        "loc" => $loc,
        "stylesheet" => "$loc/style/rm_sailor.css",
        "header-left" => "raceManager SAILOR",
        "header-center" => "",
        "header-right" => "",
        "body" => $tmpl_o->get_template("start_menu", array(), $params),
        "footer-left" => "",
        "footer-center" => "",
        "footer-right" => ""
    );

    echo $tmpl_o->get_template("basic_page", $pagefields);
    exit();
}
