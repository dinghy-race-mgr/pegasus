<?php

/* 
ISSUES:
- should be able to set start and end dates to process
- check it loads

*/

$input_file = "tide_data_2021.txt";
$output_file = "tide_import_2021.csv";

$req_start = "2021-01-01";
$req_end = "2021-12-31";


// open output csv file
$outfile = fopen($output_file, "w") or die("Unable to open output file!");
// output header names
fputcsv($outfile, 
        array('date','bst','midday','best_hw','height','start','hw1','height1','hw2','height2'));   

// open data file
$infile = fopen($input_file, "r") or die("Unable to open input file!");

// initialise
$num_days = 0;
$status = 0;
$num_lines = 0;
$start_date = "";
$end_date = "";
$num_days = "";


// loop through lines in data file
while(! feof($infile))
{
   $num_lines++;
   // if ($num_lines > 200) { exit(); }

   $line = trim(fgets($infile));
   
   // echo $num_lines." --- ".$line."<br>";
    

   $check = check_line($line);

   // if line only contains a date - create new output array - with date and fixed fields 
   if ($check == "date")
   {
       if (outside_period($req_start, $req_end, $line)) { continue; }  // skip if it is a date we don't need
	   
	   $output_date = date("Y-m-d",strtotime(convert_date(trim($line))));
	   $outdata = array("date"=>$output_date, "bst"=>"0", "midday"=>"13:00", "best_hw"=>"00:00", "height"=>"0.0", "start"=>"10:00", "hw1"=>"00:00", "height1"=>"0.0","hw2"=>"00:00", "height2"=>"0.0",);
      
       $event = "";
       if (is_wednesday($outdata['date']))
       {
          $event = "evening";
       }
       
       if (empty($start_date)) { $start_date = $outdata['date']; }
   }

   // elseif line starts with "high" - parse details and add to output array
   elseif ($check == "hw" and !empty($outdata))
   {     
      if ($outdata['hw1'] != "00:00")  // already have HW1 this must be HW2
      {
         $outdata["hw2"] = substr($line, 6, 5);
         $outdata["height2"] = substr($line, 15, 4);            
      }
      else
      {
         $outdata["hw1"] = substr($line, 6, 5);
         $outdata["height1"] = substr($line, 15, 4);
      } 
   }
   
   // end of HW data output line
   elseif ($check == "lw" and !empty($outdata))
   {
		// data parsing is complete       
		// work out best HW and add to array
		$best_hw = get_best_hw($outdata);
		$outdata["best_hw"] = $outdata["hw$best_hw"];
		$outdata["height"]  = $outdata["height$best_hw"];

		// write array to output file and also to terminal 
		$num_days++;
		fputcsv($outfile, $outdata);
		echo print_r($outdata,true)."<br>";
		
		// reinitialise array
		$end_date = $outdata['date'];
		unset($outdata);
   
   }
   // else get next line
   else
   {
      continue;
   }
}

// close data file
fclose($infile);

// close output file with message (including count of number of days written and first and last date) 
fclose($outfile);
echo "<b>Conversion complete</b><br>";
echo "start/end date: $start_date &nbsp;&nbsp;&nbsp; $end_date<br>";
echo "number of days: $num_days";


function convert_date($date)
{
   // converts date to !DD-MM-YYYY) format
   return $date = str_replace("/","-",$date);
}


function outside_period($start, $end, $date)
{
    $in_period = true;
	
	// convert to European date (replace / with -) so it is interpreted correctly by strtotime
	$date = convert_date($date);
	if (strtotime($date) >= strtotime($start) AND strtotime($date) <= strtotime($end))
	{
		$in_period = false;
	
	}
	return $in_period;
}

function check_line($line)
{
   $type = "none";
   
   $line = trim($line);
   
   // convert to European date (replace / with -) so it is interpreted correctly by strtotime
   $line = convert_date($line); 
   
   // check if line is a date and only a date
   if (strtotime($line))
   {
      $type = "date";
   }

   // check if line starts with High
   if (strpos($line, "High" ) === 0)
   {
      $type = "hw";
   }
   elseif (strpos($line, "Low" ) === 0)
   {
      $type = "lw";
   }

   return $type;
}

function is_wednesday($date)
{ 
   $wed = false;
   
   // convert to European date (replace / with -) so it is interpreted correctly by strtotime
   $eur_date = convert_date($date);    
   if(date("N",strtotime($eur_date))==3)
   {
      $wed = true;
   }
   
   return $wed;
}

function get_best_hw($data)
{
   is_wednesday($data['date']) ? $ref_time = strtotime('20:00') : $ref_time = strtotime('13:00');
   $delta_1 = abs($ref_time - strtotime($data['hw1']));
   $delta_2 = abs($ref_time - strtotime($data['hw2']));
   
   if ($delta_1 <= $delta_2)
   {
      $best_hw = 1;   
   }
   else
   {
      $best_hw = 2; 
   }
   return $best_hw;
}

?>