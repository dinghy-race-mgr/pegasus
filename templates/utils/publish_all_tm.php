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

function publish_all_state($params = array())
{
    $display_events = false;
    if (!empty($params['data']))
    {
        $display_events = true;
        $table_bufr = "";
        foreach ($params['data'] as $k=>$row)
        {
            $table_bufr.= <<<EOT
            <tr>
                <td>{$row['date']}</td>
                <td>{$row['time']}</td>
                <td>{$row['name']}</td>
            </tr>
EOT;
        }
    }


    if ($params['state'] == 0)
    {
        if ($params['count'] == 0)
        {
            $bufr = <<<EOT
        <div class="alert alert-success" role="alert"><h3>Success!</h3> <h4>{count} events {action}ed in period selected - probably because already {action}ed </h4></div>
EOT;
        }
        else
        {
            $bufr = <<<EOT
        <div class="alert alert-success" role="alert"><h3>Success!</h3> <h4>{count} events {action}ed in period selected </h4></div>
EOT;
        }
    }
    elseif ($params['state'] == 1)
    {
        $bufr = <<<EOT
        <div class="alert alert-warning" role="alert"><h3>Problem!</h3> <h4>no events found for period selected</h4> </div>
EOT;
    }
    elseif ($params['state'] == 2)
    {
        $bufr = <<<EOT
        <div class="alert alert-danger" role="alert"><h3>Failed!</h3> <h4> page status not recognised - please contact System Manager </h4></div>
EOT;
    }
    elseif ($params['state'] == 3)
    {
        $bufr = <<<EOT
        <div class="alert alert-warning" role="alert"><h3>Problem!</h3> <h4> the end date is before the start date</h4></div>
EOT;

    }
    else
    {
        $bufr = <<<EOT
        <div class="alert alert-warning" role="alert"><h3>Warning!</h3> <h4> Unrecognised completion state - please check rota lists </h4></div>
EOT;
    }

    if ($display_events)
    {
        $bufr.= <<<EOT
     <div>
     <p><b>Events {action}ed</b></p>
     <table class="table table-striped table-condensed">
        <tbody>
            $table_bufr
        </tbody>    
     </table>
     </div>
EOT;
    }

    // add button
    $bufr.= <<<EOT
    <div class="row margin-top-20">
        <div class="col-sm-8 col-sm-offset-1">
            <div class="pull-left">
                <a class="btn btn-lg btn-warning" style="min-width: 200px;" type="button" name="Quit" id="Quit" onclick="return quitBox('quit');">
                <span class="glyphicon glyphicon-remove"></span>&nbsp;&nbsp;<b>Cancel</b></a>
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

