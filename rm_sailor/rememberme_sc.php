<?php
/**
 *  creates a cookie for remembering a boat
 *  FIXME - this still needs to be implemented - not required for first release
 *
 *
 *
 */
$loc        = "..";
$page       = "rememberme";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_sailor_lib.php");

u_initpagestart(0,"rememberme_sc",false);   // starts session and sets error reporting

require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");

$db_o = new DB();
$tmpl_o = new TEMPLATE(array("../templates/sailor/layouts_tm.php", "../templates/sailor/layouts_tm.php"));

// check arguments
$sailorid = check_argument("sailor", "checkint", "");

if ($sailorid) { // get protest information
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("under_construction",
        array('title' => "Remember Me", 'info' => "This function will be available in a future release"),
        array('back_button' => true, 'back_url' => 'race_pg.php'));

} else {       // report system error
    $error_fields = array(
        "error" => "The 'remember me' sailor information is not valid",
        "detail" => "problem was - sailor not specified or invalid or not found",
        "action" => "Please report this to your system administrator"
    );
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("error_msg", $error_fields);
}

echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
exit();