<?php
/**
 * RESULTS_LIB.PHP - function library for results related functionality
 * 
 * @function    listScoringCodes   lists scoring codes used in race results   
 * @function    createRaceResults  produces results output for a single race (multiple fleets)  
 *    
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 * 
 */
/* ----------------------------------------------------------------------------------------------------*/ 

/**
 * listScoringCodes
 * 
 * Produces list of scoring codes held in table t_code_result
 *  
 * @param   string  $stylesheet    pathname to include file for stylesheet
 * 
 * @global  array   $lang          language text
 * @global  array   $appname       application name
 * 
 * @return  string  $bufr          html buffer
 * 
 * @todo  sort out codes that have manual/avg scoring
 */
function s_listScoringCodes($stylesheet)
{
    global $lang;
    global $loc;
        
    // create database object
    $db = new DB();
    
    // get results for this race in fleet and position order
    $results = $db->db_get_rows("SELECT * FROM t_code_result ORDER BY code");  
    
    // get codes into html bufr
    $abufr = <<<EOT
        <div><table width=90% align=center>
            <thead>
                <th class="lightshade" style="width: 10%;">{$lang['words']['codes']}</th>
                <th class="lightshade" style="width: 60%;">{$lang['words']['meaning']}</th>
                <th class="lightshade" style="width: 30%;">{$lang['words']['scoring']}</th>
            </thead>
            <tbody>
EOT;
    foreach ($results as $key=>$row)
    {
        $trans = array("N" => "{$lang['result']['competitorsinrace']}", "S" => "{$lang['result']['competitorsinseries']}", "P" =>"{$lang['words']['position']}");
        $scoring = strtr($row['scoring'],$trans);
        $scoring = "[$scoring]";
        
        $abufr.= <<<EOT
        <tr style="vertical-align: top;" >
            <td class="lightshade text-center text-alert" ><b>{$row['code']}</b></td>
            <td>{$row['info']}</td>
            <td class="text-grey"><i>$scoring</i></td>
        </tr>
EOT;
    }
    $abufr.= "</tbody></table></div>";
    
    $title = ucwords($lang['words']['scoring']." ".$lang['words']['codes']);
    $hbufr = <<<EOT
        <div class="title">$title</div>
        <div class="divider clearfix"></div>
EOT;
    $create_date = date("D j M y H:i");
    $fbufr = $fbufr = <<<EOT
        <div class="divider clearfix"></div>
        <p><a href="{$_SESSION['sys_website']}">{$_SESSION['sys_name']} {$lang['words']['results']}:</a> $create_date</p>
        <br>
EOT;
 

    // create html object
    $html = new HTMLPAGE($_SESSION['lang']);                            // create html page object
    $html->html_addinclude("$loc/rm_racebox/include/rm_export.inc");            // html header section with embedded styles styles
    $html->html_body("");                                               // page body statement
    $html->html_addhtml($hbufr);                                        // page title
    $html->html_addhtml($abufr);                                        // abbreviations
    $html->html_addhtml($fbufr);                                        // footer with divider                              
    $bufr = $html->html_render();                                       // return page
    
    return $bufr;   
}



 

 
/**
 * createRaceResults
 * 
 * Generates html string for a race using data stored in t_results
 * 
 * @param string  $eventid       event id
 * @param array   $tablecols     array of column information
 * @param string  $tablequery    string query to get results from t_results
 * @param array   $fleet         array of information for each fleet
 * @param string  $stylesheet    pathname to include file for stylesheet
 * 
 * @global array  $lang          language text
 * @global string $abbrev_url    url to abbreviations list
 * @global array  $racemgr_url   url to racemanager website
 * @global array  $myclub        club full name
 * @global array  $appname       application name
 * 
 * @return string $bufr          html buffer
 * 
 */ 
