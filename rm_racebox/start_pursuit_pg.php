<?php
/**
 * start_pursuit_pg.php - race administration page
 * 
 * This page is used to manage the start period for a pursuit race
 *   - managing the start timer (which can be synchronised with a automated lights or flag system
 *   - recording start line infringements
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 * 
 * @param string $eventid
 * 
 */

$loc        = "..";       // <--- relative path from script to top level folder
$page       = "start";     //
$scriptname = basename(__FILE__);
define('START_WARN_SECS', 30);                           // <-- number of seconds before start when timer colour will change
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/lib/rm_lib.php");
require_once ("{$loc}/common/lib/pursuit_lib.php");

$eventid = u_checkarg("eventid", "checkintnotzero","");
if (!$eventid) {
    u_exitnicely($scriptname, 0, "$page page - the requested event has an invalid record identifier [{$_REQUEST['eventid']}]",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));}

// start session
session_id('sess-rmracebox');   // creates separate session for this application
session_start();

// page initialisation
u_initpagestart($eventid, $page, true);   // starts session and sets error reporting

// classes
include ("{$loc}/common/classes/db_class.php");
include ("{$loc}/common/classes/template_class.php");
include ("{$loc}/common/classes/event_class.php");
include ("{$loc}/common/classes/race_class.php");

// templates
$tmpl_o = new TEMPLATE(array("../common/templates/general_tm.php", "./templates/layouts_tm.php", "./templates/pursuit_tm.php"));

// database connection
$db_o   = new DB;
$race_o = new RACE($db_o, $eventid);

// page controls
include ("./include/start_ctl.inc");
include("./templates/growls.php");

// get current event status
$event_state   = r_decoderacestatus($_SESSION["e_$eventid"]['ev_status']);

// get fleet data
$fleet_data = array();   // FIXME we shouldn't be using text integers as keys
for ($fleetnum = 1; $fleetnum <= $_SESSION["e_$eventid"]['rc_numfleets']; $fleetnum++) {
    $fleet_data["$fleetnum"] = $_SESSION["e_$eventid"]["fl_$fleetnum"];
}

// set master timer
$timer_start = $_SESSION["e_$eventid"]['timerstart'];
$first_prep_delay = r_getstartdelay(1, $_SESSION["e_$eventid"]['rc_startscheme'], $_SESSION["e_$eventid"]['rc_startint']);

if ($event_state == "in progress" or $_SESSION["e_$eventid"]['ev_status'] == "abandoned")
{
    $start_master = $timer_start + $first_prep_delay - time();
}
else
{
    $start_master = $first_prep_delay;
}

// get start information for all competitors
/*
 * raceinit - needs to add fleet format info on min_py, max_py, start_interval time_limit and pyfield into session['pursuitcfg'] array
 * getstarttimes on status page should update session['pursuitcfg'] if necessary and warn OOD
 */





// ----- navbar -----------------------------------------------------------------------------
$fields = array("eventid" => $eventid, "brand" => "raceBox: {$_SESSION["e_$eventid"]['ev_label']}", "club" => $_SESSION['clubcode']);
$params = array("page" => $page, "pursuit" => $_SESSION["e_$eventid"]['pursuit'], "links" => $_SESSION['clublink'], "num_reminders" => $_SESSION["e_$eventid"]['num_reminders']);
$nbufr = $tmpl_o->get_template("racebox_navbar", $fields, $params);


// ----- left hand panel -----------------------------------------------------------------------------
$lbufr = u_growlProcess($eventid, $page);                      // process growls

// get competitors in order (pn DESC, class ASC, sailnum ASC
$competitors = $race_o->race_getentries("", array("pn"=>"DESC"));
$starts = p_getstarts_competitors($competitors, $_SESSION['pursuitcfg']['maxpn'], $_SESSION['pursuitcfg']['length'], $_SESSION['pursuitcfg']['interval']);

$fields = array(
    "length"   => $_SESSION['pursuitcfg']['length'],
    "maxpn"    => $_SESSION['pursuitcfg']['maxpn'],
    "minpn"    => $_SESSION['pursuitcfg']['minpn'],
    "startint" => $_SESSION['pursuitcfg']['interval'],
    "pntype"   => $_SESSION['pursuitcfg']['pntype'],
    "start-info" => render_start_by_competitor($starts)
);
$lbufr.= $tmpl_o->get_template("start_by_competitor", $fields );



// ----- right hand panel -----------------------------------------------------------------------------
$rbufr = "";

if ( $event_state == "not started")
{
    $timer_btn_bufr = $tmpl_o->get_template("btn_link", $btn_timerstart['fields'], $btn_timerstart);
    $timer_script = "";
}
else
{
    $mdl_timerstop['fields']['body']= $tmpl_o->get_template("fm_stoptimer_ok", $mdl_timerstop['fields'], $mdl_timerstop);
    $timer_btn_bufr = $tmpl_o->get_template("btn_modal", $btn_timerstop['fields'], $btn_timerstop);
    $timer_btn_bufr.= $tmpl_o->get_template("modal", $mdl_timerstop['fields'], $mdl_timerstop);
    $timer_script   = gettimerscript();
}

// Timer
$fields = array(
    "start-master" => $start_master,
    "start-delta"  => gmdate("H:i:s", $start_master),
    "timer-btn"    => $timer_btn_bufr,
);
$rbufr.= $tmpl_o->get_template("timer", $fields, array("event-state" => $event_state, "timer-start"  => $timer_start));

