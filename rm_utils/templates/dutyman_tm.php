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
        <div class="jumbotron" style="margin-top: 20px;">
            <h3 class="text-primary">Instructions:</h3>
            <p class="text-primary"><small>{instructions}</small></p>
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
    //echo "<pre>".print_r($params,true)."</pre>";
    
    $bufr = "";

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
<div>
    <h3>DUTYMAN: creating DUTYMAN export file(s)</h3>	 
</div>
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
        $event_file_bufr = "";
        if ($params['event_file'])
        {
            $event_file_bufr = <<<EOT
<br>
    <p >Event Export: <span class="text-muted"><small>[{eventpath}]</small></span> . . . &nbsp;&nbsp;&nbsp;&nbsp;
        <a href="{eventpath}" download><button type="button" class="btn btn-success pull-right">Download Events</button></a>
    </p>
EOT;
        }

        $duty_file_bufr = "";
        if ($params['duty_file'])
        {
            $duty_file_bufr = <<<EOT
<br>
    <p >Duty Export: <span class="text-muted"><small>[{dutypath}]</small></span> . . . &nbsp;&nbsp;&nbsp;&nbsp;
        <a href="{dutypath}" download><button type="button" class="btn btn-success pull-right">Download Duties</button></a>
    </p>
EOT;
        }

        $rows_bufr = "";
        if ($params['duty_file'])
        {
            $cols_bufr = <<<EOT
            <th>Date</th>
            <th>Time</th>
            <th>Duty</th>
            <th>Event</th>
            <th>Name</th>
            <th>Swappable</th>
            <th>Rota Check</th>
EOT;
            foreach($params['duty_data'] as $row)
            {
                $row['exists'] ? $check = "<span class='glyphicon glyphicon-ok' aria-hidden='true'></span>" : $check = "<span class='glyphicon glyphicon-remove text-danger' aria-hidden='true'></span>";
                $rows_bufr .= <<<EOT
                    <tr style="font-size: 0.8em;">
                        <td>{$row['duty_date']}</td><td>{$row['duty_time']}</td><td>{$row['duty_type']}</td><td>{$row['event']}</td>
                        <td>{$row['first_name']} {$row['last_name']}</td><td>{$row['swappable']}</td><td>$check</td>
                    </tr>
EOT;
            }
        }
        else // only want event information
        {
            $cols_bufr = <<<EOT
            <th>Date</th>
            <th>Event</th>
            <th>Start</th>
            <th>Description</th>
EOT;
            foreach($params['event_data'] as $row)
            {
                 $rows_bufr .= <<<EOT
                    <tr style="font-size: 0.8em;">
                        <td>{$row['date']}</td><td>{$row['event']}</td><td>{$row['start']}</td><td>{$row['description']}</td>
                    </tr>
EOT;
            }
        }

            $bufr .= <<<EOT
<div>
    <h3>DUTYMAN: creating DUTYMAN export file(s)</h3>	 
</div>
<div class="jumbotron" style="font-size: 60% !important">
    <p >Generating duty allocation details from raceManager as a csv file for import to dutyman</p>
    <p >Start Date: <span class="text-info">{start}</span> &nbsp;&nbsp;&nbsp;&nbsp;End Date: <span class="text-info">{end}</span> </p>
    <p >Rotas Requested: <span class="text-info">{rotas}</span></p>
    $event_file_bufr
    <br>
    $duty_file_bufr
    <br>
	<p class="pull-right"><small><i>Using database server [{host}/{database}]</i></small><p>
    </br>
</div>
<table class="table table-hover table-condensed">
    <thead><tr>
        $cols_bufr
    </tr></thead>
    <tbody>
        $rows_bufr
    </tbody>
</table>
EOT;

    }

    return $bufr;
}

