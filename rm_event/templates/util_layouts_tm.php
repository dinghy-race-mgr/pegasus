<?php
/*
 *   Layouts for rm_event utilities
 */

function utils_page ($params = array())
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
    
        <link rel="canonical" href="https://getbootstrap.com/docs/5.0/examples/sticky-footer-navbar/">
    
        <!-- Bootstrap core CSS -->
        <link href="../common/oss/bootstrap532/css/{styletheme}bootstrap.min.css" rel="stylesheet">
        <link href="../common/oss/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        
        <!-- Bootstrap core js -->
        <script src="../common/oss/bootstrap532/js/bootstrap.bundle.min.js"></script>      
    
        <!-- Custom styles for this template -->
        <link href="{stylesheet}" rel="stylesheet">
    </head>
    <body class="d-flex flex-column h-100">
       
        {page-navbar}
        
        {page-main}
        
        {page-footer}
        
        {page-modals}
               
        {page-js}
        
        <script>
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        </script>
    
    </body>
    </html>

EOT;

    return $htm;
}

function problem_report ($params = array())
{
    $more = "";
    if ($params['info'])
    {
        $more = <<<EOT
<p >{data}</p>
EOT;
    }

    $bufr = <<<EOT
    <div class="container" style="margin-top: 40px;">
        <div class="col-md-8">
            <div class="h-100 p-5 bg-dark-subtle border border-warning border-3 rounded-3">
                <h3>Oops we have a problem ...</h3>
                <p class="lead">{problem}</p>
                $more
                <blockquote class="blockquote border-start border-dark border-5" style="--bs-border-opacity: .5;">
                    <p class="ps-3">{action}</p>
                </blockquote>
                <div class="text-end">
                    <a class="btn btn-warning float-right" type="button" name="Quit" id="Quit" onclick="return quitBox('quit');"><span class="glyphicon glyphicon-remove">Quit</a>
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

function error_report ($params = array())
{
    empty($params['event-title']) ? $title = "<br><br>": $title = "<p class='display-5 text-info mb-5'><b>{event-title}</b></p>";
    empty($params['action']) ? $action = "" : $action = "<br><br>You could try ... ".$params['action'];

    $htm = <<<EOT
    <main class="" >
        <div class="container nav-margin">
            $title           
            <div class="p-5 mb-4 text-bg-secondary border border-danger border-5 rounded-3">
              <div class="container-fluid py-5">
                <h1 class="display-6 fw-bold">Oops ... we have a problem</h1>
                <p class="col-md-10 fs-5">Something has gone wrong - {problem} $action</p>        
                <p class="text-end"><b>debug detail:-</b> <i>file</i>: {file} <i>line</i>: {line} <i>data</i>: {evidence} ]</p>
              </div>
            </div>           
        </div>    
    </main>
EOT;

    return $htm;
}

function navbar_utils ($params = array())
{
    $htm = <<<EOT
    <header>
        <nav class="navbar bg-primary navbar-expand-lg bg-body-tertiary" data-bs-theme="dark">
            <div class="container-fluid">
                <a class="navbar-brand fs-2" href="#">{util-name}</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" 
                               aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarText">
                    <div class="col text-end">
                        <span class="navbar-text fs-5 ">raceManager {version}</span><br>
                        <span class="navbar-text fs-6"> &copy; Elmswood Software {year}</span>
                    </div>
                </div>
           </div>
        </nav>
    </header>
EOT;

    return $htm;
}

function footer_utils ($params = array())
{
        $htm = "";
        if ($params['footer'])
        {
            $htm = <<<EOT
        <footer class="footer-dark footer-shadow-dark p-5">
            <div class="container-fluid px-4 py-3 text-bg-secondary d-flex align-items-baseline">
                <div class="row col-12">         
                    <div class="col md-4 text-success fs-4 text-start">
                        <span class="align-top">{footer-left}</span>
                    </div>
                    <div class="col md-4 text-center fs-4">
                        <span class="align-top">{footer-center}</span>
                    </div>
                    <div class="col md-4 text-end text-success-subtle fs-4">
                        <span class="align-top">{footer-right}</span>
                    </div>                   
                </div>
            </div> 
        </footer>
EOT;
        }
    // FIXME - get msg and close button back button working
    return $htm;
}

function format_entry_counts($arr, $field)
{
    $str = false;
    if ($arr)
    {
        $str = "";
        foreach ($arr as $row)
        {
            $str.= $row[$field].": ".$row['number']." , ";
        }
        $str = rtrim($str,", ");
    }
    return $str;
}

