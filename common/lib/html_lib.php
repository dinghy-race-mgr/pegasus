<?php

/* ----------------------------------------------------------------------------------------------------
    html_lib
    
    Library of functions for creating consistent mark up.  Largely based on Bootstrap framework.

   ----------------------------------------------------------------------------------------------------
*/

function h_createbtnlabel($label, $glyph, $pos)
{
    if (empty($label))
    {
        $label = "<span class=\"glyphicon $glyph\"></span>";
    }
    else
    {
        if (empty($glyph))
        {
            $label = "$label";
        }
        else
        {
            $glyph = "<span class=\"glyphicon $glyph\"></span>";
            ($pos == "left") ? $label = "$glyph&nbsp;$label&nbsp;" : $label = "$label&nbsp;$glyph&nbsp";
        }
    }    
    return $label;
}

function h_button_link($btn)
{
    $bufr = "";
    
    if (!$_SESSION['button_help']) { unset($btn['popover']);}                    // empty tooltip if help is off
    
    (!empty($btn['style']))? $style    = "btn-{$btn['style']}" : $style = "btn-default";
    (!empty($btn['size'])) ? $size     = "btn-{$btn['size']}" : $size = "";
    (!empty($btn['data'])) ? $data     = $btn['data'] : $data = "";
    (!empty($btn['topmargin'])) ? $topmargin = $btn['topmargin'] : $topmargin = "margin-top-10";
    (!empty($btn['popposn'])) ? $popposn = $btn['popposn'] : $popposn = "left";
    ($btn['disabled'])     ? $disabled = "disabled=\"{$btn['disabled']}\"" : $disabled = "";
    ($btn['block'])        ? $block    = "btn-block" : $block = "";
    
    $label = h_createbtnlabel ($btn['label'], $btn['glyph'], $btn['glyphpos']);   // get label

    // button with tooltip
    $bufr = <<<EOT
    <div data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true" data-title="{$btn['popover']}" data-placement="$popposn">
        <a id="{$btn['id']}" href="{$btn['link']}" class="btn $style $size $block $topmargin" role="button" $data $disabled>$label</a>
    </div>
EOT;
    return $bufr;
}

function h_button_multilink($btn)
{
    if (!$_SESSION['button_help']) { unset($btn['popover']);}                     // empty tooltip if help is off
    
    $bufr = "";    
    (!empty($btn['style']))? $style    = "btn-{$btn['style']}" : $style = "btn-default";
    (!empty($btn['size'])) ? $size     = "btn-{$btn['size']}" : $size = "";
    (!empty($btn['data'])) ? $data     = $btn['data'] : $data = "";
    (!empty($btn['topmargin'])) ? $topmargin = $btn['topmargin'] : $topmargin = "margin-top-10";
    (!empty($btn['popposn'])) ? $popposn = $btn['popposn'] : $popposn = "left";
    ($btn['disabled'])     ? $disabled = "disabled=\"{$btn['disabled']}\"" : $disabled = "";
    ($btn['block'])        ? $block    = "btn-block" : $block = "";
    
    $label = h_createbtnlabel ($btn['label'], $btn['glyph'], $btn['glyphpos']);  // get label

     // button with tooltip
    $bufr.= <<<EOT
    <div class="btn-group $block" data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true" data-title="{$btn['popover']}" data-placement="$popposn">
        <button id="{$btn['id']}" type="button" class="btn $style $size $block $topmargin dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
            $label <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" role="menu">
EOT;
 
    // drop down options
    foreach ($btn['options'] as $label => $link)
    {
        $bufr.= <<<EOT
            <li><a href="{$link}" target="{$btn["target"]}">{$label}</a></li>        
EOT;
    }
    $bufr.= "</ul></div>";
    
    return $bufr;
}

