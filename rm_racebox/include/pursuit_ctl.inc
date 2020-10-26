<?php

/**
 *  pursuit_ctl
 * 
 *  Include file for configuring timer page buttons and modals
 * 
 *  Buttons:
 *    xxx       - xxxx
 * 
 *  Inline Buttons
 *    xxx       - xxxxx

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


// -------- xxxx ----------------------------------------------------------------
$btn_xxxx = array(
    "id"          => "xxx",
    "style"       => "primary",
    "size"        => "lg",
    "label"       => "xxxx",
    "link"        => "pursuit_sc.php?eventid=$eventid&pagestate=xxx",
    "glyph"       => "glyphicon-flag",
    "g-pos"       => "left",
    "popover"     => "xxxx",
    "pop-pos"     => "left",
    "block"       => "btn-block",
    "disabled"    => "",
    "top-margin"  => "margin-top-10"
);

$mdl_xxxx = array(
    "id"          => "xxx",
    "size"        => "lg",
    "style"       => "primary",
    "title"       => "xxx",
    "body"        => "xxx",
    "form"        => "false",
    "script"      => "",
    "onsubmit"    => "",
    "action"      => "",
    "target"      => "",
    "close-lbl"   => "",
    "reset-lbl"   => "",
    "submit-lbl"  => "",
    "reload"      => true,
    "footer"      => ""
);


?>
