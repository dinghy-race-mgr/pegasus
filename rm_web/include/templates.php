<?php

class TEMPLATE
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

        <link rel="shortcut icon" href="{loc}/images/favicon.ico">
        <link rel="stylesheet" href="{loc}/oss/bootstrap/css/bootstrap.min.css" >
        <link rel="stylesheet" href="{loc}/oss/bootstrap/css/bootstrap-theme.min.css">
        <link rel="stylesheet" href="{loc}/oss/bs-validator/dist/css/formValidation.min.css">

        <script type="text/javascript" src="{loc}/oss/jquery/jquery.min.js"></script>
        <script type="text/javascript" src="{loc}/oss/bs-validator/dist/js/formValidation.min.js"></script>
        <script type="text/javascript" src="{loc}/oss/bs-validator/dist/js/framework/bootstrap.min.js"></script>
        <script type="text/javascript" src="{loc}/oss/bs-validator/dist/js/addons/mandatoryIcon.js"></script>

        <script type="text/javascript" src="{loc}/oss/bootstrap/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="{loc}/oss/bs-growl/jquery.bootstrap-growl.min.js"></script>

        <!-- Custom styles for this template -->
        <link href="{loc}/custom.css" rel="stylesheet">

        </head>
        <body >
        {header}
            <div class="container-fluid" style="margin-top:{margin-top};">
                {body}
            </div>
        {footer}
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
       <div class="container">
          <div class="row">
             <div class="col-md-3 navbar-text">{left}</div>
             <div class="col-md-3 navbar-text" style="text-align: center">{center}</div>
             <div class="col-md-3 navbar-text navbar-right" style="text-align: right">{right}</div>
          </div>
       </div>
EOT;
       if ($params['footer_type'] == "fixed")
       {
           $html = <<<EOT
           <div class="navbar navbar-default navbar-fixed-bottom">
              $html
           </div>
EOT;
       }
    return $html;
    }
    
    
    public function error($params=array())
    {
       $stop_button = $this->close_button();
       $html = <<<EOT
       <div class="jumbotron center-block" style="width:60%; margin-top: 60px;">
          <h3>Problem encountered:</h3>
          <div style="margin-left:20px">
             <p><span class="err-problem">{problem}</span></p>
             <p><span class="err-symptom">{symptom}</span></p>
             <p><span class="err-where">{where}</span></p>
             <p><span class="err-fix">{fix}</span></p>
          </div>
          <div class="pull-right" style="padding-right: 30px">$stop_button</div>
          <div>&nbsp;</div>
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
       if ($params['inc_tide']) { $tide = "<th>Tide</th>"; }
       if ($params['inc_type']) { $category = "<th>Type/Format</th>"; } 
       if ($params['inc_duty']) { $duty = "<th>Duties</th>"; }
              
       $html = <<<EOT
       <thead>
          <tr class="bg-primary" style="font-size: 0.8em;"> 
             <th>Date</th> 
             <th>Time</th> 
             <th>Event</th> 
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
       if ($params['inc_tide']) { $tide = "<td class=\"\">{tide}</td>"; }       
       if ($params['inc_type']) { $category = "<td class=\"\">{category}<br><span class=\"tb_format\">{subcategory}</span></td>"; }  
       if ($params['inc_duty']) 
       { 
          $duty = <<<EOT
             <td class="" style="width: 20%">
                <button class="btn btn-info btn-xs" type="button" data-toggle="collapse" data-target="#duty{id}" 
                    aria-expanded="false" aria-controls="duty{id}" style="min-width:100px;">Duties</button>
                <div class="collapse{duty_show}" id="duty{id}">
                   <div class="well" style="padding: 0px !important">
                         <table class="table condensed">
                         {duties}
                         </table>
                   </div>
                </div>
             </td>
EOT;
       }

       $html = <<<EOT
          <tr class="prg_{state}">
             <td class="">{alert}{date}</td>
             <td class="">{time}</td>
             <td class="prg_event">{event}<br><span class="prg_notes">{note}</span></td>
             $category
             $tide
             $duty
         </tr>
EOT;
        return $html;    
    }


    public function tb_prg_none($params=array())
    {
       $html = <<<EOT
       <div class="row">
         <div class="alert alert-warning center-block" role="alert" style="width: 60%">
            <h4>No events for this period or search</h4>
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
      <div class="row">
         
         <div class="col-sm-9 col-md-9">
            <nav aria-label="Page navigation">
               <ul class="pagination" style="margin-left: 20%; padding-top: 0px !important">
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
               <input type="text" class="form-control" placeholder="Search" name="srch-term" id="srch-term">
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
    
    
    
    public function menu_card($params=array())
    {
       $html = <<<EOT
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
EOT;

       return $html;
    }
    
    public function notready($params=array())
    {
    $html = <<<EOT
       <div class="jumbotron center-block" style="width:60%; margin-top: 60px;">
          <div class="row">
             <div class="col-md-6">
                 <img src="./images/uc_hat_t.png" alt="under construction" height="200" width="200"> 
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
    
}



?>