function h_button_modal($btn)
{
    if (!$_SESSION['button_help']) { unset($btn['popover']);}                     // empty tooltip if help is off
    
    $bufr = "";
    (!empty($btn['style']))? $style    = "btn-{$btn['style']}" : $style = "btn-default";
    (!empty($btn['size'])) ? $size     = "btn-{$btn['size']}" : $size = "";
    (!empty($btn['data'])) ? $data     = $btn['data'] : $data = "";
    (!empty($btn['topmargin'])) ? $topmargin = $btn['topmargin'] : $topmargin = "margin-top-10";
    (!empty($btn['popposn'])) ? $popposn = $btn['popposn'] : $popposn = "left";
    ($btn['disabled'])     ? $disabled = " disabled " : $disabled = "";
    ($btn['block'])        ? $block    = " btn-block " : $block = "";
    
     $label = h_createbtnlabel ($btn['label'], $btn['glyph'], $btn['glyphpos']);  // get label

    $bufr.= <<<EOT
    <div data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true" data-title="{$btn['popover']}" data-placement="$popposn">
        <button id="{$btn['id']}" type="button" class="btn $style $size $block  $topmargin" $disabled data-toggle="modal" data-target="#{$btn['id']}Modal" $data >
            $label
        </button>
    </div>
EOT;

     return $bufr;
}

function h_button_dialog($btn)
{
    if (!$_SESSION['button_help']) { unset($btn['popover']);}                     // empty tooltip if help is off
    
    $bufr = "";
    (!empty($btn['style']))? $style    = "btn-{$btn['style']}" : $style = "btn-default";
    (!empty($btn['size'])) ? $size     = "btn-{$btn['size']}" : $size = "";
    (!empty($btn['data'])) ? $data     = $btn['data'] : $data = "";
    ($btn['disabled'])     ? $disabled = "disabled=\"{$btn['disabled']}\"" : $disabled = "";
    ($btn['block'])        ? $block    = "btn-block" : $block = "";
    
     $label = h_createbtnlabel ($btn['label'], $btn['glyph'], $btn['glyphpos']);  // get label

    // button
    $bufr = <<<EOT
     <div data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true" data-title="{$btn['popover']}" data-placement="left">
        <button id="{$btn['id']}" type="button" class="btn $style $size $block margin-top-05" $disabled $data>
            $label
        </button>
    </div>
EOT;

    return $bufr;
}

function h_badge_link($btn)
{
    if (!$_SESSION['button_help']) { unset($btn['popover']);}                     // empty tooltip if help is off
    
    $bufr = "";
    
    (!empty($btn['style']))? $style    = "{$btn['style']}"    : $style = "btn-default";
    (!empty($btn['size'])) ? $size     = "btn-{$btn['size']}" : $size = "";
    (!empty($btn['data'])) ? $data     = $btn['data']         : $data = "";
    (!empty($btn['target'])) ?  $btn['target'] = $btn['target']   : $btn['target'] = "self";
    ($btn['disabled'])     ? $disabled = "disabled=\"{$btn['disabled']}\"" : $disabled = "";
    ($btn['block'])        ? $block    = "btn-block"          : $block = "";
        
    $label = h_createbtnlabel ($btn['label'], $btn['glyph'], $btn['glyphpos']);  // get label        
    
    $bufr.= <<<EOT
    <span data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true" data-title="{$btn['popover']}" data-placement="bottom">
        <a id="{$btn['id']}" href="{$btn['link']}" role="button" class="btn btn-link $size $block " style="padding:0px" target="{$btn["target"]}"  $data >
             <span class="badge $style" style="font-size: 80%">$label</span>
        </a>
    </span>
EOT;
    
    return $bufr;
}


function h_badge_modal($btn)
{
    if (!$_SESSION['button_help']) { unset($btn['popover']);}                     // empty tooltip if help is off
   
    $bufr = "";
    
    (!empty($btn['style']))? $style    = "{$btn['style']}" : $style = "btn-default";
    (!empty($btn['size'])) ? $size     = "btn-{$btn['size']}" : $size = "";
    (!empty($btn['data'])) ? $data     = $btn['data'] : $data = "";
    ($btn['disabled'])     ? $disabled = "disabled=\"{$btn['disabled']}\"" : $disabled = "";
    ($btn['block'])        ? $block = "btn-block" : $block = "";
    
    $label = h_createbtnlabel ($btn['label'], $btn['glyph'], $btn['glyphpos']);  // get label  
    
    $bufr.= <<<EOT
    <span data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true" data-title="{$btn['popover']}" data-placement="bottom">
        <button type="button" class="btn btn-link $size $block " style="padding:0px" data-toggle="modal" rel="tooltip" data-original-title="{$btn['popover']}" data-placement="bottom" data-target="#{$btn['id']}Modal" $data>
            <span class="badge $style" style="font-size: 80%">$label</span>
        </button>
    </span>

EOT;
    
    return $bufr;
}

