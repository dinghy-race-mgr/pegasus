<?php
class PAGES
{  
   
   public function __construct($cfg)
   {
       include ("./include/templates.php");
       include ("./include/util.php");
       $this->tmpl_o = new TEMPLATE();
       $this->cfg = $cfg;
   }
   
   public function pg_menu()
   {
      // clear parsed data
      unset($_SESSION['prg']); 
      
      
      $num_pages = count($this->cfg['pages']);
      
      if ($num_pages < 1)    // display error
      {
           $this->pg_none("No information pages configured", "", "", ""); 
      }
      elseif ($num_pages == 1)    // go straight to page
      {
           $page = array_search(true, $this->cfg['pages'] ,true);
           $this->{"pg_$page"}();      
      }
      else                       // layout pages with three options per line
      {
           $bufr = <<<EOT
           <div class="row">
              <div class="col-md-10 col-md-offset-1 col-sm-10 col-sm-offset-1"> 
                 <div class="row">
EOT;
           $count = 0;
           foreach ($this->cfg['pages'] as $page=>$include)
           {
              if ($include)
              {              
                  $count++;
                  $fields = array(
                     "color"=>"menu-block menu-block-$count", 
                     "label"=>$this->cfg[$page]['title'], 
                     "text"=>$this->cfg[$page]['caption'], 
                     "link"=>$this->cfg[$page]['url'], 
                     "icon" => $this->cfg[$page]['icon']
                     );
                  $bufr.= "<div class=\"col-md-4 col-sm-4\">";
                  $bufr.= $this->get_template("menu_card", $fields);
                  $bufr.= "</div>";
              }              
              if ($count == 3) { $count = 0; }  // start on next row
              
           }           
           $bufr.= '</div></div></div>';
      }
           
      
      $fields = array(
         "loc"    => $this->cfg['loc'],
         "window" => "raceManager",
         "header" => $this->get_header(),
         "margin-top"=> "80px",
         "body"   => $bufr,
         "footer" => $this->get_footer(),     
      );      
      echo $this->get_template("layout_master", $fields, array());
   }

   public function pg_programme()
   {
      $prog = new PROGRAMME($this->cfg['programme']['json'], "future", false);  
      
      if (array_key_exists("error", $_SESSION)) 
      { 
          $this->pg_none($_SESSION['error']); 
          exit();
      }
      
      $params = $prog->set_parameters($_REQUEST);      
            
      $current_date = date("Y-m-d");
      if (!empty($params['start']) and checkmydate($params['start']))   
      {
          $current_date = $_REQUEST['start'];
      }
      
      // calendar navigation       
      $cal_fields = $prog->calendar_nav($current_date, $params);
      $body = $this->get_template("calendar_nav", $cal_fields, $params);
      
      // search to get rows to display
      $events = $prog->search_programme($params['search'], $params['start'], $params['end']);
      
      // create table header  
      //
            
      // create table data
      if (empty($events))
      {
          $fields = array();
          $fields['search'] = (empty($params['search']) ? "none" : $params['search']);
          $fields['start']  = (empty($params['start'])  ? "none" : $params['start']);
          $fields['end']    = (empty($params['end'])    ? "none" : $params['end']);
          
          $body.= $this->get_template("tb_prg_none", $fields, array());
      }
      else
      {
          $table_data = "";
          foreach($events as $id=>$event)
          {         
             $alert = "";
             if ($event['state'] == "next")
                { $alert = "NEXT EVENT ...<br>"; }
             elseif ($event['state'] == "important")
                { $alert = "IMPORTANT ...<br>"; }
   
             $duty_info = "";
             foreach($event['duties'] as $duty=>$person)
             {
                $duty_info.= $this->get_template("tb_prg_duty", array("duty"=>$duty, "person"=>$person), array());
             }      
             $evt_fields = array(
                "state"       => $event['state'],
                "alert"       => $alert,
                "id"          => $id,
                "date"        => date("d M Y", strtotime($event['datetime'])),
                "time"        => date("H:m", strtotime($event['datetime'])),
                "event"       => $event['name'],
                "note"        => $event['note'],
                "category"    => $event['category'],
                "subcategory" => $event['subcategory'],
                "tide"        => $event['tide'],
                "duties"      => $duty_info, 
                "duty_show" => "",   // ".in" for expanded
             );
             $table_data.= $this->get_template("tb_prg_data", $evt_fields, $this->cfg["programme"]['fields']);
          }
          $table_head = "";
          if ($this->cfg['programme']['table_hdr'])
          {
             $table_head = $this->get_template("tb_prg_header", array(), $this->cfg["programme"]['fields']);
          }
          
          $body.= $this->get_template("tb_prg_table", array("header"=>$table_head, "data"=>$table_data), array("condensed"=>false));
      }
      
      $fields = array(
         "loc"    => $this->cfg['loc'],
         "window" => "raceManager",
         "header" => $this->get_header("PROGRAMME", "menu"),
         "margin-top"=> "50px",
         "body"   => $body,
         "footer" => $this->get_footer(),     
      );      
      echo $this->get_template("layout_master", $fields, array());
   }
 
 
   public function pg_results()
   {
      $prog = new RESULTS(true);  
      
      if (array_key_exists("error", $_SESSION)) 
      { 
          $this->pg_none($_SESSION['error']); 
          exit();
      }
   }
   
