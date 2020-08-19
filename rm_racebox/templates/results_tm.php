<?php

function result_tabs($params = array(), $results)
{
    $eventid = $params['eventid'];

    $tabs = "";
    $panels = "";
    for ($i = 1; $i <= $params['num-fleets']; $i++)
    {
        $fleet_name = $_SESSION["e_$eventid"]["fl_$i"]['name'];
        empty($results['data'][$i]) ? $count = 0 : $count = count($results['data'][$i]);
        $racetype = $_SESSION["e_$eventid"]["fl_$i"]['scoring'];

        $tabs.= <<<EOT
        <li role="presentation" class="">
        &nbsp;<a href="#fleet$i" aria-controls="$fleet_name" role="tab" data-toggle="pill"> $fleet_name</a>&nbsp; </li>
EOT;
        if ($count <= 0)
        {
            $panels .= <<<EOT
            <div role="tabpanel" class="tab-pane" id="fleet$i">
                <div class="alert alert-warning" role="alert" style="margin-left: 0%; margin-right: 40%">
                   <b>no entries in the $fleet_name fleet</b><br>
                </div>
            </div>
EOT;
        }
        else
        {
            $columns  = format_columns($racetype);
            $rows     = format_rows($racetype, $results['data'][$i]);
            $warnings = format_warnings($results['warning'][$i]);
            $panels .= <<<EOT
            <div role="tabpanel" class="tab-pane" id="fleet$i">
                $warnings
                <table class="table table-striped">
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


function format_columns($racetype)
{
    if ($racetype == "level")  // pn and corrected time not required
    {
        $columns = <<<EOT
        <thead>
            <tr>
               <th width="3%"  > </th>
               <th width="13%" >class</th>
               <th width="8%"  >no.</th>
               <th width="20%" >crew</th>
               <th width="6%" style="text-align: center" >elapsed</th>
               <th width="6%" style="text-align: center" >laps</th>
               <th width="6%" style="text-align: center" >code</th>
               <th width="6%" style="text-align: center" >points</th>
               <th width="20%" > </th>
            </tr>
        </thead>
EOT;
    }
    elseif ($racetype == "pursuit") // elapsed and corrected time not required
    {
        $columns = <<<EOT
        <thead>
            <tr>
               <th width="3%"  > </th>
               <th width="13%" style="font-weight: bold" >class</th>
               <th width="8%"  style="font-weight: bold" >no.</th>
               <th width="20%" >crew</th>
               <th width="6%" style="text-align: center" >elapsed</th>
               <th width="6%" style="text-align: center" >laps</th>
               <th width="6%" style="text-align: center" >code</th>
               <th width="6%" style="text-align: center" >points</th>
               <th width="20%" > </th>
            </tr>
        </thead>
EOT;
    }
    elseif ($racetype == "handicap")
    {
        $columns = <<<EOT
        <thead>
            <tr>
               <th width="3%"  > </th>
               <th width="13%" >class</th>
               <th width="8%"  >no.</th>
               <th width="20%" >crew</th>
               <th width="6%" style="text-align: center" >PN</th>
               <th width="6%" style="text-align: center" >elapsed</th>
               <th width="6%" style="text-align: center" >corrected</th>
               <th width="6%" style="text-align: center" >laps</th>
               <th width="6%" style="text-align: center" >code</th>
               <th width="6%" style="text-align: center" >points</th>
               <th width="20%" > </th>
            </tr>
        </thead>
EOT;
    }
    else  // average lap
    {
        $columns = <<<EOT
        <thead>
            <tr>
               <th width="3%"  > </th>
               <th width="11%" >class</th>
               <th width="8%"  >no.</th>
               <th width="13%" >crew</th>
               <th width="6%" style="text-align: center" >PN</th>
               <th width="6%" style="text-align: center" >elapsed</th>
               <th width="6%" style="text-align: center" >corrected</th>
               <th width="6%" style="text-align: center" >laps</th>
               <th width="6%" style="text-align: center" >code</th>
               <th width="6%" style="text-align: center" >points</th>
               <th width="20%" > </th>
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
        <p class="text-{$warning['type']}">
           <span class="glyphicon glyphicon-alert" aria-hidden="true"></span>
           &nbsp;&nbsp;&nbsp;{$warning['msg']}
        </p>
EOT;
    }
    if (!empty($warn_bufr))
    {
        $bufr.= <<<EOT
        <div class="alert alert-warning" style="width: 40%" role="alert">
           <div class="row">
              <div class="col-md-4"><b>Warning!</b></div>
              <div class="col-md-8">$warn_bufr</div>
           </div>
        </div>
EOT;
    }

    return $bufr;
}

function format_rows($racetype, $race_results)
{
    $rows = "";
    foreach($race_results as $result)
    {
        if ($racetype == "level")  // pn and corrected time not required
        {
            $rows.= <<<EOT
            <tr>
               <td >{$result['status']}</td>
               <td style="font-weight: bold">{$result['class']}</td>
               <td style="font-weight: bold">{$result['sailnum']}</td>
               <td >{$result['competitor']}</td>
               <td style="text-align: center">{$result['et']}</td>
               <td style="text-align: center">{$result['lap']}</td>
               <td style="text-align: center">{$result['code']}</td>
               <td style="text-align: center">{$result['points']}</td>
               <td style="text-align: center">{$result['button']}</td>
            </tr>
EOT;
        }
        elseif ($racetype == "pursuit") // elapsed and corrected time not required
        {
            $rows.= <<<EOT
            <tr>
               <td >{$result['status']}</td>
               <td style="font-weight: bold">{$result['class']}</td>
               <td style="font-weight: bold">{$result['sailnum']}</td>
               <td >{$result['competitor']}</td>
               <td style="text-align: center">{$result['et']}</td>
               <td style="text-align: center">{$result['lap']}</td>
               <td style="text-align: center">{$result['code']}</td>
               <td style="text-align: center">{$result['points']}</td>
               <td style="text-align: center">{$result['button']}</td>
            </tr>
EOT;
        }
        elseif ($racetype == "handicap")
        {
            $rows.= <<<EOT
            <tr>
               <td >{$result['status']}</td>
               <td style="font-weight: bold">{$result['class']}</td>
               <td style="font-weight: bold">{$result['sailnum']}</td>
               <td >{$result['competitor']}</td>
               <td style="text-align: center">{$result['pn']}</td>
               <td style="text-align: center">{$result['et']}</td>
               <td style="text-align: center">{$result['ct']}</td>
               <td style="text-align: center">{$result['lap']}</td>
               <td style="text-align: center">{$result['code']}</td>
               <td style="text-align: center">{$result['points']}</td>
               <td style="text-align: center">{$result['button']}</td>
            </tr>
EOT;
        }
        else  // average lap
        {
            $rows.= <<<EOT
            <tr>
               <td >{$result['status']}</td>
               <td style="font-weight: bold">{$result['class']}</td>
               <td style="font-weight: bold">{$result['sailnum']}</td>
               <td >{$result['competitor']}</td>
               <td style="text-align: center">{$result['pn']}</td>
               <td style="text-align: center">{$result['et']}</td>
               <td style="text-align: center">{$result['ct']}</td>
               <td style="text-align: center">{$result['lap']}</td>
               <td style="text-align: center">{$result['code']}</td>
               <td style="text-align: center">{$result['points']}</td>
               <td style="text-align: center">{$result['button']}</td>
            </tr>
EOT;
        }
    }
    return $rows;
}


function fm_edit_result($params, $data)

{
    $labelwidth = "col-xs-3";
    $fieldwidth = "col-xs-7";

    $resultcodes = array();
    foreach($data['resultcodes'] as $row)
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

    <input name="entryid" type="hidden" id="identryid" value="">

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

function fm_change_finish($params, $data)
{
    // instructions
    $html = <<<EOT
    <div class="alert alert-warning alert-dismissable" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        This can be useful if you have forgotten to shorten course -
        but finished the boats and they are still showing on the Time Laps page.
        Just set the finish lap for each fleet to the lap you actually finished the boats on.<br>
    </div>
    <div>
            <div class="col-xs-5" style="text-align:right; color: darkred"><b>FLEET</b></div>
            <div class="col-xs-7" style="color: darkred"><b>LAPS</b></div>
    </div>
EOT;

    // create input fields - one per fleet
    $rows = "";
    for ($i=1; $i<=$params['num-fleets']; $i++)
    {
        $current = $data["fl_$i"]['maxlap'];
        if ($current>0)
        {
            $html .= <<<EOT
            <div class="form-group">
                <label class="col-xs-5 control-label">{$data["fl_$i"]['name']}</label>
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


function fm_race_message($params, $data=array())
{
    /**
     * This form allows the user to send a message to the support team.  The
     * message will be stored in the t_message table, and optionally will be emailled
     * to the local support team if the emailer function has been configured.
     *
     * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
     *
     * %%copyright%%
     * %%license%%
     *
     */
    $labelwidth = "col-xs-3";
    $fieldwidth = "col-xs-7";

// form instructions
    $html = <<<EOT
    <div class="alert alert-warning alert-dismissable" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        Use this form to let your support team know about any problems you had with the race or compiling the results <br>
    </div>

    <div class="form-group">
        <label class="$labelwidth control-label">Your Name</label>
        <div class="$fieldwidth inputfieldgroup">
            <input type="text" class="form-control" id="msgname" name="msgname" value=""
                required data-fv-notempty-message="please add your name here"
            />
        </div>
    </div>

    <div class="form-group">
        <label class="$labelwidth control-label">Your Email</label>
        <div class="$fieldwidth inputfieldgroup">
            <input type="email" class="form-control" id="email" name="email" value=""
                placeholder="enter your email if you would like a reply"
                data-fv-emailaddress-message="This does not look like a valid email address"
            />
        </div>
    </div>

    <div class="form-group">
        <label class="$labelwidth control-label">Message</label>
        <div class="$fieldwidth inputfieldgroup">
            <textarea rows=4 class="form-control" id="message" name="message"
                required data-fv-notempty-message="please describe your problem here"
                >
            </textarea>
        </div>
    </div>
EOT;

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

function process_footer($params=array(), $data)
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

    $problem = array_search(false, $data['complete']);

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


function fm_publish($params, $data)
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

?>