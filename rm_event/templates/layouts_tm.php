<?php


function page ($params = array())
{
    $htm = <<<EOT
    <!doctype html>
    <html lang="en" class="h-100">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="Mark Elkington">
        <title>{page-title}</title>
    
        <link rel="canonical" href="https://getbootstrap.com/docs/5.0/examples/sticky-footer-navbar/">
    
        <!-- Bootstrap core CSS -->
        <link href="../common/oss/bootstrap532/css/{page-theme}bootstrap.min.css" rel="stylesheet">
        <link href="../common/oss/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        
        <!-- Bootstrap core js -->
        <script src="../common/oss/bootstrap532/js/bootstrap.bundle.min.js"></script>      
    
        <!-- Custom styles for this template -->
        <link href="sticky-footer-navbar.css" rel="stylesheet" >
        <link href="./style/rm_event.css" rel="stylesheet">
    </head>
    <body class="d-flex flex-column h-100">
    
        {page-navbar}
        
        {page-main}
        
        {page-footer}
        
        {page-modals}
               
        {page-js}
        
        <script>
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        </script>
    
    </body>
    </html>

EOT;

    return $htm;
}

function navbar ($params = array())
{
//    echo "<pre>".print_r($params,true)."</pre>";
//    exit();


    // setup page link options
    $htm_options = "";
    if ($params['page'] != "list")
    {
        foreach ($params['options'] as $option)
        {
            // don't include page if event is complete unless its the results page or view mode id "preview"
            //if ($option['page'] != "results" and $params['complete']) { continue; }
            //echo "<pre>{$params['view']} {$params['complete']} {$option['page']}</pre>";
            if ($params['view'] != "preview" and $params['complete'] and $option['page'] != "results") { continue; }

            //echo "<pre>continuing</pre>";
            $inc_count = "";
            if ($option['page'] == "documents" or $option['page'] == "entries" or
                $option['page'] == "notices" or $option['page'] == "results")
            {
                $params['counts'][$option['page']] == 0 ? $inc_count = "" : $inc_count = "({$params['counts'][$option['page']]})";
            }

            if ($params['active'] == $option['page'])
            {
                $htm_options.=<<<EOT
                <li class="nav-item px-2 lead active active-option">
                    <a class="nav-link text-black fw-bold" href="{$option['script']}{$params['eid']}&view={$params['view']}">{$option['label']}&nbsp;$inc_count</a>
                </li>
EOT;
            }
            else
            {
                $htm_options.=<<<EOT
                <li class="nav-item px-2 lead">
                    <a class="nav-link" href="{$option['script']}{$params['eid']}&view={$params['view']}">{$option['label']}&nbsp;$inc_count</a>
                </li>
EOT;
            }
        }
    }



    // setup contact drop down
    $htm_contacts = "";
    foreach ($params['contact'] as $contact)
    {
        $htm_contacts.= <<<EOT
        <li><a class="dropdown-item text-info fs-5" href="{$contact['link']}" target="_BLANK">{$contact['name']} - {$contact['job']}</a></li>
EOT;
    }

    // setup previous years drop down for list page
    $current_year = date("Y");
    $htm_year_select = "";
    for ($i = $current_year; $i >= $params['start-year']; $i--)
    {
        $htm_year_select.= <<<EOT
            <li><a class="dropdown-item text-info fs-5" href="rm_event.php?page=list&year=$i&view={view}">$i</a></li>
EOT;
    }

    // if we are past 1 Sept in current year - allow option to see next year's events
    if (strtotime(date("Y-m-d")) > strtotime(date("Y-m-d", mktime(0, 0, 0, 9, 1, $current_year))))
    {
        $k = $current_year + 1;
        $add = <<<EOT
            <li><a class="dropdown-item text-info fs-5" href="rm_event.php?page=list&year=$k&view={view}">next year</a></li>
EOT;
        $htm_year_select = $add.$htm_year_select;
    }

    $htm_years = "";
    if ($params['page'] == "list" and !empty($htm_year_select))
    {
        $htm_years = <<<EOT
        <div class="navbar-text dropdown-center" style="min-width: 300px;">
            <a class="btn btn-info dropdown-center btn-lg dropdown-toggle " href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Change Year
                </a>
                <ul class="dropdown-menu dropdown-menu-dark">
                    $htm_year_select
                </ul>
        </div>
EOT;
    }

    $htm = <<<EOT
    <header>
    <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand text-info" href="rm_event.php?page=list">{brand-label}</a>
            <div class="">
                <button class="navbar-toggler " type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav me-auto mb-md-0 flex-nowrap" >$htm_options</ul>
                $htm_years
                <span class="navbar-text">
                    <a class="btn btn-info btn-lg dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Contact SYC</a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark"> $htm_contacts </ul>
                </span>
            </div>
        </div>
    </nav>
    </header>
EOT;

    return $htm;
}

