<?php

function fm_start_adjusttimer($params=array())
{
    $labelwidth = "col-xs-5";
    $fieldwidth = "col-xs-6";

    $html = <<<EOT
    <div class="well" role="alert">
        <p class="lead">Forgotten to set the timer at the first signal?</p>
        <p class="lead" style="text-indent: 50px">&hellip; enter the (approx) time of the first signal e.g 10:32:30</p>
    </div>
    <br>

    <!-- field #1 - restart time -->
    <div class="form-group margin-top-20">
        <label class="$labelwidth control-label">Time of First Warning Signal</label>
        <div class="$fieldwidth inputfieldgroup">
            <input type="text" class="form-control" id="adjusttime" name="adjusttime" value=""
                placeholder="hh:mm:ss (24 hour clock)"
                required data-fv-notempty-message="this information is required"
                data-fv-regexp="true"
                data-fv-regexp-regexp="^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$"
                data-fv-regexp-message="start time must be in HH:MM:SS format"
            />
        </div>
    </div>
EOT;
    return $html;
}

function fm_start_genrecall($params=array())
{
    $labelwidth = "col-xs-4";
    $fieldwidth = "col-xs-7";

    $html = <<<EOT
    <div class="alert well well-sm" role="alert">
        <p class="text-info lead">If you have a general recall - enter the actual START time for this fleet.</p>
        <p class="text-primary lead"><small>Note: This can also be used to correct for a delayed start for other reasons</small></p>
    </div>

    <!-- field #1 - restart time -->
    <div class="form-group margin-top-20">
        <label class="$labelwidth control-label">Time of RESTART</label>
        <div class="$fieldwidth inputfieldgroup">
            <input type="text" class="form-control" id="restarttime" name="restarttime" value=""
                placeholder="hh:mm:ss (24 hour clock)"
                required data-fv-notempty-message="this information is required"
                data-fv-regexp="true"
                data-fv-regexp-regexp="^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$"
                data-fv-regexp-message="start time must be in HH:MM:SS format"
            />
            <span class="pull-right text-primary">[ current start time for this fleet <span id="origstart{$params['startnum']}"></span> ]</span>
        </div>
    </div>
    <input name="startnum" type="hidden" id="start{$params['startnum']}" value={$params['startnum']} />
EOT;
    return $html;
}

function fm_stoptimer_ok($param=array())
{
    $html = <<<EOT
        <!-- instructions -->
        <h4 class="text-danger"><b>Are you REALLY &nbsp;<span style="font-size: 1.3em">REALLY</span>  &nbsp;<span style="font-size: 1.6em">REALLY</span> sure?</b></h4>
        <p><b>If you reset the timer you will lose any lap timings you have made.</b></p>
        <p>This should only be necessary if you have abandoned the start and are starting the entire sequence again
        - if you started the timer at the wrong time  - use the "forgot to start timer" to reset the correct start time,
        and if you have a general recall <i>(or just want to move the fleet start sequence)</i>, use the general recall
        button to adjust the start time for each fleet.</p>
     
        <!-- confirm field -->
        <div class="well" style="margin-left: 20px; margin-right: 20px;">
            <div class="form-group" style="margin-left: 10%; margin-right: 10%;">
                <label class="control-label">type "stop" to confirm you want to reset the timer!</label>
                <div class="inputfieldgroup">
                    <input type="text" class="form-control" id="confirm" name="confirm" value="" />
                </div>
            </div>
        </div>
EOT;
    return $html;
}

function timer($params=array())
{
    if ($params['event-state'] == "not started")
    {
        $timer_msg = "Not started";
    }
    else
    {
        $timer_msg = "First warning signal was at ".date("H:i:s", $params['timer-start']);
    }

    $html = <<<EOT
       <h1 class="margin-top-10">Race Timer</h1>
       <p class="text-primary">$timer_msg</p>
       <div class="timer-lg" id="clock" data-clock="c0" data-countdown="{start-master}">
           {start-delta}
       </div>
       <div class="margin-top-10">
           {timer-btn}
       </div>
       <div style="margin-top: 200px; ">
           <div class="pull-right" data-toggle="tooltip" style="cursor:pointer; width: 70%" data-html="true"
                data-title="if you forgot to start the Timer at the first signal - click here" data-placement="bottom">
               <a class="btn btn-info btn-lg pull-right lead" id="latetimer" data-toggle="modal" data-target="#latetimerModal">
                    <span class=""><span class="glyphicon glyphicon-hourglass" aria-hidden="true"></span> &nbsp;Forgot to start TIMER?</span>
               </a>
           </div>
       </div>
       <br>
EOT;
    return $html;
}


