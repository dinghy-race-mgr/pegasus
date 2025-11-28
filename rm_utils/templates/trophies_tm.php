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
    
        {page-title}
        
        {page-main}
             
        {page-footer}
    
    </body>
    </html>

EOT;

    return $htm;
}
function trophy_search_form($params = array())
{
    // not currently used, retained for future use
    $start_year = $params['start_year'];
    $end_year = $params['end_year'];

    // set up dropdown for full year prize lists
    $year_options = "";
    foreach($params['periods'] as $period)
    {
        $year_options.= "<li><a class='dropdown-item' 
        href='./trophy_winners_display.php?pagestate=submit&period={$period['period']}&report_style=slate_' 
        target='_BLANK'>{$period['period']}</a></li>";
    }

    // create datalist for all trophies
    $trophylist = "";
    foreach($params["trophies"] as $trophy){
        if(strtolower($trophy["sname"]) == "glassware"){
            continue;
        }
        else{
            $trophylist.= "<option value='{$trophy["name"]}'><b>{$trophy["name"]}</b></option>";
        }
    }

    // create error report if required
    $err_txt = "";
    foreach ($params["error"] as $k => $error){
        if ($error)
        {
            if ($k == 1){$err_txt.= "&nbsp;&nbsp; the start period cannot be later than end period</br>";}
            if ($k == 2){$err_txt.= "&nbsp;&nbsp; please provide either trophy name or person's name</br>";}
        }
    }

    if (empty($err_txt)){
        $error_html = "";
    }
    else {
        $error_html = <<<EOT
        <div class="row">
            <div class="col-3">&nbsp;</div>
            <div class="col-6 alert alert-danger lead" role="alert"><i class="bi bi-exclamation-triangle-fill">&nbsp;</i>$err_txt</div>
        </div>
EOT;
    }


    $bufr = "";
    $bufr.= <<<EOT
    <div class="container-fluid">
        <div class="row">
            <div class="col-2">&nbsp;</div>
            <div class="col-7 text-info lead">{instructions}</div>
        </div>
        <div class="row mt-2">
            <div class="col-2">&nbsp;</div>
            <div class="col-7">
                <p class="lead">{lower-instructions}</p>
            </div>
        </div>
       
        <form class="" enctype="multipart/form-data" id="trophysearchform" action="{script}" method="post">
                    
        <div class="row g-5 align-items-center">
            <div class="col-2">&nbsp;</div>
            <div class="col-3">
                     <div class="form-floating">          
                        <input class="form-control form-control-lg" list="trophyopts" id="trophy" name="trophy" placeholder="" value="" autofocus />
                        <datalist id="trophyopts">$trophylist</datalist>
                        <label for="trophy" class="label-style" style="color: darkslategray; ! important">Trophy Name</label>                       
                        <!-- div class="text-primary mx-5">start typing  &hellip;</div-->          
                    </div>                  
            </div>
            
            <div class="col-1"><b>AND / OR</b></div>
            
            <div class="col-3">
                <div class="form-floating">          
                    <input class="form-control form-control-lg" type="text" id="class" name="person" placeholder="Person (First Last preferred)" value=""/>
                    <label for="floatingInput" class="label-style" style="color: darkslategray; ! important">Person Name (e,g Ben Ainslie)</label>                           
                </div>  
            </div>
        </div>
        
        <input type="hidden" id="start_year" name="start_year" value="$start_year"/>
        <input type="hidden" id="end_year" name="end_year" value="$end_year"/>
        <!-- div class="row g-5 align-items-center mt-3" >
            <div class="col-1">&nbsp;</div>
            <div class="col-3 ms-5">
                    <input type="hidden" id="start_year" name="start_year" value="$start_year"/>
                    <select class="form-control" name="start_year">
                    </select>               
            </div>
            <div class="col-2"><b>- - to - -</b></div>
            <div class="col-3">
                    <input type="hidden" id="end_year" name="end_year" value="$end_year"/>
                    <select class="form-control" name="end_year">
                    </select>               
            </div>
        </div -->      
        
        <!-- buttons -->
        <div class="row g-5 align-items-center mt-3" >
            <div class="col-2">&nbsp;</div>
            <div class="col-6">
                <div class="text-end">             
                    <button class="btn btn-lg btn-info dropdown-toggle" style="min-width: 250px;" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-file-earmark-text-fill">&nbsp;</i><b>Whole Year</b></button>
                    <ul class="dropdown-menu">$year_options</ul>
                              
                    <a class="btn btn-lg btn-warning ms-3" style="min-width: 250px;" type="button" name="Quit" id="Quit" onclick="return quitBox('quit');">
                    <i class="bi bi-arrow-left-square">&nbsp;</i>&nbsp;<b>Back to Results</b></a>

                    <button type="submit" class="btn btn-lg btn-success ms-3"  style="min-width: 250px;" >
                    <i class="bi bi-search">&nbsp;&nbsp;</i><b>Search</b></button>
                </div>
            </div>
        </div>
        </form>
                
        <div class="mt-5">
            $error_html
        </div>
        
    </div>

EOT;

    return $bufr;
}

