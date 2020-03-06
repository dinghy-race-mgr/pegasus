<?php
/**
 * pickboat_sc - deals with user selection of boat - setting details of selected boat into $_SESSION
 * 
 * In the case where the competitor search finds more than one
 * competitor, the user will pick the correct competitor.  This script 
 * finds the relevant competitor details, sets them as session
 * variable entry and then either redirects back to the options page or to the already selected option page
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 */
$loc        = "..";       
$page       = "pickboat_sc";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");      
require_once ("{$loc}/common/lib/util_lib.php");

u_initpagestart(0,$page,false);   // starts session and sets error reporting

// libraries
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/comp_class.php");

$tmpl_o = new TEMPLATE(array("../templates/sailor/layouts_tm.php"));

// check if competitor id has been passed
if (isset($_REQUEST['compid']) and is_numeric($_REQUEST['compid']))
{
    // connect to database to get competitor information
    $db_o = new DB();
    $comp_o = new COMPETITOR($db_o);

// get details for competitor id - can only return one as this is primary key
    $competitors = $comp_o->comp_findcompetitor(array("id"=>$_REQUEST['compid']));
    $competitors ? $numcompetitors = count($competitors) : $numcompetitors = 0;

    if ($numcompetitors==1)  // found
    {
        $_SESSION['sailor'] = $competitors[0];
        $_SESSION['sailor']['change'] = false;
        $_SESSION['sailor']['chg-sailnum'] = "";
        $_SESSION['sailor']['chg-helm'] = "";
        $_SESSION['sailor']['chg-crew'] = "";

        // if no races today - go to options  FIXME - shouldn't need this
        if ($_SESSION['events']['numevents'] <= 0)
        {
            header("Location: options_pg.php");
            exit();
        }
        else
        {
            header("Location: race_pg.php?state=init");
            exit();
        }
    }
    else // single match not found - report error
    {
        u_writelog("Fatal Error: selected boat from search list not found", "");
        $error_fields = array(
            "error"  => "Boat selected from search not located",
            "detail" => "Boat (id: {$_REQUEST['compid']}) selected from list cannot be retrieved (pickboat_sc.php: records found $numcompetitors)",
            "action" => "Please report error to your raceManager administrator",
        );
        $_SESSION['pagefields']['body'] = $tmpl_o->get_template("error_msg", $error_fields);
        echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
        exit();
    }
}
else
{
    // id passed is not valid
    u_writelog("Fatal Error: selected boat from search list not recognised", "");
    $error_fields = array(
        "error"  => "Fatal Error: selected boat from search list not recognised",
        "detail" => "Boat ({$_REQUEST['compid']}) selected from list does not have a valid identifier (pickboat_sc.php)",
        "action" => "Please report error to your raceManager administrator",
    );
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("error_msg", $error_fields);
    echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
}

