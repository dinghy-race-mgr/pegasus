<?php
/**
 * en-sailor-lang.php - system initialisation functionality
 * 
 * @abstract English language file for rm_sailor applications
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 * 
 */

/* no menu language
$lang ['menu'] = array(              // menu labels (case sensitive)
);
*/    

$lang['btn'] = array(               // button labels (case sensitive)
    "add_boat"          => "Add Boat",
    "back"              => "Back",
    "change"            => "Change",
    "change_detail"     => "Change Detail",
    "reset"             => "Reset",
    "results"           => "Results",
    "retry"             => "Try Again",
    "sign_on"           => "Sign On",
    "sign_off"          => "Sign Off",
    "confirm_entry"     => "Confirm Entry",

); 

$lang ['msg'] = array(               // application messages (case sensitive)
    "nonefound"         => "Club specific message - what should the user do if the sail number is not found",
    "signonsuccess"     => "Have a good race",
    "signonfail"        => "Club specific message - what should the user do if the signon is unsuccessful",

); 

$lang['form'] = array(              // form language (case insensitive)
    "temporary"         => "just today",
    "permanently"       => "permanently",
); 

$lang['app'] = array(              // general vocabulary (case insensitive)
    // class
    "class_ineligible"  => "not eligible",
    "class_invalid"     => "class details not found",

    // competitor
    "comp_sailno"       => "sail number",
    "comp_new_sailno"   => "number you are using",
    "comp_crew"         => "crew",
    "comp_new_crew"     => "new crew name",
    "comp_multiple"     => "more than one boat with sail number",
    
    // entry
    "entry_failed"      => "entry failed",
    
    // race
    "race_none_today"   => "no races today",
    "race_next"         => "next race is",
    "race_today"        => "races today",    
    "race_start"        => "start",
    "race_entry"        => "race entry",
    "race_invalid"      => "race details not found",
);

$lang['sys']   = array(             // common system vocabulary (case sensitive)
    "apology"           => "Sorry",
    "error_prefix"      => "error:",    
    "initialising"      => "initialising",
    "mandatory"         => "required information",
    "not_found"         => "not found",
    "pick_one"          => "pick one",
	"entry_failed"      => "entry failed",
    "invalid_race"      => "race not recognised",
    "invalid_class"     => "class not recognised",
    "not_eligible"      => "competitor not eligible for this race",
);

$lang['err']   = array(             // error messages (case sensitive)
    // system
    "sys000"            => "we have encountered an unexpected error",
    "sys001"            => "your raceManager system is not configured to use this application",
    "sys003"            => "application configuration file does not exist",  
    "sys004"            => "requested language file does not exist",
    "sys005"            => "database not available",
    "sys006"            => "event id not recognised",
    
    // competitor
    "comp001"           => "competitor id is invalid",
    "comp002"           => "competitor details update failed", 
    "comp003"           => "no competitor information available",
    
    // event
    "event001"          => "no race information available",
    "event002"          => "you have not chosen any races to enter",
);    
$lang['help']   = array(
    "restart"           => "try closing the browser and restarting racemanager",
); 

?>