<?php
class PROGRAMME
{
   
   public function __construct($programme_file, $mode, $force)
   {
      $this->programme_file = $programme_file;
      
      // parse programme into session object
      if (empty($_SESSION['prg']) or $force)    // if $SESSION data not created - do it
      {         
         $status = $this->import_programme();
         if (!$status)                          // programme file not found or not readable
         {
            $_SESSION['error']['problem'] = "Cannot display current event programme";
            $_SESSION['error']['symptom'] = "Programme file not found on system (or not readable)";
            $_SESSION['error']['where']   = "programme_construct";
            $_SESSION['error']['fix']     = "";
         }          
      }
      
      // set date limits for programme to be displayed
      $this->start = date("Y-m-d");
      $this->end   = $_SESSION['prg']['meta']['last'];
      if ($mode == "full") { $this->start = $_SESSION['prg']['meta']['first']; }
           
   }
   
   
   public function set_parameters($request)
   {
      $params = array("opt"=>"", "start"=>"", "end"=>"", "search"=>"");
      if ($request['opt'] == "none" or empty($request['opt']))
      {
          $params['opt'] = "none";
          if (!empty($request['start'])) { $params['start'] = $request['start']; }
          if (!empty($request['end'])) { $params['end'] = $request['end']; }    
      }
      elseif ($request['opt'] == "search")
      {
          $params['opt']    = "search";
          if (!empty($request['srch-term'])) { $params['search'] = $request['srch-term']; }      
      }
      elseif ($request['opt'] == "all") 
      {
          $params['opt'] = "all";
      }
      else
      {
          $params['opt']   = "init";
          $params['start'] = date("Y-m-d", strtotime("first day of this month"));
          $params['end']   = date("Y-m-d", strtotime("first day of next month"));
      }
      return $params;
   }
   
   
   public function calendar_nav($current, $params)
   {
      // programme start and end - and get months between these two dates
      $start    = new DateTime($this->start);
      $start->modify('first day of this month');
      $end      = new DateTime($this->end);
      $end->modify('first day of next month');
      $interval = DateInterval::createFromDateString('1 month');
      $period   = new DatePeriod($start, $interval, $end);
      
      // current date
      $current = new DateTime($current);
      $current_mon = $current->format("m/y");
      
      // start and end of interval containing current date
      $int_start = clone $current;
      $int_start->modify('first day of this month');
      $int_end = clone $current;
      $int_end->modify('first day of next month');
      
      // set limits on previous month - with earliest month constraint
      $prev_start = clone $int_start;
      $prev_start->modify('first day of previous month');
      $prev_end = clone $int_start;
      if ($prev_start < $start) 
      { 
         $prev_start = clone $int_start;
         $prev_end = clone $int_end;
      } 
      
      // set limits on next month - with latest month constraint
      $next_start = clone $int_end;
      $next_end = clone $int_end;
      $next_end->modify('first day of next month');
      if ($next_end > $end)
      {
         $next_start = clone $int_start;
         $next_end = clone $int_end;
      }
      
      $months = "";
      $month_baseurl = $_SERVER['PHP_SELF']."?page=programme&start=%s&end=%s&opt=%s";
      $prev_url  = sprintf($month_baseurl, $prev_start->format("Y-m-d"), $prev_end->format("Y-m-d"), "none");      
      $next_url  = sprintf($month_baseurl, $next_start->format("Y-m-d"), $next_end->format("Y-m-d"), "none");
      $all_url = sprintf($month_baseurl, $start->format("Y-m-d"), $end->format("Y-m-d"), "all");
      $search_url = $_SERVER['PHP_SELF'];
      
      foreach ($period as $dt) 
      {
         $active = false;
         $active_start = "";
         $active_end = "";
         
         $month_str = $dt->format("M");
         $this_mon = $dt->format("m/y");
         
         $class = "";
         if (($this_mon == $current_mon) and $params['opt'] != "all" and $params['opt'] != "search")  { $active = true; }
         
         $dt_start = $dt->format("Y-m-d");
         $dt->modify('first day of next month');
         $dt_end = $dt->format("Y-m-d");         
         $month_url = sprintf($month_baseurl, $dt_start, $dt_end, "none");
         
         if ($active)
         {
            $class = "active";
            $active_start = $dt_start;
            $active_end = $dt_end;
         }         
         $months.= "<li class='$class'><a href='$month_url'>$month_str</a></li>";
      }
      
      $cal_fields = array(
         "search_url"   => $search_url,
         "prev_url"     => $prev_url,
         "months"       => $months,
         "next_url"     => $next_url,
         "all_url"      => $all_url, 
         "active_start" => $active_start,
         "active_end"   => $active_end,
      );
      
      return $cal_fields;
   }
   