function reception_rept ($params = array())
{
    // title details
    $timestamp = date("d-M-Y H:i");
    $title = "{event-title}";

    // format counts
    $entry_count = "TOTAL ENTRIES : - ".count($params['entries']);
    $count_str = format_entry_counts($params['counts']['class'], "b-class");
    empty($count_str) ? $class_count = "": $class_count = "CLASSES :- $count_str";
    $count_str = format_entry_counts($params['counts']['fleet'], "b-fleet");
    empty($count_str) ? $fleet_count = "": $fleet_count = "FLEETS :- $count_str";
    $count_str = format_entry_counts($params['counts']['division'], "b-division");
    empty($count_str) ? $group_count = "": $group_count = "GROUPS :- $count_str";

    // table columns
    $cols = <<<EOT
<thead><tr>
    <th width="12%">Boat</th>
    <th width="20%">Crew</th>
    <th width="12%">Club</th>
    <th width="12%">Fleet/Group</th>
    <th width="28%">Entry Changes</th>
    <th width="8%">Paid</th>
    <th width="5%">Tally</th>
</tr></thead>
EOT;

    // table rows
    $rows = "";
    foreach ($params['entries'] as $key => $data)
    {
        $helmname = $data['h-name'];
        if (!empty($data['h-age']) and $data['h-age'] < 18) { $helmname = $helmname . " [J]";}

        $crewname = $data['c-name'];
        if (!empty($data['c-age']) and $data['c-age'] < 18) { $crewname = $crewname . " [J]"; }

        empty($data['c-name']) ? $team = ucwords($helmname) : $team = ucwords($helmname) . "<br>" . ucwords($crewname);

        empty($data['b-fleet']) ? $fleet_div = $data['b-division'] : $fleet_div = $data['b-fleet'] . "<br>" . $data['b-division'];

        $rows .= <<<EOT
<tr>
    <td width="">{$data['b-class']} {$data['b-sailno']}<br>&nbsp;</td>
    <td>$team</td>
    <td>{$data['h-club']}</td>
    <td>$fleet_div</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td> 
    <td>{$data['e-tally']}</td>  
</tr>
EOT;
    }

    // render report
    $htm = <<<EOT
    <main class="" >
        <div class="container-fluid">
            <p class='fs-3'>
                $title<br>
                <span class="fs-6">[created at $timestamp]</span> 
            </p>
               
            <div >   
                <table class="table table-success table-striped table-hover table-bordered" style="padding-left: 30px; width: 95%">
                    $cols
                    $rows           
                </table>
                <div>
                    <p>$entry_count</p>  
                    <p>$class_count</p>
                    <p>$fleet_count</p>  
                    <p>$group_count</p>
                </div> 
                
            </div>   
        </div>    
    </main>

EOT;

    return $htm;
}

function entrycheck_rept ($params = array())
{
    // title details
    $timestamp = date("d-M-Y H:i");
    $title = "{event-title}";

    // table columns
    $cols = <<<EOT
<thead><tr>
    <th width="10%">Boat</th>
    <th width="15%">Crew</th>
    <th width="10%">Club</th>
    <th width="10%">Fleet / Group</th>
    <th width="25%">Checks</th>
    <th width="5%">Known Class</th>
    <th width="6%">Known Comp.</th>   
</tr></thead>
EOT;

    // table rows
    $rows = "";
    foreach ($params['entries'] as $key => $data)
    {
        $helmname = $data['h-name'];
        if (!empty($data['h-age']) and $data['h-age'] < 18) { $helmname = $helmname." [J]"; }

        $crewname = $data['c-name'];
        if (!empty($data['c-age']) and $data['c-age'] < 18) { $crewname = $crewname." [J]"; }

        empty($data['c-name']) ? $team = ucwords($helmname) : $team = ucwords($helmname)."<br>".ucwords($crewname);

        if ($data['rm_class'])
        {
            $known_class = "<i class='bi bi-x-square-fill' style='font-size: 2rem; color: red;'></i>";
        }
        else
        {
            $known_class = "<i class='bi bi-check-square-fill' style='font-size: 2rem; color: green;'></i>";
        }

        if ($data['rm_comp'])
        {
            $known_comp = "<i class='bi bi-x-square-fill' style='font-size: 2rem; color: red;'></i>";
        }
        else
        {
            $known_comp = "<i class='bi bi-check-square-fill' style='font-size: 2rem; color: green;'></i>";
        }

        empty($data['b-fleet']) ? $fleet_div = $data['b-division'] : $fleet_div = $data['b-fleet']."<br>".$data['b-division'];

        $checks = "no checks made";
        if ($params['checks'])
        {
            $checks = "";
            for ($i = 1; $i <= 6; $i++)
            {
                if ($data["chk$i"]) { $checks.= $data["chk$i"]." | "; }
            }
            $checks= rtrim($checks, "| ");
        }

        $rows.= <<<EOT
<tr>
    <td width="">{$data['b-class']} {$data['b-sailno']}<br>&nbsp;</td>
    <td>$team</td>
    <td>{$data['h-club']}</td>
    <td>$fleet_div</td>
    <td>$checks</td>  
    <td>$known_class</td> 
    <td>$known_comp</td> 
</tr>
EOT;
    }

    $key = count($params['entries'])." entries <br>{key}";

    $htm = <<<EOT
    <main class="" >
        <div class="container-fluid">
            <p class='fs-3'>
                $title<br>
                <span class="fs-6">[created at $timestamp]</span> 
            </p>
               
            <div >   
                <table class="table table-success table-striped table-hover table-bordered" style="padding-left: 30px; width: 95%">
                    $cols
                    $rows           
                </table> 
            </div>   
        </div>    
    </main>

EOT;

    return $htm;
}