function footer ($params = array())
{
    if (!$params['footer'])
    {
        $htm = "";
    }
    elseif ($params['page'] == "details")
    {
        $params['counts']['entries'] > 0 ? $entries_htm = "[{$params['counts']['entries']}]" : $entries_htm = "";
        $params['counts']['documents'] > 0 ? $documents_htm = "[{$params['counts']['documents']}]" : $documents_htm = "";
        $params['counts']['notices'] > 0 ? $notices_htm = "[{$params['counts']['notices']}]" : $notices_htm = "";

        $htm = <<<EOT
        <footer class="footer"> 
            <div class="container-fluid px-4 py-3 text-bg-secondary d-flex align-items-baseline">
                <div class="row gap-5"
                    <div class="col-8 text-start  ">
                        <a class="btn btn-warning fs-5" href="rm_event.php?page=entries&eid={$params['eid']}" style="width: 200px" >
                            <b>ENTER EVENT</b> $entries_htm
                        </a>
                        <a class="btn btn-warning fs-5" href="rm_event.php?page=documents&eid={$params['eid']}" style="width: 200px" >
                            <b>DOCUMENTS</b> $documents_htm
                        </a>
                        <a class="btn btn-warning fs-5 " href="rm_event.php?page=notices&eid={$params['eid']}" style="width: 200px" >
                            <b>NOTICES</b> $notices_htm
                        </a>
                    </div>           
                    <div class="col text-end">
                        <span class="align-top">raceManager {version}<br>copyright Elmswood Software {year}</span>
                    </div>                   
                </div>
            </div>    
        </footer>
EOT;
    }
    else
    {
        $htm = <<<EOT
        <footer class="footer pt-5">
            <div class="container-fluid py-2 text-bg-secondary text-end">
                raceManager {version} - copyright Elmswood Software {year}
            </div>
        </footer>
EOT;
    }

    return $htm;
}

function list_body_1 ($params = array())
{
    $events = $params['events'];

    // set up page title
    $lead_txt = "";
    $year = $params['year'];

    $panels_htm = "";
    foreach ($events as $event)
    {
        // prep data
        $event_status = get_event_list_status($event);
        $panel_style  = get_event_list_style($event_status);
        $event_dates  = format_event_dates($event['date-start'], $event['date-end']);

        // get link
        $link = "";
        if ($event_status == "open" or $event_status == "complete" or $event_status == "review")
        {
            if ($event_status == "open" or $event_status == "review")
            {
                $link = "rm_event.php?page=details&eid={$event['id']}";
                $link_icon = "bi-box-arrow-right";
                $link_title = "Get details, documents and enter online";
            }
            elseif ($event_status == "complete")
            {
                $link = "rm_event.php?page=results&eid={$event['id']}";
                $link_icon = "bi-collection-play";
                //$link_icon = "bi-file-ruled-fill";
                $link_title = "Get Results, Reports, Photos, etc";
            }

            $link_htm = <<<EOT
            <div class="position-absolute top-0 end-0">
                <a class="icon-link fs-4 " data-bs-toggle="tooltip" data-bs-placement="top"
                   data-bs-custom-class="list-tooltip" data-bs-title="$link_title" 
                   href="$link" title="$link_title" >
                    <i class="$link_icon" style="font-size: 3rem; color: black;"></i>
                </a>                                 
            </div>
EOT;
        }
        elseif ($event_status == "list" or $event_status == "cancel")
        {
            $link_htm = "&nbsp;";
        }

        // get text for event panel
        $title_htm = "$event_dates - <b>{$event['title']}</b><br>";
        if ($event['sub-title'])
        {
            $title_htm .= "<p class='fs-6 mb-0'>{$event['sub-title']}</p>";
        }

        !empty($event['list-status-txt']) ? $status_htm = $event['list-status-txt'] : $status_htm = "&nbsp;";

        // event panel
        $panels_htm .= <<<EOT
          
        <div class="col-12"> 
            <div class="alert $panel_style mb-3" role="alert"> 
            <div class="row">                 
                <div class="col-7" style="border-right: solid 1pt white !important;">               
                    <div class="fs-5"> $title_htm </div>
                </div>
                <div class="col-4">
                    <div class="fs-6"> $status_htm </div>
                </div>
                <div class="col-1">
                    <div class="position-relative"> $link_htm </div>
                </div>
            </div>
            </div>
        </div>
EOT;
    }

    $params['layout'] == "wide" ? $container = "container-fluid" : $container = "container";
    $htm = <<<EOT
    <main class="" >
        <div class="$container nav-margin">
            <p class="display-6 text-info mb-3"><b>$year Events Schedule</b></p>   
            $panels_htm
        </div>
    </main>
EOT;

    return $htm;
}

