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
require_once ("{$loc}/common/lib/raceformat_lib.php");
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/event_class.php");

u_initpagestart("", $page, false);                                    // starts session and sets error reporting
include ("{$loc}/config/{$_SESSION['lang']}-racebox-lang.php");       // language file
if ($_SESSION['debug']==2) { u_sessionstate($scriptname, $page, 0); } // if debug send session to file

$db_o = new DB;
$tmpl_o  = new TEMPLATE(array("../templates/general_tm.php",          // required templates
                             "../templates/racebox/layouts_tm.php",
                             "../templates/racebox/navbar_tm.php",
                             "../templates/racebox/pickrace_tm.php"));

include ("./include/pickrace_ctl.inc");                                //  control definitions

$today = date("Y-m-d");
$today_display = date("jS F");

// get event information for today
$event_o  = new EVENT($db_o);
$event_constraint = array("event_date"=>$today);
$events = $event_o->event_getevents($event_constraint, $_SESSION['mode'] );

// ----- navbar -----------------------------------------------------------------------------
$nav_fields = array(
    "page"     => $page,
    "eventid"  => 0,
    "brand"    => "raceBox PICK RACE",
);
$nbufr = $tmpl_o->get_template("pickrace_navbar", $nav_fields);

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
        $racecfg   = $event_o->event_getracecfg($eventid, $event['event_format']);       // get race format
        $rs        = configurestate($eventid, $event['event_status'] );                  //configure race based on status
            
        $vbufr = "";
        if ($rs['link'])
        {                
            if (empty($racecfg['race_name'])) { $racecfg['race_name'] = $lang['app']['unknown_format']; }
            $fields = array(
                "popover"    => $lang['popover']['race_format'],
                "eventid"    => $eventid,
                "raceformat" => $racecfg['race_name'],
                "style"      => $rs['text'],
            );
            $vbufr.= $tmpl_o->get_template("race_format", $fields);
        }

        $fields = $rs;
        $fields['eventname'] = $event['event_name'];
        $fields['oodname']   = $event_o->event_getdutyperson($eventid, "ood_p");
        $fields['starttime'] = $event['event_start'];

        empty($event['tide_time']) ? $fields['tidetime'] = "" : $fields['tidetime'] = "[HW {$event['tide_time']} {$event['tide_height']}m]";

        $fields['viewbufr']  = $vbufr;
        $lbufr.= $tmpl_o->get_template("race_panel", $fields);

        // view format modal
        $viewbufr = createdutypanel($event_o->event_geteventduties($eventid, ""), $eventid, "");
        $viewbufr.= createfleetpanel ($event_o->event_getfleetcfg($event['event_format']), $eventid, "");
        $viewbufr.= createsignalpanel(getsignaldetail($event_o, $event), $eventid, "");

        $mdl_format['fields']['id']     = "format".$eventid;
        $mdl_format['fields']['body']   = $viewbufr;
        $mdl_format['fields']['title']  = "Race Format: <b>{$event['event_name']}</b>";
        $mdl_format['fields']['footer'] = createprintbutton($eventid, true);
        $lbufr.= $tmpl_o->get_template("modal", $mdl_format['fields'], $mdl_format);
    }
}
else     // no events today
{
    $fields = array(
        "support_team" => $tmpl_o->get_template("support_team",
            array("label" => $lang['sys']['supportteam'], "info" => $lang['sys']['supportteaminfo'])),
        "msg1"  => $lang['msg']['race_none'],
        "msg2"  => $lang['msg']['race_create']
    );
    $lbufr.= $tmpl_o->get_template("no_races", $fields);
}

// ----- right hand panel --------------------------------------------------------------------
$rbufr = $tmpl_o->get_template("btn_modal", $btn_addrace['fields'], $btn_addrace);
$mdl_addrace['fields']['body'] = $tmpl_o->get_template("fm_addrace", array());
$rbufr.= $tmpl_o->get_template("modal", $mdl_addrace['fields'], $mdl_addrace);


// ----- page footer -------------------------------------------------------------------------
$fields = array(
    "l_foot" => "<span class='glyphicon glyphicon-copyright-mark' aria-hidden='true'></span> ".$_SESSION['sys_copyright'],
    "m_foot" => demobutton($_SESSION['mode']),
    "r_foot" => "{$_SESSION['sys_release']}  {$_SESSION['sys_version']}",
    "style"  => ""
);
$fbufr = $tmpl_o->get_template("footer", $fields, array("fixed"=>true));