function h_modal($mdl)
{
    $bufr = "";
    $footer = "";

    if(!empty($mdl['footer']))
    {
        $footer = <<<EOT
            <div class="modal-footer">
               {$mdl['footer']} 
            </div>
EOT;
    }
    
    $bufr.= <<<EOT
    <div class="modal fade" id="{$mdl['id']}Modal" tabindex="-1" role="dialog" aria-labelledby="{$mdl['id']}ModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-{$mdl['size']}">
            <div class="modal-content">
                <div class="modal-header bg-{$mdl['style']}-modal">
                   <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">close &times;</span></button>
                   <h4 class="modal-title" id="{$mdl['id']}ModalLabel">{$mdl['title']}</h4>
                </div>
                <div class="modal-body">                
                  {$mdl['body']}
                  <script>
                      $(document).ready(function() {               
                          $("[data-toggle=popover]").popover({trigger: 'hover',html: 'true'}); 
                      });        
                   </script>                
                </div>
                $footer
                <!-- </form> -->
            </div>
        </div>
    </div>
    <script>
         $('#{$mdl['id']}Modal').appendTo("body");
         $('#{$mdl['id']}Modal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget) // Button that triggered the modal
                {$mdl['script']}
          })
    </script>
EOT;
    if (!empty($mdl['scripthide']))
    {
        $bufr.= <<<EOT
        <script>
         $('#{$mdl['id']}Modal').on('hidden.bs.modal', function (event) {
                {$mdl['scripthide']}
          })
        </script>        
EOT;
    }
    
    return $bufr;
    
}


function h_modalform($mdl)
{
    $bufr = "";
    empty($mdl['submitbtn']) ? $submitlbl = "" : $submitlbl = "<span class=\"glyphicon glyphicon-ok\"></span>&nbsp;{$mdl['submitbtn']}";
    empty($mdl['closebtn']) ? $closelbl = "" : $closelbl = "<span class=\"glyphicon glyphicon-remove\"></span>&nbsp;{$mdl['closebtn']}";
    empty($mdl['target']) ? $target = "" : $target = "target=\"{$mdl['target']}\"";
    empty($mdl['onsubmit']) ? $onsubmit = "" : $onsubmit = "onClick=\"{$mdl['onsubmit']}\"";
    empty($mdl['body']) ? $mdl['body'] = "" : $mdl['body'] = $mdl['body'];
    
    
    
    $bufr = <<<EOT
    <div class="modal fade" id="{$mdl['id']}Modal" tabindex="-1" role="dialog" aria-labelledby="{$mdl['id']}ModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-{$mdl['size']}">
            <div class="modal-content">
                <div class="modal-header bg-{$mdl['style']}-modal">
                   <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">close &times;</span></button>
                   <h4 class="modal-title" id="{$mdl['id']}ModalLabel">{$mdl['title']}</h4>
                </div>
                <style type="text/css">
                   #{$mdl['id']}Form  .inputfieldgroup .form-control-feedback,
                   #{$mdl['id']}Form  .selectfieldgroup .form-control-feedback {
                        top: 0;
                        right: -15px;
                    }
                </style>
                <form id="{$mdl['id']}Form" class="form-horizontal" action="{$mdl['action']}" method="post" $target 
                    data-fv-addons="mandatoryIcon"
                    data-fv-addons-mandatoryicon-icon="glyphicon glyphicon-asterisk"
        
                    data-fv-framework="bootstrap"
                    data-fv-icon-valid="glyphicon glyphicon-ok"
                    data-fv-icon-invalid="glyphicon glyphicon-remove"
                    data-fv-icon-validating="glyphicon glyphicon-refresh"
                >
                    <div class="modal-body">                
                      {$mdl['body']}
                      
                      <script>
                          $(document).ready(function() {        
                              $('#{$mdl['id']}Form').formValidation({
                                    excluded: [':disabled'],
                              })                
                    
                              $('#resetBtn').click(function() {
                                 $('#{$mdl['id']}Form').data('bootstrapValidator').resetForm(true);
                              });
        
                              $("[data-toggle=popover]").popover({trigger: 'hover',html: 'true'}); 
                          });        
                       </script>                
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">$closelbl</button>
                        <button type="submit" class="btn btn-{$mdl['style']}" $onsubmit>$submitlbl</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
         $('#{$mdl['id']}Modal').appendTo("body");
         $('#{$mdl['id']}Modal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget) // Button that triggered the modal
                {$mdl['script']}
          })
    </script>
