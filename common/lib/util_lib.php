<?php
/**
 * util_lib.php - utility functions
 * 
 * Utility Functions
 * 
 *      Error   ----------------------------------
 *          u_exitnicely      handles unexpected system error
 *      
 *      Logging ----------------------------------
 *          u_writelog        writes message to system or event log
 *          u_writedbg        writes message to debug log
 *          u_argumentdbg     writes function argument values to debug log
 * 
 *      Initialisation ---------------------------
 *          u_initconfigfile  loads content of config file into session
 *          u_initsetparams   sets language, mode and debug into session
 *          u_initpagestart   opens session and sets error reporting
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * 
 * %%copyright%%
 * %%license%%
 *  
 * 
 */

// NOT SORTED YET

function u_checkarg($arg, $mode, $check, $default = "")
{
    // tests $_REQUEST argument for existence and sets values or defaults accordingly.
    // e.g
    // $external = u_checkarg("state", "setbool", "init", true)
    // $action['event'] = u_checkarg("event", "set", "", 0)

    $val = "";
    if (key_exists($arg, $_REQUEST)) {  // if key exists do checks according to mode
        if ($mode == "set") {
            empty($_REQUEST[$arg]) ? $val = $default : $val = $_REQUEST[$arg];
        } elseif ($mode == "setnotnull") {
            empty($_REQUEST[$arg]) ? $val = false : $val = $_REQUEST[$arg];
        } elseif ($mode == "checkset") {
            $_REQUEST[$arg] == $check ? $val = $_REQUEST[$arg] : $val = $default;
        } elseif ($mode == "setbool") {
            $_REQUEST[$arg] == $check ? $val = true : $val = false;
        } elseif ($mode == "checkint") {
            ctype_digit($_REQUEST[$arg]) ? $val = $_REQUEST[$arg] : $val = false;
        } elseif ($mode == "checkintnotzero") {
            ctype_digit($_REQUEST[$arg]) and $_REQUEST[$arg] ? $val = $_REQUEST[$arg] : $val = false;
        }

    } else {  // if key doesn't exist set to default if provided
        empty($default) ? $val = "" : $val = $default;
    }

    return $val;
}

function u_htmlflush($bufr)
{
    $bufr = str_repeat("\n",4096);
    return $bufr;
}

function u_2darray_search($array, $field, $match)
{
    $keys = array_keys(array_column($array, $field), $match);

    return $keys;
}

function u_array_column($data, $key)
{
    /* similar to std PHP array_column function except uses row key as index
    */
    $column = array();
    foreach($data as $origKey => $value)
    {
        if (isset($value[$key]))
        {
            $column[$origKey] = $value[$key];
        }
    }
    return $column;
}

function u_array_orderby()
// sorts array by multiple columns
// $out_arr = array_orderby($in_arr, 'key1', SORT_DESC, 'key2', SORT_ASC);
{
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
        if (is_string($field)) {
            $tmp = array();
            foreach ($data as $key => $row)
                $tmp[$key] = $row[$field];
            $args[$n] = $tmp;
        }
    }
    $args[] = &$data;
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
}

function u_array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
    /*
     * usage:
     *
$data[] = array('volume' => 67, 'edition' => 2);
$data[] = array('volume' => 86, 'edition' => 1);
$data[] = array('volume' => 85, 'edition' => 6);
$data[] = array('volume' => 98, 'edition' => 2);
$data[] = array('volume' => 86, 'edition' => 6);
$data[] = array('volume' => 67, 'edition' => 7);

u_array_sort_by_column($data, "edition", SORT_DESC);
     */
    $sort_col = array();
    foreach ($arr as $key=> $row) {
        $sort_col[$key] = $row[$col];
    }

    array_multisort($sort_col, $dir, $arr);
}

function u_getitem(&$var, $default=null)
{
    return isset($var) ? $var : $default;
}

function u_format($msg, $vars)
/* php function equivalent to the python format
     u_format("{} and {}", array("pinky", "perky")
*/
{
    $vars = (array)$vars;

    $msg = preg_replace_callback('#\{\}#', function($r){
        static $i = 0;
        return '{'.($i++).'}';
    }, $msg);

    return str_replace(
        array_map(function($k) {
            return '{'.$k.'}';
        }, array_keys($vars)),

        array_values($vars),

        $msg
    );
}

function u_check($arr, $label="check")
{
    $html =  "$label: <pre>".print_r($arr,true)."</pre><br>";
    return $html;
}

function u_conv_secstotime($secs)
{
    $secs = intval($secs);
    if ($secs < 3600)
    {
        $time = gmdate("i:s", $secs);
    }
    else
    {
        $time = gmdate("h:i:s", $secs);
    }
    
    return $time;
}

function u_conv_timetosecs($time)
{
    return strtotime("1970-01-01 $time UTC");
}