function fleet_panel($params)
{
    if ( $params['timer-start'] > 0 )
    {
        $startdisplay = date("H:i:s",$params['timer-start'] + $params['start-delay']);
    }
    else
    {
        $startdisplay = gmdate("+ H:i:s", $params['start-delay']);
    }

    $infringe_bufr = "{infringe}";
    $params['pursuit'] ? $recall_bufr = "&nbsp;" : $recall_bufr   = "{recall}";

    // put panel together

    $html = <<<EOT
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary margin-top-10" >
                <div class="panel-heading" style="padding-top:5px; padding-bottom:5px">
                    <div class="panel-title">
                        <span class="lead">
                         <div class="row">
                            <div class="col-md-2">Start {startnum}</div>
                            <div class="col-md-4">                                
                                {fleet-list}
                            </div>
                            <div class="col-md-3">                                
                                {start-boats} boats
                            </div>
                            <div class="col-md-3">
                                <span class="pull-right">$startdisplay</span>
                            </div>
                         </div>
                         </span>
                    </div>
                </div>
                <div class="panel-body" style="padding: 8px 15px;">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="timer-sm" id="startclock{startnum}" data-clock="c{startnum}" data-countdown="{start-secs}">{start-delta}</div>
                        </div>
                        <div class="col-md-2">&nbsp;<img src="../common/images/signal_flags/{flag}" alt="warning flag" width="50px" height="35px"></div>
                        <div class="col-md-4">$infringe_bufr</div>
                        <div class="col-md-4">$recall_bufr</div>
                    </div>
                </div> <!-- end of panel-body -->
            </div> <!-- end of panel -->
        </div>
    </div>  <!-- end of row -->
EOT;
    return $html;
}


function infringe($params)
{

    $eventid = $params['eventid'];
    $startnum= $params['startnum'];

    if ($params['entries'] > 0)
    {
        $entry_bufr = "";
        $drop_dirn = "";
        $i = 0;
        foreach($params['entry-data']as $entry)
        {
            $i++;

            if (($drop_dirn == "") and ($i > $params['entries']/2) and ($i > 6) ) { $drop_dirn = "dropup"; }

            $boat = "{$entry['class']}-{$entry['sailnum']}";
            $link = <<<EOT
start_infringements_pg.php?eventid=$eventid&pagestate=setcode&startnum=$startnum&fleet={$entry['fleet']}&entryid={$entry['id']}
&boat=$boat&racestatus={$entry['status']}&declaration={$entry['declaration']}&lap={$entry['lap']}&finishlap={$entry['finishlap']}
EOT;
            $code_link = get_code($entry['code'], $link, "startcodes", $drop_dirn);

            $entry_bufr.= <<<EOT
            <tr class = "table-data">
                <td width="15%">{$entry['class']}</td>
                <td width="10%">{$entry['sailnum']}</td>
                <td width="30%">{$entry['helm']}</td>
                <td width="25%"> $code_link</td>
            </tr>
EOT;
        }

        $html = <<<EOT
        <div class="container">
            <div class="alert well well-sm" role="alert">
                <p class="text-info">Enter codes for start infringements using the drop down menu</p>
            </div>
            <table class="table table-striped table-hover table-condensed">
                $entry_bufr
            </table>
        </div>
EOT;
    }
    else
    {
        $html = <<<EOT
        <div class="container">
            <div class="alert alert-warning" role="alert" style="margin-left: 0%; margin-right: 40%">
                No entries for this start<br>
            </div>
        </div>
EOT;
    }

    return $html;
}

