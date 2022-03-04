<?php
/**
 * results_lib.php - function library for results creation functionality
 * 
 * used in rm_racebox and rm_admin
 * 
 */
/* ----------------------------------------------------------------------------------------------------*/

function process_archive()
    /*
     * copies race data from t_race to t_results
     * creates archive of t_race/t_lap/t_finish in equivalent a_*** tables
     */
{
    global $result_o;

    $status['copy']    = $result_o->race_copy_results();      // copy data from t_race to t_results
    $status['archive'] = $result_o->race_copy_archive();      // copy data from t_race/t_lap/t_finish to a_<tables>>

    return $status;
}


function process_result_file($loc, $result_status, $include_club, $result_notes, $fleet_msg)
    /*
     * Creates an html race results file and posts it to he master (internal) results folder
     */
{
    global $result_o;

    // establish file attributes
    $file_attr = array(
        "eventid"   => $result_o->eventid,
        "eventyear" => date("Y", $result_o->eventdate),
        "folder"    => "races",
        "format"    => "htm",
        "filename"  => $result_o->get_race_filename(),
        "label"     => "race results",
        "notes"     => $result_notes,
        "status"    => $result_status,
        "rank"      => "1",
        "upload"    => false,
    );

    // FIXME - remove use of $_SESSION here
    $race_path = $_SESSION['result_path'].DIRECTORY_SEPARATOR.$file_attr['eventyear'].DIRECTORY_SEPARATOR.$file_attr['folder'];
    $file_path = $race_path.DIRECTORY_SEPARATOR.$file_attr['filename'];
    $race_url  = $_SESSION['result_url']."/".$file_attr['eventyear']."/".$file_attr['folder']."/".$file_attr['filename'];

    // create results html
    $race_bufr = $result_o->render_race_result($loc, $result_status, $include_club, $result_notes, $fleet_msg);

    // check if we have a matching file in t_resultfile - if we do delete record
    $num_deleted = $result_o->del_obsolete_file($file_attr);

    // If master folder doesn’t exist - create it (year/type)
    $folder_exists = true;
    if (!file_exists($race_path))
    {
        if(!mkdir($race_path, 0777, true)) { $folder_exists = false; } // fixme refine the permissions given
    }

    // if master file already exists - delete it
    $file_exists = false;
    if (file_exists($file_path))
    {
        if (!unlink($file_path)) { $file_exists = true; }
    }

    // create new file in master folder
    $num_bytes = file_put_contents($file_path, $race_bufr);

    // set up return to main process
    if (!$folder_exists)
    {
        $status = array('success' => false, 'err' => "error creating results folder [$race_path]");
    }
    elseif ($file_exists)
    {
        $status = array('success' => false, 'err' => "previous version could not be deleted [$file_path]");
    }
    elseif ($num_bytes === FALSE)
    {
        $status = array('success' => false, 'err' => "error creating file [$file_path]");
    }
    elseif ($num_bytes == 0)
    {
        $status = array('success' => false, 'err' => "file empty [$file_path]");
    }
    else  // file created successfully
    {
        $status = array('success' => true, 'err' => "file created [$race_path]", 'url' => $race_url,
            'path' => $race_path, 'file' => $file_attr['filename']);

        // add result file entry to t_resultfile
        $listed = $result_o->add_result_file($file_attr);
        if (!$listed)
        {
            $status= array('success' => false, 'err' => "file created but not added to results list [$race_path]");
        }

    }
    return $status;
}


