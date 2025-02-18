<?php

/**
 *  timer_ctl
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

// -----------------------------------------------------------------------------------


// -------- shorten all----------------------------------------------------------------
$btn_shorten = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"          => "shorten",
        "style"       => "info",
        "size"        => "lg",
        "label"       => "Shorten ALL Fleets",
        "link"        => "",
        "glyph"       => "glyphicon-flag",
        "popover"     => "click here to shorten ALL fleets at the end of their next lap",
        "pop-pos"     => "left",
        "block"       => "btn-block",
        "disabled"    => "",
        "top-margin"  => "margin-top-10"
    ),
);

$mdl_shorten = array(
    "form"    => true,
    "footer"  => true,
    "reload"  => false,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"          => "shorten",
        "size"        => "lg",
        "style"       => "info",
        "title"       => "Shorten All Fleets",
        "body"        => "",
        "script"      => "",
        "onsubmit"    => "",
        "action"      => "timer_sc.php?eventid=$eventid&pagestate=shortenall",
        "target"      => "",
        "close-lbl"   => " don't shorten!",
        "reset-lbl"   => "",
        "submit-lbl"  => " Confirm Changes",
        "footer"      => ""
    ),
);


// -------- undo ----------------------------------------------------------------
$btn_undo = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"             => "undo",
        "style"          => "warning",
        "size"           => "lg",
        "label"          => "Undo Last Timing",
        "link"           => "",
        "glyph"          => "glyphicon-step-backward",
        "popover"        => "click here to undo last lap/finish timing",
        "pop-pos"        => "left",
        "block"          => "btn-block",
        "disabled"       => "",
        "top-margin"     => "margin-top-10"
    ),
);


// -------- laptime ----------------------------------------------------------------
$btn_laptime = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"          => "laptime",
        "style"       => "progress-bar-warning",
        "size"        => "",
        "label"       => "Time Lap",
        "glyph"       => "glyphicon-time",
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
    "popover"   => true,
    "fields"    => array(
        "id"          => "finish",
        "style"       => "progress-bar-warning",
        "size"        => "",
        "label"       => "Finish",
        "glyph"       => "glyphicon-flag",
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
    "popover"   => true,
    "fields"    => array(
        "id"          => "editlap",
        "style"       => "success",
        "size"        => "",
        "label"       => "",
        "glyph"       => "glyphicon-pencil",
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
        "style"       => "success",
        "title"       => "Edit Lap Times",
        "body"        => "<iframe id=\"editlapFrame\" src=\"\" frameborder=\"0\" style=\"width: 100%; height: 730px;\"></iframe>",
        "script"      => "  $( '#editlapModalLabel' ).text( 'Edit lap times for ' + button.data('boat'))
                            $( '#editlapFrame' ).attr('src', button.data('iframe'))
                         ",
        "onsubmit"    => "",
        "action"      => "",
        "target"      => "",
        "close-lbl"   => "exit laps editing",
        "reset-lbl"   => "",
        "submit-lbl"  => "",
        "footer"      => ""
    ),
);


// ---- shorten fleet button -------------------------------------------- WIP ------------

$btn_shortenfleet = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"        => "shortenfleet",    // fleet number is appended when used
        "style"     => "warning",
        "size"      => "md",
        "label"     => "",
        "glyph"     => "glyphicon-flag",
        "popover"   => "click here to shorten this fleet at the end of the next lap",
        "pop-pos"   => "top",
        "block"     => "",
        "disabled"  => "",
        "top-margin"=> ""
    ),
);

$mdl_shortenfleet = array(
    "form"    => true,
    "footer"  => true,
    "reload"  => true,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"          => "shortenfleet",   // fleet number is appended when used
        "style"       => "warning",
        "size"        => "md",
        "title"       => "",
        "body"        => "",
        "script"      => "",
        "action"      => "",
        "target"      => "",
        "onsubmit"    => "",
        "close-lbl"   => "changed my mind",
        "reset-lbl"   => "",
        "submit-lbl"  => "Shorten this Fleet",
        "footer"      => ""
    ),
);

// ---- change shorten lap ----------------------------------------------------------------
$btn_undoshorten = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"         => "undoshorten",
        "size"       => "lg",
        "style"      => "info",
        "label"      => "Change Shorten Lap",
        "glyph"      => "glyphicon-pencil",
        "popover"    => "VERY useful if you forgot to shorten course",
        "pop-pos"    => "left",
        "block"      => "btn-block",
        "disabled"   => "",
        "top-margin" => "margin-top-10"
    ),
);

$mdl_undoshorten = array(
    "form"    => true,
    "footer"  => false,
    "reload"  => true,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"         => "undoshorten",
        "size"       => "lg",
        "style"      => "info",
        "title"      => "Change Shorten Lap",
        "body"       => "",
        "script"     => "",
        "onsubmit"   => "",
        "action"     => "timer_sc.php?eventid=$eventid&pagestate=undoshorten",
        "target"     => "",
        "close-lbl"  => "changed my mind",
        "reset-lbl"  => "",
        "submit-lbl" => "Change Shorten Lap(s)",
        "footer"     => ""
    ),
);

/*
// ---- change finish lap ----------------------------------------------------------------
$btn_forgotshorten = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"         => "forgotshorten",
        "size"       => "lg",
        "style"      => "success",
        "label"      => "Forgot to Shorten",
        "glyph"      => "glyphicon-pencil",
        "popover"    => "VERY useful if you forgot to shorten course",
        "pop-pos"    => "left",
        "block"      => "btn-block",
        "disabled"   => "",
        "top-margin" => "margin-top-10"
    ),
);

$mdl_forgotshorten = array(
    "form"    => true,
    "footer"  => false,
    "reload"  => true,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"         => "forgotshorten",
        "size"       => "lg",
        "style"      => "success",
        "title"      => "Forgot to Shorten",
        "body"       => "",
        "script"     => "",
        "onsubmit"   => "",
        "action"     => "timer_sc.php?eventid=$eventid&pagestate=forgotshorten",
        "target"     => "",
        "close-lbl"  => "changed my mind",
        "reset-lbl"  => "",
        "submit-lbl" => "Forgot to Shorten",
        "footer"     => ""
    ),
);
*/



