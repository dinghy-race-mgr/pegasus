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
        "eventyear" => date("Y", strtotime($result_o->eventdate)),
        "folder"    => "races",
        "format"    => "htm",
        "filename"  => $result_o->get_race_filename(),
        "label"     => "race results",
        "notes"     => $result_notes,
        "status"    => $result_status,
        "rank"      => "1",
    );

    // FIXME - remove use of $_SESSION here
    $race_path = $_SESSION['result_path']."/".$file_attr['eventyear']."/".$file_attr['folder'];
    $file_path = $race_path."/".$file_attr['filename'];
    $race_url  = $_SESSION['result_url']."/".$file_attr['eventyear']."/".$file_attr['folder']."/".$file_attr['filename'];

    // create results html
    $race_bufr = $result_o->render_race_result($loc, $result_status, $include_club, $result_notes, $fleet_msg);

    // check if we have a matching file in t_resultfile - if we do delete record
    $num_deleted = $result_o->del_obsolete_file(array("folder"=>$file_attr['folder'], "eventid"=>$file_attr['eventid']));

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
    if ($folder_exists and !$file_exists)
    {
        $num_bytes = file_put_contents($file_path, $race_bufr);
    }

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

    $series_o = new SERIES_RESULT($db_o, $series_code, $opts, false);

    // establish file attributes
    $file_attr = array(
        "eventid"   => $result_o->eventid,
        "eventyear" => date("Y", strtotime($result_o->eventdate)),
        "folder"    => "series",
        "format"    => "htm",
        "filename"  => $series_o->get_series_filename(),
        "label"     => "series results",
        "notes"     => $series_notes,
        "status"    => $series_status,
        "rank"      => "1",
    );

    // set data for series result
    $err_detail = "";
    $err = $series_o->set_series_data();
    //u_writedbg("err after set series data: $err", __FILE__, __FUNCTION__, __LINE__);
    if (!$err)
    {
        // calculate series result
        $err = $series_o->calc_series_result();
        //u_writedbg("err after calc series result: $err", __FILE__, __FUNCTION__, __LINE__);
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

            $series_bufr = $series_o->series_render_styled($sys_detail,  $series_status, file_get_contents($opts['styles']));

            // if series has more than 6 completed - set page format to landscape
            $counts = $series_o->get_race_counts();
            if ($counts['races_num'] > 6)
            {
                $series_bufr = str_replace("A4 portrait", "A4 landscape", $series_bufr);
            }
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

    //u_writedbg("before trying to create file: |$err|".strlen($series_bufr)."|", __FILE__, __FUNCTION__, __LINE__);
    if (!$err and !empty($series_bufr)) // FIXME this is the bugg
    {
        // get file name, path and url for series file
        // FIXME - remove use of $_SESSION here
        $series_path = $_SESSION['result_path']."/".$file_attr['eventyear']."/".$file_attr['folder'];
        $file_path = $series_path."/".$file_attr['filename'];
        $series_url  = $_SESSION['result_url']."/".$file_attr['eventyear']."/".$file_attr['folder']."/".$file_attr['filename'];

        // If master folder doesn’t exist - create it (year/type)
        $folder_exists = true;
        if (!file_exists($series_path))
        {
            if (!mkdir($series_path, 0777, true)) { $folder_exists = false; } // fixme refine the permissions given
        }

        // if master file already exists - delete it
        $file_exists = false;
        if (file_exists($file_path))
        {
            if (!unlink($file_path)) { $file_exists = true; }
        }

        // output htm to file
        if ($folder_exists and !$file_exists)
        {
            $num_bytes = file_put_contents($file_path, $series_bufr);
        }

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

            // check if we have a matching file in t_resultfile - if we do delete record
            $num_deleted = $result_o->del_obsolete_file(array("folder"=>$file_attr['folder'], "filename" => $file_attr['filename']));

            // add series result file entry to t_resultfile
            $listed = $result_o->add_result_file($file_attr);
            if (!$listed)
            { $status = array('success' => false, 'err' => "file created but not added to results list [$series_path]"); }

        }
    }
    else
    {
        // return calculation error
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
        "result_path" => $_SESSION['result_public_path'],
        "result_url"  => $_SESSION['result_public_url']
    );

    // create inventory
    $status = $result_o->create_result_inventory($result_year, $inventory['path']."/".$inventory['filename'], $system_info);

    // return inventory file details if created ok
    if ($status) {
        return $inventory; }
    else {
        return $status;
    }
}


