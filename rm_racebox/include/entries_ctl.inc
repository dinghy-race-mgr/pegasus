<?php

/**
 *  entries_ctl.inc
 * 
 *  Include file for configuring entries page buttons and modals
 *
 *  PAGE Buttons:
 *    loadentry     -  loads entries from t_entry
 *    loadregular   -  loads all regular entries from t_competitor
 *    loadprevious  -  loads entries that have entered previous races today
 *    addentry      -  OOD can pick boats to enter in modal form
 *    addcompetitor -  button to modal form for OOD to add a new competitor to t_competitor
 *    addclass      -  button to modal form for OOD to add a new class to t_class
 *    printentries  -  multilink button to access various entries reports
 *
 *  INLINE buttons
 *    change        -  inline button to get modal form to change entry details
 *    duty          -  inline button to give an entry duty points
 *    unduty        -  inline button to remove entry points from a competitor
 *    remove        -  inline button to remove entries
 **/

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

// -------- loadentry ---------------------------------------------------------------- NOT DONE
$btn_loadentry = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"          => "loadentry",
        "style"       => "primary",
        "size"        => "lg",
        "label"       => "Load Entries ",
        "link"        => "entries_sc.php?eventid=$eventid&pagestate=loadentries",
        "glyph"       => "glyphicon-cloud-download",
        "popover"     => "click here to load entries from the raceManager sign on facility",
        "pop-pos"     => "left",
        "block"       => "btn-block",
        "disabled"    => "",
        "top-margin"  => "margin-top-10"
    ),
);

// -------- loadregular ----------------------------------------------------------------  NOT DONE
$btn_loadregular = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"          => "loadregular",
        "style"       => "primary",
        "size"        => "lg",
        "label"       => "Load Regulars",
        "link"        => "entries_sc.php?eventid=$eventid&pagestate=loadregular",
        "glyph"       => "glyphicon-cloud-download",
        "popover"     => "click here to load competitors marked as regular racers in the database",
        "pop-pos"     => "left",
        "block"       => "btn-block",
        "disabled"    => "",
        "top-margin"  => "margin-top-10"
    ),
);

// -------- loadprevious ----------------------------------------------------------------
$btn_loadprevious = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"          => "loadprevious",
        "style"       => "primary",
        "size"        => "lg",
        "label"       => "Load Previous",
        "glyph"       => "glyphicon-cloud-download",
        "link"        => "entries_sc.php?eventid=$eventid&pagestate=loadprevious",
        "popover"     => "click here to load competitors who have entered other races today",
        "pop-pos"     => "left",
        "block"       => "btn-block",
        "disabled"    => "",
        "top-margin"  => "margin-top-10"
    ),
);

// -------- addentry ----------------------------------------------------------------
$btn_addentry = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"          => "addentry",
        "style"       => "info",
        "size"        => "lg",
        "label"       => "Enter Boat(s)",
        "glyph"       => "glyphicon-list-alt",
        "popover"     => "click here to add individual boats to the race",
        "pop-pos"     => "left",
        "block"       => "btn-block",
        "disabled"    => "",
        "top-margin"  => "margin-top-10"
    ),
);

$mdl_addentry = array(
    "form"    => false,
    "footer"  => false,
    "reload"  => true,
    "close"   => false,
    "reset"   => false,
    "submit"  => false,
    "fields"  => array(
        "id"          => "addentry",
        "size"        => "lg",
        "style"       => "info",
        "title"       => "Pick Boats to Enter",
        "body"        => "<iframe src='entries_add_pg.php?eventid=$eventid&pagestate=init'
                           frameborder='0' style='width: 100%; height: 600px;' id='entryframe'></iframe>",
        "script"      => "",
        "onsubmit"    => "",
        "action"      => "",
        "target"      => "",
        "close-lbl"   => "",
        "reset-lbl"   => "",
        "submit-lbl"  => "",
        "footer"      => ""
    ),
);

$btn_enterone = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "right",
    "popover"   => true,
    "fields"    => array(
        "id"             => "enterone",
        "style"          => "progress-bar-info",
        "size"           => "lg",
        "label"          => "enter",
        "glyph"          => "glyphicon-triangle-right",
        "popover"        => "",
        "pop-pos"        => "",
        "block"          => "",
        "disabled"       => "",
        "link"           => "entries_add_sc.php?eventid=$eventid&pagestate=enterone",
        "target"         => "_self"
    ),
);

//$btn_enterall = array(
//    "id"             => "enterall",
//    "style"          => "progress-bar-success",
//    "size"           => "lg",
//    "label"          => "all today",
//    "glyph"          => "glyphicon-plus",
//    "popover"        => "",
//    "pop-pos"        => "",
//    "block"          => "",
//    "disabled"       => "",
//    "link"           => "entries_add_sc.php?eventid=$eventid&pagestate=enterall",
//    "target"         => "_self",
//);

// -------- addcompetitor ----------------------------------------------------------------
$btn_addcompetitor = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"          => "addcompetitor",
        "style"       => "success",
        "size"        => "lg",
        "label"       => "Register New Boat",
        "glyph"       => "glyphicon-plus",
        "popover"     => "click here to add a new competitor to racemanager database",
        "pop-pos"     => "left",
        "block"       => "btn-block",
        "disabled"    => "",
        "top-margin"  => "margin-top-10"
    ),
);

$mdl_addcompetitor = array(
    "form"    => true,
    "footer"  => true,
    "reload"  => false,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"          => "addcompetitor",
        "size"        => "lg",
        "style"       => "success",
        "title"       => "Register New Boat",
        "body"        => "",
        "script"      => "",
        "action"      => "entries_sc.php?eventid=$eventid&pagestate=addcompetitor",
        "target"      => "",
        "onsubmit"    => "",
        "close-lbl"   => "changed my mind",
        "reset-lbl"   => "",
        "submit-lbl"  => "add boat",
        "footer"      => ""
    ),
);

