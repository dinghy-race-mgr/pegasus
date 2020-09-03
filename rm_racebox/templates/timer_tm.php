<?php

function timer_tabs($params = array())
{
    // debug: u_writedbg(u_check($timings, "TIMINGS:"),__FILE__,__FUNCTION__,__LINE__); // debug;

    $eventid = $params['eventid'];

    $tabs = "";
    $panels = "";

    $state_cfg = array(
        "default"  => array("row_style" => "", "label_style" => "label-primary", "annotation" => ""),
        "racing"   => array("row_style" => "", "label_style" => "label-primary", "annotation" => " click to time "),
        "finished" => array("row_style" => "finished", "label_style" => "label-finished", "annotation" => " FINISHED"),
        "lastlap"  => array("row_style" => "lastlap", "label_style" => "label-danger", "annotation" => " ON LAST LAP"),
    );

    $url_base      = "timer_sc.php?eventid=$eventid";
    $timelap_link  = $url_base."&pagestate=timelap&fleet=%s&start=%s&entryid=%s&boat=%s&lap=%s&pn=%s&etime=%s";
    $finish_link   = $url_base."&pagestate=finish&fleet=%s&start=%s&entryid=%s&boat=%s&lap=%s&pn=%s&etime=%s";
    $setcode_link  = $url_base."&pagestate=setcode&fleet=%s&entryid=%s&boat=%s&racestatus=%s";

    for ($i = 1; $i <= $params['num-fleets']; $i++)   // loop for each fleet
    {
        $fleet        = $_SESSION["e_$eventid"]["fl_$i"];
        $num_entries  = $_SESSION["e_$eventid"]["fl_$i"]['entries'];
        $num_racing   = count($params['timings'][$i]);
        $all_finished = "";
        $laps_btn     = "";

        // create TABS
        $tabs.= <<<EOT
        <li role="presentation" class="">
              &nbsp;<a href="#fleet$i" aria-controls="{$fleet['name']}" role="tab" data-toggle="pill">{$fleet['name']}</a>&nbsp;
        </li>
EOT;

        // create PANELS
        if ($num_entries <= 0)    // no entries for this fleet
        {
            $panels .= <<<EOT
            <div role="tabpanel" class="tab-pane" id="fleet$i">
                <div class="alert alert-warning" role="alert" style="margin-left: 0%; margin-right: 40%; text-align: center;"
                   <span><b>no entries in the {$fleet['name']} fleet </b></span><br>
                </div>
            </div>
EOT;
        }
        else                      // we have entries for this fleet
        {
            if ($num_racing <= 0)        // all finished - nothing to time
            {
                $all_finished = <<<EOT
                <div role="tabpanel" class="tab-pane" id="fleet$i">
                    <div class="alert alert-warning" role="alert" style="margin-left: 0%; margin-right: 40%; text-align: center;"
                       <span><b>all finished - no more boats to time in the {$fleet['name']} fleet </b></span><br>
                    </div>
                </div>
EOT;
            }
            else
            {
                if (!$_SESSION["e_$eventid"]['pursuit'])
                {
                    if ($_SESSION["e_$eventid"]["fl_$i"]['maxlap'] <= 0)    // no laps warning
                    {
                        $laps_btn = <<<EOT
                        <div class="row margin-top-0">
                            <div class="col-sm-12 text-center margin-top-0" >
                                <a href="#setlapsModal" data-toggle="modal" class="btn btn-danger btn-lg margin-top-0" aria-expanded="false" role="button" >
                                    <span class="glyphicon glyphicon-exclamation-sign"></span>
                                    &nbsp;NO LAPS set for this fleet - click here to set laps&nbsp;
                                </a>
                            </div>
                        </div>
EOT;
                    }
                    else                                                    // shorten laps button
                    {
                        $laps_btn = <<<EOT
                        <div class="row margin-top-0">
                            <div class="col-sm-12 text-center margin-top-0" >
                                <div data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true"
                                     data-title="click here to shorten this fleet at the end of the next lap" data-placement="top" class="btn-group ">
                                    <a id="shorten$i" href="timer_sc.php?eventid=$eventid&pagestate=shorten&fleet=$i" class="btn btn-info btn-lg margin-top-0" aria-expanded="false" role="button" >
                                        <span class="glyphicon glyphicon-flag"></span>&nbsp;
                                        {$fleet['maxlap']} LAPS - click to shorten course&nbsp;
                                    </a>
                                </div>
                            </div>
                        </div>
EOT;
                    }
                }
            }

            // create table rows
            $rows = "";
            $finish_btn_tmpl = btn_finish_tmpl();   // create finish button template

            foreach ($params['timings'][$i] as $j=>$r)   // loop over each boat in this fleet
            {
                $boat = "{$r['class']} - {$r['sailnum']}";
                $finish_link = vsprintf($finish_link, array($r['fleet'], $r['start'], $r['id'], $boat, $r['lap'], $r['pn'], $r['etime'] ));

                $current_lap = $r['lap'] + 1;
                $cfg = $state_cfg['default'];
                $finish_btn  = "";

                if ($r['status']=="F" OR $r['status']=="X")                       // boat has finished (applies to all race types)
                {
                    $cfg = $state_cfg['finished'];
                    $finish_btn  = "&nbsp;";
                    $skip = "rowlink-skip";
                }

                if ($r['status'] == "R")
                {
                    $skip = "";
                    if ($current_lap == $fleet['maxlap'] OR
                        ($_SESSION["e_$eventid"]["fl_$i"]['status'] == "finishing" AND $_SESSION["e_$eventid"]["fl_$i"]['scoring'] == "average" ))                            // boat is on last lap
                    {
                        $cfg = $state_cfg['lastlap'];
                        if ($fleet['scoring'] != "pursuit")                          // show finish button unless pursuit
                        {
                            $finish_btn  = vsprintf($finish_btn_tmpl, array("finish boat", $finish_link, " ", "danger"));
                        }

                    }
                    else                                                              // not on last lap
                    {
                        $cfg = $state_cfg['racing'];
                        if  ($fleet['scoring'] == "handicap" OR $fleet['scoring'] == "level")
                        {
                            $finish_btn  = vsprintf($finish_btn_tmpl, array("can't finish - not on last lap", $finish_link, "disabled", "default"));
                        }
                        elseif ($fleet['scoring'] == "average")
                        {
                            $finish_btn  = vsprintf($finish_btn_tmpl, array("finish boat - ignoring lap", $finish_link, " ", "danger"));
                        }
                    }
                }

                $laptimes_bufr = laptimes_html($r['laptimes'], $cfg['label_style'], $cfg['annotation']);

                $row_link = vsprintf($timelap_link,
                    array($r['fleet'], $r['start'], $r['id'], $boat, $r['lap'], $r['pn'], $r['etime'] ));

                $code_link = codes_html($r['code'], vsprintf($setcode_link,
                    array($r['fleet'], $r['id'], $boat, $r['status'])));

                $edit_link = editlaps_html($eventid, $r['id'], $boat);

                $rows.= <<<EOT
                    <tr class="{$cfg['row_style']}">
                        <td style="width: 1%;"><a href="$row_link"></a></td>
                        <td class="$skip truncate" style="width: 10%;">{$r['class']}</td>
                        <td class="$skip truncate" style="width: 5%; ">{$r['sailnum']}</td>
                        <td class="$skip truncate" style="width: 10%;">{$r['helm']}</td>
                        <td class="$skip" style="width: 40%; margin-left:15px;">$laptimes_bufr</td>
                        <td class="rowlink-skip" style="width: 10%">$code_link</td>
                        <td class="rowlink-skip" style="width: 10%;text-align: left">$finish_btn</td>
                        <td class="rowlink-skip" style="width: 5%; text-align: left">$edit_link</td>
                    </tr>
EOT;
            }

            // put panel table layout together
            $panels .= <<<EOT
            <div role="tabpanel" class="tab-pane margin-top-0" id="fleet$i">
                $all_finished
                $laps_btn
                <table class="table table-hover margin-top-05" style="width: 100%; table-layout: fixed;">
                    <tbody data-link="row" class="rowlink">
                        $rows
                    </tbody>
                </table>
            </div>
EOT;
        }
    }

    // final page body layout
    $html = <<<EOT
    <div class="margin-top-10" role="tabpanel">
        <ul class="nav nav-pills red" role="tablist">
           $tabs
        </ul>
        <div class="tab-content">
           $panels
        </div>
    </div>
EOT;
    return $html;
}


