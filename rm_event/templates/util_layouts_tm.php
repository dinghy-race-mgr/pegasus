<?php


function page ($params = array())
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
        <link href="../common/oss/bootstrap532/css/bootstrap.min.css" rel="stylesheet">
        <link href="../common/oss/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        
        <!-- Bootstrap core js -->
        <script src="../common/oss/bootstrap532/js/bootstrap.bundle.min.js"></script>      
    
        <!-- Custom styles for this template -->
        <link href="./style/rm_event.css" rel="stylesheet">
    </head>
    <body class="d-flex flex-column h-100">
       
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


function error_body ($params = array())
{
    empty($params['event-title']) ? $title = "<br><br>": $title = "<p class='display-5 text-info mb-lg-5'><b>{event-title}</b></p>";
    empty($params['action']) ? $action = "" : $action = "<br><br>You could try ... ".$params['action'];

    $error_display = <<<EOT
    <div class="p-5 mb-4 text-bg-secondary border rounded-3">
      <div class="container-fluid py-5">
        <h1 class="display-6 fw-bold">Oops ... we have a problem</h1>
        <p class="col-md-10 fs-5">Something has gone wrong. {problem} $action</p>        
        <p class="col-md-10 fs-5">You can report this problem to our System Administrator using the button below.</p>
        <a class="btn btn-danger btn-lg" href="{report-link}" type="button" target="_BLANK">report problem</a>
        <p class="text-end">[ debug detail: {location} {evidence} ]</p>
      </div>
    </div>
EOT;

    $htm = <<<EOT
    <main class="" >
        <div class="container nav-margin">
            $title           
            $error_display           
        </div>    
    </main>
EOT;

    return $htm;
}

function footer ($params = array())
{
    if (!$params['footer'])
    {
        $htm = "";
    }
    else
    {
        $htm = <<<EOT
        <footer class="footer ">
        <div class="container-fluid px-4 py-3 text-bg-secondary d-flex align-items-baseline">
            <div class="row gap-5"
                <div class="col-6 text-start  ">
                {msg}   
                </div>           
                <div class="col text-end">
                    <span class="align-top">raceManager {version} &nbsp;&nbsp;-&nbsp;&nbsp;  copyright Elmswood Software {year}</span>
                </div>                   
            </div>
        </div> 
        <footer></footer>
EOT;
    }

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

