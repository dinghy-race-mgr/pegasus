<?php
/*
  html templates for csv import utility
*/


function publishresults_form($params = array())
{
    if ($params['series'])
    {
        $series_bufr = <<<EOT
        <div class="row form-inline margin-top-10" style="font-size:1.3em">
            <label class="col-sm-3 control-label text-right text-info">Create SERIES Results</label>
            <div class="col-sm-3 col-sm-offset-1">
                <label class="radio-inline">
                  <input type="radio" name="series_results" value="1" checked>&nbsp;&nbsp;yes&nbsp;&nbsp;
                </label>
                <label class="radio-inline">
                  <input type="radio" name="series_results" value="0">&nbsp;&nbsp;no&nbsp;&nbsp;
                </label>
            </div>
            <div class="col-sm-5 text-info" >
                [ series: {$params['series_name']} ]
            </div>
        </div>

EOT;
    }
    else
    {
        $series_bufr = <<<EOT
        <div class="row form-inline margin-top-10" style="font-size:1.3em">
            <label class="col-sm-3 control-label text-right text-muted">Create SERIES Results</label>
            <div class="col-sm-3 col-sm-offset-1 text-muted">
                <label class="radio-inline">
                  <input type="radio" name="series_results" value="1" disabled>&nbsp;&nbsp;yes&nbsp;&nbsp;
                </label>
                <label class="radio-inline">
                  <input type="radio" name="series_results" value="0" disabled>&nbsp;&nbsp;no&nbsp;&nbsp;
                </label>
            </div>
            <div class="col-sm-5 text-muted">
                [ this race is not defined as part of a series ...]
            </div>
        </div>
EOT;
    }


    $bufr = <<<EOT
    <div class="container" style="margin-top: 40px;">
        <div class="jumbotron">
            <h3 class="text-primary">Instructions:</h3>
            <p class="text-primary">{instructions}</p>
        </div>
        <form enctype="multipart/form-data" id="publishresultsForm" action="{script}" method="post">
                   
        <div class="row form-inline" style="font-size:1.3em">
            <label class="col-sm-3 control-label text-right text-info">Create RACE Results</label>
            <div class="col-sm-4 col-sm-offset-1">
                <label class="radio-inline">
                  <input type="radio" name="race_results" value="1" checked>&nbsp;&nbsp;yes&nbsp;&nbsp;
                </label>
                <label class="radio-inline">
                  <input type="radio" name="race_results" value="0">&nbsp;&nbsp;no&nbsp;&nbsp;
                </label>
            </div>
        </div>  
        
        $series_bufr
        
        <div class="row form-inline margin-top-10" style="font-size:1.3em">
            <label class="col-sm-3 control-label text-right text-info">Post Results to WEBSITE</label>
            <div class="col-sm-4 col-sm-offset-1">
                <label class="radio-inline">
                  <input  type="radio" name="post_results" value="1" checked>&nbsp;&nbsp;yes&nbsp;&nbsp;
                </label>
                <label class="radio-inline">
                  <input type="radio" name="post_results" value="0">&nbsp;&nbsp;no&nbsp;&nbsp;
                </label>
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
                    <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;&nbsp;<b>Process</b></button>
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


function publishresults_item_rpt($params = array())
{
    $bufr = "<pre>ITEM REPORT<br>".print_r($params, true)."</pre>";
    return $bufr;
}

function publishresults_error($params = array())
{

    if ($params['state'] == 1)
    {
        $bufr = <<<EOT
        <div class="alert alert-warning" role="alert"><h3>Problem!</h3> <h4>error state not recognised</h4>
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
        <div class="alert alert-warning" role="alert"><h3>Problem!</h3> <h4> event specified [{$params['eventid']}] is not recognised or not found in the database</h4>
EOT;
    }
    else
    {
        $bufr = <<<EOT
        <div class="alert alert-warning" role="alert"><h3>Warning!</h3> <h4> Unrecognised completion state - please contact System Manager </h4>
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