function tallylist_rept ($params = array())
{
//    echo "<pre>".print_r($params['counts'],true)."</pre>";
//    exit();

    // title details
    $timestamp = date("d-M-Y H:i");
    $title = "{event-title}";

    // format counts
    $entry_count = "TOTAL ENTRIES : - ".count($params['entries']);
    $count_str = format_entry_counts($params['counts']['class'], "b-class");
    empty($count_str) ? $class_count = "": $class_count = "CLASSES :- $count_str";
    $count_str = format_entry_counts($params['counts']['fleet'], "b-fleet");
    empty($count_str) ? $fleet_count = "": $fleet_count = "FLEETS :- $count_str";
    $count_str = format_entry_counts($params['counts']['division'], "b-division");
    empty($count_str) ? $group_count = "": $group_count = "GROUPS :- $count_str";

    // table columns
    $cols = <<<EOT
<thead><tr>
    <th width="5%">Tally</th>
    <th width="12%">Boat / Sail No.</th>
    <th width="20%">Helm / Crew</th>
    <th width="12%">Club</th>
    <th width="12%">Fleet / Group</th>  
</tr></thead>
EOT;

    // table rows
    $rows = "";
    foreach ($params['entries'] as $key => $data)
    {
        $helmname = $data['h-name'];
        if (!empty($data['h-age']) and $data['h-age'] < 18) { $helmname = $helmname . " [J]";}

        $crewname = $data['c-name'];
        if (!empty($data['c-age']) and $data['c-age'] < 18) { $crewname = $crewname . " [J]"; }

        empty($data['c-name']) ? $team = ucwords($helmname) : $team = ucwords($helmname) . " / " . ucwords($crewname);

        empty($data['b-fleet']) ? $fleet_div = $data['b-division'] : $fleet_div = $data['b-fleet'] . "    " . $data['b-division'];

        $rows .= <<<EOT
<tr>
    <td class='fs-4'>{$data['e-tally']}</td> 
    <td>{$data['b-class']} {$data['b-sailno']}</td>
    <td>$team</td>
    <td>{$data['h-club']}</td>
    <td>$fleet_div</td>
</tr>
EOT;
    }

    // render report
    $htm = <<<EOT
    <main class="" >
        <div class="container-fluid">
            <p class='fs-3'>
                $title<br>
                <span class="fs-6">[created at $timestamp]</span> 
            </p>
               
            <div >   
                <table class="table table-success table-hover table-bordered" style="padding-left: 30px; width: 95%">
                    $cols
                    $rows           
                </table>
                <div>
                    <p>$entry_count</p>  
                </div>               
            </div>   
        </div>    
    </main>

EOT;

    return $htm;
}



