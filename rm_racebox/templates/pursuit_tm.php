<?php

function start_by_class($params=array())
{
    $htm = <<<EOT
    <div class="row">
        <div class="col-md-3" style="padding-left: 30px; ">
            <div class="well">           
                <table class="table ">
                    <tr><td colspan="2" class="rm-text-md"><b>RACE DETAILS</b></td></tr>
                    <tr><td>Race Length</td><td class="rm-text-sm"><b>{length} minutes</b></td></tr>
                    <tr><td>Scratch PN</td><td class="rm-text-sm"><b>{maxclass}</b></td></tr>  
                    <tr><td>Fastest PN</td><td class="rm-text-sm"><b>{minclass}</b></td></tr>
                    <tr><td>PY Used</td><td class="rm-text-sm"><b>{pntype}</b></td></tr>
                    <tr><td>Start Interval</td><td class="rm-text-sm"><b>{startint} seconds</b></td></tr>         
                </table>
                <span class="print"><a class="button-green noprint" onclick="window.print()" href="#" >Print Start List</a></span>
            </div>       
        </div>
        <div class="col-md-9">
            <table class="table table-condensed ">
                <tr class="rm-background"><th style="width: 20%;">START TIME</th><th style="width: 80%;">CLASSES</th></tr>
                {start-info}            
            </table>       
        </div>       
    </div>

EOT;

    return $htm;
}


function start_by_competitor($params=array())
{
    $htm = <<<EOT

    <div class="row" style="margin-top: 60px; padding-left: 50px; padding-right: 50px;">
        <div class="col-md-4" style="padding-left: 30px; ">
            <div class="well">           
                <table class="table ">
                    <tr><td colspan="2" class="rm-text-md"><b>RACE DETAILS</b></td></tr>
                    <tr><td>Race Length</td><td class="rm-text-sm"><b>{length} minutes</b></td></tr>
                    <tr><td>Scratch</td><td class="rm-text-sm"><b>{maxpn}</b></td></tr>  
                    <tr><td>Fastest</td><td class="rm-text-sm"><b>{minpn}</b></td></tr>
                    <tr><td>PY Used</td><td class="rm-text-sm"><b>{pntype}</b></td></tr>
                    <tr><td>Start Interval</td><td class="rm-text-sm"><b>{startint} seconds</b></td></tr>         
                </table>
                <!--span class="print"><a class="button-green noprint" onclick="window.print()" href="#" >Print Start List</a></span -->
            </div>       
        </div>
        <div class="col-md-8">
            <div style="overflow-y: scroll; overflow-x: hidden; height:600px;">
                <table class="table width="90%">
                    {start-info}            
                </table>       
            </div> 
        </div>      
    </div>


EOT;

    return $htm;
}


