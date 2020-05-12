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

    if ($params['state'] == "noevents") {
        $event_bufr .= $params['event-list]'];

    } elseif ($params['state'] == "submitentry") {
        $event_list = "";
        foreach ($params['event-list'] as $eventid => $row) {   // loop over events
            // race identity
            $race_txt = $row['time'] . " - " . $row['name'];

            if ($row['event-status'] == "cancelled") {                // race is cancelled
                $event_list .= <<<EOT
                <tr style="height: 8em !important;">
                <td width="25%" class="rm-text-sm rm-text-trunc">$race_txt</td>
                <td width="66%" class="rm-text-md text-warning" colspan="7">this race is CANCELLED</td>                     
                </tr>
EOT;
            } elseif ($row['event-status'] == "abandoned") {          // race is abandoned

                // protest option
                $protest_btn = protest_btn($_SESSION['sailor_protest'], $eventid, $row['event-status-code'], $row['entry-status']);

                $event_list .= <<<EOT
                <tr style="height: 8em !important;">
                <td width="25%" class="rm-text-sm rm-text-trunc">$race_txt</td>
                <td width="56%" class="rm-text-md text-warning" colspan="5">this race is ABANDONED</td>   
                <td width="5%" class="rm-text-md ">$protest_btn</td>                   
                </tr>
EOT;
            } else {                                                   // race is happening
                // signon button
                $signon_btn = signon_btn($eventid, $row['event-status-code'], $row['entry-status']);

                // declare (signoff) button
                $declare_btn = declare_btn($row['signon'], $eventid, $row['event-status-code'], $row['entry-status']);

                // retire
                $retire_btn = retire_btn($row['signon'], $eventid, $row['event-status-code'], $row['entry-status']);

                // entry status
                $entry_status_txt = entry_status_txt($row['entry-status'], $row['entry-alert'], $row['start']);

                // event status
                $event_status_txt = "race: " . $row['event-status-txt'];

                // results option
                $results_btn = results_btn($_SESSION['sailor_results'], $eventid, $row['event-status-code'], $row['entry-status']);

                // protest option
                $protest_btn = protest_btn($_SESSION['sailor_protest'], $eventid, $row['event-status-code'], $row['entry-status']);

                $event_list .= <<<EOT
                <tr style="height: 8em !important;">
                <td width="25%" class="rm-text-sm rm-text-trunc" >$race_txt</td>
                <td width="30%" class="rm-text-md">$signon_btn &nbsp; $declare_btn &nbsp; $retire_btn</td>
                <td width="20%" class="rm-text-sm text-success">$entry_status_txt
                    <br><span class="text-warning">$event_status_txt</span></td>   
                <td width="5%" class="rm-text-md ">$results_btn</td>   
                <td width="5%" class="rm-text-md ">$protest_btn</td>                     
                </tr>
EOT;
            }
        }

        $event_bufr .= <<<EOT
            <form id="confirmform" action="race_sc.php" method="post" role="submit" autocomplete="off">        
                <table class="table" width="100%" style="table-layout: fixed"> 
                    $event_list
                </table>
            </form>
EOT;
        $bufr.= <<<EOT
            <div class="row">
                <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">   
                    {boat-label}
                </div>
            </div>
            
            <div class="row margin-top-10">
                <div class="col-xs-10 col-xs-offset-1 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-10 col-lg-offset-1">   
                    $instruction_bufr        
                    $event_bufr
                </div>
            </div>
            
            <div class="margin-top-40">
                <a href="boatsearch_pg.php" class="btn btn-info btn-md rm-text-bg pull-right" role="button" >
                    <span class="glyphicon glyphicon-step-backward" aria-hidden="true"></span> &nbsp;Done ...
                </a>
            </div>
EOT;

    } else {
        // deal with unknown state - error   FIXME go to standard error page with a restart button
        $bufr .= "error";
    }

    return $bufr;
}


function entry_status_txt($en_state, $en_alert, $start)
{
    if (empty($en_alert)) {
        $txt = $en_state;
        if ($en_state == "entered") {
            $txt .= " - start " . $start;
        } elseif ($en_state == "updated") {
            $txt .= " - start " . $start;
        }
    } else {
        $txt = $en_alert;
    }

    return $txt;
}


function signon_btn($eventid, $ev_state, $en_state)
{
    if ($ev_state <= 2)    // race not started
    {
        if (empty($en_state))  // not entered yet - green button
        {
            $mode = "btn-success";
            $label = "Sign On";
        } elseif ($en_state == "entered" or $en_state == "updated") {    // update entry
            $mode = "btn-info";
            $label = "Update";
        }
        else {
            $mode = "btn-default disabled";
            $label = "Sign On";
        }
    } else {                     // otherwise not allowed to enter - disabled button
        $mode = "btn-default disabled";
        $label = "Sign On";
    }

    $bufr = <<<EOT
        <a href="race_sc.php?opt=signon&event=$eventid" type='button' class='rm-text-xs btn btn-sm $mode' >
            $label
        </a>
EOT;

    return $bufr;
}


