<?php
/* class for help page function

*/


// for test purposes
/*$page       = "help";     //
$scriptname = basename(__FILE__);
require_once ("../lib/util_lib.php");
require_once ("./db_class.php");
require_once ("./template_class.php");

session_start();

$_SESSION['db_host'] = "127.0.0.1";
$_SESSION['db_user'] = "rmuser";
$_SESSION['db_pass'] = "pegasus";
$_SESSION['db_port'] = "3306";
$_SESSION['db_name'] = "pegasus";
$_SESSION['sql_debug'] = false;
$db_o = new DB;
//$help_o = new HELP($db_o, "race", array("pursuit"=>0, "numrace"=>1, "name"=>"", "format"=>"", "date"=>"");
$help_o = new HELP($db_o, "reminder", array("pursuit"=>1, "numrace"=>2, "name"=>"demo series", "format"=>"1", "date"=>"2021-11-28"));

$topics = $help_o->get_help();
//$htm =  $help_o->render_help();
$htm =  $help_o->render_reminders();

$tmpl_o = new TEMPLATE(array("../templates/general_tm.php", "../../rm_racebox/templates/layouts_tm.php"));
echo $tmpl_o->get_template("basic_page", array("theme"=>"flatly_", "loc"=> "../..", "navbar"=>"", "footer"=>"", "body"=> $htm));
*/

class HELP
{
    private $db;

    //Method: construct class object
    public function __construct(DB $db, $page, $constraints = array())
    {
        $this->db = $db;
        $this->page = strtolower($page);
        $this->constraints = $constraints;
        $this->topics = array();
        $this->page == "race" ? $this->pagelabel = "STATUS" : $this->pagelabel = strtoupper($this->page);  // FIXME ugly

        /* constraints are (* only relevent to reminder page)
        pursuit - true if a pursuit race
        numrace - true if more than one race today
        name* - event name
        format* - race format id
        date* - event date (yyyy-mm-dd)
        */

        //echo "<pre>".print_r($this->constraints,true)."</pre>";
    }


    public function get_help()
    {
        $where = " FIND_IN_SET('{$this->page}',category) > 0  AND `active` = 1";
        $sql = "SELECT * FROM t_help WHERE $where ORDER by `listorder` ASC";
        //echo "<pre>$sql</pre>";
        $topics = $this->db->db_get_rows($sql);
        //echo "<pre>constraints: ".print_r($this->constraints,true)."</pre>";

        foreach ($topics as $k=>$topic)
        {
            //echo "<pre>results".print_r($topic,true)."</pre>";

            // if not a pursuit race and this topic is only for pursuit races - remove
            if (!$this->constraints['pursuit'])
            {
                if ($topic['pursuit'])
                {
                    //echo "<pre>unsetting pursuit</pre>";
                    unset($topics[$k]);
                }
            }

            // if not a multi-race day and this topic is only for multiple races - remove
            if ($this->constraints['numrace'] <= 1)
            {
                if ($topic['multirace'])
                {
                    //echo "<pre>unsetting multirace</pre>";
                    unset($topics[$k]);
                }
            }

            // check other constraints if a reminder page
            if ($this->page == "reminder") {

                //  if topic has an event name constraint - if current event name doesn't contain constraint string - remove
                if (!empty($topic['eventname'])) {
                    if (strpos(strtolower($this->constraints['name']), strtolower($topic['eventname'])) === false) {
                        //echo "<pre>unsetting eventname</pre>";
                        unset($topics[$k]);
                    }
                }

                // if topic has an event format constraint - if it is not matched by the current event format - remove
                if (!empty($topic['format'])) {
                    if ($this->constraints['format'] != $topic['format']) {
                        //echo "<pre>unsetting format</pre>";
                        unset($topics[$k]);
                    }
                }

                // if topic has event constraints (start and/or end) - if not matched by current event date - remove
                if (!empty($topic['startdate']) or !empty($topic['enddate'])) {
                    if (!empty($topic['startdate']) and !empty($topic['enddate'])) {
                        if (strtotime($this->constraints['date']) < strtotime($topic['startdate']) or
                            strtotime($this->constraints['date']) > strtotime($topic['enddate'])) {
                           // echo "<pre>unsetting date 1</pre>";
                            unset($topics[$k]);
                        }
                    } elseif (!empty($topic['startdate'])) {
                        if (strtotime($this->constraints['date']) < strtotime($topic['startdate'])) {
                            //echo "<pre>unsetting date 2</pre>";
                            unset($topics[$k]);
                        }
                    } elseif (!empty($topic['enddate'])) {
                        if (strtotime($this->constraints['date']) > strtotime($topic['enddate'])) {
                            //echo "<pre>unsetting date 3</pre>";
                            unset($topics[$k]);
                        }
                    }
                }
            }
        }

        $this->topics = array_values($topics);    // reindex

        //echo "<pre>TOPICS".print_r($this->topics,true)."</pre>";

        return $topics;
    }


