<?php

class PAGES
{
    public function __construct($cfg)
    {
        $this->tmpl_o = new TEMPLATE(array( "./templates/layouts_tm.php"));
        $this->cfg = $cfg;

        // fixme needs to be in session when eid is established (also need to have numbers for entries, documents and notices)
        $this->cfg['options'] = array(
            "1" => array("page" => "details","label" => "Event Details", "script" => "rm_event.php?page=details&eid="),
            "2" => array("page" => "entries","label" => "Entries", "script" => "rm_event.php?page=entries&eid="),
            "3" => array("page" => "documents", "label" => "Documents", "script" => "rm_event.php?page=documents&eid="),
            "4" => array("page" => "notices","label" => "Notices", "script" => "rm_event.php?page=notices&eid="),
            "5" => array("page" => "results", "label" => "Results", "script" => "rm_event.php?page=results&eid=")
        );

        //echo "<pre>".print_r($this->cfg,true)."</pre>";
    }

    public function pg_list($db_o, $year = "")
    {
        // FIXME : needs to handle "review" and "cancel" status - only available with password
        //


        // get events for selected year
        if (empty($year)) { $year = date("Y"); }
        $events = $db_o->run("SELECT * FROM e_event WHERE `date-start` >= ? and `date-end` <= ?", array("$year-01-01","$year-12-31") )->fetchall();

        // get club contact
        $contacts = parse_contacts($this->cfg['rm_event']['events_contact'], "club");

        // create navbar
        $fields = array("eventid" => "", "version" => $this->cfg['sys_version'], "year" => date("Y"),
                        "brand-label" => $this->cfg['rm_event']['brand']);
        $params = array("page" => "list", "active" => "", "start-year"=> $this->cfg['rm_event']['start_year'], "options"=>$this->cfg['options'], "contact"=> $contacts );
        $nav =$this->tmpl_o->get_template("navbar", $fields, $params);

        // create page body
        $list_panels_htm = "";
        foreach ($events as $event)
        {
            $panel_status = get_event_list_status($event);
            $panel_style = get_event_list_style($panel_status);
            $fields = array(
                "event-dates"      => format_event_dates($event['date-start'], $event['date-end']),
                "event-title"      => $event['title'],
                "sub-title"        => $event['sub-title'],
                "list-status-txt"  => $event['list-status-txt'],
                "event-style"      => $panel_style
            );
            $params = array("event-status" => $panel_status,
                "sub-title" => $event['sub-title'],
                "list-status-txt" => $event['list-status-txt'],
                "eid" => $event['id']);

            $list_panels_htm.= $this->tmpl_o->get_template("list_event_panel", $fields, $params);
        }

        $fields = array("list-lead-txt" => $this->cfg['rm_event']['list_lead_txt'],
                        "event-panels" => $list_panels_htm
        );
        $params = array("year" => $year);
        $body =$this->tmpl_o->get_template("list_body", $fields, $params);

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

    public function pg_event($db_o, $page, $eid, $newentry)
    {
        // get event details
        $event = $db_o->run("SELECT * FROM e_event WHERE id = ?", array($eid) )->fetch();

        // get contacts
        $contacts_rs = $db_o->run("SELECT * FROM e_contact WHERE eid = ?", array($eid) )->fetchall();
        if ($contacts_rs)
        {
            $contacts = parse_contacts($contacts_rs, "event");
        }
        else
        {
            $contacts = parse_contacts($this->cfg['rm_event']['events_contact'], "club");
        }

        // get content
        $event_content = $db_o->run("SELECT * FROM e_content WHERE (eid = 0 or eid = ?) and page = ?", array($eid, $page) )->fetchall();
        $content = array();
        foreach($event_content as $rs) { $content[$rs['name']] = $rs; }    // index by key

        // get info counts
        $counts["entries"]   = $db_o->run("SELECT count(*) FROM e_entry WHERE eid=?", array($eid) )->fetchColumn();
        $counts["documents"] = $db_o->run("SELECT count(*) FROM e_document WHERE eid=? and category != 'results'", array($eid) )->fetchColumn();
        $counts["notices"]   = $db_o->run("SELECT count(*) FROM e_notice WHERE eid=?", array($eid) )->fetchColumn();
        $counts["results"]   = $db_o->run("SELECT count(*) FROM e_document WHERE eid=? AND category = 'results'", array($eid) )->fetchColumn();

        // create navbar
        $fields = array("eventid" => $eid, "version" => $this->cfg['sys_version'], "year" => date("Y"),
            "brand-label" => $this->cfg['rm_event']['brand']);
        $params = array("page" => $page, "active" => $page, 'eid' => $eid,
            "start-year"=> $this->cfg['rm_event']['start_year'], "options"=>$this->cfg['options'],
            "contact"=> $contacts, "counts" => $counts);
        $nav =$this->tmpl_o->get_template("navbar", $fields, $params);

        // create body
        $body = "";
        if ($event)
        {
            // ---------------------------------- details page
            if ($page == "details")
            {
                // get topics (reusable parts of content)
                if (!empty($event['topics']))
                {
                    $event_topics = $db_o->run("SELECT * FROM e_content WHERE id IN ({$event['topics']}) and page = ?", array($page))->fetchall();
                }

                $topics = array();
                foreach ($event_topics as $rs) { $topics[$rs['label']] = $rs; }

                $fields = array(
                    "event-title" => $event['title'],
                    "event-dates" => format_event_dates($event['date-start'], $event['date-end']),
                    "event-subtitle" => $event['sub-title'],
                );

                $params = array(
                    "eventid" => $eid,
                    "content" => $content,
                    "topics" => $topics
                );

                $body = $this->tmpl_o->get_template("details_body", $fields, $params);
            }


            // ---------------------------------- entries page
            elseif ($page == "entries")
            {
                $entries = $db_o->run("SELECT * FROM e_entry WHERE eid = ? and `e-exclude` = 0 ORDER BY `b-fleet` ASC, `b-class` ASC, `b-sailno` * 1 ASC", array($eid) )->fetchall();

                if ($newentry == "noentry" or $newentry == "failed")           // no new entry or entry failed
                {
                    $entry_data['status'] = $newentry;
                }
                else                                                           // possible new entry get details
                {
                    $entry_data = $db_o->run("SELECT * FROM e_entry WHERE id = ? ", array(intval($newentry)) )->fetch();
                    $entry_data ? $entry_data['status'] = "success" : $entry_data['status'] = "notfound";
                }

                if (count($entries) > 0)
                {
                    $fields = array(
                        "event-title" => $event['title'],
                        "entry-count" => count($entries),
                        "entries-intro" => $content['entries-intro']['content'],
                    );
                    $params = array(
                        "eventid" => $eid,
                        "entries" => $entries,
                        "entry-end" => $event['entry-end'],
                        "newentry"  => $entry_data
                    );

                    $body = $this->tmpl_o->get_template("entries_body", $fields, $params);
                }
                else
                {
                    $fields = array(
                        "event-title" => $event['title'],
                        "record-type" => "entries"
                    );

                    $params = array(
                        "eventid" => $eid,
                        "record-type" => "entries",
                    );
                    $body = $this->tmpl_o->get_template("no_records", $fields, $params);
                }
            }


            // ---------------------------------- newentry page
            /*
             * Issues
             *  - where to handle the form processing
             *  - how to hanle multiple different forms
             */
            elseif ($page == "newentry")
            {
                // get form (internal or external)
                if (!empty($event['entry_form_link']))   // external entry form
                {
                    // go to external form
                    header("Location:{$event['entry-form-link']}");
                    exit;
                }
                elseif (!empty($event['entry-form']))    // internal entry form (e.g merlin_open_fm.php?class=merlin&mode=confirmation)
                {
                    $url_arr = parse_url($event['entry-form']);
                    empty($url_arr['query']) ? $form_params = array() : parse_str(parse_url($url_arr['query']), $form_params); // adds any params to an array
                    $form_name = $url_arr['path'];

                    $form_detail = $db_o->run("SELECT * FROM e_form WHERE `form-label` = '$form_name'", array() )->fetch();


                    $fields = array(
                        "event-title" => $event['title'],
                        "newentry-intro" => $content['newentry-intro']['content'],
                    );

                    $params = array(
                        "eventid" => $eid,
                        "form-name" => $form_name,
                        "form-params" => $form_params,
                        "newentry-instructions" => "instructions for the merlin open"
                    );
                    $body = $this->tmpl_o->get_template("newentry_body", $fields, $params);
                }
                else                                     // no entry form report error
                {
                    $fields = array(
                        "page" => $page,
                        "problem" => "The requested entry form is not recognised.",
                        "location" => __FILE__." - #".__LINE__,
                        "evidence" => "form selected: internal - {$event['entry_form']} external - {$event['entry_form_link']}",
                        "report-link" => $this->cfg['rm_event']['system_admin_contact'],
                        "event-title" => $event['title']
                    );

                    $params = array("action" => "", "event-title" => $event['title']);
                    $body = $this->tmpl_o->get_template("error_body", $fields, $params);
                }

            }


            // ---------------------------------- documents page
            elseif ($page == "documents")
            {
                $documents = $db_o->run("SELECT * FROM e_document WHERE eid = ? AND category != 'results' ORDER BY createdate DESC", array($eid) )->fetchall();

                if (count($documents) > 0)
                {
                    $fields = array(
                        "event-title" => $event['title'],
                        "documents-intro" => $content['documents-intro']['content'],
                    );

                    $params = array(
                        "eventid" => $eid,
                        "mode" => "documents",
                        "content" => $content,
                        "documents" => $documents
                    );

                    $body = $this->tmpl_o->get_template("documents_body", $fields, $params);
                }
                else
                {
                    $fields = array(
                        "event-title" => $event['title'],
                        "record-type" => "documents"
                    );

                    $params = array(
                        "eventid" => $eid,
                        "record-type" => "documents",
                    );

                    $body = $this->tmpl_o->get_template("no_records", $fields, $params);
                }

            }

            // ---------------------------------- notices page
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
                        "notices" => $notices
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
                    );
                    $body = $this->tmpl_o->get_template("no_records", $fields, $params);
                }
            }


