<?php

function boatsearch_form($params = array())
{
    $bufr = <<<EOT
        <div class="margin-top-20">
            <form id="sailnumform" action="boatsearch_sc.php" method="post" role="search" autocomplete="off">
                <div class="row">
                   <div class="col-xs-10 col-xs-offset-1 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
                        <div class="input-group">
                          <input id="sailnum" autocomplete="off" class="form-control input-lg" type="text" placeholder="registered sail number, class or helm name" name="sailnum" />
                          <span class="input-group-btn">
                                <button class="btn btn-warning btn-lg" type="submit">
                                    &nbsp;&nbsp;<span class="glyphicon glyphicon-search" aria-hidden="true" style="vertical-align: middle"></span>&nbsp;&nbsp;
                                </button>
                          </span>
                        </div>
                        <br><br>
                        {events_bufr}
                   </div>
                </div>
            </form>
        </div> 
        <br>        
EOT;
    return $bufr;
}

function search_nonfound_response($params = array())
{
    $bufr = "";

    $bufr.= <<<EOT
    <div class="row margin-top-40">
        <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2 ">
            <div class="alert alert-danger" role="alert">
                <span style="font-size:2.0em;"><b>Sorry!</b> no boats found matching <b>"{searchstr}"</b></span>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-xs-10 col-xs-offset-1 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">
            <h4>If you think there should be a boat matching your search please contact the race officer<br><br>Otherwise &hellip;</h4>
        </div>
    </div>
    
    <div class="row margin-top-10">
        <div class="col-xs-6 col-xs-offset-3 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4">
            <a href="{retryscript}" class="btn btn-block btn-warning btn-lg" role="button">
                <strong><span class="glyphicon glyphicon-step-backward" aria-hidden="true"></span> &nbsp;Search again ...</strong>
            </a>
        </div>
    </div>
EOT;
    return $bufr;
}

function search_manyfound_response($params = array())
{
    $bufr = "";
    // build list
    $lbufr = "";
    foreach($params as $k=>$comp)
    {
    $team = u_conv_team($comp['helm'], $comp['crew'], 30);
    $team_full = u_conv_team($comp['helm'], $comp['crew'], 0);
    $boat = u_conv_boat($comp['classname'], $comp['sailnum'], "", 20);
    $boat_full = u_conv_boat($comp['classname'], $comp['sailnum'], "", 00);
    $label  = "$boat<br>$team";
    $script = "pickboat_sc.php?compid={$comp['id']}&option={option}";
    $lbufr.= <<<EOT
    <div class="row margin-top-10">
        <div class="col-xs-8 col-xs-offset-2 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-6 col-lg-offset-3">
            <a href="$script" class="btn btn-info btn-block btn-lg active" role="button"><strong>$label</strong></a>
        </div>
    </div>
EOT;
    }

    // insert list
    $bufr.=<<<EOT
    <div class="page-title">
        <div class="col-xs-12 col-xs-offset-0 col-sm-10 col-sm-offset-1 col-md-9 col-md-offset-2">
            <h3>More than one boat matching <b>"{searchstr}"</b> found - pick one</h3>
        </div>
    </div>
    $lbufr
    <div class="margin-top-40">
        <div class="row margin-top-10">
            <div class="col-xs-6 col-xs-offset-2 col-sm-4 col-sm-offset-3 col-md-3 col-md-offset-3 col-lg-3 col-lg-offset-4 pull-right">
                <a href="boatsearch_pg.php" class="btn btn-warning btn-block btn-lg active" role="button" >
                    <strong><span class="glyphicon glyphicon-step-backward" aria-hidden="true"></span> &nbsp;Search again ...</strong>
                </a>
            </div>
        </div>
    </div>
EOT;
    return $bufr;
}

function listevents($params = array())
{
    $bufr = "";

    // list events
    $event_table = "";
    foreach ($params['details'] as $k => $row)
    {
        $event_table.= <<<EOT
                <tr>
                    <td><h3>{$row['event_name']}</h3></td>
                    <td><h3>{$row['event_start']}</h3></td>
                </tr>
EOT;
    }

    $bufr.= <<<EOT
         <h4>{$_SESSION['events']['numevents']} race(s) today</h4>
         <table class="table">
            $event_table
         </table>
EOT;

    return $bufr;
}



?>