<?php
/**
 * change_sc.php - deals with changes to boat details
 * 
 * @
 *
 */
$loc        = "..";       
$page       = "change_sc";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");      
require_once ("{$loc}/common/lib/util_lib.php");

u_initpagestart(0,"change_sc",false);   // starts session and sets error reporting

// libraries
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/comp_class.php");

$tmpl_o = new TEMPLATE(array("../templates/sailor/layouts_tm.php"));

// check if change is temporary or permanent
if ($_REQUEST['scope']=="perm" OR $_REQUEST['scope']=="temp")        // details changed
{
    $update_fields = array();
    $_SESSION['sailor']['change']  =  $_REQUEST['scope'];

    $_SESSION['sailor']['chg-sailnum'] = ucwords(strtolower(u_change($_REQUEST['sailnum'], $_SESSION['sailor']['sailnum'])));
    if ($_SESSION['sailor']['chg-sailnum']) { $update_fields['sailno'] = $_SESSION['sailor']['chg-sailnum']; }

    $_SESSION['sailor']['chg-helm'] = ucwords(strtolower(u_change($_REQUEST['helm'], $_SESSION['sailor']['helmname'])));
    if ($_SESSION['sailor']['chg-helm']) { $update_fields['helm'] = $_SESSION['sailor']['chg-helm']; }

    $_SESSION['sailor']['chg-crew'] = ucwords(strtolower(u_change($_REQUEST['crew'], $_SESSION['sailor']['crewname'])));
    if ($_SESSION['sailor']['chg-crew']) { $update_fields['crew'] = $_SESSION['sailor']['chg-crew']; }
    
    // if permanent change - update t_competitor
    if ($_REQUEST['scope']=="perm")
    {
        $db_o = new DB(); 
        $comp_o = new COMPETITOR($db_o);
        $status = $comp_o->comp_updatecompetitor($_SESSION['sailor']['id'], $update_fields);
        
        if ($status != "failed")
        {
            $_SESSION['sailor']['sailnum']  = u_change($_SESSION['sailor']['chg-sailnum'], $_SESSION['sailor']['sailnum']);
            $_SESSION['sailor']['helmname'] = u_change($_SESSION['sailor']['chg-helm'], $_SESSION['sailor']['helm']);
            $_SESSION['sailor']['crewname'] = u_change($_SESSION['sailor']['chg-crew'], $_SESSION['sailor']['crew']);
        }
        else
        {
            $_SESSION['pagefields']['body'] = $tmpl_o->get_template("error_msg", array(
                "error"  => "Unable to change boat details",
                "detail" => "",
                "action" => "Please report error to your raceManager administrator"));
            echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
            exit();
        }
    }
    else
    {
        if ($_SESSION['sailor']['chg-helm']) { $_SESSION['sailor']['helmname'] = $_SESSION['sailor']['chg-helm']; }
        if ($_SESSION['sailor']['chg-crew']) { $_SESSION['sailor']['crewname'] = $_SESSION['sailor']['chg-crew']; }
        if ($_SESSION['sailor']['chg-sailnum']) { $_SESSION['sailor']['sailnum'] = $_SESSION['sailor']['chg-sailnum']; }
    }
} 

else         // system error - action not set or not recognised
{
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("error_msg", array(
        "error"  => "Type of change undefined",
        "detail" => "Change scope [{$_REQUEST['scope']}] not recognised",
        "action" => "Please report error to your raceManager administrator"));
    echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
    exit();
}

header("Location: signon_pg.php?");
exit();

?>