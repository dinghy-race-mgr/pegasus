<?php
/* ----------------------------------------------------------------------------------------------



*/
// include files
$loc  = "..";
$page = "import_classes";     //
$scriptname = basename(__FILE__);
//include ("{$loc}/common/lib/util_lib.php");
include ("{$loc}/common/classes/html_class.php");
include ("{$loc}/common/classes/db_class.php");
include ("{$loc}/common/classes/boat_class.php");
include ("{$loc}/common/classes/import_class.php");

$sbufr = <<<EOT
<script>
function quitBox(cmd)
{
    if (cmd=='quit')
    {
        open(location, '_self').close();
    }
    return false;
}
</script>
EOT;

//FIXME include ("{$loc}/config/{$_SESSION['lang']}-utils-lang.php");

// FIXME - need to get this from somewhere
$_SESSION['db_host'] = "127.0.0.1";
$_SESSION['db_user'] = "root";
$_SESSION['db_pass'] = "";
$_SESSION['db_name'] = "pegasus";

$_SESSION['lang'] = "en";

// FIXME - sort out logs
$_SESSION['syslog'] = "{$loc}/logs/syslogs/import.log";

$nbufr = <<<EOT
<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
     <div class="container" style="margin-left:10px; margin-right:10px;">
          <div class="navbar-header">
               <p class="navbar-brand rm-brand-title" >raceManager Import: Classes</p>
          </div>
     </div>
</div>
EOT;

$lang = array(
    "instructions" => "<h2>Import class details into the raceManager database using a
    csv file</h2><p>Use the \"import_classes\" template file in the install/import_templates directory
    or a classes export from your current raceManager database as a starting point.  Add new classes, or change
    details of existing classes in the csv file and then import it here.<br><br>The first line of your file must
    contain the field names, and you must comply with the mandatory and unique field requirements that are
    defined in the template.</p>",
);


if ($_REQUEST['pagestate']=="init")
{
    // create file input button
    $pbufr = <<<EOT
    <div class="container" style="margin-top: 40px;">
        <div class="jumbotron" style="margin-top: 40px;">
            {$lang['instructions']}
        </div>
        <form enctype="multipart/form-data" id="selectfileForm" action="dbimport_class.php?pagestate=import" method="post">
        <div class="row">
            <div class="col-sm-6 col-sm-offset-3">
                    <form enctype="multipart/form-data" id="selectfileForm" action="dbimport_class.php?pagestate=import" method="post">
                    <h4>Select import file</h4>
                    <div>
                    <span class="file-input btn btn-info btn-lg btn-file">
                        <input type="file" style="width:400px !important" name="importfile" value=""  required  >
                    </span>
                    </div>
            </div>
        </div>
        <input type="hidden" name="pagestate" value="submit">
        <div class="row margin-top-40">
            <div class="col-sm-8 col-sm-offset-1">
                <div class="pull-left">
                    <a class="btn btn-lg btn-warning"  type="button" name="Quit" id="Quit" onclick="return quitBox('quit');">
                    <span class="glyphicon glyphicon-remove"></span>&nbsp;<b>quit</b></a>
                </div>
                <div class="pull-right">
                    <button type="submit" class="btn btn-lg btn-danger"  >
                    <span class="glyphicon glyphicon-ok"></span>&nbsp;<b>import classes</b></button>
                </div>
            </div>
        </div>
        </form>
    </div>
EOT;

    $html = new HTMLPAGE("en");
    // header - set to not refresh
    $html->html_header($loc, "$loc/rm_racebox/css/rm_racebox.css", true, false, 0, "import classes");  // header
    $html->html_body("");                               // body tag
    $html->html_addhtml($nbufr);                        // navbar
    $html->html_addhtml($sbufr);
    $html->html_addhtml($pbufr);                        // page

    $html->html_endscripts();
    $bufr = $html->html_render();                       // render page
    echo $bufr;
}


