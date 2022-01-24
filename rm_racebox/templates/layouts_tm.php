<?php
/**
 * layouts_tm.php
 *
 * @abstract Page layout templates for the racebox application
 *
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 *
 * %%copyright%%
 * %%license%%
 *
 * templates:
 *     two_col_page
 *     basic_page
 *     support_team
 */

function two_col_page($params = array())
{
    $refresh = "";
    if (isset($params['refresh']) and $params['refresh'] > 0) {
        $refresh = "<meta http-equiv=\"refresh\" content=\"{$params['refresh']}\">";
    }

    isset($params["l_width"]) ? $l_width = $params["l_width"] : $l_width = 10;         // set column widths
    $r_width = 12 - $l_width;

    $form_links = "";
    if ($params['forms']) {
        $form_links = <<<EOT
        <link rel="stylesheet" href="{loc}/common/oss/bs-validator/dist/css/formValidation.min.css">
        <script type="text/javascript" src="{loc}/common/oss/bs-validator/dist/js/formValidation.min.js"></script>
        <script type="text/javascript" src="{loc}/common/oss/bs-validator/dist/js/framework/bootstrap.min.js"></script>
        <script type="text/javascript" src="{loc}/common/oss/bs-validator/dist/js/addons/mandatoryIcon.js"></script>
EOT;
    }
    $table_links = "";
    if ($params['tables']) {
        $table_links = <<<EOT
        <link rel="stylesheet" href="{loc}/common/oss/bs-jasny/css/jasny-bootstrap.min.css">
        <script type="text/javascript" src="{loc}/common/oss/bs-jasny/js/jasny-bootstrap.min.js"></script>
EOT;
    }

    $tab_context = "";
    if ($params['page'] == "entries" OR $params['page'] == "timer" OR $params['page'] == "results") {
        $tab_context = <<<EOT
        <!-- keeps panel (fleet) context on relevant pages -->
        <script type="text/javascript">
            $(function() {
              $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
                localStorage.setItem('lastTab', $(this).attr('href'));
              });

              var lastTab = localStorage.getItem('lastTab');

              if (lastTab) {
                  $('[href="'+lastTab+'"]').tab('show');
              }
              else {
                  $('[href="#fleet1"]').tab('show');
              }
            });
        </script>
EOT;
    }

    if ($params['page']=="start")
    {
        $countdown = <<<EOT
        <script type="text/javascript" src="{loc}/common/scripts/jquery.countdown.js"></script>
EOT;
    }
    else
    {
        $countdown = "";
    }

    $html = <<<EOT
    <!DOCTYPE html><html lang="en">
    <head>
            <title>{title}</title>
            $refresh
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="description" content="">
            <meta name="author" content="">

            <link   rel="shortcut icon"    href="{loc}/common/images/favicon.ico">
            <link   rel="stylesheet"       href="{loc}/common/oss/bootstrap341/css/{theme}bootstrap.min.css" >
            <script type="text/javascript" src="{loc}/common/oss/jquery/jquery.min.js"></script>
            <script type="text/javascript" src="{loc}/common/oss/bootstrap341/js/bootstrap.min.js"></script>
            <script type="text/javascript" src="{loc}/common/oss/bs-growl/jquery.bootstrap-growl.min.js"></script>
            <script type="text/javascript" src="{loc}/common/scripts/clock.js"></script>

            $form_links
            $table_links

            <!-- Custom styles for this template -->
            <link href="{stylesheet}" rel="stylesheet">

    </head>
    <body class="{body_attr}">
        $countdown
        {navbar}
        <div class="container-fluid" role="main">
            <div class="row">
                <div id="lhcol" class="col-md-$l_width col-sm-$l_width col-xs-$l_width" style="padding-right: 5%">
                    {l_top}
                    {l_mid}
                    {l_bot}
                </div>
                <div id="rhcol" class="col-md-$r_width col-sm-$r_width col-xs-$r_width">
                    <div class="margin-top-20">
                        {r_top}
                        {r_mid}
                        {r_bot}
                    </div>
                </div>
            </div>
        </div>
        {footer}

        <!-- popover activation for all popovers -->
        <script type="text/javascript">
            $(document).ready(function() {
            $("[data-toggle=popover]").popover({trigger: 'hover',html: 'true'});
            });
        </script>

        <!-- tooltip activation for all tooltips -->
        <script type="text/javascript">
            $(document).ready(function() {
            $("[data-toggle=tooltip]").tooltip({trigger: 'hover',html: 'true'});
            });
        </script>
        $tab_context
    </body>
    </html>
