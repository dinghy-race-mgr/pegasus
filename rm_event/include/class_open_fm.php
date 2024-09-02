<?php
$instructions_htm = "";
if (!empty($params['instructions']))
{
    $instructions_htm = <<<EOT
<div class="offset-md-1 col-md-10 mt-3">
    <div class="alert alert-warning alert-dismissible fade show fs-6 " role="alert">
        {$params['instructions']}.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
EOT;
}

$fields_bufr = <<<EOT
<!-- boat section -->
<div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;Boat &hellip;</div>
<div class="row mb-3">
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control-plaintext readonly-style" id="class" name="class" placeholder="boat class &hellip;" value="{class-name}" readonly>
            <label for="floatingInput" class="label-style">Class</label>
        </div>
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
            <input type="text" class="form-control" id="club" name="club" placeholder="home club &hellip;" value="" >
            <label for="floatingInput" class="label-style">home club</label>
            <div class="invalid-feedback">enter your home club name (e.g. Exe SC)</div>
        </div>
    </div>
</div>

<!-- optional fleet category field -->
{{fleets_select_htm}}

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
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="helm-age" name="helm-age" placeholder="age if under 18 &hellip;" value="" >
            <label for="floatingInput" class="label-style">Age (if under 18)</label>
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

<!-- optional crew section -->
{{crewname_input_htm}}

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
        <div class="offset-md-1 col-md-10">
            <form id="entryForm" action="rm_event_sc.php?eid={$params['eventid']}&pagestate=newentry&mode={$params['form-mode']}"
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
          
        // Custom validation for sail number field
        let sailnoInput = document.getElementById("sailnumber");
        if (sailnoInput.value === "") {sailnoInput.setCustomValidity("error");} else {sailnoInput.setCustomValidity("");}
     
        // Custom validation for club field
        let clubInput = document.getElementById("club");
        if (clubInput.value === "") {clubInput.setCustomValidity("error");} else {clubInput.setCustomValidity("");}
        
        // Custom validation for (optional) category field
        {{fleets_validation_js}} 
             
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
               
        // Custom validation for (optional) crew name field
        {{crewname_validation_js}}
        
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



