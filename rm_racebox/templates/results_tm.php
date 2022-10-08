<?php

function result_tabs($params = array())
{
    //echo "<pre><br><br><br></br>PARAMS".print_r($params,true)."</pre>";
    //echo "<pre>".print_r($params['data'],true)."</pre>";
    
    $eventid = $params['eventid'];

    $tabs = "";
    $panels = "";

    for ($i = 1; $i <= $params['num-fleets']; $i++)
    {

        $fleet = $_SESSION["e_$eventid"]["fl_$i"];     // FIXME - not great to use session variables in templates
        $fleet_name = strtolower($fleet['name']);
        //$num_entries = $_SESSION["e_$eventid"]["fl_$i"]['entries'];
        $racetype = $_SESSION["e_$eventid"]["fl_$i"]['scoring'];    // FIXME - not great to use session variables in templates

        // create TABS
        if ($params['entries'][$i] == 0)
        {
            $tab_label = "";
        }
        else
        {
            if (count($params['warning'][$i]) > 0)
            {
                $tab_label = <<<EOT
            <span class="label label-danger label-sm pull-right" style="font-weight: normal; width: 120px">
                <span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Warnings
            </span>
EOT;
            }
            else
            {
                $tab_label = <<<EOT
            <span class="label label-success label-sm pull-right" style="font-weight: normal; width: 120px">
                <span class="glyphicon glyphicon-ok" aria-hidden="true"></span> &nbsp;&nbsp;OK
            </span>           
EOT;
            }
        }


        $tabs.= <<<EOT
        <li role="presentation" class="lead text-center">
              <a class="text-primary" href="#fleet$i" aria-controls="$fleet_name" role="tab" data-toggle="pill" style="padding-top: 20px;">
              <b>$fleet_name</b><br><br>$tab_label        
              </a>
        </li>
EOT;


        // create PANELS
        if (empty($params['data'][$i]))
        {
            $panels .= <<<EOT
            <div role="tabpanel" class="tab-pane" id="fleet$i">
                <div class="alert alert-info text-center" role="alert" style="margin-right: 40%;">
                   <h3>no entries in the $fleet_name fleet</h3><br>
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
        - &nbsp;&nbsp;&nbsp;{$warning['msg']}<br>
EOT;
    }
    $warn_bufr = rtrim($warn_bufr, "<br>");
    if (!empty($warn_bufr))
    {
        $warn_bufr = rtrim($warn_bufr, "<br>");

        $bufr.= <<<EOT
        <div class="col-md-8 alert alert-danger">
            <span class="lead">
                <span class="glyphicon glyphicon-alert" aria-hidden="true"></span>&nbsp;&nbsp;&nbsp;<b>Warnings&hellip;</b>
            </span>
            <p style="text-indent: 50px; font-size: 1.2em;">$warn_bufr</p>
        </div>
EOT;

    }
    else
    {
        $bufr.= <<<EOT
        <div class="col-md-8 alert alert-success">
            <span class="lead">
                <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>&nbsp;&nbsp;&nbsp;Results for this fleet are complete&hellip;
            </span>
        </div>
EOT;
    }

    return $bufr;
}

function format_rows($eventid, $racetype, $race_results)
{
    $rows = "";
    $row_num = 0;
    foreach($race_results as $k => $result)
    {
        $row_num++;

        $result['editbtn'] = editresult_html($eventid, $result['entryid'], $result['boat'], $result['status']);

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
        if ($result['lap'] == 0 and empty($result['code'])) { $result['points'] = ""; }

        // code
        $link = <<<EOT
        results_sc.php?eventid=$eventid&pagestate=setcode&fleet={$result['fleet']}
&entryid={$result['entryid']}&boat={$result['boat']}&racestatus={$result['status']}
&declaration={$result['declaration']}&lap={$result['lap']}&finishlap={$result['finishlap']}
EOT;
        $row_num >= 8 ? $dirn = "dropup" : $dirn = "" ;

        $code_link = get_code($result['code'], $link, "resultcodes", $dirn);

        $result['stillracing'] == "Y" ? $row_style = "lastlap" : $row_style = "";

        // points
        $result['points'] >= 999 ? $points = " - " : $points = number_format((float)$result['points'], 1, '.', '');;

        if ($racetype == "level")  // pn and corrected time not required
        {
            $rows.= <<<EOT
            <tr class="table-data $row_style" >
               <td >{$result['status_flag']}</td>
               <td class="truncate">{$result['class']}</td>
               <td class="truncate">{$result['sailnum']}</td>
               <td class="truncate">{$result['competitor']}</td>
               <td style="text-align: center">{$result['et']}</td>
               <td style="text-align: center">{$result['lap']}</td>
               <td style="text-align: center">$code_link</td>
               <td style="text-align: center">$points</td>
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
               <td style="text-align: center">$points</td>
               <td >&nbsp;</td>
               <td >{$result['editbtn']}</td>
               <td >{$result['deletebtn']}</td>
            </tr>
EOT;
        }
        elseif ($racetype == "handicap $row_style")
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
               <td style="text-align: center">$points</td>
               <td >&nbsp;</td>
               <td >{$result['editbtn']}</td>
               <td >{$result['deletebtn']}</td>
            </tr>
EOT;
        }
        else  // average lap
        {
            $rows.= <<<EOT
            <tr class="table-data $row_style">
               <td >{$result['status_flag']}</td>
               <td class="truncate">{$result['class']}</td>
               <td class="">{$result['sailnum']}</td>
               <td class="truncate">{$result['competitor']}</td>
               <td style="text-align: center">{$result['pn']}</td>
               <td style="text-align: center">{$result['et']}</td>
               <td style="text-align: center">{$result['ct']}</td>
               <td style="text-align: center">{$result['lap']}</td>
               <td style="text-align: center">$code_link</td>
               <td style="text-align: center">$points</td>
               <td >&nbsp;</td>
               <td >{$result['editbtn']}</td>
               <td >{$result['deletebtn']}</td>
            </tr>
EOT;
        }
    }
    return $rows;
}

