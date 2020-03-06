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


function under_construction($params=array())
    /*
     FIELDS
        title  :  title for under construction note
        info   :  detail for under construction note

     PARAMS
        none
     */
{
    if ($params['back_button'])
    {
        $back_button = <<<EOT
        <div class="pull-right margin-top-20">
            <button type="button" class="btn btn-warning btn-lg" onclick="location.href = '{$params['back_url']}';">
                <span class="glyphicon glyphicon-chevron-left"></span>&nbsp;back
        </button>
EOT;
    }
    else
    {
        $back_button = "";
    }

    $html = <<<EOT
        <div class="jumbotron center-block" style="width:60%; margin-top: 60px; background-color: darkgrey">
            <div class="row">
                <div class="col-md-6">
                    <img src="../common/images/web_graphics/uc_hat_t.png" alt="under construction" height="200" width="200">
                </div>
                <div class="col-md-6 text-default">
                    <p><b>{title}</b></p>
                    <p>{info}</p>
                    $back_button
                </div>
            </div>
            <div>&nbsp;</div>
        </div>
EOT;
    return $html;
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
     $bufr = <<<EOT
        <div class="row margin-top-10">
           <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
               <div class="alert alert-danger" role="alert"> 
                    <h1>Sorry ...</h1>
                    <h3>{error}</h3>
                    <h4>{detail}</h4>
                    <hr>
                    <h3 class="text-right">{action}</h3> 
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
        empty($params['change_set']) ? $change_color = "" : $change_color = "red";

        $bufr.= <<<EOT
        <a href="change_pg.php?sailnum={sailnum}&crew={crew}&helm={helm}" class="btn btn-default btn-block btn-md active" role="button">
            <h2 class="pull-left">{class} {sailnum}</h2>
            <h3>{team}</h3>
            <span class="badge pull-right" style="font-size: 120%; background-color: $change_color;">
                <span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;Change Crew / Sail No.&nbsp;&nbsp;
            </span>
        </a>
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

function no_events($params = array())
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
         <div class="rm-text-space margin-left-40">
            <span class="rm-text-md">next race is </span><br>                
            <span class="rm-text-md rm-text-highlight"> $name - $date  $start </span>
         </div>
EOT;
    }

    return $bufr;
}

function list_events($params = array())
{
    $bufr = "";

    // list events
    $event_table = "";
    foreach ($params['details'] as $k => $row)
    {
        if ($row['event_status'] != "cancelled")
        {
            $event_table.= <<<EOT
            <tr>
                <td class="rm-text-md rm-text-trunc" width="50%">{$row['event_name']}</td>
                <td class="rm-text-md" width="20%">{$row['event_start']}</td>
                <td class="rm-text-md" width="40%">{$row['race_name']}</td>
            </tr>
EOT;
        }
        else
        {
            $event_table.= <<<EOT
            <tr>
                <td class="rm-text-md rm-text-trunc" width="50%">{$row['event_name']}</td>
                <td class="rm-text-md text-warning" colspan="2">*** race is CANCELLED ***</td>
            </tr>
EOT;
        }

    }

    $bufr.= <<<EOT
         <h2 class="text-success">{$_SESSION['events']['numevents']} race(s) today</h2>
         <div class="margin-left-40">
             <table class="table" width="100%" style="table-layout: fixed">
                $event_table
             </table>
         </div>
EOT;

    return $bufr;
}

