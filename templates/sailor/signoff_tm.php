<?php
function signoff($params=array())
{
    $bufr = "";
    //$bufr = "<pre>state: {$params['state']}</pre>";
    //$bufr.= "<pre>state: ".print_r($params, true)."</pre>";
    //$bufr.= "<pre>state: ".print_r($_SESSION['sailor'], true)."</pre>";

    $event_bufr = "";
    if ($params['state'] == "noevents")     // there are no races today
    {
        $event_bufr.= <<<EOT
             <div class="rm-text-space">
                <span class="rm-text-bg">No races today &hellip;</span>
             </div>
             <div class="rm-text-space">                
                <span class="rm-text-md">next race is : &nbsp; &nbsp;</span>
                <span class="rm-text-bg rm-text-highlight"> {next-event-name} - {next-event-date} - {next-event-start-time} </span>
             </div>
EOT;
    }

    elseif ($params['state'] == "noentries")    // the sailor hasn't entered any races
    {
        $event_bufr .= <<<EOT
             <div class="rm-text-space"> 
                  <span class="rm-text-bg"><b>You have not entered any races today &hellip; </b>
                        <br>Use the Sign On option to enter
                  </span>
             </div>
EOT;
    }
    else      // the sailor has entered at least one event - create table rows for signoff form
    {

        $event_list = "";
        foreach ($params['entries'] as $eventid => $e)
        {
            $e['protest'] ? $protest_chk = "checked" : $protest_chk = "";

            $position_bufr = "";
            $declare_bufr = "";
            $protest_bufr = "";

            if ($e['entered']) {
                $position_bufr .= "<h4>{$e['position']}</h4>";

                if ($e['declare'] == "declare")
                {
                    $declare_bufr = "<h4 style='text-align: center; margin-top: 20px;'>signed off</h4>";
                }
                elseif ($e['declare'] == "retire")
                {
                    $declare_bufr = "<h4 style='text-align: center; margin-top: 20px;'><b>RTD</b></h4>";
                }
                else
                {
                    $declare_bufr = <<<EOT
                    <div >
                        <label class="radio-inline" >
                           <input type="radio" name="declare{$eventid}" class=""  
                                  value="declare" checked> <span class="rm-text-md">&nbsp;&nbsp;sign off &nbsp;&nbsp;</span>
                        </label>
                        <label class="radio-inline" >
                           <input type="radio" name="declare{$eventid}" class="" 
                                  value="retire" > <span class="rm-text-md">&nbsp;&nbsp;retire &nbsp;&nbsp;</span>
                        </label>
                    </div>
EOT;
                }

                if ($params['protest_option'])
                {
                    $e['protest'] ? $protest_chk = "checked" : $protest_chk = "";
                    $protest_bufr = <<<EOT
                        <div class="checkbox" >
                           <label>
                               <input type="checkbox" name="protest{$eventid}" class="rm-form-label" $protest_chk> 
                           </label>
                        </div>
EOT;
                }


            }
            else    // not entered for this race
            {
                $position_bufr = "<h4 style='text-align: left;'><i>not entered</i></h4><br>";
            }

            $event_list .= <<<EOT
            <tr>
                <td><h4>{$e['event-name']}</h4></td>
                <td>$position_bufr</td>
                <td style="vertical-align: middle;">$declare_bufr</td>
                <td style="text-align: center; vertical-align: middle;">$protest_bufr</td>
            </tr>
EOT;
        }

        $params['protest_option'] ? $protest_col = "<th style='width: 20%;'>Protest/Redress?</th>" : $protest_col = "";

        $event_bufr.= <<<EOT
            <form id="confirmform" action="signoff_sc.php" method="post" role="submit" autocomplete="off"> 
                <h4>Note:  All reported race positions are provisional &hellip; </h4>       
                <table class="table table-condensed"> 
                    <thead><tr class="rm-table-col-title">
                        <th style="width: 30%;">Race</th>
                        <th style="width: 15%;">Position</th>
                        <th style="width: 35%;">Declaration</th>
                        $protest_col                   
                    </tr></thead>
                    $event_list
                </table>
            
                <!-- confirm button -->
                <div class="row margin-top-10">
                    <div class="col-md-6 col-md-offset-3">
                        <button type="submit" class="btn btn-warning btn-block btn-lg" >
                            <span class="glyphicon glyphicon-ok"></span>
                            &nbsp;&nbsp;<strong>Confirm Declarations</strong>
                        </button>
                    </div>
                </div>

            </form>
EOT;
    }


    // put page together
    $bufr.= <<<EOT
     <!-- boat details -->
     <div class="row">
        <div class="col-xs-12 col-xs-offset-0 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-10 col-lg-offset-1">
            <div class="list-group list-group-item list-group-item-info">
                <h3>{class} {sailnum}</h3>
                <h4>{team}</h4>
            </div>
        </div>
     </div>

     <!-- events -->
     <div class="row margin-top-20">
          <div class="col-xs-12 col-xs-offset-0 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-10 col-lg-offset-1">        
            <table class="table table-condensed"> 
                $event_bufr
            </table>
          </div>
          
     </div>
EOT;

    return $bufr;
}


function signoff_race_confirm($params = array())
{
    if ($params['declare'] == "declare")
    {
        $declaration = "<span class='glyphicon glyphicon-ok rm-glyph-bg'  aria-hidden='true'></span>";
    }
    elseif ($params['declare'] == "retire")
    {
        $declaration = "<span style='color: darkred; font-size:1.5em'>RTD</span>";
    }
    else
    {
        $declaration = $params['declare'];
    }

    $params['protest'] ? $protest = "protest notified" : $protest = "" ;

    $bufr = <<<EOT
        <tr>
            <td><h4>{name}</h4></td>
            <td><h4>{position}</h4></td>
            <td><h4>$declaration</h4></td>
            <td><h4>$protest</h4></td>
        </tr>
EOT;
    return $bufr;
}


function signoff_confirm($params=array())
{
    $bufr = "";
    if ($params['complete'])
    {
        $confirm_msg = <<<EOT
        <div class="row margin-top-10">
           <div class="col-xs-12 col-xs-offset-0 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-10 col-lg-offset-1">
               <div class="alert alert-success rm-text-md" role="alert"> 
                  All done . . . thanks<br>If you want to change your declaration - select the signoff option again. 
               </div>
           </div>
        </div>
EOT;
    }
    else
        $confirm_msg = <<<EOT
        <div class="row margin-top-10">
           <div class="col-xs-12 col-xs-offset-0 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-10 col-lg-offset-1">
               <div class="alert alert-danger rm-text-md" role="alert"> 
                    There was a problem with your race entry<br> . . . please contact the race officer 
               </div>
           </div>
        </div>
EOT;

    $bufr.=<<<EOT
     <!-- boat details -->
     <div class="row">
        <div class="col-xs-12 col-xs-offset-0 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
            <div class="list-group">
                <h2>{class} {sailnum}</h2>
                <h4><span style="font-size: 110%">{team}</span></h4>
            </div>
        </div>
     </div>

     <!-- events -->
     <div class="row margin-top-10">
          <div class="col-xs-12 col-xs-offset-0 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-10 col-lg-offset-1"">        
            <table class="table table-condensed"> 
                {event-list}
            </table>
          </div>
     </div>

     <!-- confirm button -->
     $confirm_msg
EOT;

    return $bufr;
}

?>