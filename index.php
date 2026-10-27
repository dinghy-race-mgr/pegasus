<?php 
/* ---------------------------------------------------------------------------------------------
    index.php       standard start up page for racemanager applications
        
   ---------------------------------------------------------------------------------------------
*/

// includes
include ("./common/lib/util_lib.php");
include ("./common/classes/db.php");

// read ini file
$cfg = u_set_config("./config/common.ini", array(), false);
$racemanager_cfg = u_set_config("./config/racemanager.ini", array(), false);
if (array_key_exists("risk_assess", $racemanager_cfg))
{
    $cfg['risk_assess'] = $racemanager_cfg['risk_assess'];
}
unset($racemanager_cfg);

// check if demo is set to default on this index page
file_exists('demo_on.txt') ? $mode = "demo" : $mode = "live";
$debug = 0;

// initialise optional blocks
$links_block_htm = "";
$risk_block_htm = "";

// initialise database access
$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host']);

// check if system is down for maintenance
if (file_exists('maintenance_on.txt'))
{
    // undergoing maintenance message
    $application_block_htm =  <<<EOT
    <div>
        <h3 class="text-info text-center display-6">Sorry! - raceManager is currently ...</h3>
        <div style="text-align: center;">
            <img src="common/images/web_graphics/maintenance.jpg" alt="under maintenance" class="img-responsive img-rounded center-block" style="width: 300px;"></img><br>
		    <a href="https://www.freepik.com/free-vector/maintenance-background-design_1000106.htm#page=2&query=under%20maintenance&position=5&from_view=keyword">
		    <span style="font-size: 0.5em">Image by lexamer on Freepik</span></a>
        </div>
    </div>
EOT;

}
else
{

    $application_block_htm = <<<EOT
    <div class="container mb-3">
        <div class="row">
            <div class="col-md-3">
                <a class="btn btn-info d-grid" href="./rm_sailor/index.php?demo=$mode" target="_blank" role="button">
                <span style="font-size: 1.7em;">Sailor</span>
                </a>
                <p class="text-secondary mt-3">Member entry and declaration interface for today&rsquo;s races</p>
            </div>
            
            <div class="col-md-3">
                <a class="btn btn-warning d-grid" href="common/scripts/loaderpage.php?text=starting RaceBox application&script=../../rm_racebox/rm_racebox.php?mode=$mode%26lang=en%26debug=$debug" role="button">
                <span style="font-size: 1.7em;">Race Box</span>
                </a>
                <p class="text-secondary mt-3">Race officer application to run a race; time the finish; create and publish the results. Supports class, handicap, average lap and pursuit racing.</p>
            </div>
            
            <div class="col-md-3">
                <a class="btn btn-info d-grid" href="rm_admin/app/login.php" target="_blank" role="button" >
                <span style="font-size: 1.7em;">Administration</span>
                </a>
                <p class="text-secondary mt-3">Administration interface for results coordinator to configure the system and administer the race results.</p>
            </div>
            
            <div class="col-md-3">
                <a class="btn btn-info d-grid" href="rm_web/rm_web.php" target="_blank" role="button" >
                <span style="font-size: 1.7em;">Club Website</span>
                </a>
                <p class="text-secondary mt-3">Dynamic pages which can be incorporated in your club website - programme, results, trophy winners etc.</p>
            </div>   
        </div>
    </div>
EOT;

    // links block  - optional block containing links to useful information - configured in t_link table

    $local_links = $db_o->run("SELECT * FROM t_link WHERE category = ? order by listorder", array("index") )->fetchall();
    if (count($local_links) > 0)
    {
        if (!empty($local_links))
        {
            $local_links_htm = "";
            foreach ($local_links as $link)
            {
                $local_links_htm.= <<<EOT
                <a type="button" class="btn btn-md btn-outline-{$link['style']} mx-3 my-2 icon-link"
                      data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="list-tooltip" data-bs-title="{$link['tip']}"
                      href="{$link['url_link']}" target="_blank">
                    {$link['label']}&nbsp;&nbsp;<i class="{$link['icon']}"></i>
                 </a>
EOT;
            }

            $links_block_htm = <<<EOT
            <div class="container text-left">
                <div class="row">
                    <div id = "inline_text" class="text-info fs-4"><b>Useful Information</b></div> 
                    <div id = "inline_block" class="col-12 p-3 border border-info-subtle border-4 rounded">                                         
                        $local_links_htm
                    </div>
                    <div class="col">&nbsp;</div>
                </div>
            </div>
EOT;
        }
    }

    if ($cfg['risk_assess'])
    {
        // risk form block - optional block to request OOD to complete an risk assessment

        // first check if we have already had a risk assessment submitted today
        $risk_assessment = $db_o->run("SELECT * FROM t_riskassess WHERE DATE(date) = CURDATE();", array() )->fetchall();
        if ($risk_assessment)
        {
            $risk_block_htm = <<<EOT
            <div class="container text-left">
                <div class="row">
                    <div id = "inline_text" class="text-success fs-4"><b>Risk Assessment</b></div> 
                    <div id = "inline_block" class="col-12 p-3 border border-success-subtle border-4 rounded">                                         
                        <div class="row">
                            <div class="col-8 text-success">
                                <h4 class="mx-3"><b>Conditions changed?</b> redo risk assessment</h4>
                            </div>
                            <div id="div_center" class="col-4" style="margin-left: auto;">
                                <a href="./rm_risk/risk_assessment.php?form=CR_risk_assessment_cfg.json" 
                                class="btn btn-success btn-lg " tabindex="-1" role="button" aria-disabled="true" target="_blank">New Risk Assessment</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
EOT;
        }
        else
        {
            $risk_block_htm = <<<EOT
            <div class="container text-left flash ">
                <div class="row">
                    <div id = "inline_text" class="text-danger fs-4"><b>Risk Assessment</b></div> 
                    <div id = "inline_block" class="col-12 p-3 border border-danger-subtle border-4 rounded">                                         
                        <div class="row">
                            <div class="col-8 ">
                                <h4 class="mx-3 text-danger flash"><b>Important !</b> - before you start please complete a risk assessment</h4>
                                <p class="mx-3 fs-6 text-primary-subtle">The Sailing Committee needs you to complete a <u>simple</u> risk assessment form before starting the race.</p>
                            </div>
                            <div id="div_center" class="col-4" style="margin-left: auto;">
                                <a href="./rm_risk/risk_assessment.php?form=CR_risk_assessment_cfg.json" class="btn btn-danger btn-lg " tabindex="-1" role="button" aria-disabled="true" target="_blank">Risk Assessment</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
EOT;
        }
    }
}