function laptimes_html($laptimes_str, $label_style, $annotation)
{
    $lap_cnt = 0;
    $style = "font-size:1.0em; margin-left: 5px";

    $bufr = "";
    if (!empty($laptimes_str))
    {
        $laptimes = explode(",", $laptimes_str);
        $lap_cnt = count($laptimes);
        $j = 0;
        foreach ($laptimes as $lap=>$laptime)
        {
            $j++;
            $formattedtime = gmdate("H:i:s", $laptime);
            if ($lap_cnt <=6)
            {
                $bufr.= "<span class='label $label_style' style='$style'>$formattedtime</span> ";
            }
            else
            {
                if ($j == 1 )
                {
                    $bufr.= "<span class='label $label_style' style='$style'>$formattedtime</span>";
                }
                elseif ($j == $lap_cnt - 1 OR $j == $lap_cnt)
                {
                    $bufr.= "<span class='label $label_style' style='$style'>$formattedtime</span>";
                }
                else
                {
                    $bufr.= "<i>&nbsp;. $j. &nbsp;</i>";
                }
            }
        }
    }
    if ($lap_cnt < 6) { $bufr.= " &nbsp;&nbsp;$annotation"; };

    return $bufr;
}


function btn_finish_tmpl()
{
    $bufr = <<<EOT
        <span data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true" data-title="%s" data-placement="top">
        <a id="finish" href="%s" role="button" class="btn btn-link %s" style="padding:0px" target="">
            <span class="badge progress-bar-%s" style="font-size: 1em">
                <span class="glyphicon glyphicon-flag"></span>&nbsp;Finish&nbsp;
            </span>
        </a>
        </span>
EOT;

    return $bufr;
}

