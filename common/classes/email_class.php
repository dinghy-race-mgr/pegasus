<?php
/*
   class for email functions
   - currently implemented for the Brevo service
*/

// change


// for test purposes

$scriptname = basename(__FILE__);
$config = "../config/common.ini";
require_once ("../lib/util_lib.php");
require_once ("./db_class.php");
require_once ("./template_class.php");

session_start();

$cfg = u_set_config("../config/common.ini", array(), false);

$email_type = "single";
$email_tmpl = false;
$logfile = "../../logs/email.log";

$email_o = new EMAIL($cfg['BREVO_URL'], $cfg['BREVO_API'], $email_type, $logfile);

$email_o->set_sender("markeb14.762@gmail.com", "Mark D Elkington");
$email_o->set_subject("This is a test email");
$email_o->set_template("This is a fixed text test email on two lines<br><br>so there");
$email_o->set_recipient("markelkington640@gmail.com", "Mark Elkington", "", array(), array());
$email_o->send_email();


$tmpl_o = new TEMPLATE(array("../templates/general_tm.php", "../../rm_racebox/templates/layouts_tm.php"));
echo $tmpl_o->get_template("basic_page", array("theme"=>"flatly_", "loc"=> "../..", "navbar"=>"", "footer"=>"", "body"=> $htm));


class EMAIL
{
    //Method: construct class object
    public function __construct($url, $key, $type, $logfile = "")
    {
        $this->account   = array("url" => $url, "key" => $key);

        // initialise object data
        $this->recipient  = array();
        $this->sender     = array();
        $this->tosend_num = 0;
        $this->sent_num   = 0;
        $this->email_type = $type;       // single | multi   <-check valid
        $this->email_tmpl = "";       // true | false     <-check valid
        $this->logging    = $logfile;    // check dir exists <-
    }


    private function chk_email($email)
    {
        $success = false;

        if (filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            $success = true;
        }

        return $success;
    }


    public function set_sender($sender_email, $sender_name)
    {
        $err = 0;

        if (!chk_email($sender_email))
        {
            $err = 1;
        }
        elseif (empty($sender_name))
        {
            $err = 2;
        }

        if ($err == 0) { $this->sender = array(); }

        return $err;
    }

    public function set_subject($subject)
    {
        $err = 0;

        if (empty($subject)) { $err = 1; }

        if ($err == 0)
        {
            $this->subject = $subject;
        }

        return $err;

    }

    public function set_template_string($tmpl)
    {

        $this->tmpl_content = $tmpl;
        $this->tmpl_type = "string";

    }

    public function set_template_file($tmpl)
    {

    }

    public function  set_recipient ($email, $name, $subject, $fields=array(), $params = array())
    {
        $err = array();

        if (!chk_email($email))
        {
            $err[] = "invalid email address";
        }
        elseif (empty($name))
        {
            $err[] = "empty recipient name";
        }
        elseif (empty($subject))
        {
            $err[] = "empty subject";
        }
     //   elseif (check template_    // need to be able to pass template as a string variable or as file



        // sets array with recipient details and template data
        $this->recipient[] = array(
            "email"    => $email,
            "name"     => $name,
            "subject"  => $subject,
 //           "template" => $template,
            "fields"   => $fields,
            "params"   => $params
        );

        // check we have mandatory fields populated (email, subject (only if multi)
    }

//    public function set_template($template_path)
//    {
//        $this->template = $template_path;
//
//        // check path exists
//        // only valid if email_tmpl = true
//    }

    public function send_email($arr, $subject, $template, $api_key) //  add logging option
    {
        // send 20 at a time
        // construct email from supplied text or template
        // take the curl part into a separate private function

        global $tmpl_o;

        $status_arr = array("success"=>true, "err"=>"", "return"=>"");

        $display_date = date("l jS F Y H:m");

        $fields = array("firstname"=>$arr['firstname'], "year"=>date("Y"), "num_unconfirmed"=>$arr['unconfirmed'], "txt_unconfirmed"=>$arr['detail'], "dtm_login"=>$arr['login']);
        $params = array("num_unconfirmed"=>$arr['unconfirmed']);

        $htm = $tmpl_o->get_template($template, $fields, $params);

        $data = array(
            "sender"      => array("email" => "noreply@starcrossyc.org.uk", "name"=> "Starcross YC no reply"),
            "to"          => array("0" => array("email" => $arr['email']['emailto'], "name"=> $arr['email']['name'])),
            "subject"     => $subject,
            "htmlContent" => "<html><head></head><body>$htm</body></html>"
        );

        // do not send if email is empty
        if (!empty($data['to']))
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.sendinblue.com/v3/smtp/email');
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 9);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $headers = array();
            $headers[] = 'Accept: application/json';
            $headers[] = "Api-Key: $api_key";
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);
            $status_arr['return'] = $result;

            if (curl_errno($ch))
            {
                $err = curl_errno($ch);
                u_cronlog(" dbg - email for {$arr['email']['name']} FAILED - curl[errno: $err | error: ".curl_error($ch)." | err msg: ".curl_strerror($err)."| return: $result");
                $status_arr['err'] = 'Error: ' . curl_error($ch);
                $status_arr['success'] = false;
            }

            curl_close($ch);
        }
        else
        {
            $status_arr['err'] = 'Error: no email created';
            $status_arr['success'] = false;
        }

        return $status_arr;
    }
}

