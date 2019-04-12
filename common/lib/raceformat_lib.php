<?php
/*
 * raceformat_lib
 * Functions to present information about an event format.
 */

/**
 * Creates title to display in header of modal
 * @param $event  array with event information
 * @param $close_icon   bool to enable close button on modal header
 * @return string   html markup for modal header content
 */
function createmodaltitle($event, $close_icon)
/*
 *  basic race information - start, tides, duties
 */
{
    global $lang; 
    
    // basic race information - start, tides, duties
    $start_txt = ucwords($lang['app']['start']);
    $tide_txt = "";
    if (!empty($event['tide_time']))
    {
        $tide_title = ucwords($lang['app']['tides']);
        $tide_txt = <<<EOT
            <span class="text-primary" >$tide_title :</span> <b>{$event['tide_time']} - {$event['tide_height']}m</b>
EOT;
    }

    $close_bufr = "";
    if ($close_icon)
    {
        $close_bufr = <<<EOT
            <button type="button" class="close" data-dismiss="modal">
                <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
            </button>
EOT;
    }
    
    // open modal 
    $html = <<<EOT
    <div class="modal-header">
        $close_bufr
        <div class="row">
            <div class="col-md-7">
                <h3 class="text-primary" id="raceviewModalLabel"><strong>{$event['event_name']}</strong></h3>                
            </div>
            <div class="col-md-5">
                <h4 class="pull-right" style="margin-right:20px">
                    <span class="text-primary" >$start_txt :</span> 
                    <b>{$event['event_start']}</b>
                    $tide_txt
            </div>   
    </div>                   
EOT;
    return $html;
}


/**
 * @param $duties
 * @param $eventid
 * @param $panel_state
 * @return string
 */
function createdutypanel($duties, $eventid, $panel_state )
{
    global $lang;
    global $db_o;
    
    if (!empty($duties))
    {
        $content = "<dl class=\"dl-horizontal\">";
        foreach ($duties as $key=>$duty)               
            { 
                $dutytype = $db_o->db_getsystemlabel("rota_type", $duty['dutycode']);
                $content.= "<dt>$dutytype:</dt><dd>{$duty['person']}</dd>";
            }
        $content.= "</dl>";
    }
    else
    {
        $content = $lang['msg']['duties_none'];
    }

    $title = ucwords($lang['app']['duties']);
    $html = <<<EOT
        <div class="panel-group" style="padding-top: 0px" id="accordion{$eventid}0">
            <div class="panel panel-default">
                <div class="panel-heading">
                   <h4 class="text-primary">
                      <a data-toggle="collapse" data-parent="#{$eventid}0" href="#collapse{$eventid}0">
                      <span class="glyphicon glyphicon-chevron-down" ></span>&nbsp;&nbsp;&nbsp;$title 
                      </a>
                   </h4>
                </div>
                <div id="collapse{$eventid}0" class="panel-collapse collapse $panel_state">
                   <div class="panel-body">
                       $content
                   </div>
                </div>
             </div>
        </div>
EOT;
    return $html;
}


/**
 * @param $fleetcfg
 * @param $eventid
 * @param $panel_state
 * @return string
 */
function createfleetpanel($fleetcfg, $eventid, $panel_state)

{
    global $lang;
    global $loc;
    global $db_o;
    
    $title = ucwords($lang['app']['fleet']." ".$lang['sys']['details']);

    // get details for each fleet
    $content = "";
    foreach ($fleetcfg as $key=>$row)
    {
        $scoring     = $db_o->db_getsystemlabel("race_type", $row['scoring']);
        $timelimit   = u_gettimelimit_str($row['timelimit_abs'],$row['timelimit_rel']);
        $classes     = u_getclasses_str($db_o, $row);
        $competitors = u_getcompetitors_str($db_o, $row);
        $content.= <<<EOT
        <tr>
            <td>{$row['start_num']}</td>
            <td><b>{$row['fleet_name']}</b></td>
            <td>$scoring</td>
            <td>{$row['py_type']}</td>
            <td><img src="{$loc}/common/images/signal_flags/{$row['warn_signal']}" height="24" width="32" style="border:1px solid #021a40"></td>
            <td>$timelimit</td>                
            <td>$classes</td>
            <td>$competitors</td>            
        </tr>
EOT;
    }

    $html = <<<EOT
    <div class="panel-group" style="padding-top: 0px" id="accordion{$eventid}1">
        <div class="panel panel-default">
            <div class="panel-heading">
               <h4 class="text-primary">
                  <a data-toggle="collapse" data-parent="#accordion{$eventid}1" href="#collapse{$eventid}1">
                      <span class="glyphicon glyphicon-chevron-down" ></span>&nbsp;&nbsp;&nbsp;$title
                  </a>
               </h4>
            </div>
            <div id="collapse{$eventid}1" class="panel-collapse collapse $panel_state">
                <div class="panel-body">
                    <table class="table" style="margin-bottom: 0px; font-size:1.0em">
                        <thead>
                            <tr>
                                <th width="5%" >{$lang['app']['start']}</th>
                                <th width="15%">{$lang['app']['fleet']}</th>
                                <th width="10%">{$lang['app']['scoring']}</th>
                                <th width="5%" >{$lang['app']['rating']}</th>
                                <th width="5%" >{$lang['app']['signal']}</th>
                                <th width="10%">{$lang['app']['time_limit']}</th>
                                <th width="20%">{$lang['app']['classes']}</th>
                                <th width="15%">{$lang['app']['competitors']}</th>
                            </tr>
                        </thead>
                        <tbody>
                            $content
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
EOT;
    return $html;
}