function dtm_export_err($params = array())
{
    $bufr = "";

    $params['event_file'] ? $event_req = "YES" : $event_req = "NO";
    $params['duty_file'] ? $duty_req = "YES" : $duty_req = "NO";

    $error_bufr = "";
    foreach($params['errors'] as $error)
    {
        $error_bufr.= <<<EOT
    <p class="rm-text-error" style="padding-left: 40px"> - {$error['msg']}</p>
EOT;
    }

    $bufr.= <<<EOT
    <div class="jumbotron" style="margin-top: 20px;">
        <h3 class="text-primary">Input Error(s) ....</h3>
        $error_bufr
        <br>
        <p class = "text-info"><small>INPUT VALUES: start-date: {start} | end-date: {end} | rotas: {rotas} | event export: $event_req | duties export: $duty_req</small></p>
        <p class = "text-info"><small>SYSTEM: server: {host} | database: {database}</small></p>
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
        <div class="jumbotron" style="margin-top: 20px;">
            <h3 class="text-primary">Instructions:</h3>
            <p class="text-primary"><small>{instructions}</small></p>
        </div>
        <form class = "form-horizontal" enctype="multipart/form-data" id="selectfileForm" 
              action="dtm_import.php?pagestate=submit" method="post">
            <!--div class="form-inline">
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
            </div -->
            <div class="form-group margin-top-20">
                <label for="dutymanfile" class="col-sm-2 control-label">Dutyman Export File</label>
                <div class="col-sm-10">
                    <span class="file-input btn btn-default btn-lg btn-file">
                        <input type="file" accept="csv" style="width:400px !important" id="dutymanfile" name="dutymanfile" value="" required>
                    </span> 
                </div>
            </div>       
            <div class="row margin-top-20">
                <div class="col-sm-8 col-sm-offset-1">
                    <div class="pull-left">
                        <a class="btn btn-lg btn-warning" style="min-width: 200px;" type="button" name="Quit" id="Quit" onclick="history.back()">
                        <span class="glyphicon glyphicon-chevron-left"></span>&nbsp;&nbsp;<b>Back</b></a>
                    </div>
                    <div class="pull-right">
                        <button type="submit" class="btn btn-lg btn-primary"  style="min-width: 200px;" >
                        <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;&nbsp;<b>Synchronise</b></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
EOT;
    return $bufr;
}

