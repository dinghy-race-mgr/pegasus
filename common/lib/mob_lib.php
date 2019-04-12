<?php
/**
 * mob_lib - general function library for mobile interfaces
 * 
 * @function    m_exit        information display if app exits
 * @function    m_pageheader  standard page header  
 * @function    m_exitbutton  button for user to exit  
 *    
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 * 
 */

function m_pageheader($left, $center, $options)
{
    $color = "lightgrey";
    $options_bufr = "";
    if ($options)
    {
        $options_bufr.= <<<EOT
          <div class="pull-right">
              <div class="btn-group">
                  <button type="submit" class="btn btn-link" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                    <span class="glyphicon glyphicon-menu-hamburger" style="color:$color; font-size:1.5em"></span>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-right">
                    <li><a href="#">Sign on</a></li>
                    <li><a href="#">Sign off</a></li>
                    <li><a href="#">Add Boat</a></li>
                    <li role="separator" class="divider"></li>
                    <li><a href="#">Results</a></li>
                  </ul>
              </div>
          </div>
EOT;
    }

    $bufr = <<<EOT
    <div class="container" style="border-bottom: 1px solid white; margin-bottom: 30px">
        <div class="row">
            <div class="col-md-4 col-sm-4 col-xs-4 text-left" style="color: $color">$left</div>
            <div class="col-md-4 col-sm-4 col-xs-4 text-center" style="font-size:1.5em;color: $color"><b>$center</b></div>
            <div class="col-md-4 col-sm-4 col-xs-4 pull-right" style="color: $color" >$options_bufr</div>
        </div>   
    </div>
EOT;

    return $bufr;
}


function m_pagefooter($left, $center, $right)
{
    $color = "lightgrey";
    $bufr = <<<EOT
    <div class="container" style="margin-top: 60px"">
        <div class="row">
            <div class="col-md-4 col-sm-4 col-xs-4 text-left" style="color: $color">$left</div>
            <div class="col-md-4 col-sm-4 col-xs-4 text-center" style="color: $color">$center</div>
            <div class="col-md-4 col-sm-4 col-xs-4 text-right" style="color: $color" >$right</div>
        </div>   
    </div>
EOT;

    return $bufr;
}


function m_button($script, $label, $style="btn-default btn-block")
{
    $button = <<<EOT
        <div class="row margin-top-10">
            <div class="col-xs-8 col-xs-offset-2 col-sm-6 col-sm-offset-3 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4">
                <a href="$script" class="btn $style" role="button"><strong>$label</strong></a>
            </div>
        </div>
EOT;
    return $button;
}

/*function m_exitbutton($label)
{
    $bufr = <<<EOT
    <a href="{$_SESSION['exitlink']}" class="btn btn-danger btn-block" role="button">$label</a>
EOT;
    return $bufr;
}*/

function m_title($title)
{
    $bufr = <<<EOT
    <div class="page-title">
        <div class="col-xs-12 col-xs-offset-0 col-sm-10 col-sm-offset-1 col-md-9 col-md-offset-2">
            <h3><b>$title</b></h3>
        </div>
    </div>
EOT;
    return $bufr;
}

function m_alert($type, $body, $glyph, $dismiss)
{
    $dismiss_bufr = "";
    $dismiss_opt  = "";
    if ($dismiss)
    {
        $dismiss_opt.= "alert-dismissible";
        $dismiss_bufr.= <<<EOT
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><br>
EOT;
    }
    $glyph_bufr = "";
    if (!empty($glyph))
    {
        $glyph_bufr = <<<EOT
        <span class="$glyph" style="font-size: 140%;"></span>
EOT;
    }
    
    $bufr = <<<EOT
    <div class="alert alert-$type $dismiss_opt" role="alert">
        $dismiss_bufr
        <div class="row">
            <div class="col-xs-2 margin-top-20">
                $glyph_bufr
            </div>
            <div class="col-xs-10">
                $body
            </div>               
        </div>
    </div>    
EOT;
    return $bufr;    
}

function m_block($content)
/* standard responsive block */
{
    $bufr = <<<EOT
        <div class="row">
             <div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-6 col-lg-offset-3">
                 $content
             </div>
        </div>
EOT;
    return $bufr;
}

function m_page($loc, $pbufr, $menu = true)
{
    $html = new HTMLPAGE($_SESSION['lang']);
    
    // header
    $html->html_header($loc, "$loc/rm_sailor/css/rm_mobile.css", true, true, 0, ""); 
    
    // body tag
    $html->html_body("class='{$_SESSION['background']}' style='padding-top:20px; padding-bottom: 20px' ");
    
    // open container
    $html->html_addhtml("<div class='container-fluid'>");
    
    // navbar
    $html->html_addhtml(m_pageheader($_SESSION['clubname'], strtoupper($_SESSION['app_name']), $menu));
    
    // body content
    $html->html_addhtml($pbufr);
    
    // close fluid container
    $html->html_addhtml("</div>");
    
    // fixed footer
    // FIXME add club and make floating
    $html->html_addhtml(m_pagefooter($_SESSION['sys_name'], "", $_SESSION['sys_copyright']));

    // scripts
    $html->html_flush();
    
    // render
    $bufr = $html->html_render();
    
    return $bufr;
}


function m_errorpage($loc, $pbufr)
{
    
    require_once ("{$loc}/common/classes/html_class.php"); 
    $html = new HTMLPAGE("en");
    
    // header
    //$html->html_header($loc, "$loc/rm_sailor/css/rm_mobile.css", true, true, 0, ""); 
    $html->html_header($loc, "", false, false,0 ,"");
    
    // body tag
    $html->html_body("class=\" {$_SESSION['background']} \"");
    
    // open container
    $html->html_addhtml("<div class=\"container-fluid\" style=\"margin-top: 40px;\">");
        
    // standard left and right column pag
    $html->html_addhtml($pbufr);
    
    // close container
    $html->html_addhtml("</div>");
    
    // scripts
    $html->html_flush();
    
    // render
    $bufr = $html->html_render();
    
    return $bufr;
}

?>