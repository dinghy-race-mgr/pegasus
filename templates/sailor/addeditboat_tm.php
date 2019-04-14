<?php

/*
 *
 *
*classid 	int(10)	        NO
*boatnum 	varchar(20) 	NO
*sailnum 	varchar(20)	    NO
*boatname 	varchar(60)	    OPTIONAL
*helm 	varchar(40)	        NO
*helm_dob 	date	        OPTIONAL
*helm_email 	varchar(100)	OPTIONAL
*crew 	varchar(40)	YES 	OPTIONAL
*crew_dob 	date	YES 	OPTIONAL
*crew_email 	varchar(100)	OPTIONAL
*club 	varchar(60)	    OPTIONAL
*personal_py 	int(5)	    SET AUTO
skill_level 	varchar(20)	OPTIONAL
*flight 	varchar(20)	        NONE
*regular 	tinyint(1)	    HIDDEN 		0
*last_entry 	date	        NONE
*last_event 	int(11)	        NONE
*active 	tinyint(1)	        HIDDEN 	1
*prizelist 	varchar(200)	NONE 		NULL
*grouplist 	varchar(100)	NONE 		NULL
memberid 	varchar(20)	    NONE 		NULL
*updby 	varchar(20)	        HIDDEN 		"rm_sailor"
 */

