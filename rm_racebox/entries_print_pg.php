<?php
/* ------------------------------------------------------------------------
   rbx_pg_printentries.php
   
   Produces various output formats for the list of entries
   
   parameters:
        eventid:    id for event
        format:     format code for required output
   
   Elmswood Software 2014
   ------------------------------------------------------------------------
  
*/

$loc   = "..";
$page  = "printentries"; 
$scriptname = basename(__FILE__);  
$today = date("Y-m-d");
include ("{$loc}/common/lib/util_lib.php");
//include ("{$loc}/common/lib/html_lib.php");
//include ("{$loc}/common/lib/rm_lib.php");
include ("{$loc}/common/lib/results_lib.php");          // FIXME needs to use templates

u_initpagestart($_REQUEST['eventid'], $page, $_REQUEST['menu']);

include ("{$loc}/common/classes/template_class.php");
include ("{$loc}/common/classes/html_class.php");       // FIXME required for results_lib
include ("{$loc}/common/classes/db_class.php");
include ("{$loc}/common/classes/race_class.php");

include ("{$loc}/config/{$_SESSION['lang']}-racebox-lang.php");

$tmpl_o = new TEMPLATE(array("../templates/general_tm.php",
    "../templates/racebox/layouts_tm.php",
    "../templates/racebox/navbar_tm.php",
    "../templates/racebox/entries_tm.php"));


if (empty($_REQUEST['eventid']))
{
    u_exitnicely($scriptname, "", $lang['msg']['race_not_specified'], "event id specified as {$_REQUEST['eventid']}" );
}
else
{
//    $tblcols = array(
//        array("label" => "class", "width" => "50%", "attr"  => "" ),
//        array("label" => "sailnum", "width" => "25%", "attr"  => "" ),
//        array("label" => "helm", "width" => "25%", "attr"  => "" ),
//               );
//    $cols = 3;
    
    $eventid = $_REQUEST['eventid'];
    $format  = $_REQUEST['format'];

    $db_o = new DB;
    $race_o = new RACE($db_o, $eventid);
    
    for ($i = 1; $i <= $_SESSION["e_$eventid"]['rc_numfleets']; $i++)
    {
       //$race_o = new RACE($db_o, $eventid);
       $entries[$i] = $race_o->race_getentries(array("fleet"=>$i));
       $count[$i]   = count($entries[$i]);          
    }
    
    // select format
    if ($format == "entrylist")
    {
        $ignore = array("id", "code");
        $html = s_createEntryList($eventid, "Entry List", $entries, $ignore);
        $fields = array(
            "title"      => "entry list",
            "stylesheet" => "$loc/style/rm_report.css",
            "body"       => $html,
        );
        echo $tmpl_o->get_template("report_page", $fields);
    }
    elseif($format == "declarationsheet")
    {
        $ignore = array("id", "pn", "helm", "crew", "club");
        $html = s_createDeclarationSheet($eventid, "Declaration Sheet", $entries, $ignore,
                                         $_SESSION['declaration_pagination']);
        $fields = array(
            "title"      => "signoff sheet",
            "stylesheet" => "$loc/style/rm_report.css",
            "body"       => $html,
        );
        echo $tmpl_o->get_template("report_page", $fields);
    }
    elseif($format == "timingsheet")
    {
        $ignore = array("id", "crew", "club");
        $html = s_createTimingSheet($eventid, "Timing Sheet", $entries, $ignore,
                                    $_SESSION['timing_pagination']);
        $fields = array(
            "title"      => "timing sheet",
            "stylesheet" => "$loc/style/rm_report.css",
            "body"       => $html,
        );
        echo $tmpl_o->get_template("report_page", $fields);
    }
    else
    {
        $fields = array(
            "title"      => "entry list",
            "loc"        => $loc,
            "stylesheet" => "$loc/style/rm_racebox.css",
            "navbar"     => "",
            "body"       => $tmpl_o->get_template("under_construction",
                            array("title"=>"Report: Entries", "info"=>"Selected report ($format) is not available yet")),
            "footer"     => "",
        );
        echo $tmpl_o->get_template("basic_page", $fields);
    }
}

?>