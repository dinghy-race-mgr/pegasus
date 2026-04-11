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
        <link href="../common/oss/bootstrap532/css/{page-theme}bootstrap.min.css" rel="stylesheet">
        <link href="../common/oss/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        
        <!-- Bootstrap core js -->
        <script src="../common/oss/bootstrap532/js/bootstrap.bundle.min.js"></script>      
    
        <!-- Custom styles for this template -->
        <link href="sticky-footer-navbar.css" rel="stylesheet" >
        <link href="./style/rm_event.css" rel="stylesheet">
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

function navbar_utils ($params = array())
{
    //echo "<pre>NAVBAR".print_r($params,true)."</pre>";

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
    //echo "<pre>FOOTER".print_r($params,true)."</pre>";

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

    return $htm;
}