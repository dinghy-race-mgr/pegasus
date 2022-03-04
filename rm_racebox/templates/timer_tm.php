<?php

function timer_list($params = array())
{
    //echo "<pre>".print_r($params,true)."</pre>";
    
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
        $optlink = "timer_pg.php?eventid={$params['eventid']}&mode={$val['mode']}$view_str";

        $view_option.= <<<EOT
            <a class="btn btn-md $btn_state text-center lead" href="$optlink">{$val['label']}</a>
EOT;
    }

    // get boat display
    $view_bufr = timer_list_view($params['eventid'], $params['timings'], $params['view'], 1);

    $last_click_txt = "";
    if (array_key_exists("boat", $_SESSION["e_{$params['eventid']}"]['lastclick']))
    {
        $last_click_txt = "<blockquote><h4>Last Time Recorded: {$_SESSION["e_{$params['eventid']}"]['lastclick']['boat']}</h4></blockquote>";
    }

    // final page body layout
    $html = <<<EOT
    <div class="margin-top-40" >
        <div class="btn-group pull-left"  style="display: block;">$view_option</div>
        <div class="pull-right text-info"  style="display: block;">$last_click_txt</div>
        <div class="clearfix"></div>
            $view_bufr
    </div>
EOT;

    return $html;
}

function timer_list_view($eventid, $data, $view, $rows = 1)
{
    $timelap_link = "timer_sc.php?eventid=$eventid&pagestate=timelap";
    $undo_link = "timer_sc.php?eventid=$eventid&pagestate=undoboat";
    $bunch_link = "timer_sc.php?eventid=$eventid&pagestate=bunch&action=addnode";
    $finish_link = "timer_sc.php?eventid=$eventid&pagestate=finish";
    $edit_link = "";  // fixme no edit on timer page - is this needed - could add it to menu and use same popup form as tabbed

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
                    $dbufr[$item][] = array(
                        "entryid" => $entry['id'],
                        "class"   => $entry['class'],
                        "sailnum" => $entry['sailnum'],
                        "boat"    => $entry['class']." - ".$entry['sailnum'],
                        "fleet"   => $entry['fleet'],
                        "start"   => $entry['start'],
                        "lap"     => $entry['lap'],
                        "code"    => $entry['code'],
                        "pn"      => $entry['pn'],
                        "etime"   => $entry['etime'],
                        "status"  => $entry['status'],
                        "declaration" => $entry['declaration'],
                        "label"   => strtoupper(substr($entry['class'], 0, 3))."&nbsp;&nbsp;".$entry['sailnum']   // FIXME - should use stored class acronym if available
                    );
                }
            }
        }
    }

    elseif ($view == "class")
    {
        $configured = true;     // fixme this needs to be set somewhere
        $classes = array();
        if (array_key_exists("racebox_class_category", $_SESSION))
        {
            $classes = explode("|", $_SESSION["racebox_class_category"]);   // FIXME instead of using a fixed list would it be better to have number threshold
                                                                            // FIXME and just generate classes who reach the threshold
        }
        if (empty($classes))
        {
            $configured = false;
        }
        else
        {
            $category = array();
            for ($i=1; $i <= count($classes); $i++)
            {
                $category[$i] = $classes[$i-1];
            }
            $category[] = "MISC";
        }

        if ($configured)    // FIXME - what happens if this is not configured
        {
            $dbuf = array();
            for ($i = 1; $i <= count($category); $i++) {
                $dbufr[$i] = array();
            }

            foreach ($data as $class => $group) {
                foreach ($group as $entry) {
                    $set = false;
                    for ($i = 1; $i < count($category); $i++) {
                        if (strpos(strtolower($entry['class']), strtolower($category[$i])) !== false) {
                            $dbufr[$i][] = array(
                                "entryid" => $entry['id'],
                                "class"   => $entry['class'],
                                "sailnum" => $entry['sailnum'],
                                "boat"    => "{$entry['class']} - {$entry['sailnum']}",
                                "fleet"   => $entry['fleet'],
                                "start"   => $entry['start'],
                                "lap"     => $entry['lap'],
                                "code"    => $entry['code'],
                                "pn"      => $entry['pn'],
                                "etime"   => $entry['etime'],
                                "status"  => $entry['status'],
                                "declaration" => $entry['declaration'],
                                "label"   => $entry['sailnum']

                            );
                            $set = true;
                            break;
                        }
                    }

                    if (!$set) {                                    // add to misc group
                        $dbufr[count($category)][] = array(
                            "entryid" => $entry['id'],
                            "class"   => $entry['class'],
                            "sailnum" => $entry['sailnum'],
                            "boat"    => "{$entry['class']} - {$entry['sailnum']}",
                            "fleet"   => $entry['fleet'],
                            "start"   => $entry['start'],
                            "lap"     => $entry['lap'],
                            "code"    => $entry['code'],
                            "pn"      => $entry['pn'],
                            "etime"   => $entry['etime'],
                            "status"  => $entry['status'],
                            "declaration" => $entry['declaration'],
                            "label"   => strtoupper(substr($entry['class'], 0, 3))."&nbsp;&nbsp;".$entry['sailnum']    // FIXME - should use stored class acronym if available
                        );
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
                    $dbufr[$item][] = array(
                        "entryid" => $entry['id'],
                        "class"   => $entry['class'],
                        "sailnum" => $entry['sailnum'],
                        "boat"    => "{$entry['class']} - {$entry['sailnum']}",
                        "fleet"   => $entry['fleet'],
                        "start"   => $entry['start'],
                        "lap"     => $entry['lap'],
                        "code"    => $entry['code'],
                        "pn"      => $entry['pn'],
                        "etime"   => $entry['etime'],
                        "status"  => $entry['status'],
                        "declaration" => $entry['declaration'],
                        "label"   => strtoupper(substr($entry['class'], 0, 3))."&nbsp;&nbsp;".$entry['sailnum']   // FIXME - should use stored class acronym if available
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
        $data_bufr = "<div class='row' style='margin-left: 10px; margin-bottom: 10px'>";
        foreach ($category as $i => $label) {
            // flush buffers if we need to go to second or third row (6 columns per row)
            if ($i % 7 === 0)
            {
                $html.= $label_bufr . $data_bufr;
                $label_bufr = "</div><br><br><div class='row'>";
                $data_bufr  = "</div><div class='row' style='margin-left: 10px; margin-bottom: 10px'>";
            }
            // category labels
            $label_bufr .= <<<EOT
            <div class="col-md-2 text-center"><h4><b>$label</b></h4></div>
EOT;

            // boat buttons
            $data_bufr .= "<div class='col-md-2' style='padding: 0px 0px 0px 0px;'>";
            foreach ($dbufr[$i] as $entry) {
                // fixme this will a) need to have links and b) change depending on race type
                // fixme - finish will only be required for average lap

                $laps = $_SESSION["e_$eventid"]["fl_{$entry['fleet']}"]['maxlap'];
                $status = $_SESSION["e_$eventid"]["fl_{$entry['fleet']}"]['status'];
                $scoring = $_SESSION["e_$eventid"]["fl_{$entry['fleet']}"]['scoring'];

// debug       if ($entry['entryid'] == 183) { echo "<pre>{$entry['lap']}, $laps, $status, $scoring</pre>"; exit(); }

                // set styles

                // default setting before lap recorded
                $bcolor = "btn-success";
                $state = "racing";

                // lap recorded but not on last lap
                if ($entry['lap'] > 0 and $entry['lap'] < $laps - 1)
                {
                    $bcolor = "btn-info";
                    $state = "racing";
                }
                // on last lap
                elseif (($entry['lap'] > 0 and $entry['lap'] == $laps - 1)
                    OR ($status == "finishing" AND $scoring == "average" ))  // on last lap
                {
                    $bcolor = "btn-warning";
                    $state = "lastlap";
                }
                // finished
                elseif ( $entry['lap'] > 0 and $entry['lap'] >= $laps) // finished
                {
                    $bcolor = "btn-default";
                    $state = "finished";
                }

                empty($entry['code']) ? $cog_style = "primary" : $cog_style = "danger";
                $state == "lastlap" ? $bunch_link .= "&lastlap=true" : $bunch_link .= "&lastlap=false";

                // competitor identity and lap count
                $title = $entry['label'];
                $lapcount = "L {$entry['lap']}";

                // popover information
                $ptitle = "<b>{$entry['class']} - {$entry['sailnum']}</b>";
                $pcontent = "lap: {$entry['lap']} [ ".gmdate("H:i:s", $entry['etime'])." ]&nbsp;&nbsp;&nbsp;<b>{$entry['code']}</b>";

                // set params for link options
                unset($entry['class']);
                unset($entry['sailnum']);
                $params_list = "&" . http_build_query($entry);

                // setcode link
                $link = <<<EOT
timer_sc.php?eventid=$eventid&pagestate=setcode&fleet={$entry['fleet']}&entryid={$entry['entryid']}&boat={$entry['boat']}
&racestatus={$entry['status']}&declaration={$entry['declaration']}&lap={$entry['lap']}&finishlap=$laps}
EOT;
                // finish button
                $finish_option = "";
                if ($scoring == "average") {
                    $finish_option = <<<EOT
                <li><a href="$finish_link$params_list">Finish</a></li>
EOT;
                }

                $options_bufr = <<<EOT
                <ul class="dropdown-menu">
                    <li><a href="$undo_link$params_list">Undo Last Timing</a></li>
                    <li><a href="$bunch_link$params_list">Bunch</a></li>
                    $finish_option
                    <li><a href="timer_sc.php?">Edit (future)</a></li>
                    <li class="divider"></li>
                    <li><a href="$link&code=">clear code</a></li>  <!--FIXME don't display this if no code set -->
                    <li><a href="$link&code=DNC">set DNC</a></li>
                    <li><a href="$link&code=DNF">set DNF</a></li>
                    <li><a href="$link&code=DNS">set DNS</a></li>
                    <li><a href="$link&code=NSC">set NSC</a></li>
                    <li><a href="$link&code=OCS">set OCS</a></li>
                </ul>
EOT;

                $data_bufr .= <<<EOT
                <div class="btn-group btn-block" role="group" aria-label="...">
                    <a type="button" href="$timelap_link$params_list" class="btn $bcolor btn-xs" style="width:70%" 
                        data-toggle="popover" data-placement="top"  title="$ptitle" data-content="$pcontent">
                        <div class="pull-left">$title</div>
                        <div class="pull-right">$lapcount</div>
                    </a>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-$cog_style btn-xs dropdown-toggle" 
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
                        </button>
                        $options_bufr
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

    // display for tabbed view

    $eventid = $params['eventid'];

    $tabs = "";
    $panels = "";
    $clock = "<span class='text-primary glyphicon glyphicon-time'></span>";

    $state_cfg = array(
        "default"  => array("row_style" => "default",  "label_style" => "label-primary",  "annotation" => " "),
        "racing"   => array("row_style" => "racing",   "label_style" => "laptime-racing",  "annotation" => " "),
        "finished" => array("row_style" => "finished", "label_style" => "laptime-finish", "annotation" => " FINISHED"),
        "lastlap"  => array("row_style" => "lastlap",  "label_style" => "laptime-lastlap",  "annotation" => " LAST LAP"),
        "excluded" => array("row_style" => "excluded", "label_style" => "label-finish",  "annotation" => " NOT RACING"),
    );

    $url_base      = "timer_sc.php?eventid=$eventid";
    $timelap_link  = $url_base."&pagestate=timelap&fleet=%s&start=%s&entryid=%s&boat=%s&lap=%s&pn=%s&etime=%s";
    $finish_link_tmpl   = $url_base."&pagestate=finish&fleet=%s&start=%s&entryid=%s&boat=%s&lap=%s&pn=%s&etime=%s";
    $undoboat_link = $url_base."&pagestate=undoboat&entryid=%s";
//  $setcode_link  = $url_base."&pagestate=setcode&fleet=%s&entryid=%s&boat=%s&racestatus=%s";


    //echo "<pre>".print_r($_SESSION["e_$eventid"],true)."</pre>";
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
                    else                                                    // shorten laps button
                    {
                        $laps_btn = <<<EOT
                        <div class="row margin-top-0">
                            <div class="col-sm-12 text-left" >
                                <div data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true"
                                     data-title="click here to shorten this fleet at the end of the next lap" data-placement="top" class="btn-group ">
                                    <a id="shorten$i" href="timer_sc.php?eventid=$eventid&pagestate=shorten&fleet=$i" class="btn btn-warning btn-md margin-top-0" style="color: black;" aria-expanded="false" role="button" >
                                        <span class="glyphicon glyphicon-flag"></span>&nbsp;
                                        {$fleet['maxlap']} LAPS - click here to <b>SHORTEN COURSE</b>&nbsp;
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
            $finish_btn_tmpl = <<<EOT
                <span data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true" data-title="%s" data-placement="top">
                <a id="finish" href="%s" role="button" class="btn btn btn-%s btn-xs %s" target="">
                    <span class="glyphicon glyphicon-volume-up"></span>
                </a>
                </span>
EOT;
            //echo "<pre>".print_r($params['timings'],true)."</pre>";

            foreach ($params['timings'][$i] as $j=>$r)   // loop over each boat in this fleet
            {
                $boat = "{$r['class']} - {$r['sailnum']}";
                $finish_link = vsprintf($finish_link_tmpl, array($r['fleet'], $r['start'], $r['id'], $boat, $r['lap'], $r['pn'], $r['etime'] ));

                $current_lap = $r['lap'] + 1;
                $cfg = $state_cfg['default'];
                $finish_btn  = "";

                if ($r['status'] == "R")  // racing
                {
                    $skip = "";
                    // finish button
                    if ($current_lap == $fleet['maxlap'] OR
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
                elseif ($r['status']=="X")    // excluded
                {
                    $cfg = $state_cfg['excluded'];
                    $finish_btn  = "&nbsp;";
                    $skip = "rowlink-skip";
                }
                else
                {
                    $cfg = $state_cfg['default'];
                    $finish_btn  = "&nbsp;";
                    $skip = "rowlink-skip";
                }

                //$laptimes_bufr = laptimes_html($r['laptimes'], $cfg['label_style'], $cfg['annotation']);
                $laptimes_bufr = laptimes_html($r['laptimes'], $cfg);

                $row_link = vsprintf($timelap_link,
                    array($r['fleet'], $r['start'], $r['id'], $boat, $r['lap'], $r['pn'], $r['etime'] ));

                $link = <<<EOT
timer_sc.php?eventid=$eventid&pagestate=setcode&fleet={$r['fleet']}&entryid={$r['id']}&boat=$boat
&racestatus={$r['status']}&declaration={$r['declaration']}&lap={$r['lap']}&finishlap={$r['finishlap']}
EOT;

                $code_link = get_code($r['code'], $link, "timercodes");

                $edit_link = editlaps_html($eventid, $r['id'], $boat, $r['laptimes']);


                if ($_SESSION['racebox_timer_bunch'])
                {
                    $bunch_label = "<th width='5%' style='text-align: center'>bunch</th>";
                    $bunch_link = "<td class='rowlink-skip' style='text-align: center'></td>";
                    if ($r['status'] != "F")
                    {
                        $cfg['row_style'] == "lastlap" ? $lastlap = "true" : $lastlap = "false";
                        $bunch_link = bunch_html($eventid, $r['id'], $boat, $r, $lastlap);
                    }
                }

                $undo_link = undoboat_html($undoboat_link, $eventid, $r['id'], $boat, $r['laptimes']);

                $rows.= <<<EOT
                    <tr class="table-data {$cfg['row_style']}">
                        <td style="width: 1%;"><a href="$row_link" ></a></td>
                        <td class="$skip truncate" >{$r['class']}</td>
                        <td class="$skip" >{$r['sailnum']}</td>
                        <td class="$skip truncate" style="padding-left:10px;">{$r['helm']}</td>
                        <td class="$skip" style="padding-left:15px;" >$laptimes_bufr</td>
                        <td class="rowlink-skip" style="text-align: left">$code_link</td>
                        $bunch_link
                        <td class="rowlink-skip" style="text-align: center">$finish_btn</td>
                        <td class="rowlink-skip" style="text-align: center">$edit_link</td>
                        <td class="rowlink-skip" style="text-align: center">$undo_link</td>
                    </tr>
EOT;
            }

            // put panel table layout together
            $panels .= <<<EOT
            <div role="tabpanel" class="tab-pane margin-top-0" id="fleet$i">
                $all_finished
                $laps_btn
                <table class="table table-striped table-condensed table-hover table-top-padding table-top-border" style="width: 100%; table-layout: fixed;">
                    <thead class="text-info" >
                        <tr >
                            <th width="1%"></th>
                            <th width="10%">class</th>
                            <th width="5%">sail no.</th> 
                            <th width="10%">helm</th>                           
                            <th width="">lap times</th>
                            <th width="5%" style="text-align: center">code</th>
                            $bunch_label
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

//function codes_html($code, $url)    // FIXME same code in both timer_tm and results_tm
//    /*
//     * displays codes dropdown on timer page
//     */
//{
//    if (empty($code))
//    {
//        //$label = "<span>code &nbsp;</span>";
//        $label = "<span class='glyphicon glyphicon-cog'>&nbsp;</span>";
//        $style = "btn-info";
//    }
//    else
//    {
//        $label = "<span>$code&nbsp;</span>";
//        $style = "btn-danger";
//    }
//
//    $codebufr = u_dropdown_resultcodes($_SESSION['timercodes'], "short", $url);
//
//    $bufr = <<<EOT
//    <div class="dropdown">
//        <button type="button" class="btn $style btn-xs dropdown-toggle" data-toggle="dropdown" >
//            <span class="default"><b>$label&nbsp;</b></span><span class="caret" ></span>
//        </button>
//        <ul class="dropdown-menu">
//            $codebufr
//        </ul>
//    </div>
//EOT;
//
//    return $bufr;
//}

function editlaps_html($eventid, $entryid, $boat, $laptimes_str)
{
    if (!empty($laptimes_str))
    {
        $bufr = <<<EOT
        <span data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true"
              data-title="edit lap times for this boat" data-placement="top">
            <a type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#editlapModal" data-boat="$boat"
                    data-iframe="timer_editlaptimes_pg.php?eventid=$eventid&pagestate=init&entryid=$entryid" >
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


function bunch_html($eventid, $entryid, $boat, $r, $lastlap)
    // creates button to add a boat to the bunch list
{
    // array to pass data to bunch process
    $params = array(
        "fleet"   => $r['fleet'],
        "start"   => $r['start'],
        "entryid" => $r['id'],
        "boat"    => $boat,
        "lap"     => $r['lap'],
        "pn"      => $r['pn'],
        "etime"   => $r['etime'],
        "lastlap" => $lastlap
    );

    $link_txt = "timer_sc.php?eventid=$eventid&pagestate=bunch&action=addnode&".http_build_query($params);

    $bufr = <<<EOT
    <td class="rowlink-skip" style="text-align: center">
        <span data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true" data-title="save for bunch" data-placement="top">
            <a id="bunchboat" type="button" href="$link_txt" role="button" class="btn btn-info btn-xs" >
                <span class="glyphicon glyphicon-pushpin"></span>
            </a>
        </span>
    </td>
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
    $bufr = "";

    // hidden fields
    $bufr.= <<<EOT
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

        $formatted_time = gmdate("H:i:s", $laptime);
        $bufr.= <<<EOT
        <div class="form-group margin-top-10" style="min-width: 30%">
            <label for="lap$i">lap $i &nbsp;</label>
            <input type="text" class="form-control" id="lap$i" name="etime[$i]" value="$formatted_time"
                required data-fv-notempty-message="a time [hh:mm:ss] must be entered"
                data-fv-regexp="true"
                data-fv-regexp-regexp="^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$"
                data-fv-regexp-message="lap time must be in HH:MM:SS format" />
        </div>
EOT;
        $i++;
    }

    $bufr.= <<<EOT
    <div class="row"></div>
    <div class="pull-right margin-top-20">
        <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span>&nbsp;Update Lap Times</button>
    </div>
    </div>
EOT;

    $html = <<<EOT
    <div class="alert well well-sm" role="alert">
        <p class="text-info"><b>edit the lap times and click the update button to save them</b></br> use hh:mm:ss for elapsed time (e.g. 00:46:32)</p>
    </div>
    <form id="editlapForm" class="form-inline" action="timer_editlaptimes_pg.php?pagestate=submit" method="post"
        data-fv-framework="bootstrap"
        data-fv-icon-valid="glyphicon glyphicon-ok"
        data-fv-icon-invalid="glyphicon glyphicon-remove"
        data-fv-icon-validating="glyphicon glyphicon-refresh"
    >
    $bufr
       
    </form>

EOT;

    return $html;
}


function edit_laps_success($params=array())
{
    $html = <<<EOT
    <div class="alert alert-success" role="alert" style="margin-top: 30px">
        <p style="font-size: 100%;"><b>Successful changes</b> were made to the lap times for {boat}.<br></p>
        <span style="text-indent: 30px;">{msg}</span>
        <p>Use the <b>BACK</b> button to make more changes or the <b>Close</b> button at the top of the page to return to the Timer page</p>
    </div>

    <div class="row pull-right">
        <a href="timer_editlaptimes_pg.php?eventid={eventid}&entryid={entryid}&pagestate=init" class="btn btn-primary btn-md active" role="button">
        <span class="glyphicon glyphicon-step-backward" aria-hidden="true">Back</span>
        </a>
    </div>

EOT;
    return $html;
}


function edit_laps_error($params=array())
{
    $html = <<<EOT
    <div class="alert alert-danger" role="alert"  style="margin-top: 30px">
    <p style="font-size: 100%;"><b>No changes</b> were made to the lap times for {boat}<br></p>
    <p>The following problems were found with the times you entered .</p>
    <span style="text-indent: 30px;">{msg}</span>
    <p>Use the <b>BACK</b> button to try again or the <b>Close</b> button
    at the top of the page to return to the Timer page</p>
    </div>
    <div class="row pull-right">
        <a href="timer_editlaptimes_pg.php?eventid={eventid}&entryid={entryid}&pagestate=init" class="btn btn-primary btn-md active" role="button">
        <span class="glyphicon glyphicon-step-backward" aria-hidden="true">Back</span>
        </a>
    </div>

EOT;
    return $html;
}

//function fm_timer_resetlaps($params=array())
//{
//    return "fm_timer_resetlaps";
//}

function fm_timer_shortenall($params=array())
{
    global $tmpl_o;

    $data = array(
        "mode"       => "shortenall",
        "instruction"=> true,
        "footer"     => true
    );

    $fields = array(
        "instr_content" => "<p>Use the form below to set the shortened laps for each fleet - the laps shown for each fleet are the current lap for the leading boat in that fleet.</p>  
                            <p>You can also use this form to reset laps if you made a mistake shortening - be careful to not set it higher than the laps <u>originally</u> set for each fleet ",
        "footer_content" => "<p>click the <span class='lead'>Change Finish Laps</span> button to set/reset the finish lap for each fleet<br>",
        "reminder" => "<div class='alert alert-danger'><b>REMEMBER</b> - that you need to signal the shorten course</div>"
    );

    foreach ($params['fleet-data'] as $i=>$fleet)
    {
//        // debug
//        if ($i == 1)
//        {$fleet['status'] = "notstarted";}
//        elseif ($i == 2)
//        {$fleet['status'] = "inprogress";}
//        elseif ($i == 3)
//        {$fleet['status'] = "finishing";}
//        elseif ($i == 4)
//        {$fleet['status'] = "allfinished";}

        $data['fleets'][$i] = array(
            "fleetname"  => ucwords($fleet['name']),
            "fleetnum"   => $i,
            "fleetlaps"  => $fleet['currentlap'],  // FIXME is this correct or does it need to be +1
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


    //echo "<pre>".print_r($params,true)."</pre>";

//    $html = "";
//
//    if ($params['mode'] == "shorten")
//    {
//        $lapskey = "shlaps";
//        $action = "shortened";
//        $instruction1 = <<<EOT
//            Each fleet will be shortened to finish on the <b>next lap</b> as shown below &hellip;<br>
//            you can also change the required finish lap manually before submitting
//EOT;
//        $instruction2 = <<<EOT
//            click <b>SHORTEN ALL</b> button below to apply the changes
//EOT;
//
//    }
//    else
//    {
//        $lapskey = "maxlaps";
//        $action = "changed";
//        $instruction1 = <<<EOT
//            You can set/change the laps for each fleet here &hellip;<br>
//            This can also be used to <b>undo a shorten course</b> if you applied one by mistake
//
//EOT;
//        $instruction2 = <<<EOT
//            click <b>SET LAPS</b> button below to apply the changes
//EOT;
//
//    }
//
//    $fields_bufr = "";
//    foreach ($params['fleets'] as $i=>$fleet )
//    {
//        if ($fleet['scoring'] == "pursuit")
//        {
//            $fields_bufr.=<<<EOT
//                <div class="form-group">
//                    <label class="col-xs-offset-2 col-xs-3 control-label" style="text-align: left;">{$fleet['name']} </label>
//                    <div class="col-xs-6 ">
//                        <p class="text-info">pursuit race - laps cannot be $action</p>
//                        <input type="hidden" id="laps$i" name="lapskey$i" value="{$fleet[$lapskey]}">
//                    </div>
//                </div >
//EOT;
//        }
//        elseif ($fleet['status'] == "finishing" OR $fleet['status'] == "allfinished" )
//        {
//            $fields_bufr.=<<<EOT
//            <div class="form-group">
//                <label class="col-xs-offset-2 col-xs-3 control-label" style="text-align: left;">{$fleet['name']} </label>
//                <div class="col-xs-6 ">
//                    <p class="text-info">boats have finished or are finishing on lap {$fleet['maxlaps']} - cannot be $action</p>
//                    <input type="hidden" id="laps$i" name="lapskey$i" value="{$fleet[$lapskey]}">
//                </div>
//            </div >
//EOT;
//        }
//        elseif ($fleet['status'] == "notstarted")
//        {
//            if ($params['mode'] == "shorten")
//            {
//                $fields_bufr.=<<<EOT
//                <div class="form-group">
//                    <label class="col-xs-offset-2 col-xs-3 control-label" style="text-align: left;">{$fleet['name']} </label>
//                    <div class="col-xs-6 ">
//                        <p class="text-info">race not started - cannot be $action</p>
//                        <input type="hidden" id="laps$i" name="lapskey$i" value="{$fleet[$lapskey]}">
//                    </div>
//                </div >
//EOT;
//            }
//            else
//            {
//                $fields_bufr.=<<<EOT
//                <div class="form-group">
//                    <label class="col-xs-offset-2 col-xs-3 control-label" style="text-align: left;">{$fleet['name']} </label>
//                    <div class="col-xs-3 inputfieldgroup">
//                        <input type="number" class="form-control" id="laps$i" name="$lapskey$i" value="{$fleet[$lapskey]}"
//                            required data-fv-notempty-message="the lap for shortening is required" min="1"
//                            data-fv-greaterthan-message="The no. of laps must be greater than 0"
//                        />
//                    </div>
//                    <div class="col-xs-3 control-label" style="text-align: left;">
//                        <label> laps </label>
//                    </div>
//                </div >
//EOT;
//            }
//        }
//        else   // in progress
//        {
//            $fields_bufr.=<<<EOT
//            <div class="form-group">
//                <label class="col-xs-offset-2 col-xs-3 control-label" style="text-align: left;">{$fleet['name']} </label>
//                <div class="col-xs-3 inputfieldgroup">
//                    <input type="number" class="form-control" id="laps$i" name="laps$i" value="{$fleet[$lapskey]}"
//                        required data-fv-notempty-message="the lap for shortening is required" min="1"
//                        data-fv-greaterthan-message="The no. of laps must be greater than 0"
//                    />
//                </div>
//                <div class="col-xs-3 control-label" style="text-align: left;">
//                    <label> laps </label>
//                </div>
//            </div >
//EOT;
//        }
//    }
//
//// form  - instructions + fields
//    $html = <<<EOT
//        <div class="alert well well-sm" role="alert">
//            <p class="text-info">$instruction1</p>
//        </div>
//
//        $fields_bufr
//
//        <div class="alert well well-sm" role="alert">
//            <p class="text-info">$instruction2</p>
//        </div>
//EOT;
//
//    return $html;
//}


/*
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
*/