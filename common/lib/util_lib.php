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

function u_getitem(&$var, $default=null)
{
    return isset($var) ? $var : $default;
}

function u_format($msg, $vars)
/* php function equivalent to the python format */
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

function u_conv_result($code, $points)
{
    if ($code)
    {
        if ($code =="ZFP" OR $code =="SCP")
        {
            $result = "($points)";
        }
        else
        {
            $result = $code;
        }
    }
    else
    {
        $result = $points;
    }
    
    return $result;
}

function u_numordinal ($number)
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

function u_timeresolution($resolution, $time)
/*
    resolves time (in decimal minutes) to nearest minute, 30 seconds, or
    10 seconds based on desired resolution.
    
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
 * @param string    $error       standard system error code (e.g. sys001)
 * @param string    $msg         custom error message for this occurence 
 * @return void
 */
 function u_exitnicely($script, $eventid, $error, $msg)
{    
    global $lang;
    global $loc;
    
    $link = "";
    if (strpos($script, "rbx")!== false)
    {
       $link  = "<a class=\"btn btn-primary btn-sm\" href=\"rbx_pg_pickrace.php\" role=\"button\">Return</a>";  
    }
    
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
            <div class="jumbotron">
              <h1>{$lang['sys']['apology']}</h1>
              <p>{$lang['err']['sys000']}</p>
              <p class="text-danger">$error</p>
              <br>
              <p>
                  $link
                  <span class="pull-right"><small><i>$msg [script: $script]</i></small></span>
              </p>
            </div>
        </div>
      </body>
    </html>
EOT;
    
    $logmsg = "**** FATAL ERROR $error - {$lang['err']["$error"]}".PHP_EOL."script: $script, event: $eventid, message: $msg";
    
    u_writelog($logmsg, 0);                // write to system log
    if ($eventid!=0)
    {
        u_writelog($logmsg, $eventid);     // write to event log
    }    
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
    
    //return $status;
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


function u_startsyslog($scriptname, $app)
{
    global $loc;
    $_SESSION['syslog'] = "{$loc}/logs/syslogs/sys_".date("Y-m-d").".log";       // e.g  log/sys_2014-08-08.log
    //$_SESSION['dbglog'] = "{$loc}/logs/dbglogs/dbg_".date("Y-m-d_H-i").".log";  // e.g  log/debug_2014-08-08_14-50.log
    $_SESSION['dbglog'] = "{$loc}/logs/dbglogs/debug.log";  // FIXME - temp while developing

    u_writelog("$app START: $scriptname --------------------------", 0);
}


function u_starteventlogs($scriptname, $eventid)
{
    global $loc;
    
    $_SESSION["e_$eventid"]['eventlog'] = "{$loc}/logs/eventlogs/event_$eventid.log";     // setup event log e.g  event_907.log
    u_writelog("initialising event: $scriptname --- [eventid: $eventid] ", 0);              // add system log entry
    u_writelog(date("Y-m-d")." EVENT START: $scriptname --- [eventid: $eventid] ", $eventid); 
}


function u_sessionstate($scriptname, $reference, $eventid)
{
    $filename = "session_{$reference}_{$eventid}.htm";
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
/**
 * u_initconfigfile()
 * 
 * loads content off system configuration file - assumes no
 * sections in inifile  
 *
 * @param string  $inifile      path to inifile 
 * @return void
 */

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
           else
           {
              $_SESSION["$key"] = $data;
           }     
       }
    }
    else
    {
        u_exitnicely($scriptname,0,$lang['err']['sys003'],"application initialisation file ($inifile) does not exist"); 
    }
}


function u_initsetparams($lang, $mode, $debug)
{
    global $loc;
    
    if (!empty($lang)) 
    { 
        if (file_exists("$loc/config/$lang-racebox-lang.php"))
        {
            $_SESSION['lang'] = $lang;
        }
    }
    else
    {
        $_SESSION['lang'] = "en";
        u_writelog("ERROR: requested language file does not exist - using english default", 0);
    } 
    
    $_SESSION['mode'] = "live";  //<-- live as default
    if (!empty($mode))
    {
        if ($mode=="demo") { $_SESSION['mode']="demo"; }
    }
    
    $_SESSION['debug'] = 0;  //<-- no debug as default
    if (!empty($debug))
    {
        if (is_numeric($debug) AND $debug>=0 AND $debug<=2) { $_SESSION['debug'] = $debug; }
    }
    u_writelog("parameters: lang = {$_SESSION['lang']}, mode = {$_SESSION['mode']}, debug = {$_SESSION['debug']}", 0);    
}


