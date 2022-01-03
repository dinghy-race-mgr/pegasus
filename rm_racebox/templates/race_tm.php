<?php
/**
 * race_tm.php
 *
 * @abstract Custom templates for the race page
 *
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 *
 * %%copyright%%
 * %%license%%
 *
 * templates:
 *    fm_change_race
 *    fm_race_message
 *    fm_race_reset
 *    fm_race_pursuitstart
 *    race_close_ok
 *    race_close_notok
 *    race_exit
 *    race_detail_display
 *    race_status_display
 */

function fm_changerace($params=array())
{
    global $db_o;

    $labelwidth = "col-xs-4";
    $fieldwidth = "col-xs-7";

    $entry_select = u_selectcodelist($db_o->db_getsystemcodes("entry_type"), $params['entry-option']);
    $start_select = u_selectcodelist($db_o->db_getsystemcodes("start_scheme"), $params['start-option']);

    $html = <<<EOT
        <!-- instructions -->
        <div class="alert alert-dismissable well well-sm" role="alert">
            <button type="button" class="close" style="right: 1px !important" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <p class="text-info lead">Just edit the details you want to change.
            <p class="text-info"><b>Want a New Race?</b> - restart raceManager: RACEBOX and select the <span class="label label-info">Add Race</span> option</p>
        </div>
        
        <!-- field #0 - ood name -->
        <div class="form-group">
            <label class="$labelwidth control-label">OOD Name</label>
            <div class="$fieldwidth inputfieldgroup">
                <input type="text" class="form-control" id="start" name="event_ood" value="{event-ood}"
                    placeholder="enter new OOD name"
                    required data-fv-notempty-message="this information is required"
                />
            </div>
        </div>

        <!-- field #1 - start time -->
        <div class="form-group">
            <label class="$labelwidth control-label">Start Time</label>
            <div class="$fieldwidth inputfieldgroup">
                <input type="text" class="form-control" id="start" name="event_start" value="{start-time}"
                    placeholder="enter new start time (hh:mm)"
                    required data-fv-notempty-message="this information is required"
                    data-fv-regexp="true"
                    data-fv-regexp-regexp="^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$"
                    data-fv-regexp-message="start time must be in HH:MM format"
                />
            </div>
        </div>

        <!-- field #2 - entry type  -->
        <div class="form-group">
            <label class="$labelwidth control-label">Entry Type</label>
            <div class="$fieldwidth selectfieldgroup">
                <select class="form-control" name="event_entry">
                    $entry_select
                </select>
            </div>
        </div>

        <!-- field #3 - start scheme -->
        <div class="form-group">
            <label class="$labelwidth control-label">Signal Sequence (mins)</label>
            <div class="$fieldwidth selectfieldgroup">
                <select class="form-control" name="start_scheme">#
                    $start_select
                </select>
            </div>
        </div>

        <!-- field #4 - time interval between starts -->
        <div class="form-group">
            <label class="$labelwidth control-label">Time Between Starts (mins)</label>
            <div class="$fieldwidth inputfieldgroup">
                <input type="number" class="form-control" id="start_interval" name="start_interval" value="{start-interval}"
                    placeholder="interval between starts in minutes"
                    data-fv-integer="true"
                    data-fv-integer-message="must be number of minutes"
                />
            </div>
        </div>

        <!-- field #5 - event notes -->
        <div class="form-group">
            <label class="$labelwidth control-label">Event Notes</label>
            <div class="$fieldwidth inputfieldgroup">
                <input type="text" class="form-control" id="event_notes" name="event_notes" value="{event-notes}"
                    placeholder="programme note (e.g early start) "
                    data-fv-stringlength="true"
                    data-fv-stringlength-max="150"
                    data-fv-stringlength-message="must be less than 150 characters"
                />
            </div>
        </div>
EOT;
    return $html;
}


function fm_cancel_ok($param=array())
{
    $html = <<<EOT
    <div class="margin-top-10">
        <h4><b>You should only CANCEL a race if you haven't started it yet</b></h4>
        <h4><b> - if it has already started use the ABANDON option</b></h4>
        <p>If you are sure you want to CANCEL this race - click the button below to confirm</p>
        <div class="alert alert-danger" role="alert">No results will be recorded!</div>
    </div>
EOT;
    return $html;
}


