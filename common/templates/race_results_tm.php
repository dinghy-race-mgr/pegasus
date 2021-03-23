<?php



function race_sheet($params = array())
{
    $layout = array(
        "level"    => array(
            "fields" => "class,sailnum,team,club,etime,result",
            "widths" => "15,10,20,15,10,10",
        ),
        "handicap" => array(
            "fields" => "class,sailnum,team,club,pn,etime,ctime,result",
            "widths" => "10,5,20,10,5,10,10,10",
        ),
        "average"  => array(
            "fields" => "class,sailnum,team,club,pn,lap,etime,atime,result",
            "widths" => "10,5,20,10,5,5,8,8,10",
        ),
        "pursuit"  => array(
            "fields" => "class,sailnum,team,club,pn,result",
            "widths" => "15,10,20,15,5,10",
        ),
    );

    $ext_stylesheet = $_SESSION['baseurl']."/rm_racebox/style/rm_report.css";
    // FIXME this will need to be the operational location on the installed club website

    $doc_head_bufr = <<<EOT
    <!DOCTYPE html><html lang="en">
    <head>
            <title>{pagetitle}</title>
            
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="description" content="">
            <meta name="author" content="">

            <link   rel="shortcut icon"    href="../common/images/favicon.ico">

            <!-- Custom styles for this template - includes print styles -->
            <style>
               {styles}
            </style>
            
            <!-- stylesheet used if it is available - allows styling to be changed on existing results pages -->
            <link href="$ext_stylesheet" rel="stylesheet">

    </head>
EOT;
    // header
    $header_bufr = <<<EOT
    <!-- header -->
    <div class="title2 pull-right">{club_name} Results</div>
EOT;

    // event detail
    $event_bufr = <<<EOT
    <!-- event detail -->
    <div>
        <span class="title pull-left" style="width: 50%; display: inline-block;">{event_name}</span>
        <span class="pull-right" style="width: 45%; display: inline-block;">
            <a class="noprint" onclick="window.print()" href="#">Print results</a>
        </span>
    </div>
    <div class="pull-left" ><span class="text-alert">{result_notes}</span></div>
    <div class="pull-left small-note">
        date: <b>{event_date}</b> | start: <b>{event_start}</b> | wind: <b>{event_wind}</b> | ood: <b>{event_ood}</b> | status: <b>{result_status}</b>
    </div>
EOT;

    // format results for each fleet
    $fleet_block = array();
    foreach($params['fleet'] as $race=>$fleet)
    {
        // fleet detail
        $fleet_detail_bufr = <<<EOT
        <!-- fleet detail -->
        <div class="title2">{$fleet['fleet_name']}</div>
        <div class="divider clearfix"></div>
        <div class="pull-left" style="width:60%;"><span class="text-alert">{$fleet['msg']}</span></div>
        <div class="pull-left small-note" style="width:50%;">
            scoring: <b>{$fleet['scoring']}</b> | yardstick: <b>{$fleet['py_type']}</b>
        </div>
EOT;

        if (count($params["result"][$race]) > 0)
        {
            $fleet_results_bufr = format_result_columns($fleet['scoring'], $layout, $params['inc_club']);
            $fleet_results_bufr.= format_result_data($params["result"][$race], $fleet['scoring'], $layout, $params['inc_club']);

            // add layout for each fleet
            $fleet_block[$race] = <<<EOT
            $fleet_detail_bufr
            <table style="width: 95%; align: center;" >
                $fleet_results_bufr
            </table>
EOT;
        }
        else
        {
            $fleet_block[$race] = <<<EOT
            $fleet_detail_bufr
            <div class="pull-center"><b>&hellip; no entries in this fleet &hellip;</b></div>
EOT;
        }
    }

    // codes used - including result code list if required)
    $code_bufr = "";
    if ($params['add_codes'])
    {
        if ($params['inc_codes'] ) { $code_bufr = format_result_codes($params['inc_codes']); }
    }

    // footer
    $createdate = date("D j M y H:i");
    $footer_bufr = <<<EOT
        <div class='divider'>
            <p class="pull-right small-note"><a href='{sys_website}'>{sys_name} - ({sys_version})</a>  - created $createdate</p>
        </div>
EOT;


    // layout
    if ($params['pagination'])
    {
        $body = "";
        foreach ($fleet_block as $fleet_bufr)
        {
            $body.= $header_bufr.$event_bufr.$fleet_bufr.$code_bufr.$footer_bufr;
            $body.= "<div class='page-break'>&nbsp;</div>";
        }
        $htm = $doc_head_bufr.$body;
    }
    else
    {
        $body = "";
        foreach ($fleet_block as $fleet_bufr)
        {
            $body.=$fleet_bufr;
        }
        $htm = <<<EOT
        $doc_head_bufr
        $header_bufr
        $event_bufr
        $body
        $code_bufr
        $footer_bufr
EOT;
    }

    return $htm;
}