/*function s_createRaceResults($eventid, $tablecols, $tablequery, $fleet, $stylesheet)
{
    global $lang;
    global $loc;
    global $abbrev_url;
    global $racemgr_url;
    global $myclub;
    global $appname;
        
    // create database object
    $db = new DB();
    
    // get event details
    $eventobj = new EVENT($db);
    $event = $eventobj->event_getevent($db, $eventid, true);         //FIXME - doesn't work for demo races'
    
    // get results for this race in fleet and position order
    $results = $db->db_get_rows($tablequery);  // FIXME what if no results
    
    // get event year and name
    $eventname = date("Y",strtotime($event['event_date']))."&nbsp;&nbsp;&nbsp;".$event['event_name'];
/*    if (!empty($event['series_code']) AND !empty($event['series_num']))
    {
        $eventname.= " - {$lang['words']['race']} {$event['series_num']}";
    }
*/
    
/*    // set up event title with action links
    $tbufr = <<<EOT
    <div style="width: 100%; display: table;">
        <div style="display: table-row;">
            <div class="title" style="width: 75%;display: table-cell;">$eventname</div>
            <div class="pull-right" style="width: 25%;display: table-cell;"><a class="noprint" onclick="window.print()" href="#">{$lang['words']['print']} {$lang['words']['results']}</a></div>
        </div>
    </div>
    <div class="divider clearfix"></div>
    <p> {$lang['words']['date']}: <b>{$event['event_date']}</b> | {$lang['words']['start']}: <b>{$event['event_start']}</b> | {$lang['words']['wind']}: <b>{$event['wind']}</b> | {$lang['words']['ood']}: <b>{$event['ood']}</b></p>     
EOT;
              
    // loop over all results to create results tables
    $race = 0;
    $rbufr = "";
    foreach ($results as $key=>$result)
    {
        if ($result['race']!=$race)                           // new race - close any open table and start new one
        {
            if ($race!=0) { $rbufr.= "</tbody></table>"; }    // close previous table
            
            $race = $result['race'];
            $rbufr.= "<div class=\"title2\">".ucwords($fleet[$race]['name'])."</div>";    # FIXME add results status ALSO should the race name be in t_results (captures in time)
                        
            if (!empty($fleet[$race]['msg'])) { $rbufr.= "<div class=\"text-alert\">{$fleet[$race]['msg']}</div>"; } // fleet specific message
            
            // start table and column labels
            $rbufr.= "<table width=90% align=center><thead>";
            foreach ($tablecols as $key=>$col)
            {
               $rbufr.= "<th class=\"{$tablecols[$key]["l_class"]}\">{$tablecols[$key]['label']}</th>"; 
            }        
            $rbufr.= "</thead><tbody>";          
        }
        
        // table for data
        $rbufr.= "<tr>";
        foreach ($tablecols as $kcol=>$col)
        {
           $field = $result["{$tablecols[$kcol]['field']}"];
           if ($tablecols[$kcol]['field']=="position")              // for position field replace with results code if set
           {
              if (empty($result['code']))                           // if code is set  - display code
              {
                  $field = $result['points'];
              }
              else
              {
                  $field = $result['code'];
              }
           }
           $rbufr.= "<td class=\"{$tablecols[$kcol]['f_class']}\">$field</td> ";   
        }
        $rbufr.= "</tr>";
    }
    $rbufr.= "</tbody></table>";   // close final table

    // header 
    $hbufr = "<div class=\"title2 pull-right\">$myclub ".ucwords($lang['words']['results'])."</div>";
EOT;
    
    // footer with links
    $create_date = date("D j M y H:i");
    $fbufr = <<<EOT
        <div class="divider clearfix"></div>
        <p><span class="noprint"><a href="$abbrev_url">{$lang['words']['abbreviations']}</a> |</span> <a href="$racemgr_url">$appname {$lang['words']['results']}:</a> $create_date</p>
        <br>
EOT;
    
    // create html object
    $html = new HTMLPAGE($_SESSION['lang']);                      // create html document object
    $html->html_addinclude("$loc/rm_racebox/include/rm_export.inc");      // html header section with embedded styles
    $html->html_body("");                                         // body statement
    $html->html_addhtml($hbufr);                                  // page header
    $html->html_addhtml($tbufr);                                  // event title, links and details
    $html->html_addhtml($rbufr);                                  // results tables
    $html->html_addhtml($fbufr);                                  // page footer   
    $bufr = $html->html_render();                                 // return page
    
    return $bufr;
    
}
*/

