<?php
/**
 * change_sc.php - processes submissions of change form from change_pg.php
 *
 */
$loc        = "..";       
$page       = "change";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");      
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_sailor_lib.php");

u_initpagestart(0,"change_sc",false);   // starts session and sets error reporting

// libraries
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/comp_class.php");

$tmpl_o = new TEMPLATE(array("../templates/sailor/layouts_tm.php"));

// process each field
foreach ($_SESSION['change_fm'] as $field => $fieldspec) {
    if ($fieldspec['status'] and array_key_exists($field, $_REQUEST)) {
        if (!empty($_REQUEST[$field])) {
            $_SESSION['sailor'][$field] = $_REQUEST[$field];
            $_SESSION['sailor']['change'] = true;
        }
    }
}

if ($_SESSION['sailor']['chg-helm'])    { $_SESSION['sailor']['helmname'] = $_SESSION['sailor']['chg-helm']; }
if ($_SESSION['sailor']['chg-crew'])    { $_SESSION['sailor']['crewname'] = $_SESSION['sailor']['chg-crew']; }
if ($_SESSION['sailor']['chg-sailnum']) { $_SESSION['sailor']['sailnum'] = $_SESSION['sailor']['chg-sailnum']; }

// redirect to relevant page according to mode
sailor_redirect($_SESSION['mode'], "race_pg.php?", "cruise_pg.php?" );
