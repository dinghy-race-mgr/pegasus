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