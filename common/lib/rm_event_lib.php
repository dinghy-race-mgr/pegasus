<?php
function set_config($cfgfile, $sections, $flatten = true)
{
    $arr = parse_ini_file($cfgfile, true);

    if ($flatten) {
        $cfg = array();
        foreach ($arr as $k => $v) {
            if (is_array($v) and in_array($k, $sections)) {
                foreach ($v as $l => $m) {
                    $cfg[$l] = $m;
                }
            } else {
                $cfg[$k] = $v;
            }
        }
    } else {
        $cfg = $arr;
    }

    return $cfg;
}

function numordinal ($number)
    // FIXME - this is a copy of u_numordinal - need to add util_lib to the includes in rm_event.php and retest
{
    if (key_exists("lang", $_SESSION))
    {
        if ($_SESSION['lang']=="en")
        {
            $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        }
        elseif($_SESSION['lang']=="fr")
        {
            $ends = array('eme','er','eme','eme','eme','eme','eme','eme','eme','eme');
        }
        else
        {
            $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        }
    }
    else
    {
        $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    }

    if (($number %100) >= 11 && ($number%100) <= 13)
    {
        $abbreviation = $number.$ends[0];
    }
    else
    {
        $abbreviation = $number.$ends[$number % 10];
    }

    return $abbreviation;
}

function truncatestring ($string, $length, $dots = "...")
    // FIXME - this is a copy of u_truncatestring - need to add util_lib to the includes in rm_event.php and retest
{
    if ($length == 0)
    {
        return $string;
    }
    else
    {
        return (strlen($string) > $length) ? substr($string, 0, $length - strlen($dots)) . $dots : $string;
    }
}


function format_event_dates($start, $end)
{
    if (date("Y-m-d", strtotime($start)) == date("Y-m-d", strtotime($end)))                  // one day event
    {
        $event_dates = date("jS F", strtotime($start));
    }
    else
    {
        if (date("m", strtotime($start)) == date(date("m", strtotime($end))))         // multi-day event in one month
        {
            $event_dates = date("jS", strtotime($start))." / ".date("jS", strtotime($end))." ".date("F", strtotime($start));
        }
        else                                                                          // multi-day event crossing month boundary
        {
            $event_dates = date("jS M", strtotime($start))." / ".date("jS M", strtotime($end));
        }
    }

    return $event_dates;
}

function get_event_list_status($event)
{
    if ($event['publish-status'] == "cancel")
    {
        $status = "cancel";
    }
    elseif ($event['publish-status'] == "review")
    {
        $status = "review";
    }
    elseif ($event['publish-status'] == "detail")
    {
        date("Y-m-d") > $event['date-end'] ? $status = "complete" : $status = "open";
    }
    else
    {
        $status = "list";
    }

    return $status;
}

function get_event_list_style($status)
{
    $style = array ("list" => "event-list", "open"=> "event-open", "complete"=> "event-complete", "cancel"=> "event-cancel", "review"=> "event-review");
    return $style[$status];
}

function parse_contacts($contacts_data, $mode)
{
    $contacts = array();
    if ($mode == "club")
    {
        $contact_data = explode(",", $contacts_data);
        $contacts[0] = array("name" => trim($contact_data[0]), "job" => trim($contact_data[1]),
            "email" => trim($contact_data[2]), "link" => trim($contact_data[3]));
    }
    else
    {
        foreach ($contacts_data as $k=> $contact)
        {
            $contacts[] = array("name" => trim($contact['name']), "job" => trim($contact['job']),
                                "email" => trim($contact['email']), "link"=>$contact['link']);
        }
    }

    return $contacts;
}

function render_content($content, $document_dir, $image_posn = "")
{
    // define html for content heading if set
    $label_html = "";
    if (!empty($content['label']))
    {
        $label_html = <<<EOT
        <h3>{$content['label']}</h3>
EOT;
    }

    // define html for link if set
    $link_html = "";
    if (!empty($content['link']))
    {
        $link_html = <<<EOT
        <p>for more information see &hellip;<a class="link-info lead" href="{$content['link']}" target="_BLANK"><b>{$content['link-label']}</b></a></p>
EOT;
    }

    // define html for image if set
    $image_html = "";
    if (!empty($content['image']))
    {
        $image_url = $document_dir."/content/images/".$content['image'];
        $image_html = <<<EOT
<img src="$image_url" alt="{$content['image-label']}" title="{$content['image-label']}" class="rounded mx-left d-block" style="width: 100%; max-height: 80%"></p>
EOT;
        // set image position
        if (empty($image_posn))
        {
            empty($content['image_posn']) ? $image_posn = "top" : $image_posn = $content['image_posn'];
        }
    }

    if (empty($image_html))                              // no image
    {
        $html = <<<EOT
            <div class="container">
                $label_html
                <p class="" >{$content['content']}</p>
                $link_html
            </div>
EOT;
    }
    elseif ($image_posn == "right")
    {
        $html = <<<EOT
            <div class="container">
              <div class="row">
                <div class="col-8">
                    $label_html
                    <p class="" >{$content['content']}</p>$link_html
                </div>
                <div class="col">
                  <p class="" >$image_html</p>
                </div>
              </div>
            </div>
EOT;
    }
    elseif ($image_posn == "left")
    {
        $html = <<<EOT
            <div class="container">
              <div class="row">
                <div class="col">
                  <p class="" >$image_html</p>
                </div>
                <div class="col-8">
                    $label_html
                    <p class="" >{$content['content']}</p>$link_html</div>               
              </div>
            </div>
EOT;
    }
    elseif ($image_posn == "top")
    {
        $html = <<<EOT
            <div class="container">
                $label_html
                <p class="" >$image_html</p>                
                <p class="" >{$content['content']}</p>$link_html            
            </div>
EOT;
    }
    elseif ($image_posn == "bottom")
    {
        $html = <<<EOT
            <div class="container">
                $label_html
                <p class="" >{$content['content']}</p>$link_html 
                <p class="" >$image_html</p>                          
            </div>
EOT;
    }

    return $html;
}

