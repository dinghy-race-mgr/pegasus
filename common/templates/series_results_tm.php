<?php

// NEXT - add missing fields in class
// NEXT - test with file input (changing display properties)
// NEXT - tune styles
// NEXT - revisit race report to get consistent

/*
 * HTML template for producing html markup for a series result using the raceManager template class
 *
 * The text fields passed to replace the template {} fields are:
 * pagetitle
 * styles
 * sys_name
 * sys_version
 *
 * The arguments past to the template via the $params array are:
 *
 * [opts] = Array
 *    ( [inc-club]      => true|false        include club name for each competitor
 *      [inc-codes]     => true|false        include definitions for each scoring code used
 *      [inc-pagebreak] => true|false        include page break between fleets
 *      [race-label]    => number | date     option for labelling race R1 or 08-11
 *      [club-logo]     => absolute url      if set - display club logo
 *      [styles]        => absolute url      embedded styles to be used
 *    )
 *
 * [codes] = Array                           codes used in this series
 *    (
 *      [1] => Array
 *          ( [code]    => DNF
 *            [short]   => Did Not Finish
 *            [scoring] => N + 1
 *          )
 *       ....
 *
 * [rst]  multi-dimensional array with the result data held in the 'rst' array in the following structure:
 *
 * [series] => Array
        (
            [clubname] => Starcross Yacht Club
            [clubcode] => SYC
            [cluburl] => www.starcrossyc.org.uk
            [name] => Summer Series
            [code] => SPRING-21
            [type] => long series
            [notes] => Spring Sunday Series
            [raceformat] => 1
            [merge] => laser,laser radial,laser 4.7|rs100 8.4,rs100 10.2
            [classresults] =>
            [avgscheme] => all_counting
            [discard] => 0,0,1,1,2,2
            [nodiscard] => 0,0,0,0,0,0
            [multiplier] =>
            [maxduty] => 1
            [dutypoints] => 0
            [races_num] => 6
            [races_complete] => 5
            [results-date] => 2021-06-04 14:43
            [avg_turnout] => 4.8
            [max_turnout] => 6
            [min_turnout] => 3
        )

    [races] => Array
        (
            [1] => Array
                (
                    [race-name] => R1
                    [race-status] => completed
                    [race-full-date] => 2016-06-01
                    [race-short-date] => 06/01
                    [race-url] =>                // FIXME - future extension
             ....
    [fleets] => Array
            (
                [1] => Array
                    (
                        [fleet-name] => monohull
                        [num-competitors] => 4
                        [avg_turnout] =>
                        [min_turnout] =>
                        [max_turnout] =>
                        [sailors] => Array
                            (
                                [1] => Array
                                    (
                                        [class] => Merlin Rocket
                                        [sailnum] => 3718
                                        [team] => Mark Elkington \ Sarah Roberts
                                        [club] => Starcross YC
                                        [total] => 13.2
                                        [net] => 8.2
                                        [posn] => 1
                                        [note] => only one leg
                                        [rst] => Array
                                            (
                                                [r1] => Array
                                                    (
                                                        [result] => 2
                                                        [code] =>
                                                        [discard] =>
                                                    )
                                                 ...
                                  ...
                   ...

 */

