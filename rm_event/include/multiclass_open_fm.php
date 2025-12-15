<?php
/*multiclass_open_fm.php

An entry form for a multiclass event such as a pursuit race or handicap event

currently no provision for optional fleet category or crew fields as we don't know the class when the form opens
 - for multi class events it is currently assumed the class specific fleet categories won't be used and we will always include the crew fields

--- FIXME -to be done
Better handling of field definitions:
We need to be able to standardise field definitions across all forms (essentially the form floating elements).
Suggest that we hold them in a json file.
The form script will include a list of all possible fields (names) that can be used on that form.  The script will process
each required field and create a bufr for that content - excluding fields to be hidden for this configuration using the style visible=hidden
Note - might want to provide flexibility for which fields are required as well (HOW)
The layout of fields will on the screen will be stored in this script - loading the bufr outputs for each field.

Declaration Text
This should be handled in the same way as the instructions

Compatibility
Should be held in a tooltip - with a button to access.

*/

$form_name = "multiclass_open_fm.php";     // important this matches the form-file field in e_form

$required = "<span class='field-reqd'>&nbsp;<i class='bi bi-asterisk'></i></span>";

$instructions_htm = "";
if (!empty($params['instructions']))
{
    $instructions_htm = <<<EOT
<!-- instructions -->
<div class="">
    <div class="alert alert-light alert-dismissible fade show fs-6 " role="alert">
        {$params['instructions']}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
EOT;
}

// check if we have classes
if (empty($params['class-list']))        // no classes defined just add a simple text field
{
    $class_field_htm = <<<EOT
    <div class="form-floating">       
        <input type="text" class="form-control" id="class" name="class" placeholder="" value="" required autofocus />
        <label for="floatingInput" class="label-style">Class Name $required</label>
        <div class="invalid-feedback">Enter the class you will be sailing (e.g RS400)</div>
    </div>
EOT;
}
else                                     // create select field with supplied classes (+ option to add new class)
{
    $classes = explode(",", $params['class-list']);
    $class_options = "<option value=''></option>";
    foreach ($classes as $class)
    {
        $class_options.= "<option value='$class'>".trim($class)."</option>";
    }

    $class_field_htm = <<<EOT
    <!-- class add button -->     
    <div class="input-group">
        <div class="form-floating">          
            <input class="form-control" list="classopts" id="class" name="class" placeholder="" value="" required autofocus />
            <datalist id="classopts">$class_options</datalist>
            <label for="class" class="label-style">Boat Class $required</label>                       
            <div class="invalid-feedback">please pick your boat class (or add missing class name)</div> 
            <div class="text-primary mx-5">start typing then pick from suggestions &hellip; (or add new class)</div>          
        </div>                  
    </div>      
EOT;
}

$fields_bufr = <<<EOT
<!-- boat section -->
<div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;Boat &hellip;</div>

<div class="row mb-3 gx-5">
    <div class="col-md-6">   
        $class_field_htm
    </div>   
    <div class="col-md-4">
        <div class="form-floating">            
            <input type="text" class="form-control" id="sailnumber" name="sailnumber" placeholder="" value="" required /> 
            <label for="floatingInput" class="label-style">Sail Number $required</label>          
            <div class="invalid-feedback">enter your sail number</div>
        </div>
    </div>
</div>

<div class="row mb-3 gx-5">
    <div class="col-md-6">
        <div class="form-floating">           
            <input type="text" class="form-control" id="boatname" name="boatname" placeholder="e.g Black Pearl &hellip;" value="" /> 
            <label for="floatingInput" class="label-style">Boat Name / Sponsor</label>          
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-floating">            
            <input type="text" class="form-control" id="club" name="club" placeholder="" value="" required> 
            <label for="floatingInput" class="label-style">Home Club $required</label>         
            <div class="invalid-feedback">enter your home club name (e.g. Exe SC)</div>
        </div>
    </div>
</div>

<!-- helm section -->
<div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;Helm &hellip;</div>
<div class="row mb-3 gx-5">
    <div class="col-md-6">
        <div class="form-floating">            
            <input type="text" class="form-control" id="helm-name" name="helm-name"  placeholder="" value="" required />   
            <label for="floatingInput" class="label-style">Helm Name $required</label>         
            <div class="invalid-feedback">enter the helm name (e.g. Ben Ainslie).</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-floating"  style="visibility:visible;">           
            <input type="text" class="form-control" id="helm-age" name="helm-age" placeholder="" value="" /> 
            <label for="floatingInput" class="label-style">Age (if under 18) </label>           
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-floating" style="visibility:hidden;">                  
            <input type="text" class="form-control" list="genderlist" id="h-gender" name="h-gender" 
                   pattern="female|male|other" placeholder="" value="" />
            <datalist id="genderlist"><option value="female"><option value="male"><option value="other"></datalist>
            <label for="h-gender" class="label-style">Gender</label>
            <div class="invalid-feedback">female/male/other - or leave blank.</div>
        </div>
    </div>
