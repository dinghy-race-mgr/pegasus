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
        <title>{page-title}</title>
    
        <link rel="canonical" href="https://getbootstrap.com/docs/5.0/examples/sticky-footer-navbar/">
    
        <!-- Bootstrap core CSS -->
        <link href="../common/oss/bootstrap532/css/{page-theme-utils}bootstrap.min.css" rel="stylesheet">
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
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarText">
                    <div class="col text-end">
                    <span class="navbar-text fs-5 ">raceManager {version} - &copy; Elmswood Software {year}</span>
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

function entries_review_body ($params = array())
{
    $htm = "";

    // title details
    $timestamp = date("d-M-Y H:i");
    $title = "Entry Review: {event-title}";

    // options
    $option_txt = "[ OPTIONS: ";
    foreach ($params['options'] as $k=>$v)
    {
        $option_txt.= "$k - $v, ";
    }
    $option_txt = rtrim($option_txt, ',' )." ]";

    // table columns
    $cols = "<thead><tr>";
    foreach ($params['cols'] as $k => $v)
    {
        $cols.= "<th>".ucwords($k)."</th>";
    }

    if ($params['checks']) { $cols.= "<th>Entry Checks</th>"; }
    $cols.= "</tr></thead>";

    // table rows
    $rows = "";
    foreach ($params['entries'] as $key => $data)               // loop over rows
    {
        $rows.= "<tr>";
        foreach ($params['cols'] as $k => $f)                   // loop to get required field data
        {
            if (array_key_exists($f, $data)) { $rows.= "<td>{$data["$f"]}</td>"; }  // add field
        }

        // add record check information
        if ($params['checks'])
        {
            $checks = "";
            for ($i = 1; $i <= 8; $i++)
            {
                if ($i == 1) {
                    $checks .= "juniors: {$data["chk$i"]} issues: ";
                } elseif ($data["chk$i"]) {
                    $checks .= "$i | ";
                }
            }
            $rows.= "<td>".rtrim($checks, "| ")."</td>";
        }

        $rows.= "</tr>";
    }
    echo "<pre>$rows</pre>";

    // legend for checks
    if ($params['checks'])
    {
        $key = "ISSUES KEY: 2 - missing junior consents, 3 - missing emergency contact, 4 - missing crew name, 
        5 - missing gender info, 6 - missing sailno, 7 - class not known to RM, 8 - competitor not known to RM";
    }


    $htm = <<<EOT
    <main class="" >
        <div class="container-fluid">
            <p class='fs-3'><b>$title</b><span class="fs-6">[created at $timestamp]</span></p>    
            <p><i>$option_txt</i></p>
            <div style="padding-left: 30px; width: 75%">   
                <table class="table table-success table-striped table-hover">
                    $cols
                    $rows           
                </table> 
                <p>$key</p> 
            </div>    
        </div>    
    </main>

EOT;

    return $htm;
}


function sailwave_export_form($params = array())
{
    $bufr = <<<EOT
    <div class="container" style="margin-top: 40px;">
        <div class="col-md-8">
            <div class="h-100 p-5 bg-dark-subtle border rounded-3">
                <h3>{function}</h3>
                <p class="lead">{instructions}</p>
                <div class="text-end">
                    <a class="btn btn-info float-right" href="./documents/RM_EVENT_documentation.pdf" target="_BLANK" type="button" role="button">get help ...</a>
                </div>               
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
                        <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;&nbsp;<b>Create</b></button>
                    </div>
            </div>
        </form>
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