   public function pg_pyanalysis()
   {
      $fields = array(
         "loc"    => $this->cfg['loc'],
         "window" => "raceManager",
         "header" => $this->get_header("PY ANALYSIS", "menu"),
         "margin-top"=> "50px",
         "body"   => $this->under_construction(array("title" => "PY Analysis Page:", "info" => "We are still working on PY analysis page")),
         "footer" => $this->get_footer(),     
      );      
      echo $this->get_template("layout_master", $fields, array());   
   }
   
   public function pg_none($error)
   {                 
      unset($_SESSION['error']);
      
      if (empty($error['fix'])) 
      { 
         $error['fix'] = "Please contact your raceManager administrator ..."; 
      }
      $body = $this->get_template("error", array("problem" => $error['problem'], "symptom" => $error['symptom'], "where" => $error['where'], "fix" => $error['fix']));
      
      $fields = array(
         "loc"    => $this->cfg['loc'],
         "window" => "raceManager",
         "header" => $this->get_header("", "menu"),
         "margin-top"=> "50px",
         "body"   => $body,
         "footer" => $this->get_footer(),  
      );      
      echo $this->get_template("layout_master", $fields, array());  
   }
   
   
   private function get_header($page="", $back="")
   {
      if (empty($page)) 
      {
         $suffix = "onLine";
      }
      else
      {         
         $suffix = $page;
      }
      
      if (empty($back)) 
      {
         $link = "";
      }
      else
      {
         $link = $_SERVER['PHP_SELF']."?page=$back";
      }
      
      $header = "";
      if ($this->cfg['menubar'])
      {
         $fields = array(
         "link"      => $link,
         "name"      => "raceManager",
         "suffix"    => $suffix,
         );
         $header = $this->get_template("header", $fields);
      }
      return $header;
   }
   
   private function get_footer()
   {
      $footer = "";
      if ($this->cfg['footer'])
      {
         $fields = array(
            "left"     => "copyright: Elmswood Software 2016",
            "center"   => "",
            "right"    => date("Y-m-d"),
         );
         $footer = $this->get_template("footer", $fields, $params = array("footer_type"=>"fixed"));
      }
      return $footer;
   }
   
   private function get_template($template, $fields, $params=array())
   {
       $html = $this->tmpl_o->{"$template"}($params);
       foreach ($fields as $field=>$value)
       {
          $html = str_replace("{".$field."}", $value, $html);
          // might be able to speed this up with str_replace(array_keys($fields), array_values($fields), $html)
       }
       return $html;
   }
   
   private function under_construction($fields, $params=array())
   // under construction page
   {
       return $this->get_template("notready", $fields, $params=array());
   }

}
?>