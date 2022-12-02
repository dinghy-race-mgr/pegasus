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
                <input type="text" class="form-control" style="text-transform: capitalize;" id="helm" name="helm" value="{helm}"
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
                <!-- input type="text" class="form-control" style="text-transform: capitalize;" id="helm" name="helm" value="{helm}" readonly / -->               
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
                    <!-- input type="text" class="form-control" style="text-transform: capitalize;" id="helm" name="class" value="{class}" readonly / -->                    
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
            <div class="form-group">
                <label class="col-xs-{lblw} control-label text-success">finish lap</label>
                <div class="col-xs-{fldw} inputfieldgroup">
                    <input type="number" class="form-control" id="idlap" name="lap" value="{lap}"
                    required data-fv-notempty-message="the number of laps completed is required"
                    placeholder="e.g. 3"
                    min="0" 
                    />
                </div>
                <div class="col-xs-{hlpw} help-block">no.of completed laps for this boat</div>
            </div>       
 
            <!-- finish line -->
            <div class="form-group">
                <label class="col-xs-{lblw} control-label text-success">finish line number</label>
                <div class="col-xs-{fldw} inputfieldgroup">
                    <input type="number" class="form-control" id="idfinishline" name="finishline" value="{finishline}" 
                    required data-fv-notempty-message="the finish line number  is required"
                    placeholder="e.g 2"
                    min="1" 
                    />
                </div>
                <div class="col-xs-{hlpw} help-block">finishing line for this boat (where 1 is finish for leaders, 2 is next finish line, and so on)</div>
            </div> 
 
            <!-- position at finish line -->
            <div class="form-group">
                <label class="col-xs-{lblw} control-label text-success">position at finish line</label>
                <div class="col-xs-{fldw} inputfieldgroup">
                    <input type="number" class="form-control" id="idfinishpos" name="finishpos" value="{finishpos}" 
                    required data-fv-notempty-message="the finishing position is required"
                    placeholder="e.g. 4"
                    min="1" 
                    />
                </div>
                <div class="col-xs-{hlpw} help-block">finishing position at specified finish line (e.g 4 means 4th boat to finish at specified finish line)</div >
            </div>

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