function fm_cancel_notok($param=array())
{
    $html = <<<EOT
        <!-- instructions -->
        <h4 class="text-danger"><b>Sorry - you can't CANCEL this race</b></h4>
        <p>{reason}</p>
        <p>{notes}</p>
        <p>{action}</p>
EOT;
    return $html;
}


function fm_uncancel_ok($param=array())
{
    $html = <<<EOT
    <div class="margin-top-10">
        <h4>This will undo the cancelling of the race</h4>
        <p>If you are sure you want to UNCANCEL this race - click the button below to confirm</p>
    </div>
EOT;
    return $html;
}


function fm_uncancel_notok($param=array())
{
    $html = <<<EOT
        <!-- instructions -->
        <h4 class="text-danger"><b>Sorry - you can't reset this CLOSED race</b></h4>
        <p>{reason}</p>
        <p>{notes}</p>
        <p>{action}</p>
EOT;
    return $html;
}


function fm_abandon_ok($param=array())
{
    $html = <<<EOT
    <div class="margin-top-10">
        <p>Are you sure you want to abandon this race ?</p>
        <p><b>No results will be recorded</b></p>
        <div class="alert alert-danger fade-in">
            <p><b>Finish at an Earlier Lap</b></p>
            <p>If the boats have completed at least one lap and your sailing
            instructions allow finishing at an earlier lap - use the <b>"reset finish lap"</b> option on the
            Results Page instead of abandoning the race.</p>
            <p><a class="btn btn-primary" href="results_pg.php?eventid={eventid}" role="button">
                Result Page <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
            </a></p>
        </div>
    </div>
EOT;
    return $html;
}


function fm_abandon_notok($param=array())
{
    $html = <<<EOT
        <!-- instructions -->
        <h4 class="text-danger"><b>Sorry - you can't ABANDON this race</b></h4>
        <p>{reason}</p>
        <p>{notes}</p>
        <p>{action}</p>
EOT;
    return $html;
}


function fm_unabandon_ok($param=array())
{
    $html = <<<EOT
    <div class="margin-top-10">
        <h4>This will undo the abandoning of the race</h4>
        <p>If you sure you want to undo the abandonment of this race - click the button below to confirm</p>
    </div>
EOT;
    return $html;
}

function fm_unabandon_notok($param=array())
{
    $html = <<<EOT
        <!-- instructions -->
        <h4 class="text-danger"><b>Sorry - you can't reset this ABANDONED race</b></h4>
        <p>{reason}</p>
        <p>{notes}</p>
        <p>{action}</p>
EOT;
    return $html;
}


function fm_close_ok($param=array())
{
    $html = <<<EOT
    <div class="margin-top-10">
        <h4><b>Congratulations - job done!</b></h4>
        <p>After closing the race you will be returned to the RaceBox dashboard</p>
        <br>
        <p>If you want to send a message to the Results Team about the results or any problems you had please enter the details below</p>

        <!-- message field -->
        <div class="well" style="margin-left: 20px; margin-right: 20px;">
            <div class="form-group" style="margin-left: 5%; margin-right: 5%;">
                <div class="inputfieldgroup">
                    <textarea rows="3" class="form-control" id="message" name="message"
                     placeholder="any problems? ..."></textarea>
                </div>
            </div>
        </div>
    </div>
EOT;
    return $html;
}


function fm_close_notok($param=array())
{
    $html = <<<EOT
        <!-- instructions -->
        <h4 class="text-danger"><b>Sorry - you can't CLOSE this race</b></h4>
        <p>{reason}</p>
        <p>{notes}</p>
        <p>{action}</p>
EOT;
    return $html;
}


