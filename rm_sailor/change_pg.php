<?php
/**
 * mob_pg_change.php - allows user to change details for signon
 * 
 * Allows sail number and/or crew to be changed 
 *  
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 *   
 * 
 */
$loc        = "..";       
$page       = "change";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");

u_initpagestart(0,"changen_pg",false);   // starts session and sets error reporting

// libraries
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");

$tmpl_o = new TEMPLATE(array( "../templates/sailor/layouts_tm.php", "../templates/sailor/signon_tm.php"));

if (empty($_SESSION['sailor']['change']))
{
    $editboat_fields = array(
        "compid" => $_SESSION['sailor']['id'],
        "helm"   => $_SESSION['sailor']['helmname'],
        "crew"   => $_SESSION['sailor']['crewname'],
        "sailnum"=> $_SESSION['sailor']['sailnum']
    );
}
else
{
    $editboat_fields = array(
        "compid"  => $_SESSION['sailor']['id'],
        "helm"    => u_pick($_SESSION['sailor']['chg-helm'], $_SESSION['sailor']['helmname']),
        "crew"    => u_pick($_SESSION['sailor']['chg-crew'], $_SESSION['sailor']['crewname']),
        "sailnum" => u_pick($_SESSION['sailor']['chg-sailnum'], $_SESSION['sailor']['sailnum'])
    );
}


$editboat_params = array(
    "points_allocation" => $_SESSION['points_allocation'],
    "singlehander"      => $_SESSION['sailor']['crew'] == 1,
);

$_SESSION['pagefields']['body'] = $tmpl_o->get_template("change_fm", $editboat_fields, $editboat_params);
echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);


?>