<?php
/*
   class for email functions
   - currently implemented for the Brevo service
*/

/*
TO DO
- means of adding style sheet to header for fancy emails
- improve logging - include application, person receiving email, email used, time sent, status
- move email.log into logs/sys - monthly partition.
*/

/* patterns
1 - each email is personalised to each recipient  (e.g. specific duty allocation)
Loop over each recipient creating one "to" address with a recipient specific fields array. Call process_email for EACH recipient.
NOTE - you can add multiple recipients - but they will all get the same email and addresses for all will be visible

2 - as for pattern 1 - but multiple recipients using transactional template for each recipient and not showing all addressees

email templates can either be defined as a string or read from a file.  {} used to replace content.  Does not use the racemanager template_class

*/

/* test script - note you should create a common/sys directory for testing purposes */
$scriptname = basename(__FILE__);
$loc = "../..";
require_once ("../lib/util_lib.php");

$cfg = u_set_config("$loc/config/common.ini", array(), false);
$log_tag = "test class email";
$logfile = "C:/xampp/htdocs/pegasus/logs/email.log";
$email_o = new EMAIL("rm_event", $cfg['BREVO_URL'], $cfg['BREVO_API'], $log_tag, $logfile);

/* TEST 1 - single recipient with fixed html formatted message - non-transactional */
echo "<pre>TEST 1: single recipient with fixed html formatted message - non-transactional </pre>";

$status = $email_o->set_sender("markeb14.762@gmail.com", "Mark D Elkington");
$sender = $email_o->get_sender();
if ($status == false) { exit("set_sender fail: ".print_r($sender,true));}

$status = $email_o->set_subject("TEST 1 - this is fixed subject content");
$subject = $email_o->get_subject();
if ($status == false) { exit("set_subject fail: $subject"); }

$status = $email_o->set_message_str("This is a fixed text test message on two lines with styling<br><br><b>SO £</b> there");
$message = $email_o->get_message();
if ($status == false) { exit("set_message_str fail: $message"); }

$status = $email_o->set_recipient("markelkington640@gmail.com", "Mark Elkington", "", array());
$recipient = $email_o->get_recipient();
if ($status == false) { exit("set_recipient fail: ".print_r($recipient,true)); }

$status = $email_o->process_email();
$err = $email_o->get_error();
echo "<pre>ERRORS".print_r($err,true)."</pre>";
echo "<pre>END Test 1 -------------------- </pre>";

/* TEST 2 - single recipient with message template read from file - non-transactional */
echo "<pre>TEST 2: single recipient with message template read from file - non-transactional </pre>";

$status = $email_o->set_sender("markeb14.762@gmail.com", "Mark D Elkington");
$sender = $email_o->get_sender();
if ($status == false) { exit("set_sender fail: ".print_r($sender,true));}

$status = $email_o->set_subject("TEST 2 - dynamic subject for the owner of {class} {sailnum}");
$subject = $email_o->get_subject();
if ($status == false) { exit("set_subject fail: $subject"); }

$status = $email_o->set_message_file("../../testing/test_email_tmpl.txt");
$message = $email_o->get_message();
if ($status == false) { exit("set_message_file fail: $message"); }

$status = $email_o->set_recipient("markelkington640@gmail.com", "Mark Elkington", "", array("class" => "RS200", "sailnum"=> "1604"));
$recipient = $email_o->get_recipient();
if ($status == false) { exit("set_recipient fail: ".print_r($recipient,true)); }

$status = $email_o->process_email();
$err = $email_o->get_error();
echo "<pre>ERRORS".print_r($err,true)."</pre>";
echo "<pre>END Test 2 -------------------- </pre>";

/* TEST 3 - multiple emails with transactional subject and message */
echo "<pre>TEST 3: multiple emails with transactional subject and message </pre>";

$status = $email_o->set_sender("markeb14.762@gmail.com", "Mark D Elkington");
$sender = $email_o->get_sender();
if ($status == false) { exit("set_sender fail: ".print_r($sender,true));}

$status = $email_o->set_subject("TEST 3 - dynamic subject for the owner of {class} {sailnum}");
$subject = $email_o->get_subject();
if ($status == false) { exit("set_subject fail: $subject"); }

$status = $email_o->set_message_str("This is a transactional message for this recipient with a {class} {sailnum} - test 3");
$message = $email_o->get_message();
if ($status == false) { exit("set_message_str fail: $message"); }

$status = $email_o->set_recipient("markelkington640@gmail.com", "Mark Elkington",  "", array("class" => "RS200", "sailnum"=> "1604"));
$recipient = $email_o->get_recipient();
if ($status == false) { exit("set_recipient #1 fail: ".print_r($recipient,true)); }

$status = $email_o->set_recipient("markeb14.762@gmail.com", "Mark D Elkington",  "", array("class" => "B14", "sailnum"=> "762"));
$recipient = $email_o->get_recipient();
if ($status == false) { exit("set_recipient #2 fail: ".print_r($recipient,true)); }

$status = $email_o->process_email();
$err = $email_o->get_error();
echo "<pre>ERRORS".print_r($err,true)."</pre>";
echo "<pre>END Test 3 -------------------- </pre>";


class EMAIL
{
    //Method: construct class object
    public function __construct($app, $apiurl, $key, $log_tag, $logging = false)
    {
        $this->app = $app;
        $this->account   = array("apiurl" => $apiurl, "key" => $key);

        // initialise object data
        $this->recipient  = array();      // one of more recipients for email
        $this->sender     = array();      // account sending email
        $this->subject    = "";           // subject text for email (not dynamic)
        $this->tosend_num = 0;            // is number of recipients
        $this->sent_num   = 0;            // is number successfully sent
        $this->message    = "";           // html string with or without dynamic content {}
        $this->logging    = $logging;     // file for logging email dispatch
        $this->log_tag    = $log_tag;
        $this->err        = array();      // error detail [WHAT IS STRUCTURE]
    }

