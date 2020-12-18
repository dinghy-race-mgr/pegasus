<?php 
/* ---------------------------------------------------------------------------------------------
    rm_splash       standard start up page for racemanager applications
        
   ---------------------------------------------------------------------------------------------
*/

// these might change on the page
$langcode = "en";
if (!empty($_REQUEST['lang'])) {$langcode = $_REQUEST['lang'];}
$mode     = "live";
$debug    = "0";

// includes
include ("common/lib/util_lib.php");
u_initconfigfile("config/common.ini");
include ("config/racemanager_cfg.php");
include ("config/lang/$langcode-startup-lang.php");

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
        <link href="common/oss/bootstrap341/css/bootstrap.min.css" rel="stylesheet">
        
        <style>
            body { padding-top: 70px; font-family: Kalinga, Arial, sans-serif;}
            .brand-splash {font-weight: bold; font-size: 120%; color: orange;}
        </style>
        
        <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
          <script src="common/oss/jquery/html5shiv.js"></script>
          <script src="common/oss/jquery/respond.min.js"></script>
        <![endif]-->  
  
      </head>      
      <body>
EOT;

// navbar
echo <<<EOT
<nav class="navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container-fluid">
        <div class="collapse navbar-collapse">  
            <a class="navbar-brand " href="{$_SESSION['sys_website']}"><span class="brand-splash">{$_SESSION['sys_name']} <small>[{$_SESSION['sys_release']} - {$_SESSION['sys_version']}]</small></span></a>
        
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
         <span style="font-size: 1.7em;">{$lang['menu']['sailor']}</span>
      </a>
      <p style="margin-top: 1.0em;">{$lang['msg']['sailor-text']}</p>
    </div>
        
    <div class="col-md-3">
      <a class="btn btn-danger btn-block" href="common/scripts/loaderpage.php?text={$lang['sys']['initialising']}&script=../../rm_racebox/rm_racebox.php?mode=$mode%26lang=$langcode%26debug=$debug" role="button">
         <span style="font-size: 1.7em;">{$lang['menu']['racebox']}</span>
      </a>
      <p style="margin-top: 1.0em;">{$lang['msg']['racebox-text']}</p>
    </div>
        
    <div class="col-md-3">
      <a class="btn btn-primary btn-block" href="rm_admin/app/login.php" target="_blank" role="button" >
         <span style="font-size: 1.7em;">{$lang['menu']['admin']}</span>
      </a>
      <p style="margin-top: 1.0em;">{$lang['msg']['admin-text']}</p>
    </div>

    <div class="col-md-3">
      <a class="btn btn-primary btn-block" href="rm_web/rm_web.php" target="_blank" role="button" >
         <span style="font-size: 1.7em;">{$lang['menu']['website']}</span>
      </a>
      <p style="margin-top: 1.0em;">{$lang['msg']['website-text']}</p>
    </div>
        
<!--    <div class="col-md-3">
       <div class="dropdown" >
          <button type="button" class="btn btn-primary btn-block dropdown-toggle" data-toggle="dropdown">
             <span style="font-size: 1.7em;">{$lang['menu']['website']} </span><span class="glyphicon glyphicon-chevron-down" aria-hidden="true"></span>
          </button>
          <ul class="dropdown-menu" role="menu">
                <li><a href="#" target="_blank">Events Calendar</a></li>
                <li><a href="#" target="_blank">Results Page</a></li>
                <li><a href="#" target="_blank">Local PY Analysis</a></li>
          </ul>
        </div>
        <p style="margin-top: 1.0em;">{$lang['msg']['website-text']}</p>
    </div> -->
        
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

