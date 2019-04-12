<?php
/**
 * boatsearch_pg
 * 
 * @abstract  Form to allow user to enter search string for competitor search.
 *            Passes control to boatsearch_sc.  Will try to interpret search
 *            string as sailnumber, class name, or surname of helm.
 * 
 * @author Mark Elkington <racemanager@gmail.com>
 * 
 * %%copyright%%
 * %%license%%
 *   
 */
$loc        = "..";       
$page       = "boatsearch_pg";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");

u_initpagestart(0,"boatsearch_pg",false);   // starts session and sets error reporting

// libraries
require_once ("{$loc}/common/classes/template_class.php");
$tmpl_o = new TEMPLATE(array( "../templates/sailor/layouts_tm.php", "../templates/sailor/search_tm.php"));

// clear entry
unset($_SESSION['entry']);
unset($_SESSION['races']);


if ($_SESSION['events']['numevents'] <= 0)
{
    $events_bufr = $tmpl_o->get_template("noevents", array(), $_SESSION['events']);
}
else
{
    $events_bufr = $tmpl_o->get_template("listevents", array(), $_SESSION['events']);
}
$_SESSION['pagefields']['body'] = $tmpl_o->get_template("boatsearch_form", array("events_bufr"=>$events_bufr));

echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields'] );
    
// set focus on search field
echo <<<EOT
    <script>$("#sailnum").focus();</script>
EOT;

?>