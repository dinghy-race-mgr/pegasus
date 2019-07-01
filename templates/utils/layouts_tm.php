<?php
/*
 * html layouts for util applications
 *
 */

/*
 * Main page template with defined header and footer
 */
 function basic_page($params = array())
 {
     $bufr = <<<EOT
     <!DOCTYPE html>
     <html lang="en">
          <head>
            <title>{title}</title>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="description" content="">
            <meta name="author" content="">

            
            <link rel="shortcut icon" href="{loc}/common/images/favicon.ico">             
            <link rel="stylesheet"    href="{loc}/common/oss/bootstrap/css/bootstrap.min.css" >      
            <link rel="stylesheet"    href="{loc}/common/oss/bootstrap/css/bootstrap-theme.min.css">
            <link rel="stylesheet"    href="{loc}/common/oss/bs-dialog/css/bootstrap-dialog.min.css">
                    
            <script type="text/javascript" src="{loc}/common/oss/jquery/jquery.min.js"></script>
            <script type="text/javascript" src="{loc}/common/oss/bootstrap/js/bootstrap.min.js"></script>
            <script type="text/javascript" src="{loc}/common/oss/bs-dialog/js/bootstrap-dialog.min.js"></script>
            <script type="text/javascript" src="{loc}/common/oss/bs-growl/jquery.bootstrap-growl.min.js"></script>
            <script type="text/javascript" src="{loc}/common/scripts/clock.js"></script>
            
            <!-- forms -->
            <link rel="stylesheet" href="{loc}/common/oss/bs-validator/dist/css/formValidation.min.css">
            <script type="text/javascript" src="{loc}/common/oss/bs-validator/dist/js/formValidation.min.js"></script>
            <script type="text/javascript" src="{loc}/common/oss/bs-validator/dist/js/framework/bootstrap.min.js"></script>
            <script type="text/javascript" src="{loc}/common/oss/bs-validator/dist/js/addons/mandatoryIcon.js"></script>

            <!-- Custom styles for this template -->
            <link href="{stylesheet}" rel="stylesheet"> 
      
          </head>
          <body class="{$_SESSION['background']}" style="padding-top:10px; padding-bottom: 10px" >
            <div class="container-fluid">
            <!-- Header -->
                <div class="container" style="border-bottom: 1px solid white; margin-bottom: 10px">
                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 text-left rm-pagebold">{header-left}</div>
                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 text-center rm-pagebold">{header-center}</div>
                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 text-right rm-pagebold">{header-right}</div>
                    </div>   
                </div>
            
                <!-- Body -->
                <div class="container" style="margin-bottom: 20px; min-height: 400px;">
                {body}
                </div>
                
                <!-- Footer -->
                <div class="container" >
                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 text-left rm-page">{footer-left}</div>
                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 text-center rm-page">{footer-center}</div>
                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 text-right rm-page">{footer-right}</div>
                    </div>   
                </div>

            </div>
            
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
          </body>
     </html>        
EOT;

    return $bufr;
 }


 function options($params = array())
 {
     $bufr = <<<EOT
       <!--<div class="row">-->
           <!--<div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">   -->
               <!--{no-events}-->
           <!--</div>-->
       <!--</div>-->
       <div class="row">
           <div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">   
               {object-label}
           </div>
       </div>
EOT;

     // options
     foreach ($params as $k => $arr)
     {
         $bufr.= <<<EOT
        <div class="row margin-top-10">
            <div class="col-xs-8 col-xs-offset-2 col-sm-6 col-sm-offset-3 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4">
                <a href="{$arr['url']}" class="btn btn-warning btn-block btn-lg" role="button"><strong>{$arr['label']}</strong></a>
            </div>
        </div>
EOT;
     }

     return $bufr;
 }



 function options_hamburger($params = array())
     /*
      * Produces hamburger options menu for use in header with a customisable options list
      */
 {
     $bufr = "";
     $options = false;

     $opts_bufr = "";
     foreach ($_SESSION['option_cfg'] as $func => $opt)
     {
         if ($opt['active'])
         {
             $options = true;
             $opts_bufr .= <<<EOT
                <li class=""><a href="{$opt['url']}">{$opt['label']}</a></li>
EOT;
         }
     }

     if ($options)
     {
         $bufr.= <<<EOT
          <div class="pull-right">
              <div class="btn-group">
                  <button type="submit" class="btn btn-link" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                    <span class="glyphicon glyphicon-menu-hamburger rm-hamburger" > </span>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-right">
                    $opts_bufr
                  </ul>
              </div>
          </div>
EOT;
     }

     return $bufr;
 }

