<?php
/**
 * pickrace_pg.php
 * 
 * @abstract Page for OOD to pick race to run from the races scheduled for today
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 *
 * %%copyright%%
 * %%license%%
 * 
 */

$loc        = "..";                                                 // relative path from script to top level folder
$page       = "pickrace";
$scriptname = basename(__FILE__);
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/lib/rm_lib.php");
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/rota_class.php");
require_once ("{$loc}/common/lib/raceformat_lib.php");

// starts session and sets error reporting
u_initpagestart("", $page, false);
if ($_SESSION['debug']==2) { u_sessionstate($scriptname, $page, 0); } // if debug send session to file
u_sessionstate($scriptname, $page, 0);

// required templates
$tmpl_o  = new TEMPLATE(array("$loc/common/templates/general_tm.php", "./templates/layouts_tm.php", "./templates/pickrace_tm.php"));

//  control definitions
include ("./include/pickrace_ctl.inc");

$today = date("Y-m-d");
$today_display = date("jS F");

// get racing event information for today
$db_o = new DB;
$event_o  = new EVENT($db_o);
$rota_o = new ROTA($db_o);
$_SESSION['mode'] == "demo"? $status = "demo" : $status = "active";
$events = $event_o->get_events("racing", $status, array("start" => $today, "end" => $today), array() );

// echo "<pre>".print_r($_SESSION,true)."</pre>";

// ----- navbar -----------------------------------------------------------------------------
$nav_fields = array("page" => $page, "eventid" => 0, "brand" => "raceBox Programme", "club" => $_SESSION['clubcode']);
$nav_params = array("page"=> $page, "baseurl"=>$_SESSION['baseurl'], "links"=>$_SESSION['clublink']);
$nbufr = $tmpl_o->get_template("racebox_navbar", $nav_fields, $nav_params );


// ----- left hand panel --------------------------------------------------------------------
$lbufr = u_growlProcess(0, $page);
$lbufr.= u_growlProcess(0, "race");    // returning from closing race

// list events
if ($events)
{
    $_SESSION['events_today'] = count($events);
    foreach ($events as $event)
    {
        $eventid   = $event['id'];
        $racecfg   = $event_o->event_getracecfg($event['event_format'], $eventid);       // get race format
        $fields    = configurestate($eventid, $event['event_status'] );                  //configure race based on status

        // set event details
        $fields['eventid']   = $eventid;
        $fields['eventname'] = $event['event_name'];

        empty($racecfg['race_name']) ? $fields['raceformat'] = "unknown race format!" : $fields['raceformat'] = $racecfg['race_name'];

        $fields['oodname']   = $rota_o->get_duty_person($eventid, "ood_p");
        if (empty($fields['oodname'])) { $fields['oodname'] = $event['event_ood'] ; }

        $fields['starttime'] = $event['event_start'];
        empty($event['tide_time']) ? $fields['tidetime'] = "" : $fields['tidetime'] = " [ HW {$event['tide_time']} {$event['tide_height']}m ]";

        $lbufr.= $tmpl_o->get_template("race_panel", $fields, array("status" => $event['event_status']));

        // add view format modal
        $racecfg = $event_o->event_getracecfg($event['event_format'], $eventid);
        $fleetcfg = $event_o->event_getfleetcfg($event['event_format']);
        $viewbufr = createdutypanel($rota_o->get_event_duties($eventid), $eventid, "");
        $viewbufr.= createfleetpanel ($fleetcfg, $eventid, "");
        $viewbufr.= createsignalpanel(getsignaldetail($racecfg, $fleetcfg, $event), $eventid, "");

        $mdl_format['fields']['id']     = "format".$eventid;
        $mdl_format['fields']['body']   = $viewbufr;
        $mdl_format['fields']['title']  = "Race Format: <b>{$event['event_name']}</b>";
        $mdl_format['fields']['footer'] = createprintbutton($eventid, true);
        $lbufr.= $tmpl_o->get_template("modal", $mdl_format['fields'], $mdl_format);
    }
}
else     // no events today
{
    $fields['support_team'] = "";
    if(!empty($_SESSION['support_url'])) {
        $fields['support_team'] = $tmpl_o->get_template("support_team", array("link" => $_SESSION['support_url']));
    }
    $lbufr.= $tmpl_o->get_template("no_races", $fields);
}


// ----- right hand panel --------------------------------------------------------------------
$rbufr = $tmpl_o->get_template("btn_modal", $btn_addrace['fields'], $btn_addrace);
$mdl_addrace['fields']['body'] = $tmpl_o->get_template("fm_addrace", array());
$rbufr.= $tmpl_o->get_template("modal", $mdl_addrace['fields'], $mdl_addrace);


// ----- page footer -------------------------------------------------------------------------
$toggle_fields = array(
    "on"          => "left",
    "size"        => "md",
    "off-style"   => "default",
    "on-style"    => "warning",
    "left-label"  => "Live",
    "left-link"   => "rm_racebox.php?mode=live",
    "right-label" => "Demo",
    "right-link"  => "rm_racebox.php?mode=demo"
);
$_SESSION['mode'] == "live" ? $toggle_fields['on'] = "left" : $toggle_fields['on'] = "right";

//$copyright = <<<EOT
//    <span class="text-success">
//        <span class='glyphicon glyphicon-copyright-mark' aria-hidden='true'></span> {$_SESSION['sys_copyright']}
//    </span>
//EOT;