function fm_result_edit_pursuit($params = array())
{
    // set up scoring and result code data
    $resultcodes = array();
    foreach ($params['resultcodes'] as $row) {
        $resultcodes[] = array ("code" => $row['code'], "label" => "{$row['code']} : {$row['short']}");
    }
    $scoring_code_bufr = u_selectcodelist($resultcodes, $params['code']);


    // set up helm field options
    $helm_bufr = "";
    if ($params['points_allocation'] == "boat") {
        $helm_bufr = <<<EOT
        <div class="form-group">
            <label class="col-xs-{lblw} control-label text-success">helm</label>
            <div class="col-xs-{fldw} inputfieldgroup">
                <input type="text" class="form-control" style="text-transform: capitalize;" id="idhelm" name="helm" value="{helm}"
                    required data-fv-notempty-message="this information is required" />
            </div>
            <div class="col-xs-{hlpw} help-block">e.g. Fred Flintstone</div>
            
        </div>
EOT;
    }
    else
    {
        $helm_bufr = <<<EOT
        <div class="form-group">
            <label class="col-xs-{lblw} control-label text-success">helm</label>
            <div class="col-xs-{fldw} inputfieldgroup">
                <p class="form-control-static">{helm}</p>
                <input type="hidden" class="form-control" style="text-transform: capitalize;" id="idhelm" name="helm" value="{helm}">               
            </div>
            <div class="col-xs-{hlpw} help-block">cannot be changed</div>
        </div>
EOT;
    }


    $html = <<<EOT

    <div class="alert well well-sm" role="alert">
        <p class="text-info"><b>Change boat / finish details as required for this competitor and then click the update button to submit the changes </p>
    </div>
    
    <form id="resulteditForm" class="form-horizontal" action="results_edit_pg.php?pagestate=submit-pursuit" method="post"
        data-fv-framework="bootstrap"
        data-fv-icon-valid="glyphicon glyphicon-ok"
        data-fv-icon-invalid="glyphicon glyphicon-remove"
        data-fv-icon-validating="glyphicon glyphicon-refresh"
    >
    <input name="entryid" type="hidden" id="identryid" value="{$params['entryid']}">
    <input name="eventid" type="hidden" id="ideventid" value="{eventid}">
    
  <div class="panel panel-default">
  
    <div class="panel-heading panel-heading-nav">    
      <ul class="nav nav-pills">      
        <li role="presentation" >
          <a href="#boatpanel" aria-controls="boatpanel" role="tab" data-toggle="tab">
          <span class="glyphicon glyphicon-list-alt"> </span> Boat Details</a>
        </li>
        
        <li role="presentation" class="active">
          <a href="#resultpanel" aria-controls="resultpanel" role="tab" data-toggle="tab">
          <span class="glyphicon glyphicon-flag"> </span> Result Details</a>
        </li>
        
        <div class="pull-right">
            <button onclick='window.top.location.href = "results_pg.php?eventid={eventid}";' type="button" class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span>&nbsp;cancel</button>
            <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-ok"></span>&nbsp;Update Result</button>
        </div>
      </ul>      
    </div>
       
    <div class="panel-body">
    
      <div class="tab-content">
      
        <!-- boat panel -->
        <div role="tabpanel" class="tab-pane fade in" id="boatpanel">
        
            <!-- class -->
            <div class="form-group">
                <label class="col-xs-{lblw} control-label text-success">class</label>
                <div class="col-xs-{fldw} inputfieldgroup">
                    <p class="form-control-static">{class}</p>
                    <input type="hidden" class="form-control" style="text-transform: capitalize;" id="idclass" name="class" value="{class}" />                    
                </div>
                <div class="col-xs-{hlpw} help-block">cannot be changed</div>
            </div>
            
            
            <!-- sailnumber -->  
            <div class="form-group">
                <label class="col-xs-{lblw} control-label text-success" >sail no.</label>
                <div class="col-xs-{fldw} inputfieldgroup">
                    <input type="text" class="form-control" id="idsailnum" name="sailnum" value="{sailnum}"
                        required data-fv-notempty-message="this information is required" />
                </div>
                <div class="col-xs-{hlpw} help-block">sail number used for this race</div>
            </div>
                
            <!-- helm -->  
            $helm_bufr
            
            <!-- crew -->          
            <div class="form-group">
                <label class="col-xs-{lblw} control-label text-success">crew</label>
                <div class="col-xs-{fldw} inputfieldgroup">
                    <input type="text" class="form-control" style="text-transform: capitalize;" id="idcrew" name="crew" value="{crew}"
                    placeholder="for double handers only"
                    />
                </div>
                <div class="col-xs-{hlpw} help-block">e.g. Barney Rubble</div>
            </div>
                
            <!-- Club -->
            <div class="form-group">
                <label class="col-xs-{lblw} control-label text-success">club</label>
                <div class="col-xs-{fldw} inputfieldgroup">
                    <input type="text" class="form-control" style="text-transform: capitalize;" id="club" name="club" value="{club}"
                    />
                </div>
                <div class="col-xs-{hlpw} help-block">generally only required for open events</div>
            </div>                   
        </div>
        
        <!-- result panel --> 
        <div role="tabpanel" class="tab-pane fade in active" id="resultpanel">
            
            <!-- Laps -->  
            <!-- div class="form-group">
                <label class="col-xs-{lblw} control-label text-success">finish lap</label>
                <div class="col-xs-{fldw} inputfieldgroup">
                    <input type="number" class="form-control" id="idlap" name="lap" value="{lap}"
                    required data-fv-notempty-message="the number of laps completed is required"
                    placeholder="e.g. 3"
                    min="0" 
                    />
                </div>
                <div class="col-xs-{hlpw} help-block">no.of completed laps for this boat</div>
            </div -->       
 
            <!-- finish line -->
            <!-- div class="form-group">
                <label class="col-xs-{lblw} control-label text-success">finish line number</label>
                <div class="col-xs-{fldw} inputfieldgroup">
                    <input type="number" class="form-control" id="idf_line" name="f_line" value="{f_line}" 
                    required data-fv-notempty-message="the finish line number  is required"
                    placeholder="e.g 2"
                    min="1" 
                    />
                </div>
                <div class="col-xs-{hlpw} help-block">finishing line for this boat (where 1 is finish for leaders, 2 is next finish line, and so on)</div>
            </div --> 
 
            <!-- position at finish line -->
            <!-- div class="form-group">
                <label class="col-xs-{lblw} control-label text-success">position at finish line</label>
                <div class="col-xs-{fldw} inputfieldgroup">
                    <input type="number" class="form-control" id="idf_pos" name="f_pos" value="{f_pos}" 
                    required data-fv-notempty-message="the finishing position is required"
                    placeholder="e.g. 4"
                    min="1" 
                    />
                </div>
                <div class="col-xs-{hlpw} help-block">finishing position at specified finish line (e.g 4 means 4th boat to finish at specified finish line)</div >
            </div -->

            <!-- scoring code -->
            <div class="form-group">
                <label class="col-xs-{lblw} control-label text-success">scoring code</label>
                <div class = "col-xs-{fldw}">
                    <select class="form-control" name="code" id="idcode">
                        $scoring_code_bufr
                    </select >
                </div>
                <div class="col-xs-{hlpw} help-block">e.g. OCS, DNF - otherwise leave blank</div >
            </div>                   
            
            <!-- penalty score -->        
            <div class="form-group">
                <label class="col-xs-{lblw} control-label text-success">penalty score</label>
                <div class="col-xs-{fldw}">
                    <input type="text" class="form-control" id="idpenalty" name="penalty" value="{penalty}" 
                    placeholder="extra points to be applied (e.g. 2.5)"
                    data-fv-regexp="true"
                    data-fv-regexp-regexp="^(\d*)\.?(\d){0,1}$"
                    data-fv-regexp-message="penalty must be in format like 2.3">
                </div>
                <div class="col-xs-{hlpw} help-block">CARE - can ONLY be used when DPI code is set. </div>
            </div>
            
            <!-- notes -->
            <div class="form-group">
                <label class="col-xs-{lblw} control-label text-success">notes</label>
                <div class="col-xs-{fldw}">
                    <input name="note" type="text" class="form-control" id="idnote" value="{note}"
                    >
                </div>
                <div class="col-xs-{hlpw} help-block">useful to record why result was edited</div>
            </div>
                      
        </div>
       
      
      </div> <!-- end tab-content -->
      
    </div>  <!-- end panel-body -->
    
  </div> <!-- end panel -->
  
  <!-- disable penalty field unless code is set to DPI (first script seems to work-->
  <script type=text/javascript>
      $(document).ready(function(){ $("#idpenalty").prop("disabled", $('#idcode').val() !== "DPI"); });  
  </script>
  <script type=text/javascript>
    document.getElementById('idcode').onchange = function () {
    if(this.value !== "DPI") {
        document.getElementById("idpenalty").disabled = true;
        }

    else {
        document.getElementById("idpenalty").disabled = false;
        }
    }
  </script>
  

  </form>
EOT;

    return $html;
}


function timer_list_pursuit($params = array())
{
    //echo "<pre>".print_r($params,true)."</pre>";

    // get display for list view

    // view selector buttons
    $view_arr = array(
        "sailnum_p" => array ("label"=>"Sail No.", "mode"=>"list", "style"=>"btn-default", "params"=>""),
        "class_p"   => array ("label"=>"Class", "mode"=>"list", "style"=>"btn-default", "params"=>""),
        "finish_p"  => array ("label"=>"Finish Line", "mode"=>"list", "style"=>"btn-default", "params"=>""),
//        "result_p"  => array ("label"=>"Result", "mode"=>"list", "style"=>"btn-default", "params"=>""),
    );

    $view_option = "";
    foreach ($view_arr as $view=>$val)
    {
        $view == $params['view'] ? $btn_state = "btn-warning" : $btn_state = "btn-default";
        $view == "tab" ? $view_str = "" : $view_str = "&view=$view";
        $optlink = "timer_pg.php?eventid={$params['eventid']}&mode={$val['mode']}$view_str";

        $view_option.= <<<EOT
            <a class="btn btn-lg $btn_state text-center lead" href="$optlink">{$val['label']}</a>
EOT;
    }

    // get boat display
    $view_bufr = timer_list_view_pursuit($params['eventid'], $params['timings'], $params['view'], 1);

    $last_click_txt = "";
    if (array_key_exists("boat", $_SESSION["e_{$params['eventid']}"]['lastclick']))
    {
        $last_click_txt = "<h4><span style='color: darkgrey'>Last Boat Recorded: </span><b>{$_SESSION["e_{$params['eventid']}"]['lastclick']['boat']}</b></h4>";
    }

    $renumber_option = "";
    if ($params['view'] == "finish_p")
    {
        $link = "timer_sc.php?eventid={$params['eventid']}&pagestate=renumberlinepursuit";

        $renumber_option = <<<EOT
        <div class="btn-group">
          <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            ReNumber Finishers <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            <li><a href="$link&line=1">Line 1</a></li>
            <li><a href="$link&line=2">Line 2</a></li>
            <li><a href="$link&line=3">Line 3</a></li>
            <li><a href="$link&line=4">Line 4</a></li>
            <li><a href="$link&line=5">Line 5</a></li>
          </ul>
        </div>
EOT;
    }


    $colour_key = <<<EOT
       <a href="#" class="btn btn-default btn-sm" style="margin-right: 2px; min-width:15%">no laps</a>
       <a href="#" class="btn btn-info btn-sm"    style="margin-right: 2px; min-width:15%">racing</a>&nbsp;
       <a href="#" class="btn btn-success btn-sm" style="margin-right: 2px; min-width:15%">finished</a>&nbsp;
       <a href="#" class="btn btn-primary btn-sm" style="margin-right: 2px; min-width:15%">not racing</a>&nbsp;
EOT;

    // final page body layout
    $html = <<<EOT
    <div class="row margin-top-40" >
        <div class="col-md-5 btn-group pull-left"  style="display: block;">$view_option</div>
        <div class="col-md-3 btn-group pull-left"  style="display: block;">$renumber_option</div>
        <div class="col-md-4 pull-right text-danger" style="display: block;">
           <blockquote style="padding-left: 5px; padding-right: 0px">$colour_key $last_click_txt </blockquote>
        </div>        
    </div>
    <div class="clearfix"></div>
    <div>
            $view_bufr
    </div>
EOT;

    return $html;
}

function timer_list_view_pursuit($eventid, $data, $view, $rows = 1)
{
    // link details for boat controls
    $timelap_link = "timer_sc.php?eventid=$eventid&pagestate=timelap";
    $undo_link    = "timer_sc.php?eventid=$eventid&pagestate=undoboat";
    $move_link    = "timer_sc.php?eventid=$eventid&pagestate=swappositionpursuit";
    $clearfinish_link = "timer_sc.php?eventid=$eventid&pagestate=clearfinishpursuit";
    $bunch_link   = "timer_sc.php?eventid=$eventid&pagestate=bunch&action=addnode";
    $finish_link  = "timer_sc.php?eventid=$eventid&pagestate=setfinishpursuit";

    // boat state info
    $boat_states = array(
        "beforelap" => array("color"=>"default", "val"=>"no laps",     "index" => "racing"),
        "racing"    => array("color"=>"info",    "val"=>"racing",     "index" => "racing"),
        "notracing" => array("color"=>"primary", "val"=>"not racing", "index" => "notracing" ),
        "finished"  => array("color"=>"success", "val"=>"finished",   "index" => "finished"),
    );

    $fieldmap = array("entryid" => "id", "class" => "class", "sailnum" => "sailnum", "fleet" => "fleet", "start" => "start", "lap" => "lap",
        "finishlap" => "finishlap", "f_line" => "f_line", "f_pos" => "f_pos", "code" => "code", "pn" => "pn", "etime" => "etime",
        "status" => "status", "declaration" => "declaration");

    if ($view == "finish_p")
    {
        $configured = true;  // fixme this needs to be set somewhere
        $category = array();
        $dbuf = array();
        for ($i=1; $i <= 6; $i++)
        {
            $i == 6 ? $category[$i] = "No Finish" : $category[$i] = "Line $i";
            $dbufr[$i] = array();
        }

        if ($configured)
        {
            foreach ($data as $item => $group)
            {
                foreach ($group as $entry)
                {
                    foreach ($fieldmap as $k=>$v)
                    {
                        $arr[$k] = $entry[$v];
                    }
                    $arr["boat"] = $entry['class']." - ".$entry['sailnum'];
                    //$arr["label"] = strtoupper(substr($entry['class'], 0, 3))."&nbsp;&nbsp;".$entry['sailnum'];
                    $arr["label"] = $entry['classcode']."&nbsp;&nbsp;".$entry['sailnum'];
                    $arr["bunchlbl"] = explode(' ',trim($entry['class']))[0]." - ".$entry['sailnum'];

                    $dbufr[$item][] = $arr;
                }

                //u_array_sort_by_column($dbufr[$item], "f_pos");
            }
        }
    }

    elseif ($view == "class_p")
    {
        $configured = true;     // fixme this needs to be set somewhere

        $category = array();
        $i = 0;
        foreach($_SESSION["e_$eventid"]['classes'] as $k=>$v)
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
                    foreach ($fieldmap as $k=>$v)
                    {
                        $arr[$k] = $entry[$v];
                    }
                    $arr["boat"] = $entry['class']." - ".$entry['sailnum'];
                    $arr["bunchlbl"] = explode(' ',trim($entry['class']))[0]." - ".$entry['sailnum'];

                    $set = false;
                    for ($i = 1; $i < count($category); $i++) {

                        if (strtolower($entry['class']) == strtolower($category[$i])) {
                            $arr['label'] = $entry['sailnum']; // only need sailnum for label
                            $dbufr[$i][] = $arr;
                            $set = true;
                            break;
                        }
                    }

                    if (!$set) {                                    // add to misc group
                        //$arr['label'] = strtoupper(substr($entry['class'], 0, 3))."&nbsp;&nbsp;".$entry['sailnum'];
                        $arr['label'] = $entry['classcode']."&nbsp;&nbsp;".$entry['sailnum'];
                        $dbufr[count($category)][] = $arr;
                    }
                }
            }
        }
    }

