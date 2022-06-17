<?php
/**
 * General templates for use in rm_sailor application
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
            <link rel="stylesheet"    href="{loc}/common/oss/bootstrap341/css/{theme}bootstrap.min.css" >      
            <link rel="stylesheet"    href="{loc}/common/oss/bs-dialog/css/bootstrap-dialog.min.css">
                    
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
          <body class="{background}" style="padding-top:5px; padding-bottom: 10px" >
            <div class="container-fluid">
            <!-- Header -->
                <div class="container-fluid" style="border-bottom: 1px solid white; margin-bottom: 5px">
                    <div class="row">
                        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 text-left rm-text-md text-warning">{header-left}</div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 text-center rm-text-md text-warning"><span style='letter-spacing: 2px'>{header-center}</span></div>
                        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 text-right rm-text-sm text-warning">{header-right}</div>
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
            
            <!-- quitbox -->
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
          </body>
     </html>        
EOT;

    return $bufr;
}

function restart_switch($params = array())
{

    if (empty($params['eventlist']))
    {
        $restart_url = "rm_sailor.php?mode={$_SESSION['mode']}&usage={$_SESSION['usage']}&demo={$_SESSION['demo']}&event={$_SESSION['event_arg']}";

        $bufr = <<<EOT
        <div class="pull-right">
            <a href="$restart_url" class="rm-text-sm" style="color: white" >
               <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span> &nbsp;restart&nbsp;&nbsp;&nbsp;&nbsp;                       
            </a>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <a href="index.php" class="rm-text-sm" style="color: white" >
               <span class="glyphicon glyphicon-transfer" aria-hidden="true"></span> &nbsp;switch to Cruising App&nbsp;&nbsp;&nbsp;&nbsp;                        
            </a>            
        </div>
EOT;
    }
    else
    {
        $bufr = <<<EOT
        <a class="rm-text-sm pull-right" style="color: white" name="Quit" id="Quit" onclick="return quitBox('quit');">
           <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span> &nbsp;exit                        
        </a>  
EOT;
    }

    return $bufr;
}


function change_fm($params = array())
{
    $label_colour = "text-info";
    $label_width  = "col-xs-2";

    $formbufr = "";
    foreach ($params['change'] as $field => $fieldspec) {

        if (!$fieldspec['status']) { // this field is not configured
            continue;
        }

        $placeholder = "";
        if (array_key_exists("placeholder", $fieldspec)) {
            $placeholder = "placeholder=\"{$fieldspec['placeholder']}\"";
        }
        $value = "{" . $field . "}";

        $formbufr .= <<<EOT
        <div class="form-group form-condensed">
            <label for="$field" class="rm-form-label control-label $label_width $label_colour">{$fieldspec['label']}</label>
            <div class="{$fieldspec['width']}">
                <input name="$field" autocomplete="off" type="text" class="form-control rm-form-input-md placeholder-md" 
                 id="id$field" $placeholder value="$value">
            </div>
        </div>
EOT;
    }

    $bufr = <<<EOT
    <div class="rm-form-style">
    
        <div class="row">     
            <div class="col-xs-10 col-sm-10 col-md-8 col-lg-8 alert alert-info"  role="alert">Change details TEMPORARILY (for an event) ...</div>
        </div>
    
        <form id="changeboatForm" class="form-horizontal" action="change_sc.php" method="post">
    
            $formbufr
            <input name="compid" type="hidden" value="{compid}">
    
            <div class="pull-right margin-top-20">
                <button type="button" class="btn btn-default btn-lg" style="margin-right: 10px" onclick="history.go(-1);">
                    <span class="glyphicon glyphicon-remove"></span>&nbsp;Cancel
                </button>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <button type="submit" class="btn btn-warning btn-lg" >
                    <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;<b>Change Details</b>
                </button>
            </div>
    
        </form>
    </div>
EOT;
    return $bufr;
}


function under_construction($params=array())
{
    if ($params['back_button']) {
        $back_button = <<<EOT
        <div class="pull-right margin-top-20">
            <button type="button" class="btn btn-warning btn-lg" onclick="location.href = '{$params['back_url']}';">
                <span class="glyphicon glyphicon-chevron-left"></span> back
        </button>
EOT;
    } else {
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




function options_hamburger($params = array())
{
    $bufr = "";
    $options = false;

    $opts_bufr = "";
    foreach ($params["options"] as $opt) {
        if ($opt['active']) {
            $options = true;
            $opts_bufr .= <<<EOT
                <li class="rm-text-sm"><a href="{$opt['url']}">{$opt['label']}</a></li>
EOT;
        }
    }
    if ($params['page'] != "search") {
        $opts_bufr .= <<<EOT
        <li class="divider"></li>
        <li class="rm-text-sm"><a href="javascript: history.go(-1)"><span class="glyphicon glyphicon-step-backward"></span> back</a></li>
EOT;
    }


    if ($options)
    {
        $bufr.= <<<EOT
          <div class="pull-right">
              <div class="btn-group" >
                  <button type="submit" class="btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="background-color: transparent;">
                    <span class="glyphicon glyphicon-menu-hamburger rm-text-md"  > </span>
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
    if ($params['restart']) {
    $restart = <<<EOT
        <div class="pull-right" style="padding-right: 20px !important">
            <button type="button" class="btn btn-primary btn-md" onclick="location.href = '{url}';">
                <span class="glyphicon glyphicon-chevron-left"></span> back
            </button>
        </div>
EOT;
} else {
    $restart = "";
}

    $bufr = <<<EOT
    <div class="row margin-top-10">
       <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
           <div class="alert alert-danger" role="alert"> 
                $restart
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
    if ($params['change']) {
        empty($params['change_set']) ? $change_style = "badge-info" : $change_style = "badge-warning";

        if (!empty($params['type']))
        {
            $params['type'] == "race" ? $label = "Change Details" : $label = "Add Details";
        }

        $bufr .= <<<EOT
        <a href="change_pg.php?sailnum={sailnum}&crew={crew}&helm={helm}" class="btn btn-default btn-block btn-md" role="button">
            <p class="rm-text-trunc">
                <span class="rm-text-bg">{class} {sailnum} - </span>
                <span class="rm-text-md">{team}</span>
            </p>
            <span class="badge pull-right $change_style rm-text-sm" >
                <span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;$label&nbsp;&nbsp;
            </span>
        </a>
EOT;
    } else {
        $bufr .= <<<EOT
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
        <div class="rm-text-space"><span class="rm-text-bg">No races today &hellip;</span> </div>
EOT;

    if ($params['nextevent']) {
        $name = $params['nextevent']['event_name'];
        $date = date("jS F", strtotime($params['nextevent']['event_date']));
        $start = "[ {$_SESSION['events']['nextevent']['event_start']} ]";

        $bufr .= <<<EOT
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

    // display for date
    $today = date("Y-m-d");
    $eventday = date("Y-m-d", strtotime($params['eventday']));
    if ($params['numdays'] == 1 and $today == $eventday)                           // event(s) all today
    {
        $when = "today";
    }
    elseif ($params['numdays'] == 1 and strtotime($today) < strtotime($eventday))  // event(s) all on same day in future
    {
        $when = "on ".date("l jS F", strtotime($params['eventday']));
    }
    else                                                                           // multiple days
    {
        $when = "";
    }

    // display for number of races
    $params['numevents'] == 1 ? $num = "1 race" : $num = $params['numevents']." races";

    // list events
    $event_table = "";
    foreach ($params['details'] as $k => $row) {

        if ($row['event_status'] != "cancelled")
        {
            $params['numdays'] <= 1 ? $event_date = "" : $event_date = " - ".date("jS M", strtotime($row['event_date'])) ;

            $event_table .= <<<EOT
            <tr>
                <td class="rm-text-md rm-text-trunc" width="50%">{$row['event_name']}$event_date</td>
                <td class="rm-text-md" width="20%">{$row['event_start']}</td>
                <td class="rm-text-md" width="40%">{$row['race_name']}</td>
            </tr>
EOT;
        } else
        {
            $event_table .= <<<EOT
            <tr>
                <td class="rm-text-md rm-text-trunc" width="50%">{$row['event_name']}</td>
                <td class="rm-text-md text-warning" colspan="2">*** race is CANCELLED ***</td>
            </tr>
EOT;
        }
    }


    $bufr.= <<<EOT
         <h2 class="text-success">$num $when</h2>
         <div>
             <table class="table" width="100%" style="table-layout: fixed">
                $event_table
             </table>
         </div>
EOT;

    return $bufr;
}


function start_menu($params = array())
{
    $menu_bufr = "";
    foreach($params['items'] as $item) {
        $menu_bufr.= <<<EOT
        <div class="col-lg-4 col-lg-offset-1 col-md-4 col-md-offset-1 col-sm-5 col-sm-offset-1 col-xs-5 col-xs-offset-1">
            <a href="{$item['link']}" style="text-decoration: none">
              <div class="panel panel-widgets" style="min-height: 300px; margin-top: 30px; margin-right:30px; font-size: 1.2em; {$item['color']}">
                 <div id=weather>
                    <div class="panel-heading">
                       <h2><span class="{$item['icon']}"></span> {$item['label']}</h2>
                    </div>
                    <div class="panel-body">{$item['text']}</div>
                 </div>
              </div>
            </a>
        </div>
EOT;
    }

    $bufr = <<<EOT
        <div class="row" style="margin-top: 80px;">
            <div class="col-lg-10 col-lg-offset-1 col-md-10 col-md-offset-1 col-sm-10 col-sm-offset-1 col-xs-10 col-xs-offset-2"> 
                <div class="row">
                $menu_bufr
                </div>
            </div>
        </div>
EOT;
    return $bufr;
}


function closed($params = array())
{
    if ($params['opentime'])
    {
        $opentime = "The service is expected to be available at: <b>{$params['opentime']}</b>";
    }
    else
    {
        $opentime = "The service should be back shortly - please check with the club website for more details";
    }

    $type_text = "service";
    if ($params['mode'] == "race")
    {
        $type_text = "[Racing] service";
    }
    elseif ($params['mode'] == "cruise")
    {
        $type_text = "[Leisure Sailing] service";
    }

    $bufr = <<<EOT
        <div class="row" style="margin-top: 80px;">
            <div class="col-lg-6 col-lg-offset-3 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 col-xs-8 col-xs-offset-2">
            
                <div class="panel panel-widgets" style="min-height: 300px; margin-top: 30px; margin-right:30px; 
                        font-size: 1.2em; background-color: #00a2b4 !important; color: #ffffff !important; ">
                        <div class="panel-heading">
                           <h2><span class="glyphicon glyphicon-thumbs-down"></span> &nbsp;&nbsp; Apologies ...</h2>
                        </div>
                        <div class="panel-body lead">
                            <p>The SAILOR $type_text is currently not available</p>
                            <p>$opentime</p>
                        </div>
                </div>
            </div>
        </div>
EOT;
    return $bufr;
}