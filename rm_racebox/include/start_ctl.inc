<?php
/* button configuration for start page
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

// ---- timer start button ----------------------------------------------------------------
$btn_timerstart = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"         => "timerstart",
        "size"       => "lg",
        "style"      => "success",
        "label"      => "Start Timer <br><span style='text-align: center'></span><small>click on 1st signal</small></span>",
        "glyph"      => "glyphicon-time",
        "popover"    => "Click at the same time as the FIRST signal in your start sequence",
        "pop-pos"    => "bottom",
        "block"      => "btn-block",
        "disabled"   => "",
        "link"       => "start_sc.php?eventid=$eventid&pagestate=starttimer",
        "top-margin" => "margin-top-0"
    ),
);


// ---- timer stop button ----------------------------------------------------------------
$btn_timerstop = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"         => "timerstop",
        "size"       => "lg",
        "style"      => "danger",
        "label"      => "Stop Timer<br>&nbsp;",
        "glyph"      => "glyphicon-time",
        "popover"    => "Click here to stop the master timer and start again",
        "pop-pos"    => "left",
        "block"      => "btn-block",
        "disabled"   => "",
        "top-margin" => "margin-top-0"
    ),
);

$mdl_timerstop = array(
    "form"    => true,
    "footer"  => true,
    "reload"  => true,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"          => "timerstop",
        "size"        => "md",
        "style"       => "danger",
        "title"       => "Reset Timer - are you sure?",
        "body"        => "",
        "script"      => "",
        "action"      => "start_sc.php?eventid=$eventid&pagestate=stoptimer",
        "target"      => "",
        "onsubmit"    => "",
        "close-lbl"   => "changed my mind",
        "reset-lbl"   => "",
        "submit-lbl"  => "STOP race timer",
        "footer"      => ""
    ),
);

// ---- start infringement button ----------------------------------------------------------------
$btn_infringestart = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "right",
    "popover"   => true,
    "fields"    => array(
        "id"         => "infringestart",
        "style"      => "default",
        "size"       => "lg",
        "label"      => "Start Infringements",
        "glyph"      => "glyphicon-flag",
        "popover"    => "Click here to set OCS, BFD etc. for THIS fleet",
        "pop-pos"    => "bottom",
        "block"      => "btn-block",
        "disabled"   => "",
        "top-margin" => "margin-top-0"
    ),
);

$mdl_infringestart = array(
    "form"    => false,
    "footer"  => true,
    "reload"  => true,
    "close"   => true,
    "reset"   => true,
    "submit"  => true,
    "fields"  => array(
        "id"          => "infringestart",
        "size"        => "lg",
        "style"       => "success",
        "title"       => "Start Infringements",
        "body"        => "",
        "script"      => "",
        "action"      => "",
        "target"      => "",
        "onsubmit"    => "",
        "close-lbl"   => "",
        "reset-lbl"   => "",
        "submit-lbl"  => "",
        "footer"      => ""
    ),
);


// ---- general recall button ----------------------------------------------------------------

$btn_generalrecall = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "right",
    "popover"   => true,
    "fields"    => array(
        "id"         => "generalrecall",
        "size"       => "lg",
        "style"      => "default",
        "label"      => "General Recall",
        "glyph"      => "glyphicon-time",
        "popover"    => "click here to set timer for a general recall of THIS fleet",
        "pop-pos"    => "bottom",
        "block"      => "btn-block",
        "disabled"   => "",
        "top-margin" => "margin-top-0"
    ),
);

$mdl_generalrecall = array(
    "form"    => true,
    "footer"  => true,
    "reload"  => true,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"          => "generalrecall",
        "size"        => "md",
        "style"       => "success",
        "title"       => "General Recall",
        "body"        => "",
        "script"      => "",
        "action"      => "start_sc.php?eventid=$eventid&pagestate=generalrecall",
        "target"      => "",
        "onsubmit"    => "",
        "close-lbl"   => "changed my mind",
        "reset-lbl"   => "",
        "submit-lbl"  => "Set Time",
        "footer"      => ""
    ),
);

$mdl_latetimer = array(
    "form"    => true,
    "footer"  => true,
    "reload"  => true,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"          => "latetimer",
        "size"        => "md",
        "style"       => "warning",
        "title"       => "Adjust Timer",
        "body"        => "",
        "script"      => "",
        "action"      => "start_sc.php?eventid=$eventid&pagestate=adjusttimer",
        "target"      => "",
        "onsubmit"    => "",
        "close-lbl"   => "changed my mind",
        "reset-lbl"   => "",
        "submit-lbl"  => "Set Time",
        "footer"      => ""
    )
);




