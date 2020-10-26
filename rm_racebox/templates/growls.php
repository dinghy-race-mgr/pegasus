<?php
/*
 * growls.php
 * growl definitions for the racebox application
 * "msg"  => "<b>That is WEIRD</b><br>
               The command (%s) sent to the %s page is not recognised<br>
               <i>Please let your raceManager guru know.</i>",
 */

// ----- GENERAL ----------------------------------------------------------------------------
$g_sys_invalid_pagestate = array(
    "type" => "danger",
    "msg"  => "<b>That is WEIRD</b><br> - the command (%s) sent to the %s page is not recognised<br><i>Please let your raceManager guru know.</i>",
);
// ----- page: PICKRACE ---------------------------------------------------------------------

// ---------- pagestate: ADD RACE -----------------------------------------------------------
$g_add_event_success = array(
   "type" => "success",
   "msg"  => "<b>SUCCESS</b><br>Event %s on %s has been added to the programme",
);

$g_add_event_warning = array(
   "type" => "warning",
   "msg"  => "<b>WARNING</b><br>Event %s on %s added to the programme - but duties not added",
);

$g_add_event_fail = array(
   "type" => "danger",
   "msg"  => "<b>SORRY</b><br>Event %s has not been added to the programme",
);

// ----- page: RACE     ---------------------------------------------------------------------

// ----------- pagestate: CHANGE ------------------------------------------------------------
$g_event_change_success = array(
    "type" => "success",
    "msg"  => "<b>SUCCESS:</b><br>Event details updated",
);

$g_event_change_fail = array(
    "type" => "danger",
    "msg"  => "<b>SORRY:</b><br>Event details update failed",
);

$g_event_change_none = array(
    "type" => "info",
    "msg"  => "<b>INFO:</b><br>Event details not changed - nothing to update",
);

// ----------- pagestate: MESSAGE -----------------------------------------------------------
$g_race_msg_success = array(
    "type" => "success",
    "msg"  => "<br>Message sent",
);

$g_race_msg_fail = array(
    "type" => "danger",
    "msg"  => "<b>SORRY:</b><br>Sending message failed",
);

// ----------- pagestate: CANCEL ------------------------------------------------------------
$g_race_cancel_success = array(
    "type" => "success",
    "msg"  => "<br>Race successfully cancelled",
);

$g_race_cancel_fail = array(
    "type" => "danger",
    "msg"  => "<b>SORRY:</b><br>Attempt to cancel race failed<br><i>Please let your raceManager guru know.</i>",
);

// ----------- pagestate: UN-CANCEL ---------------------------------------------------------
$g_race_uncancel_success = array(
    "type" => "success",
    "msg"  => "Cancelled race successfully reset",
);

$g_race_uncancel_fail = array(
    "type" => "danger",
    "msg"  => "Attempt to reset cancelled race failed<br><i>Please let your raceManager guru know.</i>",
);

// ----------- pagestate: ABANDON -----------------------------------------------------------
$g_race_abandon_success = array(
    "type" => "success",
    "msg"  => "Race successfully abandoned - please CLOSE the race to finish",
);

$g_race_abandon_fail = array(
    "type" => "danger",
    "msg"  => "Attempt to abandon race failed<i>Please let your raceManager guru know.</i>",
);

// ----------- pagestate: UN-ABANDON --------------------------------------------------------
$g_race_unabandon_success = array(
    "type" => "success",
    "msg"  => "Abandoned race successfully reset",
);

$g_race_unabandon_fail = array(
    "type" => "danger",
    "msg"  => "Attempt to reset abandoned race failed<br><i>Please let your raceManager guru know.</i>",
);

// ----------- pagestate: CLOSE -------------------------------------------------------------
$g_race_close_success = array(
    "type" => "info",
    "msg"  => "<b>RACE CLOSED:</b><br> -- Thank you",
);

$g_race_close_fail = array(
    "type" => "warning",
    "msg"  => "<b>SORRY:</b> race<br>CLOSE failed<br><i>Please let your raceManager guru know.</i>",
);

$g_race_close_reminder = array(
    "type" => "info",
    "msg"  => "<span style='font-size: 1.2em;' ><b>Now CLOSE the race ...</b></span>",
);

