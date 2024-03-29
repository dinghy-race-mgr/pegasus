<?php
/**
 * Templates used for managing the race entry / declaration process
 *
 */

function race_control($params = array())
{
    $bufr = "";
    $event_bufr = "";
    $instruction_bufr = "";

    // no events
    if ($params['state'] == "noevents")
    {
        $event_bufr .= $params['event-list'];
        $bufr.= <<<EOT
            <div class="row margin-top-10">
                <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">   
                    {boat-label}
                </div>
            </div>
            
            <div class="row margin-top-10">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10 col-lg-offset-1">   
                    $instruction_bufr        
                    $event_bufr
                </div>
            </div>
            
            <hr>
            <div class="margin-top-40">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
                    <a href="search_pg.php" class="btn btn-info btn-sm rm-text-sm" role="button" >
                        <span class="glyphicon glyphicon-step-backward" aria-hidden="true"></span> &nbsp;Done ...
                    </a>
                </div>
            </div>
EOT;

    }
    // something to enter
    elseif ($params['state'] == "submitentry") {
        $event_list = "";

        foreach ($params['event-list'] as $eventid => $row) {   // loop over events

            $params['numdays'] > 1 ?  $event_date = " - ".date("jS M", strtotime($row['date'])) : $event_date = "" ;

            // race identity
            $racename = $row['name'];
            if (strlen($row['name']) > 25)
            {
                $racename = substr($row['name'],0,25)." ...";
            }
            $race_txt = $row['time'] . $event_date . "<br>" . $racename;

            // protest button
            $protest_btn = protest_btn($_SESSION['sailor_protest'], $eventid, $row['event-status-code'], $row['entry-status'],
                                       $params['opt_cfg']['protest']['tip']);

            if ($row['event-status'] == "cancelled" or $row['event-status'] == "abandoned")    // race is cancelled/abandoned
            {
                $row['event-status'] == "cancelled" ? $protest_opt = "" : $protest_opt = $protest_btn;
                $event_status_txt = "this race is ".strtoupper($row['event-status']);

                $event_list .= <<<EOT
                <div class="row margin-top-20">
                    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
                        <span class="rm-text-sm rm-text-trunc">$race_txt</span>
                    </div>
                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-5">
                        <span class="rm-text-md rm-text-highlight">$event_status_txt</span>
                    </div>
                    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-2">
                        <span class="rm-text-md">$protest_btn</span>
                    </div>                     
                </div>
EOT;

            }
            else
            {                                                                                       // race is happening
                // signon / update button
                $signon_btn = signon_btn($eventid, $row['event-status-code'], $row['entry-status']);

//                // declare (signoff) button
//                $declare_btn = declare_btn($row['signon'], $eventid, $row['event-status-code'], $row['entry-status']);

                // retire / cancel button
                $retire_btn = retire_btn($eventid, $row['event-status-code'], $row['entry-status'], $row['signon'], $row['entry-loaded']);

                // entry status
                $entry_status_txt = entry_status_txt($row['entry-status'], $row['entry-alert'], $row['start'], $row['fleet-code'], $row['update-num']);

                // event status
                $event_status_txt = "race: " . $row['event-status-txt'];

                // results option
                $results_btn = results_btn($_SESSION['sailor_results'], $eventid, $row['event-status-code'], $row['entry-status'],
                                           $params['opt_cfg']['results']['tip']);

                // protest option
                $protest_opt = $protest_btn;

                $event_list .= <<<EOT
                <div class="row margin-top-20">
                    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
                        <span class="rm-text-sm rm-text-trunc" >$race_txt</span>
                    </div>
                    <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                        <div class="row rm-text-md">
                            <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">$signon_btn</div>
                            <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">$retire_btn</div>
                        </div>
                    </div>
                    <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
                        <div style="float: left; display: block !important; width: 100%;"> 
                            <div class="alert alert-warning" style="padding: 1px 5px 1px 5px !important ; margin-bottom: 1px !important">
                                <span class="rm-text-sm">$entry_status_txt</span>
                            </div>
                            <div>
                                <span class="rm-text-sm text-warning">$event_status_txt</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-1 col-sm-1 col-md-1 col-lg-1">
                        <span class="rm-text-md">$results_btn</span>
                    </div>
                    <div class="col-xs-1 col-sm-1 col-md-1 col-lg-1">
                        <span class="rm-text-md">$protest_opt</span>
                    </div>                   
                </div>
EOT;
            }
        }

        $event_bufr .= <<<EOT
            <form id="confirmform" action="race_sc.php" method="post" role="submit" autocomplete="off">        
                <!-- table class="table" width="100%" style="table-layout: fixed" --> 
                    $event_list
                <!-- /table -->
            </form>
EOT;

        // PLUGIN HANDLING --------------------------------------------
        $plugin_link_1 = "&nbsp;";
        $plugin_link_2 = "&nbsp;";
        $plugin_link_3 = "&nbsp;";

        if (!empty($params['plugins']))
        {
            // set up data
            reset($params['event-list']);
            $event1_arr = current($params['event-list']);
            $event1_status = $event1_arr['event-status-code'];
            ($event1_status >=3 and $event1_status <=5) ? $racestatus = "inprogress" : $racestatus = "notstarted" ;

            $plugin_data = array(
                "{eventid}"    => "$eventid",
                "{helm}"       => $params['data']['helm'],
                "{crew}"       => $params['data']['crew'],
                "{class}"      => $params['data']['class'],
                "{sailnum}"    => $params['data']['sailnum'],
                "{racestatus}" => $racestatus,                    // event status for first event
            );

            if (array_key_exists("1", $params['plugins'])) {
                $plugin_link_1 = str_replace(array_keys($plugin_data), array_values($plugin_data), $params['plugins'][1]);
            }
            if (array_key_exists("2", $params['plugins'])) {
                $plugin_link_2 = str_replace(array_keys($plugin_data), array_values($plugin_data), $params['plugins'][2]);
            }
            if (array_key_exists("3", $params['plugins'])) {
                $plugin_link_3 = str_replace(array_keys($plugin_data), array_values($plugin_data), $params['plugins'][3]);
            }
        }


        $bufr.= <<<EOT
            <div class="row margin-top-10">
                <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">   
                    {boat-label}
                </div>
            </div>
            
            <div class="row margin-top-10">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">   
                    $instruction_bufr        
                    $event_bufr
                </div>
            </div>
            
            <hr>        

            <div class="row margin-top-40 margin-bot-40">
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2 text-left">
                    $plugin_link_1
                </div>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2 text-left">
                    $plugin_link_2
                </div>
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2 text-left">
                    $plugin_link_3
                </div>
                <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 text-right">
                    <a href="search_pg.php" class="btn btn-info btn-sm rm-text-sm" role="button">
                        <span class="glyphicon glyphicon-step-backward" aria-hidden="true"></span> &nbsp;Back to Start ...
                    </a>
                </div>
            </div>
EOT;

    }
    // unknown state
    else {
        // deal with unknown state - error
        $bufr.= <<<EOT
        <div class="row margin-top-40">
            <div class="col-xs-8 col-xs-offset-2 col-sm-8 col-sm-offset-2 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">   
                <div class="alert alert-danger rm-text-md" role="alert"> 
                    <h2>Fatal Error: Unrecognised page state;</h2>
                    <p>Page state encountered was - {$params['state']}</p> 
                    <p><small>Please contact your system administrator </small></p>
                    
                    <div class="pull-right" style="padding-right: 20px !important">
                        <button type="button" class="btn btn-primary btn-md" onclick="location.href = 'index.php';">
                            <span class="glyphicon glyphicon-chevron-left"></span> back
                        </button>
                    </div>           
                </div>
            </div>
        </div>
EOT;
    }

    return $bufr;
}