function series_sheet($params = array())
{
    // partition params for ease of use
    $opts   = $params['opts'];
    $codes  = $params['codes'];
    $series = $params['series'];
    $races  = $params['races'];
    $fleets = $params['fleets'];
    $sys    = $params['sys'];

    //echo "<pre>TEMPLATE series_sheet: ".print_r($opts,true)."</pre>";

    $doc_head_bufr = <<<EOT
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
               {styles}
            </style>

    </head>   
EOT;

    // header
    empty($opts['club-logo']) ? $club_logo = "" : $club_logo = "<img class='club-logo' style='width: 100px; height: auto' src='{$opts['club-logo']}'>";

    $header_bufr = <<<EOT
    <div class="flex-container">
        <div class="flex-child">$club_logo</div> 
        <div class="flex-child" style="text-align: right">
            <span class="event-hdr-right">{$series['clubname']}</span>
        </div> 
    </div>
EOT;

    // event detail
    $series_turnout = "";
    if ($opts['inc-turnout'])
    {
        $series_turnout = <<<EOT
        turnout: max - {$series['max_turnout']}, min - {$series['min_turnout']}, avg - {$series['avg_turnout']}
EOT;
    }

    $event_bufr = <<<EOT
    <div class="flex-container">
        <div class="flex-child">
            <span class="event-hdr-left" >{$series['name']} {$params['eventyear']}</span>
            <div class="report-notes" >{$series['notes']} | $series_turnout</div>
        </div> 
        <div class="flex-child" style="text-align: right">
            <span class="print"><a class="button-green noprint" onclick="window.print()" href="#" type="button">Print Results</a></span>
        </div> 
    </div>
EOT;

    // format results for each fleet
    //$discard = explode(",", $series['discard']);
    !empty($discard[$series['races_complete']]) ? $discard_txt = "| discards: <b>{$series['discard'][$series['races_complete']]}</b>"
        : $discard_txt = "";
    $fleet_block = array();
    foreach($fleets as $i=>$fleet)
    {
        $fleet_turnout = "";
        if ($opts['inc-turnout']) {
            $fleet_turnout = <<<EOT
        turnout: max - {$fleet['max_turnout']}, min - {$fleet['min_turnout']}, avg - {$fleet['avg_turnout']} 
EOT;
        }

        // fleet detail
        $fleet_detail_bufr = <<<EOT

            <div class="fleet-hdr">{$fleet['fleet-name']}</div>
            <div class="spacer"></div>
            <div class="fleet-info">
                entries: <b>{$fleet['num-competitors']}</b> | races sailed: <b>{$series['races_complete']}</b> $discard_txt | $fleet_turnout
            </div>
EOT;
        // fleet data
        $fleet_cols_bufr = format_series_columns($races, $opts['inc-club'], $opts['race-label']);
        if ($fleet['num-competitors'] > 0)   // results to display
        {
            $fleet_data_bufr = format_series_data($fleet, $races, $opts['inc-club']);

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
                <div>               
                    <table >
                        $fleet_cols_bufr
                    </table>
                </div>
EOT;
        }
    }

    // INFO section

    // codes list - currently not showing codes on series result page so has no meaning
    $code_info = "";
    // if ($opts['inc-codes']) { $code_info = format_series_codes($codes); }

    // get race status info
    $race_status_info = format_event_status_info($races, $opts['race-label']);
    $info_bufr = <<<EOT
        <div><br></div>
        <div class="flex-container">
            <div class="flex-child"><span class="info-notes">$code_info</span></div> 
            <div class="flex-child" style="text-align: right"><span class="info-notes">$race_status_info</span></div> 
        </div>
EOT;

    // footer
    if (!empty($sys['sys_website']))
    {
        $system_txt = <<<EOT
            <a href="{$sys['sys_website']}">{$sys['sys_name']} </a> ({$sys['sys_release']}) {$sys['sys_version']}
EOT;
    }
    else
    {
        $system_txt = "{$sys['sys_name']} ({$sys['sys_release']}) {$sys['sys_version']}";
    }

    $print_date = date("j M Y \a\\t H:i");
    $footer_bufr =<<<EOT
        <br><div class="spacer"></div>
        <div class="flex-container">
            <div class="flex-child"><span class="footer">$system_txt  - created $print_date</span></div> 
            <div class="flex-child" style="text-align: right"><span class="footer">Copyright: {$sys['sys_copyright']}</span></div> 
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
            $htm.= "<div style='break-inside: avoid'>".$fleet_bufr."</div>";
        }
        $body = "<body>".$header_bufr.$event_bufr.$htm.$info_bufr.$footer_bufr."</body></html>";
    }

    return $doc_head_bufr.$body;
}


