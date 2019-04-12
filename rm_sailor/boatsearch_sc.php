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
//require_once ("{$loc}/common/lib/mob_lib.php");

u_initpagestart(0,"rm_sailor",false);   // starts session and sets error reporting

// libraries
require_once ("{$loc}/common/classes/db_class.php");  
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/comp_class.php");       

// initialising
$pbufr = "";
$numcompetitors = 0;
$tmpl_o = new TEMPLATE(array( "../templates/sailor/layouts_tm.php", "../templates/sailor/search_tm.php"));

// connect to database to get event information
$db_o = new DB();
$comp_o = new COMPETITOR($db_o); 

// check for match on sailnumber
$searchstr = trim($_REQUEST['sailnum']);
$competitors = $comp_o->comp_searchcompetitor($searchstr);

if ($competitors)  {  $numcompetitors = count($competitors);  }

if ($numcompetitors == 0)  // none found
{
   $pbufr = $tmpl_o->get_template("search_nonfound_response",
       array("searchstr"=>$searchstr, "retryscript"=>"boatsearch_pg.php"));

}
elseif ($numcompetitors == 1)  // one match found - go straight to requested function with no display or display details
{
    // get full details for competitor and pass into session
    foreach ($competitors as $comp)
    {
        $data = $comp_o->comp_findcompetitor(array("id"=>$comp['id']));
        $_SESSION['sailor'] = $data[0];
    }
    $_SESSION['sailor']['change'] = false;
    $_SESSION['sailor']['chg-sailnum'] = "";
    $_SESSION['sailor']['chg-helm'] = "";
    $_SESSION['sailor']['chg-crew'] = "";

    if ($_SESSION['option'])     // if required option is known go to relevant page
    {
        header("Location: {$_SESSION['option']}_pg.php");
        exit();
    }
    else                         // go to options page
    {
        header("Location: options_pg.php");
        exit();
    }

}

else  // more than one competitor match found - user has to pick
{
    $pbufr = $tmpl_o->get_template("search_manyfound_response",
        array("searchstr"=>$searchstr, "option"=>$_SESSION['option']), $competitors);
}

$_SESSION['pagefields']['body'] = $pbufr;
echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields'] );

?>