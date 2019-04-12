<?php

/**
 * oickrace_tm.php
 *
 * @abstract Custom templates for the pickrace page
 *
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 *
 * %%copyright%%
 * %%license%%
 *
 * templates:
 *    race_panel
 *    race_format
 *    no_races
 *    fm_addrace
 */

function race_panel($field=array())
{
    empty($field['oodname']) ? "not listed" : $field['oodname'];
    $html = <<<EOT
    <div class="row">
        <div class="col-xs-10 col-xs-offset-1 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1">
            <div class="panel {style} ">
                <div class="panel-heading">
                    <h3 ><b>{eventname}</b><span class="pull-right">&hellip; {label}</span></h3>
                </div>
                <div class="panel-body" >
                    <div class="row">

                        <div class="col-sm-3 col-md-3 {'text'}">
                            <p class="big-text"><small>race officer: &nbsp;</small><br><b>{oodname}</b></p>
                        </div>

                        <div class="col-sm-3 col-md-3 {'text'}">
                            <p class="big-text"><small>start time: &nbsp;</small><br><b>{starttime} {tidetime}</b></p>
                        </div>

                        <div class="col-sm-3 col-md-3 {'text'}" >
                            {viewbufr}
                        </div>

                        <div class="col-sm-3 col-md-3" {bpopup}>
                            <a href="{blink}" class="btn btn-block {bstyle} big-text" target="_SELF">{blabel}</a>
                        </div>
                    </div> <!-- row -->
                </div> <!-- panel body -->
            </div> <!-- panel -->
        </div>
    </div>
EOT;
    return $html;
}


function race_format($param=array())
{
    $html = <<<EOT

         <p class="big-text">
            <small>race format: &nbsp;</small><br>
            <b>{raceformat}&nbsp;&nbsp;&nbsp;</b>
         </p>
         <div data-toggle="popover" data-content="{popover}" data-placement="top">
            <a role="button" href="" data-toggle="modal" data-target="#format{eventid}Modal">
                <span class="glyphicon glyphicon-list-alt" style="font-size: 1.5em;"></span>
            </a>
         </div>
EOT;

    return $html;
}

function no_races($param=array())
{
    $html = <<<EOT
    <div class="container">
        <div class="row">
        <div class="col-md-9 col-md-offset-1">
        <div class="jumbotron margin-top-40">
            <div class="row">
                <div class="col-md-9" >
                    <h2>{msg1}</h2>
                    <h4>{msg2}</h4>
                </div>
                <div class="col-md-3" >
                    {support_team}
                </div>
            </div>
        </div>
        </div>
        </div>
    </div>
EOT;
    return $html;
}

function fm_addrace($params=array())
{
    global $db_o;
    global $event_o;
    isset($params['label-width']) ? $labelwidth = "col-xs-{$params['label-width']}" : $labelwidth= "col-xs-3" ;
    isset($params['field-width']) ? $fieldwidth = "col-xs-{$params['field-width']}" : $fieldwidth= "col-xs-7" ;

    $today = date("Y-m-d");

    $event_formats = $event_o->event_geteventformats(true);
    $event_format_select = u_selectlist($event_formats);

    $entry_types = $db_o->db_getsystemcodes("entry_type");
    $entry_type_select = u_selectcodelist($entry_types, "");

    $series_list = $event_o->event_getseriescodes();
    $series_select = u_selectlist($series_list);

    // form instructions
    $html = <<<EOT
    <!-- form instructions -->
    <div class="alert alert-danger alert-dismissable" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <b>Care</b> - make sure you are not creating a race that is already in the programme
    </div>

    <!-- field 1 event name -->
    <div class="form-group">
        <label class="$labelwidth control-label">event name</label>
        <div class="$fieldwidth inputfieldgroup">
            <input type="text" class="form-control" name="eventname" id="eventname" value=""
                placeholder="e.g. Summer Series or Champions Trophy"
                required data-fv-notempty-message="this information is required"
            />
        </div>
    </div>

    <!-- event name -->
    <input type="hidden" name="eventdate" id="eventdate" value="$today">

    <!--  field 2 start time -->
    <div class="form-group">
        <label class="$labelwidth control-label">start time</label>
        <div class="$fieldwidth inputfieldgroup">
            <input type="text" class="form-control" name="starttime" id="starttime" value=""
                placeholder="HH:MM (e.g. 10:30)"
                required data-fv-notempty-message="this information is required"
                data-fv-regexp="true"
                data-fv-regexp-regexp="^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$"
                data-fv-regexp-message="start time must be in HH:MM format"
            />
        </div>
    </div>

    <!--  field 3 event format -->
    <div class="form-group">
        <label class="$labelwidth control-label">event format</label>
        <div class="$fieldwidth selectfieldgroup">
            <select class="form-control" name="eventformat" aria-describedby="helpBlock1"
                required data-fv-notempty-message="choose one of these options">
                <option value="">&hellip; pick one </option>
                $event_format_select
            </select>
            <span id="helpBlock1" class="help-block">This is the format of the race you are creating.</span>
        </div>
    </div>

    <!--  field 4 event entry -->
    <div class="form-group">
        <label class="$labelwidth control-label">sailor entry</label>
        <div class="$fieldwidth selectfieldgroup">
            <select class="form-control" name="evententry" aria-describedby="helpBlock2"
                    required data-fv-notempty-message="choose one of these options">
                    <option value="" selected>&hellip; pick one </option>
            $entry_type_select
            </select>
            <span id="helpBlock2" class="help-block">
                This determines how a competitor enters (and signs off) from the race.
            </span>
        </div>
    </div>

    <!--  field 5 series -->
    <div class="form-group">
        <label class="$labelwidth control-label">series name</label>
        <div class="$fieldwidth selectfieldgroup">
            <select class="form-control" name="seriesname" aria-describedby="helpBlock3" >
                <option value="">&hellip; pick one </option>
                $series_select
            </select>
            <span id="helpBlock3" class="help-block">
                If not part of a series or series not shown - just leave blank
            </span>
        </div>
    </div>

    <!--  field 6 OOD -->
    <div class="form-group">
        <label class="$labelwidth control-label">OOD for race</label>
        <div class="$fieldwidth inputfieldgroup">
            <input type="text" class="form-control" name="oodname" id="oodname" value=""
                placeholder="name of OOD (e.g. Ben Ainslie)"
                required data-fv-notempty-message="this information is required"
            />
        </div>
    </div>
EOT;

    return $html;
}

?>