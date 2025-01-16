<?php

/* 
USAGE:
- take tide .doc file and convert to text file - > put files in maintenance directory
- converts to bst for the relevant dates
- run script with parameters - outputs a csv file with a header line
   ../tide_data_scrape.php?infile=<pathtoinputfile>&outfile=<pathtooutfile>&start-date=<YYYY-MM-DD>&end-date=<YYYY-MM-DD>
- http://localhost/pegasus/maintenance/tide_data_scrape.php?infile=Starcross_HW_2027.txt&outfile=starcross_tidedata_2027&start-date=2027-01-01&end-date=2027-12-31
- take tide output files and put in data/tide
- then run tide import data function on rm_admin tide page to load csv file on your PC
*/
$loc  = "..";
$page = "programmeMaker";     //
$scriptname = basename(__FILE__);
$today = date("Y-m-d");

session_id("sess-rmutil-".str_replace("_", "", strtolower($page)));
session_start();
unset($_SESSION);

include ("$loc/config/rm_utils_cfg.php");


if (empty($_REQUEST['infile']) or empty($_REQUEST['outfile']) or empty($_REQUEST['start-date']) or empty($_REQUEST['end-date']))
{
    echo "ERROR: argument missing from script - infile, outfile, start-date, end-date<br>";
    exit("SCRIPT EXIT");
}

$pos = strpos($_REQUEST['infile'], ".txt");
if ($pos === false)
{
    echo "ERROR: input file must be a text file (.txt)<br>";
    exit("SCRIPT EXIT");
}

$pos = strpos($_REQUEST['outfile'], ".csv");
if ($pos === false)
{
    echo "ERROR: output file must be a csv file (.txt)<br>";
    exit("SCRIPT EXIT");
}

$input_file = $_REQUEST['infile'];
$output_file = $_REQUEST['outfile'];

$req_start = $_REQUEST['start-date'];
$req_end = $_REQUEST['end-date'];



// open output csv file
$outfile = fopen($output_file, "w") or die("Unable to open output file!");
// output header names
//fputcsv($outfile, array('date','bst','midday','best_hw','height','start','hw1','height1','hw2','height2'));
fputcsv($outfile, array('date','hw1_time','hw1_height','hw2_time','hw2_height','time_reference','height_units'));

// open data file
$infile = fopen($input_file, "r") or die("Unable to open input file!");

// initialise
$num_days   = 0;
$num_lines  = 0;
$start_date = "";
$end_date   = "";


// loop through lines in data file
while(! feof($infile))
{
   $num_lines++;
   $line = trim(fgets($infile));
   $check = check_line($line);

   // if line only contains a date - create new output array - with date and fixed fields 
   if ($check == "date")
   {
       if (outside_period($req_start, $req_end, $line)) { continue; }  // skip this data if it is a date we don't need
	   
	   $output_date = date("Y-m-d",strtotime(convert_date(trim($line))));
       $outdata = array(
           'date'           => $output_date,
           'hw1_time'       => "00:00",
           'hw1_height'     => "0.0",
           'hw2_time'       => "",
           'hw2_height'     => "",
           'time_reference' => "gmt",
           'height_units'   => "m",
       );
       
       if (empty($start_date)) { $start_date = $outdata['date']; }
   }

   // elseif line starts with "high" - parse details and add to output array
   elseif ($check == "hw" and !empty($outdata))
   {     
      if ($outdata['hw1_time'] != "00:00")  // already have HW1 this must be HW2
      {
         $outdata["hw2_time"]   = substr($line, 6, 5);
         $outdata["hw2_height"] = substr($line, 15, 4);
      }
      else
      {
         $outdata["hw1_time"]   = substr($line, 6, 5);
         $outdata["hw1_height"] = substr($line, 15, 4);
      } 
   }
   
   // end of HW data output line
   elseif ($check == "lw" and !empty($outdata))
   {
        // convert to local time if necessary
       if ($outdata['time_reference'] == "gmt")
       {
           $new_hw1 = convert_to_local_time($outdata['date'], $outdata['hw1_time'], $_SESSION['daylight_saving']);
           if ($new_hw1 != $outdata['hw1_time'])
           {
               $outdata['time_reference'] = "bst";
               $outdata['hw1_time'] = $new_hw1;
               if (!empty($outdata['hw2_time']))
               {
                   $outdata['hw2_time'] = convert_to_local_time($outdata['date'], $outdata['hw2_time'], $_SESSION['daylight_saving']);
               }
           }
       }

		// write array to output file and also to terminal 
		$num_days++;
		fputcsv($outfile, $outdata);
		echo $outdata['date']." | ".$outdata['hw1_time']." | ",$outdata['hw1_height']." | ".
             $outdata['hw2_time']." | ".$outdata['hw2_height']." | ".$outdata['time_reference'].
             " | ".$outdata['height_units']."<br>";
		
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
echo "start/end date of file: $start_date &nbsp;&nbsp;&nbsp; $end_date <br>";
echo "start/end date of extracted data: $req_start &nbsp;&nbsp;&nbsp; $req_end [$num_days days]<br>";


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

function convert_to_local_time($date_str, $time_str, $local)
{
    // get start and end of daylight saving time
    $year = date("Y", strtotime($date_str));
    $dst_start_date = str_replace("YYYY", $year, $local['start_ref']);
    $dst_end_date   = str_replace("YYYY", $year, $local['end_ref']);
    $dst_start = strtotime("$dst_start_date {$local['start_delta']}");
    $dst_end   = strtotime("$dst_end_date {$local['end_delta']}");

    if (strtotime($date_str) >= $dst_start and strtotime($date_str) < $dst_end)
    {
        $t = (new DateTime($time_str))->add(new DateInterval("PT{$local['time_diff']}H0M"));
        $time_str = $t->format("H:i");
    }

    return $time_str;
}