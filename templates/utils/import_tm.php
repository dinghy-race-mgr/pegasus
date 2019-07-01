<?php
/*
  html templates for csv import utility
*/
function upload_import_file($params = array())
{
    $bufr = <<<EOT
    <div class="container" style="margin-top: 40px;">
        <div class="jumbotron" style="margin-top: 40px;">
            <p class="text-info">Instructions:</p>
            <p class="text-info">{instructions}</p>
        </div>
        <form enctype="multipart/form-data" id="selectfileForm" action="dbimport.php?pagestate=submit" method="post">
        <div class="row">
            <div class="col-sm-6 col-sm-offset-3">
                    <h3>Select Import File</h3>
                    <div>
                    <span class="file-input btn btn-info btn-lg btn-file">
                        <input type="file" accept="{file-types}" style="width:400px !important" name="importfile" value=""  required  >
                    </span>
                    </div>
            </div>
        </div>
        <input type="hidden" name="importtype" value="{import-type}">
        <div class="row margin-top-40">
            <div class="col-sm-8 col-sm-offset-1">
                <div class="pull-left">
                    <a class="btn btn-lg btn-warning" style="min-width: 200px;" type="button" name="Quit" id="Quit" onclick="return quitBox('quit');">
                    <span class="glyphicon glyphicon-remove"></span>&nbsp;&nbsp;<b>Cancel</b></a>
                </div>
                <div class="pull-right">
                    <button type="submit" class="btn btn-lg btn-success"  style="min-width: 200px;" >
                    <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;&nbsp;<b>Import {import-title}</b></button>
                </div>
            </div>
        </div>
        </form>
    </div>
    <script language="javascript">
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
    return $bufr;
}

function submit_import($params = array())
{
    $rpt_bufr = "";
    if ($params['success']) {
        $state = "success";
        $title = "Import successful";
        $rpt_bufr = <<<EOT
            <p><strong>{rows-in-file} data records in import file</strong></p>
            <p>Before import  <b>{records-before}</b> {import-type} records - after import <b>{records-after}</b> {import-type} records</p>
EOT;
        if ($params['insert'])
        {
            $rpt_bufr.= <<<EOT
            <p><strong>Inserts: </strong>
            {$params['insert']}</p>
EOT;
        }
        else
        {
            $rpt_bufr.= <<<EOT
            <p><strong>Inserts: </strong>none</p>
EOT;
        }

        if ($params['update'])
        {
            $rpt_bufr.= <<<EOT
            <p><strong>Updates: </strong>
            {$params['update']}</p>
EOT;
        }
        else
        {
            $rpt_bufr.= <<<EOT
            <p><strong>Updates: </strong>none</p>
EOT;
        }

        if ($params['delete'])
        {
            $rpt_bufr.= <<<EOT
            <p><strong>Deletes: </strong>
            {$params['delete']}</p>
EOT;
        }
        else
        {
            $rpt_bufr.= <<<EOT
            <p><strong>Deletes: </strong>none</p>
EOT;
        }
    }
    else
    {
        $failed_already = false;
        $state = "danger";
        $title = "Import failed";
        if (!$params['file_status'])
        {
            $rpt_bufr .= <<<EOT
            <div class="alert alert-warning alert-dismissible" style="padding-left: 60px" role="alert">
                <h3>File Problems:</h3>
                {file-problems}
                <h3>Suggested Fix! </h3>
                <p>Please check your import file and make sure that it has a csv file type AND
                the first row has field labels as defined in the import instructions for this type of data.
                <br>[Note: the database content has not been modified.]</p>
            </div>
EOT;
            $failed_already = true;
        }

        if (!$failed_already AND !$params['read_status'])
        {
            $rpt_bufr .= <<<EOT
            <div class="alert alert-warning alert-dismissible" style="padding-left: 60px" role="alert">
                <h3>File Read Problems:</h3>
                {read-problems}
                <h3>Suggested Fix! </h3>
                <p>Please check your import file and make sure that it has a complete set of data fields
                for each row of data AND the number of data fields matches the number of fields in the header for all records.
                <br>[Note: the database content has not been modified.]</p>
            </div>
EOT;
            $failed_already = true;
        }

        if (!$failed_already AND !$params['data_status'])
        {
            $rpt_bufr .= <<<EOT
            <div class="alert alert-warning alert-dismissible" style="padding-left: 60px" role="alert">
               <h3>Data Problems:</h3>
               {data-problems}
               <h3>Suggested Fix! </h3>
               <p>Please correct the data in the rows reported above and try again.
               <br>[Note: the database content has not been modified.]</p>
            </div>
EOT;
            $failed_already = true;
        }

        if (!$failed_already AND !$params['import_status']) {
            $rpt_bufr .= <<<EOT
            <div class="alert alert-warning alert-dismissible" style="padding-left: 60px" role="alert">
               <h3>Database Import Problems</h3>
               {import-problems}
               <h3>Suggested Fix!</h3>
               <span class="text-warning">Your database may be corrupted.</span><br><br>
               To recover from this please read the Imports section in the user guide
               [your back up recovery file can be found at <strong>{recovery-file]}</strong> in your raceManager folder.
            </div>
EOT;
        }
    }

    $bufr = <<<EOT
    <div class="panel panel-$state">
        <div class="panel-heading">
            <h3>$title:</h3>
        </div>
        <div class="panel-body" style="padding-left: 30px">
            $rpt_bufr
        </div>
    </div>
EOT;
        return $bufr;

}