function s_createPursuitStarts($eventid, $starts, $length, $scratchclass, $resolution, $pytype)
{
    global $lang;
    global $loc;
    $title = "Start Times";
    
    // document header
    include ("$loc/rm_racebox/css/rm_export_classic.css");

    // header
    $hbufr = "<div class=\"title2 pull-right\">{$_SESSION['clubname']} - Start Times</div>";
    
    // set up event title with action links
    $tbufr = <<<EOT
    <div style="width: 100%; display: table;">
        <div style="display: table-row;">
            <div class="title" style="width: 75%;display: table-cell;">{$_SESSION["e_$eventid"]['ev_fname']}</div>
            <div class="pull-right" style="width: 25%;display: table-cell;"><a class="noprint" onclick="window.print()" href="#">print start times</a></div>
        </div>
    </div>
    <div class="divider clearfix"></div>
    <p> race length: <b>$length mins</b> | scratch class: <b>$scratchclass</b> | start interval: <b>$resolution secs</b> | handicaps used: <b>$pytype</b></p>     
EOT;

    if (!empty($starts))
    {
        // sort out columns and data based on handicap type
        if ($pytype=="local" OR $pytype=="national")
        {
            $tblcols = array(
               array("label" => "class", "width" => "50%", "attr"  => "" ),
               array("label" => "PN", "width" => "25%", "attr"  => "" ),
               array("label" => "start time (mins)", "width" => "25%", "attr"  => "" ),
               );
           $cols = 3;
           $tabledata = array();
           foreach ($starts as $key => $row)
           {
              if (trim($row['start']) == "0" or trim($row['start']) == "0:00") 
              { 
                    $row['start'] = "race start"; 
              }
              elseif ($row['start'][0] == "-")
              {
                    $row['start'] = "- ".ltrim($row['start'],"-");
              }
              else
              {
                    $row['start'] = "+ ".$row['start'];
              }
              $tabledata[] = array ($row['class'],  $row['pn'], $row['start']);                    
           }
        }
        else
        {
            $tblcols = array(
               array("label" => "competitor", "width" => "20%", "attr"  => "" ),
               array("label" => "class", "width" => "20%", "attr"  => "" ),
               array("label" => "sailnum", "width" => "20%", "attr"  => "" ),
               array("label" => "PN", "width" => "20%", "attr"  => "" ),
               array("label" => "start time", "width" => "20%", "attr"  => "" ),
               );
           $cols = 5;
           $tabledata = array();
           foreach ($starts as $key => $row)
           {
              $tabledata[] = array ($row['helm'], $row['class'],  $row['sailnum'], $row['pn'], $row['start']);
           }
        }
    
        // start table and column labels
        $rbufr = "<table width=90% align=center><thead>";
            foreach ($tblcols as $col)
            {
               $rbufr.= <<<EOT
               <th class="lightshade" width="{$col["width"]}">{$col['label']}</th>
EOT;
            }        
            $rbufr.= "</thead>";
            
            // add table
            $rbufr.= "<tbody>";
            foreach ($tabledata as $row)
            {
                $rbufr.= "<tr>";
                foreach ($row as $cell)
                {
                    $rbufr.= "<td>$cell</td>";
                }
                $rbufr.= "</tr>";
            }
            $rbufr.= "</tbody>";
        $rbufr.= "</table>";  
        
    }
    else // report no data
    {
        $rbufr = <<<EOT
        <div class="pull-center"><h3>No classes found</h3></div>
        <div class="pull-center"><h4><i>If this is a personal handicap pursuit make sure you have all the entries loaded before calculating the start times</i></h4></div>
EOT;
    }

    // footer with links
    $create_date = date("D j M Y H:i");
    $fbufr = <<<EOT
        <div class="divider clearfix"></div>
        <p><a href="{$_SESSION['sys_website']}">{$_SESSION['sys_name']}</a> Pursuit Start Times: $create_date</p>
        <br>
EOT;
    
    // create full html markup
    // create html object
    $html = new HTMLPAGE($_SESSION['lang']);                              // create html document object
    $html->html_addhtml($bufr);                                           // header    
    $html->html_body("");                                                 // body statement
    $html->html_addhtml($hbufr);                                          // page header
    $html->html_addhtml($tbufr);                                          // event title, links and details
    $html->html_addhtml($rbufr);                                          // results tables
    $html->html_addhtml($fbufr);                                          // page footer   
    $bufr = $html->html_render();                                         // return page
    
    return $bufr;
}