function u_conv_boat($class, $sailnum, $code, $length=0)
{
    if ($length == 0)
    {
        empty($code)? $boat = $class : $boat = $code;
    }
    else
    {
        strlen($class)>$length? $boat = u_truncatestring(rtrim($class), $length)."..." : $boat = $class ;
    }
    $boat.= " ".$sailnum;

    return $boat;
}

function u_conv_team($helm, $crew, $length=0)
{
    $team = $helm;
    if ($crew != "")
    {
        $team.= " / ".$crew;
    }
    if ($length == 0)
    {
        $team = rtrim($team);
    }
    else
    {
        $team = u_truncatestring(rtrim($team), $length);
    }

    return $team;
}

function u_conv_eventname($eventname)
{
    $words = explode(" ",$eventname);
    $last_key = count($words) - 1;
    if ($last_key > 0 and strtolower($words[$last_key] != "race"))
    {
        $eventname = ucwords($eventname." race");
    }
    return $eventname;
}

function u_change($newvalue, $oldvalue)
    // if $newvalue has a value and it is different from $oldvalue - return the $newvalue
    // otherwise return an empty string
{
    $change = "";
    if (!empty($newvalue))
    {
        if (strtolower($newvalue) != strtolower($oldvalue))
        {
            $change  = $newvalue;
        }
    }
    return $change;
}

function u_pick ($newvalue, $oldvalue)
    // if $newvalue has a value use that otherwise use $oldvalue
{
    if (!empty($newvalue))
        $pick = $newvalue;
    else
    {
        $pick = $oldvalue;
    }
    return $pick;
}

function u_conv_result($code, $code_type, $points)
{
    $val = number_format((float)$points, 1, '.', '');
    if (empty($code)) {
        $result = "$val";
    } else {
        $code_type == "series" ? $result = "$code" : $result = "$val ($code)";
    }

    return $result;
}

function u_plural( $amount, $singular = '', $plural = 's' ) {
    if ( $amount === 1 ) {
        return $singular;
    }
    return $plural;
}

function u_numordinal ($number)
{
     if (key_exists("lang", $_SESSION))
     {
         if ($_SESSION['lang']=="en")
         {
             $ends = array('th','st','nd','rd','th','th','th','th','th','th');
         }
         elseif($_SESSION['lang']=="fr")
         {
             $ends = array('eme','er','eme','eme','eme','eme','eme','eme','eme','eme');
         }
         else
         {
             $ends = array('th','st','nd','rd','th','th','th','th','th','th');
         }
     }
     else
     {
         $ends = array('th','st','nd','rd','th','th','th','th','th','th');
     }

     if (($number %100) >= 11 && ($number%100) <= 13)
     {
        $abbreviation = $number.$ends[0];
     }
     else
     {
        $abbreviation = $number.$ends[$number % 10];
     }

     return $abbreviation;
}

function u_truncatestring ($string, $length, $dots = "...") 
{
    if ($length == 0)
    {
        return $string;
    }
    else
    {
        return (strlen($string) > $length) ? substr($string, 0, $length - strlen($dots)) . $dots : $string;
    }

}

function u_daysdiff($datestr1, $datestr2)
{
    // gets no. of days between two dates
    $date1 = new DateTime(date("Y-m-d", strtotime($datestr1)));
    $date2 = new DateTime(date("Y-m-d", strtotime($datestr2)));
    $interval = $date1->diff($date2);

    return $interval->days;
}

function u_roundminutes($time, $resolution)
/*
 * rounds minutes in time (hh:mm) to nearest number of minutes defined by
 * resolution (e.g if resolution is 30 the time will be rounded to nearest 30 minutes
 */
{
    $time = strtotime($time);
    $round = $resolution*60;
    $rounded = round($time / $round) * $round;
    return date("H:i", $rounded);
}

function u_timeresolution($resolution, $time)
/*
    resolves time (in decimal minutes) to nearest minute, 30 seconds, or
    10 seconds based on desired resolution.  Used in calculating start times for pursuit race
    
    resolution:  required resolution |60|30|10|
    time:        time in decimal minutes
    
    returns time as string with appropriate resolution applied.
*/
{
    $rem = ($time - floor($time)) * 60;
    if ($resolution=="10")
    {        
        if     ($rem<5)                  { $start = floor($time).":00"; }
        elseif ($rem>=5 AND $rem<15)     { $start = floor($time).":10"; }
        elseif ($rem>=15 AND $rem<25)    { $start = floor($time).":20"; }
        elseif ($rem>=25 AND $rem<35)    { $start = floor($time).":30"; }
        elseif ($rem>=35 AND $rem<45)    { $start = floor($time).":40"; } 
        elseif ($rem>=45 AND $rem<55)    { $start = floor($time).":50"; }                                                
        else                             { $start = ceil($time).":00"; }            
    }
    elseif ($resolution=="30")
    {
        if     ($rem<15)                 { $start = floor($time).":00"; }
        elseif ($rem>=15 AND $rem<45)    { $start = floor($time).":30"; }
        else                             { $start = ceil($time).":00"; }
    }
    else  // time to nearest minute
    {
        $start = round($time)." "; 
    }
    return $start;
}
 


