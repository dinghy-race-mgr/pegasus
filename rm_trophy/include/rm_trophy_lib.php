<?php

function decode_winner($i, $winner_str, $section, $decode_type = 'class')
{
// Boatname|SailNumber|Helm Name|Crew1 name, Crew2 Name|Other Info|
// Class|Sailnumber|Helm Name|Crew Name|Other Info|

$position = array("1"=>"1st","2"=>"2nd","3"=>"3rd");

$winner_arr = array();
$arr = array_filter(explode("|", $winner_str));  /// array_filter ensures null array

empty($arr) ? $winner_arr['exists'] = false : $winner_arr['exists'] = true;

if ($winner_arr['exists'] === true)
{
$winner_arr['type']    = $decode_type;
$winner_arr['section'] = $section;
$winner_arr['posn']    = $position[$i];
!empty($arr[0]) ? $winner_arr['boat'] = $arr[0] : $winner_arr['boat'] = '';
!empty($arr[1]) ? $winner_arr['number'] = $arr[1] : $winner_arr['number'] = '';
!empty($arr[2]) ? $winner_arr['helm'] = $arr[2] : $winner_arr['helm'] = '';
!empty($arr[3]) ? $winner_arr['crew'] = $arr[3] : $winner_arr['crew'] = '';
!empty($arr[4]) ? $winner_arr['info'] = $arr[4] : $winner_arr['info'] = '';
}

return $winner_arr;
}