function list_body_2 ($params = array())
{
    $events = $params['events'];

    // set up page title
    $lead_txt = "";
    $year = date("Y");

    $i = 0;        // row counter
    $panels_htm = "";
    foreach ($events as $event)
    {
        // prep data
        $event_status    = get_event_list_status($event);
        $panel_style     = get_event_list_style($event_status);
        $event_dates     = format_event_dates($event['date-start'], $event['date-end']);

        // get link
        $link = "";
        if ($event_status == "open" or $event_status == "complete" or $event_status == "review")
        {
            if ($event_status == "open" or $event_status == "review") {
                $link = "rm_event.php?page=details&eid={$event['id']}";
                $link_icon = "bi-box-arrow-right";
                $link_title = "Get details, documents and enter online";
            } elseif ($event_status == "complete") {
                $link = "rm_event.php?page=results&eid={$event['id']}";
                $link_icon = "bi-file-ruled-fill";
                $link_title = "Get Results, Reports, Photos, etc";
            }

            $link_htm = <<<EOT
            <div class="position-absolute top-0 end-0">
                <a class="icon-link fs-4 " data-bs-toggle="tooltip" data-bs-placement="top"
                   data-bs-custom-class="list-tooltip" data-bs-title="$link_title" 
                   href="$link" title="$link_title" >
                    <i class="$link_icon" style="font-size: 2rem; color: white;"></i>
                </a>                                 
            </div>
EOT;
        }
        elseif ($event_status == "list" or $event_status == "cancel")
        {
            $link_htm = "&nbsp;";
        }

        // get text for event panel
        $title_htm = "$event_dates - <b>{$event['title']}</b><br>";
        if ($event['sub-title']) { $title_htm.= "<p class='fs-5 mb-0'>{$event['sub-title']}</p>" ;}

        !empty($event['list-status-txt']) ? $status_htm = $event['list-status-txt'] : $status_htm = "&nbsp;";

        $i++;                                                        // panel on row count
        if ($i == 1)                                                 // start a new row
        {
            $panels_htm.= "<div class=\"row justify-content-evenly\">";
        }

        // event panel
        $panels_htm.= <<<EOT
        <div class="col-6">
            <div class="alert $panel_style mb-5" role="alert">
                <div class="row">
                    <div class="col-md-11 fs-4"> $title_htm </div>
                    <div class="col-md-1 position-relative"> $link_htm </div>
                </div>
                <div class = "row">
                    <div class="col-md-12 fs-6"> $status_htm </div>
                </div>
            </div>
        </div>
EOT;

        if($i == 2)                                                  // finish a row - two panels per row
        {
            $panels_htm.= "</div>";
            $i = 0;              // reset for next row
        }
    }

    // deal with trailing single panel
    if ($i == 1) { $panels_htm.= "<div class=\"col-6\">&nbsp</div>";}

    $params['layout'] == "wide" ? $container = "container-fluid" : $container = "container";
    $htm = <<<EOT
    <main class="" >
        <div class="$container nav-margin">
            <p class="display-6 text-info mb-3"><b>$year Events Schedule</b></p>   
            $panels_htm
        </div>
    </main>
EOT;

    return $htm;
}



function details_body ($params = array())
{
    // assemble lead text layout
    $htm_leadtext = "";
    if (array_key_exists("event-leadtext", $params['content']))
    {
        $htm_leadtext = render_content($params['content']['event-leadtext'], $params['document_dir']);
    }

    // assemble subtext layout
    $htm_subtext = render_content($params['content']['event-subtext'], $params['document_dir']);

    // assemble collapsible topics layout
    $htm_topics_buttons = "";
    $htm_topics_content = "";
    $i = 0;
    foreach ($params['topics'] as $topic)
    {
        $i++;

        $htm_topics_buttons.= <<<EOT
        <a class="btn btn-info fs-4" style="width: 300px" data-bs-toggle="collapse" href="#collapsetopic$i" 
           role="button" aria-expanded="false" aria-controls="collapsetopic$i">
            {$topic['content-label']} &hellip;
        </a>       
EOT;

        $htm_topic = render_content($topic, $params['document_dir'], "bottom");
        $htm_topics_content.= <<<EOT
        <div class="collapse" id="collapsetopic$i">
            <div class="card card-body fs-6" style="background-color: lightyellow">
                <p class="lead"><b>{$topic['content-label']} &hellip;</b> </p>
                $htm_topic
            </div>
        </div>
EOT;
    }

    // assemble complete body htm
    $params['layout'] == "wide" ? $container = "container-fluid" : $container = "container";
    $htm = <<<EOT
    <main class="" >
        <div class="$container nav-margin">
            <p class="display-6 text-info mb-0"><b>{event-title}</b></p>
            <p class="fs-5 mb-0">{event-dates} | {event-subtitle}</p>
            <p class="lead mt-2">$htm_leadtext</p>
            <div>$htm_subtext</div>
            <div class="container py-3">
                <div class="row">
                <p class="d-inline-flex gap-4">
                    $htm_topics_buttons
                </p>
                </div>
            </div>            
        </div>
        <div>$htm_topics_content</div>
    </main>
EOT;

    return $htm;
}

