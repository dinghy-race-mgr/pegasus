<?php
// script for dependent drop down when checking people belonging to a rota


include("include/dbcommon.php");

$result = array();
$duty = db_addslashes(trim(postvalue('category')));
$sql = "SELECT * FROM t_rotamember WHERE rotas LIKE '%$duty%'";
$rs = CustomQuery($sql);

while ($data = db_fetch_array($rs)) {
  $result[] = array(
    'id' => $data['firstname']." ".$data['familyname'],
    'name' => $data['familyname']." ".$data['firstname']
  );
}
error_log("call: ".print_r($result,true),3,"aaaa_debug.log");
echo json_encode($result);

?>