function boat_fm($params = array())
{
    $lbl_width  = "col-xs-2";
    $fld_width  = "col-xs-6";
    $fld_narrow = "col-xs-3";

    if ($params['mode'] == "edit")
    {
        $instruction_bufr = "Edit boat details &hellip;";
        $post_script = "editboat_sc.php";
        $btn_label = "Change Details";
    }
    else
    {
        $instruction_bufr = "Add boat details &hellip;";
        $post_script = "addboat_sc.php";
        $btn_label = "Add Boat";
    }

    // hidden fields
    $hidden_bufr = <<<EOT
    <input name="boatnum"     type="hidden" id="boatnum"     value="{boatnum}">
    <input name="personal_py" type="hidden" id="personal_py" value="{personal_py}">
    <input name="flight"      type="hidden" id="flight"      value="{flight}">
    <input name="regular"     type="hidden" id="regular"     value="{regular}">
    <input name="last_entry"  type="hidden" id="last_entry"  value="{last_entry}">
    <input name="last_event"  type="hidden" id="last_event"  value="{last_event}">
    <input name="active"      type="hidden" id="active"      value="1"> 
    <input name="prizelist"   type="hidden" id="prizelist"   value="{prizelist}">           
    <input name="grouplist"   type="hidden" id="grouplist"   value="{grouplist}">
    <input name="memberid"    type="hidden" id="memberid"    value="{memberid}">
    <input name="updby"       type="hidden" id="updby"       value="{updby}">
EOT;

    // boat details
    $boat_bufr = <<<EOT
    <div class="form-group form-condensed">
        <label for="classid" class="rm-form-label control-label $lbl_width">Class</label>
        <div class="selectfieldgroup $fld_width">
            <select name="classid" class="form-control input-lg " required data-fv-notempty-message="choose the class of boat" id="classid" >
                {$params['class_list']}
            </select>
        </div>
    </div>
    <div class="form-group form-condensed">
        <label for="helm" class="rm-form-label control-label $lbl_width">&nbsp;</label>
        <div class="inputfieldgroup $fld_width">
            <input name="sailnum" autocomplete="off" type="text" class="form-control input-lg " id="sailnum" value="{sailnum}"
                   placeholder="sail number e.g 12345 ..." required data-fv-notempty-message="sail number is required">
        </div>
    </div> 
    <div class="form-group form-condensed">
        <label for="boatname" class="rm-form-label control-label $lbl_width">&nbsp;</label> 
        <div class="inputfieldgroup $fld_width">     
            <input name="boatname" autocomplete="off" type="text" class="form-control input-lg" id="boatname" value="{boatname}"
                   placeholder="boat name ...">
        </div>
    </div>
    <div class="form-group form-condensed">
        <label for="classid" class="rm-form-label control-label $lbl_width">&nbsp;</label> 
        <div class="inputfieldgroup  $fld_width">
            <input name="club" autocomplete="off" type="text" class="form-control input-lg " id="club" value="{club}"
                   placeholder="home club e.g Starcross YC ..." required data-fv-notempty-message="club name is required">
        </div>       
    </div>
EOT;

// helm dob details
    $helm_dob_bufr = "";
    if ($params['fields']['dob'])
    {
        $helm_dob_bufr.= <<<EOT
        <div class="form-group form-condensed">
            <label for="helm_dob" class="rm-form-label control-label $lbl_width">&nbsp;</label> 
            <div class="inputfieldgroup $fld_width">     
                <input name="helm_dob" autocomplete="off" type="text" class="form-control input-lg" id="helm_dob" value="{helm_dob}"
                       placeholder="date of birth - dd/mm/yyyy ...">
            </div>
        </div> 
EOT;
    }

    // helm email details
    $helm_email_bufr = "";
    if ($params['fields']['helm_email'])
    {
        $helm_email_bufr .= <<<EOT
        <div class="form-group form-condensed">
            <label for="helm_email" class="rm-form-label control-label $lbl_width">&nbsp;</label> 
            <div class="inputfieldgroup $fld_width">     
                <input name="helm_email" autocomplete="off" type="email" class="form-control input-lg" id="helm_email" value="{helm_email}"
                       placeholder="email address ..." data-fv-emailaddress-message="provide a valid email address">
            </div>
        </div> 
EOT;
    }

    // skill details
    $skill_bufr = "";
    if ($params['fields']['skill_level'])
    {
        $skill_bufr.= <<<EOT
        <div class="form-group form-condensed">
            <label for="classid" class="rm-form-label control-label $lbl_width">Class</label>
            <div class="selectfieldgroup $fld_width">
                <select name="skill_level" class="form-control input-lg " required data-fv-notempty-message="choose your skill level" id="skill_level" >
                    {$params['skill_list']}
                </select>
            </div>
        </div>
EOT;
    }

    // crew dob details
    $crew_dob_bufr = "";
    if ($params['fields']['dob'])
    {
        $crew_dob_bufr.= <<<EOT
        <div class="form-group form-condensed">
            <label for="crew_dob" class="rm-form-label control-label $lbl_width">&nbsp;</label> 
            <div class="inputfieldgroup $fld_width">     
                <input name="crew_dob" autocomplete="off" type="text" class="form-control input-lg" id="crew_dob" value="{crew_dob}"
                       placeholder="date of birth - dd/mm/yyyy ...">
            </div>
        </div> 
EOT;
    }

    // crew email details
    $crew_email_bufr = "";
    if ($params['fields']['crew_email'])
    {
        $crew_email_bufr.= <<<EOT
        <div class="form-group form-condensed">
            <label for="crew_email" class="rm-form-label control-label $lbl_width">&nbsp;</label> 
            <div class="inputfieldgroup $fld_width">     
                <input name="crew_email" autocomplete="off" type="email" class="form-control input-lg" id="crew_email" value="{crew_email}"
                       placeholder="email address ..." data-fv-emailaddress-message="provide a valid email address">
            </div>
        </div> 
EOT;
    }

    $helm_bufr = <<<EOT
    <div class="form-group form-condensed">
        <label for="helm" class="rm-form-label control-label $lbl_width">Helm</label>
        <div class="inputfieldgroup $fld_width">
            <div>
            <input name="helm" autocomplete="off" type="text" class="form-control input-lg " id="helm" value="{helm}"
                   placeholder="name - e.g. Fred Flintstone ..." required data-fv-notempty-message="helm name is required">
            </div>
            $helm_dob_bufr
            $helm_email_bufr                 
            $skill_bufr           
        </div>                
    </div>
EOT;

    $crew_bufr = <<<EOT
    <div class="form-group form-condensed">
        <label for="crew" class="rm-form-label control-label $lbl_width">Crew</label>
        <div class="$fld_width">
            <div>
            <input name="crew" autocomplete="off" type="text" class="form-control input-lg " id="crew" value="{crew}"
                   placeholder="name - e.g. Barney Rubble ...">
            </div>
            $crew_dob_bufr
            $crew_email_bufr
        </div>
    </div>
EOT;


    $bufr = <<<EOT
    <div class="rm-form-style">   
        <div class="row">     
            <div class="col-xs-10 col-sm-10 col-md-8 col-lg-8 alert alert-info"  role="alert">$instruction_bufr</div>
        </div>
    
        <style type="text/css">
           #editboatForm  .inputfieldgroup .form-control-feedback { top: 0; right: -30px; }
           #editboatForm  .selectfieldgroup .form-control-feedback { top: 0; right: -30px; }
           .has-error .help-block, .has-error .control-label, .has-error .form-control-feedback {color: lightcoral !important;}
           .has-success .help-block, .has-success .control-label, .has-success .form-control-feedback {color: springgreen !important;}
        </style>
        <form id="editboatForm" class="form-horizontal" action="$post_script" method="post"
            data-fv-addons="mandatoryIcon"
            data-fv-addons-mandatoryicon-icon="glyphicon glyphicon-asterisk"
            data-fv-framework="bootstrap"
            data-fv-icon-valid="glyphicon glyphicon-ok"
            data-fv-icon-invalid="glyphicon glyphicon-remove"
            data-fv-icon-validating="glyphicon glyphicon-refresh" >
            $hidden_bufr   
            $boat_bufr
            <hr>
            $helm_bufr
            <hr>    
            $crew_bufr
    
            <div class="pull-right margin-top-20">
                <button type="button" class="btn btn-default btn-lg" onclick="location.href = 'options_pg.php';">
                    <span class="glyphicon glyphicon-remove"></span>&nbsp;Cancel
                </button>
                
                <button type="submit" class="btn btn-warning btn-lg" >
                    <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;<b>$btn_label</b>
                </button>
            </div>
    
        </form>
    </div>

    <script>
        $(document).ready(function() {
            $('#editboatForm').formValidation({
                excluded: [':disabled'],
            })
            $('#resetBtn').click(function() {
             $('#editboatForm').data('bootstrapValidator').resetForm(true);
            });
        });
    </script>
EOT;

    return $bufr;
}

