<?php
/* modal configuration for race page */

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

// ----- ADD RACE --------------------------------------------------
$btn_addrace = array(
    "label"   => true,
    "glyph"   => true,
    "g-pos"   => "left",
    "popover" => true,
    "fields"  => array(
        "id"          => "addrace",
        "style"       => "primary",
        "size"        => "lg",
        "label"       => " Add Race Today",
        "glyph"       => "glyphicon-plus-sign",
        "popover"     => "click to add a new race for today",
        "pop-pos"     => "left",
        "disabled"    => "",
        "block"       => "btn-block",
        "top-margin"  => "margin-top-10",
    ),
);

$mdl_addrace = array(
    "form"    => true,
    "footer"  => true,
    "reload"  => true,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"          => "addrace",
        "size"        => "lg",
        "style"       => "primary",
        "title"       => "Add Race Today",
        "body"        => "",
        "script"      => "",
        "action"      => "pickrace_sc.php?pagestate=addrace",
        "target"      => "",
        "onsubmit"    => "",
        "close-lbl"   => "nope",
        "reset-lbl"   => "",
        "submit-lbl"  => "add race",
        "footer"      => ""
    ),
);

// ------ VIEW RACE FORMAT
$mdl_format = array(
    "form"    => false,
    "footer"  => false,
    "reload"  => false,
    "close"   => false,
    "reset"   => false,
    "submit"  => false,
    "fields" => array(
        "id"          => "format",
        "size"        => "lg",
        "style"       => "primary",
        "title"       => "",
        "body"        => "",
        "action"      => "",
        "target"      => "",
        "close-lbl"   => "",
        "reset-lbl"   => "",
        "submit-lbl"  => "",
        "onsubmit"    => "",
        "footer"      => "",
        "script"      => "",
        ),
);