function codes_html($code, $url)
{
    if (empty($code))
    {
        $label = "<span style='color: red;'>set code &nbsp;</span>";
    }
    else
    {
        $label = "<span style='color: black;font-weight: bold;'>$code &nbsp;</span>";
    }

    $codebufr = u_dropdown_resultcodes($_SESSION['timercodes'], "short", $url);

    $bufr = <<<EOT
    <div class="dropdown">
        <button type="button" class="btn btn-default btn-md text-default dropdown-toggle"
                data-toggle="dropdown" style="width: 7em; padding: 1px 2px;">
            <span class="default"><b>$label&nbsp;</b></span><span class="caret" style="color:red;"></span>
        </button>
        <ul class="dropdown-menu">
            $codebufr
        </ul>
    </div>
EOT;

    return $bufr;
}

function editlaps_html($eventid, $entryid, $boat)
{
    $bufr = <<<EOT
    <span data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true" data-title="edit lap times for this boat" data-placement="top">
        <button type="button" class="btn btn-link btn-  " style="padding:0px 5px 0px 5px;" data-toggle="modal"
                rel="tooltip" data-original-title="edit lap times for this boat" data-placement="bottom" data-target="#editlapModal"
                data-boat="$boat"
                data-iframe="timer_editlaptimes_pg.php?eventid=$eventid&pagestate=init&entryid=$entryid" >
            <span class="badge progress-bar-default" style="font-size: 100%">
                <span class="glyphicon glyphicon-pencil"></span>
            </span>
        </button>
    </span>
EOT;

    return $bufr;
}

function fm_timer_setlaps($params)
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


function problems($params=array())
{
    $html = "";

    $msg = array(
        "timer" => array(
            "title" => "Timer has not been started",
            "info"  => "Return to the start page and start the main timer at the same time as your first preparatory signal",
            "link"  => "start_pg.php?eventid={eventid}&menu=true",
            "label" => "Start Page",
        ),
        "laps" => array(
            "title" => "The laps have not been set for any fleet",
            "info"  => "Return to the race page and set the number of laps you want each fleet to sail",
            "link"  => "race_pg.php?eventid={eventid}&menu=true",
            "label" => "Race Page",
        ),
        "entries" => array(
            "title" => "No entries in any fleet",
            "info"  => "You need to add some boats on the entries page - either by selecting boats (add entry) or by loading entries",
            "link"  => "entries_pg.php?eventid={eventid}&menu=true",
            "label" => "Entries Page",
        ),
        "unknown" => array(
            "title" => "Unknown Problem",
            "info"  => "Problem detected preventing lap timing - try the help page",
            "link"  => "help_pg.php?eventid={eventid}&page=timer&menu=true",
            "label" => "Help Page",
        )
    );

    $pbufr = "";
    foreach ($params as $type => $problem)
    {
        if (!empty($problem))
        {
            $data = $msg["$type"];
            $pbufr.= <<<EOT
            <div class="col-md-8 col-md-offset-2 row margin-top-20">
                <div class="alert alert-danger alert-dismissible fade in" role="alert">
                   <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                   <h4><b>{$data['title']}</b></h4>
                   <p>{$data['info']}</p>
                   <p> <a class="btn btn-danger" href="{$data['link']}">
                            <span class="glyphicon glyphicon-menu-right"><b> {$data['label']}</b><span>
                       </a>
                   </p>
               </div>
            </div>
EOT;
        }
    }

    $html = "";
    if (!empty($pbufr))
    {
        $html= <<<EOT
        <div class="margin-top-20">
            <div class="row">
            $pbufr
            </div>
        </div>
EOT;
    }

    return $html;
}