function declare_btn($signon, $eventid, $ev_state, $en_state)
{
    $mode = "";
    if ($signon == "signon-declare") {
        if (empty($en_state) or $ev_state < 3) {   // button disabled if not entered or race not started
            $mode = "btn-default disabled";
        } elseif ($ev_state >= 3 and $ev_state <= 5 and ($en_state == "entered" or $en_state == "updated" or $en_state == "retired")) {
            $mode = "btn-warning";
        } elseif ($en_state == "signed off") {
            $mode = "btn-default disabled";
        }
    }

    if (empty($mode)) {
        $bufr = "";
    } else {
        $bufr = <<<EOT
            <a href="race_sc.php?opt=declare&event=$eventid" type='button' class='rm-text-xs btn btn-sm $mode' >Sign Off</a>
EOT;
    }

    return $bufr;
}


function retire_btn($signon, $eventid, $ev_state, $en_state)
{
    $mode = "";
    $action = "retire";

    if ($signon == "signon-declare" or $signon == "signon-retire") {
        if (empty($en_state)) {
            $mode = "btn-default disabled";
        } elseif ($ev_state < 5 and ($en_state == "entered" or $en_state == "updated"
                                     or $en_state == "signed off" or $en_state == "declared")) {
            $mode = "btn-danger";
        } else {
            $mode = "btn-default disabled";
        }
    }

    if (empty($mode)) {
        $bufr = "";
    } else {
        $bufr = <<<EOT
        <a href="race_sc.php?opt=$action&event=$eventid" type='button' class='rm-text-xs btn btn-sm $mode'>&nbsp;Retire&nbsp;</a>
EOT;
    }

    return $bufr;
}


function results_btn($opt, $eventid, $ev_state, $en_state)
{
    $mode = "";
    if ($opt) {
        if ($ev_state >= 4 and $ev_state <= 5 and ($en_state == "retired" or $en_state == "signed off"
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
        <a href="results_pg.php?event=$eventid&mode=list" type='button' title="results" class="btn btn-md $mode">
        <span class='rm-text-md glyphicon glyphicon-list-alt'  aria-hidden='true'></span></a>           
EOT;
    }

    return $bufr;
}

function protest_btn($opt, $eventid, $ev_state, $en_state)
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
        <a href="protest_pg.php?event=$eventid" type='button' title="protest" class="btn btn-md $mode">
        <span class='rm-text-md glyphicon glyphicon-pencil'  aria-hidden='true'></span></a>
EOT;
    }

    return $bufr;
}

// FIXME is this required
//function change_fm($params = array())
//{
//    $label_colour = "text-info";
//    $label_width  = "col-xs-2";
//
//    $formbufr = "";
//    foreach ($params['change'] as $field => $fieldspec)
//    {
//        if (!$fieldspec['status']) { // this field is not configured
//            continue;
//        }
//
//        if (key_exists("evtype", $fieldspec)){ // field is configured but is not relevant to events today
//            if (strpos($params['evtypes'], $fieldspec['evtype']) === false) {
//                continue;
//            }
//        }
//
//        $placeholder = "";
//        if (array_key_exists("placeholder", $fieldspec)) {
//            $placeholder = "placeholder=\"{$fieldspec['placeholder']}\"";
//        }
//        $value = "{".$field."}";
//
//        $formbufr.= <<<EOT
//        <div class="form-group form-condensed">
//            <label for="$field" class="rm-form-label control-label $label_width $label_colour">{$fieldspec['label']}</label>
//            <div class="{$fieldspec['width']}">
//                <input name="$field" autocomplete="off" type="text" class="form-control input-lg rm-form-field"
//                 id="id$field" $placeholder value="$value">
//            </div>
//        </div>
//EOT;
//    }
//
//    $bufr = <<<EOT
//    <div class="rm-form-style">
//
//        <div class="row">
//            <div class="col-xs-10 col-sm-10 col-md-8 col-lg-8 alert alert-info"  role="alert">Change details for today ...</div>
//        </div>
//
//        <form id="changeboatForm" class="form-horizontal" action="change_sc.php" method="post">
//
//            $formbufr
//            <input name="compid" type="hidden" value="{compid}">
//
//            <div class="pull-right margin-top-20">
//                <button type="button" class="btn btn-default btn-lg" style="margin-right: 10px" onclick="history.go(-1);">
//                    <span class="glyphicon glyphicon-remove"></span>&nbsp;Cancel
//                </button>
//                &nbsp;&nbsp;&nbsp;&nbsp;
//                <button type="submit" class="btn btn-warning btn-lg" >
//                    <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;<b>Change Details</b>
//                </button>
//            </div>
//
//        </form>
//    </div>
//EOT;
//    return $bufr;
//}