$mdl_latetimer['fields']['body'] = $tmpl_o->get_template("fm_start_adjusttimer", array());
$rbufr.= $tmpl_o->get_template("modal", $mdl_latetimer['fields'], $mdl_latetimer);

// disconnect database
$db_o->db_disconnect();

// ----- render page -----------------------------------------------------------------------------
$fields = array(
    "title"      => $_SESSION["e_$eventid"]['ev_label'],
    "theme"      => $_SESSION['racebox_theme'],
    "loc"        => $loc,
    "stylesheet" => "./style/rm_racebox.css",
    "navbar"     => $nbufr,
    "l_top"      =>"<div class='margin-top-20' style='margin-left:10%; margin-right:10%;'>",
    "l_mid"      => $lbufr,
    "l_bot"      => "</div>",
    "r_top"      => "<div class='margin-top-10' style='margin-left: 30px;'>",
    "r_mid"      => $rbufr,
    "r_bot"      => "</div>".$timer_script,
    "footer"     => "",
    "body_attr"  => "onload=\"startTime()\""
);

$params = array(
    "page"      => $page,
    "refresh"   => 0,
    "l_width"   => 9,
    "forms"     => true,
    "tables"    => false,
);

echo $tmpl_o->get_template("two_col_page", $fields, $params);

// ----- functions -----------------------------------------------------------------------------

function debugTimer($eventid, $start_master, $start, $timerstart)
{
    $timemsg = "TIMER DETAILS: \n";
    $timemsg = "num starts: {$_SESSION["e_$eventid"]['rc_numstarts']} \n";
    $timemsg.= "current time: ".date("H:i:s")."\n";
    $timemsg.= "timerstart: ".gmdate("H:i:s", $timerstart)."\n";
    $timemsg.= "start_master: ".date("H:i:s",$start_master)."\n";
    for ($j=1; $j<=$_SESSION["e_$eventid"]['rc_numstarts']; $j++)
    {
        $display = date("H:i:s", $start[$j]);
        $timemsg.= "$j:  start: {$start[$j]}  time: $display delay: {$_SESSION["e_$eventid"]["st_$j"]['startdelay']}\n";
    }
    //u_writedbg($timemsg, __FILE__,__FUNCTION__,__LINE__);
}


function gettimerscript()
{
    $warnsecs = constant("START_WARN_SECS");
    $bufr = <<<EOT
        <script type="text/javascript">
        $('[data-countdown]').each(function() {   
            var \$this = $(this);
            var totime = new Date().getTime() + ($(this).data('countdown') * 1000);
            \$this.countdown(totime, {elapse: true})
            .on ('update.countdown', function(event) {
                var secstogo = (event.offset.minutes * 60) + event.offset.seconds;
                var clock = $(this).data('clock');
                var elapsed = event.elapsed;
                if (event.elapsed) {
                    \$this.html(event.strftime('<span  style=\"color: lightblue\">%H:%M:%S</span>'));
                } else {
                    if(secstogo <= $warnsecs & clock!='c0') {
                        \$this.html(event.strftime('<span style=\"color: red\">%H:%M:%S</span>'));
                    } else {
                        \$this.html(event.strftime('<span style=\"color: orange\"><b>%H:%M:%S</b></span>'));
                    }
                }
                if (secstogo == $warnsecs & clock!='c0' & !elapsed) {
                        window.location.reload(true);
                }
            });
        });
        </script>
EOT;
    return $bufr;
}


function render_start_by_competitor($starts)
{
    $bufr = "";
    $this_start = -1;
    $this_class = "";
    foreach ($starts as $i => $start)
    {

        $s_diff = (int)$start['start'] - $this_start ;                           // check if start time has changed
        $start['class'] != $this_class ? $c_diff = true : $c_diff = false;       // check if class has changed

        if ($s_diff > 0)                                                         // new start add banner
        {
            $bufr .= "<tr class='info' colspan='2'><td>Start {$start['start']} mins  [xx boats]</td></tr>";
        }

        if ($c_diff)                                                            //  new class
        {
            if (!empty($bufr)) { $bufr.= "</td></tr>"; }                        // finish incomplete row for previous class

            $bufr.= "<tr ><td>&nbsp;</td><td>{$start['class']}-{$start['sailnum']}, &nbsp;";  // add first boat of new class
        }
        else
        {
            $bufr.= "{$start['class']}-{$start['sailnum']}, &nbsp;";            // add subsequent boat of this class
        }

        $this_start = (int)$start['start'];                                     // reset start time
        $this_class = $start['class'];                                          // reset class

//        if ($s_diff > 0)                                                                            // next start
//        {
//            if (!empty($bufr)) { $bufr.= "</td></tr>"; }                                          // finish incomplete tow
//
//            if ($s_diff > 1 ) { $bufr.= "<tr><td>&nbsp;</td><td>----</td></tr>"; }                  // missing start - leave a gap
//
//            $bufr.= "<tr><td >{$start['start']} mins&nbsp;&nbsp;&nbsp;&nbsp;</td>
//                         <td ><span style=''>{$start['class']}</span>&nbsp;&nbsp;";                   // start + first class
//        }
//        else
//        {
//            $bufr.= ", <span style=''>{$start['class']}</span>&nbsp;&nbsp;";                // add additional classes
//        }
//        $this_start = (int)$start['start'];
    }

    return $bufr;
}