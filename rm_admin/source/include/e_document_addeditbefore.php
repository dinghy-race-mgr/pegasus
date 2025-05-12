<?php
$msg = "";

// check we only have an internal file OR external link - not both
if (!empty($values['filename']) and !empty($values['file']))
{
    $msg.= "- you have specified both an internal and external file - please remove one of them <br>";
}
else
{
    $values['filename'] ? $values['file-loc'] = "external" : $values['file-loc'] = "local";
}

// check publication dates are valid
$pub_start = strtotime($values['publish-start']);
$pub_end = strtotime($values['publish-end']);
if ($pub_end < $pub_start)
{
    $msg.= "- publishing end date is before start date<br>";
}

if (empty($msg))
{
    $commit = true;
    $values['updby']      = $_SESSION['UserID'];
    $values['upddate']    = NOW();
    $message = "";
}
else
{
    $commit = false;
    $message = "<span style=\"white-space: normal\">DOCUMENT ISSUES:<br>$msg </span>";
}





