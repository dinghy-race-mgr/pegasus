<?php
/* button configuration for race page
BUTTONS
    Parameters
        label     : label required  (true|false)
        glyph     : glyph required  (true|false)
        g-pos     : glyph position (left|right)
        popover   : popover required (true|false)

    Fields
        id        : id for button
        style     : bootstrap style for button
        size      : bootstrap size for button
        label     : label for button
        link      : url link for button
        glyph     : glyph name
        popover   : popover text
        pop-pos   : popover position
        block     : 'btn-block' if a block button otherwise leave blank
        disabled  : 'disabled' if button is to be disabled otherwise leave blank
        top-margin: top margin style (e.g margin-top-20)

  MODALS
    Parameters
        form       : form/validation required in modal (true|false)
        footer     : form/validation required in modal (true|false)
        reload     : reload of main page required after modal closed (true|false)
        close      : cancel button required in modal (true|false)
        reset      : reset button required in modal (true|false)
        submit     : submit button required in modal (true|false)

    Fields
        id         : base id used for modal and form
        size       : size of modal using bootstrap codes (sm|md|lg)
        style      : bootstrap style for modal header
        title      : title to appear in modal header
        body       : html content for body of modal
        script     : js script to be run when modal opens - blank if not required
        action     : action to take on post form - link to script
        target     : if set can be used to set window target
        onsubmit   : js action to take on submit
        close-lbl  : label on close/cancel/back button
        reset-lbl  : label on reset button
        submit-lbl : label on submit button
        footer     : content of footer if requested in params (not used in forms)
*/

// ---- edit race button ----------------------------------------------------------------

$btn_change = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"        => "change",
        "style"     => "success",
        "size"      => "lg",
        "label"     => "Change Details",
        "glyph"     => "glyphicon-pencil",
        "popover"   => "click to change race details or add notes to race/results",
        "pop-pos"   => "left",
        "disabled"  => "",
        "block"     => "btn-block",
        "top-margin"  => "margin-top-10",
    ),
);

$mdl_change = array(
    "form"    => true,
    "footer"  => true,
    "reload"  => true,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"          => "change",
        "size"        => "lg",
        "style"       => "success",
        "title"       => "<b>Change Race Details - $eventname</b>",
        "body"        => "",
        "script"      => "",
        "action"      => "race_sc.php?eventid=$eventid&pagestate=change",
        "target"      => "",
        "onsubmit"    => "",
        "close-lbl"   => "changed my mind",
        "reset-lbl"   => "",
        "submit-lbl"  => "change details",
        "footer"      => "",
    ),
);



// ---- view format button ---------------------------------------------------------------
$btn_format = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"        => "raceformat",
        "size"      => "lg",
        "style"     => "info",
        "label"     => "Race Format",
        "glyph"     => "glyphicon-th-list",
        "popover"   => "click to get race format details: start times, signals, tide etc.",
        "pop-pos"   => "left",
        "block"     => "btn-block",
        "disabled"  => "",
        "top-margin"  => "margin-top-10",
    ),
);

$mdl_format = array(
    "form"    => false,
    "footer"  => true,
    "reload"  => false,
    "close"   => false,
    "reset"   => false,
    "submit"  => false,
    "fields"  => array(
        "id"          => "raceformat",
        "style"       => "info",
        "size"        => "lg",
        "title"       => "<b>Race Format Details</b>",
        "body"        => "",
        "script"      => "",
        "action"      => "",
        "target"      => "",
        "onsubmit"    => "",
        "close-lbl"   => "",
        "reset-lbl"   => "",
        "submit-lbl"  => "",
        "footer"      => "",
    ),
);


// ---- send message button ---------------------------------------------------------------
$btn_message = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"        => "sendmessage",
        "size"      => "lg",
        "style"     => "success",
        "label"     => "Report Issue",
        "glyph"     => "glyphicon-send",
        "popover"   => "click to send email message to support team",
        "pop-pos"   => "left",
        "block"     => "btn-block",
        "disabled"  => "",
        "top-margin"  => "margin-top-10"
    ),
);

$mdl_message = array(
    "form"    => true,
    "footer"  => true,
    "reload"  => true,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"          => "sendmessage",
        "style"       => "success",
        "size"        => "lg",
        "title"       => "<b>Send Message</b>",
        "body"        => "",
        "script"      => "",
        "action"      => "race_sc.php?eventid=$eventid&pagestate=message",
        "target"      => "",
        "onsubmit"    => "",
        "close-lbl"   => "changed my mind",
        "reset-lbl"   => "",
        "submit-lbl"  => "send message",
        "footer"      => ""
    ),
);


