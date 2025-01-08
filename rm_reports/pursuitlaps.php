<?php
$loc   = "..";
$page  = "pursuitlaps";
$scriptname = basename(__FILE__);
$today = date("Y-m-d");

include ("{$loc}/common/lib/util_lib.php");
include ("{$loc}/common/classes/template_class.php");

// get templates
$tmpl_o = new TEMPLATE(array("./templates/layouts_tm.php", "./templates/entries_tm.php"));

// get report data from POST as encoded JSON
$rpData = json_decode(file_get_contents('php://input'), true);

// no report header
$bufr = "";

// report details
$fields = array(
    "event-name" =>ucwords($rpData['admin']['event']),
    "report"     =>ucwords($rpData['admin']['report'])
);
$bufr.= $tmpl_o->get_template("event_title", $fields, array("print" => $rpData['admin']['print']));

$rpData['attr']['entries'] = $rpData['fleets'][1]['count'];
$bufr.= $tmpl_o->get_template("event_attributes", array(), array("attr"=>$rpData['attr']));

// report data
$numfleets = count($rpData['fleets']);

foreach ($rpData['fleets'] as $key=>$fleet )       // loop over fleets
{
    $params = array(
        "fleet"=> $key,
        "count"=> $fleet['count'],
        "cols"=> $rpData['cols'],
        "table-style" => $rpData['admin']['table-style']
    );

    // get column header
    $col_bufr = "";
    foreach ($rpData['cols'] as $col)
    {
        $col_bufr.= <<<EOT
<th style="{$col['style']}">{$col['label']}</th>
EOT;
    }

    // get lap columns
    $lap_col = "";
    for ($i = 1; $i <= $rpData['admin']['data_col']; $i++)
    {
        $lap_col.= "<td style='border: solid 1px black; width: 6%; '>&nbsp;</td>";
    }

    $row_bufr = "";
    foreach ($rpData['rows'][$key] as $row)
    {
       $row_bufr.= <<<EOT
        <tr >
            <td style="border: solid 1px black; width: 15%; " >{$row['class']}</td>
            <td style="border: solid 1px black; width: 10%; text-align: right; margin-right:2em">{$row['sailnum']}&nbsp;&nbsp;&nbsp;</td>
            $lap_col
            <td style="border: solid 1px black; width: 15%; ">&nbsp;</td>
        </tr>
EOT;

    }

    $bufr.=<<<EOT
    <table style="{$rpData['admin']['table-style']}">
        <thead><tr>$col_bufr</tr></thead>
        <tbody>$row_bufr</tbody>  
    </table>
EOT;

    if ($rpData['admin']['paging'])
    {
        if ($key < $numfleets and $fleet['count'] > 0) { $bufr.= "<div class=\"page-break\"> </div>"; }
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

echo $tmpl_o->get_template("report_page", $fields, $params);