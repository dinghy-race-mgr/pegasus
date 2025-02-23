<?php

function timer_list($params = array())
{
    //echo "<pre>".print_r($params,true)."</pre>";

    $eventid = $params['eventid'];
    
    // get display for list view

    // view selector buttons
    $view_arr = array(
        "sailnum" => array ("label"=>"Sail No.", "mode"=>"list", "style"=>"btn-default", "params"=>""),
        "class"   => array ("label"=>"Class", "mode"=>"list", "style"=>"btn-default", "params"=>""),
        "fleet"   => array ("label"=>"Fleet", "mode"=>"list", "style"=>"btn-default", "params"=>""),
        "tab"     => array ("label"=>"Tabbed", "mode"=>"tabbed", "style"=>"btn-default", "params"=>""),
    );

    $view_option = "";
    foreach ($view_arr as $view=>$val)
    {
        $view == $params['view'] ? $btn_state = "btn-warning" : $btn_state = "btn-default";
        $view == "tab" ? $view_str = "" : $view_str = "&view=$view";
        //$optlink = "timer_pg.php?eventid={$params['eventid']}&mode={$val['mode']}$view_str";

        $view_option.= <<<EOT
            <a class="btn btn-lg $btn_state text-center lead" href="timer_pg.php?eventid={$params['eventid']}&mode={$val['mode']}$view_str">{$val['label']}</a>
EOT;
    }

    // get boat display
    $view_bufr = timer_list_view($params['eventid'], $params['timings'], $params['view'], 1);

    $last_click_txt = "";
    if (array_key_exists("boat", $_SESSION["e_{$params['eventid']}"]['lastclick']))
    {
        $last_click_txt = "<h4><span style='color: darkgrey'>Last Time Recorded: </span><b>{$_SESSION["e_{$params['eventid']}"]['lastclick']['boat']}</b></h4>";
    }

    $colour_key = <<<EOT
   <a href="#" class="btn btn-default btn-xs" style="margin-right: 2px; min-width:15%">on first lap</a>
   <a href="#" class="btn btn-info btn-xs"    style="margin-right: 2px; min-width:15%">racing</a>&nbsp;
   <a href="#" class="btn btn-warning btn-xs" style="margin-right: 2px; min-width:15%">on last lap</a>&nbsp;
   <a href="#" class="btn btn-success btn-xs" style="margin-right: 2px; min-width:15%">finished</a>&nbsp;
   <a href="#" class="btn btn-primary btn-xs" style="margin-right: 2px; min-width:15%">not racing</a>&nbsp;
EOT;

    // get shorten fleet
    $fleet_opt = "";
    for ($i=1; $i <= $_SESSION["e_$eventid"]['rc_numfleets']; $i++)
    {
        //$fleetname = $_SESSION["e_$eventid"]["fl_$i"]['code'];
        $fleetname = $_SESSION["e_$eventid"]["fl_$i"]['name'];
        $fleet_opt.= <<<EOT
        <li><a href='timer_sc.php?eventid=$eventid&pagestate=shorten&fleet=$i'><b>$fleetname</b></a></li>
EOT;
    }
    // get forgot shorten link

    $shorten_forgotten_li = "";
    $shorten_forgotten_msg = "";
    if ($_SESSION['racebox_shorten_reminder'] != 0 AND (time() - $_SESSION["e_$eventid"]["fl_1"]['starttime']) > ($_SESSION['racebox_shorten_reminder'] * 60))
    {
        $shorten_forgotten_li = <<<EOT
        <li><li role="separator" class="divider"></li>
        <li><a href="help_pg.php?eventid=$eventid&page=timer&helpid=15"><span style="color: steelblue">Forgot to Shorten?</span></a></li>
EOT;
        $shorten_forgotten_msg = <<<EOT
        <br><span style = "color: orange; font-weight: bold;">Consider Shortening?</span>
EOT;

    }

    $shorten_bufr = <<<EOT
    <div class="dropdown">
        <button class="btn btn-lg btn-info dropdown-toggle lead" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
        <span data-toggle="tooltip" data-delay='{"show":"500", "hide":"100"}' data-html="true" 
          data-title="click here to shorten the selected fleet at the end of their next lap" data-placement="top"> Fleet Shorten Course <span class="caret"></span> </span>$shorten_forgotten_msg</button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
        <li class="dropdown-header">pick fleet - will shorten to lap of current leader</li>
        <li role="separator" class="divider"></li>
        $fleet_opt
        $shorten_forgotten_li
        </ul>
</div>

EOT;

    // final page body layout
    $html = <<<EOT
    <div class="row margin-top-40" >
        <div class="col-md-5 btn-group pull-left"  style="display: block;">$view_option</div>
        <div class="col-md-3 btn-group pull-left" style="display: block;">$shorten_bufr</div>
        <div class="col-md-4 pull-right text-danger" style="display: block;">
           <blockquote style="padding-left: 5px; padding-right: 0px">$last_click_txt $colour_key</blockquote>
        </div>        
    </div>
    <div class="clearfix"></div>
    <div>
            $view_bufr
    </div>
EOT;

    return $html;
}

