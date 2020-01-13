<?php
/*
  html templates for csv import utility
*/
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