function entries_body ($params = array())
{
    // construct confirmed entries table
    $entry_rows = "";
    $current_fleet = "";
    $i = 0;
    foreach ($params['entries'] as $entry)
    {
        if ($i == 1 or $current_fleet != $entry['b-fleet'])  // add fleet divider if new fleet
        {
            $entry_rows.="<tr><td colspan='6' class='table-success'>{$entry['b-fleet']}</td></tr>";
        }

        $entry['id'] == $params['process']['recordid'] ? $highlight = "fw-bold text-dark table-warning" : $highlight = "" ; // set row styling to highlight new entry

        // setup team names and clubs
        empty($entry['c-name']) ? $people = $entry['h-name'] : $people = truncatestring($entry['h-name']." / ".$entry['c-name'], 40);
        empty($entry['c-club']) ? $clubs = $entry['h-club'] : $clubs = truncatestring($entry['h-club']." / ".$entry['c-club'], 40);

        // setup notes field
        $notes_htm = "";
        if ($entry['consents_reqd'] > 0)
        {
            $entry['consents_reqd'] == 1 ? $num_reqd = "reqd" : $num_reqd = $entry['consents_reqd']." required";
            $notes_htm.= <<<EOT
            <a class="btn btn-sm btn-secondary" href="rm_event.php?page=juniorconsentform&eid={$params['eid']}&recordid={$entry['id']}" role="button">
            <span class="fs-6">Parental Consent ($num_reqd)</span>
            </a>
EOT;
        }
        else
        {
            if ($entry['junior']) { $notes_htm.= "<span class='fs-6'>Parental Consent (<i class=\"bi-check2-square\" style=\"font-size: 1rem; color: darkgreen;\"></i>)</span>"; }
        }

        // add row
        $entry_rows.= <<<EOT
        <tr class="$highlight">
            <td>{$entry['b-class']}</td>
            <td>{$entry['b-sailno']}</td>
            <td>$people</td>
            <td>$clubs</td>
            <!-- td>{$entry['b-division']}</td -->
            <td width="20%">$notes_htm</td>
        </tr>
EOT;
        $current_fleet = $entry['b-fleet'];
    }

    // construct waiting list entries table
    $waiting_htm = "";
    if (count($params['waiting']) > 0)  // waiting list is active
    {
        // create list of boats on waiting list
        $waiting_rows = "";
        $i = 0;
        foreach ($params['waiting'] as $row)
        {
            $i++;
            $order = numordinal($i);
            $waiting_rows .= <<<EOT
        <tr>
            <td>$order</td>
            <td>{$row['b-class']}</td>
            <td>{$row['b-sailno']}</td>
            <td>{$row['h-name']}</td>
            <td>{$row['h-club']}</td>
            <td>{$row['b-division']}</td>
        </tr>
EOT;
        }
        // create waiting list table
        $waiting_htm = <<<EOT
        <hr>
        <div class="pt-3 col-8">             
            <table class="table table-light table-hover caption-top">
                <caption class="fs-4">Waiting List</caption>
                <tbody class="table-group-divider">
                    $waiting_rows
                </tbody>
            </table>
            </div>
        </div>
EOT;
    }

    $params['layout'] == "wide" ? $container = "container-fluid" : $container = "container";
    $htm = <<<EOT
        <main class="" >
            <div class="$container nav-margin">
                <p class="display-6 text-info mb-0"><b>{event-title}</b></p>
                <p class="lead mt-2">{entries-intro}</p>
                
                {entry-confirm-block}
                
                {entry-status-block}
                                        
                <div class="pt-3">                   
                    <table class="table table-success table-hover caption-top">
                        <caption class="fs-4">List of Entries</caption>
                        <thead>
                            <tr>
                                <td>Class</td><td>Sail Number</td><td>Team</td><td>Club</td><!--td>Division</td--><td>Notes</td>
                            </tr>
                        </thead>
                        <tbody class="table-group-divider">
                            $entry_rows
                        </tbody>
                    </table>
                    </div>
                    $waiting_htm
                </div>
                
            </div>    
        </main>
EOT;

    return $htm;
}

