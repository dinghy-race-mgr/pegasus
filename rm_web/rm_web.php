<?php
/* rm_web


*/

// start session
session_id('sess-rmweb');
session_start();

// includes
request_once ("./include/pages.php");
request_once ("./include/programme.php");

$cfg = parse_ini_file("./config/rm_web.ini", true);

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
    $if_o->pg_none($problem, $symptom, $where, $fix);   // FIXME (probably should be $_SESSION['error']['problem'] etc
}
else    // not recognised - go to menu page
{
    $if_o->pg_menu();
}


