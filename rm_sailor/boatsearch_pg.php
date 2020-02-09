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
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/event_class.php");
require_once ("./include/rm_sailor_lib.php");

u_initpagestart(0,"boatsearch_pg",false);   // starts session and sets error reporting

$tmpl_o = new TEMPLATE(array( "../templates/sailor/layouts_tm.php", "../templates/sailor/search_tm.php"));

$opt_map = get_options_map("boatsearch");
$options = array();
foreach ($opt_map as $opt)
{
    if (array_key_exists($opt, $_SESSION['option_cfg']))
    {
        $options[] = $_SESSION['option_cfg'][$opt];
    }
}

// clear entry
unset($_SESSION['entry']);
unset($_SESSION['races']);

$db_o = new DB();
$event_o = new EVENT($db_o);

// get events for today - or from list passed as arguments
$_SESSION['events'] = get_event_details($_SESSION['event_passed']);

if ($_SESSION['events']['numevents'] == 0)
{
    $events_bufr = $tmpl_o->get_template("no_events", array(), $_SESSION['events']);
}
elseif ($_SESSION['events']['numevents'] > 0)
{
    $events_bufr = $tmpl_o->get_template("list_events", array(), $_SESSION['events']);
}
else
{
    $events_bufr = $tmpl_o->get_template("error_msg",
        array("error"=> "Race Configuration Error",
              "detail"=> "The system configuration for today's race is invalid",
              "action" => "Please contact the race Officer to enter the race"), $_SESSION['events']);
}
$_SESSION['pagefields']['body'] = $tmpl_o->get_template("boatsearch_form", array("events_bufr"=>$events_bufr));
$_SESSION['pagefields']['header-right'] = $tmpl_o->get_template("options_hamburger", array(), array("options" => $options));

echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields'] );
    
// set focus on search field
echo <<<EOT
    <script>$("#sailnum").focus();</script>
EOT;

