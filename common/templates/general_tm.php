<?php
/*
 *  under_construction
 */
function under_construction($params=array())
    /*
     FIELDS
        title  :  title for under construction note
        info   :  detail for under construction note

     PARAMS
        none
     */
{
    $html = <<<EOT
        <div class="jumbotron center-block" style="width:60%; margin-top: 60px;">
            <div class="row">
                <div class="col-md-6">
                    <img src="../common/images/web_graphics/uc_hat_t.png" alt="under construction" height="200" width="200">
                </div>
                <div class="col-md-6 ">
                    <p><b>{title}</b></p>
                    <p>{info}</p>
                </div>
            </div>
        </div>
EOT;
    return $html;
}



function error_msg($params = array())
{
    $bufr = <<<EOT
        <div class="row margin-top-10">
           <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
               <div class="alert alert-danger rm-text-md" role="alert"> 
                    <h1>Sorry ...</h1>
                    <h3>{error}</h3>
                    <h4>{detail}</h4>
                    <hr>
                    <p>{action}<p> 
               </div>
           </div>
        </div>
EOT;
    return $bufr;
}

//function growls($params=array())
///*
// FIELDS
//    none
//
// PARAMS
//    eventid :  event id
//    page    :  page to display growl on
//*/
//{
//    $eventid = $params["eventid"];
//    $page    = $params["page"];
//    $html = "";
//    // first check that we have a current growl and that it is for this page
//    if (!empty($_SESSION["e_$eventid"]['growl']["$page"]))
//    {
//        $html.= <<<EOT
//        <script>
//        $(function() {
//            $.bootstrapGrowl("<h4 class=\"alert-heading\">{$_SESSION["e_$eventid"]['growl']['msg']}</h4>", {
//                type: '{$_SESSION["e_$eventid"]['growl']['type']}',
//                offset: {from: 'top', amount: 70},
//                align: 'left',
//                width: '600',
//                delay: {$_SESSION["e_$eventid"]['growl']['close']},
//                allow_dismiss: true
//            });
//        });
//        </script>
//EOT;
//        unset($_SESSION["e_$eventid"]['growl']);    // now unset growls
//    }
//    return $html;
//}

function footer($params = array())
/*
 FIELDS
    l_foot : html markup for left hand section of footer
    m_foot : html markup for center section of footer
    r_foot : html markup for right hand section of footer

 PARAMS
    style  :  bootstrap footer style
    fixed  :  if true footer will be fixed to bottom of page
*/
{
    empty($params['style']) ?  $type = "navbar-default" : $type = "navbar-{$params['style']}";
    $params['fixed'] ? $mode = "navbar-fixed-bottom" : $mode = "navbar-bottom";

    $html = <<<EOT
    <div class="navbar $type $mode">
            <div class="row">
                <div class="col-md-4 navbar-text" style="text-align: left; padding-left: 5%">{l_foot}</div>
                <div class="col-md-4 navbar-text" style="text-align: center">{m_foot}</div>
                <div class="col-md-3 navbar-text navbar-right" style="text-align: right; padding-right: 5%">{r_foot}</div>
            </div>
    </div>
EOT;
    return $html;
}


function btn_modal($params=array())
    /*
     FIELDS
        id        : id for button
        style     : bootstrap style for button
        size      : bootstrap size for button
        label     : text label
        glyph     : glyph label
        popover   : popover text
        pop-pos   : popover position
        disabled  : 'disabled' if button is to be disabled otherwise leave blank
        block     : 'btn-block' if a block button otherwise leave blank
        top-margin: top margin style (e.g margin-top-20)

     PARAMS
        label     :  true if label required
        glyph     :  true if glyph required
        g-pos     :  glyph position (left|right)
        popover   : true if popover required
     */
{
    if (!$_SESSION['button_help']) { $params['popover'] = false; }  // turn off tooltip if help is off
    $popover = "";
    if ($params['popover'])
    {
        $popover = <<<EOT
        data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true" data-title="{popover}" data-placement="{pop-pos}"
EOT;
    }
    $label = createbtnlabel ($params['label'], $params['glyph'], $params['g-pos']);  // get label

    empty($params['data']) ? $data_items = "" : $data_items = $params['data'];


    $html = <<<EOT
    <div $popover class="btn-group {block}">
        <button id="{id}" type="button" class="btn btn-{style} btn-{size} {block} {top-margin}" {disabled} data-toggle="modal" data-target="#{id}Modal" $data_items>
            $label
        </button>
    </div>
EOT;

    return $html;
}

