<?php
/*
  html templates for csv import utility
*/
function upload_pmaker_file($params = array())
{
    $bufr = <<<EOT
    <div class="container">
        <div class="jumbotron" style="margin-top: 40px;">
            <h2 class="text-primary">Instructions:</h2>
            <p class="text-primary">{instructions}</p>
        </div>
        <form class = "form-horizontal" enctype="multipart/form-data" id="selectfileForm" action="programmemaker.php?pagestate=submit" method="post">
            <div class="form-inline">
                <label class="col-sm-2 control-label">Period (from/to)</label>
                <div class="form-group">                   
                    <div class="col-sm-10">
                        <input type="date" class="form-control" id="date-start"  name="date-start" value="" required>
                    </div>
                </div>
                <div class="form-group" style="margin-left: 30px !important">                   
                    <div class="col-sm-10">
                        <input type="date" class="form-control" id="date-end" name="date-end" value="" required>
                    </div>
                </div>
            </div>
            <div class="form-group margin-top-20">
                <label for="date-end" class="col-sm-2 control-label">Configuration File</label>
                <div class="col-sm-10">
                    <span class="file-input btn btn-default btn-lg btn-file">
                        <input type="file" accept="{file-types}" style="width:400px !important" id="pmakerfile" name="pmakerfile" value="" required>
                    </span> 
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
                    <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;&nbsp;<b>Create Programme</b></button>
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