function fleetlist_rept ($params = array())
{
    // title details
    $timestamp = date("d-M-Y H:i");
    $title = "{event-title}";

    // format counts
    $entry_count = "TOTAL ENTRIES : - ".count($params['entries']);
    $count_str = format_entry_counts($params['counts']['class'], "b-class");
    empty($count_str) ? $class_count = "": $class_count = "CLASSES :- $count_str";
    $count_str = format_entry_counts($params['counts']['fleet'], "b-fleet");
    empty($count_str) ? $fleet_count = "": $fleet_count = "FLEETS :- $count_str";

    // table columns
    $cols = <<<EOT
<thead><tr>
    <th width="12%">Fleet</th>
    <th width="12%">Boat</th>
    <th width="20%">Crew</th>
    <th width="12%">Club</th>    
    <th width="5%">Tally</th>
</tr></thead>
EOT;

    // table rows
    $rows = "";
    $fleet_div = "";
    foreach ($params['entries'] as $key => $data)
    {
        if ($fleet_div != ucwords(strtolower($data['b-fleet'])))  // add blank row divider
        {
            $fleet_div = ucwords(strtolower($data['b-fleet']));
            $rows .= <<<EOT
<tr>
    <td colspan="5" class="text-center fs-4"><b>$fleet_div</b></td>  
</tr>
EOT;
        }

        $helmname = $data['h-name'];
        if (!empty($data['h-age']) and $data['h-age'] < 18) { $helmname = $helmname . " [J]"; }

        $crewname = $data['c-name'];
        if (!empty($data['c-age']) and $data['c-age'] < 18) { $crewname = $crewname . " [J]"; }

        empty($data['c-name']) ? $team = ucwords($helmname) : $team = ucwords($helmname) . " / " . ucwords($crewname);

        //empty($data['b-fleet']) ? $fleet_div = $data['b-division'] : $fleet_div = $data['b-fleet'] . "<br>" . $data['b-division'];
        $fleet_div = ucwords(strtolower($data['b-fleet']));

        $rows .= <<<EOT
<tr>
    <td>$fleet_div</td>
    <td>{$data['b-class']} {$data['b-sailno']}</td>
    <td>$team</td>
    <td>{$data['h-club']}</td>
    <td>{$data['e-tally']}</td>  
</tr>
EOT;
    }

    // render report
    $htm = <<<EOT
    <main class="" >
        <div class="container-fluid">
            <p class='fs-3'>
                $title<br>
                <span class="fs-6">[created at $timestamp]</span> 
            </p>
               
            <div >   
                <table class="table table-success table-striped table-hover table-bordered" style="padding-left: 30px; width: 95%">
                    $cols
                    $rows           
                </table>
                <div>
                    <p>$entry_count</p>  
                    <p>$class_count</p>
                    <p>$fleet_count</p>  
                </div>                
            </div>   
        </div>    
    </main>

EOT;

    return $htm;
}


function set_tally_info($params = array())
{
    $button = "";
    $report = "";
    if ($params['state'] == "instructions")
    {
        $button = "<a href='./rmu_set_tally.php?eid={$params['eid']}&pagestate=submit' class='btn btn-warning btn-lg' 
                      type='button' target='_parent'>
                      Set Tallies
                   </a>";
    }
    elseif ($params['state'] == "results")
    {
        $button = "<a href='./rmu_entries_reports.php?eid={$params['eid']}&output=tallylist' class='btn btn-info btn-lg'
                      type='button' target='_blank'>
                      Print Tally List
                   </a>";
        $report = "<p class='lead'>{$params['count']} tallies allocated</p>";
    }


    $htm = <<<EOT
    <div class="container d-flex align-items-center justify-content-center">
    <div class="col-8 justify-content-center mt-4 p-5 bg-primary text-white rounded">
        <h2>Adding tally numbers to entry list&hellip;</h2>
        <p>This should be done when entries have closed - <u>typically the day before the event</u>.  Tallies will be 
           allocated to confirmed entries sorted on class name, and sail number.  Tallies for boats that enter at 
           the event or are introduced from the waiting list should be allocated at reception</p>
        <p>Once the tally numbers have been allocated you will be able to print a tally list.</p>
        $report
        $button
    </div>
    </div>
EOT;


    return $htm;
}