function fm_reset_ok($param=array())
{
    $html = <<<EOT
        <!-- instructions -->
        <h4 class="text-danger"><b>Are you REALLY REALLY sure?</b></h4>
        <p><b>This will <u>delete</u> all entries* and lap timings you have recorded.</b></p>
        <p>If you are part way through a race and have a problem it is usually best to record
        finishing times on paper and try to resolve the problem after the race <span class="text-danger"><b>without a reset</b></span></p>
        <p class="text-primary"><small>* If entries are made through the sign on system - these can be reloaded after the reset</small></p>

        <!-- confirm field -->
        <div class="well" style="margin-left: 20px; margin-right: 20px;">
            <div class="form-group" style="margin-left: 10%; margin-right: 10%;">
                <label class="control-label">type the word "reset" to confirm you are of sound mind!</label>
                <div class="inputfieldgroup">
                    <input type="text" class="form-control" id="confirm" name="confirm" value="" />
                </div>
            </div>
        </div>
EOT;
    return $html;
}

function fm_reset_notok($param=array())
{
    $html = <<<EOT
        <!-- instructions -->
        <h4 class="text-danger"><b>Sorry - you can't RESET this race</b></h4>
        <p>{reason}</p>
        <p>{notes}</p>
        <p>{action}</p>
EOT;
    return $html;
}


function fm_race_pursuitstart($params=array(), $data)
{

$labelwidth = "col-xs-3";
$fieldwidth = "col-xs-7";

if ($params['pytype'] != "personal")
{
    $list = "";
    foreach ($data as $k => $class)
    {
        $list.= "<option value=\"$k\" >$class</option>";
    }

    $scratch = <<<EOT
    <div class="form-group">
        <label class="$labelwidth control-label">First Start</label>
        <div class="$fieldwidth selectfieldgroup">
            <select class="form-control" name="scratchid"
                 required data-fv-notempty-message="Choose the first (slowest) class start for this race">
                <option value="">pick slowest class</option>
                $list
            </select>
        </div>
    </div>
EOT;
    }
    else
    {
        $scratch = <<<EOT
        <input type="hidden" name="scratchid" value="0" \>
EOT;
    }

    $html = <<<EOT
     <p>This form gets the details necessary to calculate the pursuit start times.<br>
         <span class="text-danger">
             <b>The list of start times will open in a new browser window for you to print</b>
         <span>
     </p>

     <!-- field 1 - race length -->
     <div class="form-group">
         <label class="$labelwidth control-label">Race Length</label>
         <div class="$fieldwidth inputfieldgroup">
             <input type="text" class="form-control" id="start" name="length" value=""
                    placeholder="race length in minutes"
                    required data-fv-notempty-message="we need the pursuit race time in minutes"/>
         </div>
     </div>

     <!-- field 2 - slowest class -->
     $scratch

     <!-- field 3 - start interval -->
     <div class="form-group">
         <label class="$labelwidth control-label">Start Interval (secs)</label>
         <div class="$fieldwidth selectfieldgroup">
             <select class="form-control" name="resolution">
                 <option value="60" selected>60</option>
                 <option value="30" >30</option>
                 <option value="10" >10</option>
             </select>
         </div>
     </div>

EOT;

    return $html;
}


function race_close_ok($params=array())
{
    $html = <<<EOT
    <div class="margin-top-10">
        <h4>Congratulations - job done</h4>
        <p>When you click the confirm button below this window will also close returning you to the RaceBox dashboard</p>
        <p>If you want to send a message to the Results Team exit this window - send a message and then close the race</p>
        <br>

    </div>
EOT;
    return $html;
}



function race_exit($params=array())
{
    $html = <<<EOT
    <div class="margin-top-40">
        <div class="jumbotron">
          <h1>Congrats - job done</h1>
          <p>Please close this browser window</p>
        </div>
    </div>
EOT;
    return $html;
}