</div>

<div class="row mb-3 gx-5">
    <div class="col-md-6">
        <div class="form-floating">           
            <input type="email" class="form-control" id="helm-email" name="helm-email"  placeholder="" value="" required>
            <label for="floatingInput" class="label-style">Email $required</label>
            <div class="invalid-feedback">enter valid email address (e.g. ben@gmail.com)</div>
        </div>
    </div>
    <div class="col-md-6" style="">
        <div class="form-floating">           
            <input type="text" class="form-control" id="ph-mobile" name="ph-mobile" pattern="[0-9]+" minlength="9" maxlength="11" placeholder="" value="" />
            <label for="floatingInput" class="label-style">Mobile </label>
            <div class="invalid-feedback">enter phone number (e.g. 07804555666)</div>
            <div class="text-primary mx-5">9 to 11 digits no spaces &hellip;</div> 
        </div>
    </div>
    
</div>

<!-- crew section (info not required) -->
<div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;Crew &hellip;</div>
<div class="row mb-3 gx-5">
    <div class="col-md-6">
        <div class="form-floating">            
            <input type="text" class="form-control" id="crew-name" name="crew-name" placeholder="" value="" >  
            <label for="floatingInput" class="label-style">Crew Name</label>         
            <div class="invalid-feedback">enter the crew name (e.g. Ben Ainslie).</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-floating">          
            <input type="text" class="form-control" id="crew-age" name="crew-age" placeholder="" value="" >  
            <label for="floatingInput" class="label-style">Age (if under 18)</label>          
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-floating" style="visibility:hidden;">                 
            <input type="text" class="form-control" list="genderlist" id="c-gender" name="c-gender" 
                   pattern="female|male|other" placeholder="" value="">
            <datalist id="genderlist"><option value="female"><option value="male"><option value="other"></datalist>
            <label for="h-gender" class="label-style">Gender</label>
            <div class="invalid-feedback">female/male/other - or leave blank.</div>
        </div>
    </div>
</div>

<!-- admin section -->
<div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;Emergency Contact and Consent &hellip;</div>  
<div class="row mb-3 gx-5">
    <div class="col-md-6">
        <div class="form-floating">           
            <input type="text" class="form-control" id="ph-emer" name="ph-emer" placeholder="" value="" required>            
            <label for="floatingInput" class="label-style">Emergency Contact (during event)<span class="field-reqd"> *</span></label>
            <div class="invalid-feedback">enter contact name and phone number.</div>
            <div class="text-primary mx-5"><i>(e.g. Mary Little, 07804555666)</i></div>
        </div>
    </div> 
</div>

<div class="row mb-3 gx-5">  
    <div class="col-md-8"> 
        I undertake that I hold valid third party insurance of a minimum of &pound;3,000,000 for the entered boat 
        and I agree to be bound by the rules of the event as set out in the Notice of Race and Sailing Instructions
    </div>
    <div class="col-md-4">                  
        <div class="mb-3 form-check">
            <label class="form-check-label" for="consent">&nbsp;&nbsp;&nbsp;I consent<span class="field-reqd"> *</span></label>
            <input type="checkbox" class="form-check-input" id="consent" name="consent" required />
            <div class="invalid-feedback">You must agree to the terms and conditions to submit your entry.</div>
        </div>
    </div>
</div>
EOT;

$buttons_bufr = <<<EOT
<div class="mb-3 d-flex justify-content-end">
    <button type="submit" disabled style="display: none" aria-hidden="true"></button> <!-- hack to stop form submitting on enter-->
    <button type="button" class="btn btn-secondary me-2" onclick="history.back()">Cancel</button>
    <button type="submit" class="btn btn-primary me-2">Enter Boat</button>                   
</div>
EOT;

$form_htm = <<<EOT
<!-- form details -->
<div class="container mt-1">
    <div class="row">
        <div class="">
            <form id="entryForm" action="rm_event_sc.php?eid={$params['eventid']}&pagestate=newentry&formname=$form_name&mode={$params['form-mode']}"
                  method="post" role="form" autocomplete="off" class="needs-validation" novalidate>
                  $fields_bufr
                  $buttons_bufr
            </form>
        </div>
    </div>
</div>
EOT;

$validation_htm = <<<EOT
<script>
(function () {
    "use strict";
    var form = document.getElementById("entryForm");

    form.addEventListener("submit", function (event) {           
        if (!form.checkValidity()) {   
            event.preventDefault();
            event.stopPropagation();
        }

        form.classList.add("was-validated");        
    });
})();
</script>
EOT;



