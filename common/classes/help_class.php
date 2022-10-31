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
        //$where = " category LIKE '%".$this->page."%' and active = 1 ";
        $where = " FIND_IN_SET('".$this->page."',category) > 0  AND active = 1";
        $topics = $this->db->db_get_rows("SELECT * FROM t_help WHERE $where ORDER by rank ASC");

        foreach ($topics as $k=>$topic)
        {
            
            // if not a pursuit race and this topic is only for pursuit races - remove
            if (!$this->constraints['pursuit'])
            {
                if ($topic['pursuit'])
                {
                    unset($topics[$k]);
                }
            }

            // if not a multi-race day and this topic is only for multiple races - remove
            if ($this->constraints['numrace'] <= 1)
            {
                if ($topic['multirace'])
                {
                    unset($topics[$k]);
                }
            }

            // check other constraints if a reminder page
            if ($this->page == "reminder") {

                //  if topic has an event name constraint - if current event name doesn't contain constraint string - remove
                if (!empty($topic['eventname'])) {
                    if (strpos(strtolower($this->constraints['name']), strtolower($topic['eventname'])) === false) {
                        unset($topics[$k]);
                    }
                }

                // if topic has an event format constraint - if it is not matched by the current event format - remove
                if (!empty($topic['format'])) {
                    if ($this->constraints['format'] != $topic['format']) {
                        unset($topics[$k]);
                    }
                }

                // if topic has event constraints (start and/or end) - if not mayched by current event date - remove
                // if ;
                if (!empty($topic['startdate']) or !empty($topic['enddate'])) {
                    if (!empty($topic['startdate']) and !empty($topic['enddate'])) {
                        if (strtotime($this->constraints['date']) < strtotime($topic['startdate']) or
                            strtotime($this->constraints['date']) > strtotime($topic['enddate'])) {
                            unset($topics[$k]);
                        }
                    } elseif (!empty($topic['startdate'])) {
                        if (strtotime($this->constraints['date']) < strtotime($topic['startdate'])) {
                            unset($topics[$k]);
                        }
                    } elseif (!empty($topic['enddate'])) {
                        if (strtotime($this->constraints['date']) > strtotime($topic['enddate'])) {
                            unset($topics[$k]);
                        }
                    }
                }
            }
        }

        $this->topics = array_values($topics);    // reindex

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
                $panel_bufr.= <<<EOT
                <div class="panel panel-primary margin-top-20" style="margin-bottom: 20px">
                     <div class="panel-heading" role="tab" id="heading$i">
                     <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse$i" aria-expanded="true" aria-controls="collapse$i" style="text-decoration: none">
                        <p class="panel-title help-title-text" ><small>topic: </small>&nbsp; {$topic['question']}</p>
                     </a>
                    </div>
                    
                    <div id="collapse$i" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading$i">
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
            <h4>The club suggests checking these topics as they may be relevant to today's races</h4>
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



    public function render_help()
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
                $panel_bufr.= <<<EOT
                <div class="panel panel-info">
                     <div class="panel-heading" role="tab" id="heading$i">                    
                     <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse$i"  aria-expanded="true" aria-controls="collapse$i" style="text-decoration: none">                       
                       <p class="panel-title help-title-text"><small>topic: </small>&nbsp; {$topic['question']} </p>
                     </a>                                              
                     
                    </div>
                    
                    <div id="collapse$i" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading$i">
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

