<?php

/*
 * Simple form that will collect OOD observations of conditions and then provide a RAG response (red - no race, amber - mitigation, green ok to race
 *
 * Form data will be held in table t_risk - allow for up to 10 questions - each with a response of 1-10.
 * Will also the calculated score + details of person completing form and the event.
 * [Initially there will be no custom access software - just phpmyadmin]
 *
 * Form field spec will be held in json file  (question (label, info, active)->option (label, score, active)) + score ranges for RAG - with form response text
 * Form buttons will be submit, clear, cancel
 *
 * Response will be coloured RAG display with actions to be taken by
 *
 *
 */

$loc  = "..";
$page = "risk form";     //
$scriptname = basename(__FILE__);
$styletheme = "flatly_";
$stylesheet = "./style/rm_utils.css";
date_default_timezone_set('Europe/London');

// imports
require_once ("{$loc}/common/classes/db.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/lib/util_lib.php");

// script arguments
empty($_REQUEST['pagestate']) ? $pagestate = "init" : $pagestate = strtolower($_REQUEST['pagestate']);              // initial pagestate if not provided
empty($_REQUEST['formname']) ? $formname = "clubrace_risk_form" : $formname = strtolower($_REQUEST['formname']);    // default form name if not provided
empty($_REQUEST['date']) ? $today = date("Y-m-d") : $today = date("Y-m-d", strtotime($_REQUEST['date']));           // date for assessment if not provided
empty($_REQUEST['demo']) ? $demo = false : $demo = true;                                                            // demo state true/false

// set config
$cfg = u_set_config("../config/common.ini", array(), false);
echo "<pre>" . print_r($cfg, true) . "</pre>";

// connect to database using PDO
$db_o = new DB();
foreach ($db_o->db_getinivalues(false) as $data) {
    $_SESSION["{$data['parameter']}"] = $data['value'];
}

// set templates
$tmpl_o = new TEMPLATE(array("$loc/common/templates/general_tm.php", "./templates/layouts_tm_bs5.php", "./templates/risk_tm.php"));

// set fixed page layout fields
$pagefields = array(
    "loc"        => $loc,
    "theme"      => $styletheme,
    "stylesheet" => $stylesheet,
    "title"      => "Risk Form",
    "navbar"     => $tmpl_o->get_template("navbar_utils", $formfields, array()),
    "body"       => "",
    "footer"     => $tmpl_o->get_template("footer_utils", $formfields, array()),
);

// get json configuration data
$formcfg = json_decode(file_get_contents("./data/race_risk_assessment_cfg"), true);     // FIXME - handle multiple files defined by $formname
$questions = $formcfg['questions'];
$mitigations = $formcfg['mitigations'];
$thresholds = $formcfg['thresholds'];


// processing for each pagestate
$err = 0;
if ($pagestate != "init" AND $pagestate != "submit")     // unrecognised page state
{
    $err = 2;
}

elseif ($pagestate == "init")                            // present risk form
{
    $formfields = array(
        "instructions" => $formcfg['instructions'],
        "script" => "race_risk_assessment.php?pagestate=submit&formname=$formname&date=$today",
    );
    if ($demo) { $formfields['script'].= "&demo"; }

    $pagefields['body'] = $tmpl_o->get_template("risk_form", $formfields, array());
}

elseif ($pagestate == "submit")                          // risk response page
{

    $response = get_assessment($formname, $_REQUEST);
    if ($response['status'] == "valid")
    {
        // display response   (include try again)
        $pagefields['body'] = $tmpl_o->get_template("risk_response", array(), array("rag" => $response['rag'], "actions" => $response['actions']));
    }
    else
    {
        $err = 3;
    }
}

if ($err != 0 )  // deal with error conditions
{
    $pagefields['body'] = $tmpl_o->get_template("error_report", array(), array("err"=>$err));
}

// display output for pagestate or error condition
echo $tmpl_o->get_template("page", $pagefields );

function get_assessment($formname, $args)
{

}

function get_total_risk($args)
{

}
