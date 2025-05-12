<?php

// initialise
$msg = "";
$display_msg = false;

// set eid
$sql = DB::prepareSQL("insert into e_content set eid=':master.eid'");
DB::Exec( $sql );


if (!empty($msg))
{
    error_log("|$display_msg|$msg|", 3, $_SESSION['dbglog']);
    echo "<script type='text/javascript'>alert(\"JUST CHECKING... $msg\");</script>";
}


