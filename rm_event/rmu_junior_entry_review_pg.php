<?php
/*
 * Displays consent info - DO NOT IMPLEMENT UNTIL EVENT IS UP
 *
 */

// initialise
// start session
session_id('sess-rmuevent');
session_start();

// error_reporting(E_ERROR);  set for live operation
require_once("include/rm_event_lib.php");
require_once("classes/template.php");
require_once("classes/db.php");

// initialise application
$cfg = set_config("config.ini", array("rm_event"), true);
$cfg['logfile'] = str_replace("_date", date("_Y"), $cfg['logfile']);

$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host']);
$tmpl_o = new TEMPLATE(array( "./templates/util_layouts_tm.php"));

// arguments
if (key_exists("access", $_REQUEST))
{
    if ($_REQUEST['access'] == "BF2AA3C-80F3-DB06-F28A-D0F961CAB875")
    {
        if (key_exists("event", $_REQUEST))   // requesting single nicknamed event
        {
            // find event matching nick name
            $event = $db_o->run("SELECT * FROM e_event WHERE nickname = ?", array($_REQUEST['event']) )->fetch();
            $eid = $event['id'];
        }
        else
        {
            $fields = array(
                "page"        => "Entry Review",
                "problem"     => "event name not recognised",
                "location"    => __FILE__." - #".__LINE__,
                "evidence"    => "event name set to - {$_REQUEST['event']} -",
                "report-link" => $this->cfg['system_admin_contact'],
                "event-title" => $event['title']
            );
            $params = array("action" => "", "event-title" => "");

            return $this->tmpl_o->get_template("error_body", $fields, $params);
        }
    }
    else
    {

        $fields = array(
            "page"        => "Entry Review",
            "problem"     => "access code not recognised",
            "location"    => __FILE__." - #".__LINE__,
            "evidence"    => "access code set to - {$_REQUEST['access']} -",
            "report-link" => $this->cfg['system_admin_contact'],
            "event-title" => $event['title']
        );

        $params = array("action" => "", "event-title" => $event['title']);

        return $this->tmpl_o->get_template("error_body", $fields, $params);
    }

}


// joined query on t_entry and t_consent
    $query = <<<EOT
SELECT  a.id, a.`b-class`, a.`b-sailno`, a.`b-altno`, a.`b-name`, a.`h-name`, a.`h-age`, 
            a.`h-gender`, a.`h-phone`, a.`h-email`, a.`h-club`, a.`e-notes`, a.createdate as `entry-create`,
            b.parent_name, b.parent_phone, b.parent_email, b.parent_address, b.alt_contact_detail, 
            b.child_name, b.child_dob, b.medical, b.dietary, b.`confirm-media`, b.`confirm-treatment`,
            b.`confirm-confident`, b.createdate as `consent-create`
            FROM e_entry as a LEFT JOIN e_consent as b ON a.id=b.entryid 
            WHERE a.eid = $eid AND `e-exclude` = 0 AND `e-waiting` = 0 
            ORDER BY `b-fleet` ASC, `b-class` ASC, `b-sailno` * 1 ASC 
EOT;

    $entry = $db_o->run($query, array())->fetchall();
    $num_entries = count($entry);

    $fields = array("event-title" => $event['title']);
    $params = array("entries" => $entry);
    $review_body = $tmpl_o->get_template("entry_review", $fields, $params);

    //echo "<pre>$query</pre>";
    //echo "<pre>".print_r($entry,true)."</pre>";

    // assemble page

    // create footer
    $fields = array('version' => $cfg['sys_version'], 'year' => date("Y"), "msg" => "Total Entries: $num_entries");
    $params = array('footer' => true, 'page' => "entry_review", 'event' => $event['title'], "layout" => $cfg["layout"]);
    $footer = $tmpl_o->get_template("footer", $fields, $params);

    // assemble page
    $fields = array(
        'page-title' => "entry review",
        'page-navbar'=> "&nbsp;",                // no navbar
        'page-main'  => $review_body,
        'page-footer'=> $footer,
        'page-modals'=> "&nbsp;",                // no modals
        'page-js'    => "&nbsp;"                 // no javascript
    );
    $params = array();
    echo $tmpl_o->get_template("page", $fields, $params);