function editresult_html($eventid, $entryid, $boat, $status)
{
    if ($status == "R")
    {
        $disable = "disabled";
        $data_title = "not finished - can't edit";
        $btn_style = "btn-default";
    }
    else
    {
        $disable = "";
        $data_title = "edit result for this boat";
        $btn_style = "btn-success";
    }


    $bufr = <<<EOT
        <span data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true"
              data-title="$data_title" data-placement="top">
            <a type="button" class="btn $btn_style btn-xs $disable" data-toggle="modal" data-target="#editresultModal" data-boat="$boat"
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


    // set up scoring and result code data
    $scoring_code_bufr = "";
    $resultcodes = array();
    foreach ($params['resultcodes'] as $row) {
        $resultcodes[] = array ("code" => $row['code'], "label" => "{$row['code']} : {$row['short']}");
    }
    $scoring_code_bufr = u_selectcodelist($resultcodes, $params['code']);

    // handle lap number editing - only allowed for average lap
    $params['scoring'] == "average" ? $lap_readonly = "" : $lap_readonly = "readonly";

    // set up helm field options
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
    else
    {
        $helm_bufr = <<<EOT
        <div class="form-group">
            <label class="$lbl_width control-label">helm</label>
            <div class="$fld_width inputfieldgroup">
                <input type="text" class="form-control" style="text-transform: capitalize;" id="helm" name="helm" value="{helm}" readonly />               
            </div>
            <div class="$hlp_width help-block">cannot be changed</div>
        </div>
EOT;
    }

    $etime = gmdate("H:i:s", $params['etime']);


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
          <span class="glyphicon glyphicon-flag"> </span> Result Details</a>
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
        
            <!-- class -->
            <div class="form-group">
                <label class="$lbl_width control-label">class</label>
                <div class="$fld_width inputfieldgroup">
                    <input type="text" class="form-control" style="text-transform: capitalize;" id="helm" name="helm" value="{class}" readonly />                    
                </div>
                <div class="$hlp_width help-block">cannot be changed</div>
            </div>
            
            
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
        </div>
        
        <!-- result panel --> 
        <div role="tabpanel" class="tab-pane fade in active" id="resultpanel">
                 
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
            
            <!-- Laps -->  
            <div class="form-group">
                <label class="$lbl_width control-label text-success">finish lap</label>
                <div class="$fld_width inputfieldgroup">
                    <input type="number" class="form-control" id="idlap" name="lap" value="{lap}" $lap_readonly
                    required data-fv-notempty-message="the number of laps completed is required"
                    placeholder=""
                    min="0" 
                    />
                </div>
                <div class="$hlp_width help-block">finishing lap for this boat</div>
            </div>       
            
            <!-- elapsed time -->
            <div class="form-group">
                <label class="$lbl_width control-label text-success">finish elapsed time</label>
                <div class="$fld_width inputfieldgroup">
                    <input type="text" class="form-control" id="idetime" name="etime" value="$etime"
                           placeholder="hh:mm:ss"
                           required data-fv-notempty-message="a time [hh:mm:ss] must be entered"
                           data-fv-regexp="true"
                           data-fv-regexp-regexp="^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$"
                           data-fv-regexp-message="lap time must be in HH:MM:SS format"
                    />
                </div>
                <div class="$hlp_width help-block">elapsed time for boat at the finish (hh:mm:ss)</div>
            </div> 
            
            <!-- scoring code -->
            <div class="form-group">
                <label class="control-label $lbl_width text-success">scoring code</label>
                <div class = "$fld_width">
                    <select class="form-control $fld_width" name="code" id="idcode">
                        $scoring_code_bufr
                    </select >
                </div>
                <div class="$hlp_width help-block">e.g. OCS, DNF - otherwise leave blank</div >
            </div>                   
            
            <!-- penalty score -->        
            <div class="form-group">
                <label class="control-label $lbl_width text-success">penalty score</label>
                <div class="$fld_width">
                    <input name="penalty" type="text" class="form-control" id="idpenalty" value="{penalty}" 
                    placeholder="extra points to be applied (e.g. 2.5)"
                    data-fv-regexp="true"
                    data-fv-regexp-regexp="^(\d*)\.?(\d){0,1}$"
                    data-fv-regexp-message="penalty must be in format like 2.3">
                </div>
                <div class="$hlp_width help-block">CARE - can ONLY be used when DPI code is set. </div>
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
  
  <!-- disable penalty field unless code is set to DPI (first script seems to work-->
  <!--script type=text/javascript>
      $(document).ready(function(){ $("#idpenalty").prop("disabled", $('#idcode').val() != "DPI"); });  
  </script -->
  <!--script type=text/javascript>
    document.getElementById('idcode').onchange = function () {
    if(this.value != "DPI") {
        document.getElementById("idpenalty").disabled = true;
        }

    else {
        document.getElementById("idpenalty").disabled = false;
        }
    }
  </script -->
  

  </form>
EOT;

    return $html;
}

//function fm_change_finish($params = array())
//{
//    global $tmpl_o;
//
//    $data = array(
//        "mode"       => "changefinish",
//        "instruction"=> true,
//        "footer"     => true
//    );
//
//    $fields = array(
//        "instr_content" => "<p>This can be useful in three situations ... if you have:<br>
//                            &nbsp;&nbsp;&nbsp;- forgotten to SHORTEN course and boats are showing as 'still racing' ... or<br>
//                            &nbsp;&nbsp;&nbsp;- SHORTENED course by mistake and want to reset the original finish lap ... or<br>
//                            &nbsp;&nbsp;&nbsp;- ABANDONED the race and want to take the results from a PREVIOUS completed lap ... or</p>
//        <p>Set the finish lap for each fleet to the lap you want the boats to finish on (i.e. the laps for the finish of the leading boat).</p>",
//        "footer_content" => "click the <span>Change Finish lap</span> button to set the finish lap for each fleet",
//        "reminder" => ""
//    );
//
//    foreach ($params['fleet-data'] as $i=>$fleet)
//    {
//
//        $data['fleets'][$i] = array(
//            "fleetname"  => ucwords($fleet['name']),
//            "fleetnum"   => $i,
//            "fleetlaps"  => $fleet['maxlap'],
//            "status"     => $fleet['status']
//        );
//
//        if ($fleet['status'] == "notstarted")
//        {
//            $data['fleets'][$i]['minvallaps'] = array("val"=>1, "msg"=>"cannot be less than 1 lap");;
//        }
//        else
//        {
//            $data['fleets'][$i]['minvallaps'] = array("val"=>1, "msg"=>"cannot be less than 1 lap");
//            //$data['fleets'][$i]['maxvallaps'] = array("val"=>$fleet['maxlap'], "msg"=>"cannot be more than {$fleet['maxlap']} lap(s)");;
//        }
//    }
//
//    return $tmpl_o->get_template("fm_set_laps", $fields, $data);
//
//}


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
EOT;
    return $html;
}

