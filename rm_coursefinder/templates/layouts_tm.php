<?php
/*
 * html layouts for util applications
 *
 */

/*
 * Main page template with defined header and footer
 *
 */
 function basic_page($params = array())
 {
     $bufr = <<<EOT
     <!DOCTYPE html>
     <html lang="en">
          <head>
            <title>{title}</title>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="description" content="">
            <meta name="author" content="">

            
            <link rel="icon"          href="{loc}/common/images/logos/favicon.png">           
            <link rel="stylesheet"    href="{loc}/common/oss/bootstrap341/css/{theme}bootstrap.min.css" >      
            <link rel="stylesheet"    href="{loc}/common/oss/bs-dialog341/css/bootstrap-dialog.min.css">
                    
            <script type="text/javascript" src="{loc}/common/oss/jquery/jquery.min.js"></script>
            <script type="text/javascript" src="{loc}/common/oss/bootstrap341/js/bootstrap.min.js"></script>
            <script type="text/javascript" src="{loc}/common/oss/bs-dialog/js/bootstrap-dialog.min.js"></script>
            <script type="text/javascript" src="{loc}/common/oss/bs-growl/jquery.bootstrap-growl.min.js"></script>
            <script type="text/javascript" src="{loc}/common/scripts/clock.js"></script>
            
            <!-- forms -->
            <link rel="stylesheet" href="{loc}/common/oss/bs-validator/dist/css/formValidation.min.css">
            <script type="text/javascript" src="{loc}/common/oss/bs-validator/dist/js/formValidation.min.js"></script>
            <script type="text/javascript" src="{loc}/common/oss/bs-validator/dist/js/framework/bootstrap.min.js"></script>
            <script type="text/javascript" src="{loc}/common/oss/bs-validator/dist/js/addons/mandatoryIcon.js"></script>

            <!-- Custom styles for this template -->
            <link href="{stylesheet}" rel="stylesheet"> 
      
          </head>
          <body class="{$_SESSION['background']}" style="" >
          <div class="container-fluid">
            <nav class="navbar navbar-expand-lg navbar-dark bg-primary noprint">
                <div class="container-fluid">
                    <h2 class="text-success">{header-left}<span class="pull-right">{header-right}</span></h2>
                </div>
            </nav>
                     
            <!-- Body -->
            <div class="container-fluid" style="margin-bottom: 20px; min-height: 400px;">
                <div class="row">
                    <div class="col-md-offset-1 col-md-10">{body}</div>
                </div>
            </div>
                
            <!-- Footer -->
            <div class="container" >
                <div class="row">
                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 text-left rm-page">{footer-left}</div>
                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 text-center rm-page">{footer-center}</div>
                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 text-right rm-page">{footer-right}</div>
                </div>   
            </div>

          </div>
            
            <!-- popover activation for all popovers -->
            <script type="text/javascript">
                $(document).ready(function() {
                $("[data-toggle=popover]").popover({trigger: 'hover',html: 'true'});
                });
            </script>

            <!-- tooltip activation for all tooltips -->
            <script type="text/javascript">
                $(document).ready(function() {
                $("[data-toggle=tooltip]").tooltip({trigger: 'hover',html: 'true'});
                });
            </script>
          </body>
     </html>        
EOT;

    return $bufr;
 }

 function courseinit_page($params = array())
 {
    $htm = <<<EOT
    <div class="row">
        <div class="col-md-7">
            <h2>pick wind direction&hellip;</h2>
            <img src="./images/compassrose.jpg" alt="Compass" usemap="#compass" width="480" height="462">
            <map name="compass">
              <area shape="poly" coords="146,6,334,6,260,185,222,185" alt="N"  title="north" href="{link}N">
              <area shape="poly" coords="350,15,457,120,287,214,264,186" alt="NE" title="north-east" href="{link}NE">
              <area shape="poly" coords="465,140,465,324,291,248,285,217" alt="E"  title="east" href="{link}E">
              <area shape="poly" coords="451,344,348,440,265,277,287,253" alt="SE" title="south-east" href="{link}SE">
              <area shape="poly" coords="335,458,150,456,226,281,254,280" alt="S"  title="south" href="{link}S">
              <area shape="poly" coords="129,443,26,326,200,254,219,274" alt="SW" title="south-west" href="{link}SW">
              <area shape="poly" coords="16,329,16,140,193,218,194,245" alt="W"  title="west" href="{link}W">
              <area shape="poly" coords="30,124,130,24,219,189,199,207" alt="NW" title="north-west" href="{link}NW" >
            </map>
        </div>
        <div class="col-md-5">
            <h2>{today}</h2>
            <h3>{tide_str}</h3>
            <h3>{race_str}</h3>
        </div>
    </div>
EOT;
     return $htm;
 }

 function coursedetail_page($params = array())
 {
    $courseid = $params['courseid'];
    $htm = <<<EOT
    <div>
        {course-selection}
        
        <div class='alert alert-info' style='padding: 5px;'></div>

        <div class="row">
            <div class="col-md-offset-1 col-md-10">{course-board}</div>
        </div>
        
        <div class='alert alert-info' style='padding: 5px;'></div>
        
        <div class="row margin-top-20">
            <div class="col-md-8">
                <div class=" alert alert-warning">
                    <h3>OOD Instructions&hellip;</h3>
                    <div style="font-size: 1.5em !important">{course-instructions}</div>
                </div>
            </div>
            <div class="col-md-offset-1 col-md-3">
                <span class="noprint">
                <a class="btn btn-info btn-md btn-block" href="rm_coursefinder.php?pagestate=courseprint&courseid=$courseid" type="button">
                    <span class="glyphicon glyphicon-print" aria-hidden="true"></span>&nbsp;&nbsp;&nbsp;Print Friendly
                </a>
                </span>
                <br>
                <span class="noprint">
                <a class="btn btn-info btn-md btn-block" href="rm_coursefinder.php?pagestate=init" type="button">
                    <span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span>&nbsp;&nbsp;&nbsp;Back to Wind Directions
                </a>
                </span>           
            </div>
        </div>
    </div>
EOT;

    return $htm;
 }

 function coursedetail_print($params = array())
 {

    $htm = <<<EOT
    <div>
        {course-selection}
        
        <hr style='border: 5px solid grey; border-radius: 3px;'>

        <div class="row">
            <div class="col-md-offset-1 col-md-10">{course-board}</div>
        </div>
        
        <hr style='border: 5px solid grey; border-radius: 3px;'>
        
        <div class="row margin-top-20">
            <div class="col-md-8">
                <div style="padding: 15px; border: 2px solid darkgrey; border-radius 4px">
                    <h3>OOD Instructions&hellip;</h3>
                    <div style="font-size: 1.5em !important">{course-instructions}</div>
                </div>
            </div>
            <div class="col-md-offset-1 col-md-3">
                &nbsp;         
            </div>
        </div>
    </div>
EOT;

     return $htm;
 }

 function no_courses($params = array())
 {
     $htm = <<<EOT
        <div class="margin-top-40" style="width: 60%">
            <div class="alert alert-warning" role="alert">
                <h2>No courses available for wind direction - <b>{wind}</b></h2>
                Please contact your system administrator           
            </div>
            <!-- back button -->
            <div class="pull-right">
            <a class="btn btn-primary" href="{url}" role="button" style="width: 150px">
            <span class="glyphicon glyphicon-step-backward" aria-hidden="true"></span>&nbsp;Back</a>
            </div>
        <div>
EOT;
     return $htm;
 }

 function missing_course_detail($params=array())
 {
     $htm = <<<EOT
        <div class="margin-top-40" style="width: 60%">
            <div class="alert alert-warning" role="alert">
                <h2>Course information missing</b></h2>
                <h3>{reason}</h3>
                <h3>Please contact your system administrator</h3>           
            </div>
            <!-- back button -->
            <div class="pull-right">
            <a class="btn btn-primary" href="{url}" role="button" style="width: 150px">
            <span class="glyphicon glyphicon-step-backward" aria-hidden="true"></span>&nbsp;Back</a>
            </div>
        <div>
EOT;
     return $htm;
 }

