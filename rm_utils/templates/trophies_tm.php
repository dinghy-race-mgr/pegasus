<?php
function print_page($params = array())
{

    $htm = <<<EOT
    <!doctype html>
    <html lang="en" class="h-100">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="Mark Elkington">
        <title>{tab-title}</title>
    
        <!-- Bootstrap core CSS -->
        <link href="../common/oss/bootstrap532/css/{page-theme}bootstrap.min.css" rel="stylesheet">
        <link href="../common/oss/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        
        <!-- Bootstrap core js -->
        <script src="../common/oss/bootstrap532/js/bootstrap.bundle.min.js"></script>      
    
        <!-- Custom styles for this template -->
        <link href="./style/rm_trophy.css" rel="stylesheet">
    </head>
    <body class="d-flex flex-column h-100">
    
        {page-title}
        
        {page-main}
        
        {page-footer}
    
    </body>
    </html>

EOT;

    return $htm;
}


function trophy_display_content($params = array())
{
    $data = $params['data'];
    $section_cfg = $params['section'];

    //echo "<pre>".count($data)." winners and ".count($section_cfg)." config</pre>";

    // loop over each trophy/winners - with a section header as required
    $new_section = "";
    $bufr = "<h2>SYC Trophy Winners - XXXXXX</h2>";
    $i = 0;
    foreach ($data as $k=>$row)
    {
        $i++;

        if ($new_section != $row['group_sort'])      // new section
        {
            $bufr.= get_section_header($row, $section_cfg["{$row['group_sort']}"]);
            $new_section = $row['group_sort'];
        }

        $bufr.= get_trophy_report ($row, $section_cfg["{$row['group_sort']}"]);
    }

    return $bufr;
}

function get_section_header($row, $cfg)
{
    $htm = "<div><h4>".ucwords($cfg['heading'])."<span style='font-size: 0.5em'>".ucfirst($cfg['description'])."</span></h4></div>";

    return $htm;
}

function get_trophy_report($row, $cfg)
{
    // table field widths
    $col1 = "50px";
    $col2 = "200px";
    $col3 = "400px";

    $winners = "";
    for ($i = 1; $i<= $cfg['num_winners']; $i++)
    {
        $arr = $row["winner_".$i."_arr"];
        if ($arr['exists'])
        {
            $boat = "{$arr['boat']} {$arr['number']}";
            $crew = "{$arr['helm']} / {$arr['crew']}";
            $crew = rtrim($crew, '/ ');
            $winners.= "<tr><td width='$col1'>{$arr['posn']}</td><td width='$col2'>$boat</td><td width='$col3'>$crew</td></tr>";
        }
    }

    $award = "{$row['award_category']} / {$row['award_division']}";
    $award = rtrim($award, '\ ');
    $htm = <<<EOT
    <div>
    <table class="table table-condensed">
    <tr>
        <td width='25%'><b>{$row['name']}</b></td>
        <td width='25%'>$award</td>
        <td width='50%'><table>$winners</table></td>  
        <td>&nbsp;</td>      
    </tr>
    </table>
    </div>
EOT;
    return $htm;
}