function get_guid()
{
    if (function_exists('com_create_guid'))
    {
        return com_create_guid();
    }
    else
    {
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
            .substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12)
            .chr(125);// "}"
        return $uuid;
    }
}

function get_class_name($in_class)
{
    /*
    Two options - classes defined in e_event as a list or defined as fleets with PY limits (not currently supported)
    Need to be sure that entered class have same (case sensitive) name as in racemanager
    For now assume classlist fixed value is correct - return this when fleets/PY ranges is implemented
    */

    $class = $in_class;
    return $class;
}

function get_class_entry_btns($eid, $class_list)
{
    if (empty($class_list))
    {
        $classes = array();
    }
    else
    {
        $classes = explode(",", $class_list);
    }


    if (count($classes) <= 0)                 // no classes defined - report error message
    {
        $entry_btns = <<<EOT
            <div class="alert alert-danger" role="alert">
                  <h4>Sorry - the entry form is not available</h4>
                  <p>Please use the contact button to make the event organiser aware of this  </p>
            </div>
EOT;
    }
    elseif (count($classes) == 1)             // only one  class defined - go straight to form with single button
    {
        $class_txt = ucwords($classes[0]);
        $entry_btns = <<<EOT
        <div class="btn-group" >
            <a class="btn btn-primary btn-lg" type="button" href="rm_event.php?page=newentryform&eid=$eid&class=$class_txt" 
               role="button" aria-expanded="false">
                Enter $class_txt &hellip;
            </a>            
        </div>    
EOT;
    }
    elseif (count($classes) <= 10)         // small number classes are defined - pre-select class and go straight to form
    {
        $dropdown = "";
        foreach ($classes as $k => $class)
        {
            $class_txt = ucwords($class);
            $dropdown.= <<<EOT
            <li><a class="dropdown-item" href="rm_event.php?page=newentryform&eid=$eid&class=$class_txt">$class_txt</a></li>
EOT;
        }

        $entry_btns = <<<EOT
        <div class="btn-group dropend" data-bs-theme="dark">
            <button class="btn btn-primary btn-lg dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                Enter Boat
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><h6 class="dropdown-header">pick class ...</h6></li>
                <li><hr class="dropdown-divider"></li>
                $dropdown
            </ul>
        </div>
EOT;
    }
    else                                   // large number of classes defined - go to form without defining class
    {
        $entry_btns = <<<EOT
        <div class="btn-group" >
            <a class="btn btn-primary btn-lg " type="button" href="rm_event.php?page=newentryform&eid=$eid&class=none" 
               role="button" aria-expanded="false">
                Enter Boat
            </a>            
        </div>
EOT;
    }

    return $entry_btns;
}

function get_class_list($eventcfgid)
    /*
     * Creates CVS list of eligible classes based on race format
     */
{
    global $db_o;

    // get fleet configurations for race format
    $fleets = $db_o->run("SELECT * FROM t_cfgfleet WHERE `eventcfgid` = ? ORDER BY start_num, fleet_num", array($eventcfgid))->fetchAll();

    // get all classes
    $classes = $db_o->run("SELECT * FROM t_class WHERE `active` = 1 ORDER BY classname ASC", array())->fetchAll();

    // get list of classes eligible for the event
    $class_list = "";
    $i = 0;
    foreach ($classes as $class)
    {
        $alloc = r_allocate_fleet($class, $fleets);
        if ($alloc['status'])
        {
            $i++;
            $class_list.= trim($class['classname']).",";
            //echo "<pre>{$class['classname']} included</pre>";
        }
    }
    //echo "<pre>classes: $class_list</pre>";exit();

    return $class_list;
}

function check_class_exists($classname)
{
    global $db_o;
    $class = $db_o->run("SELECT * FROM t_class WHERE `classname` = '$classname' and active = 1 LIMIT 1");

    if ($class)
    {
        return $class;
    }
    else
    {
        return false;
    }
}

function get_category($in_category)
{
    $category = strtolower($in_category);
    return $category;
}