function process_footer($params=array())
{
    // setup language
    $lang = array(
        "0" => array(
            "step"   => "Adding wind details to event",
            "action" => "",
        ),
        "1" => array(
            "step"   => "Archiving results data",
            "action" => "Please send a message to the raceManage team - do not close the race",
        ),
        "2" => array(
            "step"   => "Creating race results file",
            "action" => "Please send a message to the raceManage team - do not close the race",
        ),
        "3" => array(
            "step"   => "Updating series results file",
            "action" => "Please send a message to the raceManage team before closing the race",
        ),
        "4" => array(
            "step"   => "Transferring files to club website",
            "action" => "Please send a message to the raceManage team before closing the race",
        ),
    );

    $problem = array_search(false, $params['complete']);

    if ($problem)
    {
        $style = "danger";
        $title = "Problem!";
        $message = "The results publishing failed at <b>Step $problem - {$lang[$problem]['step']}</b>";
        $action = "<p>{$lang["$problem"]['action']}</p>";
    }
    else
    {
        $style = "success";
        $title = "Success";
        $message = "The results publishing was completed successfully";
        $action = "";
    }

    if ($style == "success")
    {
        $close_reminder = <<<EOT
        <div class="alert alert-info" role="alert">
            <p class="lead text-center">if you are happy that the results are complete ...<p>
            <h2 class="text-center">Please CLOSE the race on the STATUS page</h2>
        </div>
EOT;
    }
    else
    {
        $close_reminder = "";
    }

    $html = <<<EOT
    <div style='padding-left:10%; width: 80%; position: absolute; top: {top}px;' >
        <div class="alert alert-$style" role="alert">
            <h4><b>$title</b></h4>
            <p>$message</p>
            $action
            <p><i><b>You can republish the results at any time before you close the race</b></i><p>
        </div>
        $close_reminder
    </div>
EOT;


    return $html;
}


