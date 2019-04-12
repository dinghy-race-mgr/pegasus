<?php
/**
 *  SERIES RESULT class
 * 
 *  Handles production of series result - based on data in t_results.
 *  Supports merging of classes (e.g. lasers) as an option
 *  Supports three different average point schemes
 *  Supports tie breaking as defined in Appendix A.8 of the RRS
 *  Produces unformatted and formatted output
 * 
 *  METHODS
 *     __construct
 * 
 *
*/

/*
ISSUES -
2 - test tie resolution
3 - add merging
4 - importer code (series_data) from results table
5 - add template management as htmTemplate class
6 - produce formatted output (but not file)



*/

/**
 * Class SERIES
 */
//session_start();
//include ("./db_class.php");
//include ("../lib/util_lib.php");
//$_SESSION['db_host'] = "localhost";
//$_SESSION['db_user'] = "root";
//$_SESSION['db_pass'] = "";
//$_SESSION['db_name'] = "pegasus";
//
//$eventid = 1000;
//$merge_classes = array();
//$db_o = new DB;
//$rs = new SERIES($db_o, $eventid, $merge_classes);
//
//echo "====== STARTING ======<br>";
//
//$status = $rs->series_result_html($event_list = array(1000,1001,1002,1003,1004), false, "");
//
//echo "<br>====== RETURN ======<br>status: $status<br>error: {$rs->error_msg}";


class SERIES
{
    private $db;

    //Method: construct class object
    public function __construct(DB $db, $eventid, $merge_classes)
    {
        //FIXME - why do I need $eventid??
        //FIXME - get merge classes from database internally
        include

        $this->db = $db;
        $this->result = array("success" => false, "err" => "", "output" => "");
        $this->opts = array();
        
        $this->status_run = array("complete", "abandoned", "cancelled");   // fixme check that this is complete
        $this->status_complete = array ("complete");                       // fixme ditto

        $this->races_inseries_num = 0;
        $this->races_run_num = 0;
        $this->races_complete_num = 0;

        $this->club = array(
            "clubname" => u_getitem($_SESSION['clubname'], "myclub"),
            "clubcode" => u_getitem($_SESSION['clubcode'], "MySC"),
            "cluburl"  => u_getitem($_SESSION['cluburl'], "www.myclub.org"),
        );


//        isset($_SESSION['clubname'])? $this->club['clubname'] = $_SESSION['clubname']: $this->club['clubname'] = "";
//        isset($_SESSION['clubcode'])? $this->club['clubcode'] = $_SESSION['clubcode']: $this->club['clubcode'] = "";
//        isset($_SESSION['cluburl'])? $this->club['cluburl'] = $_SESSION['cluburl']: $this->club['cluburl'] = "";

        $this->series = array();                // series information
        $this->fleets = array();                // information for each fleet in series
        $this->races  = array();                // information for each race in series
        $this->sailor = array();                // information for each sailor in series
        $this->rst    = array();                // result information for each sailor/race
        $this->codes_used = array();            // array of codes used in series results
        $this->merge_classes = $merge_classes;
        /* 2d array of groups of classes to be merged
               $merge_classes = array(
                     "laser" => array ("laser", "laser 4.7", "laser radial")
                     "rs100" => array ("rs100 8.4", "rs100 10.2")
                     )
        */
    }


    public function series_result($series_code, $options, $event_list = array())
    {
        # set default options if not set
        if (!array_key_exists("race_cfg", $options) or !is_bool($options['race_cfg']))
        {
            $options['race_cfg'] = true;
        }

        if (!array_key_exists("merge", $options) or !is_bool($options['merge']))
        {
            $options['merge'] = false;
        }

        if (!array_key_exists("club", $options) or !is_bool($options['club']))
        {
            $options['club'] = true;
        }
        $this->opts = $options;


        // get series information
        $event_o = new EVENT($this->db);
        $series = $event_o->event_getseries($series_code);
        if (empty($series))
        {
            $this->result["err"].= "series code not found in raceManager database<br>";
            $this->result["success"] = false;
        }
        else
        {
            // get list of event ids to be included in series if not provided as argument
            if (empty($event_list))
            {
                $event_list = $event_o->series_eventlist($series_code);
            }
            $this->races_inseries_num = count($event_list);

            // check race_cfg is consistent for each event in list - if requested
            if ($options['race_cfg'])
            {
                $i = 0;
                $race_cfg = "";
                foreach ($event_list as $event)
                {
                    $i++;
                    $event_o->event_getevent($event['id']);     // get event
                    if ($i == 1)
                    {
                        $race_cfg = $event['event_format'];     // set standard configuration for this series
                    }
                    else
                    {
                        if ($race_cfg != $event['event_format'])
                        {
                            $this->result["err"].= " the events for series do not have a consistent race format<br>";
                            $this->result["success"] = false;
                        }
                    }
                }
            }
        }

        // derive series result from event list
        if (!empty($event_list))
        {
            $this->result['output'] = $this->calculate_series_result($event_list, $options['merge']);

            $this->result["err"].= "";
            $this->result["success"] = true;

            // get display data structure together
            $display = $this->get_series_result_info($this->result['output']);

            // produce html file if requested

            // else create json file

        }
        else
        {
            $this->result["err"].= " no races defined for requested series<br>";
            $this->result["success"] = false;
        }

        return $this->result;
    }


