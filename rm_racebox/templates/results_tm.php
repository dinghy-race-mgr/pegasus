<?php

function result_tabs($params = array())
{
    //echo "<pre>".print_r($params,true)."</pre>";
    
    $eventid = $params['eventid'];

    $tabs = "";
    $panels = "";

    // state settings
    $state_cfg = array(
        "default"  => array("row_style" => "default", "label_style" => "label-primary", "annotation" => ""),
        "racing"   => array("row_style" => "racing", "label_style" => "label-default", "annotation" => " <span class='text-primary glyphicon glyphicon-time'></span> "),
        "finished" => array("row_style" => "finished", "label_style" => "label-finished", "annotation" => " FINISHED"),
        "lastlap"  => array("row_style" => "lastlap", "label_style" => "label-danger", "annotation" => "<span class='text-danger'> LAST LAP</span>"),
        "excluded" => array("row_style" => "excluded", "label_style" => "label-primary", "annotation" => " EXCLUDED"),
    );

    for ($i = 1; $i <= $params['num-fleets']; $i++)
    {
        $fleet = $_SESSION["e_$eventid"]["fl_$i"];
        $num_entries = $num_entries  = $_SESSION["e_$eventid"]["fl_$i"]['entries'];
        //empty($params['data'][$i]) ? $count = 0 : $count = count($params['data'][$i]);
        $racetype = $_SESSION["e_$eventid"]["fl_$i"]['scoring'];

        // create TABS
        if (count($params['warning'][$i]) > 0)
        {
            $tab_label = <<<EOT
            <span class="label pull-right">
                <span class="glyphicon glyphicon-remove text-danger" aria-hidden="true"></br>&nbsp;</span>
            </span>
EOT;
        }
        else
        {
            $tab_label = <<<EOT
            <span class="label pull-right">
                <span class="glyphicon glyphicon-ok text-success" aria-hidden="true"></span>
            </span>           
EOT;
        }

        $tabs.= <<<EOT
        <li role="presentation" class="lead text-center">
              <a class="text-primary" href="#fleet$i" aria-controls="{$fleet['name']}" role="tab" data-toggle="pill" style="padding-top: 20px;">
              <b>{$fleet['name']}</b> <br> $tab_label             
              </a>
        </li>
EOT;


        // create PANELS
        if ($num_entries <= 0)
        {
            $panels .= <<<EOT
            <div role="tabpanel" class="tab-pane" id="fleet$i">
                <div class="alert alert-info text-center" role="alert" style="margin-right: 40%;">
                   <h3>no entries in the {$fleet['name']} fleet</h3><br>
                </div>
            </div>
EOT;
        }
        else
        {
            $columns  = format_columns($racetype);
            $rows     = format_rows($eventid, $racetype, $params['data'][$i]);
            $warnings = format_warnings($params['warning'][$i]);
            $panels .= <<<EOT
            <div role="tabpanel" class="tab-pane" id="fleet$i">
                $warnings
                <table class="table table-striped table-condensed table-hover table-top-padding table-top-border">
                    $columns
                    <tbody>
                        $rows
                    </tbody>
                </table>
            </div>
EOT;
        }
    }

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

function format_columns($racetype)
{
    if ($racetype == "level")  // pn and corrected time not required
    {
        $columns = <<<EOT
        <thead>
            <tr class="text-info">
               <th width="6%"  > </th>
               <th width="13%" >class</th>
               <th width="8%"  >no.</th>
               <th width="20%" >crew</th>
               <th width="10%" style="text-align: center" >elapsed</th>
               <th width="6%" style="text-align: center" >laps</th>
               <th width="6%" style="text-align: center" >code</th>
               <th width="6%" style="text-align: center" >points</th>
               <th width="2%" >&nbsp;</th>
               <th width="5%" >edit</th>
               <th width="5%" >delete</th>
            </tr>
        </thead>
EOT;
    }
    elseif ($racetype == "pursuit") // elapsed and corrected time not required
    {
        $columns = <<<EOT
        <thead>
            <tr class="text-info">
               <th width="6%"  > </th>
               <th width="13%" style="font-weight: bold" >class</th>
               <th width="8%"  style="font-weight: bold" >no.</th>
               <th width="20%" >crew</th>
               <th width="10%" style="text-align: center" >elapsed</th>
               <th width="6%" style="text-align: center" >laps</th>
               <th width="6%" style="text-align: center" >code</th>
               <th width="6%" style="text-align: center" >points</th>
               <th width="2%" >&nbsp;</th>
               <th width="5%" >edit</th>
               <th width="5%" >delete</th>
            </tr>
        </thead>
EOT;
    }
    elseif ($racetype == "handicap")
    {
        $columns = <<<EOT
        <thead>
            <tr class="text-info">
               <th width="6%"  > </th>
               <th width="13%" >class</th>
               <th width="8%"  >no.</th>
               <th width="20%" >crew</th>
               <th width="6%" style="text-align: center" >PN</th>
               <th width="10%" style="text-align: center" >elapsed</th>
               <th width="10%" style="text-align: center" >corrected</th>
               <th width="6%" style="text-align: center" >laps</th>
               <th width="6%" style="text-align: center" >code</th>
               <th width="6%" style="text-align: center" >points</th>
               <th width="2%" >&nbsp;</th>
               <th width="5%" >edit</th>
               <th width="5%" >delete</th>
            </tr>
        </thead>
EOT;
    }
    else  // average lap
    {
        $columns = <<<EOT
        <thead>
            <tr class="text-info">
               <th width="6%"  > </th>
               <th width="11%" >class</th>
               <th width="8%"  >no.</th>
               <th width="13%" >crew</th>
               <th width="6%" style="text-align: center" >PN</th>
               <th width="10%" style="text-align: center" >elapsed</th>
               <th width="10%" style="text-align: center" >corrected</th>
               <th width="6%" style="text-align: center" >laps</th>
               <th width="6%" style="text-align: center" >code</th>
               <th width="6%" style="text-align: center" >points</th>
               <th width="2%" >&nbsp;</th>
               <th width="5%" >edit</th>
               <th width="5%" >delete</th>
            </tr>
        </thead>
EOT;
    }

    return $columns;
}

function format_warnings($warnings)
{
    $bufr = "";
    $warn_bufr = "";
    foreach ($warnings as $warning)
    {
        $warn_bufr.= <<<EOT
        &nbsp;- {$warning['msg']}<br>
EOT;
    }
    $warn_bufr = rtrim($warn_bufr, "<br>");
    if (!empty($warn_bufr))
    {
        $warn_bufr = rtrim($warn_bufr, "<br>");
        $bufr.= <<<EOT
        <div class="bg-warning" style="width: 50%; color: white; font-size: 20px">
            <div class="row" style="padding:5px 5px 5px 5px!important">
                <div class="col-md-3" ><span class="glyphicon glyphicon-alert" aria-hidden="true"></span> Warnings</div>
                <div class="col-md-9" >$warn_bufr</div>
            </div>
        </div>
EOT;
    }
    else  // placeholder
    {
        $bufr.= <<<EOT
        <div class="" style="width: 50%; color: white; font-size: 20px">
            <div class="row" style="padding:5px 5px 5px 5px!important">
                <div class="col-md-3" >&nbsp;</div>
                <div class="col-md-9" >&nbsp;</div>
            </div>
        </div>
EOT;
    }

    return $bufr;
}

function format_rows($eventid, $racetype, $race_results)
{
    $rows = "";
    foreach($race_results as $result)
    {
        $result['editbtn'] = editresult_html($eventid, $result['entryid'], $result['boat']);
        $result['deletebtn'] = <<<EOT
            <span data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true" data-title="remove boat from race" data-placement="top">
                <a type="button" class="btn btn-danger btn-xs" data-toggle="modal"
                       rel="tooltip" data-original-title="remove boat from race" data-placement="bottom" data-target="#removeModal"
                       data-entryid="{$result['entryid']}"
                       data-entryname="{$result['boat']}" >
                       <span class="glyphicon glyphicon-trash"></span>
                </a>
            </span>
EOT;

        // conversions
        $result['et'] = u_conv_secstotime($result['et']);
        $result['ct'] = u_conv_secstotime($result['ct']);
        $boat = $boat = "{$result['class']} - {$result['sailnum']}";

        // code
        $link = "results_sc.php?eventid=$eventid&pagestate=setcode&fleet={$result['fleet']}
                 &entryid={$result['entryid']}&boat={$result['boat']}&racestatus={$result['status']}
                 &declaration={$result['declaration']}&lap={$result['lap']}&finishlap={$result['finishlap']}";
        $code_link = get_code($result['code'], $link, "resultcodes");

        if ($racetype == "level")  // pn and corrected time not required
        {
            $rows.= <<<EOT
            <tr class="table-data">
               <td >{$result['status_flag']}</td>
               <td class="truncate">{$result['class']}</td>
               <td class="truncate">{$result['sailnum']}</td>
               <td class="truncate">{$result['competitor']}</td>
               <td style="text-align: center">{$result['et']}</td>
               <td style="text-align: center">{$result['lap']}</td>
               <td style="text-align: center">$code_link</td>
               <td style="text-align: center">{$result['points']}</td>
               <td >&nbsp;</td>
               <td >{$result['editbtn']}</td>
               <td >{$result['deletebtn']}</td>
            </tr>
EOT;
        }
        elseif ($racetype == "pursuit") // elapsed and corrected time not required
        {
            $rows.= <<<EOT
            <tr class="table-data">
               <td >{$result['status_flag']}</td>
               <td class="truncate">{$result['class']}</td>
               <td class="">{$result['sailnum']}</td>
               <td class="truncate">{$result['competitor']}</td>
               <td style="text-align: center">{$result['et']}</td>
               <td style="text-align: center">{$result['lap']}</td>
               <td style="text-align: center">$code_link</td>
               <td style="text-align: center">{$result['points']}</td>
               <td >&nbsp;</td>
               <td >{$result['editbtn']}</td>
               <td >{$result['deletebtn']}</td>
            </tr>
EOT;
        }
        elseif ($racetype == "handicap")
        {
            $rows.= <<<EOT
            <tr class="table-data">
               <td >{$result['status_flag']}</td>
               <td class="truncate">{$result['class']}</td>
               <td class="">{$result['sailnum']}</td>
               <td class="truncate">{$result['competitor']}</td>
               <td style="text-align: center">{$result['pn']}</td>
               <td style="text-align: center">{$result['et']}</td>
               <td style="text-align: center">{$result['ct']}</td>
               <td style="text-align: center">{$result['lap']}</td>
               <td style="text-align: center">$code_link</td>
               <td style="text-align: center">{$result['points']}</td>
               <td >&nbsp;</td>
               <td >{$result['editbtn']}</td>
               <td >{$result['deletebtn']}</td>
            </tr>
EOT;
        }
        else  // average lap
        {
            $rows.= <<<EOT
            <tr class="table-data">
               <td >{$result['status_flag']}</td>
               <td class="truncate">{$result['class']}</td>
               <td class="">{$result['sailnum']}</td>
               <td class="truncate">{$result['competitor']}</td>
               <td style="text-align: center">{$result['pn']}</td>
               <td style="text-align: center">{$result['et']}</td>
               <td style="text-align: center">{$result['ct']}</td>
               <td style="text-align: center">{$result['lap']}</td>
               <td style="text-align: center">$code_link</td>
               <td style="text-align: center">{$result['points']}</td>
               <td >&nbsp;</td>
               <td >{$result['editbtn']}</td>
               <td >{$result['deletebtn']}</td>
            </tr>
EOT;
        }
    }
    return $rows;
}

function editresult_html($eventid, $entryid, $boat)
{
        $bufr = <<<EOT
        <span data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true"
              data-title="edit result details for this boat" data-placement="top">
            <a type="button" class="btn btn-success btn-xs" data-toggle="modal" data-target="#editresultModal" data-boat="$boat"
                    data-iframe="results_edit_pg.php?eventid=$eventid&pagestate=init&entryid=$entryid" >
                    <span class="glyphicon glyphicon-pencil"></span>
            </a>
        </span>
EOT;

    return $bufr;
}


function result_edit_warnings($params)
{
    //echo "<pre>".print_r($params,true)."</pre>";
    $eventid = $params['eventid'];
    $entryid = $params['entryid'];

    $bufr = "";

    // create list
    $abufr = "";
    foreach ($params['warnings'] as $warning)
    {
        if ($warning['type'] == "error")
        {
            $style = "alert-danger";
            $label = "Error!";
        }
        else
        {
            $style = "alert-warning";
            $label = "Warning!";
        }

        $abufr.= <<<EOT
        <div class="alert $style">
            <h4>$label {$warning['title']}</h4>
            <p>{$warning['msg']}</p>
        </div>
EOT;
    }

    // page with buttons
    $bufr.= <<<EOT
    <div>
    <h2>Possible Issues</h2>
    $abufr    
    </div>
    <div class="pull-right">
        <button onclick='window.top.location.href = "results_pg.php?eventid=$eventid";' type="button" class="btn btn-default" data-dismiss="modal">
            <span class="glyphicon glyphicon-remove"></span>&nbsp;ignore
        </button>
        <button onclick='location.href = "results_edit_pg.php?eventid=$eventid&pagestate=init&entryid=$entryid";' type="submit" class="btn btn-warning">
            <span class="glyphicon glyphicon-pencil"></span>&nbsp;back to edit
        </button>
    </div>
EOT;

    return $bufr;
}


function fm_result_edit($params)
{
    $lbl_width = "col-xs-2";
    $fld_width = "col-xs-5";
    $hlp_width = "col-xs-4";

    //echo "<pre>".print_r($params,true)."</pre>";

    $resultcodes = array();
    foreach ($params['resultcodes'] as $row) {
        $resultcodes[] = array ("code" => $row['code'], "label" => "{$row['code']} : {$row['short']}");
    }
    $scoring_code_bufr = u_selectcodelist($resultcodes, $params['code']);

    $helm_bufr = "";
    if ($params['points_allocation'] == "boat") {
        $helm_bufr = <<<EOT
        <div class="form-group">
            <label class="$lbl_width control-label">helm</label>
            <div class="$fld_width inputfieldgroup">
                <input type="text" class="form-control" style="text-transform: capitalize;" id="helm" name="helm" value="{helm}"
                    required data-fv-notempty-message="this information is required"
                />
                <div class="$hlp_width help-block">e.g. Fred Flintstone</div>
            </div>
        </div>
EOT;
    }

/*
    // loop over lap times - field names are laptime[lap]
    $i = 1;
    $bufr = "";
    if (!empty($laptimes))
    {
        $laptimes = explode(",", $params['laptimes']);
        foreach ($laptimes as $laptime) {
            $formatted_time = gmdate("H:i:s", $laptime);
            $bufr .= <<<EOT
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
    }
*/
//    $etime = "";
//    if (!empty($params['etime']))
//    {
        $etime = gmdate("H:i:s", $params['etime']);
/*    }*/

    $html = <<<EOT

    <div class="alert well well-sm" role="alert">
        <p class="text-info"><b>Change boat / race /lap details as required for this competitor and then click the update button to submit the changes </p>
    </div>
    
    <form id="resulteditForm" class="form-horizontal" action="results_edit_pg.php?pagestate=submit" method="post"
        data-fv-framework="bootstrap"
        data-fv-icon-valid="glyphicon glyphicon-ok"
        data-fv-icon-invalid="glyphicon glyphicon-remove"
        data-fv-icon-validating="glyphicon glyphicon-refresh"
    >
    <input name="entryid" type="hidden" id="identryid" value="{$params['entryid']}">
    <input name="eventid" type="hidden" id="ideventid" value="{$params['eventid']}">
    
  <div class="panel panel-default">
    <div class="panel-heading panel-heading-nav">
    
      <ul class="nav nav-pills">
      
        <li role="presentation" >
          <a href="#boatpanel" aria-controls="boatpanel" role="tab" data-toggle="tab">
          <span class="glyphicon glyphicon-list-alt"> </span> Boat Details</a>
        </li>
        
        <li role="presentation" class="active">
          <a href="#resultpanel" aria-controls="resultpanel" role="tab" data-toggle="tab">
          <span class="glyphicon glyphicon-flag"> </span> Race Details</a>
        </li>
        
        <!-- li role="presentation" >
          <a href="#lapspanel" aria-controls="lapspanel" role="tab" data-toggle="tab">
          <span class="glyphicon glyphicon-time"> </span> Lap Times</a>
        </li -->
        
        <div class="pull-right">
        <button onclick='window.top.location.href = "results_pg.php?eventid={$params['eventid']}";' type="button" class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span>&nbsp;cancel</button>
        <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-ok"></span>&nbsp;Update Result</button>
        </div>
      </ul>
      
    </div>
    
    
    <div class="panel-body">
    
      <div class="tab-content">
      
        <!-- boat panel -->
        <div role="tabpanel" class="tab-pane fade in" id="boatpanel">
        
            <!-- sailnumber -->  
            <div class="form-group">
                <label class="$lbl_width control-label text-success" >sail no.</label>
                <div class="$fld_width inputfieldgroup">
                    <input type="text" class="form-control" id="idsailnum" name="sailnum" value="{sailnum}"
                        required data-fv-notempty-message="this information is required"
                    />
                </div>
                <div class="$hlp_width help-block">sail number used for this race</div>
            </div>
                
            <!-- helm -->  
            $helm_bufr
            
            <!-- crew -->          
            <div class="form-group">
                <label class="$lbl_width control-label text-success">crew</label>
                <div class="$fld_width inputfieldgroup">
                    <input type="text" class="form-control" style="text-transform: capitalize;" id="idcrew" name="crew" value="{crew}"
                    placeholder="for double handers only"
                    />
                </div>
                <div class="$hlp_width help-block">e.g. Barney Rubble</div>
            </div>
                
            <!-- Club -->
            <div class="form-group">
                <label class="$lbl_width control-label text-success">club</label>
                <div class="$fld_width inputfieldgroup">
                    <input type="text" class="form-control" style="text-transform: capitalize;" id="club" name="club" value="{club}"
                    />
                </div>
                <div class="$hlp_width help-block">generally only required for open events</div>
            </div>
               
            <!-- PN -->  
            <div class="form-group">
                <label class="$lbl_width control-label text-success">yardstick</label>
                <div class="$fld_width inputfieldgroup">
                    <input type="text" class="form-control" id="idpn" name="pn" value="{pn}"
                        required data-fv-notempty-message="this information is required"
                        min="{$_SESSION['min_py']}" max="{$_SESSION['max_py']}"
                        data-fv-between-message="The PY must be between {$_SESSION['min_py']} and {$_SESSION['max_py']}"
                    />
                </div>
                <div class="$hlp_width help-block">handicap number for this race - if unsure use number for a similar class</div>
            </div>
                   
        </div>
        
        <!-- result panel --> 
        <div role="tabpanel" class="tab-pane fade in active" id="resultpanel">
        
            <!-- Laps -->  
            <div class="form-group">
                <label class="$lbl_width control-label text-success">laps completed</label>
                <div class="$fld_width inputfieldgroup">
                    <input type="number" class="form-control" id="idlap" name="lap" value="{lap}"
                    required data-fv-notempty-message="the number of laps completed is required"
                    placeholder=""
                    min="0" 
                    />
                </div>
            </div>       
            
            <!-- elapsed time -->
            <div class="form-group">
                <label class="$lbl_width control-label text-success">elapsed time</label>
                <div class="$fld_width inputfieldgroup">
                    <input type="text" class="form-control" id="idetime" name="etime" value="$etime"
                           placeholder="hh:mm:ss"
                           required data-fv-notempty-message="a time [hh:mm:ss] must be entered"
                           data-fv-regexp="true"
                           data-fv-regexp-regexp="^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$"
                           data-fv-regexp-message="lap time must be in HH:MM:SS format"
                    />
                </div>
                <div class="$hlp_width help-block">elapsed time at the finish (hh:mm:ss)</div>
            </div>        
            
            <!-- code -->
            <div class="form-group">
                <label class="control-label $lbl_width text-success">result code</label>
                <div class = "$fld_width">
                    <select class="form-control $fld_width" name="code" id="idcode" >        
                        $scoring_code_bufr
                    </select>
                </div>
                <div class="$hlp_width help-block">e.g. OCS, NCS, DNF - otherwise leave blank</div>
            </div>
            
            <!-- penalty score -->        
            <div class="form-group">
                <label class="control-label $lbl_width text-success">penalty score</label>
                <div class="$fld_width">
                    <input name="penalty" type="number" class="form-control" id="idpenalty" value="{penalty}"
                    placeholder="penalty points to be applied to score">
                </div>
                <div class="$hlp_width help-block">CARE - only use when using DPI scoring code. </div>
            </div>
            
            <!-- notes -->
            <div class="form-group">
                <label class="control-label $lbl_width text-success">notes</label>
                <div class="$fld_width">
                    <input name="note" type="text" class="form-control" id="idnote" value="{note}"
                    >
                </div>
                <div class="$hlp_width help-block">useful to record why result was edited</div>
            </div>
          
        </div>
        
        <!-- lap times panel 
        <div role="tabpanel" class="tab-pane fade in" id="lapspanel">
            Lap time fields here
        </div> -->
      
      </div> <!-- end tab-content -->
      
    </div>  <!-- end panel-body -->
    
  </div> <!-- end panel -->

  

  </form>
EOT;

    return $html;
}

function fm_change_finish($params)
{
    //echo "<pre>".print_r($params,true)."</pre>";

    // instructions
    $html = <<<EOT
    <div class="alert well well-sm text-info" role="alert">
        <p>This can be useful if you...<br> - have forgotten to shorten course and boats are showing as 'still racing', OR<br>
         - you have abandoned the race and want to take the results from a previously completed lap</p>
        <p>Set the finish lap for each fleet to the lap you want the boats to finish on (leading boat if an 'average lap' race).</p>
    </div>

    <div class="row text-info">
            <div class="col-xs-5" style="text-align:right;"><b>FLEET</b></div>
            <div class="col-xs-7" ><b>FINISH LAP</b></div>
    </div>
EOT;

    // create input fields - one per fleet
    $fields_bufr = "";
    foreach ($params['fleets'] as $i=>$fleet)
    {
        if ($fleet['status'] == "notstarted")
        {
            $fields_bufr.=<<<EOT
                <div class="form-group">
                    <label class="col-xs-offset-2 col-xs-3 control-label" style="text-align: left;">{$fleet['name']} </label>
                    <div class="col-xs-6 ">
                        <p class="text-info">race not started - finishing lap cannot be changed</p>
                        <input type="hidden" id="finlap$i" name="finlap$i" value="{$fleet['maxlaps']}">
                    </div>   
                </div >
EOT;
        }
        elseif ($fleet['scoring'] == "pursuit")
        {
            $fields_bufr.=<<<EOT
                <div class="form-group">
                    <label class="col-xs-offset-2 col-xs-3 control-label" style="text-align: left;">{$fleet['name']} </label>
                    <div class="col-xs-6 ">
                        <p class="text-info">pursuit race - finishing lap cannot be changed</p>
                        <input type="hidden" id="finlap$i" name="finlap$i" value="{$fleet['maxlaps']}">
                    </div>   
                </div >
EOT;
        }
        else
        {
            $fields_bufr.=<<<EOT
            <div class="form-group">
                <label class="col-xs-offset-2 col-xs-3 control-label" style="text-align: left;">{$fleet['name']} </label>
                <div class="col-xs-3 inputfieldgroup">
                    <input type="number" class="form-control" id="laps$i" name="finlap$i" value="{$fleet['maxlaps']}"
                        required data-fv-notempty-message="you need to provide the required finishing lap" min="1" max="{$fleet['maxlaps']}"
                        data-fv-between-message="must be a value between 1 and {$fleet['maxlaps']}"
                    />
                </div> 
                <div class="col-xs-3 control-label" style="text-align: left;">
                    <label> laps </label>
                </div>   
            </div >
EOT;
        }
    }

    return $html;
}


function process_header($params=array())
{
    $html = <<<EOT
    <!DOCTYPE html><html lang="en">
    <head>
            <title>{title}</title>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="description" content="">
            <meta name="author" content="">

            <link   rel="shortcut icon"    href="{loc}/common/images/favicon.ico">
            <link   rel="stylesheet"       href="{loc}/common/oss/bootstrap341/css/{theme}bootstrap.min.css" >
            <script type="text/javascript" src="{loc}/common/oss/jquery/jquery.min.js"></script>
            <script type="text/javascript" src="{loc}/common/oss/bootstrap341/js/bootstrap.min.js"></script>
            <script type="text/javascript" src="{loc}/common/oss/bs-growl/jquery.bootstrap-growl.min.js"></script>

            <!-- Custom styles for this template -->
            <link href="{stylesheet}" rel="stylesheet">

    </head>
    <body>
    <!-- h4 style="color: darkorange; margin-top: 5px !important; margin-bottom: 5px !important;">Results publishing ...</h4-->
EOT;
    return $html;
}

function process_footer($params=array())
{
    // setup language
    $lang = array(
        "0" => array(
            "step"   => "Adding wind details to event",
            "error"  => "",
            "action" => "",
        ),
        "1" => array(
            "step"   => "Archiving results data",
            "error"  => "",
            "action" => "",
        ),
        "2" => array(
            "step"   => "Creating race results file",
            "error"  => "",
            "action" => "",
        ),
        "3" => array(
            "step"   => "Updating series results file",
            "error"  => "",
            "action" => "",
        ),
        "4" => array(
            "step"   => "Transferring files to club website",
            "error"  => "",
            "action" => "",
        ),
    );

    $problem = array_search(false, $params['complete']);

    if ($problem)
    {
        $style = "danger";
        $title = "Problem!";
        $message = "The results publishing failed at <b>Step $problem - {$lang[$problem]["step"]}</b>";
    }
    else
    {
        $style = "success";
        $title = "Success!";
        $message = "The results publishing was completed successfully";
    }

    $html = <<<EOT
    <div style='padding-left:10%; width: 80%; position: absolute; top: {top}%;' >
    <div class="alert alert-$style" role="alert">
        <p style='font-size: 1.3em;'><b>$title</b></p>
        <p>$message</p>
        <p><i>You can republish the results at any time before you close the race</i><p>
    </div>
    </div>
EOT;
    return $html;
}


function fm_publish($params)
{
    /**
     * This form collects information from OOD prior to publishing results.
     *
     * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
     *
     * %%copyright%%
     * %%license%%
     *
     */

    $html = <<<EOT
    <div class="container" style="margin-top: -40px;">
              
        <div class="alert well well-sm" role="alert">
            <p class="text-info"><b>Add details on the WIND during the race and any NOTES to be included in the published results</p>
        </div>

        <div class="margin-top-05">
            <style type="text/css">
               #windForm  .inputfieldgroup .form-control-feedback,
               #windForm  .selectfieldgroup .form-control-feedback {
                    top: 0px;
                    right: -15px;
                }
            </style>
            <form class="form-horizontal" id="windForm" method="post" role="search" autocomplete="off"
                  action="results_publish_pg.php?eventid={eventid}&pagestate=process"
                  data-fv-addons="mandatoryIcon"
                  data-fv-addons-mandatoryicon-icon="glyphicon glyphicon-asterisk"
                  data-fv-framework="bootstrap"
                  data-fv-icon-valid="glyphicon glyphicon-ok"
                  data-fv-icon-invalid="glyphicon glyphicon-remove"
                  data-fv-icon-validating="glyphicon glyphicon-refresh"
            >

                <div class="row">
                    <label class="col-xs-3 col-xs-offset-3 control-label text-success" style="text-align: center !important;">Race Start</label>
                    <label class="col-xs-3 control-label text-success" style="text-align: center !important;">Race End</label>
                    <hr class="col-xs-9 col-xs-offset-1" style="margin-top: 0px;">
                </div>
                <div class="row">
                    <label class="col-xs-3 col-xs-offset-1 control-label">Wind Direction</label>
                    <div class="col-xs-3 selectfieldgroup">
                        <select class="form-control" name="wd_start" style="width: 150px">
                            <option value="">&hellip; pick one </option>
                            {wd_start}
                        </select>
                    </div>
                    <div class="col-xs-3 selectfieldgroup">
                        <select class="form-control" name="wd_end" style="width: 150px">
                            <option value="">&hellip; pick one </option>
                            {wd_end}
                        </select>
                    </div>
                </div>
                <br>
                <div class = "row">
                    <label class="col-xs-3 col-xs-offset-1 control-label">Wind Speed</label>
                    <div class="col-xs-3 selectfieldgroup">
                        <select class="form-control" name="ws_start" style="width: 150px">
                            <option value="">&hellip; pick one </option>
                            {ws_start}
                         </select>
                    </div>
                    <div class="col-xs-3 selectfieldgroup">
                        <select class="form-control" name="ws_end" style="width: 150px">
                            <option value="">&hellip; pick one </option>
                            {ws_end}
                       </select>
                    </div>
                </div>
                <br>
                <div class="row">
                    <label class="col-xs-3 col-xs-offset-1 control-label">Results Status</label>
                    <div class="col-xs-7 inputfieldgroup" style="padding-bottom: 10px">
                        <label class="radio-inline">
                               <input type="radio" name="result_status" value="final" checked> final
                        </label>
                        <label class="radio-inline">
                              <input type="radio" name="result_status" value="provisional"> provisional
                        </label>
                        <label class="radio-inline">
                              <input type="radio" name="result_status" value="embargoed"> embargoed
                        </label>
                    </div>
                </div>
                 <div class="row">
                    <label class="col-xs-3 col-xs-offset-1 control-label">Club Names?</label>
                    <div class="col-xs-7 checkbox">
                        <label>
                          <input type="checkbox" id="include_club" name="include_club"> tick to include club names in results
                        </label>
                    </div>
                </div>
                <br>
                <div class="row">
                    <label class="col-xs-3 col-xs-offset-1 control-label">Notes</label>
                    <div class="col-xs-7 inputfieldgroup">
                        <input type="text" class="form-control" id="notes" name="result_notes" value="{notes}"
                               placeholder="any notes you want to appear in the results (e.g. subject to protest)"  />
                    </div>
                </div>

                <div class="row">
                    <br>
                    <div class="col-xs-10">
                         <button class="btn btn-primary btn-md pull-right" type="submit">
                             &nbsp;&nbsp;Publish&nbsp;&nbsp;<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                         </button>
                    </div>
                </div>

            </form>
            <script>
                  $(document).ready(function() {
                      $('#windForm').formValidation({
                            excluded: [':disabled'],
                      })

                      $('#resetBtn').click(function() {
                         $('#').data('bootstrapValidator').resetForm(true);
                      });

                      $("[data-toggle=popover]").popover({trigger: 'hover',html: 'true'});
                  });
             </script>
        </div>
    </div>
EOT;

    return $html;

}

