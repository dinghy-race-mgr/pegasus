<?php

// set acronym if not set
//if ($values['acronym']=="") { $values['acronym'] = substr($values['classname'],0,6) ;}

// create RYA code (acronym + rya_crew+rya_rig+rya_spinnaker
//if ($values['rya_id']=="") { $values['rya_id'] = strtoupper($values['acronym'].$values['crew'].$values['rig'].$values['spinnaker']); }

// set active flag
$values['active'] = 1;

// check local PN is set - if not set to national value
if ($values['local_py']=="") {$values['local_py'] = $values['nat_py'];}

// check PNs are valid
if (is_numeric($values['nat_py']))
{
   if (intval($values['nat_py'])<400 OR intval($values['nat_py'])>2000)
   {
       $message = "ERROR - the national PY must be a number between 400 and 2000";
       return false;
   }
}
else
{
    $message = "ERROR - the national PY must be a number";
    return false;
}

if (is_numeric($values['local_py']))
{
   if (intval($values['local_py'])<400 OR intval($values['local_py'])>2000)
   {
       $message = "ERROR - the local PY must be a number between 400 and 2000";
       return false;
   }
}
else
{
    $message = "ERROR - the local PY must be a number";
    return false;
}


return true;

?>