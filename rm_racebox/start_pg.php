<?php
/**
 * rbx_pg_race.php - race administration page
 * 
 * This page allows the user to change some details of the race they are running and run
 * some administration functions (e.g. cancel, reset, close, etc.).  It is the racebox
 * application landing page once the event has been chosen.
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

$eventid = $_REQUEST['eventid'];

u_initpagestart($eventid, $page, $_REQUEST['menu']);   // starts session and sets error reporting
if ($_SESSION['debug']!=0) { u_sessionstate($scriptname, $page, $eventid); }

// initialising language   
include ("{$loc}/config/{$_SESSION['lang']}-racebox-lang.php");

// check we have request id - if not stop with system error
if (empty($eventid) or !is_numeric($eventid)) 
{
    $passed_event = "not defined";
    u_exitnicely($scriptname, $passed_event, $lang['err']['sys002'], "event id is not defined");  
    exit();
}

include ("{$loc}/common/classes/db_class.php");
include ("{$loc}/common/classes/template_class.php");
include ("{$loc}/common/classes/event_class.php");
include ("{$loc}/common/classes/race_class.php");

// templates
$tmpl_o = new TEMPLATE(array("../templates/general_tm.php",
    "../templates/racebox/layouts_tm.php",
    "../templates/racebox/navbar_tm.php",
    "../templates/racebox/start_tm.php"));

// database connection
$db_o   = new DB;
$race_o = new RACE($db_o, $eventid);

// page controls
include ("./include/start_ctl.inc");

// get current event status
$event_state   = r_decoderacestatus($_SESSION["e_$eventid"]['ev_status']);

// get fleet data
$fleet_data = array();
for ($fleetnum=1; $fleetnum<=$_SESSION["e_$eventid"]['rc_numfleets']; $fleetnum++)
{
        $fleet_data["$fleetnum"] = $_SESSION["e_$eventid"]["fl_$fleetnum"];
}

// set master timer
$timer_start = $_SESSION["e_$eventid"]['timerstart'];
$first_prep_delay = r_getstartdelay(1, $_SESSION["e_$eventid"]['rc_startscheme'], $_SESSION["e_$eventid"]['rc_startint']);
$event_state == "in progress" ? $start_master = $timer_start + $first_prep_delay - time() : $start_master = $first_prep_delay;

// set race timers
$start = array();
for ($j=1; $j<=$_SESSION["e_$eventid"]['rc_numstarts']; $j++)
{
    $start[$j] = $_SESSION["e_$eventid"]["st_$j"]['startdelay'];
    if ($event_state == "in progress" )
    {
        $start[$j] = $timer_start + $_SESSION["e_$eventid"]["st_$j"]['startdelay'] - time();
    }
}
//debugTimer($eventid, $start_master, $start, $_SESSION["e_$eventid"]['ev_timerstart']);

// ----- navbar -----------------------------------------------------------------------------
$fields = array(
    "eventid"  => $eventid,
    "brand"    => "raceBox: {$_SESSION["e_$eventid"]['ev_sname']}",
    "page"     => $page,
    "pursuit"  => $_SESSION["e_$eventid"]['pursuit'],
);
$nbufr = $tmpl_o->get_template("racebox_navbar", $fields);


// ----- left hand panel -----------------------------------------------------------------------------
$lbufr = u_growlProcess($eventid, $page);                      // process growls

// build panels for each start
for ($startnum=1; $startnum<=$_SESSION["e_$eventid"]['rc_numstarts']; $startnum++)
{
    $fleetlist = "";
    $start_fleet = array();
    for ($fleetnum=1; $fleetnum<=$_SESSION["e_$eventid"]['rc_numfleets']; $fleetnum++)
    {
        # get fleets in this start
        if ($fleet_data["$fleetnum"]['startnum'] == $startnum)
        {
            $fleetlist.= "{$_SESSION["e_$eventid"]["fl_$fleetnum"]['name']}, ";
            $start_fleet["$fleetnum"] = $_SESSION["e_$eventid"]["fl_$fleetnum"];
        }
    }
    $fleetlist = rtrim($fleetlist, ", ");

    // infringe start button
    $btn_infringestart['id']   = "infringestart$startnum";
    $btn_infringestart['data'] = "data-start=\"$startnum\"";
    $start[$startnum] > constant('START_WARN_SECS') ? $btn_infringestart["style"] = "default": $btn_infringestart["style"] = "warning";
    $infringebufr = $tmpl_o->get_template("btn_modal", $btn_infringestart);

    $mdl_infringestart['id'] = "infringestart$startnum";
    $mdl_infringestart['body'] = <<<EOT
    <iframe src="start_infringements_pg.php?eventid=$eventid&startnum=$startnum&pagestate=init" frameborder="0"
            style="width: 100%; height: 400px;" id="entryframe">
    </iframe>
EOT;
    $mdl_infringestart['script'] = "$( '#infringestart{$startnum}ModalLabel' ).text( 'Infringements - Start ' + button.data('start') + '  [$fleetlist]')";
    $infringebufr.= $tmpl_o->get_template("modal", $mdl_infringestart);

    // general recall button
    $recallbufr = "";
    if (!$_SESSION["e_$eventid"]['pursuit'])
    {
        if ($timer_start > 0)
        {
            $startdisplay = date("H:i:s",$timer_start + $_SESSION["e_$eventid"]["st_$startnum"]['startdelay']);
        }
        else
        {
            $startdisplay = date("H:i:s",strtotime($_SESSION["e_$eventid"]['ev_starttime'])
                + $_SESSION["e_$eventid"]["st_$startnum"]['startdelay']);
        }

        $start[$startnum] > constant('START_WARN_SECS') ? $btn_generalrecall["style"] = "default" : $btn_generalrecall["style"] = "warning";

        $btn_generalrecall['data'] = "data-start=\"$startnum\"  data-starttime=\"$startdisplay\" ";
        $recallbufr.= $tmpl_o->get_template("btn_modal", $btn_generalrecall);

        // FIXME - do the fields need setting
        $mdl_generalrecall['body'] = $tmpl_o->get_template("fm_start_genrecall", $fields);
        $recallbufr.= $tmpl_o->get_template("modal", $mdl_generalrecall);
    }

    $params = array(
        "eventid"       => strval($eventid),
        "pursuit"       => $_SESSION["e_$eventid"]['pursuit'],
        "startnum"      => strval($startnum),
        "fleet-list"    => $fleetlist,
        "start-delta"   => gmdate("H:i:s", $start[$startnum]),
        "start-secs"    => strval($start[$startnum]),
        "infringe"      => $infringebufr,
        "recall"        => $recallbufr,
    );

    $data = array(
        "start-fleet"   => $start_fleet,
        "pursuit"       => $_SESSION["e_$eventid"]['pursuit'],
        "numfleets"     => $_SESSION["e_$eventid"]['rc_numfleets'],
        "startnum"      => $startnum,
        "timer-start"   => $timer_start,
        "start-delay"   => $_SESSION["e_$eventid"]["st_$startnum"]['startdelay'],
    );
    $lbufr.= $tmpl_o->get_template("fleet_panel", $params, $data);
}

// ----- right hand panel -----------------------------------------------------------------------------
$rbufr = "";

if ( $event_state == "not started")
{
    $timer_btn_bufr = $tmpl_o->get_template("btn_link", $btn_timerstart);
    $timer_script = "";
}
else
{
    $timer_btn_bufr = $tmpl_o->get_template("btn_modal", $btn_timerstop);
    $timer_btn_bufr.= $tmpl_o->get_template("modal", $mdl_timerstop);
    $timer_script   = gettimerscript();
}

// Timer
$fields = array(
    "event-state"  => $event_state,
    "timer-start"  => $timer_start,
    "start-master" => $start_master,
    "start-delta"  => gmdate("H:i:s", $start_master),
    "timer-btn"    => $timer_btn_bufr,
);
$rbufr.= $tmpl_o->get_template("timer", $fields);

$mdl_latetimer['body'] = $tmpl_o->get_template("fm_start_adjusttimer", array());
$rbufr.= $tmpl_o->get_template("modal", $mdl_latetimer);

// disconnect database
$db_o->db_disconnect();

// ----- render page -----------------------------------------------------------------------------
$fields = array(
    "title"      => "racebox",
    "loc"        => $loc,
    "stylesheet" => "$loc/style/rm_racebox.css",
    "navbar"     => $nbufr,
    "l_top"      =>"<div class='margin-top-20' style='margin-left:10%; margin-right:10%;'>",
    "l_mid"      => $lbufr,
    "l_bot"      => "</div>",
    "r_top"      => "<div class='margin-top-10' style='margin-left: 30px;'>",
    "r_mid"      => $rbufr,
    "r_bot"      => "</div>".$timer_script,
    "footer"     => "",
    "page"       => $page,
    "refresh"    => 0,
    "l_width"    => 9,
    "forms"      => true,
    "tables"     => false,
    "body_attr"  => "onload=\"startTime()\""
);
echo $tmpl_o->get_template("two_col_page", $fields);

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


// FIXME this move to a template
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
                if (event.elapsed) {
                    \$this.html(event.strftime('<span  style=\"color: lightblue\">%H:%M:%S</span>'));
                } else {
                    if(secstogo <= $warnsecs & clock!='c0') {
                        \$this.html(event.strftime('<span style=\"color: red\">%H:%M:%S</span>'));
                    } else {
                        \$this.html(event.strftime('<span style=\"color: orange\"><b>%H:%M:%S</b></span>'));
                    }
                }
                if (secstogo == $warnsecs) {
                        window.location.reload(true);
                }
            });
        });
        </script>
EOT;
    return $bufr;
}