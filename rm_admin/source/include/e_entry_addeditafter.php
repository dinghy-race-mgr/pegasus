<?php

// initialise
$msg = "";
$display_msg = false;

// nothing to do

if (!empty($msg))
{
    error_log("|$display_msg|$msg|", 3, $_SESSION['dbglog']);
    echo "<script type='text/javascript'>alert(\"JUST CHECKING... $msg\");</script>";
}


