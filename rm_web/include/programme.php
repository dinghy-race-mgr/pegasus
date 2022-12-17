<?php
class PROGRAMME
{
   
    public function __construct($loc, $programme_file, $mode, $force)
    {
        $status = true;
        $this->programme_file = $programme_file;

        // parse programme into session object
        $status = $this->import_programme($loc);
        if (!$status)                          // programme file not found or not readable
        {
            $_SESSION['error']['problem'] = "Cannot display current event programme";
            $_SESSION['error']['symptom'] = "Programme file not found on system (or not readable)";
            $_SESSION['error']['where']   = "rm_web | programme.php | __construct | line ".__LINE__;
            $_SESSION['error']['fix']     = "";
        }

        if ($status)
        {
            // set date limits for programme to be displayed
            $this->start = $_SESSION['prg']['meta']['first'];
            $this->end   = $_SESSION['prg']['meta']['last'];
        }

        u_writelog("rm_web - programme page request");
    }

   public function set_parameters($request)
   {
        // sets params for display of events on screen
        $params = array("opt"=>"", "start"=>"", "end"=>"", "search"=>"");

        // displays all events between requested start and end - used for month tab navigation
        if ($request['opt'] == "none" or empty($request['opt']))
        {
            $params['opt'] = "none";
            if (!empty($request['start'])) { $params['start'] = date("Y-m-d", strtotime($request['start'])); }
            if (!empty($request['end'])) { $params['end'] = date("Y-m-d", strtotime($request['end'])); }
        }

        // displays all events that match search term
        elseif ($request['opt'] == "search")  // displays all events that match results of search
        {
            $params['opt'] = "search";
            if (!empty($request['srch-term'])) { $params['search'] = trim($request['srch-term']); }
        }

        // displays all events
        elseif ($request['opt'] == "all")  // displays all events initially
        {
            $params['opt'] = "all";
        }

        // initial display on startup - shows current month OR if current month is outside the period covered
        // by the programme it displays the nearest month (i.e first or last month
        else
        {
            $params['opt']   = "init";

            $today = date("Y-m-d");
            $today_ts = strtotime($today);

            if ($today_ts >= strtotime($this->start) and $today_ts <= strtotime($this->end))  // in period
            {
                //echo "<pre> in period </pre>";
                $d = new DateTime( $today );
                $d->modify( 'first day of this month' );
                $params['start'] = $d->format("Y-m-d");
                $d->modify( 'first day of next month' );
                $params['end'] = $d->format("Y-m-d");
            }
            elseif ($today_ts < strtotime($this->start))                                      // before period
            {
                //echo "<pre> before period </pre>";
                $d = new DateTime( $this->start );
                $d->modify( 'first day of this month' );
                $params['start'] = $d->format("Y-m-d");
                $d->modify( 'first day of next month' );
                $params['end'] = $d->format("Y-m-d");
            }
            elseif ($today_ts > strtotime($this->end))                                        // after period
            {
                //echo "<pre> after period </pre>";
                $d = new DateTime( $this->end );
                $d->modify( 'first day of this month' );
                $params['start'] = $d->format("Y-m-d");
                $d->modify( 'first day of next month' );
                $params['end'] = $d->format("Y-m-d");
            }
        }

        //echo "<pre> {$params['opt']} {$params['start']} {$params['end']}</pre>";

        return $params;
   }
   
   
   public function calendar_nav($current, $params)

