<?php
/**
 * Allows a user to change boat/crew details for today's events
 *
 */
$loc        = "..";       
$page       = "change";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_sailor_lib.php");

// start session
session_id('sess-rmsailor');
session_start();

// initialise page
u_initpagestart(0,$page,false);   // starts session and sets error reporting

// libraries
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");

$tmpl_o = new TEMPLATE(array( "./templates/layouts_tm.php"));

$change_fields = array();
foreach ($_SESSION['change_fm'] as $field => $spec) {
    if ($spec['status']) {
        $change_fields[$field] = $_SESSION['sailor'][$field];
    }
}
$change_fields["compid"]      = $_SESSION['sailor']['id'];
$change_fields["chg-helm"]    = u_pick($_SESSION['sailor']['chg-helm'], $_SESSION['sailor']['helmname']);
$change_fields["chg-crew"]    = u_pick($_SESSION['sailor']['chg-crew'], $_SESSION['sailor']['crewname']);
$change_fields["chg-sailnum"] = u_pick($_SESSION['sailor']['chg-sailnum'], $_SESSION['sailor']['sailnum']);

$event_str = "";
foreach ($_SESSION['events']['details'] as $event) {
    $event_str .= $event['event_type'] . "|";
}
$event_str = rtrim($event_str, "|");

$change_params = array(
    "mode"    => $_SESSION['mode'],
    "evtypes"  => $event_str,
    "change"  => $_SESSION['change_fm']
);

if ($_SESSION['sailor']['crew'] == 1) {
    $_SESSION['change_fm']['crew']['status'] = false;
}

// assemble and render page
$_SESSION['pagefields']['header-center'] = $_SESSION['option_cfg'][$page]['pagename'];
$_SESSION['pagefields']['header-right'] = $tmpl_o->get_template("options_hamburger", array(),
                                                                array("page" => $page, "options" => set_page_options($page)));
$_SESSION['pagefields']['body'] = $tmpl_o->get_template("change_fm", $change_fields, $change_params);

echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
exit();
