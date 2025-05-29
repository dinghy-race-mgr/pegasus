<?php
/*
 * update_yardsticks.php
 *
 * Loads yardstick numbers into the t_class table - mapping numbers to class using either the name of the class or the rya id
 *
 * csv format is:
 *      ryaid     : rya unique identifier (integer number)*
 *      classname : name of class (text)*
 *      crewnum   : no. of crew (integer number)
 *      rig       : type of rig
 *      spinnaker : type of spinnaker
 *      yardstick : yardstick number (integer)*
 *      change    : change from previous yardstick (signed integer)
 *      notes     : explanatory notes (text)
 *      last_pn   : last yardstick used for obsolete class
 *      last_year : last year yardstick was published for obsolete class
 *      num_years : number of years yardstick was published for obsolete class
 *
 *      * - indicates required data
 *
 * Arguments are:
 *      match     : ryaid or class - determines whether match is made on ryaid or class name (default ryaid)
 *      dryrun    : on or off - if on only identifies which records will be changed doesn't make change (default on)
 */

$loc  = "..";
$page = "update_yardsticks";     //
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
$styletheme = "flatly_";
$stylesheet = "./style/rm_utils.css";
require_once ("{$loc}/common/lib/util_lib.php");

session_id("sess-rmutil-".str_replace("_", "", strtolower($page)));
session_start();

$init_status = u_initialisation("$loc/config/rm_utils_cfg.php", $loc, $scriptname);

