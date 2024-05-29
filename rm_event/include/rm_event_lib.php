<?php


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
        $status = "open";
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
    $style = array ("list" => "primary", "open"=> "warning", "complete"=> "secondary", "cancel"=> "danger", "review"=> "warning");
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

function render_content($content, $image_posn = "")
{
    $link_html = "";
    if (!empty($content['link']))                              // link to be added
    {
        $link_html = <<<EOT
        <p>for more information see &hellip;<a class="link-info lead" href="{$content['link']}" target="_BLANK"><b>{$content['link-label']}</b></a></p>
EOT;
    }

    if (empty($content['image']))                              // no image
    {
        $html = <<<EOT
            <div class="container">
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
                <div class="col-8"><p class="" >{$content['content']}</p>$link_html</div>
                <div class="col">
                  <p class="" ><img src='{$content['image']}' alt='{$content['label']}' class='rounded mx-auto d-block' style='max-width: 100%; max-height: 100%'></p>
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
                  <p class="lead" ><img src='{$content['image']}' alt='{$content['label']}' class='rounded mx-auto d-block' style='max-width: 100%; max-height: 100%'></p>
                </div>
                <div class="col-8"><p class="" >{$content['content']}</p>$link_html</div>               
              </div>
            </div>
EOT;
    }
    elseif ($image_posn == "top")
    {
        $html = <<<EOT
            <div class="container">
                <p class="" ><img src='{$content['image']}' alt ='{$content['label']}' class='rounded mx-auto d-block' style='max-width: 60%; max-height: 60%'></p>
                <p class="" >{$content['content']}</p>$link_html            
            </div>
EOT;
    }
    elseif ($image_posn == "bottom")
    {
        $html = <<<EOT
            <div class="container">
                <p class="" >{$content['content']}</p>$link_html 
                <p class="" ><img src='{$content['image']}' alt='{$content['label']}' class='rounded mx-auto d-block' style='max-width: 60%; max-height: 60%'></p>                          
            </div>
EOT;
    }

    //echo "<pre>$html</pre>";
    //exit();

    return $html;
}