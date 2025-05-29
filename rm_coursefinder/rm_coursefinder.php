<?php
/*
 * provides a mechanism to display course options to OODs
 *
 * TO DO
 *  - add today event or passed event details to init page DONE
 *  - sort out course detail error page  DONE
 *  - sort out instructions panel - change schema
 *  - print button
 *  - implement print page
 *  - implement race format specific display
 *  - add courses to database
 */
$loc  = "..";
$page = "coursefinder";     //
$scriptname = basename(__FILE__);
$today      = date("Y-m-d");
$styletheme = "flatly_";
$stylesheet = "./style/rm_coursefinder.css";

require_once ("{$loc}/common/lib/util_lib.php");

session_id("sess-coursefinder");
session_start();

$init_status = u_initialisation("$loc/config/rm_coursefinder_cfg.php", $loc, $scriptname);

if ($init_status)
{
    // set timezone
    if (array_key_exists("timezone", $_SESSION)) { date_default_timezone_set($_SESSION['timezone']); }

    // start log
    error_log(date('d-Y H:i:s')." -- COURSE FINDER ------- [session: ".session_id()."]".PHP_EOL, 3, $_SESSION['syslog']);

    // set initialisation flag
    $_SESSION['util_app_init'] = true;
}
else
{
    u_exitnicely($scriptname, 0, "one or more problems with script initialisation",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

// classes
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
$tmpl_o = new TEMPLATE(array("$loc/common/templates/general_tm.php","./templates/layouts_tm.php"));

// arguments
$eventid      = u_checkarg("eventid", "checkintnotzero", "", "");
$category     = u_checkarg("category", "set", "", "");
$courseid     = u_checkarg("courseid", "checkintnotzero", "", "");
$pagestate    = u_checkarg("pagestate", "set", "", "init");
$valid_states = array("init", "courselist", "coursedetail", "courseprint");
in_array($pagestate, $valid_states) ? $valid_state = true : $valid_state = false;

// log arguments
$args_txt = "";
if (array_key_exists("eventid", $_REQUEST)) { $args_txt.= "eventid:{$_REQUEST['eventid']} | "; }
if (array_key_exists("category", $_REQUEST)) { $args_txt.= "category:{$_REQUEST['category']} | "; }
if (array_key_exists("courseid", $_REQUEST)) { $args_txt.= "courseid:{$_REQUEST['courseid']} | "; }
if (array_key_exists("pagestate", $_REQUEST)) { $args_txt.= "pagestate:{$_REQUEST['pagestate']} | "; }

error_log(date('d-Y H:i:s')." -- COURSE FINDER ------- [ARGS: $args_txt]".PHP_EOL, 3, $_SESSION['syslog']);

// control params
$check_event = u_checkarg("check_event", "set", "", "");
if ($check_event) {
    if ($check_event == "off") {
        $_SESSION['check_event'] = false;
    }
}
$check_eventformat = u_checkarg("check_eventformat", "set", "", "");
if ($check_eventformat) {
    if ($check_eventformat == "off") {
        $_SESSION['check_eventformat'] = false;
    }
}
$check_tide = u_checkarg("check_tide", "set", "", "");
if ($check_tide) {
    if ($check_tide == "off") {
        $_SESSION['check_tide'] = false;
    }
}


if ($valid_state)
{
    // connect to database
    $db_o = new DB();
    foreach ($db_o->db_getinivalues(false) as $data)
    {
        $_SESSION["{$data['parameter']}"] = $data['value'];
    }

    // set standard template
    $pagefields = array(
        "loc"          => $loc,
        "theme"        => $styletheme,
        "stylesheet"   => $stylesheet,
        "title"        => "CourseFinder",
        "header-left"  => "raceManager",
        "header-right" => "CourseFinder",
        "body"         => "",
        "footer-left"   => "",
        "footer-center" => "",
        "footer-right"  => "",
    );

    $category_str = array("N"=>"north", "NE"=>"north-east","E"=>"east","SE"=>"south-east",
        "S"=>"south", "SW"=>"south-west","W"=>"west","NW"=>"north-west",);

    if ($pagestate == "init")
    {
        // get event information if eventid passed
        if ($_SESSION['check_event'] )
        {
            require_once ("{$loc}/common/classes/event_class.php");
            $event_o = new EVENT($db_o);
            $_SESSION['event'] = false;

            if ($eventid)
            {
                $_SESSION['event'] = $event_o->get_event_byid($eventid, false);
            }
            else
            {
                $events = $event_o->get_events_bydate(date("Y-m-d"), "", $requiredtype = "racing");
                if ($events)
                {
                    $_SESSION['event'] = $events[0];
                }
            }
        }

        // get tide information
        $tide = "";
        if ($_SESSION['event'])
        {
            $tide = $_SESSION['event']['tide_time']." - ".$_SESSION['event']['tide_height']."m";
        }

        // display course picker
        $htm = state_init($tide, $_SESSION['event'], $_SESSION['wind_info']);
    }

    elseif ($pagestate == "coursedetail")
    {
        // get all courses that match category
        $course_list = $db_o->db_get_rows("SELECT * FROM t_course WHERE category = '$category' ORDER BY category asc, sort asc");

        if (empty($course_list))
        {
            // report no courses
            $htm = state_coursedetail_none($category);
        }
        else
        {
            // pick course to display and get details for that course
            $course = array();
            if ($courseid)
            {
                foreach($course_list as $row)
                {
                    if ($courseid == $row['id']) { $course = $row; }
                }
            }
            else  // just take the first one
            {
                $course = $course_list[0];
                $courseid = $course_list[0]['id'];
            }

            if ($course)
            {
                // decode instructions for course
                $course['info'] = decode_instructions($course['info']);

                // get subcourse information
                $subcourses = array();
                $subcourses = $db_o->db_get_rows("SELECT * FROM t_coursedetail WHERE courseid = '$courseid' ORDER BY sort asc");

                // modify courses for race format if option is set
                if ($_SESSION['check_eventformat']) { $subcourses = modify_course_for_format($_SESSION['event'], $subcourses); }

                foreach ($subcourses as $j => $subcourse)
                {
                    $subcourses[$j]['fleets'] = decode_fleets($subcourse['fleets']);     // decode fleets
                    $subcourses[$j]['start']  = decode_starts($subcourse['start']);       // decode starts
                    $subcourses[$j]['buoys']  = decode_buoys($subcourse['buoys']);        // decode buoys
                    $subcourses[$j]['laps']   = decode_laps($subcourse['laps']);           // decode laps
                }
            }

            // render page
            $htm = state_coursedetail($category, $course_list, $courseid, $course, $subcourses, $_SESSION['event']);
        }
    }

    elseif ($pagestate == "courseprint")
    {
        // get course and details
        $course = $db_o->db_get_row("SELECT * FROM t_course WHERE id = $courseid ORDER BY category asc, sort asc");

        // decode instructions for course
        $course['info'] = decode_instructions($course['info']);

        // get fleet information
        $subcourses = array();
        $subcourses = $db_o->db_get_rows("SELECT * FROM t_coursedetail WHERE courseid = '$courseid' ORDER BY sort asc");

        // modify courses for race format if option is set
        if ($_SESSION['check_eventformat']) { $subcourses = modify_course_for_format($_SESSION['event'], $subcourses); }

        foreach ($subcourses as $j => $subcourse)
        {
            $subcourses[$j]['fleets'] = decode_fleets($subcourse['fleets']);     // decode fleets
            $subcourses[$j]['start']  = decode_starts($subcourse['start']);       // decode starts
            $subcourses[$j]['buoys']  = decode_buoys($subcourse['buoys']);        // decode buoys
            $subcourses[$j]['laps']   = decode_laps($subcourse['laps']);           // decode laps
        }

        // render
        $htm = state_courseprint($course, $subcourses, $_SESSION['event']);
    }
}
else
{
    // not a valid state
    $htm = state_error();
}
if ($category) { $pagefields['header-left'] = ucwords($category_str["$category"]." wind courses"); }

$pagefields['body'] = $htm;
echo $tmpl_o->get_template("basic_page", $pagefields );

function state_init($tide, $event, $wind_info)
{
    global $tmpl_o;

    // displays image map based on compass rose
    $link = "rm_coursefinder.php?pagestate=coursedetail&category=";
    $today = date("d-M-Y");

    // tide info
    empty($tide) ? $tide_str = "" : $tide_str = "<small>tide:</small> ".$tide ;

    // race display
    $race_str = "";
    if ($event)
    {
        $race_str = "<small>race:</small> ".$event['event_name'].
                    "<br><small>start:</small> ".$event['event_start'].
                    "<br><small>type:</small> &nbsp;".$event['race_name'];
    }

    $fields = array(
        "today" => date("d-M-Y"),
        "tide_str" => $tide_str,
        "race_str" => $race_str,
        "link" => $link
    );

    $htm = $tmpl_o->get_template("courseinit_page", $fields, array("wind_info_link"=>$wind_info));

    return $htm;
}


function state_coursedetail_none($category)
{
    global $tmpl_o;
    global $category_str;

    $_SESSION['event'] ? $eventid = $_SESSION['event']['id'] : $eventid = "" ;

    $fields = array(
        "wind" => ucwords($category_str["$category"]),
        "url"  => "rm_coursefinder.php?pagestate=init&eventid=$eventid"
    );

    $htm = $tmpl_o->get_template("no_courses", $fields, array());

    return $htm;
}


function state_coursedetail($category, $course_list, $courseid, $course, $subcourses, $event = array())
{
    global $tmpl_o;
    global $category_str;

    $wind_str = ucwords($category_str["$category"]);

    if (empty($course_list) or empty($courseid) or empty($course) or empty($subcourses))
    {
        if (empty($course_list))
        {
            $reason = "no courses defined for this wind direction";
        }
        elseif (empty($courseid) or empty($course))
        {
            $reason = "requested course information not available";
        }
        elseif(empty($subcourses))
        {
            $reason = "details for the requested course have not been defined";
        }
        else
        {
            $reason = "unknown problem";
        }

        $url = "rm_coursefinder.php?pagestate=init";
        if ($_SESSION['event']) { $url.= "&eventid={$_SESSION['event']['id']}"; }

        $htm = $tmpl_o->get_template("missing_course_detail", array("reason"=>ucfirst($reason), "url" => $url), array());
    }
    else // we have courses to display
    {
        // create course selection block - use template
        $htm_courseselection = $tmpl_o->get_template("course_selection", array("category"=>$category, "wind_str" => $wind_str),
            array("courses" => $course_list, "courseid" => $courseid, "category"=>$category));

        // create course board block - use template
        if (empty($course['buoy_url']))
        {
            $htm_courseboard = $tmpl_o->get_template("course_board", array( "wind-str" => $wind_str, "course-title" => $course['name']),
                array("mode"=>"colour", "subcourses" => $subcourses));
        }
        else
        {
            $htm_courseboard = $course['buoy_url'];
        }

        // create instructions block - use template
        if (empty($course['info_url']))
        {
            $htm_courseinstructions = $tmpl_o->get_template("course_instructions", array(), array("course" => $course));
        }
        else
        {
            $htm_courseinstructions = $course['info_url'];
        }

        // create picture block
        $htm_coursepicture = "";
        if (!empty($course['other_url']))
        {
            $htm_coursepicture = $tmpl_o->get_template("course_picture", array(), array("course" => $course));
        }

        $fields = array(
            "course-selection"    => $htm_courseselection,
            "course-board"        => $htm_courseboard,
            "course-instructions" => $htm_courseinstructions,
            "course-picture"      => $htm_coursepicture
        );

        $htm = $tmpl_o->get_template("coursedetail_page", $fields, array("courseid" => $courseid));
    }

    return $htm;
}

function state_courseprint($course, $details, $event = array())
{
    global $tmpl_o;

    $params = array();
    if (!empty($event))
    $params = array(
        "eventname"  => $event['event_name'],
        "eventdate"  => $event['event_date'],
        "eventstart" => $event['event_start'],
        "tidetime"   => $event['tide_time'],
        "tideheight" => $event['tide_height']
    );

    // create course selection block - use template
    $htm_courseselection = $tmpl_o->get_template("course_selection_print", array("name"=>$course['name'], "blurb" => $course['blurb']), $params);

    // create course board block - use template
    if (empty($course['buoy_url']))
    {
        $htm_courseboard = $tmpl_o->get_template("course_board", array(), array("mode"=>"monochrome", "subcourses" => $details));
    }
    else
    {
        $htm_courseboard = $course['buoy_url'];
    }

    // create instructions block - use template
    if (empty($course['info_url']))
    {
        $htm_courseinstructions = $tmpl_o->get_template("course_instructions", array(), array("course" => $course));
    }
    else
    {
        $htm_courseinstructions = $course['info_url'];
    }

    // create picture block
    $htm_coursepicture = "";
    if (!empty($course['other_url']))
    {
        $htm_coursepicture = $tmpl_o->get_template("course_picture", array(), array("course" => $course));
    }

    $fields = array(
        "course-selection"    => $htm_courseselection,
        "course-board"        => $htm_courseboard,
        "course-instructions" => $htm_courseinstructions,
        "course-picture"      => $htm_coursepicture
    );

    $htm = $tmpl_o->get_template("coursedetail_print", $fields, array());

    return $htm;
}


function state_error()
{
    $htm = "error";
    return $htm;
}

function decode_instructions($instructions_str)
{
    return explode("|", $instructions_str);
}

function decode_fleets($fleets_str)
{
    return explode("|", $fleets_str);
}

function decode_starts($starts_str)
{
    $start = array();
    $arr = explode("|", $starts_str);
    foreach($arr as $group)
    {
        $start[] = decode_group($group);
    }
    return $start;
}

function decode_buoys($buoys_str)
{
    $buoys = array();
    $arr = explode("|", $buoys_str);
    foreach ($arr as $group)
    {
        $buoys[] = decode_group($group);
    }
    return $buoys;
}

function decode_laps($laps_str)
{
    $laps = array();
    $arr = explode("|", $laps_str);
    foreach ($arr as $group)
    {
        $laps[] = decode_group($group);
    }
    return $laps;
}

function decode_group($group)
{
    $data = array();
    $colours = array("R"=>"red", "Y"=>"orange", "G"=>"green", "B"=>"blue",
                     "W"=>"white", "P"=>"red", "S"=>"green") ;

    $elem = explode("-", $group);
    $data['type'] = trim($elem[0]);
    isset($elem[1]) ? $col = trim(strtoupper($elem[1])) : $col = "W";
    //$col = trim(strtoupper($elem[1]));
    if (array_key_exists($col, $colours))
    {
        $data['colour'] = $colours[$col];
    }
    else
    {
        $data['colour'] = $colours['W'];
    }

    return $data;
}

function modify_course_for_format($event, $courses)
{

// note these are modification for the current four fleet standard courses

    if (!empty($event))
    {
        if ($event['race_name'] == "trophy")
        {
            // remove fast-assy and slow hcap courses
            $courses[0] = $courses[1];
            $courses[1] = $courses[3];
            unset($courses[2]);
            unset($courses[3]);

            // change details on dinghy course
            $courses[0]['fleets'] = "DINGHY";
            $courses[0]['start'] = str_replace("G", "Y", $courses[0]['start']);
            $courses[0]['laps'] = str_replace("G", "Y", $courses[0]['laps']);

            // change details on multihull course
            $courses[1]['start'] = str_replace("W", "G", $courses[1]['start']);
            $courses[1]['laps'] = str_replace("W", "G", $courses[1]['laps']);

        }
        elseif($event['race_name'] == "multi-junior" or $event['race_name'] == "club-pursuit")
        {
            // reorganise courses   -  fast becomes pursuit/junior
            $courses[0] = $courses[3];
            $courses[2] = $courses[1];
            unset($courses[3]);

            // change details on multihull course
            $courses[0]['fleets'] = "MULTI";
            $courses[0]['start'] = str_replace("W", "Y", $courses[0]['start']);
            $courses[0]['laps'] = str_replace("W", "Y", $courses[0]['laps']);

            // change details on junior course
            $courses[1]['fleets'] = "JUNIOR";
            $courses[1]['start'] = str_replace("R", "G", $courses[1]['start']);
            $courses[1]['laps'] = "1-G";

            // change details on pursuit course
            $courses[2]['fleets'] = "PURSUIT";
            $courses[2]['start'] = str_replace("G", "Y", $courses[2]['start']);
            $courses[2]['laps'] = "";
        }
        elseif($event['race_name'] == "evening-series")
        {
            unset($courses[0]);
            $courses = array_values($courses);

            // change details on fast course
            $courses[0]['start'] = str_replace("G", "Y", $courses[0]['start']);
            $courses[0]['laps'] = str_replace("G", "Y", $courses[0]['laps']);

            // change details on fast course
            $courses[1]['start'] = str_replace("R", "G", $courses[1]['start']);
            $courses[1]['laps'] = str_replace("R", "G", $courses[1]['laps']);

            // change details on multihull course
            $courses[2]['start'] = str_replace("W", "R", $courses[2]['start']);
            $courses[2]['laps'] = str_replace("W", "R", $courses[2]['laps']);
        }
    }

//    echo "<pre>EXIT ".print_r($courses,true)."</pre>";
//    exit();
    return $courses;
}


