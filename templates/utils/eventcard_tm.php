<?php
/*
  html templates for csv import utility
*/

function event_card($params = array())
{
// essentially two layouts - if no duties, notes go in separate column - otherwise go under even name

//    echo "<pre>".print_r($params['constraints'], true)."</pre>";

    // decide which display format to use
    $duties_inc = false;
    if ($params['constraints']['race_duty'] or $params['constraints']['safety_duty'] or
        $params['constraints']['club_duty'])  { $duties_inc = true; }

    $params['constraints']['tide'] ? $th_tide = "<th class=\"pull-left\" style='width: 15%'>{$params['fields']['tide']}</th>" : $th_tide = "";
    $params['constraints']['notes'] ? $th_notes = "<th class=\"pull-left\" style=\"width: 30%\">{$params['fields']['notes']}</th>" : $th_notes = "";

    if ($duties_inc)  // layout with duties
    {
        $th_duties = "";
        if ($params['constraints']['race_duty'])   { $th_duties.= "<th class='pull-left' style='width: 15%'>{$params['fields']['race_duty']}</th>"; }
        if ($params['constraints']['safety_duty']) { $th_duties.= "<th class='pull-left' style='width: 15%'>{$params['fields']['safety_duty']}</th>"; }
        if ($params['constraints']['club_duty'])   { $th_duties.= "<th class='pull-left' style='width: 15%'>{$params['fields']['club_duty']}</th>"; }


        $table_hdr_bufr = <<<EOT
            <th class="pull-left" style="width: 8%">{$params['fields']['date']}</th>
            <th class="pull-left" style="width: 8%">{$params['fields']['time']}</th>
            <th class="pull-left" style="width: 25%">{$params['fields']['event']}</th>          
            $th_tide
            $th_duties
EOT;
        $table_data_bufr = "";
        foreach($params['data'] as $k => $row)
        {
            $duty_bufr = "";
            if ($params['constraints']['race_duty'])   { $duty_bufr .= "<td>".str_replace("|", "<br>", $row['race_duty'])."</td>"; }
            if ($params['constraints']['safety_duty']) { $duty_bufr .= "<td>".str_replace("|", "<br>", $row['safety_duty'])."</td>"; }
            if ($params['constraints']['club_duty'])   { $duty_bufr .= "<td>".str_replace("|", "<br>", $row['club_duty'])."</td>"; }

            $params['constraints']['tide'] ? $tide_bufr = "<td>{$row['tide']}</td>" : $tide_bufr = "";
            $params['constraints']['notes'] ? $notes_bufr = "<br><i><span class=\"text-grey\">{$row['notes']}</span></i>" : $notes_bufr = "";

            $table_data_bufr .= <<<EOT
                <tr>
                    <td>{$row['date']}</td>
                    <td>{$row['time']}</td>
                    <td>{$row['event']}$notes_bufr</td>
                    $tide_bufr
                    $duty_bufr
                </tr>
EOT;
        }
    }
    else    // layout with no duties
    {

        $table_hdr_bufr = <<<EOT
            <th class="pull-left" style="width: 15%">{$params['fields']['date']}</th>
            <th class="pull-left" style="width: 10%">{$params['fields']['time']}</th>
            <th class="pull-left" style="width: 40%">{$params['fields']['event']}</th>          
            $th_tide
            $th_notes
EOT;


        $table_data_bufr = "";
        foreach($params['data'] as $k => $row)
        {
            $params['constraints']['tide'] ? $tide_bufr = "<td>{$row['tide']}</td>" : $tide_bufr = "";
            $params['constraints']['notes'] ? $notes_bufr = "<td><i>{$row['notes']}</i></td>" : $notes_bufr = "";
            $table_data_bufr.= <<<EOT
                <tr>
                    <td>{$row['date']}</td>
                    <td>{$row['time']}</td>
                    <td>{$row['event']}</td>
                    $tide_bufr
                    $notes_bufr
                </tr>
EOT;
        }
    }

    $bufr = <<<EOT
     <!DOCTYPE html>
     <html lang="en">
     <head>
        <title>{title}</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="racemanager">
        <meta name="author" content="mark elkington">
        
        <meta http-equiv="cache-control" content="max-age=0" />
        <meta http-equiv="cache-control" content="no-cache" />
        <meta http-equiv="expires" content="0" />
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        <meta http-equiv="pragma" content="no-cache" />

 
        <style>
           body    {font-family: Kalinga, verdana,sans-serif; font-size: 0.8em;}
           h1      {font-family: Kalinga,arial,helvetica,sans-serif; font-size: 250%; letter-spacing: +2px; color: rgb(44, 76, 124);}
           h2      {font-family: Kalinga,arial,helvetica,sans-serif; font-size: 200%; color: rgb(44, 76, 124);}
           h3      {font-family: Kalinga,arial,helvetica,sans-serif; font-size: 150%; color: rgb(194, 0, 0);}
           p       {font-family: Kalinga,arial,helvetica,sans-serif; font-weight: normal; color: rgb(0, 0, 0); line-height: 1.2em; padding-bottom: 0.2em;}
           
           td      {display: table-cell; vertical-align: top; font-size: 0.9em;}
           a:link  {color: rgb(44, 76, 124); text-decoration: none;}
            
           .title  {font-family: Kalinga,arial,helvetica,sans-serif; font-weight: bold; font-size: 250%; letter-spacing: -1px; color: rgb(44, 76, 124);}
           .title2 {font-family: Kalinga,arial,helvetica,sans-serif; font-weight: bold; font-size: 200%; color: rgb(44, 76, 124); padding-top: 10px}
            
           .divider   {border-bottom: 1px solid rgb(204, 204, 204); margin-top: 5px; margin-bottom: 5px;}
           .clearfix  {display: block;}
           
           .note    {font-weight: normal; color: rgb(0, 0, 0); line-height: 1.2em; margin: 0px; }
            
           .text-center {text-align: center;}
           .text-right {text-align: right; padding-right: 15px; }
           .text-left {text-align: left; padding-left: 15px; }
           .text-grey   {color: rgb(119, 119, 119);}
           .text-alert  {color: rgb(200, 76, 44)}
            
           .pull-right  {text-align: right;}
           .pull-left   {text-align: left;}
           .pull-center {text-align: center;}
 
           .noshade   {background: none repeat scroll 0% 0% rgb(255, 255, 255);}
           .lightshade{background: none repeat scroll 0% 0% rgb(238, 238, 238);}
           .darkshade {background: none repeat scroll 0% 0% rgb(153, 153, 153);}
           
           @media all {
	           .page-break	{ display: none; }
           }

           @media print {
               .noprint { display:none }
               .page-break	{ display: block; page-break-before: always; }
           }
         
        </style>   
      </head> 
      <body>
          
        <div class="container-fluid">
        
            <!-- Body -->
            <div class="container" >
            {header}
            </div>
            
            <div class="container" >
                <table>
                    <thead><tr>
                        $table_hdr_bufr
                    </tr></thead>
                    <tbody>
                        $table_data_bufr
                    </tbody>
                </table>
            </div>
            
            <div class="container" >
            {footer}
            </div>

        </div>
           
      </body>
     </html>        
EOT;

    return $bufr;
}

