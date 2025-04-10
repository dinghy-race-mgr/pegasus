<?php

/*
  html templates for PYS export utility
*/

function publish_form($params = array())
{
    if (empty($params['control-files']))
    {
        $bufr = <<<EOT
            <div class="container" style="margin-top: 40px;">
EOT;
    }

    $select_htm = "";
    foreach($params['control-files'] as $file) {
        $select_htm .= "<option value='{$file['url']}'>{$file['name']}</option>";
    }

    $bufr = <<<EOT
    <div class="container" style="margin-top: 40px;">
        <div class="jumbotron">
            <h3 class="text-primary">Instructions:</h3>
            <p class="text-primary"><small>{instructions}</small></p>
        </div>
        <form enctype="multipart/form-data" id="publishallForm" action="{script}" method="post">
        
            <div class="row margin-top-20">
                <label class="col-sm-3 control-label text-right">Select Control File</label>                                 
                <div class="col-sm-8">
                    <select class="form-control" name="control-file">
                        $select_htm
                    </select>               
                </div>               
            </div>           
            
            <div class="row form-inline margin-top-20">
                <label class="col-sm-3 control-label text-right">Define Period (from/to)</label>
                <div class="form-group">                   
                    <div class="col-sm-8">
                        <input type="date" class="form-control" id="start-date"  name="start-date" value="">
                    </div>
                </div>
                <div class="form-group" style="margin-left: 30px !important">                   
                    <div class="col-sm-10">
                        <input type="date" class="form-control" id="end-date" name="end-date" value="">
                    </div>
                </div>
            </div>
             
            <div class="row form-inline margin-top-10" >
                <label class="col-sm-3 control-label text-right">Output File Type</label>
                <div class="col-sm-8">
                    &nbsp;&nbsp;
                    <label class="radio-inline"><input type="radio" name="file-type" value="csv"> CSV </label>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <label class="radio-inline"><input type="radio" name="file-type" checked value="xml"> XML </label>
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
                        <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;&nbsp;<b>Process Data</b></button>
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

function publish_results($params = array())
{
    // starts processing report - completed by command_report and end_report templates
    if ($params['state-error'])
    {
        $hdr = <<<EOT
        <h3> Error in processing request </h3>
EOT;
    }
    else
    {
        $hdr = <<<EOT
        <h3>start processing: <span class="text-info"><b>{$params['name']}</b></span></h3>
        <p>[control file: <i>{$params['file']}</i> ]</p>
EOT;
    }

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
          <body class="{$_SESSION['background']}" style="padding-top:10px; padding-bottom: 10px" >
            <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
                <div class="container-fluid">
                    <h2 class="text-success">{header-left}<span class="pull-right">{header-right}</span></h2>
                </div>
            </nav>
          
            <div class="container-fluid">
            
                <!-- Body -->
                <div class="container" style="margin-bottom: 20px;">
                    <div class="row">
                        <div class="col-md-9">
                            $hdr
                        </div>
                    </div>
                
EOT;

    return $bufr;
}

function command_report($params = array())
{
    $cmd = $params['command'];

    $bufr = <<<EOT
    <div class="row">
    <div class="col-md-9">
    <div class="panel panel-success">
          <div class="panel-heading">
                <h3 class="panel-title">Process: {$cmd['description']}</h3>
          </div>
          <div class="panel-body">
                <p>Summary Results ...&nbsp;&nbsp;[{$cmd['mode']}: {$cmd['attribute']}&nbsp;&nbsp;&nbsp;&nbsp;(start: {$cmd['start_date']} end: {$cmd['end_date']})&nbsp;&nbsp;]</p>
                <table class="table">
                    <thead><tr>
                        <th> EVENTS</th>
                        <th> INDIVIDUAL RACES</th>
                        <th> LOGS / OUTPUT</th>
                    </tr></thead>
                    <br>
                        <td>
                            found: {$cmd['events_found']}</br>
                            processed: {$cmd['events_processed']}
                        </td>
                        <td>
                            found: {$cmd['races_found']}</br>
                            included: {$cmd['races_included']}</br>
                            excluded: {$cmd['races_excluded']}</br>
                            &nbsp;&nbsp;-&nbsp;&nbsp;no entries: {$cmd['races_fail_0']}</br>
                            &nbsp;&nbsp;-&nbsp;&nbsp;pursuit race: {$cmd['races_fail_1']}</br>
                            &nbsp;&nbsp;-&nbsp;&nbsp;< 3 boats: {$cmd['races_fail_2']}</br>
                            &nbsp;&nbsp;-&nbsp;&nbsp;one class: {$cmd['races_fail_3']}</br>
                            &nbsp;&nbsp;-&nbsp;&nbsp;short race: {$cmd['races_fail_4']}</br>
                        </td>
                        <td>
                            <a href="{$cmd['log_link']}" target="_blank">Log File</a><br><br>
                            <a href="{$cmd['datafile_link']}" target="_blank">Review Export File</a><br><br>
                             <a href="{$cmd['datafile_link']}" download>Download Export File</a>
                        </td>
                    </tr>
                </table>
          </div>
    </div>
    </div>
    </div>
EOT;

    return $bufr;
}

function end_report($params = array())
{
    $bufr = <<<EOT

    <div class="row">
        <div class="col-md-9">
            <h3>processing complete</h3>
            <p>You can access the output files through the links above or if you have access to the server directly at <i>{$params['dir']}</i> </p>
        </div>
    </div>

    <!-- Footer -->
    <div class="container" >
        <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 text-left rm-page"></div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 text-center rm-page"></div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 text-right rm-page"></div>
        </div>   
    </div>
    
    </div>

</body>
</html>  
EOT;

    return $bufr;
}

function publish_state($params = array())
{
    $start = date("d/m/Y", strtotime($params['args']['start-date']));
    $end = date("d/m/Y", strtotime($params['args']['end-date']));

    $errmsg = array(
        "1" => "<h3>Problem!</h3> <h4>No events found for period selected [$start - $end]</h4>",
        "2" => "<h3>Failed! </h3> <h4>Page state not recognised - please contact System Manager </h4>",
        "3" => "<h3>Problem!</h3> <h4>Defined start and/or end date, missing or invalid  [$start - $end]</h4>",
        "4" => "<h3>Problem!</h3> <h4>Could not create directory for output files</h4>",
        "5" => "<h3>Problem!</h3> <h4>Unable to read control file - or control file contains no processing commands</h4>",
        "6" => "<h3>Warning!</h3> <h4>Unrecognised completion state - please check output data </h4>",
        "7" => "<h3>Warning!</h3> <h4>Output file type (cvs or xml) not defined </h4>"
    );

    $bufr = "";
    foreach ($params['error'] as $error)
    {
        $error == 3 ? $style = "danger" : $style = "warning";
        $bufr.= "<div class='alert alert-$style' role='alert'>".$errmsg[$error]."</div>";
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


function output_xml($params = array())
{

    $submit_date = date("Y-m-d");
    $submit_time = date("H:i:s");

    $xml = <<<EOT
<?xml version="1.0" encoding="utf-8"?><?xml-stylesheet href="../RYAPY.xsl" type="text/xsl" ?>
<RYAPY xmlns:xs="http://www.w3.org/2001/XMLSchema-instance"
       noNamespaceschemaLocation="http://www.halsraceresults.com/XMLSchemas/RYAPY.xsd">
<admin>
<source>raceManager</source>
<sourcever>11.0</sourcever>
<submittedon>$submit_date</submittedon>
<submittedat>$submit_time</submittedat>
</admin>
<event>
<clubid>{$params['pys_id']}</clubid>
<clubpassword>ryapy</clubpassword>
<clubname>{$params['club']}</clubname>
<eventid>{$params['eventid']}</eventid>
<eventname>{$params['eventname']}</eventname>
</event>
<races>
EOT;

    foreach ($params['data'] as $j=>$race)                             // process each race in results
    {
        $xml.= <<<EOT
<race>
<date>{$race['event_date']}</date>
<raceno>{$race['race-num']}</raceno>
<starts>
EOT;

        foreach ($race['fleets'] as $k=>$fleet)                    // process each start (fleet) in race
        {
            $xml.= <<<EOT
<start>
<name>{$fleet['fleet_name']}</name>
<windspeed>{$race['ws_start']}</windspeed>
<winddir>{$race['wd_start']}</winddir>
<starttime>{$race['event_start']}</starttime>
<entries>
EOT;

                foreach ($fleet['entries'] as $entry)              // process each entry (boat) in fleet
                {

                    $entry['helm'] = htmlentities($entry['helm'], ENT_XML1);
                    $entry['crew'] = htmlentities($entry['crew'], ENT_XML1);
                    $xml.= <<<EOT
                    <entry>
                    <classid>{$entry['class']}</classid>
                    <persons>{$entry['crewnum']}</persons>
                    <category>{$entry['category']}</category>
                    <rig>{$entry['rig']}</rig>
                    <spinnaker>{$entry['spinnaker']}</spinnaker>
                    <keel>{$entry['keel']}</keel>
                    <engine>{$entry['engine']}</engine>
                    <sailno>{$entry['sailnum']}</sailno>
                    <helm>{$entry['helm']}</helm>
                    <crew1>{$entry['crew']}</crew1>
                    <rating>{$entry['pn']}</rating>
                    <elapsed>{$entry['etime']}</elapsed>
                    <corrected>{$entry['atime']}</corrected>
                    <laps>{$entry['lap']}</laps>
                    <rank>{$entry['points']}</rank>
                    </entry>
EOT;
                }

            $xml.= <<<EOT
</entries>
</start>
EOT;
        }

        $xml.= <<<EOT
</starts>    
</race>
EOT;

    }

    $xml.= <<<EOT
</races>
</RYAPY>
EOT;

return $xml;

}

