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

// start session
session_id('sess-rmsailor');
session_start();

// initialise page
u_initpagestart(0,$scriptname,false);

require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");

$db_o = new DB();
$tmpl_o = new TEMPLATE(array("./templates/layouts_tm.php"));

// check arguments
$sailorid = u_checkarg("sailor", "checkint", "");
$searchstr = u_checkarg("searchstr", "set", "", "");

if ($sailorid) { //
    $_SESSION['pagefields']['header-center'] = $_SESSION['option_cfg'][$page]['pagename'];
    $_SESSION['pagefields']['header-right'] = $tmpl_o->get_template("options_hamburger", array(),
        array("page" => $page, "options" => set_page_options($page)));
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("under_construction",
        array('title' => "Remember Me", 'info' => "This function will be available in a future release"),
        array('back_button' => true, 'back_url' => 'pickboat_pg.php?searchstr=$searchstr'));

} else {       // report system error
    $error_fields = array(
        "error" => "The 'remember me' sailor information is not valid",
        "detail" => "problem was - sailor not specified or invalid or not found",
        "action" => "Please report this to your system administrator",
        "url" => "index.php"
    );
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("error_msg", $error_fields, array("restart"=>true));
}

echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
exit();