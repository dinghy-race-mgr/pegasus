<?php
/**
 * html_class.php - class to hold html for output page
 * 
 * basic methods for constructing an html page using the Bootstrap framework
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 * 
 * 
 * @param string $eventid
 * @param 
 * 
 */
     
class HTMLPAGE
{    

    /**
     * HTMLPAGE::__construct()
     * 
     * constructor - adds DOCTYPE and opens html tag
     * 
     * @return
     */
    public function __construct($language)
	{
        $this->pagebufr = "<!DOCTYPE html><html lang=\"$language\">";
        return $this->pagebufr;
	}
    
    /**
     * HTMLPAGE::html_header()
     * 
     * adds header with required bootstrap css and scripts - optionally includes css and scripts
     * for tables and forms
     * 
     * @param string $loc        relative path to top level folder
     * @param string $css_path   relative path from top level folder to local stylesheet
     * @param bool   $forms      true if form css and scripts to be included
     * @param bool   $tables     true if table css and scripts to be included
     * @param int    $refresh    no. of seconds before auto-refresh - 0 for no refresh
     * @param bool   $title      (optional) title to be included on web page 
     * 
     * @return
     */
    public function html_header($loc, $css_path, $forms, $tables, $refresh, $title="")
    {    
		$form_css       = "";  
        $form_script    = "";
        $table_css      = ""; 
        $table_script   = "";  
        $refresh_script = "";

        if ($forms)
        {
            $form_css = <<<EOT
<link rel="stylesheet" href="{$loc}/common/oss/bs-validator/dist/css/formValidation.min.css">       
EOT;
            $form_script = <<<EOT
<script type="text/javascript" src="{$loc}/common/oss/bs-validator/dist/js/formValidation.min.js"></script>
<script type="text/javascript" src="{$loc}/common/oss/bs-validator/dist/js/framework/bootstrap.min.js"></script>
<script type="text/javascript" src="{$loc}/common/oss/bs-validator/dist/js/addons/mandatoryIcon.js"></script>        
EOT;
        }
        
        if ($tables)
        {
            $table_css = <<<EOT
<link rel="stylesheet" href="{$loc}/common/oss/bs-jasny/css/jasny-bootstrap.min.css">        
EOT;
            $table_script = <<<EOT
<script type="text/javascript" src="{$loc}/common/oss/bs-jasny/js/jasny-bootstrap.min.js"></script>
EOT;
}
        if ($refresh > 0)
        {
            $refresh_script = "<meta http-equiv=\"refresh\" content=\"$refresh\">";
        }
                
        // get html for header with options
        $bufr = <<<EOT
          <head>
            <title>$title</title>
            $refresh_script
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="description" content="">
            <meta name="author" content="">
            
 

            
            <link rel="shortcut icon" href="{$loc}/common/images/favicon.ico">             
            <link rel="stylesheet"    href="{$loc}/common/oss/bootstrap/css/bootstrap.min.css" >      
            <link rel="stylesheet"    href="{$loc}/common/oss/bootstrap/css/bootstrap-theme.min.css">
            <link rel="stylesheet"    href="{$loc}/common/oss/bs-dialog/css/bootstrap-dialog.min.css">
            $form_css
            $table_css
                    
            <script type="text/javascript" src="{$loc}/common/oss/jquery/jquery.min.js"></script>
            $form_script
            $table_script
            <script type="text/javascript" src="{$loc}/common/oss/bootstrap/js/bootstrap.min.js"></script>
            <script type="text/javascript" src="{$loc}/common/oss/bs-dialog/js/bootstrap-dialog.min.js"></script>
            <script type="text/javascript" src="{$loc}/common/oss/bs-growl/jquery.bootstrap-growl.min.js"></script>
            <script type="text/javascript" src="{$loc}/common/scripts/clock.js"></script>

            <!-- Custom styles for this template -->
            <link href="$css_path" rel="stylesheet"> 
      
          </head>
             
EOT;
        $this->pagebufr.= $bufr;
        return $this->pagebufr;
    }
    
    public function html_footer($style, $ltext, $mtext, $rtext, $fixed )
    {
        $type = "default";
        if (!empty($style))  { $type = "$style";  }
        
        $mode = "navbar-bottom";    
        if ($fixed) { $mode = "navbar-fixed-bottom"; }
        
        $bufr = <<<EOT
            <div class="navbar navbar-$type $mode">
                <div class="container">
                    <div class="row">
                        <div class="col-md-4 col-sm-4 col-xs-4 navbar-text navbar-left">$ltext</div>
                        <div class="col-md-4 col-sm-4 col-xs-4 navbar-text" style="text-align: center">$mtext</div>
                        <div class="col-md-4 col-sm-4 col-xs-4 navbar-text navbar-right" style="text-align: right">$rtext</div>
                    </div>   
                </div>
            </div>
EOT;
        $this->pagebufr.= $bufr;    
    }
    
