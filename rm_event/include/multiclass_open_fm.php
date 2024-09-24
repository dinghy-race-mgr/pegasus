<?php

$form_name = "multiclass_open_fm.php";     // important this matches the form-file field in e_form


$instructions_htm = "";
if (!empty($params['instructions']))
{
    $instructions_htm = <<<EOT
<!-- instructions -->
<div class="mt-3">
    <div class="alert alert-warning alert-dismissible fade show fs-6 " role="alert">
        {$params['instructions']}.
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
            <input type="text" class="form-control" id="class" name="class" placeholder="class name &hellip;" value="" required autofocus>
            <label for="floatingInput" class="label-style">Class Name <span class="field-reqd"> *</span></label>
            <div class="invalid-feedback">enter your sailnumber</div>
    </div>
EOT;
}
else                                     // create select field with supplied classes (+ option to add new class)
{
    $classes = explode(",", $params['class-list']);
    $class_options = "";
    foreach ($classes as $class)
    {
        $class_options.= "<option value='$class'>".trim($class)."</option>";
    }

    $class_field_htm = <<<EOT
        <div class="form-floating">
           <label for="class" class="control-label col-sm-3">Boat Class<span class="field-reqd"> *</span></label>
           <div class="input-group col-xs-8">
              <!-- input class="form-control" list="classlist" id="class" name="class" placeholder="Type to search..." value="" required autofocus -->
              <select class="form-control" name="class" id="class" required autofocus >
                 <option value=''>pick class &hellip;</option>
                 <datalist id="classlist">
                    $class_options
                 </datalist>
              </select>
           </div>
           <div class="invalid-feedback">please select your boat class (or add and select)</div>
        </div>
        <button id="add-option" class="btn btn-link">add class if not in list</button>
        <!-- class add button -->
        <script>
            var addOption = document.getElementById("add-option");
            var selectField = document.getElementById("class");
            addOption.addEventListener("click", function() {
                var item = prompt("What class do you want to enter?  . . . new class will appear at bottom of list");
                var option = document.createElement("option");
                option.setAttribute("value", item);
                var optionName = document.createTextNode(item);
                option.appendChild(optionName);
                selectField.appendChild(option);
            });
        </script>
        <!-- https://stackoverflow.com/questions/37815200/bootstrap-select-menu-add-new-option -->
EOT;
}


$fields_bufr = <<<EOT
<!-- boat section -->
<div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;Boat &hellip;</div>

<div class="row mb-3">
    <div class="col-md-6">
        $class_field_htm
    </div>
   
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="sailnumber" name="sailnumber" placeholder="sail number &hellip; value="" required autofocus>
            <label for="floatingInput" class="label-style">Sail Number<span class="field-reqd"> *</span></label>
            <div class="invalid-feedback">enter your sailnumber</div>
        </div>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="boatname" name="boatname" placeholder="name / sponsor &hellip;" value="" >
            <label for="floatingInput" class="label-style">boat name / sponsor</label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="club" name="club" placeholder="home club &hellip;" value="" required>
            <label for="floatingInput" class="label-style">home club<span class="field-reqd"> *</span></label>
            <div class="invalid-feedback">enter your home club name (e.g. Exe SC)</div>
        </div>
    </div>
</div>

<!-- helm section -->
<div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;Helm &hellip;</div>
<div class="row mb-3">
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="helm-name" name="helm-name" placeholder="helm name &hellip;" value="" required>
            <label for="floatingInput" class="label-style">Name<span class="field-reqd"> *</span></label>
            <div class="invalid-feedback">enter the helm name (e.g. Ben Ainslie).</div>
        </div>
    </div>
</div>    
<div class="row mb-3">
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="helm-age" name="helm-age" placeholder="age if under 18 &hellip;" value="" >
            <label for="floatingInput" class="label-style">Age (if under 18)</label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-floating">      
            <input class="form-control" list="genderlist" id="h-gender" name="h-gender" placeholder="Type to search..." value="">
            <datalist id="genderlist">
              <option value="female">
              <option value="male">
              <option value="other">
            </datalist>
            <label for="h-gender" class="form-label">Gender</label>
        </div>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="ph-mobile" name="ph-mobile" placeholder="mobile phone number &hellip;" value="">
            <label for="floatingInput" class="label-style">Contact Phone</label>
            <div class="invalid-feedback">enter phone number (e.g. 07804555666).</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="helm-email" name="helm-email" placeholder="email address &hellip;" value="" >
            <label for="floatingInput" class="label-style">Contact Email</label>
            <div class="invalid-feedback">enter valid email address (e.g. ben@gmail.com).</div>
        </div>
    </div>