function addboat_success($params = array())
{
    $bufr = <<<EOT
     <div class="row margin-top-40">
        <div class="col-xs-8 col-xs-offset-2 col-sm-8 col-sm-offset-2 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">   
            <div class="alert alert-success rm-text-md" role="alert"> 
                <h2>New boat added &hellip;</h2>
                <p><b>{class} {sailnum}<br>{team}</b></p> 
                <p>You can now enter this boat for future races</p>
            </div>
        </div>
     </div>
EOT;
    return $bufr;
}

function addboat_fail($params = array())
{
    $bufr = <<<EOT
    <div class="row margin-top-40">
        <div class="col-xs-8 col-xs-offset-2 col-sm-8 col-sm-offset-2 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">   
            <div class="alert alert-danger rm-text-md" role="alert"> 
                <h2>Failed to add boat &hellip;</h2>
                <p><b>{class} {sailnum}<br>{team}</b></p> 
                <p>This may be due to the unavailability of the raceManager server - try again later or contact your system administrator</p>
            </div>
        </div>
     </div>
EOT;
    return $bufr;

}

function addboat_duplicate($params = array())
{
    $bufr = <<<EOT
    <div class="row margin-top-40">
        <div class="col-xs-8 col-xs-offset-2 col-sm-8 col-sm-offset-2 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">   
            <div class="alert alert-info rm-text-md" role="alert"> 
                <h2>Duplicate boat &hellip;</h2> 
                <p> <b>{class} {sailnum} / {helm}</b><br></p> 
                <p>This boat is already registered </p>
                <p><small>If it is not recognised when searching the system please contact your system administrator</small></p>
            </div>
        </div>
     </div>
EOT;

    return $bufr;
}

function editboat_success($params = array())
{
    $bufr = <<<EOT
     <div class="row margin-top-40">
        <div class="col-xs-8 col-xs-offset-2 col-sm-8 col-sm-offset-2 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">   
            <div class="alert alert-success rm-text-md" role="alert"> 
                <h2>Boat details updated &hellip;</h2>
                <p><b>{class} {sailnum}<br>{team}</b></p> 
            </div>
        </div>
     </div>
EOT;


    return $bufr;
}

function editboat_fail($params = array())
{
    $bufr = <<<EOT
    <div class="row margin-top-40">
        <div class="col-xs-8 col-xs-offset-2 col-sm-8 col-sm-offset-2 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">   
            <div class="alert alert-danger rm-text-md" role="alert"> 
                <h2>Boat details update failed &hellip;</h2>
                <p><b>{class} {sailnum}<br>{team}</b></p> 
                <p><small>This may be due to the unavailability of the raceManager server - try again later or contact your system administrator</small></p>
            </div>
        </div>
     </div>
EOT;
    return $bufr;
}
