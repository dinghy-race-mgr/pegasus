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

// -- RIGHT HAND PANEL ---------------------------------------------------------------

// ---- load retirements--------------------------------------------------------------
$btn_loadret = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => "true",
    "fields"    => array(
        "id"         => "loadret",
        "size"       => "lg",
        "style"      => "primary",
        "label"      => "+ Retirements",
        "link"       => "results_sc.php?eventid=$eventid&pagestate=retirements",
        "glyph"      => "glyphicon-cloud-download",
        "g-pos"      => "left",
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
    "popover"   => "true",
    "fields"    => array(
        "id"         => "loaddec",
        "size"       => "lg",
        "style"      => "primary",
        "label"      => "+ Declarations",
        "link"       => "results_sc.php?eventid=$eventid&pagestate=declarations",
        "glyph"      => "glyphicon-cloud-download",   // FIXME - probably not best - need to update glyphicons
        "g-pos"      => "left",
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
    "popover"   => "true",
    "fields"    => array(
        "id"         => "changefinish",
        "size"       => "lg",
        "style"      => "primary",
        "label"      => "Change Finish Lap",
        "glyph"      => "glyphicon-pencil",
        "g-pos"      => "left",
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
        "style"      => "primary",
        "title"      => "<b>Change Finish Lap</b>",
        "body"       => "",
        "form"       => "true",
        "script"     => "",
        "onsubmit"   => "",
        "action"     => "results_sc.php?eventid=$eventid&pagestate=changefinish",
        "target"     => "",
        "close-lbl"  => "nope",
        "reset-lbl"  => "",
        "submit-lbl" => "change finish lap",
        "reload"     => true,
        "footer"     => ""
    ),
);

// ---- send message ----------------------------------------------------------------
$btn_message = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => "true",
    "fields"    => array(
        "id"         => "sendmessage",
        "size"       => "lg",
        "style"      => "primary",
        "label"      => "Report Issue",
        "glyph"      => "glyphicon-send",
        "g-pos"      => "left",
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
        "style"      => "primary",
        "title"      => "<b>Send Message</b>",
        "body"       => "",
        "form"       => "true",
        "script"     => "",
        "onsubmit"   => "",
        "action"     => "results_sc.php?eventid=$eventid&pagestate=message",
        "target"     => "",
        "close-lbl"  => "nope",
        "reset-lbl"  => "",
        "submit-lbl" => "send message",
        "reload"     => false,
        "footer"     => ""
    ),
);


// ---- publish results ----------------------------------------------------------------
$btn_publish = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => "true",
    "fields"    => array(
        "id"         => "publish",
        "size"       => "lg",
        "style"      => "primary",
        "label"      => "Publish",
        "glyph"      => "glyphicon-list-alt",
        "g-pos"      => "left",
        "popover"    => "click here to publish the final results - you can correct errors and republish them again if necessary",
        "pop-pos"    => "left",
        "block"      => "btn-block",
        "disabled"   => "",
        "top-margin" => "margin-top-20"
    ),
);

$mdl_publish = array(
    "form"    => true,
    "footer"  => false,
    "reload"  => true,
    "close"   => false,
    "reset"   => false,
    "submit"  => false,
    "fields"  => array(
        "id"         => "publish",
        "size"       => "lg",
        "style"      => "primary",
        "title"      => "<b>Publish Race Results</b>",
        "body"       => "<iframe src=\"results_publish_pg.php?eventid=$eventid&pagestate=init\" style=\"width: 100%; height: 550px; border:0px\" id=\"entryframe\"></iframe>",
        "form"       => false,
        "script"     => "",
        "onsubmit"   => "",
        "action"     => "results_sc.php?eventid=$eventid&pagestate=message",
        "target"     => "",
        "close-lbl"  => "",
        "reset-lbl"  => "",
        "submit-lbl" => "",
        "reload"     => true,
        "footer"     => ""
    ),
);


// -- INLINE BUTTONS ---------------------------------------------------------------

// ---- edit result ----------------------------------------------------------------
$btn_edit = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => "true",
    "fields"    => array(
        "id"         => "edit",
        "size"       => "",
        "style"      => "progress-bar-default",
        "label"      => "",
        "glyph"      => "glyphicon-pencil",
        "g-pos"      => "left",
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
    "close"   => true,
    "reset"   => false,
    "submit"  => true,
    "fields"  => array(
        "id"         => "edit",
        "size"       => "lg",
        "style"      => "primary",
        "title"      => "Edit Result",
        "body"       => "",
        "form"       => true,
        "script"     => "
                        $( '#editModalLabel' ).text( 'Edit result for ' + button.data('boat'))
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
        "reload"     => true,
        "footer"     => ""
    ),
);




// -------- show detail ----------------------------------------------------------------
$btn_detail = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => "true",
    "fields"    => array(
        "id"         => "detail",
        "size"       => "",
        "style"      => "progress-bar-default",
        "label"      => "",
        "glyph"      => "glyphicon-list-alt",
        "g-pos"      => "left",
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
        "form"        => false,
        "script"      => "var table = button.data('table')
                          document.getElementById(\"detailtxt\").innerHTML = table
                         ",
        "onsubmit"   => "",
        "action"     => "",
        "close-lbl"  => "",
        "reset-lbl"  => "",
        "submit-lbl" => "",
        "reload"     => true,
        "footer"     => "<button type=\"button\" class=\"btn btn-default pull-left\" data-dismiss=\"modal\">Close</button>"
    ),
);



// -------- remove result ----------------------------------------------------------------
$btn_remove = array(
    "label"     => true,
    "glyph"     => true,
    "g-pos"     => "left",
    "popover"   => "true",
    "fields"    => array(
        "id"         => "remove",
        "size"       => "",
        "style"      => "progress-bar-danger",
        "label"      => "",
        "glyph"      => "glyphicon-remove",
        "g-pos"      => "left",
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
        "form"       => true,
        "script"     => "var entryname = button.data('entryname')
                         document.getElementById(\"removetxt\").innerHTML = entryname
                         var entryid = button.data('entryid')
                         var modal = $(this)
                         modal.find('.modal-body input').val(entryid)
                        ",
        "onsubmit"   => "",
        "action"     => "results_sc.php?eventid=$eventid&pagestate=delete",
        "close-lbl"  => "nope",
        "reset-lbl"  => "",
        "submit-lbl" => "remove boat",
        "reload"     => true,
        "footer"     => ""
    ),
);