$fields = array(
    "l_foot" => $_SESSION['sys_name']." (".$_SESSION['sys_release'].") ".$_SESSION['sys_version'],
    "m_foot" => $tmpl_o->get_template("toggle_button", array(), $toggle_fields),
    "r_foot" => "<span class='glyphicon glyphicon-copyright-mark' aria-hidden='true'></span> {$_SESSION['sys_copyright']}",
    "style"  => ""
);
$fbufr = $tmpl_o->get_template("footer", $fields, array("fixed"=>true));


// ----- render page -------------------------------------------------------------------------                                                                  // render
$fields = array(
    "theme"      => $_SESSION['racebox_theme'],
    "title"      => "racebox",
    "loc"        => $loc,
    "stylesheet" => "./style/rm_racebox.css",
    "navbar"     => $nbufr,
    "l_top"      => "<br><h2>$today_display - races today</h2><br>",
    "l_mid"      => $lbufr,
    "l_bot"      => "",
    "r_top"      => $rbufr,
    "r_mid"      => "",
    "r_bot"      => "",
    "footer"     => $fbufr,
    "body_attr" => ""
);

$params = array(
    "page"      => $page,
    "refresh"   => 0,
    "l_width"   => 10,
    "forms"     => true,
    "tables"    => false,
);

echo $tmpl_o->get_template("two_col_page", $fields, $params);

echo "<pre>CURRENT SESSION: <br>".print_r($_SESSION,true)."</pre>";


// ----- page specific functions ---------------------------------------------------------------



function configurestate($eventid, $status )
{
    global $lang;

    $state = r_decoderacestatus($status);       
    switch ($state)
    {
        case "scheduled":   
            {
                $rs['code']   = $state;
                $rs['label']  = strtoupper("race scheduled");
                $rs['style']  = "panel-info";
                $rs['text']   = "text-primary";
                $rs['link']   = true;
                $rs['blabel'] = "Run Race &nbsp;<span class=\"glyphicon glyphicon-forward\" aria-hidden=\"true\"></span>";
                $rs['bstyle'] = "btn-success";
                $rs['blink']  = "raceinit_sc.php?eventid=$eventid&mode=init";
                $rs['bpopup'] = "data-toggle=\"popover\" data-content=\"{$lang['msg']['goto_race']}\" data-placement=\"bottom\"";
            }
            break;

        case "not started":  
            {
                $rs['code']   = $state;
                $rs['label']  = strtoupper("race not started");
                $rs['style']  = "panel-warning";
                $rs['text']   = "text-warning";
                $rs['link']   = true;
                $rs['blabel'] = "Back to Race &nbsp;<span class=\"glyphicon glyphicon-forward\" aria-hidden=\"true\"></span>";
                $rs['bstyle'] = "btn-warning";
                $rs['blink']  = "raceinit_sc.php?eventid=$eventid&mode=rejoin";
                $rs['bpopup'] = "data-toggle=\"popover\" data-content=\"Click to return to this race\" data-placement=\"bottom\"";
            }
            break;

        case "in progress": 
            {
                $rs['code']   = $state;
                $rs['label']  = strtoupper("race in progress");
                $rs['style']  = "panel-danger";
                $rs['text']   = "text-danger";
                $rs['link']   = true;
                $rs['blabel'] = "Back to Race &nbsp;<span class=\"glyphicon glyphicon-forward\" aria-hidden=\"true\"></span>";
                $rs['bstyle'] = "btn-danger";
                $rs['blink']  = "raceinit_sc.php?eventid=$eventid&mode=rejoin";
                $rs['bpopup'] = "data-toggle=\"popover\" data-content=\"Click to return to this race\" data-placement=\"bottom\"";
            }
            break;

        case "complete": 
            { 
                $rs['code']   = $state;
                $rs['label']  = strtoupper($status);
                $rs['style']  = "panel-success";
                $rs['text']   = "text-success";
                $rs['link']   = true;
                $rs['blabel'] = $lang['btn']['race_complete'];
                $rs['bstyle'] = "btn-default disabled";
                $rs['blink']  = "";
                $rs['bpopup'] = "data-toggle=\"popover\" data-content=\"{$lang['msg']['race_not_available']}\" data-placement=\"bottom\"";

                if ($status != "completed")  // i.e. abandoned or cancelled
                {
                    $rs['blabel'] = "Changed your mind? &nbsp;<span class=\"glyphicon glyphicon-forward\" aria-hidden=\"true\"></span>";
                    $rs['bstyle'] = "btn-warning";
                    $rs['blink']  = "race_pg.php?eventid=$eventid";
                    $rs['bpopup'] = "data-toggle=\"popover\" data-content=\"Changed your mind - click to run race\" data-placement=\"bottom\"";
                }
            }
            break;
        
        default:   // unknown   
            { 
            $rs['code']   = "error";
            $rs['label']  = "!! race state not known";
            $rs['style']  = "panel-warning";
            $rs['text']   = "text-warning";
            $rs['link']   = false;
            $rs['blabel'] = "Error !!";
            $rs['bstyle'] = "btn-default disabled";
            $rs['blink']  = "";
            $rs['bpopup'] = "data-toggle=\"popover\" data-content=\"race is complete and not available\" data-placement=\"bottom\"";
            }
    }
    
    return $rs;
}
