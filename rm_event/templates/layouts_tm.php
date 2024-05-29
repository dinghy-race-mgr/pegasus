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
        <link href="../common/oss/bootstrap532/css/bootstrap.min.css" rel="stylesheet">
        <link href="../common/oss/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        
        <!-- Bootstrap core js -->
        <script src="../common/oss/bootstrap532/js/bootstrap.bundle.min.js"></script>      
    
        <!-- Custom styles for this template -->
        <link href="sticky-footer-navbar.css" rel="stylesheet" >
        <link href="./style/rm_event.css" rel="stylesheet">
    </head>
    <body class="d-flex flex-column h-100">
    
        {page-navbar}
        
        <!-- Begin page content -->
        <div class="container nav-margin">
            {page-main}
        </div>
        
        {page-footer}
        
        {page-modals}
               
        {page-js}
    
    </body>
    </html>

EOT;

    return $htm;
}

function navbar ($params = array())
{
    // setup page link options
    $htm_options = "";
    if ($params['page'] != "list")
    {
        foreach ($params['options'] as $option)
        {
            $inc_count = "";
            if ($option['page'] == "documents" or $option['page'] == "entries" or
                $option['page'] == "notices" or $option['page'] == "results")
            {
                $params['counts'][$option['page']] == 0 ? $inc_count = "" : $inc_count = "({$params['counts'][$option['page']]})";
            }

            if ($params['active'] == $option['page'])
            {
                $htm_options.=<<<EOT
                <li class="nav-item px-4 lead active active-option">
                    <a class="nav-link text-black fw-bold" href="{$option['script']}{$params['eid']}">{$option['label']}&nbsp;$inc_count</a>
                </li>
EOT;
            }
            else
            {
                $htm_options.=<<<EOT
                <li class="nav-item px-4 lead">
                    <a class="nav-link" href="{$option['script']}{$params['eid']}">{$option['label']}&nbsp;$inc_count</a>
                </li>
EOT;
            }
        }
    }

    // setup contact drop down
    $htm_contacts = "";
    foreach ($params['contact'] as $contact)
    {
        // FIXME - use a modal when we have out own email service
        $htm_contacts.= <<<EOT
        <li><a class="dropdown-item text-info fs-5" href="{$contact['link']}" target="_BLANK">{$contact['name']} - {$contact['role']}</a></li>
EOT;
    }

    // setup previous years drop down for list page
    $current_year = date("Y");
    $htm_year_select = "";
    for ($i = $current_year - 1; $i >= $params['start-year']; $i--)
    {
        $htm_year_select.= <<<EOT
            <li><a class="dropdown-item text-info fs-5" href="#">$i</a></li>
EOT;
    }

    $htm_years = "";
    if ($params['page'] == "list" and !empty($htm_year_select))
    {
        $htm_years = <<<EOT
        <div class="navbar-text dropdown-center" style="min-width: 300px;">
            <a class="btn btn-info dropdown-center btn-lg dropdown-toggle " href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Previous Years
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
                <ul class="navbar-nav me-auto mb-4 mb-md-0 flex-nowrap" >
                    $htm_options
                </ul>
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
<br><br>
        <footer class="footer mt-auto"> 
            <div class="container px-4 py-3 text-bg-secondary d-flex align-items-baseline">
                <div class="row  gap-5"
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
                        <span class="align-top">raceManager {version} - copyright Elmswood Software {year}</span>
                    </div>                   
                </div>
            </div>    
        </footer>
EOT;
    }
    else
    {
        $htm = <<<EOT
        <footer class="footer mt-auto">
            <div class="container py-2 text-bg-secondary text-end">
                raceManager {version} - copyright Elmswood Software {year}
            </div>
        </footer>
EOT;
    }

    return $htm;
}

function list_body ($params = array())
{
    $lead_txt = "";
    $year = date("Y");
    if ($params['year'] == $year)
    {
        $lead_txt = <<<EOT
        <p class="lead"> {list-lead-txt} &hellip;</p>
EOT;
    }

    $htm = <<<EOT
    <br>
    <p class="display-5 text-info"><b>$year Events Schedule</b></p>
    $lead_txt   
    {event-panels}
EOT;

    return $htm;
}

