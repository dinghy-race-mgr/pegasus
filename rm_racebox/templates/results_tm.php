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
            <small><span class="label label-danger">
                <span class="glyphicon glyphicon-alert" aria-hidden="true"></span>
            </span></small>
EOT;
        }
        else
        {
            $tab_label = <<<EOT
            <small><span class="label label-success">
                <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
            </span></small>
EOT;
        }

        $tabs.= <<<EOT
        <li role="presentation" class="lead text-center">
              <a class="text-primary" href="#fleet$i" aria-controls="{$fleet['name']}" role="tab" data-toggle="pill">
              <b>{$fleet['name']}</b> <br> $tab_label             
              </a>
        </li>
EOT;


        // create PANELS
        if ($num_entries <= 0)
        {
            $panels .= <<<EOT
            <div role="tabpanel" class="tab-pane" id="fleet$i">
                <div class="alert alert-warning" role="alert" style="margin-left: 0%; margin-right: 40%">
                   <b>no entries in the {$fleet['name']} fleet</b><br>
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

function format_rows($racetype, $eventid, $race_results)
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

        if ($racetype == "level")  // pn and corrected time not required
        {
            $rows.= <<<EOT
            <tr class="table-data">
               <td >{$result['status']}</td>
               <td class="truncate">{$result['class']}</td>
               <td class="truncate">{$result['sailnum']}</td>
               <td class="truncate">{$result['competitor']}</td>
               <td style="text-align: center">{$result['et']}</td>
               <td style="text-align: center">{$result['lap']}</td>
               <td style="text-align: center">{$result['code']}</td>
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
               <td >{$result['status']}</td>
               <td class="truncate">{$result['class']}</td>
               <td class="">{$result['sailnum']}</td>
               <td class="truncate">{$result['competitor']}</td>
               <td style="text-align: center">{$result['et']}</td>
               <td style="text-align: center">{$result['lap']}</td>
               <td style="text-align: center">{$result['code']}</td>
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
               <td >{$result['status']}</td>
               <td class="truncate">{$result['class']}</td>
               <td class="">{$result['sailnum']}</td>
               <td class="truncate">{$result['competitor']}</td>
               <td style="text-align: center">{$result['pn']}</td>
               <td style="text-align: center">{$result['et']}</td>
               <td style="text-align: center">{$result['ct']}</td>
               <td style="text-align: center">{$result['lap']}</td>
               <td style="text-align: center">{$result['code']}</td>
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
               <td >{$result['status']}</td>
               <td class="truncate">{$result['class']}</td>
               <td class="">{$result['sailnum']}</td>
               <td class="truncate">{$result['competitor']}</td>
               <td style="text-align: center">{$result['pn']}</td>
               <td style="text-align: center">{$result['et']}</td>
               <td style="text-align: center">{$result['ct']}</td>
               <td style="text-align: center">{$result['lap']}</td>
               <td style="text-align: center">{$result['code']}</td>
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
              data-title="edit lap times for this boat" data-placement="top">
            <a type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#editlapModal" data-boat="$boat"
                    data-iframe="result_edit_pg.php?eventid=$eventid&pagestate=init&entryid=$entryid" >
                    <span class="glyphicon glyphicon-pencil"></span>
            </a>
        </span>
EOT;

    return $bufr;
}

function fm_edit_result($params)

{
    $labelwidth = "col-xs-3";
    $fieldwidth = "col-xs-7";

    $resultcodes = array();
    foreach($params['resultcodes'] as $row)
    {
        $resultcodes["{$row['code']}"] = "{$row['code']} : {$row['short']}";
    }
    $code_options = u_selectlist($resultcodes, "");


    $helm = "";
    if ($params['allocation'] == "boat")  // if series point to boats - then allow helm name to be edited
    {
        $helm = <<<EOT
        <div class="form-group">
            <label class="$labelwidth control-label">Helm</label>
            <div class="$fieldwidth inputfieldgroup">
                <input type="text" class="form-control" id="idhelm" name="helm" value=""
                    required data-fv-notempty-message="helm must be entered"
                />
            </div>
        </div>
EOT;
    }

    $html = <<<EOT
    <div class="alert alert-warning alert-dismissable" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        This form can be used to edit information about the competitor or their result.
        If you want to edit the individual lap times use the lap times icon<br>
    </div>

    <input name="entryid" type="hidden" id="identryid" value="{entryid}">

    $helm

    <div class="form-group">
        <label class="$labelwidth control-label">Crew</label>
        <div class="$fieldwidth inputfieldgroup">
            <input type="text" class="form-control" id="idcrew" name="crew" value=""
            placeholder="only necessary for double hander"
            />
        </div>
    </div>

    <div class="form-group">
        <label class="$labelwidth control-label">Sail No.</label>
        <div class="$fieldwidth inputfieldgroup">
            <input type="text" class="form-control" id="idsailnum" name="sailnum" value=""
            />
        </div>
    </div>

    <div class="form-group">
        <label class="$labelwidth control-label">PN</label>
        <div class="$fieldwidth inputfieldgroup">
            <input type="text" class="form-control" id="idpn" name="pn" value=""
            />
        </div>
    </div>

    <div class="form-group">
        <label class="$labelwidth control-label">laps</label>
        <div class="$fieldwidth inputfieldgroup">
            <input type="text" class="form-control" id="idlap" name="lap" value=""
            />
        </div>
    </div>

    <div class="form-group">
        <label class="$labelwidth control-label">elapsed time</label>
        <div class="$fieldwidth inputfieldgroup">
            <input type="text" class="form-control" id="idetime" name="etime" value=""
                   required data-fv-notempty-message="a time [hh:mm:ss] must be entered"
                   data-fv-regexp="true"
                   data-fv-regexp-regexp="^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$"
                   data-fv-regexp-message="lap time must be in HH:MM:SS format"
            />
        </div>
    </div>

    <div class="form-group">
        <label class="control-label $labelwidth">result code (e.g. DNF)></label>
        <div class="$fieldwidth inputfieldgroup">
            <select class="form-control" name="code" id="idcode" value="" >
                <option value="">&nbsp;</option>";
                $code_options
            </select>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label $labelwidth">penalty</label>
        <div class="$fieldwidth">
            <input name="penalty" type="text" class="form-control" id="idpenalty" value=""
            placeholder="additional penalty points to be applied to position">
        </div>
    </div>

    <div class="form-group">
        <label class="control-label $labelwidth">notes</label>
        <div class="$fieldwidth">
            <input name="note" type="text" class="form-control" id="idnote" value="X"
            placeholder="any notes you want to add for this result">
        </div>
    </div>

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
         - had to abandon the race and want to take the results from a previously completed lap</p>
        <p>Set the finish lap for each fleet to the lap you want the boats to finish on.</p>
    </div>

    <div class="row text-info">
            <div class="col-xs-5" style="text-align:right;"><b>FLEET</b></div>
            <div class="col-xs-7" ><b>FINISH LAP</b></div>
    </div>
EOT;

    // create input fields - one per fleet
    $rows = "";
    for ($i=1; $i<=$params['rc_numfleets']; $i++)
    {
        $current = $params["fl_$i"]['maxlap'];
        if ($current>0)
        {
            $html .= <<<EOT
            <div class="form-group">
                <label class="col-xs-5 control-label">{$params["fl_$i"]['name']}</label>
                <div class="col-xs-2 inputfieldgroup">
                <input type="text" class="form-control" name="finishlap[$i]" min="1" max="$current" value="$current"
                    required data-fv-between-message="must be a value between 1 and $current"
                />
                </div>
            </div>
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
            <link   rel="stylesheet"       href="{loc}/common/oss/bootstrap/css/bootstrap.min.css" >
            <link   rel="stylesheet"       href="{loc}/common/oss/bootstrap/css/bootstrap-theme.min.css">
            <script type="text/javascript" src="{loc}/common/oss/jquery/jquery.min.js"></script>
            <script type="text/javascript" src="{loc}/common/oss/bootstrap/js/bootstrap.min.js"></script>
            <script type="text/javascript" src="{loc}/common/oss/bs-growl/jquery.bootstrap-growl.min.js"></script>

            <!-- Custom styles for this template -->
            <link href="{stylesheet}" rel="stylesheet">

    </head>
    <body>
    <h4 style="color: darkorange; margin-top: 5px !important; margin-bottom: 5px !important;">Results publishing ...</h4>
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
        <div class="alert alert-warning alert-dismissable" role="alert">
           <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
           <b>Add details on the WIND during the race and any NOTES to be included in the published results</b>
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
                    <label class="col-xs-3 col-xs-offset-3 control-label text-danger">Race Start</label>
                    <label class="col-xs-3 control-label text-danger">Race End</label>
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
                    <label class="col-xs-3 col-xs-offset-1 control-label">&nbsp;</label>
                    <div class="col-xs-7 checkbox">
                        <label>
                          <input type="checkbox" id="include_club" name="include_club"> Include competitor club names in results
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