// ---- pursuit start times button ----------------------------------------------------------
$btn_pursuit = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"        => "pursuitstart",
        "size"      => "lg",
        "style"     => "info",
        "label"     => "Get Start Times",
        "glyph"     => "glyphicon-time",
        "popover"   => "click to get list of start times for this pursuit race",
        "pop-pos"   => "left",
        "block"     => "btn-block",
        "disabled"  => "",
        "top-margin"  => "margin-top-10"
    ),
);

$mdl_pursuit = array(
    "form"    => true,
    "footer"  => true,
    "reload"  => true,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"          => "pursuitstart",
        "style"       => "info",
        "size"        => "lg",
        "title"       => "<b>Pursuit Start Times</b>",
        "body"        => "",
        "script"      => "",
        "action"      => "rbx_ex_pursuitstart.php?eventid=$eventid&print=false", // FIXME this function doesn't exist
        "target"      => "_BLANK",
        "onsubmit"    => "",
        "close-lbl"   => "close",
        "reset-lbl"   => "",
        "submit-lbl"  => "get start times",
        "footer"      => ""
    ),
);


// ---- cancel race button --------------------------------------------------------------
$btn_cancel = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"        => "cancel",
        "style"     => "primary",
        "size"      => "lg",
        "label"     => "Cancel Race",
        "glyph"     => "glyphicon-flag",
        "popover"   => "marks the race as cancelled - only use if the race has NOT started",
        "pop-pos"   => "left",
        "block"     => "btn-block",
        "disabled"  => "",
        "top-margin"  => "margin-top-10"
    ),
);

$mdl_cancel = array(
    "form"    => true,
    "footer"  => true,
    "reload"  => true,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"          => "cancel",
        "style"       => "success",
        "size"        => "md",
        "title"       => "<b>CANCEL - $eventname</b>",
        "body"        => "",
        "script"      => "",
        "action"      => "race_sc.php?eventid=$eventid&eventname=$eventname&pagestate=cancel",
        "target"      => "",
        "onsubmit"    => "",
        "close-lbl"   => "changed my mind",
        "reset-lbl"   => "",
        "submit-lbl"  => "cancel race",
        "footer"      => ""
    ),
);

// ---- un-cancel race button ----------------------------------------------------------------
$btn_uncancel = array(
    "label"     => true,
    "glyph"     => false,
    "g-pos"     => "",
    "popover"   => true,
    "fields"    => array(
        "id"        => "uncancel",
        "style"     => "warning",
        "size"      => "lg",
        "label"     => "Undo Cancel",
        "glyph"     => "",
        "popover"   => "reset the cancelled race",
        "pop-pos"   => "left",
        "block"     => "btn-block",
        "disabled"  => "",
        "top-margin"  => "margin-top-10"
    ),
);

$mdl_uncancel = array(
    "form"    => true,
    "footer"  => true,
    "reload"  => true,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"          => "uncancel",
        "style"       => "warning",
        "size"        => "md",
        "title"       => "<b>CANCEL reset  - $eventname</b>",
        "body"        => "",
        "script"      => "",
        "action"      => "race_sc.php?eventid=$eventid&eventname=$eventname&pagestate=uncancel&prevstatus={$_SESSION["e_$eventid"]['ev_prevstatus']}",
        "target"      => "",
        "onsubmit"    => "",
        "close-lbl"   => "changed my mind",
        "reset-lbl"   => "",
        "submit-lbl"  => "reset cancelled race",
        "footer"      => ""
    ),
);


// ---- abandon race button ----------------------------------------------------------------
$btn_abandon = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"        => "abandon",
        "style"     => "primary",
        "size"      => "lg",
        "label"     => "Abandon Race",
        "glyph"     => "glyphicon-flag",
        "popover"   => "marks the race as abandoned with no results recorded",
        "pop-pos"   => "left",
        "block"     => "btn-block",
        "disabled"  => "",
        "top-margin"  => "margin-top-10"
    ),
);

$mdl_abandon = array(
    "form"    => true,
    "footer"  => true,
    "reload"  => true,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"          => "abandon",
        "style"       => "success",
        "size"        => "md",
        "title"       => "<b>ABANDON  - $eventname</b>",
        "body"        => "",
        "script"      => "",
        "action"      => "race_sc.php?eventid=$eventid&eventname=$eventname&pagestate=abandon",
        "target"      => "",
        "onsubmit"    => "",
        "close-lbl"   => "changed my mind",
        "reset-lbl"   => "",
        "submit-lbl"  => "abandon race",
        "footer"      => ""
    ),
);


