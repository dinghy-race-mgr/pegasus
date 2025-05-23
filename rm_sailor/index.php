<?php
/**
 * Allows sailor to select whether to use the cruise or race version of rm_sailor
 *
 * usage:
 * index.php?mode=race&demo=live&usage=multi&debug=0,
 * index.php?closed=20200830T08:00,
 *
 * args:
 * mode (str)   race|cruise    selects racing or cruising events - if not provided user selects
 * demo (str)   live|demo      selects real or demo events - defaults to live
 * usage (str)  multi|single   use multi if multiple users are using the same instance / single if personal use
 * closed (str) true|<date>    displays service unavailable - if date is specified this is used to announce
 *                             the reopening date time YYYYMMDDTHH:MM
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
$demo   = u_checkarg("demo", "checkset", "demo", "live");
$mode   = u_checkarg("mode", "set", "", "");
$usage  = u_checkarg("usage", "checkset", "single", "multi");
$event  = u_checkarg("event", "setnotnull","");
$closed = u_checkarg("closed", "set", "", "");

//fixme - don't put args not used in string (use form url php command)
$race_option = "rm_sailor.php?mode=race&demo=$demo&usage=$usage";
//$cruise_option = "rm_sailor.php?mode=cruise&demo=$demo&usage=$usage";
$cruise_option = "https://www.starcrossyc.org.uk/leisuresail/";  // fixme - need to configure link to external system as part of .ini or t_ini

if ($event)
{
    $race_option.= "&event=$event";
    $cruise_option.= "&event=$event";
}

if (!$closed)
{

    if ($mode == "cruise") {  // go straight to cruise option
        header("Location: $cruise_option");

    } elseif ($mode == "race") { // go straight to race option
        header("Location: $race_option");

    } else { // let the user pick which option they want

        $params['items'][] = array(
            "color" => "background-color: #00a2b4 !important; color: #ffffff !important; ",
            "label" => "Leisure Sailing",
            "text"  => "Click here to record going afloat for an individual or organised cruise",
            "link"  => $cruise_option,
            "icon"  => "glyphicon glyphicon-sunglasses"
        );

        $params['items'][] = array(
            "color" => "background-color: #B9107C !important;; color: #ffffff !important ;",
            "label" => "Racing",
            "text"  => "Click here to participate in the racing today",
            "link"  => $race_option,
            "icon"  => "glyphicon glyphicon-list-alt"
        );

        $pagefields = array(
            "title"         => "rm_sailor",
            "theme"         => "flatly_",
            "background"    => "bg-primary",
            "loc"           => $loc,
            "stylesheet"    => "./style/rm_sailor.css",
            "header-left"   => "raceManager SAILOR",
            "header-center" => "",
            "header-right"  => "",
            "body"          => $tmpl_o->get_template("start_menu", array(), $params),
            "footer-left"   => "",
            "footer-center" => "",
            "footer-right"  => ""
        );

        echo $tmpl_o->get_template("basic_page", $pagefields);
        exit();
    }
}
else
{
    if (strtotime($closed))
    {
        $opentime = date("l jS \of F H:i", strtotime($closed));
    }
    else
    {
        $opentime = "";
    }

    $pagefields = array(
        "title" => "rm_sailor",
        "theme" => "flatly_",
        "background" => "bg-primary",
        "loc" => $loc,
        "stylesheet" => "./style/rm_sailor.css",
        "header-left" => "raceManager SAILOR",
        "header-center" => "",
        "header-right" => "",
        "body" => $tmpl_o->get_template("closed", array(), array("mode" => $mode, "opentime" => $opentime)),
        "footer-left" => "",
        "footer-center" => "",
        "footer-right" => ""
    );

    echo $tmpl_o->get_template("basic_page", $pagefields, array());
    exit();
}