function format_result_codes($codes)
{
    // get codes into html bufr

//    $code_rows = "";
    $code_str = "";
    $count = 0;
    foreach ($codes as $key => $row) {
        $count++;
        if (empty($row['scoring']))
        {
            $scoring = "";
        }
        else
        {
            $scoring = strtr($row['scoring'], array("N" => "race competitors", "S" => "series competitors", "P" => "position"));
            $scoring = "[$scoring]";
        }

        $code_str.= "<b>{$row['code']}</b> - {$row['short']} <i>$scoring</i>";
        if ($count >= 3)
        {
            $code_str.= "<br>";
        }
        else
        {
            $code_str.= "&nbsp;&nbsp;|&nbsp;&nbsp;";
        }

    }
    $code_str = rtrim($code_str, "|");

    $bufr = <<<EOT
    <div class = "small-note" style="margin-top: 25px;"><b>Result Codes: </b><br>$code_str</div>
EOT;

    return $bufr;
}

function format_result_columns($scoring, $layout, $include_club)
{
    $cols = array(
        "class"   => "<th class='lightshade' width='%s%%'>class</th>",
        "sailnum" => "<th class='lightshade' width='%s%%'>no.</th>",
        "team"    => "<th class='lightshade' width='%s%%'>sailor</th>",
        "club"    => "<th class='lightshade' width='%s%%'>club</th>",
        "pn"      => "<th class='lightshade' width='%s%%'>PN</th>",
        "lap"     => "<th class='lightshade' width='%s%%'>laps</th>",
        "etime"   => "<th class='lightshade' width='%s%%'>elapsed</th>",
        "ctime"   => "<th class='lightshade' width='%s%%'>corrected</th>",
        "atime"   => "<th class='lightshade' width='%s%%'>corrected</th>",
        "result"  => "<th class='lightshade' width='%s%%'>position</th>",
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


function format_result_data($results, $scoring, $layout, $include_club)
{
    $cols = array(
        "class" => "<td class='text-left truncate'>{class}</td>",
        "sailnum" => "<td class='text-right'>{sailnum}</td>",
        "team" => "<td class='text-left truncate'>{team}</td>",
        "club" => "<td class='text-left truncate'>{club}</td>",
        "pn" => "<td class='text-center'>{pn}</td>",
        "lap" => "<td class='text-center'>{lap}</td>",
        "etime" => "<td class='text-right'>{etime}</td>",
        "ctime" => "<td class='text-right'>{ctime}</td>",
        "atime" => "<td class='text-right'>{atime}</td>",
        "result" => "<td class='text-center'>{result}</td>",
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


/*
function series_sheet($params = array(), $data)
{

    // FIXME
    //   - would be good to allow users to have a url to take them to the race results
    //   - needs a summary for missing races
    //   - would be nice to include race date
    //   - need to deal with issue of fleet being abandoned (a) event is abandoned or cancelled, b) no. of starters is < than cofigured threshold

    // END FIXME

//    $layout = array(
//        "level"    => "class,sailnum,team,club,etime,result",
//        "handicap" => "class,sailnum,team,club,pn,etime,ctime,result",
//        "average"  => "class,sailnum,team,club,pn,lap,etime,atime,result",
//        "pursuit"  => "class,sailnum,team,club,pn,result",
//    );

    $doc_head_bufr = file_get_contents($data['style']);

    // header
    $h_bufr = <<<EOT
    <!-- header -->
    <div class="title2 pull-right">{club_name} Results</div>
EOT;

    // event detail
    $e_bufr = <<<EOT
    <!-- event detail -->
    <div>
        <span class="title pull-left" style="width: 50%; display: inline-block;">{series_name}</span>
        <span class="pull-right text-alert" style="width: 45%; display: inline-block;">
            <a class="noprint" onclick="window.print()" href="#">Print results</a>
        </span>
    </div>
    <div class="pull-left" ><span class="text-alert">{series_notes}<br>{series_status}</span></div>
EOT;

    // format results for each fleet
    $fleet_block = array();
    foreach($data['fleet'] as $race=>$fleet)
    {
        // fleet detail
        $fleet_detail_bufr = <<<EOT
        <!-- fleet detail -->
        <div class="title2">{$fleet['fleet_name']}</div>
        <div class="divider clearfix"></div>
        <div class="pull-left" style="width:45%;">
            entries: <b>{$fleet['num_entries']}</b> | races sailed: <b>{races_complete} (of {races_inseries})</b> | discards: <b>{discards}</b>
        </div>
EOT;

        if (count($data["result"][$race]) > 0)
        {
            $fleet_results_bufr = format_series_columns($data['num_races'], $data['inc_club']);
            $fleet_results_bufr.= format_series_data($data["result"][$race], $fleet['scoring'], $data['inc_club']);

            // add layout for each fleet
            $fleet_block[$race] = <<<EOT
            $fleet_detail_bufr
            <table style="min-width: 70%" >
                $fleet_results_bufr
            </table>
EOT;
        }
        else
        {
            $fleet_block[$race] = <<<EOT
            $fleet_detail_bufr
            <div class="pull-center"><b>&hellip; no entries in this fleet &hellip;</b></div>
EOT;
        }
    }

    // codes list - including result code list if required)
    $codes_bufr = "";
    if ($data['add_codes'])
    {
        if ($data['inc_codes'] ) { $codes_bufr = format_result_codes($data['inc_codes']); }
    }

    // footer - including result code list if required)
    $f_bufr = "<div class='divider clearfix'></div>".
        "<p><a href='{sys_website}'>{sys_name} ({sys_version}) System:</a>  - created ".date("D j M y H:i")."</p>";

    // layout
    if ($data['pagination'])
    {
        $body = "";
        foreach ($fleet_block as $fleet_bufr)
        {
            $body.= $h_bufr.$e_bufr.$fleet_bufr.$code_bufr.$f_bufr;
            $body.= "<div class='page-break'>&nbsp;</div>";
        }
        $htm = $doc_head_bufr.$body;
    }
    else
    {
        $body = "";
        foreach ($fleet_block as $fleet_bufr)
        {
            $body.=$fleet_bufr;
        }
        $htm = <<<EOT
        $doc_head_bufr
        $h_bufr
        $e_bufr
        $body
        $code_bufr
        $f_bufr
EOT;
    }

    return $htm;
}
*/
function format_series_columns($num_races, $inc_club)
{
    $inc_club ? $club_col = "<th class='darkshade'>club</th>" : $club_col = "" ;

    $race_cols = "";
    for ($i=1; $i<$num_races; $i++)
    {
        $race_cols.= "<th class='darkshade'>R$i</th>";
    }

    $bufr = <<<EOT
    <thead>
        <tr>
            <th class='darkshade'>position</th>
            <th class='darkshade'>class</th>
            <th class='darkshade'>no.</th>
            <th class='darkshade'>competitor</th>
            $club_col
            $race_cols
            <th class='darkshade'>total pts</th>
            <th class='darkshade'>net pts</th>
            <th class='darkshade'>&nbsp;</th>
        </tr>
    </thead>
EOT;

    return $bufr;
}

function format_series_data($results, $num_races, $inc_club)
{
    $bufr = "<tbody>";
    foreach ($results as $data)
    {
        $inc_club ? $club_col = "<td class=''>club</td>" : $club_col = "" ;





        $bufr .= <<<EOT
        <tr>
            <td class=''>position</td>
            <td class=''>class</td>
            <td class=''>sailno</td>
            <td class=''>competitor</td>
            $club_col
            $race_cols
            <td class=''>points</td>
            <td class=''>net</td>
            <td class=''>&nbsp;</td>
        </tr>
EOT;
    }
    $bufr.= "<tbody>";
    return $bufr;
}

