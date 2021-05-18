<?php
/*
  duty_check_tm.php
  Templates for rm_utils/duty_check script

*/


function duty_check_form($params = array())
{
    // setup select for duty type
    $sel_bufr = <<<EOT
       <option value="all" >all rotas ...</option>";
EOT;

    foreach ($params['duty_types'] as $code=>$label)
    {
        $sel_bufr.= <<<EOT
           <option value="$code" >$label</option>";
EOT;
    }

    $bufr = <<<EOT
    <div class="container" style="margin-top: 40px;">
        <div class="jumbotron">
            <h3 class="text-primary">Instructions:</h3>
            <p class="text-primary">{instructions}</p>
        </div>
        <form enctype="multipart/form-data" id="dutycheckForm" action="{script}" method="post">
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
            
            <div class="form-group row margin-top-20">
                <label class="col-sm-3 control-label text-right">Pick Rotas</label>                                 
                <div class="col-sm-8">
                  <select name="rotas[]" multiple size="10" class="form-control">
                      $sel_bufr
                  </select>                
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
                    <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;&nbsp;<b>Create Report</b></button>
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

function duty_check_report($params = array())
{
    $bufr = "";

    $rows = "";
    foreach($params['data'] as $k=>$row)
    {
        //$row['duties'] = str_replace("|", " | ", $row['duties']);
        $row['duties'] = str_replace("|", "<br>", $row['duties']);

        $row_stripe = "";
        if ($row['numduties'] == 0 ) { $row_stripe = "warning"; }
        if ($row['numduties'] >= 1 ) { $row_stripe = "success"; }
        if ($row['numduties'] > $_SESSION['dutycheck']['max_duty']) { $row_stripe = "danger"; }


        $rows.=<<<EOT
        <tr class="$row_stripe">
            <td>
                {$row['firstname']} {$row['familyname']}
            </td>
            <td>
                {$row['numduties']} &nbsp;(<i>{$row['numevents']}</i>)
            </td>
            <td>
                {$row['duties']}
            </td>
        </tr>
EOT;

    }

    $bufr.=<<<EOT
    <div class="container"></div>
        <h1>Duty Check Report </h1>
        
        <div class="row margin-top-20">
            <div class="col-sm-5 col-sm-offset-1">
                <h1>Duty Check Report </h1>
            </div>
            <div class="col-sm-5">    
                <a class="btn btn-lg btn-warning" style="min-width: 200px;" type="button" name="Quit" id="Quit" onclick="return quitBox('quit');">
                <span class="glyphicon glyphicon-remove"></span>&nbsp;&nbsp;<b>Close</b></a>
            </div>
        </div>
        
        <h3>[{rotas}]</h3>
        <table class="table table-condensed">
            <tr><thead>
                <th width="25%">Member</th>
                <th width="15%">Duties (Events)</th> 
                <th width="60%">Details</th>
            </thead></tr>
            <tbody>
                $rows
            </tbody>
        </table>
        <hr
        <div><p class="pull-right"><small>Report generated - {date}</small></p></div>

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

function duty_check_state($params = array())
{
    if ($params['state'] == 1)
    {
        $bufr = <<<EOT
        <div class="alert alert-warning" role="alert"><h3>Problem!</h3> <h4>no events found for period selected</h4> 
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
    elseif ($params['state'] == 4)
    {
        $bufr = <<<EOT
        <div class="alert alert-warning" role="alert"><h3>Problem!</h3> <h4> no duties found for the period selected</h4>
EOT;
    }
    elseif ($params['state'] == 5)
    {
        $bufr = <<<EOT
        <div class="alert alert-warning" role="alert"><h3>Problem!</h3> <h4> no members in selected rotas</h4>
EOT;
    }
    else
    {
        $bufr = <<<EOT
        <div class="alert alert-warning" role="alert"><h3>Warning!</h3> <h4> Unrecognised completion state - please check with your System Administrator </h4>
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