    private function get_series_result_info($rst)
    {
        $d = array();

        // get banner
        $d['banner'] = array(
            "club_name" => $this->club['clubname'],
            "club_logo" => "",
            "club_url"  => $this->club['cluburl'],
            "title"     => ""
        );

        // get messages
        $d['info'] = array(
            "notes"      => "",
            "last_race"  => "",
            "sailed"     => "",
            "not_sailed" => "",
            "to_count"   => "",
            "err_report" => ""
        );

        // get race info
        $d['races'] = array(
            1 => array(
                "name" => "",
                "date" => "",
                "status" => "",
                "rst_file" => ""
            ),

        );

        // loop over fleets
        $d['fleets'] = array(
            1 => array(
                "name" => "",
                "entries" => "",
                "max_entries" => "",
                "min_entries" => "",
                "avg_entries" => "",
                "scoring" => "",
                "rst" => array(
                    1 => array(
                        "posn"  => "",
                        "class" => "",
                        "snum"  => "",
                        "team"  => "",
                        "club"  => "",
                        "tpts"  => "",
                        "npts"  => "",
                        "rdata" => array(
                            1 => "<span class='count'>3</span>",
                            2 => "<span class='discard'>5<span class='code'>/AVG</span></span><span>",
                        ),

                    )
                )

            )

        );

        // get code info
        $d['codes'] = array(
            1 => array(
                "code" => "",
                "text" => "",
                "expr" => ""
            ),

        );

        $d['notes'] = array(
            1 => "",
        );

        // get system footer info
        $d['system'] = array(
            "name" => $_SESSION['sys_name'],
            "vers" => $_SESSION['sys_version'],
            "logo" => $_SESSION['sys_logo'],
            "url"  => $_SESSION['sys_website'],
            "date" => date("js M Y H:i"),    // creation date
        );

        return $d;

    }


    public function calculate_series_result($event_list, $merge)
    {
        // get race and result data into object data
        $this->series_data($event_list);                       // dummy created
        
        $this->series_races_sailed();
        
        if ($this->races_complete_num <= 0)
        {
           $this->result['err'].= "no races completed in this series";
           return "";
        }
        
        $this->merge_sailors($this->merge_classes);          // TODO
        
        $this->count_sailors();                              // done
        
        $this->score_dnc();                                  // done
        
        $this->series_discard();
        
        $this->score_avg();                                 // done but need to premliminary discard analysis
        
        $this->series_discard();                            // required because avg score might change discards

        $this->series_points();

        $output = $this->series_score();


        // create

//        echo "FINAL RESULTS ---:";
//        echo "<table border='1'>";
//        foreach ($output as $k=>$sailor)
//        {
//            echo <<<EOT
//            <tr>
//            <td>{$sailor['class']}</td>
//            <td>{$sailor['sailno']}</td>
//            <td>{$sailor['helm']}</td>
//            <td>{$sailor['club']}</td>
//EOT;
//            foreach ($sailor['rst'] as $p=>$result)
//            {
//                $str = $result['pts'];
//
//                if (!empty($result['code'])) { $str.= " ({$result['code']})"; }
//                if ($result['discard']) { $str = "<span style=\"text-decoration: underline;\">$str</span>"; }
//                echo "<td width='100px'>$str</td>";
//
//            }
//            echo "<td width='100px'>{$sailor['tot_pts']}</td>";
//            echo "<td width='100px'>{$sailor['net_pts']}</td>";
//            echo "<td width='100px'>{$sailor['posn']}</td>";
//            echo "</tr>";
//        }
//        echo "</table><br> -----------";

        return true;

    }

