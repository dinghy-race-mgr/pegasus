<?php


function signon($params = array())
{
    $bufr = "";
    $event_bufr = "";
    $confirm_bufr = "";
    $instruction_bufr = "";

    if ($params['state'] == "noevents") { $event_bufr.= $params['event-list]'];}

    elseif ($params['state'] == "submitentry")
    {
        $event_list = "";

//        $event_list = "";
//        $eligible = false;
//        $i = 0;
//        foreach ($params['entries'] as $eventid => $row)
//        {
//            $i++;
//            $checked = "";
//            if ($_SESSION['sailor_entry'] == "all" OR ($_SESSION['sailor_entry'] == "first" AND $i == 1))
//            {
//                $checked = "checked";
//            }
//
//            if ( $row['allocate']['status'] )
//            {
//                $eligible = true;   // eligible for at least one race today
//                $event_list.=<<<EOT
//                 <tr>
//                    <td><h4>{$row['event-name']}</h4></td>
//                    <td><h4>{$row['start-time']}</h4></td>
//                    <td class="text-center" style="width:20%" >
//                        <h4><input type="checkbox" $checked name="race{$eventid}"></h4>
//                        <input type="hidden" name="start{$eventid}" value="{$row['allocate']['start']}">
//                        <input type="hidden" name="status{$eventid}" value="{$row['allocate']['status']}">
//                        <input type="hidden" name="reason{$eventid}" value="">
//                    </td>
//                 </tr>
//EOT;
//            }
//            else
//            {
//                $reason = array("tt" => "unknown problem", "label" =>"unknown problem");
//                if ($row['allocate']['alloc_code'] == "E")
//                {
//                    $reason = array("tt" => "class or competitor not eligible for this race", "label" =>"not eligible");
//                }
//                elseif ($row['allocate']['alloc_code'] == "X")
//                {
//                    $reason = array("tt" => "no class details found - not able to establish eligibility ", "label" =>"class info missing");
//                }
//
//                $event_list.= <<<EOT
//                    <tr>
//                        <td><h4>{$row['event-name']}</h4></td>
//                        <td colspan="2" title="{$reason['tt']}">
//                            <h4>
//                                <span class="badge progress-bar-info" style="font-size: 100%" >
//                                    <span class="glyphicon glyphicon-remove"></span>
//                                    not eligible
//                                </span>
//                            </h4>
//                        </td>
//                        <input type="hidden" name="race{$row['event-id']}" value="off">
//                        <input type="hidden" name="status{$row['event-id']}" value="false">
//                        <input type="hidden" name="reason{$row['event-id']}" value="{$reason['label']}">
//                    </tr>
//EOT;
//            }
//        }
        $sign_on_possible = false;
        foreach ($params['event-list'] as $eventid => $row)
        {
            if (empty($row['entry-status']) OR $row['entry-status'] == "entered")
            {
                $sign_on_possible = true;
            }

            if (empty($row['entry-status']))
            {
                $entry_col = <<<EOT
                    <input type="checkbox" name="entry{$eventid}">
EOT;
            }
            else
            {
                if ($row['entry-status'] == "entered" OR
                    $row['entry-status'] == "retired" OR
                    $row['entry-status'] == "signed off")
                {
                    $glyph = "<span class=\"glyphicon glyphicon-ok\"  aria-hidden=\"true\"></span>";
                }
                elseif ($row['entry-status'] == "not eligible" OR
                    $row['entry-status'] == "class not recognised")
                {
                    $glyph = "<span class=\"glyphicon glyphicon-remove\"  aria-hidden=\"true\"></span>";
                }
                else
                {
                    $glyph = "";
                }

                $entry_col = <<<EOT
                    {$row['entry-status']}&nbsp;&nbsp; $glyph
EOT;
                if ($row['entry-status'] == "entered")
                {
                    $entry_col.= <<<EOT
                       <br>[start {$row['start']}]
                       <input type="hidden" name="entry{$eventid}" value="on">
EOT;
                }
            }

            $race_col = "";
            if ($row['event-status']!="scheduled" AND $row['event-status']!="selected")
            {
                $race_col = <<<EOT
                    race {$row['event-status']}
EOT;
            }

            $event_list.=<<<EOT
                 <tr >
                    <td class="rm-text-md rm-table-event-list">{$row['name'] }</td>
                    <td class="rm-text-md rm-table-event-list">{$row['time']}</td>
                    <td class="rm-text-md rm-table-event-list text-center" style="width:30%;"> $entry_col </td>  
                    <td class="rm-text-md rm-table-event-list">$race_col</td>                  
                 </tr>
EOT;
        }


        if ($sign_on_possible)  // eligible for at least one race
        {
            $instruction_bufr = <<<EOT
            <div class="rm-text-space"> 
                <span class="rm-text-bg"><b>Tick the box to enter a race &hellip;</b></span>
            </div>
EOT;

            $confirm_bufr = <<<EOT
                <!-- confirm button -->
                <div class="row margin-top-10">
                    <div class="col-md-6 col-md-offset-3">
                        <button type="submit" class="btn btn-warning btn-block btn-lg" >
                            <span class="glyphicon glyphicon-ok"></span>
                            &nbsp;&nbsp;<strong>Confirm Sign On</strong>
                        </button>
                    </div>
                </div>
EOT;
        }
        else
        {
            $confirm_bufr = <<<EOT
                 <div class="alert alert-danger rm-text-md" role="alert"> 
                      <b>No new entries possible</b><br> 
                      ... please contact the race officer if you think this is incorrect
                 </div>
EOT;
        }

        $event_bufr.= <<<EOT
            <form id="confirmform" action="signon_sc.php" method="post" role="submit" autocomplete="off">        
                <table class="table table-condensed"> 
                    $event_list
                </table>
                $confirm_bufr
            </form>
EOT;
    }

    $bufr.= <<<EOT
     <div class="row">
        <div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">   
        {boat-label}
        </div>
     </div>
     
     <div class="row margin-top-10">
        <div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">   
            $instruction_bufr        
            $event_bufr
        </div>
     </div>
EOT;

    return $bufr;
}


