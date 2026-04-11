<?php
/* ---------------------------------------------------------------------------------------------
    index.php       standard start up page for racemanager applications
        
   ---------------------------------------------------------------------------------------------
*/
$year = date("Y");

// these might change on the page
file_exists('demo_on.txt') ? $mode = "demo" : $mode = "live";
$debug    = "0";

// includes
include ("../common/lib/util_lib.php");
$cfg = u_set_config("../config/common.ini", array(), false);
$version = $cfg['sys_version'];

// read utils configuration json
$utils_cfg = json_decode(file_get_contents("./data/util_apps.json"), true);

if (file_exists('maintenance_on.txt'))
{
    // undergoing maintenance message
    $page_body = <<<EOT
    <div>
        <h1 style="color: darkslateblue; text-align: center; margin-bottom:20px">Sorry! - raceManager is currently ...</h1></div>
        <div style="text-align: center;">
            <img src="common/images/web_graphics/maintenance.jpg" alt="under maintenance" class="img-responsive img-rounded center-block" style="width: 500px;"></img><br>
		    <a href="https://www.freepik.com/free-vector/maintenance-background-design_1000106.htm#page=2&query=under%20maintenance&position=5&from_view=keyword">Image by lexamer</a> on Freepik
    </div>
EOT;
}
else
{
    // applications block
    $page_body = "";
    foreach ($utils_cfg['apps'] as $app)
    {
        if ($app['status'] == "current") {
            isset($app['notes']) ? $notes = "<hr><p class='mb-0'>{$app['notes']}</p>" : $notes = "";

            $page_body .= <<<EOT
            <div class="alert alert-primary ms-5 mt-3" style='width:60%' role="alert">
                <div class="row">
                    <div class="col-md-4 fs-4">{$app['title']}</div>
                    <div class="col-md-5 fs-6" >{$app['description']}</div>
                    <div class="col-md-3" style="padding: 10px;">
                        <a class="btn btn-dark btn-md btn-block" href='{$app['url']}' role="button">Run This Utility</a>
                    </div>           
                </div>
                $notes
            </div>
EOT;
        }
    }
}

// document
echo <<<EOT
<!DOCTYPE html>
    <html lang="en">
      <head>
        <title>{$cfg['sys_name']}</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="icon" href="../common/images/logos/favicon.png">
    
        <!-- Bootstrap core CSS -->
        <link   rel="stylesheet"       href="../common/oss/bootstrap532/css/cerulean_bootstrap.min.css" >
        <script type="text/javascript" src="../common/oss/jquery/jquery.min.js"></script>
        <script type="text/javascript" src="../common/oss/bootstrap532/js/bootstrap.min.js"></script>
        
        <style>
            body { padding-top: 70px; font-family: Kalinga, Arial, sans-serif;}
            .brand-splash {font-weight: bold; font-size: 120%; color: orange;}
        </style> 
  
      </head>      
      <body class="d-flex flex-column h-100">
        <header>
        <nav class="navbar bg-primary navbar-expand-lg bg-body-tertiary" data-bs-theme="dark">
            <div class="container-fluid">
                <a class="navbar-brand fs-2" href="#">raceManager Utilities</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" 
                               aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarText">
                    <div class="col text-end">
                        <span class="navbar-text fs-5 ">raceManager $version</span><br>
                        <span class="navbar-text fs-6"> &copy; Elmswood Software $year</span>
                    </div>
                </div>
           </div>
        </nav>
    </header>
        
        $page_body
        
        <script src="common/oss/jquery/jquery.min.js"></script>
        <script src="common/oss/bootstrap341/js/bootstrap.min.js"></script>
  </body>
</html>
EOT;



