<?php

function fm_start_adjusttimer($params=array())
{
    $labelwidth = "col-xs-5";
    $fieldwidth = "col-xs-6";

    $html = <<<EOT
    <div class="well" role="alert">
        <div>If you have forgotten to set the timer at the first signal,
        enter the (approx) clock time of the first signal e.g 10:30:00</div>
    </div>

    <!-- field #1 - restart time -->
    <div class="form-group margin-top-20">
        <label class="$labelwidth control-label">Clock Time of First Signal</label>
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
    <div class="alert alert-danger alert-dismissable" role="alert">
        <div id="instructions">
                 Use this form to adjust the timer for a general recall of this fleet
                 - enter the planned <b>START</b> time for the restart (not the preparatory or warning signals).
                 <br><br>
                 <i><b>Note:</b> This can also be used to delay this start for reasons other than a general recall</i>
            </div>
    </div>

    <!-- field #1 - restart time -->
    <div class="form-group margin-top-20">
        <label class="$labelwidth control-label">Clock Time of Re-Start</label>
        <div class="$fieldwidth inputfieldgroup">
            <input type="text" class="form-control" id="restarttime" name="restarttime" value=""
                placeholder="hh:mm:ss (24 hour clock)"
                required data-fv-notempty-message="this information is required"
                data-fv-regexp="true"
                data-fv-regexp-regexp="^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$"
                data-fv-regexp-message="start time must be in HH:MM:SS format"
            />
            <span class="pull-right text-primary"><i>[current start time <span id="origstart">00:00</span>]</i></span>
        </div>
    </div>
    <input name="startnum" type="hidden" id="start" value=0 />
EOT;
    return $html;
}

//function fm_start_setlaps($params, $data)
//{
//    $html = "";
//    if ($data['lapstatus']==0)
//    {
//        $html.= <<<EOT
//        <div class="alert alert-danger alert-dismissable" role="alert">
//            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
//            <span aria-hidden="true">&times;</span></button>
//            Please set the number of laps for ALL fleets.
//        </div>
//EOT;
//    }
//
//    foreach($data['fleet-data'] as $fleet)
//    {
//        ( isset($fleet['maxlap']) AND $fleet['maxlap']>0 ) ? $laps = "{$fleet['maxlap']}" : $laps = "";
//
//        $html.= <<<EOT
//        <div class="form-group" >
//             <label class="col-xs-5 control-label">
//                {$fleet['name']}
//             </label>
//             <div class="col-xs-3 inputfieldgroup">
//                 <input type="number" class="form-control" style="padding-right:10px;" name="laps[{$fleet['fleetnum']}]"
//                    value="$laps" placeholder="set laps" min="1"
//                    data-fv-greaterthan-message="The no. of laps must be greater than 0"
//                  />
//             </div>
//        </div>
//EOT;
//    }
//
//    return $html;
//}


function timer($params=array())
{
    if ($params['event-state'] == "not started")
    {
        $timer_msg = "Not started</small>";
    }
    else
    {
        $timer_msg = "First preparatory signal at ".date("H:i:s", $params['timer-start']);
    }

    $html = <<<EOT
       <h1>Race Timer</h1>
       <span class="text-primary">$timer_msg</span>
       <div class="timer-lg" id="clock" data-clock="c0" data-countdown="{start-master}">
           {start-delta}
       </div>
       <div class="margin-top-10">
           {timer-btn}
       </div>
       <div style="margin-top: 120px; ">
           <div data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' style="cursor:pointer" data-html="true"
                data-title="if you forgot to start the Timer at the first signal - click here" data-placement="left">
               <a class="btn btn-warning pull-right lead" id="latetimer" data-toggle="modal" data-target="#latetimerModal">
                    Forgot to start Timer?
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
            <div class="panel panel-default margin-top-10" style="border: 2px solid lightblue">
                <div class="panel-heading" style="padding-top:2px; padding-bottom:2px">
                    <div class="panel-title">
                        <span class="lead">
                         <div class="row">
                            <div class="col-md-2">Start {startnum}</div>
                            <div class="col-md-7">
                                
                                {fleet-list}
                            </div>
                            <div class="col-md-3">
                                <span class="pull-right text-info">$startdisplay</span>
                            </div>
                         </div>
                         </span>
                    </div>
                </div>
                <div class="panel-body" style="padding: 8px 15px;">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="timer-sm" id="startclock{startnum}" data-clock="c{startnum}" data-countdown="{start-secs}">
                                {start-delta}
                            </div>
                        </div>
                        <div class="col-md-4">&nbsp;<img src="../common/images/signal_flags/{flag}" alt="warning flag" width="15%" height="15%" style="border: 1px solid slategrey"></div>
                        <div class="col-md-3">$infringe_bufr</div>
                        <div class="col-md-3">$recall_bufr</div>
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
    if ($params['entries'] > 0)
    {
        $entry_bufr = "";
        $drop_dirn = "";
        $i = 0;
        foreach($params['entry-data']as $entry)
        {
            $i++;

            $i == 1 ? $instr = "<span class=\"glyphicon glyphicon-triangle-left\" aria-hidden=\"true\"></span> Set Code" : $instr = "";


            $drop_down = str_replace(array("ENTRY","BOAT"),
                array("{$entry['id']}","{$entry['class']}-{$entry['sailnum']}"), $params['code-bufr']);

            if (($drop_dirn == "") and ($i > $params['entries']/2) and ($i > 6) ) { $drop_dirn = "dropup"; }

            $entry_bufr.= <<<EOT
            <tr>
                <td width="15%">{$entry['class']}</td>
                <td width="10%">{$entry['sailnum']}</td>
                <td width="30%">{$entry['helm']}</td>
                <td width="25%" >
                    <div class="btn-group $drop_dirn pull-right">
                            <button type="button" class="btn btn-default btn-sm text-default" style="width: 4em; padding: 1px 1px;">
                        		<span class="default"><b>{$entry['code']}&nbsp;</b></span>
                        	</button>

                        	<button type="button" class="btn btn-default btn-sm dropdown-toggle"
                        	        style="width: 2em; padding: 1px 1px;" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>

                        	<ul class="dropdown-menu" >
                        		$drop_down
                        	</ul>
                    </div>
                    
                </td>
                <td width="25%">&nbsp;&nbsp;&nbsp;<span class="text-info">$instr</span></td>
            </tr>
EOT;
        }

        $html = <<<EOT
        <div class="container">
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