function entry_status_before_open($params = array())
{
    $opendate = date("D jS F", strtotime($params['entry-start']));
    if ($params['entry-end'])
    {
        if (strtotime($params['entry-end']) > strtotime($params['entry-start']))
        {
            $closetxt = " . . . and close on ".date("D jS F", strtotime($params['entry-end']));
        }
        else
        {
            $closetxt = "";
        }
    }
    $txt = "<b>Entries not open yet &hellip;</b> <br><span class='fs-5'>will open on $opendate $closetxt</span>";


    $htm = <<<EOT
    <div class="alert alert-info fs-3" role="alert">
        <div class="row">
            <div class="col-9 fs-4">$txt</div>
        </div> 
    </div>               

EOT;

    return $htm;
}

function entry_status_open($params = array())
{
    // get waiting list information
    $num_waiting = count($params['waiting']);
    $waiting_txt = "";
    if ($num_waiting > 0)
    {
        $waiting_txt = "Waiting list has $num_waiting boats - see below&hellip;";
    }
    elseif ($params['entry-limit'] > 0 and ($params['entry-count'] == $params['entry-limit']))
    {
        $waiting_txt = "Entry limit is {$params['entry-limit']} boats - waiting list is active";
    }

    // getentry buttons for eligible classes
    $entry_btns = get_class_entry_btns ($params['eid'], $params['classes']);

    $htm = <<<EOT
    <div class="alert alert-info" role="alert">
        <div class="row">
            <div class="col-8">$entry_btns</div>
            <div class="col-4 fs-4">
                <p class="text-end">{$params['entry-count']} entries&hellip;<br>
                <p class="text-end fs-6">$waiting_txt</p>
            </div>                       
        </div> 
    </div>
EOT;

    return $htm;
}

function entry_status_after_close($params = array())
{
    $txt = "<h3>Sorry - Entries Now Closed</h3>";

    if ($params['entry-reqd'])
    {
        $txt.= "<p class='text-danger'>An entry is required for this event - apologies but we cannot accept entries on the day of the event</p>";
    }
    else
    {
        $txt.= "<p>Please enter on the day at reception</p>";
    }

    // get waiting list information
    $num_waiting = count($params['waiting']);
    $num_waiting > 0 ? $waiting_txt = "Waiting list has $num_waiting boats &hellip; see below" : $waiting_txt = "";

    $htm = <<<EOT
    <div class="alert alert-info fs-3" role="alert">
        <div class="row">
            <div class="col-8 fs-6">$txt</div>
            <div class="col-4 fs-4">
                <p class="text-end">{entry-count} entries&hellip;<br>
                <p class="text-end fs-6">$waiting_txt</p>
            </div>
        </div> 
    </div>
EOT;

    return $htm;
}

function external_entries_body ($params = array())
{
    $htm = <<<EOT
        <main class="" >
            <div class="container nav-margin min-vh-100">
                <iframe height="1600" width="1000" src="{$params['entry_form']}" title="event entry form"></iframe>           
            </div>    
        </main>
EOT;

    return $htm;
}

function entries_at_club_body ($params = array())
{
    $htm = <<<EOT
        <main class="" >
            <div class="container nav-margin">
                <div class="alert alert-warning" role="alert">
                    <div class="row">
                        <div class="col-1">
                            <p class="bi-info-square-fill" style="font-size: 3rem;"></p>
                        </div>
                        <div class="col-8">
                            <p class="fs-4">We are not collecting pre-entries for this event - please come to the club on the day of the event 
                             and follow the instructions for entering on arrival.</p>
                        </div>                       
                    </div> 
                </div>
            </div>    
        </main>
EOT;

    return $htm;
}

function newentry_body ($params = array())
{
    // include specific form for this event - defines instructions, form and validation html/js
//    echo "<pre>FORM - {$params['form-name']}</pre>";
//    exit();
    require_once("include/{$params['form-name']}");

    // standard entry form layout
    $htm = <<<EOT
        <main class="" >
            <div class="container nav-margin">
                <p class="display-6 text-info mb-0"><b>{event-title}</b></p>            
                $instructions_htm                        
                <div class="pt-3">
                    $form_htm
                </div>
                $validation_htm
            </div>    
        </main>
EOT;

    return $htm;
}