   {
      // programme start and end - and get months between these two dates as an array
      $start    = new DateTime($this->start);
      $start->modify('first day of this month');
      $end      = new DateTime($this->end);
      $end->modify('first day of next month');
      $interval = DateInterval::createFromDateString('1 month');
      $period   = new DatePeriod($start, $interval, $end);
      
      // current date
      $current = new DateTime($current);
      $current_mon = $current->format("m/y");
      //$current_mon = "06/16"; FIXME - what happens if current month is before programme - set to first month
      
      // start and end of interval containing current date
      $int_start = clone $current;
      $int_start->modify('first day of this month');
      $int_end = clone $current;
      $int_end->modify('first day of next month');
      
      // set limits on previous month - with earliest month constraint
      $prev_start = clone $int_start;
      $prev_start->modify('first day of previous month');
      $prev_end   = clone $int_start;
      if ($prev_start < $start) 
      { 
         $prev_start = clone $int_start;
         $prev_end   = clone $int_end;
      } 
      
      // set limits on next month - with latest month constraint
      $next_start = clone $int_end;
      $next_end   = clone $int_end;
      $next_end->modify('first day of next month');
      if ($next_end > $end)
      {
         $next_start = clone $int_start;
         $next_end   = clone $int_end;
      }
      
      $months = "";
      $month_baseurl = $_SERVER['PHP_SELF']."?page=programme&start=%s&end=%s&opt=%s";
      $prev_url  = sprintf($month_baseurl, $prev_start->format("Y-m-d"), $prev_end->format("Y-m-d"), "none");      
      $next_url  = sprintf($month_baseurl, $next_start->format("Y-m-d"), $next_end->format("Y-m-d"), "none");
      $all_url   = sprintf($month_baseurl, $start->format("Y-m-d"), $end->format("Y-m-d"), "all");
      $search_url = $_SERVER['PHP_SELF'];
      
      foreach ($period as $dt) 
      {
         $active = false;
         $active_start = "";
         $active_end   = "";
         
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
   
//   private function export_programme($event_data, $duty_data, $source, $title, $club, $datetime)
//   /* Produces programme_old.json file for use by rm_web
//      Assumes that events will be presented to it in date/time ascending order
//      Returns false if file not writable or no events to add - otherwise returns no. of events in file
//   */
//   {
//      $num = 0;
//      $out['prg'] = array(
//         "meta"   => array(
//             "last_update" => $datetime,
//             "source"      => $source,
//             "title"       => $title,
//             "club"        => $club,
//         ),
//         "events" => array(),
//      );
//      foreach ($event_data as $k=>$event)
//      {
//         $num++;
//         if ($num == 1)
//         {
//             $out['prg']['meta']['first'] = date("Y-m-d", strtotime($event['date']));
//         }
//
//         $date = date("Y-m-d",strtotime($event['event_date']));
//         $time = date("H:i",strtotime($event['event_start']));
//         $hw   = date("H:i",strtotime($event['tide-time']));
//         $out['prg']['events']["ev_{$event['id']}"] = array(
//                    "id"          => $event['id'],
//                    "name"        => $event['event_name'],                 // "Spring Series 1",
//                    "note"        => $event['event_notes'],                // "First race of series",
//                    "datetime"    => $date."T".$time,                      // "2016-09-02T10:00",
//                    "category"    => $event['event_type'],                 // "racing",
//                    "subcategory" => $event['event_format'],               // "club series",
//                    "tide"        => "HW $hw {$event['tide-time']}m",      // "HW 10:45 3.4m",
//         );
//
//         // check if event is flagged as important
//         if (array_key_exists('important', $event))
//         {
//            if ($event['important']) { $out['prg']['events']["ev_{$event['id']}"]['state'] = "important"; }
//         }
//
//         // add duties for this event
//         foreach ($duty_data[$event['id']] as $duty=>$person)
//         {
//            $out['prg']['events']["ev_{$event['id']}"]['duties'][$duty] = $person;
//         }
//      }
//      $out['prg']['meta']['last'] = date("Y-m-d", strtotime($event['date']));
//
//      if ($num > 0)
//      {
//          // create json file
//          if (is_writable($this->programme_file))
//          {
//             $fp = fopen($this->programme_file, 'w');
//             fwrite($fp, json_encode($out));
//             fclose($fp);
//             return $num;
//          }
//          else
//          {
//             return false;
//          }
//
//      }
//      else
//      {
//          return false;
//      }
//   }
   
    private function import_programme($loc)
    // reads json file created with export_programme and creates session array
    {
        $status = false;
        if (is_readable($this->programme_file))
        {
            // get absolute file path for json file
            $absolute_file = substr_replace($loc,'', strrpos($loc, '/')).ltrim($this->programme_file, ".");
            //echo "<pre>$absolute_file</pre>";
            // get contents - use dummy query to prevent caching
            $string = file_get_contents($absolute_file."?dev=".rand(1,1000));
            $_SESSION['prg'] = json_decode($string, true);
            
            $today = new DateTime(date("Y-m-d"));

            // add event state where required
            $next_set = false;
            foreach($_SESSION['prg']['events'] as $eventid => $event)
            {
                $edate = new DateTime(date("Y-m-d", strtotime($event['date'])));
                if ($edate >= $today)
                {
                    if ($next_set)
                    {
                        if ($event['state'] != "trophy" and $event['state'] != "open" and
                            $event['state'] != "important" and $event['state'] != "noevent")
                        {
                            $_SESSION['prg']['events']["$eventid"]['state'] = "future";
                        }
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
            $status = true;
        }
        else
        {
            u_writelog("programme: events inventory file not found or not readable or empty [{$this->programme_file}]");
            $status = false;
        }
        return $status;
    }

   
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
               foreach($value as $role=>$duty)
               {
                  if (stripos($duty['person'], $needle) !== FALSE OR stripos($duty['duty'], $needle))
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
         $date = new DateTime($event['date']);
         if ($date >= $search_start AND $date < $search_end)    // note assumes end date is first date after search period
         {
             $events[$k]=$event;
         }
      }
      return $events;
   }
}
