<?php
/* ------------------------------------------------------------
   loaderpage
   
   a general purpose page to display a loading spinner while
   another page is loading
   
   arguments:
       script       script to load
   
   Elmswood Software 2014
   ------------------------------------------------------------
*/

// header
echo <<<EOT
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>loader</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="../oss/bootstrap341/css/bootstrap.min.css" rel="stylesheet">
	</head>

	<body >
EOT;

// display loading image
echo <<<EOT
<div class="container" style="padding: 100px;">
   <div class="row">
      <div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 alert alert-danger">
         <strong class="alert-heading"><h2>{$_REQUEST['text']}</h2></strong>
         <br><br><br>
         <div style="text-align: center;">
            <img class="img-responsive center-block"  src="../images/loading.gif">
         </div>
      </div>
   </div>
</div>
EOT;

// flush buffer to present immediately
echo str_repeat("\n",4096);
flush();
sleep(2);

// now go to requested script while loading image is displayed
$script = $_REQUEST['script'];
echo <<<EOT
<script type="text/JavaScript">window.location="$script"</script>
EOT;

echo "</body>";

echo <<<EOT
    <script type="text/javascript">
        $('#container').css('opacity', 0);
        $(window).load(function() {
          $('#container').css('opacity', 1);
        });
        $(document).ready(function() {
          setTimeout('$("#container").css("opacity", 1)', 2000);
        });
    </script>
EOT;

echo "</html>";

