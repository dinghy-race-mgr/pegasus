<?php
/* button configuration for race page */


// ---- edit race button ----------------------------------------------------------------
$btn_change = array(
    "id"        => "change",
    "style"     => "primary",
    "size"      => "lg",
    "label"     => "Change Details",
    "glyph"     => "glyphicon-pencil",
    "g-pos"     => "left",
    "popover"   => "click to change race details or add notes to race/results",
    "pop-pos"   => "left",
    "block"     => "btn-block",
    "disabled"  => "",
    "top-margin"  => "margin-top-10",
);

$mdl_change = array(
    "id"          => "change",
    "style"       => "primary",
    "size"        => "lg",
    "title"       => "<b>Change Race Details - $eventname</b>",
    "body"        => "",
    "form"        => true,
    "script"      => "",
    "action"      => "race_sc.php?eventid=$eventid&pagestate=change",
    "target"      => "",
    "onsubmit"    => "",
    "close-lbl"   => "nope",
    "reset-lbl"   => "",
    "submit-lbl"  => "change details",
    "reload"      => true,
    "footer"      => "",
);

// ---- view format button ---------------------------------------------------------------
$btn_format = array(
    "id"        => "raceformat",
    "size"      => "lg",
    "style"     => "info",
    "label"     => "Race Format",
    "glyph"     => "glyphicon-th-list",
    "g-pos"     => "left",
    "popover"   => "click to get race format details: start times, signals, tide etc.",
    "pop-pos"   => "left",
    "block"     => "btn-block",
    "disabled"  => "",
    "top-margin"  => "margin-top-10",
);

$mdl_format = array(
    "id"          => "raceformat",
    "style"       => "primary",
    "size"        => "lg",
    "title"       => "<b>Race Format Details  - $eventname</b>",
    "body"        => "",
    "form"        => false,
    "script"      => "",
    "action"      => "",
    "target"      => "",
    "onsubmit"    => "",
    "close-lbl"   => "",
    "reset-lbl"   => "",
    "submit-lbl"  => "",
    "reload"      => false,
    "footer"      => "",
);


// ---- send message button ---------------------------------------------------------------
$btn_message = array(
    "id"        => "sendmessage",
    "size"      => "lg",
    "style"     => "primary",
    "label"     => "Report Issue",
    "glyph"     => "glyphicon-send",
    "g-pos"     => "left",
    "popover"   => "click to send email message to support team",
    "pop-pos"   => "left",
    "block"     => "btn-block",
    "disabled"  => "",
    "top-margin"  => "margin-top-10",
);

$mdl_message = array(
    "id"          => "sendmessage",
    "style"       => "primary",
    "size"        => "lg",
    "title"       => "<b>Send Message</b>",
    "body"        => "",
    "form"        => true,
    "script"      => "",
    "action"      => "race_sc.php?eventid=$eventid&pagestate=message",
    "target"      => "",
    "onsubmit"    => "",
    "close-lbl"   => "nope",
    "reset-lbl"   => "",
    "submit-lbl"  => "send message",
    "reload"      => false,
    "footer"      => "",
);


// ---- pursuit start times button ----------------------------------------------------------
$btn_pursuit = array(
    "id"        => "pursuitstart",
    "size"      => "lg",
    "style"     => "primary",
    "label"     => "Get Start Times",
    "glyph"     => "glyphicon-time",
    "g-pos"     => "left",
    "popover"   => "click to get list of start times for this pursuit race",
    "pop-pos"   => "left",
    "block"     => "btn-block",
    "disabled"  => "",
    "top-margin"  => "margin-top-10",
);

$mdl_pursuit = array(
    "id"          => "pursuitstart",
    "style"       => "primary",
    "size"        => "lg",
    "title"       => "<b>Pursuit Start Times</b>",
    "body"        => "",
    "form"        => true,
    "script"      => "",
    "action"      => "rbx_ex_pursuitstart.php?eventid=$eventid&print=false",
    "target"      => "_BLANK",
    "onsubmit"    => "",
    "close-lbl"   => "nope",
    "reset-lbl"   => "",
    "submit-lbl"  => "get start times",
    "reload"      => false,
    "footer"      => "",
);


