<?php
$msg = "";
$display_msg = false;

error_log("|$display_msg|$msg|", 3, $_SESSION['dbglog']);

if (!empty($msg)) { echo "<script type='text/javascript'>alert(\"JUST CHECKING... $msg\");</script>"; }






