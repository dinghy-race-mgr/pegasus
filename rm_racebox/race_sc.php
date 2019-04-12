<?php
/**
 * race_sc.php
 * 
 * @abstract Processes server requests from the race page
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 *
 * FIXME - send emails at end of race
 * FIXME - exitnicely
 * 
 */
$loc        = "..";                                                 // relative path from script to top level folder
$page       = "race";     // 
$scriptname = basename(__FILE__);
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/lib/rm_lib.php");

$eventid   = (!empty($_REQUEST['eventid']))? $_REQUEST['eventid']: "";
$pagestate = (!empty($_REQUEST['pagestate']))? $_REQUEST['pagestate']: "";

u_initpagestart($eventid, $page, "");                               // starts session and sets error reporting
include ("{$loc}/config/{$_SESSION['lang']}-racebox-lang.php");     // language file

if ($eventid AND $pagestate)
{
    require_once ("{$loc}/common/classes/db_class.php");
    require_once ("{$loc}/common/classes/event_class.php");
    require_once ("{$loc}/common/classes/entry_class.php");
    require_once ("{$loc}/common/classes/race_class.php");

    include("../templates/racebox/growls.php");                           // confirmation message definitions

    $db_o = new DB;
    $event_o = new EVENT($db_o);
    $race_o = new RACE($db_o, $eventid);

    // eventname
    $event = $event_o->event_getevent($eventid);

    if ($event)
    {
        // ------- CHANGE --------------------------------------------------------------------------
        if ($pagestate == "change")
        {
            // setup update fields - check fields are not null and have changed
            $fields = array();
            if (!empty($_REQUEST['event_start'])
                and $_REQUEST['event_start'] != $_SESSION["e_$eventid"]['ev_starttime'])
                { $fields['event_start'] = $_REQUEST['event_start']; }
            if (!empty($_REQUEST['event_entry'])
                and $_REQUEST['event_entry']   != $_SESSION["e_$eventid"]['ev_entry'])
                { $fields['event_entry']    = $_REQUEST['event_entry']; }
            if (!empty($_REQUEST['start_scheme'])
                and $_REQUEST['start_scheme']  != $_SESSION["e_$eventid"]['rc_startscheme'])
                { $fields['start_scheme']   = $_REQUEST['start_scheme']; }
            if (!empty($_REQUEST['start_interval'])
                and $_REQUEST['start_interval']!= $_SESSION["e_$eventid"]['rc_startint'])
                { $fields['start_interval'] = $_REQUEST['start_interval']; }
            if (!empty($_REQUEST['event_notes']) and $_REQUEST['event_notes'] != $_SESSION["e_$eventid"]['ev_notes'])
                { $fields['event_notes']    = $_REQUEST['event_notes']; }
            elseif (empty($_REQUEST['event_notes']))
                { $fields['event_notes'] = ""; }
            
            if (!empty($fields))
            {        
                $update = $event_o->event_changedetail($eventid, $fields);                
                if ($update)  // update succeeded
                {
                    u_writelog("event details changed: ".print_r($fields, true), $eventid);
                    u_growlSet($eventid, $page, $g_event_change_success);
                }
                else          // update failed
                {
                    u_writelog("event details update failed: ".print_r($fields, true), $eventid);
                    u_growlSet($eventid, $page, $g_event_change_fail);
                }               
            }
            else              // no changes
            {
                u_growlSet($eventid, $page, $g_event_change_none);
            }
        }

        // ------- MESSAGE --------------------------------------------------------------------------
        elseif ($pagestate == "message")
        {
            // set fields to enter
            $fields = array();
            $fields["name"]    = $_REQUEST['msgname'];
            $fields["subject"] = $_SESSION["e_$eventid"]['ev_fname']." - OOD message";
            $fields["message"] = $_REQUEST['message'];
            !empty($_REQUEST['email']) ? $fields["email"] = $_REQUEST['email'] : $fields["email"] = "";
            $fields["status"]  = "received";
            
            // add message to message table
            $add = $event_o->event_addmessage($eventid, $fields);
            if ($add) // report success
            {
                u_writelog("message sent", $eventid);
                u_growlSet($eventid, $page, $g_race_msg_success);
            }
            else      // report fail
            {
                u_writelog("ERROR - attempt to send message failed", $eventid);
                u_growlSet($eventid, $page, $g_race_msg_fail);
            }
        }

        // ------- CANCEL --------------------------------------------------------------------------
        elseif ($pagestate == "cancel")
        {
            $result = $event_o->event_updatestatus($eventid, "cancelled");
            if ($result)
            {
                u_writelog("race CANCELLED", $eventid);
                u_growlSet($eventid, $page, $g_race_cancel_success);
                u_growlset($eventid, $page, $g_race_close_reminder);
            }
            else
            {
                u_writelog("ERROR - attempt to cancel race failed ", $eventid);
                u_growlSet($eventid, $page, $g_race_cancel_fail);
            }
        }

        // ------- UN-CANCEL --------------------------------------------------------------------------
        elseif ($pagestate == "uncancel")
        {
            $result = $event_o->event_updatestatus($eventid, $_SESSION["e_$eventid"]['ev_prevstatus']);
            if ($result)
            {
                u_writelog("cancelled race reset", $eventid);
                u_growlSet($eventid, $page, $g_race_uncancel_success);
            }
            else
            {
                u_writelog("ERROR - attempt to reset cancelled race failed ", $eventid);
                u_growlSet($eventid, $page, $g_race_uncancel_fail);
            }
        }

        // ------- ABANDON --------------------------------------------------------------------------
        elseif ($pagestate == "abandon")
        {
            $result = $event_o->event_updatestatus($eventid, "abandoned");
            if ($result)
            {
                u_writelog("race ABANDONED", $eventid);
                u_growlSet($eventid, $page, $g_race_abandon_success);
                u_growlset($eventid, $page, $g_race_close_reminder);
            }
            else
            {
                u_writelog("ERROR - attempt to abandon race failed ", $eventid);
                u_growlSet($eventid, $page, $g_race_abandon_fail);
            }
        }

        // ------- UN-ABANDON --------------------------------------------------------------------------
        elseif ($pagestate == "unabandon")
        {
            $result = $event_o->event_updatestatus($eventid, $_SESSION["e_$eventid"]['ev_prevstatus']);
            if ($result)
            {
                u_writelog("abandoned race reset", $eventid);
                u_growlSet($eventid, $page, $g_race_unabandon_success);

            }
            else
            {
                u_writelog("ERROR - attempt to reset abandoned race failed ", $eventid);
                u_growlSet($eventid, $page, $g_race_unabandon_fail);
            }
        }

        // ------- CLOSE --------------------------------------------------------------------------
        elseif ($pagestate == "close")
        {
            if (!empty($_REQUEST['message']))       // send message if necessary
            {
                // set fields to enter
                $fields = array(
                    "name"    => "OOD",
                    "subject" => $_SESSION["e_$eventid"]['ev_fname']." - OOD closing message",
                    "message" => $_REQUEST['message'],
                    "email"   => "",
                    "status"  => "received",
                );
                $add = $event_o->event_addmessage($eventid, $fields);
            }

            $result = $event_o->event_close($eventid);
            if ($result)
            {
                $_SESSION["e_$eventid"]['exit'] = true;
                u_writelog("race COMPLETED", $eventid);
                u_growlSet(0, $page, $g_race_close_success);

                // return to dashboard
                header("Location: pickrace_pg.php?");
                exit();
            }
            else
            {
                u_writelog("ERROR - attempt to close race failed ", $eventid);
                u_growlSet($eventid, $page, $g_race_close_fail);
            }   
        }

        // ------- RESET --------------------------------------------------------------------------
        elseif ($pagestate == "reset")
        {           
            if (strtolower($_REQUEST['confirm'] == "reset"))
            {
                $result = $event_o->event_reset($eventid);
                if ($result)
                {
                    u_writelog("RESET - race reset by user", $eventid);
                    u_growlSet($eventid, $page, $g_race_reset_success);
                }
                else
                {
                    u_writelog("ERROR - attempt to reset race failed ", $eventid);
                    u_growlSet($eventid, $page, $g_race_reset_fail);
                }
            }
            else
            {
                u_writelog("RESET not implemented - command not confirmed by user  ", $eventid);
                u_growlSet($eventid, $page, $g_race_reset_noconfirm);
            }
        }

        elseif ($pagestate == "setalllaps")        // sets laps for all fleets
        {
            $lapsetfail = false;
            $growlmsg   = "Setting laps:<br>";
            for ($i=1; $i<=$_SESSION["e_$eventid"]['rc_numfleets']; $i++)
            {
                $fleetname = $_SESSION["e_$eventid"]["fl_$i"]['name'];
                $status = $race_o->race_laps_set($i, $_REQUEST['laps'][$i]);
                if ($status)
                {
                    if ($status == "less_than_current")
                    {
                        $growlmsg.="$fleetname - not set, at least one boat is on this lap already<br>";
                        $lapsetfail = true;
                    }
                    else
                    {
                        u_writelog("setlaps: $fleetname - {$_REQUEST['laps'][$i]} laps", $eventid);
                    }
                }
                else
                {
                    u_writelog("setlaps: $fleetname - failed [{$_REQUEST['laps'][$i]}] laps", $eventid);
                    $growlmsg.= "$fleetname - laps set FAILED <br>";
                    $lapsetfail = true;
                }
            }
            if ($lapsetfail)  { u_growlSet($eventid, $page, $g_race_lapset_fail, array($growlmsg)); }
        }

        elseif ($pagestate == "setlap")   // sets lap for one fleet
        {
            $fleetname = $_SESSION["e_$eventid"]["fl_{$_REQUEST['fleet']}"]['name'];
            $rs = $race_o->race_laps_set($_REQUEST['fleet'], $_REQUEST['laps']);
            //-- u_writedbg("status = $status", __FILE__,__FUNCTION__,__LINE__);
            if ($rs)
            {
                if ($rs['result'] === "less_than_current")
                {
                    u_growlSet($eventid, $page, $g_race_fleetset_notok, array($fleetname));
                }
                else
                {
                    u_writelog("setlaps: $fleetname - {$_REQUEST['laps']} laps", $eventid);
                }
            }
            else
            {
                u_writelog("setlaps: $fleetname - failed [{$_REQUEST['laps'][$i]} laps]", $eventid);
                u_growlSet($eventid, $page, $g_race_fleetset_fail, array($fleetname));
            }
        }

        else
        {
            u_exitnicely($scriptname, $eventid,"event001",$lang['err']['exit-action']);
        }
        
        // return to race page
        header("Location: race_pg.php?eventid=$eventid&pagestate=race");
        exit();
    } 
    else
    {
        u_exitnicely($scriptname, $eventid,"sys005",$lang['err']['exit-action']);
    }  
       
}
else
{
    u_exitnicely($scriptname, $eventid,"sys005",$lang['err']['exit-action']);
}

// --- FUNCTIONS ---------------------------------------------------------------------------

?>