function course_selection($params = array())
{
    $htm = "";
    $courses  = $params['courses'];
    $courseid = $params['courseid'];
    $category = $params['category'];

    if (!empty($courses))
    {
        $i = 0;
        $htm = "";
        foreach ($courses as $course)
        {
            $courseid == $course['id'] ? $active = true : $active = false;

            if ($i == 3) // break row after 4 courses
            {
                $htm.= "</div>";
                $i = 0;
            }

            $i++;

            $active ? $btn_type = "info" : $btn_type = "default";
            $blurb = $course['blurb'];

            $link = "rm_coursefinder.php?pagestate=coursedetail&category=$category&courseid={$course['id']}";
            $button = "<a href='$link' class='btn btn-$btn_type btn-lg btn-block' role='button'>{$course['name']}</a>";

            if ($i == 1)
            {
                $htm.= "<div class='row' style='padding-bottom: 5px;'>";
                $htm.= "<div class='col-md-offset-1 col-md-3' >$button<br><span style='font-size: 1.2em'>$blurb</span></div>";
            }
            else
            {
                $htm.= "<div class='col-md-offset-1 col-md-3'>$button<br><span style='font-size: 1.2em'>$blurb</span></div>";
            }
        }
        $htm.= "</div>";
    }
    else
    {
        $htm = <<<EOT
        <div class="alert alert-warning" role="alert" style="width: 60%">
            No courses available for wind direction - {category}
        </div>
EOT;
    }
    return $htm;
}

