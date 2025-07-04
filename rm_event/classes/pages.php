<?php

class PAGES
{
    public function __construct($cfg)
    {
        $this->tmpl_o = new TEMPLATE(array( "./templates/layouts_tm.php"));
        $this->cfg = $cfg;
        $this->class_spec = array();

        // fixme needs to be in session when eid is established (also need to have numbers for entries, documents and notices)
        // fixme - temp solution for only showing results when complete
        $this->cfg['options'] = array(
            "1" => array("page" => "details","label" => "Details", "script" => "rm_event.php?page=details&eid="),
            "2" => array("page" => "entries","label" => "Enter", "script" => "rm_event.php?page=entries&eid="),
            "3" => array("page" => "documents", "label" => "Documents", "script" => "rm_event.php?page=documents&eid="),
            "4" => array("page" => "notices","label" => "Notices", "script" => "rm_event.php?page=notices&eid="),
            "5" => array("page" => "results", "label" => "Results", "script" => "rm_event.php?page=results&eid=")
        );
    }

    public function pg_list($db_o, $year = "")
    {
        // FIXME : needs to handle "review" and "cancel" status - only available with password

        // get events for selected year
        if (empty($year)) { $year = date("Y"); }
        $events = $db_o->run("SELECT * FROM e_event WHERE `date-start` >= ? and `date-end` <= ? ORDER BY `date-start` ASC", array("$year-01-01","$year-12-31") )->fetchall();

        // get club contact
        $contacts = parse_contacts($this->cfg['events_contact'], "club");

        // create navbar
        $fields = array("eventid" => "", "version" => $this->cfg['sys_version'], "year" => date("Y"),
                        "brand-label" => $this->cfg['brand'], "view" => $this->cfg['view_status']);
        $params = array("page" => "list", "active" => "", "start-year"=> $this->cfg['start_year'], "options"=>$this->cfg['options'], "contact"=> $contacts );
        $nav =$this->tmpl_o->get_template("navbar", $fields, $params);

        $fields = array(
            "list-lead-txt" => $this->cfg['list_lead_txt'],
        );
        $params = array(
            "year" => $year,
            "events" => $events,
            "layout" => $this->cfg["layout"]
        );
        $body =$this->tmpl_o->get_template("list_body_1", $fields, $params);  // currently set to 1 event per row

        // create footer
        $fields = array('version' => $this->cfg['sys_version'], 'year' => date("Y"));
        $params = array('page' => "list", 'footer' => true, );
        $footer = $this->tmpl_o->get_template("footer", $fields, $params);

        // assemble page
        $fields = array(
            'page-title'=>$this->cfg['sys_name'],
            'page-navbar'=>$nav,
            'page-main'=>$body,
            'page-footer'=>$footer,
            'page-modals'=>"&nbsp;",
            'page-js'=>"&nbsp;");
        $params = array();
        echo $this->tmpl_o->get_template("page", $fields, $params);
    }

