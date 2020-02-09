<?php
/**
 * boarsearch_pg - searches for boat for signon
 * 
 * Searches for an exact match on sail number.  If one found
 * goes directly to confirm page.  If none found reports none 
 * found and allow user to return to try again.  If more
 * than one found allows user to select one or try again.
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 *   
 * 
 */
$loc        = "..";       
$page       = "boatsearch_sc";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_sailor_lib.php");

u_initpagestart(0,"rm_sailor",false);   // starts session and sets error reporting

// libraries
require_once ("{$loc}/common/classes/db_class.php");  
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/comp_class.php");       

// initialising
$pbufr = "";
$numcompetitors = 0;
$pick_script = "pickboat_sc.php?compid=%u&option=0";
$hide_script = "hideboat_sc.php?sailnum=".$_REQUEST['sailnum']."&compid=%u";
$tmpl_o = new TEMPLATE(array( "../templates/sailor/layouts_tm.php", "../templates/sailor/search_tm.php"));

$opt_map = get_options_map("boatsearch");
$options = array();
foreach ($opt_map as $opt)
{
    if (array_key_exists($opt, $_SESSION['option_cfg']))
    {
        $options[] = $_SESSION['option_cfg'][$opt];
    }
}

// connect to database to get event information
$db_o = new DB();
$comp_o = new COMPETITOR($db_o); 

// check for match on sailnumber
$searchstr = trim($_REQUEST['sailnum']);
$competitors = $comp_o->comp_searchcompetitor($searchstr);

if ($competitors)  {  $numcompetitors = count($competitors);  }

u_writedbg("numcompetitors: $numcompetitors", __FILE__, __FUNCTION__, __LINE__, false);

if ($numcompetitors == 0)  // none found
{
   $pbufr = $tmpl_o->get_template("search_nonfound_response",
       array("searchstr"=>$searchstr, "retryscript"=>"boatsearch_pg.php"));

}
elseif ($numcompetitors == 1)  // one match found - go straight to requested function with no display or display details
{
    $target = sprintf($pick_script, $competitors[0]['id'], $_SESSION['option']);
    header("Location: $target");
    exit();
}

else  // more than one competitor match found - user has to pick
{
    $pbufr = $tmpl_o->get_template("search_manyfound_response",
        array("searchstr"=>$searchstr, "option"=>$_SESSION['option']),
        array("pickscript"=>$pick_script, "hidescript"=>$hide_script, "data"=>$competitors,
              "opt_cfg"=>$_SESSION['option_cfg']) );
}

$_SESSION['pagefields']['body'] = $pbufr;
echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields'] );

