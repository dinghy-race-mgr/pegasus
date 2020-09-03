<?php

/**
 *  rbx_timer_btn
 * 
 *  Include file for configuring timer page buttons and modals
 * 
 *  Buttons:
 *    shorten       - drop down button to shorten course for all fleets
 *    quicktime     - button to start iframe modal with quick timing functions
 *    bunch         - button to start iframe modal with functions for handling bunches
 *    undo          - reverts last timing
 *    timersettings - button to change timer page settings
 * 
 *  Inline Buttons
 *    laptime       - inline button to time a lap for a boat
 *    finish        - inline button to finish a boat
 *    editlap       - inline button to start modal for editting lap times

*/

/*
  BUTTONS
  FIELDS
     id        : id for button
     style     : bootstrap style for button
     size      : bootstrap size for button
     label     : text label
     glyph     : glyph label
     g-pos     :  glyph position (left|right)
     popover   : popover text
     pop-pos   : popover position
     disabled  : 'disabled' if button is to be disabled otherwise leave blank
     block     : 'btn-block' if a block button otherwise leave blank
     top-margin: top margin style (e.g margin-top-20)

  MODALS
  FIELDS
    id         : base id used for modal and form
    size       : size of modal using bootstrap codes
    style      : bootstrap style for modal
    title      : title to appear in modal header
    body       : html content for body of modal (fields if form)
    form       : true if modal contains a form
    script     : js script to be run when modal opens

  F action     : action to take on post form - link to script
  F target     : if set can be used to set window target
  F onsubmit   : js action to take on submit
  F close-lbl  :  label on close/back button
  F reset-lbl  :  label on reset button
  F submit-lbl :  label on submit button
    reload     :  true if reloads parent page after modal is hidden
    footer     : content of footer if requested in params (not used in forms)
*/

// -----------------------------------------------------------------------------------
$mdl_setlaps = array(
    "form"    => true,
    "footer"  => false,
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
        "form"        => true,
        "script"      => "",
        "action"      => "timer_sc.php?eventid=$eventid&pagestate=setalllaps",
        "target"      => "",
        "onsubmit"    => "",
        "close-lbl"   => "nope",
        "reset-lbl"   => "",
        "submit-lbl"  => "set laps",
        "reload"      => true,
        "footer"      => ""
    ),
);

// -------- shorten all----------------------------------------------------------------
$btn_shorten = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => "true",
    "fields"    => array(
        "id"          => "shorten",
        "style"       => "primary",
        "size"        => "lg",
        "label"       => "Shorten All Fleets",
        "link"        => "timer_sc.php?eventid=$eventid&pagestate=shorten&fleet=all",
        "glyph"       => "glyphicon-flag",
        "g-pos"       => "left",
        "popover"     => "click here to shorten ALL fleets at the end of the next lap",
        "pop-pos"     => "left",
        "block"       => "btn-block",
        "disabled"    => "",
        "top-margin"  => "margin-top-10"
    ),
);

// -------- quicktime ----------------------------------------------------------------
//$btn_quicktime = array(
//    "label"     => true,
//    "glyph"     => true,
//    "g-pos"     => "left",
//    "popover"   => "true",
//    "fields"    => array(
//        "id"          => "quicktime",
//        "style"       => "warning",
//        "size"        => "lg",
//        "label"       => "Quick Find",
//        "link"        => "",
//        "glyph"       => "glyphicon-search",
//        "g-pos"       => "left",
//        "popover"     => "find boat quickly",
//        "pop-pos"     => "left",
//        "block"       => "btn-block",
//        "disabled"    => "",
//        "top-margin"  => "margin-top-10"
//    ),
//);
//
//$mdl_quicktime = array(
//    "form"    => false,
//    "footer"  => false,
//    "reload"  => true,
//    "close"   => false,
//    "reset"   => false,
//    "submit"  => false,
//    "fields"  => array(
//        "id"          => "quicktime",
//        "size"        => "lg",
//        "style"       => "warning",
//        "title"       => "Quick Find",
//        "body"        => "<iframe src=\"timer_quicktime_pg.php?eventid=$eventid&pagestate=init\" frameborder=\"0\" style=\"width: 100%; height: 400px;\" id=\"entryframe\"></iframe>",
//        "form"        => "false",
//        "script"      => "",
//        "onsubmit"    => "",
//        "action"      => "",
//        "target"      => "",
//        "close-lbl"   => "",
//        "reset-lbl"   => "",
//        "submit-lbl"  => "",
//        "reload"      => true,
//        "footer"      => ""
//    ),
//);

