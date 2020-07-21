<?php

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