//    elseif ($view == "result_p")
//    {
//        $configured = true;     // fixme this needs to be set somewhere
//
//        $category = array();
//        $dbuf = array();
//        for ($i=1; $i <= 6; $i++)
//        {
//            $category[$i] = "Group $i";
//            $dbufr[$i] = array();
//        }
//
//        if ($configured)
//        {
//            foreach ($data as $item => $group) {
//                foreach ($group as $entry) {
//                    foreach ($fieldmap as $k=>$v)
//                    {
//                        $arr[$k] = $entry[$v];
//                    }
//                    $arr["boat"] = $entry['class']." - ".$entry['sailnum'];
//                    $arr["label"] = $entry['classcode']."&nbsp;&nbsp;".$entry['sailnum'];
//                    $arr["bunchlbl"] = explode(' ',trim($entry['class']))[0]." - ".$entry['sailnum'];
//
//                    $dbufr[$item][] = $arr;
//                }
//            }
//        }
//    }

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
                        "finishlap" => $entry['finishlap'],
                        "f_line"  => $entry["f_line"],
                        "f_pos"   => $entry["f_pos"],
                        "code"    => $entry['code'],
                        "pn"      => $entry['pn'],
                        "etime"   => $entry['etime'],
                        "status"  => $entry['status'],
                        "declaration" => $entry['declaration'],
                        "label"   => $entry['classcode']."&nbsp;&nbsp;".$entry['sailnum'],
                        //"label"   => strtoupper(substr($entry['class'], 0, 3))."&nbsp;&nbsp;".$entry['sailnum'],
                        "bunchlbl"=> explode(' ',trim($entry['class']))[0]." - ".$entry['sailnum']
                    );
                }
            }
        }
    }

    // now produce html

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
            $label_bufr .= <<<EOT
            <div class="col-md-2 text-center"><h4 style="margin-bottom: 4px;"><b>$label</b></h4></div>