// -------- bunch ----------------------------------------------------------------
//$btn_bunch = array(
//    "label"     => true,
//    "glyph"     => true,
//    "g-pos"     => "left",
//    "popover"   => "true",
//    "fields"    => array(
//        "id"          => "bunch",
//        "style"       => "warning",
//        "size"        => "lg",
//        "label"       => "Time Bunch",
//        "link"        => "",
//        "glyph"       => "glyphicon-option-horizontal",
//        "g-pos"       => "left",
//        "popover"     => "allows you to preset a group of boats ready for timing in a group",
//        "pop-pos"     => "left",
//        "block"       => "btn-block",
//        "disabled"    => "",
//        "top-margin"  => "margin-top-10"
//    ),
//);

//$mdl_bunch = array(
//    "form"    => false,
//    "footer"  => false,
//    "reload"  => true,
//    "close"   => false,
//    "reset"   => false,
//    "submit"  => false,
//    "fields"  => array(
//        "id"          => "bunch",
//        "size"        => "lg",
//        "style"       => "warning",
//        "title"       => "Preset Approaching Boats",
//        "body"        => "<iframe src=\"timer_bunch_pg.php?eventid=$eventid&pagestate=init\" frameborder=\"0\" style=\"width: 100%; height: 400px;\" id=\"entryframe\"></iframe>",
//        "form"        => "false",
//        "script"      => "",
//        "onsubmit"    => "",
//        "action"      => "",
//        "target"      => "",
//        "close-lbl"   => "",
//        "reset-lbl"   => "",
//        "submit-lbl"  => "",
//        "reload"      => true,
//        "footer"      => ""
//    ),
//);

// -------- undo ----------------------------------------------------------------
$btn_undo = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => "true",
    "fields"    => array(
        "id"             => "undo",
        "style"          => "danger",
        "size"           => "lg",
        "label"          => "Undo Last Timing",
        "link"           => "",
        "glyph"          => "glyphicon-trash",
        "g-pos"          => "left",
        "popover"        => "click here to undo last lap/finish timing",
        "pop-pos"        => "left",
        "block"          => "btn-block",
        "disabled"       => "",
        "top-margin"     => "margin-top-10"
    ),
);


// -------- timersettings ----------------------------------------------------------------
$btn_timersettings = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => "true",
    "fields"    => array(
        "id"          => "timersettings",
        "style"       => "default",
        "size"        => "lg",
        "label"       => "Settings",
        "link"        => "",
        "glyph"       => "",
        "g-pos"       => "",
        "popover"     => "change the display settings for this page",
        "pop-pos"     => "left",
        "block"       => "btn-block",
        "disabled"    => "",
        "top-margin"  => "margin-top-10"
    ),
);

$mdl_timersettings = array(
    "form"    => true,
    "footer"  => false,
    "reload"  => true,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"          => "timersettings",
        "size"        => "lg",
        "style"       => "primary",
        "title"       => "Change Timer Page Settings",
        "body"        => "",
        "form"        => "true",
        "script"      => "",
        "onsubmit"    => "",
        "action"      => "timer_sc.php?eventid=$eventid&pagestate=timersettings",
        "target"      => "",
        "close-lbl"   => "nope",
        "reset-lbl"   => "",
        "submit-lbl"  => "change settings",
        "reload"      => true,
        "footer"      => ""
    ),
);


// -------- shorten fleet ----------------------------------------------------------------
$btn_shortenfleet = array(
    "id"          => "shortenfleet",
    "style"       => "info",
    "size"        => "sm",
    "link"        => "",
    "label"       => " LAPS - click to shorten course",
    "glyph"       => "glyphicon-flag",
    "g-pos"       => "left",
    "popover"     => "click here to shorten this fleet at the end of the next lap",
    "pop-pos"     => "top",
    "block"       => "",
    "disabled"    => "",
    "top-margin"  => "margin-top-0"
);


