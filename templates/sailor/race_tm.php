<?php


function signon($params = array())
{
    $bufr = "";
    $event_bufr = "";
    $instruction_bufr = "";

    if ($params['state'] == "noevents") { $event_bufr.= $params['event-list]']; }

    elseif ($params['state'] == "submitentry")
    {
        $event_list = "";

        // loop over events
        foreach ($params['event-list'] as $eventid => $row)
        {
//            echo "<pre>
//                event name: {$row['name']} {$row['time']} $eventid
//                signon type: {$row['signon']}
//                entry status: {$row['entry-status']}
//                event status: {$row['event-status']}
//                </pre>";

            // race identity
            $col1 = $row['time']." - ".$row['name'];                    // race identity

            // signon button
            if (empty($row['entry-status']))
            {
                $col2 = <<<EOT
                <a href="race_sc.php?opt=signon&event=$eventid" type='button' class='rm-text-xs btn btn-success btn-sm'>Sign On</a>
EOT;
            }
            else
            {
                if ($row['entry-status'] == "entered" OR $row['entry-status'] == "signed off" OR $row['entry-status'] == "retired")
                {
                    $col2 = <<<EOT
                <a href="race_sc.php?opt=signon&event=$eventid" type='button' class='rm-text-xs btn btn-default btn-sm disabled'>Sign On</a>
EOT;
                }
                else
                {
                    $col2 = <<<EOT
                <a href="race_sc.php?opt=signon&event=$eventid" type='button' class='rm-text-xs btn btn-default btn-md disabled' >Sign On</a>
                <!-- span class='glyphicon glyphicon-remove'  aria-hidden='true'></span -->
EOT;
                }
             }

            // signoff button
            if (empty($row['entry-status']) )
            {
                $col3 = <<<EOT
                <a href="race_sc.php?opt=declare&event=$eventid" type='button' class='rm-text-xs btn btn-warning btn-sm disabled' 
                >Sign Off</a>
EOT;
            }
            elseif ($row['entry-status'] == "entered")
            {
                $col3 = <<<EOT
                <a href="race_sc.php?opt=declare&event=$eventid" type='button' class='rm-text-xs btn btn-warning btn-sm' 
                >Sign Off</a>
EOT;
            }
            elseif ($row['entry-status'] == "signed off")
            {
                $col3 = <<<EOT
                <a href="race_sc.php?opt=declare&event=$eventid" type='button' class='rm-text-xs btn btn-default btn-sm disabled' 
                ><span class='glyphicon glyphicon-ok'  aria-hidden='true'></span></a>
EOT;
            }
            else
            {
                $col3 = <<<EOT
                <a href="race_sc.php?opt=declare&event=$eventid" type='button' class='rm-text-xs btn btn-default btn-sm disabled' 
                ><span class='glyphicon glyphicon-remove'  aria-hidden='true'></span></a>
EOT;
            }

            // retire
            if (empty($row['entry-status']) )
            {
                $col4 = <<<EOT
                <a href="race_sc.php?opt=retire&event=$eventid" type='button' class='rm-text-xs btn btn-danger btn-sm disabled' 
                >&nbsp;Retire&nbsp;</a>
EOT;
            }
            elseif ($row['entry-status'] == "entered")
            {
                $col4 = <<<EOT
                <a href="race_sc.php?opt=retire&event=$eventid" type='button' class='rm-text-xs btn btn-danger btn-sm' 
                >&nbsp;Retire&nbsp;</a>
EOT;
            }
            elseif ($row['entry-status'] == "retired")
            {
                $col4 = <<<EOT
                <a href="race_sc.php?opt=retire&event=$eventid" type='button' class='rm-text-xs btn btn-danger btn-sm disabled' 
                ><span class='glyphicon glyphicon-ok'  aria-hidden='true'></span></a>
EOT;
            }
            else
            {
                $col4 = <<<EOT
                <a href="race_sc.php?opt=retire&event=$eventid" type='button' class='rm-text-xs btn btn-danger btn-sm disabled' 
                ><span class='glyphicon glyphicon-remove'  aria-hidden='true'></span></a>
EOT;
            }

            // entry status
            if (empty($row['entry-status']))
            {
                $col5_top = "";
            }
            elseif ($row['entry-status'] == "entered")
            {
                $col5_top = $row['entry-status']." - start ".$row['start'];
            }
            else
            {
                $col5_top = $row['entry-status'];
            }

            // event status
            $col5_bot = "race: ".$row['event-status-txt'];

            // results option    fixme - disabled when race not started or not finished
            if ($_SESSION['sailor_results'])
            {
                $col6 = <<<EOT
            <a href="results_pg.php?event=$eventid&mode=list" type='button' title="results" class="btn btn-md btn-success"><span class='rm-text-md glyphicon glyphicon-list-alt'  aria-hidden='true'></span></a>           
EOT;
            }
            else
            {
                $col6 = "&nbsp;";
            }


            // protest option   fixme - disabled when race not started
            if ($_SESSION['sailor_protest'])
            {
                $col7 = <<<EOT
            <a href="protest_pg.php?event=$eventid" type='button' title="protest" class="btn btn-md btn-success"><span class='rm-text-md glyphicon glyphicon-pencil'  aria-hidden='true'></span></a>
            
EOT;
            }
            else
            {
                $col7 = "&nbsp;";
            }

            $event_list.=<<<EOT
             <tr style="min-height: 2em">
                <td width="25%" class="rm-text-sm rm-table-event-list rm-text-trunc">$col1</td>   <!-- event title -->
EOT;
            if ($row['signon'] == "signon")
            {
                $event_list.=<<<EOT
                <td width="12%" class="rm-text-md rm-table-event-list">$col2</td>   <!--  signon   -->
                <td width="12%" class="rm-table-event-list">&nbsp;</td>   <!--  signoff   -->
                <td width="12%" class="rm-table-event-list">&nbsp</td>   <!--  retire   -->  
                <td width="20%" class="rm-text-sm rm-table-event-list text-success">$col5_top
                <br><span class="text-warning">$col5_bot</span></td>   <!--  entry status --> 
EOT;
            }
            elseif ($row['signon'] == "signon-retire")
            {
                $event_list.=<<<EOT
                <td width="12%" class="rm-text-md rm-table-event-list">$col2</td>   <!--  signon   -->
                <td width="12%" class="rm-text-md rm-table-event-list">&nbsp;</td>   <!--  signoff   -->
                <td width="12%" class="rm-text-md rm-table-event-list">$col4</td>   <!--  retire-->  
                <td width="20%" class="rm-text-sm rm-table-event-list text-success">$col5_top
                <br><span class="text-warning">$col5_bot</span></td>   <!--  entry status -->
EOT;
            }
            elseif ($row['signon'] == "signon-declare")
            {
                $event_list.=<<<EOT
                <td width="15%" class="rm-text-md rm-table-event-list">$col2</td>   <!--  signon   -->
                <td width="15%" class="rm-text-md rm-table-event-list">$col3</td>   <!--  signoff -->
                <td width="15%" class="rm-text-md rm-table-event-list">$col4</td>   <!--  retire-->
                <td width="20%" class="rm-text-sm rm-table-event-list text-success">$col5_top
                <br><span class="text-warning">$col5_bot</span></td>   <!--  entry status -->
EOT;
            }
            else
            {
                $event_list.=<<<EOT
                <td width="30%" class="rm-text-md rm-table-event-list">Please check with Race Officer for entry procedure</td>   <!--  signon   -->             
EOT;
            }
            $event_list.=<<<EOT
                <td width="5%" class="rm-text-md rm-table-event-list">$col6</td>   <!--  results option -->    
                <td width="5%" class="rm-text-md rm-table-event-list">$col7</td>   <!--  results option -->                     
             </tr>
EOT;
        }

        $event_bufr.= <<<EOT
            <form id="confirmform" action="race_sc.php" method="post" role="submit" autocomplete="off">        
                <table class="table" width="100%" style="table-layout: fixed"> 
                    $event_list
                </table>
            </form>
EOT;
    }

    $bufr.= <<<EOT
     <div class="row">
        <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">   
        {boat-label}
        </div>
     </div>
     
     <div class="row margin-top-10">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 ">   
            $instruction_bufr        
            $event_bufr
        </div>
     </div>
EOT;

    return $bufr;
}

