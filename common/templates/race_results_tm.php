<?php

/*
 * HTML template for producing html markup for a race result using the raceManager template class
 *
 * The text fields passed to replace the template {} fields are:
 * pagetitle           event_name
 * styles              event_date
 * club_name           event_start
 * result_notes        event_wind
 * result_status       event_ood
 * sys_name
 * sys_version
 *
 * The params argument passed as an multi-dimensional array with the result data has the following structure
 * FIXME -add this structure during a test run
 */


function race_sheet($params = array())
{
    //echo "<pre>".print_r($params,true)."</pre>";

    $opts = $params['opts'];

    $layout = array(
        "level"    => array(
            "fields" => "class,sailnum,team,club,etime,result",
            "widths" => "15,10,25,15,10,10",
        ),
        "handicap" => array(
            "fields" => "class,sailnum,team,club,pn,etime,ctime,result",
            "widths" => "15,10,25,10,5,10,10,10",
        ),
        "average"  => array(
            "fields" => "class,sailnum,team,club,pn,lap,etime,atime,result",
            "widths" => "12,8,25,10,5,5,8,8,8",
        ),
        "pursuit"  => array(
            "fields" => "class,sailnum,team,club,pn,result",
            "widths" => "15,10,25,15,5,10",
        ),
    );

    $htm_head_bufr = <<<EOT
    <!DOCTYPE html><html lang="en">
    <head>
            <title>{pagetitle}</title>
            
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="description" content="">
            <meta name="author" content="">

            <link rel="shortcut icon"    href="../common/images/favicon.ico">

            <!-- Custom styles for this template - includes print styles  - obtained from .css file -->
            <style>
               {$opts['styles']}
            </style>

    </head>
EOT;

    // header
    empty($opts['club-logo']) ? $club_logo = "" : $club_logo = "<img class='club-logo' style='width: 100px; height: auto' src='{$opts['club-logo']}'>";

    $header_bufr = <<<EOT
    <div class="flex-container">
        <div class="flex-child">$club_logo</div> 
        <div class="flex-child" style="text-align: right">
            <span class="event-hdr-right">{club_name}</span>
        </div> 
    </div>
EOT;

    // event detail ( + print button)
    $event_bufr = <<<EOT
    <div class="flex-container">
        <div class="flex-child">
            <span class="event-hdr-left" >{event_name} <span style="font-size: 0.7em;">[{short_date} &nbsp; {event_start}]</span></span>
            <div class="report-notes">{result_notes}</div>
            <div class="fleet-info"> date: <b>{event_date}</b> | start: <b>{event_start}</b> | wind: <b>{event_wind}</b> 
                                     | ood: <b>{event_ood}</b> | status: <b>{result_status}</b></div>
        </div> 
        <div class="flex-child" style="text-align: right">
            <span class="print"><a class="button-green noprint" onclick="window.print()" href="#" type="button">Print Results</a></span>
        </div> 
    </div>
EOT;

    // format results for each fleet
    $fleet_block = array();
    foreach($params['fleet'] as $i=>$fleet)
    {
        empty($fleet['msg']) ? $fleet_msg_txt = "" : $fleet_msg_txt = "<div class='fleet-info'>{$fleet['msg']}</div>";

        // fleet detail
        $fleet_detail_bufr = <<<EOT
            <div class="fleet-hdr">{$fleet['fleet_name']}</div>
            <div class="spacer"></div>
            $fleet_msg_txt
            <div class="fleet-info">scoring: <b>{$fleet['scoring']}</b> | yardstick: <b>{$fleet['py_type']}</b></div>
EOT;
        // fleet data
        if (count($params["result"][$i]) > 0)   // results to display
        {
            $fleet_cols_bufr = format_race_columns($fleet['scoring'], $layout, $opts['inc-club']);
            $fleet_data_bufr = format_race_data($params["result"][$i], $fleet['scoring'], $layout, $opts['inc-club']);

            $fleet_block[$i] = <<<EOT
                <div>$fleet_detail_bufr</div>
                <div>               
                    <table class="table">
                        $fleet_cols_bufr
                        $fleet_data_bufr
                    </table>
                </div>
EOT;

        }
        else  // no competitors
        {
            $fleet_block[$i] = <<<EOT
                <div>$fleet_detail_bufr</div>
                <div class="info-notes indent20">&hellip; no entries in this fleet &hellip;</div>
EOT;
        }
    }

    // INFO SECTION
    // codes list - including result code list if required)
    $code_info = "";
    if ($opts['inc-codes']) { $code_info = format_race_codes($params['codes']); }

    $info_bufr = <<<EOT
        <div class="spacer"></div>
        <div class="flex-container">
            <div class="flex-child"><span class="info-notes">$code_info</span></div> 
            <div class="flex-child" style="text-align: right"><span class="info-notes">&nbsp;</span></div> 
        </div>
EOT;

    // DOC FOOTER
    if (!empty($params['sys_website']))
    {
        $system_txt = <<<EOT
            <a href="{$params['sys_website']}">{sys_name} </a> ({sys_release}) {sys_version}
EOT;
    }
    else
    {
        $system_txt = "{sys_name} ({sys_release}) {sys_version}";
    }

    $print_date = date("j M Y \a\\t H:i");
    $footer_bufr =<<<EOT
        <br><div class="spacer"></div>
        <div class="flex-container">
            <div class="flex-child"><span class="footer">$system_txt  - created $print_date</span></div> 
            <div class="flex-child" style="text-align: right"><span class="footer">Copyright: {sys_copyright}</span></div> 
        </div>
EOT;

// report layout
    if ($opts['inc-pagebreak'])
    {
        $body = "<body>";
        foreach ($fleet_block as $fleet_bufr)
        {
            $body.= $header_bufr.$event_bufr.$fleet_bufr.$info_bufr.$footer_bufr;
            $body.= "<div class='page-break'>&nbsp;</div>";
        }
        $body.= "</body></html>";
    }
    else
    {
        $htm = "";
        foreach ($fleet_block as $fleet_bufr)
        {
            $htm.=$fleet_bufr;
        }
        $body = "<body>".$header_bufr.$event_bufr.$htm.$info_bufr.$footer_bufr."</body></html>";
    }

    return $htm_head_bufr.$body;
}