    public function render_series_result($series, $loc, $result_status, $include_club, $results, $stylesheet)
    {
        global $result_o;
        global $tmpl_o;
        $htm = "";

        // get system info
        if (is_readable("$loc/config/racemanager_cfg.php"))   // set racemanager config file content into SESSION
        {
            include("$loc/config/racemanager_cfg.php");
        }
        else
        {
            $_SESSION['sys_name'] = "raceManager";                                   // name of system
            $_SESSION['sys_release'] = "";                                           // release name
            $_SESSION['sys_version'] = "";                                           // code version
            $_SESSION['sys_copyright'] = "Elmswood Software " . date("Y");           // copyright
            $_SESSION['sys_website'] = "http://dinghyracemanager.wordpress.com/";    // website
        }

        // get information on codes used
        $result_codes = $this->db->db_getresultcodes("result");
        $codes_info = u_get_result_codes_info($result_codes, $this->codes_used);

//        // get club info
//        $club = $this->db->db_getinivalues(true);
//
//
//
//        // get info for each event in series
//
//        $num_events = count($event);
//
//        // get fleet information and reindex
//        $fleet = $this->db->db_get_rows(
//            "SELECT * FROM t_cfgfleet WHERE eventcfgid = {$event['event_format']} ORDER BY start_num, fleet_num");
//        array_unshift($fleet, null);
//        unset($fleet[0]);
//        $num_fleets = count($fleet);
//
//        // get codes used in results
//
//        // get code information for codes used
//        $codes_info = $result_o->get_result_codes_used(array_unique($codes_used));
//        //u_writedbg("<pre>".print_r($codes_info,true)."</pre>", __FILE__, __FUNCTION__, __LINE__); //debug:);

        $params = array(
            "club_name"     => $this->club['clubname'],
            "series_name"   => $this->series['name'],
            "result_status" => $result_status,
            "sys_website"   => $_SESSION['sys_website'],
            "sys_name"      => $_SESSION['sys_name'],
            "sys_version"   => $_SESSION['sys_version'],
            "page_title"    => "raceManager series result",
            "races_run"     => strval($this->races_run_num),
            "races_complete"  => strval($this->races_complete_num),
            "races_inseries"  => strval($this->races_inseries_num),
        );

        $data = array(
            "style"         => "$loc/style/$stylesheet",
            "pagination"    => $this->club['result_pagination'],
            "add_codes"     => $this->club['result_addcodes'],
            "inc_club"      => $include_club,
            "inc_codes"     => $codes_info,
            "num_race"      => $this->races_inseries_num,
            "fleet"         => $this->fleets,
            "races"         => $this->races,
            "result"        => $results,
        );

//        u_writedbg("<pre>" . print_r($params, true) . "</pre>", __FILE__, __FUNCTION__, __LINE__); //debug:);
//        u_writedbg("<pre>" . print_r($data, true) . "</pre>", __FILE__, __FUNCTION__, __LINE__); //debug:);

        $htm = $tmpl_o->get_template("series_sheet", $params, $data);





        return $htm;
    }