function signon_race_confirm($params = array())
{
    ($params['status'] == "update" or $params['status'] == "enter") ? $glyph = "glyphicon-ok" : $glyph = "glyphicon-remove";

    $bufr = <<<EOT
        <tr>
            <td><h4>{name}</h4></td>
            <td><h4>{start-time}</h4></td>
            <td><h4>{text}</h4></td>
            <td><span class="glyphicon $glyph rm-glyph-bg"  aria-hidden="true"></span></td>
        </tr>
EOT;
    return $bufr;
}


function signon_confirm($params=array())
{
    $bufr = "";
    if ($params['status'])
    {
        $confirm_msg = <<<EOT
        <div class="row margin-top-10">
           <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
               <div class="alert alert-success rm-text-md" role="alert"> All done . . . have a good race </div>
           </div>
        </div>
EOT;

    }
    else
        $confirm_msg = <<<EOT
        <div class="row margin-top-10">
           <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
               <div class="alert alert-danger rm-text-md" role="alert"> There was a problem with your race entry<br> . . . please contact the race officer </div>
           </div>
        </div>

EOT;

    // render the page
    $bufr.=<<<EOT
     <!-- boat details -->
     <div class="row">
        <div class="col-xs-12 col-xs-offset-0 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
            {boat-label}
        </div>
     </div>

     <!-- events -->
     <div class="row margin-top-10">
          <div class="col-xs-12 col-xs-offset-0 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">        
            <table class="table table-condensed"> 
                {event-list}
            </table>
          </div>
     </div>

     <!-- confirm message -->
     $confirm_msg
EOT;

    return $bufr;
}



function change_fm($params = array())
{
    $lbl_width  = "col-xs-3";
    $fld_width  = "col-xs-7";
    $fld_narrow = "col-xs-3";

    // deal with helm if points accumulated by boat
    $helm_bufr = "";
    if ($params['points_allocation'] == "boat")
    {
    $helm_bufr.= <<<EOT
    <div class="form-group form-condensed">
        <label for="helm" class="rm-form-label control-label $lbl_width">Helm</label>
        <div class="$fld_width">
            <input name="helm" autocomplete="off" type="text" class="form-control input-lg rm-form-field" id="idhelm" value="{helm}">
        </div>
    </div>
EOT;
    }

    // deal with singlehanders
    $crew_bufr = "";
    if (!$params['singlehander'])
    {
    $crew_bufr.= <<<EOT
    <div class="form-group form-condensed">
        <label for="crew" class="rm-form-label control-label $lbl_width">Crew</label>
        <div class="$fld_width">
            <input name="crew" autocomplete="off" type="text" class="form-control input-lg rm-form-field" id="idcrew" value="{crew}" >
        </div>
    </div>
EOT;
    }

    $bufr = <<<EOT
    <div class="rm-form-style">
    
        <div class="row">     
            <div class="col-xs-10 col-sm-10 col-md-8 col-lg-8 alert alert-info"  role="alert">Change details as necessary...</div>
        </div>
    
        <form id="editboatForm" class="form-horizontal" action="change_sc.php" method="post">
            <div class=""><input name="compid" type="hidden" id="idcomp" value="{compid}"></div>
    
            $helm_bufr
    
            $crew_bufr
    
            <div class="form-group form-condensed">
                <label for="sailnum" class="rm-form-label control-label $lbl_width">Sail No.</label>
                <div class="$fld_narrow">
                    <input name="sailnum" autocomplete="off" type="text" class="form-control input-lg rm-form-field" id="idsailnum" value="{sailnum}" >
                </div>
            </div>
    
    
            <div class="row margin-top-20">
                <div class = "col-xs-10 col-xs-offset-3 col-sm-10  col-sm-offset-3 col-md-8  col-md-offset-2 col-lg-8 col-lg-offset-3">
                    <label class="radio-inline">
                        <input type="radio" name="scope" class="rm-form-label" value="temp" checked>
                        &nbsp;Just for today &nbsp;&nbsp;&nbsp;
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scope" class="rm-form-label" value="perm">
                        &nbsp;Today and all future races
                    </label>
                </div>
            </div>
    
            <div class="pull-right margin-top-20">
                <button type="button" class="btn btn-default btn-lg" onclick="history.go(-1);">
                    <span class="glyphicon glyphicon-remove"></span>&nbsp;Cancel
                </button>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <button type="submit" class="btn btn-warning btn-lg" >
                    <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;<b>Change Details</b>
                </button>
            </div>
    
        </form>
    </div>
EOT;
    return $bufr;
}

?>