function trophy_search_results($params = array())
{
    //create no results found message
    $noresults_html = "";
    if($params['data_num'] <= 0)
    {
        $noresults_html = <<<EOT
        <div class="row mt-5">
            <div class="col-3">&nbsp;</div>
            <div class="col-6 alert alert-danger lead" role="alert">
            <i class="bi bi-exclamation-triangle-fill">&nbsp;</i>
            <span style="font-size:1.2em"><b>Sorry</b></span>&nbsp;&nbsp;&nbsp;&nbsp;no data found to match your search - please try again.</div>
        </div>
EOT;
    }

    // format table with data
    $rows_htm = "";
    foreach ($params['data'] as $data)
    {
        $rows_htm.= <<<EOT
        <tr>
          <td>{$data["place"]}</td>
          <td>{$data["period"]}</td>
          <td><b>{$data["team"]}</b></td>
          <td>{$data["trophy"]}</td>
          <td>{$data["category"]}</td>
          <td>{$data["division"]}</td>
          <td>{$data["boat"]}</td>
        </tr>
EOT;
    }

    $bufr = <<<EOT
<div class="container container-fluid" style="margin-top: 20px;">
    <hr class="divider-line" style="background-color: steelblue;"> 
        $noresults_html
    <table class="table table-hover">
        <tbody>
        $rows_htm
        </tbody>
    </table>
    </div>
EOT;
    return $bufr;
}

function trophies_display_form($params = array())
{
    $bufr = "";

    $select_opts = "";
    foreach($params['periods'] as $period) {
        $select_opts.= "<option value='{$period['period']}'>{$period['period']}</option>";
    }

    $bufr.= <<<EOT
    <div class="container" style="margin-top: 40px;">
        <div class="col-md-8">
            <div class="p-3 text-primary-emphasis bg-primary-subtle border border-primary-subtle rounded-3">
                <!--h3>{function}</h3-->
                <p class="lead">{instructions}</p>
                <!-- div class="text-end">
                    <a class="btn btn-info float-right" href="./documents/RM_EVENT_documentation.pdf" target="_BLANK" type="button" role="button">get help ...</a>
                </div -->               
            </div>
        </div>

        <form class="row g-3" enctype="multipart/form-data" id="trophyForm" action="{script}" method="post">
        
            <!-- period argument -->           
            <div class="row g-5 align-items-center">
                <div class="col-auto">
                    <label for="period" class="col-form-label"><strong>Report Period</strong></label>
                </div>
                <div class="col-auto">
                    <select class="form-select mb-3" id="period" name="period">
                      $select_opts
                    </select>
                </div>
                <div class="col-auto">
                    <span id="periodHelpInline" class="form-text">Select period you want to be represented in your report</span>
                </div>
            </div>
                                
            <!-- style argument-->
            <div class="row g-5 align-items-center">
                <div class="col-auto">
                    <label for="report_style"class="col-form-label"><strong>Report Style</strong></label>
                </div>
                <div class="col-auto">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="report_style" value="standard" id="report_style1" checked>
                        <label class="form-check-label" for="report_style1">Standard</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="report_style" value="cerulean_" id="report_style2">
                        <label class="form-check-label" for="report_style2">Cerulean</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="report_style" value="flatly_" id="report_style3">
                        <label class="form-check-label" for="report_style3">Flatly</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="report_style" value="united_"id="report_style4" checked>
                        <label class="form-check-label" for="report_style4">United</label>
                    </div>
                </div>
                <div class="col-auto">
                    <span id="styleHelpInline" class="form-text">Select format for your report</span>
                </div>
            </div>           

            <!-- buttons -->
            <div class="mt-5">
                    <div class="text-end">
                        <a class="btn btn-lg btn-warning mx-5" style="min-width: 200px;" type="button" name="Quit" id="Quit" onclick="return quitBox('quit');">
                        <span class="glyphicon glyphicon-remove"></span>&nbsp;&nbsp;<b>Cancel</b></a>

                        <button type="submit" class="btn btn-lg btn-primary mx-5"  style="min-width: 200px;" >
                        <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;&nbsp;<b>Create Report</b></button>
                    </div>
            </div>
            
        </form>
    </div>
    <script language="javascript">
    function quitBox(cmd)
    {   
        if (cmd=='quit') { open(location, '_self').close(); }   
        return false;   
    }
    </script>
EOT;
    return $bufr;

}