function rm_export_form($params = array())
{
    $num_entries = count($params['entries']);

    $fixable  = true;
    $problem_count = 0;
    $problem_htm = "";
    $i = 0;
    foreach ($params['entries'] as $entry)
    {
        $i++;

        empty($entry['c-name']) ? $team = ucwords($entry['h-name']) : $team = ucwords($entry['h-name']) . " / " . ucwords($entry['c-name']);

        $txt_1 = " ";
        if ($entry['rm_club'])
        {
            $entry['rm_comp'] ? $txt_1 = "<span class='text-info'> </span>" : $txt_1 = "<span class='text-info'>different club</span>";
        }

        $txt_2 = "known competitor";
        if ($entry['rm_comp'])
        {
            $txt_2 = "<span class='text-warning'>unknown competitor</span>";
        }

        $txt_3 = "class ok";
        if ($entry['rm_class'] )
        {
            $txt_3 = "<span class='text-danger'><b>unknown class</b></span>";
            $fixable = false;
            $problem_count++;
        }

        $problem_htm.=<<<EOT
        <tr>
            <td class="px-3">$i</td>
            <td class="px-3">{$entry['b-class']} {$entry['b-sailno']}</td>
            <td class="px-3">$team</td>
            <td class="px-3">{$entry['h-club']}</td>
            <td class="px-3">$txt_1</td>  
            <td class="px-3">$txt_2</td>  
            <td class="px-5">$txt_3</td>          
        </tr>
EOT;
    }

    if ($fixable)
    {
        $warning = " This application should be able to resolve these issues as part of the transfer process.";
        $button  = <<<EOT
<a type="button" class="btn btn-lg btn-primary mx-5 fs-4"  style="min-width: 200px;" href="./rmu_racemgr_entry_export.php?eid={$params['eid']}&pagestate=submit">
<span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;&nbsp;<b>Prepare Transfer</b></a>
EOT;
   }
    else
    {
        $warning = " This application cannot fix the issues shown in red below - please use the raceManager administration functions to resolve them before trying again.";
        $button  = "";
    }

    $htm = <<<EOT
<div class="container align-items-center justify-content-center" >
    <!-- instructions -->
    <div class="row mt-4 p-5 fs-4 bg-primary text-white rounded">
        <h2>Transferring entries to raceManager&hellip; for {event-title}</h2>
        <p>This should be done when when all entries have been confirmed - <u>typically after reception has closed</u>.</p>
        <p>There are currently $num_entries entries - potential problems with these entries are listed below.</p>
        <p>Some of the issues might be resolvable automatically by this application - but there may be other problems which you must resolve before this script can complete the transfer</p>
    </div>
    
    <div class="mt-5">
        <div class="text-end">
            <a class="btn btn-lg btn-warning mx-5 fs-4" style="min-width: 200px;" type="button" name="Quit" id="Quit" onclick="return quitBox('quit');">
            <span class="glyphicon glyphicon-remove"></span>&nbsp;&nbsp;<b>Cancel</b></a>
            $button              
        </div>
    </div>

    <div class="row justify-content-center">
        <p class="lead pt-5 text-center">There are $num_entries entries for this event and $problem_count unresolvable issue(s) have been detected - as shown below.</p>
        <p class="lead text-center">$warning</p>
        <div class="col-auto">
            <table class="table table-responsive table-bordered" >
                $problem_htm
            </table>
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
    
    return $htm;
}

function rm_export_report($params = array())
{
    $audit = $params['audit'];
    $totals = $params['totals'];
    
    echo "<pre>".print_r($audit,true)."</pre>";

    // confirmation html
    $summary = "";
    $i = 0;
    foreach ($audit as $data)
    {
        $i++;
        $comp_info = "";
        $entry_info = "";
        if ($data['class'])
        {
            $comp_info.= "<span class='text-danger'><b>UNKNOWN CLASS - needs manual configuration in raceManager</b></span>";
        }
        else
        {
            if ($data['comp'])
            {
                $data['comp_Y'] == 0 ? $comp_info.= "<span class='text-danger'>FAILED to register new competitor in raceManager</span>" :
                    $comp_info.= "New competitor registered in racemanager [id: {$data['comp_Y']}] ";
            }
            else
            {
                if ($data['club']) { $comp_info.= " - club information updated"; };
            }
            $data['entry_Y'] == 0 ? $entry_info.= $data['info'] : $entry_info.= $data['info']." [ id: {$data['entry_Y']} ]";
        }
        $summary.= <<<EOT
<tr>
    <td class="px-3">$i</td>
    <td class="px-3">{$data['boat']}</td>
    <td class="px-3">$comp_info</td>
    <td class="px-3">$entry_info</td>         
</tr>

EOT;
    }

    $htm = <<<EOT
<div class="container align-items-center justify-content-center" >
    <div class="row mt-4 p-5 fs-4 bg-primary text-white rounded">
        <h2>{event-title}</h2>
        <p>Entries prepared for transfer to raceManager&hellip; </p>
        <p>Please check the audit information below for any reported problems.  If none reported use the Commit button below to complete the transfer</p>
    </div>
    
    <div class="mt-5">
        <div class="text-end">
            <a class="btn btn-lg btn-warning mx-5 fs-4" style="min-width: 200px;" type="button" name="Quit" id="Quit" onclick="return quitBox('quit');">
            <span class="glyphicon glyphicon-remove"></span>&nbsp;&nbsp;<b>Cancel</b></a>
            <a type="button" class="btn btn-lg btn-primary mx-5 fs-4"  style="min-width: 200px;" href="./rmu_racemgr_entry_export.php?eid={$params['eid']}&pagestate=commit&entered={$totals['entered']}">
            <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;&nbsp;<b>Commit Transfer</b></a>              
        </div>
    </div>

    <div class="row justify-content-center">
        <p class="lead text-center">Entry audit information ...</p>
        <p class="lead text-center">[new boats registered: <b>{$totals['registered']}</b> , total boats entered: <b>{$totals['entered']}</b>]</p>
        <div class="col-auto">
            <table class="table table-responsive table-bordered" >
                $summary
            </table>
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

    return $htm;
}

