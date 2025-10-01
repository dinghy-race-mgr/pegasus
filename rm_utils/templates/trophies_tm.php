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
        <link href="./style/rm_utils.css" rel="stylesheet">
    </head>
    <body class="d-flex flex-column h-100">
    
        <h2>{page-title}</h2>
        
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

    // loop over each trophy/winners - with a section header as required
    $new_section = "";
    $bufr = "";
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
    $htm = "<div><h4 class='text-primary'>".ucwords($cfg['heading']).
           "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style='font-size: 0.7em; font-style: italic'>- ".
           ucfirst($cfg['description'])."</span></h4></div>";

    return $htm;
}

function get_trophy_report($row, $cfg)
{
    // table field widths
    $col1 = "50px";
    $col2 = "200px";
    $col3 = "400px";

    empty($row['sname']) ? $trophy_name = $row['name'] : $trophy_name = $row['sname'];

    if (strtolower($row['group_sort']) == "achievement"
        or strtolower($row['group_sort']) == "junior_training"
        or strtolower($row['group_sort']) == "junior_regatta")
    {
        $award = $row['allocation_notes'];
    }
    else
    {
        $award = "{$row['award_category']} / {$row['award_division']}";
        $award = rtrim($award, '\ ');
    }

    // check if we only have one winner
    $only_1 = false;
    if (!empty($row["winner_1"]) and empty($row["winner_2"]) and empty($row["winner_3"]))
    {
        $only_1 = true;
    }
    $winners = "";
    for ($i = 1; $i<= $cfg['num_winners']; $i++)
    {
        $arr = $row["winner_".$i."_arr"];
        if ($arr['exists'])
        {
            $boat = "{$arr['boat']} {$arr['number']}";
            $crew = "{$arr['helm']} / {$arr['crew']}";
            $crew = rtrim($crew, '/ ');

            $only_1 ? $posn = "&nbsp;": $posn = $arr['posn'];

            $winners.= "<tr><td width='$col1'>$posn</td><td width='$col2'>$boat</td><td width='$col3'>$crew</td></tr>";
        }
    }


    $htm = <<<EOT
    <div>
    <table class="table table-condensed">
    <tr>
        <td width='20%'><b>$trophy_name</b></td>
        <td width='30%'>$award</td>
        <td width='50%'><table>$winners</table></td>  
        <td>&nbsp;</td>      
    </tr>
    </table>
    </div>
EOT;
    return $htm;
}

function trophies_error($params = array())
{

//    if ($params['state'] == 1)
//    {
//        $bufr = <<<EOT
//        <div class="alert alert-warning" role="alert"><h3>Problem!</h3> <h4>error state not recognised</h4>
//EOT;
//    }
//    elseif ($params['state'] == 2)
//    {
//        $bufr = <<<EOT
//        <div class="alert alert-danger" role="alert"><h3>Failed!</h3> <h4> page status not recognised - please contact System Manager </h4>
//EOT;
//    }
//    else
//    {
//        $bufr = <<<EOT
//        <div class="alert alert-warning" role="alert"><h3>Warning!</h3> <h4> Unrecognised completion state - please contact System Manager </h4>
//EOT;
//   }

    // add button into div
    $bufr = <<<EOT
    <div class="container text-center">
      <div class="row justify-content-md-center">
        
        <div class="col-lg-6">
          <div class="alert alert-warning" role="alert">
            A simple warning alert—check it out!
          </div>
        </div>
        
      </div>
    </div>
   
    <script language="javascript">
    function quitBox(cmd)
    {   
        if (cmd=='quit')
        {
            open(location, '_self').close();
        }   
        return false;   
    }
    </script>
EOT;

    return $bufr;
}