    public function html_endscripts($page="")
    {    
		$bufr = "";

        // applies to all pages
		$bufr.= <<<EOT
        <!-- popover activation for all popovers -->
        <script type="text/javascript">
            $(document).ready(function() {
            $("[data-toggle=popover]").popover({trigger: 'hover',html: 'true'});                
            });
        </script>
        
        <!-- tooltip activation for all tooltips -->
        <script type="text/javascript">
            $(document).ready(function() {
            $("[data-toggle=tooltip]").tooltip({trigger: 'hover',html: 'true'});                
            });
        </script>    
EOT;
        
        // keeps panel (fleet) context on relevant pages
        if ($page == "entries" OR $page == "timer" OR $page == "results")
        {
            $bufr.= <<<EOT
            <script type="text/javascript">
            $(function() { 
              $('a[data-toggle="pill"]').on('click', function (e) {
                localStorage.setItem('lastTab', $(e.target).attr('href'));
              });
            
              //go to the latest tab, if it exists:
              var lastTab = localStorage.getItem('lastTab');
            
              if (lastTab) {
                  $('a[href="'+lastTab+'"]').click();
              }
              else {
                  $('a[href="#fleet1"]').click();                
              }
            });
            </script>
EOT;
        }
        $this->pagebufr.= $bufr;
        return $this->pagebufr;
    }

    public function html_body($attr)
    {    
		// get html for header from include file		        
        $this->pagebufr.= "<body $attr>   ";
        return $this->pagebufr;
    }
    
    public function html_addhtml ($html)
    {    
		// add html to page		        
        $this->pagebufr.= $html;
        return $this->pagebufr;
    }
    
    public function html_addinclude ($include)
    {    
		// get html for header from include file
		include ($include);		        
        $this->pagebufr.= $include;
        return $this->pagebufr;
    }
    
    public function html_rmonecolpage($body)
    {
        $bufr = <<<EOT
        <div class="container-fluid" role="main">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    $body
                </div>
            </div>
        </div>
EOT;
        // add html to page
        $this->pagebufr.= $bufr;
        return $this->pagebufr;
    }

    public function html_rmtwocolpage ($left, $right, $leftwidth = 10)
    {
        $rightwidth = 12 - $leftwidth;
        $bufr = "";
        $bufr.= <<<EOT
        <div class="container-fluid" role="main">
            <div class="row">
                <div class="col-md-$leftwidth">
                    $left
                </div>
                <div  class="col-md-$rightwidth"  >
                    <div class="margin-top-20">
                    <div id="sidebar" data-spy="affix"> 
                        $right
                    </div> 
                    </div>
                </div>
            </div>
        </div>
EOT;
        // add html to page		        
        $this->pagebufr.= $bufr;
        return $this->pagebufr;
    }
    
    public function html_render()
    {
        $this->pagebufr.= "</body></html>";
        return $this->pagebufr;
    }
 
 
    public function html_flush()
    {
        $this->pagebufr.= str_repeat("\n",4096);          
        return $this->pagebufr;
    }
         

/*    public function html_modal($modalid, $title, $header, $footer, $close, $include, $reset, $back)
    {
        global $lang;
        
        $bufr = "<div class=\"modal fade\" id=\"$modalid\" data-backdrop=\"static\"><div class=\"modal-dialog\"><div class=\"modal-content\">";
        
        // header
        if ($header)
        {
            $bufr.= "<div class=\"modal-header\">";
            if ($close) { $bufr.= "<button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-hidden=\"true\">&times;</button>"; }                 
            $bufr.= "<h4 class=\"modal-title\">$title</h4></div>";
        }    
        
        // body
        $bufr.= "<div class=\"modal-body\" style=\"padding: 10px 10px 0px 10px;\">";
        include($include);
        $bufr.= "</div>"; 
    
        // the submission function and dialogue
        if ($footer)
        {
            $bufr.= "<div class=\"modal-footer\">";
            if (!empty($reset) and !$formvalid)
            {
                $bufr.= "<input id=\"resetBtn\" value=\"Edit &raquo;\" style=\"width: 120px\" onclick=\"window.location='$reset'\" class=\"btn btn-default\" />";
            }
            if (!empty($back))
            {
                $bufr.= "<input id=\"backBtn\" value=\"Back &raquo;\" style=\"width: 100px\" onclick=\"window.location='$back'\" class=\"btn btn-info\" />";
            }
            $bufr.= "</div>";        
        } 
    
        // close model divs             
        $bufr.= "</div></div></div>";
    
        $this->pagebufr.= $bufr;
    }
    
    public function html_button()
    {
        
    }
    
    function html_dropdown($style, $size, $btitle, $links)
    {
        $bufr = "";
        if ($links)
        {
            if ($style=="") { $style = "default"; }
            
            $bufr.= <<<EOT
            <!-- Split button -->
            <div class="btn-group">
                <button type="button" class="btn btn-$style btn-$size">$btitle</button>
                <button type="button" class="btn btn-$style btn-$size dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">$btitle</span>
                </button>
EOT;
            foreach ($links as $i => $row)
            {
               $bufr.= "<li><a href=\"{$row['url']}\">{$row['label']}</a></li>"; 
            }            
            $bufr.= "</ul></div>";            
        }
        $this->pagebufr.= $bufr;
    }
    
    public function html_panel($style, $header, $htext, $footer, $ftext, $body)
    {
        if ($style=="") { $style = "default"; }
        
        $bufr = "<div class=\"panel panel-$style\">";
        
        if ($header) { $bufr.= "<div class=\"panel-heading panel-title\">$htext</div>"; }
        
        $bufr.= "<div class=\"panel-body\">$body</div>";
        
        if ($footer) { $bufr.= "<div class=\"panel-footer\">$ftext</div>"; }    
        
        $bufr.= "</div>";
        
        $this->pagebufr.= $bufr;
    
    }

    function html_well($size, $body)
    {
        $wellsize = "";
        if ($size=="sm" or $size=="lg") {  $wellsize = "well-$size"; }
        
        $bufr = <<<EOT
           <div class="well $wellsize">
                $body
           </div>
EOT;
        $this->pagebufr.= $bufr;
    }
*/    

    
}	



?>