function timer_list_view($eventid, $data, $view, $rows = 1)
{
    // link details for boat controls
    $timelap_link = "timer_sc.php?eventid=$eventid&pagestate=timelap";
    $undo_link    = "timer_sc.php?eventid=$eventid&pagestate=undoboat";
    $bunch_link   = "timer_sc.php?eventid=$eventid&pagestate=bunch&action=addnode";
    $finish_link  = "timer_sc.php?eventid=$eventid&pagestate=finish";
    $edit_link    = "";  // fixme no edit on timer page - is this needed - could add it to menu and use same popup form as tabbed

    // boat state info
    $boat_states = array(
        "beforelap" => array("color"=>"btn-default", "val"=>"racing",     "index" => "racing"),
        "racing"    => array("color"=>"btn-info",    "val"=>"racing",     "index" => "racing"),
        "notracing" => array("color"=>"btn-primary", "val"=>"not racing", "index" => "notracing" ),
        "lastlap"   => array("color"=>"btn-warning", "val"=>"last lap",   "index" => "lastlap"),
        "finished"  => array("color"=>"btn-success", "val"=>"finished",   "index" => "finished"),
    );


    if ($view == "fleet")
    {
        $configured = true;  // fixme this needs to be set somewhere
        $category = array();
        $dbuf = array();
        for ($i=1; $i <= $_SESSION["e_$eventid"]['rc_numfleets']; $i++)
        {
            $category[$i] = $_SESSION["e_$eventid"]["fl_$i"]['code'];
            $dbufr[$i] = array();
        }

        if ($configured) {
            foreach ($data as $item => $group) {
                foreach ($group as $entry) {
                    empty($entry['classcode']) ? $classcode = explode(' ',trim($entry['class']))[0]." - ".$entry['sailnum'] : $classcode = $entry['classcode'] ;

                    $dbufr[$item][] = array(
                        "entryid" => $entry['id'],
                        "class"   => $entry['class'],
                        "sailnum" => $entry['sailnum'],
                        "boat"    => $entry['class']." - ".$entry['sailnum'],
                        "helm"    => $entry['helm'],
                        "fleet"   => $entry['fleet'],
                        "start"   => $entry['start'],
                        "lap"     => $entry['lap'],
                        "finishlap" => $entry['finishlap'],
                        "code"    => $entry['code'],
                        "pn"      => $entry['pn'],
                        "etime"   => $entry['etime'],
                        "status"  => $entry['status'],
                        "declaration" => $entry['declaration'],
                        "label"   => $classcode."&nbsp;&nbsp;".$entry['sailnum'],
                        //"label"   => strtoupper(substr($entry['class'], 0, 3))."&nbsp;&nbsp;".$entry['sailnum'],
                        //"bunchlbl"=> explode(' ',trim($entry['class']))[0]." - ".$entry['sailnum']
                        "bunchlbl"=> $entry['class']." - ".$entry['sailnum']
                    );
                }
            }
        }
    }

    elseif ($view == "class")
    {
        $configured = true;     // fixme this needs to be set somewhere
        $category = array();
        $i = 0;
        foreach($_SESSION["e_$eventid"]['classes'] as $k=>$v)                               // establish categories
        {
            $i++;
            $category[$i] = $k;
        }
        sort($category);                                                                    // sort categories alphabetically
        $category = array_combine(range(1, count($category)), array_values($category));     // reindex from 1
        $category[] = "MISC";                                                               // add MISC category


        if ($configured)    // FIXME - what happens if this is not configured
        {
            $dbuf = array();
            for ($i = 1; $i <= count($category); $i++) {
                $dbufr[$i] = array();
            }

            foreach ($data as $class => $group) {
                foreach ($group as $entry) {
                    empty($entry['classcode']) ? $classcode = explode(' ',trim($entry['class']))[0]." - ".$entry['sailnum'] : $classcode = $entry['classcode'] ;

                    $arr = array(
                        "entryid" => $entry['id'],
                        "class"   => $entry['class'],
                        "sailnum" => $entry['sailnum'],
                        "boat"    => "{$entry['class']} - {$entry['sailnum']}",
                        "helm"    => $entry['helm'],
                        "fleet"   => $entry['fleet'],
                        "start"   => $entry['start'],
                        "lap"     => $entry['lap'],
                        "finishlap" => $entry['finishlap'],
                        "code"    => $entry['code'],
                        "pn"      => $entry['pn'],
                        "etime"   => $entry['etime'],
                        "status"  => $entry['status'],
                        "declaration" => $entry['declaration'],
                        //"bunchlbl"=> explode(' ',trim($entry['class']))[0]." - ".$entry['sailnum']
                        "bunchlbl"=> $entry['class']." - ".$entry['sailnum']
                    );

                    $set = false;
                    for ($i = 1; $i < count($category); $i++) {
                        if (strtolower($entry['class']) == strtolower($category[$i])) {
                            $arr['label'] = $entry['sailnum']; // only need sailnum for label
                            $dbufr[$i][] = $arr;
                            $set = true;
                            break;
                        }
                    }

                    // if not set add to misc group with class label
                    if (!$set) {
                        $arr['label'] = $classcode."&nbsp;&nbsp;".$entry['sailnum'];
                        $dbufr[count($category)][] = $arr;
                    }
                }
            }
        }
    }

    else   // sailnumber view
    {
        $configured = true;    // fixme this needs to be set somewhere
        $category = array(1=>"1 &hellip;", 2=>"2 &hellip;", 3=>"3 &hellip;", 4=>"4 &hellip;", 5=>"5 &hellip;", 6=>"6 &hellip;", 7=>"7 &hellip;", 8=>"8 &hellip;", 9=>"9 &hellip;", 10=>"other",);
        $dbufr = array(1=>array(), 2=>array(), 3=>array(), 4=>array(), 5=>array(), 6=>array(), 7=>array(), 8=>array(), 9=>array(), 10=>array() );

        if ($configured) {
            foreach ($data as $item => $group) {
                foreach ($group as $entry) {
                    empty($entry['classcode']) ? $classcode = explode(' ',trim($entry['class']))[0]." - ".$entry['sailnum'] : $classcode = $entry['classcode'] ;
                    $dbufr[$item][] = array(
                        "entryid" => $entry['id'],
                        "class"   => $entry['class'],
                        "sailnum" => $entry['sailnum'],
                        "boat"    => "{$entry['class']} - {$entry['sailnum']}",
                        "helm"    => $entry['helm'],
                        "fleet"   => $entry['fleet'],
                        "start"   => $entry['start'],
                        "lap"     => $entry['lap'],
                        "finishlap" => $entry['finishlap'],
                        "code"    => $entry['code'],
                        "pn"      => $entry['pn'],
                        "etime"   => $entry['etime'],
                        "status"  => $entry['status'],
                        "declaration" => $entry['declaration'],
                        "label"   => $classcode."&nbsp;&nbsp;".$entry['sailnum'],
                        //"label"   => strtoupper(substr($entry['class'], 0, 3))."&nbsp;&nbsp;".$entry['sailnum'],
                        //"bunchlbl"=> explode(' ',trim($entry['class']))[0]." - ".$entry['sailnum']
                        "bunchlbl"=> $entry['class']." - ".$entry['sailnum']
                    );
                }
            }
        }
    }

    if (empty($dbufr)) {
        $html = <<<EOT
            <div role="tabpanel" class="tab-pane" id="fleet$i">
                <div class="alert alert-info text-center" role="alert" style="margin-right: 40%;">
                   <h3>no entries - nothing to display</h3><br>
                </div>
            </div>
EOT;
    }

    elseif ($configured)
    {
        $html = "";

        $label_bufr = "<div class='row'>";
        $data_bufr  = "<div class='row' style='margin-left: 10px; margin-bottom: 10px'>";
        foreach ($category as $i => $label) {
            // flush buffers if we need to go to second or third row (6 columns per row)
            if ($i % 7 === 0)
            {
                $html.= $label_bufr . $data_bufr;
                $label_bufr = "</div><br><br><div class='row'>";
                $data_bufr  = "</div><div class='row' style='margin-left: 10px; margin-bottom: 10px'>";
            }

            // category labels
            $view == "fleet" ? $laps_txt = " <small> {$_SESSION["e_$eventid"]["fl_$i"]['maxlap']} lap(s)</small>" : $laps_txt = "" ;
            $label_bufr .= <<<EOT
            <div class="col-md-2 text-center"><h4 style="margin-bottom: 4px;"><b>$label</b></h4> $laps_txt</div>
EOT;

            // boat buttons
            $data_bufr .= "<div class='col-md-2' style='padding: 0px 0px 0px 0px;'>";
            foreach ($dbufr[$i] as $entry) {

                $boat = "{$entry['class']} - {$entry['sailnum']}";
                $laps = $_SESSION["e_$eventid"]["fl_{$entry['fleet']}"]['maxlap'];
                $scoring = $_SESSION["e_$eventid"]["fl_{$entry['fleet']}"]['scoring'];

                if ($scoring == "average")
                {
                    if ( $entry['status'] == "X" )
                    {
                        $state = $boat_states['notracing'];
                    }
                    elseif ($entry['lap'] > 0)
                    {
                        if ( $entry['status'] == "F" )
                        {
                            { $state = $boat_states['finished']; }
                        }
                        else
                        {
                            if ( $entry['lap'] < $entry['finishlap'] - 1 )      { $state = $boat_states['racing']; }
                            elseif ( $entry['lap'] >= $entry['finishlap'] )     { $state = $boat_states['finished']; }
                            elseif ( $entry['lap'] == $entry['finishlap'] - 1 ) { $state = $boat_states['lastlap']; }
                        }
                    }
                    else
                    {
                        $state = $boat_states['beforelap'];
                    }
                }
                else
                {
                    if ( $entry['status'] == "X" )
                    {
                        $state = $boat_states['notracing'];
                    }
                    elseif ($entry['lap'] > 0)
                    {
                        if ( $entry['lap'] < $laps - 1 )      { $state = $boat_states['racing']; }
                        elseif ( $entry['lap'] >= $laps )     { $state = $boat_states['finished']; }
                        elseif ( $entry['lap'] == $laps - 1 ) { $state = $boat_states['lastlap']; }
                    }
                    else
                    {
                        $state = $boat_states['beforelap'];
                    }
                }

                empty($entry['code']) ? $cog_style = "primary" : $cog_style = "danger";

                // competitor identity and lap count
                $title = $entry['label'];
                $lapcount = "L {$entry['lap']}";

                // set special style for block if it was the last one clicked
                $last_click_style = "";
                if (array_key_exists("entryid", $_SESSION["e_$eventid"]['lastclick']))
                {
                    if ($_SESSION["e_$eventid"]['lastclick']['entryid'] == $entry['entryid']) { $last_click_style = "border: solid 5px crimson;"; }
                }


                // popover information
                //$ptitle = "<b>{$entry['class']} - {$entry['sailnum']}</b> - ".strtoupper($state['val']);
                $ptitle = <<<EOT
                    <h4><b><span class='text-info'>{$entry['class']} - {$entry['sailnum']}</span></b></h4> {$entry['helm']} - &nbsp;&nbsp;<i>{$state['val']}</i>
EOT;
                $etime = gmdate("H:i:s", $entry['etime']);
                $pcontent = <<<EOT
                    <p class='timer-tt-lap'>&nbsp;&nbsp;lap: &nbsp;{$entry['lap']}</p>
                    <p>[ $etime ]&nbsp;&nbsp;&nbsp;<span class='lead text-danger'><b>{$entry['code']}</b></span></p>
EOT;


                // set params for link options
                unset($entry['class']);
                unset($entry['sailnum']);
                $params_list = "&" . http_build_query($entry);

                // setcode link
                $link = <<<EOT
timer_sc.php?eventid=$eventid&pagestate=setcode&fleet={$entry['fleet']}&entryid={$entry['entryid']}&boat={$entry['boat']}
&racestatus={$entry['status']}&declaration={$entry['declaration']}&lap={$entry['lap']}&finishlap=$laps}
EOT;
                // finish/bunch menu options
                if ($entry['status'] == "F")
                {
                    $finish_option = "";
                    $bunch_option = "";
                }
                else
                {
                    if ($scoring == "average")
                    {
                        $finish_option = <<<EOT
                            <li><a href="$finish_link$params_list">Finish</a></li>
EOT;
                    }
                    else
                    {
                        $finish_option = "";
                    }

                    $bunch_option = <<<EOT
                        <li><a href="$bunch_link$params_list">Bunch</a></li>
EOT;
                }


                $options_bufr = <<<EOT
                <ul class="dropdown-menu">
                    <li><a href="$undo_link$params_list">Undo Last Timing</a></li>
                    $bunch_option
                    $finish_option
                    <!-- li><a href="timer_sc.php?">Edit (future)</a></li -->
                    <li class="divider"></li>
                    <li><a href="$link&code=">clear code</a></li>  <!--FIXME don't display this if no code set -->
                    <li><a href="$link&code=DNC">set DNC</a></li>
                    <li><a href="$link&code=DNF">set DNF</a></li>
                    <li><a href="$link&code=DNS">set DNS</a></li>
                    <li><a href="$link&code=NSC">set NSC</a></li>
                    <li><a href="$link&code=OCS">set OCS</a></li>
                </ul>
EOT;
                if ($entry['status'] == "F")
                {
                    $disabled = "disabled"; // turn off lap timing for boats that have finished
                    $bunch_btn = "";        // don't display bunch button for boats that have finished
                }
                else
                {
                    $disabled = "";
                    $bunch_btn = <<<EOT
                    <a type="button" href="$bunch_link$params_list" class="btn btn-info btn-sm" aria-haspopup="true" aria-expanded="false">
                        <span class="glyphicon glyphicon-pushpin" aria-hidden="true"></span>
                    </a>
EOT;
                }

                $data_bufr .= <<<EOT
                <div class="btn-group btn-block" role="group" aria-label="..." >
                    <div id="boat-block" data-toggle="popover" data-container="body" data-placement="right"  title="$ptitle" data-content="$pcontent">
                        <a type="button" href="$timelap_link$params_list" class="btn {$state['color']} btn-sm $disabled" style="width:60%; $last_click_style" 
                            >
                            <div class="pull-left">$title</div>
                            <div class="pull-right">$lapcount</div>     
                        </a>                 
                        <button type="button" class="btn btn-$cog_style btn-sm dropdown-toggle" 
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
                        </button>
                        $options_bufr
                        $bunch_btn
                    </div>
                </div>
EOT;

            }
            $data_bufr .= "</div>";

        }
        $label_bufr .= "</div>";
        $data_bufr .= "</div>";


        $html.= $label_bufr . $data_bufr;
    }
    else
    {
        $html = <<<EOT
        <div class="pull-left text-info"  style="display: block;">
            <blockquote>
                <h4>This view is not configured - please contact your system administrator</h4>
            </blockquote>
        </div>
EOT;

    }

    return $html;
}



