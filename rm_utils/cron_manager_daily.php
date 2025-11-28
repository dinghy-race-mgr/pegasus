<?php

// fixme - cronlog coding - annual log - needs to be accessible to other utils (functions in rm_util/lib)

// includes

// setup
//$cfg = ....

require_once("../common/lib/util_lib.php");

// logging - start process - date and time - controls file
u_cronlog("\n******raceManager daily cron job - start");    // annual cron logs

// today
$today = date("Y-m-d");

// decode control file
$list = json_decode(file_get_contents("../data/cron/daily.json"),true);  // fixme where should cron controls be kept
$process_count = count($list['controls']);

echo "<pre>".print_r($list,true)."</pre>";

$i = 0;
foreach ($list['controls'] as $control)
{
    // check for date hit
    $process = check_process_run ($control, $today);
    if ($process['action'] == "run")
    {
        $i++;
        u_cronlog("process ". $process['log_text'] ."- start process");

        require_once($control['run_script']);   // this will write to cronlog + set true/false success value

        $status ? $status_txt = "completed" : $status_txt = "failed" ;
        u_cronlog("process {$process['log_text']} [{$control['run_script']}] $status_txt ");
    }
    elseif ($process['action'] == "" or $process['action'] == "norun")
    {
        // logging - confirm not running this process
        u_cronlog("process {$process['log_text']} [{$control['run_script']}] - not scheduled to run");
    }
    else
    {
        // logging - confirm process action not recognised
        u_cronlog("process {$process['log_text']} - action request not recognised");
    }
}

// logging - end of all processes
u_cronlog("raceManager daily cron job - completed [$i processes run from $process_count submitted]");

function check_process_run($control, $today)
{
    $process = array();
    $action = "norun";

    if ($control['type'] == "all")
    {
        $action = "run";
    }
    elseif ($control['type'] == "monthday")
    {
        if (date("j", strtotime($today) == $control['value']))
        {
            $action = "run";
        }
    }
    elseif ($control['type'] == "weekday")
    {
        if (date("N", strtotime($today) == $control['value']))     // value of N - 1 = Monday, 7 = Sunday
        {
            $action = "run";
        }
    }
    elseif ($control['type'] == "datelist")
    {
        $dates_arr = explode(",", $control['value']);
        if (in_array(date("Y-m-d",strtotime($today)), $dates_arr))
		{
            $action = "run";
        }
	}


	if ($action == "run")
    {
        $process['action'] = "run";
        $process['script'] = $control['run_script'];
        $process['log_text'] = $control['log_text'];
    }
    else
    {
        $process['action'] = "norun";
        $process['script'] = "";
        $process['log_text'] = "process type [{$control['type']}] not recognised - not run";
    }

	return $process;
}