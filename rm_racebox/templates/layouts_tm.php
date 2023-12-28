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

    if ($params['page']=="start" or $params['page']=="race")
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

            <link   rel="icon"             href="{loc}/common/images/logos/favicon.png">
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
            $('[data-toggle=tooltip]').tooltip({container: 'body'});
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
    //echo "<pre>".print_r($params,true)."</pre>";
    
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
     *      num_reminders no. of remiders for this event
     *      current_view: current view on timer page (str: tabbed | list ) - only required on timer page
     */
//    $refresh_htm = "";
//    if ($params['page'] == "race")
//    {
//        $refresh_htm = <<<EOT
//        <script>
//        document.addEventListener("visibilitychange", function() {
//           if (!document.hidden){
//                location.reload();
//           }
//        });
//        </script>
//EOT;
//    }


    $options = array(
        "1" => array("name" => "race",      "label" => "status",    "target" => "race_pg.php"),
        "2" => array("name" => "entries",   "label" => "entries",   "target" => "entries_pg.php"),
        "3" => array("name" => "start",     "label" => "start",     "target" => "start_pg.php"),
        "4" => array("name" => "timer",     "label" => "timer",     "target" => "timer_pg.php"),
        "5" => array("name" => "pursuit",   "label" => "pursuit",   "target" => "pursuit_pg.php"),    // fixme - remove if we don't have a separate pursuit page
        "6" => array("name" => "results",   "label" => "results",   "target" => "results_pg.php"),
    );

    // build up options bufr
    $option_bufr = "";

    if ($params['page'] != "pickrace")
    {
        $brand_href = "pickrace_pg.php?eventid={eventid}&page={$params['page']}&menu=true";

        foreach ($options as $k=> $option)
        {
            //if ($option['name'] == "pursuit" and !$params['pursuit'])   // FIXME remove if we don't have a separate pursuit page
            if ($option['name'] == "pursuit")
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
            <span class="rm-navmenu-right rm-navmenu-icon text-muted"><small>CHANGE VIEW</small>
                <!--span class="glyphicon glyphicon-transfer" aria-hidden="true" ></span -->
            </span>
        </a>
EOT;
    }

    $help_bufr = "";
    if ($params['page'] != "help")
    {
        $help_bufr.= <<<EOT
            <a href="help_pg.php?eventid={eventid}&page={$params['page']}" title="HELP!">
                <span class="rm-navmenu-right rm-navmenu-icon text-muted"><small>HELP</small></span>
            </a>
EOT;

    }

    // setup club local links menu
    $club_bufr = "";

    if (!empty($params['links']))
    {
        $club_links = "";
        if ($params['page'] != "pickrace" and $params['num_reminders'] > 0 )  // if not pickrace and we have some reminders - include access to today's reminders
        {
            $club_links.= <<<EOT
            <li ><a href="reminder_pg.php?eventid={eventid}&source={$params['page']}">Todays Reminders</a></li>
EOT;
        }

        foreach ($params['links'] as $link)
        {
            $club_links.= <<<EOT
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
                <li >&nbsp;<b>{club} links</b></li>
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
                    <li>$view_bufr</li>                    
                    <li>$help_bufr</li>
                    <li class="dropdown">$club_bufr</li>                   
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
        <p class="well well-sm text-info">Use this form to let the raceManager support team know about any issues you had ... <i>(plaudits also welcome)</i> </p>

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


function readonly_laps($params = array())
{
    $htm = <<<EOT
        <div class="form-group">
            <div><label class="col-sm-offset-2 col-sm-3 control-label" style="text-align: left;">{fleetname} </label></div>
            <div class="col-sm-2">                
                <input type="hidden" id="laps{fleetnum}" name="laps[{fleetnum}]" value="{fleetlaps}">
                <span style="font-weight: 700; padding-top: 22px;">{fleetlaps} lap(s)</span>               
            </div>
            <div class="col-sm-5 text-warning"><span style="font-weight: 700; padding-top: 22px;">{reason}</span></div>
                
        </div>
EOT;
    return $htm;
}



function not_change_laps($params = array())
{
    // implements a readonly view of the laps when they can't be changed

    $htm = <<<EOT
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-3 control-label" style="text-align: left; margin-bottom: 11px">
                <label>{fleetname}</label>
            </div>
            <div class="col-sm-2 control-label" style="text-align: left; margin-bottom: 11px">
                <label>{fleetlaps} lap(s)</label>    
                <input type="hidden" id="laps{fleetnum}" name="laps[{fleetnum}]" value="{fleetlaps}">           
            </div>
            <div class="col-sm-5 text-info control-label" style="text-align: left; margin-bottom: 11px">
                 <label>{reason}</label>
            </div>                          
        </div>
EOT;
    return $htm;
}

function ok_change_laps($params = array())
{
    $min_validation = "";
    $max_validation = "";

    if (array_key_exists("minvallaps", $params))
    {
        $min_validation = "min='{$params['minvallaps']['val']}' data-fv-greaterthan-message='{$params['minvallaps']['msg']}'";
    }

    if (array_key_exists("maxvallaps", $params))
    {
        $max_validation = "max='{$params['maxvallaps']['val']}' data-fv-lessthan-message='{$params['maxvallaps']['msg']}'";
    }

//    echo "<pre>|$min_validation|$max_validation|</pre>";
//    exit();

    $htm = <<<EOT
        <div class="form-group">
            <label class="col-sm-offset-2 col-sm-3 control-label" style="text-align: left;">{fleetname} </label>
            <div class="col-sm-2 inputfieldgroup">
                <input type="number" class="form-control" id="laps[{fleetnum}]" name="laps[{fleetnum}]" value="{fleetlaps}" placeholder="set laps"
                    required data-fv-notempty-message="the no. of laps must be set" 
                    $min_validation $max_validation
                />
            </div> 
            <div class="col-xs-5 control-label" style="text-align: left;"><label> laps </label></div>    
        </div>
EOT;
    return $htm;
}


function fm_set_laps($params = array())
{
    /* general purpose used for changing laps throughout race box application
         -  race_pg  [set laps]
         -  timer_pg [shorten all, reset laps]
         -  results_pg [change finish lap]

        has to deal with four race states
         - fleet not started
         - fleet started finishing
         - fleet all finished ???

        has three sections
          - instructions - which can be shrunk or expanded
          - laps for each fleet - with inline status detail
          - footer

    */
    global $tmpl_o;

    $fields_bufr = "";

    foreach ($params['fleets'] as $i => $fleet) {
        $fields = array(
            "fleetname" => $fleet['fleetname'],
            "fleetnum" => $i,
            "fleetlaps" => $fleet['fleetlaps'],
        );

        if ($fleet['status'] == "notstarted" or $fleet['status'] == "inprogress") {
            if ($params['mode'] == "shortenall") {
                $fields['fleetlaps'] = $fleet['fleetlaps'] + 1;   // change laps shown to be currentlap + 1
                if ($fleet['setlaps'] > $fleet['fleetlaps'] + 1) {
                    $fields['reason'] = 'will be shortened to lap shown ...';
                    $_SESSION['shorten_possible'] = true;
                } else {
                    $fields['reason'] = 'boats on last lap - CANNOT be shortened ...';
                }
                $fields_bufr .= $tmpl_o->get_template("not_change_laps", $fields, $fleet);
            } else {
                $fields_bufr .= $tmpl_o->get_template("ok_change_laps", $fields, $fleet);
            }
        } elseif ($fleet['status'] == "finishing") {
            if ($params['mode'] == "changefinish") {
                $fields_bufr .= $tmpl_o->get_template("ok_change_laps", $fields, $fleet);
            } else {
                $fields['reason'] = "boats finishing - lap change NOT possible ...";
                $fields_bufr .= $tmpl_o->get_template("not_change_laps", $fields, $fleet);
            }
        } elseif ($fleet['status'] == "allfinished") {
            if ($params['mode'] == "changefinish") {
                $fields_bufr .= $tmpl_o->get_template("ok_change_laps", $fields, $fleet);
            } else {
                $fields['reason'] = "all boats finished - lap change NOT possible ...";
                $fields_bufr .= $tmpl_o->get_template("not_change_laps", $fields, $fleet);
            }
        } else   // unknown
        {
            $fields_bufr .= $tmpl_o->get_template("not_change_laps", $fields, $fleet);
            $fields['reason'] = "fleet status unknown - lap change NOT possible";
        }

    }

    $instr_bufr = "";
    if ($params['instruction']) {
        $instr_bufr .= <<<EOT
        <div class="alert well well-sm alert-dismissable text-primary" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span style="padding-right: 20px" aria-hidden="true">&times;</span>
            </button>
            <h4>{instr_content}</h4>                    
        </div>       
EOT;
    }

    $footer_bufr = "";
    if ($params['footer']) {
        $footer_bufr .= <<<EOT
        <div class="alert well well-sm text-info" role="alert">
            <h4>{footer_content}</h4>
        </div>
        <h4>{reminder}</h4>
EOT;
    }

    $htm = <<<EOT
    <div>
        $instr_bufr
        $fields_bufr
        $footer_bufr            
    </div>
EOT;

    return $htm;
}

function fm_change_finish($params = array())
{
    global $tmpl_o;

    $data = array(
        "mode"       => "changefinish",
        "instruction"=> true,
        "footer"     => true
    );

    $fields = array(
        "instr_content" => "<p style='line-height:1.6'>This can be useful in following situations ... :<br>
                        &nbsp;&nbsp;&nbsp;- forgotten to SHORTEN course and boats are showing as 'still racing' ... or<br>
                        &nbsp;&nbsp;&nbsp;- ABANDONED the race and want to take the results from a PREVIOUS completed lap<br>
                        <i>[if you want to just correct and mistaken shortening of the course use the Undo Shorten Course button on the timer page]</i>
                        </p>
    <p class='text-info'>Set the finish lap for each fleet to the lap you want the boats to finish on (i.e. the laps for the finish of the leading boat).</p>",
        "footer_content" => "click the <b>Change Finish Lap</b> button to set the finish lap for each fleet",
        "reminder" => ""
    );

    foreach ($params['fleet-data'] as $i=>$fleet)
    {
        $data['fleets'][$i] = array(
            "fleetname"  => ucwords($fleet['name']),
            "fleetnum"   => $i,
            "fleetlaps"  => $fleet['maxlap'],
            "status"     => $fleet['status']
        );

        if ($fleet['status'] == "notstarted")
        {
            $data['fleets'][$i]['minvallaps'] = array("val"=>1, "msg"=>"cannot be less than 1 lap");;
        }
        else
        {
            $data['fleets'][$i]['minvallaps'] = array("val"=>1, "msg"=>"cannot be less than 1 lap");
            //$data['fleets'][$i]['maxvallaps'] = array("val"=>$fleet['maxlap'], "msg"=>"cannot be more than {$fleet['maxlap']} lap(s)");;
        }
    }

    return $tmpl_o->get_template("fm_set_laps", $fields, $data);

}

function fm_close_ok($param=array())
{
    $html = <<<EOT
    <div class="margin-top-10">
        <h4><b>Congratulations - job done!</b></h4>
        <p>After closing the race you will be returned to the RaceBox dashboard</p>
        <br>
        <p>If you want to send a message to the Results Team about the results or any problems you had please enter the details below</p>

        <!-- message field -->
        <div class="well" style="margin-left: 20px; margin-right: 20px;">
            <div class="form-group" style="margin-left: 5%; margin-right: 5%;">
                <div class="inputfieldgroup">
                    <textarea rows="3" class="form-control" id="message" name="message"
                     placeholder="any problems? ..."></textarea>
                </div>
            </div>
        </div>
    </div>
EOT;
    return $html;
}


function fm_close_notok($param=array())
{
    $html = <<<EOT
        <!-- instructions -->
        <h3 >Sorry - you can't CLOSE this race yet because ...</h3>
        <div style="margin-left: 20px;">
            <h4>{reason}</h4>
            <p>{info}</p>
        </div>
        
        <h4><br><hr style="border-top: 1px solid green">If you are not able to resolve the problems with the results
        - publish them anyway and use the <b>Report Issue</b> button above to describe the problem</h4>
EOT;
    return $html;
}