function timer_tabs($params = array())
{
    global $tmpl_o;
    global $btn_shortenfleet;
    global $mdl_shortenfleet;

    // display for tabbed view
    $eventid = $params['eventid'];

    $tabs = "";
    $panels = "";

    $state_cfg = array(
        "default"  => array("row_style" => "default",  "label_style" => "laptime-racing",  "annotation" => " "),
        "racing"   => array("row_style" => "racing",   "label_style" => "laptime-racing",  "annotation" => " "),
        "finished" => array("row_style" => "finished", "label_style" => "laptime-finish",  "annotation" => " FINISHED"),
        "lastlap"  => array("row_style" => "lastlap",  "label_style" => "laptime-lastlap", "annotation" => " LAST LAP"),
        "excluded" => array("row_style" => "excluded", "label_style" => "laptime-finish",  "annotation" => " NOT RACING"),
    );

    $url_base         = "timer_sc.php?eventid=$eventid";
    $timelap_link     = $url_base."&pagestate=timelap&fleet=%s&start=%s&entryid=%s&boat=%s&lap=%s&finishlap=%s&pn=%s&etime=%s&status=%s";
    $finish_link_tmpl = $url_base."&pagestate=finish&fleet=%s&start=%s&entryid=%s&boat=%s&lap=%s&finishlap=%s&pn=%s&etime=%s&status=%s";
    $undoboat_link    = $url_base."&pagestate=undoboat&entryid=%s";
//  $setcode_link  = $url_base."&pagestate=setcode&fleet=%s&entryid=%s&boat=%s&racestatus=%s";


    //echo "<pre>".print_r($_SESSION["e_$eventid"],true)."</pre>";
    $shorten_reminder = "";
    for ($i = 1; $i <= $params['num-fleets']; $i++)   // loop for each fleet
    {
        // fixme - would be good not to use session variables
        $fleet        = $_SESSION["e_$eventid"]["fl_$i"];
        $fleet_name   = strtolower($fleet['name']);
        $num_racing   = count($params['timings'][$i]);
        $all_finished = "";
        $laps_btn     = "";

        // create TABS
        $tabs.= <<<EOT
        <li role="presentation" class="lead text-center">
              <a class="text-primary" href="#fleet$i" aria-controls="$fleet_name" role="tab" data-toggle="pill" style="padding-top: 20px;">
              <b>$fleet_name</b>              
              </a>
        </li>
EOT;

        // create PANELS
        if (empty($params['timings'][$i]))    // no entries for this fleet
        {
            $panels .= <<<EOT
            <div role="tabpanel" class="tab-pane" id="fleet$i">
                <div class="alert alert-info text-center" role="alert" style="margin-right: 40%;">
                   <h3>no entries in the $fleet_name fleet</h3><br>
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
                       <span><b>all finished - no more boats to time in the $fleet_name fleet </b></span><br>
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
                            <div class="col-sm-12 text-left" >
                                <a href="#setlapsModal" data-toggle="modal" class="btn btn-danger btn-lg margin-top-0" aria-expanded="false" role="button" >
                                    <span class="glyphicon glyphicon-exclamation-sign"></span>
                                    &nbsp;NO LAPS set for this fleet - click here to set laps&nbsp;
                                </a>
                            </div>
                        </div>
EOT;
                    }
                    else   // shorten laps button
                    {
                        if ($fleet['maxlap'] <= 1)    // info only
                        {
                            $laps_btn = <<<EOT
                            <div class="row margin-top-0">
                                <div class="col-sm-12 text-left" >
                                    <div class="btn-group ">
                                        <button class="btn btn-info btn-md margin-top-0" style="color: white;" aria-expanded="false" role="button" disabled >
                                            <span class="glyphicon glyphicon-flag"></span>&nbsp;{$fleet['maxlap']} LAPS &nbsp;
                                        </button>
                                    </div>
                                </div>
                            </div>
    
EOT;
                        }
                        else      // clickable shorten button
                        {
                            $upper_fleet = strtoupper($fleet['name']);
                            $btn_shortenfleet['fields']['id'] = "shortenfleet$i";
                            $btn_shortenfleet['fields']['label'] = "{$fleet['maxlap']} LAPS - click to SHORTEN COURSE";
                            $mdl_shortenfleet['fields']['id'] = "shortenfleet$i";
                            $mdl_shortenfleet['fields']['title'] = "Shorten Course - $upper_fleet fleet";
                            $mdl_shortenfleet['fields']['body'] = <<<EOT
                                <h3>Are you sure?</h3>
                                <div style = "font-size: 1.0em; margin-left: 40px;">
                                <p> This will shorten the $upper_fleet fleet so that the leaders will finish on their CURRENT lap</p>
                                <p>Click the <b>Shorten this Fleet</b> button below to confirm this change</p>
                                <p class="text-danger"><i>[Note: to undo this change - use the Undo Shorten Course option]</i></p>
                                </div>
                                
EOT;
                            $mdl_shortenfleet['fields']['action'] = "timer_sc.php?eventid=$eventid&pagestate=shorten&fleet=$i";

                            $laps_btn.= $tmpl_o->get_template("btn_modal", $btn_shortenfleet['fields'], $btn_shortenfleet);
                            $laps_btn.= $tmpl_o->get_template("modal", $mdl_shortenfleet['fields'], $mdl_shortenfleet);


                            // set shorten course reminder if configured reminder time is passed for this fleet
                            $shorten_reminder = "";
                            if ($_SESSION['racebox_shorten_reminder'] != 0 AND (time() - $fleet['starttime']) > ($_SESSION['racebox_shorten_reminder'] * 60))
                            {
                                $shorten_reminder = <<<EOT
                            <span style="color: red;">forgotten to shorten course? </span>
                            <a href="help_pg.php?eventid=$eventid&page=timer&helpid=15" >
                            <button  
                               class="btn btn-default btn-md" style="border: solid 2px red !important; background-color: white !important;">
                                  <span style="color: red !important; "><span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>&nbsp;&nbsp; <b>Get Help Here</b></span>
                            </button>
                            </a>
EOT;
                            }

                        }
                    }
                }
            }

            // create table rows
            $rows = "";
            $finish_btn_tmpl = <<<EOT
                <span data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true" data-title="%s" data-placement="top">
                <a id="finish" href="%s" role="button" class="btn btn btn-%s btn-xs %s" target="">
                    <span class="glyphicon glyphicon-volume-up"></span>
                </a>
                </span>