function entry_status_txt($en_state, $en_alert, $start, $fleet_code, $update_num)
{
    if (empty($en_alert))
    {
        $txt = strtoupper($en_state);
        empty($start) ? $start_txt = "" : $start_txt = u_numordinal($start)." start" ;

        empty($fleet_code) ? $fleet_txt = "" : $fleet_txt = " [ $fleet_code fleet ]" ;

        if ($en_state == "entered")
        {
            $txt .= ": $start_txt $fleet_txt";
        }
        elseif ($en_state == "updated")
        {
            $update_num > 0 ? $update_txt = "[$update_num]" : $update_txt = "";
            $txt .= " $update_txt: $start_txt $fleet_txt";
        }
        elseif ($en_state == "not eligible" or $en_state == "class not recognised")
        {
            $txt = "<span class='text-danger'>$txt</span>";
        }
    }
    else
    {
        $txt = $en_alert;
    }

    return $txt;
}


function signon_btn($eventid, $ev_state, $en_state)
{
    if ($ev_state <= 2 )    // race not started
    {
        if (empty($en_state))  // not entered yet - green button
        {
            $mode = "btn-success";
            $label = "Enter";
            //++ $label = "Sign On";
        }
        elseif ($en_state == "entered" or $en_state == "updated") {    // update entry
            $mode = "btn-info";
            $label = "Update Entry";
        }
        else
        {
            $mode = "btn-default disabled";
            $label = "Enter";
            //++ $label = "Sign On";
        }
    }
    else
    {                     // otherwise not allowed to enter - disabled button
        $mode = "btn-default disabled";
        $label = "Enter";
    }

    $bufr = <<<EOT
        <a href="race_sc.php?opt=signon&event=$eventid" type='button' class='rm-text-xs btn btn-md btn-block $mode' title = "enter race">
            $label
        </a>
EOT;

    return $bufr;
}


