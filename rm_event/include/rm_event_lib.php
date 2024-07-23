<?php
function set_config($cfgfile, $sections, $flatten = true)
{
    $arr = parse_ini_file("config.ini", true);

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

function u_numordinal ($number)
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

function u_truncatestring ($string, $length, $dots = "...")
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
        $contacts[0] = array("name" => trim($contact_data[0]), "role" => trim($contact_data[1]),
            "email" => trim($contact_data[2]), "link" => trim($contact_data[3]));
    }
    else
    {
        foreach ($contacts_data as $k=> $contact)
        {
            $contacts[] = array("name" => trim($contact['name']), "role" => trim($contact['role']),
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
<img src="$image_url" alt="{$content['image-label']}" title="{$content['image-label']}" class="rounded mx-left d-block" style="max-width: 80%; max-height: 80%"></p>
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
                  <p class="lead" >$image_html</p>
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
    if (empty($class_list))        // multi class handicap racing
    {
        $entry_btns = <<<EOT
            <div class="align-self-start">
                <a class="btn btn-large btn-success p-6" href="rm_event.php?page=newentryform&eid=$eid" role="button">
                    <span class="fs-4">Enter Boat &hellip;</span>
                </a>                  
            </div>      
EOT;
    }
    else                           // single class or multi class (with separate fleet racing racing)
    {
        $entry_btns = "<div class='hstack gap-5'>";
        $classes = explode(",", $class_list);
        foreach ($classes as $k => $class)
        {
            $class_txt = ucwords($class);
            $entry_btns.= <<<EOT
                    <div class="p-6">
                        <a class="btn btn-large btn-success" href="rm_event.php?page=newentryform&eid=$eid&class=$class_txt" role="button">
                            <span class="fs-5"><i>enter</i> <b>$class_txt</b></span>
                        </a>
                    </div>
EOT;
        }
        $entry_btns.= "</div>";
    }

    return $entry_btns;
}

function get_category($in_category)
{
    $class = strtolower($in_category);
    return $class;
}

function get_name($in_name)
{
    $name = ucwords(strtolower($in_name));
    return $name;
}

function get_pn($scoring, $handicap, $class)
{
    global $db_o;

    $pn = 0;
    if ( $scoring == 'handicap' or $scoring == 'pursuit' )
    {
        $handicap == "national" ? $field = "nat_py" : $field = "local_py" ;
        $pn = $db_o->run("SELECT $field FROM t_class WHERE classname = ? and `active` = 1", array($class))->fetchColumn();
        empty($pn)? $entry['b_pn'] = 0 : $entry['b_pn'] = $pn;
    }

    return $pn;
}

function get_personal_pn($competitor_id, $handicap_type)
{
    global $db_o;

    if ( $handicap_type == 'personal' and $competitor_id !== 0)
    {
        $hcap = $db_o->run("SELECT personal_py FROM t_competitor WHERE `id` = ?", array($competitor_id) )->fetchColumn();
        empty($hcap) ? $personal_pn = 0 : $personal_pn = $hcap;
    }
    else
    {
        $personal_pn = 0;
    }

    return $personal_pn;
}

function get_phone($in_phone)
{
    $in_phone = trim($in_phone);

    // remove international codes
    if (strpos($in_phone, "+") === 0) { $in_phone = str_replace('+','',$in_phone); }
    if (strpos($in_phone, "44") === 0) { $in_phone = str_replace('+','',$in_phone); }

    $phone = $in_phone;

    // check phone number is 11 digits starting with a 0
    if (ctype_digit($phone))
    {
        if ($phone[0] != "0")  { $phone = "0".$phone; }          // check if first digit is a 0

        if (strlen($phone) != 11 ) { $phone = "invalid"; }       // check if 11 digits
    }
    else
    {
        $phone = "invalid";
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

function check_competitor_exists($class, $sailno, $helm)
{
    global $db_o;

    $names = explode(" ", $helm);
    $surname = $names[1];
    $competitorid = 0;
    $classid = $db_o->run("SELECT id FROM t_class WHERE classname = ?", array($class) )->fetchColumn();
    if ($classid)
    {
        $competitorid = $db_o->run("SELECT id FROM t_competitor WHERE classid = ? and sailnum = ? and 
(helm LIKE '%$helm%' or helm LIKE '%$surname%') ORDER BY createdate DESC LIMIT 1", array($classid, $sailno) )->fetchColumn();
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
    $today = date ("Y-m-d");
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