function list_event_panel($params = array())
{
    // create link depending on status
    if ($params['event-status'] == "complete")
    {
        $link = <<<EOT
        <div class="position-absolute bottom-0 end-0">
            <a class="icon-link fs-4 " href="rm_event.php?page=results&eid={$params['eid']}" style="text-decoration: none;">
                Get Results <i class="bi-arrow-right-square-fill" style="font-size: 2rem; color: cornflowerblue;"></i>
            </a>                                 
        </div>
EOT;
    }
    elseif ($params['event-status'] == "open")
    {
        $link = <<<EOT
        <div class="position-absolute bottom-0 end-0">
            <a class="icon-link fs-4 " href="rm_event.php?page=details&eid={$params['eid']}" style="text-decoration: none;">
                Event Details <i class="bi-arrow-right-square-fill" style="font-size: 2rem; color: cornflowerblue;"></i>
            </a>                                 
        </div>
EOT;
    }
    else
    {
        $link = "";
    }

    // create event title
    $title_txt = "{event-dates} - <b>{event-title}</b><br>";
    if ($params['sub-title']) { $title_txt.= "<p class=\"fs-5\"><b>{sub-title}</b></p>" ;}
    if ($params['list-status-txt']) { $title_txt.= "<span class=\"text-danger-emphasis fs-5\"><i>{list-status-txt}</i></span>";}

    $htm = <<<EOT
    <div class="alert alert-{event-style}" role="alert">
        <div class="row">
            <div class="col-md-9 fs-4">
                $title_txt
            </div>
            <div class="col-md-3 position-relative">    
                $link
            </div>
        </div>
    </div>
EOT;

    return $htm;
}

function details_body ($params = array())
{

    // assemble lead text layout
    $htm_leadtext = "";
    if (array_key_exists("event-leadtext", $params['content']))
    {
        $htm_leadtext = render_content($params['content']['event-leadtext'], "right");
    }

    // assemble subtext layout
    $htm_subtext = render_content($params['content']['event-subtext'], "right");

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
            {$topic['label']} &hellip;
        </a>       
EOT;

        $htm_topic = render_content($topic,"bottom");
        $htm_topics_content.= <<<EOT
        <div class="collapse" id="collapsetopic$i">
            <div class="card card-body fs-6" style="background-color: lightyellow">
                <p class="lead"><b>{$topic['label']} &hellip;</b> </p>
                $htm_topic
            </div>
        </div>
EOT;
    }

    // assemble complete body htm
    $htm = <<<EOT
    <main class="" >
        <div class="container nav-margin">
            <p class="display-6 text-info mb-0"><b>{event-title}</b></p>
            <p class="fs-3 mb-0">{event-dates} | {event-subtitle}</p>
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
    empty($params['entry-end']) ? $entry_end = date("d M y", strtotime($params['entry-end'])): $entry_end = "";
    $entry_count_detail = <<<EOT
        <p class="lead">{entry-count} entries so far.  $entry_end</p>
EOT;

    $new_entry_button = <<<EOT
    <a class="btn btn-large btn-success" href="rm_event.php?page=newentry&eid={$params['eventid']}" role="button">
        <span class="fs-4">Enter a Boat &hellip;</span>
    </a>
EOT;

    // set this according to no entry/entry confirmed/entry failed

    if ($params['newentry']['status'] == "noentry")
    {
        $entry_confirmation = "";
    }
    elseif ($params['newentry']['status'] == "success")
    {
        $entry_confirmation = <<<EOT
        <div class="alert alert-success fs-3" role="alert">
            <b>Good God</b> - you are entered 
        </div>
EOT;
    }
    elseif ($params['newentry']['status'] == "failed")
    {
        $entry_confirmation = <<<EOT
        <div class="alert alert-danger fs-3" role="alert">
            <b>Holy Guacamole</b> - your entry failed
        </div>
EOT;
    }
    else
    {
        $entry_confirmation = "";
    }

    $table_hdr = <<<EOT
    <tr>
        <td>Class</td>
        <td>Sail Number</td>
        <td>Team</td>
        <td>Club</td>
        <td>Division</td>
    </tr>
