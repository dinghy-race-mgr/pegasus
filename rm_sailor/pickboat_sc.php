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

u_initpagestart(0,"boatsearch_pg",false);   // starts session and sets error reporting

// libraries
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/comp_class.php");   

// check if competitor id has been passed
if (isset($_REQUEST['compid']) and is_numeric($_REQUEST['compid']))
{
    // connect to database to get competitor information
    $db_o = new DB();
    $comp_o = new COMPETITOR($db_o);

// get details for competitor id - can only return one as this is primary key
    $competitors = $comp_o->comp_findcompetitor(array("id"=>$_REQUEST['compid']));
    $numcompetitors = 0;
    if ($competitors)  {  $numcompetitors = count($competitors);  }

    if ($numcompetitors!=1)  // not found - this should not be possible so close
    {
        u_writelog("Fatal Error: selected boat from search list not found", "");
        $error_fields = array(
            "error"  => "Boat selected from search list not located",
            "detail" => "Boat (id: {$_REQUEST['compid']}) selected from list cannot be retrieved (pickboat_sc.php: records found $numcompetitors)",
            "action" => "Please report error to your raceManager administrator",
        );
        $tmpl_o = new TEMPLATE(array("../templates/sailor/layouts_tm.php", "../templates/sailor/search_tm.php"));
        $_SESSION['pagefields']['body'] = $tmpl_o->get_template("error_msg", $error_fields);
        echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
        exit();
    }
    else // one match found - go straight to option
    {
        $_SESSION['sailor'] = $competitors[0];

        $_SESSION['sailor']['change'] = false;
        $_SESSION['sailor']['chg-sailnum'] = "";
        $_SESSION['sailor']['chg-helm'] = "";
        $_SESSION['sailor']['chg-crew'] = "";

        // check if option is defined and valid - if not go to options page
        if (!empty($_REQUEST['option']) AND array_key_exists($_REQUEST['option'], $_SESSION['option_cfg']))
        {
            header("Location: {$_REQUEST['option']}_pg.php");
            exit();
        }
        else
        {
            header("Location: options_pg.php");
            exit();
        }
    }
}
else
{
    // id passed is not valid
    u_writelog("Fatal Error: selected boat from search list not recognised", "");
    $error_fields = array(
        "error"  => "Fatal Error: selected boat from search list not recognised",
        "detail" => "Boat id ({$_REQUEST['compid']}) selected from list is not a valid id (pickboat_sc.php)",
        "action" => "Please report error to your raceManager administrator",
    );
    $tmpl_o = new TEMPLATE(array("../templates/sailor/layouts_tm.php", "../templates/sailor/search_tm.php"));
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("error_msg", $error_fields);
    echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
    exit();
}


?>