<?php
function checkarg($arg, $mode, $check, $default = "")
{
    // tests $_REQUEST argument for existence and sets values or defaults accordingly.
    // e.g
    // $external = u_checkarg("state", "setbool", "init", true)
    // $action['event'] = u_checkarg("event", "set", "", 0)

    $val = "";
    if (key_exists($arg, $_REQUEST)) {  // if key exists do checks according to mode
        if ($mode == "set") {
            empty($_REQUEST[$arg]) ? $val = $default : $val = $_REQUEST[$arg];
        } elseif ($mode == "setnotnull") {
            empty($_REQUEST[$arg]) ? $val = false : $val = $_REQUEST[$arg];
        } elseif ($mode == "checkset") {
            $_REQUEST[$arg] == $check ? $val = $_REQUEST[$arg] : $val = $default;
        } elseif ($mode == "setbool") {
            $_REQUEST[$arg] == $check ? $val = true : $val = false;
        } elseif ($mode == "checkint") {
            ctype_digit($_REQUEST[$arg]) ? $val = $_REQUEST[$arg] : $val = $default;
        } elseif ($mode == "checkintnotzero") {
            ctype_digit($_REQUEST[$arg]) and $_REQUEST[$arg] ? $val = $_REQUEST[$arg] : $val = false;
        }

    } else {  // if key doesn't exist set to default if provided
        empty($default) ? $val = "" : $val = $default;
    }

    return $val;
}

function u_writelog($logmessage)
{
    error_log(date('Y-m-d H:i:s')." -- ".$logmessage.PHP_EOL, 3, $_SESSION['logfile']);
}

function u_array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
    /*
     * usage:
     *
$data[] = array('volume' => 67, 'edition' => 2);
$data[] = array('volume' => 86, 'edition' => 1);
$data[] = array('volume' => 85, 'edition' => 6);
$data[] = array('volume' => 98, 'edition' => 2);
$data[] = array('volume' => 86, 'edition' => 6);
$data[] = array('volume' => 67, 'edition' => 7);

u_array_sort_by_column($data, "edition", SORT_DESC);
     */
    $sort_col = array();
    foreach ($arr as $key=> $row) {
        $sort_col[$key] = $row[$col];
    }

    array_multisort($sort_col, $dir, $arr);
}

function u_numordinal ($number)
{
    if (key_exists("lang", $_SESSION))
    {
        if ($_SESSION['lang']=="en")
        {
            $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        }
        elseif($_SESSION['lang']=="fr")
        {
            $ends = array('eme','er','eme','eme','eme','eme','eme','eme','eme','eme');
        }
        else
        {
            $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        }
    }
    else
    {
        $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    }

    if (($number %100) >= 11 && ($number%100) <= 13)
    {
        $abbreviation = $number.$ends[0];
    }
    else
    {
        $abbreviation = $number.$ends[$number % 10];
    }

    return $abbreviation;
}


