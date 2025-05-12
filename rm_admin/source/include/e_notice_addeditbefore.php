<?php
//error_log("<pre>".print_r($values,true)."</pre>\n", 3, $_SESSION['dbglog']);

// initialise
$msg = "";

// set label for more info link
if (!empty($values['moreinfo']) and empty($values['moreinfo-label']))
{
    $values['moreinfo-label'] = "more info ...";
}


// field checks complete
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
    $message = "<span style=\"white-space: normal\">NOTICE ISSUES:<br>$msg </span>";
}