function fm_publish($params = array())
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
            <p class="text-info lead">Add details on wind conditions and any notes to be included in the published results</p>
            <p class="text-info"><small>Note: 'embargoed' results will be produced but not transferred to the website</small></p>
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
                    <label class="col-xs-3 col-xs-offset-3 control-label" style="text-align: right !important;">Race Start</label>
                    <label class="col-xs-3 control-label" style="text-align: right !important;">Race End</label>
                    <hr class="col-xs-9 col-xs-offset-1" style="margin-top: 0px;">
                </div>
                <div class="row">
                    <label class="col-xs-3 col-xs-offset-1 control-label">Wind Direction</label>
                    <div class="col-xs-3 selectfieldgroup">
                        <select class="form-control" name="wd_start" style="width: 200px"> {wd_start} </select>
                    </div>
                    <div class="col-xs-3 selectfieldgroup">
                        <select class="form-control" name="wd_end" style="width: 200px"> {wd_end} </select>
                    </div>
                </div>
                <br>
                <div class = "row">
                    <label class="col-xs-3 col-xs-offset-1 control-label">Wind Speed</label>
                    <div class="col-xs-3 selectfieldgroup">
                        <select class="form-control" name="ws_start" style="width: 200px"> {ws_start} </select>
                    </div>
                    <div class="col-xs-3 selectfieldgroup">
                        <select class="form-control" name="ws_end" style="width: 200px"> {ws_end} </select>
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
                    <div class="col-xs-12">
                         <button class="btn btn-info btn-md pull-right" type="submit">
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



