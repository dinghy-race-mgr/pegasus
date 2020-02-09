<?php
/**
 * protest_pg - allows sailor to submit a protest
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
require_once ("{$loc}/common/classes/template_class.php");

$db_o = new DB();
$tmpl_o = new TEMPLATE(array("../templates/sailor/layouts_tm.php", "../templates/sailor/protest_tm.php"));

// get mode and event from $_REQUEST if they are set
$eventid = 0;

// check arguments
$args_ok = false;
$args_err = "unknown";
if (isset($_REQUEST['event']))
{
    if (ctype_digit($_REQUEST['event']))  // check for integer
    {
        $eventid = $_REQUEST['event'];
        $args_ok = true;
    }
    else
    {
        $args_err = "event identifier not valid";
    }
}
else
{
    $args_err = "event not specified";
}


// get protest information
if ($args_ok)
{
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("under_construction",
        array('title'=>"Submit Protest", 'info'=>"This function will be available in a future release"),
        array('back_button'=>true, 'back_url'=>'race_pg.php'));
}
else         // report system error
{
    $error_fields = array(
        "error"=>"The protest page event information is not valid",
        "detail"=>"problem was - $args_err",
        "action" =>"Please report this to your system administrator"
    );
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("error_msg", $error_fields);
}

echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
exit();
