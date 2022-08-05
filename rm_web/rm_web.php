<?php
/* rm_web


*/

// start session
session_id('sess-rmweb');
session_start();
error_reporting(E_ERROR);  // turn of warnings

// includes
require_once ("./include/pages.php");
include ("./templates/web_tm.php");
include ("./include/rm_web_lib.php");
require_once ("./include/programme.php");
require_once ("./include/results.php");

// initialise application
$cfg = parse_ini_file("./rm_web.ini", true);

// set opening page to the rm_web menu page unless page is defined as parameter
$page = "menu";
if (!empty($_REQUEST['page'])) { $page = $_REQUEST['page']; }

$if_o = new PAGES($cfg);

if ($page == "menu")
{
    $if_o->pg_menu();
}
elseif ($page == "programme" AND $cfg['pages']['programme'])
{
    $if_o->pg_programme();
}
elseif ($page == "results"  AND $cfg['pages']['results'])
{
    $if_o->pg_results();
}
elseif ($page == "pyanalysis"  AND $cfg['pages']['pyanalysis'])
{
    $if_o->pg_pyanalysis();
}
elseif ($page == "error")
{
    $error = array(
        "problem" => "Page is set to error",
        "symptom" => "",
        "where"   => "",
        "fix"     => "Please contact your system administrator",
    );
    $if_o->pg_none($error);   // FIXME (probably should be $_SESSION['error']['problem'] etc
}
else    // not recognised - go to menu page
{
    $if_o->pg_menu();
}


