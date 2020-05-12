<?php
/**
 * Allows user to pick boat if more than one found
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 *   
 * 
 */
$loc        = "..";       
$page       = "pickboat";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_sailor_lib.php");

u_initpagestart(0,"pickboat_pg",false);   // starts session and sets error reporting

// libraries
require_once ("{$loc}/common/classes/template_class.php");

// initialising
$pbufr = "";
$numcompetitors = 0;
$searchstr = trim($_REQUEST['sailnum']);
$pick_script = "pickboat_sc.php?compid=%u&option=0";
$hide_script = "hideboat_sc.php?sailnum=".$_REQUEST['sailnum']."&compid=%u";
$tmpl_o = new TEMPLATE(array( "../templates/sailor/layouts_tm.php", "../templates/sailor/search_tm.php"));

// check number of competitors found
if ($_SESSION['competitors']) { $numcompetitors = count($_SESSION['competitors']);  }

if ($numcompetitors == 0) { // none found
   $pbufr = $tmpl_o->get_template("search_nonfound_response",
       array("searchstr"=>$searchstr, "retryscript"=>"boatsearch_pg.php"), array("addboat"=>$_SESSION['option_cfg']['addboat']));

}  elseif ($numcompetitors == 1) { // one match found - go straight to requested function with no display or display details
    // FIXME - do we still want to use predefined option
    $target = sprintf($pick_script, $_SESSION['competitors'][0]['id'], $_SESSION['option']);
    header("Location: $target");
    exit();
}

else  { // more than one competitor match found - user has to pick
    $pbufr = $tmpl_o->get_template("search_manyfound_response",
        array("searchstr"=>$searchstr, "option"=>$_SESSION['option']),
        array("pickscript"=>$pick_script, "hidescript"=>$hide_script, "data"=>$_SESSION['competitors'],
              "opt_cfg"=>$_SESSION['option_cfg']) );
}

$_SESSION['pagefields']['body'] = $pbufr;
$_SESSION['pagefields']['header-right'] = $tmpl_o->get_template("options_hamburger", array(), array("options" => set_page_options("pickboat")));
$_SESSION['pagefields']['header-center'] = $_SESSION['pagename']['pick'];
echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields'] );