/*function declare_btn($signon, $eventid, $ev_state, $en_state)
{
    $mode = "";
    if ($signon == "signon-declare") {
        if (empty($en_state) or $ev_state < 3) {   // button disabled if not entered or race not started
            $mode = "btn-default disabled";
        } elseif ($ev_state >= 3 and $ev_state < 5 and ($en_state == "entered" or $en_state == "updated" or $en_state == "retired")) {
            $mode = "btn-warning";
        } elseif ($en_state == "signed off") {
            $mode = "btn-default disabled";
        }
    }

    if (empty($mode)) {
        $bufr = "";
    } else {
        $bufr = <<<EOT
            <a href="race_sc.php?opt=declare&event=$eventid" type='button' class='rm-text-xs btn btn-sm $mode'
               title = "complete declaration">Sign Off</a>
EOT;
    }

    return $bufr;
}*/


function retire_btn($eventid, $ev_state, $en_state, $signon, $loaded)
{
    //echo "<pre>$eventid|$ev_state|$en_state|$signon|$loaded</pre>";

    $mode = "";                                                  // initialise to have no button

    if ($signon == "signon-retire")                              // check if we have the retire option
    {

        if ($en_state == "entered" or $en_state == "updated")
        {
            if ($ev_state <= 2)                                      // not started - show cancel entry option
            {
                $action = "cancel";
                $label = "Remove Entry";
                $mode = "btn-default";
            }
            elseif ($ev_state > 2 and $ev_state <= 4)                // race is running and not closed - show retire button
            {
                $action = "retire";
                $label = "Retire";
                $mode = "btn-info";
            }
        }


//        if ($en_state == "entered" or $en_state == "updated")    // do we have an entry
//        {
//            if ($loaded)                                         // entry has been loaded into racebox app - set retire option
//            {
//                $action = "retire";
//                $label = "Retire";
//                $mode = "btn-info";
//            }
//            else                                                 // entry has no been loaded into racebox app - set cancel option
//            {
//                $action = "cancel";
//                $label = "Remove Entry";
//                $mode = "btn-default";
//            }
//        }
    }

    if (empty($mode))
    {
        $bufr = "";
    }
    else
    {
        $bufr = <<<EOT
        <a href="race_sc.php?opt=$action&event=$eventid" type='button' class='rm-text-xs btn btn-block btn-md $mode' 
           title = "retire from race">$label</a>
EOT;
    }

/*
    $mode = "";
    $action = "retire";

    if ($signon == "signon-retire")
    {
        if (empty($en_state))
        {
            $mode = "btn-default disabled";
            $label = "Retire";
        }
        elseif
        ($ev_state < 5 and ($en_state == "entered" or $en_state == "updated"
                                     or $en_state == "signed off" or $en_state == "declared")) {
            $mode = "btn-danger";
            $label = "Retire";
        }
        else
        {
            $mode = "btn-default disabled";
            $label = "Retire";
        }
    }

    if (empty($mode))
    {
        $bufr = "";
    }
    else
    {
        $bufr = <<<EOT
        <a href="race_sc.php?opt=$action&event=$eventid" type='button' class='rm-text-xs btn btn-block btn-md $mode' title = "retire from race">
            $label
        </a>
EOT;
    }*/

    return $bufr;
}


function results_btn($opt, $eventid, $ev_state, $en_state, $tip)
{
    $mode = "";
    if ($opt)
    {
        if ($ev_state >= 4 and $ev_state <= 5 and ($en_state == "retired" or $en_state == "signed off"
                                                   or $en_state == "entered" or $en_state == "updated")) {
            $mode = "btn-success";
        }
//        else
//        {
//            $mode = "btn-default disabled";
//        }
    }

    if (empty($mode)) {
        $bufr = "&nbsp;";
    } else {
        $bufr = <<<EOT
        <a href="results_pg.php?event=$eventid&mode=list" data-toggle="tooltip" data-placement="top" 
            title="$tip" type='button' class="btn btn-md $mode">
        <span class='rm-text-md glyphicon glyphicon-list-alt'  aria-hidden='true' ></span></a>           
EOT;
    }

    return $bufr;
}

function protest_btn($opt, $eventid, $ev_state, $en_state, $tip)
{
    $mode = "";
    if ($opt) {
        if ($ev_state >= 1 and ($en_state == "retired" or $en_state == "signed off"
                or $en_state == "entered" or $en_state == "updated")) {
            $mode = "btn-success";
        } else {
            $mode = "btn-default disabled";
        }
    }

    if (empty($mode)) {
        $bufr = "&nbsp;";
    } else {
        $bufr = <<<EOT
        <a href="protest_pg.php?event=$eventid" data-toggle="tooltip" data-placement="top" 
            title="$tip" type='button' class="btn btn-md $mode">
        <span class='rm-text-md glyphicon glyphicon-pencil'  aria-hidden='true'></span></a>
EOT;
    }

    return $bufr;
}