function juniorconsent_body ($params = array())
{
    // include specific form for this event - returns instructions, form and validation html/js
    require_once("include/{$params['form-name']}");

    // standard entry form layout
    $htm = <<<EOT
        <main class="" >
            <div class="container nav-margin">
                <p class="display-6 text-info mb-0"><b>{event-title}</b></p>            
                $instructions_htm                        
                <div class="pt-3">
                    $form_htm
                </div>
                $validation_htm
            </div>    
        </main>
EOT;

    return $htm;
}


function documents_body ($params = array())
{
    global $cfg;
    $today = date("Y-m-d H:i:s");
    $format_icon = array(
        "pdf"      => "bi-filetype-pdf",
        "word"     => "bi-filetype-doc",
        "xls"      => "bi-filetype-xls",
        "htm"      => "bi-link",
        "sailwave" => "bi-file-earmark-ruled"
    );

    if ($params['mode'] == "results")
    {
        $caption = "Results List";
    }
    else
    {
        $caption = "Documents List";
    }

    // documents
    $table_rows = "";
    foreach ($params['documents'] as $document)
    {
        $publish = true;
        // must not be published before publish start date
        if (!empty($document['publish-start']) and strtotime($today) < strtotime($document['publish-start']))
        {
            $publish = false;
        }
        // must not be published after publish end date
        if (!empty($document['publish-end']) and strtotime($today) > strtotime($document['publish-end']))
        {
            $publish = false;
        }
        // must not be published unless document is final (i.e. not draft or embargoed)
        if ($document['status'] != "final")
        {
            $publish = false;
        }

        if ($publish)   // we can publish the document
        {
            $release = date("d-M-Y", strtotime($document['upddate']));

            if ($params['mode'] == "results")
            {
                $version = $document['status'];
            }
            else
            {
                //!empty($document['version']) ? $version = "version: " . $document['version'] : $version = "";
                !empty($document['version']) ? $version = "version: " . $release : $version = "";
            }

            if (empty($document['filename']) and empty($document['file']))
            {
                $link = "available soon ";
            }
            else
            {
                $icon = $format_icon[$document['format']];
                ($document['format'] == "htm" or $document['format'] == "pdf") ? $label = "view" : $label = "download";
                $filename = basename($document['filename']);
                $label == "download" ? $download = "download =\"$filename\"" : $download = "";

                if ($document['file-loc'] == "external")
                {
                    $anchor = $document['filename'];
                }
                elseif ($document['file-loc'] == "local")
                {
                    if (empty($document['filename']))
                    {
                        // decode json content
                        $files = json_decode($document['file'], true);
                        $file_link = $cfg['baseurl'].strstr($files[0]['name'], '/data');
                        //$bit = strstr($files[0]['name'], '/data');
                        //echo "<pre>".$file_link."</pre>";
                        //exit();
                        if (!empty($file_link))
                        {
                            $anchor = $file_link;
                        }
                        else
                        {
                            $anchor = "file not found";
                        }
                    }
                    else
                    {
                        $anchor = "../data/events/{$document['filename']}";
                    }
                }
                else
                {
                    $anchor = $document['filename'];
                }

                $link = <<<EOT
                <a href="$anchor" class="btn btn-sm btn-outline-secondary icon-link fs-6" $download style="min-width: 200px" target="_BLANK">
                        <i class="$icon" style="font-size: 2rem; color: cornflowerblue;"></i> $label
                </a>
EOT;
            }

            $table_rows.= <<<EOT
            <tr>
                <td style="width: 60%"><span class="text-danger fs-3">{$document['title']}</span><br>{$document['infotxt']}</td>
                <!-- td style="width: 20%">$release<br><b>$version</b></td></td -->
                <td style="width: 20%">$version</td></td>
                <td style="width: 20%">$link</td>
            </tr>
EOT;
        }
    }

    $params['layout'] == "wide" ? $container = "container-fluid" : $container = "container";
    $htm = <<<EOT
    <main class="" >
        <div class="$container nav-margin">
            <p class="display-6 text-info mb-0"><b>{event-title}</b></p>
            <p class="lead mt-2">{documents-intro}</p>
            
            <div class="container pt-3">        
                <div class="pt-3">                
                    <table class="table table-light">
                        <caption>List of documents</caption>
                        <tbody class="table-group-divider">
                            $table_rows
                        </tbody>
                    </table>
                </div>
            </div>
        </div>    
    </main>
EOT;

    return $htm;
}


