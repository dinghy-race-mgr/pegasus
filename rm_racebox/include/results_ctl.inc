<?php

/**
 *  results_ctl
 * 
 *  Include file for configuring timer page buttons and modals
 * 
 *  Buttons:
 *    loadret       - load retirements
 *    loaddec       - load declarations (retirements and signoff)
 *    changefinish  - change finish lap
 *    message       - send message
 *    publish       - publish results
 * 
 *  Inline Buttons
 *    edit          - edit result
 *    detail        - show detail lap times
 *    remove        - remove result

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

// ---- load retirements--------------------------------------------------------------
$btn_loadret = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"         => "loadret",
        "size"       => "lg",
        "style"      => "primary",
        "label"      => "Retirements",
        "link"       => "results_sc.php?eventid=$eventid&pagestate=retirements",
        "glyph"      => "glyphicon-cloud-download",
        "popover"    => "click here to load retirements from the racemanager sign on facility ",
        "pop-pos"    => "left",
        "block"      => "btn-block",
        "disabled"   => "",
        "top-margin" => "margin-top-10"
    ),
);


// ----load declarations ----------------------------------------------------------------
$btn_loaddec = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"         => "loaddec",
        "size"       => "lg",
        "style"      => "primary",
        "label"      => "+ Declarations",
        "link"       => "results_sc.php?eventid=$eventid&pagestate=declarations",
        "glyph"      => "glyphicon-cloud-download",   // FIXME - probably not best - need to update glyphicons
        "popover"    => "click here to load declarations/retirements from the racemanager sign on facility ",
        "pop-pos"    => "left",
        "block"      => "btn-block",
        "disabled"   => "",
        "top-margin" => "margin-top-10"
    ),
);


// ---- change finish lap ----------------------------------------------------------------
$btn_changefinish = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"         => "changefinish",
        "size"       => "lg",
        "style"      => "primary",
        "label"      => "Change Finish Lap",
        "glyph"      => "glyphicon-pencil",
        "popover"    => "change the lap used for calculating the finishing positions - very useful if you forgot to shorten course on the Timer page",
        "pop-pos"    => "left",
        "block"      => "btn-block",
        "disabled"   => "",
        "top-margin" => "margin-top-10"
    ),
);

$mdl_changefinish = array(
    "form"    => true,
    "footer"  => false,
    "reload"  => true,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"         => "changefinish",
        "size"       => "lg",
        "style"      => "success",
        "title"      => "Change Finish Lap",
        "body"       => "",
        "script"     => "",
        "onsubmit"   => "",
        "action"     => "results_sc.php?eventid=$eventid&pagestate=changefinish",
        "target"     => "",
        "close-lbl"  => "changed my mind",
        "reset-lbl"  => "",
        "submit-lbl" => "change finish laps",
        "footer"     => ""
    ),
);

// ---- send message ----------------------------------------------------------------
$btn_message = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"         => "sendmessage",
        "size"       => "lg",
        "style"      => "success",
        "label"      => "Report Issue",
        "glyph"      => "glyphicon-send",
        "popover"    => "click to send message to the results manager",
        "pop-pos"    => "left",
        "block"      => "btn-block",
        "disabled"   => "",
        "top-margin" => "margin-top-10"
    ),
);

$mdl_message = array(
    "form"    => true,
    "footer"  => false,
    "reload"  => false,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"         => "sendmessage",
        "size"       => "lg",
        "style"      => "success",
        "title"      => "<b>Send Message</b>",
        "body"       => "",
        "script"     => "",
        "onsubmit"   => "",
        "action"     => "results_sc.php?eventid=$eventid&pagestate=message",
        "target"     => "",
        "close-lbl"  => "changed my mind",
        "reset-lbl"  => "",
        "submit-lbl" => "send message",
        "footer"     => ""
    ),
);


// ---- publish results ----------------------------------------------------------------
$btn_publish = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"         => "publish",
        "size"       => "lg",
        "style"      => "warning",
        "label"      => "Publish",
        "glyph"      => "glyphicon-list-alt",
        "popover"    => "click here to publish the final results - you can correct errors and republish them again if necessary",
        "pop-pos"    => "left",
        "block"      => "btn-block",
        "disabled"   => "",
        "top-margin" => "margin-top-20"
    ),
);

$mdl_publish = array(
    "form"    => false,
    "footer"  => false,
    "reload"  => true,
    "close"   => false,
    "reset"   => false,
    "submit"  => false,
    "fields"  => array(
        "id"         => "publish",
        "size"       => "lg",
        "style"      => "info",
        "title"      => "<b>Publish Results - $eventname</b>",
        "body"       => "<iframe src=\"results_publish_pg.php?eventid=$eventid&pagestate=init&warnings=$warning_count\" style=\"width: 100%; height: 550px; border:0px\" id=\"entryframe\"></iframe>",
        "script"     => "",
        "onsubmit"   => "",
        "action"     => "", //"results_sc.php?eventid=$eventid&pagestate=message",
        "target"     => "",
        "close-lbl"  => "",
        "reset-lbl"  => "",
        "submit-lbl" => "",
        "footer"     => ""
    ),
);


// -- INLINE BUTTONS ---------------------------------------------------------------

// ---- edit result ----------------------------------------------------------------
$btn_edit = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"         => "editresult",
        "size"       => "",
        "style"      => "progress-bar-default",
        "label"      => "",
        "glyph"      => "glyphicon-pencil",
        "popover"    => "edit this result",
        "pop-pos"    => "top",
        "block"      => "",
        "disabled"   => "",
        "top-margin" => "",
    ),
);

$mdl_edit = array(
    "form"    => true,
    "footer"  => false,
    "reload"  => true,
    "close"   => false,
    "reset"   => false,
    "submit"  => false,
    "fields"  => array(
        "id"         => "editresult",
        "size"       => "lg",
        "style"      => "success",
        "title"      => "Edit Result",
        "body"       => "<iframe id=\"editresultFrame\" src=\"\" frameborder=\"0\" style=\"width: 100%; height: 700px;\"></iframe>",
        "script"     => "
                        $( '#editresultModalLabel' ).text( 'Edit result for ' + button.data('boat'))
                        $( '#editresultFrame' ).attr('src', button.data('iframe'))
                        ",
        "onsubmit"   => "",
        "action"     => "",
        "target"     => "",
        "close-lbl"  => "cancel",
        "reset-lbl"  => "",
        "submit-lbl" => "",
        "footer"     => ""
    ),
);

/*
$mdl_edit = array(
    "form"    => true,
    "footer"  => false,
    "reload"  => true,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"         => "editresult",
        "size"       => "lg",
        "style"      => "primary",
        "title"      => "Edit Result",
        "body"       => "",
        "script"     => "
                        $( '#editresultModalLabel' ).text( 'Edit result for ' + button.data('boat'))
                        $( '#identryid' ).val(button.data('entryid'))
                        $( '#idhelm' ).val(button.data('helm'))
                        $( '#idcrew' ).val(button.data('crew'))
                        $( '#idsailnum' ).val(button.data('sailnum'))
                        $( '#idpn' ).val(button.data('pn'))
                        $( '#idlap' ).val(button.data('lap'))
                        $( '#idetime' ).val(button.data('etime'))
                        $( '#idcode' ).val(button.data('code'))
                        $( '#idpenalty' ).val(button.data('penalty'))
                        $( '#idnote' ).val(button.data('note'))
                        ",
        "onsubmit"   => "",
        "action"     => "results_sc.php?eventid=$eventid&pagestate=editresult",
        "target"     => "",
        "close-lbl"  => "nope",
        "reset-lbl"  => "",
        "submit-lbl" => "update result",
        "footer"     => ""
    ),
);
*/

