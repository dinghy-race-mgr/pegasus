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
        
    
        <!-- Custom styles for this template -->
        <link href="sticky-footer-navbar.css" rel="stylesheet" >
        <link href="./style/rm_event.css" rel="stylesheet">
    </head>
    <body class="d-flex flex-column h-100">
    
        {page-navbar}
        
        <!-- Begin page content -->
        <main class="" >
            <div class="container nav-margin">
                {page-main}
            </div>
        </main>
        
        {page-footer}
        
        {page-modals}
        
        <script src="../common/oss/bootstrap532/js/bootstrap.bundle.min.js"></script>
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
            if ($option['page'] == "documents" or $option['page'] == "entries" or $option['page'] == "notices")
            {
                if ($option['num'] > 0) { $inc_count = "[{$option['num']}]"; }
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
        $htm_contacts.= <<<EOT
        <li><a class="dropdown-item text-info fs-5" data-bs-toggle="modal" data-bs-target="#idcontactmodal" data-bs-email = "{$contact['email']}">
            {$contact['name']} - {$contact['role']}</a>
        </li>
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
        $htm = <<<EOT
        <footer class="footer mt-auto">
            <div class="container py-3">
                <p class="d-inline-flex gap-5">
                    <a class="btn btn-secondary fs-4" href="rm_event.php?page=entries&eid={$params['eid']}" style="width: 300px" >
                        ENTER EVENT
                    </a>
                    <a class="btn btn-secondary fs-4" href="rm_event.php?page=documents&eid={$params['eid']}" style="width: 300px" >
                        DOCUMENTS [2]
                    </a>
                    <a class="btn btn-secondary fs-4" href="rm_event.php?page=notices&eid={$params['eid']}" style="width: 300px" >
                        NOTICES [3]
                    </a>
                </p>
            </div>
            <div class="container py-3 bg-info text-end">
                <span class="text-muted ">raceManager {version} - copyright Elmswood Software {year}</span>
            </div>
        </footer>
EOT;
    }
    else
    {
        $htm = <<<EOT
        <footer class="footer mt-auto">
            <div class="container py-3 bg-info text-end">
                <span class="text-muted ">raceManager {version} - copyright Elmswood Software {year}</span>
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

function detail_body ($params = array())
{

    // assemble collapsible topics layout
    $htm_topics_buttons = "";
    $htm_topics_content = "";
    $i = 0;
    foreach ($params['topics'] as $topic)
    {
        $i++;

        $i % 3 == 1 ? $open_p = "<p class=\"d-inline-flex gap-5 row-gap-5\">" : $open_p = "";
        $i != 1 ? $close_p = "</p" : $close_p = "";

        $htm_topics_buttons.= <<<EOT
        $close_p
        $open_p
        a class="btn btn-info fs-4" style="width: 300px" data-bs-toggle="collapse" href="#collapsetopic$i" 
           role="button" aria-expanded="false" aria-controls="collapsetopic$i">
            {$topic['label']} &hellip;
        </a>       
EOT;
        $htm_topics_content.= <<<EOT
        <div class="collapse" id="collapsetopic$i">
            <div class="card card-body fs-6">
                {$topic['text']}
            </div>
        </div>
EOT;
    }
    $htm_topics_buttons.= "</p>";

    // assemble subtext layout

    if ($params['subtext'] == "type1")                                // single text bloxk
    {
        $htm_subtext = <<<EOT
        <div class="d-flex my-5" style="height: 300px;">
            <div class="vr"></div>
            <div> <p class="lead px-5" >{event-subtext} </div>
        </div>
EOT;
    }
    elseif ($params['subtext'] == "type2")                           // text block and image block
    {
        $htm_subtext = <<<EOT
        <div class="d-flex my-5" style="height: 300px;">
            <div class="vr"></div>
            <p class="lead px-5" >{event-subtext}</p>
            <p class="lead px-5" > <img src="{event_subtext_image}" alt="{event_subtext_image_label}" height="300px"></p>
        </div>
EOT;
    }
    else
    {
        $htm_subtext = "";                                           // type not recognised
    }


/*
   $htm = <<<EOT

        <div>
            <p class="d-inline-flex gap-5 row-gap-5">
                <a class="btn btn-info fs-4" style="width: 300px" data-bs-toggle="collapse" href="#collapseExample1" role="button" aria-expanded="false" aria-controls="collapseExample1">
                    Camping Info &hellip;
                </a>
                <button class="btn btn-info fs-4" style="width: 300px" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample2" aria-expanded="false" aria-controls="collapseExample2">
                    Entry Info &hellip;
                </button>
                <a class="btn btn-info fs-4" style="width: 300px" data-bs-toggle="collapse" href="#collapseExample1" role="button" aria-expanded="false" aria-controls="collapseExample1">
                    Barbecue Info &hellip;
                </a>
            <!-- second line>
            </p><p class="d-inline-flex gap-5 row-gap-5">
                <button class="btn btn-lg btn-info" style="width: 300px" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample2" aria-expanded="false" aria-controls="collapseExample2">
                    Entry Info ...
                </button>
                <a class="btn btn-lg btn-info" style="width: 300px" data-bs-toggle="collapse" href="#collapseExample1" role="button" aria-expanded="false" aria-controls="collapseExample1">
                    Camping Info ...
                </a>
                <button class="btn btn-lg btn-info" style="width: 300px" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample2" aria-expanded="false" aria-controls="collapseExample2">
                    Entry Info ...
                </button>
            </p>
            <end second line -->
        </div>

        <div class="collapse" id="collapseExample1">
            <div class="card card-body fs-6">
                CAMPING <br>
                This tells you everything you wanted to know about camping
                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.

                Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
                This tells you everything you wanted to know about camping
                <br><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
                This tells you everything you wanted to know about camping
                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
                This tells you everything you wanted to know about camping
                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
                This tells you everything you wanted to know about camping
                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
                This tells you everything you wanted to know about camping
                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
                This tells you everything you wanted to know about camping
                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
                This tells you everything you wanted to know about camping
                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.

            </div>
        </div>
        <div class="collapse" id="collapseExample2">
            <div class="card card-body">
                This tells you everything you wanted to know about camping
                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                <ul class="list-unstyled">
                    <li>Nested lists:
                        <ul>
                            <li>are unaffected by this style</li>
                            <li>will still show a bullet</li>
                            <li>and have appropriate left margin</li>
                        </ul>
                    </li>
                    <li>This may still come in handy in some situations.</li>
                </ul>
                Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
            </div>
        </div>

        <!-- option 1  single text block)
        <div class="d-flex my-5" style="height: 300px;">
            <div class="vr"></div>
            <div>
            <p class="lead px-5" >{event-text} More detailed text - Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
            <p class="lead px-5" >{event-text} More detailed text - Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
            </div>
        </div>
        <end of option1 -->

        <!-- option 2  text block + image -->
        <div class="d-flex my-5" style="height: 300px;">
            <div class="vr"></div>
            <p class="lead px-5" ><!-- {event-text} --> More detailed text - Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
            <p class="lead px-5" > <img src="http://localhost/pegasus/testing/pic1.jpg" alt="SYC open" height="300px"></p>
        </div>
        <!-- end of option 2 -->

    </div>
</main>

EOT;
*/

    // assemble complete body htm
    $htm = <<<EOT
    <main class="" >
        <div class="container nav-margin">
            <p class="display-4 text-info mb-0"><b>{event-title}</b></p>
            <p class="display-6 mb-0">{event-date}</p>
            <p class="display-6 mb-0">{event-subtitle}</p>
            <p class="lead mt-2">{event-leadtxt}</p>
            <div>
                $htm_topics_buttons
            </div>  
            <div>
                $htm_topics_content
            </div>
            <div>
                $htm_subtext
            </div>
        </div>
    </main>
EOT;

    return $htm;
}

function entry_body ($params = array())
{
    $htm = <<<EOT

EOT;

    return $htm;
}

function documents_body ($params = array())
{
    $htm = <<<EOT

EOT;

    return $htm;
}

function notices_body ($params = array())
{
    $htm = <<<EOT

EOT;

    return $htm;
}

function results_body ($params = array())
{
    $htm = <<<EOT

EOT;

    return $htm;
}

