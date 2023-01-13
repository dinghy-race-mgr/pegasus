<?php
/*
 * util version needs to call pursuit_class or pursuit_lib to do this
 * then racebox can use same functions
 */
$loc  = "..";
$page = "pursuit_starts";     //
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
$styletheme = "flatly_";
$stylesheet = "./style/rm_utils.css";
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/lib/rm_lib.php");
require_once ("{$loc}/common/lib/pursuit_lib.php");

session_id("sess-rmutil-".str_replace("_", "", strtolower($page)));
session_start();


// initialise session if this is first call
$init_status = u_initialisation("$loc/config/rm_utils_cfg.php", $loc, $scriptname);

if ($init_status)
{
    // set timezone
    if (array_key_exists("timezone", $_SESSION)) { date_default_timezone_set($_SESSION['timezone']); }

    // start log
    error_log(date('H:i:s')." -- rm_util HANDICAPS --------------------[session: ".session_id()."]".PHP_EOL, 3, $_SESSION['syslog']);

    // set initialisation flag
    $_SESSION['util_app_init'] = true;
}
else
{
    u_exitnicely($scriptname, 0, "one or more problems with script initialisation",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

// classes
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/boat_class.php");
require_once ("{$loc}/common/classes/template_class.php");

// connect to database
$db_o = new DB();
$boat_o = new BOAT($db_o);

// set templates
$tmpl_o = new TEMPLATE(array("../common/templates/general_tm.php","./templates/layouts_tm.php",
                             "./templates/pursuit_util_tm.php", "../rm_racebox/templates/pursuit_tm.php"));

$pagefields = array(
    "loc" => $loc,
    "theme" => $styletheme,
    "stylesheet" => $stylesheet,
    "title" => "Pursuit Start Times",
    "header-left" => "raceManager",
    "header-right" => "Pursuit Start Times",
    "footer-left" => "",
    "footer-center" => "",
    "footer-right" => ""
);

/* -------- form here to collect info -------------------*/
if (empty($_REQUEST['pagestate'])) { $_REQUEST['pagestate'] = "init"; }

if ($_REQUEST['pagestate'] == "init")
{

    $args = array(
        "pytype"    => "",
        "fastclass" => "",
        "slowclass" => "",
        "timelimit" => "90",
        "boattypes" => "D",
        "interval"  => "60",
    );

    if (!empty($_REQUEST['format']))
    {
        if(!empty($_REQUEST['pytype'])) { $args['pytype'] = $_REQUEST['pytype']; }
        if(!empty($_REQUEST['fastclass'])) { $args['fastclass'] = $_REQUEST['fastclass']; }
        if(!empty($_REQUEST['slowclass'])) { $args['slowclass'] = $_REQUEST['slowclass']; }
        if(!empty($_REQUEST['timelimit'])) { $args['timelimit'] = $_REQUEST['timelimit']; }
        if(!empty($_REQUEST['interval'])) { $args['interval'] = $_REQUEST['interval']; }
        if(!empty($_REQUEST['boattypes'])) { $args['boattypes'] = $_REQUEST['boattypes']; }
    }

    // get list of classes
    $classes = $boat_o->getclasses(array(), $sort=array("nat_py"=>"DESC"));

    // use form to get parameters for start time report
    $formfields = array(
        "instructions" => "Enter the relevant detail in the form below and click Get Start Times to produce the start list</br>",
        "script" => "pursuit_starttimes.php?pagestate=submit",
    );

    // present form to select json file for processing (general template)
    $params = array();
    $pagefields['body'] =  $tmpl_o->get_template("pursuit_start_form", $formfields, array("classes"=>$classes, "args"=>$args));
    echo $tmpl_o->get_template("basic_page", $pagefields );
}
elseif ($_REQUEST['pagestate'] == "submit")
{

    //echo "<pre>".print_r($_REQUEST,true)."</pre>";

    // get/check arguments
    $pntype     = u_checkarg('pntype', 'set', "", "national");      // selects nat_py or local_py field
    $length     = u_checkarg('length', 'set', "", 0);               // length of race in minutes
    $maxclassid = u_checkarg('maxclassid', 'set', "", 0);           // class id for slowest class
    $minclassid = u_checkarg('minclassid', 'set', "", 0);           // class id for fastest class
    $startint   = u_checkarg('startint', 'set', "", 60);            // interval between starts in seconds
    $dinghy     = u_checkarg('typeD', 'set', "1", "");              // dinghy classes included
    $keelboat   = u_checkarg('typeK', 'set', "1", "");              // keelboat classes included
    $foiler     = u_checkarg('typeF', 'set', "1", "");              // foiler classes included
    $multihull  = u_checkarg('typeM', 'set', "1", "");              // multihull classes included

    // select py to use
    $pntype == "national" ? $py_field = "nat_py" : $py_field = "local_py";

    // get pn for fastest and slowest classes
    $maxdata = get_class_pn($maxclassid, $pntype);   // slowest
    $mindata = get_class_pn($minclassid, $pntype);   // fastest

    // create array of allowed boat types
    $btype_arr = get_boat_types_array($dinghy,$keelboat,$foiler,$multihull);

    // get all classes from t_class
    $classes = $boat_o->getclasses(array(), $sort=array("$py_field"=>"DESC"));

    // get start time report data
    $starts = p_getstarts_class($classes, $mindata['pn'], $maxdata['pn'], $py_field, $length, $startint, $btype_arr);

    $fields = array(
        "length"     => $length,
        "maxclass"   => $maxdata['class'],
        "minclass"   => $mindata['class'],
        "startint"   => $startint,
        "pntype"     => $pntype,
        "start-info" => render_start_by_class($starts)
    );
    $pagefields['body'] = $tmpl_o->get_template("start_by_class", $fields);
    echo $tmpl_o->get_template("basic_page", $pagefields );
}
else
{
    // report error
    $params = array();
    $pagefields['body'] =  $tmpl_o->get_template("pursuit_starttimes_err", array(), array());
    echo $tmpl_o->get_template("basic_page", $pagefields );
}



function get_class_pn($classid, $pntype)
{
    global $boat_o;
    $data = array();

    if (empty($classid) or empty($pntype) or ($pntype != "national" and $pntype != "local"))
    {
        $data = false;
    }
    else
    {
        $row = $boat_o->boat_getdetail("", $classid);
        if ($row)
        {
            $pntype == "local" ? $data['pn'] = $row['local_py'] : $data['pn'] = $row['nat_py'] ;
            $data['class'] = $row['classname'];
        }
        else
        {
            $data = false;
        }
    }

    return $data;
}

function get_boat_types_array($d,$k,$f,$m)
{
    $boat_types_arr = array();
    if ($d) { $boat_types_arr[] = "D"; }
    if ($k) { $boat_types_arr[] = "K"; }
    if ($f) { $boat_types_arr[] = "F"; }
    if ($m) { $boat_types_arr[] = "M"; }

    return $boat_types_arr;
}

function render_start_by_class($starts)
{
    $bufr = "";
    $this_start = -1;
    foreach ($starts as $i => $start)
    {
        $start['popular'] ? $style = "font-weight: bold;" : $style = "";

        $diff = (int)$start['start'] - $this_start ;
        if ($diff > 0)                                                                            // next start
        {
            if (!empty($bufr)) { $bufr.= "</td></tr>"; }                                          // finish incomplete tow

            if ($diff > 1 ) { $bufr.= "<tr><td>&nbsp;</td><td>----</td></tr>"; }                  // missing start - leave a gap

            $bufr.= "<tr><td >{$start['start']} mins &nbsp;&nbsp;&nbsp;&nbsp;</td>
                         <td ><span style=\"$style\">{$start['class']}</span>";        // start + first class
        }
        else
        {
            $bufr.= ", <span style=\"$style\">{$start['class']}</span>";                // add additional classes
        }
        $this_start = (int)$start['start'];
    }

    return $bufr;
}