function get_entry_status_text($status, $start)
{
    if ($status == "entered" )
    {
        $txt = "<span class='glyphicon glyphicon-ok'  aria-hidden='true'></span> start $start ";
    }
    elseif($status == "retired" OR $status == "signed off")  // racing
    {
        $txt = "<span class='glyphicon glyphicon-ok'  aria-hidden='true'></span> $status";
    }
    elseif ($status == "not eligible" OR $status == "class not recognised")      // ineligible
    {
        $txt = "<span class='glyphicon glyphicon-remove'  aria-hidden='true'></span> $status";
    }
    else
    {
        $txt = "";
    }

    return $txt;
}

//function signon_race_confirm($params = array())
//{
//    ($params['status'] == "update" or $params['status'] == "enter") ? $glyph = "glyphicon-ok" : $glyph = "glyphicon-remove";
//
//    $bufr = <<<EOT
//        <tr>
//            <td><h4>{name}</h4></td>
//            <td><h4>{start-time}</h4></td>
//            <td><h4>{text}</h4></td>
//            <td><span class="glyphicon $glyph rm-glyph-bg"  aria-hidden="true"></span></td>
//        </tr>
//EOT;
//    return $bufr;
//}
//
//
//function signon_confirm($params=array())
//{
//    $bufr = "";
//    if ($params['status'])
//    {
//        $confirm_msg = <<<EOT
//        <div class="row margin-top-10">
//           <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
//               <div class="alert alert-success rm-text-md" role="alert"> All done . . . have a good race </div>
//           </div>
//        </div>
//EOT;
//
//    }
//    else
//        $confirm_msg = <<<EOT
//        <div class="row margin-top-10">
//           <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
//               <div class="alert alert-danger rm-text-md" role="alert"> There was a problem with your race entry<br> . . . please contact the race officer </div>
//           </div>
//        </div>
//
//EOT;
//
//    // render the page
//    $bufr.=<<<EOT
//     <!-- boat details -->
//     <div class="row">
//        <div class="col-xs-12 col-xs-offset-0 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
//            {boat-label}
//        </div>
//     </div>
//
//     <!-- events -->
//     <div class="row margin-top-10">
//          <div class="col-xs-12 col-xs-offset-0 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
//            <table class="table table-condensed">
//                {event-list}
//            </table>
//          </div>
//     </div>
//
//     <!-- confirm message -->
//     $confirm_msg
//EOT;
//
//    return $bufr;
//}



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