function get_name($in_name)
{
    // FIXME create util lib functions capitalising names and titles (separate) - use it here
    $name = u_ucname(strtolower($in_name));
    return $name;
}

function get_pn($handicap, $class)
{
    global $db_o;

    $pn = 0;
    if ( $handicap )
    {
        if ($handicap == "local")     // using locally derived PNs or another non-RYA source
        {
            $field = "local_py";
        }
        else                          // get national PY
        {
            $field = "nat_py";
        }

        $val = $db_o->run("SELECT $field FROM t_class WHERE classname = ? and `active` = 1", array($class))->fetchColumn();
        if ( $val ) { $pn = $val; };
    }

    return $pn;
}

function get_personal_pn($competitor_id, $handicap_type)
{
    global $db_o;

    $personal_pn = 0;
    if ( $handicap_type == 'personal' and $competitor_id !== 0)
    {
        $val = $db_o->run("SELECT personal_py FROM t_competitor WHERE `id` = ?", array($competitor_id) )->fetchColumn();
        if ($val) { $personal_pn = $val; }
    }

    return $personal_pn;
}

function get_phone($in_phone)
{
    if (empty($in_phone))
    {
        $phone = "not given";
    }
    else
    {
        //$in_phone = trim($in_phone);
        $in_phone = str_replace(' ', '', $in_phone);

        // remove international codes
        if (strpos($in_phone, "+") === 0) { $in_phone = str_replace('+','',$in_phone); }
        if (strpos($in_phone, "44") === 0) { $in_phone = str_replace('44','',$in_phone); }

        $phone = $in_phone;

        // check phone number starts with a 0
        if (ctype_digit($phone))
        {
            if ($phone[0] != "0")  { $phone = "0".$phone; }          // check if first digit is a 0
        }
        else
        {
            $phone = "invalid";
        }
    }

    return $phone;
}

function get_club($in_club, $club_std = "")
{
    $club = trim($in_club);
    if (strtolower($club) == strtolower($club_std))                // used to allow home club to always use the same format
    {
        $club = $club_std;
    }
    else
    {
        $club = str_ireplace("sailing club","SC", $club);
        $club = str_ireplace("yacht club","YC", $club);
    }

    return ucwords($club);
}

function get_class_detail($class_str)
{
    global $db_o;
    $sql = "SELECT `id`, `classname`, `variant`, `nat_py`, `local_py`, `category`, `crew`, `rig`, `spinnaker`, 
                   `engine`, `keel`, `fleets`, replace(`classname`,' ','') as `class_nospace` 
                   FROM `t_class` WHERE replace(`classname`,' ','') = ? and `active` = 1";
    //echo "<pre>$sql</pre>";
    $class = $db_o->run($sql, array(str_replace(' ', '', $class_str)))->fetch();
    if (!$class)
    {
        $class = false;
    }
    return $class;
}

function check_competitor_exists($classid, $sailno, $helm)  // FIXME - needs retesting
{
    global $db_o;

    $names = explode(" ", $helm);
    $surname = $names[1];
    $competitorid = 0;

    // check first on class, sailnum and helm
    $id = $db_o->run("SELECT id FROM t_competitor WHERE classid = ? and sailnum = ?
                     and (helm LIKE '%$helm%' or helm LIKE '%$surname%') 
                     ORDER BY createdate DESC LIMIT 1", array($classid, $sailno) )->fetchColumn();
    if (empty($id))
    {
        // check if we can find just a match on just class and helm
        $id = $db_o->run("SELECT id FROM t_competitor WHERE classid = ? 
                     and (helm LIKE '%$helm%' or helm LIKE '%$surname%') 
                     ORDER BY createdate DESC LIMIT 1", array($classid, $sailno) )->fetchColumn();
        if (!empty($id))
        {
            $competitor['id'] = $id;
        }
    }
    else
    {
        $competitorid = $id;
    }

    return $competitorid;
}

function check_waiting_list ($entrylimit, $eid)
{
    global $db_o;

    $waiting_chk = false;
    if ($entrylimit > 0)
    {
        // get no. of current entries in this event
        $numentries = $db_o->run("SELECT COUNT(*) as count FROM e_entry WHERE eid = ? and `e-exclude` = 0 GROUP BY eid", array($eid) )->fetchColumn();
        if ( $numentries >= $entrylimit ) { $waiting_chk = true; }
    }

    return $waiting_chk;
}

function check_junior_consent($helm_age, $crew_age)
{
    $junior_chk = false;

    if ( !empty($helm_age) and $helm_age < 18 ) { $junior_chk = true; }
    elseif ( !empty($crew_age) and $crew_age < 18 ) { $junior_chk = true; }

    return $junior_chk;
}


function check_entry_open($start, $end)
{
    $today = date ("Y-m-d H:i");
    if (strtotime($today) < strtotime($start))
    {
        $status = "before";
    }
    elseif (strtotime($today) > strtotime($end))
    {
        $status = "after";
    }
    else
    {
        $status = "open";
    }

    return $status;
}


