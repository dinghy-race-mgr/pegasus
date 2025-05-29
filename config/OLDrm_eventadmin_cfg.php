<?php

// get common system settings
$ini = parse_ini_file("../../config/common.ini");
foreach ($ini as $key => $value) { $_SESSION["$key"] = $value; }

// get app settings
$adminini = parse_ini_file("../../config/rm_eventadmin.ini");
foreach ($adminini as $key => $value) { $_SESSION["$key"] = $value; }

$_SESSION['syslog']    = "../../logs/sys/sys_".date("Y-m-d").".log";                                 // sys log file
$_SESSION['dbglog']    = "../../logs/dbg/" . $_SESSION['app_name'] . "_" . date("Y-m-d") . ".log";   // debug log
$_SESSION['sql_debug'] = false;                                                                      // true to debug sql commands




