<?php

function boatsearch_form($params = array())
{
    $bufr = <<<EOT
        <div class="margin-top-20">
            
                <div class="row">
                   <div class="col-xs-9 col-xs-offset-1 col-sm-9 col-sm-offset-1 col-md-8 col-md-offset-1 col-lg-8 col-lg-offset-1">
                        <form id="sailnumform" class="form-large" action="boatsearch_sc.php" method="post" role="search" autocomplete="off">
                        <div class="input-group">
                          <input id="sailnum" autocomplete="off" class="form-control input-lg" 
                          type="text" placeholder="sail number, class or helm name" name="sailnum" />
                          <span class="input-group-btn">
                                <button class="btn btn-warning btn-lg" type="submit">
                                    &nbsp;&nbsp;<span class="glyphicon glyphicon-search" aria-hidden="true" style="vertical-align: middle"></span>&nbsp;&nbsp;
                                </button>
                          </span>
                        </div>
                        </form>
                        <br><br>
                        {events_bufr}
                   </div>
<!--                   <div class="rm-text-bg text-center">
                        <a href="boatsearch_pg.php">
                            <span class="btn btn-xs btn-success" style="font-size: 1.2em">
                                <span class="glyphicon glyphicon-menu-left"></span> start again
                            </span>
                        </a> 
                   </div> --!>
                </div> 
            
            
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
            <div class="alert alert-danger rm-text-bg" role="alert">
                <h2><b>Sorry! </b></h2> <p>no boats found matching your search .... <b>"{searchstr}"</b></p>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-xs-10 col-xs-offset-1 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">
            <h4 class="rm-text-md">If you think there should be a boat matching your search in the system<br>please contact the race officer<br><br>Otherwise &hellip;</h4>
        </div>
    </div>
    
    <div class="row margin-top-10">
        <div class="col-xs-6 col-xs-offset-3 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4">
            <a href="{retryscript}" class="btn btn-block btn-warning btn-lg rm-text-bg" role="button">
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
    $hide_str = "";
    $remember_str = "";


    if ($params['opt_cfg']['hideboat']['active'])
    {
        $hide_str = <<<EOT
            <a  href="{$params['hidescript']}" data-toggle="tooltip" data-placement="top" 
            title="{$params['opt_cfg']['hideboat']['tip']}">
                <span class="badge progress-bar-warning" style="padding: 10px;">
                    &nbsp;&nbsp;Hide Me&nbsp;&nbsp;
               </span>
            </a>
EOT;

    }

    if ($params['opt_cfg']['rememberme']['active'])
    {
        $remember_str = <<<EOT
            <a href="rememberme_pg.php?compid=%u" data-toggle="tooltip" data-placement="bottom" 
            title="{$params['opt_cfg']['rememberme']['tip']}">
                <span class="badge progress-bar-danger" style="padding: 10px;">
                    &nbsp;&nbsp;Remember&nbsp;&nbsp;
               </span>
            </a>
EOT;
    }

    foreach($params['data'] as $k=>$comp)
    {
        $team = u_conv_team($comp['helm'], $comp['crew'], 50);
        $boat = u_conv_boat($comp['classname'], $comp['sailnum'], "", 40);
        $script = sprintf($params['pickscript'],$comp['id'],$_SESSION['option']);

        $hide_bfr = sprintf($hide_str, $comp['id']);
        $remember_bfr = sprintf($remember_str, $comp['id']);

        $lbufr.= <<<EOT
        <div class="row margin-top-10">
            <div class="col-xs-8 col-xs-offset-1 col-sm-8 col-sm-offset-1 col-md-8 col-md-offset-1 col-lg-6 col-lg-offset-2">
                <a href="$script" class="btn btn-info btn-block btn-lg active" role="button"><strong>$boat<br>$team</strong></a>
            </div>
            <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3 " style="padding-top: 20px">
               $hide_bfr
               &nbsp;&nbsp;
               $remember_bfr
            </div>
        </div>
EOT;
    }

    // insert list
    $bufr.=<<<EOT
    <div class="row page-title">
        <div class="col-xs-12 col-xs-offset-0 col-sm-11 col-sm-offset-1 col-md-11 col-md-offset-1 col-lg-11 col-lg-offset-1">
            <h3>More than one boat matching <b>"{searchstr}"</b> found - click the one you want</h3><br>
        </div>
    </div>
    $lbufr

    <div class="row margin-top-40">
        <div class="col-xs-6 col-xs-offset-3 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4">
            <a href="boatsearch_pg.php" class="btn btn-warning btn-block btn-lg active rm-text-md" role="button" >
                <span class="glyphicon glyphicon-step-backward" aria-hidden="true"></span> &nbsp;Search again ...
            </a>
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



