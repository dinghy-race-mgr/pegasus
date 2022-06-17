<?php

/**
 * Templates for adding a new boat or editing an existing boat
 *
 * Currently only the mandatory fields are included and the remaining fields are hidden/defaulted or not used
 *
 * FIXME - need to find way to make included fields configurable by the end user
 */

function boat_fm($params = array())
{
    $label_col = "text-info";
    $lbl_width  = "col-xs-2";
    $fld_width  = "col-xs-6";
    $fld_narrow = "col-xs-3";

    if ($params['action'] == "edit") {
        $instruction_bufr = "Details entered here will be PERMANENTLY changed for this boat &hellip;";
        $post_script = "editboat_sc.php";
        $btn_label = "Change Details";
        $classlist_bufr = <<<EOT
        <div class="form-group form-condensed">
            <label for="classid" class="rm-form-label control-label $lbl_width $label_col">Class</label>
            <div class="input-md $fld_width" style="color: black;">
                <input name="classid" type="hidden" id="classid" readonly value="{$params['sailor']['classid']}">
                <span class="text-muted rm-form-input-md"> {$params['sailor']['classname']} </span>
                      <span class="text-info rm-text-xs"><i>[ * use the add boat option if you want to change class]</i></span>
            </div>
        </div>

EOT;

    } else {
        $instruction_bufr = "Complete his form to create a new boat record &hellip;";
        $post_script = "addboat_sc.php";
        $btn_label = "Add Boat";
        $classlist_bufr = <<<EOT
        <div class="form-group form-condensed">
            <label for="classid" class="rm-form-label control-label $lbl_width $label_col">Class</label>
            <div class="selectfieldgroup $fld_width">
                <select name="classid" class="form-control rm-form-input-md placeholder-md" style="height: 60px ! important"
                        required data-fv-notempty-message="choose the class of boat" id="classid" >
                    {$params['class_list']}
                </select>
            </div>
        </div>
EOT;
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
    $classlist_bufr
    <div class="form-group form-condensed">
        <label for="sailnum" class="rm-form-label control-label $lbl_width $label_col">&nbsp;</label>
        <div class="inputfieldgroup $fld_width">
            <input name="sailnum" autocomplete="off" type="text" class="form-control rm-form-input-md placeholder-md" id="sailnum" value="{sailnum}"
                   placeholder="sail number e.g 12345 ..." required data-fv-notempty-message="sail number is required">
        </div>
    </div> 
    <div class="form-group form-condensed">
        <label for="boatname" class="rm-form-label control-label $lbl_width $label_col">&nbsp;</label> 
        <div class="inputfieldgroup $fld_width">     
            <input name="boatname" autocomplete="off" type="text" class="form-control rm-form-input-md placeholder-md" id="boatname" value="{boatname}"
                   placeholder="boat name ...">
        </div>
    </div>
    <div class="form-group form-condensed">
        <label for="club" class="rm-form-label control-label $lbl_width $label_col">&nbsp;</label> 
        <div class="inputfieldgroup  $fld_width">
            <input name="club" autocomplete="off" type="text" class="form-control rm-form-input-md placeholder-md" id="club" value="{club}"
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
            <label for="helm_dob" class="rm-form-label control-label $lbl_width $label_col">&nbsp;</label> 
            <div class="inputfieldgroup $fld_width">     
                <input name="helm_dob" autocomplete="off" type="text" class="form-control rm-form-input-md placeholder-md" id="helm_dob" value="{helm_dob}"
                       placeholder="date of birth - dd/mm/yyyy ...">
            </div>
        </div> 
EOT;
    }

    // helm email details
    $helm_email_bufr = "";
    if ($params['fields']['helm_email']) {
        $helm_email_bufr .= <<<EOT
        <div class="form-group form-condensed">
            <label for="helm_email" class="rm-form-label control-label $lbl_width $label_col">&nbsp;</label> 
            <div class="inputfieldgroup $fld_width">     
                <input name="helm_email" autocomplete="off" type="email" class="form-control rm-form-input-md placeholder-md" id="helm_email" value="{helm_email}"
                       placeholder="email address ..." data-fv-emailaddress-message="provide a valid email address">
            </div>
        </div> 
EOT;
    }

    // skill details
    $skill_bufr = "";
    if ($params['fields']['skill_level']) {
        $skill_bufr .= <<<EOT
        <div class="form-group form-condensed">
            <label for="skill_level" class="rm-form-label control-label $lbl_width $label_col">Class</label>
            <div class="selectfieldgroup $fld_width">
                <select name="skill_level" class="form-control rm-form-input-md placeholder-md" required data-fv-notempty-message="choose your skill level" id="skill_level" >
                    {$params['skill_list']}
                </select>
            </div>
        </div>
EOT;
    }

    // crew dob details
    $crew_dob_bufr = "";
    if ($params['fields']['dob']) {
        $crew_dob_bufr .= <<<EOT
        <div class="form-group form-condensed">
            <label for="crew_dob" class="rm-form-label control-label $lbl_width $label_col">&nbsp;</label> 
            <div class="inputfieldgroup $fld_width">     
                <input name="crew_dob" autocomplete="off" type="text" class="form-control rm-form-input-md placeholder-md" id="crew_dob" value="{crew_dob}"
                       placeholder="date of birth - dd/mm/yyyy ...">
            </div>
        </div> 
EOT;
    }

    // crew email details
    $crew_email_bufr = "";
    if ($params['fields']['crew_email']) {
        $crew_email_bufr .= <<<EOT
        <div class="form-group form-condensed">
            <label for="crew_email" class="rm-form-label control-label $lbl_width $label_col">&nbsp;</label> 
            <div class="inputfieldgroup $fld_width">     
                <input name="crew_email" autocomplete="off" type="email" class="form-control rm-form-input-md placeholder-md" id="crew_email" value="{crew_email}"
                       placeholder="email address ..." data-fv-emailaddress-message="provide a valid email address">
            </div>
        </div> 
EOT;
    }

    $helm_bufr = <<<EOT
    <div class="form-group form-condensed">
        <label for="helm" class="rm-form-label control-label $lbl_width $label_col">Helm</label>
        <div class="inputfieldgroup $fld_width">
            <div>
            <input name="helm" autocomplete="off" type="text" class="form-control rm-form-input-md placeholder-md" id="helm" value="{helm}"
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
        <label for="crew" class="rm-form-label control-label $lbl_width $label_col">Crew</label>
        <div class="$fld_width">
            <div>
            <input name="crew" autocomplete="off" type="text" class="form-control rm-form-input-md placeholder-md" id="crew" value="{crew}"
                   placeholder="name - e.g. Barney Rubble ...">
            </div>
            $crew_dob_bufr
            $crew_email_bufr
        </div>
    </div>
EOT;

    // composite template
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
                <a type="button" class="btn btn-default btn-lg" href="javascript: history.go(-1)">                   
                    <span class="glyphicon glyphicon-remove"></span>&nbsp;Cancel
                </a>
                
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
    if ($params['mode'] == "race") {
        $txt = "You can now enter this boat for races (and cruises!)";
    } else {
        $txt = "You can now enter this boat for cruises (and races!)";
    }

    $restart = "";
    if ($params['restart']) {
        $restart = <<<EOT
        <button type="button" class="btn btn-primary btn-md" style="position: absolute; right: 15px; bottom: 0px;" 
                onclick="location.href = 'javascript: history.go(-2)';">
            <span class="glyphicon glyphicon-step-backward"></span> Continue
        </button>
EOT;
    }

    $bufr = <<<EOT
     <div class="row margin-top-30">
        <div class="col-xs-8 col-xs-offset-2 col-sm-8 col-sm-offset-2 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">   
            <div class="alert alert-success rm-text-md" role="alert">             
                <div class="row">                   
                    <div class="col-xs-10 col-sm-10 col-md-10 col-lg-10" style="min-height: 200px">
                        <h2>New boat added &hellip;</h2>
                        <p><b>{class} {sailnum}<br>{team}</b></p>
                        <p>$txt</p>
                    </div>
                    <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2" style="min-height: 200px;">
                        $restart
                    </div>
                </div>
            </div>
        </div>
     </div>
EOT;
    return $bufr;
}


