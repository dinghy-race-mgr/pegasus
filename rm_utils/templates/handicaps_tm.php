<?php
/*
  html templates for csv import utility
*/
function handicaps_report($params = array())
{
    $rows = "";
    foreach($params['data'] as $row)
    {
        if ($row['category'] != "P")
        {
            $rows.=<<<EOT
        <tr class="active">
        <td><b>{$row['classname']}</b></td>
        <td>{$row['variant']}</td>
        <td>{$row['category']}/{$row['rig']}/{$row['spinnaker']}/{$row['crew']}</td>
        <td>{$row['local_py']}</td>
        <td><b>{$row['nat_py']}</b></td>
        </tr>
EOT;
        }
    }

    $bufr = <<<EOT
    <div class="container" style="margin-top: 40px;">
        <div class="pull-right">
            <a class="btn btn-md btn-warning" style="min-width: 200px;" type="button" name="Quit" id="Quit" onclick="return quitBox('quit');">
            <span class="glyphicon glyphicon-chevron-left"></span>&nbsp;&nbsp;<b>Close</b></a>
        </div> 
        <br>
        <hr>
        <br>
        
        <table class="table table-striped table-hover" style="width: 80%">
            <thead><tr style>
                <th>Class</th>
                <th>Variant</th>
                <th>RYA Category</th>
                <th>Local PN</th>
                <th>National PN</th>
            </tr></thead>
            <tbody>$rows</tbody>
        </table> 
        
          
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
