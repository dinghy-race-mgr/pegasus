<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 17/01/2016
 * Time: 10:48
 */

/* --- codes.php -----------------------------------------------------------------------

updates CODES from development system to live database


*/
session_start();
$loc = "../../../";
include("$loc/common/lib/db_lib.php");

include("config.php");
$old_conn = db_connect('old');
$new_conn = db_connect('new');

// clear settings
$clear = db_query($new_conn, "TRUNCATE t_ini");
$clear = db_query($new_conn, "TRUNCATE t_link");

// transfer ini settings
$result = db_query($old_conn, "SELECT * FROM t_ini ORDER BY id");
echo "transfer club settings:<br>";
$i = 0;
while ($row = db_fetchrow($result))
{
    $insert = db_query($new_conn, "INSERT INTO t_ini (`category`,`parameter`,`label`,`value`,`notes`,`updby`) VALUES
('{$row['category']}','{$row['parameter']}','{$row['label']}','{$row['value']}','{$row['notes']}','{$row['updby']}')");
    echo "&nbsp;&nbsp;-&nbsp;{$row['category']}: {$row['parameter']}<br>";
    $i++;
}
echo "... $i settings";
echo "<hr>";

// transfer link settings
$result = db_query($old_conn, "SELECT * FROM t_link ORDER BY id");
echo "transfer club links:<br>";
$i = 0;
while ($row = db_fetchrow($result))
{
    $insert = db_query($new_conn, "INSERT INTO t_link (`label`,`url`,`tip`,`category`,`rank`) VALUES
('{$row['label']}','{$row['url']}','{$row['tip']}','{$row['category']}','{$row['rank']}')");
    echo "&nbsp;&nbsp;-&nbsp;{$row['label']}<br>";
    $i++;
}
echo "... $i links";
echo "<hr>";