echo <<<EOT
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>{$cfg['sys_name']}</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="icon" href="./common/images/logos/favicon.png">
        
        <!-- Bootstrap core CSS -->
        <link   rel="stylesheet" href="common/oss/bootstrap532/css/flatly_bootstrap.min.css" >
        <link href="common/oss/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        <script type="text/javascript" src="common/oss/bootstrap532/js/bootstrap.bundle.min.js"></script>
        
        <style>
            body { padding-top: 20px; font-family: Kalinga, Arial, sans-serif;}
            .brand-splash { font-weight: bold; font-size: 120%; color: orange;}
            #inline_text  { background-color: #FFF; text-align: left; position: relative; z-index: 1000; width: 260px; margin-left: 10%;}
            #inline_block { padding: 20px; position: relative; margin-top: -15px; }
            #div_center   { display: flex; align-items: center; justify-content: center; }
        </style> 
        
        <style>    
            .flash { animation: blink 1000ms 4; }
            @keyframes blink {
                0%   { opacity: 1; }
                50%  { opacity: 0; }
                100% { opacity: 1; }
            }       
        </style>   
    </head>      
    <body>
        <!-- Identity block -->
        <div class="row px-5 py-2 mb-4 border rounded-3" style="background-color: var(--bs-success-bg-subtle)">
           <div class="col-md-6">
               <h1><img src="common/images/logos/rmlogo-md.jpg"> raceManager</h1>
               <p class="text-danger-emphasis fs-4 px-5">An integrated system for race timing and results management at dinghy sailing clubs</p>
               <p><small>Release: {$cfg['sys_release']} - {$cfg['sys_version']} &nbsp; &nbsp;Copyright {$cfg['sys_copyright']}</small></p>
           </div>
           <div class="col-md-6">
               <div class="text-end" >
                   <img src="config/images/club_banner.png" alt="club specific image" class="img-responsive img-rounded"  style="max-height:150px !important;"></img>
               </div>
           </div>
        </div>
        <div>$application_block_htm</div>
        <div>$links_block_htm</div>
        <div>$risk_block_htm</div>
        <script>
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        </script>
    </body>
</html>
EOT;

