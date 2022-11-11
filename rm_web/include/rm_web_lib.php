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

/*
function checkmydate($date) 
//
// checks that Y-m-d string is a valid date
//
{
  $tempDate = explode('-', $date);
  if (checkdate($tempDate[1], $tempDate[2], $tempDate[0])) 
  {
       return true;
  } 
  else 
  {
     return false;
  }
}

function get_alphanumeric($str, $remove = false) 
{
    if ($remove)
    {
       $list = array("the", "a");
       
       $words = explode(" ", $str);
       if (in_array(strtolower($words[0]), $list)) { unset($words[0]); }
    
    }
    return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($str)), '-');
}

function truncate($string, $length, $dots = "...") 
{
    return (strlen($string) > $length) ? substr($string, 0, $length - strlen($dots)) . $dots : $string;
}


function get_result_filename($id, $name, $date, $type)

   creates file names that are url friendly with four common facets
   <filetype>_<name>_<id>_<date>.htm
   
   for a race result:
      filetype = RR
      name = lowercase concatenated string of the first two words of the event name (or just first word if too long)
      id = eventid
      date = eventdate
      
   for series result
      filetype = SR
      name = as for RR
      id = series code
      date = startdate of the series


{
    $filename = "";
    if ($type == "race")
    {
       $facet_1 = "RR";
       $name = explode("", $name);
       ((strlen($name[0]) + strlen($name[1])) > 30) ? $str = $name[0]."-".$name[1] : $name[0];
       $facet_2 = truncate(get_alphanumeric($str),30, "");
       $facet_3 = "$id";
       $facet_4 = date("Y-m-d",strtotime($date));
    }
    elseif ($type == "series")
    {
       $facet_1 = "SR";
       $name = explode("", $name);
       ((strlen($name[0]) + strlen($name[1])) > 30) ? $str = $name[0]."-".$name[1] : $name[0];
       $facet_2 = truncate(get_alphanumeric($str),30, "");
       $facet_3 = get_alphanumeric($id);
       $facet_4 = date("Y-m-d",strtotime($date));
    }
    else
    {
       return false;
    }

    if (empty($facet_1) or empty($facet_2) or empty($facet_3) or empty($facet_4))
    {
      return false;
    }
   
    return $facet_1."_".$facet_2."_".$facet_3."_".$facet_4.".htm";
}

function prettyPrint( $json )
{
    $result = '';
    $level = 0;
    $in_quotes = false;
    $in_escape = false;
    $ends_line_level = NULL;
    $json_length = strlen( $json );

    for( $i = 0; $i < $json_length; $i++ ) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        if( $ends_line_level !== NULL ) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if ( $in_escape ) {
            $in_escape = false;
        } else if( $char === '"' ) {
            $in_quotes = !$in_quotes;
        } else if( ! $in_quotes ) {
            switch( $char ) {
                case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                case '{': case '[':
                    $level++;
                case ',':
                    $ends_line_level = $level;
                    break;

                case ':':
                    $post = " ";
                    break;

                case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
            }
        } else if ( $char === '\\' ) {
            $in_escape = true;
        }
        if( $new_line_level !== NULL ) {
            $result .= "\n".str_repeat( "\t", $new_line_level );
        }
        $result .= $char.$post;
    }

    return $result;
}
*/