// ----------- pagestate: RESET -------------------------------------------------------------
$g_race_reset_success = array(
    "type" => "success",
    "msg"  => "Race has been reset<br><i>entries from raceManager SAILOR can be reloaded</i>",
);

$g_race_reset_fail = array(
    "type" => "danger",
    "msg"  => "Race RESET failed<br>internal system problem<br><i>Please let your raceManager guru know.</i>",
);

$g_race_reset_noconfirm = array(
    "type" => "warning",
    "msg"  => "Race NOT reset - reset confirmation not entered<br>",
);

// ----------- pagestate: SETLAPS and SETALLLAPS -------------------------------------------------------------

$g_race_laps_not_set = array(
    "type" => "warning",
    "delay"=> "10000",
    "msg"  => "<b>Laps not set </b><br>- at least one fleet has entries but the no. of laps is set to 0 ",
);

$g_race_lapset_fail = array(
    "type" => "danger",
    "msg"  => "%s",
);

$g_race_fleetset_notok = array(
    "type" => "danger",
    "msg"  => "laps not set for %s<br>- at least one boat is on this lap already",
);

$g_race_fleetset_fail = array(
    "type" => "danger",
    "msg"  => "setting laps for %s FAILED",
);


// ----- page: ENTRIES  ---------------------------------------------------------------------
$g_invalid_pagestate = array(
    "type" => "danger",
    "msg"  => "system problem - entries not processed (pagestate error)",
);
// ----------- pagestate: CHANGE ------------------------------------------------------------
$g_entries_change_none = array(
    "type" => "info",
    "msg"  => "no changes specified - entry not updated",
);
$g_entries_change_entry_failed = array(
    "type" => "danger",
    "msg"  => "attempt to change entry detail failed &hellip;<br><i>Please let your raceManager guru know.</i>",
);
// ----------- pagestate: DUTYPOINTS --------------------------------------------------------
$g_entries_add_duty_failed = array(
    "type" => "danger",
    "msg"  => "attempt to set duty code failed &hellip;<br><i>Please let your raceManager guru know.</i>",
);
// ----------- pagestate: UNDUTY ------------------------------------------------------------
$g_entries_remove_duty_failed = array(
    "type" => "danger",
    "msg"  => "attempt to remove duty code detail failed &hellip;<br><i>Please let your raceManager guru know.</i>",
);
// ----------- pagestate: DELETE ------------------------------------------------------------
$g_entries_delete_failed = array(
    "type" => "danger",
    "msg"  => "attempt to delete entry failed &hellip;<br><i>Please let your raceManager guru know.</i>",
);
//----------- pagestate: LOAD ENTRIES, LOAD REGULAR, LOAD PREVIOUS -------------------------
//$g_entries_loaded = array(
//    "type" => "success",
//    "msg"  => "%s entries added &hellip;<br>",
//);

$g_entries_failed = array(
    "type" => "warning",
    "msg"  => "%s competitor signon on request not accounted for &hellip;<br>",
);

$g_entries_report = array(
    "type" => "info",
    "msg"  => "%s competitor sign on requests found: <br>- %s entries made, <br>- %s existing entries updated, <br>- %s existing entries deleted<br>",
);

$g_entries_none = array(
    "type" => "info",
    "msg"  => "no entries to process &hellip;<br>",
);
// ----------- pagestate: ADD COMPETITOR ----------------------------------------------------
$g_entry_add_comp_success = array(
    "type" => "success",
    "msg"  => "<b>Competitor ADDED</b>  - %s <br><br> REMEMBER to use the ENTER BOAT option to enter it into this race",
);
$g_entry_add_comp_fail = array(
    "type" => "danger",
    "msg"  => "<b>SORRY: Add Competitor FAILED</b><br>boat - %s <br>Please let your raceManager guru know.",
);

