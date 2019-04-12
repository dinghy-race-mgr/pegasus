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