// ------ SYSTEM FUNCTIONS --------------------------------------------------------------------------------------------

/**
 * u_exitnicely()
 * 
 * controlled closedown of a script with a standard message display - writes
 * closing message to system and event logs
 * 
 * @param string    $script      name of script where problem occured
 * @param int       $eventid     eventid or 0 if not event 
 * @param string    $error       description of error
 * @param string    $action      suggested action to take
 * @param array    $attr        array with filename, function, line no., calling script, calling arguments
 * @return void
 */
 function u_exitnicely($script, $eventid, $error, $action, $attr = array())
{
    global $loc;

    empty($_SESSION['racebox_theme']) ? $theme = $_SESSION['racebox_theme'] : $theme = "flatly_";
    $title = "raceManager";
    if (empty($action)) { $action = "Closing the browser completely and restarting the part of raceManager you were using"; }
    $function = $attr['function'];

    $line = $attr['line'];

    empty($attr['calledby']) ? $calledby = "" : $calledby = "- called by {$attr['calledby']}";

    $argtxt = "";
    if (!empty($calledby) and !empty($attr['args']))
    {
        foreach ($attr['args'] as $i=>$arg) { $argtxt.= $i.": ".$arg.", "; }
        $argtxt = " with args [ ".rtrim($argtxt, ", ")." ]";
    }


    echo <<<EOT
    <!DOCTYPE html><html lang="en">
    <head>
            <title>$title</title>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="description" content="">
            <meta name="author" content="">

            <link   rel="shortcut icon"    href="$loc/common/images/favicon.ico">
            <link   rel="stylesheet"       href="$loc/common/oss/bootstrap341/css/{$theme}bootstrap.min.css" >
            <script type="text/javascript" src="$loc/common/oss/jquery/jquery.min.js"></script>
            <script type="text/javascript" src="$loc/common/oss/bootstrap341/js/bootstrap.min.js"></script>
            <script type="text/javascript" src="$loc/common/oss/bs-growl/jquery.bootstrap-growl.min.js"></script>
    </head>
    <body class="{body_attr}">
        <nav class="navbar navbar-default">
          <div class="container-fluid">
            <div class="navbar-header navbar-brand">raceManager</div>           
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav"></ul>
              <ul class="nav navbar-nav navbar-right"><li><a href="#">FATAL SYSTEM ERROR</a></li></ul>
            </div>
          </div>
        </nav>     
        
        <div class="container" style="margin-top: 50px;">
            <div class="jumbotron">
              <h2 class="text-info">Oops sorry... &nbsp;&nbsp;we have encountered an unexpected error</h2>
              <p class="text-primary">$error</p>
            </div>
            <div>
                <div class="alert alert-info" style="margin-top: 60px;">
                  <h3>You could try ...</h3> 
                  <p class="lead">$action<br>
                  - if this doesn't work contact your raceManager administrator</p>
                </div>
            </div>
            <div class="well well-lg" style="margin-top: 80px;">
                The problem is probably due to some frankly shoddy coding by the deranged system developer!! - the details below might help him find and fix the problem.<br>
                $script $function (line $line) $calledby $argtxt 
            </div>
        </div>
    </body>
    </html>
EOT;
    
    $logmsg = "**** FATAL ERROR - $error".PHP_EOL."script: $script, event: $eventid, function: $function, line: $line, calledby: $calledby, args: $argtxt";
    u_writelog($logmsg, 0);                                // write to system log
    if ($eventid!=0) { u_writelog($logmsg, $eventid); }    // write to event log
    exit();
}



// ------ LOGGING FUNCTIONS ------------------------------------------------------------------------------------------
/**
 * u_writelog()
 * 
 * Outputs message to system or event log
 * 
 * @param string $logmessage       message for log
 * @param int    $eventid          eventid - used to identify correct system log
 * @return void
 */
function u_writelog($logmessage, $eventid)
{
    $log = date('H:i:s')." -- ".$logmessage.PHP_EOL;
    if (empty($eventid))
        { error_log($log, 3, $_SESSION['syslog']); }
    else
        { error_log($log, 3, $_SESSION["e_$eventid"]['eventlog']); }

}

/**
 * u_writedbg()
 * 
 * Outputs message to debug log
 * 
 * @param string $dbgmessage      message to be output to debug log
 * @param string $script          name of script
 * @param string $function        name of script
 * @param int    $line            line in script
 * @return void
 */