EOT;
    return $html;
}


function basic_page($params = array())
{
    if (isset($params['form_validation'])) {
        $formval_hdr = <<<EOT
        <link rel="stylesheet" href="{loc}/common/oss/bs-validator/dist/css/formValidation.min.css">
        <script type="text/javascript" src="{loc}/common/oss/bs-validator/dist/js/formValidation.min.js"></script>
        <script type="text/javascript" src="{loc}/common/oss/bs-validator/dist/js/framework/bootstrap.min.js"></script>
        <script type="text/javascript" src="{loc}/common/oss/bs-validator/dist/js/addons/mandatoryIcon.js"></script>
EOT;

        $formval_ftr = <<<EOT
        <script>
            $(document).ready(function() {
                $('#{id}Form').formValidation({
                    excluded: [':disabled'],
                })
                $('#resetBtn').click(function() {
                 $('#{id}Form').data('bootstrapValidator').resetForm(true);
                });
            });
        </script>
EOT;
    }
    else
    {
        $formval_hdr = "";
        $formval_ftr = "";
    }
    $html = <<<EOT
    <!DOCTYPE html><html lang="en">
    <head>
            <title>{title}</title>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="description" content="">
            <meta name="author" content="">

            <link   rel="shortcut icon"    href="{loc}/common/images/favicon.ico">
            <link   rel="stylesheet"       href="{loc}/common/oss/bootstrap341/css/{theme}bootstrap.min.css" >
            <script type="text/javascript" src="{loc}/common/oss/jquery/jquery.min.js"></script>
            <script type="text/javascript" src="{loc}/common/oss/bootstrap341/js/bootstrap.min.js"></script>
            <script type="text/javascript" src="{loc}/common/oss/bs-growl/jquery.bootstrap-growl.min.js"></script>

            $formval_hdr

            <!-- Custom styles for this template -->
            <link href="{stylesheet}" rel="stylesheet">

    </head>
    <body class="{body_attr}">
        <script type="text/javascript" src="../common/oss/countuptimer/dist/jquery.countdown.js"></script>
        {navbar}
        <div class="container-fluid" role="main">
            {body}
            $formval_ftr
        </div>
        {footer}



        <!-- popover activation for all popovers -->
        <script type="text/javascript">
            $(document).ready(function() {
            $("[data-toggle=popover]").popover({trigger: 'hover',html: 'true'});
            });
        </script>

        <!-- tooltip activation for all tooltips -->
        <script type="text/javascript">
            $(document).ready(function() {
            $("[data-toggle=tooltip]").tooltip({trigger: 'hover',html: 'true'});
            });
        </script>
    </body>
    </html>
EOT;
    return $html;

}