function fm_editlaptimes($params=array())
{
    // form instructions
    $html = <<<EOT
    <div class="alert alert-warning alert-dismissable" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <b>edit the lap times and click the update button to save them</b></br> use hh:mm:ss for elapsed time (e.g. 00:46:32);
    </div>
EOT;

    // form header
    $html.= <<<EOT
    <form id="editlapForm" class="form-horizontal" action="timer_editlaptimes_pg.php?pagestate=submit"
        method="post"
        data-fv-addons="mandatoryIcon"
        data-fv-addons-mandatoryicon-icon="glyphicon glyphicon-asterisk"
        data-fv-framework="bootstrap"
        data-fv-icon-valid="glyphicon glyphicon-ok"
        data-fv-icon-invalid="glyphicon glyphicon-remove"
        data-fv-icon-validating="glyphicon glyphicon-refresh"
    >
EOT;

    if ($params)
    {
        // hidden fields
        $html.= <<<EOT
        <input type="hidden" name="eventid" value="{eventid}">
        <input type="hidden" name="entryid" value="{entryid}">
        <input type="hidden" name="fleet" value="{fleet}">
        <input type="hidden" name="boat" value="{boat}">
        <input type="hidden" name="pn" value="{pn}">
EOT;

        // loop over lap times - field names are laptime[lap]
        $i = 1;
        foreach ($params as $laptime)
        {
//        echo "<pre>{$row['id']}".print_r($lap, true)."</pre>";
            $formatted_time = gmdate("H:i:s", $laptime);
            $html.= <<<EOT
            <div class="form-group">
                <label class="col-xs-2 col-xs-offset-2 text-right">lap $i</label>
                <div class="col-xs-4">
                    <input type="text" class="form-control" id="lap$i" name="etime[$i]" value="$formatted_time"
                        required data-fv-notempty-message="a time [hh:mm:ss] must be entered"
                        data-fv-regexp="true"
                        data-fv-regexp-regexp="^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$"
                        data-fv-regexp-message="lap time must be in HH:MM:SS format" />
                </div>
            </div>
EOT;
            $i++;
        }

        $html.= <<<EOT
        <div class="pull-right">
            <button type="submit" class="btn btn-danger"><span class="glyphicon glyphicon-ok"></span>&nbsp;Update Lap Times</button>
        </div>
EOT;
    }
    else
    {
        // no laptimes for this boat
        $html.= <<<EOT
        <div class="alert alert-warning" role="alert" style="margin-left: 20%; margin-right: 20%">
            <b>no lap times recorded for this boat</b><br>
        </div>
EOT;
    }

    $html.= <<<EOT
    </form>
EOT;
    return $html;
}


function edit_laps_success($params=array())
{
    $html = <<<EOT
    <div class="alert alert-success" role="alert" style="margin-top: 30px">
        <p style="font-size: 150%;"><b>Successful changes</b> were made to the lap times for {boat}.<br></p>
        <span style="text-indent: 30px;">{msg}</span>
        <p>Use the <b>BACK</b> button to make more changes or the <b>Close</b> button at the top of the page to return to the Timer page</p>
    </div>

    <a href="timer_editlaptimes_pg.php?eventid={eventid}&entryid={entryid}&pagestate=init" class="btn btn-primary btn-lg active" role="button">
    <span class="glyphicon glyphicon-step-backward" aria-hidden="true">Back</span>
    </a>

EOT;
    return $html;
}


function edit_laps_error($params=array())
{
    $html = <<<EOT
    <div class="alert alert-danger" role="alert"  style="margin-top: 30px">
    <p style="font-size: 150%;"><b>No changes</b> were made to the lap times for {boat}<br></p>
    <p>The following problems were found with the times you entered .</p>
    <span style="text-indent: 30px;">{msg}</span>
    <p>Use the <b>BACK</b> button to try again or the <b>Close</b> button
    at the top of the page to return to the Timer page</p>
    </div>

    <a href="timer_editlaptimes_pg.php?eventid={eventid}&entryid={entryid}&pagestate=init" class="btn btn-primary btn-lg active" role="button">
    <span class="glyphicon glyphicon-step-backward" aria-hidden="true">Back</span>
    </a>

EOT;
    return $html;
}
