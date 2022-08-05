<?php
$loc   = "..";
$page  = "entrylist";
$scriptname = basename(__FILE__);
$today = date("Y-m-d");

include ("{$loc}/common/lib/util_lib.php");
include ("{$loc}/common/classes/template_class.php");

// get templates
$tmpl_o = new TEMPLATE(array("./templates/layouts_tm.php", "./templates/entries_tm.php"));

// get report data from POST as encoded JSON
$rpData = json_decode(file_get_contents('php://input'), true);

// report header
$fields = array(
    "club"=>ucwords($rpData['admin']['club']),
    "report"=>ucwords($rpData['admin']['report'])
);
$bufr= $tmpl_o->get_template("report_title", $fields);

// report details
$fields = array(
    "event-name" =>ucwords($rpData['admin']['event']),
    "report"     =>ucwords($rpData['admin']['report'])
);
$bufr.= $tmpl_o->get_template("event_title", $fields, array("print" => $rpData['admin']['print']));

$bufr.= $tmpl_o->get_template("event_attributes", array(), array("attr"=>$rpData['attr']));

// report data
$numfleets = count($rpData['fleets']);
$i = 0;
foreach ($rpData['fleets'] as $key=>$fleet )
{
    $i++;
    $fields = array(
        "name"  =>ucwords($fleet['name']),
        "count" =>$fleet['count']
    );
    empty($fleet['desc']) or $fleet['name']==$fleet['desc'] ? $fields['desc'] = "" : $fields['desc'] = $fleet['desc'] ;
    $bufr.= $tmpl_o->get_template("fleet_title", $fields);

    $params = array(
        "fleet"=> $key,
        "count"=> $fleet['count'],
        "cols"=> $rpData['cols'],
        "entries" => $rpData['rows'],
        "table-style" => $rpData['admin']['table-style']
    );
    $bufr.=$tmpl_o->get_template("entry_table", array(), $params);

    if ($rpData['admin']['paging'])
    {
        if ($i < $numfleets and $fleet['count'] > 0)
        {
            $bufr.= "<div class=\"page-break\"> </div>";
        }
    }
}

// report footer
$fields = array(
    "title"    => $rpData['admin']['title'],
    "info"     => "total <b>{$rpData['admin']['total']}</b> entries",
    "sys-name" => $rpData['admin']['sys-name'],
    "sys-url"  => $rpData['admin']['sys-url'],
);
$bufr.= $tmpl_o->get_template("footer", $fields);


$fields = array(
    "title" => $rpData['admin']['title'],
    "style" => $tmpl_o->get_template("table_style1", array()),
    "body"  => $bufr
);

$params = array(

);

echo $tmpl_o->get_template("report_page", $fields, $params);