function process_transfer_network($files)
{
    global $db_o;

    $status = array("result" => false, "complete" => false, "num_files" => 0, "files"=>array());
    $result_dirs = array("class", "races", "series", "special");   // fixme - needs to be in configuration somewhere
    $num_files = count($files);
    $num_files_sent = 0;

    //u_writedbg("<pre>files to transfer: ".print_r($files,true)."</pre>", __CLASS__, __FUNCTION__, __LINE__);

    foreach ($files as $k=>$file)
    {
        $continue = false;
        $status['files'][$k]['log'] = "network transfer {$file['file']}: ";

        empty($file['type']) ? $sub_dir = "/".$file['year']."/".$file['file'] : $sub_dir = "/".$file['year']."/".$file['type']."/".$file['file'] ;

        // get source_file, target file name, path and url for file
        $source_file = $_SESSION['result_path'].$sub_dir;
        $target_file = $_SESSION['result_public_path'].$sub_dir;
        $target_year_dir = $_SESSION['result_public_path']."/".$file['year'];

        $status['files'][$k]['file_name'] = $file['file'];
        $status['files'][$k]['file_type'] = $file['type'];

        //u_writedbg("<pre>source: $source_file<br>target: $target_file<br>target_year_dir: $target_year_dir</pre>", __CLASS__, __FUNCTION__, __LINE__);

        // check if source file exists
        if (file_exists($source_file))
        {
            $status['files'][$k]['source_exists'] = true;
            $status['files'][$k]['log'].= "source file exists | ";
            $continue = true;
        }
        else
        {
            $status['files'][$k]['source_exists'] = false;
            $status['files'][$k]['log'].= "source file does not exist | ";
        }

        // check target year directory exists - if not create with underlying structure
        if ($continue)
        {
            $continue = false;

            $dirs_check = true;
            $dirs_needed = 0;
            foreach ($result_dirs as $dir)
            {
                //u_writedbg("<pre>checking: $target_year_dir/$dir</pre>", __CLASS__, __FUNCTION__, __LINE__);
                if (!is_dir("$target_year_dir/$dir"))
                {
                    $dirs_needed++;
                    $mkdir = mkdir("$target_year_dir/$dir", 0777, true);
                    if (!is_dir("$target_year_dir/$dir")) {
                        $status['files'][$k]['target_exists'] = false;
                        $status['files'][$k]['log'] .= "failed to create $target_year_dir/$dir directory | ";
                        $dirs_check = false;
                        break;
                    }
                }
            }
            if ($dirs_check)
            {
                $dirs_needed > 0 ? $txt = "created" : $txt = "exists";
                $status['files'][$k]['target_exists'] = true;
                $status['files'][$k]['log'] .= "target directory $txt | ";
                $continue = true;
            }

        }

        // move file
        if ($continue)
        {
            if (!copy($source_file, $target_file))
            {
                $status['files'][$k]['file_transferred'] = false;
                $status['files'][$k]['log'] .= "file not transferred | ";
            }
            else
            {
                $num_files_sent++;
                $upd = set_upload_time($file['file_id']);
                $status['files'][$k]['file_transferred'] = true;
                $status['files'][$k]['log'] .= "file transferred | ";
            }
        }
    }

    $num_files_sent > 0 ?  $status['result'] = true : $status['result'] = false;
    $num_files_sent >= $num_files ? $status['complete'] = true : $status['complete'] = false;
    $status['num_files'] = $num_files_sent;

    //u_writedbg("<pre>".print_r($status,true)."</pre>", __CLASS__, __FUNCTION__, __LINE__);

    return $status;
}