function rm_export_confirm($params = array())
{
    $htm = <<<EOT
<div class="container align-items-center justify-content-center" >
    <!-- instructions -->
    <div class="row mt-4 p-5 fs-4 bg-primary text-white rounded">
        <p>Completed - {num_entered} entries transferred to raceManager&hellip; for {event-title}</p>       
    </div>
</div>
EOT;

    return $htm;
}

function sailwave_export_form($params = array())
{
    $htm = <<<EOT
<div class="container align-items-center justify-content-center" >
    <!-- instructions -->
    <div class="row mt-4 p-5 fs-4 bg-primary text-white rounded">
        <h2>Transferring entries to Sailwave&hellip; for {event-title}</h2>
        <p>This should be done when when all entries have been confirmed - <u>typically after reception has closed</u>.</p>
        <p>Please select the field set you need and the categories of entry records to include in your transfer.</p>
        <div class="text-end">
            <a class="btn btn-info float-right" href="./documents/RM_EVENT_documentation.pdf" target="_BLANK" type="button" role="button">get help ...</a>
        </div>
    </div>
    
    <form class="row g-3" enctype="multipart/form-data" id="sailwaveexportForm" action="{script}" method="post">
        
        <!-- mode argument -->
        <div class="col-md-3 mx-5 pt-5">
            <label for="fieldSet"><h5>Field Set<h5></label>
            <select class="form-select form-select-lg mb-3" size="4" id="mode" name="mode">
              <option selected value="standard">standard</option>
              <option value="extended">extended</option>
            </select>
        </div>
        
        <!-- include argument -->
        <div class="col-md-4 pt-5">
            <label for="include"><h5>Included Fields<h5></label>
            <select class="form-select form-select-lg mb-3" size="5" id="include" name="include" >
              <option selected value="none">only entered boats</option>
              <option value="waiting">+ boats on waiting list</option>
              <option value="excluded">+ excluded boats</option>
              <option value="all">+ waiting list and excluded boats</option>
            </select>
        </div>           

        <!-- buttons -->
        <div class="mt-5">
                <div class="text-end">
                    <a class="btn btn-lg btn-warning mx-5" style="min-width: 200px;" type="button" name="Quit" id="Quit" onclick="return quitBox('quit');">
                    <span class="glyphicon glyphicon-remove"></span>&nbsp;&nbsp;<b>Cancel</b></a>

                    <button type="submit" class="btn btn-lg btn-primary mx-5"  style="min-width: 200px;" >
                    <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;&nbsp;<b>Create Export File</b></button>
                </div>
        </div>
    </form>
    
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
   
</div>
EOT;

    return $htm;
}


function sailwave_export_output($params = array())
{
    $bufr = <<<EOT
    <div class="container" style="margin-top: 40px;">
        <div class="col-md-8">
            <div class="h-100 p-5 bg-dark-subtle border rounded-3">
                <h3><b>{event}</b>&nbsp;&nbsp;-&nbsp;&nbsp;Sailwave Entry Export</h3>
                <p>fields : <b>{fieldlist}</b>&nbsp;&nbsp;&nbsp;&nbsp;entries : <b>{entries}</b></p>
                <p class="lead">Entries Processed: {num_total}&nbsp;&nbsp;&nbsp;Waiting List Entries: {num_waiting}&nbsp;&nbsp;&nbsp;Excluded Entries: {num_excluded}</p>
                <div class="text-end">
                    <a class="btn btn-info float-right" href="{filename}" target="_BLANK" type="button" role="button">Download Output File</a>
                </div>               
            </div>
        </div>
    </div>
EOT;

    return $bufr;
}