function dtm_duty_import_report($params = array())
{
    $bufr = "";
    $mode_bufr = "";

    if ($params['numevents'] == 0)
    {
        $mode_bufr = <<<EOT
<div class="alert alert-danger" style="width: 50%;" role="alert">
    <b>DUTY CHECK </b>: raceManager has no events for the selected period<br>
    Please check the dates you specified<br>  
</div>
EOT;
    }
    else
    {
        $open_close_btn = <<<EOT
<p class="text-right">
    <b>click event for detail</b> or 
    <a class="btn btn-success btn-xs openall" href="#">open all</a> <a class="btn btn-danger btn-xs closeall" href="#">close all</a>
</p> 
EOT;
        if ($params['mode'] == "report")
        {
            $mode_bufr = <<<EOT
<div class="alert alert-info"  role="alert">
    <b>DUTY CHECK </b>: swaps are NOT implemented at this stage<br>
    Planned changes and issues are reported below - events shown in red<br>
    <dl class="dl-horizontal">
        <dt>{$params['swaps']}</dt><dd>duties to be swapped</dd>
        <dt>{$params['missing']}</dt><dd>duty missing in dutyman</dd>
        <dt>{$params['crossswaps']}</dt><dd>requested swap of duty type</dd>
    </dl>  
    $open_close_btn    
</div>
EOT;
        }
        else
        {
            $mode_bufr = <<<EOT
<div class="alert alert-success" role="alert">
    <b>DUTIES UPDATED </b>: swaps have been applied<br>
    <dl class="dl-horizontal">
        <dt>{$params['swaps']}</dt><dd>duties to be swapped - APPLIED</dd>
        <dt>{$params['missing']}</dt><dd>duty missing in dutyman - NOT FIXED</dd>
        <dt>{$params['crossswaps']}</dt><dd>requested swap of duty type - NOT APPLIED</dd>
    </dl>
    $open_close_btn
</div>
EOT;
        }
    }

    $buttons_bufr = "";
    $reminder_bufr = "";
    if ($params['numevents'] > 0)
    {
        if ($params['mode'] == "report" and $params['swaps'] > 0)
        {
            $buttons_bufr = <<<EOT
        <div class="pull-right">
            <a href="dtm_import.php?pagestate=init" class="btn btn-warning btn-sm" style="min-width: 150px;" type="button" name="back" id="back" onclick="history.back()">
            <span class="glyphicon glyphicon-chevron-left"></span>&nbsp;&nbsp;<b>Back</b></a>
            <a href="dtm_import.php?pagestate=apply" class="btn btn-primary btn-sm" style="min-width: 150px; margin-left:50px;" type="button" name="submit" id="submit">
            <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;<b>Apply Changes</b></a>
        </div>
EOT;
        }
        else
        {
            $buttons_bufr = <<<EOT
        <div class="text-center">
            <a href="dtm_import.php?pagestate=init" class="btn btn-warning btn-sm" style="min-width: 150px;" type="button" name="back" id="back"">
            <span class="glyphicon glyphicon-chevron-left"></span>&nbsp;&nbsp;<b>Back</b></a>
        </div>
EOT;
            if ($params['swaps'] > 0)
            {
                $reminder_bufr = <<<EOT
                <div class="text-danger" style="padding: 20px;"><br>
                    <b><span class="lead">Important!</span></b> to make these swaps visible on the website you must use the <b>Update Website</b> option in the racemanager ADMIN system
                </div>
EOT;
            }
        }
    }


    $report_bufr = "";
    if ($params['numevents'] > 0)
    {
        $report_bufr = <<<EOT
<div>
    <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
        {report}
    </div>
</div>
EOT;

    }

    $bufr.= <<<EOT
    <div class="alert alert-warning" role="alert">
        <h3>Dutyman Duty Update</h3> 
        <h4>Updates checked for period {start} to {end}</h4>       
        <div class="row margin-top-20">
            <div class="col-md-7">
            $mode_bufr
            </div>
            <div class="col-md-5">
            $buttons_bufr 
            </div>
            $reminder_bufr
        </div>  
                          
    </div> 
    $report_bufr
    
    <script language="javascript">
    function quitBox(cmd)
    {   
        if (cmd=='quit') { open(location, '_self').close(); }   
        return false;   
    }
    </script>
    
    <script language="javascript">
            $('.closeall').click(function () {
$('.collapse').collapse('hide');
});


$('.openall').click(function () {
        $('.collapse').collapse('show');
})
    </script> 
EOT;

    return $bufr;
}



function dtm_confirm_email_1($params=array())
{

    if ($params['num_unconfirmed'] == 1)
    {
        $txt1 = "Currently {num_unconfirmed} duty has not been confirmed : ";
        $txt2 = "Please could you take a minute now to confirm this duty or request a swap using this link - ";
    }
    else
    {
        $txt1 = "Currently {num_unconfirmed} of your duties have not been confirmed : ";
        $txt2 = "Please could you take a minute now to confirm these duties or request swaps using this link - ";
    }

    $htm = <<<EOT
<!-- first dutyman confirmation reminder email -->
<p>Dear {firstname}</p>
<p>In January we sent an email with details of the duties you have been allocated in {year}, 
with a request to EITHER confirm that you can do the duty OR request a duty swap through
the Dutyman system.  This confirmation process is important to enable our volunteer rota managers to ensure that each race / cruise / event can take place. </p>
<p>$txt1</p>
<p style="padding-left: 30px;"><b>{txt_unconfirmed}</b></p>
<p>$txt2<a href="{dtm_login}" target="_blank"> your Dutyman link</a>. <p>
<p style="padding-left: 30px;">
- the calendar view will underline in red the month(s) you have a duty.<br>  
- click on each underlined month and expand your duty on the day(s) underlined in red. <br> 
- then choose one of the options: to CONFIRM - or request a SWAP.
</p>

<p>Thanks for your help</p>
<p>Simon Greenslade - Commodore</p>
<hr>
<p>If you are having difficultly using Dutyman or organising a swap, please contact the relevant rota manager for your duty<br>
<ul>
    <li>Racebox duties - <a href="https://www.starcrossyc.org.uk/all-club-officers/149-flag-captain" target="_BLANK">Matthew Holmes</a></li>
    <li>Safety Boat/Dinghy Cruise duties - <a href="https://www.starcrossyc.org.uk/all-club-officers/110-training-officer" target="_BLANK">John Allen</a></li>
    <li>Clubhouse Duties - <a href="https://www.starcrossyc.org.uk/all-club-officers/129-galley-bar-duty-coordinator" target="_BLANK">Matthew Tanner</a></li>
</ul>
</p>
EOT;

    return $htm;
}

