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

function fm_change_race($params=array())
{
    global $db_o;

    $labelwidth = "col-xs-4";
    $fieldwidth = "col-xs-7";

    $entry_select = u_selectcodelist($db_o->db_getsystemcodes("entry_type"), $params['entry-option']);
    $start_select = u_selectcodelist($db_o->db_getsystemcodes("start_scheme"), $params['start-option']);

    $html = <<<EOT
        <!-- instructions -->
        <div class="alert alert-warning alert-dismissable" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            Just add the details you want to change. <br>
            [<b>Want a New Race?</b> - restart the raceManager racebox application and select the add race option]
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


function fm_race_message($params=array())
{
    $labelwidth = "col-xs-3";
    $fieldwidth = "col-xs-7";

    $html = <<<EOT
        <!-- instructions -->
        <p>Use this form to let your support team know about any problems you had </p>

        <!-- field #1 - name -->
        <div class="form-group">
            <label class="$labelwidth control-label">Your Name</label>
            <div class="$fieldwidth inputfieldgroup">
                <input type="text" class="form-control" id="msgname" name="msgname" value=""
                    required data-fv-notempty-message="please add your name here" />
            </div>
        </div>

        <!-- field #2 - email address -->
        <div class="form-group">
            <label class="$labelwidth control-label">Your Email</label>
            <div class="$fieldwidth inputfieldgroup">
                <input type="email" class="form-control" id="email" name="email" value=""
                    placeholder="enter your email if you would like a reply"
                    data-fv-emailaddress-message="This does not look like a valid email address" />
            </div>
        </div>

        <!-- field #3 - message -->
        <div class="form-group">
            <label class="$labelwidth control-label">Message</label>
            <div class="$fieldwidth inputfieldgroup">
                <textarea rows=4 class="form-control" id="message" name="message"
                    required data-fv-notempty-message="please describe your issue or problem here"
                    >
                </textarea>
            </div>
        </div>
EOT;
    return $html;
}

function fm_cancel_ok($param=array())
{
    $html = <<<EOT
    <div class="margin-top-10">
        <p><b>You should only CANCEL a race if you haven't started it yet <br>- if it has already started use the ABANDON option</b></p>
        <p>If you are sure you want to CANCEL this race - click the button below to confirm</p>
        <p>No results will be recorded!</p>
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
            <p><b>Finish at an Earlier Lap</b><br>If the boats have completed at least one lap and your sailing
            instructions allow finishing at an earlier lap - use the <b>"reset finish lap"</b> option on the
            Results Page instead of abandoning the race.<br>
            <a class="btn btn-default" href="results_pg.php?eventid={eventid}" role="button">
                Result Page <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
            </a>
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
        <p>If you are part way through a race and have a problem it will usually best to record
        finish times on paper and try to resolve the problem after the race <u>without a reset</u></p>
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
    empty($params['tide-detail']) ? $tide_str = $params['tide-detail']: $tide_str = "[Tide: {tide-detail} ]";
    empty($params['series-name']) ? $series_str = "<i>-- none --</i>" : $series_str = $params['series-name'];

    // event statue
    $event_status = r_decoderacestatus($params['event-status']);
    $event_style  = r_styleracestatus($params['event-status']);
    if ($params['event-status'] == "cancelled" OR $params['event-status'] == "abandoned")
    {
        $event_status.= " - ".ucfirst($params['event-status']);
    }
    $html = <<<EOT
    <div class="margin-top-10">
        <div class="row ">
            <div class="col-md-6">
                <h3 class="text-default" style="text-transform: uppercase;"><b>{event-name}</b></h3>
                <h3 class="text-primary" style="margin-left:20px"><b>- $event_status</b></h3>
            </div>

            <div class="col-md-6 margin-top-10">
                <table class="table table-borderless table-condensed" style="padding:1px !important;">
                    <tr>
                        <td><span class="pull-left"><i>start</i></span></td>
                        <td class="text-primary"><strong>{start-time}</strong>&nbsp;&nbsp;&nbsp;$tide_str</td>
                    </tr>
                    <tr>
                        <td><span class="pull-left"><i>ood</span></i></td>
                        <td class="text-primary"><strong>{ood-name}</strong></td>
                    </tr>
                    <tr>
                        <td><span class="pull-left"><i>format</i></span></td>
                        <td class="text-capitalize text-primary"><strong>{race-format} - {race-starts} start(s)</strong></td>
                    </tr>
                    <tr>
                        <td><span class="pull-left"><i>series</i></span></td>
                        <td class="text-primary"><strong>$series_str</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
EOT;
    return $html;
}


function race_status_display($params, $data)
{
    $table = "";
    $eventid = $data['eventid'];
    if ($data['fleet-data'])
    {
        foreach ($data['fleet-data'] as $fleet)
        {
            // number racing
            empty($fleet['num_racing']) ? $num_racing = 0 : $num_racing = $fleet['num_racing'];

            // set laps
            if ($fleet['maxlap'] == 0)
            {
                $style = "danger";
                $label = "set";
            }
            else
            {
                $style = "primary";
                $label = "change";
            }
            $setlaps_bufr = set_laps_control($eventid, $fleet['race'], $fleet['maxlap'], $style, $label);

            // current lap
            $laps = $fleet['currentlap'];

            // elapsed time/status
            $elapsed = r_getelapsedtime("secs", $data['timer-start'], time(), $fleet['startdelay']);
            if ($data['timer-start']==0)
            {
                $fleet['elapsed'] = "00:00:00";
            }
            else
            {
                $fleet_prep = r_getstartdelay($fleet['start'], $_SESSION["e_$eventid"]['rc_startscheme'], $_SESSION["e_$eventid"]['rc_startint']);
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
            <tr>
                <td class="racestate-title" >{$fleet['racename']}</td>
                <td class="racestate-text" >start - {$fleet['start']}</td>
                <td class="racestate-text" >{$fleet['racetype']}</td>
                <td class="racestate-text" >{$fleet['entries']}</td>
                <td class="racestate-text" >$num_racing</td>
                <td style="text-align: left; margin-left: 1em;">$setlaps_bufr</td>
                <td class="racestate-text" style="text-align: left;">$laps</td>
                <td class="racestate-text" >{$fleet['elapsed']}</td>
                <td class="racestate-text" >{$fleet['status']}</td>
            </tr>
EOT;
        }
    }
    else
    {
        $table = <<<EOT
                    <tr><td colspan="8"><strong>Fleet information missing !</strong></td></tr>
EOT;
    }

    $html =<<<EOT
    <div class="row">
        <div class="col-md-10" col-md-offset-1>
            <div class="panel panel-default">
                <div class="panel-heading" >
                    <span class="text-primary" style="font-size: 1.5em;"><b>Race Status</b></span>
                    <a class="btn btn-xs btn-default pull-right" type="submit" onclick="location.reload();">
                        <span class="glyphicon glyphicon-refresh"> Refresh</a>
                </div>
                <div class="panel-body bg-default">
                    <table class="table">
                        <thead class="text-danger">
                            <th>fleet</th>
                            <th>start</th>
                            <th>scoring type</th>
                            <th>entries</th>
                            <th>no. racing</th>
                            <th>set laps</th>
                            <th>current lap</th>
                            <th>elapsed</th>
                            <th>status</th>
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


function set_laps_control($eventid, $race, $maxlap, $style, $label)
{
    $html = <<<EOT
    <div class="btn-group pull-left" style="margin-bottom: 5px; display:inline-block">
      <button type="button" class="btn btn-default btn-md " style="width: 5em;">
         <span ><b>$maxlap laps</b></span>
      </button>

      <button type="button" class="btn btn-$style btn-md dropdown-toggle" style="width: 5em;"
              data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="caret"></span>
        <span class="sr-only">Toggle Dropdown</span>
        <span style="font-size: 0.9em">$label</span>
      </button>
      <ul class="dropdown-menu">
        <li><a href="race_sc.php?eventid=$eventid&fleet=$race&laps=1&pagestate=setlap">1 lap</a></li>
        <li><a href="race_sc.php?eventid=$eventid&fleet=$race&laps=2&pagestate=setlap">2 laps</a></li>
        <li><a href="race_sc.php?eventid=$eventid&fleet=$race&laps=3&pagestate=setlap">3</a></li>
        <li><a href="race_sc.php?eventid=$eventid&fleet=$race&laps=4&pagestate=setlap">4</a></li>
        <li><a href="race_sc.php?eventid=$eventid&fleet=$race&laps=5&pagestate=setlap">5</a></li>
        <li><a href="race_sc.php?eventid=$eventid&fleet=$race&laps=6&pagestate=setlap">6</a></li>
        <li role="separator" class="divider"></li>
        <li><a href="#setlapsModal" data-toggle="modal">more ...</a></li>
      </ul>
    </div>
EOT;

    return $html;
}

function fm_race_setlaps($params, $data)
{
    $html = "";
    if ($data['lapstatus']==0)
    {
        $html.= <<<EOT
        <div class="alert alert-danger alert-dismissable" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span></button>
            Please set the number of laps for ALL fleets.
        </div>
EOT;
    }

    foreach($data['fleet-data'] as $fleet)
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
?>