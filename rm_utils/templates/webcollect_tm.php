<?php
/*
 * html layouts for application scripts related to the webcollect interface
 *
 */


function berth_synch_report($params = array())
{
    if ($params['status'] != "0")
    {
        $alert = <<<EOT
        <div class="alert alert-danger" role="alert">
            FAILED to create csv file correctly  [ failure mode: {$params['status']} ]
        </div>
EOT;
    }
    else
    {
        $alert = <<<EOT
        <div class="alert alert-success" role="alert">
            <a class="btn btn-default btn-lg" href="{file}" role="button" >Download CSV File</a>
        </div>
EOT;
    }

    // get data rows
    $bufr = <<<EOT
    <div class="jumbotron" style="margin-top: 40px;">
        <h3>Beth Report Summary</h3>
        <p>
            <dl class="dl-horizontal">
                <dt>members processed</dt><dd>{member_input}</dd>
                <dt>berth records created</dt><dd>{member_output}</dd>
            </dl>        
        </p>        
    </div>
    $alert
    The table below provides a summary of the berth records in the output file<br><br>
    <table class="table table-condensed table-hover">
    <thead><tr><th width="25%">member</th><th width="25%">berth</th><th width="25%">boat</th></tr></thead>
    <tbody>
    {berth_data}
    </tbody></table>
EOT;

    return $bufr;
}

function rota_synch_totals($params = array())
{
    $bufr = <<<EOT
    <div class="jumbotron" style="margin-top: 40px;">
        <h3>Synchronisation Summary</h3>
        <p>
            <dl class="dl-horizontal">
                <dt>members processed</dt><dd>{member_input}</dd>
                <dt>members transferred</dt><dd>{member_output}</dd>
                <dt>rota records created</dt><dd>{rota_total}</dd>
                <dt>records with no rota</dt><dd>{rota_map_err}</dd>
            </dl>        
        </p>        
    </div>
EOT;

    return $bufr;
}

function rota_synch_dryrun($params = array())
{

    // get data rows
    $bufr = <<<EOT
    <div class="alert alert-success" role="alert"><b>DRYRUN selected!</b> no records transferred <br>
    Table below lists rota details that would have been transferred </div>
    <table class="table table-condensed">
        <thead>
            <tr>
                <th>Name</th>
                <th>Rota</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody
            {rota_data}
        </tbody>
    </table>
EOT;

    return $bufr;
}

function rota_synch_state($params = array())
{
    if ($params['state'] == 0)
    {
        $bufr = <<<EOT
        <div class="alert alert-success" role="alert"><b>Success!</b> Rota information successfully synchronised </div>
EOT;
    }
    elseif ($params['state'] == 1)
    {
        $bufr = <<<EOT
        <div class="alert alert-danger" role="alert"><b>Failed!</b> raceManager database updated failed - please contact System Administrator </div>
EOT;
    }
    elseif  ($params['state'] == 2)
    {
        $bufr = <<<EOT
        <div class="alert alert-success" role="alert"><b>Failed!</b> raceManager rota records not cleared - please contact System Administrator </div>
EOT;
    }
    else
    {
        $bufr = <<<EOT
        <div class="alert alert-warning" role="alert"><b>Warning!</b> Unrecognised completion state - please check rota lists </div>
EOT;
    }

    return $bufr;
}