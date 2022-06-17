<?php
/**
 * Allows sailor to submit a protest
 *
 */
$loc        = "..";       
$page       = "protest";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_sailor_lib.php");

u_initpagestart(0,"results_pg",false);   // starts session and sets error reporting

require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");

$db_o = new DB();
$tmpl_o = new TEMPLATE(array("./templates/layouts_tm.php", "./templates/protest_tm.php"));

// check arguments
$eventid = u_checkarg("event", "checkint", "");

if ($eventid) { // get protest information
    $_SESSION['pagefields']['header-center'] = $_SESSION['option_cfg'][$page]['pagename'];
    $_SESSION['pagefields']['header-right'] = $tmpl_o->get_template("options_hamburger", array(),
        array("page" => $page, "options" => set_page_options($page)));
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("under_construction",
        array('title' => "Submit Protest", 'info' => "This function will be available in a future release"),
        array('back_button' => true, 'back_url' => 'race_pg.php'));

} else {       // report system error
    $error_fields = array(
        "error" => "The protest page event information is not valid",
        "detail" => "problem was - event not specified or invalid",
        "action" => "Please report this to your system administrator",
        "url" => "index.php"
    );
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("error_msg", $error_fields, array("restart"=>true));
}

echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
exit();
