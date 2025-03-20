<?php 
/* ---------------------------------------------------------------------------------------------
    index.php       standard start up page for racemanager applications
        
   ---------------------------------------------------------------------------------------------
*/

// these might change on the page
file_exists('demo_on.txt') ? $mode = "demo" : $mode = "live";
$debug    = "0";

// includes
include ("../common/lib/util_lib.php");
u_initconfigfile("../config/common.ini");

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
        <link rel="icon" href="../common/images/logos/favicon.png">
    
        <!-- Bootstrap core CSS -->
        <link   rel="stylesheet"       href="../common/oss/bootstrap341/css/flatly_bootstrap.min.css" >
        <script type="text/javascript" src="../common/oss/jquery/jquery.min.js"></script>
        <script type="text/javascript" src="../common/oss/bootstrap341/js/bootstrap.min.js"></script>
        
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

/*// jumbotron block
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
*/

if (file_exists('maintenance_on.txt'))
{
    // undergoing maintenance message
    echo <<<EOT
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
    echo <<<EOT
    <h1 class="text-success" style="margin-left:20px;">raceManager Utilities</h1>
        <div class="row">      
        <div class="col-md-3" style="margin-left:10px; margin-top: 10px;">
            <div style="padding-left:20px;">
                <h4>dutyman import</h4>
                <p class="text-info">Synchronises duty swaps in dutyman into the t_eventduty table</p>
                <a class="btn btn-primary btn-md btn-block" href="dtm_import.php?pagestate=init" role="button"><span style="font-size: 0.8em">dtm_import.php?pagestate=init</span></a>
            </div>
        </div>
        <div class="col-md-3" style="margin-left:10px; margin-top: 10px;">
            <div style="padding-left:20px;">
                <h4>dutyman export</h4>
                <p class="text-info">Creates export files (events and/or duties) for import into dutyman</p>
                <a class="btn btn-primary btn-md btn-block" href="dtm_export.php?pagestate=init" role="button"><span style="font-size: 0.8em">dutyman_export.php?pagestate=init</span></a>
            </div>
        </div>
        <!--div class="col-md-3" style="margin-left:20px; margin-top: 20px;">
            <div style="padding-left:20px;">
                <h4>dutyman export</h4>
                <p class="text-info">Compares dutyman data with programme data on duties</p>
                <a class="btn btn-primary btn-md btn-block" href="dtm_import.php?pagestate=init" role="button"><span style="font-size: 0.7em">dtm_duty_import.php?pagestate=init</span></a>
            </div>
        </div -->
    </div>
EOT;
}




// end of page
echo <<<EOT
    <script src="common/oss/jquery/jquery.min.js"></script>
    <script src="common/oss/bootstrap341/js/bootstrap.min.js"></script>
  </body>
</html>
EOT;