EOT;

            // boat buttons
            $data_bufr .= "<div class='col-md-2' style='padding: 0px 0px 0px 0px;'>";
            foreach ($dbufr[$i] as $entry) {

                $boat = "{$entry['class']} - {$entry['sailnum']}";
                $laps = $_SESSION["e_$eventid"]["fl_{$entry['fleet']}"]['maxlap'];
//                $scoring = $_SESSION["e_$eventid"]["fl_{$entry['fleet']}"]['scoring'];

                empty($entry['code']) ? $cog_style = "primary" : $cog_style = "danger";


                if ($entry['status'] == "X")
                {
                    $state = $boat_states['notracing'];
                }
                elseif ($entry['status'] == "F")
                {
                    $state = $boat_states['finished'];
                }
                else {
                    if ($entry['lap'] == 0)
                    {
                        $state = $boat_states['beforelap'];
                    }
                    else
                    {
                        $state = $boat_states['racing'];
                    }
                }

                // competitor identity and lap count
                $title = $entry['label'];
                $lapcount = "L {$entry['lap']}";

                // popover information
                $ptitle = "<b>$boat</b>";
                empty($entry['code']) ? $pstatus = strtoupper($state['val']) : $pstatus = $entry['code'];
                $pcontent = "line: <b>{$entry['f_line']}</b>&nbsp;&nbsp;pos: <b>{$entry['f_pos']}</b>&nbsp;&nbsp;lap: <b>{$entry['lap']}</b>";

                // set params for link options
                unset($entry['class']);
                unset($entry['sailnum']);
                $params_list = "&" . http_build_query($entry);

                // setcode link
                $link = <<<EOT
timer_sc.php?eventid=$eventid&pagestate=setcode&fleet={$entry['fleet']}&entryid={$entry['entryid']}&boat={$entry['boat']}
&racestatus={$entry['status']}&declaration={$entry['declaration']}&lap={$entry['lap']}&finishlap=$laps}
EOT;
                // finish menu options
                $finish_option = $finish_link."&entryid=".$entry['entryid'];


                // bunch / undo / finish options
                if ($entry['status'] == "F") {
                    $bunch_option = "";
                    $undo_option = "";
                    $finish_display = $entry['f_pos'];
                    $move_option = "<li><a href=\"$move_link$params_list&dir=up\">Move Up</a></li>
                                    <li><a href=\"$move_link$params_list&dir=down\">Move Down</a></li>";
                    $clearfinish_option = "<li><a href=\"$clearfinish_link$params_list&dir=up\">Clear Finish</a></li>";
                }
                else
                {
                    $bunch_option = "<li><a href='$bunch_link$params_list'>Bunch</a></li>";
                    $undo_option = "<li><a href=\"$undo_link$params_list\">Undo Last Lap</a></li>";
                    $finish_display = "<span class=\"glyphicon glyphicon-flag\" aria-hidden=\"true\"></span>";
                    $move_option = "";
                    $clearfinish_option = "";
                }


                $options_bufr = <<<EOT
                <ul class="dropdown-menu">
                    $undo_option
                    $bunch_option
                    $clearfinish_option
                    $move_option
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
                $entry['status'] == "F" ? $disabled = "disabled" : $disabled = "";  // turn of lap timing for boats that have finished

                $data_bufr .= <<<EOT
                <div class="btn-group btn-block" role="group" aria-label="..." >
                    <div data-toggle="popover" data-placement="top"  title="$ptitle" data-content="$pstatus</br>$pcontent">
                        <a type="button" href="$timelap_link$params_list" class="btn btn-{$state['color']} btn-xs $disabled" style="width:60%" 
                            >
                            <div class="pull-left">$title</div>
                            <div class="pull-right">$lapcount</div>     
                        </a>                 
                        <button type="button" class="btn btn-$cog_style btn-xs dropdown-toggle" 
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
                        </button>
                        $options_bufr
                        <a type="button" href="$finish_option" class="btn btn-{$state['color']} btn-xs dropdown-toggle" style="min-width: 28px">
                            $finish_display
                        </a>
               
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