//// -------- results check ----------------------------------------------------------------
//$btn_resultcheck = array(
//    "label"     => true,
//    "glyph"     => true,
//    "g-pos"     => "left",
//    "popover"   => "true",
//    "fields"    => array(
//        "id"          => "resultcheck",
//        "style"       => "info",
//        "size"        => "md",
//        "label"       => "Check Results",
//        "link"        => "",
//        "glyph"       => "",
//        "g-pos"       => "",
//        "popover"     => "check the results for this fleet",
//        "pop-pos"     => "top",
//        "block"       => "",
//        "disabled"    => "",
//        "top-margin"  => "margin-top-0"
//    ),
//);

//$mdl_resultcheck = array(
//    "form"    => false,
//    "footer"  => false,
//    "reload"  => true,
//    "close"   => false,
//    "reset"   => false,
//    "submit"  => false,
//    "fields"  => array(
//        "id"          => "resultcheck",
//        "size"        => "lg",
//        "style"       => "primary",
//        "title"       => "Check Results",
//        "body"        => "<iframe id=\"resultcheckFrame\" src=\"timer_resultcheck_pg.php?eventid=$eventid&pagestate=init&fleet=0\" frameborder=\"0\" style=\"width: 100%; height: 400px;\" id=\"entryframe\"></iframe>",
//        "form"        => "false",
//        "script"      => "  $( '#resultcheckModalLabel' ).text( 'Current results for ' + button.data('fleet') + ' fleet')
//                            $( '#resultcheckFrame' ).attr('src', button.data('iframe'))
//                         ",
//        "onsubmit"    => "",
//        "action"      => "",
//        "target"      => "",
//        "close-lbl"   => "",
//        "reset-lbl"   => "",
//        "submit-lbl"  => "",
//        "reload"      => true,
//        "footer"      => ""
//    ),
//);


// -------- laptime ----------------------------------------------------------------
$btn_laptime = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => "true",
    "fields"    => array(
        "id"          => "laptime",
        "style"       => "progress-bar-warning",
        "size"        => "",
        "label"       => "Time Lap",
        "glyph"       => "glyphicon-time",
        "g-pos"       => "left",
        "popover"     => "time lap for this boat",
        "pop-pos"     => "top",
        "block"       => "",
        "disabled"    => "",
        "top-margin"  => "",
        "data"        => "",
        "link"        => "",
        "target"      => ""
    ),
);


// -------- finish ----------------------------------------------------------------
$btn_finish = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => "true",
    "fields"    => array(
        "id"          => "finish",
        "style"       => "progress-bar-warning",
        "size"        => "",
        "label"       => "Finish",
        "glyph"       => "glyphicon-flag",
        "g-pos"       => "left",
        "popover"     => "finish boat - ignoring lap",
        "pop-pos"     => "top",
        "block"       => "",
        "disabled"    => "",
        "top-margin"  => "",
        "data"        => "",
        "link"        => "",
        "target"      => ""
    ),
);


// -------- editlap ----------------------------------------------------------------
$btn_editlap = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => "true",
    "fields"    => array(
        "id"          => "editlap",
        "style"       => "progress-bar-default",
        "size"        => "",
        "label"       => "",
        "glyph"       => "glyphicon-pencil",
        "g-pos"       => "left",
        "popover"     => "edit lap times for this boat",
        "pop-pos"     => "top",
        "block"       => "",
        "disabled"    => "",
        "top-margin"  => ""
    ),
);


// note: form is set true for mdl_editlap so that I can get a close button in the header.
// The iframe contains the actual form
$mdl_editlap = array(
    "form"    => true,
    "footer"  => false,
    "reload"  => true,
    "close"   => true,
    "reset"   => false,
    "submit"  => false,
    "fields"  => array(
        "id"          => "editlap",
        "size"        => "lg",
        "style"       => "primary",
        "title"       => "Edit Lap Times",
        "body"        => "<iframe id=\"editlapFrame\" src=\"\" frameborder=\"0\" style=\"width: 100%; height: 400px;\"></iframe>",
        "form"        => true,
        "script"      => "  $( '#editlapModalLabel' ).text( 'Edit lap times for ' + button.data('boat'))
                            $( '#editlapFrame' ).attr('src', button.data('iframe'))
                         ",
        "onsubmit"    => "",
        "action"      => "",
        "target"      => "",
        "close-lbl"   => "Close",
        "reset-lbl"   => "",
        "submit-lbl"  => "",
        "reload"      => true,
        "footer"      => ""
    ),
);



