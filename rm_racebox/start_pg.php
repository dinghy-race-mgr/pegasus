<?php
/**
 * start_pg.php - race administration page
 * 
 * This page is used to manage the start period.
 *   - managing the start timer (which can be synchronised with a automated lights or flag system
 *   - recording start line infringements
 *   - managing general recall restarts
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
//echo "<pre><br><br><br><br>".print_r($_SESSION["e_$eventid"],true)."</pre>";

// classes
include ("{$loc}/common/classes/db_class.php");
include ("{$loc}/common/classes/template_class.php");
include ("{$loc}/common/classes/event_class.php");
include ("{$loc}/common/classes/race_class.php");

// templates
$tmpl_o = new TEMPLATE(array("../common/templates/general_tm.php", "./templates/layouts_tm.php", "./templates/start_tm.php", "./templates/pursuit_tm.php"));

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

// set race timers
$start = array();
$numboats = array();
for ($j=1; $j<=$_SESSION["e_$eventid"]['rc_numstarts']; $j++)
{
    $start[$j] = $_SESSION["e_$eventid"]["st_$j"]['startdelay'];
    if ($event_state == "in progress" or $_SESSION["e_$eventid"]['ev_status'] == "abandoned")
    {
        $start[$j] = $timer_start + $_SESSION["e_$eventid"]["st_$j"]['startdelay'] - time();
    }

    $numboats[$j] = 0;
    for ($i=1; $i<=$_SESSION["e_$eventid"]['rc_numfleets']; $i++)
    {
        if ( $_SESSION["e_$eventid"]["fl_$i"]['startnum'] == $j )
        {
            $numboats[$j] = $numboats[$j] + $_SESSION["e_$eventid"]["fl_$i"]['entries'];
        }
    }
}
//debugTimer($eventid, $start_master, $start, $_SESSION["e_$eventid"]['ev_timerstart']);

// --- navbar -----------------------------------------------------------------------------
$fields = array("eventid" => $eventid, "brand" => "raceBox: {$_SESSION["e_$eventid"]['ev_label']}", "club" => $_SESSION['clubcode']);
$params = array("page" => $page, "pursuit" => $_SESSION["e_$eventid"]['pursuit'], "links" => $_SESSION['clublink'], "num_reminders" => $_SESSION["e_$eventid"]['num_reminders']);
$nbufr = $tmpl_o->get_template("racebox_navbar", $fields, $params);

// --- left hand panel -----------------------------------------------------------------------------
$lbufr = u_growlProcess($eventid, $page);                      // initialise bufr with process growls


// -------   PURSUIT RACE   --------------------------------------------------------
if ($_SESSION["e_$eventid"]['pursuit'])
{
    // check current config settings (and update as necessary)
    check_pursuit_cfg($eventid);

    require_once ("./include/rm_racebox_lib.php");
    $competitors = $race_o->race_getentries("", array("pn"=>"DESC"));   // get competitors in order (pn DESC, class ASC, sailnum ASC

    $warnings = array();
    if (empty($competitors)) {
        $warnings[] = "> no boats entered yet <br>... 
                       <span class='text-info'>try again when you have boats entered </span>";
    }
    if (empty($_SESSION['pursuitcfg']['maxpn'])) {
        $warnings[] = "> the class for the first start has not been set <br>... 
                       <span class='text-info'>use the <b>Pursuit Start Times</b> option on the Status Page to set this</span>";
    }
    if (empty($_SESSION['pursuitcfg']['length'])) {
        $warnings[] = "> the length of the race has not been set <br>... 
                       <span class='text-info'>use the <b>Pursuit Start Times</b> option on the Status Page to set this</span>";
    }

    $pursuit_starts = p_getstarts_competitors($competitors, $_SESSION['pursuitcfg']['slowpn'],             // allocate to starts
                                              $_SESSION['pursuitcfg']['length'], $_SESSION['pursuitcfg']['interval']);

    $lbufr.= pursuit_start_list($pursuit_starts, $warnings, $eventid);             // render display
}

// -------   CLASS-HANDICAP-AVERAGE LAP RACE   ---------------------------------------
else
{
    for ($startnum = 1; $startnum <= $_SESSION["e_$eventid"]['rc_numstarts']; $startnum++)
    {
        $start_detail = get_start_details($startnum, $fleet_data, $eventid);

        // infringements control
        $infringebufr = get_infringements_control($eventid, $startnum, $start_detail["fleetlist"], $start, $btn_infringestart, $mdl_infringestart);

        // general recall control
        $recallbufr = get_genrecall_control($eventid, $startnum, $start_detail["fleetlist"], $start, $timer_start, $btn_generalrecall, $mdl_generalrecall);

        $fields = array(
            "startnum" => strval($startnum),
            "flag" => $start_detail["warning_flag"],
            "fleet-list" => $start_detail["fleetlist"],
            "start-delta" => gmdate("H:i:s", $start[$startnum]),
            "start-secs" => strval($start[$startnum]),
            "start-boats" => $numboats[$startnum],
            "infringe" => $infringebufr,
            "recall" => $recallbufr,
        );

        $params = array(
            "pursuit" => $_SESSION["e_$eventid"]['pursuit'],
            "timer-start" => $timer_start,
            "start-delay" => $_SESSION["e_$eventid"]["st_$startnum"]['startdelay'],
        );
        $lbufr .= $tmpl_o->get_template("fleet_panel", $fields, $params);
    }
}

// --- right hand panel -----------------------------------------------------------------------------
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
    "l_top"      => "<div class='margin-top-40' >",
    "l_mid"      => $lbufr,
    "l_bot"      => "</div>",
    "r_top"      => "<div class='margin-top-10' style='margin-left: 30px;'>",
    "r_mid"      => $rbufr,
    "r_bot"      => "</div>".$timer_script,
    "footer"     => "",
    "body_attr"  => ""                                                          //onload=\"startTime()\""
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
    // FIXME this code is also included in race_pg.php
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
                        window.location.reload();
                }
            });
        });
        </script>
EOT;
    return $bufr;
}

function pursuit_start_list($starts, $warnings, $eventid)
{
    global $tmpl_o;

        // get number of boats on each start
        $num_boats = get_boats_per_start($starts);

        // get classes representing pn limits
        //$pnclass = p_class_match(array("maxpn"=>$_SESSION['pursuitcfg']['maxpn'], "minpn"=>$_SESSION['pursuitcfg']['minpn']), $_SESSION['pursuitcfg']['pntype']);

        // render start list
        $fields = array(
            "length"     => $_SESSION['pursuitcfg']['length'],
            "slowclass"  => p_class_match($_SESSION['pursuitcfg']['slowpn'], $_SESSION['pursuitcfg']['pntype']),
            "fastclass"  => p_class_match($_SESSION['pursuitcfg']['fastpn'], $_SESSION['pursuitcfg']['pntype']),
            "interval"   => $_SESSION['pursuitcfg']['interval'],
            "pntype"     => $_SESSION['pursuitcfg']['pntype'],
            "start-info" => render_start_by_competitor($starts, $num_boats, $eventid)
        );

    return $tmpl_o->get_template("start_by_competitor", $fields, array("warnings"=>$warnings));
}

function get_boats_per_start ($starts)
{
    $num_boats = array();

    foreach ($starts as $start)
    {
        if (!array_key_exists((int)$start['start'], $num_boats))
        {
            $num_boats[(int)$start['start']] = 0;
        }
        $num_boats[(int)$start['start']]++;
    }

    return $num_boats;
}

function render_start_by_competitor($starts, $num_boats, $eventid)
{
    $link_tmpl = "start_sc.php?eventid=$eventid&pagestate=setcode&startnum=1&fleet=1&finishlap=1000
                               &racestatus=%s&declaration=%s&lap=%s&entryid=%s&boat=%s&code";

    $menu_tmpl = '
    <div class="btn-group">
      %1$s - %2$s <span class="label label-danger" style="font-size: 1.1em;">%3$s</span> 
      <a href="#" class="dropdown-toggle" role="button" data-toggle="dropdown" >
                <span class="caret text-info" style="border-width:7px;"></span>
      </a>
      <ul class="dropdown-menu">
        <li><a href="%4$s=" >clear code</a></li>
        <li><a href="%4$s=OCS">OCS</a></li>
        <li><a href="%4$s=DNS">DNS</a></li>
        <li><a href="%4$s=DNC">DNC</a></li>
      </ul>
    </div>
';

    $bufr = "";
    $this_start = -1;
    $this_class = "";

    foreach ($starts as $i => $start)
    {
        $boat = "{$start['class']}-{$start['sailnum']}";
        $link = sprintf($link_tmpl, $start['class'], $start['declaration'], $start['lap'],$start['id'],$boat);
        $menu = sprintf($menu_tmpl, $start['class'], $start['sailnum'], $start['code'], $link);


        $s_diff = (int)$start['start'] - $this_start ;                           // check if start time has changed
        $start['class'] != $this_class ? $c_diff = true : $c_diff = false;       // check if class has changed

        if ($s_diff > 0)                                                         // new start add banner
        {
            $bufr .= "<tr><td colspan='2'>&nbsp;&nbsp;</td></tr><tr class='info' ><td style='width: 85%;'>Start +{$start['start']} mins <td>
                          <td style='width: 15%; pull-right'> {$num_boats[(int)$start['start']]} boat(s)</td></tr>";
        }

        if ($c_diff)                                                            //  new class
        {
            if (!empty($bufr)) { $bufr.= "</td></tr>"; }                        // finish incomplete row for previous class

            $bufr.= "<tr><td colspan='2'>$menu |";  // add first boat of new class
        }
        else
        {
            $bufr.= "$menu | ";            // add subsequent boat of this class
        }

        $this_start = (int)$start['start'];                                     // reset start time
        $this_class = $start['class'];                                          // reset class
    }

    return $bufr;
}

function get_start_details($startnum, $fleet_data, $eventid)
{
    $start_detail = array("fleetlist" => "", "warning_fleg" => "");

    $fleetlist = "";
    //$start_fleet = array();
    for ($fleetnum = 1; $fleetnum <= $_SESSION["e_$eventid"]['rc_numfleets']; $fleetnum++)
    {
        // get fleets in this start
        if ($fleet_data["$fleetnum"]['startnum'] == $startnum)
        {
            $start_detail["fleetlist"] .= "{$_SESSION["e_$eventid"]["fl_$fleetnum"]['name']}, ";
            //$start_fleet["$fleetnum"] = $_SESSION["e_$eventid"]["fl_$fleetnum"];
            $start_detail["warning_flag"] = $_SESSION["e_$eventid"]["fl_$fleetnum"]['warnsignal'];
        }
    }
    $start_detail["fleetlist"] = rtrim($start_detail["fleetlist"], ", ");

    return $start_detail;
}

function get_infringements_control($eventid, $startnum, $fleetlist, $start, $btn_infringestart, $mdl_infringestart)
{
    global $tmpl_o;

    $bufr = "";
    // infringe start button
    $btn_infringestart['fields']['id'] = "infringestart$startnum";
    $btn_infringestart['data'] = "data-start=\"$startnum\"";

    // change button colour with 30 seconds to go
    $start[$startnum] > constant('START_WARN_SECS') ? $btn_infringestart['fields']["style"] = "default" : $btn_infringestart['fields']["style"] = "info";
    $bufr = $tmpl_o->get_template("btn_modal", $btn_infringestart['fields'], $btn_infringestart);

    // infringe modal
    $mdl_infringestart['fields']['id'] = "infringestart$startnum";
    $mdl_infringestart['fields']['body'] = <<<EOT
        <iframe src="start_infringements_pg.php?eventid=$eventid&startnum=$startnum&pagestate=init" 
                frameborder="0" style="width: 100%; height: 600px;" id="entryframe">
        </iframe>
EOT;
    $mdl_infringestart['fields']['script'] = "$( '#infringestart{$startnum}ModalLabel' ).text( 'Infringements - Start ' + button.data('start') + '  [$fleetlist]')";

    $bufr .= $tmpl_o->get_template("modal", $mdl_infringestart['fields'], $mdl_infringestart);

    return $bufr;
}

function get_genrecall_control($eventid, $startnum, $fleetlist, $start, $timer_start, $btn_generalrecall, $mdl_generalrecall)
{
    global $tmpl_o;

    $bufr = "";

    if ($timer_start > 0) {
        $startdisplay = date("H:i:s", $timer_start + $_SESSION["e_$eventid"]["st_$startnum"]['startdelay']);
    } else {
        $startdisplay = date("H:i:s", strtotime($_SESSION["e_$eventid"]['ev_starttime'])
            + $_SESSION["e_$eventid"]["st_$startnum"]['startdelay']);
    }

    // general recall button
    $btn_generalrecall['fields']['id'] = "generalrecall$startnum";
    $btn_generalrecall['data'] = "data-start=\"$startnum\"  data-starttime=\"$startdisplay\" ";

    // change button colour with 30 seconds to go
    $start[$startnum] > constant('START_WARN_SECS') ? $btn_generalrecall['fields']["style"] = "default" : $btn_generalrecall['fields']["style"] = "info";
    $bufr .= $tmpl_o->get_template("btn_modal", $btn_generalrecall['fields'], $btn_generalrecall);

    // general recall modal
    $mdl_generalrecall['fields']['id'] = "generalrecall$startnum";
    $mdl_generalrecall['fields']['body'] = $tmpl_o->get_template("fm_start_genrecall", array(), array("startnum" => $startnum));
    $mdl_generalrecall['fields']['script'] =
        "$( '#generalrecall{$startnum}ModalLabel' ).text( 'General Recall - Start ' + button.data('start') + '  [$fleetlist]')
        $( '#start{$startnum}' ).val(button.data('start'))
        $( '#origstart{$startnum}' ).text(button.data('starttime'))";

    $bufr .= $tmpl_o->get_template("modal", $mdl_generalrecall['fields'], $mdl_generalrecall);

    return $bufr;
}