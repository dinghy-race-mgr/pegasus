<?php
/**
 * race_pg.php
 *
 * @abstract race administration page
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
 * FIXME - exit nicely
 * FIXME - r_oktoreset needs to be implemented
 * FIXME - r_oktoclose
 *
 */

$loc        = "..";
$page       = "race";
$scriptname = basename(__FILE__);
require_once ("{$loc}/common/lib/util_lib.php");

// start session
u_startsession("sess-rmracebox", 10800);

// arguments
$eventid = u_checkarg("eventid", "checkintnotzero","");
if (!$eventid)
{
    u_exitnicely($scriptname, 0, "requested event has an invalid or missing identifier [ eventid = {$_REQUEST['eventid']}]",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => $_REQUEST));
}

// page initialisation
u_initpagestart($eventid, $page, true);

// classes/libraries
require_once ("{$loc}/common/lib/rm_lib.php");
require_once ("{$loc}/common/lib/raceformat_lib.php");
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/rota_class.php");

$eventname = u_conv_eventname($_SESSION["e_$eventid"]['ev_name']);

$tmpl_o = new TEMPLATE(array("../common/templates/general_tm.php", "./templates/layouts_tm.php", "./templates/race_tm.php"));

$db_o = new DB;                                                  // database connection
$event_o = new EVENT($db_o);
$rota_o = new ROTA($db_o);
$event = $event_o->get_event_byid($eventid);

require_once ("./include/race_ctl.inc");
require_once("./templates/growls.php");

$fleet_data = array();
$flag_data = array();
for ($fleetnum=1; $fleetnum<=$_SESSION["e_$eventid"]['rc_numfleets']; $fleetnum++)
{
    $fleet_data["$fleetnum"] = $_SESSION["e_$eventid"]["fl_$fleetnum"];
    $flag_data["$fleetnum"] = $_SESSION["e_$eventid"]["fl_$fleetnum"]['warnsignal'];
}

//  check laps status
$laps_set = checklapstatus($eventid);
if (in_array(false, $laps_set, true)) { u_growlSet($eventid, $page, $g_race_laps_not_set); }

// ----- navbar -----------------------------------------------------------------------------
$nav_fields = array("eventid" => $eventid, "brand" => "raceBox: {$_SESSION["e_$eventid"]['ev_label']}", "club" => $_SESSION['clubcode']);
$nav_params = array("page" => $page, "pursuit" => $_SESSION["e_$eventid"]['pursuit'], "links" => $_SESSION['clublink']);
$nbufr = $tmpl_o->get_template("racebox_navbar", $nav_fields, $nav_params);

// ----- left hand panel --------------------------------------------------------------------
$lbufr_top = u_growlProcess($eventid, $page);                      // check for confirmations to present
$lbufr_mid = "";
$lbufr_bot = "";

