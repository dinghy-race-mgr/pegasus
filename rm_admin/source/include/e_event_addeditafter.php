<?php

// initialise
$msg = "";
$display_msg = false;

if ($mode == "add")
{
    // create directory for event files
    $year = date("Y",strtotime($values['date-start']));
    if (!mkdir("../../data/events/$year/{$values['nickname']}", 0777, true))
    {
        $msg.= "FAILED to create open meeting file directory - please investigate<br>";
    }
}


// if new event then also create contacts and content records
if ($mode == "add")
{
    if (!empty($_SESSION['rm_event_copy']))
    {
        // copy contacts from original event
        $contacts_num = 0;
        $rs = db_query("SELECT * FROM e_contact WHERE eid = {$_SESSION['rm_event_copy']}", $conn);
        while ($c = db_fetch_array($rs))
        {
            $contacts_num++;
            $sql = "INSERT INTO e_contact (`eid`,`name`,`job`,`email`,`phone`,`image`,`internal_form`,`link`,`contact`,`updby`) 
                    VALUES ({$values['id']},'{$c['name']}','{$c['job']}','{$c['email']}','{$c['phone']}','{$c['image']}','{$c['internal_form']}', 
                            '{$c['link']}','{$c['contact']}','{$_SESSION['UserID']}')";
            $insert = db_query($sql, $conn);
        }

        // copy content from original event
        $content_copy = false;
        $content_num = 0;
        $rs = db_query("SELECT * FROM e_content WHERE eid = {$_SESSION['rm_event_copy']}", $conn);
        while ($c = db_fetch_array($rs))
        {
            $content_copy = true;
            $content_num++;
            $sql = "INSERT INTO e_content (`open_type`,`eid`,`page`,`name`,`description`,`content-label`,`content`,`link`,`link-label`,`image`,`image-label`,`image_posn`,`reusable`,`updby`) 
                    VALUES ('{$c['open_type']}',{$values['id']},'{$c['page']}','{$c['name']}','{$c['description']}','{$c['content-label']}','{$c['content']}','{$c['link']}',
                    '{$c['link-label']}','{$c['image']}','{$c['image-label']}','{$c['image_posn']}','{$c['reusable']}','{$_SESSION['UserID']}')";
            $insert = db_query($sql, $conn);
        }
    }

    else
    {
        // add blank content records
        if (!empty($_SESSION['event']['std_content']))
        {
            $records = explode(",", $_SESSION['event']['std_content']);
            foreach ($records as $record)
            {
                $rs = db_query("INSERT INTO e_content (`open_type`,`eid`,`page`,`name`,`description`,`content-label`,`content`,`link`,`link-label`,`image`,`image-label`,`image_posn`,`reusable`,`updby`) 
                            SELECT `open_type`,{$values['id']},`page`,`name`,`description`,`content-label`,`content`,`link`,`link-label`,`image`,`image-label`,`image_posn`,0,'{$_SESSION['UserID']}'
                             FROM e_content WHERE name = '$record' and eid = 0 and open_type = 'std')",$conn);
            }
        }
    }
}

$msg.= "EVENT COPIED: including $contacts_num contacts, and $content_num content elements- please update for new event as necessary.<br>";

$message = "<span style=\"white-space: normal\">$msg</span>";
//error_log("|$display_msg|$msg|", 3, $_SESSION['dbglog']);




