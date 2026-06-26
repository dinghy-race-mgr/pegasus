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
 */


$loc  = "..";
$page = "Race Risk Assessment";
$scriptname = basename(__FILE__);
$styletheme = "cerulean_";
$stylesheet = "./style/rm_utils.css";
date_default_timezone_set('Europe/London');

// imports
require_once ("{$loc}/common/classes/db.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/classes/event_class.php");

// script arguments
empty($_REQUEST['pagestate']) ? $pagestate = "init" : $pagestate = strtolower($_REQUEST['pagestate']);              // initial pagestate if not provided
empty($_REQUEST['formname']) ? $formname = "clubrace_risk_form" : $formname = strtolower($_REQUEST['formname']);    // default form name if not provided
empty($_REQUEST['date']) ? $today = date("Y-m-d") : $today = date("Y-m-d", strtotime($_REQUEST['date']));           // date for assessment if not provided
empty($_REQUEST['demo']) ? $demo = false : $demo = true;                                                            // demo state true/false

// set config
$cfg = u_set_config("../config/common.ini", array(), false);

// connect to database using PDO
$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host'], $cfg['db_port']);
foreach ($db_o->getinivalues(false) as $k=>$v) {
    $cfg[$v['parameter']] = $v['value'];
}

// set templates
$tmpl_o = new TEMPLATE(array("./templates/layouts_tm_bs5.php", "./templates/risk_tm.php"));

// get json configuration data
$cfg_err = false;
if (array_key_exists("form", $_REQUEST))                               // check if requested cfg file exists
{
    $cfg_file = "./data/" . $_REQUEST['form'];
    if (file_exists($cfg_file) and is_readable($cfg_file))
    {
        $formcfg = json_decode(file_get_contents($cfg_file), true);
    }
    else
    {
        $cfg_err = true;
    }
}
else
{
    $cfg_err = true;
}

if ($cfg_err)
{
    u_exitnicely($scriptname, 0, "the requested risk assessment configuration file either cannot be found or is not readable","",
        array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

// set fixed page layout fields
$pagefields = array(
    "loc"         => $loc,
    "page-theme"  => $styletheme,
    "stylesheet"  => $stylesheet,
    "page-title"  => "Risk Form",
    "page-navbar" => $tmpl_o->get_template("navbar_utils", array("header-left"=>$formcfg['title'], "version"=>$cfg['sys_version'], "year" => date("Y")), array()),
    "page-footer" => $tmpl_o->get_template("footer_utils", array("footer-left"=>"", "footer-center"=>"", "footer-right"=>""), array("footer" => false)),
    "page-modals" => "",
    "page-js"     => ""
);

// processing for each pagestate
$err = 0;
if ($pagestate != "init" AND $pagestate != "submit")     // unrecognised page state
{
    $err = 2;
}

elseif ($pagestate == "init")                            // present risk form
{
    // get today's events
    $event_list = "";
    $event_o = new EVENT($db_o);
    $events = $event_o->get_events_bydate(date("Y-m-d"), "active", "racing");
    if ($events)
    {
        foreach ($events as $event)
        {
            $event_list.= $event['event_name'].", ";
        }
        $event_list = rtrim($event_list, ", ");
    }

    $pagefields['event'] = $event_list;
    $pagefields['instructions'] = $formcfg['form']['instructions'];
    $pagefields['script'] = "risk_assessment.php?pagestate=submit&form={$_REQUEST['form']}&date=$today";
    $pagefields['page-main'] = $tmpl_o->get_template("risk_form", $pagefields, array("questions"=> $formcfg['questions'], "links"=>$formcfg['links']));

    echo $tmpl_o->get_template("page", $pagefields, array());
}

elseif ($pagestate == "submit")                          // risk response page
{
    //echo "<pre>".print_r($_REQUEST,true)."</pre>";

    // add OOD responses to database
    $insert_arr = array(
        "date"     => $_REQUEST['date'],
        "events"   => $_REQUEST['event'],
        "person"   => $_REQUEST['person'],
        "formcode" => $_REQUEST['form']
    );

    $risk_total = 0;
    $scores_text = "RISK SCORES: ";
    for ($i=0; $i<=9; $i++)
    {
        if (!empty($_REQUEST["q$i"]))
        {
            $insert_arr["r$i"] = $_REQUEST["q$i"];
            $scores_text.= " {$formcfg['questions'][$i]['code']}-{$_REQUEST["q$i"]} ";
            $risk_total = $risk_total + $insert_arr["r$i"];
        }
    }
    $scores_text.= "-- TOTAL: $risk_total";

    // add to database
    $insertid = $db_o->insert("t_riskassess", $insert_arr);

    $risk = get_assessment($insert_arr);

    if ($risk['state'] == "red" or $risk['state'] == "amber" or $risk['state'] == "green")
    {
        // update database with assessment
        $upd = $db_o->run("UPDATE t_riskassess SET `response` = '{$risk['state']}', `response_info` = 'MAIN ISSUES: {$risk['risk_1']}, {$risk['risk_2']}' WHERE id = $insertid", array());

        // display response   (include try again)
        $pagefields['page-main'] = $tmpl_o->get_template("risk_response", array("scores"=>$scores_text), array("risk" => $risk, "data" => $formcfg));
        echo $tmpl_o->get_template("page", $pagefields, array());
    }
    else
    {
        $err = 3;
    }
}

if ($err != 0 )  // deal with error conditions
{
    $pagefields['body'] = $tmpl_o->get_template("error_report", array(), array("err"=>$err));
    echo $tmpl_o->get_template("page", $pagefields, $params );
}

function get_assessment($arr)
{
    global $formcfg;

    // count scores for up to ten questions
    $sort_arr = array();
    $risk_arr = array("total"=>0, "state"=>"not set", "risk_1" => "not set", "risk_2" => "not set");
    for ($i=0; $i < 10; $i++)
    {
        if (isset($arr["r$i"]))
        {
            $risk_arr["total"]  = $risk_arr["total"] + $arr["r$i"];
            $sort_arr[$i]       = $formcfg['questions'][$i]["max"] - $arr["r$i"];
        }
    }
    //echo "<pre>RISK".print_r($risk_arr,true)."</pre>";
    //echo "<pre>SORT".print_r($sort_arr,true)."</pre>";

    if ($risk_arr["total"] >= $formcfg['thresholds']['red']['limit'])   // check if 'red' assessment
    {
        $risk_arr['state'] = "red";
    }
    elseif ($risk_arr["total"] >= $formcfg['thresholds']['amber']['limit'])
    {
        $risk_arr['state'] = "amber";
    }
    else
    {
        $risk_arr['state'] = "green";
    }

    // reorder question scores to get top two risks -
    // search mitigations for relevance for these risks
    // render unique mitigations
    asort($sort_arr);
    $i = 0;
    foreach ($sort_arr as $k=>$risk)
    {
        if ($i == 0)
        {
            $risk_arr['risk_1'] = $formcfg['questions'][$k]['code'];
        }
        elseif ($i == 1)
        {
            $risk_arr['risk_2'] = $formcfg['questions'][$k]['code'];
        }
        else
        {
            break;
        }
        $i++;
    }

    return $risk_arr;
}