if ($_SESSION["e_$eventid"]['exit'])
{
    $lbufr_top.= $tmpl_o->get_template("race_exit", array());
    //unset($_SESSION["e_$eventid"]['exit']);                     // FIXME why is his unset
}
else
{
    //tide
    empty($_SESSION["e_$eventid"]['ev_tidetime']) ? $tidestr = "" :
        $tidestr = r_tideformat($_SESSION["e_$eventid"]['ev_tidetime'], $_SESSION["e_$eventid"]['ev_tideheight']);

    // ood
    $ood  = $rota_o->get_duty_person($eventid, "ood_p");
    if (empty($ood)) { $ood = $_SESSION["e_$eventid"]['ev_ood'] ; }

    // secondary series
    $secondary_series_txt = "";
    if (!empty($_SESSION["e_$eventid"]['ev_seriescodeextra']))
    {
        $series_arr = explode(",", $_SESSION["e_$eventid"]['ev_seriescodeextra']);
        $secondary_series_txt = "[ and";
        foreach ($series_arr as $v)
        {
             $series = $event_o->event_getseries($v);
             $secondary_series_txt.= " ".ucwords($series['seriesname']).",";

            //$secondary_series_txt.= " ".ucwords(substr($v, 0, strpos($v, "-"))).",";
        }
        $secondary_series_txt = rtrim($secondary_series_txt, ",")." ]";
        unset($series);
    }

    $fields = array(
        "start-time"   => $_SESSION["e_$eventid"]['ev_starttime'],
        "tide-detail"  => $tidestr,
        "ood-name"     => $ood,
        "event-name"   => $_SESSION["e_$eventid"]['ev_name'],
        "event-status" => $_SESSION["e_$eventid"]['ev_status'],
        "race-format"  => $_SESSION["e_$eventid"]['rc_name'],
        "race-starts"  => $_SESSION["e_$eventid"]['rc_numstarts'],
        "series-name"  => $_SESSION["e_$eventid"]['ev_seriesname'],
        "series-extra" => $secondary_series_txt,
        "event-notes"  => $_SESSION["e_$eventid"]['ev_notes'],
        "coursefinder" => $_SESSION['racebox_coursefinder']
    );
    $lbufr_top .= $tmpl_o->get_template("race_detail_display", $fields, $fields);

    $event_state   = r_decoderacestatus($_SESSION["e_$eventid"]['ev_status']);
    $event_state == "not started" ? $timer_script = "" : $timer_script   = gettimerscript();

    // get current fleet status
    $params = array(
        "eventid"        => $eventid,
        "race-status"    => $_SESSION["e_$eventid"]['ev_status'],
        "timer-start"    => $_SESSION["e_$eventid"]['timerstart'],       // time watch started
        "fleet-data"     => $event_o->get_fleetstatus($eventid),         // current fleet status
        "flag-data"      => $flag_data,
        "pursuit"        => $_SESSION["e_$eventid"]['pursuit'],
        "start-scheme"   => $_SESSION["e_$eventid"]['rc_startscheme'],
        "start-interval" => $_SESSION["e_$eventid"]['rc_startint']
    );
    $lbufr_mid = $tmpl_o->get_template("race_status_display", array(), $params);

    // full laps control (not required if a pursuit race)
    if (!$_SESSION["e_$eventid"]['pursuit'])
    {
        $mdl_setlaps['fields']['body'] = $tmpl_o->get_template("fm_race_setlaps", array(), array("fleet-data"=>$fleet_data));
        $lbufr_bot = $tmpl_o->get_template("modal", $mdl_setlaps['fields'], $mdl_setlaps);
    }

}

// ----- right hand panel --------------------------------------------------------------------
$rbufr_top = "";

// change race details - modal
$rbufr_top.= $tmpl_o->get_template("btn_modal", $btn_change['fields'], $btn_change);
$fields = array(
    "event-ood"      => $_SESSION["e_$eventid"]['ev_ood'],
    "start-time"     => $_SESSION["e_$eventid"]['ev_starttime'],
    "entry-option"   => $_SESSION["e_$eventid"]['ev_entry'],
    "start-option"   => $_SESSION["e_$eventid"]['rc_startscheme'],
    "start-interval" => $_SESSION["e_$eventid"]['rc_startint'],
    "event-notes"    => $_SESSION["e_$eventid"]['ev_notes'],
    "pursuit"        => $_SESSION["e_$eventid"]['pursuit'],
);
$mdl_change['fields']['body'] = $tmpl_o->get_template("fm_changerace", $fields, $fields);
$rbufr_top.= $tmpl_o->get_template("modal", $mdl_change['fields'], $mdl_change);

