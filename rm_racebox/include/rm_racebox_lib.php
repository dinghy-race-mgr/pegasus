<?php
function enter_boat($entry, $eventid, $type)
/*
 * enters boat into race  (used in entries_add_sc, entries_sc, and start_sc scripts
 */
{
    global $entry_o, $event_o, $db_o;

    // get fleet allocation details for entry
    $boat_o = new BOAT($db_o);
    $classcfg = $boat_o->boat_getdetail($entry['classname']);
    $fleets = $event_o->event_getfleetcfg($_SESSION["e_$eventid"]['ev_format']);
    $alloc = r_allocate_fleet($classcfg, $fleets);

    $success = "failed";
    $problem = "";
    $entry_tag = "{$entry['classname']} - {$entry['sailnum']}";

    if ($alloc['status'])                                                    // fleet allocated so ok to load entry
    {
        $entry = array_merge($entry, $alloc);
        $i = $entry['fleet'];
        $result = $entry_o->set_entry($entry, $_SESSION["e_$eventid"]["fl_$i"]['pytype'], $_SESSION["e_$eventid"]["fl_$i"]['maxlap']);
        if ($result['status'])
        {
            $i = $entry['fleet'];

            if ($result["exists"])                                            // updating existing entry
            {
                $success = "exists";
                u_writelog("ENTRY ($type) UPDATED: $entry_tag", $eventid);
            }
            else                                                              // adding new entry
            {
                $success = "entered";
                $_SESSION["e_$eventid"]["fl_$i"]['entries']++;
                u_writelog("ENTRY ($type): $entry_tag", $eventid);
            }

            if ($type == "signon")
            {
                $upd = $entry_o->confirm_entry($entry['t_entry_id'], "L", $result['raceid']);
            }

            $fleet_name = $_SESSION["e_$eventid"]["fl_$i"]['code'];
            $_SESSION["e_$eventid"]['enter_rst'][] = "<b>$entry_tag</b> &nbsp;&nbsp;[$fleet_name]";

            $_SESSION["e_$eventid"]['result_status'] = "invalid";              // new competitor details so reset results update flag
        }
        else                                                                   // failed to enter
        {
            $problem = $result["problem"];
            u_writelog("ENTRY ($type) FAILED: $entry_tag [$problem]", $eventid);
            if ($type == "signon")
            {
                $upd = $entry_o->confirm_entry($entry['t_entry_id'], "F");
            }

            $_SESSION["e_$eventid"]['enter_rst'][] = "<b>$entry_tag</b> &nbsp;&nbsp;[FAILED] <br>$problem";
        }
    }
    else                                                                       // fleet not allocated
    {
        $alloc['alloc_code'] == "E" ? $reason_txt = "boat ineligible for this event" : $reason_txt = "fleet configuration not defined for this event";

        $problem = "no fleet allocation - ".$reason_txt;
        u_writelog("ENTRY ($type) FAILED: $entry_tag [$problem]", $eventid);
        if ($type == "signon")
        {
            $upd = $entry_o->confirm_entry($entry['t_entry_id'], $alloc['alloc_code']);
        }

        $_SESSION["e_$eventid"]['enter_rst'][] = "<b>$entry_tag</b> &nbsp;&nbsp;[FAILED] <br>$problem";
    }

    $status = array ("state" => $success, "entry" => $entry_tag, "reason" => $problem);

    return $status;
}


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

function add_auto_redirect ($target_url, $secs)
{
    $bufr = "";
    if (!empty($target_url) and $secs > 0)
    {
        $bufr.= <<<EOT
    <script>    
        $(function(){
          var idleTimer;
          function resetTimer(){
            clearTimeout(idleTimer);
            idleTimer = setTimeout(whenUserIdle,$secs*1000);
          }
          $(document.body).bind('mousemove keydown click',resetTimer); // events list that we want to monitor
          resetTimer();                                                // start the timer when the page loads
        });
        
        function whenUserIdle(){
          location.replace ('$target_url');
        }
    </script>
EOT;
    }

    return $bufr;
}
