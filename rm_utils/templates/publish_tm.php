<?php
/*
  html templates for csv import utility
*/


function publish_form($params = array())
{

    if ($params['action'])
    {
        $action_bufr = <<<EOT
        <div class="row margin-top-20">
            <label class="col-sm-3 control-label text-right">Publish or Unpublish</label>                                 
            <div class="col-sm-8">
              <label class="radio-inline"><input type="radio" name="action" value="publish" checked>&nbsp;publish&nbsp;&nbsp;&nbsp;</label>
              <label class="radio-inline"><input type="radio" name="action" value="unpublish" >&nbsp;unpublish&nbsp;&nbsp;&nbsp;</label>                 
            </div>               
        </div>
EOT;
        $submit_label = "Publish / Unpublish";
    }
    else
    {
        $action_bufr = "";
        $submit_label = "Create File";
    }

    $bufr = <<<EOT
    <div class="container" style="margin-top: 40px;">
        <div class="jumbotron">
            <h3 class="text-primary">Instructions:</h3>
            <p class="text-primary">{instructions}</p>
        </div>
        <form enctype="multipart/form-data" id="publishallForm" action="{script}" method="post">
        
            <div class="row form-inline">
                <label class="col-sm-3 control-label text-right">Period (from/to)</label>
                <div class="form-group">                   
                    <div class="col-sm-8">
                        <input type="date" class="form-control" id="date-start"  name="date-start" value="">
                    </div>
                </div>
                <div class="form-group" style="margin-left: 30px !important">                   
                    <div class="col-sm-10">
                        <input type="date" class="form-control" id="date-end" name="date-end" value="">
                    </div>
                </div>
            </div>
            
        $action_bufr    
            
        <div class="row margin-top-20">
            <div class="col-sm-8 col-sm-offset-1">
                <div class="pull-left">
                    <a class="btn btn-lg btn-warning" style="min-width: 200px;" type="button" name="Quit" id="Quit" onclick="return quitBox('quit');">
                    <span class="glyphicon glyphicon-remove"></span>&nbsp;&nbsp;<b>Cancel</b></a>
                </div>
                <div class="pull-right">
                    <button type="submit" class="btn btn-lg btn-primary"  style="min-width: 200px;" >
                    <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;&nbsp;<b>$submit_label</b></button>
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

function publish_report($params = array())
{
    $bufr = "";
    $table_bufr = "";

    if ($params['display']) {
        if (!empty($params['data'])) {
            foreach ($params['data'] as $k => $row) {
                $table_bufr .= "<tr><td>{$row['date']}</td><td>{$row['time']}</td><td>{$row['name']}</td></tr>";
            }
        }
    }

    $params['count'] == 0 ? $txt = "{$params['count']} events selected for publishing - probably because already {action}ed<br><br>" :
        $txt = "{$params['count']} events {action}ed in period selected<br><br>" ;

    $bufr.= <<<EOT
    <div class="alert alert-success" role="alert">
        <h3>Success!</h3> 
        <h4>$txt</h4>
        <div class="row margin-top-20">
            <div class="col-sm-12">
                <div class="pull-right">
                    <a class="btn btn-default" style="min-width: 200px;" type="button" name="Quit" id="Quit" onclick="return quitBox('quit');">
                    <span class="glyphicon glyphicon-chevron-left"></span>&nbsp;&nbsp;<b>Back</b></a>
                </div>
            </div>
        </div>
    
    </div> <! end of alert>

    <p><b>Events {action}ed</b></p>
    <table class="table table-striped table-condensed">
        <tbody>
            $table_bufr
        <tbody>
    </table>

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

function publish_file_report($params = array())
{
    $bufr = "";
    $table_bufr = "";
    $row_bufr = "";
    $created = true;
    $transferred = true;

    if ($params['display'])
    {
        if (!empty($params['data']))
        {
            foreach ($params['data'] as $k=>$row)
            {
                $row_bufr.= "<tr><td>{$row['date']}</td><td>{$row['time']}</td><td>{$row['name']}</td></tr>";
            }
            $table_bufr.= <<<EOT
                <p><b>Events Included&hellip;</b></p>
                <table class="table table-striped table-condensed">
                    <tbody>$row_bufr</tbody>
                </table>
EOT;
        }
    }

    $report = "{$params['count']} events included for selected period <br><br>";

    if ($params['file'])
    {
        $glyph = "glyphicon glyphicon-ok";
        $msg   = "programme file created for use by web display software";
    }
    else
    {
        $glyph = "glyphicon glyphicon-remove";
        $msg   = "programme file creation FAILED";
        $created = false;
    }
    $report.= <<<EOT
    <div style="text-indent: 20px; margin-bottom: 15px"><span class="$glyph" aria-hidden="true"></span> $msg</div>
EOT;

    // echo "<pre>template transfer: {$params['transfer']}  state: {$params['state']}</pre>";

    $text_style = "";
    if ($params['transfer'] == 1 and $params['state'] == 0)
    {
        $glyph = "glyphicon glyphicon-ok";
        $msg   = "programme file transferred to website";
    }
    elseif ( $params['transfer'] == 0 and $params['state'] == 0)
    {
        $glyph = "glyphicon glyphicon-ok";
        $msg   = "programme file transfer to website was NOT requested (or NOT required)";
    }
    elseif ($params['transfer'] > 1 and $params['state'] == 0)
    {
        $reason = array(
            "2" => "transfer session not established",
            "3" => "source file does not exist",
            "4" => "target directory does not exist",
            "5" => "unknown reason",
        );

        $glyph = "glyphicon glyphicon-remove";
        $msg   = "programme file transfer to website FAILED - ".$reason[$params['transfer']];
        $text_style = "text-danger";
        $transferred = false;
    }
    $report.= <<<EOT
    <div class="$text_style" style="text-indent: 20px; margin-bottom: 10px"><span class="$glyph" aria-hidden="true"></span> $msg</div>
EOT;

    $alert = "";
    if (!$created or !$transferred)
    {
        $alert = <<<EOT
        <p><span class="label label-danger">WARNING:  The programme has not been updated</span></p>
EOT;
    }

    $bufr.= <<<EOT
    <div class="jumbotron" >
        <h3>Programme Transfer Report&hellip;</h3> 
        <h4>$report</h4>
        $alert
        <div class="row margin-top-20">
            <div class="col-sm-12">
                <div class="pull-right">
                    <a class="btn btn-default" style="min-width: 200px;" type="button" name="Quit" id="Quit" onclick="return quitBox('quit');">
                    <span class="glyphicon glyphicon-chevron-left"></span>&nbsp;&nbsp;<b>Back</b></a>
                </div>
            </div>
        </div>   
    </div> 
EOT;

    $bufr.=<<<EOT
    $table_bufr

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

function publish_state($params = array())
{
    $start = date("d/m/Y", strtotime($params['args']['date-start']));
    $end = date("d/m/Y", strtotime($params['args']['date-end']));
    if ($params['state'] == 1)
    {
        $bufr = <<<EOT
        <div class="alert alert-warning" role="alert"><h3>Problem!</h3> <h4>no events found for period selected [$start - $end]</h4>
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
        <div class="alert alert-warning" role="alert"><h3>Problem!</h3> <h4> the end date is before the start date [$start - $end]</h4>
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