// pursuit start times - link
if ($_SESSION["e_$eventid"]['pursuit'])
{
    $args = array(
        "pagestate"=>"init",
        "caller" => "race_pg",
        "format" => $_SESSION["e_$eventid"]['rc_name'],
        "pntype" => $_SESSION["e_$eventid"]['fl_1']['pytype'],
        "length" => $_SESSION["e_$eventid"]['fl_1']['timelimitabs'],
        "maxpy"  => $_SESSION["e_$eventid"]['fl_1']['maxpy'],
        "minpy"  => $_SESSION["e_$eventid"]['fl_1']['minpy'],
        "eventid" => $eventid
);
    $btn_pursuit['fields']['link'].= http_build_query($args);
    //echo "<br><br><br><pre>{$btn_pursuit['fields']['link']}</pre>";
    $rbufr_top.= $tmpl_o->get_template("btn_link", $btn_pursuit['fields'], $btn_pursuit);
}
else
{
    // race format  - modal
    $rbufr_top.= $tmpl_o->get_template("btn_modal", $btn_format['fields'], $btn_format);

    $racecfg  = $event_o->event_getracecfg($event['event_format'], $eventid);
    $fleetcfg = $event_o->event_getfleetcfg($event['event_format']);
    $duties   = $rota_o->get_event_duties($eventid);
    $viewbufr = "";
    if ($fleetcfg)
    {
        $viewbufr.= createdutypanel($duties, $eventid, "in");
        $viewbufr.= createfleetpanel ($event_o->event_getfleetcfg($event['event_format']), $eventid, "");
        $viewbufr.= createsignalpanel(getsignaldetail($racecfg, $fleetcfg, $event), $eventid, "");
    }

    $mdl_format['fields']['body'] = $viewbufr;
    $mdl_format['fields']['title'] = "Race Format: <b>$eventname</b>";
    $mdl_format['fields']['footer'] = createprintbutton($eventid, true);
    $rbufr_top.= $tmpl_o->get_template("modal", $mdl_format['fields'], $mdl_format);
}

// send message - modal
$rbufr_top.= $tmpl_o->get_template("btn_modal", $btn_message['fields'], $btn_message);
$mdl_message['fields']['body'] = $tmpl_o->get_template("fm_race_message", array());
$rbufr_top.= $tmpl_o->get_template("modal", $mdl_message['fields'], $mdl_message);


$rbufr_mid ="<hr>";

// cancel  - modal
if ($_SESSION["e_$eventid"]['ev_status']!="cancelled")
{
    $cancel_ok = r_oktocancel($eventid, "cancel");      // only if race has not started
    if ($cancel_ok['result'])
    {
        $mdl_cancel['fields']['body'] = $tmpl_o->get_template("fm_cancel_ok", $cancel_ok);
    }
    else
    {
        $mdl_cancel['fields']['body'] = $tmpl_o->get_template("fm_cancel_notok", $cancel_ok);
        $mdl_cancel['fields']['close-lbl'] = "back";
        $mdl_cancel['submit'] = false;
    }
    $rbufr_mid.= $tmpl_o->get_template("btn_modal", $btn_cancel['fields'], $btn_cancel);
    $rbufr_mid.= $tmpl_o->get_template("modal", $mdl_cancel['fields'], $mdl_cancel);
}
else
{
    $cancel_ok = r_oktocancel($eventid, "uncancel");   // only if cancelled
    if ($cancel_ok['result'])
    {
        $mdl_uncancel['fields']['body'] = $tmpl_o->get_template("fm_uncancel_ok", $cancel_ok);
    }
    else
    {
        $mdl_uncancel['fields']['body'] = $tmpl_o->get_template("fm_uncancel_notok", $cancel_ok);
        $mdl_cancel['fields']['close-lbl'] = "back";
        $mdl_uncancel['submit'] = false;
    }
    $rbufr_mid.= $tmpl_o->get_template("btn_modal", $btn_uncancel['fields'], $btn_uncancel);
    $rbufr_mid.= $tmpl_o->get_template("modal", $mdl_uncancel['fields'], $mdl_uncancel);
}

