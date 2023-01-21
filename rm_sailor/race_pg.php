<?php
/**
 * race process administration page
 *
 * User can enter a race, and optionally declare or retire from a race.  Also
 * provides links to the results a protest submission page for each race.
 *
 */
$loc        = "..";
$page       = "race";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/lib/rm_lib.php");
require_once ("./include/rm_sailor_lib.php");

// start session
session_id('sess-rmsailor');
session_start();

// initialise page
u_initpagestart(0,$page,false);

require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/entry_class.php");
require_once ("{$loc}/common/classes/event_class.php");

// arguments
$external = u_checkarg("state", "setbool", "init", true);
$action = array(
    "event"  => u_checkarg("event", "set", "", 0),
    "type"   => u_checkarg("action", "set", ""),
    "status" => u_checkarg("status", "set", ""),
    "msg"    => u_checkarg("msg", "set", "")
);

// connect to database to get event information
$db_o = new DB();
$event_o = new EVENT($db_o);
$tmpl_o = new TEMPLATE(array("./templates/layouts_tm.php", "./templates/race_tm.php"));

// update event details
$_SESSION['events'] = get_event_details($_SESSION['sailor_event_window'], $_SESSION['event_passed']);

// update information on entries
$_SESSION['entries'] = get_entry_information($_SESSION['sailor']['id'], $_SESSION['events']['details']);

// set up boat information
$race_fields = set_boat_details();
$race_fields["boat-label"] = $tmpl_o->get_template("boat_label", $race_fields,
             array("change"=>true, "change_set"=>$_SESSION['sailor']['change'], "type" => "race", ));

// get plugin links
$plugin_htm = array( "1"=>"&nbsp;", "2"=>"&nbsp;", "3"=>"&nbsp;",);
foreach ($_SESSION['plugins'] as $k => $plugin)
{
    if ($plugin['configured'])
    {
        $plugin_htm["$k"].=  file_get_contents("./plugins/{$plugin['name']}/include_link.htm");
    }
}

// display race page
if ($_SESSION['events']['numevents'] > 0)
{
    $signon_entry_list = set_event_status_list($_SESSION['events']['details'], $_SESSION['entries'], $action);

    //echo "<pre>".print_r($signon_entry_list,true)."</pre>"; exit();

    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("race_control", $race_fields,
        array('state'=>"submitentry", 'numdays'=> $_SESSION['events']['numdays'],
              'event-list'=>$signon_entry_list, 'opt_cfg' =>$_SESSION['option_cfg'], "data" => $race_fields, "plugins" => $plugin_htm ));
}

else
{
    $event_list = $tmpl_o->get_template("no_events", array(), $_SESSION['events']);
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("race_control", $race_fields,
        array('state' => "noevents", 'event-list' => $event_list) );
}

// assemble and render page
$_SESSION['pagefields']['header-center'] = $_SESSION['option_cfg'][$page]['pagename'];
$_SESSION['pagefields']['header-right'] = $tmpl_o->get_template("options_hamburger", array(),
    array("page" => $page, "options" => set_page_options($page)));

// add automated timed return to search page if usage and delay are configured
$_SESSION['pagefields']['body'].= add_auto_continue($_SESSION['usage'], $_SESSION['sailor_race_sleep_delay'],
    $external, "search_pg.php");

if (empty($_SESSION['plugins']))
{
    $params = array();
}
else
{
    $plugin_scripts_htm = "";
    foreach ($_SESSION['plugins'] as $plugin)
    {
        if ($plugin['configured'])
        {
            $plugin_scripts_htm.=  file_get_contents("./plugins/{$plugin['name']}/include_scripts.htm");
        }
    }
    $params['plugin'] = $plugin_scripts_htm;
}

//// add scripts required for plugins if necessary  FIXME - needs to relate to config + handle multiple plugins
//$plugin_scripts_files = array("plugin_1" => "./plugins/qfo/include_scripts.htm");

echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields'], $params);
exit();



