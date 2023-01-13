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

//echo "<pre>".print_r($rpData,true)."</pre>";

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

$table_hdr = <<<EOT
    <table style="border: 1px solid black; width: 95%">
        <thead>
            <tr>
                <th style="width: 30%; text-align: left;">Class</th>
                <th style="width: 20%; text-align: left;">Sailnum</th>
                <th style="width: 50%; text-align: left;">Laps / Code</th>
            </tr>
        </thead>
        <tbody>
EOT;

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

    //echo "<pre>".print_r($rpData['rows'][$key], true)."</pre>";
    $num_boats = count($rpData['rows'][$key]);
    $round = 20;
    $num_per_col = (round($num_boats/3)%$round === 0) ? round($num_boats/3) : round(($num_boats/3+$round/2)/$round)*$round;;
    //echo "<pre>boats: $num_boats per_col: $num_per_col</pre>";

    $j = 1;  //column count
    $k = 1;  //entry per col count
    $tbufr = "";
    foreach ($rpData['rows'][$key] as $row)                       // loop over boats
    {
        //echo "<pre>TOP $j:$k:$num_per_col</pre>";
        if ($k == 1)
        {
            if ($j <= 1) // start first column
            {
                $tbufr.= "<div class='column' style='order: $j; max-width: 33%;'>$table_hdr";
            }
            else  // close old column and start a new one
            {
                $tbufr.= "</tbody></table></div>";
                $tbufr.= "<div class='column' style='order: $j; max-width: 33%;'>$table_hdr";
            }
        }

        $tbufr.=<<<EOT
        <tr>
            <td style="border: 1px solid black;">{$row['class']}</td>
            <td style="border: 1px solid black;">{$row['sailnum']}</td>
            <td style="border: 1px solid black;">&nbsp;</td>
        </tr>
EOT;

        $k++;
        //echo "<pre>BOTTOM 1 $j:$k:$num_per_col</pre>";
        if ($k > $num_per_col) { $k = 1; $j++; }
        //echo "<pre>BOTTOM 2 $j:$k:$num_per_col</pre>";
    }
    $tbufr.= "</tbody></table></div>";

    $bufr.=<<<EOT
       <div class="container">
           $tbufr
       </div>
EOT;

    if ($rpData['admin']['paging'])
    {
        if ($key < $numfleets and $fleet['count'] > 0)
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

echo $tmpl_o->get_template("report_page", $fields, $params);