</div>

<!-- crew section (info not required) -->
<div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;Crew &hellip;</div>
<div class="row mb-3">
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="crew-name" name="crew-name" placeholder="crew name &hellip;" value="" >
            <label for="floatingInput" class="label-style">Name</label>
            <div class="invalid-feedback">enter the crew name (e.g. Ben Ainslie).</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="crew-age" name="crew-age" placeholder="age if under 18 &hellip;" value="" >
            <label for="floatingInput" class="label-style">Age (if under 18)</label>
        </div>
    </div>
</div>

<!-- admin section -->
<div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;Emergency Contact and Consent &hellip;</div>  
<div class="row mb-3">
    <div class="col-md-8">
        <div class="form-floating">
            <input type="text" class="form-control" id="ph-emer" name="ph-emer" placeholder="emergency contact phone number during event &hellip;" value="" required>
            <label for="floatingInput" class="label-style">Emergency Telephone Contact (during event)<span class="field-reqd"> *</span></label>
            <div class="invalid-feedback">enter phone number (e.g. 07804555666).</div>
        </div>
    </div> 
</div>
<div class="row mb-3">  
    <div class="col-md-8">
        I undertake that I hold valid third party insurance of a minimum of &pound;3,000,000 for the entered boat 
        and I agree to be bound by the rules of the event as set out in the Notice of Race and Sailing Instructions
    </div>
    <div class="col-md-4">                  
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="consent" name="consent" required />
            <label class="form-check-label" for="consent">&nbsp;&nbsp;&nbsp;I consent<span class="field-reqd"> *</span></label>
            <div class="invalid-feedback">You must agree to the terms and conditions to submit your entry.</div>
        </div>
    </div>
</div>
EOT;

$buttons_bufr = <<<EOT
<div class="mb-3 d-flex justify-content-end">
    <button type="cancel" class="btn btn-secondary me-2" onclick="history.back()">Cancel</button>
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
     
        // Custom validation for club field
        let clubInput = document.getElementById("club");
        if (clubInput.value === "") {clubInput.setCustomValidity("error");} else {clubInput.setCustomValidity("");}
             
        // Custom validation for helm name field
        let helmInput = document.getElementById("helm-name");
        if (helmInput.value === "") {helmInput.setCustomValidity("error");} else {helmInput.setCustomValidity("");}
        
        // custom validation for ph-mobile field
        let mobileInput = document.getElementById("ph-mobile");
        let mobileRegex = /^(?:\d[- ]*){9,}$/;
        if (!mobileRegex.test(mobileInput.value)) {mobileInput.setCustomValidity("error");} else { mobileInput.setCustomValidity("");}

        // custom validation for email field
        let emailInput = document.getElementById("helm-email");
        let emailRegex = /^(?:[\w\.\-]+@([\w\-]+\.)+[a-zA-Z]+)$/;
        if (!emailRegex.test(emailInput.value)) {emailInput.setCustomValidity("error");} else { emailInput.setCustomValidity("");}
               
        // Custom validation for (optional) crew name field - none
        //let crewInput = document.getElementById("crew-name");
        //if (crewInput.value === "") {crewInput.setCustomValidity("error");} else {crewInput.setCustomValidity("");}
        
        // custom validation for ph-emer field
        let emerInput = document.getElementById("ph-emer");
        let emerRegex = /^(?:\d[- ]*){9,}$/;
        if (!emerRegex.test(emerInput.value)) {emerInput.setCustomValidity("error");} else { emerInput.setCustomValidity("");}
            
        // Custom validation for consent field
        let consentInput = document.getElementById("consent");
        if (consentInput.value === "") {consentInput.setCustomValidity("error");} else {consentInput.setCustomValidity("");}

        form.classList.add("was-validated");
    });
})();
</script>
EOT;