function dtm_confirm_email_2($params=array())
{

    if ($params['num_unconfirmed'] == 1)
    {
        $txt1 = "Currently {num_unconfirmed} of your duties has not been confirmed : ";
        $txt2 = "Please could you take a minute now to confirm this duty or request a swap using this link - ";
    }
    else
    {
        $txt1 = "Currently {num_unconfirmed} of your duties have not been confirmed : ";
        $txt2 = "Please could you take a minute now to confirm these duties or request swaps using this link - ";
    }
    $htm = <<<EOT
<!-- second dutyman confirmation reminder email -->
<p>Dear  {firstname} </p>
<p>We have sent you a number of reminders regarding the need to review and confirm the duties that you have been allocated.
We need you to access the Dutyman system to EITHER confirm that you can do the duty OR request a duty swap through
the Dutyman system.  Completing this process is essential to enable our volunteer rota managers to ensure that each race / cruise / event can take place. </p>
<p>$txt1</p>
<p style="padding-left: 30px;"><b>{txt_unconfirmed}</b></p>
<p>$txt2<a href="{dtm_login}" target="_blank"> your Dutyman link</a>. <br>
- the calendar view will underline in red the month(s) you have a duty.<br>  
- click on each underlined month and expand your duty on the day(s) underlined in red. <br> 
- then choose one of the options to confirm - OR request a swap.
</p>

<p>Thanks for your help</p>
<p>Simon Greenslade - Commodore</p>

<p>If you are having difficultly using Dutyman or organising a swap, please contact the relevant rota manager for your duty<br>
<ul>
    <li>Racebox duties - <a href="https://www.starcrossyc.org.uk/all-club-officers/149-flag-captain" target="_BLANK">Matthew Holmes</a></li>
    <li>Safety Boat/Dinghy Cruise duties - <a href="https://www.starcrossyc.org.uk/all-club-officers/110-training-officer" target="_BLANK">John Allen</a></li>
    <li>Clubhouse Duties - <a href="https://www.starcrossyc.org.uk/all-club-officers/129-galley-bar-duty-coordinator" target="_BLANK">Matthew Tanner</a></li>
</ul>
</p>
EOT;

    return $htm;
}

function dtm_confirm_rept($params = array())
{
    $num_emails = count($params['data']);

    $data_rows = "";
    foreach ($params['data'] as $k=>$row)
    {
        empty($row['email']['emailto']) ? $email_text = "<span style='color: darkred;'>no email address</span>" : $email_text = $row['email']['emailto'];

        $data_rows.=<<<EOT
        <tr><td style="vertical-align: top;">$k</td>
        <td style="vertical-align: top;">$email_text</td>
        <td style="vertical-align: top; padding-left: 15px;">{$row['detail']}</td></tr>
EOT;
    }

    $htm = <<<EOT
    <h2>Dutyman Confirm Check - Dryrun report</h2>
    <p class="lead">This would generate $num_emails emails</p>
    <hr>
    
    <table class="table-condensed table-bordered table-hover">
    <thead>
    <tr>
        <th>recipient</th>
        <th>email</th>
        <th>duties</th>
    </tr>
    </thead>
    <tbody>
        $data_rows
    </tbody>   
    </table>
    
EOT;

    return $htm;

}