function process_series_file($opts, $series_code, $series_status, $series_notes="")
    /*
     * creates an html series result file
     */
{
    global $result_o;
    global $db_o;
    //global $tmpl_o;

    $series_o = new SERIES_RESULT($db_o, $series_code, $opts, false);

    // establish file attributes
    $file_attr = array(
        "eventid"   => $result_o->eventid,
        "eventyear" => date("Y", $result_o->eventdate),
        "folder"    => "series",
        "format"    => "htm",
        "filename"  => $series_o->get_series_filename(),
        "label"     => "series results",
        "notes"     => $series_notes,
        "status"    => $series_status,
        "rank"      => "1",
        "upload"    => false,
    );

    // set data for series result
    $err = $series_o->set_series_data();
    if (!$err)
    {
        // calculate series result
        $err = $series_o->calc_series_result();

        if (!$err)
        {
            // render series result into html
            $sys_detail = array(
                "sys_name"      => $_SESSION['sys_name'],
                "sys_release"   => $_SESSION['sys_release'],
                "sys_version"   => $_SESSION['sys_version'],
                "sys_copyright" => $_SESSION['sys_copyright'],
                "sys_website"   => $_SESSION['sys_website'],
            );

            $htm = $series_o->series_render_styled($sys_detail,  $series_status, file_get_contents($opts['styles']));
        }
        else
        {
            $err_detail = $series_o->get_err();
        }
    }
    else
    {
        $err_detail = $series_o->get_err();
    }

    if (!$err and !empty($htm))
    {
        // get file name, path and url for series file
        // FIXME - remove use of $_SESSION here
        $series_path = $_SESSION['result_path'].DIRECTORY_SEPARATOR.$file_attr['eventyear'].DIRECTORY_SEPARATOR.$file_attr['folder'];
        $file_path = $series_path.DIRECTORY_SEPARATOR.$file_attr['filename'];
        $series_url  = $_SESSION['result_url']."/".$file_attr['eventyear']."/".$file_attr['folder']."/".$file_attr['filename'];

        // If master folder doesn’t exist - create it (year/type)
        $folder_exists = true;
        if (!file_exists($series_path))
        {
            if(!mkdir($series_path, 0777, true)) { $folder_exists = false; } // fixme refine the permissions given
        }

        // if master file already exists - delete it
        $file_exists = false;
        if (file_exists($file_path))
        {
            if (!unlink($file_path)) { $file_exists = true; }
        }

        // output htm to file
        $num_bytes = file_put_contents($series_path, $htm);

        if (!$folder_exists)
        {
            $status = array('success' => false, 'err' => "error creating results folder [$series_path]");
        }
        elseif ($file_exists)
        {
            $status = array('success' => false, 'err' => "previous version could not be deleted [$file_path]");
        }
        elseif ($num_bytes === FALSE)
        {
            $status = array('success' => false, 'err' => "error creating series file [$series_path]");
        }
        elseif ($num_bytes == 0)
        {
            $status = array('success' => false, 'err' => "series file empty [$series_path]");
        }
        else
        {
            $status = array('success' => true, 'err' => "series file created [$series_path]", 'url' => $series_url,
                'path' => $series_path, 'file' => $file_attr['filename']);

            // add series result file entry to t_resultfile
            $listed = $result_o->add_result_file($file_attr);
            if (!$listed)
            { $status = array('success' => false, 'err' => "file created but not added to results list [$series_path]"); }

        }
    }
    else
    {
        // return calculation error with
        $status = array('success' => false, 'err' => "series calculation failed", "detail" => $err_detail);
    }

    return $status;
}


function process_inventory($result_year)
{
    global $result_o;

    $inventory = array(
        "filename" => $result_o->get_inventory_filename($result_year),
        "path" => $_SESSION['result_path'].DIRECTORY_SEPARATOR.$result_year
    );
    $inventory['url'] = $_SESSION['result_url'] . "/$result_year/" . $inventory['filename'];

    // FIXME - remove use of $_SESSION here
    $system_info = array(
        "sys_name"    => $_SESSION['sys_name'],
        "sys_version" => $_SESSION['sys_version'],
        "clubname"    => $_SESSION['clubname'],
        "result_path" => $_SESSION['result_path'],
        "result_url"  => $_SESSION['result_url']
    );

    // create inventory
    $status = $result_o->create_result_inventory($result_year, $inventory['path'].DIRECTORY_SEPARATOR.$inventory['filename'], $system_info);

    // return inventory file details if created ok
    if ($status) {
        return $inventory; }
    else {
        return $status;
    }
}