function process_transfer_sftp($files, $ftp_env)
{
    global $db_o;

    error_reporting(E_ERROR);
    set_include_path(get_include_path() . PATH_SEPARATOR . $_SESSION['basepath'] . "/common/oss/phpseclib");
    include("Net/SFTP.php");
    define('NET_SFTP_LOGGING', NET_SFTP_LOG_COMPLEX);

    $status = array("login" => false, "connection" => "", "result" => false, "complete" => false, "num_files" => 0, "files" => array());
    $result_dirs = array("class", "races", "series", "special");   // fixme - needs to be in configuration somewhere
    $num_files = count($files);
    $num_files_sent = 0;

    $sftp = new Net_SFTP($ftp_env['server']);

    // login to server
    if ($sftp->login($ftp_env['user'], $ftp_env['pwd']))
    {
        $status['login'] = true;
        $status['connection'] .= "{$ftp_env['server']}|{$ftp_env['user']}";
        $continue = true;
    }
    else
    {
        $status['connection'] .= "{$ftp_env['server']}|{$ftp_env['user']} failed ";
        $continue = false;
    }

    //u_writedbg("<pre>files to transfer: ".print_r($files,true)."</pre>", __CLASS__, __FUNCTION__, __LINE__);

    if ($continue) {
        foreach ($files as $k => $file)
        {
            $continue = false;
            $status['files'][$k]['log'] .= "sftp transfer {$file['file']}: ";

            empty($file['type']) ? $sub_dir = "/".$file['year']."/".$file['file'] : $sub_dir = "/".$file['year']."/".$file['type']."/".$file['file'];

            // get source_file, target file name, path and url for file
            $source_file = $_SESSION['result_path'] . $sub_dir;
            $target_file = $_SESSION['result_public_path'] . $sub_dir;
            $target_year_dir = $_SESSION['result_public_path']. "/" . $file['year'];

            $status['files'][$k]['file_name'] = $file['file'];
            $status['files'][$k]['file_type'] = $file['type'];

            //u_writedbg("<pre>source: $source_file<br>target: $target_file<br>target_year_dir: $target_year_dir</pre>", __CLASS__, __FUNCTION__, __LINE__);

            // check if source file exists
            if (file_exists($source_file)) {
                $status['files'][$k]['source_exists'] = true;
                $status['files'][$k]['log'] .= "source file exists | ";
                $continue = true;
            }
            else
            {
                $status['files'][$k]['source_exists'] = false;
                $status['files'][$k]['log'].= "source file does not exist | ";
            }

            // check target year directory exists - if not create with underlying structure
            if ($continue)
            {
                $continue = false;

                $dirs_check = true;
                $dirs_needed = 0;
                foreach ($result_dirs as $dir)
                {
                    //u_writedbg("<pre>checking: $target_year_dir/$dir</pre>", __CLASS__, __FUNCTION__, __LINE__);

                    if (!$sftp->chdir("$target_year_dir/$dir"))
                    {
                        $dirs_needed++;
                        $mkdir = $sftp->mkdir("$target_year_dir/$dir", 0777, true);
                        if (!$sftp->chdir("$target_year_dir/$dir")) {
                            $status['files'][$k]['target_exists'] = false;
                            $status['files'][$k]['log'] .= "failed to create $target_year_dir/$dir directory | ";
                            $dirs_check = false;
                            break;
                        }
                    }
                }
                if ($dirs_check)
                {
                    $dirs_needed > 0 ? $txt = "created" : $txt = "exists";
                    $status['files'][$k]['target_exists'] = true;
                    $status['files'][$k]['log'] .= "target directory $txt | ";
                    $continue = true;
                }
            }

            // transfer file
            if ($continue)
            {
                if ($sftp->put($target_file, $source_file, NET_SFTP_LOCAL_FILE)) {
                    $num_files_sent++;
                    $upd = set_upload_time($file['file_id']);
                    $status['files'][$k]['file_transferred'] = true;
                    $status['files'][$k]['log'] .= "file transferred | ";
                } else {
                    $status['files'][$k]['file_transferred'] = false;
                    $status['files'][$k]['log'] .= "file not transferred | ";
                }
            }

        }
    }

    $num_files_sent > 0 ? $status['result'] = true : $status['result'] = false;
    $num_files_sent >= $num_files ? $status['complete'] = true : $status['complete'] = false;
    $status['num_files'] = $num_files_sent;

    //u_writedbg("<pre>".print_r($status,true)."</pre>", __CLASS__, __FUNCTION__, __LINE__);

    return $status;

}


function set_upload_time($file_id)
{
    global $db_o;
    // sets datetime file was uploaded to the website
    // file_id is 0 for inventory file - so no update required
    $upd = true;
    if ($file_id > 0)
    {
        $upd = $db_o->db_query("UPDATE t_resultfile SET upload = CURRENT_TIMESTAMP where id = $file_id");
    }

    return $upd;
}