function u_writedbg($dbgmessage, $script, $function, $line )
{
   $log = date('H:i:s')." -- [script: $script function: $function line: $line]".PHP_EOL.$dbgmessage.PHP_EOL;
   error_log($log, 3, $_SESSION['dbglog']);
}

/**
 * u_argumentdbg()
 * 
 * lists the value for each argument on entry to a function
 * and outputs to the debug log
 * 
 * @param array  $arg_list      array with argument details
 * @param string $file        name of script
 * @param string $function      name of function
 * @param int    $line          line in script

 * @return void
 */

function u_requestdbg($arg_list, $file, $function, $line, $inline=false)
{
    $msg = " <pre>".print_r($arg_list,true)."</pre>";
    if ($inline)
    {
        echo "Debug [$file/$function/$line]".$msg;
    }
    else
    {
       u_writedbg("Arguments...".$msg, $file, $function, $line);
    }
}

function u_startsyslog($scriptname, $app, $sessionid = "")
{
    $msg = "$app START: $scriptname --------------------------";
    if (!empty($sessionid)) { $msg.= " [session: $sessionid]"; }
    u_writelog($msg, 0);
}


function u_starteventlogs($scriptname, $eventid, $mode)
{
    $_SESSION["e_$eventid"]['eventlog'] = "../logs/event/event_$eventid.log";        // setup event log e.g  event_907.log
    u_writelog("initialising event: $scriptname --- [eventid: $eventid] ", 0);       // add system log entry
    u_writelog(date("Y-m-d")." EVENT START: $scriptname --- [eventid: $eventid mode: $mode] ", $eventid);
}


function u_sessionstate($scriptname, $reference, $file_dir, $eventid)
{
    $filename = $file_dir."/"."session_{$reference}_{$eventid}.htm";
    $eventid==0 ? $title = "$scriptname: $reference " : $title = "$scriptname: $reference event: $eventid";
    
    $file = fopen($filename,"w");
    fwrite($file, $title);
    if ($eventid!=0)
    {
        fwrite($file, "<pre>".print_r($_SESSION["e_$eventid"],true)."</pre>");
    }
    else
    {
        fwrite($file, "<pre>".print_r($_SESSION,true)."</pre>");
    }
       
    fclose($file);
    
}


// ------ INITIALISATION FUNCTIONS ----------------------------------------------------------------------------------

function u_initialisation($app_cfg_file, $loc, $scriptname)
{
    $status = true;
    $_SESSION['app_init'] = false;

    // set application config file content into SESSION
    if (is_readable($app_cfg_file))
    {
        include ("$app_cfg_file");
    }
    else
    {
        $status = false;
        u_exitnicely("util_lib.php", 0,"File error - application configuration file ($app_cfg_file) does not exist or is unreadable",
            "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__,
                "calledby" => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[1]['function'], "args" => func_get_args()));
    }

    // process common (system wide) ini file
    $common_ini_file = "$loc/config/common.ini";
    if (is_readable($common_ini_file))
    {
        u_initconfigfile($common_ini_file);
    }
    else
    {
        $status = false;
        u_exitnicely("util_lib.php", 0,"File error - application configuration file ($common_ini_file) does not exist or is unreadable",
            "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__,
                "calledby" => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[1]['function'], "args" => func_get_args()));
    }
    // process application specific ini file
    if (!empty($_SESSION['app_ini'])) {
        $app_ini_file = "$loc/config/{$_SESSION['app_ini']}";
        if (is_readable($app_ini_file)) {
            u_initconfigfile($app_ini_file);
        } else {
            $status = false;
            u_exitnicely("util_lib.php", 0,"File error - application configuration file ($app_ini_file) does not exist or is unreadable",
                "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__,
                    "calledby" => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[1]['function'], "args" => func_get_args()));
        }
    }
    // deal with php ini setting changes
    if (!empty($_SESSION['session_timeout']))
    {
        ini_set('session.gc_maxlifetime', $_SESSION['session_timeout']);   // set sessions length
    }

    if ($status)
    {
        $_SESSION['app_init'] = true;
    }

    return $status;
}


function u_initconfigfile($inifile)
{
    global $scriptname;
    
    if (is_readable($inifile)) 
    {
        $ini = parse_ini_file($inifile, false);
        foreach ($ini as $key=>$data)
        {
           if ($key == "email_list")   // create array from list
           {
               $emails = explode(",", $data);
               $i = 0;
               foreach ($emails as $email)
               {
                  $_SESSION["email"][$i] = $email;
                  $i++;
               }
           }
           elseif ($key == "sys_copyright")
           {
               $_SESSION["sys_copyright"] = "$data ".date("Y");
           }
           else
           {
              $_SESSION["$key"] = $data;
           }
        }
    }
    else
    {
        u_exitnicely("util_lib.php", 0,"File error - application initialisation file ($inifile) does not exist or is unreadable",
            "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__,
                "calledby" => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[1]['function'], "args" => func_get_args()));
    }
}