function race_detail_display($params=array())
{
    empty($params['tide-detail']) ? $tide_str = "": $tide_str = "&nbsp;|&nbsp;<small>tide:</small> <span class=\"lead\"><b>{tide-detail}</b></span>";
    empty($params['series-name']) ? $series_str = "none" : $series_str = $params['series-name'];

    // event statue
    $event_status = r_decoderacestatus($params['event-status']);
    $event_style  = r_styleracestatus($params['event-status']);
    if ($params['event-status'] == "cancelled" OR $params['event-status'] == "abandoned") {
        $event_status .= " - " . ucfirst($params['event-status']);
        $event_style = "danger";
    }

    $html = <<<EOT
    <div class="margin-top-10 well">
        <div class="row">
            <div class="col-md-6">
                <h3 class="text-default" style="text-transform: uppercase;">{event-name}
                    <span class="text-$event_style"> - $event_status</span></h3>
            </div>
            <div class="col-md-5 margin-top-10">
                <h4 class="text-info">
                  <small>start:</small> <span class="lead"><b>{start-time}</b></span>
                  $tide_str
                  &nbsp;|&nbsp;<small>ood:</small> <span class="lead"><b>{ood-name}</b></span><br>
                  <small>format:</small> <span class="lead"><b>{race-format}</b></span>
                  &nbsp;|&nbsp;<small>starts:</small> <span class="lead"><b>{race-starts}</b></span>
                  &nbsp;|&nbsp;<small>series:</small> <span class="lead"><b>$series_str</b></span>
                </h4>
            </div>
        </div>
    </div>
EOT;
    return $html;
}


function race_status_display($params)
{
    global $db_o;

    $table = "";
    $eventid = $params['eventid'];
    if ($params['fleet-data'])
    {
        foreach ($params['fleet-data'] as $fleet)
        {
            // fleet/flag
            $fleetname = ucwords($fleet['racename']);
            $warning_flag = $params['flag-data'][$fleet['fleet']];

            // number racing
            empty($fleet['num_racing']) ? $num_racing = 0 : $num_racing = $fleet['num_racing'];

            // set laps
            if ($fleet['maxlap'] == "pursuit")
            {
                $setlaps_bufr = "n/a";
            }
            else
            {
                $setlaps_bufr = $fleet['maxlap'];
//                if ($fleet['maxlap'] == 0)
//                {
//                    $style = "danger";
//                    $label = "set";
//                }
//                else
//                {
//                    $style = "success";
//                    $label = "change";
//                }
//                $setlaps_bufr = set_laps_control($eventid, $fleet['fleet'], $fleet['maxlap'], $style, $label);
            }

            // race type
            $race_type = $db_o->db_getsystemlabel("race_type", $fleet['racetype']);

            // current lap
            $laps = $fleet['currentlap'];

            // elapsed time/status
            $elapsed = r_getelapsedtime("secs", $params['timer-start'], time(), $fleet['startdelay']);
            if ($params['timer-start']==0)
            {
                $fleet['elapsed'] = "00:00:00";
            }
            else
            {
                $fleet_prep = r_getstartdelay($fleet['start'], $params['start-scheme'], $params['start-interval']);
                if ($elapsed > 0)
                {
                    $fleet['elapsed'] = gmdate("H:i:s", $elapsed);
                    $fleet['status'] = "racing";
                }
                elseif ($elapsed >= (0 - $fleet_prep))
                {
                    $fleet['elapsed'] = "<span style='color: red;'>- ".gmdate("H:i:s", abs($elapsed))."</span>";
                    $fleet['status'] = "start sequence";
                }
                else
                {
                    $fleet['elapsed'] = "00:00:00";
                    $fleet['status'] = "not started";
                }
            }

            $table.= <<<EOT
            <tr class="lead">
                <td class="truncate text-info" ><b>$fleetname</b></td>
                <td style="text-align: center">{$fleet['start']}</td>
                <td ><img class="img-responsive" src="../common/images/signal_flags/$warning_flag" alt="warning flag"></td>
                <td style="text-align: center">$race_type</td>
                <td style="text-align: center">{$fleet['entries']}</td>
                <td style="text-align: center">$num_racing</td>
                <td align="center">$setlaps_bufr</td>
                <td style="text-align: center">$laps</td>
                <td style="text-align: center">{$fleet['elapsed']}</td>
                <td style="text-align: center">{$fleet['status']}</td>
            </tr>
EOT;
        }
        $table.=<<<EOT
        <tr>       
            <td colspan="6">&nbsp;</td>
            <td class="text-center"><a class="btn btn-md btn-info btn-block" href="#setlapsModal" data-toggle="modal">Set Laps</a></td>
            <td colspan="3">&nbsp;</td>
        </tr>
EOT;

    }
    else
    {
        $table = <<<EOT
        <tr><td class="text-danger" colspan="8"><strong>Fleet information missing !</strong></td></tr>
EOT;
    }

    $html =<<<EOT
    <div class="row margin-top-20">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading clearfix" >
                     <div class="row">
                        <div class="col-md-9">
                            <h3 class="text-primary">Fleet Status</h3>
                        </div>
                        <div class="col-md-3">
                            <a class="btn btn-sm btn-primary pull-right" type="submit" onclick="location.reload();">
                                <span class="glyphicon glyphicon-refresh"></span> Refresh</a>
                        </div>
                    </div> 
                </div>
                <div class="panel-body bg-default">
                    <table id="racetable" class="table table-striped table-hover" style="position: relative; display: inline-block;">
                        <thead class="text-info" >
                            <th width="15%"><h4>fleet</h4></th>
                            <th><h4 class="pull-right">start</h4></th>
                            <th style="text-align: center" width="3%"><h4>&nbsp;</h4></th>
                            <th style="text-align: center"><h4>scoring</h4></th>
                            <th style="text-align: center"><h4>entries</h4></th>
                            <th style="text-align: center"><h4>no. racing</h4></th>
                            <th style="text-align: center"><h4>set laps</h4></th>
                            <th style="text-align: center"><h4>current lap</h4></th>
                            <th style="text-align: center"><h4>elapsed time</h4></th>
                            <th style="text-align: center"><h4>status</h4></th>
                        </thead>
                        <tbody>
                            $table
                        </tbody>
                    </table>
                    
                </div>
            </div>
        </div>
    </div>
EOT;
    return $html;
}

