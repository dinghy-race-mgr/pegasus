<?php
include ("{$loc}/common/lib/fieldchk_lib.php");

function t_event_AddEditBefore($values, $conn)
{
	$msg = "";
	
	if ($values['event_type'] == "race")
	{
	    // check race format 
		if (!f_check_exists("t_cfgrace", " id={$values['event_format']} ", $conn))
		{
			$msg.= "- race format is not recognised ";
		}
		
		// check race order or start time provided
		if (empty($values['event_order'])) 
		{ 
			$msg.= "- race event must have event order on day defined "; 
		}
			
		// check start_interval is set if start_scheme has been set
		if (f_values_dependset($values['start_scheme'], $values['start_interval']))
		{ 
			$msg.= "- default start scheme has changed and the start interval must be set ";
		}

		// check series code exists
		if (!f_check_exists("t_series", " seriescode='{$values['seriescode']}' ", $conn))
		{
			$msg.= "- series code is not recognised ";
		}
		
		// check entry type is set
		if (empty($values['event_entry']))
		{
			$msg.= "- race entry method must be set ";		
		}
		
	}
	elseif ($values['event_type'] == "training")
	{
		// no additional checks required
	}
	elseif  ($values['event_type'] == "social")
	{
		// no additional checks required	
	}
	elseif  ($values['event_type'] == "cruise") 
	{
		// no additional checks required	
	}

	return $msg;
}



function t_cfgfleet_AddEditBefore($values)
{	
	$msg = "";
	
	// absolute time limit check
	if (f_values_lessthan($values['timelimit_abs'], 0)) { $msg.= "- absolute time limit is negative "; }

	// relative  time limit check
	if (f_values_lessthan($values['timelimit_rel'], 0)) { $msg.= "- relative time limit is negative "; }

	// signal check
	if (f_values_equal($values['warn_signal'], $values['prep_signal'])) { $msg.= "- warning and preparatory signals are the same "; }

	// py check
	if (!f_values_inrange($values['max_py'], 1, 10000)) { $msg.= "- max PY must be between 1 and 10000 "; }
	if (!f_values_inrange($values['min_py'], 1, 10000)) { $msg.= "- min PY must be between 1 and 10000 "; }
    if (!f_values_insequence($values['min_py'], $values['max_py'])) { $msg.= "- max PY must be greater than min PY "; }


	// helm age check
	if (!f_values_inrange($values['min_helmage'], 1, 100)) { $msg.= "- min helm age must be between 1 and 100 "; }
    if (!f_values_inrange($values['max_helmage'], 1, 100)) { $msg.= "- min helm age must be between 1 and 100 "; }
    if (!f_values_insequence($values['min_helmage'], $values['max_helmage'])) { $msg.= "- max helm age must be greater than min helm age "; }


	// default laps
	if (f_values_lessthan($values['defaultlaps'], 0)) { $msg.= "- default laps must be greater than 0"; }

	// class conflicts
	if (!empty($values['classinc']) AND !empty($values['classexc']))
	{
		$array1 = explode(",", $values['classinc']);
		$array2 = explode(",", $values['classexc']);
		$intersect = array_intersect($array1, $array2);
		if ($intersect)
		{
			$msg.= "- some classes are both specifically included and excluded";
		}
	}

    return $msg;
}


	
	

?>