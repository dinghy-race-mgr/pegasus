<?php
/* button configuration for start page */

// ---- timer start button ----------------------------------------------------------------
$btn_timerstart = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => "true",
    "fields"    => array(
        "id"         => "timerstart",
        "size"       => "lg",
        "style"      => "success",
        "label"      => "Start Timer <br><span style='text-align: center'></span><small>click on 1st signal</small></span>",
        "glyph"      => "glyphicon-time",
        "g-pos"      => "left",
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
    "popover"   => "true",
    "fields"    => array(
        "id"         => "timerstop",
        "size"       => "lg",
        "style"      => "danger",
        "label"      => "Stop Timer<br>&nbsp;",
        "glyph"      => "glyphicon-time",
        "g-pos"      => "left",
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
        "body"        => "<b>CAREFUL</b> - If you reset the timer you will lose any lap timings you have made.<br><br>
                          This should only be necessary if you have abandoned the start and are starting the entire sequence again
                          - if you started the timer at the wrong time  - use the \"forgot to start timer\" to reset the correct start time,
                          and if you have a general recall <i>(or just want to move the fleet start sequence)</i>, use the general recall
                          button to adjust the start time for each fleet  <br><br>
                          Please confirm that you really want to stop the Master Timer",
        "form"        => true,
        "script"      => "",
        "action"      => "start_sc.php?eventid=$eventid&pagestate=stoptimer",
        "target"      => "",
        "onsubmit"    => "return confirm('I am going to stop the Master Timer\\n are you really, REALLY SURE?');",
        "close-lbl"   => "nope",
        "reset-lbl"   => "",
        "submit-lbl"  => "STOP race timer",
        "reload"      => true,
        "footer"      => ""
    ),
);

// ---- start infringement button ----------------------------------------------------------------
$btn_infringestart = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => "true",
    "fields"    => array(
        "id"         => "infringestart",
        "style"      => "default",
        "size"       => "md",
        "label"      => "Start Infringements",
        "glyph"      => "glyphicon-flag",
        "g-pos"      => "right",
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
        "style"       => "primary",
        "title"       => "Start Infringements",
        "body"        => "",
        "form"        => false,
        "script"      => "",
        "action"      => "",
        "target"      => "",
        "onsubmit"    => "",
        "close-lbl"   => "",
        "reset-lbl"   => "",
        "submit-lbl"  => "",
        "reload"      => true,
        "footer"      => ""
    ),
);


// ---- general recall button ----------------------------------------------------------------

$btn_generalrecall = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => "true",
    "fields"    => array(
        "id"         => "generalrecall",
        "size"       => "md",
        "style"      => "default",
        "label"      => "General Recall",
        "glyph"      => "glyphicon-time",
        "g-pos"      => "right",
        "popover"    => "click here to reset timer for a general recall of THIS fleet",
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
        "style"       => "primary",
        "title"       => "General Recall",
        "body"        => "",
        "form"        => true,
        "script"      => "
                         $( '#generalrecallModalLabel' ).text( 'General Recall - Start ' + button.data('start'))
                         $( '#start' ).val(button.data('start'))
                         $( '#origstart' ).text(button.data('starttime'))
                         ",
        "action"      => "start_sc.php?eventid=$eventid&pagestate=generalrecall",
        "target"      => "",
        "onsubmit"    => "",
        "close-lbl"   => "nope",
        "reset-lbl"   => "",
        "submit-lbl"  => "Set Time",
        "reload"      => true,
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
        "form"        => true,
        "script"      => "",
        "action"      => "start_sc.php?eventid=$eventid&pagestate=adjusttimer",
        "target"      => "",
        "onsubmit"    => "",
        "close-lbl"   => "nope",
        "reset-lbl"   => "",
        "submit-lbl"  => "Set Time",
        "reload"      => true,
        "footer"      => ""
    )
);