    public function render_reminders()
    {
        if (count($this->topics) <= 0)
        {
            $htm = <<<EOT
                <h2>Race Officer Reminders ...</h2>
                <blockquote>
                    <p class="lead">No reminders for today </p>
                </blockquote>
EOT;
        }
        else
        {
            $panel_bufr = "";
            $i = 0;
            foreach ($this->topics as $k=>$topic)
            {
                $i++;
                //$i==1 ? $panel_status = "in" : $panel_status = "";
                $panel_status = "";
                $panel_bufr.= <<<EOT
                <div class="panel panel-success margin-top-20" style="margin-bottom: 20px">
                     <div class="panel-heading" role="tab" id="heading$i">
                     <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse$i" aria-expanded="true" aria-controls="collapse$i" style="text-decoration: none">
                        <p class="panel-title help-title-text" ><small>topic: </small>&nbsp; {$topic['question']}</p>
                     </a>
                    </div>
                    
                    <div id="collapse$i" class="panel-collapse collapse $panel_status" role="tabpanel" aria-labelledby="heading$i">
                        <div class="panel-body">
                            <div class="help-answer-text">{$topic['answer']}</div>
                            <hr style="border: 1px solid darkblue">
                            <div class="help-notes-text">{$topic['notes']}</div>
                        </div>
                    </div>
                </div>

EOT;
            }

            // add outer div
            $htm = <<<EOT
            <h2 style="margin-top:60px; margin-bottom:20px;">Race Officer Reminders &hellip;&nbsp;&nbsp;&nbsp;<small> click topics to view</small></h2>
            <h4>The club suggests checking the information below as they may be relevant to the races you are running today</h4>
            <h4 class="text-info">You can return to this page at any time by selecting "Today's Reminders" option from the 
                                  <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span> menu</h4>
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true" >
                        $panel_bufr  
                    </div>
                </div>
            </div>
EOT;
        }

        return $htm;
    }



    public function render_help($helpid = false)
    {
        // title

        if (count($this->topics) <= 0)
        {
            $htm = <<<EOT
                <h2 style="margin-top:60px; margin-bottom:20px;">Help for the {$this->pagelabel} page &hellip;</h2>
                <blockquote style="margin-left: 100px;">
                    <p class="lead">Sorry - no help information for this page</p>
                </blockquote>
EOT;
        }
        else
        {
            // develop accordion panel for each topic
            $panel_bufr = "";
            $i = 0;

            foreach ($this->topics as $k=>$topic)
            {
                $i++;
                $panel_status = "";
                //$i==1 ? $panel_status = "in" : $panel_status = "";
                $helpid and $helpid == $topic['id'] ? $panel_status = "in" : $panel_status = "";    // open this item
                $panel_bufr.= <<<EOT
                <div class="panel panel-info">
                     <div class="panel-heading" role="tab" id="heading$i">                    
                     <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse$i"  aria-expanded="true" aria-controls="collapse$i" style="text-decoration: none">                       
                       <p class="panel-title help-title-text"><small>topic: </small>&nbsp; {$topic['question']} </p>
                     </a>                                              
                     
                    </div>
                    
                    <div id="collapse$i" class="panel-collapse collapse $panel_status" role="tabpanel" aria-labelledby="heading$i">
                        <div class="panel-body">
                            <div class="help-answer-text">{$topic['answer']}</div>
                            <hr style="border: 1px solid darkblue">
                            <div class="help-notes-text">{$topic['notes']}</div>
                        </div>
                    </div>
                </div>

                </br>
EOT;
            }

            // add outer div
            $htm = <<<EOT
            <h2 style="margin-top:60px; margin-bottom:20px;">Help for the {$this->pagelabel} page &hellip; <small> click topics to view</small></h2>
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                        $panel_bufr  
                    </div>
                </div>
            </div>
EOT;
        }

        return $htm;
    }

}