// ---- un-abandon race button ----------------------------------------------------------------
$btn_unabandon = array(
    "label"     => true,
    "glyph"     => false,
    "g-pos"     => "",
    "popover"   => "true",
    "fields"    => array(
        "id"        => "unabandon",
        "style"     => "warning",
        "size"      => "lg",
        "label"     => "Undo Abandon",
        "glyph"     => "",
        "popover"   => "undo the race abandonment",
        "pop-pos"   => "left",
        "block"     => "btn-block",
        "disabled"  => "",
        "top-margin"  => "margin-top-10"
    ),
);

$mdl_unabandon = array(
    "form"    => true,
    "footer"  => true,
    "reload"  => true,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"          => "unabandon",
        "style"       => "warning",
        "size"        => "md",
        "title"       => "<b>ABANDON reset  - $eventname</b>",
        "body"        => "",
        "script"      => "",
        "action"      => "race_sc.php?eventid=$eventid&eventname=$eventname&pagestate=unabandon&prevstatus={$_SESSION["e_$eventid"]['ev_prevstatus']}",
        "target"      => "",
        "onsubmit"    => "",
        "close-lbl"   => "changed my mind",
        "reset-lbl"   => "",
        "submit-lbl"  => "reset abandoned race",
        "footer"      => ""
    ),
);

// ---- close race button --------------------------------------------------------------------------
$btn_close = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"       => "close",
        "style"     => "primary",
        "size"      => "lg",
        "label"     => "Close Race",
        "glyph"     => "glyphicon-log-out",
        "popover"   => "click here to close the race and tidy up the system",
        "pop-pos"   => "left",
        "block"     => "btn-block",
        "disabled"  => "",
        "top-margin"  => "margin-top-10"
    ),
);

$mdl_close = array(
    "form"    => true,
    "footer"  => true,
    "reload"  => true,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"          => "close",
        "style"       => "success",
        "size"        => "md",
        "title"       => "<b>CLOSE - $eventname</b>",
        "body"        => "",
        "script"      => "",
        "action"      => "race_sc.php?eventid=$eventid&pagestate=close",
        "target"      => "",
        "onsubmit"    => "",
        "close-lbl"   => "changed my mind",
        "reset-lbl"   => "",
        "submit-lbl"  => "CLOSE race",
        "footer"      => ""
    ),
);


// ---- reset race button -----------------------------------------------------------------------------------
$btn_reset = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"        => "reset",
        "style"     => "danger",
        "size"      => "lg",
        "label"     => "Reset Race",
        "glyph"     => "glyphicon-refresh",
        "popover"   => "click here to reset the race - start again",
        "pop-pos"   => "left",
        "block"     => "btn-block",
        "disabled"  => "",
        "top-margin"  => "margin-top-10"
    ),
);

$mdl_reset = array(
    "form"    => true,
    "footer"  => true,
    "reload"  => true,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"          => "reset",
        "style"       => "danger",
        "size"        => "md",
        "title"       => "<b>RESET - $eventname</b>",
        "body"        => "",
        "script"      => "",
        "action"      => "race_sc.php?eventid=$eventid&pagestate=reset",
        "target"      => "",
        "onsubmit"    => "",
        "close-lbl"   => "changed my mind",
        "reset-lbl"   => "",
        "submit-lbl"  => "RESET race",
        "footer"      => ""
    ),
);


// ---- set laps button ----------------------------------------------------------------

$btn_setlaps = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => false,
    "fields"    => array(
        "id"         => "setlaps",
        "size"       => "md",
        "style"      => "danger",
        "label"      => "Set Laps",
        "glyph"      => "glyphicon-list",
        "popover"    => "click here to set or change the laps for each fleet",
        "pop-pos"    => "bottom",
        "block"      => "btn-block",
        "disabled"   => "",
        "top-margin" => "margin-top-0"
    ),
);

$mdl_setlaps = array(
    "form"    => true,
    "footer"  => true,
    "reload"  => true,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"          => "setlaps",
        "size"        => "md",
        "style"       => "primary",
        "title"       => "Set Laps",
        "body"        => "",
        "script"      => "",
        "action"      => "race_sc.php?eventid=$eventid&pagestate=setalllaps",
        "target"      => "",
        "onsubmit"    => "",
        "close-lbl"   => "changed my mind",
        "reset-lbl"   => "",
        "submit-lbl"  => "set laps",
        "footer"      => ""
    ),
);


