<?php
/*
 * html layouts for rm_sailor application
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
            <link rel="stylesheet"    href="{loc}/common/oss/bootstrap/css/{theme}bootstrap.min.css" >      
            <!-- <link rel="stylesheet"    href="{loc}/common/oss/bootstrap/css/bootstrap-theme.min.css"> -->
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
                <div class="container-fluid" style="border-bottom: 1px solid white; margin-bottom: 10px">
                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 text-left rm-pagebold">{header-left}</div>
                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 text-center rm-pagebold">{header-center}</div>
                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 text-right">{header-right}</div>
                    </div>   
                </div>
            
                <!-- Body -->
                <div class="container-fluid" style="margin-bottom: 20px; min-height: 400px;">
                {body}
                </div>
                
                <!-- Footer -->
                <div class="container-fluid" >
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

     if ($params['numevents'] != 0)
     {
         $msg = "{$params['numevents']} race(s) today";
     }
     else
     {
         $msg = "No races today";
     }

     $bufr = <<<EOT
        <div class="row">
            <div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">   
               {boat-label}
            </div>
        </div>
        <div class="row">
            <div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2"> 
                <h3>$msg</h3>
            </div>
        </div>
EOT;

     // options
     foreach ($params['options'] as $k => $opt)
     {
         if ($opt['active'])
         {
            $bufr.= <<<EOT
            <div class="row margin-top-10">
                <div class="col-xs-8 col-xs-offset-2 col-sm-6 col-sm-offset-3 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4">
                    <a href="{$opt['url']}" class="btn btn-warning btn-block btn-lg rm-text-bg" role="button" 
                       data-toggle="tooltip" data-placement="right" title="{$opt['tip']}">
                       <strong>{$opt['label']}</strong></a>
                </div>
            </div>
EOT;
         }
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
    foreach ($params["options"] as $opt)
    {
        if ($opt['active'])
        {
            $options = true;
            $opts_bufr .= <<<EOT
                <li class="rm-text-bg"><a href="{$opt['url']}">{$opt['label']}</a></li>
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

 function error_msg($params = array())
 {
     // TBD - see pick boat
     $bufr = <<<EOT
        <div class="row margin-top-10">
           <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
               <div class="alert alert-danger" role="alert"> 
                    <h1>Sorry ...</h1>
                    <h3>{error}</h3>
                    <h4>{detail}</h4>
                    <hr>
                    <h3 class="pull-right">{action}</h3> 
               </div>
           </div>
        </div>
EOT;
     return $bufr;
}

function boat_label($params = array())
{
    $bufr = "";
    // boat details
    if ($params['change'])
    {
        $bufr.= <<<EOT
        <div class="list-group">
            <a href="change_pg.php?sailnum={sailnum}&crew={crew}&helm={helm}" class="list-group-item list-group-item-info ">
                <h3>{class} {sailnum}</h3>
                <h4><span style="font-size: 110%">{team}</span></h4>
                <span class="badge progress-bar-danger" style="font-size: 120%">
                    <span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;Change&nbsp;&nbsp;
                </span>
            </a>
        </div>
EOT;
    }
    else
    {
        $bufr.= <<<EOT
             <div class="list-group">
                 <div class="list-group-item list-group-item-info ">
                    <h3>{class} {sailnum}</h3>
                    <h4><span style="font-size: 110%">{team}</span></h4>
                 </div>
            </div>
EOT;
    }

    return $bufr;
}

function noevents($params = array())
{
    $bufr = <<<EOT
         <div class="rm-text-space">
            <span class="rm-text-bg">No races today &hellip;</span> 
         </div>
EOT;

    if ($params['nextevent'])
    {
        $name  = $params['nextevent']['event_name'];
        $date  = date("jS F",strtotime($params['nextevent']['event_date']));
        $start = "[ {$_SESSION['events']['nextevent']['event_start']} ]";

        $bufr.= <<<EOT
         <div class="rm-text-space">
            <span class="rm-text-md">next race is </span><br>                
            <span class="rm-text-md rm-text-highlight"> $name - $date  $start </span>
         </div>
EOT;
    }

    return $bufr;
}

