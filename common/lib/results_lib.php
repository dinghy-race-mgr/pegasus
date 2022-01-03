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
     * Creates an html race results file
     */
{
    global $result_o;

    $race_bufr = $result_o->render_race_result($loc, $result_status, $include_club, $result_notes, $fleet_msg);

    // get file path and url
    $race_file = $result_o->get_race_filename();
    $race_path = $_SESSION['result_path'].DIRECTORY_SEPARATOR."races".DIRECTORY_SEPARATOR.$race_file;
    $race_url  = $_SESSION['result_url']."/races/".$race_file;

    // write html to file
    $num_bytes = file_put_contents($race_path, $race_bufr);

    // set up return to main process
    if ($num_bytes === FALSE)
    {
        $status = array('success' => false, 'err' => "error creating file [$race_path]");
    }
    elseif ($num_bytes == 0)
    {
        $status = array('success' => false, 'err' => "file empty [$race_path]");
    }
    else
    {
        $status = array('success' => true, 'err' => "file created [$race_path]", 'url' => $race_url,
            'path' => $race_path, 'file' => $race_file);

        // add result file entry to t_resultfile
        if ($result_status != "embargoed")
        {
            $listed = $result_o->add_result_file(array(
                "status"   => $result_status,
                "folder"   => "races",
                "format"   => "htm",
                "filename" => $race_file,
                "label"    => "race",
                "rank"     => "1",
                "notes"    => $result_notes ));
            if (!$listed) { $status= array('success' => false, 'err' => "file created but not added to results list [$race_path]"); }
        }

    }
    return $status;
}


function process_series_file($eventid, $opts, $series_code, $series_status)
    /*
     * creates an html series result file
     */
{
    global $result_o;
    global $db_o;
    global $tmpl_o;

    $series_o = new SERIES_RESULT($db_o, $series_code, $opts, true);

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

    // FIXME output error summary

    if (!$err and !empty($htm))
    {
        // get file name, path and url for series file
        $series_file = $series_o->get_series_filename();
        $series_path = $_SESSION['result_path'].DIRECTORY_SEPARATOR."series".DIRECTORY_SEPARATOR.$series_file;
        $series_url  = $_SESSION['result_url']."/series/".$series_file;

        // output htm to file
        $num_bytes = file_put_contents($series_path, $htm);

        if ($num_bytes === FALSE)
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
                'path' => $series_path, 'file' => $series_file);

            // add series result file entry to t_resultfile
            $listed = $result_o->add_result_file(array(
                "status"   => $series_status,
                "folder"     => "race",
                "format"   => "htm",
                "filename" => $series_file,
                "label"    => "series",
                "rank"     => "2",
                "notes"    => "results file created by raceManager"
            ));

            if (!$listed) { $status = array('success' => false, 'err' => "file created but not added to results list [$series_path]"); }

        }
    }
    else
    {
        // return calculation error with
        $status = array('success' => false, 'err' => "series calculation failed", "detail" => $err_detail);
    }

    return $status;
}


function process_transfer($files, $protocol)
    /*
     * transfers results files to website
     * [Note - it doesn't create or transfer the results inventory file]
     */
{
    global $loc;
    $ftp_env = array(
        "server" => $_SESSION['ftp_server'],
        "user"   => $_SESSION['ftp_userr'],
        "pwd"    => $_SESSION['ftp_pwd'],
    );

    $status = ftpFiles($loc, $protocol, $ftp_env, $files);

    $num_files = count($files);
    $file_count = 0;
    foreach ($files as $key => $file)
        if ($status['file'][$key])
        {
            $file_count++;
        }

    $status['success'] = "none";
    if ($file_count == $num_files)
    {
        $status['success'] = "all";
    }
    elseif($file_count > 0)
    {
        $status['success'] = "some";
    }

    return $status;
}