/**
 * @param $event_o
 * @param $event
 * @return array|int
 */
function getsignaldetail($event_o, $event)
{
    $signal = array();
    $racecfgid = $event['event_format'];
    
    // get race config
    $racecfg = $event_o->event_getracecfg($event['id'], $racecfgid);
    if (!$racecfg) { return -1; }
    
    // get all fleet config 
    $fleetcfg = $event_o->event_getfleetcfg($racecfgid);
    if (!$fleetcfg) { return -2; }
    
    // convert sequence string into integer array
    $sequence       = array_map('intval', explode('-', $racecfg['start_scheme'])); 
    $signal_count   = count($sequence);
    $start_interval = $racecfg['start_interval'] * 60;
    empty($event['event_start']) ? $racestarttime  = strtotime("00:00")
        : $racestarttime  = strtotime($event['event_start']);
    
    $k = 0;
    // loop over starts
    $prev_sec = 0;
    $seq_time = 0;
    for ($i=0; $i<$racecfg['numstarts']; $i++)
    {                
        // find the fleets and flags for this start
        $fleets = "";
        foreach ($fleetcfg as $key=>$row)
        {            
            if ($row['start_num'] == ($i + 1))
            {
                $fleets.= $row['fleet_name']." / ";
                $prep_flag = (empty($row['prep_signal'])) ? ' - ' : $row['prep_signal'];
                $warn_flag = (empty($row['warn_signal'])) ? ' - ' : $row['warn_signal'];
            }
        }
        $fleets = rtrim($fleets, " /");

        if ($i!=0) { $prev_sec = $prev_sec + $racecfg['start_interval']; }   // move the starting point for the next start sequence
                
        // loop over start sequence
        $j = 1;
        foreach ($sequence as $key=>$seq)
        {
            // first start and first signal
            ($i==0 and $j==1) ? $seq_time = -$seq : $seq_time = $seq_time + ($prev_sec - $seq);

            $seq_ctime = $racestarttime + ($seq_time * 60);    // clock time based on advertised start time

            $signal[$k] = array(
                "int"       => $k,
                "time"      => $seq_time,
                "clocktime" => date("H:i", $seq_ctime),
                "name"      => $fleets
            );

            if ($j==1)                          // first signal in sequence for this start - preparatory
            {
                $signal[$k]['type']      = "warn";
                $signal[$k]['dir']       = "up";
                $signal[$k]['flag']      = $warn_flag;
            }
            elseif ($j==$signal_count)          // last signal in sequence - start
            {
                $signal[$k]['type']      = "start";
                $signal[$k]['dir']       = "down";
                $signal[$k]['flag']      = $warn_flag;
            }
            else                                // intermediate warning signal(s)
            {
                $signal[$k]['type']      = "prep";
                $signal[$k]['dir']       = "up";
                $signal[$k]['flag']      = $prep_flag;
            }
            
            $j++;
            $k++;
            $prev_sec = $seq;
        }
    }
    // add final signal - drop prep
    $signal[$k] = array(
        "int"       => $k,
        "time"      => $seq_time,
        "clocktime" => date("H:i", $seq_ctime),
        "name"      => $fleets,
        "type"      => "prep",
        "dir"       => "down",
        "flag"      => $prep_flag
    );
    
    // sort signals into time and fleet order
    $sort = array();
    foreach($signal as $k=>$v) 
    {
       $sort['time'][$k] = $v['time'];
       $sort['int'][$k]  = $v['int'];
    }
    array_multisort($sort['time'], SORT_ASC, $sort['int'], SORT_ASC, $signal);
    
    return $signal;
}