function modal($params=array())
    /*
    FIELDS
     * id    : base id used for modal and form
     * size  : size of modal using bootstrap codes
     * style : bootstrap style for modal
     * title : title to appear in modal header
     * body  : html content for body of modal (fields if form)
     * script: js script to be run when modal opens
     * footer: content of footer if requested in params (not used in forms)
  F    action: action to take on post form - link to script
  F    target: if set can be used to set window target
  F    onsubmit: js action to take on submit
  F    close-lbl:  label on close/back button
  F    reset-lbl:  label on reset button
  F    submit-lbl:  label on submit button

    PARAMS
       form  :  true if modal contains a form
       footer:  true if footer required
       reload:  true if reloads parent page after modal is hidden
       back  :  true if back/cancel button required
       reset :  true if reset button required
       submit:  true if submit button required
     *
     */
{

    $html = "";
    empty($params['footer']) ? $footer = "" : $footer = "<div class=\"modal-footer\">{footer}</div>";

    $reload = "";
    if ($params['reload'])
    {
        $reload = <<<EOT
        <script>
        $('#{id}Modal').on('hidden.bs.modal', function () {
            window.location.reload();
        });
        </script>
EOT;
    }

    $form_hdr = "";
    $form_script = "";
    $form_buttons = "";

    if ($params['form'])
    {
        $form_hdr = <<<EOT
        <style type="text/css">
           #{id}Form  .inputfieldgroup .form-control-feedback,
           #{id}Form  .selectfieldgroup .form-control-feedback { top: 0; right: -15px; }
        </style>
        <form id="{id}Form" class="form-horizontal" action="{action}" method="post" target="{target}"
            data-fv-addons="mandatoryIcon"
            data-fv-addons-mandatoryicon-icon="glyphicon glyphicon-asterisk"
            data-fv-framework="bootstrap"
            data-fv-icon-valid="glyphicon glyphicon-ok"
            data-fv-icon-invalid="glyphicon glyphicon-remove"
            data-fv-icon-validating="glyphicon glyphicon-refresh" >
EOT;

        $form_script = <<<EOT
        <script>
            $(document).ready(function() {
                $('#{id}Form').formValidation({
                    excluded: [':disabled'],
                })
                $('#resetBtn').click(function() {
                 $('#{id}Form').data('bootstrapValidator').resetForm(true);
                });
            });
        </script>
EOT;
        $footer = "<div class=\"modal-footer\">";
        if (!empty($params['close']))
        {
            $footer.= <<<EOT
            <button type="button" class="btn btn-default" data-dismiss="modal">
                <span class="glyphicon glyphicon-remove"></span>&nbsp;{close-lbl}
            </button>
EOT;
        }
        if (!empty($params['reset']))
        {
            $footer.= <<<EOT
            <button id="resetBtn" type="button" class="btn btn-default">{reset-lbl}</button>
EOT;
        }
        if (!empty($params['submit']))
        {
            $footer.= <<<EOT
            <button type="submit" class="btn btn-{style}" onClick="{onsubmit}">
                <span class="glyphicon glyphicon-ok"></span>&nbsp;{submit-lbl}
            </button>
EOT;
        }
        $footer.= "</div></form>";
    }

    $html.= <<<EOT
    <div class="modal fade" id="{id}Modal" tabindex="-1" role="dialog" aria-labelledby="{id}ModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-{size}">
            <div class="modal-content">

                <div class="modal-header bg-{style}-modal">
                   <button type="button" class="close modal-close" data-dismiss="modal" aria-label="Close" >
                      <span aria-hidden="true">close &times;</span>
                   </button>
                   <h4 class="modal-title" id="{id}ModalLabel">{title}</h4>
                </div>
                $form_hdr
                <div class="modal-body">

                  {body}
                  $form_script
                  <!-- activate popovers in body -->
                  <script>
                      $(document).ready(function() {
                          $("[data-toggle=popover]").popover({trigger: 'hover',html: 'true'});
                      });
                   </script>
                </div>
                $footer
            </div>
        </div>
    </div>
    <script>
         <!-- moves modal content outside of container -->
         $('#{id}Modal').appendTo("body");
         <!-- allows information to be passed from button to modal -->
         $('#{id}Modal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget)
                {script}
          })
    </script>
    $reload