function s_createEntryList($eventid, $title, $entries, $ignore)
{
    // display columns
    $tblcols = array (
             array( "attr" => "text-align: left", "width" => "15%", "label" => "class" ),
             array( "attr" => "text-align: left", "width" => "10%", "label" => "sail no." ),
             array( "attr" => "text-align: left", "width" => "10%", "label" => "py" ),
             array( "attr" => "text-align: left", "width" => "20%", "label" => "helm" ),
             array( "attr" => "text-align: left", "width" => "20%", "label" => "crew" ),
             array( "attr" => "text-align: left", "width" => "25%", "label" => "club" )
             );
    $cols = count($tblcols);
    
    // header
    $bufr = s_dispPageTitle($_SESSION['clubname'], ucwords($title));
    
    // event title / print link
    $bufr.= s_dispEventTitle($_SESSION["e_$eventid"]['ev_fname'], $title, true);
    
    // attributes list
    $bufr.= s_dispAttributes(array("date"=>$_SESSION["e_$eventid"]['ev_date'],
                                   "start time"=>$_SESSION["e_$eventid"]['ev_starttime'],
                                   "race format"=>$_SESSION["e_$eventid"]['rc_name'],
                                   "starts"=>$_SESSION["e_$eventid"]['rc_numstarts'],
                                   "start sequence"=>$_SESSION["e_$eventid"]['rc_startscheme']));

    $evententries = 0;
    for ($i = 1; $i <= $_SESSION["e_$eventid"]['rc_numfleets']; $i++)     // loop over each fleet
    {
        // race title
        $raceentries = count($entries[$i]);
        $evententries = $evententries + $raceentries;
        $bufr.= <<<EOT
            <div class="title2 pull-left">{$_SESSION["e_$eventid"]["fl_$i"]['name']}</div>
            <p class="note">{$_SESSION["e_$eventid"]["fl_$i"]['desc']} [ <b>$raceentries</b> entries ]</p>
EOT;

        $tabledata = $entries[$i];
        if (count($tabledata)>0)
        {
            $bufr.= s_dispTable($tblcols, $tabledata, $ignore,
                                array("width"=>"90%", "leftmargin"=>"5%", "rowheight"=>"20px", "rowborder"=>""));
        }
        else // report no entries
        {
            $bufr.= <<<EOT
                <div class="pull-center"><p>No entries for this fleet</p></div>
EOT;
        }
    }
    $bufr.= s_dispFooter("total entries: $evententries", ucwords($title));
    
    return $bufr;
}


