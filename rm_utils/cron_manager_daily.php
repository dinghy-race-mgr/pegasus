<?php
error_reporting(E_ALL);
$dir = "/home/sycuser/websites/www.starcrossyc.org.uk/html/non-joomla/pegasus_stx";

require_once("$dir/common/lib/util_lib.php");

// logging - start process - date and time - controls file
cronlog("****** raceManager daily cron job - start");    // annual cron logs
echo "****** raceManager daily cron job - start"; // appears in system logs

// today
$today = date("Y-m-d");

// decode control file
$list = json_decode(file_get_contents("$dir/data/cron/daily.json"),true);
$process_count = count($list['controls']);

$i = 0;
foreach ($list['controls'] as $control)
{
    // check for date hit
    $process = check_process_run ($control, $today);
    if ($process['action'] == "run")
    {
        $i++;
        cronlog("process ". $process['log_text'] ."- start process");

        $status_arr = curl_get($control['run_script'], $control['params'], array());
        cronlog("<pre>process status: ".print_r($status_arr,true)."</pre>");

        $status_arr['success'] ? $status_txt = "completed" : $status_txt = "failed" ;
        cronlog("process {$process['log_text']} [{$control['run_script']}] $status_txt ");
    }
    elseif ($process['action'] == "" or $process['action'] == "norun")
    {
        // logging - confirm not running this process
        cronlog("process {$process['log_text']} [{$control['run_script']}] - not scheduled to run");
    }
    else
    {
        // logging - confirm process action not recognised
        cronlog("process {$process['log_text']} - action request not recognised");
    }
}

// logging - end of all processes
cronlog("raceManager daily cron job - completed [$i processes run from $process_count submitted]");

function cronlog($logtext)
{
    global $dir;

    $logfile = "$dir/logs/sys/cronlog_" . date("Y") .".log";
    $log = date('d-M H:i:s')."| ".$logtext.PHP_EOL;
    error_log($log,3,$logfile);
}


function check_process_run($control, $today)
{
    $process = array();
    $action = "norun";
    $msg = "";

    if ($control['type'] == "all")
    {
        $action = "run";
    }
    elseif ($control['type'] == "monthday")
    {
        // format list of monthdays as array
        $list_arr = explode(",", $control['value']);
        $today_val = date("j", strtotime($today));
        if (in_array($today_val, $list_arr))
        {
            $action = "run";
        }
    }
    elseif ($control['type'] == "weekday")
    {
        // format list of weekdays as array
        $list_arr = explode(",", $control['value']);
        $today_val = date("N", strtotime($today));
        if (in_array($today_val, $list_arr))
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
    else
    {
        $msg = "process type [{$control['type']}] not recognised - process not run";
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
        if (empty($msg))
        {
            $process['log_text'] = "process not required today ($today) - [ {$control['type']} -  {$control['value']} ] ";
        }
        else
        {
            $process['log_text'] = $msg;
        }
    }

	return $process;
}


/**
 * Send a GET requst using cURL
 * string $url to request
 * string $get params to send
 * array $options for cURL
 * returns $arr array
 */
function curl_get($url, $params_str, array $options = array())
{
    $status_arr = array("success"=>true, "err"=>"", "return"=>"");

    parse_str($params_str, $params_arr);

    $defaults = array(
        CURLOPT_URL => $url. (strpos($url, '?') === FALSE ? '?' : ''). http_build_query($params_arr),
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_TIMEOUT => 10
    );

    $ch = curl_init();
    curl_setopt_array($ch, ($options + $defaults));

    $result = curl_exec($ch);
    $status_arr['return'] = $result;

    if (curl_errno($ch))
    {
        $status_arr['err'] = 'Error:' . curl_error($ch);
        $status_arr['success'] = false;
    }

    curl_close($ch);

    return $status_arr;
}