// abandon - modal
// FIXME ultimately needs to handle abandoning fleets individually
if ($_SESSION["e_$eventid"]['ev_status']!="abandoned")
{
    $abandon_ok = r_oktoabandon($eventid, "abandon");   // race must have started and have entries
    if ($abandon_ok['result'])
    {
        $abandon_ok['eventid'] = $eventid;
        $mdl_abandon['fields']['body'] = $tmpl_o->get_template("fm_abandon_ok", $abandon_ok);
    }
    else
    {
        $mdl_abandon['fields']['body'] = $tmpl_o->get_template("fm_abandon_notok", $abandon_ok);
        $mdl_abandon['submit'] = false;
    }
    $rbufr_mid.= $tmpl_o->get_template("btn_modal", $btn_abandon['fields'], $btn_abandon);
    $rbufr_mid.= $tmpl_o->get_template("modal", $mdl_abandon['fields'], $mdl_abandon);

}
else
{
    $abandon_ok = r_oktoabandon($eventid, "unabandon");  // race must be abandoned
    if ($abandon_ok['result'])
    {
        $mdl_unabandon['fields']['body'] = $tmpl_o->get_template("fm_unabandon_ok", $abandon_ok);
    }
    else
    {
        $mdl_unabandon['fields']['body'] = $tmpl_o->get_template("fm_unabandon_notok", $abandon_ok);
        $mdl_unabandon['submit'] = false;
    }
    $rbufr_mid.= $tmpl_o->get_template("btn_modal", $btn_unabandon['fields'], $btn_unabandon);
    $rbufr_mid.= $tmpl_o->get_template("modal", $mdl_unabandon['fields'], $mdl_unabandon);

}

$rbufr_bot ="";

// close  - modal
$close_ok = r_oktoclose($eventid);    // results must be published and all boats finished
if ($close_ok['result'])
{
    $mdl_close['fields']['body'] = $tmpl_o->get_template("fm_close_ok", $close_ok);
}
else
{
    $mdl_close['fields']['body'] = $tmpl_o->get_template("fm_close_notok", $close_ok);
    $mdl_close['submit'] = false;
}
$rbufr_bot.= $tmpl_o->get_template("btn_modal", $btn_close['fields'], $btn_close);
$rbufr_bot.= $tmpl_o->get_template("modal", $mdl_close['fields'], $mdl_close);

$rbufr_bot.="<hr style='border-top: solid 1px steelblue !important'>";

// reset  - modal
$reset_ok = r_oktoreset($eventid);   // currntly no restrictions on resetting race
if ($reset_ok['result'])
{
    $mdl_reset['fields']['body'] = $tmpl_o->get_template("fm_reset_ok", array());
}
else
{
    $mdl_reset['fields']['body'] = $tmpl_o->get_template("fm_reset_notok", $reset_ok);
    $mdl_reset['submit'] = false;
}
$rbufr_bot.= $tmpl_o->get_template("btn_modal", $btn_reset['fields'], $btn_reset);
$rbufr_bot.= $tmpl_o->get_template("modal", $mdl_reset['fields'], $mdl_reset);

// disconnect database
$db_o->db_disconnect();

if ($_SESSION["e_$eventid"]['exit'])
{
    $nbufr = "";
    $rbufr = "";
}

// ----- render page -------------------------------------------------------------------------
$fields = array(
    "title"      => $_SESSION["e_$eventid"]['ev_label'],
    "theme"      => $_SESSION['racebox_theme'],
    "loc"        => $loc,
    "stylesheet" => "./style/rm_racebox.css",
    "navbar"     => $nbufr,
    "l_top"      => $lbufr_top,
    "l_mid"      => $lbufr_mid,
    "l_bot"      => $lbufr_bot,
    "r_top"      => $rbufr_top,
    "r_mid"      => $rbufr_mid,
    "r_bot"      => $rbufr_bot.$timer_script,
    "footer"     => "",
    "body_attr"  => ""                                                      // onload=\"startTime()\""
);

$params = array(
    "page"      => $page,
    "refresh"   => 0,
    "l_width"   => 10,
    "forms"     => true,
    "tables"    => true,
);
echo $tmpl_o->get_template("two_col_page", $fields, $params);


// ---- local functions ----------------------------------------------------
function checklapstatus ($eventid)
{
    $laps_set = array();
    $laps_set[0] = true;
    for ($i=1; $i<=$_SESSION["e_$eventid"]['rc_numfleets']; $i++)
    {
        $laps_set[$i] = true;
        if ($_SESSION["e_$eventid"]["fl_$i"]['entries'] > 0 AND $_SESSION["e_$eventid"]["fl_$i"]['maxlap'] <= 0 )
        {
            $laps_set[0] = false;
            $laps_set[$i]= false;
        }
    }
    return $laps_set;
}

function gettimerscript()
    // FIXME this code is also included in start_pg.php
{
    $warnsecs = 0;
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