EOT;
            //echo "<pre>".print_r($params['timings'],true)."</pre>";
            $row_num = 0;
            foreach ($params['timings'][$i] as $j=>$r)   // loop over each boat in this fleet
            {
                $row_num++;
                $boat = "{$r['class']} - {$r['sailnum']}";
                $finish_link = vsprintf($finish_link_tmpl,
                    array($r['fleet'], $r['start'], $r['id'], $boat, $r['lap'], $r['finishlap'], $r['pn'], $r['etime'], $r['status'] ));

                $current_lap = $r['lap'] + 1;
                $cfg = $state_cfg['default'];
                $finish_btn  = "";

                if ($r['status'] == "R")  // racing
                {
                    $skip = "";
                    // finish button
                    //if ($current_lap == $fleet['maxlap'] OR
                    if ($current_lap == $r['finishlap'] OR
                        ($_SESSION["e_$eventid"]["fl_$i"]['status'] == "finishing" AND $_SESSION["e_$eventid"]["fl_$i"]['scoring'] == "average" ))                            // boat is on last lap
                    {
                        $cfg = $state_cfg['lastlap'];
                        if ($fleet['scoring'] != "pursuit")                          // show finish button unless pursuit
                        {
                            $finish_btn  = vsprintf($finish_btn_tmpl, array("finish boat", $finish_link, "danger", " "));
                        }
                    }
                    else                                                              // not on last lap
                    {
                        $cfg = $state_cfg['racing'];
                        if  ($fleet['scoring'] == "handicap" OR $fleet['scoring'] == "level")
                        {
                            $finish_btn  = vsprintf($finish_btn_tmpl, array("can't finish - not on last lap", $finish_link, "default", "disabled"));
                        }
                        elseif ($fleet['scoring'] == "average")
                        {
                            $finish_btn  = vsprintf($finish_btn_tmpl, array("finish boat - ignoring lap", $finish_link, "warning", ""));
                        }
                    }
                }
                elseif ($r['status']=="F")    // finished
                {
                    $cfg = $state_cfg['finished'];
                    $finish_btn  = "&nbsp;";
                    $skip = "rowlink-skip";
                }
                elseif ($r['status']=="X")    // excluded (but carry on timing)
                {
                    $cfg = $state_cfg['excluded'];
                    $finish_btn  = "&nbsp;";
                    //$skip = "rowlink-skip";
                    $skip = "";
                }
                else
                {
                    $cfg = $state_cfg['default'];
                    $finish_btn  = "&nbsp;";
                    $skip = "rowlink-skip";
                }

                $laptimes_bufr = laptimes_html($r['laptimes'], $cfg);

                $row_link = vsprintf($timelap_link,
                    array($r['fleet'], $r['start'], $r['id'], $boat, $r['lap'], $r['finishlap'], $r['pn'], $r['etime'], $r['status'] ));

                // FIXME  - ugly - hide this in get_code
                $link = <<<EOT
timer_sc.php?eventid=$eventid&pagestate=setcode&fleet={$r['fleet']}&entryid={$r['id']}&boat=$boat
&racestatus={$r['status']}&declaration={$r['declaration']}&lap={$r['lap']}&finishlap={$r['finishlap']}
EOT;

                $row_num >= 10 ? $dirn = "dropup" : $dirn = "" ;
                $code_link = get_code($r['code'], $link, "timercodes", $dirn);

                $edit_link = editlaps_html($eventid, $r['id'], $boat, $r['laptimes']);


                if ($_SESSION['racebox_timer_bunch'])
                {
                    $bunch_label = "bunch";
                    $bunch_link = "&nbsp;";
                    if ($r['status'] != "F") { $bunch_link = bunch_html($eventid, $r['id'], $boat, $r); }
                }
                else
                {
                    $bunch_label = "&nbsp;";
                    $bunch_link = "&nbsp;";
                }


                $undo_link = undoboat_html($undoboat_link, $eventid, $r['id'], $boat, $r['laptimes']);

                $rows.= <<<EOT
                    <tr class="table-data {$cfg['row_style']}">
                        <td style="width: 1%;"><a href="$row_link" ></a></td>
                        <td class="$skip truncate" >{$r['class']}</td>
                        <td class="$skip" ><span style="font-size: 1.1em; color: darkblue"><b>{$r['sailnum']}</b></span></td>
                        <td class="$skip truncate" style="padding-left:10px;">{$r['helm']}</td>
                        <td class="$skip" style="padding-left:15px;" >$laptimes_bufr</td>
                        <td class="rowlink-skip inline-button">$code_link</td>
                        <td class="rowlink-skip inline-button">$bunch_link</td>
                        <td class="rowlink-skip inline-button">$finish_btn</td>
                        <td class="rowlink-skip inline-button">$edit_link</td>
                        <td class="rowlink-skip inline-button">$undo_link</td>
                    </tr>
EOT;
            }

            // put panel table layout together
            $panels.= <<<EOT
            <div role="tabpanel" class="tab-pane margin-top-0" id="fleet$i">
                $all_finished
                <div class="row">
                    <div class = "col-sm-3">$laps_btn</div>
                    <div class = "col-sm-4">&nbsp;$shorten_reminder</div>               
                </div>
                <table class="table table-striped table-condensed table-hover table-top-padding table-top-border" style="width: 100%; table-layout: fixed;">
                    <thead class="text-info" >
                        <tr >
                            <th width="1%"></th>
                            <th width="10%" style="text-align: center">class</th>
                            <th width="5%"  style="text-align: center">sail no.</th> 
                            <th width="10%" style="text-align: center">helm</th>                           
                            <th style="text-align: center">lap times</th>
                            <th width="7%" style="text-align: center">code</th>
                            <th width='5%' style='text-align: center'>$bunch_label</th>                           
                            <th width="5%" style="text-align: center">finish</th>                           
                            <th width="5%" style="text-align: center">edit</th>
                            <th width="5%" style="text-align: center">undo</th>
                        </tr>
                    </thead>
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
        <ul class="nav nav-pills pill-fleet" role="tablist">
           $tabs
        </ul>
        <div class="tab-content">
           $panels
        </div>
    </div>