    public function get_error()
    {
        return $this->err;
    }

    public function set_sender($sender_email, $sender_name)
    {
        $status = true;

        if (filter_var($sender_email, FILTER_VALIDATE_EMAIL) === false)            // check valid email
        {
            $this->err['sender'] = array("msg" => "invalid email account", "code" => 1, "fatal" => false);
            $status = false;
        }

        if (empty($sender_name))                                      // check sender name exists
        {
            $this->err['sender'] = array("msg" => "sender name missing", "code" => 2, "fatal" => false);
        }

        $this->sender = array("email"=>$sender_email, "name"=>$sender_name);

        return $status;
    }

    public function get_sender()
    {
        return $this->sender;
    }

    public function set_recipient ($email, $name, $subject, $fields = array())
    {
        // called once to add each recipient - must be at least one recipient
        // fields defaults to empty array if none transactional email
        // subject argument can be empty if email subject not required to be recipient specific - but must be set by set_subject

        $status = true;

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false)            // check valid email
        {
            $this->err['recipient'][]= array("msg" => "invalid email ($email )account for $name", "code" => 1, "fatal" => false);
            $status = false;
        }

        if (empty($name))                                                   // check recipient name
        {
            $this->err['recipient'][] = array("msg" => "recipient full name is empty for $email", "code" => 2, "fatal" => false);
            $status = false;
        }

        if (empty($subject))                                                // set subject if empty
        {
            if (empty($this->subject))
            {
                $this->err['subject'][]= array("msg" => "subject string is empty", "code" => 2, "fatal" => true);
                $status = false;
            }
            else
            {
                $subject = $this->subject ;
            }
        }

        if ($status)
        {
            // sets array with recipient details and template data
            $this->recipient[] = array(
                "email"   => $email,
                "name"    => $name,
                "subject" => $subject,
                "data"    => $fields
            );
        }

        return $status;
    }

    public function set_subject($subject_tmpl_str)
    {
        $status = true;
        if (empty($subject_tmpl_str))
        {
            $this->err['subject'][]= array("msg" => "subject string is empty", "code" => 2, "fatal" => true);
            $status = false;
        }
        else
        {
            $this->subject = $subject_tmpl_str;
        }

        return $status;
    }

    public function get_subject()
    {
        return $this->subject;
    }

    public function set_message_str($template_str)
    {
        $status = true;
        if (empty($template_str))
        {
            $status = false;
        }
        else
        {
            $this->message = $template_str;
        }

        return $status;
    }

    public function get_message()
    {
        return $this->message;
    }

    public function set_message_file($tmpl_file)
    {
        // template file is a relative address to an email file  - typically in the local templates directory for the specific app
        $template_str = file_get_contents("$tmpl_file", true);

        $status = true;
        if (empty($template_str))
        {
            $status = false;
        }
        else
        {
            $this->message = $template_str;
        }

        return $status;
    }

    public function render_template($template, $fields)
    {
        $html = $template;
        foreach ($fields as $field => $value)
        {
            $html = str_replace("{" . $field . "}", $value, $html, $count);
        }

        return $html;
    }

    public function get_recipient()
    {
        return $this->recipient;
    }

    public function process_email()
    {
        $status = array("success"=>false, "err"=>"", "return"=>"");              // initialise return array

        foreach ($this->recipient as $row)
        {
            $subject_htm = $this->render_template($row['subject'], $row['data']);      // process templates
            $body_htm = $this->render_template($this->message, $row['data']);

            $data = array(
                "sender"      => $this->sender,
                "to"          => array("0" => array("email" => $row['email'], "name"=> $row['name'])),
                "subject"     => $subject_htm,
                "htmlContent" => "<html><head></head><body>$body_htm</body></html>"
            );

            $status_arr = $this->send_email($data);

            if ($this->logging)
            {
                if ($status_arr['success'] == true)
                {
                    $logtext = " $this->log_tag - {$row['name']} - {$row['email']} - success";
                    u_app_log($logtext, $this->app, $this->logging);
                }
                else
                {
                    $logtext = " $this->log_tag - {$row['name']} - {$row['email']} - fail".PHP_EOL.
                        "error - {$status_arr['err']}".PHP_EOL."return - {$status_arr['return']}";
                    u_app_log($logtext, $this->app, $this->logging);
                }
            }
        }

        return $status;
    }

    private function send_email($data)
    {
        // FIXME https://developers.brevo.com/docs/batch-send-transactional-emails
        // use this to override cc settings + send

        $ch = curl_init();

        // set options
        curl_setopt($ch, CURLOPT_URL, "{$this->account['apiurl']}");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 9);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $headers = array();
        $headers[] = 'Accept: application/json';
        $headers[] = "Api-Key: {$this->account['key']}";
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // send email
        $result = curl_exec($ch);
        $status_arr['return'] = $result;

        if (strpos($result, "messageId") and curl_errno($ch) == 0)    // assume message sent
        {
            $status_arr['err'] = '';
            $status_arr['success'] = true;
        }
        else
        {
            $status_arr['err'] = 'Error: ' . curl_error($ch);
            $status_arr['success'] = false;
        }

        curl_close($ch);

        return $status_arr;
    }
}

