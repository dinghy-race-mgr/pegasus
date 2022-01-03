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

        /* constraints are (* only relevent to reminder page)
        pursuit - true if a pursuit race
        multirace - true if more than one race today
        name* - event name
        format* - race format id
        date* - event date (yyyy-mm-dd)
        */
    }


    public function get_help()
    {
        $where = " category LIKE '%".$this->page."%' and active = 1 ";
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

            // multiple races
            if ($this->constraints['numrace'] <= 1)
            {
                if ($topic['multirace'])
                {
                    unset($topics[$k]);
                }
            }

            // check other constraints if a reminder page
            if ($this->page == "reminder") {

                //  does it match event name
                if (!empty($topic['eventname'])) {
                    if (strpos(strtolower($this->constraints['name']), strtolower($topic['eventname'])) === false) {
                        unset($topics[$k]);
                    }
                }

                // event format
                if (!empty($topic['format'])) {
                    if ($this->constraints['format'] != $topic['format']) {
                        unset($topics[$k]);
                    }
                }

                // dates
                //echo "dates: {$topic['startdate']} | {$topic['enddate']} | {$this->constraints['date']}<br>";
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
                <h2>Reminders ...</h2>
                <blockquote>
                    <p class="lead">No reminders for today </p>
                </blockquote>
EOT;
        }
        else
        {
            $panel_bufr = "";
            foreach ($this->topics as $k=>$topic)
            {
                $panel_bufr.= <<<EOT
                <div class="row">
                <div class="col-md-8 col-md-offset-2">                   
                    <div class="alert alert-danger" role="alert"><p class="lead">{$topic['question']}</p></div>
                    <div>
                        <blockquote>                        
                            <p>{$topic['answer']}</p>
                            <p><small>{$topic['notes']}</small></p>
                        </blockquote>                
                    </div>
                </div>
                </div>
EOT;
            }

            // add outer div
            $htm = <<<EOT
            <h2>Reminders for Today ...</h2>
            <div>
                $panel_bufr  
            </div>
EOT;
        }

        return $htm;
    }



    public function render_help()
    {
        // title
        $this->page == "reminders" ? $title = "Reminders &hellip" : $title = "Help for ".strtoupper($this->page)." page" ;

        if (count($this->topics) <= 0)
        {
            $htm = <<<EOT
                <h2 style="margin-top:40px; margin-bottom:20px;">$title&hellip;</h2>
                <blockquote style="margin-left: 100px;">
                    <p class="lead">Sorry - no help information for this page</p>
                </blockquote>
EOT;
        }
        else
        {
            // develop accordian panel for each topic
            $panel_bufr = "";
            $i = 0;

            foreach ($this->topics as $k=>$topic)
            {
                $i++;
                $panel_bufr.= <<<EOT
                <div class="panel panel-info">
                    <div class="panel-heading" role="tab" id="heading$i">
                        <h2 class="panel-title" style="font-size: 24px;">
                            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse$i" aria-expanded="true" aria-controls="collapse$i">
                              {$topic['question']}
                            </a>
                        </h2>
                    </div>
                    <div id="collapse$i" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading$i">
                        <div class="panel-body">
                            <div class="">{$topic['answer']}</div>
                            <div class="">{$topic['notes']}</div>
                        </div>
                    </div>
                </div>
                </br>
EOT;
            }

            // add outer div
            $htm = <<<EOT
            <h2 style="margin-top:40px; margin-bottom:20px;">$title&hellip;</h2>
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
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