EOT;
    return $html;
}

/**
 * @param array $params   fields for template
 * @return string
 */
function btn_link($params = array())
    /*
 PARAMS
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
    data
    link
 */
{
    if (!$_SESSION['button_help']) { $params['popover'] = false; }  // turn off tooltip if help is off
    $popover = "";
    if ($params['popover'])
    {
        $popover = <<<EOT
        data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true" data-title="{popover}" data-placement="{pop-pos}"
EOT;
    }

    $label = createbtnlabel ($params['label'], $params['glyph'], $params['g-pos']);  // get label

    $html = <<<EOT
    <div $popover class="btn-group {block}">
        <a id="{id]}" href="{link}" class="btn btn-{style} btn-{size} {block} {top-margin}" aria-expanded="false" role="button" {data} {disabled}>
            $label
        </a>
    </div>
EOT;
    return $html;
}

function btn_link_blink($params = array())
    /*
 PARAMS
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
    data
    link
 */
{
    if (!$_SESSION['button_help']) { $params['popover'] = false; }  // turn off tooltip if help is off
    $popover = "";
    if ($params['popover'])
    {
        $popover = <<<EOT
        data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true" data-title="{popover}" data-placement="{pop-pos}"
EOT;
    }

    $label = createbtnlabel ($params['label'], $params['glyph'], $params['g-pos']);  // get label

    $html = <<<EOT
    <div $popover class="btn-group {block}">
        <a id="{id}" href="{link}" class="btn btn-{style} btn-{size} {block} {top-margin}" aria-expanded="false" role="button" {data} {disabled}>
            $label
        </a>
    </div>
    <script type="text/javascript">
    $(document).ready(function(){
        var count = 0;
            do {
                $('#{id}').fadeOut(500).fadeIn(500);
                count++;
            } while(count < 5);
        });
    </script>
EOT;
    return $html;
}

function badge_link($params = array())
    /*
 PARAMS
    id        : id for button
    style     : bootstrap style for button
    size      : bootstrap size for button
    label     : text label
    glyph     : glyph label
    g-pos     : glyph position (left|right)
    popover   : popover text
    pop-pos   : popover position
    disabled  : 'disabled' if button is to be disabled otherwise leave blank
    block     : 'btn-block' if a block button otherwise leave blank
    target    : window target for link
    link      : link from badge
    data      : data to be accessed at link
 */
{
    if (!$_SESSION['button_help']) { $params['popover'] = false; }  // turn off tooltip if help is off

    $size = "";
    if ($params['size'])
    {
        $size = "btn-{$params['size']}";
    }

    $popover = "";
    if ($params['popover']) {
        $popover = <<<EOT
        data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true" data-title="{popover}" data-placement="{pop-pos}"
EOT;
    }

    $label = createbtnlabel($params['label'], $params['glyph'], $params['g-pos']);  // get label

    $html = <<<EOT
    <span $popover>
        <a id="{id}" href="{link}" role="button" class="btn btn-link $size {block} " style="padding:0px" target="{target}" {data} {disabled} >
             <span class="badge {style}" style="font-size: 100%">$label</span>
        </a>
    </span>
EOT;
    return $html;
}