// -------- show detail ----------------------------------------------------------------
/*$btn_detail = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"         => "detail",
        "size"       => "",
        "style"      => "progress-bar-default",
        "label"      => "",
        "glyph"      => "glyphicon-list-alt",
        "popover"    => "view lap times",
        "pop-pos"    => "top",
        "block"      => "",
        "disabled"   => "",
        "top-margin" => ""
    ),
);

$mdl_detail = array(
    "form"    => false,
    "footer"  => true,
    "reload"  => true,
    "close"   => false,
    "reset"   => false,
    "submit"  => false,
    "fields"  => array(
        "id"          => "detail",
        "size"        => "md",
        "style"       => "primary",
        "title"       => "Lap Detail",
        "body"        => "<span id=\"detailtxt\"></span>",
        "script"      => "var table = button.data('table')
                          document.getElementById(\"detailtxt\").innerHTML = table
                         ",
        "onsubmit"   => "",
        "action"     => "",
        "close-lbl"  => "",
        "reset-lbl"  => "",
        "submit-lbl" => "",
        "footer"     => "<button type=\"button\" class=\"btn btn-default pull-left\" data-dismiss=\"modal\">Close</button>"
    ),
);
*/


// -------- remove result ----------------------------------------------------------------
$btn_remove = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => true,
    "fields"    => array(
        "id"         => "remove",
        "size"       => "",
        "style"      => "progress-bar-danger",
        "label"      => "",
        "glyph"      => "glyphicon-remove",
        "popover"    => "remove boat from results",
        "pop-pos"    => "top",
        "block"      => "",
        "disabled"   => "",
        "top-margin" => ""
    )
);

$mdl_remove = array(
    "form"    => true,
    "footer"  => false,
    "reload"  => true,
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"         => "remove",
        "size"       => "lg",
        "style"      => "danger",
        "title"      => "Remove Boat from Results",
        "body"       => "<h4><span id=\"removetxt\"></span></h4>
                         <input name=\"entryid\" type=\"hidden\" class=\"form-control\" id=\"entryid\">
                        ",
        "script"     => "var entryname = button.data('entryname')
                         document.getElementById(\"removetxt\").innerHTML = 'Are you sure you want to permanently delete <b>' + entryname + '</b>' + '</br></br><span style=\'color: red\';>This cannot be undone</span'
                         var entryid = button.data('entryid')
                         var modal = $(this)
                         modal.find('.modal-body input').val(entryid)
                        ",
        "onsubmit"   => "",
        "action"     => "results_sc.php?eventid=$eventid&pagestate=delete",
        "close-lbl"  => "nope",
        "reset-lbl"  => "",
        "submit-lbl" => "remove boat",
        "footer"     => ""
    ),
);