function s_createDeclarationSheet($eventid, $title, $entries, $ignore, $paging=false)
{
    // display columns
    $tblcols = array (
        array( "attr" => "text-align: left", "width" => "15%", "label" => "class" ),
        array( "attr" => "text-align: left", "width" => "10%", "label" => "sail no." ),
        array( "attr" => "text-align: center", "width" => "50%", "label" => "declaration" ),
    );
    $cols = count($tblcols);

    // header
    $bufr = s_dispPageTitle($_SESSION['clubname'], ucwords($title));

    // event title / print link
    $bufr.= s_dispEventTitle($_SESSION["e_$eventid"]['ev_fname'], $title, true);

    // attributes list
    $bufr.= s_dispAttributes(array("date"=>$_SESSION["e_$eventid"]['ev_date'],
                                   "start time"=>$_SESSION["e_$eventid"]['ev_starttime'],
                                   "race format"=>$_SESSION["e_$eventid"]['rc_name'],
                                   "starts"=>$_SESSION["e_$eventid"]['rc_numstarts'],
                                   "start sequence"=>$_SESSION["e_$eventid"]['rc_startscheme']));

    $evententries = 0;
    for ($i = 1; $i <= $_SESSION["e_$eventid"]['rc_numfleets']; $i++)     // loop over each fleet
    {
        $raceentries = count($entries[$i]);
        $evententries = $evententries + $raceentries;
        // race title
        $bufr.= <<<EOT
            <div class="title2 pull-left">{$_SESSION["e_$eventid"]["fl_$i"]['name']}</div>
EOT;

        $tabledata = $entries[$i];
        if (count($tabledata)>0)
        {
            $bufr.= s_dispTable($tblcols, $tabledata, $ignore,
                                array("width"=>"70%", "leftmargin"=>"5%", "rowheight"=>"50px",
                                      "rowborder"=>"border-bottom: solid 1px silver"));
        }
        else // report no entries
        {
            $bufr.= <<<EOT
                <div class="pull-center"><p>No entries for this fleet</p></div>
EOT;
        }
        if ($paging) { $bufr.= "<p style='page-break-before: always'>&nbsp;</p>"; }
    }
    $bufr.= s_dispFooter("total entries: $evententries", ucwords($title));

    return $bufr;
}


function s_createTimingSheet($eventid, $title, $entries, $ignore, $paging = false)
{
    // display columns
    $tblcols = array (
        array( "attr" => "text-align: left", "width" => "10%", "label" => "class" ),
        array( "attr" => "text-align: left", "width" => "5%", "label" => "sail no." ),
        array( "attr" => "text-align: left", "width" => "5%", "label" => "py" ),
        array( "attr" => "text-align: left", "width" => "10%", "label" => "helm" ),
        array( "attr" => "", "width" => "10%", "label" => "lap 1" ),
        array( "attr" => "", "width" => "10%", "label" => "lap 2" ),
        array( "attr" => "", "width" => "10%", "label" => "lap 3" ),
        array( "attr" => "", "width" => "10%", "label" => "lap 4" ),
        array( "attr" => "", "width" => "10%", "label" => "lap 5" ),
        array( "attr" => "", "width" => "10%", "label" => "lap 6" ),
        array( "attr" => "", "width" => "10%", "label" => "position" ),
    );
    $cols = count($tblcols);

    // header
    $bufr = s_dispPageTitle($_SESSION['clubname'], ucwords($title));

    // event title / print link
    $bufr.= s_dispEventTitle($_SESSION["e_$eventid"]['ev_fname'], $title, true);

    // attributes list
    $bufr.= s_dispAttributes(array("date"=>$_SESSION["e_$eventid"]['ev_date'],
        "start time"=>$_SESSION["e_$eventid"]['ev_starttime'],
        "race format"=>$_SESSION["e_$eventid"]['rc_name'],
        "starts"=>$_SESSION["e_$eventid"]['rc_numstarts'],
        "start sequence"=>$_SESSION["e_$eventid"]['rc_startscheme']));

    $evententries = 0;
    for ($i = 1; $i <= $_SESSION["e_$eventid"]['rc_numfleets']; $i++)     // loop over each fleet
    {
        // race title
        $raceentries = count($entries[$i]);
        $evententries = $evententries + $raceentries;
        $bufr.= <<<EOT
            <div class="title2 pull-left">{$_SESSION["e_$eventid"]["fl_$i"]['name']}</div>
            <p class="note">{$_SESSION["e_$eventid"]["fl_$i"]['desc']} [ <b>$raceentries</b> entries ]</p>
EOT;

        $tabledata = $entries[$i];
        if (count($tabledata)>0)
        {
            $bufr.= s_dispTable($tblcols, $tabledata, $ignore,
                array("width"=>"90%", "leftmargin"=>"5%", "rowheight"=>"50px", "rowborder"=>"border: solid 1px silver"));
        }
        else // report no entries
        {
            $bufr.= <<<EOT
                <div class="pull-center"><p>No entries for this fleet</p></div>
EOT;
        }
    }
    $bufr.= s_dispFooter("total entries: $evententries", ucwords($title));

    return $bufr;
}


