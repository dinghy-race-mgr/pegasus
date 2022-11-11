<?php
class RESULTS
{



    public function __construct($year, $searchstr)
    {
        $this->year = $year;
        $this->searchstr = $searchstr;
        $this->rst = array();
        $this->inv_dir = "";
        $this->inv_file = "";
        $this->inv_data = array();
        $this->inv_admin = array();

        u_writelog("rm_web - results page request");
    }

    public function setinventoryfile($resultsurl)
    {
        $this->inv_dir = $resultsurl."/".$this->year;
        $this->inv_file = $this->inv_dir."/"."inventory_".$this->year.".json";

        return "inventory_".$this->year.".json";
    }

    public function importinventorydata()
    {
        $string = file_get_contents($this->inv_file."?dev=".rand(1,1000));  // FIXME need a exit nicely if not found or not readable

        if (!$string)
        {
            u_writelog("results: inventory file not found or not readable or empty [{$this->inv_file}]");
            $status = false;
        }
        else
        {
            $this->inv_data = json_decode($string, true);

            // extract the part of the data we need
            $this->inv_admin = $this->inv_data['admin'];
            unset($this->inv_data['admin']);
            $this->inv_data = $this->inv_data['events'];
            $status = true;
        }
        return $status;
    }

    public function filter_result_data($searchstr)
    {
        // filter data based on searchstr
        if (!empty($searchstr))
        {
            foreach ($this->inv_data as $eventid => $event) {
                if (strpos(strtolower($event['eventname']), strtolower(trim($searchstr))) === false) {
                    unset ($this->inv_data[$eventid]);
                }
            }
        }

        // reverse array to get latest events first
        //$this->inv_data = array_reverse($this->inv_data, true);   // inventory is already in reverse order
    }

    public function render_results_table($loc)
    {
        $tbufr = <<<EOT
       <thead>
          <tr class="bg-primary" > 
             <th width="15%">Date</th> 
             <th width="20%">Race</th> 
             <th width="15%">Status</th> 
             <th width="15%">Race Officer</th> 
             <th width="35%">Results</th>            
          </tr> 
       </thead> 
EOT;
        foreach ($this->inv_data as $eventid=>$event)
        {
            // check if want to include this event (i.e. all events up to and including tomorrow)
            if (strtotime($event['eventdate']) > strtotime(date("Y-m-d", strtotime("+1 day"))))
            {
                continue;
            }

            $datetime = date("d M y", strtotime($event['eventdate']));
            if (!empty($event['eventtime']))
            {
                $datetime.= " - ".$event['eventtime'];
            }
            elseif (!empty($event['eventorder']))
            {
                $datetime.= " - ".$event['eventorder'];
            }

            $eventname = ucwords(strtolower($event['eventname']));
            $eventstatus = strtoupper($event['eventstatus']);

            $eventood = $this->getraceofficer($event['duties'], "race officer");

            $eventfiles = "";
            u_array_sort_by_column($event['resultsfiles'], "rank");

            foreach($event['resultsfiles'] as $file)
            {
                $url = $this->inv_dir."/".$file['type']."/".$file['file'];
                $label = str_replace(" ", "&nbsp;", $file['label']);

                $eventfiles.= <<<EOT
                    <a style="display:inline; white-space: nowrap;" href="$url" target="_BLANK" >
                        <span >&nbsp;<img alt="file icon" src="$loc/include/file.png"/></span>
                        <span >$label</span>
                    </a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
EOT;
            }

            $tbufr.= <<<EOT
              <tr style="font-size: 1.0em;"> 
                 <td >$datetime</td> 
                 <td class="text-primary"><b>$eventname</b></td> 
                 <td >$eventstatus</td> 
                 <td >$eventood</td> 
                 <td >$eventfiles</td>           
              </tr>
EOT;
        }
        return $tbufr;
    }


   private function getraceofficer($duties, $dutystr)
   {
       $eventood = "not recorded";
       foreach ($duties as $k => $duty)
       {
           if (strtolower($duty['dutytype']) == strtolower($dutystr))
           {
               $eventood = ucwords(strtolower($duty['dutyname']));
               break;
           }
       }

       return $eventood;
   }
   


}
