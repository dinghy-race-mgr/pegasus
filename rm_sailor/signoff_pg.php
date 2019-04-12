<?php
/**
 * signoff_pg
 * Allows sailor to declare or retire from race. Also allows user
 * to notify race committee of wish to submit protest.
 *
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 *   
 * 
 */
$loc        = "..";       
$page       = "signoff";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_sailor_lib.php");

u_initpagestart(0,"signoff_pg",false);   // starts session and sets error reporting

// libraries
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/entry_class.php");
require_once ("{$loc}/common/classes/event_class.php");

// connect to database to get event information
$db_o = new DB();
$tmpl_o = new TEMPLATE(array("../templates/sailor/layouts_tm.php", "../templates/sailor/signoff_tm.php"));

// get information on entries
$_SESSION['entries'] = get_entry_information($_SESSION['sailor']['id'], $_SESSION['events']['details']);


if (empty($_SESSION['sailor']['change']))
{
    $signoff_fields = array(
        "id"      => $_SESSION['sailor']['id'],
        "class"   => $_SESSION['sailor']['classname'],
        "sailnum" => $_SESSION['sailor']['sailnum'],
        "helm"    => $_SESSION['sailor']['helmname'],
        "crew"    => $_SESSION['sailor']['crewname']
    );
}
else
{
    $signoff_fields = array(
        "id"      => $_SESSION['sailor']['id'],
        "class"   => $_SESSION['sailor']['classname'],
        "sailnum" => u_pick($_SESSION['sailor']['chg-sailnum'], $_SESSION['sailor']['sailnum']),
        "helm"    => u_pick($_SESSION['sailor']['chg-helm'], $_SESSION['sailor']['helmname']),
        "crew"    => u_pick($_SESSION['sailor']['chg-crew'], $_SESSION['sailor']['crewname'])
    );
}
$signoff_fields['team'] = u_conv_team($signoff_fields['helm'], $signoff_fields['crew'], 0);

if($_SESSION['events']['numevents'] <= 0)  // check if we have any states
{
    $signoff_fields['state'] = "noevents";
    $signoff_fields["next-event-name"] = $_SESSION['events']['nextevent']['event_name'];
    $signoff_fields["next-event-date"] = date("jS M", strtotime($_SESSION['events']['nextevent']['event_date']));
    $signoff_fields["next-event-start-time"] = $_SESSION['events']['nextevent']['event_start'];

    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("signoff", $signoff_fields, array('state' => "noevents"));
    echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
}
elseif (count_entries($_SESSION['entries']) <= 0)     // check if we have any entries
{
    $signon_fields['state'] = "noentries";
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("signoff", $signoff_fields, array('state' => "noentriess"));
    echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
}
else  // we have some entries - display them
{
    $signon_fields['state'] = "entries";
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("signoff", $signoff_fields,
        array('state' => "entries", 'events' => $_SESSION['events']['details'],
              'entries' => $_SESSION['entries'], 'protest_option' => $_SESSION['sailor_protest']));
    echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
}

?>