function fm_publish_demo($params = array())
{
    $html = <<<EOT
    <div class="container" style="margin-top: -40px;">
              
        <div class="alert alert-danger" role="alert">
            <h4>Results Publish [ DEMO SYSTEM ]</h4>
        </div>
        <div class="margin-top-05">
            <p>The DEMO version of raceManager does not publish results</p>
            <p>The LIVE version does the following for you: </p>
            <ul>
                <li>allows you to select various options for the results </li>
                <li>archives all the race data</li>
                <li>produces the race results</li>
                <li>updates any series results the race is associated with </li>
                <li>posts the results (race and series) to the club website</li>
            </ul>
            
            <p>If you try and publish the results with boats not finished or given a scoring code (e.g. DNF), 
            the system will warn you but will let you continue to publish the results - with the exception that 
            it will not post any results files to the club website</p>
            
            <p><b>IMPORTANT:</b>If you are running a race and have problems with the results that you are unable to resolve
             <b>ALWAYS publish the results</b> as this archives all the race data so that your raceManager support team can 
             investigate the problem</p>
        </div>
        <div class="row">
            <br>
            <div class="col-md-offset-10 col-md-2">
                 <!-- go back to results page -->
                 <button type="button" id="closeBtn" class="btn btn-success btn-md pull-right" onclick="window.parent.closeModal();">
                    <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>&nbsp;&nbsp;Close&nbsp;&nbsp;
                 </button>
            </div>
        </div>
    </div>
EOT;

    return $html;
}


function fm_publish_warning($params = array())
{
    /**
     * This form reports.
     *
     * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
     *
     * %%copyright%%
     * %%license%%
     *
     */

    $html = <<<EOT
    <div class="container" style="margin-top: -40px;">
              
        <div class="alert alert-danger" role="alert">
            <p class="lead">Results for this race still have unresolved warnings &hellip;</p>
            <br>
            <p class="">This is usually either because a boat has not been given a scoring code (e.g. DNF) or has not been recorded with the required no. of laps to complete the race.</p>
            <p>You can go back and correct these issues on the RESULTS page - more information on resolving the warnings can be found on the Help page - <span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span></p>
            <br>
            <hr>
            <p style="text-indent: 50px"><b>Alternatively you can publish the results without fixing the warnings</b></p>
            <p style="text-indent: 80px"> - the results will <b>not</b> be accessible from the website</p>
            <p style="text-indent: 80px"> - you will be able to view and print the results</p>
        </div>

        <div class="margin-top-05">
            <form class="form-horizontal" id="checkForm" method="post" role="search" autocomplete="off"
                  action="results_publish_pg.php?eventid={eventid}&pagestate=init&overide=1" >

                <div class="row">
                    <br>
                    <div class="col-md-offset-8 col-md-2">
                         <!-- go back to results page -->
                         <button type="button" id="closeBtn" class="btn btn-warning btn-md" onclick="window.parent.closeModal();">
                            <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>&nbsp;&nbsp;Fix Issues&nbsp;&nbsp;
                         </button>
                    </div>
                    <div class="col-md-2">    
                         <!-- continue with publishing -->
                         <button class="btn btn-primary btn-md" type="submit">
                             &nbsp;&nbsp;Publish Anyway&nbsp;&nbsp;<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                         </button>
                    </div>
                </div>

            </form>
            <script>
                  $(document).ready(function() {
                      $("[data-toggle=popover]").popover({trigger: 'hover',html: 'true'});
                  });
             </script>
        </div>
    </div>
EOT;

    return $html;

}