// ----- render page -------------------------------------------------------------------------                                                                  // render
$fields = array(
    "page"       => $page,
    "refresh"    => 0,
    "l_width"    => 10,
    "forms"      => true,
    "tables"     => false,
    "body_attr"  => "",
    "title"      => "racebox",
    "loc"        => $loc,
    "stylesheet" => "$loc/style/rm_racebox.css",
    "navbar"     => $nbufr,
    "l_top"      => "<br><h2>$today_display - ".strtolower(htmlentities($lang['msg']['race_today']))."</h2><br>",
    "l_mid"      => $lbufr,
    "l_bot"      => "",
    "r_top"      => $rbufr,
    "r_mid"      => "",
    "r_bot"      => "",
    "footer"     => $fbufr
);

$params = array(
    "page"      => $page,
    "refresh"   => 0,
    "l_width"   => 10,
    "forms"     => true,
    "tables"    => false,
    "body_attr" => "",
);

echo $tmpl_o->get_template("two_col_page", $fields, $params);


// ----- page specific functions ---------------------------------------------------------------

function demobutton($mode)
{
    global $lang;

    $demo = "btn-default";
    $live = "btn-danger";
    if ($mode == "demo")
    {
        $demo = "btn-danger";
        $live = "btn-default";
    }

    $bufr = <<<EOT
        <form id="demoswitch" class="form-horizontal" action="#" method="post">
            <div class="btn-group btn-toggle pull-left"> 
                <a class="btn btn-sm $demo" style="width: 100px; font-weight: bold" href="rm_racebox.php?mode=demo">
                    {$lang['sys']['demo']}
                </a>
                <a class="btn btn-sm $live" style="width: 100px; font-weight: bold" href="rm_racebox.php?mode=live">
                    {$lang['sys']['live']}
                </a>
            </div>
        </form>
EOT;
    return $bufr;
}


function configurestate($eventid, $status )
{
    global $lang;

    $state = r_decoderacestatus($status);       
    switch ($state)
    {
        case "scheduled":   
            {
                $rs['code']   = $state;
                $rs['label']  = strtoupper($lang['app']['race_scheduled']);
                $rs['style']  = "panel-info";
                $rs['text']   = "text-primary";
                $rs['link']   = true;
                $rs['blabel'] = $lang['btn']['race_run']." &nbsp;<span class=\"glyphicon glyphicon-forward\" aria-hidden=\"true\"></span>";
                $rs['bstyle'] = "btn-primary";
                $rs['blink']  = "raceinit_sc.php?eventid=$eventid&mode=init";
                $rs['bpopup'] = "data-toggle=\"popover\" data-content=\"{$lang['msg']['goto_race']}\" data-placement=\"bottom\"";
            }
            break;

        case "not started":  
            {
                $rs['code']   = $state;
                $rs['label']  = strtoupper($lang['app']['race_notstarted']);
                $rs['style']  = "panel-warning";
                $rs['text']   = "text-warning";
                $rs['link']   = true;
                $rs['blabel'] = $lang['btn']['race_back']." &nbsp;<span class=\"glyphicon glyphicon-forward\" aria-hidden=\"true\"></span>";
                $rs['bstyle'] = "btn-warning";
                $rs['blink']  = "raceinit_sc.php?eventid=$eventid&mode=rejoin";
                $rs['bpopup'] = "data-toggle=\"popover\" data-content=\"Click to return to this race\" data-placement=\"bottom\"";
            }
            break;

        case "in progress": 
            {
                $rs['code']   = $state;
                $rs['label']  = strtoupper($lang['app']['race_inprogress']);
                $rs['style']  = "panel-danger";
                $rs['text']   = "text-danger";
                $rs['link']   = true;
                $rs['blabel'] = $lang['btn']['race_back']." &nbsp;<span class=\"glyphicon glyphicon-forward\" aria-hidden=\"true\"></span>";
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
            $rs['label']  = $lang['app']['race_status_unknown'];
            $rs['style']  = "panel-warning";
            $rs['text']   = "text-warning";
            $rs['link']   = false;
            $rs['blabel'] = $lang['btn']['race_error'];
            $rs['bstyle'] = "btn-default disabled";
            $rs['blink']  = "";
            $rs['bpopup'] = "data-toggle=\"popover\" data-content=\"{$lang['msg']['race_not_available']}\" data-placement=\"bottom\"";
            }
    }
    
    return $rs;
}
?>