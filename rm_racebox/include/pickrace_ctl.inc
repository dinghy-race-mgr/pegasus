<?php
/* modal configuration for race page */

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

// ----- ADD RACE --------------------------------------------------
//$btn_addrace = array(
//    "id"          => "addrace",
//    "style"       => "primary",
//    "size"        => "md",
//    "label"       => "Add Race Today",
//    "glyph"       => "glyphicon-plus-sign",
//    "g-pos"       => "left",
//    "popover"     => "click to add a new race for today",
//    "pop-pos"     => "left",
//    "disabled"    => "",
//    "block"       => "btn-block",
//    "top-margin"  => "margin-top-10",
//);

$btn_addrace = array(
    "label"   => true,
    "glyph"   => true,
    "g-pos"   => "left",
    "popover" => "true",
    "fields"  => array(
        "id"          => "addrace",
        "style"       => "primary",
        "size"        => "lg",
        "label"       => " Add Race Today",
        "glyph"       => "glyphicon-plus-sign",
        "g-pos"       => "left",
        "popover"     => "click to add a new race for today",
        "pop-pos"     => "left",
        "disabled"    => "",
        "block"       => "btn-block",
        "top-margin"  => "margin-top-10",
    ),
);

//$mdl_addrace = array(
//    "id"          => "addrace",
//    "size"        => "lg",
//    "style"       => "primary",
//    "title"       => "Add Race Today",
//    "body"        => "",
//    "form"        => true,
//    "script"      => "",
//    "action"      => "pickrace_sc.php?pagestate=addrace",
//    "target"      => "",
//    "onsubmit"    => "",
//    "close-lbl"   => "nope",
//    "reset-lbl"   => "",
//    "submit-lbl"  => "add race",
//    "reload"      => true,
//    "footer"      => "",
//);

$mdl_addrace = array(
    "form"    => true,
    "footer"  => true,
    "reload"  => false,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"          => "addrace",
        "size"        => "lg",
        "style"       => "primary",
        "title"       => "Add Race Today",
        "body"        => "",
        "form"        => true,
        "script"      => "",
        "action"      => "pickrace_sc.php?pagestate=addrace",
        "target"      => "",
        "onsubmit"    => "",
        "close-lbl"   => "nope",
        "reset-lbl"   => "",
        "submit-lbl"  => "add race",
        "reload"      => true,
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
        "form"        => false,
        "action"      => "",
        "target"      => "",
        "close-lbl"   => "",
        "reset-lbl"   => "",
        "submit-lbl"  => "",
        "onsubmit"    => "",
        "footer"      => "",
        "script"      => "",
        "reload"      => false,
        ),
);