function event_card_form($params = array())
{
    $bufr = <<<EOT
    <div class="container" style="margin-top: 40px;">
        <div class="jumbotron">
            <h3 class="text-primary">Instructions:</h3>
            <p class="text-primary">{instructions}</p>
        </div>
        <form enctype="multipart/form-data" id="eventcardForm" action="{script}" method="post">
            <div class="row form-inline">
                <label class="col-sm-3 control-label text-right">Period (from/to)</label>
                <div class="form-group">                   
                    <div class="col-sm-8">
                        <input type="date" class="form-control" id="date-start"  name="date-start" value="" required>
                    </div>
                </div>
                <div class="form-group" style="margin-left: 30px !important">                   
                    <div class="col-sm-10">
                        <input type="date" class="form-control" id="date-end" name="date-end" value="" required>
                    </div>
                </div>
            </div>
            
            <div class="row margin-top-20">
                <label class="col-sm-3 control-label text-right">Include ?</label>                         
                <div class="col-sm-8">
                  <label class="checkbox-inline"><input type="checkbox" name="tide" value="true" checked>&nbsp;Tide&nbsp;&nbsp;&nbsp;</label>
                  <label class="checkbox-inline"><input type="checkbox" name="notes" value="true" checked>&nbsp;Notes&nbsp;&nbsp;&nbsp;</label>               
                  <label class="checkbox-inline"><input type="checkbox" name="race_duty" value="true" checked>&nbsp;Racebox Duties&nbsp;&nbsp;&nbsp;</label>
                  <label class="checkbox-inline"><input type="checkbox" name="safety_duty" value="true" checked>&nbsp;Safety Duties&nbsp;&nbsp;&nbsp;</label>
                  <label class="checkbox-inline"><input type="checkbox" name="club_duty" value="true" checked>&nbsp;Clubhouse Duties&nbsp;&nbsp;&nbsp;</label>                               
                </div>             
            </div>
            
            <div class="row margin-top-20">
                <label class="col-sm-3 control-label text-right">Include Unpublished Events</label>                                 
                <div class="col-sm-8">
                  <label class="radio-inline"><input type="radio" name="scope" value="0" >&nbsp;yes&nbsp;&nbsp;&nbsp;</label>
                  <label class="radio-inline"><input type="radio" name="scope" value="1" checked>&nbsp;no&nbsp;&nbsp;&nbsp;</label>                 
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
                    <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;&nbsp;<b>Create</b></button>
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

function eventcard_state($params = array())
{
    if ($params['state'] == 1)
    {
        $bufr = <<<EOT
        <div class="alert alert-warning" role="alert"><h3>Problem!</h3> <h4>no published events found for period selected</h4>
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
        <div class="alert alert-warning" role="alert"><h3>Problem!</h3> <h4> the end date is before the start date</h4>
EOT;

    }
    else
    {
        $bufr = <<<EOT
        <div class="alert alert-warning" role="alert"><h3>Warning!</h3> <h4> Unrecognised completion state - please check rota lists </h4>
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