            // ---------------------------------- results page
            elseif ($page == "results")
            {
                // reusing documents page rendering

                $documents = $db_o->run("SELECT * FROM e_document WHERE eid = ? and category = ? ORDER BY createdate DESC", array($eid, 'results') )->fetchall();

                if (count($documents) > 0)
                {
                    $fields = array(
                        "event-title" => $event['title'],
                        "documents-intro" => $content['results-intro']['content'],
                    );

                    $params = array(
                        "eventid" => $eid,
                        "mode" => "results",
                        "content" => $content,
                        "documents" => $documents
                    );

                    $body = $this->tmpl_o->get_template("documents_body", $fields, $params);
                }
                else
                {
                    $fields = array(
                        "event-title" => $event['title'],
                        "record-type" => "results"
                    );

                    $params = array(
                        "eventid" => $eid,
                        "record-type" => "results",
                    );
                    $body = $this->tmpl_o->get_template("no_records", $fields, $params);
                }
            }


            // ---------------------------------- unknown page
            else
            {
                // report unknown page
                $fields = array(
                    "page" => $page,
                    "problem" => "The page you requested is not recognised.",
                    "location" => __FILE__." - #".__LINE__,
                    "evidence" => '$page = '."|$page|",
                    "report-link" => $this->cfg['rm_event']['system_admin_contact'],
                    "event-title" => $event['title']
                );

                $params = array("action" => "", "event-title" => $event['title']);

                $body = $this->tmpl_o->get_template("error_body", $fields, $params);
            }
        }
        // ----------------------- unknown event
        else
        {
           // report unknown event
            $fields = array(
                "page" => $page,
                "problem" => "The event you requested is not recognised.",
                "location" => __FILE__." - #".__LINE__,
                "evidence" => 'eventid = '."|$eid|",
                "report-link" => $this->cfg['rm_event']['system_admin_contact'],
                "event-title" => $event['title']
            );

            $params = array("action" => "", "event-title" => $event['title']);

            $body = $this->tmpl_o->get_template("error_body", $fields, $params);
        }

        // create footer
        $fields = array('version' => $this->cfg['sys_version'], 'year' => date("Y"));
        $params = array('page' => $page, 'eid' => $eid, 'footer' => true, "counts" => $counts);
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






















}