function racebox_navbar($params=array())
{
    /*
     *   fields:
     *      eventid:      id for event (int) - not required on pickrace page
     *      brand:        label on left of navbar (str)
     *      club:         club acronym (e.g. SYC) (str)
     *
     *   params:
     *      page:         page name (str)
     *      pursuit:      flag if pursuit race (int:  1 | 0 )
     *      baseurl:      url to racemanager startup page (str) - only required on pickrace page
     *      links:        local links for custom menu (array) - can be empty array
     *      current_view: current view on timer page (str: tabbed | list ) - only required on timer page
     */

    $options = array(
        "1" => array("name" => "race",      "label" => "status",    "target" => "race_pg.php"),
        "2" => array("name" => "entries",   "label" => "entries",   "target" => "entries_pg.php"),
        "3" => array("name" => "start",     "label" => "start",     "target" => "start_pg.php"),
        "4" => array("name" => "timer",     "label" => "timer",     "target" => "timer_pg.php"),
        "5" => array("name" => "pursuit",   "label" => "pursuit",   "target" => "pursuit_pg.php"),
        "6" => array("name" => "results",   "label" => "results",   "target" => "results_pg.php"),
    );

    // build up options bufr
    $option_bufr = "";

    if ($params['page'] != "pickrace")
    {
        $brand_href = "pickrace_pg.php?eventid={eventid}&page={$params['page']}&menu=true";

        foreach ($options as $k=> $option)
        {
            if ($option['name'] == "pursuit" and !$params['pursuit'])   // FIXME ned
            {
                continue;
            }

            // set class for state of option (active or not)
            $params['page'] == $option['name'] ? $state = "rm-navmenu-active" : $state = "rm-navmenu" ;

            $option_bufr.= <<<EOT
            <li>
                <a href="{$option['target']}?eventid={eventid}&menu=true" >
                  <span class="$state" >{$option['label']}</span>
                </a>
            </li>
EOT;
        }
    }
    else
    {
        $brand_href = $params['baseurl'];
    }

    // change view on timer page
    $view_bufr = "";
    if ($params['page'] == "timer")
    {
        $params["current_view"] == "tabbed" ? $display_mode = "list" : $display_mode = "tabbed";
        $view_bufr = <<<EOT
        <a href="timer_pg.php?eventid={eventid}&mode=$display_mode" title="switch to $display_mode view" role="button" >
            <span class="rm-navmenu-right rm-navmenu-icon text-muted">
                <span class="glyphicon glyphicon-transfer" aria-hidden="true" ></span>
            </span>
        </a>
EOT;
    }

    // setup club local links menu
    $club_bufr = "";
    if (!empty($params['links']))
    {
        $club_links = "";
        foreach ($params['links'] as $link)
        {
            $club_links .= <<<EOT
            <li ><a href="{$link['url']}" target="_blank">{$link['label']}</a></li>
EOT;
        }

        $club_bufr.=<<<EOT
        <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" title="{club} Info">
                <span class="rm-navmenu-right rm-navmenu-icon text-muted"> 
                    <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                    <span class="caret"></span>
                </span>
            </a>
            <ul class="dropdown-menu" role="menu">
                <li ><b>{club} links</b></li>
                <li class="divider"></li>
                $club_links
            </ul>
        </li>
EOT;

    }

    $html = <<<EOT
    <nav class="navbar navbar-default navbar-fixed-top">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand rm-brand-title" href="$brand_href" target="_parent">
                    <span class="rm-navmenu-icon text-success" style="padding-right: 40px">{brand}</span>
                </a>
            </div>
        
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">$option_bufr</ul>
                
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        $view_bufr
                    </li>
                    
                    <li>
                        <a href="help_pg.php?eventid={eventid}&page={$params['page']}" title="HELP!">
                            <span class="rm-navmenu-right rm-navmenu-icon text-muted">
                                <span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>
                            </span>
                        </a>
                    </li>
                    $club_bufr
                </ul>
            </div>
        </div>
    </nav>
EOT;
    return $html;
}


function support_team($params = array())
{
    $html = <<<EOT
    <a class="btn btn-default" role="button" data-content="Click here for local help" data-toggle="popover"
        data-placement="bottom" href="{link}" data-original-title="<b>Support Team</b>">
        <h4>
            <span class="glyphicon glyphicon-user text-primary" style="font-size: 2em;"></span>
            <span class="glyphicon glyphicon-user text-danger"  style="font-size: 2em;"></span>
        </h4>
        Support Team
    </a>
EOT;
    return $html;
}

function fm_race_message($params=array())
{
    $labelwidth = "col-xs-3";
    $fieldwidth = "col-xs-7";

    $html = <<<EOT
        <!-- instructions -->
        <p class="well well-sm text-info">Use this form to let the raceManager support team know about any problems you had </p>

        <!-- field #1 - name -->
        <div class="form-group">
            <label class="$labelwidth control-label">Your Name</label>
            <div class="$fieldwidth inputfieldgroup">
                <input type="text" class="form-control" id="msgname" name="msgname" value=""
                placeholder="your name ..."
                required data-fv-notempty-message="please add your name here" />
            </div>
        </div>

        <!-- field #2 - email address -->
        <div class="form-group">
            <label class="$labelwidth control-label">Your Email</label>
            <div class="$fieldwidth inputfieldgroup">
                <input type="email" class="form-control" id="email" name="email" value=""
                    placeholder="your email if you would like a reply ..."
                    data-fv-emailaddress-message="This does not look like a valid email address" />
            </div>
        </div>

        <!-- field #3 - message -->
        <div class="form-group">
            <label class="$labelwidth control-label">Message</label>
            <div class="$fieldwidth inputfieldgroup">
                <textarea rows=4 class="form-control" id="message" name="message" value=""
                    placeholder="description of issue ..."
                    required data-fv-notempty-message="please describe your issue or problem here"></textarea>
            </div>
        </div>
EOT;
    return $html;
}