function addboat_fail($params = array())
{
    $restart = "";
    if ($params['restart']) {
        $restart = <<<EOT
        <button type="button" class="btn btn-primary btn-md" style="position: absolute; right: 15px; bottom: 0px;" 
                onclick="location.href = 'javascript: history.go(-2)';">
            <span class="glyphicon glyphicon-step-backward"></span> Continue
        </button>
EOT;
    }

    $bufr = <<<EOT
    <div class="row margin-top-30">
        <div class="col-xs-8 col-xs-offset-2 col-sm-8 col-sm-offset-2 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">   
            <div class="alert alert-danger rm-text-md" role="alert"> 
                <div class="row"> 
                    <div class="col-xs-10 col-sm-10 col-md-10 col-lg-10" style="min-height: 200px">
                        <h2>System Error - failed to add boat &hellip;</h2>
                        <p><b>{class} {sailnum}<br>{team}</b></p>
                        <p><small>Please contact your system administrator or try again later  </small></p>
                    </div>
                    <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2" style="min-height: 200px;">
                        $restart
                    </div>
                </div>
            </div>
        </div>
     </div>
EOT;
    return $bufr;
}


function addboat_duplicate($params = array())
{
    $restart = "";
    if ($params['restart']) {
        $restart = <<<EOT
        <button type="button" class="btn btn-primary btn-md" style="position: absolute; right: 15px; bottom: 0px;" 
                onclick="location.href = 'search_pg.php';">
            <span class="glyphicon glyphicon-step-backward"></span> Search again &hellip;
        </button>
EOT;
    }

    $bufr = <<<EOT
    <div class="row margin-top-30">
        <div class="col-xs-8 col-xs-offset-2 col-sm-8 col-sm-offset-2 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">   
            <div class="alert alert-warning rm-text-md" role="alert"> 
                <div class="row">
                    <div class="col-xs-10 col-sm-10 col-md-10 col-lg-10" style="min-height: 200px">              
                        <h2>Duplicate boat &hellip;</h2>
                        <p><b>{class} {sailnum}<br>{team}</b></p>                   
                        <p><small>Not registered - this boat is already registered</small></p>
                        <p><small>Try searching on class name or helm's surname</small></p>
                    </div>
                    <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2" style="min-height: 200px;">
                        $restart
                    </div>
                </div>
            </div>
        </div>
    </div>
EOT;

    return $bufr;
}