function course_selection_print($params=array())
{
    $event = "";
    $start_tide = "";
    if ($params['eventname'])
    {
        $event.= $params['eventname'];
        if ($params['eventdate']) { $event.= "&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;".date("D M j", strtotime($params['eventdate'])); }

        if ($params['eventstart'])
        {
            $start_tide.= "Start:&nbsp;&nbsp;&nbsp;".$params['eventstart']."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        }

        if ($params['tidetime'])
        {
            $start_tide.= "Tide:&nbsp;&nbsp;&nbsp;".$params['tidetime'];
            if ($params['tideheight']) { $start_tide.= " - ".$params['tideheight']."m"; }
        }
    }

    $htm = <<<EOT
    <div class="well">
        <div class="row">
            <div class="col-md-6"><h2>$event</h2><h3>$start_tide</h3></div>
            <div class="col-md-6 ">
                <a class="btn btn-info btn-md pull-right noprint" onclick="window.print()" href="#" type="button">
                    <span class="glyphicon glyphicon-print" aria-hidden="true"></span>&nbsp;&nbsp;&nbsp;Print 
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-10"><h3>Course: {name}</h3><h4>{blurb}</h4></div>
        </div>
    </div>
EOT;


    return $htm;
}

function course_board($params=array())
{
    if ($params['mode'] == "colour")
    {
        $htm = "<h2>{course-title} Course</h2>";
    }
    else
    {
        $htm = "";
    }

    foreach ($params['subcourses'] as $course)
    {
        $row = "";

        // fleet boards
        $fleets = "";
        foreach ($course['fleets'] as $fleet)
        {
            $fleets.= $fleet."<br>";
        }
        $fleets = rtrim($fleets, "<br>");

        // start boards
        $starts = <<<EOT
        <table class="board-table"><tbody><tr>
EOT;
        foreach($course['start'] as $start)
        {
            if ($params['mode'] == "colour")
            {
                $starts.= <<<EOT
                    <td class="board-thin board-{$start['colour']}">{$start['type']}</td>
EOT;
            }
            else
            {
                $starts.= <<<EOT
                    <td class="board-thin-print ">{$start['type']}<br><span class="colour-txt">{$start['colour']}</span></td>
EOT;
            }
        }
        $starts.= "</tr></tbody></table>";

        // race mark boards
        $buoys = <<<EOT
        <table class="board-table"><tbody><tr>
EOT;
        foreach($course['buoys'] as $buoy)
        {
            if ($params['mode'] == "colour")
            {
                $buoys.= <<<EOT
                 <td class="board-wide board-{$buoy['colour']}">{$buoy['type']}</td>
EOT;
            }
            else
            {
                $buoys.= <<<EOT
                 <td class="board-wide-print ">{$buoy['type']}<br><span class="colour-txt">{$buoy['colour']}</span></td>
EOT;
            }
        }
        $buoys.= "</tr></tbody></table>";

        // lap boards
        $laps = <<<EOT
        <table class="board-table"><tbody><tr>
EOT;
        foreach($course['laps'] as $lap)
        {
            if ($params['mode'] == "colour")
            {
                $laps.= <<<EOT
                <td class="board-thin board-{$lap['colour']}">{$lap['type']}</td>
EOT;
            }
            else
            {
                $laps.= <<<EOT
                <td class="board-thin-print">{$lap['type']}<br><span class="colour-txt">{$lap['colour']}</span></td>
EOT;
            }
        }
        $laps.= "</tr></tbody></table>";

        $row.= <<<EOT
        <table style="table-layout: fixed; width: 80%;"><tbody>
        <tr>
            <td width="10%" class="board-fleet">$fleets</td>
            <td width="15%">$starts</td>
            <td width="65%">$buoys</td>
            <td width="10%">$laps</td>
         </tr>            
        </tbody></table>
EOT;
         $htm.= $row;
    }

    return $htm;
}

function course_instructions($params=array())
{
    $instructions = $params['course']['info'];

    $list = "";
    foreach ($instructions as $instruction)
    {
        $list.= "<li>$instruction</li>";
    }

    $htm = <<<EOT
    <ul>$list</ul>
EOT;

    return $htm;
}
function course_picture($params=array())
{
    $course = $params['course'];
    $htm = "";

    $htm.= <<<EOT
    <img src="{$course['other_url']}"></img>
EOT;

    return $htm;
}