// ---- cancel race button --------------------------------------------------------------
$btn_cancel = array(
    "id"        => "cancel",
    "style"     => "warning",
    "size"      => "lg",
    "label"     => "Cancel Race",
    "glyph"     => "glyphicon-flag",
    "g-pos"     => "left",
    "popover"   => "marks the race as cancelled - only use if the race has NOT started",
    "pop-pos"   => "left",
    "block"     => "btn-block",
    "disabled"  => "",
    "top-margin"  => "margin-top-10",
);

$mdl_cancel = array(
    "id"          => "cancel",
    "style"       => "warning",
    "size"        => "md",
    "title"       => "<b>CANCEL - $eventname</b>",
    "body"        => "",
    "form"        => true,
    "script"      => "",
    "action"      => "race_sc.php?eventid=$eventid&eventname=$eventname&pagestate=cancel",
    "target"      => "",
    "onsubmit"    => "",
    "close-lbl"   => "changed my mind",
    "reset-lbl"   => "",
    "submit-lbl"  => "cancel race",
    "reload"      => true,
    "footer"      => "",
);

// ---- un-cancel race button ----------------------------------------------------------------
$btn_uncancel = array(
    "id"        => "uncancel",
    "style"     => "warning",
    "size"      => "lg",
    "label"     => "Undo Cancel",
    "glyph"     => "",
    "g-pos"     => "left",
    "popover"   => "reset the cancelled race",
    "pop-pos"   => "left",
    "block"     => "btn-block",
    "disabled"  => "",
    "top-margin"  => "margin-top-10",
);

$mdl_uncancel = array(
    "id"          => "uncancel",
    "style"       => "warning",
    "size"        => "md",
    "title"       => "<b>CANCEL reset  - $eventname</b>",
    "body"        => "",
    "form"        => true,
    "script"      => "",
    "action"      => "race_sc.php?eventid=$eventid&eventname=$eventname&pagestate=uncancel&prevstatus={$_SESSION["e_$eventid"]['ev_prevstatus']}",
    "target"      => "",
    "onsubmit"    => "",
    "close-lbl"   => "changed my mind",
    "reset-lbl"   => "",
    "submit-lbl"  => "reset cancelled race",
    "reload"      => true,
    "footer"      => "",
);


// ---- abandon race button ----------------------------------------------------------------
$btn_abandon = array(
    "id"        => "abandon",
    "style"     => "warning",
    "size"      => "lg",
    "label"     => "Abandon Race",
    "glyph"     => "glyphicon-flag",
    "g-pos"     => "left",
    "popover"   => "marks the race as abandoned with no results recorded",
    "pop-pos"   => "left",
    "block"     => "btn-block",
    "disabled"  => "",
    "top-margin"  => "margin-top-10",
);

$mdl_abandon = array(
    "id"          => "abandon",
    "style"       => "warning",
    "size"        => "md",
    "title"       => "<b>ABANDON  - $eventname</b>",
    "body"        => "",
    "form"        => true,
    "script"      => "",
    "action"      => "race_sc.php?eventid=$eventid&eventname=$eventname&pagestate=abandon",
    "target"      => "",
    "onsubmit"    => "",
    "close-lbl"   => "changed my mind",
    "reset-lbl"   => "",
    "submit-lbl"  => "abandon race",
    "reload"      => true,
    "footer"      => "",
);


// ---- un-abandon race button ----------------------------------------------------------------
$btn_unabandon = array(
    "id"        => "unabandon",
    "style"     => "warning",
    "size"      => "lg",
    "label"     => "Undo Abandon",
    "glyph"     => "",
    "g-pos"     => "left",
    "popover"   => "undo the race abandonment",
    "pop-pos"   => "left",
    "block"     => "btn-block",
    "disabled"  => "",
    "top-margin"  => "margin-top-10",
);