function u_initpagestart($eventid, $page, $menu)
{
    session_start();                                    // start session
    date_default_timezone_set($_SESSION['timezone']);   // set timezone
    
    // error reporting - full for development
    $_SESSION['sys_type'] == "live" ? error_reporting(E_ERROR) : error_reporting(E_ALL);
    
    // add line to indicate change of page
    if ($menu)
    {
        unset($_REQUEST['menu']);
        u_writelog("**** $page page ", $eventid);
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
    <li><a href="$link&code=" > -- clear code --</a></li>
    <!-- <li role="separator" class="divider" style="padding: 0px"></li> -->
EOT;
    foreach ($codes as $code)
    {       
        $bufr.= <<<EOT
            <li><a href="$link&code={$code['code']}" >{$code['code']}: {$code["$detail"]}</a></li>
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
    global $lang;
    
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
            $str.= strtoupper(" {$lang['app']['not']}")." [ ".str_replace(",", ", ", str_replace(", ", ",", $fleetcfg['classexc']))." ] "; 
        }
    }    
    return $str;
}


function u_getcompetitors_str($db, $fleetcfg)
{    
    global $lang;
    
    $str = "";
     
    if (!empty($fleetcfg['groupinc']) OR !empty($fleetcfg['min_skill']) OR !empty($fleetcfg['max_skill']) OR !empty($fleetcfg['min_helmage']) OR !empty($fleetcfg['max_helmage']))
    {        
        // competitor groups
        if (!empty($fleetcfg['groupinc']))
        {
            $str.= " {$lang['app']['groups']} - ".$fleetcfg['groupinc']."<br>";
        }
        
        // ages
        if (!empty($fleetcfg['min_helmage']) OR !empty($fleetcfg['max_helmage']))
        {
            $str.= "{$lang['app']['age']}: ";
            if (!empty($fleetcfg['max_helmage']) and empty($fleetcfg['min_helmage']))
            {
                $str.= " {$lang['app']['up_to']} {$fleetcfg['max_helmage']}<br>";
            }
            elseif(!empty($fleetcfg['min_helmage']) and empty($fleetcfg['max_helmage']))
            {
                $str.= "{$fleetcfg['min_helmage']} {$lang['app']['and']} {$lang['app']['above']}<br>";
            }
            else
            {
                $str.= " {$fleetcfg['min_helmage']} - {$fleetcfg['max_helmage']}<br>";
            }
        }
        
        // skill
        if (!empty($fleetcfg['min_skill']) OR !empty($fleetcfg['max_skill']))
        {
            $str.= "skill: ";
            if (!empty($fleetcfg['max_skill']) and empty($fleetcfg['min_skill']))
            {
                $str.= " {$lang['app']['up_to']} {$lang['app']['level']} {$fleetcfg['max_skill']}<br>";
            }
            elseif(!empty($fleetcfg['min_skill']) and empty($fleetcfg['max_skill']))
            {
                $str.= "{$lang['app']['level']} {$fleetcfg['min_skill']} {$lang['app']['and']} {$lang['app']['above']}<br>";
            }
            else
            {
                $str.= " {$lang['app']['level']} {$fleetcfg['min_skill']} - {$fleetcfg['max_skill']}<br>";
            }
        }
    } 
    if (empty($str)) { $str = "{$lang['app']['any']}"; }   
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
    $att_default = array(
       "msg"             => "oops no message!",
       "glyph"           => true,
       "ele"             => "body",
       "type"            => "info",
       "offset_from"     => "bottom",
       "offset_amount"   => "50",
       "align"           => "right",
       "width"           => "600",
       "delay"           => "4000",
       "allow_dismiss"   => "true",
       "stackup_spacing" => "20",
    );

    $glyph = array(
        "success" => "<span class='glyphicon glyphicon-ok big-text'></span>&nbsp;&nbsp;&nbsp;",
        "warning" => "<span class='glyphicon glyphicon-exclamation-sign big-text'></span>&nbsp;&nbsp;&nbsp;",
        "info"    => "<span class='glyphicon glyphicon-info-sign big-text'></span>&nbsp;&nbsp;&nbsp;",
        "danger"  => "<span class='glyphicon glyphicon-remove big-text'></span>&nbsp;&nbsp;&nbsp;",
        "primary" => "",
    );

    $html = "";
    // first check that we have a current growl  and that it is for this page

    if (!empty($_SESSION["e_$eventid"]['growl']["$page"]))
    {
        $html.= <<<EOT
        <script>
        $(function() {
EOT;
        foreach ($_SESSION["e_$eventid"]['growl']["$page"] as $growl)
        {
            if ($growl['type'] == "danger")
            {
                $att_default['delay'] = "30000";
            }

            $att = array_merge($att_default, $growl);
            $att["glyph"] ? $msg = $glyph["{$att["type"]}"]." ".$att["msg"] : $msg = $att["msg"] ;   // add contextual glyph
            $html.= <<<EOT
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
        });
        </script>
EOT;
        u_growlUnset($eventid, $page);   // now unset growls for this page
    }
    return $html;
}


function u_selectcodelist($codelist, $selected="")
{
    $bufr = "<option value=\"\">please select ...</option>";
    foreach ($codelist as $opt)
    {
        $selectstr = "";
        if (($selected=="default" AND $opt['defaultval']) OR  ($selected == $opt['code']))
        { $selectstr="selected"; }
        $bufr.= "<option value=\"{$opt['code']}\" $selectstr>{$opt['label']}</option>";
    }
    return $bufr;
}

function u_selectlist($list, $selected="")
{
    $bufr = "<option value=\"\" >please select ...</option>";
    foreach ($list as $key=>$opt)
    {
        ($opt == $selected) ? $selectstr = "selected" : $selectstr = "";
        $bufr.= "<option value=\"$key\" $selectstr>$opt</option>";
    }
    return $bufr;
}


function ftpFiles($loc, $protocol, $ftp_env, $files)
{
    // initialise logfile

    error_reporting(0);  //error_reporting(E_ERROR | E_WARNING | E_PARSE);

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

                foreach($files as $key=>$file)   // loop over all files
                {
                    if (ftp_put($conn_id, $file['dest'], $file['source'], FTP_BINARY))   // transfer file
                    {
                        $status['file'][$key] = true;
                        $status['log'].= " - file transferred ({$file['source']})<br>";
                    }
                    else
                    {
                        $status['file'][$key] = false;
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
        $status['log'] = "Transferring results files using sftp protocol<br>";

        include("$loc/common/oss/phpseclib/Net/SFTP.php");
        define('NET_SFTP_LOGGING', NET_SFTP_LOG_COMPLEX);

        $sftp = new Net_SFTP($ftp_env['server']);

        if ($sftp->login($ftp_env['user'], $ftp_env['pwd']))
        {
            $status['connect'] = true;
            $status['login'] = true;
            $status['log'].= " - logged in to sftp server<br>";

            foreach ($files as $key => $file)   // loop over all files
            {
                if ($sftp->put($file['dest'], $file['source'], NET_SFTP_LOCAL_FILE))   // transfer file
                {
                    $status['file'][$key] = true;
                    $status['log'].= " - file transferred ({$file['source']})<br>";
                }
                else
                {
                    $status['file'][$key] = false;
                    $status['log'].= " - file transfer failed ({$file['source']})<br>";
                }
            }
        }
        else
        {
            $status['login'] = false;
            $status['connect'] = false;
            $status['log'].= " - failed to login to sftp server ({$ftp_env['user']}/{$ftp_env['pwd']})<br>";
        }
    }
    else
    {
        $status['log'].= "File transfer not possible - protocol [$protocol] not supported<br>";
    }

    error_reporting(E_ERROR);
    return $status;
}


function u_get_result_codes_info($result_codes, $codes_used = array())
{
    $output = $result_codes;
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

?>