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
    empty($_REQUEST['caller']) ? $caller = "util" : $caller = $_REQUEST['caller'];

    if ($caller == "race_pg")
    {
        // get args data from file
        $cfg = check_pursuit_cfg($_REQUEST['eventid']);

        if ($cfg === false)   // file hasn't been written yet - use parameters from race format cfg
        {
            $_SESSION['pursuitcfg']['length']    = $_REQUEST['length'];
            $_SESSION['pursuitcfg']['interval']  = 60;
            $_SESSION['pursuitcfg']['pntype']    = $_REQUEST['pntype'];
            $_SESSION['pursuitcfg']['slowclass'] = p_class_match($_REQUEST['maxpy'], $_REQUEST['pntype']) ;
            $_SESSION['pursuitcfg']['fastclass'] = p_class_match($_REQUEST['minpy'], $_REQUEST['pntype']) ;
        }

        $args = array(
            //"pagestate" => "init",
            //"format"    => $_REQUEST['format'],
            //"caller"    => "race_pg",
            "pntype"    => $_SESSION['pursuitcfg']['pntype'],
            "fastclass" => $_SESSION['pursuitcfg']['fastclass'],
            "slowclass" => $_SESSION['pursuitcfg']['slowclass'],
            "length"    => $_SESSION['pursuitcfg']['length'],
            "interval"  => $_SESSION['pursuitcfg']['interval'],
            "boattypes" => "D",
            "eventid"   => $_REQUEST['eventid']
        );

        //echo "<pre>".print_r($args,true)."</pre>";
    }
    else
    {
        // direct util call - get args from script url
        empty($_REQUEST['pntype'])    ? $args['pntype'] = "national" : $args['pntype'] = $_REQUEST['pntype'];
        empty($_REQUEST['fastclass']) ? $args['fastclass'] = ""      : $args['fastclass'] = $_REQUEST['fastclass'];
        empty($_REQUEST['slowclass']) ? $args['pntype'] = ""         : $args['slowclass'] = $_REQUEST['slowclass'];
        empty($_REQUEST['length'])    ? $args['length'] = "90"       : $args['length'] = $_REQUEST['length'];
        empty($_REQUEST['interval'])  ? $args['interval'] = "60"     : $args['interval'] = $_REQUEST['interval'];
        empty($_REQUEST['boattypes']) ? $args['boattypes'] = "D"     : $args['boattypes'] = $_REQUEST['boattypes'];
        empty($_REQUEST['eventid'])   ? $args['eventid'] = "0"       : $args['eventid'] = $_REQUEST['eventid'];

    }

    // get list of classes
    $args['pntype'] == "national" ? $py_field = "nat_py" : $py_field = "local_py";
    $classes = $boat_o->getclasses(array(), $sort=array("$py_field"=>"DESC"));

    // use form to get parameters for start time report
    $formfields = array(
        "instructions" => "Enter the relevant detail in the form below and click Get Start Times to produce the start list</br>",
        "script" => "pursuit_starttimes.php?pagestate=submit",
    );

    if ($caller == "race_pg")
    {
        $formfields["instructions"].= "<br><b>Important:</b><br>
                                       <span class='text-info'>The information entered here will be used to calculate the start times for each boat on the Start Page</span>";
    }

    // present form to select json file for processing (general template)
    $params = array();
    $pagefields['body'] =  $tmpl_o->get_template("pursuit_start_form", $formfields, array("classes"=>$classes, "args"=>$args));
    echo $tmpl_o->get_template("basic_page", $pagefields );
}


elseif ($_REQUEST['pagestate'] == "submit")
{
    // get/check arguments
    $pntype     = u_checkarg('pntype', 'set', "", "national");      // selects nat_py or local_py field
    $length     = u_checkarg('length', 'set', "", 90);              // length of race in minutes
    $slowclassid = u_checkarg('slowclassid', 'set', "", 0);         // class id for slowest class
    $fastclassid = u_checkarg('fastclassid', 'set', "", 0);         // class id for fastest class
    $interval   = u_checkarg('interval', 'set', "", 60);            // interval between starts in seconds
    $dinghy     = u_checkarg('typeD', 'set', "", "1");              // dinghy classes included
    $keelboat   = u_checkarg('typeK', 'set', "", "0");              // keelboat classes included
    $foiler     = u_checkarg('typeF', 'set', "", "0");              // foiler classes included
    $multihull  = u_checkarg('typeM', 'set', "", "0");              // multihull classes included
    $eventid    = u_checkarg('eventid', 'set', "", "");

    // select py to use
    $pntype == "national" ? $py_field = "nat_py" : $py_field = "local_py";

    // get pn for fastest and slowest classes
    $slowdata = get_class_pn($slowclassid, $py_field);   // slowest
    $fastdata = get_class_pn($fastclassid, $py_field);   // fastest

    // create array of allowed boat types
    $btype_arr = get_boat_types_array($dinghy,$keelboat,$foiler,$multihull);

    // get all classes from t_class
    $classes = $boat_o->getclasses(array(), $sort=array("$py_field"=>"DESC"));

    // get start time report data
    $starts = p_getstarts_class($classes, $fastdata['pn'], $slowdata['pn'], $py_field, $length, $interval, $btype_arr);

    // send details to tmp file for use by racemanager
    $cfg = array("slowpn"=> $slowdata['pn'], "slowclass"=> $slowdata['class'],
                 "fastpn" => $fastdata['pn'], "fastclass"=> $fastdata['class'],
                 "length" => $length, "interval" => $interval, "pntype" => $pntype);
    $set_tmp_file = file_put_contents($_SESSION['basepath']."/tmp/pursuitcfg_$eventid.json", json_encode($cfg));

    $fields = array(
        "length"     => $length,
        "slowclass"   => $slowdata['class'],
        "fastclass"   => $fastdata['class'],
        "interval"   => $interval,
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



function get_class_pn($classid, $py_field)
{
    global $boat_o;
    $data = array();

    if (empty($classid) or empty($py_field) or ($py_field != "nat_py" and $py_field != "local_py")) {
        $data = false;
    } else {
        $row = $boat_o->boat_getdetail("", $classid);
        if ($row) {
            $py_field == "local_py" ? $data['pn'] = $row['local_py'] : $data['pn'] = $row['nat_py'];
            $data['class'] = $row['classname'];
        } else {
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
        if ($diff > 0)                                                                 // next start
        {
            if (!empty($bufr)) { $bufr.= "</td></tr>"; }                               // finish incomplete tow

            if ($diff > 1 ) { $bufr.= "<tr><td>&nbsp;</td><td>----</td></tr>"; }       // missing start - leave a gap

            $bufr.= "<tr><td >{$start['start']} mins &nbsp;&nbsp;&nbsp;&nbsp;</td>
                         <td ><span style=\"$style\">{$start['class']}</span>";        // start + first class
        }
        else
        {
            $bufr.= ", <span style=\"$style\">{$start['class']}</span>";               // add additional classes
        }
        $this_start = (int)$start['start'];
    }

    return $bufr;
}