function s_dispPageTitle($club, $title)
{
    $bufr =  <<<EOT
        <div class="title2 pull-right">$club - $title</div>
EOT;
    return $bufr;
}


function s_dispEventTitle($event, $title, $print)
{
    if ($print)
    {
        $pbufr =  <<<EOT
            <div class="pull-right" style="width: 25%;display: table-cell;"><a class="noprint" onclick="window.print()" href="#">print $title</a></div>
EOT;
    }
    $bufr = <<<EOT
       <div style="width: 100%; display: table;">
           <div style="display: table-row;">
                <div class="title" style="width: 75%;display: table-cell;">$event</div>
                $pbufr
           </div>
       </div>   
EOT;
    return $bufr;
}


function s_dispAttributes($attributes)
{
    $bufr = <<<EOT
        <div class="divider clearfix"></div>
EOT;
    $bufr.="<p>|";
    foreach($attributes as $key=>$value)
    {
        $bufr.= " $key: <b>$value</b> |";
    }
    
    $bufr.="</p>";
    return $bufr;
}


function s_dispTable($tblcols, $tabledata, $ignore, $attr)
{
    $bufr= "<table width='{$attr['width']}' style=\"margin-left: {$attr['leftmargin']}%\"><thead>";
    $numcols = count($tblcols);
    foreach ($tblcols as $col)
    {
       $bufr.= <<<EOT
       <th class="lightshade" width="{$col['width']}" style="{$col['attr']}">{$col['label']}</th>
EOT;
    }        
    $bufr.= "</thead>";
    
    // add table
    $bufr.= "<tbody>";
    foreach ($tabledata as $row)
    {
        $bufr.= "<tr>";
        $numcell = 0;        
        foreach ($row as $key=>$cell)
        {
            if (!in_array($key, $ignore))  // ignores specified fields
            {
                $numcell++;
                if ($numcell <= $numcols)  // ignores trailing fields
                {
                    $bufr.= "<td style='height: {$attr['rowheight']};{$attr['rowborder']}'>$cell</td>";
                }
            }
        }
        // add trailing cells
        while ($numcell < $numcols)
        {
            $numcell++;
            $bufr.= "<td style='height: {$attr['rowheight']};{$attr['rowborder']}'>&nbsp;</td>";
        }
        $bufr.= "</tr>";
    }
    $bufr.= "</tbody></table>";
    return $bufr;
}

function s_dispFooter($info, $title)
{
    $create_date = date("D j M Y H:i");
    $fbufr = <<<EOT
        <div class="divider clearfix"></div>
        <div class="pull-left"><p>$info</p></div>
        <div class="pull-right"><p><a href="{$_SESSION['sys_website']}">{$_SESSION['sys_name']}</a> $title: $create_date</p></div>
        <br>
EOT;
    return $fbufr;
}
?>