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

      // work out how many 'pages' have been configured
      $num_pages = 0;
      foreach ($this->cfg['pages'] as $page)
      {
          if ($page) { $num_pages++; }
      }
      
      if ($num_pages < 1)    // display error
      {
          $_SESSION['error'] = array(
            "problem" => "No options have been configured for this installation",
            "symptom" => "",
            "where" => "rm_web | rm_web.ini | pages.php | ".__LINE__,
            "fix" => ""
          );
          $this->pg_none($_SESSION['error']);
          exit();
      }
      elseif ($num_pages == 1)    // go straight to page
      {
           $page = array_search(1, $this->cfg['pages']);
           $this->{"pg_$page"}();      
      }
      else                   // layout pages with three options per line
      {
            $cards_bufr = "";
            $count = 0;
            foreach ($this->cfg['pages'] as $page=>$include)
            {
              if ($include)
              {
                  $count++;
                  $fields = array(
                     "color"=>"menu-block menu-block-$count",
                     "label"=>$this->cfg[$page]['title'],
                     "text" =>$this->cfg[$page]['caption'],
                     "link" =>$this->cfg[$page]['url'],
                     "icon" => $this->cfg[$page]['icon']
                     );
                  $cards_bufr.= $this->get_template("menu_card", $fields);
              }
              if ($count == 3) { $count = 0; }  // start on next row
            }
          $fields = array(
              "loc"    => $this->cfg['loc'],
              "ossloc" => "..",
              "window" => "raceManager",
              "header" => $this->get_header(),
              "margin-top"=> "80px",
              "body"   => $this->get_template("menu_page", array("cards"=>$cards_bufr)),
              "footer" => $this->get_footer(),
          );
          echo $this->get_template("layout_master", $fields, array());
      }
   }

    public function pg_programme()
    {
        $prog = new PROGRAMME($this->cfg['programme']['json'], "full", false);

        if (array_key_exists("error", $_SESSION))
        {
            $this->pg_none($_SESSION['error']);
            exit();
        }

        $params = $prog->set_parameters($_REQUEST);
        empty($params['start']) ? $current_date = date("Y-m-d") : $current_date = $params['start'] ;
      
        // calendar navigation
        $cal_fields = $prog->calendar_nav($current_date, $params);
        $this->cfg['programme']['fields']['inc_duty'] ? $cal_fields['placeholder'] = "Search ... event or person" :
        $cal_fields['placeholder'] = "Search event... ";

        $body = $this->get_template("calendar_nav", $cal_fields, $params);

        // search to get rows to display
        $events = $prog->search_programme($params['search'], $params['start'], $params['end']);

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
                if ($event['state'] == "next") { $alert = "NEXT EVENT ...<br>"; }
                elseif ($event['state'] == "important") { $alert = "IMPORTANT ...<br>"; }

                $duty_info = "";
                $duty_count = count($event['duties']);
                if ($this->cfg['programme']['fields']['inc_duty_ood_only'])
                {
                    if (array_key_exists("Race Officer", $event['duties']))
                    {
                        $duty_info = $event['duties']["Race Officer"];
                    }
                }
                else
                {
                    foreach ($event['duties'] as $duty => $person)
                    {
                         $duty_info .= $this->get_template("tb_prg_duty", array("duty" => $duty, "person" => $person), array());
                    }
                }

                $format = "";
                if ($event['category'] == "racing")
                {
                    $format = $_SESSION['prg']['meta']['racetype'][$event['subcategory']]['desc'];
                }

                $info_present = false;
                if (!empty($event['info']))
                {
                    $info_present = true;
                    if (empty($event['infolbl']))
                    {
                        $event['infolbl'] = "more information";
                    }
                }

                $evt_fields = array(
                    "state"       => $event['state'],
                    "alert"       => $alert,
                    "id"          => $id,
                    "date"        => date("D d M", strtotime($event['date'])),
                    "time"        => date("H:i", strtotime($event['time'])),
                    "event"       => $event['name'],
                    "note"        => $event['note'],
                    "category"    => $_SESSION['prg']['meta']['eventtype'][$event['category']],
                    "subcategory" => $event['subcategory'],
                    "format"      => $format,
                    "tide"        => $event['tide'],
                    "info"        => $event['info'],
                    "infolbl"     => $event['infolbl'],
                    "duties"      => $duty_info,
                    "duty_num"    => $duty_count
                );

                // ".in" for expanded duty info
                $this->cfg["programme"]['duty_display'] ? $evt_fields['duty_show'] = ".in" : $evt_fields['duty_show'] = "";

                $table_data.= $this->get_template("tb_prg_data", $evt_fields,
                    array("fields" => $this->cfg["programme"]['fields'], "state" => $event['state'], "info" => $info_present));
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
            "ossloc" => "..",
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
       $fields = array(
           "loc"    => $this->cfg['loc'],
           "ossloc" => "..",
           "window" => "raceManager",
           "header" => $this->get_header("RACE RESULTS", "menu"),
           "margin-top"=> "50px",
           "body"   => $this->under_construction(array("ossloc" => "..", "title" => "Race Results Page:",
                                                       "info" => "We are still working on the results page")),
           "footer" => $this->get_footer(),
       );
       echo $this->get_template("layout_master", $fields, array());
   }
   
   public function pg_pyanalysis()
   {
      $fields = array(
         "loc"    => $this->cfg['loc'],
         "ossloc" => "..",
         "window" => "raceManager",
         "header" => $this->get_header("PY ANALYSIS", "menu"),
         "margin-top"=> "50px",
         "body"   => $this->under_construction(array("ossloc" => "..", "title" => "PY Analysis Page:",
                                                     "info" => "We are still working on PY analysis page")),
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
      $body = $this->get_template("error", $error, array("stop_btn"=>true));
      
      $fields = array(
         "loc"    => $this->cfg['loc'],
         "window" => "raceManager",
         "header" => $this->get_header("", "menu"),
         "margin-top"=> "50px",
         "body"   => $body,
         "footer" => $this->get_footer(),  
      );      
      echo $this->get_template("layout_master", $fields);
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
            "left"     => "copyright: Elmswood Software ".date("Y"),
            "center"   => "",
            "right"    => date("d-m-Y"),
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
