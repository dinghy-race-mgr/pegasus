<?php

class PAGES
{
    public function __construct($cfg)
    {
        $this->tmpl_o = new TEMPLATE(array( "./templates/layouts_tm.php"));
        $this->cfg = $cfg;

        // fixme needs to be in session when eid is established (also need to have numbers for entries, documents and notices)
        $this->cfg['options'] = array(
            "1" => array("page" => "details","label" => "Event Details", "script" => "rm_event.php?page=details&eid=", "num" => ""),
            "2" => array("page" => "entries","label" => "Entries", "script" => "rm_event.php?page=entries&eid=", "num" => 0),
            "3" => array("page" => "documents", "label" => "Documents", "script" => "rm_event.php?page=documents&eid=", "num" => 0),
            "4" => array("page" => "notices","label" => "Notices", "script" => "rm_event.php?page=notices&eid=", "num" => ""),
            "5" => array("page" => "results", "label" => "Results", "script" => "rm_event.php?page=results&eid=", "num" => "")
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

    public function pg_event($db_o, $page, $eid)
    {
        echo "<pre>$page|$eid</pre>";

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
        echo "<pre>$page|$eid</pre>";

        // get info counts
        $counts["entries"]   = $db_o->run("SELECT count(*) FROM e_entry WHERE eid=?", array($eid) )->fetchColumn();
        $counts["documents"] = $db_o->run("SELECT count(*) FROM e_document WHERE eid=?", array($eid) )->fetchColumn();
        $counts["notices"]  = $db_o->run("SELECT count(*) FROM e_notice WHERE eid=?", array($eid) )->fetchColumn();

        // create navbar
        $fields = array("eventid" => $eid, "version" => $this->cfg['sys_version'], "year" => date("Y"),
            "brand-label" => $this->cfg['rm_event']['brand']);
        $params = array("page" => $page, "active" => $page, 'eid' => $eid,
            "start-year"=> $this->cfg['rm_event']['start_year'], "options"=>$this->cfg['options'],
            "contact"=> $contacts, "counts" => $counts);
        $nav =$this->tmpl_o->get_template("navbar", $fields, $params);

        // create body
        $body = <<<EOT
         <h1> "$page" Page for Event $eid</h1>
EOT;
//      if ($event)
//      {
//          elseif ($page == "details") {}
//          elseif ($page == "entries") {}
//          elseif ($page == "documents") {}
//          elseif ($page == "notices") {}
//          elseif ($page == "results") {}
//      }
//      else
//      {
//           report error - no event
//      }

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

    public function pg_entries($db_o, $eid)
    {
        // create navbar
        $contact_data = explode(",", $this->cfg['rm_event']['events_contact']);
        $contacts[0] = array("name" => trim($contact_data[0]), "role" => trim($contact_data[1]), "email" => trim($contact_data[2]));

        $fields = array("eventid" => "", "version" => $this->cfg['sys_version'], "year" => date("Y"),
            "brand-label" => $this->cfg['rm_event']['brand']);

        $params = array("page" => "entries", "active" => "entries",  'eid' => $eid,
            "start-year"=> $this->cfg['rm_event']['start_year'], "options"=>$this->cfg['options'], "contact"=> $contacts );
        $nav =$this->tmpl_o->get_template("navbar", $fields, $params);

        // create body
        $body = <<<EOT
         <h1> Entries Page for Event $eid </h1>
EOT;

        // create footer
        $fields = array('version' => $this->cfg['sys_version'], 'year' => date("Y"));
        $params = array('page' => "entries", 'eid' => $eid, 'footer' => true, );
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

    public function pg_documents()
    {

    }

    public function pg_notices()
    {

    }

    public function pg_results()
    {

    }






















}