function no_records ($params = array())
{
    $params['layout'] == "wide" ? $container = "container-fluid" : $container = "container";
    $htm = <<<EOT
    <main class="" >
        <div class="$container nav-margin">
            <p class="display-6 text-info mb-6"><b>{event-title}</b></p>
            <div class="alert alert-info" role="alert">
                <div class="row">
                    <div class="col-6 fs-5">No {record-type} have been published yet!</div>
                </div> 
            </div>               
        </div>    
    </main>
EOT;

    return $htm;
}

function notices_body ($params = array())
{
    $title_color = array(
        "protest" => "text-success",
        "competitor" => "text-danger",
        "social" => "text-primary"
    );

    // deal wih no notices
    if (count($params['notices']) <= 0)
    {
        $htm = <<<EOT
        <main class="" >
            <div class="container nav-margin">
                <p class="display-6 text-info mb-6"><b>{event-title}</b></p>
                <div class="alert alert-info fs-3" role="alert">
                    No notices have been published yet!
                </div>
            </div>
        </main>
EOT;
    }

    else
    {
        // filter buttons
        if (count($params['notices']) <= 5)
        {
            $filter_buttons = <<<EOT
        <div class="row">
            <div class="col">
                <div class="mb-2 mx-5 d-inline-flex gap-4">
                    <a href="rm_event.php?page=notices&eid={$params['eventid']}" class="btn btn-outline-info" aria-expanded="false" style="width:150px"> <b>Refresh</b></a>
                </div>
            </div>
            <div class="col">
                <div class="lead text-primary"> <b>{num-notices} {latest-notice-date}</b></div>
            </div>
        </div>
EOT;
        }
        else
        {
            $filter_buttons = <<<EOT
            <div class="row">
                <div class="col">
                    <div class="mb-2 mx-5 d-inline-flex gap-4">                    
                        <a href="rm_event.php?page=notices&eid={$params['eventid']}&mode=competitor" class="btn btn-outline-info" aria-expanded="false" style="width:150px"> competitors </a>
                        <a href="rm_event.php?page=notices&eid={$params['eventid']}&mode=protest" class="btn btn-outline-info" aria-expanded="false" style="width:150px"> protests </a>
                        <a href="rm_event.php?page=notices&eid={$params['eventid']}&mode=social" class="btn btn-outline-info" aria-expanded="false" style="width:150px"> social </a>
                    </div>
                </div>
                <div class="col">
                    <div class="lead text-primary"> <b>{num-notices} {latest-notice-date}</b></div>
                </div>
            </div>
EOT;
        }

        // table header
        $table_hdr = <<<EOT
        <thead class="lead text-center">
            <tr >
                <th scope="col">#</th>
                <th scope="col">Released</th>
                <th scope="col">Type</th>
                <th scope="col">Notice</th>
                <th scope="col">From</th>
                <th scope="col" style="min-width: 250px">Info</th>
            </tr>
        </thead>
EOT;

        // notice data
        $table_rows = "";
        foreach ($params['notices'] as $notice)
        {
            $release = date("d-M-Y H:i", strtotime($notice['createdate']));
            $notice_type = $notice['category'];
            if (empty($notice['moreinfo']))
            {
                $more_info = " - ";
            }
            else
            {
                empty($notice['moreinfo-label']) ? $more_info = "More Information" : $more_info = $notice['moreinfo-label'];

                $more_info = <<<EOT
            <a href="{$notice['moreinfo']}" class="btn btn-sm btn-outline-secondary icon-link fs-6" style="min-width: 200px" target="_BLANK">
                    <i class="bi-link" style="font-size: 2rem; color: cornflowerblue;"></i> $more_info &hellip;
            </a>
EOT;
            }

            $table_rows.= <<<EOT
            <tr>
                <td>{$notice['id']}</td>
                <td>$release</td>
                <td><span class="{$title_color[$notice_type]}"><i>{$notice['category']}</i></span></td>
                <td><span class="{$title_color[$notice_type]}"><b>{$notice['title']}</b></span><br>{$notice['leadtxt']}<br><span class="text-black">{$notice['txt']}</span></td></td>
                <td>{$notice['publisher']}</td>
                <td>$more_info</td>
            </tr>
EOT;
        }

        $params['layout'] == "wide" ? $container = "container-fluid" : $container = "container";
        $htm = <<<EOT
        <main class="" >
            <div class="$container nav-margin">
                <p class="display-6 text-info mb-0"><b>{event-title}</b></p>
                <p class="lead mt-2">{notices-intro}</p>
                
                <div class="container pt-3">
                $filter_buttons            
                <div class="pt-3">
                    
                        <table class="table table-light table-striped">
                            <caption>List of notices</caption>
                            $table_hdr
                            <tbody class="table-group-divider">
                                $table_rows
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>    
        </main>
EOT;
    }

    return $htm;
}


