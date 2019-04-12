<?php
/*
 *
 *
 *
 */


function racebox_navbar($params=array())
    /*
     * fields
     *    eventid
     *    brand
     *    clubcode
     *    website
     *    page
     *    scoring
     */
{

    $state = array (
        "dashboard"     =>"rm-navmenu",
        "race"          =>"rm-navmenu",
        "entries"       =>"rm-navmenu",
        "start"         =>"rm-navmenu",
        "timer"         =>"rm-navmenu",
        "pursuit"       =>"rm-navmenu",
        "results"       =>"rm-navmenu",
        "help"          =>"rm-navmenu",
        "club"          =>"rm-navmenu",
    );
    $state["{$params['page']}"] = "rm-navmenu-active";

// setup club menu
    $club_menu = "";
    if (!empty($_SESSION['clublink']))
    {
        foreach ($_SESSION['clublink'] as $data) {
            $club_menu .= <<<EOT
                            <li ><a href="{$data['url']}" target="_blank">{$data['label']}</a></li>
EOT;
        }
    }

// setup pursuit finish menu option
    $pursuit = "";
    if ($params['pursuit'])
    {
        $pursuit = <<<EOT
            <li>
                <a href="pursuit_pg.php?eventid={eventid}&menu=true" >
                   <span class="{$state['pursuit']}">pursuit</span>
                </a>
            </li>
EOT;
    }

    $html = <<<EOT
        <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
             <div class="" style="margin-left:15px; margin-right:15px;">
                  <div class="navbar-header" style="min-width:25%">
                       <a class="navbar-brand rm-brand-title" href="{$_SESSION['sys_website']}" target="_blank">
                           <span style="padding-right: 40px">{brand}</span>
                       </a>
                  </div>

                  <div class="collapse navbar-collapse">
                      <ul class="nav navbar-nav" >
                          <li>
                              <a href="pickrace_pg.php?eventid={eventid}&menu=true" >
                                  <span class="{$state['dashboard']}"><i>programme</i></span>
                              </a>
                          </li>

                          <li>
                              <a href="race_pg.php?eventid={eventid}&menu=true" >
                                  <span class="{$state['race']}">race admin</span>
                              </a>
                          </li>

                          <li>
                              <a href="entries_pg.php?eventid={eventid}&menu=true" >
                                  <span class="{$state['entries']}">entries</span>
                              </a>
                          </li>

                          <li>
                              <a href="start_pg.php?eventid={eventid}&menu=true" >
                                  <span class="{$state['start']}">start</span>
                              </a>
                          </li>

                          <li>
                              <a href="timer_pg.php?eventid={eventid}&menu=true" >
                                  <span class="{$state['timer']}">time laps</span>
                              </a>
                          </li>

                          $pursuit

                          <li>
                              <a href="results_pg.php?eventid={eventid}&menu=true" >
                                  <span class="{$state['results']}">results</span>
                              </a>
                          </li>

                      </ul>

                      <ul class="nav navbar-nav pull-right">
                          <li>
                              <a href="help_pg.php?eventid={eventid}&page={page}&menu=true" >
                                  <span class="{$state['help']}">help</span>
                              </a>
                          </li>
                          <li class="dropdown">
                              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                  <span class="{$state['club']}">{$_SESSION['clubcode']} <span class="caret"></span></span>
                              </a>
                              <ul class="dropdown-menu" role="menu">
                                  <li >&nbsp;&nbsp;<b>Local information &hellip;</b></li>
                                  <li class="divider"></li>
                                  $club_menu
                              </ul>
                          </li>
                          <li>
                              <div id="clockdisplay" style="width:100px; color:orange; font-size:150%; font-weight: bold; text-align: right; padding-top:12px"></div>
                          </li>
                      </ul>

                  </div>
             </div>
        </div>
EOT;
    return $html;
}


function pickrace_navbar($params=array())
{
    $club_menu = "";
    if (!empty($_SESSION['clublink']))
    {
        foreach ($_SESSION['clublink'] as $data) {
            $club_menu .= <<<EOT
                            <li ><a href="{$data['url']}" target="_blank">{$data['label']}</a></li>
EOT;
        }
    }

    $html = <<<EOT
       <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
           <div class="" style="margin-left:10px; margin-right:10px;">
               <div class="navbar-header">
                    <a class="navbar-brand rm-brand-title" href="#" target="_blank">
                        <span style="padding-right: 60px">{brand}</span>
                    </a>
               </div>

               <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav pull-right">
                        <li>
                            <a href="help_pg.php?eventid={eventid}&page={page}&menu=false" >
                               <span class="rm-navmenu">help</span>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                               <span class="rm-navmenu">{$_SESSION['clubcode']} <span class="caret"></span></span>
                            </a>
                            <ul class="dropdown-menu pull-right" role="menu">
                                $club_menu
                            </ul>
                        </li>
                    </ul>
               </div>
           </div>
       </div>
EOT;
    return $html;
}


function help_navbar($params=array())
{
    $clubmenu = "";
    if (!empty($params['clublink']))
    {
        foreach ($params['clublink'] as $data) {
           $clubmenu .= <<<EOT
           <li class=""><a href="{$data['url']}" target="_blank">{$data['label']}</a></li>
EOT;
        }
    }

    $html = <<<EOT
       <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
           <div class="" style="margin-left:10px; margin-right:10px;">
               <div class="navbar-header">
                    <a class="navbar-brand rm-brand-title" href="rbx_pg_{page}.php?eventid={eventid}&page={page}&menu=false" target="_blank">
                        <span style="padding-right: 60px">{brand}</span>
                    </a>
               </div>

               <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav pull-right">
                        <li>
                            <a href="help_pg.php?eventid={eventid}&page={page}&menu=false" >
                               <span class="rm-navmenu">help</span>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                               <span class="rm-navmenu">{clubcode} <span class="caret"></span></span>
                            </a>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li >&nbsp;&nbsp;<b>Local information &hellip;</b></li>
                                <li class="divider"></li>
                                $clubmenu
                            </ul>
                        </li>
                    </ul>
               </div>
           </div>
       </div>
EOT;
    return $html;
}

?>