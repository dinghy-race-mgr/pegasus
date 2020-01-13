<?php
/*
  check_fleet_allocation_tm.php
  Templates for rm_utils/check_fleet_allocation script

*/


function check_fleet_allocation_form($params = array())
{
    // setup select buffes for class and race formats
    $sel_class_bufr = <<<EOT
       <option value="all" >ALL classes ...</option>";
EOT;
    foreach ($params['classlist'] as $id=>$class)
    {
        $sel_class_bufr.= <<<EOT
           <option value="$id" >$class</option>";
EOT;
    }

    $sel_race_bufr = <<<EOT
       <option value="all" >ALL race formats ...</option>";
EOT;
    foreach ($params['racelist'] as $id=>$race)
    {
        $sel_race_bufr.= <<<EOT
           <option value="$id" >$race</option>";
EOT;
    }

    $bufr = <<<EOT
    <div class="container" style="margin-top: 40px;">
        <div class="jumbotron">
            <h3 class="text-primary">Instructions:</h3>
            <p class="text-primary">{instructions}</p>
        </div>
        <form enctype="multipart/form-data" id="dutycheckForm" action="{script}" method="post">
            
            <div class="form-group row margin-top-20">
                <label class="col-sm-3 control-label text-right">Pick Class(s)</label>                                 
                <div class="col-sm-8">
                  <select name="class[]" multiple size="5" class="form-control">
                      $sel_class_bufr
                  </select>                
                </div>               
            </div>
            
            <div class="form-group row margin-top-20">
                <label class="col-sm-3 control-label text-right">Pick Race Format(s)</label>                                 
                <div class="col-sm-8">
                  <select name="race[]" multiple size="5" class="form-control">
                      $sel_race_bufr
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

function check_fleet_allocation_report($params = array())
{
    $bufr = "";

    //echo "<pre>".print_r($params['data'],true)."</pre>";
    // table header style='width: 200px; display:inline-block !important'
    $thead_bufr = "<tr><thead><th >Classes</th>";
    foreach($params['formats'] as $format)
    {
        $thead_bufr.= "<th>$format</th>";
    }
    $thead_bufr.= "</thead></tr>";

    // table data
    $rows = "";
    foreach($params['data'] as $k=>$class)
    {
        $cells = "";
        foreach ($class as $j=>$alloc)
        {
            $alloc['eligible'] ? $cstyle = "" : $cstyle = "danger";
            $cells.= "<td class='$cstyle'> {$alloc['start']} / {$alloc['start']}</td>";
        }
        $rows.=<<<EOT
        <tr class="">
            <td >$k</td>
            $cells
        </tr>
EOT;
    }

    $bufr.=<<<EOT
    <div class="container"></div>
        <h1>Fleet Allocation Report </h1>
        <p>Allocation is reported as 'start number' / 'fleet number' for each race format</p>
        <table class="table table-condensed" >
            $thead_bufr
            <tbody>
                $rows
            </tbody>
        </table>
        <hr
        <div><p class="pull-right"><small>Report generated - {date}</small></p></div>

    </div>
EOT;

    return $bufr;
}

function check_fleet_allocation_state($params = array())
{
    if ($params['state'] == 1)
    {
        $bufr = <<<EOT
        <div class="alert alert-warning" role="alert"><h3>Problem!</h3> <h4>no classes selected for the report</h4> 
EOT;
    }
    elseif ($params['state'] == 2)
    {
        $bufr = <<<EOT
        <div class="alert alert-danger" role="alert"><h3>Problem!</h3> <h4> no race types selected for the report </h4>
EOT;
    }
    elseif ($params['state'] == 3)
    {
        $bufr = <<<EOT
        <div class="alert alert-warning" role="alert"><h3>Failed!</h3> <h4> page status not recognised - please contact System Manager</h4>
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