EOT;
    return $html;
}



function laptimes_html($laptimes_str, $cfg)
    /*
     * displays lap times on timer page
     */
{
    $lap_cnt = 0;
    $max_display = 6;   // fixme - make configurable
    $label_style = $cfg['label_style'];
    $style = "margin-left: 5px";

    $bufr = "";
    if (!empty($laptimes_str))
    {
        $laptimes = explode(",", $laptimes_str);
        $lap_cnt = count($laptimes);
        $j = 0;
        foreach ($laptimes as $lap=>$laptime)
        {
            $j++;
            $laptime > 3600 ? $formattedtime = gmdate("H:i:s", $laptime) : $formattedtime = gmdate("i:s", $laptime);

            if ($lap_cnt <= $max_display)
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

    if ($cfg['row_style'] =="lastlap" or $cfg['row_style'] =="racing")         // add clock icon if necessary
    {
        $bufr.= "<span class='text-primary glyphicon glyphicon-time'></span>";
    }

    $bufr.= "<span class='pull-right text-right'>&nbsp;&nbsp;{$cfg['annotation']}</span>";
    //$bufr.= "&nbsp;&nbsp;{$cfg['annotation']}";

    return $bufr;
}

function editlaps_html($eventid, $entryid, $boat, $laptimes_str)
{
    if (!empty($laptimes_str))
    {
        $bufr = <<<EOT
        <span data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true"
              data-title="edit lap times for this boat" data-placement="top">
            <a type="button" class="btn btn-success btn-xs" data-toggle="modal" data-target="#editlapModal" data-boat="$boat"
                    data-iframe="timer_editlaptimes_pg.php?eventid=$eventid&pagestate=init&entryid=$entryid&clear=1" >
                    <span class="glyphicon glyphicon-pencil"></span>
            </a>
        </span>
EOT;
    }
    else
    {
        $bufr = "";
    }


    return $bufr;
}

function undoboat_html($link, $eventid, $entryid, $boat, $laptimes_str)
{
    if (!empty($laptimes_str))
    {
        $link = vsprintf($link, array($entryid));

        $bufr = <<<EOT
        <span data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true" data-title="remove last lap time for this boat" data-placement="top">
            <a id="undoboat" type="button" href="$link" role="button" class="btn btn-warning btn-xs" >
                <span class="glyphicon glyphicon-step-backward"></span>
            </a>
        </span>
EOT;
    }
    else
    {
        $bufr = "";
    }
    return $bufr;
}


function bunch_html($eventid, $entryid, $boat, $r)
    // creates button to add a boat to the bunch list
{
    //u_writedbg("<pre>bunch_hmtl".print_r($r,true)."</pre>",__CLASS__,__FUNCTION__,__LINE__);

    // array to pass data to bunch process
    empty($r['classcode']) ? $classcode = explode(' ',trim($r['class']))[0]." - ".$r['sailnum'] : $classcode = $r['classcode'];
    $params = array(
        "entryid" => $entryid,
        "boat"    => $boat,
        "fleet"   => $r['fleet'],
        "start"   => $r['start'],
        "lap"     => $r['lap'],
        "finishlap" => $r['finishlap'],
        "pn"      => $r['pn'],
        "etime"   => $r['etime'],
        "status"  => $r['status'],
        "bunchlbl"=> $r['class']." - ".$r['sailnum']
    );

    $link_txt = "timer_sc.php?eventid=$eventid&pagestate=bunch&action=addnode&".http_build_query($params);

    $bufr = <<<EOT
    <span data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true" data-title="save for bunch" data-placement="top">
        <a id="bunchboat" type="button" href="$link_txt" role="button" class="btn btn-info btn-xs" >
            <span class="glyphicon glyphicon-pushpin"></span>
        </a>
    </span>
EOT;

    return $bufr;
}


function problems($params=array())
{
    $html = "";

    $msg = array(
        "timer" => array(
            "title" => "Timer has not been started",
            "info"  => "Go to the start page and start the main timer at the same time as your first preparatory signal",
            "link"  => "start_pg.php?eventid={eventid}&menu=true",
            "label" => "Start Page",
        ),
        "laps" => array(
            "title" => "Laps have not been set for any fleet",
            "info"  => "Go to the status page and set the number of laps you want each fleet to sail",
            "link"  => "race_pg.php?eventid={eventid}&menu=true",
            "label" => "Status Page",
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
            <div class="row margin-top-20">
            <div class="col-md-8 col-md-offset-2 ">
                <div class="alert alert-info alert-dismissible" role="alert">
                   <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                   <h3><b>{$data['title']}</b></h3>
                   <p class="lead">{$data['info']}</p>
                   <p class=" text-right"> 
                       <a type="button" class="btn btn-primary" href="{$data['link']}">
                            <span class="glyphicon glyphicon-menu-right"></span><b> {$data['label']}</b>
                       </a>
                   </p>
               </div>
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
    $lapbufr = "";
    $msgbufr = "";
    $eventid = $params['eventid'];

    // hidden fields
    $lapbufr.= <<<EOT
    <input type="hidden" name="eventid" value="{eventid}">
    <input type="hidden" name="entryid" value="{entryid}">
    <input type="hidden" name="fleet" value="{fleet}">
    <input type="hidden" name="boat" value="{boat}">
    <input type="hidden" name="pn" value="{pn}">
EOT;

//    // initial add lap button
//    $bufr.= <<<EOT
//        <div class="row">
//            <div class="col-md-offset-1 col-md-4 text-center">
//                <a id="xxx" href="timer_editlaptimes_pg.php?pagestate=addlap&eventid={eventid}&entryid={entryid}&lap=1"
//                   role="button" class="btn btn-default btn-xs" target="_self">
//                    <span class="glyphicon glyphicon-plus"></span>&nbsp;add new lap 1
//                </a>
//            </div>
//            <div class="col-md-offset-3 col-md-4">
//                &nbsp;
//            </div>
//        </div>
//EOT;

    // loop over lap times - field names are laptime[lap]
    $i = 1;
    foreach ($params['laps'] as $laptime)
    {
        $formatted_time = gmdate("H:i:s", $laptime);
        $lapbufr.= <<<EOT
        <div class="row margin-top-10">
            <div class="col-md-offset-1 col-md-7 text-center">
                <a id="xxx" href="timer_editlaptimes_pg.php?pagestate=addlap&eventid={eventid}&entryid={entryid}&lap=$i"
                   role="button" class="btn btn-default btn-xs" target="_self" style="min-width: 150px;">
                    <span class="glyphicon glyphicon-plus"></span>&nbsp;add new lap $i
                </a>
            </div>
            <div class="col-md-4">
                &nbsp;
            </div>
        </div>
        <div class="row margin-top-10">
            <div class="col-md-offset-1 col-md-7">
                <div class="form-group" style="min-width: 30%">
                    <label for="lap$i">lap $i &nbsp;</label>
                    <input type="text" class="form-control" id="lap$i" name="etime[$i]" value="$formatted_time"
                        required data-fv-notempty-message="a time [hh:mm:ss] must be entered"
                        data-fv-regexp="true"
                        data-fv-regexp-regexp="^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$"
                        data-fv-regexp-message="lap time must be in HH:MM:SS format" />
                </div>
            </div>
            <div class="col-md-4 text-center" style="margin-top: 7px;">
                <a id="xxx" href="timer_editlaptimes_pg.php?pagestate=removelap&eventid={eventid}&entryid={entryid}&lap=$i"
                   role="button" class="btn btn-warning btn-xs" target="_self" style="min-width: 150px;">
                    <span class="glyphicon glyphicon-minus "></span>&nbsp;remove this lap $i
                </a>
            </div>
        </div>

        
EOT;
        $i++;
    }

    // final add lap
    $lapbufr.= <<<EOT
        <div class="row margin-top-10">
            <div class="col-md-offset-1 col-md-7 text-center">
                <a id="xxx" href="timer_editlaptimes_pg.php?pagestate=addlap&eventid={eventid}&entryid={entryid}&lap=$i"
                   role="button" class="btn btn-default btn-xs" target="_self" style="min-width: 150px;">
                    <span class="glyphicon glyphicon-plus"></span>&nbsp;add new lap $i
                </a>
            </div>
            <div class="col-md-4">
                &nbsp;
            </div>
        </div>
EOT;

    // set finish button


    //echo "<pre>MSG: ".print_r($_SESSION["e_$eventid"]['lapeditmsg'],true)."</pre>";
    // create msg_bufr
    if (empty($_SESSION["e_$eventid"]['lapeditmsg']['type']))
    {
        $msgbufr = "";
    }
    else
    {
        $msgbufr = <<<EOT
        <div class="alert alert-{$_SESSION["e_$eventid"]['lapeditmsg']['type']}" role="alert">
            {$_SESSION["e_$eventid"]['lapeditmsg']['msg']}
        </div>
EOT;
    }

    $html = <<<EOT
    
    <form id="editlapForm" class="form-inline" action="timer_editlaptimes_pg.php?pagestate=submit" method="post"
        data-fv-framework="bootstrap"
        data-fv-icon-valid="glyphicon glyphicon-ok"
        data-fv-icon-invalid="glyphicon glyphicon-remove"
        data-fv-icon-validating="glyphicon glyphicon-refresh"
    >
        <div class="row">
            <div class="col-md-7">
                $lapbufr
            </div>
            
            <div class="col-md-offset-1 col-md-4 margin-top-40">
                $msgbufr
                <div class="well well-md">
                    <p>When you have completed the edits - click the exit button to return to the timer page</p>    
                </div>
            </div>
        </div>
        
        
        <div class="well well-lg" style="position:absolute; bottom:0;width: 95%" role="alert">
            <div class="row">
                <div class="col-md-8 text-info" style="font-size: 1.2em">
                    <p>Edit the lap times and click the UPDATE button to save them</p> 
                    <p>If you add or remove a lap the system will renumber them for you</p>
                </div>
                <div class="col-md-4 text-right">
                    <button type="submit" class="btn btn-success">
                        <span class="glyphicon glyphicon-ok"></span>&nbsp;UPDATE Lap Times
                    </button>
                </div>
            </div>                         
        </div>
       
    </form>

EOT;

    return $html;
}


//function edit_laps_success($params=array())
//{
//    if ($params['changes'])
//    {
//        $msg = <<<EOT
//            <b>Successful changes</b> were made to the lap times for {boat}.<br><span style="text-indent: 30px;">{msg}</span>
//EOT;
//    }
//    else
//    {
//        $msg = <<<EOT
//            <b>No changes</b> were made to the lap times for {boat}.
//EOT;
//    }
//
//    $html = <<<EOT
//    <div class="alert alert-success" role="alert" style="margin-top: 30px">
//        <p style="font-size: 100%;">$msg</span></p>
//        <br>
//        <p>Use the <b>More Changes</b> button to make more changes or the <b>Finished</b> button to return to the Lap Timing page</p>
//    </div>
//
//    <div class="row pull-right">
//        <a href="timer_editlaptimes_pg.php?eventid={eventid}&entryid={entryid}&pagestate=init" class="btn btn-default btn-md active" style="width:200px" role="button">
//        <span class="glyphicon glyphicon-step-backward" aria-hidden="true"></span>
//         More Changes &hellip; </a>
//    </div>
//
//EOT;
//    return $html;
//}


//function edit_laps_error($params=array())
//{
//    $html = <<<EOT
//    <div class="alert alert-danger" role="alert"  style="margin-top: 30px">
//    <p style="font-size: 100%;"><b>No changes</b> were made to the lap times for {boat}<br></p>
//    <p>The following problems were found with the times you entered .</p>
//    <span style="text-indent: 30px;">{msg}</span>
//    <p>Use the <b>More Changes</b> button to make more changes or the <b>Finished</b> button to return to the Lap Timing page</p>
//    </div>
//    <div class="row pull-right">
//        <a href="timer_editlaptimes_pg.php?eventid={eventid}&entryid={entryid}&pagestate=init" class="btn btn-primary btn-md active" role="button">
//        <span class="glyphicon glyphicon-step-backward" aria-hidden="true">Back</span>
//        </a>
//    </div>
//
//EOT;
//    return $html;
//}


function fm_timer_shortenall($params=array())
{
    global $tmpl_o;

    $fields = array(
        "instr_content" => "<p class='lead'>Each fleet will be shortened to the lap shown below.</p>
                            <p>To shorten one fleet click the button above the fleet table in the tabbed view</p>",
        "footer_content" => "<p>click <b>Confirm Changes</b> button to make the laps change(s)<br>",
        "reminder"       => "<div class='alert alert-warning'><b>REMEMBER</b> - signal any shortened course to the competitors affected</div>"
    );

    $data = array(
        "mode"       => "shortenall",
        "instruction"=> true,
        "footer"     => true
    );

    foreach ($params['fleet-data'] as $i=>$fleet)
    {
        $data['fleets'][$i] = array(
            "fleetname"  => ucwords($fleet['name']),
            "fleetnum"   => $i,
            "fleetlaps"  => $fleet['currentlap'],
            "setlaps"    => $fleet['maxlap'],
            "status"     => $fleet['status']
        );

        if ($fleet['status'] == "notstarted")
        {
            $data['fleets'][$i]['minvallaps'] = array("val"=>1, "msg"=>"cannot be shortened to less than 1 lap");
        }
        elseif ($fleet['status'] == "inprogress")
        {
            $minval = $fleet['currentlap'];
            $data['fleets'][$i]['minvallaps'] = array("val"=>$minval, "msg"=>"cannot be shortened to less than $minval lap(s)");
            $data['fleets'][$i]['maxvallaps'] = array("val"=>$fleet['maxlap'], "msg"=>"cannot be shortened to more than {$fleet['maxlap']} lap(s)");;
        }
    }

    return $tmpl_o->get_template("fm_set_laps", $fields, $data);
}

function fm_timer_undoshorten($params = array())
{
    global $tmpl_o;

    $fields = array(
        "instr_content" => "<p>Set the shortened lap you want for EACH fleet</p><p style='font-size: 0.8em; color: steelblue;'>For example if you forgot to shorten a fleet 
                            to 3 laps and you actually gave a finish signal to a boat on 3 laps - just set the shorten fleet to 3.</p>",
        "footer_content" => "click the Change Shorten Laps button to make the changes",
        "reminder" => ""
    );

    $data = array(
        "mode"       => "setlaps",
        "instruction"=> true,
        "footer"     => true
    );

    foreach ($params['fleet-data'] as $i=>$fleet)
    {
        $data['fleets'][$i] = array(
            "fleetname"  => ucwords($fleet['name']),
            "fleetnum"   => $i,
            "fleetlaps"  => $fleet['maxlap'],
            "status"     => $fleet['status']
        );

        if ($fleet['status'] == "notstarted")
        {
            $data['fleets'][$i]['minvallaps'] = array("val"=>1, "msg"=>"cannot be less than 1 lap");
        }
        elseif ($fleet['status'] == "inprogress")
        {
            $data['fleets'][$i]['minvallaps'] = array("val"=>1, "msg"=>"cannot be less than 1 lap");
            //$data['fleets'][$i]['minvallaps'] = array("val"=>$fleet['currentlap'], "msg"=>"cannot be less than {$fleet['currentlap']} lap(s)");
        }
    }

    return $tmpl_o->get_template("fm_set_laps", $fields, $data);
}


//function fm_timer_resetlaps($params = array())
//{
//    $html = "";
//    if ($params['lapstatus']==0)
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
//    foreach($params['fleet-data'] as $fleet)
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
