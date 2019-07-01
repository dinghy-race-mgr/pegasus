<?php

	
/* database details */
    $_SESSION['db_host']="127.0.0.1";               
    $_SESSION['db_user']="root";                   
    $_SESSION['db_pass']="";                                   
    $_SESSION['db_name']="syc_clubm";  
    $_SESSION['protocol'] = "http://";	


/* database details */
/*
    $_SESSION['db_host']="127.0.0.1";               
    $_SESSION['db_user']="syc_club_manage1";                   
    $_SESSION['db_pass']="manageC1ub!";                                   
    $_SESSION['db_name']="syc_clubm"; 
	$_SESSION['protocol'] = "https://";
	*/
	
/* duty instructions */
$duty_instruction = array(
    "duty_1" => "PLEASE ARRIVE AT LEAST AN HOUR BEFORE THE PUBLISHED DUTY TIME",
    "duty_2" => "PLEASE ARRIVE AT LEAST AN HOUR BEFORE THE PUBLISHED DUTY TIME",
    "duty_3" => "PLEASE ARRIVE AT LEAST AN HOUR BEFORE THE PUBLISHED DUTY TIME",
    "duty_4" => "PLEASE ARRIVE AT LEAST AN HOUR BEFORE THE PUBLISHED DUTY TIME",
	"duty_5" => "PLEASE ARRIVE AT LEAST AN HOUR BEFORE THE PUBLISHED DUTY TIME",
	"duty_6" => "PLEASE ARRIVE AT LEAST AN HOUR BEFORE THE PUBLISHED DUTY TIME",
	"duty_7" => "",
	"duty_8" => ""
);

/* duty name mappings */
$duty_type = array(
    "duty_1" => "OOD",
    "duty_2" => "AOOD",
    "duty_3" => "AOOD",
    "duty_4" => "Safety Boat 1",
	"duty_5" => "Safety Boat 2",
	"duty_6" => "Safety Boat 3",
	"duty_7" => "Galley",
	"duty_8" => "Bar"
);	
?>