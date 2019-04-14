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
                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 pull-right rm-page">{header-right}</div>
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
               {boat-label}
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

     $opts_bufr = <<<EOT
        <li class=""><a href="boatsearch_pg.php">Find Boat</a></li>
EOT;
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

 function error_msg($params = array())
 {
     // TBD - see pick boat
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



//function signoff($params=array())
//{
//    $bufr = "";
//    //$bufr = "<pre>state: {$params['state']}</pre>";
//    //$bufr.= "<pre>state: ".print_r($params, true)."</pre>";
//    //$bufr.= "<pre>state: ".print_r($_SESSION['sailor'], true)."</pre>";
//
//    $event_bufr = "";
//    if ($params['state'] == "noevents")     // there are no races today
//    {
//        $event_bufr.= <<<EOT
//             <div class="rm-text-space">
//                <span class="rm-text-bg">No races today &hellip;</span>
//             </div>
//             <div class="rm-text-space">
//                <span class="rm-text-md">next race is : &nbsp; &nbsp;</span>
//                <span class="rm-text-bg rm-text-highlight"> {next-event-name} - {next-event-date} - {next-event-start-time} </span>
//             </div>
//EOT;
//    }
//
//    elseif ($params['state'] == "noentries")    // the sailor hasn't entered any races
//    {
//        $event_bufr .= <<<EOT
//             <div class="rm-text-space">
//                  <span class="rm-text-bg"><b>You have not entered any races today &hellip; </b>
//                        <br>Use the Sign On option to enter
//                  </span>
//             </div>
//EOT;
//    }
//    else      // the sailor has entered at least one event - create table rows for signoff form
//    {
//
//        $event_list = "";
//        foreach ($params['entries'] as $eventid => $e)
//        {
//            $e['protest'] ? $protest_chk = "checked" : $protest_chk = "";
//
//            $position_bufr = "";
//            $declare_bufr = "";
//            $protest_bufr = "";
//
//            if ($e['entered']) {
//                $position_bufr .= "<h4>{$e['position']}</h4>";
//
//                if ($e['declare'] == "declare")
//                {
//                    $declare_bufr = "<h4 style='text-align: center; margin-top: 20px;'>signed off</h4>";
//                }
//                elseif ($e['declare'] == "retire")
//                {
//                    $declare_bufr = "<h4 style='text-align: center; margin-top: 20px;'><b>RTD</b></h4>";
//                }
//                else
//                {
//                    $declare_bufr = <<<EOT
//                    <div >
//                        <label class="radio-inline" >
//                           <input type="radio" name="declare{$eventid}" class=""
//                                  value="declare" checked> <span class="rm-text-md">&nbsp;&nbsp;sign off &nbsp;&nbsp;</span>
//                        </label>
//                        <label class="radio-inline" >
//                           <input type="radio" name="declare{$eventid}" class=""
//                                  value="retire" > <span class="rm-text-md">&nbsp;&nbsp;retire &nbsp;&nbsp;</span>
//                        </label>
//                    </div>
//EOT;
//                }
//
//                if ($params['protest_option'])
//                {
//                    $e['protest'] ? $protest_chk = "checked" : $protest_chk = "";
//                    $protest_bufr = <<<EOT
//                        <div class="checkbox" >
//                           <label>
//                               <input type="checkbox" name="protest{$eventid}" class="rm-form-label" $protest_chk>
//                           </label>
//                        </div>
//EOT;
//                }
//
//
//            }
//            else    // not entered for this race
//            {
//                $position_bufr = "<h4 style='text-align: left;'><i>not entered</i></h4><br>";
//            }
//
//            $event_list .= <<<EOT
//            <tr>
//                <td><h4>{$e['event-name']}</h4></td>
//                <td>$position_bufr</td>
//                <td style="vertical-align: middle;">$declare_bufr</td>
//                <td style="text-align: center; vertical-align: middle;">$protest_bufr</td>
//            </tr>
//EOT;
//        }
//
//        $params['protest_option'] ? $protest_col = "<th style='width: 20%;'>Protest/Redress?</th>" : $protest_col = "";
//
//        $event_bufr.= <<<EOT
//            <form id="confirmform" action="signoff_sc.php" method="post" role="submit" autocomplete="off">
//                <h4>Note:  All reported race positions are provisional &hellip; </h4>
//                <table class="table table-condensed">
//                    <thead><tr class="rm-table-col-title">
//                        <th style="width: 30%;">Race</th>
//                        <th style="width: 15%;">Position</th>
//                        <th style="width: 35%;">Declaration</th>
//                        $protest_col
//                    </tr></thead>
//                    $event_list
//                </table>
//
//                <!-- confirm button -->
//                <div class="row margin-top-10">
//                    <div class="col-md-6 col-md-offset-3">
//                        <button type="submit" class="btn btn-warning btn-block btn-lg" >
//                            <span class="glyphicon glyphicon-ok"></span>
//                            &nbsp;&nbsp;<strong>Confirm Declarations</strong>
//                        </button>
//                    </div>
//                </div>
//
//            </form>
//EOT;
//    }
//
//
//    // put page together
//    $bufr.= <<<EOT
//     <!-- boat details -->
//     <div class="row">
//        <div class="col-xs-12 col-xs-offset-0 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-10 col-lg-offset-1">
//            <div class="list-group list-group-item list-group-item-info">
//                <h3>{class} {sailnum}</h3>
//                <h4>{team}</h4>
//            </div>
//        </div>
//     </div>
//
//     <!-- events -->
//     <div class="row margin-top-20">
//          <div class="col-xs-12 col-xs-offset-0 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-10 col-lg-offset-1">
//            <table class="table table-condensed">
//                $event_bufr
//            </table>
//          </div>
//
//     </div>
//EOT;
//
//    return $bufr;
//}
//
//
//function signoff_race_confirm($params = array())
//{
//    if ($params['declare'] == "declare")
//    {
//        $declaration = "<span class='glyphicon glyphicon-ok rm-glyph-bg'  aria-hidden='true'></span>";
//    }
//    elseif ($params['declare'] == "retire")
//    {
//        $declaration = "<span style='color: darkred; font-size:1.5em'>RTD</span>";
//    }
//    else
//    {
//        $declaration = $params['declare'];
//    }
//
//    $params['protest'] ? $protest = "protest notified" : $protest = "" ;
//
//    $bufr = <<<EOT
//        <tr>
//            <td><h4>{name}</h4></td>
//            <td><h4>{position}</h4></td>
//            <td><h4>$declaration</h4></td>
//            <td><h4>$protest</h4></td>
//        </tr>
//EOT;
//return $bufr;
//}
//
//
//function signoff_confirm($params=array())
//{
//    $bufr = "";
//    if ($params['complete'])
//    {
//        $confirm_msg = <<<EOT
//        <div class="row margin-top-10">
//           <div class="col-xs-12 col-xs-offset-0 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-10 col-lg-offset-1">
//               <div class="alert alert-success rm-text-md" role="alert">
//                  All done . . . thanks<br>If you want to change your declaration - select the signoff option again.
//               </div>
//           </div>
//        </div>
//EOT;
//    }
//    else
//        $confirm_msg = <<<EOT
//        <div class="row margin-top-10">
//           <div class="col-xs-12 col-xs-offset-0 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-10 col-lg-offset-1">
//               <div class="alert alert-danger rm-text-md" role="alert">
//                    There was a problem with your race entry<br> . . . please contact the race officer
//               </div>
//           </div>
//        </div>
//EOT;
//
//    $bufr.=<<<EOT
//     <!-- boat details -->
//     <div class="row">
//        <div class="col-xs-12 col-xs-offset-0 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
//            <div class="list-group">
//                <h2>{class} {sailnum}</h2>
//                <h4><span style="font-size: 110%">{team}</span></h4>
//            </div>
//        </div>
//     </div>
//
//     <!-- events -->
//     <div class="row margin-top-10">
//          <div class="col-xs-12 col-xs-offset-0 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-10 col-lg-offset-1"">
//            <table class="table table-condensed">
//                {event-list}
//            </table>
//          </div>
//     </div>
//
//     <!-- confirm button -->
//     $confirm_msg
//EOT;
//
//    return $bufr;
//}

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


?>