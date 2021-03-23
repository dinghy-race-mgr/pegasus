<?php

function display_entries($params=array())
{
    $today = date("l jS F H:i");
    $rows = "";
    $num_entries = 0;
    $last_class = "";
    foreach ($params['entries'] as $entry)
    {
        if ($last_class != strtolower($entry['classname']))
        {
            $rows.= <<<EOT
                <tr colspan="4"><td>&nbsp;</td></tr>
EOT;
        }

        if (strpos($entry['action'], "enter") !== false)
        {
            $linestyle = "";
            $status = "";
            $num_entries++;
        }
        elseif (strpos($entry['action'], "update") !== false)
        {
            $linestyle = "";
            $status = "";
            $num_entries++;
        }
        elseif (strpos($entry['action'], "retire") !== false)
        {
            $linestyle = "color: lightcoral";
            $status = "RETIRED";
        }
        else
        {
            $linestyle = "";
            $status = "";
            $num_entries++;
        }

        $team = $entry['helmname'];
        if (!empty($entry['crewname'])) { $team.= " / ".$entry['crewname']; }
        $rows.= <<<EOT
        <tr>
            <td style="$linestyle">{$entry['classname']}</td>
            <td style="$linestyle">{$entry['sailnum']}</td>
            <td style="$linestyle">$team</td>
            <td style="$linestyle">$status</td>        
        </tr>
EOT;
        $last_class = strtolower($entry['classname']);
    }

    $datetime = date("jS F",strtotime($params['event']['event_date']));
    if (!empty($params['event']['event_start'])) { $datetime.= " - ".$params['event']['event_start']; }

    $bufr = <<<EOT
        <div style="margin-bottom: 30px;">
            <h2 class="rm-text-highlight">{$params['event']['event_name']}&nbsp;&nbsp;<small>$datetime</small></h2>
            <h4>$num_entries entries <small>as at $today</small></h4>
        </div>
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <table class="table table-condensed table-responsive">
                    <thead >
                        <tr class="info">
                            <th>Class</th>
                            <th>Sail No.</th>
                            <th>Team</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <tr></tr>
                    $rows
                    </tbody>
                </table>
            </div>
        </div>
EOT;

    return $bufr;
}