function format_series_codes($codes)
{
    $code_str = "";
    $count = 0;
    foreach ($codes as $key => $code)
    {
        $count++;
        $scoring = "";
        if (!empty($code['scoring']))
        {
            $scoring = strtr($code['scoring'], array("N" => "no. in race", "S" => "no. in series", "P" => "position"));
        }
        $code_str.= "{$code['code']} - {$code['short']}"; // <i>[$scoring]</i>";

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

    return "RESULT CODES: ".$code_str;
}


function format_series_columns($races, $inc_club, $race_label)
{
    if ($inc_club)
    {
        $colwidth = array("pos"=>"3","class"=>"12","sailnum"=>"7","comp"=>"20","club"=>"10","total"=>"7","net"=>"7");
        $club_col = "<th class='table-col' style='width: {$colwidth['club']}%'>club</th>";
    }
    else
    {
        $colwidth = array("pos"=>"3","class"=>"12","sailnum"=>"7","comp"=>"25","club"=>"0","total"=>"7","net"=>"7");
        $club_col = "" ;
    }


    $race_cols_hdr = "";
    foreach ($races as $i=>$race)
    {
        $race_label == "date" ? $val = $race['race-short-date'] : $val = "R$i";

        if (!empty($race['race-url']))
        {
            $race_cols_hdr.= <<<EOT
            <td class='table-col' style='text-align: center' >
                    <a href="{$race['race-url']}" target="_BLANK" style="color: white;" title="click for race result" >$val</a>
            </td>
EOT;
        }
        else
        {
            $race_cols_hdr.= "<th class='table-col'>$val</th>";
        }
    }



    $htm = <<<EOT
    <thead>
        <tr>
            <th class='table-col' style='width: {$colwidth['pos']}%'>pos</th>
            <th class='table-col' style='width: {$colwidth['class']}%'>class</th>
            <th class='table-col' style='width: {$colwidth['sailnum']}%'>no.</th>
            <th class='table-col' style='width: {$colwidth['comp']}%' >competitors</th>
            $club_col
            $race_cols_hdr
            <th class='table-col' style='width: {$colwidth['total']}%'>total</th>
            <th class='table-col' style='width: {$colwidth['net']}%'>net</th>
        </tr>
    </thead>
EOT;

    return $htm;
}

function format_series_data($fleet, $races, $inc_club)
{
    $htm = "";

    $rows_htm = "";
    foreach ($fleet['sailors'] as $i => $sailor)
    {
        $race_cells = "";
        foreach ($races as $j => $race)
        {
            if ($race['race-status'] == "completed"  or $race['race-status'] == "sailed" or $race['race-status'] == "running")
            {
                if (array_key_exists("r$j", $sailor['rst'])) {

                    // get score for this race and this sailor

                    $points = $sailor['rst']["r$j"]['result'];
                    $score = ($points == (int) $points) ? (int) $points : (float) $points;  // convert to int if possible

//                    if (!empty($sailor['rst']["r$j"]['code']))                              // add code if set
//                    {
//                        $score = $score . "/" . $sailor['rst']["r$j"]['code'];
//                    }

                    if ($sailor['rst']["r$j"]['discard'])                                    // if score is discarded display score as in brackets
                    {
                        $score = "[$score]";
                    }

                    $race_cells .= "<td class='table-cell table-points'>$score</td>";
                }
            }
            else
            {
                $race_cells .= "<td class='table-cell table-points'>&nbsp;</td>";
            }
        }

        $inc_club ? $club_cell = "<td class='table-cell truncate'>{$sailor['club']}</td>" : $club_cell = "";

        $rows_htm .= <<<EOT
            <tr class="table-row">
                <td class="table-cell">{$sailor['posn']}</td>
                <td class="table-cell truncate">{$sailor['class']}</td>
                <td class="table-cell">{$sailor['sailnum']}</td>
                <td class="table-cell truncate">{$sailor['team']}</td>
                $club_cell
                $race_cells
                <td class="table-cell table-points" >{$sailor['total']}</td>
                <td class="table-cell table-points" >{$sailor['net']}</td>                   
            </tr>
EOT;
    }

    $htm .= <<<EOT
        <tbody class="">
            $rows_htm
        </tbody>                          
EOT;

    return $htm;
}

function format_event_status_info($races, $race_label)
{
    $list = array();
    foreach ($races as $i=>$race)
    {
        $race_label == "date" ? $label = $race['race-short-date'] : $label = "$i";
        if ($race['race-status'] == "abandoned" or $race['race-status'] == "cancelled")
        {
            $list[] ="Race $label - {$race['race-status']}";
        }
        elseif ($race['race-status'] == "selected" or $race['race-status'] == "inprogress")
        {
            $list[] = "Race $label - in progress";
        }
    }
    $htm = "";
    if (!empty($list))
    {
        foreach($list as $race)
        {
            $htm.= $race."<br>";
        }

    }

    return $htm;
}