    private function series_data($eventlist)
    {
        /*
         * Puts result data into 2D array and race data into separate data
         * Change fleets to 1 if $keep_raccfg is false and fix fleet to 1
         * result  compid, raceid, (fleet, pos, code, discardable, multiplier, discard, score, net, totel)
         * race:  id, order, date, name
         * assume all formatting has already been done on names, club etc
         */

         // IMPORTANT to decide whether this data includes a null record for races not sailed
         // should be array for races and another for fleets (including total no. of races, no. sailed, no. to count)

        // get races in series


        $testdata = array(
            "club" => array(
                "clubname" => "Starcross Yacht Club",
                "clubcode" => "SYC",
                "cluburl"  => "www.starcrossyc.org.uk",
                "result_pagination" => true,
                "result_addcodes" => true,
            ),
            "series" => array(
                'name'       => "Summer Series",
                'discard'    => "0,0,1,1",
                'avg_scheme' => "avg_raced"
            ),
            "fleets" => array(
                1 => array ('name' => "monohull", 'num_entries'=> 4),
                2 => array ('name' => "multihull", 'num_entries'=> 0),
            ),
            "races" => array(
                1 => array('eventid' => 1000, 'date' => "2016-06-01", 'name' => "1", 'no_discard'=> 0, 'multiplier' => 1, 'status'=> "complete", 'notes' => ""),
                2 => array('eventid' => 1001, 'date' => "2016-06-08", 'name' => "2", 'no_discard'=> 0, 'multiplier' => 1, 'status'=> "complete", 'notes' => ""),
                3 => array('eventid' => 1002, 'date' => "2016-06-15", 'name' => "3", 'no_discard'=> 0, 'multiplier' => 1, 'status'=> "complete", 'notes' => ""),
                4 => array('eventid' => 1003, 'date' => "2016-06-22", 'name' => "4", 'no_discard'=> 0, 'multiplier' => 1, 'status'=> "complete", 'notes' => ""),
                5 => array('eventid' => 1004, 'date' => "2016-06-22", 'name' => "4", 'no_discard'=> 0, 'multiplier' => 1, 'status'=> "abandoned", 'notes' => "too windy"),
            ),
            "sailor" => array(
                1 => array('id' => 901 , 'helm' => "Mark Elkington", 'crew' => "Sarah Roberts", 'club' => "Starcross YC", 'class' => "Merlin Rocket", 'sailno' => "3718", 'fleet' => 1, 'pn' => 991, 'note' => "only one leg"),
                2 => array('id' => 902 , 'helm' => "David Bartlett", 'crew' => "", 'club' => "Starcross YC", 'class' => "D-zero", 'sailno' => "144", 'fleet' => 1, 'pn' => 1030, 'note' => ""),
                3 => array('id' => 903 , 'helm' => "David Lee", 'crew' => "Hannah Jones", 'club' => "Starcross YC", 'class' => "Merlin Rocket", 'sailno' => "3792", 'fleet' => 1, 'pn' => 991, 'note' => ""),
                4 => array('id' => 904 , 'helm' => "Dick Garry", 'crew' => "Sam Woolner", 'club' => "Starcross YC", 'class' => "Hornet", 'sailno' => "2164", 'fleet' => 1, 'pn' => 963, 'note' => ""),
            ),
            "rst" => array(
                1 => array(
                    "r1" => array('pts' => 2, 'code' => "", 'discard' => false, 'sort' => 2, 'exclude' => false),
                    "r2" => array('pts' => 0, 'code' => "DNC", 'discard' => false, 'sort' => 9999, 'exclude' => false),
                    "r3" => array('pts' => 1, 'code' => "", 'discard' => false, 'sort' => 1, 'exclude' => false),
                    "r4" => array('pts' => 2, 'code' => "", 'discard' => false, 'sort' => 2, 'exclude' => false)
                ),
                2 => array(
                    "r1" => array('pts' => 1, 'code' => "", 'discard' => false, 'sort' => 1, 'exclude' => false),
                    "r2" => array('pts' => 1, 'code' => "", 'discard' => false, 'sort' => 1, 'exclude' => false),
                    "r3" => array('pts' => 2, 'code' => "", 'discard' => false, 'sort' => 2, 'exclude' => false),
                    "r4" => array('pts' => 4, 'code' => "", 'discard' => false, 'sort' => 4, 'exclude' => false)
                ),
                3 => array(
                    "r1" => array('pts' => 3, 'code' => "", 'discard' => false, 'sort' => 3, 'exclude' => false),
                    "r2" => array('pts' => 0, 'code' => "DNC", 'discard' => false, 'sort' => 9999, 'exclude' => false),
                    "r3" => array('pts' => 6, 'code' => "OCS", 'discard' => false, 'sort' => 6, 'exclude' => false),
                    "r4" => array('pts' => 3, 'code' => "", 'discard' => false, 'sort' => 3, 'exclude' => false)
                ),
                4 => array(
                    "r1" => array('pts' => 0, 'code' => "DNC", 'discard' => false, 'sort' => 9999, 'exclude' => false),
                    "r2" => array('pts' => 13, 'code' => "DNE", 'discard' => false, 'sort' => 0, 'exclude' => false),
                    "r3" => array('pts' => 2, 'code' => "", 'discard' => false, 'sort' => 2, 'exclude' => false),
                    "r4" => array('pts' => 1, 'code' => "", 'discard' => false, 'sort' => 1, 'exclude' => false),
                )
            ),
        );

        $this->club = $testdata['club'];
        $this->series = $testdata['series'];
        $this->fleets = $testdata['fleets'];
        $this->races = $testdata['races'];
        $this->sailor = $testdata['sailor'];
        $this->rst = $testdata['rst'];

        foreach($this->rst as $k=>$sailor)
        {
            foreach ($this->sailor as $j=>$row)
            {
                if (!empty($row['code'])) { $this->codes_used[] = $row['code']; }
            }
        }

    }
    
//    private function series_debug($checkpoint, $results_only)
//    {
//        echo "<br><br><b>------- position: [$checkpoint] ------</b><br>";
//
//        if (!$results_only)
//        {
//           echo "club ---:<br>".print_r($this->club,true)."<br><br>";
//           echo "series ---:<br>".print_r($this->series,true)."<br><br>";
//           echo "fleets ---:<br>".print_r($this->fleets,true)."<br><br>";
//           echo "races ---:<br>".print_r($this->races,true)."<br><br>";
//        }
//        echo "RESULTS ---:";
//        echo "<table border='1'>";
//        foreach ($this->sailor as $k=>$sailor)
//        {
//            echo <<<EOT
//            <tr><td>{$sailor['class']} {$sailor['sailno']} - {$sailor['helm']}</td>
//EOT;
//            foreach ($this->rst[$k] as $p=>$result)
//            {
//                $str = $result['pts'];
//
//                if (!empty($result['code'])) { $str.= " ({$result['code']})"; }
//                if ($result['discard']) { $str = "<span style=\"text-decoration: underline;\">$str</span>"; }
//                echo "<td width='100px'>$str</td>";
//
//            }
//            echo "<td width='100px'>{$sailor['tot_pts']}</td>";
//            echo "<td width='100px'>{$sailor['net_pts']}</td>";
//            echo "<td width='100px'>{$sailor['posn']}</td>";
//            echo "</tr>";
//        }
//        echo "</table><br> -----------";
//    }
    