$g_entry_add_comp_exists = array(
    "type" => "warning",
    "msg"  => "<b>SORRY: Add Competitor FAILED</b><br>boat - %s already exists in the system<br>Please try searching using Enter Boat.",
);
// ----------- pagestate: ADD CLASS ---------------------------------------------------------
$g_entry_add_class_success = array(
    "type" => "success",
    "msg"  => "<b>Class ADDED: </b> - %s",
);
$g_entry_add_class_fail = array(
    "type" => "danger",
    "msg"  => "<b>SORRY: Add Class FAILED</b><br>%s <br>Please let your raceManager guru know.",
);
// ----- page: START    ---------------------------------------------------------------------
$g_start_timer_stop = array(
    "type" => "info",
    "msg"  => "Master Timer stopped &hellip;",
);

$g_start_timer_continue = array(
    "type" => "info",
    "msg"  => "Master Timer stop request NOT confirmed correctly &hellip;",
);

$g_start_timer_adjusted = array(
    "type" => "success",
    "msg"  => "<b>Master Timer reset with first preparatory signal at %s &hellip;</b>",
);

$g_start_recall_fail = array(
    "type" => "danger",
    "msg"  => "<b>setting restart time - FAILED</b><br>New start time must be later than original start time",
);

$g_start_recall_success = array(
    "type" => "success",
    "msg"  => "<b>Start %s - start time reset to %s</b>",
);


// ----- page: TIMER    ---------------------------------------------------------------------
//<br>Not possible to time or finish a boat yet

$g_timer_racenotstarted = array(
    "type" => "warning",
    "msg"  => "<b>This fleet has not started yet</b>",
);

$g_timer_doubleclick = array(
    "type" => "warning",
    "msg"  => "<b>%s: double click detected</b></br>lap/finish time not recorded",
);

$g_timer_finish = array(
    "type" => "success",
    "msg"  => "%s finished",
);

$g_timer_firstfinish = array(
    "type" => "info",
    "msg"  => "%s: is the first boat to finish in its fleet</br> ALL other boats in that fleet will now finish when they next cross the finish line",
);

$g_timer_timingfailed = array(
    "type" => "danger",
    "msg"  => "<b>Timing for %s failed</b><br>[%s]",
);

$g_timer_finishfailed = array(
    "type" => "danger",
    "msg"  => "<b>Finish option for %s failed</b><br> [%s]",
);

$g_timer_setcodefailed = array(
    "type" => "danger",
    "msg"  => "<b>Set code option for %s failed</b><br>[%s]",
);

$g_timer_editlaps_failed = array(
    "type" => "danger",
    "msg"  => "<b>%s: edit lap times option FAILED</b><br>[%s]",
);

$g_timer_editlaps_none = array(
    "type" => "info",
    "msg"  => "<b>%s:  no laps times to change</b><br>",
);

$g_timer_editlaps_success = array(
    "type" => "info",
    "msg"  => "<b>%s : lap times changed for lap(s): %s</b>",
);

$g_timer_undo_success = array(
    "type" => "success",
    "msg"  => "%s: last timing removed via UNDO</b>",
);

$g_timer_undo_fail = array(
    "type" => "danger",
    "msg"  => "<b>Unknown problem attempting to UNDO last timing</b>",
);

$g_timer_shortenone_report = array(
    "type" => "info",
    "msg"  => "%s",
);

$g_timer_shortenall_report = array(
    "type" => "info",
    "msg"  => "Shorten course applied to the following fleets:<br>%s",
);

$g_timer_shorten_fail = array(
    "type" => "danger",
    "msg"  => "<b>Unknown problem attempting to shorten one or more fleets</b><br>[%s]",
);




// ----- page: PURSUIT  ---------------------------------------------------------------------
// ----- page: RESULTS  ---------------------------------------------------------------------
$g_results_zero_declare = array(
    "type" => "info",
    "msg"  => "<b>no retirements/declarations to process</b>",
);

$g_result_edit_success = array(
    "type" => "success",
    "msg"  => "<b>Competitor %s deleted from results</b>",
);

$g_result_del_success = array(
    "type" => "success",
    "msg"  => "<b>Competitor %s deleted from results</b>",
);

$g_result_del_fail = array(
    "type" => "danger",
    "msg"  => "<b>SORRY: attempt to delete competitor %s FAILED</b>",
);

$g_results_recalc_fail = array(
    "type" => "danger",
    "msg"  => "<b>results update not resolved</b>",
);

$g_results_recalc_success = array(
    "type" => "success",
    "msg"  => "<b>results updated successfully</b>",
);

?>