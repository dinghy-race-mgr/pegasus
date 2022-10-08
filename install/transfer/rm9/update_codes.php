<?php
/* --- codes.php -----------------------------------------------------------------------

updates CODES from development system to live database


*/
session_start();
$loc = "../../../";
include("$loc/common/lib/db_lib.php");

include("config.php");
$old_conn = db_connect('old');
$new_conn = db_connect('new');

// check new against existing



// check existing against new




// clear codes
$clear = db_query($new_conn, "TRUNCATE t_code_type");
$clear = db_query($new_conn, "TRUNCATE t_code_system");
$clear = db_query($new_conn, "TRUNCATE t_code_result");

$result = db_query($old_conn, "SELECT * FROM t_code_type ORDER BY id");
echo "transfer code types:<br>";
$i = 0;
while ($row = db_fetchrow($result))
{
    $insert = db_query($new_conn, "INSERT INTO t_code_type (`id`,`groupname`,`label`,`info`,`rank`,`type`) VALUES ('{$row['id']}','{$row['groupname']}','{$row['label']}','{$row['info']}','{$row['rank']}','{$row['type']}')");
    echo "&nbsp;&nbsp;-&nbsp;{$row['groupname']}<br>";
    $i++;
}
echo "... $i values";
echo "<hr>";

$result = db_query($old_conn, "SELECT * FROM t_code_system ORDER BY id");
echo "transfer code values <br>";
$i = 0;
while ($row = db_fetchrow($result))
{
    $insert = db_query($new_conn, "INSERT INTO t_code_system (`id`,`groupname`,`code`,`label`,`rank`,`defaultval`,`deletable`,`upddate`,`updby`)
 VALUES ('{$row['id']}', '{$row['groupname']}', '{$row['code']}', '{$row['label']}', '{$row['rank']}', '{$row['defaultval']}', '{$row['deletable']}', '{$row['upddate']}', '{$row['updby']}')");
    echo "&nbsp;&nbsp;-&nbsp;{$row['groupname']}: {$row['code']}<br>";
    $i++;
}
echo "... $i values";
echo "<hr>";

$result = db_query($old_conn, "SELECT * FROM t_code_result ORDER BY id");
echo "transfer result codes <br>";
$i = 0;
while ($row = db_fetchrow($result))
{
    $insert = db_query($new_conn, "INSERT INTO t_code_result (`id`,`code`,`short`,`info`,`scoringtype`,`scoring`,
`timing`, `startcode`, `timercode`, `nonexclude`, `rank`, `active`, `upddate`, `updby`) VALUES
('{$row['id']}','{$row['code']}','{$row['short']}','{$row['info']}','{$row['scoringtype']}',
'{$row['scoring']}','{$row['timing']}','{$row['startcode']}','{$row['timercode']}','{$row['nonexclude']}',
'{$row['rank']}','{$row['active']}','{$row['upddate']}','{$row['updby']}')");
    echo "&nbsp;&nbsp;-&nbsp;{$row['code']}: {$row['short']}<br>";
    $i++;
}
echo "... $i values";
echo "<hr>";
echo "code update complete ..."

?>