    private function series_races_sailed()
    {
       $this->complete_count = 0;
       $this->run_count = 0;
       foreach ($this->races as $race)
       {
           if (in_array($race['status'], $this->status_run))
           {
              $this->run_count++;
           }
           if (in_array($race['status'], $this->status_complete))
           {
              $this->complete_count++;
           }
       }
    }

    private function merge_sailors($merge_classes)
        /**
         * for each merge class - looks through results and merges the results for each competitor sailing any
         * classes within the group in the same fleet.  The results are merged into the first class in the group
         */
    {
            /*
            data structure
            merge-groups = array("lead class"=>array("class1", "class2", "class3"))

            create list of all master classes to be merged - merge-list
            loop over competitors (I)
                if competitor is sailing a class in merge-list
                    loop through all other competitors (J) (after this one)
                         merge results from (J) into (I)
                         delete competitor J
                    end loop
                    change class for competitor I to lead class in group
                endif
            end loop
            */

        return true;
    }
    
    private function count_sailors()
        /**
         * gets number of competitors in each fleet
         */
    {
        $count = array_count_values($this->array_column($this->sailor, 'fleet'));

        foreach($this->fleets as $k=>$fleet)
        {
            if (isset($count[$k]))
            {
                $this->fleets[$k]['nsailors'] = $count[$k];
            }

            else
            {
                $this->fleets[$k]['nsailors'] = 0;
            }
        }
    }    

    private function score_dnc()
    {
    /**
     * for each fleet calculate points for unscored races as (DNC) - and add scores to result records
     * uses code expression for DNC - if no code or default to numsailors + 1
    */
        $code_func = "";
        $code = $this->db->db_getresultcode("DNC");
        if (!empty($code['scoring']))
        {
            // check func only contains allowed characters (S, 0-9, arithmetic operators, white space)
            if (preg_match("/[^0-9().S\\s +\\-+*\\/]/", $code['scoring']) != 1) { $code_func = $code['scoring']; }
        }

        if (empty($code_func))
        {
            $code_func = "S + 1";
        }

        $dnc_score = array();
        foreach ($this->fleets as $k=>$fleet)
        {
            $code_func = str_replace("S", $fleet['nsailors'], $code_func);
            $dnc_score[$k] = eval("return ".$code_func.";");
        }
        
        // loop through sailors setting the DNC values to the appropriate value
        foreach ($this->sailor as $s_id=>$sailor)
        {
           // loop through results for this sailor
           foreach ($this->rst[$s_id] as $r=>$result)
           {
              if ($result['code'] == "DNC")
              {
                 $this->rst[$s_id][$r]['pts'] = $dnc_score[$sailor['fleet']];
                 $this->rst[$s_id][$r]['sort'] = $dnc_score[$sailor['fleet']];
              }           
           }        
        }
    }