if ($init_status)
{
    // set timezone
    if (array_key_exists("timezone", $_SESSION)) { date_default_timezone_set($_SESSION['timezone']); }

    // start log
    error_log(date('d-M H:i:s')." -- rm_util UPDATE HANDICAPS FROM CSV --------------------[session: ".session_id()."]".PHP_EOL, 3, $_SESSION['syslog']);

    // set initialisation flag
    $_SESSION['util_app_init'] = true;
}
else
{
    u_exitnicely($scriptname, 0, "one or more problems with script initialisation",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/import_class.php");

// connect to database
$db_o = new DB();

// set templates
$tmpl_o = new TEMPLATE(array("$loc/common/templates/general_tm.php","./templates/layouts_tm.php", "./templates/update_pn_tm.php"));

// arguments
empty($_REQUEST['pagestate']) ? $pagestate = "init" : $pagestate = "submit";

$_SESSION['pagefields'] = array(
    "loc" => $loc,
    "theme" => $styletheme,
    "stylesheet" => $stylesheet,
    "title" => "Update PN",
    "header-left" => "raceManager",
    "header-right" => "Update PN Yardsticks",
    "body" => "",
    "footer-left" => "",
    "footer-center" => "",
    "footer-right" => "",
);

$fieldmap = array(
    "ryaid" => 'ryaid',
    "classname" => 'classname',
    "crewnum"   => 'crewnum',
    "rig" => 'rig',
    "spinnaker" => 'spinnaker',
    "yardstick" => 'yardstick',
    "change"    => 'change',
    "notes"     => 'notes',
    "last_pn"   => 'last_pn',
    "last_year" => 'last_year',
    "num_years" => 'num_years'
);

if ($pagestate == "init")
{
    // present form to select csv file for processing (general template)
    $_SESSION['pagefields']['body'] =  $tmpl_o->get_template("upload_pn_file", $_SESSION['pagefields']);

    // render page
    echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
}
elseif ($pagestate == "submit")
{
    $arg_match = "ryaid";   // in future there may be more match options (e.g. class + characteristics)
    $report = "";

    if (empty($_REQUEST['dryrun'])) { $_REQUEST['dryrun'] = "on"; }
    $_REQUEST['dryrun'] == "on" ? $arg_dryrun = true : $arg_dryrun = false;

    // check and read file
    $params = array("file_status" => false, "read_status" => false, "data_status" => false, "import_status" => false);
    $csv_o = new IMPORT_CSV($db_o, $fieldmap);
    $params['file_status'] = $csv_o->check_importfile($_FILES);
    if ($params['file_status'])                                          // no file errors
    {
        $params['read_status'] = $csv_o->read_importdata();
        //echo "<pre>read_status: {$params['read_status']}</pre>";
        if ($params['read_status'])                                      // no read errors
        {
            $import = $csv_o->get_importdata();
            $report = process_PN_data($import, $arg_dryrun);
            $params['success'] = true;
        }
        else
        {
            $read_error = $csv_o->get_data_info();
            $_SESSION['pagefields']['read-problems'] = format_error($read_error, 10);
            $params['success'] = false;
        }
    }
    else
    {
        $_SESSION['pagefields']['file-problems'] = $csv_o->get_file_val();
        $params['success'] = false;
    }

    // render page
    $_SESSION['pagefields']['rows-in-file'] = $csv_o->num_imports;
    $_SESSION['pagefields']['report'] = $report;
    $arg_dryrun ? $_SESSION['pagefields']['mode'] = "dryrun" : $_SESSION['pagefields']['mode'] = "database update" ;
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("update_report", $_SESSION['pagefields'], $params);
    echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
}
else
{
    // report pagestate error
    u_exitnicely($scriptname, 0, "page state not recognised",
        "contact your System Administrator", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

function process_PN_data($import, $dryrun)
{
    global $db_o;

    $i = 0;
    $report_txt = "";
    foreach ($import as $r)
    {
        $sql = "SELECT * FROM t_class WHERE rya_id = {$r['ryaid']}";    // check for matching record in t_class
        $rs = $db_o->db_get_rows($sql);

        if (!$rs)                                                        // no match in raceManager
        {
            $class_rpt = <<<EOT
                <tr>
                    <td style="text-transform: uppercase;">{$r['classname']}</td>
                    <td>class not in raceManager</td>
                    <td>&nbsp;</td>
                    <td><i>[ rya - {$r['ryaid']}]</i></td>
                </tr>
EOT;
        }
        else                                                             // matching record(s)
        {
            $num_classes = count($rs);
            $class_rpt = "";
            foreach ($rs as $db)
            {
                $update = false;
                $class = $db['classname'];
                $old = $db['nat_py'];
                $new = $r['yardstick'];
                $diff = $new - $old;

                if ($r['yardstick'] == $db['nat_py'])                    // yardstick not changed
                {
                    $action = "no change";
                    $detail = $new;
                }
                else                                                     // yardstick changed
                {
                    $dryrun ? $action = "<b>will be updated</b>" : $action = "<b>raceManager updated</b>";
                    $detail = "$old <span class=\"glyphicon glyphicon-arrow-right\" aria-hidden=\"true\"></span> <b>$new</b>  (change $diff )";
                    $i++;

                    if (!$dryrun)                                                    // update data record if not dryrun
                    {
                        //echo "<pre>".print_r($r,true)."</pre>";
                        
                        array_key_exists("last_year", $r) ?  $upd_year = $r['last_year'] : $upd_year = date("Y");
                        $upd = $db_o->db_update( "t_class", $variables = array("nat_py" => $r['yardstick'], "upd_year" => $upd_year),
                                                  array("id"=>$db['id']) );
                        // FIXME report errors and stop
                    }
                }

                $class_rpt.= <<<EOT
                <tr>
                    <td style="text-transform: uppercase;">$class</td>
                    <td>$action</td>
                    <td>$detail</td>
                    <td><i>[ rya - {$r['ryaid']}]</i></td>
                </tr>
EOT;
            }
        }
        $report_txt.= $class_rpt;
    }

    $report = <<<EOT
    <table width="80%">
        <thead>
            <th width="30%"></th><th width="25%"></th><th width="30%"></th><th width="15%"></th>   
        </thead>
        <tbody>
            $report_txt
        </tbody>
    </table>
    <br><p><b>Update completed - $i updates</b></p>
EOT;

    return $report;
}

function format_error($errors, $err_limit)
{
    $err_count = 0;
    $error_msg = "";
    foreach ($errors as $line => $error)
    {
        $err_count++;
        if ($err_count > $err_limit)
        {
            $error_msg .= " --- truncated error report ---<br>";
            break;
        }
        $error_msg .= "Row $line: " . rtrim($error, "; ") . "<br>";
    }
    return $error_msg;
}