EOT;

    if (!empty($mdl['scripthide']))
    {
        $bufr.= <<<EOT
        <script>
         $('#{$mdl['id']}Modal').on('hidden.bs.modal', function (event) {
                {$mdl['scripthide']}
          })
        </script>       
EOT;
    }
        
    return $bufr;
    
}

// fixme - this can probably be replaced with a different approach - used on pg_race
function h_dialog($mdl)
{
    $bufr = "";
       
    $umtype = strtoupper($mdl['style']);    
    
    # if success messages in mdl array used them - otherwise display what is returned
    if (!empty($mdl['successmsg']))
    {
        $mdl_success = <<<EOT
        if(data === 'success') 
        {
            dialog.setMessage("<div class=\"alert alert-success\" role=\"alert\"><span style=\"font-size: 1.5em\"><b>Success</b></span><br>{$mdl['successmsg']}</div>");{$mdl['successact']}
        }
        else 
        {
            dialog.setMessage("<div class=\"alert alert-danger\" role=\"alert\"><span style=\"font-size: 1.5em\"><b>Sorry</b></span><br>{$mdl['failmsg']}</div>");{$mdl['failact']}
        }
EOT;
    }
    else
    {
        $mdl_success = "dialog.setMessage(data);";
    }
    
    $mdl_error = <<<EOT
<div class=\"alert alert-danger\" role=\"alert\"><span style=\"font-size: 1.5em\"><b>Sorry</b></span><br>{$mdl['errormsg']}</div>{$mdl['erroract']}
EOT;

    if (!$mdl['nosubmitbtn'])  
    {
            $bufr.= <<<EOT
    <script>
      $('#{$mdl['id']}').on('click',function(){
        BootstrapDialog.show({
            title:   '{$mdl['title']}',
            message: '{$mdl['body']}',
            type:     BootstrapDialog.TYPE_$umtype, 
            closable: {$mdl['closeable']}, 
            
            onhide: function(dialogRef) { {$mdl['onhide']}; },
            
            buttons: [
            {
                id:    'close-btn',
                icon:  'glyphicon glyphicon-remove',
                label: '{$mdl['closebtn']}',
                action: function(closebtn) { closebtn.close(); }
            }, 
            {
                id:       'confirm-btn',
                label:    '{$mdl['submitbtn']}',
                icon:     'glyphicon glyphicon-ok',
                cssClass: 'btn-{$mdl['btnstyle']}',
                action: function(dialog) 
                {
                    if(dialog) 
                    {
                       jQuery.ajax({
                          type: "GET",
                          url: "{$mdl['action']}",
                          error:function(msg) { dialog.setMessage("$mdl_error"); },
                          success:function(data){
                              $mdl_success                              
                              setTimeout(function(){ dialog.close(); },  
                              {$mdl['timeout']} );
                          }
                       }); 
                    } 
                 } 
             }]
        });
      });
    </script>
EOT;
    }   
    else
    {
        $bufr.= <<<EOT
    <script>
      $('#{$mdl['id']}').on('click',function(){
        BootstrapDialog.show({
            title:   '{$mdl['title']}',
            message: '{$mdl['body']}',
            type:     BootstrapDialog.TYPE_$umtype, 
            closable: {$mdl['closeable']}, 
            
            onhide: function(dialogRef) { {$mdl['onhide']}; },
            
            buttons: [
            {
                id:    'close-btn',
                icon:  'glyphicon glyphicon-remove',
                label: '{$mdl['closebtn']}',
                action: function(closebtn) { closebtn.close(); }
            }, 
            ]
        });
      });
    </script>
EOT;
    }  
    
    return $bufr;
}