    private function score_avg()
    {
    /** Average points options
       avg_races - average of all races
       avg_raced - average of all non-dnc races
       avg_counting - average of all non-discarded races (tricky as the race may then replace a discarded one)
    */

        // calculate average score for each sailor according to scheme configured for this series
        foreach ($this->sailor as $s_id=>$sailor)
        {
           // loop through results for this sailor
           $score_sum = 0;
           $score_count = 0;
           foreach ($this->rst[$s_id] as $k=>$result)
           {
              if ($this->series['avg_scheme'] == "avg_races")
              {
                  $score_count++;
                  $score_sum = $score_sum + $result['pts'];              
              }
              elseif ($this->series['avg_scheme'] == "avg_raced")
              {
                  if ($result['code'] != "DNC" AND $result['code'] != "AVG")
                  {
                      $score_count++;
                      $score_sum = $score_sum + $result['pts'];              
                  }
              }
              elseif  ($this->series['avg_scheme'] == "avg_counting")
              {
                  /* FIXME need to work out discarded races before doing this */
                  if (!$result['discard'])
                  {
                      $score_count++;
                      $score_sum = $score_sum + $result['pts']; 
                  }              
              }          
           }  
           
           // set average for this sailor
           if ($score_count == 0)
           {
              $average = 0;
           }
           else
           {
              $average = round($score_sum/$score_count, 1);
           }
           $this->sailor[$s_id]['average'] = $average;
           
           // apply this score to relevant results
           foreach ($this->rst[$s_id] as $k=>$result)
           {
               if ($result['code'] == "AVG")
               {
                   if ($average != 0)    // FIXME what if only scoring race is AVG - needs to be set to DNC
                   {
                      $this->rst[$s_id][$k]['pts'] = $average;
                   }               
               }           
           }           
        }
    }


    private function series_discard()
    {
        // get number to count
        $profile = explode(",", $this->series['discard']);
        $num_to_count = $this->complete_count - $profile[$this->complete_count - 1];
        
        // apply discards for each sailor's results
        foreach ($this->sailor as $s_id=>$sailor)
        {
            // sort results in ascending order based on sort attribute
            $data = $this->rst[$s_id];
            $sort = array();
            foreach ($data as $key=>$row)
            {
                $sort[$key]  = $row['sort'];
            }
            array_multisort($sort, SORT_ASC, $data);

            // mark discarded races with discard flag
            $count = 0;
            foreach ($data as $race=>$detail) // FIXME check this works with DNE
            {
                $count++;
                if (strtoupper($detail['code']) != "DNE")
                {
                    if ($count > $num_to_count)
                    {
                        $this->rst[$s_id][$race]['discard'] = true;
                    }
                }
            }
        }
    }


    private function series_points()
    {
        foreach ($this->sailor as $s_id=>$sailor)
        {
            $series_total_pts = 0.0;
            $series_net_pts = 0.0;
            foreach ($this->rst[$s_id] as $k=>$result)
            {
                $series_total_pts = $series_total_pts + $result['pts'];
                if (!$result['discard'])
                {
                    $series_net_pts = $series_net_pts + $result['pts'];
                }
            }
            $this->sailor[$s_id]['net_pts'] = $series_net_pts;
            $this->sailor[$s_id]['tot_pts'] = $series_total_pts;
        }
    }


