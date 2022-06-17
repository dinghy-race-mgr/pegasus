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
//include ("{$loc}/common/lib/results_lib.php");          // FIXME needs to use templates   reports_tm.php + reports.css

$eventid = u_checkarg("eventid", "checkintnotzero","");
$format = u_checkarg("format", "set","");

if (!$eventid or empty($format))
{
    u_exitnicely($scriptname, 0, "$page page has an invalid or missing event identifier [{$_REQUEST['eventid']}] or the output report format has not been specified [{$_REQUEST['format']}]",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}
else
{
    u_initpagestart($_REQUEST['eventid'], $page, false);
}

// classes
include ("{$loc}/common/classes/template_class.php");
include ("{$loc}/common/classes/db_class.php");
include ("{$loc}/common/classes/race_class.php");

$tmpl_o = new TEMPLATE(array("../common/templates/general_tm.php", "./templates/layouts_tm.php"));

$db_o = new DB;
$race_o = new RACE($db_o, $eventid);

$total = 0;
for ($i = 1; $i <= $_SESSION["e_$eventid"]['rc_numfleets']; $i++)
{
   $entries[$i] = $race_o->race_getentries(array("fleet"=>$i));
   $count[$i]   = count($entries[$i]);
   $total = $total + $count[$i];
   $fleets[$i] = array("name" => $_SESSION["e_$eventid"]["fl_$i"]['name'], "desc" => $_SESSION["e_$eventid"]["fl_$i"]['name'], "count"=>$count[$i]);
}

// set report data structure
$rp_data = array(
    "admin" => array(
        "club"     => $_SESSION['clubname'],
        "event"    => $_SESSION["e_$eventid"]['ev_dname'],
        "sys-url"  => $_SESSION['sys_website'],
        "sys-name" => $_SESSION['sys_name'],
        "total"    => $total,
        "print"    => false,
        "paging"   => false,
        "table-border" => false,
        "table-style" => "width: 95%;"
    ),
    "attr" => array(
        "date"       => $_SESSION["e_$eventid"]['ev_date'],
        "time"       => $_SESSION["e_$eventid"]['ev_starttime'],
        "format"     => $_SESSION["e_$eventid"]['rc_name'],
        "starts" => $_SESSION["e_$eventid"]['rc_numstarts'],
        "sequence"   => $_SESSION["e_$eventid"]['rc_startscheme'],
    ),
    "fleets" => $fleets,
    "rows"   => $entries,
);

// select format
if ($format == "entrylist" or $format == "entrylistclub")
{
    $rp_data['cols'] = array(
        "class"   => array("label"=>"Class",    "style"=>"width: 15%; text-align: left; height: 2em;"),
        "sailnum" => array("label"=>"Sail No.", "style"=>"width: 10%; text-align: left;"),
        "helm"    => array("label"=>"Helm",     "style"=>"width: 20%; text-align: left;"),
        "crew"    => array("label"=>"Crew",     "style"=>"width: 20%; text-align: left;"),
        "club"    => array("label"=>"Club",     "style"=>"width: 20%; text-align: left;"),
        "pn"      => array("label"=>"PN",       "style"=>"width: 10%; text-align: left;")
    );

    $rp_data['admin']['report'] = "entry list";
    $rp_data['admin']['title'] = "entries";
    $rp_data['admin']['print'] = true;
    if ($format =="entrylist") { unset($rp_data['cols']['club']); }

    // create data for report as JSON and send to report creation script via POST
    echo u_sendJsonPost($_SESSION['baseurl']."/rm_reports/entrylist.php", $rp_data);

}
elseif($format == "declarationsheet")
{
    $rp_data['cols'] = array(
        "class"   => array("label"=>"Class",    "style"=>"width: 15%; text-align: left; border: 1px solid black; height: 2.5em"),
        "sailnum" => array("label"=>"Sail No.", "style"=>"width: 10%; text-align: left; border: 1px solid black; height: 2.5em"),
        "helm"    => array("label"=>"Helm",     "style"=>"width: 20%; text-align: left; border: 1px solid black; height: 2.5em"),
        "declare" => array("label"=>"Declaration", "style"=>"width: 55%; text-align: center; border: 1px solid black; height: 2.5em"),
    );

    $rp_data['admin']['report'] = "declaration sheet";
    $rp_data['admin']['title'] = "signoff";
    $rp_data['admin']['print'] = true;
    $rp_data['admin']['paging'] = true;
    $rp_data['admin']['table-border'] = true;
    $rp_data['admin']['table-style'] = "width: 95%; border-collapse: collapse; border: 1px solid black";

    // create data for report as JSON and send to report creation script via POST
    echo u_sendJsonPost($_SESSION['baseurl']."/rm_reports/entrylist.php", $rp_data);
}
elseif($format == "timingsheet")
{
    $rp_data['cols'] = array(
        "class"   => array("label"=>"Class",    "style"=>"width: 10%; text-align: left; height: 2em;"),
        "sailnum" => array("label"=>"Sail No.", "style"=>"width: 5%; text-align: left;"),
        "lap1"    => array("label"=>"1",        "style"=>"width: 10%; text-align: center;"),
        "lap2"    => array("label"=>"2",        "style"=>"width: 10%; text-align: center;"),
        "lap3"    => array("label"=>"3",        "style"=>"width: 10%; text-align: center;"),
        "lap4"    => array("label"=>"4",        "style"=>"width: 10%; text-align: center;"),
        "lap5"    => array("label"=>"5",        "style"=>"width: 10%; text-align: center;"),
        "lap6"    => array("label"=>"6",        "style"=>"width: 10%; text-align: center;"),
        "position"=> array("label"=>"POS",      "style"=>"width: 10%; text-align: center;"),
        "pn"      => array("label"=>"PN",       "style"=>"width: 5%; text-align: left;"),
    );

    $rp_data['admin']['report'] = "timing sheet";
    $rp_data['admin']['title'] = "timing";
    $rp_data['admin']['print'] = true;
    $rp_data['admin']['paging'] = true;
    $rp_data['admin']['table-border'] = true;
    $rp_data['admin']['table-style'] = "width: 95%; border-collapse: collapse; border: 1px solid black";

    // create data for report as JSON and send to report creation script via POST
    echo u_sendJsonPost($_SESSION['baseurl']."/rm_reports/entrylist.php", $rp_data);

}
else
{
    $fields = array(
        "title"      => "other",
        "loc"        => $loc,
        "stylesheet" => "$loc/style/rm_racebox.css",
        "navbar"     => "",
        "body"       => $tmpl_o->get_template("under_construction",
                        array("title"=>"Report: Entries", "info"=>"Selected report ($format) is not available yet")),
        "footer"     => "",
    );
    echo $tmpl_o->get_template("basic_page", $fields);
}