function badge_modal($params = array())
/*
PARAMS
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
data      : data to eb accessed in modal
*/
{
    if (!$_SESSION['button_help']) { $params['popover'] = false;}                     // empty tooltip if help is off

    $popover = "";
    if ($params['popover']) {
        $popover = <<<EOT
        data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true" data-title="{popover}" data-placement="{pop-pos}"
EOT;
    }
    $label = createbtnlabel ($params['label'], $params['glyph'], $params['g-pos']);  // get label

    $html = <<<EOT
    <span $popover>
        <button type="button" class="btn btn-link btn-{size} {block} " style="padding:0px 5px 0px 5px;" data-toggle="modal"
        rel="tooltip" data-original-title="{popover}" data-placement="bottom" data-target="#{id}Modal" {data} {disabled}>
            <span class="badge {style}" style="font-size: 100%">$label</span>
        </button>
    </span>

EOT;

    return $html;
}


/**
 * @param array $params   fields for template
 * @param array $data     options in link
 * @return string
 */
function btn_multilink($params = array())
    /*
 Fields
    id        : id for button
    style     : bootstrap style for button
    size      : bootstrap size for button
    label     : text label
    glyph     : glyph label
    g-pos     : glyph position (left|right)
    popover   : popover text
    pop-pos   : popover position
    disabled  : 'disabled' if button is to be disabled otherwise leave blank
    block     : 'btn-block' if a block button otherwise leave blank
    top-margin: top margin style (e.g margin-top-20)


   Params:
    label     : label required (true|false),
    glyph     : glyph required (true|false),
    g-pos     : glyph position  (left|right),
    popover   : popover requested ("true"|"false"),
    data      : array(with key as label and value as link)

 */
{
    if (!$_SESSION['button_help']) { $params['popover'] = false; }  // turn off tooltip if help is off
    $popover = "";
    if ($params['popover'])
    {
        $popover = <<<EOT
        data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true" data-title="{popover}" data-placement="{pop-pos}"
EOT;
    }

    $options_bufr = "";
    foreach ($params['data'] as $label => $link)
    {
        $options_bufr.= "<li><a href=\"{$link}\" target=\"{target}\">{$label}</a></li> ";
    }
    $label = createbtnlabel ($params['label'], $params['glyph'], $params['g-pos']);  // get label

    $html = <<<EOT
    <div $popover class="btn-group {block}">
        <button id="{id]}" type="button" class="btn btn-{style} btn-{size} {block} {top-margin} dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
            $label <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" role="menu">
           $options_bufr
        </ul>
    </div>
EOT;

    return $html;
}



function createbtnlabel($label, $glyph, $pos)
{
    if (empty($label)) {
        $label = <<<EOT
           <span class="glyphicon {glyph}"></span>
EOT;
    } else {
        if (empty($glyph)) {
            $label = "{label}";
        } else {
            if ($pos == "right") {
                $label = <<<EOT
                   {label}&nbsp;<span class="glyphicon {glyph}"></span>&nbsp;
EOT;
            } else {
                $label = <<<EOT
                   <span class="glyphicon {glyph}"></span>&nbsp;{label}&nbsp;
EOT;
            }
        }
    }

    return $label;
}


function report_page()
{
    $html = <<<EOT
    <!DOCTYPE html><html lang="en">
    <head>
        <title>{title}</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="racemanager">
        <meta name="author" content="mark elkington">

        <meta http-equiv="cache-control" content="max-age=0" />
        <meta http-equiv="cache-control" content="no-cache" />
        <meta http-equiv="expires" content="0" />
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        <meta http-equiv="pragma" content="no-cache" />

        <!-- Custom styles for this template -->
        <link href="{stylesheet}" rel="stylesheet">

    <head>
    <body>
        {body}
    </body>
    </html>
EOT;

    return $html;
}