function format_race_codes($codes)
{
    // get codes into html bufr
    $code_str = "";
    $count = 0;
    foreach ($codes as $key => $row) {
        $count++;
        $scoring = "";
        if (!empty($row['scoring']))
        {
            $scoring = strtr($row['scoring'], array("N" => "race competitors", "S" => "series competitors", "P" => "position"));
        }
        $code_str.= "<b>{$row['code']}</b> - {$row['short']}";  //<i>$scoring</i>";

        if ($count >= 3)
        {
            $code_str.= "<br>";
            $count = 0;
        }
        else
        {
            $code_str.= "&nbsp;&nbsp;|&nbsp;&nbsp;";
        }

    }
    $code_str = rtrim($code_str, "|");

//    $bufr = <<<EOT
//    <div class = "small-note" style="margin-top: 25px;"><b>Result Codes: </b><br>$code_str</div>
//EOT;

    return "RESULT CODES: ".$code_str;
}


function format_race_columns($scoring, $layout, $include_club)
{
    $cols = array(
        "class"   => "<th class='table-col' width='%s%%'>class</th>",
        "sailnum" => "<th class='table-col' width='%s%%'>no.</th>",
        "team"    => "<th class='table-col' width='%s%%'>competitors</th>",
        "club"    => "<th class='table-col' width='%s%%'>club</th>",
        "pn"      => "<th class='table-col' width='%s%%'>PN</th>",
        "lap"     => "<th class='table-col' width='%s%%'>laps</th>",
        "etime"   => "<th class='table-col' width='%s%%'>elapsed</th>",
        "ctime"   => "<th class='table-col' width='%s%%'>corrected</th>",
        "atime"   => "<th class='table-col' width='%s%%'>corrected</th>",
        "result"  => "<th class='table-col' width='%s%%'>points</th>",
    );

    $field_list = explode(",",$layout[$scoring]['fields']);
    $width_list = explode(",",$layout[$scoring]['widths']);
    if (!$include_club)
    {
        $club_key = array_search("club", $field_list);
        unset($field_list[$club_key]);
    }

    $bufr = "<thead><tr>";
    foreach($field_list as $k=>$field)
    {
        $bufr.= sprintf($cols[$field], $width_list[$k]);
    }
    $bufr.= "</tr></thead>";

    return $bufr;
}


function format_race_data($results, $scoring, $layout, $include_club)
{
    $cols = array(
        "class"   => "<td class='table-cell truncate'>{class}</td>",
        "sailnum" => "<td class='table-cell'>{sailnum}</td>",
        "team"    => "<td class='table-cell truncate'>{team}</td>",
        "club"    => "<td class='table-cell truncate'>{club}</td>",
        "pn"      => "<td class='table-cell'>{pn}</td>",
        "lap"     => "<td class='table-cell-ctr'>{lap}</td>",
        "etime"   => "<td class='table-cell-right'>{etime}</td>",
        "ctime"   => "<td class='table-cell-right'>{ctime}</td>",
        "atime"   => "<td class='table-cell-right'>{atime}</td>",
        "result"  => "<td class='table-cell table-points'>{result}</td>",
    );

    $field_list = explode(",",$layout[$scoring]['fields']);
    if (!$include_club) {
        $club_key = array_search("club", $field_list);
        unset($field_list[$club_key]);
    }

    $row_tmpl = "<tr>";
    foreach ($field_list as $field) {
        $row_tmpl .= $cols[$field];
    }
    $row_tmpl .= "</tr>";

    $bufr = "<tbody>";
    foreach ($results as $row) {
        $bufr .= u_format($row_tmpl, $row);
    }
    $bufr .= "</tbody>";

    return $bufr;
}