function u_initsetparams($lang, $mode, $debug)
{
    $_SESSION['lang'] = "en";    //<-- english as default

    $_SESSION['mode'] = "live";  //<-- live as default
    if (!empty($mode)) {
        if ($mode=="demo") { $_SESSION['mode']="demo"; }
    }
    
    $_SESSION['debug'] = 0;     //<-- no debug as default
    if (!empty($debug)) {
        if (is_numeric($debug) AND $debug>=0 AND $debug<=2) { $_SESSION['debug'] = $debug; }
    }
}


function u_initpagestart($eventid, $page, $change_page)
{

    date_default_timezone_set($_SESSION['timezone']);
    // FIXME probably remove following line
    error_log(date('H:i:s')." -- {$_SESSION['app_name']} $page ------------ [session: ".session_id()."]". PHP_EOL, 3, "../logs/sys/sys_".date("Y-m-d").".log");
    
    // error reporting - full for development
    $_SESSION['sys_type'] == "live" ? error_reporting(E_ERROR) : error_reporting(E_ALL);
    
    // add line to indicate change of page (uses arg and check against session variable)
    if (!empty($eventid))
    {
        if (!array_key_exists("current_page", $_SESSION["e_$eventid"])) { $_SESSION["e_$eventid"]['current_page'] = ""; }
        $_SESSION["e_$eventid"]['current_page'] != $page ? $page_change = true : $page_change = false;
        if ($change_page and $page_change)
        {
            u_writelog("**** $page page --- [session: ".session_id()."]", $eventid);
            $_SESSION["e_$eventid"]['current_page'] = $page;
        }
    }
}


function u_sendmail($from, $to, $subject, $message)
{
    global $loc;
    
    session_start();
    include ("{$loc}/common/lib/mail_lib.php");
    require ("{$loc}/common/oss/phpmailer/class.phpmailer.php");
	
    // create email body and send it
    $format      = "html";
    $event       = $_REQUEST['openmeeting'];
    $name        = $_REQUEST['name'];
    $from        = $_SESSION['smtp_user'];
    $fromtxt     = "no-reply";
    $reply       = $from;
    $replytxt    = "reply-to";
    $body        = nl2br($message);   // adds html line breaks
    $email_to    = $to;	
    $email_cc    = array();
    $email_bc    = array();
    $attachments = array();
    	
    // send email
    $sent = sendEmail($format, $from, $fromtxt, $reply, $replytxt, $email_to, $email_cc, $email_bc, $attachments, $subject, $body);
    
    if ($sent==0)
    { 
        //echo "contact form processed";
        echo "ok";
        return true; 
    }
    else
    { 
        echo "contact form processing failed"; 
        if ($sent==-1){ echo ": reply email address not provided"; }
        elseif ($sent==-2){ echo ": target email address not defined"; }
        elseif ($sent==-3){ echo ": no subject provided"; }
        elseif ($sent==-4){ echo ": no message provided"; }
        elseif ($sent==-4){ echo ": email transmission failed"; }
        else { echo ": undefined error"; }
        return false; 
    }
}


function u_getfleetcontext($eventid, $page)
{
    if (empty($_SESSION["e_$eventid"]['fleet_context'])) 
    {
        $_SESSION["e_$eventid"]['fleet_context'] = "fleet1";
    }
    return $_SESSION["e_$eventid"]['fleet_context'];
}


function u_dropdown_resultcodes($codes, $detail, $link)
{
    $bufr = <<<EOT
    <li><a href="{$link}&code=" > -- clear code --</a></li>
    <li role="separator" class="divider" style="padding: 0px"></li>
EOT;
    foreach ($codes as $code)
    {       
        $bufr.= <<<EOT
            <li><a href="{$link}&code={$code['code']}"><b>{$code['code']}</b>: {$code["$detail"]}</a></li>
EOT;
    }
   
    return $bufr;
}


function u_geteventname($name, $number, $length)
{
    if ($length > 0 and strlen($name)>$length)
    {
        $name = substr($name, 0, $length)."&hellip;";
    }
    if (!empty($number))
    {
        $name.= " - $number";
    }
    
    return $name;   
}

function u_getseriesname($name, $date="")
{
    return $name."-".date("y", strtotime($date));
}

function u_stripseriesname($name)
{
    $series_name = $name;
    if (substr($name, -3, 1)=="-")    // probably a full name
    {
        $series_name = substr($name, 0, -3);
    }

    return $series_name;
}

function u_gettimelimit_str($abs,$rel)
{
    if (!empty($abs) AND !empty($rel))
    {
        $str = "$abs mins or $rel mins after leader";
    }
    elseif (!empty($abs))
    {
        $str = "$abs mins";
    }
    elseif (!empty($rel))
    {
        $str = "$rel mins after leader";
    }
    else
    {
        $str = "no time limit";
    }
    return $str;
}


