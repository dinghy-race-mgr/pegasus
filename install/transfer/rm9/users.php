

<?php
/* --- users.php -----------------------------------------------------------------------

moves USERS data from one pegasus database to another


*/
session_start();
$loc = "../../../";
include("$loc/common/lib/db_lib.php");

include("config.php");
$old_conn = db_connect('old');
$new_conn = db_connect('new');

echo "starting transfer of system user details<br>";

$clear = db_query($new_conn, "TRUNCATE rm_admin_uggroups");
$clear = db_query($new_conn, "TRUNCATE rm_admin_ugmembers");
$clear = db_query($new_conn, "TRUNCATE rm_admin_ugrights");
$clear = db_query($new_conn, "TRUNCATE rm_admin_users");

$move = db_query($new_conn, "INSERT INTO {$_SESSION['new']['DBase']}.rm_admin_uggroups SELECT * FROM {$_SESSION['old']['DBase']}.rm_admin_uggroups");
$move = db_query($new_conn, "INSERT INTO {$_SESSION['new']['DBase']}.rm_admin_ugmembers SELECT * FROM {$_SESSION['old']['DBase']}.rm_admin_ugmembers");
$move = db_query($new_conn, "INSERT INTO {$_SESSION['new']['DBase']}.rm_admin_ugrights SELECT * FROM {$_SESSION['old']['DBase']}.rm_admin_ugrights");
$move = db_query($new_conn, "INSERT INTO {$_SESSION['new']['DBase']}.rm_admin_users SELECT * FROM {$_SESSION['old']['DBase']}.rm_admin_users");

echo "done ....<br>";

?>