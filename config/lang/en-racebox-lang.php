<?php
/* ------------------------------------------------------------
   en-racebox-lang.php
   
   English language file included in each page
   
   %%copyright%%
   %%license%%
   ------------------------------------------------------------
*/

$lang['menu'] = array(             // menu labels (case sensitive)
    "addrace" => "add race today",
    "race"    => "race",
    "enter"   => "entries",
    "start"   => "start",
    "timer"   => "time laps",
    "pursuitfinish" => "pursuit finish",
    "results" => "results",
    "help"    => "help",            
);
   

$lang['btn'] = array(               // button labels (case sensitive)
    // pickrace page
    "race_run"          => "Run Race",
    "race_back"         => "Back to Race",
    "race_complete"     => "Race Complete",
    "race_error"        => "Error!!!",

    // race page
    "change_details"    => "Change Details",
    "race_format"       => "Race Format",
    "send_message"      => "Send Message",
    "cancel_race"       => "Cancel Race",
    "abandon_race"      => "Abandon Race",
    "close_race"        => "Close Race",
    "reset_race"        => "Reset Race",
    
    // enter page
    // start page
    // timer page
    // results page
    // pickrace page
                        // pickrace page

); 

$lang ['msg'] = array(              // application messages (case sensitive)
    // races
    "race_none"              => "No races scheduled today",
    "race_create"            => "Want to create a race? - use the <span class=\"text-primary\">Add Race Today</span> button",
    "race_today"             => "Races today",    
    "race_not_specified"     => "Race not specified",
    "race_not_exists"        => "Race does not exist",
    "race_format_not_found"  => "Race configuration not defined",
    "fleet_detail_not_found" => "Fleet configuration not defined",
    "duties_none"            => "No duties defined",
    "goto_race"              => "Click to run this race",
    "race_not_available"     => "Race is complete and not available",
    "race_cancelled"         => "race has been recorded as cancelled - please close the race",
    "race_abandoned"         => "race has been recorded as abandoned - please close the race",
    "confirm_fail_retry"     => "action not completed successfully - please try again",
    "cancel_race"            => "Are you sure you want to cancel the race ",
    "abandon_race"           => "Are you sure you want to abandon the race ",
    "local_info"             => "Local Information",
    
    // boats
    
    // series
    

); 

$lang['form'] = array(              // form language (case insensitive)
); 

$lang['app'] = array(              // general vocabulary (case insensitive)
"race_officer"=>"race officer",
"race_format"=>"race format",
"race_scheduled"=>"race scheduled",
"race_notstarted"=>"race not started",
"race_inprogress"=>"race in progress",
"race_complete"=>"race complete",
"race_status_unknown"=>"!! race state not known",
"start_sequence"=>"start sequence",
"tides"=>"tides",
"duties"=>"duties",
"start"=>"start",
"fleet"=>"fleet",
"scoring"=> "scoring",
"rating"=> "rating",
"time_limit"=> "time limit",
"signal"=> "signal",
"classes"=> "classes",
"competitors"=> "competitors",
"preparatory"=> "preparatory",
"warning"=> "warning",
"unknown"=> "unknown",
"printable"=> "print friendly",
"and"=> "and",
"excluding"=> "excluding",
"only"=> "only",
"any"=> "any",
"age"=> "age",
"up_to"=> "up to",
"level"=> "level",
"above"=> "above",
"groups"=> "groups",
"not"=> "not",
"unknown_format" => "unknown race format!",
);

$lang['entry_type']   = array( 
    "signon"          => "racemanager sign on (OOD retire)",
    "signon-retire"   => "racemanager sign on and retire",
    "signon-declare"  => "racemanager sign on and off",
    "ood"             => "OOD entry (sign on sheets)",
);

$lang['fleet']        = array(
    "status-notstarted"  => "not started",
    "status-inprogress"  => "in progress",
    "status-finishing"   => "finishing",
    "status-allfinished" => "all finished",
    "type-hcap"          => "handicap",
    "type-avglap"        => "average lap",
    "type-level"         => "class",
    "type-pursuit"       => "pursuit",
    "type-flight"        => "flight",  
);

$lang['popover']     = array(
    "race_format"        => "click to get race format details: start times, signals, tide etc.",
    "race_changedetail"  => "click to change race details or add notes to race/results.",
    "send_message"       => "click to send email message to support team",
);

$lang['sys']   = array(             // common system vocabulary (case insensitive)
    "details"           => "details",
    "apology"           => "Sorry",
    "demo"              => "demo",
    "live"              => "live",
    "error_prefix"      => "error:",    
    "initialising"      => "initialising",
    "mandatory"         => "required information",
    "not_found"         => "not found",
    "pick_one"          => "pick one",
    "problem"           => "oops we have a problem",
//    "supportteam"       => "Support Team",
//    "supportteamhelp"   => "if you need help contact a member of the support team",
//    "supportteaminfo"   => "Mark Elkington - Merlin 3718<br>Sam Woolner - Hornet 2154<br>",
    "seekhelp"          => "if you are in any doubt please contact a member of the raceManager support team",
    "success"           => "success",
    "incorrect_request" => "request not correctly specified",
    "close"             => "no thanks",

);