    public function pg_event($db_o, $page, $eid, $entryupdate)
    {
        // get event details
        $event = $db_o->run("SELECT * FROM e_event WHERE id = ?", array($eid))->fetch();

        // set default classes definition
        $valid_class_spec = false;
        $this->class_spec = array(
            "type"      => "unknown",
            "classlist" => "",
            "err"       => "no class definition provided - list or race format"
        );

        // decode class list definition
        if (is_numeric($event['entry-classes']))
            // classes are defined using racemanager race format configuration
        {
            //echo "<pre>ENTER 1</pre>";
            $this->class_spec = array(
                "type"      => "format",
            );
            $this->class_spec['classlist'] = get_class_list($event['entry-classes']);
            if (!empty($this->class_spec['classlist']))
            {
                $valid_class_spec = true;
                $this->class_spec['err'] = "";
            }
            else
            {
                $valid_class_spec = false;
                $this->class_spec['err'] = "no eligible classes have been defined";
            }
        }
        else
            // check if classes are defined by a csv string in e_event.entry-classes
        {
            //echo "<pre>ENTER 2</pre>";
            $this->class_spec = array("type" => "list");
            $valid_class_spec = true;
            $classes = explode(",",$event['entry-classes']);
            $err_classes = "";
            foreach ($classes as $class)
            {
                if (!check_class_exists(trim($class)))
                {
                    $err_classes.= $class['classname'].",";
                }
            }

            if (!empty($err_classes))
            {
                $valid_class_spec = false;
                $this->class_spec = array(
                    "type"      => "list",
                    "classlist" => "",
                    "err"       => "$err_classes - not recognised by raceManager"
                );
            }
        }


        // if class spec is not valid stop
        if (!$valid_class_spec)
        {
            $error_msg = "The {$event['title']} does not have a valid definition for the eligible classes";
            if (!empty($this->class_spec['err'])) { $error_msg.= "[ {$this->class_spec['err']} ]"; }
            $this->exitnicely($this->cfg['sys_name'], $error_msg, "rm_event/pages.php", "", $this->cfg['system_admin_contact'],
                array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
            exit();
        }

        // get standard content and put into array indexed by content name
        $content = array();
        $std_content = $db_o->run("SELECT * FROM e_content WHERE `open_type` = ? and page = ?", array("std", $page) )->fetchall();
        foreach($std_content as $rs) { $content[$rs['name']] = $rs; }

        // get event type or event specific content - and overwrite std_content  (the ORDER clause ensures that event specific
        // content overwrites event type content)
        $event_content = $db_o->run("SELECT * FROM e_content WHERE (eid = ? or open_type = ?) and page = ? ORDER BY `name` ASC, `eid` ASC ", array($eid, $event['open_type'], $page) )->fetchall();
        foreach($event_content as $rs) { $content[$rs['name']] = $rs; }

        // get contacts
        $contacts_rs = $db_o->run("SELECT * FROM e_contact WHERE eid = ?", array($eid) )->fetchall();
        if ($contacts_rs)
        {
            $contacts = parse_contacts($contacts_rs, "event");
        }
        else
        {
            $contacts = parse_contacts($this->cfg['events_contact'], "club");
        }

        // get info counts
        $counts["entries"]   = $db_o->run("SELECT count(*) FROM e_entry WHERE eid=? and `e-exclude` = 0", array($eid) )->fetchColumn();
        $counts["documents"] = $db_o->run("SELECT count(*) FROM e_document WHERE eid=? and category != 'results'", array($eid) )->fetchColumn();
        $counts["notices"]   = $db_o->run("SELECT count(*) FROM e_notice WHERE eid=?", array($eid) )->fetchColumn();
        $counts["results"]   = $db_o->run("SELECT count(*) FROM e_document WHERE eid=? AND category = 'results'", array($eid) )->fetchColumn();

        // check if complete
        strtotime($event['date-end']) < strtotime(date("Y-m-d")) ? $complete = true : $complete = false;

        // create navbar
        $fields = array("eventid" => $eid, "version" => $this->cfg['sys_version'], "year" => date("Y"),
            "brand-label" => $this->cfg['brand']);
        $params = array("page" => $page, "active" => $page, 'eid' => $eid,
            "start-year"=> $this->cfg['start_year'], "options"=>$this->cfg['options'],
            "contact"=> $contacts, "counts" => $counts, "complete" => $complete);
        $nav =$this->tmpl_o->get_template("navbar", $fields, $params);

        // create body
        if ($event)
        {
            // ---------------------------------- details page
            if ($page == "details")
            {
                // get topics (reusable parts of content)
                $topics = array();
                if (!empty($event['topics']))
                {
                    $event_topics = $db_o->run("SELECT * FROM e_content WHERE id IN ({$event['topics']}) and page = ?", array($page))->fetchall();
                    $topics = array();
                    foreach ($event_topics as $rs) { $topics[$rs['label']] = $rs; }
                }

                $fields = array(
                    "event-title" => $event['title'],
                    "event-dates" => format_event_dates($event['date-start'], $event['date-end']),
                    "event-subtitle" => $event['sub-title'],
                );

                $params = array(
                    "eventid" => $eid,
                    "content" => $content,
                    "topics" => $topics,
                    "document_dir" => $this->cfg['document_dir'],
                    "layout" => $this->cfg["layout"]
                );

                $body = $this->tmpl_o->get_template("details_body", $fields, $params);
            }


            // ---------------------------------- entries page
            elseif ($page == "entries")
            {
                if (!empty($event['entry-form']))    // use internal entry form by default
                {
                    // get all entry data (excluding waiting list - sorted by fleet, class, sailnumber)
                    $entries = $db_o->run("SELECT * FROM e_entry WHERE eid = ? and `e-exclude` = 0 and `e-waiting` = 0 ORDER BY `b-fleet` ASC, `b-class` ASC, `b-sailno` * 1 ASC", array($eid) )->fetchall();

                    // get all waiting list entries (sorted by order on waiting list)
                    $waiting = $db_o->run("SELECT * FROM e_entry WHERE eid = ? and `e-exclude` = 0 and `e-waiting` = 1 ORDER BY `e-entryno` ASC", array($eid) )->fetchall();

                    // get entry/update confirm message if required
                    $entry_confirm_block = "";
                    if ($entryupdate['action'] == "newentry" or $entryupdate['action'] == "updentry")
                    {
                        $fields = array();
                        $params = $this->entry_confirm_params($entryupdate, $waiting, $eid);
                        $entry_confirm_block = $this->tmpl_o->get_template("entry_confirm_block", $fields, $params);
                    }

                    // add consent form detail to $entries array
                    echo "<pre>BEFORE".print_r($entries,true)."</pre>";
                    $entries = $this->mark_entries_requiring_consent($entries);
                    echo "<pre>AFTER".print_r($entries,true)."</pre>";

                    // check if entry_state and construct entry state block
                    $entry_state = check_entry_open($event['entry-start'], $event['entry-end']);  // returns before|after|open

                    // allow users with valid view code to enter anyway
                    if ($this->cfg['view_status']) { $entry_state = "open"; }

                    if ($entry_state == "before")
                    {
                        $fields = array("event-title" => $event['title']);
                        $params = array("entry-start"=> $event['entry-start'], "entry-end"=> $event['entry-end'] );
                        $entry_status_block = $this->tmpl_o->get_template("entry_status_before_open", $fields, $params);
                    }
                    elseif ($entry_state == "after")
                    {
                        $fields = array("entry-count" => count($entries));
                        $params = array("entry-reqd"=>$event['entry-reqd'], "waiting" => $waiting);
                        $entry_status_block = $this->tmpl_o->get_template("entry_status_after_close", $fields, $params);
                    }
                    else  // entry_state is "open"
                    {
                        $fields = array();
                        $params = array("eid" => $eid, "entry-count" => count($entries), "entry-limit" => $event['entry-limit'],
                            "classes" => $this->class_spec['classlist'], "waiting" => $waiting);
                        //echo "<pre>".print_r($params,true)."</pre>";
                        //exit();
                        $entry_status_block = $this->tmpl_o->get_template("entry_status_open", $fields, $params);
                    }

                    //construct entries body htm
                    $fields = array(
                        "event-title"   => $event['title'],
                        "entries-intro" => $content['entries-intro']['content'],
                        "entry-confirm-block" => $entry_confirm_block,
                        "entry-status-block" => $entry_status_block
                    );

                    $params = array(
                        "eid"        => $eid,
                        "entries"    => $entries,
                        "waiting"    => $waiting,
                        "process"    => $entryupdate,
                        "layout" => $this->cfg["layout"]
                    );

                    // render confirmation and entries table
                    $body = $this->tmpl_o->get_template("entries_body", $fields, $params);
                }
                elseif (!empty($event['entry-form-link']))
                {
                    $fields = array();
                    $params = array("entry_form"=> $event['entry-form-link']);
                    $body = $this->tmpl_o->get_template("external_entries_body", $fields, $params);
                }
                else   // report no entry form defined - please enter on the day
                {
                    $fields = array();
                    $params = array();
                    $body = $this->tmpl_o->get_template("entries_at_club_body", $fields, $params);
                }
            }


            // ---------------------------------- newentryform page ----------------------------------------------------

            elseif ($page == "newentryform")
            {
                if (!empty($event['entry-form']))    // check internal entry form
                {
                    $form_detail = $db_o->run("SELECT * FROM e_form WHERE `form-file` = '{$event['entry-form']}'", array() )->fetch();

                    empty($_REQUEST['class']) ? $class_name = "none" : $class_name = $_REQUEST['class'];

                    if ($class_name == "none")                   // class will be selected from drop down list on form
                    {
                        //key($this->class_spec) == "list" ? $class_list = $this->class_spec['list'] : $class_list = $this->class_spec['format'];
                        $class_list = $this->class_spec['classlist'];
                        $class_fleets = "";
                        $include_crew = true;
                    }
                    else                                         // class will be passed as a read only parameter on the form
                    {
                        $class_list = "";
                        // get sub-fleets for named class
                        $class = $db_o->run("SELECT fleets, crew FROM t_class WHERE `classname` = ?", array($class_name) )->fetch();
                        $class['crew'] > 1 ? $include_crew = true : $include_crew = false;
                        $class_fleets = $class['fleets'];
                    }

                    $fields = array(
                        "event-title"    => $event['title'],
                    );

                    $params = array(
                        "eventid"      => $eid,
                        "form-name"    => $form_detail['form-file'],
                        "form-mode"    => "add",
                        "instructions" => $form_detail['instructions'],
                        "class-name"   => $class_name,
                        "class-list"   => $class_list,
                        "inc_fleets"   => $class_fleets,
                        "inc_crew"     => $include_crew
                    );
//                    echo "<pre>".print_r($params,true)."</pre>";
//                    exit();
                    $body = $this->tmpl_o->get_template("newentry_body", $fields, $params);
                }
                else                                     // no entry form report error
                {
                    // FIXME - replace with an exitnicely
                    $fields = array(
                        "page" => $page,
                        "problem" => "The requested entry form is not recognised.",
                        "location" => __FILE__." - #".__LINE__,
                        "evidence" => "form selected: internal - {$event['entry_form']} external - {$event['entry_form_link']}",
                        "report-link" => $this->cfg['system_admin_contact'],
                        "event-title" => $event['title']
                    );

                    $params = array("action" => "", "event-title" => $event['title'], "layout" => $this->cfg["layout"]);
                    $body = $this->tmpl_o->get_template("error_body", $fields, $params);
                }
            }

            // ---------------------------------- consent form ---------------------------------------------------------
            elseif ($page == "juniorconsentform")
            {
                $form_detail = $db_o->run("SELECT * FROM e_form WHERE `form-label` = 'junior consent'", array() )->fetch();
                $body = $this->get_juniorconsent_htm($eid, $event, $entryupdate, $form_detail);
            }


            // ---------------------------------- documents page -------------------------------------------------------
            elseif ($page == "documents")
            {
                $mode = "documents";
                $documents = $db_o->run("SELECT * FROM e_document WHERE eid = ? AND category != 'results' ORDER BY createdate DESC", array($eid) )->fetchall();
                $body = $this->get_documents_htm($eid, $mode, $event, $content, $documents); // fixme use $this->
            }

            // ---------------------------------- notices page ---------------------------------------------------------
            elseif ($page == "notices")
            {
                if (empty($_REQUEST['mode'])) {
                    $notices = $db_o->run("SELECT * FROM e_notice WHERE eid = ? ORDER BY createdate DESC", array($eid))->fetchall();
                    $mode = "";
                }
                else
                {
                    $notices = $db_o->run("SELECT * FROM e_notice WHERE eid = ? and category = ? ORDER BY createdate DESC", array($eid, $_REQUEST['mode']) )->fetchall();
                    $mode = $_REQUEST['mode'];
                }

                $body = $this->get_notices_htm($eid, $mode, $event, $content, $notices); // fixme use $this->

            }

            // ---------------------------------- results page ---------------------------------------------------------
            elseif ($page == "results")
            {
                // reusing documents page rendering
                $mode = "results";
                $documents = $db_o->run("SELECT * FROM e_document WHERE eid = ? and category = ? ORDER BY createdate DESC", array($eid, 'results') )->fetchall();
                $body = $this->get_documents_htm($eid, $mode, $event, $content, $documents); // fixme use $this->
            }

        // ---------------------------------- unknown page -------------------------------------------------------------
            else
            {
                // report unknown page
                $fields = array(
                    "page" => $page,
                    "problem" => "The page you requested is not recognised.",
                    "location" => __FILE__." - #".__LINE__,
                    "evidence" => '$page = '."|$page|",
                    "report-link" => $this->cfg['system_admin_contact'],
                    "event-title" => $event['title']
                );

                $params = array("action" => "", "event-title" => $event['title'],"layout" => $this->cfg["layout"]);

                $body = $this->tmpl_o->get_template("error_body", $fields, $params);
            }
        }
        // ----------------------- unknown event -----------------------------------------------------------------------
        else
        {
            // report unknown event
            $problem = "The event you requested is not recognised.";
            $evidence = "eventid = |$eid|";
            $body = $this->get_error_htm($page, $event, $problem, $evidence);
        }

        // create footer
        $fields = array('version' => $this->cfg['sys_version'], 'year' => date("Y"));
        $params = array('page' => $page, 'eid' => $eid, 'footer' => true, "counts" => $counts, "layout" => $this->cfg["layout"]);
        $footer = $this->tmpl_o->get_template("footer", $fields, $params);

        // create modals

        // create javascript

        // assemble page
        $fields = array(
            'page-title'=>$this->cfg['sys_name'],
            'page-navbar'=>$nav,
            'page-main'=>$body,
            'page-footer'=>$footer,
            'page-modals'=>"&nbsp;",
            'page-js'=>"&nbsp;");
        $params = array();
        echo $this->tmpl_o->get_template("page", $fields, $params);

    }

private function get_juniorconsent_htm($eid, $event, $entryupdate, $form_detail)
{
    $fields = array("event-title" => $event['title']);

    $params = array(
        "eventid" => $eid,
        "entryid" => $entryupdate['recordid'],
        "form-name" => $form_detail['form-file'],
        "form-mode" => "add",
        "instructions" => $form_detail['instructions'],
        "layout" => $this->cfg["layout"]
    );

    return $this->tmpl_o->get_template("juniorconsent_body", $fields, $params);
}

private function get_documents_htm($eid, $mode, $event, $content, $documents)
{
    if (count($documents) > 0)
    {
        $fields = array(
            "event-title" => $event['title'],
        );
        empty($content['documents-intro']['content']) ? $fields['documents-intro'] = "" : $fields['documents-intro'] = $content['documents-intro']['content'];

        $params = array(
            "eventid"   => $eid,
            "mode"      => $mode,
            "content"   => $content,
            "doc_dir"   => $this->cfg['document_dir'],
            "documents" => $documents,
            "layout"    => $this->cfg["layout"]
        );
        $body = $this->tmpl_o->get_template("documents_body", $fields, $params);
    }
    else
    {
        $fields = array(
            "event-title" => $event['title'],
            "record-type" => $mode
        );

        $params = array(
            "eventid" => $eid,
            "record-type" => $mode,
            "layout" => $this->cfg["layout"]
        );
        $body = $this->tmpl_o->get_template("no_records", $fields, $params);
    }

    return $body;
}

private function get_notices_htm($eid, $mode, $event, $content, $notices)
{
    if (count($notices) > 0)
    {
        count($notices)> 1 ? $num_notices = "Currently ".count($notices)." $mode notices" : $num_notices = "Currently 1 $mode notice";
        $max_date = date("Y-m-d", 0);
        foreach ($notices as $k=>$notice)
        {
            if ($notice['createdate'] > $max_date)
            {
                $max_date = $notice['createdate'];
            }
        }

        $fields = array(
            "event-title" => $event['title'],
            "notices-intro" => $content['notices-intro']['content'],
            "num-notices" => $num_notices
        );

        count($notices) > 0 ? $fields['latest-notice-date'] = "[ last notice at ".date("d-M-Y H:i", strtotime($max_date))." ]" : $fields['latest-notice-date'] = "";

        $params = array(
            "eventid" => $eid,
            "content" => $content,
            "notices" => $notices,
            "layout" => $this->cfg["layout"]
        );

        $body = $this->tmpl_o->get_template("notices_body", $fields, $params);
    }
    else
    {
        $fields = array(
            "event-title" => $event['title'],
            "record-type" => "notices"
        );

        $params = array(
            "eventid" => $eid,
            "record-type" => "notices",
            "layout" => $this->cfg["layout"]
        );
        $body = $this->tmpl_o->get_template("no_records", $fields, $params);
    }

    return $body;
}

private function get_error_htm($page, $event, $problem, $evidence)
{
    $fields = array(
        "page"        => $page,
        "problem"     => $problem,
        "location"    => __FILE__." - #".__LINE__,
        "evidence"    => $evidence,
        "report-link" => $this->cfg['system_admin_contact'],
        "event-title" => $event['title']
    );

    $params = array("action" => "", "event-title" => $event['title']);

    return $this->tmpl_o->get_template("error_body", $fields, $params);
}

private function mark_entries_requiring_consent($entries)
{
    /*
     Identifies which boats have juniors on board, and how many consent forms are required
     Returns the information in an updated 'entries' array
     */

    global $db_o;

    foreach ($entries as $k=>$entry)
    {
        $entries[$k]['junior'] = false;
        $num_consents_reqd = 0;
        if (!empty($entry['h-age']) and $entry['h-age'] < 18) { $num_consents_reqd++; $entries[$k]['junior'] = true;}
        if (!empty($entry['c-age']) and $entry['c-age'] < 18) { $num_consents_reqd++; $entries[$k]['junior'] = true;}

        $num_consents = $db_o->run("SELECT count(*) as consents FROM e_consent WHERE entryid = ? GROUP BY entryid", array($entry['id']) )->fetchColumn();
        $num_consents < $num_consents_reqd ? $entries[$k]['consents_reqd'] = $num_consents_reqd - $num_consents: $entries[$k]['consents_reqd'] = 0;
    }

    return $entries;
}

private function entry_confirm_params($entryupdate, $waiting, $eid)
{
    global $db_o;

    if ($entryupdate['status'] == "success")   // get record data
    {
        $entry = $db_o->run("SELECT * FROM e_entry WHERE id = ?", array($entryupdate['recordid']))->fetch();
    }
    else
    {
        $entry = array();
    }

    if ($entry)
    {
        $waiting_num = 0;
        if ($entry['e-waiting'])   // get position of new entry on waiting list
        {
            foreach ($waiting as $row)
            {
                if ($row['e-entryno'] <= $entry['e-entryno']) { $waiting_num++; }
            }
        }
    }
    $params = array(
        "eid"     => $eid,
        "process" => $entryupdate,
        "entry"   => $entry,
        "waiting" => $waiting_num
    );

    return $params;
}

private function exitnicely($title, $error, $script, $action, $contact, $attr = array())
{
// FIXME write to event log
//    $logmsg = "**** FATAL ERROR - $error".PHP_EOL."script: $script, event: $eventid, function: {$attr['function']}, line: {$attr['line']}, calledby: {$attr['calledby'}]";
//    u_writelog($logmsg, 0);                                // write to system log
//    if ($eventid!=0) { u_writelog($logmsg, $eventid); }    // write to event log

    $fields = array(
        "error" => $error, "script" => $script, "function" => $attr['function'], "line" => $attr['line'],
    );

    $params = array(
        "contact-link" => $contact
    );

    $body = $this->tmpl_o->get_template("fatal_error_body", $fields, $params);

    // assemble page
    $fields = array(
        'page-title'=>$title,
        'page-navbar'=>"",
        'page-main'=>$body,
        'page-footer'=>"",
        'page-modals'=>"&nbsp;",
        'page-js'=>"&nbsp;");
    echo $this->tmpl_o->get_template("page", $fields, array());

    exit();
}


} // end of class