   private function export_programme($event_data, $duty_data, $source, $title, $club, $datetime)
   /* Produces programme.json file for use by rm_web
      Assumes that events will be presented to it in date/time ascending order
      Returns false if file not writable or no events to add - otherwise returns no. of events in file
   */
   {
      $num = 0;
      $out['prg'] = array(
         "meta"   => array(
             "last_update" => $datetime,
             "source"      => $source,
             "title"       => $title,
             "club"        => $club,
         ),
         "events" => array(),
      );
      foreach ($event_data as $k=>$event)
      {
         $num++;
         if ($num == 1)
         {
             $out['prg']['meta']['first'] = date("Y-m-d", strtotime($event['date']));
         }
         
         $date = date("Y-m-d",strtotime($event['event_date']));
         $time = date("H:i",strtotime($event['event_start']));
         $hw   = date("H:i",strtotime($event['tide-time']));
         $out['prg']['events']["ev_{$event['id']}"] = array(
                    "id"          => $event['id'],
                    "name"        => $event['event_name'],                 // "Spring Series 1",
                    "note"        => $event['event_notes'],                // "First race of series",
                    "datetime"    => $date."T".$time,                      // "2016-09-02T10:00",
                    "category"    => $event['event_type'],                 // "racing",
                    "subcategory" => $event['event_format'],               // "club series",
                    "tide"        => "HW $hw {$event['tide-time']}m",      // "HW 10:45 3.4m",
         );
         
         // check if event is flagged as important
         if (array_key_exists('important', $event))
         {
            if ($event['important']) { $out['prg']['events']["ev_{$event['id']}"]['state'] = "important"; }
         }            
                  
         // add duties for this event
         foreach ($duty_data[$event['id']] as $duty=>$person)
         {
            $out['prg']['events']["ev_{$event['id']}"]['duties'][$duty] = $person;
         }               
      }
      $out['prg']['meta']['last'] = date("Y-m-d", strtotime($event['date']));
      
      if ($num > 0)
      {
          // create json file
          if (is_writable($this->programme_file))
          {
             $fp = fopen($this->programme_file, 'w');
             fwrite($fp, json_encode($out));
             fclose($fp);
             return $num;          
          }
          else
          {
             return false;
          }
          
      }
      else
      {
          return false;
      }
   }
   
