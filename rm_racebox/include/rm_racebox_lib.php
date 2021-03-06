<?php

function get_code($code, $link, $domain, $dirn = "", $set = "danger", $unset= "primary" )
/*
 * displays codes dropdown for each entry on start(infringe), timer and results page
 */
{
    if ($domain == "startcodes" OR $domain == "timercodes" OR $domain == "resultcodes")
    {
        if (empty($code))
        {
            $label = "<span class='glyphicon glyphicon-cog'>&nbsp;</span>";
            $style = "btn-$unset";
        }
        else
        {
            $label = "<span>$code&nbsp;</span>";
            $style = "btn-$set";
        }

        $codebufr = u_dropdown_resultcodes($_SESSION[$domain], "short", $link);

        $bufr = <<<EOT
        <div class="btn-group $dirn">
            <button type="button" style="width: 80px;" class="btn $style btn-xs dropdown-toggle" data-toggle="dropdown" >
                <span class="default">$label&nbsp;</span>&nbsp;&nbsp;&nbsp;<span class="caret" ></span>
            </button>
            <ul class="dropdown-menu">
                $codebufr
            </ul>
        </div>
EOT;
    }
    else
    {
        $bufr = "";
    }
    return $bufr;
}


function set_code($eventid, $entryid, $code, $racestatus, $declaration, $boat, $finish_lap = 0, $current_lap = 0)
    /*
     * sets or clears code in t_race
     */
{
    global $race_o;

    if ($finish_lap and $current_lap)
    {
        $finish_lap <= $current_lap ? $finish_check = true : $finish_check = false;
    }
    else
    {
        $finish_check = false;
    }

    if ($code)
    {
        $update = $race_o->entry_code_set($entryid, $code, $finish_check);
        if ($update) { u_writelog("$boat - code set to $code", $eventid); }
    }
    else
    {
        $update = $race_o->entry_code_unset($entryid, $racestatus, $declaration, $finish_check);
        if ($update) { u_writelog("$boat - code unset", $eventid); }
    }

    return $update;
}