function error_body ($params = array())
{
    empty($params['event-title']) ? $title = "<br><br>": $title = "<p class='display-5 text-info mb-lg-5'><b>{event-title}</b></p>";
    empty($params['action']) ? $action = "" : $action = "<br><br>You could try ... ".$params['action'];

    $error_display = <<<EOT
    <div class="p-5 mb-4 text-bg-secondary border rounded-3">
      <div class="container-fluid py-5">
        <h1 class="display-6 fw-bold">Oops ... we have a problem</h1>
        <p class="col-md-10 fs-5">Something has gone wrong. {problem} $action</p>        
        <p class="col-md-10 fs-5">You can report this problem to our System Administrator using the button below.</p>
        <a class="btn btn-danger btn-lg" href="{report-link}" type="button" target="_BLANK">report problem</a>
        <p class="text-end">[ debug detail: {location} {evidence} ]</p>
      </div>
    </div>
EOT;

    $htm = <<<EOT
    <main class="" >
        <div class="container nav-margin">
            $title           
            $error_display           
        </div>    
    </main>
EOT;

    return $htm;
}

function entry_confirm_block($params = array())
{
    $consent_link = "";
    $process = $params['process'];
    $entry = $params['entry'];

    if ($process['junior'])
    {
        $consent_link = <<<EOT
        <div class="alert alert-warning fs-6" style="display:inline-block;">
            One or more of the boat's crew are under 18 - please make sure that the <b>parental consent form</b> is completed. 
            <br>Link to the consent form for your boat is in the table below.         
        </div>
EOT;
    }

    $entry_details = "";
    if (!empty($entry))
    {
        empty($entry['c-name']) ? $team = $entry['h-name'] : $team = $entry['h-name']." / ".$entry['c-name'];
        empty($entry['h-club']) ? $club = "" : $club = " - ".$entry['h-club'];

        $entry_details.= <<<EOT
        <p class="fs-4">{$entry['b-class']}  {$entry['b-sailno']} : $team &nbsp;&nbsp; $club</p>
EOT;
    }

    $htm = "";
    if ($process['status'] == "success")
    {
        if ($process['waiting'] == 1)                        // if on waiting list don't display consent link
        {
            $htm = <<<EOT
            <div class="alert alert-warning" role="alert">
                <h3>Entry accepted on <b>waiting list</b></h3>
                <p class="fs-5">
                    You are number <b>{$params['waiting']}</b> on the waiting list.  
                    We will contact you by email if a space becomes available
                </p>
            </div>
EOT;
        }
        else                                                  // display with consent link
        {
            $htm = <<<EOT
            <div class="alert alert-success" role="alert">
                <h2>Entry Accepted</h2>
                $entry_details
                $consent_link
            </div>
EOT;
        }
    }
    elseif ($process['status'] == "fail")
    {
        $htm = <<<EOT
        <div class="alert alert-danger" role="alert">
            <h3>Apologies - your entry has FAILED for some reason</h3>
            <p class="fs-5">Please use the CONTACT button to send your boat/crew details to the event contact</p>
        </div>
EOT;
    }

    return $htm;
}

function fatal_error_body($params = array())
{
    if ($params['contact-link'])
    {
        $contact_htm = <<<EOT
        <p>Please report the problem to our event cordinator.</p>
        <a class="btn btn-primary btn-lg" href = "{$params['contact-link']}" type="button">Report Problem &hellip;</a>
EOT;
    }

    $html = <<<EOT
    <header>
        <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand text-info" href="rm_event.php?page=list">raceManager EVENTS</a>
                <ul class="nav navbar-nav text-info fs-4 navbar-right"><li>SYSTEM ERROR</li></ul>
            </div>
        </nav>
    </header>
    <main class="container text-center nav-margin" >
    <div class="row">
        <div class="col">
            <div class="p-5 text-bg-dark border rounded-3">
                <p class="fs-3 text-warning fw-bold">Oops sorry&nbsp;.&nbsp;.&nbsp;. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;we have an unexpected error</p>
                <p class="fs-4">{error}</p>
                $contact_htm
                <hr>
                <p class="text-info text-end">Error Details --- script: {script} &nbsp;&nbsp; function: {function} &nbsp;&nbsp; line: {line}</p>
            </div>
        </div>
    </div>
    </main>
EOT;

    return $html;
}