   private function import_programme()
   // reads json file created with export_programme and creates session array
   {
      if (is_readable($this->programme_file))
      {
	$string = file_get_contents($this->programme_file);
	$_SESSION['prg'] = json_decode($string, true);
	
	$today = new DateTime(date("Y-m-d"));
	// add event state where required
	$next_set = false;
	foreach($_SESSION['prg']['events'] as $eventid=>$event)
	{
	    if (!array_key_exists('state', $event))
	    {
		$edate = new DateTime(date("Y-m-d", strtotime($event['datetime'])));   
		if ($edate >= $today)
		{
		    if ($next_set)
		    {
		      $_SESSION['prg']['events']["$eventid"]['state'] = "future";
		    }
		    else
		    {
		      $_SESSION['prg']['events']["$eventid"]['state'] = "next";
		      $next_set = true;
		    }
		}
		else
		{
		    $_SESSION['prg']['events']["$eventid"]['state'] = "past";                
		}            
	    }          
	}
       }
       else
       {
          return false;
       }
       return true;
   }
   

   
//    public function parse_programme()
//    // open json file and create programme array
//    // ignore events before earliest date
//    {  
//    $_SESSION['prg'] = array(
//       "meta" => array(
// 	  "last_update" => "2016-08-02T20:38",
// 	  "source" => "raceManager",
// 	  "title" => "Race Programme",
// 	  "club" => "Starcross YC",
// 	  "first" => "2016-04-04",
// 	  "last" => "2017-04-01",
//       ),
//       "events" => array(
// 	  "evt_1203" => array(
// 	    "id" => 1203,
// 	    "name" => "Spring Series 1",
// 	    "note" => "First race of series",
// 	    "datetime" => "2016-09-02T10:00",
// 	    "category" => "racing",
// 	    "subcategory" => "club series",
// 	    "tide" => "HW 10:45 3.4m",
// 	    "duties" => array(
// 		"race officer" => "Fred Binns",
// 		"timekeeper" => "Joe Binns",
// 		"safety boat 1" => "Martha Binns",
// 		"safety boat 2" => "Joey Binns",
// 		"safety crew 1" => "Freda Binns",
// 		"safety crew 2" => "Maxammilion Binns",
// 		"galley" => "Petroushca Binns",
// 		"bar" => "Robbie Binns", 
// 	    ),
// 	    "state" => "past",
// 	  ),
// 	  "evt_1204" => array(
// 	    "id" => 1204,
// 	    "name" => "Patrick Kelley Trophy",
// 	    "note" => "Pursuit race - Topper is scratch boat",
// 	    "datetime" => "2016-09-09T14:30",
// 	    "category" => "racing",
// 	    "subcategory" => "trophy race",
// 	    "tide" => "HW 13:45 4.2m",
// 	    "duties" => array(
// 		"race officer" => "Fred Binns",
// 		"timekeeper" => "Joe Binns",
// 		"safety boat 1" => "Martha Binns",
// 		"safety boat 2" => "Joey Binns",
// 		"safety crew 1" => "Freda Binns",
// 		"safety crew 2" => "Maximillion Binns",
// 		"galley" => "Petroushca Binns",
// 		"bar" => "Robbie Binns",
// 	    ),
// 	    "state" => "next",
// 	  ),
// 	  "evt_1205" => array(
// 	    "id" => 1205,
// 	    "name" => "Commodore's Cruise",
// 	    "note" => "",
// 	    "datetime" => "2016-11-17T12:00",
// 	    "category" => "dinghy cruise",
// 	    "subcategory" => "up river",
// 	    "tide" => "HW 10:45 3.4m",
// 	    "duties" => array(
// 		"safety boat 1" => "Martha Binns",
// 		"safety boat 2" => "Joey Binns",
// 		"safety crew 1" => "Freda Binns",
// 		"safety crew 2" => "Maxammilion Binns",    
// 	    ),   
// 	    "state" => "future",
// 	  ),
// 	  "evt_1206" => array(
// 	    "id" => 1204,
// 	    "name" => "Beginner's trophy",
// 	    "note" => "Pursuit race - Topper is scratch boat",
// 	    "datetime" => "2016-10-09T14:30",
// 	    "category" => "racing",
// 	    "subcategory" => "trophy race",
// 	    "tide" => "HW 13:45 4.2m",
// 	    "duties" => array(
// 		"race officer" => "Fred Binns",
// 		"timekeeper" => "Joe Binns",
// 		"safety boat 1" => "Martha Binns",
// 		"safety boat 2" => "Joey Binns",
// 		"safety crew 1" => "Freda Binns",
// 		"safety crew 2" => "Maximillion Binns",
// 		"galley" => "Petroushca Binns",
// 		"bar" => "Robbie Elkington",
// 	    ),
// 	    "state" => "important",
//           ),
//       ),
//     );
//     
//    //echo prettyPrint( json_encode($_SESSION['prg'])); 
//    }
   
   
   public function search_programme($needle, $start, $end)
   // search programme and return target array of event ids in date order that match
   {
      if (!empty($needle))    // do substring search
      {
          $events = $this->search_substring($_SESSION['prg']['events'], $needle);
      }
      else                    // do calendar search
      {         
         if (empty($start) or empty($end))   // send entire programme
         {
             $events = $_SESSION['prg']['events'];
         }
         else
         {             
             $events = $this->search_date($_SESSION['prg']['events'], $start, $end);
         }      
      }    
      return $events;      
   }
   
   
   private function search_substring($programme, $needle)
   {
      $events = array();
      foreach ($programme as $k=>$event)
      {
         foreach($event as $prop=>$value)
         {
            if ($prop == "duties")     // check in duties array
            {
               foreach($value as $role=>$person)
               {
                  if (stripos($person, $needle) !== FALSE)
                  {
                     $events[$k] = $event;
                     break 2;
                  } 
               }
            }
            else                      // check other properties
            {
                  if (stripos($value, $needle) !== FALSE)
                  {
                     $events[$k] = $event;
                     break;
                  }  
            }                           
         }          
      }
      return $events;  
   }
   
   
   private function search_date($programme, $start, $end)
   {
      $search_start = new DateTime($start);
      $search_end = new DateTime($end);
      $events = array();
      foreach ($programme as $k=>$event)
      {
         $date = new DateTime($event['datetime']);
         if ($date >= $search_start AND $date < $search_end)    // note assumes end date is first date after search period
         {
             $events[$k]=$event;
         }
      }
      return $events;
   }
   

}
?>