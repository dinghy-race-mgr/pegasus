<?php 
/* ---------------------------------------------------------------------------------------------
    index.php       standard start up page for racemanager applications
        
   ---------------------------------------------------------------------------------------------
*/

// these might change on the page
$mode     = "live";
$debug    = "0";

// includes
include ("./common/lib/util_lib.php");
u_initconfigfile("config/common.ini");

// document header
echo <<<EOT
<!DOCTYPE html>
    <html lang="en">
      <head>
        <title>{$_SESSION['sys_name']}</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="shortcut icon" href="../common/images/favicon.ico">
    
        <!-- Bootstrap core CSS -->
        <link   rel="stylesheet"       href="common/oss/bootstrap341/css/flatly_bootstrap.min.css" >
        <script type="text/javascript" src="common/oss/jquery/jquery.min.js"></script>
        <script type="text/javascript" src="common/oss/bootstrap341/js/bootstrap.min.js"></script>
        
        <style>
            body { padding-top: 70px; font-family: Kalinga, Arial, sans-serif;}
            .brand-splash {font-weight: bold; font-size: 120%; color: orange;}
        </style> 
  
      </head>      
      <body>
EOT;

// navbar
echo <<<EOT
<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container-fluid">
        <div class="collapse navbar-collapse">  
            <a class="navbar-brand " href="{$_SESSION['sys_website']}">{$_SESSION['sys_name']} <small>[{$_SESSION['sys_release']} - {$_SESSION['sys_version']}]</small></a>
        
            <ul class="nav navbar-brand navbar-right">
              <li>&copy; {$_SESSION['sys_copyright']}</li>         
            </ul>

        </div>
    </div> <!-- end of container -->
</nav><!-- end of nav -->
EOT;

// jumbotron block
echo <<<EOT
<div class="container theme-showcase" role="main">
    <div class="jumbotron" style="padding-top: 20px; padding-bottom: 20px">
      <div class="container" >
        <div class="row">
           <div class="col-md-7">
               <h1><img src="common/images/logos/rmlogo-md.jpg"> raceManager</h1>
               <p>An integrated system for race timing and results management at sailing clubs</p>
           </div>
           <div class="col-md-5">
               <div style="height:100%; " >
                   <img src="config/images/club_banner.jpg" alt="dinghy racing image" class="img-responsive img-rounded"  style="max-height:200px !important;"></img>
               </div>
           </div>
        </div>
      </div>
    </div>
</div>
EOT;

// applications block
echo <<<EOT
<div class="container">
  <div class="row">
    <div class="col-md-3">
      <a class="btn btn-primary btn-block" href="./rm_sailor/index.php" target="_blank" role="button">
         <span style="font-size: 1.7em;">Sailor</span>
      </a>
      <p style="margin-top: 1.0em;">Member entry and declaration interface for today&rsquo;s races</p>
    </div>
        
    <div class="col-md-3">
      <a class="btn btn-danger btn-block" href="common/scripts/loaderpage.php?text=starting RaceBox application&script=../../rm_racebox/rm_racebox.php?mode=$mode%26lang=en%26debug=$debug" role="button">
         <span style="font-size: 1.7em;">Race Box</span>
      </a>
      <p style="margin-top: 1.0em;">Race officer application to run a race; time the finish; create and publish the results. Supports class, handicap, average lap and pursuit racing.</p>
    </div>
        
    <div class="col-md-3">
      <a class="btn btn-primary btn-block" href="rm_admin/app/login.php" target="_blank" role="button" >
         <span style="font-size: 1.7em;">Administration</span>
      </a>
      <p style="margin-top: 1.0em;">Administration interface for results coordinator to configure the system and administer the race results.</p>
    </div>

    <div class="col-md-3">
      <a class="btn btn-primary btn-block" href="rm_web/rm_web.php" target="_blank" role="button" >
         <span style="font-size: 1.7em;">Club Website</span>
      </a>
      <p style="margin-top: 1.0em;">Dynamic programme and results pages which can be incorporated in your club website</p>
    </div>
        
  </div>
</div>
EOT;


// end of page
echo <<<EOT
    <script src="common/oss/jquery/jquery.min.js"></script>
    <script src="common/oss/bootstrap341/js/bootstrap.min.js"></script>
  </body>
</html>
EOT;

