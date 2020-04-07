<?php

function boatsearch_form($params = array())
{
    $bufr = <<<EOT
        <div class="margin-top-20">
            
            <div class="row">
               <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10 col-lg-offset-2">
                    <form id="sailnumform" class="form-inline" action="boatsearch_sc.php" method="post" role="search" autocomplete="off">
                        <div class="form-group">
                          <label class="text-success"><h2>Boat search&nbsp;&nbsp;&nbsp;</h2></label>
                          <div class="input-group">                              
                              <input id="sailnum" autocomplete="off" class="form-control input-lg rm-form-input-lg placeholder-lg" style="min-width: 500px"
                              type="text" placeholder="sail number, class or surname" name="sailnum" />
                              
                              <span class="input-group-btn">
                                 <button class="btn btn-warning btn-lg" type="submit">
                                     &nbsp;&nbsp;<span class="glyphicon glyphicon-search" aria-hidden="true" ></span>&nbsp;&nbsp;
                                 </button>
                              </span>
                          </div>
                          
                        </div>
                    </form>
                    <br><br>
                    {events_bufr}
               </div>
               
                
            </div>
            <div class="margin-top-40">
            <a href="addboat_pg.php" class="btn btn-info btn-md rm-text-bg pull-right" role="button">
                <strong><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> &nbsp;Add new boat ...</strong>
                
            </a>   
            </div>         
        </div> 
        <br>        
EOT;
    return $bufr;
}

function search_nonfound_response($params = array())
{
    $bufr = "";

    $addboat_bufr = "";
    if ($params['addboat'])
    {
        $addboat_bufr = <<<EOT
            <a href="addboat_pg.php" class="btn btn-block btn-info btn-md rm-text-bg" role="button">
                <strong><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> &nbsp;Add new boat ...</strong>
            </a>
EOT;

    }

    $bufr.= <<<EOT
    <div class="row margin-top-20">
        <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2 ">
            <div class="alert alert-danger rm-text-bg" role="alert">
                <h2><b>Sorry! </b></h2> <p>no boats found matching your search .... <b>"{searchstr}"</b></p>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-xs-10 col-xs-offset-1 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">
            <h4 class="rm-text-md">If you think there should be a boat matching your search in the system please contact the race officer &nbsp;&nbsp;-&nbsp;&nbsp; otherwise &hellip;</h4>
        </div>
    </div>
    
    <div class="row margin-top-10">
        <div class="col-xs-6 col-xs-offset-3 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4">
            <a href="{retryscript}" class="btn btn-block btn-warning btn-md rm-text-bg" role="button">
                <strong><span class="glyphicon glyphicon-step-backward" aria-hidden="true"></span> &nbsp;Search again ...</strong>
            </a>
            <br>
            $addboat_bufr           
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
                <a href="$script" class="btn btn-default btn-block btn-md active" role="button">
                    <h2>$boat</h2>
                    <h3><span style="font-size: 110%">$team</span></h3>
                </a>
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
            <h2 class="text-success">boats matching <b>"{searchstr}"</b> - click the one you want to use</h2><br>
        </div>
    </div>
    $lbufr

    <div class="margin-top-40">
        <a href="boatsearch_pg.php" class="btn btn-warning btn-md active rm-text-md pull-right role="button" >
            <span class="glyphicon glyphicon-step-backward" aria-hidden="true"></span> &nbsp;Start Again ...
        </a>
    </div>

EOT;
    return $bufr;
}





