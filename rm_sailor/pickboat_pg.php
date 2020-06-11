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
$page       = "pick";
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
$searchstr = trim($_REQUEST['searchstr']);
$pick_script = "pickboat_sc.php?searchstr=".$_REQUEST['searchstr']."&sailor=%u";
$hide_script = "hideboat_sc.php?searchstr=".$_REQUEST['searchstr']."&sailor=%u";
$remember_script = "rememberme.php?searchstr=".$_REQUEST['searchstr']."&sailor=%u";
$tmpl_o = new TEMPLATE(array( "../templates/sailor/layouts_tm.php", "../templates/sailor/search_tm.php"));

// check number of competitors found
if ($_SESSION['competitors']) { $numcompetitors = count($_SESSION['competitors']);  }

if ($numcompetitors == 0) { // none found
   $pbufr = $tmpl_o->get_template("search_nonfound_response",
       array("searchstr"=>$searchstr, "retryscript"=>"search_pg.php"), array("addboat"=>$_SESSION['option_cfg']['addboat']));

}  elseif ($numcompetitors == 1) { // one match found - go straight to requested function with no display or display details
    $target = sprintf($pick_script, $_SESSION['competitors'][0]['id']);
    header("Location: $target");
    exit();
}

else  { // more than one competitor match found - user has to pick
    $pbufr = $tmpl_o->get_template("search_manyfound_response",
        array("searchstr"=>$searchstr),
        array("pickscript"=>$pick_script, "hidescript"=>$hide_script, "rememberscript"=>$remember_script,
              "data"=>$_SESSION['competitors'], "opt_cfg"=>$_SESSION['option_cfg']) );
}

$_SESSION['pagefields']['body'] = $pbufr;
$_SESSION['pagefields']['header-right'] = $tmpl_o->get_template("options_hamburger", array(),
    array("page" => "pick", "options" => set_page_options($page)));
$_SESSION['pagefields']['header-center'] = $_SESSION['option_cfg'][$page]['pagename'];
echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields'] );