elseif ($_REQUEST['pagestate'] == "submit")
{
    $field_map = array(                                // database -> csv
        "classname" => "class",
        "acronym"   => "code",
        "nat_py"    => "national_py",
        "local_py"  => "local_py",
        "category"  => "hull_type",
        "rig"       => "rig_type",
        "crew"      => "crew_type",
        "spinnaker" => "spinnaker_type",
        "engine"    => "engine_type",
        "keel"      => "keel_type",
        "popular"   => "popular",
        "rya_id"    => "rya_code",
        "info"      => "info"
    );

    $field_req = array("classname", "nat_py", "category", "crew", "spinnaker", "rig");
    $field_unq = array("classname");

    $file_status = false;
    $read_status = false;
    $data_status = false;
    $import_status = false;

    $db_o = new DB;
    $csv_o = new IMPORT($db_o, $field_map);

    $file_status = $csv_o->check_importfile($_FILES);

    if ($file_status)
    {

        $read_status = $csv_o->read_importdata();
        if ($read_status)
        {

            $import = $csv_o->get_importdata();
            //echo "before:<br>".print_r($import,true)."<br>";
            $data_error = custom_validation();
            if (empty($data_error)) { $data_status = true; }
            //echo "error:<br>".print_r($data_error,true)."<br>";
            //echo "after:<br>".print_r($import,true)."<br>";
            $csv_o->put_importdata($import);

            if (empty($data_error))
            {
                $class_o = new BOAT($db_o);
                $classes = $class_o->boat_getclasslist();
                $pre_classes = count($classes);

                // FIXME take a backup of database
                $import_status = $csv_o->import_data("t_class", "classname");

                $classes = $class_o->boat_getclasslist();
                $post_classes = count($classes);
            }
        }
    }

    // create page
    $pbufr = "<div class=\"container\">";

    if ($file_status AND $read_status AND $import_status)
    {
        $pbufr.= <<<EOT
         <h3>Import successful:</h3>
         <p>Before import  <b>$pre_classes</b> classes - after import <b>$post_classes</b> classes</p>
EOT;
        $pbufr .= $csv_o->get_import_val();
    }
    else
    {
        $pbufr.= "<h3>Import failed:</h3>";
        if (!$file_status)
        {
            $pbufr .= "<h4>File Problems:</h4>";
            $pbufr .= $csv_o->get_file_val();
        }
        if (!empty($data_status))
        {
            $pbufr .= "<h4>Data Problems:</h4>";
            foreach ($data_error as $line=>$error)
            {
                $pbufr.= "<p>Row $key: $error</p>";
            }
        }
        $bufr = "<h4>Import Report:</h4>";
        $pbufr.= $csv_o->get_import_val();
    }
    $pbufr.= "</div>";

    $html = new HTMLPAGE("en");
    // header - set to not refresh
    $html->html_header($loc, "$loc/rm_racebox/css/rm_racebox.css", true, false, 0, "import classes");  // header
    $html->html_body("");                               // body tag
    $html->html_addhtml($nbufr);                        // navbar
    $html->html_addhtml("<div style=\"margin-top:60px;\">");
    $html->html_addhtml($ebufr);                        // debug data
    $html->html_addhtml($pbufr);                        // page data
    $html->html_addhtml("</div>");
    $html->html_endscripts();
    $bufr = $html->html_render();                       // render page
    echo $bufr;
}


function custom_validation()
{
    global $import;
    $db_o = new DB;

    $i = 1;
    $error = array();
    foreach($import as $key=>$row)
    {
        $i++;
        //echo "class: {$row['classname']} <br>";
        // classname - required and unique and  handle special characters
        if (empty($row['classname']))
        {
            $error[$i].= "- class name must be supplied<br>";
        }
        else
        {
            // check it is unique
            $unique_count = 0;
            foreach ($import as $row2)
            {
                //echo "left: {$row['classname']} | right: {$row2['classname']}<br>";

                if ($row['classname']==$row2['classname'])
                {
                    $unique_count++;
                }
            }
            //echo "unique count: $unique_count<br>";
            if ($unique_count>1)
            {
                $error[$i].= "- class name must be unique<br>";
            }
            // make it capitalised and deal with special characters
            $import[$key]['classname'] = addslashes(ucfirst($row['classname']));
        }

        // acronym      - set unknown if not supplied and handle special characters
        if (empty($row['acronym']))
            { $row['acronym'] = "none"; }
        $import[$key]['acronym'] = addslashes($row['acronym']);

        // nat_py - must be provided and must be a number
        if (empty($row['nat_py']) OR !ctype_digit($row['nat_py']))
        {
            $error[$i].= "- national py must be provided and must be a positive integer<br>";
        }

        // / local_py  - at least one must be provided - must be numbers - other one set equal
        if (empty($row['local_py']))
            { $import[$key]['category'] = $row['nat_py']; }

        // category - must be one of set codes - change to upper case
        if (!$db_o->db_checksystemcode("class_category", $row['category']))
            { $error[$i].= "class category code is not valid<br>"; }
        else
            { $import[$key]['category'] = strtoupper($row['category']); }

        // rig - must be one of set codes - change to upper case
        if (!$db_o->db_checksystemcode("class_rig", $row['rig']))
            { $error[$i].= "class rig code is not valid<br>"; }
        else
            { $import[$key]['rig'] = strtoupper($row['rig']); }

        // crew - must be one of set codes - change to upper case
        if (!$db_o->db_checksystemcode("class_crew", $row['crew']))
            { $error[$i].= "class crew code is not valid<br>"; }
        else
            { $import[$key]['crew'] = strtoupper($row['crew']); }

        // spinnaker - must be one of set codes - change to upper case
        if (!$db_o->db_checksystemcode("class_spinnaker", $row['spinnaker']))
            { $error[$i].= "class spinnaker code is not valid<br>"; }
        else
            { $import[$key]['spinnaker'] = strtoupper($row['spinnaker']); }

        // engine - must be one of set codes - change to upper case
        if (!$db_o->db_checksystemcode("class_engine", $row['engine']))
            { $error[$i].= "class engine code is not valid<br>"; }
        else
            { $import[$key]['engine'] = strtoupper($row['engine']); }

        // keel - must be one of set codes - change to upper case
        if (!$db_o->db_checksystemcode("class_keel", $row['keel']))
            { $error[$i].= "class keel code is not valid<br>"; }
        else
            { $import[$key]['keel'] = strtoupper($row['keel']); }

        // popular - set to 0 if not set
        if ($row['popular']!="0" AND $row['popular']!="1")
            { $import[$key]['popular'] = "0"; }

        // rya_id - set to none if not set - deal with special characters
        if (empty($row['rya_id']))
            { $row['rya_id'] = "none"; }
        $import[$key]['rya_id'] = addslashes($row['rya_id']);

        // info - handle special characters
        $import[$key]['info'] = addslashes($row['info']);
    }

    return $error;
}

