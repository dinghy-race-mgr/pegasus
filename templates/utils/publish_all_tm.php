<?php
/*
  html templates for csv import utility
*/


function publish_all_form($params = array())
{
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
                <label class="col-sm-3 control-label text-right">Publish or Unpublish</label>                                 
                <div class="col-sm-8">
                  <label class="radio-inline"><input type="radio" name="action" value="publish" checked>&nbsp;publish&nbsp;&nbsp;&nbsp;</label>
                  <label class="radio-inline"><input type="radio" name="action" value="unpublish" >&nbsp;unpublish&nbsp;&nbsp;&nbsp;</label>                 
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
                    <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;&nbsp;<b>Publish / Unpublish</b></button>
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

function publish_all_report($params = array())
{
    $bufr = "";
    $table_bufr = "";
    if ($params['display'])
    {
        if (!empty($params['data']))
        {
            $table_bufr.= <<<EOT
                <p><b>Events {action}ed</b></p>
                <table class="table table-striped table-condensed">
                    <tbody>
EOT;
            foreach ($params['data'] as $k=>$row)
            {
                $table_bufr.= <<<EOT
                <tr><td>{$row['date']}</td><td>{$row['time']}</td><td>{$row['name']}</td></tr>
EOT;
            }
            $table_bufr.= <<<EOT
                    <tbody>
                </table>
EOT;
        }
    }

    $params['count'] == 0 ? $txt = "{count} events {action}ed in period selected - probably because already {action}ed<br><br>" :
        $txt = "{count} events {action}ed in period selected<br><br>" ;

    if ($params['file'])
    {
        $txt.= <<<EOT
        <div style="text-indent: 20px; margin-bottom: 10px">
            <span class="glyphicon glyphicon-ok" aria-hidden="true"></span> programme file created for use by web display software
        </div>
EOT;
    }
    else
    {
        $txt.= <<<EOT
        <div style="text-indent: 20px; margin-bottom: 10px">
            <span class="glyphicon glyphicon-remove" aria-hidden="true"></span> programme file for use by web display software - NOT created
        </div>
EOT;
    }

    if ($params['transfer'])
    {
        $txt.= <<<EOT
        <div style="text-indent: 20px; margin-bottom: 10px">
            <span class="glyphicon glyphicon-ok" aria-hidden="true"></span> programme file transferred for web display software
        </div>
EOT;
    }
    else
    {
        $txt.= <<<EOT
        <div style="text-indent: 20px; margin-bottom: 10px">
            <span class="glyphicon glyphicon-remove" aria-hidden="true"></span> programme file for web display software - NOT transferred
        </div>
EOT;
    }

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
EOT;

    if ($params['state'] == 4)
    {
        $bufr.= <<<EOT
        <div class="alert alert-danger" role="alert">
            <h3>Problem!</h3> 
            <h4> failed to CREATE event programme file for website - please contact the System Administrator</h4>
        </div>
EOT;
    }
    elseif ($params['state'] == 5)
    {
        $bufr.= <<<EOT
        <div class="alert alert-danger" role="alert">
        <h3>Problem!</h3> 
        <h4> failed to TRANSFER event programme file for website - please contact the System Administrator</h4>
        </div>
EOT;
    }

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

function publish_all_state($params = array())
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