// ---- internal function called by template race_status_display
//function set_laps_control($eventid, $race, $maxlap, $style, $label)
//{
//    $html = <<<EOT
//    <div style="display:inline-block">
//    <div class="btn-group" >
//      <button type="button" class="btn btn-primary disabled btn-md " style="width: 5em; font-size: 0.8em">
//         <span >$maxlap laps</span>
//      </button>
//
//      <button type="button" class="btn btn-$style btn-md dropdown-toggle" style="width: 5em; font-size: 0.8em"
//              data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
//        <span class="caret"></span>
//        <span class="sr-only">Toggle Dropdown</span>
//        $label
//      </button>
//      <ul class="dropdown-menu">
//        <li><a href="race_sc.php?eventid=$eventid&fleet=$race&laps=1&pagestate=setlap">1 lap</a></li>
//        <li><a href="race_sc.php?eventid=$eventid&fleet=$race&laps=2&pagestate=setlap">2 laps</a></li>
//        <li><a href="race_sc.php?eventid=$eventid&fleet=$race&laps=3&pagestate=setlap">3</a></li>
//        <li><a href="race_sc.php?eventid=$eventid&fleet=$race&laps=4&pagestate=setlap">4</a></li>
//        <li><a href="race_sc.php?eventid=$eventid&fleet=$race&laps=5&pagestate=setlap">5</a></li>
//        <li><a href="race_sc.php?eventid=$eventid&fleet=$race&laps=6&pagestate=setlap">6</a></li>
//        <li role="separator" class="divider"></li>
//        <li><a href="#setlapsModal" data-toggle="modal">more ...</a></li>
//      </ul>
//    </div>
//    </div>
//EOT;
//
//    return $html;
//}

function fm_race_setlaps($params)
{
    $html = "";
    if ($params['lapstatus']==0)
    {
        $html.= <<<EOT
        <div class="alert alert-danger alert-dismissable" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span></button>
            Please set the number of laps for ALL fleets.
        </div>
EOT;
    }

    foreach($params['fleet-data'] as $fleet)
    {
        ( isset($fleet['maxlap']) AND $fleet['maxlap']>0 ) ? $laps = "{$fleet['maxlap']}" : $laps = "";

        $html.= <<<EOT
        <div class="form-group" >
             <label class="col-xs-5 control-label">
                {$fleet['name']}
             </label>
             <div class="col-xs-3 inputfieldgroup">
                 <input type="number" class="form-control" style="padding-right:10px;" name="laps[{$fleet['fleetnum']}]"
                    value="$laps" placeholder="set laps" min="1"
                    data-fv-greaterthan-message="The no. of laps must be greater than 0"
                  />
             </div>
        </div>
EOT;
    }

    return $html;
}
