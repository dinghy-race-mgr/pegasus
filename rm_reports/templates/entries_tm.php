<?php

function report_title($params=array())
{
    $html =  <<<EOT
        <div class="title2 pull-right">{club} - {report}</div>
EOT;
    return $html;
}

function event_title($params=array())
{
    $pbufr = "";
    if ($params['print'])
    {
        $pbufr =  <<<EOT
            <div class="pull-right" style="width: 25%;display: table-cell;">
                <a class="noprint" onclick="window.print()" href="#">print {report}</a>
            </div>
EOT;
    }

    $html = <<<EOT
    <div style="width: 100%; display: table;">
       <div style="display: table-row;">
            <div class="title" style="width: 75%;display: table-cell;">{event-name}</div>
            $pbufr
       </div>
   </div>      
EOT;
    return $html;
}
function event_attributes($params=array())
{
    $bufr = "";
    foreach ($params['attr'] as $key => $value) {
        $bufr.= " $key: <b>$value</b> |";
    }

    $html = <<<EOT
    <div class="divider clearfix"></div>
    <p>|
        $bufr
    </p>
EOT;

    return $html;
}

function fleet_title($params=array())
{
    $html = <<<EOT
    <div class="title2 pull-left">{name}</div>
    <p class="note">{desc} [ <b>{count}</b> entries ]</p>
EOT;
    return $html;
}

function entry_table($params=array())
{
    $html = "";
    if ($params['count'] == 0)
    {
        $html.= <<<EOT
        <div class="pull-left"><p> --- No entries for this fleet</p></div>
EOT;
    }
    else {
        // get thead markup
        $thead_bufr = "";
        foreach ($params['cols'] as $col) {
            $thead_bufr .= <<<EOT
            <th class="lightshade" style="{$col['style']}">{$col['label']}</th>
EOT;
        }

        // get tbody markup
        $tbody_bufr = "";
        foreach ($params['entries'][$params['fleet']] as $row) {
            $tbody_bufr .= "<tr>";
            //echo "<pre>ROW: ".print_r($row,true)."</pre>";
            foreach ($params['cols'] as $key => $col) {
                //echo "<pre>key: $key</pre>";
                if (key_exists($key, $row))
                {
                    $tbody_bufr .= <<<EOT
                <td class="truncate noshade" >{$row[$key]}</td>
EOT;
                }
                else
                {
                    $tbody_bufr .= <<<EOT
                <td class="truncate noshade" style="border: 1px solid black; height: 30px">&nbsp;</td>
EOT;
                }
            }
            $tbody_bufr .= "</tr>";
        }

        $html.= <<<EOT
        <table style="{$params['table-style']}">        
            <thead >
                $thead_bufr
            </thead>
            <tbody>
                $tbody_bufr
            </tbody>
        </table>
EOT;
    }

    return $html;
}
function footer($params=array())
{
    $create_date = date("D j M Y H:i");
    $html =  <<<EOT
        <div class="divider noprint clearfix"></div>
        <div class="noprint" style="width: 100%; display: flex; flex-direction: row;">
            <div class="noprint pull-left" style="width: 50%"><p>{info}</p></div>
            <div class="noprint pull-right" style="width: 50%"><p><a href="{sys-url}">{sys-name}</a> {title}: $create_date</p></div>
        </div>
        <br>
EOT;
    return $html;
}