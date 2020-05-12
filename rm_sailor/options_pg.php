<?php
/**
 * options_pg - script that allows user to select the function they need
 * FIXME  - no longer used
 *   

 */
$loc        = "..";                                 // path to root directory
$scriptname = basename(__FILE__);                   // script name
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_sailor_lib.php");

u_initpagestart(0,"options_pg",false);               // starts session and sets error reporting

// libraries
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/template_class.php");

// create classes
$db_o = new DB();               // set database class
$event_o = new EVENT($db_o);    // set event class
$tmpl_o = new TEMPLATE(array( "../templates/sailor/layouts_tm.php", "../templates/sailor/search_tm.php"));

// get event details - in case they have changed
$_SESSION['events'] = get_event_details($_SESSION['event_passed']);

if ($_SESSION['sailor']['id'] != 0)   // we know the sailor
{
  $boat = set_boat_details();
  $option_fields['boat-label'] = $tmpl_o->get_template("boat_label", $boat, array("change"=>true));

  $current_options = $_SESSION['option_cfg'];
  if ($_SESSION['events']['numevents'] < 1)
  {
      // remove options that are not relevant if no races
      foreach($_SESSION['option_race_cfg'] as $k => $r)
      {
          if (key_exists($r, $current_options)) { unset($current_options[$r]); }
      }
  }

  $_SESSION['pagefields']['body'] = $tmpl_o->get_template("options", $option_fields, array("options" => $current_options, "numevents" => $_SESSION['events']['numevents']));
  echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
  exit();

}
else
{
   header("Location: boatsearch_pg.php");
   exit();
}
