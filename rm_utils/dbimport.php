<?php
/* ----------------------------------------------------------------------------------------------



*/

/* TO DO
    - code document dbimport and import class
    - review code
    - update templates
*/

session_start();
$loc  = "..";
$page = "dbimport";     //
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
$validtypes = array("classes", "events", "rotas", "competitors");

include ("{$loc}/common/classes/html_class.php");
include ("{$loc}/common/classes/db_class.php");
include ("{$loc}/common/classes/boat_class.php");
include ("{$loc}/common/classes/comp_class.php");
include ("{$loc}/common/classes/event_class.php");
include ("{$loc}/common/classes/rota_class.php");
include ("{$loc}/common/classes/import_class.php");
include ("{$loc}/common/lib/util_lib.php");

include ("{$loc}/config/startup_cfg.php");

if (empty($_REQUEST['importtype']) OR (!in_array($_REQUEST['importtype'], $validtypes)))
{
    exitnicely("import type [{$_REQUEST['importtype']}] not recognised", "", $scriptname, $loc);
}
else
{
    $type_opts = get_typeoptions($_REQUEST['importtype']);
    if (!$type_opts)
    { exitnicely("import options not found for type: {$_REQUEST['importtype']}", "",$scriptname, $loc); }
}

if (empty($_REQUEST['pagestate'])) { $_REQUEST['pagestate'] = "init"; }

/* ------------ file selection page ---------------------------------------------*/

if ($_REQUEST['pagestate'] == "init")
{
    $_SESSION['adminlog'] = "{$loc}/logs/adminlogs/admin_$today.log";
    $_SESSION['syslog'] = "{$loc}/logs/syslogs/util_$today.log";

    if (empty($_REQUEST['lang'])) { $_SESSION['lang'] = "en"; }
    include ("{$loc}/config/{$_SESSION['lang']}-utils-lang.php");

    if (is_readable("$loc/config/{$_SESSION['app_ini']}"))
        { u_initconfigfile("$loc/config/{$_SESSION['app_ini']}"); }
    else
        { exitnicely("import configuration file not found", "", $scriptname, $loc); }

    $html = new HTMLPAGE("en");
    $html->html_header($loc, "$loc/rm_racebox/css/rm_racebox.css", true, false, 0, "import {$type_opts['title']}");  // header
    $html->html_body("");                                                                                            // body tag
    $html->html_addhtml(html_snippet("navbar", array("raceManager Import: ".ucwords($type_opts['title']))));                           // navbar
    $html->html_addhtml(html_snippet("filepick", array($type_opts['instructions'], $type_opts['type'], $type_opts['title'])));                              // page
    $html->html_endscripts();
    echo $html->html_render();                                                                                       // render page
}

/* ------------ submit page ---------------------------------------------*/