EOT;

    $table_rows = "";
    $current_fleet = "";
    $i = 0;
    foreach ($params['entries'] as $entry)
    {
        if ($i == 1 or $current_fleet != $entry['b-fleet'])
        {
            // add fleet divider
            $table_rows.=<<<EOT
            <tr><td colspan="5" class="table-success">fleet</td></tr>
EOT;
        }

        empty($entry['c-name']) ? $people = $entry['h-name'] : $people = $entry['h-name']."<br>".$entry['c-name'];
        empty($entry['c-club']) ? $clubs = $entry['h-club'] : $clubs = $entry['h-club']."<br>".$entry['c-club'];

        $table_rows.= <<<EOT
        <tr>
            <td>{$entry['b-class']}</td>
            <td>{$entry['b-sailno']}</td>
            <td>$people</td>
            <td>$clubs</td>
            <td>{$entry['b-division']}</td>
        </tr>
EOT;
        $current_fleet = $entry['b-fleet'];
    }

    $htm = <<<EOT
        <main class="" >
            <div class="container nav-margin">
                <p class="display-6 text-info mb-0"><b>{event-title}</b></p>
                <p class="lead mt-2">{entries-intro}</p>
                
                <div class="alert alert-info fs-3" role="alert">
                    <div class="row">
                        <div class="col-6 fs-3">$entry_count_detail</div>
                        <div class="col-6 text-end">$new_entry_button</div>
                    </div> 
                </div>
                
                $entry_confirmation
                         
                <div class="pt-3">                   
                        <table class="table table-light table-striped">
                            <caption>List of entries</caption>
                            <thead>
                                $table_hdr
                            </thead>
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

function newentry_body ($params = array())
{
    // include specific form for this event - returns instructions, form and validation html/js
    require_once("include/{$params['form-name']}_fm.php");

    // standard entry form layout
    $htm = <<<EOT
        <main class="" >
            <div class="container nav-margin">
                <p class="display-6 text-info mb-0"><b>{event-title}</b></p>
                <p class="lead mt-2">{newentry-intro}</p>            
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
    $today = date("Y-m-d");
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
            $release = date("d-M-Y H:i", strtotime($document['createdate']));

            if ($params['mode'] == "results")
            {
                $version = $document['status'];
            }
            else
            {
                !empty($document['version']) ? $version = "version " . $document['version'] : $version = "";
            }

            if (empty($document['filename']))
            {
                $download = " will be available shortly ";
            }
            else
            {
                $icon = $format_icon[$document['format']];
                $document['format'] == "htm" ? $label = "View" : $label = "Download";
                $download = <<<EOT
            <a href="{$document['filename']}" class="btn btn-sm btn-outline-secondary icon-link fs-6" style="min-width: 200px" target="_BLANK">
                    <i class="$icon" style="font-size: 2rem; color: cornflowerblue;"></i> $label
            </a>
EOT;
            }

            $table_rows.= <<<EOT
            <tr>
                <td style="width: 60%"><span class="text-danger fs-3">{$document['title']}</span><br>{$document['description']}</td>
                <td style="width: 20%">$release<br>$version</td></td>
                <td style="width: 20%">$download</td>
            </tr>
EOT;
        }
    }

    $htm = <<<EOT
    <main class="" >
        <div class="container nav-margin">
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
    if ($params['record-type'] == "entries")
    {
        $clause = "received yet!";
        $new_entry_button = <<<EOT
    <a class="btn btn-large btn-success" href="rm_event.php?page=newentry&eid={$params['eventid']}" role="button">
        <span class="fs-4">Enter a Boat &hellip;</span>
    </a>
EOT;
    }
    else
    {
        $clause = "have been published yet!";
        $new_entry_button = "";
    }

    $htm = <<<EOT
        <main class="" >
            <div class="container nav-margin">
                <p class="display-6 text-info mb-6"><b>{event-title}</b></p>
                <div class="alert alert-info fs-3" role="alert">
                    <div class="row">
                        <div class="col-6 fs-3">No {record-type} $clause</div>
                        <div class="col-6 text-end">$new_entry_button</div>
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
                <td><span class="{$title_color[$notice_type]}"><b>{$notice['title']}</b></span><br>{$notice['leadtxt']}</td></td>
                <td>{$notice['publisher']}</td>
                <td>$more_info</td>
            </tr>
EOT;
        }

        $htm = <<<EOT
        <main class="" >
            <div class="container nav-margin">
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

function results_body ($params = array())
{
    $htm = <<<EOT

EOT;

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