function process_transfer_network($files, $results_path, $results_url )
{
    global $result_o;

    $status = array("result" => false, "complete" => false, "num_files" => 0, "log"=>array() );

    $num_files = count($files);
    $num_files_sent = 0;
    foreach ($files as $k=>$file)
    {
        // get target file name, path and url for file
        $dir_path = $results_path.DIRECTORY_SEPARATOR.$file['year'].DIRECTORY_SEPARATOR.$file['type'];
        $file_path = $dir_path.DIRECTORY_SEPARATOR.$file['file'];
        $file_url  = $results_url."/".$file['year']."/".$file['type']."/".$file['file'];

        // check target directory exists - create if necessary
        $folder_exists = true;
        if (!file_exists($dir_path))
        {
            if(!mkdir($dir_path, 0777, true)) { $folder_exists = false; } // fixme refine the permissions given
        }

        // check if file already exists - delete if necessary
        $file_exists = false;
        if (file_exists($file_path))
        {
            if (!unlink($file_path)) { $file_exists = true; }
        }

        // copy file and update t_resultfile
        if ($folder_exists and  !$file_exists)
        {
            $source_file = $_SESSION['result_path'].DIRECTORY_SEPARATOR.$file['year'].DIRECTORY_SEPARATOR.$file['type'].DIRECTORY_SEPARATOR.$file['file'];
            if (copy($source_file, $file_path))
            {
                $upd = $result_o->set_upload_time($file['id']);
                if ($upd !== false)
                {
                    $num_files_sent++;
                    $status['log'][] = $file['label']." : uploaded - ".$file['file'];
                }
                else
                {
                    if ($file['id'] > 0)            // don't report error if inventory file
                    {
                        $status['log'][] = $file['label']." : upload FAILED - ".$file['file'];
                    }
                }
            }
        }
        else
        {
            $status['log'][] = $file['label']." : upload FAILED - bad location ".$file['file'];
        }
    }

    if ($num_files_sent > 0) { $status['result'] = true; }
    if ($num_files_sent >= $num_files) { $status['complete'] = true; }
    $status['num_files'] = $num_files_sent;

    return $status;
}


function process_transfer_ftp($files, $ftp)
{

    $status = array("result" => false, "complete" => false, "num_files" => 0, "log"=>array() );
    // FIXME to be completed

    return $status;

}


function process_transfer($result_year, $files, $protocol)
    /*
     * transfers results files to website using network, ftp or sftp
     * [Note - it doesn't create the results inventory file]
     *
     * $inventory_year = date("Y", strtotime($_SESSION["e_$eventid"]['ev_date']));
            $inventory_file = $result_o->get_inventory_filename($inventory_year);
            $inventory_path = $_SESSION['result_path'].DIRECTORY_SEPARATOR.$inventory_file;
            $inventory_url  = $_SESSION['result_url']."/".$inventory_file;

            // create inventory
            $inventory = $result_o->create_result_inventory($inventory_year, $inventory_path, $system_info);

            if ($inventory['success'])                         // if inventory created successfully then proceed
            {
                // add inventory file to file to be transferred
                $transfer_files[] = array("path" => $inventory_path, "url" => $inventory_url,
                                          "file" => $inventory_file);


    $source = "foo/fileA.txt";
$destination = "bar/"; // note how the destination has no file.
$newFile = "somefile.txt";
touch($destination . $newFile);
// then do the copy part
copy($source, $destination.$newFile);
     */
//{
//    global $loc;
//
//    if ($protocol == "local")   // transfer
//    {
//        $status = array("result" => true, "complete" => "all", "connect"=>true, "login" => true, "log"=>"", "transferred" => 0 ); // FIXME set connect to false if dir doesn't exist or create it??
//
//        foreach($files as $key=>$file)   // loop over all files
//        {
//            $source = "foo/fileA.txt";
//            $destination = "bar/"; // note how the destination has no file.
//            $newfile = "somefile.txt";
//            touch($destination . $newfile);
//// then do the copy part
//
//
//
//            if (copy($source, $destination.$newfile))   // transfer file
//            {
//                $status['transferred']++;
//                $status['log'].= " - file transferred ({$file['source']})<br>";
//            }
//            else
//            {
//                $status['log'].= " - file transfer failed ({$file['source']})<br>";
//            }
//        }
//    }
//    else // use ftp/sftp
//    {
//        $status = u_ftpFiles($protocol, $_SESSION['ftp_protocol'], $files);
//
//        $num_files = count($files);
//        $file_count = 0;
//        foreach ($files as $key => $file)  // what is happening here
//        {
//            if ($status['file'][$key])
//            {
//                $file_count++;
//            }
//        }
//
//        $status['complete'] = "none";                         // no files transferred
//
//        if ($file_count == $num_files)
//        {
//            $status['complete'] = "all";                      // all files transfered
//        }
//        elseif($file_count > 0)
//        {
//            $status['complete'] = "some";                     // some files transferred
//        }
//    }
//
//
//    return $status;
//}