function h_alert($atype, $msgline1, $msgline2)
{
    if ($atype=="success")
    {
        $style = "border-left: solid 5px darkgreen; padding-top: 0px !important; padding-bottom: 0px !important; width: 80%;";
    }
    elseif ($atype=="danger")
    {
        $style = "border-left: solid 5px darkred;  padding-top: 0px !important; padding-bottom: 0px !important; width: 80%;";
    }
    elseif ($atype=="warning")
    {
        $style = "border-left: solid 5px orange;  padding-top: 0px !important; padding-bottom: 0px !important; width: 80%;";
    }

    $bufr = <<<EOT
    <div class="alert alert-$atype margin-top-10" style="$style" role="alert">
        <h3 style="margin: 10px 10px 10px 10px;">$msgline1</h3>
        <p>$msgline2</p>
    </div>
EOT;
    return $bufr;
}

function h_supportteam()
{
    global $lang;
    $bufr = <<<EOT
       <a class="btn btn-default" role="button"  data-content="{$lang['sys']['supportteaminfo']}" data-toggle="popover" data-placement="bottom" href="#" data-original-title="<b>{$lang['sys']['supportteam']}</b>">
            <h4>
                <span class="glyphicon glyphicon-user text-primary" style="font-size: 2em;"></span>
                <span class="glyphicon glyphicon-user text-danger" style="font-size: 2em;"></span>
            </h4>
            {$lang['sys']['supportteam']}
       </a>  
EOT;
     return $bufr;
}


function h_modalHeader($left, $right, $close)
{
    if ($close)
    {
        $cbufr = <<<EOT
            <button type="button" class="close" data-dismiss="modal" style="padding: 5px;">
                <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
            </button>
EOT;
    }
    else
    {
        $cbufr = "";
    }
    
    $mbufr = <<<EOT
    <div class="modal-header" style="padding: 0px 0px 0px 30px;">
        $cbufr
        <div class="row">
            <div class="col-md-7">
                <h3 class="text-primary" id="raceviewModalLabel"><strong>$left</strong></h3>                
            </div>
            <div class="col-md-5">
                <h4 class="pull-right" style="margin-right:20px">
                    $right
                </h4>
            </div>
        </div>   
    </div>                   
EOT;
    return $mbufr;
}

function h_modalFooter($content)
{
    $fbufr = <<<EOT
        <div class="modal-footer" style="padding: 0px 0px 0px 0px;">
            <p>$content</p>
        </div>
EOT;
    return $fbufr;
}

function h_selectcodelist($codelist, $selected="")
{
    $bufr = "";
    foreach ($codelist as $opt)
    {
        $selectstr = "";
        if (($selected=="default" AND $opt['defaultval']) OR  ($selected == $opt['code'])) { $selectstr="selected"; }
        
        $bufr.= "<option value=\"{$opt['code']}\" $selectstr>{$opt['label']}</option>";
    }   
    return $bufr;
}

function h_selectlist($list, $selected="")
{
    $bufr = "";
    foreach ($list as $key=>$opt)
    {
        $selectstr = "";
        if ($opt == $selected) { $selectstr = "selected"; } 
        
        $bufr.= "<option value=\"$key\" $selectstr>$opt</option>";
    }    
    return $bufr;
}

function h_underconstruction($eventid, $title, $msg)
{
    global $lang;
    global $loc;
    
    // document header  ($headbufr)
    include ("$loc/rm_racebox/css/rm_export_classic.php");
    
    $pbufr = <<<EOT
       <div>
       <img style="display: block; margin: auto auto;" src="$loc/common/images/web_graphics/uc_hat_t.png"  alt="under construction" >
       <h3 style="text-align: center">$msg</h3>       
       </div>
       
EOT;
    
    // footer
    $create_date = date("D j M Y H:i");
    $fbufr = <<<EOT
        <br
        <div class="divider clearfix"></div>
        <div class="pull-right"><p><a href="{$_SESSION['sys_website']}">{$_SESSION['sys_name']}</a> $create_date</p></div>
        <br>
EOT;

    // create html object
    $html = new HTMLPAGE($_SESSION['lang']);                      // create html document object
    $html->html_addhtml($headbufr);                               // html header
    $html->html_body("");                                         // body statement
    $html->html_addhtml($pbufr);                                  // page content
    $html->html_addhtml($fbufr);                                  // page footer   
    $bufr = $html->html_render();                                 // return page
    
    return $bufr;
} 

?>