function u_getclasses_str($db, $fleetcfg)
{
    
    $str = "";
    if ($fleetcfg['onlyinc'])
    {
        $str.= str_replace(",", ", ", str_replace(", ", ",", $fleetcfg['classinc'])); 
    }
    else
    {
        // hull type
        if (!empty($fleetcfg['hulltype']))
        {
            $str.= " ".$db->db_getsystemlabel("class_category", $fleetcfg['hulltype'])."<br>";
        }       
        
        // py
        if (!empty($fleetcfg['min_py']) OR !empty($fleetcfg['max_py']))
        {
            $str.= "";
            if (!empty($fleetcfg['max_py']) and empty($fleetcfg['min_py']))
            {
                $str.= " max PY {$fleetcfg['max_py']}<br>";
            }
            elseif(!empty($fleetcfg['min_py']) and empty($fleetcfg['max_py']))
            {
                $str.= " min PY {$fleetcfg['min_py']}<br>";
            }
            else
            {
                $str.= " PY {$fleetcfg['min_py']} - {$fleetcfg['max_py']}<br>";
            }
        }
        
        // spin type
        if (!empty($fleetcfg['spintype']))
        {
            $str.= " ".$db->db_getsystemlabel("class_spinnaker", $fleetcfg['spintype'])."<br>";
        }
        
        // crew number
        if (!empty($fleetcfg['crew']))
        {
            $str.= " ".$db->db_getsystemlabel("class_crew", $fleetcfg['crew'])."<br>";
        }       
        
        // classes
        if (!empty($fleetcfg['classinc']))
        {
            if (strlen($str)>0)
            {
                $str.=" + ";
            }
            $str.= " [ ".str_replace(",", ", ", str_replace(", ", ",", $fleetcfg['classinc']))." ] "; 
        }
        if (!empty($fleetcfg['classexc']))
        {
            $str.= " but excluding [ ".str_replace(",", ", ", str_replace(", ", ",", $fleetcfg['classexc']))." ] ";
        }
    }    
    return $str;
}


function u_getcompetitors_str($db, $fleetcfg)
{
    
    $str = "";
     
    if (!empty($fleetcfg['groupinc']) OR !empty($fleetcfg['min_skill']) OR !empty($fleetcfg['max_skill']) OR !empty($fleetcfg['min_helmage']) OR !empty($fleetcfg['max_helmage']))
    {        
        // competitor groups
        if (!empty($fleetcfg['groupinc']))
        {
            $str.= " groups [ ".str_replace(",", ", ", str_replace(", ", ",", $fleetcfg['groupinc']))."]<br>";
        }
        
        // ages
        if (!empty($fleetcfg['min_helmage']) OR !empty($fleetcfg['max_helmage']))
        {
            $str.= "age limits [ ";
            if (!empty($fleetcfg['max_helmage']) and empty($fleetcfg['min_helmage']))
            {
                $str.= " up to {$fleetcfg['max_helmage']} ]<br>";
            }
            elseif(!empty($fleetcfg['min_helmage']) and empty($fleetcfg['max_helmage']))
            {
                $str.= " {$fleetcfg['min_helmage']} and above ]<br>";
            }
            else
            {
                $str.= " between {$fleetcfg['min_helmage']} and {$fleetcfg['max_helmage']} ]<br>";
            }
        }
        
        // skill
        if (!empty($fleetcfg['min_skill']) OR !empty($fleetcfg['max_skill']))
        {
            $str.= "skill level [ ";
            if (!empty($fleetcfg['max_skill']) and empty($fleetcfg['min_skill']))
            {
                $str.= " up to level {$fleetcfg['max_skill']} ]<br>";
            }
            elseif(!empty($fleetcfg['min_skill']) and empty($fleetcfg['max_skill']))
            {
                $str.= "level {$fleetcfg['min_skill']} and above ]<br>";
            }
            else
            {
                $str.= " between {$fleetcfg['min_skill']} and {$fleetcfg['max_skill']} ]<br>";
            }
        }
    } 
    if (empty($str)) { $str = "no restrictions"; }
    return $str;
}


// create string for team name
function u_getteamname($helm, $crew, $chars = 0)
{
    $team = $helm;
    if (!empty($crew))
    {
        $team.= " \\ $crew";
    }
    if($chars > 0 and strlen($team) > $chars)
    {
        $team = substr($team, 0, $chars)."&hellip;";
    }
    return $team;
}


function u_getclubname($club_str)
{
    $club_str = ucwords(strtolower($club_str));
    $club_str = str_replace(" Yacht Club", " YC", $club_str);
    $club_str = str_replace(" Sailing Club", " SC", $club_str);
    $club_str = str_replace(" Yc", " YC", $club_str);
    $club_str = str_replace(" Sc", " SC", $club_str);

    return $club_str;

}


