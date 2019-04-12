<?php
/**
 * exitpage.php 
 * 
 * description - standard exit page displayed when system exits unexpectedly
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 * 
 * @param string $script    name of script where problem occured
 * @param int    $eventid   event number
 * @param string $error     error code (e.g. sys001)
 * @param string $msg       descriptive messages
 */

echo <<<EOT
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="{$loc}/common/images/favicon.ico">             
    <link rel="stylesheet"    href="{$loc}/common/oss/bootstrap/css/bootstrap.min.css" >      
    <link rel="stylesheet"    href="{$loc}/common/oss/bootstrap/css/bootstrap-theme.min.css">
            
    <script type="text/javascript" src="{$loc}/common/oss/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="{$loc}/common/oss/bootstrap/js/bootstrap.min.js"></script>

  </head>
  <body>
    <div class="container" style="margin-top: 50px;">
        <div class="well bg-danger">
            <h2 class="text-danger">{$lang['err']['exit-main']}</h2>
            <p class="text-danger"><b>$msg</b></p>
            <p class="text-danger">{$lang['err']['exit-action']}
            <p class="text-warning pull-right">{$lang['words']['system']} {$lang['words']['error']}: $error [{$lang['err']["$error"]}] script: $script</p>          
        </div>
    </div>
  </body>
</html>
EOT;
?>