<?php
/*
  html templates for csv import utility
*/

function dtm_export_form($params = array())
{
    $rota_select = "<option value='all' >ALL ROTAS ...</option>";
    foreach ($params['rotas'] as $code => $label)
    {
        $rota_select.= <<<EOT
        <option value="$code">$label</option>
EOT;
    }

    $bufr = <<<EOT
    <div class="container">
        <div class="jumbotron" style="margin-top: 40px;">
            <h2 class="text-primary">Instructions:</h2>
            <p class="text-primary">{instructions}</p>
        </div>
        <form class = "form-horizontal" enctype="multipart/form-data" id="dtmexportform" action="{script}" method="post">
        
            <div class="row margin-top-20">
                <label class="col-sm-3 control-label text-right">Output Files Required</label>
                <div class="col-sm-8" style="margin-left: 15px;">                   
                    <label class="checkbox-inline"><input type="checkbox" name="event_file" value="1" checked>&nbsp;&nbsp;events&nbsp;&nbsp;&nbsp;
                    </label>
                    <label class="checkbox-inline"><input type="checkbox" name="duty_file" value="1" checked>&nbsp;&nbsp;duties&nbsp;&nbsp;&nbsp;
                    </label>
                </div>
            </div> 
                   
            <div class="row margin-top-20 form-inline">
                <label class="col-sm-3 control-label text-right">Period (from/to)</label>
                <div class="form-group">                   
                    <div class="col-sm-8" style="margin-left: 15px;">
                        <input type="date" class="form-control" id="start"  name="start" value="">
                    </div>
                </div>
                <div class="form-group" style="margin-left: 30px !important">                   
                    <div class="col-sm-10">
                        <input type="date" class="form-control" id="end" name="end" value="">
                    </div>
                </div>
            </div>          
            
            <div class="form-group margin-top-20">
                <label for="rotas" class="col-sm-3 control-label text-right">Select Duty Types</label>
                <div class="col-sm-4 selectfieldgroup">
                    <select multiple class="form-control" name="rotas[]" size="9">
                        $rota_select
                    </select>
                </div>
                <div class="col-sm-3 text-info">Press Ctrl to select multiple options</div>
            </div>         
        
            <div class="row margin-top-20">
                <div class="col-sm-8 col-sm-offset-1">
                    <div class="pull-left">
                        <a class="btn btn-lg btn-warning" style="min-width: 200px;" type="button" name="Quit" id="Quit" onclick="return quitBox('quit');">
                        <span class="glyphicon glyphicon-remove"></span>&nbsp;&nbsp;<b>Cancel</b></a>
                    </div>
                    <div class="pull-right">
                        <button type="submit" class="btn btn-lg btn-primary"  style="min-width: 200px;" >
                        <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;&nbsp;<b>Export</b></button>
                    </div>
                </div>
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

function dtm_export_report($params = array())
{
    $bufr = "";

    // heading
    $bufr.= <<<EOT
<div>
     <h2>DUTYMAN: create DUTYMAN export file(s)</h2>
     
	 
</div>
EOT;

    // file download info / file errors

    if ($params['event_file_status'] != 0 or $params['duty_file_status'] != 0)            // report errors
    {
        $err = array(
            "0" => "file output ok",
            "1" => "could not open output file for writing",
            "2" => "could not write columns into file",
            "3" => "could not write data into file"
        );

        if ($params['event_file_status'] == "not processed")
        {
            $event_status_msg = $params['event_file_status'];
        }
        else
        {
            key_exists($params['event_file_status'], $err) ? $event_status_msg = $err["{$params['event_file_status']}"] : $event_status_msg = "file status not recognised";
        }

        if ($params['duty_file_status'] == "not processed")
        {
            $duty_status_msg = $params['duty_file_status'];
        }
        else
        {
            key_exists($params['duty_file_status'], $err) ? $duty_status_msg = $err["{$params['duty_file_status']}"] : $event_status_msg = "file status not recognised";
        }


        $bufr.= <<<EOT
<div class="jumbotron" style="font-size: 60% !important">
    <h3>SORRY - we have an error in generating the output file(s)</h3>
    <p >Event export file: - <span class="rm-text-error">$event_status_msg</span></p>
    <p >Duty export file&nbsp;: - <span class="rm-text-error">$duty_status_msg</span></p>
    <p >Please contact your System Administrator</p>
</div>
EOT;
    }


    else                                                                              // report inputs and download buttons
    {
        $bufr.= <<<EOT
<div class="jumbotron" style="font-size: 60% !important">
    <p >Generating duty allocation details from raceManager as a csv file for import to dutyman</p>
    <p >Start Date: <span class="rm-text-error">{start}</span> &nbsp;&nbsp;&nbsp;&nbsp;End Date: <span class="rm-text-error">{end}</span> </p>
    <p >Rotas Requested: <span class="rm-text-error">{rotas}</span></p>
    <br>
    <p >Event Export: <span class="rm-text-error"><small>[{eventpath}]</small></span> . . . &nbsp;&nbsp;&nbsp;&nbsp;
        <a href="{eventpath}" download><button type="button" class="btn btn-success pull-right">Download Events</button></a>
    </p>
    <br><br>
    <p >Duty Export: <span class="rm-text-error"><small>[{dutypath}]</small></span> . . . &nbsp;&nbsp;&nbsp;&nbsp;
        <a href="{dutypath}" download><button type="button" class="btn btn-success pull-right">Download Duties</button></a>
    </p>
    </br>
	<p class="pull-right"><small><i>Using database server [{host}/{database}]</i></small><p>
    </br>
</div>
EOT;
    }

    // create duty report content
    $duty_rows_bufr = "";

    foreach($params['duty_data'] as $row)
    {
        $row['exists'] ? $check = "<span class='glyphicon glyphicon-ok' aria-hidden='true'></span>": $check = "<span class='glyphicon glyphicon-remove text-danger' aria-hidden='true'></span>";

        $duty_rows_bufr.= <<<EOT
<tr style="font-size: 0.8em;">
    <td>{$row['duty_date']}</td>
    <td>{$row['duty_time']}</td>
    <td>{$row['duty_type']}</td>
    <td>{$row['event']}</td>
    <td>{$row['first_name']} {$row['last_name']}</td>
    <td>{$row['swappable']}</td>
    <td>$check</td>
</tr>
EOT;

        $duty_bufr = <<<EOT
<table class="table table-hover table-condensed">
    <thead><tr>
        <th>Date</th>
        <th>Time</th>
        <th>Duty</th>
        <th>Event</th>
        <th>Name</th>
        <th>Swappable</th>
        <th>Rota Check</th>
    </tr></thead>
    <tbody>
        $duty_rows_bufr
    </tbody>
</table>
EOT;

    }

    $bufr.= $duty_bufr;

    return $bufr;
}

function dtm_export_err($params = array())
{
    $bufr = "";

    $error_bufr = "";
    foreach($params['errors'] as $error)
    {
        $error_bufr.= <<<EOT
    <p class="rm-text-error" style="padding-left: 40px"> - {$error['msg']}</p>
EOT;
    }

    $bufr.= <<<EOT
    <div class="jumbotron" style="margin-top: 40px;">
        <h2 class="text-primary">Input Error(s) ....</h2>
        $error_bufr
        <br>
        <p style="font-size: 0.9em;">INPUT VALUES: start-date: {start} | end-date: {end} | rotas: {rotas} | event export: {event_file} | duties export: {duty_file}</p>
        <p style="font-size: 0.9em;">SYSTEM: server: {host} | database: {database}</p>
        <br>
        <p>Please use go back and check your inputs ...</p>
    </div>
EOT;
    return $bufr;
}


function dtm_duty_import_form($params = array())
{
    $bufr = <<<EOT
    <div class="container">
        <div class="jumbotron" style="margin-top: 40px;">
            <h2 class="text-primary">Instructions:</h2>
            <p class="text-primary">{instructions}</p>
        </div>
        <form class = "form-horizontal" enctype="multipart/form-data" id="selectfileForm" 
              action="dtm_duty_import.php?pagestate=submit" method="post">
            <div class="form-inline">
                <label class="col-sm-2 control-label">Period (from/to)</label>
                <div class="form-group">                   
                    <div class="col-sm-10">
                        <input type="date" class="form-control" id="start"  name="start" value="" required>
                    </div>
                </div>
                <div class="form-group" style="margin-left: 30px !important">                   
                    <div class="col-sm-10">
                        <input type="date" class="form-control" id="end" name="end" value="" required>
                    </div>
                </div>
            </div>
            <div class="form-group margin-top-20">
                <label for="dutymanfile" class="col-sm-2 control-label">Dutyman Export File</label>
                <div class="col-sm-10">
                    <span class="file-input btn btn-default btn-lg btn-file">
                        <input type="file" accept="csv" style="width:400px !important" id="dutymanfile" name="dutymanfile" value="" required>
                    </span> 
                </div>
            </div>
            <!--
            <div class="form-group margin-top-20">
                <label for="dutytype" class="col-sm-2 control-label">Duty Type</label>
                <div class="col-sm-10">
                    <input type="text" id="dutytype" name="dutytype" value="" >
                </div>
            </div> -->
            <div class="form-group margin-top-20">
                <label for="dryrun" class="col-sm-2 control-label">Operation</label>
                <div class="col-sm-10">
                    <label class="radio-inline"><input type="radio" name="action" value="on" checked>&nbsp;Dry Run&nbsp;&nbsp;&nbsp;</label>
                    <label class="radio-inline"><input type="radio" name="action" value="off" >&nbsp;Make Changes&nbsp;&nbsp;&nbsp;</label>
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
                    <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;&nbsp;<b>Synchronise</b></button>
                </div>
            </div>
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

function dtm_duty_import_report($params = array())
{
    $bufr = "";
    $mode = "";
    if ($params['dryrun'])
    {
        $mode = "[DRYRUN mode: changes not implemented]";
    }

    $bufr.= <<<EOT
    <div class="alert alert-success" role="alert">
        <h3>Dutyman Duty Update</h3> 
        <h4>Updates checked for period {start} to {end}</h4>
        <p>$mode</p>
        <div class="row margin-top-20">
            <div class="col-sm-12">
                <div class="pull-right">
                    <a class="btn btn-default" style="min-width: 200px;" type="button" name="Quit" id="Quit" onclick="return quitBox('quit');">
                    <span class="glyphicon glyphicon-chevron-left"></span>&nbsp;&nbsp;<b>Back</b></a>
                </div>
            </div>
        </div>   
    </div> 
    <div>
        {report}
    </div>
    
EOT;

    return $bufr;
}