function u_getwind_str($wind = array())
{
    $wind_str = "";
    if (!empty($wind))
    {
        if (!empty($wind['wd_start'])) { $wind_str.= $wind['wd_start']." "; }
        if (!empty($wind['ws_start'])) { $wind_str.= $wind['ws_start']." mph"; }
        $wind_str.= " -> ";
        if (!empty($wind['wd_end'])) { $wind_str.= $wind['wd_end']." "; }
        if (!empty($wind['ws_end'])) { $wind_str.= $wind['ws_end']." mph"; }
    }

    return rtrim($wind_str, "- ");
}


// new growl functions
function u_growlSet($eventid, $page, $params, $data=array())
{
    if ($data){
        $params['msg'] = vsprintf($params['msg'], $data);
    }
    $_SESSION["e_$eventid"]['growl']["$page"][] = $params;
}


function u_growlUnset($eventid, $page="")
{
    if (empty($page))   // unset all growls for this event
    {
        unset($_SESSION["e_$eventid"]['growl']);
    }
    else                // unset growls for this page
    {
        unset($_SESSION["e_$eventid"]['growl']["$page"]);
    }
}

function u_growlProcess($eventid, $page)
{
    //echo "<pre>Current Growls: ".print_r($_SESSION["e_$eventid"]['growl'],true)."</pre>";

    key_exists("racebox_growl_display_time", $_SESSION) ? $growl_delay = $_SESSION['racebox_growl_display_time'] : $growl_delay = 4000;

    $att_default = array(
       "msg"             => "oops no message!",
       "glyph"           => true,
       "ele"             => "body",
       "type"            => "info",
       "offset_from"     => "bottom",
       "offset_amount"   => "20",
       "align"           => "left",
       "width"           => "800",
       "delay"           => $growl_delay,
       "allow_dismiss"   => "true",
       "stackup_spacing" => "20",
    );

    $glyph = array("success" => "thumbs-up", "warning" => "alert", "info" => "info-sign", "primary" => "question-sign", "danger" => "thumbs-down");
    // "danger"  => "<span class='glyphicon glyphicon-thumbs-down'></span>&nbsp;&nbsp;&nbsp;",

    $html = "";
    // check that we have current growl(s) for this page
    if (!empty($_SESSION["e_$eventid"]['growl']["$page"]))
    {
        $jscript = "";
        foreach ($_SESSION["e_$eventid"]['growl']["$page"] as $growl)
        {
            // merge default setting with growl specific settings
            $att = array_merge($att_default, $growl);

            // add contextual glyph if defined
            array_key_exists($att["type"], $glyph) ? $glyph_htm = "<span class='glyphicon glyphicon-".$glyph["{$att["type"]}"]."'></span>" : $glyph_htm = "" ;

            // set message
            $msg = "<div class='growl-container'><div class='growl-left'>$glyph_htm</div><div class='growl-right'>{$att['msg']}</div></div>";

            $jscript.= <<<EOT
                    $.bootstrapGrowl("{$msg}", {
                        ele:'{$att['ele']}',
                        type: '{$att['type']}',
                        offset: {from: '{$att['offset_from']}', amount: {$att['offset_amount']}},
                        align: '{$att['align']}',
                        width: '{$att['width']}',
                        delay: {$att['delay']},
                        allow_dismiss: {$att['allow_dismiss']},
                        stackup_spacing: {$att['stackup_spacing']}
                    });
EOT;
        }

        $html.= <<<EOT
        <script>
        $(function() {
           $jscript             
        });
        </script>
EOT;

        u_growlUnset($eventid, $page);   // now unset growls for this page
    }
    return $html;
}


function u_selectcodelist($codelist, $selected = "", $nocode = true)
{
    $bufr = <<<EOT
        <option value="" disabled selected hidden>&hellip; please select &hellip;</option>
EOT;

    if ($nocode)
    {
        $bufr.= <<<EOT
        <option value="">&nbsp;</option>
EOT;
    }

    foreach ($codelist as $opt)
    {
        $selectstr = "";
        if (($selected=="default" AND $opt['defaultval']) OR  ($selected == $opt['code']))
        { $selectstr="selected"; }
        $bufr.= "<option value=\"{$opt['code']}\" $selectstr>{$opt['label']}</option>";
    }
    return $bufr;
}

function u_selectlist($list, $selected="", $top = array())
    // added top to allow for 'other' type options
{
    $bufr = <<<EOT
        <option value="" disabled selected hidden>&hellip; please select &hellip;</option>
EOT;

    if (!empty($top))
    {
        foreach ($top as $k => $v)
        {
            $bufr.= "<option value=\"$k\" >$v</option>";
        }
    }

    foreach ($list as $key=>$opt)
    {
        ($opt == $selected) ? $selectstr = "selected" : $selectstr = "";
        $bufr.= <<<EOT
            <option value="$key" $selectstr>$opt</option>"
EOT;
    }
    return $bufr;
}

