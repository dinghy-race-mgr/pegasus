<?php
/* class for standard script functions

*/
/*
class SCRIPT
{
//    private $db;
//
    //Method: construct class object
    public function __construct($eventid, $page, $menu)
    {
        session_start();                                    // start session
        date_default_timezone_set($_SESSION['timezone']);   // set timezone

        // error reporting
        $_SESSION['sys_type']=="live" ? error_reporting(E_ERROR): error_reporting(E_ALL);

        // add line to indicate change of page
        if ($menu)
        {
            unset($_REQUEST['menu']);
            u_writelog("** $page page -----------------------------------", $eventid);
        }

        $this->eventid = $eventid;
        $this->page = $page;
        $this->menu = $menu;
    }

    public function chk_args($args, $specs)
    {
        $status = true;
        $parameter = array();
        foreach ($specs as $key=>$spec)
        {
            $parameter[$key] = $this->chk_parameter_valid($args['name'], $spec['default'], $spec['enum']);
            if ($parameter[$key] === false AND $spec['required'])
            {
                // we have a problem - take action
                $status = false;
                // produce error page (I can't use templates)
                $err = array(
                    "error"=> "",
                    "symptom" => "",
                    "action"  => ""
                );
                echo $this->exit_nicely($err, $spec['action']);
            }
        }
        if ($status)
        {
            return $parameter
        }
    }

    public function chk_parameter_valid($arg, $default=null, $enum=array())
    {
        if(isset($arg))
        {
            if(empty($enum))
            {
                $var = $arg;
            }
            else
            {
                in_array($arg, $enum) ? $var = $arg : $var = false ;
            }
        }
        elseif ($default != null)
        {
            $var = $default;
        }
        else
        {
            $var = false;
        }
        return $var;
    }

    public function exit_nicely($err, $action)
    {
        global $lang;
        global $loc;

        $link = "";
        if (strpos($script, "rbx")!== false)
        {
            $link  = "<a class=\"btn btn-primary btn-sm\" href=\"rbx_pg_pickrace.php\" role=\"button\">Return</a>";
        }

        echo <<<EOT
    <!DOCTYPE html>
    <html lang="en">
      <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="shortcut icon" href="{$loc}/common/images/favicon.ico">
        <link rel="stylesheet"    href="{$loc}/common/oss/bootstrap/css/bootstrap.min.css" >
        <link rel="stylesheet"    href="{$loc}/common/oss/bootstrap/css/bootstrap-theme.min.css">

        <script type="text/javascript" src="{$loc}/common/oss/jquery/jquery.min.js"></script>
        <script type="text/javascript" src="{$loc}/common/oss/bootstrap/js/bootstrap.min.js"></script>

      </head>
      <body>
        <div class="container" style="margin-top: 50px;">
            <div class="jumbotron">
              <h1>{$lang['sys']['apology']}</h1>
              <p>{$lang['err']['sys000']}</p>
              <p class="text-danger">$error</p>
              <br>
              <p>
                  $link
                  <span class="pull-right"><small><i>$msg [script: $script]</i></small></span>
              </p>
            </div>
        </div>
      </body>
    </html>
EOT;

        $logmsg = "**** FATAL ERROR $error - {$lang['err']["$error"]}".PHP_EOL."script: $script, event: $eventid, message: $msg";

        u_writelog($logmsg, 0);                // write to system log
        if ($eventid!=0)
        {
            u_writelog($logmsg, $eventid);     // write to event log
        }
        exit();
    }
}

*/