    private function series_score()
    {
        $output = array();

        // loop over fleets
        foreach($this->fleets as $k=>$v)
        {
            // select sailors in this fleet
            $sailor = array_filter($this->sailor, function ($ar) use ($k){
                return ($ar['fleet'] == $k);
            });

            // sort by net points ASC
            uasort($sailor, function ($a, $b) {
                return $a['net_pts'] - $b['net_pts'];
            });

            // allocate position for each sailor (maintain ties)
            $posn = 0;
            foreach($sailor as $i=>$row)
            {
                $posn++;
                $sailor[$i]['posn'] = $posn;
            }

            // resolve ties in this fleet
            $num_switches = $this->resolve_ties($sailor, "isaf_a8");
            if ($num_switches > 0)
            {
                // sort by position
                uasort($sailor, function ($a, $b) {
                    return $a['posn'] - $b['posn'];
                });
            }

            // create output array (class, sailnum, helm/crew, club, r1 ... rn, total, net, posn, notes)
            $output[$k] = array();
            $rownum = 0;
            foreach($sailor as $i=>$row)
            {
                // add information back to object array
                $this->sailor[$i] = $row;

                // create output array
                $rownum++;
                $output[$k][$rownum] = array(
                    "class"   => $row['class'],
                    "sailnum" => $row['sailno'],
                    "team"    => u_getteamname($row['helm'], $row['crew']),
                    "club"    => $row['club'],
                    "total"   => $row['tot_pts'],
                    "net"     => $row['net_pts'],
                    "posn"    => $row['posn'],
                    "note"    => $row['note'],
                );
                if (!$this->opts['club']) { unset($output[$k][$rownum]['club']); }

                foreach($this->rst[$i] as $j=>$result)
                {
                    //echo "RESULT - $i - $j: ".print_r($result, true)."<br>";

                    $output[$k][$rownum]['rst'][$j]= array(
                        "result"  => $result['pts'],
                        "code"    => $result['code'],
                        "discard" => $result['discard'],
                    );

                   // echo "OUTPUT: ".print_r($output[$k][$rownum]['rst'][$j], true)."<br>";
                }
            }
        }
//        $this->series_output_debug($output);
        return $output;
    }


//    private function series_output_debug($output)
//    {
//        echo "<br><br><b>------- SERIES OUTPUT ------</b><br>";
//
//        foreach ($output as $j=>$fleet)
//        {
//            echo "<h2>Fleet $j</h2>";
//            echo "<table>";
//            echo <<<EOT
//            <thead>
//               <td>class</td>
//               <td>sail no.</td>
//               <td>team</td>
//               <td>club</td>
//               <td>race 1</td>
//               <td>race 2</td>
//               <td>race 3</td>
//               <td>race 4</td>
//               <td>total pts</td>
//               <td>net pts</td>
//               <td>position</td>
//            </thead>
//EOT;
//            foreach ($fleet as $k=>$sailor)
//            {
//                echo "<tr>";
//                echo "<td>".$sailor['class']."</td>";
//                echo "<td>".$sailor['sailnum']."</td>";
//                echo "<td>".$sailor['team']."</td>";
//                echo "<td>".$sailor['club']."</td>";
//                foreach($sailor['rst'] as $n=>$result)
//                {
//                    $str = $result['result'];
//                    if (!empty($result['code'])) { $str.= " ({$result['code']})"; }
//                    if ($result['discard']) { $str = "<span style=\"text-decoration: underline;\">$str</span>"; }
//                    echo "<td width='100px'>$str</td>";
//                }
//                echo "<td width='100px'>{$sailor['total']}</td>";
//                echo "<td width='100px'>{$sailor['net']}</td>";
//                echo "<td width='100px'>{$sailor['posn']}</td>";
//                echo "<td width='100px'>{$sailor['note']}</td>";
//                echo "</tr>";
//            }
//            echo "</table>";
//            echo "------- end ----------------------------------------------------------";
//        }
//    }

    private function series_final_data()
    {
        $data = array();
        return $data;
    }

    public function series_html($style, $data)
    {
        $bufr = "";
        return $bufr;
    }

    
    private function array_column($data, $key)
    {
        $column = array();
        foreach($data as $origKey => $value)
        {
           if (isset($value[$key])) 
           {
              $column[$origKey] = $value[$key];
           }            
        }
        return $column;
    }

    private function resolve_ties($sailor_sorted, $mode)
    {
        if ($mode == "isaf_a8")
        {
            $num_switches = 0;
            $switches = true;
            while ($switches)   // iterate until no more ties
            {
                $switches = false;
                $prev_id = 0;                               # for previous sailor
                $prev_pts = 0;                              # net points for previous sailor
                $prev_non_discard = array();                # array of non-excluded race results for previous sailor -
                $prev_inc_discard = array();                # array of non-excluded race results for previous sailor
                $curr_non_discard = array();                # array of all race results for current sailor
                $curr_inc_discard = array();                # array of all race results for current sailor

                foreach($sailor_sorted as $i=>$row)
                {
                    foreach($this->rst[$i] as $k=>$result)
                    {
                        $curr_inc_discard[] = $result['pts'];
                        if (!$result['discard']) { $curr_non_discard[] = $result['pts']; }
                    }

                    if ($row['net_pts'] == $prev_pts)  // tie found
                    {
                        // check if ISAF A8.1 resolves it - returns -1 if PREVIOUS wins, +1 if CURRENT wins, 0 if still a tie
                        // checks no. of wins, 2nds, 3rds etc - ignoring discarded results
                        $tie = $this->resolve_tie_A81($prev_non_discard, $curr_non_discard);

                        // not resolved try ISAF A8.2 - put results in reverse order and find first mismatch include excluded results
                        if ($tie == 0)
                        {
                            $tie = $this->resolve_tie_A82($prev_inc_discard, $curr_inc_discard);
                        }

                        // change positions
                        if ($tie == 1)
                        {
                            $this->switch_positions($i, $prev_id);
                            $switches = true;
                            $num_switches++;
                        }
                    }
                    $prev_id = $i;
                    $prev_pts = $row['net_pts'];
                    $prev_non_discard = $curr_non_discard;
                    $prev_inc_discard = $curr_inc_discard;
                }
            }
        }
        else
        {
            // mode not recognised - return false
            return false;
        }
        return $num_switches;
    }


