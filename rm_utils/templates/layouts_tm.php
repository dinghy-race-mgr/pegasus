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
           
            <link rel="icon"          href="{loc}/common/images/logos/favicon.png">           
            <link rel="stylesheet"    href="{loc}/common/oss/bootstrap341/css/{theme}bootstrap.min.css" >      
            <link rel="stylesheet"    href="{loc}/common/oss/bs-dialog341/css/bootstrap-dialog.min.css">
                    
            <script type="text/javascript" src="{loc}/common/oss/jquery/jquery.min.js"></script>
            <script type="text/javascript" src="{loc}/common/oss/bootstrap341/js/bootstrap.min.js"></script>
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
          <body class="" style="padding-top:10px; padding-bottom: 10px" >
            <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
                <div class="container-fluid">
                    <h2 class="text-success">{header-left}<span class="pull-right">{header-right}</span></h2>
                </div>
            </nav>
          
            <div class="container-fluid">
            
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

function script_confirm($params = array())
{
    $bufr = <<<EOT
    <div class="container" style="margin-top: 40px;">
        <div class="jumbotron" style="margin-top: 40px;">
            <h2 class="text-primary">Instructions:</h2>
            <p class="text-primary">{instructions}</p>
        </div>
        <form enctype="multipart/form-data" id="confirmScript" action="{script}" method="post">

        <div class="row margin-top-40">
            <div class="col-sm-8 col-sm-offset-1">
                <div class="pull-left">
                    <a class="btn btn-lg btn-warning" style="min-width: 200px;" type="button" name="Quit" id="Quit" onclick="return quitBox('quit');">
                    <span class="glyphicon glyphicon-remove"></span>&nbsp;&nbsp;<b>Cancel</b></a>
                </div>
                <div class="pull-right">
                     <button type="submit" class="btn btn-lg btn-primary"  style="min-width: 200px;">
                    <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;&nbsp;<b>{confirm}</b></button>
                </div>
            </div>
        </div>
        </form>
    </div>
    <script language="javascript">
    function quitBox(cmd)
    {   
        if (cmd=='quit')
        {
            open(location, '_self').close();
        }   
        return false;   
    }
    </script>
EOT;
    return $bufr;
}