function trophies_presentation_list_form($params = array())
{
    $bufr = "";

    $select_opts = "";
    foreach($params['periods'] as $period) {
        $select_opts.= "<option value='{$period['period']}'>{$period['period']}</option>";
    }

    $bufr.= <<<EOT
    <div class="container" style="margin-top: 40px;">
        <div class="col-md-8">
            <div class="p-3 text-primary-emphasis bg-primary-subtle border border-primary-subtle rounded-3">
                <!--h3>{function}</h3-->
                <p class="lead">{instructions}</p>
                <!-- div class="text-end">
                    <a class="btn btn-info float-right" href="./documents/RM_EVENT_documentation.pdf" target="_BLANK" type="button" role="button">get help ...</a>
                </div -->               
            </div>
        </div>

        <form class="row g-3" enctype="multipart/form-data" id="trophyForm" action="{script}" method="post">
        
            <!-- period argument -->           
            <div class="row g-5 align-items-center">
                <div class="col-auto">
                    <label for="period" class="col-form-label"><strong>Report Period</strong></label>
                </div>
                <div class="col-auto">
                    <select class="form-select mb-3" id="period" name="period">
                      $select_opts
                    </select>
                </div>
                <div class="col-auto">
                    <span id="periodHelpInline" class="form-text">Select period you want to be represented in your report</span>
                </div>
            </div>
                                
            <!-- style argument-->
            <!--div class="row g-5 align-items-center">
                <div class="col-auto">
                    <label for="report_style"class="col-form-label"><strong>Report Style</strong></label>
                </div>
                <div class="col-auto">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="report_style" value="standard" id="report_style1" checked>
                        <label class="form-check-label" for="report_style1">Standard</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="report_style" value="cerulean_" id="report_style2">
                        <label class="form-check-label" for="report_style2">Cerulean</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="report_style" value="flatly_" id="report_style3">
                        <label class="form-check-label" for="report_style3">Flatly</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="report_style" value="united_"id="report_style4" checked>
                        <label class="form-check-label" for="report_style4">United</label>
                    </div>
                </div>
                <div class="col-auto">
                    <span id="styleHelpInline" class="form-text">Select format for your report</span>
                </div>
            </div -->           

            <!-- buttons -->
            <div class="mt-5">
                    <div class="text-end">
                        <a class="btn btn-lg btn-warning mx-5" style="min-width: 200px;" type="button" name="Quit" id="Quit" onclick="return quitBox('quit');">
                        <span class="glyphicon glyphicon-remove"></span>&nbsp;&nbsp;<b>Cancel</b></a>

                        <button type="submit" class="btn btn-lg btn-primary mx-5"  style="min-width: 200px;" >
                        <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;&nbsp;<b>Create List</b></button>
                    </div>
            </div>
            
        </form>
    </div>
    <script language="javascript">
    function quitBox(cmd)
    {   
        if (cmd=='quit') { open(location, '_self').close(); }   
        return false;   
    }
    </script>
EOT;
    return $bufr;

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

function trophy_presentation_list($params = array())
{
    $data = $params['data'];
    //echo "<pre>".print_r($data,true)."</pre>";

    // loop over each trophy/winners - creating an array for each winner with all the trophies they won
    $tbufr = "";        // temporary buffer
    $i = 0;             // counter for records procesed
    $j = 0;             // counter for records won by winner
    $winner = "";       // initial winner value
    $name = "";         // initial winner name
    $names = array();   // output data array with winners name, count of no. of trophies, and html for trophies won
    foreach ($data as $k=>$row)
    {
        $i++;

        if ($row['sortstr'] != $winner or $i == 1)     // we have a change of winner
        {
            if ($i != 1 )  // set previous name and count
            {
                $names[$name]['name']  = $name;
                $names[$name]['htm']   = $tbufr;
                $names[$name]['count'] = $j;
            }
            //echo "<pre>{$names[$name]['name']} {$names[$name]['count']}</pre>";

            // start new person
            $j=0;
            $tbufr = "";
            $tbufr.= "<tr><td><b>{$row['helm']}</b></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
        }
        $tbufr.= <<<EOT
        <tr>
            <td style="padding-left: 20px;">{$row['event']}</td>
            <td>{$row['trophy']}</td>
            <td>{$row['posn']}</td>
            <td>{$row['helm']} / {$row['crew']}</td>
            <td>{$row['boat']} / {$row['number']}</td>           
        </tr>          
EOT;
        $winner = $row['sortstr'];
        $name = $row['sortstr'];
        $j++;
    }
    // deal with final name
    $names[$name]['name']  = $name;
    $names[$name]['htm']   = $tbufr;
    $names[$name]['count'] = $j;


    // sort names array by the number of trophies won
    $count  = array_column($names, 'count');
    array_multisort($count, SORT_ASC, $names);

    // create html buffer with winners data - in order of no. of trophies won
    $tbufr = "";
    foreach ($names as $name) { $tbufr.=$name['htm']; }

    $bufr = <<<EOT
    <div>
    <table class="table table-condensed">
        $tbufr
    </table>
    </div>
EOT;

    return $bufr;
}

function get_section_header($row, $cfg)
{
    $htm = "<div class='ps-2'><h4 class='text-info'>".ucwords($cfg['heading']).
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

    $bufr = <<<EOT
    <div class="container text-center">
      <div class="row justify-content-md-center">
        
        <div class="col-lg-6">
          <div class="alert alert-warning" role="alert">
            Sorry - we seem to have a system problem. Please contact the system administrator.
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