$lang['err']   = array(             // error messages (case sensitive)
    // system
    "sys000"            => "we have encountered an unexpected error",
    "sys001"            => "your raceManager system is not configured to use this application",
    "sys003"            => "application configuration file does not exist",    
    "sys004"            => "database not available", 
    "sys005"            => "requested language file does not exist",
    "sys006"            => "required parameters not supplied",
    "sys007"            => "page state not recognised",
    "sys008"            => "event id not recognised",
    
    // competitor
    "comp001"           => "competitor id is invalid",
    "comp002"           => "competitor details update failed", 
    "comp003"           => "no competitor information available",
    
    // event
    "event001"          => "no race information available",
    "event002"          => "you have not chosen any races to enter",
    "event003"          => "attempt to close or reset race has failed",
    "event004"          => "attempt to add a duty has failed",
    "event005"          => "attempt to add a message has failed",
    
    // class
    "boat001"      => "missing class name",
    "boat002"      => "class already exists",
    "boat003"      => "adding new class failed",
    "boat004"      => "class deletion failed",
    "boat005"      => "class configuration update failed",
    "boat006"      => "yardstick update failed - bad data",
    

    //exit
    "exit-main"    => "Oops - we have a problem<br><small>The software has unexpectedly stopped.</small>",
    "exit-action"  => "Close this window and try to restart the application.  If the problems continue please report the error to your system administrator",

); 

//$lang['growl'] = array(
//    "race1"            => "race details have been changed",
//    "race2"            => "race details have not been changed",
//    "race3"            => "Success - message sent",
//    "race4"            => "Sorry - message failed",
//
//    "entries1"         => "failed to produce requested output",
//    "entries2"         => "requested output in new tab",
//    "entries3_pass"    => "Success - new class created",
//    "entries3_fail"    => "Sorry - failed to create new class"
//);



// WORDS
$lang ['words'] = array(

    // general vocabulary   
    "abbreviations" => "abbreviations", 
    "abandon"   => "abandon",
    "above"     => "above",
    "add"       => "add",
    "adjust"    => "adjust",
    "age"       => "age",
    "and"       => "and", 
    "any"       => "any",
    
    "back"      => "back",
    "bad"       => "bad",
    "below"     => "below",
    "boat"      => "boat",
    "button"    => "button",
    
    "cancel"    => "cancel",
    "change"    => "change",
    "class"     => "class",
    "close"     => "close",
    "club"      => "club",
    "codes"     => "codes",
    "competitor"=> "competitor",
    "complete"  => "complete",
    "crew"      => "crew",

    "date"      => "date",  
    "duty"      => "duty",
    
    "entry"     => "entry",
    "entries"   => "entries",
    "error"     => "error",
    "excluding" => "excluding",

    "finish"    => "finish",
    "fleet"     => "fleet",
    "found"     => "found",

    "groups"    => "groups",

    "handicap"  => "handicap",
    "helm"      => "helm",
    "help"      => "help",
 
    "including" => "including",
    "info"      => "info",
    "information" => "information",
    
    "lap"       => "lap",
    "laps"      => "laps",
    "level"     => "level",
    "limit"     => "limit",
    
    "member"    => "member",
    "meaning"   => "meaning",
    
    "name"      => "name",
    "new"       => "new",
    "no"        => "no",
    "not"       => "not",
    "note"      => "note",
    "number"    => "number",

    "officer"     => "officer",    
    "only"        => "only",
    "ood"         => "ood",
    
    "postpone"    => "postpone",
    "points"      => "points",
    "position"    => "position",
    "preparatory" => "preparatory",
    "print"       => "print",
    "printable"   => "printable",
    "problem"     => "problem",
    "publish"     => "publish",

    
    "quick"       => "quick",
            
    "race"        => "race",
    "races"       => "races",
    "rating"      => "rating",
    "reset"       => "reset",
    "results"     => "results",

    "sailnum"     => "sail no.",
    "scoring"     => "scoring",    
    "sequence"    => "sequence",
    "signal"      => "signal",
    "start"       => "start",
    "synch"       => "synch",
    "system"      => "system",
    
    "tide"        => "tide",
    "time"        => "time",
    "timer"       => "timer",
    
    "unknown"     => "unknown",
    "update"      => "update",
    "up to"       => "up to",
    
    "warning"     => "warning",
    "wind"        => "wind",
    
    "yardstick"   => "yardstick",
    "yes"         => "yes"

);

$lang['vocab'] = array(
    "general_recall" => "general recall",
    "shorten_races"  => "shorten all races",


);

// PAGES

$lang ['races']    = array(
     "noevents"  => "No races scheduled today",
     "noeventsinfo"  => "Want to create a race for today? - use the <span class=\"text-primary\">Add Race</span> menu option above - if you need help contact a member of the support team",
     "raceformatlink"  => "click to get race format details: start times, signals, tide etc.",
     "txt-addnewwarn"  => "Be VERY careful that you are not creating a race that already exists",
     "racestatusunknown"  => "race status not recognised",
);

$lang ['raceview'] = array(
     "eventnotspecified"  => "race not specified",
     "racefmtnotexists"   => "race configuration not defined",
     "fleetfmtnotexists"  => "fleet configuration not defined",


);

$lang['navbar'] = array(
    "addrace" => "add race",
    "race"    => "race",
    "enter"   => "entries",
    "start"   => "start race",
    "timer"   => "timer",
    "results" => "results",
    "help"    => "help"
);




