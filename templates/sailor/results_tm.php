<?php

function result_list($params = array())
{
    $bufr = "";
    $event_list = "";
    $background_color = array("1" => "gold", "2" => "silver", "3" => "darkgoldenrod");
    $color = array("1" => "black", "2" => "black", "3" => "white");

    foreach($params as $eventid => $event)
    {
        if ($event['position'] != "?")
        {
            $position = u_numordinal($event['position']);
        }
        else
        {
            $position = $event['position'];
        }

        if ($event['position'] == "1" OR $event['position'] == "2" OR $event['position'] == "3") {
            $display_color = "font-weight: bold; color: {$color[$event['position']]}; background-color: {$background_color[$event['position']]}";
        }
        else
        {
            $display_color = "";
        }

        if ($event['racestate'] == "allfinished" or $event['racestate'] == "finishing")
        {
            if ($event['racestate'] == "allfinished")
            {
                $btn_label ="Final Results";
                $btn_color = "success";
            }
            else
            {
                $btn_label ="Latest Positions";
                $btn_color = "warning";
            }

            $button = <<<EOT
            <a href="results_pg.php?mode=full&event=$eventid" >
                <span class="badge progress-bar-$btn_color" style="font-size: 1.2em;">
                    &nbsp;&nbsp;<span class="glyphicon glyphicon-th-list"></span>&nbsp;&nbsp;$btn_label&nbsp;&nbsp;
                </span>
            </a>
EOT;
        }
        else
        {
            $button = "no results yet ...";
        }

        $event_list .= <<<EOT
             <tr >
                <td class="rm-text-md rm-table-event-list rm-text-capitalise" style="width: 50%;">{$event['event-name']}</td>
                <td class="rm-text-md rm-table-event-list text-center" style="width: 15%; margin: 10px; $display_color"> $position</td>  
                <td class="rm-table-event-list" style="width: 35%; text-align: right !important;">$button</td>                  
             </tr>
EOT;
    }

    $bufr.= <<<EOT
     <div class="row">
        <div class="col-xs-10 col-xs-offset-1 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-10 col-lg-offset-1">   
        {boat-label}
        </div>
     </div>
     
     <div class="row margin-top-10">
        <div class="col-xs-10 col-xs-offset-1 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-10 col-lg-offset-1">         
            <table class="table table-condensed"> 
                $event_list
            </table>
        </div>
     </div>
EOT;

    return $bufr;
}

function result_data($params = array())
{
    $bufr = "";
    $race_hdr_bufr = "";
    $result_bufr = "";

    $race_hdr_bufr.= <<<EOT
        <div class="list-group">
                <h3>{event-name}&nbsp;&nbsp;&nbsp;{event-date}</h3>
                <h4>{fleet-name} Fleet</h4>
        </div>
EOT;

    if ($params['type'] = "level")
    {
        $cols = array("position"=>"position", "class"=>"class", "sailnum"=>"sailnum", "laps"=>"laps", "elapsed"=>"etime");
    }
    elseif ($params['type'] == "handicap" or $params['type'] == "average")
    {
        $cols = array("position"=>"position", "class"=>"class", "sailnum"=>"sailnum", "laps"=>"laps", "corrected"=>"atime");
    }
    elseif ($params['type'] == "pursuit")
    {
        $cols = array("position"=>"position", "class"=>"class", "sailnum"=>"sailnum", "laps"=>"laps");
    }
    else
    {
        $cols = array("position"=>"position", "class"=>"class", "sailnum"=>"sailnum", "laps"=>"laps");
    }

    $hdr_cols = "";
    foreach ($cols as $k=>$v)
    {
        $hdr_cols.= <<<EOT
           <th class="rm-table-col-title">$k</th>
EOT;
    }

    $tbl_cols = "";
    foreach ($params['data'] as $row)
    {
        $style = "rm-table-col-value";
        if ($params['sailorid'] == $row['compid'])
        {
            $style = "rm-table-col-highlight";
        }

        $tbl_cols.= "<tr>";
        foreach ($cols as $k=>$v)
        {
            $tbl_cols.= <<<EOT
              <td class="$style">{$row["$v"]}</td>
EOT;
        }
        $tbl_cols.= "</tr>";
    }

    $bufr.= <<<EOT
     <div class="row">
        <div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">   
        $race_hdr_bufr
        </div>
     </div>
     
     <div class="row margin-top-10">
        <div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2"> 
            <div style="overflow-y:auto;">        
                <table class="table table-condensed"> 
                    <thead>
                        <tr>
                            $hdr_cols          
                        </tr>
                    </thead>
                    $tbl_cols
                </table>
            </div>
            <p class="margin-top-20">Note: Results are provisional</p>
        </div>
     </div>
     <div class="pull-right margin-top-20">
        <button type="button" class="btn btn-info btn-lg" onclick="history.go(-1);">
            <span class="glyphicon glyphicon-chevron-left"></span>&nbsp;Back
        </button>
     </div>
EOT;

    return $bufr;
}

function result_none($params = array())
{
    $bufr = <<<EOT
    <div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">
        <div class="alert alert-warning margin-top-40">
             <h2>Sorry!</h2> <h4>Unable to get the results you wanted</h4>
        </div>
    </div>
    <div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">
        <div class="pull-right margin-top-40 margin-bot-40">
            <button type="button" class="btn btn-info btn-lg" onclick="history.go(-1);">
                <span class="glyphicon glyphicon-chevron-left"></span>&nbsp;Back
            </button>
        </div>
    </div> 
    <br><br>   
EOT;

    return $bufr;
}



?>