/**
 * @param $sequence
 * @param $eventid
 * @param $panel_state
 * @return string
 */
function createsignalpanel($sequence, $eventid, $panel_state)
{
    global $lang;
    global $loc;

    $title = ucwords($lang['app']['start_sequence']);

    $thistime = 0;
    $content = "";
    foreach($sequence as $key=>$signal)
    {
        // if a signal event - get times and leave space
        $rtime = "&nbsp;";
        $ctime = "&nbsp;";
        if ($signal['time']!=$thistime)
        {
            if ($key != 0) $content.= "<br>";
            $rtime = $signal['time']." mins";        // signal sequence time
            $ctime = $signal['clocktime'];           // clock time
        }

        $start = strtoupper($lang['app']['start']);
        $prep  = strtoupper($lang['app']['preparatory']);
        $warn  = strtoupper($lang['app']['warning']);
        $text = "";
        if ($signal['type']=="prep")
        {
            $text = "<strong>$prep</strong> - {$signal['name']}";
        }
        elseif ($signal['type']=="warn")
        {
            $text = "<strong>$warn</strong> - {$signal['name']}";
        }
        elseif ($signal['type']=="start")
        {
            $text = <<<EOT
               <div class="alert alert-danger" role="alert" style="padding: 5px !important; margin-bottom: 5px !important">
               <strong>$start</strong> - {$signal['name']}
               </div>
EOT;
        }

        $content.= <<<EOT
           <div class="row" style="padding-bottom:5px">
              <div class="col-md-2 col-md-offset-1"><b>$rtime</b> &nbsp;&nbsp;<small>$ctime</small></div>
              <div class="col-md-6">$text</div>
              <div class="col-md-2">
                    <img src="{$loc}/common/images/signal_flags/{$signal['flag']}" height="30" width="40" style="border:1px solid #021a40;">&nbsp;
                    <span class="text-primary glyphicon glyphicon-arrow-{$signal['dir']}" style="font-size: 120%"></span>
              </div>
           </div>
EOT;

        $thistime = $signal['time'];
    }

    $html= <<<EOT
        <div class="panel-group" style="padding-top: 0px id="accordion{$eventid}2">
         <div class="panel panel-default">
            <div class="panel-heading">
               <h4 class="text-primary">
                  <a data-toggle="collapse" data-parent="#accordion{$eventid}2" href="#collapse{$eventid}2">
                    <span class="glyphicon glyphicon-chevron-down" ></span>&nbsp;&nbsp;&nbsp;$title
                  </a>
               </h4>
            </div>
            <div id="collapse{$eventid}2" class="panel-collapse collapse $panel_state">
               <div class="panel-body">
                   $content
               </div>
            </div>
         </div>
       </div>
EOT;

    return $html;
}

/**
 * createprintbutton
 * Adds 'print friendly' button
 *
 * @param $eventid int event identifier
 * @param $print bool
 * @return string  html string
 */
function createprintbutton($eventid, $print)
{
    global $lang;

    $html = "";
    if ($print)
    {
        $html = <<<EOT
        <a class="btn btn-primary active pull-right" role="button"
           href="race_format_pg.php?eventid=$eventid" target="_BLANK" style="width: 200px" >
           {$lang['app']['printable']}  &raquo;
        </a>
EOT;
    }

    return $html;
}

/**
 * createerrorpanel
 * Displays relevant error message as alert.
 *
 * @param $eventid int event identifier
 * @param $error  int error code
 * @return string  html string
 */
function createerrorpanel($eventid, $error)
{
    global $lang;

    $content = "";
    if ($error == -1)                              // no event
    {
        $content = $lang['msg']['race_not_exists'];
    }
    elseif ($error == -2)                          // no race config
    {
        $content = $lang['msg']['race_format_not_found'];
    }
    elseif ($error == -3)                          // no fleet config fleet_detail_not_found
    {
        $content = $lang['msg']['fleet_detail_not_found'];
    }

    $html = <<<EOT
    <div class="row">
        <div class="col-md-6">
            <div class="alert alert-danger margin-top-10" role="alert">
                <h3 style="margin: 10px 10px 10px 10px;">$content</h3>
                <p>{$lang['sys']['supportteamhelp']}</p>
            </div>
       </div>
    </div>
EOT;
    
    return $html;
}

?>