    private function resolve_tie_A81($prev, $curr)
    {
        $result = 0;
        // sort in posn order (ASC) - change keys
        sort($prev);
        sort($curr);

        // loop through arrays and compare
        foreach ($prev as $k=>$posn)
        {
            if ($posn > $curr[$k])
            {
                $result = -1;
                break;
            }
            elseif ($curr[$k] > $posn)
            {
                $result = 1;
                break;
            }
        }
        return $result;
    }


    private function resolve_tie_A82($prev, $curr)
    {
        // put arrays in reverse order and find first mismatch
        array_reverse ( $prev , true );
        array_reverse ( $curr , true );
        foreach ($curr as $k=>$posn)
        {
            if ($posn < $prev[$k])
            {
                return 1;
            }
            elseif ($prev[$k] < $posn)
            {
                return -1;
            }
        }
        return 0;
    }


    private function switch_positions($prev_id, $this_id)
    {
        $switch_posn = $this->sailor[$prev_id]['posn'];
        $this->sailor[$prev_id]['posn'] = $this->sailor[$this_id]['posn'];
        $this->sailor[$this_id]['posn'] = $switch_posn;
    }







// ------------------------------------

    public function get_series_filename($series_code)
    {
        // series file is the series code in the event record (i.e including year) with any
        // non-standard characters removed
        $filename = preg_replace('/[^a-zA-Z0-9\-\._]/', '', $series_code);
        return $filename.".htm";
    }




    public function create_resultcodes($mode, $resultcodes)
    {
        // FIXME needs looking at
        // get codes into html bufr
        $bufr = <<<EOT
        <div style="margin-left:40px;"><table>
            <thead>
                <th class="lightshade" >Code</th>
                <th class="lightshade" >Meaning</th>
                <th class="lightshade" >Scoring</th>
            </thead>
            <tbody>
EOT;
        foreach ($resultcodes as $key => $row) {
            $trans = array("N" => "race competitors", "S" => "series competitors", "P" => "position");
            $scoring = strtr($row['scoring'], $trans);
            $scoring = "[$scoring]";

            $bufr .= <<<EOT
            <tr style="vertical-align: top;" >
                <td class="text-center text-alert" ><b>{$row['code']}</b></td>
                <td>{$row['info']}</td>
                <td class="text-grey"><i>$scoring</i></td>
            </tr>
EOT;
        }
        $bufr .= "</tbody></table></div>";

        return $bufr;
    }


    private function get_raceresults($eventid, $fleetnum)
    {
        $where = "";
        if ($fleetnum) {
            $where = " AND fleet = $fleetnum ";
        }

        $results = $this->db->db_get_rows("SELECT *, helm as team, points as result FROM t_result WHERE eventid=$eventid $where ORDER BY fleet, points ASC, pn, class, sailnum+0");

        foreach ($results as $key => $result) {
            $results[$key]['team'] = u_conv_team($result['helm'], $result['crew']);
            $results[$key]['result'] = u_conv_result($result['code'], $result['points']);
            $results[$key]['etime'] = u_conv_secstotime($result['etime']);
            $results[$key]['ctime'] = u_conv_secstotime($result['ctime']);
            $results[$key]['atime'] = u_conv_secstotime($result['atime']);
        }

        return $results;
    }

    private function get_resultcodes($codes_used = array())
    {
        // FIXME needs looking at it - I aslo do this for race result
        $results = $this->db->db_get_rows("SELECT * FROM t_code_result ORDER BY code");
        if (empty($codes_used))
        {
            return $results;
        }
        else  // only include result codes that are used
        {
            $output = array();
            foreach ($results as $result) {
                if (in_array($result['code'], $codes_used)) {
                    $output[] = $result;
                }
            }
            return $output;
        }
    }

}


?>