$mdl_unabandon = array(
    "id"          => "unabandon",
    "style"       => "warning",
    "size"        => "md",
    "title"       => "<b>ABANDON reset  - $eventname</b>",
    "body"        => "",
    "form"        => true,
    "script"      => "",
    "action"      => "race_sc.php?eventid=$eventid&eventname=$eventname&pagestate=unabandon&prevstatus={$_SESSION["e_$eventid"]['ev_prevstatus']}",
    "target"      => "",
    "onsubmit"    => "",
    "close-lbl"   => "no thanks",
    "reset-lbl"   => "",
    "submit-lbl"  => "reset abandoned race",
    "reload"      => true,
    "footer"      => "",
);

// ---- close race button --------------------------------------------------------------------------
$btn_close = array(
     "id"       => "close",
    "style"     => "primary",
    "size"      => "lg",
    "label"     => "Close Race",
    "glyph"     => "glyphicon-log-out",
    "g-pos"    => "left",
    "popover"   => "click here to close the race and tidy up the system",
    "pop-pos"   => "left",
    "block"     => "btn-block",
    "disabled"  => "",
    "top-margin"  => "margin-top-10",
);

$mdl_close = array(
    "id"          => "close",
    "style"       => "primary",
    "size"        => "md",
    "title"       => "<b>CLOSE - $eventname</b>",
    "body"        => "",
    "form"        => true,
    "script"      => "",
    "action"      => "race_sc.php?eventid=$eventid&pagestate=close",
    "target"      => "",
    "onsubmit"    => "",
    "close-lbl"   => "changed my mind",
    "reset-lbl"   => "",
    "submit-lbl"  => "CLOSE race",
    "reload"      => true,
    "footer"      => "",
);


// ---- reset race button -----------------------------------------------------------------------------------
$btn_reset = array(
    "id"        => "reset",
    "style"     => "danger",
    "size"      => "lg",
    "label"     => "Reset Race",
    "glyph"     => "glyphicon-refresh",
    "g-pos"     => "left",
    "popover"   => "click here to reset the race - start again",
    "pop-pos"   => "left",
    "block"     => "btn-block",
    "disabled"  => "",
    "top-margin"  => "margin-top-10",
);

$mdl_reset = array(
    "id"          => "reset",
    "style"       => "danger",
    "size"        => "md",
    "title"       => "<b>RESET - $eventname</b>",
    "body"        => "",
    "form"        => true,
    "script"      => "",
    "action"      => "race_sc.php?eventid=$eventid&pagestate=reset",
    "target"      => "",
    "onsubmit"    => "",
    "close-lbl"   => "changed my mind",
    "reset-lbl"   => "",
    "submit-lbl"  => "RESET race",
    "reload"      => true,
    "footer"      => "",
);


// ---- set laps button ----------------------------------------------------------------

$btn_setlaps = array(
    "id"         => "setlaps",
    "size"       => "md",
    "style"      => "danger",
    "label"      => "Set Laps",
    "glyph"      => "glyphicon-list",
    "g-pos"      => "left",
    "popover"    => "click here to set or change the laps for each fleet",
    "pop-pos"    => "bottom",
    "block"      => "btn-block",
    "disabled"   => "",
    "top-margin" => "margin-top-0",
);

$mdl_setlaps = array(
    "id"          => "setlaps",
    "size"        => "md",
    "style"       => "primary",
    "title"       => "Set Laps",
    "body"        => "",
    "form"        => true,
    "script"      => "",
    "action"      => "race_sc.php?eventid=$eventid&pagestate=setalllaps",
    "target"      => "",
    "onsubmit"    => "",
    "close-lbl"   => "nope",
    "reset-lbl"   => "",
    "submit-lbl"  => "set laps",
    "reload"      => true,
    "footer"      => "",
);


?>