function u_folder_exist($folder)
{
    // Get canonicalized absolute pathname
    $path = realpath($folder);

    // If it exist, check if it's a directory
    return ($path !== false AND is_dir($path)) ? $path : false;
}

function u_sendJsonPost($url, $data)
{
    $ch = curl_init($url);                                                          // create a new cURL resource
    $payload = json_encode($data);                                                  // set data array into json format
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);                                 // attach encoded JSON string to the POST fields
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));   // Set the content type to application/json
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                 // return response instead of outputting
    $result = curl_exec($ch);                                                       // execute the POST request
    curl_close($ch);                                                                // close cURL resource

    return $result;
}

function u_ftpFiles($protocol, $ftp_env, $files)
{
    //echo "<pre>".print_r($ftp_env,true)."</pre>";

    error_reporting(0);  //error_reporting(E_ERROR | E_WARNING | E_PARSE);
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    
    $status = array();
    if ($protocol == 'ftp')
    {
        $status['log'] = "Transferring results files using ftp protocol<br>";
        $conn_id = ftp_connect($ftp_env['server']);   // set up basic connection
        if ($conn_id)
        {
            $status['connect'] = true;
            $status['log'].= " - connected to ftp server ({$ftp_env['server']})<br>";
            $login = ftp_login($conn_id, $ftp_env['user'], $ftp_env['pwd']); // login with username and password
            if ($login)
            {
                $status['login'] = true;
                $status['log'].= " - logged in to ftp server<br>";

                $files_transferred = 0;
                foreach($files as $key=>$file)   // loop over all files
                {
                    if (ftp_put($conn_id, $file['dest'], $file['source'], FTP_BINARY))   // transfer file
                    {
                        $files_transferred++;
                        $status['log'].= " - file transferred ({$file['source']})<br>";
                    }
                    else
                    {
                        $status['log'].= " - file transfer failed ({$file['source']})<br>";
                    }
                }
            }
            else
            {
                $status['login'] = false;
                $status['log'].= " - failed to login to ftp server ({$ftp_env['user']}/{$ftp_env['pwd']})<br>";
            }
            ftp_close($conn_id);  // close the FTP stream
        }
        else
        {
            $status['connect'] = false;
            $status['log'].= " - failed to connect to ftp server ({$ftp_env['server']})<br>";
        }
    }
    elseif ($protocol == 'sftp')
    {
        set_include_path(get_include_path() . PATH_SEPARATOR . "{$_SESSION['basepath']}/common/oss/phpseclib");
        include('Net/SFTP.php');
        define('NET_SFTP_LOGGING', NET_SFTP_LOG_COMPLEX);
        echo "<pre># 1</pre>";

        $status['log'] = "Transferring results files using sftp protocol<br>";

        define('NET_SFTP_LOGGING', NET_SFTP_LOG_COMPLEX);
        echo "<pre># 2</pre>";
        $sftp = new Net_SFTP($ftp_env['server']);
        if ($sftp)                                                    // fixme do I need this block
        {
            $status['connect'] = true;
            echo "<pre>connected....</pre>";
        }
        echo "<pre># 3</pre>";
        if ($sftp->login($ftp_env['user'], $ftp_env['pwd']))
        {
            echo "<pre># 4</pre>";
            $status['login'] = true;
            $status['log'].= " - logged in to sftp server<br>";

            $files_transferred = 0;
            foreach ($files as $key => $file)   // loop over all files
            {
                echo "<pre># 5</pre>";
                if ($sftp->put($file['dest'], $file['source'], NET_SFTP_LOCAL_FILE))   // transfer file
                {
                    $files_transferred++;
                    $status['log'].= " - file transferred ({$file['source']})<br>";
                }
                else
                {
                    $status['log'].= " - file transfer failed ({$file['source']})<br>";
                }
            }
        }
        else
        {
            echo "<pre># 6</pre>";
            $status['login'] = false;
            $status['log'].= " - failed to login to sftp server ({$ftp_env['user']}/{$ftp_env['pwd']})<br>";
        }
    }
    else
    {
        echo "<pre># 7</pre>";
        $status['login'] = false;
        $status['log'].= "File transfer not possible - protocol [$protocol] not supported<br>";
    }

    $status['transferred'] = $files_transferred;

    error_reporting(E_ERROR);
    return $status;
}


function u_get_result_codes_info($result_codes, $codes_used = array())
{
    $output = array();
    if (!empty($codes_used))
    {
        $output = array();
        foreach ($result_codes as $code=>$detail)
        {
            if (in_array($code, $codes_used)) { $output[$code] = $detail; }
        }
    }
    return $output;
}


