<?php

class WEB_TEMPLATE
{

    public function layout_master($params=array())
    {
    $html = <<<EOT
    <!DOCTYPE html><html lang="en">
        <head>
        <title>{window}</title>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">

        <link   rel="icon"    href="{ossloc}/common/images/logos/favicon.png">
        <link rel="stylesheet" href="{ossloc}/common/oss/bootstrap341/css/bootstrap.min.css" >
        <link rel="stylesheet" href="{ossloc}/common/oss/bootstrap341/css/bootstrap-theme.min.css">
        <link rel="stylesheet" href="{ossloc}/common/oss/bs-validator/dist/css/formValidation.min.css">

        <script type="text/javascript" src="{ossloc}/common/oss/jquery/jquery.min.js"></script>
        <script type="text/javascript" src="{ossloc}/common/oss/bs-validator/dist/js/formValidation.min.js"></script>
        <script type="text/javascript" src="{ossloc}/common/oss/bs-validator/dist/js/framework/bootstrap.min.js"></script>
        <script type="text/javascript" src="{ossloc}/common/oss/bs-validator/dist/js/addons/mandatoryIcon.js"></script>

        <script type="text/javascript" src="{ossloc}/common/oss/bootstrap341/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="{ossloc}/common/oss/bs-growl/jquery.bootstrap-growl.min.js"></script>

        <!-- Custom styles for this template -->
        <link href="{loc}/style/custom.css" rel="stylesheet">

        </head>
        <body style="{body_style}">
        {header}
            <div class="container-fluid" style="margin-top:{margin-top};">
                {body}
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
    
    public function header($params=array())
    {
       // FIXME - add in menu options for each configured page if more than one
       $html = <<<EOT
       <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
          <div class="container" style="margin-left:10px; margin-right:10px;">
             <div class="navbar-header">
                <a class="navbar-brand" href="{link}" target="_parent" title="back to menu">
                   <span class="glyphicon glyphicon-chevron-left" style="padding-right: 60px;" aria-hidden="true"></span>
                   <span style="color: white;">&nbsp; {name} <b>{suffix}</b></span>
                </a>
             </div>
          </div>
       </div>
EOT;
    
    return $html;
    }
    
    public function footer($params=array())
    {
        $html = <<<EOT
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-3 text-left">{left}</div>
                    <div class="col-md-6 text-center">{center}</div>
                    <div class="col-md-3 text-right">{right}</div>
                </div>
            </div>
EOT;

        if ($params['footer_type'] == "fixed")
        {
           $html = <<<EOT
           <nav class="navbar navbar-default navbar-fixed-bottom">
              $html
           </nav>
EOT;
        }
    return $html;
    }
    
    
    public function error($params=array())
    {
        $stop_bufr = "";
        if ($params['stop_btn'])
        {
           $stop_button = $this->close_button();
           $stop_bufr = <<<EOT
                <div class="pull-right">
                    $stop_button
                    
                </div><br>
EOT;
        };
        $html = <<<EOT
        <div class="jumbotron center-block" style="width:60%; margin-top: 60px;">
          <h1>Sorry . . .</h1>
          <div style="margin-left:20px">
             <p><span class="err-problem">{problem}</span></p>
             <p><span class="err-symptom">{symptom}</span></p>
             <p><span class="err-fix">{fix}</span></p>
             <p><span class="err-where">{where}</span></p>
          </div>
          $stop_bufr
        </div>
EOT;
        return $html;
    }
    
    
    private function close_button($params=array())
    {
       $html = <<<EOT
        <button type="submit" class="btn btn-lg btn-warning" style="min-width:150px"
            onclick="window.open('', '_self', ''); window.close();">
            Stop &nbsp; <span class="glyphicon glyphicon-stop" aria-hidden="true"></span>
        </button>
EOT;
        return $html;
    }
    
    
    public function tb_prg_table($params=array())
    {
       $condensed = "";
       if ($params['condensed']) { $condensed = "table-condensed"; }

       $html = <<<EOT
       <div class="row">
          <div class="center-block" style="width:90%;">
             <table class="table table-hover $condensed">
                {header}
                {data}
             </table>
          </div>
       </div>      
EOT;
        return $html;
    }
    
    
    public function tb_prg_header($params=array())
    {
       $tide = "";
       $category = "";
       $duty = "";
       if ($params['inc_tide']) { $tide = "<th width=\"15%\">Tide</th>"; }
       if ($params['inc_type']) { $category = "<th width=\"15%\">Type/Format</th>"; }
       if ($params['inc_duty'])
       {
           if ($params['inc_duty_ood_only'])
           {
               $duty = "<th width=\"25%\">Race Officer</th>";
           }
           else
           {
               $duty = "<th width=\"25%\">Duties</th>";
           }

       }
              
       $html = <<<EOT
       <thead>
          <tr class="bg-primary" style="font-size: 0.8em;"> 
             <th width="10%">Date</th> 
             <th width="10%">Time</th> 
             <th width="25%">Event</th> 
             $category
             $tide
             $duty             
          </tr> 
       </thead> 
EOT;
        return $html;
    }
    
    public function tb_prg_data($params=array())
    {
       $tide = "";
       $category = "";
       $duty = "";

       if ($params['state']=="noevent")
       {
           $time = "<td>&nbsp;</td>";
           $name = "<td>[ {event} ]<br><span class=\"prg_notes\">{note}</span></td>";
       }
       else
       {
           $time = "<td class=\"\">{time}</td>";
           $name = "<td class=\"\"><b>{event}</b><br><span class=\"prg_notes\">{note}</span></td>";
       }

       if ($params['fields']['inc_tide'])
       {
           $tide = "<td class=\"\">{tide}</td>";
       }

       if ($params['fields']['inc_type'] and $params['state']!="noevent")
       {
           $category = <<<EOT
           <td class="">
                {category} / 
                <span class="tb_format" data-toggle="tooltip" data-placement="right" title="{format}">
                    {subcategory}
                </span>
           </td>

EOT;
       }

       $info = "";
       if ($params['info'])
       {
           $info = <<<EOT
                <a href="{info}" target="_blank">{infolbl}</a>
EOT;
       }

       if ($params['fields']['inc_duty'] and $params['state']!="noevent")
       {
           if ($params['fields']['inc_duty_ood_only'])
           {
               $duty = <<<EOT
                {duties}
EOT;
           }
           else
           {
               $duty = <<<EOT
                    <a  data-toggle="collapse" data-target="#duty{id}" 
                    aria-expanded="false" aria-controls="duty{id}" ><b>Duties [{duty_num}]</b></a>
                    <div class="collapse{duty_show}" id="duty{id}">
                       <div class="well" style="padding: 0px 0px 0px 0px !important; bottom-margin: 0px !important">
                             <table class="table condensed" style="padding: 0px 0px 0px 0px !important">
                             {duties} 
                             </table>
                       </div>
                    </div>
EOT;
           }
       }

       $html = <<<EOT
          <tr class="prg_{state}">
             <td class="">{alert}{date}</td>
             $time
             $name
             $category
             $tide
             <td class="" style="width: 20%">
                $duty<br>
                $info
             </td>
         </tr>
EOT;
        return $html;    
    }

    public function tb_prg_none($params=array())
    {
       $html = <<<EOT
       <div class="row">
         <div class="alert alert-warning center-block" role="alert" style="width: 60%">
            <h4>No events for this month or search</h4>
         </div>
       </div>
EOT;
       return $html; 
    }
    
    
    public function tb_prg_duty($params=array())
    {
       $html = <<<EOT
         <tr>
            <td class="duty_type">{duty}</td>
            <td class="duty_person">{person}</td>
         </tr>
EOT;
       return $html;
    }
    
    
    public function calendar_nav($params=array())
    {
      // FIXME text-center doesn't work with latest bootstrap3 - use center-block 
      $all_active = "";
      if ($params['opt'] == "all") { $all_active = "class=\"active\""; }

      $html = <<<EOT
      <div class="row no-print">
         
         <div class="col-sm-9 col-md-9">
            <nav aria-label="Page navigation">
               <ul class="pagination" style="margin-left: 5%; padding-top: 0px !important">
                  <li>
                     <a href="{prev_url}" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>
                  </li>
                  {months}                
                  <li>
                     <a href="{next_url}" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>
                  </li>
                  <li $all_active>
                     <a href="{all_url}"><b>ALL</b></a>
                  </li>                
               </ul>
               
            </nav>
         </div>
         
         <div class="col-sm-3 col-md-3">
         <form class="form" role="search" action="{search_url}">
            <input type="hidden" name="page" value="programme">
            <input type="hidden" name="start" value="">
            <input type="hidden" name="end" value="">
            <input type="hidden" name="opt" value="search">
            <div class="input-group" style="margin-top: 20px; margin-right: 20%;">
               <input type="text" class="form-control" placeholder="{placeholder}" name="srch-term" id="srch-term">
               <div class="input-group-btn">
                  <button class="btn btn-default" type="submit"><i class="glyphicon glyphicon-search"></i></button>
               </div>               
            </div>
         </form>
         
         </div>
     </div>
EOT;
        return $html;        
    }


    public function menu_page($params=array())
    {
        $html = <<<EOT
        <div class="row">
            <div class="col-md-10 col-md-offset-1 col-sm-10 col-sm-offset-1"> 
                <div class="row">
                {cards}
                </div>
            </div>
        </div>
EOT;
        return $html;
    }
    
    
    public function menu_card($params=array())
    {
       $html = <<<EOT
        <div class="col-md-4 col-sm-4">
            <a href="{link}" STYLE="text-decoration: none">
              <div class="panel panel-widgets {color}">
                 <div id=weather>
                    <div class="panel-heading">
                       <h2><span class="{icon}"></span> {label}</h2>
                    </div>
                    <div class=panel-body>{text}</div>
                 </div>
              </div>
            </a>
        </div>
EOT;
       return $html;
    }
    
    public function notready($params=array())
    {
    $html = <<<EOT
       <div class="jumbotron center-block" style="width:60%; margin-top: 60px;">
          <div class="row">
             <div class="col-md-6">
                 <img src="{ossloc}/common/images/web_graphics/uc_hat_t.png" alt="under construction" height="200" width="200"> 
             </div>
             <div class="col-md-6">
                <p><b>{title}</b></p>
                <p>{info}</p>
             </div>
          </div>
          <div>&nbsp;</div>
       </div>
EOT;
        return $html;    
    }


    private function year_switch($start, $end, $searchstr)
    {
        $year_list_htm = "";
        for ($i=$end; $i >= $start; $i = $i-1)
        {
            $year_list_htm.= <<<EOT
                <li><a href="rm_web.php?page=results&year=$i&searchstr=$searchstr">$i</a></li>
EOT;
        }

        $htm = <<<EOT
            <div class="btn-group" style="margin-top: 20px;">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" 
                aria-expanded="false" style="font-size: 1.2em">
                    Change Year &hellip; <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    $year_list_htm
                </ul>
            </div>
EOT;
        return $htm;

    }

    private function search_event($year, $searchstr)
    {
        $htm = <<<EOT
            <form class="form" action="rm_web.php?page=results&year=$year"
            method="post" role="search" autocomplete="off">
                <input type="hidden" value="$year">
                <div class="input-group" style="margin-top: 20px;">
                    <input type="text" class="form-control" placeholder="search for event&hellip;" name="searchstr" id="searchstr" value="$searchstr">
                    <div class="input-group-btn">
                    <button class="btn btn-default btn-md" type="submit"><i class="glyphicon glyphicon-search"></i></button>
                    </div>               
                </div>
            </form>
EOT;
        return $htm;
    }


    public function results_content($params=array())
    {
        $year_control = $this->year_switch($params['start_year'], $params['end_year'], $params['searchstr']);
        $search_control = $this->search_event($params['year'], $params['searchstr']);

        $html = <<<EOT
        <div class="container-fluid">
            <div class="row no-print">                   
                <div class="col-sm-6 col-md-6"><h1 class="text-primary">{page-title}</h1></div>
    
                <div class="col-sm-3 col-md-3">$year_control</div>
                
                <div class="col-sm-3 col-md-3">$search_control</div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <table class="table table-hover table-striped" width="100%">
                    {data}
                </table> 
            </div>              
        </div>
EOT;
        return $html;
    }


    public function no_results_content($params=array())
    {
        $year_control = $this->year_switch($params['start_year'], $params['end_year'], $params['searchstr']);
        $search_control = $this->search_event($params['year'], $params['searchstr']);

        $html = <<<EOT
        <div class="container-fluid">
            <div class="row no-print">                   
                <div class="col-sm-6 col-md-6"><h1 class="text-primary">{page-title}</h1></div>
    
                <div class="col-sm-3 col-md-3">$year_control</div>
                
                <div class="col-sm-3 col-md-3">$search_control</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-offset-2 col-md-8">
                <div class="jumbotron">
                    <h1>Sorry!</h1>
                    <p class="lead">We can't find the results for {year}</p>
                    <p>Please let your raceManager support team know about this</p>
                    <p class="pull-right"><small>[ Problem file: {inv-file} ]</small></p>
                </div>              
            </div>
        </div>
EOT;
        return $html;
    }
    
}

