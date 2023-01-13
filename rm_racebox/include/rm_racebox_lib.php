<?php

//function process_code($eventid, $params)
//{
//    $fail_reason = "";
//    $err = false;
//    key_exists("entryid", $params)    ? $entryid = $params['entryid'] : $err = true;
//    key_exists("boat", $params)       ? $boat = $params['boat'] : $err = true;
//    key_exists("racestatus", $params) ? $racestatus = $params['racestatus'] : $err = true;
//    key_exists("declaration", $params)? $declaration = $params['declaration'] : $err = true;
//    key_exists("lap", $params)        ? $lap = $params['lap'] : $err = true;
//    key_exists("finishlap", $params)  ? $finishlap = $params['finishlap'] : $err = true;
//    key_exists("code", $params)       ? $code = $params['code'] : $code = "";
//
//    echo "<pre>|err: $err|<br>".print_r($params,true)."</pre>";
//
//    if ($err)
//    {
//        $fail_reason = "required parameters were invalid
//                       (id: {$_REQUEST['entryid']}; boat: {$_REQUEST['boat']}; status: {$_REQUEST['racestatus']};)";
//        u_writelog("$boat - set code failed - $fail_reason", $eventid);
//    }
//    else {
//        $update = set_code($eventid, $entryid, $code, $racestatus, $declaration, $boat, $finishlap, $lap);
//
//        if (!$update) {
//            $fail_reason = "database update failed";
//            u_writelog("$boat - attempt to set code to $code] FAILED" - $fail_reason, $eventid);
//        }
//    }
//
//    return $fail_reason;
//}


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

        $domain == "resultcodes" ? $textsize = "font-size: 0.9em ! important" : $textsize = "";

        $codebufr = u_dropdown_resultcodes($_SESSION[$domain], "short", $link);

        $bufr = <<<EOT
        <div class="btn-group $dirn">
            <button type="button" style="width: 80px;" class="btn $style btn-xs dropdown-toggle" data-toggle="dropdown" >
                <span class="default">$label&nbsp;</span>&nbsp;&nbsp;&nbsp;<span class="caret" ></span>
            </button>
            <ul class="dropdown-menu" style="$textsize">
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


function set_code($eventid, $params)
    /*
     * sets or clears code in t_race
     */
{
    global $race_o;

    // get parameters
    $boat = "";
    $result = true;
    $err = false;
    key_exists("entryid", $params)    ? $entryid = $params['entryid'] : $err = true;
    key_exists("boat", $params)       ? $boat = $params['boat'] : $err = true;
    key_exists("racestatus", $params) ? $racestatus = $params['racestatus'] : $err = true;
    key_exists("declaration", $params)? $declaration = $params['declaration'] : $err = true;
    key_exists("lap", $params)        ? $current_lap = $params['lap'] : $err = true;
    key_exists("finishlap", $params)  ? $finish_lap = $params['finishlap'] : $err = true;
    key_exists("code", $params)       ? $code = $params['code'] : $code = "";
    //u_writedbg("<pre> SETCODE PARAMS: ".print_r($params,true)."</pre>",__FILE__,__FUNCTION__,__LINE__);

    if ($err)            // stop if params are invalid
    {
        $result = "required parameters were invalid (id: {$params['entryid']}; boat: {$params['boat']}; status: {$params['racestatus']};)";
        u_writelog("$boat - set code failed - $result", $eventid);
    }
    else                // process code change
    {
        // check if finished
        $finish_lap <= $current_lap ? $finish_check = true : $finish_check = false;

        if ($code)      // set a scoring code
        {
            $update = $race_o->entry_code_set($entryid, $code, $finish_check);
            if ($update)  // deal with response
            {
                u_writelog("$boat - code set to $code ", $eventid);
            }
            elseif ($update == -1)
            {
                $result = "boat id ($entryid) not found in t_race";
                u_writelog("$boat - code ($code) not set - $result", $eventid);
            }
            elseif ($update == -2)
            {
                $result = "code specified ($code) not recognised ";
                u_writelog("$boat - code ($code) not set - $result", $eventid);
            }
            elseif ($update == -3)
            {
                $result = "database update not completed ";
                u_writelog("$boat - code ($code) not set - $result", $eventid);
            }
            else
            {
                $result = "failed (reason unknown) ";
                u_writelog("$boat - code ($code) not set - $result", $eventid);
            }

        }
        else  // clear scoring code
        {
            // get current code
            $entry = $race_o->entry_get($entryid);

            // unset it
            $update = $race_o->entry_code_unset($entryid, $entry, $declaration, $finish_check);

            if ($update)
            {
                u_writelog("$boat - code ({$entry['code']}) cleared", $eventid);
            }
            elseif ($update == -3)
            {
                $result = "database update not completed ";
                u_writelog("$boat - code ({$entry['code']}) unset attempt - $result", $eventid);
            }
            else
            {
                $result = "failed (reason unknown) ";
                u_writelog("$boat - code ({$entry['code']}) unset attempt - $result", $eventid);
            }
        }
    }

    return $result;
}