// -------- addclass ----------------------------------------------------------------
$btn_addclass = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"          => "addclass",
        "style"       => "success",
        "size"        => "lg",
        "label"       => "Register New Class",
        "glyph"       => "glyphicon-plus",
        "popover"     => "click here to add a new type of boat to the system",
        "pop-pos"     => "left",
        "block"       => "btn-block",
        "disabled"    => "",
        "top-margin"  => "margin-top-10"
    ),
);

$mdl_addclass = array(
    "form"    => true,
    "footer"  => true,
    "reload"  => false,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"          => "addclass",
        "size"        => "lg",
        "style"       => "success",
        "title"       => "Register New Class",
        "body"        => "",
        "script"      => "",
        "action"      => "entries_sc.php?eventid=$eventid&pagestate=addclass",
        "target"      => "",
        "onsubmit"    => "",
        "close-lbl"   => "changed my mind",
        "reset-lbl"   => "",
        "submit-lbl"  => "add class",
        "footer"      => ""
    ),
);

// -------- printentries ----------------------------------------------------------------
$btn_printentries = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"          => "printentriesbtn",
        "style"       => "info",
        "size"        => "lg",
        "label"       => "Print Entries",
        "glyph"       => "glyphicon-print",
        "popover"     => "pick the type of output you want",
        "pop-pos"     => "left",
        "block"       => "btn-block",
        "disabled"    => "",
        "top-margin"  => "margin-top-10",
        "target"      => "_blank"
    ),
);

// -------- change ----------------------------------------------------------------

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
        "style"       => "primary",
        "title"       => "Change Entry Details",
        "body"        => "",
        "script"      => "
                         $( '#changeModalLabel' ).text( 'Change entry for ' + button.data('entryname'))
                         $( '#identryid' ).val(button.data('entryid'))
                         $( '#idhelm' ).val(button.data('helm'))
                         $( '#idcrew' ).val(button.data('crew'))
                         $( '#idsailnum' ).val(button.data('sailnum'))
                         $( '#idpn' ).val(button.data('pn'))
                         ",
        "onsubmit"    => "",
        "action"      => "entries_sc.php?eventid=$eventid&pagestate=change",
        "target"      => "",
        "close-lbl"   => "changed my mind",
        "reset-lbl"   => "reset",
        "submit-lbl"  => "update entry",
        "footer"      => ""
    ),
);

// -------- duty ----------------------------------------------------------------

$mdl_duty = array(
    "form"    => true,
    "footer"  => true,
    "reload"  => true,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"          => "duty",
        "size"        => "md",
        "style"       => "primary",
        "title"       => "Give Duty Points",
        "body"        => "<h4><span class=\"dynmsg\"></span></h4>
                         <input name=\"entryid\" type=\"hidden\" class=\"form-control\" id=\"entryid\">",
        "script"      => "var entryid = button.data('entryid')     // extract info from data-* attributes
                         var entryname = button.data('entryname')
                         var modal = $(this)
                         modal.find('.dynmsg').text('Give duty points for this race to ' + entryname + ' ?')
                         modal.find('.modal-body input').val(entryid)",
        "onsubmit"    => "",
        "action"      => "entries_sc.php?eventid=$eventid&pagestate=dutypoints",
        "target"      => "",
        "close-lbl"   => "changed my mind",
        "reset-lbl"   => "reset",
        "submit-lbl"  => "dutypoints",
        "footer"      => ""
    ),
);

// -------- unduty ----------------------------------------------------------------

$mdl_unduty = array(
    "form"    => true,
    "footer"  => true,
    "reload"  => true,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"          => "unduty",
        "size"        => "md",
        "style"       => "primary",
        "title"       => "Remove Duty Points",
        "body"        => "<h4><span class=\"dynmsg\"></span></h4>
                         <input name=\"entryid\" type=\"hidden\" class=\"form-control\" id=\"entryid\">",
        "script"      => "var entryid = button.data('entryid')     // extract info from data-* attributes
                         var entryname = button.data('entryname')
                         var modal = $(this)
                         modal.find('.dynmsg').text('Remove duty points for this race for ' + entryname + ' ?')
                         modal.find('.modal-body input').val(entryid)",
        "onsubmit"    => "",
        "action"      => "entries_sc.php?eventid=$eventid&pagestate=unduty",
        "target"      => "",
        "close-lbl"   => "changed my mind",
        "reset-lbl"   => "reset",
        "submit-lbl"  => "remove duty points",
        "footer"      => ""
    ),
);

// -------- remove ----------------------------------------------------------------

$mdl_remove = array(
    "form"    => true,
    "footer"  => true,
    "reload"  => true,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"          => "remove",
        "size"        => "md",
        "style"       => "danger",
        "title"       => "Remove Entry",
        "body"        => "<h4><span class=\"dynmsg\"></span></h4>
                         <input name=\"entryid\" type=\"hidden\" class=\"form-control\" id=\"entryid\">",
        "script"      => "var entryid = button.data('entryid')     // extract info from data-* attributes
                         var entryname = button.data('entryname')
                         var modal = $(this)
                         modal.find('.dynmsg').text('Are you sure you want to remove ' + entryname + ' ?')
                         modal.find('.modal-body input').val(entryid)",
        "onsubmit"    => "",
        "action"      => "entries_sc.php?eventid=$eventid&pagestate=delete",
        "target"      => "",
        "close-lbl"   => "changed my mind",
        "reset-lbl"   => "reset",
        "submit-lbl"  => "remove entry",
        "footer"      => ""
    ),
);