function editboat_success($params = array())
{
    $restart = "";
    if ($params['restart']) {
        $restart = <<<EOT
        <button type="button" class="btn btn-primary btn-md" style="position: absolute; right: 15px; bottom: 0px;" 
                onclick="location.href = 'javascript: history.go(-2)';">
            <span class="glyphicon glyphicon-step-backward"></span> Continue
        </button>
EOT;
    }

    $bufr = <<<EOT
     <div class="row margin-top-30">
        <div class="col-xs-8 col-xs-offset-2 col-sm-8 col-sm-offset-2 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2" >   
            <div class="alert alert-success rm-text-md" role="alert" >
                <div class="row">                   
                    <div class="col-xs-10 col-sm-10 col-md-10 col-lg-10" style="min-height: 200px">
                        <h2>Boat details updated &hellip;</h2>
                        <p><b>{class} {sailnum}<br>{team}</b></p>
                    </div>
                    <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2" style="min-height: 200px;">
                        $restart
                    </div>
                </div>                                
            </div>
        </div>
     </div>
EOT;
    return $bufr;
}


function editboat_fail($params = array())
{
    $restart = "";
    if ($params['restart']) {
        $restart = <<<EOT
        <button type="button" class="btn btn-primary btn-md" style="position: absolute; right: 15px; bottom: 0px;" 
                onclick="location.href = 'javascript: history.go(-2)';">
            <span class="glyphicon glyphicon-step-backward"></span> Back
        </button>
EOT;
    }

    $bufr = <<<EOT
    <div class="row margin-top-30">
        <div class="col-xs-8 col-xs-offset-2 col-sm-8 col-sm-offset-2 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2"> 
        
            <div class="alert alert-danger rm-text-md" role="alert" >
                <div class="row">                   
                    <div class="col-xs-10 col-sm-10 col-md-10 col-lg-10" style="min-height: 200px">
                        <h2>Boat details update failed &hellip;</h2>
                        <p><b>{class} {sailnum}<br>{team}</b></p>
                        <p><small>Please contact your system administrator or try again later  </small></p>
                    </div>
                    <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2" style="min-height: 200px;">
                        $restart
                    </div>
                </div>                                
            </div>
        </div>
     </div>
EOT;

    return $bufr;
}