elseif ($_REQUEST['pagestate'] == "submit")
{
    $file_status   = false;
    $read_status   = false;
    $data_status   = false;
    $import_status = false;
    $num_before = 0;
    $num_after = 0;

    $db_o = new DB;
    $csv_o = new IMPORT_CSV($db_o, $type_opts['fieldmap']);

    $file_status = $csv_o->check_importfile($_FILES);

    if ($file_status)
    {
        $read_status = $csv_o->read_importdata();
        if ($read_status)
        {
            $import = $csv_o->get_importdata();
            $import_ref = array();
            $data_error = custom_validation($_REQUEST['importtype'], $type_opts['table']);

            if (empty($data_error)) { $data_status = true; }

            if ($data_status)
            {
                $csv_o->put_importdata($import);        // set data to be imported
                $csv_o->put_importref($import_ref);     // set info to determine update/insert

                $num_before = count_records($_REQUEST['importtype'], $db_o);

                // create recovery copy table and file
                $bkup_file = $db_o->db_table_to_file("$loc/tmp/db_backup", $type_opts['table']);
                $bkup_table = $db_o->db_table_to_temptable($type_opts['table']);

                // import
                $import_status = $csv_o->import_data( $type_opts['table'], $type_opts['truncate']);
                $num_after = count_records($_REQUEST['importtype'], $db_o);
            }
        }
    }

    // create page
    $pbufr = "";
    if ($file_status AND $read_status AND $data_status AND $import_status)
    {
        $num_imports = $csv_o->get_numimports();
        $pbufr.= html_snippet("successpanel_header", array($type_opts['title'], $num_imports, $num_before, $num_after));
        $pbufr.= $csv_o->get_import_val();
    }
    else
    {
        $pbufr.= html_snippet("failpanel_header");

        if (!$file_status)
        {
            $pbufr.= "<h3>File Problems:</h3>";
            $pbufr.= $csv_o->get_file_val();
            $pbufr.= html_snippet("fileproblem");
        }
        else
        {
            if (!$data_status)
            {
                $pbufr .= "<h3>Data Problems:</h3>";
                $err_count = 0;
                foreach ($data_error as $line=>$error)
                {
                    $err_count++;
                    if ($err_count>10)
                    {
                        $pbufr.= " --- truncated error report<br>";
                        break;
                    }
                    $pbufr.= "Row $line: ".rtrim($error, "; ")."<br>";
                }
                $pbufr.= html_snippet("dataproblem");
            }
            else
            {
                $pbufr.= "<h3>Import Report:</h3>";
                $import_report = $csv_o->get_import_val();
                $pbufr.= $import_report;
                $fail_line = $csv_o->get_fail_line();
                if ($fail_line!=0)
                {
                    if ($fail_line!=2)
                    {
                        $pbufr.= html_snippet("importproblem", array($bkup_file));
                    }

                    // log update - fail
                    error_log(date('H:i:s')." -- Data import: {$type_opts['title']} (table: {$type_opts['table']}) -- FAILED
            \n $import_report \n Failed on line $fail_line".PHP_EOL, 3, $_SESSION['adminlog']);
                }
                else
                {
                    // log update - success
                    error_log(date('H:i:s')." -- Data import: {$type_opts['title']} (table: {$type_opts['table']})
            \n $import_report".PHP_EOL, 3, $_SESSION['adminlog']);
                }
            }
        }
    }

    $pbufr.= <<<EOT
    </div>
    </div>
EOT;
    $pbufr.= html_snippet("close_button");



    $page = <<<EOT
            <div class="container" style="margin-top:100px;">
                $pbufr
            </div>
EOT;

    $html = new HTMLPAGE("en");
    $html->html_header($loc, "$loc/rm_racebox/css/rm_racebox.css", true, false, 0, "import {$type_opts['title']}"); // header
    $html->html_body("");                                                                                     // body tag
    $html->html_addhtml(html_snippet("navbar", array("raceManager Import: ".ucwords($type_opts['title'])."  result....")));            // navbar
    $html->html_addhtml($page);                                                                              // page
    $html->html_endscripts();
    echo $html->html_render();                                                                                // render page
}


function custom_validation($importtype, $table)
{
    global $import;

    $i = 1;
    $error = array();
    foreach($import as $key=>$row)
    {
        $i++;
        if     ($importtype == "classes")  { $error = val_classes($i, $key, $row, $table); }
        elseif ($importtype == "events")   { $error = val_events($i, $key, $row, $table);  }
        elseif ($importtype == "rotas")    { $error = val_rotas($i, $key, $row, $table);   }
        elseif ($importtype == "competitors")  { $error = val_competitors($i, $key, $row, $table); }
    }

    return $error;
}



function html_snippet($snippet, $var = array())
{
    $html = "";
    if ($snippet == "navbar")
    {
        $html = <<<EOT
          <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
              <div class="container" style="margin-left:10px; margin-right:10px;">
                <div class="navbar-header"><p class="navbar-brand rm-brand-title" >{$var[0]}</p></div>
              </div>
          </div>
          <!-- script to allow page to be closed with quit button -->
          <script>
          function quitBox(cmd)
          {
              if (cmd=='quit')
              { open(location, '_self').close(); }
              return false;
          }
          </script>
EOT;
    }
    elseif ($snippet == "failpanel_header")
    {
        $html = <<<EOT
         <div class="panel panel-danger">
             <div class="panel-heading">
                <h3>Import failed:</h3>
             </div>
         <div class="panel-body" style="padding-left: 30px">
EOT;
    }
    elseif ($snippet == "successpanel_header")
    {
        $html = <<<EOT
         <div class="panel panel-success">
         <div class="panel-heading">
            <h3>Import successful:</h3>
         </div>
         <div class="panel-body" style="padding-left: 30px">
         <p><strong>{$var[1]} records in import file</strong></p>
         <p>Before import  <b>{$var[2]}</b> {$var[0]} - after import <b>{$var[3]}</b> {$var[0]}</p>
EOT;
    }
    elseif ($snippet == "fileproblem")
    {
        $html = <<<EOT
         <div class="alert alert-warning alert-dismissible" style="padding-left: 60px" role="alert">
         <h3>Suggested Fix! </h3>
         <p>Please check your import file and make sure that it has a csv file type and
            the first row has field labels as defined in the import instructions for this type of data.</p>
         </div>
EOT;
    }
    elseif ($snippet == "dataproblem")
    {
        $html = <<<EOT
         <div class="alert alert-warning alert-dismissible" style="padding-left: 60px" role="alert">
               <h3>Suggested Fix! </h3>
               <p>Please correct the data in the rows reported above and try again.</p>
         </div>
EOT;
    }
    elseif ($snippet == "importproblem")
    {
        $html = <<<EOT
         <div class="alert alert-warning alert-dismissible" style="padding-left: 60px" role="alert">
               <h3>Suggested Fix!</h3>
               <span class="text-warning">Your database may be corrupted.</span><br><br>
               To recover from this please read the Imports section in the user guide
               [your back up recovery file can be found at <strong>{$var[0]}</strong> in your raceManager folder.
            </div>
EOT;
    }

    elseif ($snippet == "close_button")
    {
        $html = <<<EOT
         <div class="margin-top-40">

                    <a class="btn btn-lg btn-warning" style="min-width: 200px;" type="button" name="Quit" id="Quit"
                       onclick="return quitBox('quit');">
                       <span class="glyphicon glyphicon-remove"></span>&nbsp;<b>close</b>
                    </a>

         </div>
EOT;
    }
    elseif ($snippet == "filepick")
    {
        $html = <<<EOT
         <div class="container" style="margin-top: 40px;">
        <div class="jumbotron" style="margin-top: 40px;">
            {$var[0]}
        </div>
        <form enctype="multipart/form-data" id="selectfileForm" action="dbimport.php?pagestate=import" method="post">
        <div class="row">
            <div class="col-sm-6 col-sm-offset-3">
                    <h4>Select import file</h4>
                    <div>
                    <span class="file-input btn btn-info btn-lg btn-file">
                        <input type="file" style="width:400px !important" name="importfile" value=""  required  >
                    </span>
                    </div>
            </div>
        </div>
        <input type="hidden" name="pagestate" value="submit">
        <input type="hidden" name="importtype" value="{$var[1]}">
        <div class="row margin-top-40">
            <div class="col-sm-8 col-sm-offset-1">
                <div class="pull-left">
                    <a class="btn btn-lg btn-warning" style="min-width: 200px;" type="button" name="Quit" id="Quit" onclick="return quitBox('quit');">
                    <span class="glyphicon glyphicon-remove"></span>&nbsp;<b>quit</b></a>
                </div>
                <div class="pull-right">
                    <button type="submit" class="btn btn-lg btn-danger"  style="min-width: 200px;" >
                    <span class="glyphicon glyphicon-ok"></span>&nbsp;<b>import {$var[2]}</b></button>
                </div>
            </div>
        </div>
        </form>
    </div>
EOT;
    }

    return $html;
}

function get_typeoptions($importtype)
{
  $opts = array();
  $opts['type'] = $importtype;
  
  if (strtolower($importtype) == "classes")
  {
    $opts['title'] = "classes";
    $opts['table'] = "t_class";
    $opts['update'] = true;
    $opts['truncate'] = false;
    $opts['instructions'] = <<<EOT
    <h3>Import <span style="color: darkred; font-weight: bold">class</span> details into the raceManager database using a csv file</h3>
    <p><small>Use the "import_classes" template file in the install/import_templates directory
    or a classes export from your current raceManager database as a starting point.  Add new classes, or change
    details of existing classes in the csv file and then import it here.<br><br>The first line of your file must
    contain the field names, and you must comply with the mandatory and unique field requirements that are
    defined in the template.</small></p>
EOT;
   // dbase --> csv file
      $opts['fieldmap'] = array(
        "classname" => 'classname',
        "nat_py"    => 'nat_py',
        "local_py"  => 'local_py',
        "category"  => 'category',
        "rig"       => 'rig',
        "crew"      => 'crew',
        "spinnaker" => 'spinnaker',
        "engine"    => 'engine',
        "keel"      => 'keel',
        "popular"   => 'popular',
        "info"      => 'info'
    );
  }
  
  elseif (strtolower($importtype) == "events")
  {
    $opts['title'] = "events";
    $opts['table'] = "t_event";
    $opts['update'] = true;
    $opts['truncate'] = false;
    $opts['instructions'] = <<<EOT
    <h3>Import <span style="color: darkred; font-weight: bold">event</span> details into the raceManager database using a csv file</h3>
    <p>Use the "import_events" template file in the install/import_templates directory
    or an events export from your current raceManager database as a starting point.  Add new events, or change
    details of existing events in the csv file and then import it here.<br><br>The first line of your file must
    contain the field names, and you must comply with the mandatory and unique field requirements that are
    defined in the template.</p>
EOT;
    $opts['fieldmap'] = array(
        "id"          => 'id',
        "event_date"  => 'event_date',
        "event_start" => 'event_start',
        "event_name"  => 'event_name',
        "seriescode"  => 'seriescode',
        "event_type"  => 'event_type',
        "event_format"=> 'event_format',
        "event_entry" => 'event_entry',
        "event_open"  => 'event_open',
        "tide_time"   => 'tide_time',
        "tide_height" => 'tide_height',
        "event_notes" => 'event_notes',
        "weblink"     => 'weblink',
    );
  }
  
  elseif (strtolower($importtype) == "rotas")
  {
    $opts['title'] = "rota members";
    $opts['table'] = "t_rotamember";
    $opts['update'] = true;
    $opts['truncate'] = false;
    $opts['instructions'] = <<<EOT
    <h3>Import <span style="color: darkred; font-weight: bold">rota members</span> into the raceManager database using a csv file</h3>
    <p>Use the "import_rotas" template file in the install/import_templates directory
    or a rota export from your current raceManager database as a starting point.  Add new rota members, or change
    details of existing rota members in the csv file and then import it here.<br><br>The first line of your file must
    contain the field names, and you must comply with the mandatory and unique field requirements that are
    defined in the template.</p>
EOT;
    $opts['fieldmap'] = array(
        "memberid"   => 'memberid',
        "firstname"  => 'firstname',
        "familyname" => 'familyname',
        "rota"       => 'rota',
        "phone"      => 'phone',
        "email"      => 'email',
        "note"       => 'note',
        "partner"    => 'partner'
    );
  }

  elseif (strtolower($importtype) == "competitors")
  {
    $opts['title'] = "sailors";
    $opts['table'] = "t_competitor";
    $opts['update'] = true;
    $opts['truncate'] = false;
    $opts['instructions'] = <<<EOT
    <h3>Import <span style="color: darkred; font-weight: bold">sailors</span> into the raceManager database using a csv file</h3>
    <p>Use the "import_sailors" template file in the install/import_templates directory
    or a sailors export from your current raceManager database as a starting point.  Add new sailors, or change
    details of existing sailors in the csv file and then import it here.<br><br>The first line of your file must
    contain the field names, and you must comply with the mandatory and unique field requirements that are
    defined in the template.</p>
EOT;
    $opts['fieldmap'] = array(
        "id"          => 'id',
        "classid"     => 'classid',
        "sailnum"     => 'sailnum',
        "boatname"    => 'boatname',
        "helm"        => 'helm',
        "helm_dob"    => 'helm_dob',
        "helm_email"  => 'helm_email',
        "crew"        => 'crew',
        "crew_dob"    => 'crew_dob',
        "crew_email"  => 'crew_email',
        "club"        => 'club',
        "skill_level" => 'skill_level',
        "personal_py" => 'personal_py',
        "flight"      => 'flight',
        "regular"     => 'regular',
        "prizelist"   => 'prizelist',
        "grouplist"   => 'grouplist',
        "memberid"    => 'memberid'
    );
  }
  else
  {
    return false;
  }
  return $opts;
}

function val_classes($i, $key, $row, $table)
{
    global $import;
    global $import_ref;
    global $error;
    $db_o = new DB;
    $boat_o = new BOAT($db_o);

    // check for existence
    $import_ref[$i]['ref']    = $row['classname'];
    $rs_class = $boat_o->boat_classexists($row['classname'], false);
    if ($rs_class)
    {
        $import_ref[$i]['exists'] = true;
        $import_ref[$i]['id'] = $rs_class['id'];
    }

    // class name - check not empty
    if (empty($row['classname']))   // required
    {
      $error[$i].= "class name must be supplied; ";
    }
    else   // and unique
    {
        $j = 1;
        foreach ($import as $row2)
        {
            $j++;
            if ($row['classname']==$row2['classname'] AND $i != $j)
            {
                $error[$i].= "class name must be unique; ";
                break;
            }
        }
    }

    // nat_py - must be provided and must be a number
    $py_range = array('options' => array( 'min_range' => 400, 'max_range' => 2000 ));
    if (empty($row['nat_py']) OR filter_var( $row['nat_py'], FILTER_VALIDATE_INT, $py_range ) == FALSE)
        { $error[$i].= "national py must be provided and must be a positive integer number; "; }

    // local_py  - if not provided set to national py
    if (empty($row['local_py']))
        { $row['local_py'] = $row['nat_py']; }
    elseif (filter_var( $row['local_py'], FILTER_VALIDATE_INT, $py_range ) == FALSE)
        { $error[$i].= "local py must be a positive integer number; ";}

    // category - must be one of set codes - change to upper case
    if (!$db_o->db_checksystemcode("class_category", $row['category']))
        { $error[$i].= "class category code is not valid; "; }

    // rig - must be one of set codes - change to upper case
    if (!$db_o->db_checksystemcode("class_rig", $row['rig']))
        { $error[$i].= "class rig code is not valid; "; }

    // crew - must be one of set codes - change to upper case
    if (!$db_o->db_checksystemcode("class_crew", $row['crew']))
        { $error[$i].= "class crew code is not valid; "; }

    // spinnaker - must be one of set codes - change to upper case
    if (!$db_o->db_checksystemcode("class_spinnaker", $row['spinnaker']))
        { $error[$i].= "class spinnaker code is not valid; "; }

    // engine - must be one of set codes - change to upper case
    if (!$db_o->db_checksystemcode("class_engine", $row['engine']))
        { $error[$i].= "class engine code is not valid; "; }

    // keel - must be one of set codes - change to upper case
    if (!$db_o->db_checksystemcode("class_keel", $row['keel']))
        { $error[$i].= "class keel code is not valid; "; }

    // popular - set to 0 if not set
    if ($row['popular']!="1") { $row['popular'] = "0"; }

    // rya_id - set to none if not set - deal with special characters
    if (empty($row['rya_id'])) { $row['rya_id'] = "none"; }

    $import[$key]['classname'] = addslashes(ucfirst($row['classname']));
    $import[$key]['nat_py']    = addslashes($row['nat_py']);
    $import[$key]['local_py']  = addslashes($row['local_py']);
    $import[$key]['category']  = addslashes(strtoupper($row['category']));
    $import[$key]['crew']      = addslashes(strtoupper($row['crew']));
    $import[$key]['rig']       = addslashes(strtoupper($row['rig']));
    $import[$key]['spinnaker'] = addslashes(strtoupper($row['spinnaker']));
    $import[$key]['engine']    = addslashes(strtoupper($row['engine']));
    $import[$key]['keel']      = addslashes(strtoupper($row['keel']));
    $import[$key]['popular']   = addslashes($row['popular']);
    $import[$key]['info']      = addslashes($row['info']);

    return $error;
}

function val_events($i, $key, $row, $table)
{
    global $import;
    global $import_ref;
    global $error;
    $db_o = new DB;
    $event_o = new EVENT($db_o);

    // check required fields
    $event_date = date("Y-m-d", strtotime(str_replace('/', '-', $row['event_date'])));
    if (empty($row['event_date']))
        { $error[$i] .= "date missing; "; }
    else
        { if (strtotime($event_date) == false) { $error[$i] .= "date invalid; "; } }

    if (empty($row['event_name'])) { $error[$i] .= "event name missing; "; }

    if (!$db_o->db_checksystemcode("event_type", strtolower($row['event_type'])))
        { $error[$i].= "event type not recognised; "; }

    if (!$db_o->db_checksystemcode("event_access", strtolower($row['event_open'])))
        { $error[$i] .= "event open setting not recognised; "; }

    if (strtolower($row['event_type']) == "racing")
    {
        // event format
        if (!$event_o->racecfg_findbyname($row['event_format']))
            { $error[$i] .= "event format missing/not recognised; "; }

        // event entry
        if (!$db_o->db_checksystemcode("entry_type", strtolower($row['event_entry'])))
            { $error[$i] .= "entry type missing or not recognised; "; }

        // series code
        if (!empty($row['seriescode']))
        {
            if (!$event_o->event_getseries($row['seriescode']))
                { $error[$i] .= "series not recognised; "; }
        }
    }

    if (!empty($row['tide_time']) and strtotime($row['tide_time'])==false)
        { $error[$i] .= "tide time is not a valid time; "; }

    if (!empty($row['web_link']) AND filter_var($row['web_link'], FILTER_VALIDATE_URL) === false)
        { $error[$i] .= "web link is not a valid URL; "; }

    // do special checks on id to see if this is a new record or an existing record
    $import_ref[$i]['exists'] = false;
    $import_ref[$i]['ref'] = "{$row['event_name']}[{$event_date}T{$row['event_start']}]";

    // check if event exists - either by id or by name and date/time
    $query = "";
    if (empty($row['id']))
    {
        $query  = "SELECT id FROM $table WHERE event_date = '$event_date' "
                 ."AND event_start = '{$row['event_start']}' AND event_name = '{$row['event_name']}'";
    }
    elseif (filter_var($row['id'],FILTER_VALIDATE_INT, array('options' => array( 'min_range' => 1 )) ) === true)
    {
        $query  = "SELECT id FROM $table WHERE id = {$row['id']}";
    }
    if ($query)
    {
        $detail = $db_o->db_get_row( $query );
        if ($detail)   // looks like it exists
        {
            $import_ref[$i]['exists'] = true;
            $import_ref[$i]['id'] = $detail['id'];
        }
    }

    if (empty($row['id']))     // check if really a new event
    {
        $query  = "SELECT id FROM $table WHERE event_date = '$event_date' "
                  ."AND event_start = '{$row['event_start']}' AND event_name = '{$row['event_name']}'";
        $detail = $db_o->db_get_row( $query );
        if ($detail)   // looks like it exists
        {
            $import_ref[$i]['exists'] = true;
            $import_ref[$i]['id'] = $detail['id'];
        }
    }
    else          // if id is specified - check event exists
    {
        if (filter_var($row['id'],FILTER_VALIDATE_INT ) === false)   // not a valid record id
        {
            $error[$i] .= "id must be an integer value; ";
        }
        else
        {
            $query  = "SELECT id FROM $table WHERE id = {$row['id']}";
            $detail = $db_o->db_get_row( $query );
            if ($detail)
            {
                $import_ref[$i]['exists'] = true;
                $import_ref[$i]['id'] = $row['id'];
            }
            else
            {
                $error[$i] .= "specified id does not exist in database; ";
            }
        }
    }

    // set output array
    unset($import[$key]['id']);
    $import[$key]['event_date']  = addslashes($event_date);
    $import[$key]['event_start'] = addslashes($row['event_start']);
    $import[$key]['event_name']  = addslashes($row['event_name']);
    $import[$key]['seriescode']  = addslashes(strtoupper($row['seriescode']));
    $import[$key]['event_type']  = addslashes($row['event_type']);
    $import[$key]['event_format']= addslashes($racecfg['id']);
    $import[$key]['event_entry'] = addslashes($row['event_entry']);
    $import[$key]['event_status']= "scheduled";
    $import[$key]['event_open']  = addslashes($row['event_open']);
    $import[$key]['tide_time']   = addslashes($row['tide_time']);
    $import[$key]['tide_height'] = addslashes($row['tide_height']);
    $import[$key]['event_notes'] = addslashes($row['event_notes']);
    $import[$key]['weblink']     = addslashes($row['weblink']);

  return $error;
 
}


function val_rotas($i, $key, $row, $table)
{
    global $import;
    global $import_ref;
    global $error;
    $db_o = new DB;

    // check for existence
    $import_ref[$i]['exists'] = false;
    $import_ref[$i]['ref'] = ucwords($row['firstname']." ".$row['familyname'])." [".$db_o->db_getsystemlabel("rota_type", $row['rota'])."]";
    $query  = "SELECT * FROM $table WHERE firstname = '{$row['firstname']}'"
              ." AND familyname = '{$row['familyname']}' AND rota = '{$row['rota']}'";
    $detail = $db_o->db_get_row( $query );
    if ($detail)
    {
        $import_ref[$i]['exists'] = true;
        $import_ref[$i]['id'] = $detail['id'];
    }

    if (empty($row['firstname'])) { $error[$i] .= "first name must be supplied; "; }

    if (empty($row['familyname'])) { $error[$i] .= "surname must be supplied; "; }

    if (!$db_o->db_checksystemcode("rota_type", $row['rota'])) { $error[$i] .= "rota code [{$row['rota']}] is not valid; "; }

    $import[$key]['firstname']  = addslashes(ucfirst($row['firstname']));
    $import[$key]['familyname'] = addslashes(ucfirst($row['familyname']));
    $import[$key]['rota']       = addslashes(strtolower($row['rota']));
    $import[$key]['memberid']   = addslashes($row['memberid']);
    $import[$key]['phone']      = addslashes($row['phone']);
    $import[$key]['email']      = addslashes($row['email']);
    $import[$key]['note']       = addslashes($row['note']);
    $import[$key]['partner']    = addslashes($row['partner']);

    return $error;
}

function val_competitors($i, $key, $row, $table)
{
    global $import;
    global $import_ref;
    global $error;
    $db_o = new DB;
    $boat_o = new BOAT($db_o);

    // class - convert from name to id and check it exists
    $classname = $row['classid'];
    if (!empty($classname))
    {
        $boat = $boat_o->boat_getdetail($classname);   // convert to id from name
        if (!empty($boat))
        {
            $row['classid'] = $boat['id'];
        }
        else
        {
            $error[$i] .= "class is not recognised; ";
        }
    }
    else
    {
        $error[$i] .= "class must be specified; ";
    }

    $import_ref[$i]['exists'] = false;
    $import_ref[$i]['ref'] = $classname." ".$row['sailnum'];

    if (empty($row['id']))     // check if really a new competitor
    {
        $query  = "SELECT a.id, classname, helm, sailnum FROM $table as a "
            ."JOIN t_class as b ON a.classid=b.id WHERE a.classid = {$row['classid']} "
            ."AND a.helm = '{$row['helm']}' AND a.sailnum = '{$row['sailnum']}'";
        $detail = $db_o->db_get_row( $query );
        if ($detail)   // looks like it exists
        {
            $import_ref[$i]['exists'] = true;
            $import_ref[$i]['id'] = $detail['id'];
        }
    }
    else                       // if id is specified - check competitor exists
    {
        if (filter_var($row['id'],FILTER_VALIDATE_INT ) === false)   // not a valid record id
        {
            $error[$i] .= "if specified id must be an integer value; ";
        }
        else
        {
            $query  = "SELECT classname, helm, sailnum FROM $table as a "
                ."JOIN t_class as b ON a.classid=b.id WHERE a.id = {$row['id']}";
            $detail = $this->db->db_get_row( $query );
            if ($detail)
            {
                $import_ref[$i]['exists'] = true;
                $import_ref[$i]['ref'] = $detail['classname']." ".$detail['sailnum'];
                $import_ref[$i]['id'] = $row['id'];
            }
            else
            {
                $error[$i] .= "specified id does not exist in database; ";
            }
        }
    }

    if (empty($row['sailnum']))
        { $error[$i] .= "sail number must be specified; "; }

    if (empty($row['helm']))
        { $error[$i] .= "helm name must be specified; "; }

    $row['club'] = ucwords($row['club']);
    $row['club'] = str_replace("Sailing Club", "SC", $row['club']);
    $row['club'] = str_replace("Yacht Club", "YC", $row['club']);

    if (!empty($row['helm_dob']) and strtotime($row['helm_dob']) == false)
        { $error[$i] .= "invalid date format for helm birth date; "; }

    if (!empty($row['crew_dob']) and strtotime($row['crew_dob']) == false )
        { $error[$i] .= "invalid date format for crew birth date; "; }

    if (!empty($row['skill_level']))
    {
        if (filter_var($row['skill_level'], FILTER_VALIDATE_INT, array("options" => array("min_range"=>1, "max_range"=>5))) === false)
            { $error[$i] .= "skill level must be an integer between 1 and 5; "; }
    }

    if (!empty($row['personal_py']))
    {
        if (filter_var($row['personal_py'], FILTER_VALIDATE_INT) === false)
            { $error[$i] .= "personal PN must be an integer; "; }
    }

    if (!empty($row['flight']))
    {
        $row['flight'] = trim($row['flight']);
        if (!$db_o->db_checksystemcode("flight_list", $row['flight']))
        { $error[$i].= "flight specified is not recognised; "; }

        if (filter_var($row['regular'], FILTER_VALIDATE_INT) === false)
        { $error[$i].= "regular flag must be 1 or 0; "; }
    }

    if (!empty($row['regular']))
    {
        if (filter_var($row['regular'], FILTER_VALIDATE_INT, array("options" => array("min_range"=>0, "max_range"=>1))) === false)
        { $error[$i] .= "regular flag must be either 0 or 1; "; }
    }

    if (!empty($row['grouplist']))
    {
        $row['grouplist'] = preg_replace('/\s*,\s*/', ',', $row['grouplist']);   // remove spaces before and after commas
        $groups = explode(",",$row['grouplist']);
        foreach ($groups as $group)
        {
            if (!$db_o->db_checksystemcode("competitor_list", $group))
            { $error[$i].= "group [$group] not recognised; "; }
        }
    }

    if (!empty($row['prizelist']))
    {
        $row['prizelist'] = preg_replace('/\s*,\s*/', ',', $row['prizelist']);   // remove spaces before and after commas
        $prizes = explode(",",$row['prizelist']);
        foreach ($prizes as $prize)
        {
            if (!$db_o->db_checksystemcode("prize_list", $prize))
            { $error[$i].= "prize group [$prize] not recognised; "; }
        }
    }

    unset($import[$key]['id']);
    $import[$key]['classid']     = addslashes($row['classid']);
    $import[$key]['sailnum']     = addslashes($row['sailnum']);
    $import[$key]['boatnum']     = addslashes($row['sailnum']);
    $import[$key]['boatname']    = addslashes($row['boatname']);
    $import[$key]['club']        = addslashes($row['club']);
    $import[$key]['helm']        = addslashes(ucwords($row['helm']));
    if (!empty($row['helm_dob']))
        { $import[$key]['helm_dob']    = date("Y-m-d", strtotime($row['helm_dob'])); }
    else
        { unset($import[$key]['helm_dob']); }
    $import[$key]['helm_email']  = addslashes($row['helm_email']);
    $import[$key]['crew']        = addslashes(ucwords($row['crew']));
    if (!empty($row['crew_dob']))
        { $import[$key]['crew_dob']    = date("Y-m-d", strtotime($row['crew_dob'])); }
    else
        { unset($import[$key]['crew_dob']); }
    $import[$key]['crew_email']  = addslashes($row['crew_email']);
    $import[$key]['skill_level'] = $row['skill_level'];
    $import[$key]['personal_py'] = $row['personal_py'];
    $import[$key]['flight']      = strtolower($row['flight']);
    $import[$key]['regular']     = $row['regular'];
    $import[$key]['prizelist']   = addslashes($row['prizelist']);
    $import[$key]['grouplist']   = addslashes($row['grouplist']);
    $import[$key]['memberid']    = addslashes($row['memberid']);

    return $error;
}

function count_records($importtype, $db_o)
{
    $count = 0;
    if ($importtype == "classes")
    {
        $rs_o = new BOAT($db_o);
        return $rs_o->boat_count(array());
    }
    elseif ($importtype == "events")
    {
        $rs_o = new EVENT($db_o);
        return $rs_o->event_count(array());
    }
    elseif ($importtype == "rotas")
    {
        $rs_o = new ROTA($db_o);
        return $rs_o->rota_countmembers(array());
    }
    elseif ($importtype == "competitors")
    {
        $rs_o = new COMPETITOR($db_o);
        return $rs_o->comp_count(array());
    }
    return $count;
}


function exitnicely($reason, $suggestion, $scriptname, $loc)
{
    if ($suggestion = "") {
        $suggestion = "Please contact your system administrator";
    }
    $msg = <<<EOT
  <div class="container" style="margin-top: 100px;">
    <div class="jumbotron" style="margin-top: 40px;">
        <h2>Unexpected Error</h2>
        <p class="lead">$reason</p>
        <p>$suggestion</p>
        <p><small>Script: $scriptname</small></p>
    </div>
  </div>
EOT;

    $html = new HTMLPAGE("en");
    $html->html_header($loc, "$loc/rm_racebox/css/rm_racebox.css", true, false, 0, "import");  // header
    $html->html_body("");                                                                      // body tag
    $html->html_addhtml(html_snippet("navbar", array("raceManager Import: ")));                                  // navbar
    $html->html_addhtml($msg);                                                                 // page
    $html->html_endscripts();
    echo $html->html_render();

    exit();
}



