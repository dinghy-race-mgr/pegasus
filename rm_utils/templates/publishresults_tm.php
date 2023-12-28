<?php
/*
  html templates for results publishing utility
*/
function publishresults_form($params = array())
{
    //echo "<pre>".print_r($params,true)."</pre>";

    $form_bufr = "";
    foreach ($params['list'] as $file)
    {
        $process_field_name = "process_".$file['code'];
        $transfer_field_name = "transfer_".$file['code'];
        $process_label = "";
        $transfer_label = "";
        $process_disabled = "";
        $params['upload'] ? $transfer_disabled = "" : $transfer_disabled = "disabled";

        if ($file['type'] == "race")
        {
            $field_label = "RACE results";
            $process_disabled = "disabled";
            $process_label = "required";

        }
        elseif ($file['type'] == "series")
        {
            $field_label = "SERIES - ".$file['name'];
        }
        else
        {
            $field_label = "UNKNOWN results";
        }

        $form_bufr.= <<<EOT
        <div class="row form-inline" style="font-size:1.3em">
            <div class="col-sm-5 control-label text-left text-info"><b>$field_label</b></div>
            <div class="col-sm-3">
                  <input type="checkbox" name="$process_field_name" value="1" checked $process_disabled>&nbsp;&nbsp;<small>$process_label</small>
            </div>
            <div  class="col-sm-3">
                <input type="checkbox" name="$transfer_field_name" value="1" checked>&nbsp;&nbsp;<small>$transfer_label</small>
            </div>      
        </div>  
EOT;

    }

    $bufr = <<<EOT
    <div class="container" style="margin-top: 40px;">
        <div class="jumbotron">
            <h3 class="text-primary">Instructions:</h3>
            <p class="text-primary">{instructions}</p>
        </div>
        <form enctype="multipart/form-data" id="publishresultsForm" action="{script}" method="post">
              
        <div class="row form-inline margin-top-10" style="font-size:1.3em">
            <div class="col-sm-5 control-label text-left text-primary">RESULT FILE</div>
            <div class="col-sm-3 control-label text-left text-primary">PROCESS</div>
            <div class="col-sm-3 control-label text-left text-primary" >PUBLISH</div>
        </div>     
        <div class="row" style="margin-top: 5px; margin-bottom: 15px" >
            <div class="col-sm-11" style="height: 6px; background-color: slategrey; border-radius: 5px;"></div>
        </div>
        $form_bufr                         
            
        <div class="row margin-top-20">
            <div class="col-sm-8 col-sm-offset-1">
                <div class="pull-left">
                    <a class="btn btn-lg btn-warning" style="min-width: 200px;" type="button" name="Quit" id="Quit" onclick="return quitBox('quit');">
                    <span class="glyphicon glyphicon-remove"></span>&nbsp;&nbsp;<b>Cancel</b></a>
                </div>
                <div class="pull-right">
                    <button type="submit" class="btn btn-lg btn-primary"  style="min-width: 200px;" >
                    <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;&nbsp;<b>Process</b></button>
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

/*function publishresults_form($params = array())
{
    echo "<pre>".print_r($params,true)."</pre>";

    if (!empty($params['series']))
    {
        $series_bufr = "";
        foreach ($params['series'] as $code => $series)
        {
            $series_bufr.= <<<EOT
            <div class="row form-inline margin-top-10" style="font-size:1.3em">
                <label class="col-sm-3 control-label text-right text-info">Create SERIES Results</label>
                <div class="col-sm-3 col-sm-offset-1">
                    <label class="radio-inline">
                      <input type="radio" name="series_{$series['event_seriescode']}" value="1" checked>&nbsp;&nbsp;yes&nbsp;&nbsp;
                    </label>
                    <label class="radio-inline">
                      <input type="radio" name="series_{$series['event_seriescode']}" value="0">&nbsp;&nbsp;no&nbsp;&nbsp;
                    </label>
                </div>
                <div class="col-sm-5 text-info" >
                    [ series: {$series['seriesname']} ]
                </div>
            </div>
        
EOT;
        }
    }
    else
    {
        $series_bufr = <<<EOT
        <div class="row form-inline margin-top-10" style="font-size:1.3em">
            <label class="col-sm-3 control-label text-right text-muted">Create SERIES Results</label>
            <div class="col-sm-3 col-sm-offset-1 text-muted">
                &nbsp;
                <!-- label class="radio-inline">
                  <input type="radio" name="series_results" value="1" disabled>&nbsp;&nbsp;yes&nbsp;&nbsp;
                </label>
                <label class="radio-inline">
                  <input type="radio" name="series_results" value="0" disabled>&nbsp;&nbsp;no&nbsp;&nbsp;
                </label -->
            </div>
            <div class="col-sm-5 text-muted">
                [ this race is not defined as part of any series ... ]
            </div>
        </div>
EOT;
    }


    $bufr = <<<EOT
    <div class="container" style="margin-top: 40px;">
        <div class="jumbotron">
            <h3 class="text-primary">Instructions:</h3>
            <p class="text-primary">{instructions}</p>
        </div>
        <form enctype="multipart/form-data" id="publishresultsForm" action="{script}" method="post">
                   
        <div class="row form-inline" style="font-size:1.3em">
            <label class="col-sm-3 control-label text-right text-info">Create RACE Results</label>
            <div class="col-sm-4 col-sm-offset-1">
                <label class="radio-inline">
                  <input type="radio" name="race_results" value="1" checked>&nbsp;&nbsp;yes&nbsp;&nbsp;
                </label>
                <label class="radio-inline">
                  <input type="radio" name="race_results" value="0">&nbsp;&nbsp;no&nbsp;&nbsp;
                </label>
            </div>
        </div>  
        
        $series_bufr
        
        <div class="row form-inline margin-top-10" style="font-size:1.3em">
            <label class="col-sm-3 control-label text-right text-info">Post Results to WEBSITE</label>
            <div class="col-sm-4 col-sm-offset-1">
                <label class="radio-inline">
                  <input  type="radio" name="post_results" value="1" checked>&nbsp;&nbsp;yes&nbsp;&nbsp;
                </label>
                <label class="radio-inline">
                  <input type="radio" name="post_results" value="0">&nbsp;&nbsp;no&nbsp;&nbsp;
                </label>
            </div>
        </div>                 
            
        <div class="row margin-top-20">
            <div class="col-sm-8 col-sm-offset-1">
                <div class="pull-left">
                    <a class="btn btn-lg btn-warning" style="min-width: 200px;" type="button" name="Quit" id="Quit" onclick="return quitBox('quit');">
                    <span class="glyphicon glyphicon-remove"></span>&nbsp;&nbsp;<b>Cancel</b></a>
                </div>
                <div class="pull-right">
                    <button type="submit" class="btn btn-lg btn-primary"  style="min-width: 200px;" >
                    <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;&nbsp;<b>Process</b></button>
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

}*/

function process_header($params=array())
{
    $html = <<<EOT
    <!DOCTYPE html><html lang="en">
    <head>
            <title>{title}</title>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="description" content="">
            <meta name="author" content="">

            <link   rel="shortcut icon"    href="{loc}/common/images/favicon.ico">
            <link   rel="stylesheet"       href="{loc}/common/oss/bootstrap341/css/{theme}bootstrap.min.css" >
            <script type="text/javascript" src="{loc}/common/oss/jquery/jquery.min.js"></script>
            <script type="text/javascript" src="{loc}/common/oss/bootstrap341/js/bootstrap.min.js"></script>
            <script type="text/javascript" src="{loc}/common/oss/bs-growl/jquery.bootstrap-growl.min.js"></script>

            <!-- Custom styles for this template -->
            <link href="{stylesheet}" rel="stylesheet">

    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container-fluid">
                <h2 class="text-success">{header-left}<span class="pull-right">{header-right}</span></h2>
            </div>
        </nav>
          
        <h1>Result File Refresh &hellip; <small>click title for detail info</small></h1>
        <div class="row">
        <div class="col-md-offset-2 col-md-7"
        <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
EOT;
    return $html;
}

function publishfooter($params = array())
{
    $bufr = <<<EOT
                </div>        <!-- end of accordian -->
            </div>        <!-- end of cols -->
        </div>        <!-- end of row -->
EOT;

}


function publishresults_item_rpt($params = array())
{

    //echo "<pre>REPORT_ARR - ".print_r($params,true)."</pre>";

    $tab = "{$params['tab']}";
    $action = array( "race" => "Race Results", "series" => "Series Results", "transfer" => "File Transfers" );
    //$actiontxt = $action[$params['action']];

    $righttxt = ucfirst($params['msg'])."&nbsp;&nbsp;&nbsp;&nbsp;";

    if ($params['result'] == "success")
    {
        $panelstyle = "panel-success";
        if ($params['action'] == "race" or $params['action'] == "series")
        {
            $link = $params['file']['url'];
            $righttxt.= <<<EOT
            <a href="$link" id="printrace" type="button" class="btn btn-warning btn-sm" target="_BLANK" style="font-size:1.2em;">
               <span class="glyphicon glyphicon-sunglasses"></span>&nbsp;&nbsp;View
            </a>
EOT;
        }
    }
    elseif ($params['result'] == "fail")
    {
        $panelstyle = "panel-danger";
    }
    elseif ($params['result'] == "info")
    {
        $panelstyle = "panel-info";
    }
    elseif ($params['result'] == "stopped")
    {
        $panelstyle = "panel-warning";
    }
    elseif ($params['result'] == "notrequested")
    {
        $panelstyle = "panel-info";
    }

    $panel_content = "<pre>DETAIL: <br>".print_r($params, true)."</pre>";

    $bufr = <<<EOT
    <div class="panel $panelstyle margin-top-40">
        <div class="panel-heading" role="tab" id="heading$tab" >
          <h1 class="panel-title" style="min-height: 50px;">
                <a class="pull left" style="font-size: 1.6em" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse$tab" aria-expanded="true" aria-controls="collapse$tab">
                  {label}
                </a>
                <span class="pull-right" style="font-size: 1.2em">$righttxt</span>
          </h1>
          
        </div>
        <div id="collapse$tab" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading$tab">
            <div class="panel-body"> $panel_content </div>
        </div>
    </div>
EOT;

    return $bufr;
}

function publishresults_error($params = array())
{

    if ($params['state'] == 1)
    {
        $bufr = <<<EOT
        <div class="alert alert-warning" role="alert"><h3>Problem!</h3> <h4>error state not recognised</h4>
EOT;
    }
    elseif ($params['state'] == 2)
    {
        $bufr = <<<EOT
        <div class="alert alert-danger" role="alert"><h3>Failed!</h3> <h4> page status not recognised - please contact System Manager </h4>
EOT;
    }
    elseif ($params['state'] == 3)
    {
        $bufr = <<<EOT
        <div class="alert alert-warning" role="alert"><h3>Problem!</h3> <h4> event specified [{$params['eventid']}] not found - process stopped</h4>
EOT;
    }
    elseif ($params['state'] == 4)
    {
        $bufr = <<<EOT
        <div class="alert alert-warning" role="alert"><h3>Problem!</h3> <h4> RESULTS UPDATE FAILED - process stopped</h4>
EOT;
    }
    elseif ($params['state'] == 5)
    {
        $bufr = <<<EOT
        <div class="alert alert-warning" role="alert"><h3>Problem!</h3> <h4> FILE TRANSFER FAILED - result inventory file not created - process stopped</h4>
EOT;
    }
    elseif ($params['state'] == 6)
    {
        $bufr = <<<EOT
        <div class="alert alert-warning" role="alert"><h3>Problem!</h3> <h4> FILE TRANSFER FAILED - processing stopped</h4>
EOT;
    }
    else
    {
        $bufr = <<<EOT
        <div class="alert alert-warning" role="alert"><h3>Warning!</h3> <h4> Unrecognised completion state - please contact System Manager </h4>
EOT;
    }

    // add button into div
    $bufr.= <<<EOT
    <div class="row margin-top-20">
        <div class="col-sm-12">
            <div class="pull-right">
                <a class="btn btn-default" style="min-width: 200px;" type="button" name="Quit" id="Quit" onclick="return quitBox('quit');">
                <span class="glyphicon glyphicon-chevron-left"></span>&nbsp;&nbsp;<b>Back</b></a>
            </div>
        </div>
    </div>
    
    </div> <! end of alert>

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
