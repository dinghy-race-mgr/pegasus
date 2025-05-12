<?php
//error_log("<pre>".print_r($values,true)."</pre>\n", 3, $_SESSION['dbglog']);

// initialise
$msg = "";

// check if using internal form that email address has been supplied
if ($values['internal_form'] and empty($values['email']))
{
    $msg.= "- you have specified use of the internal contact form - but not supplied the contact's email address - please add email details <br>";
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





