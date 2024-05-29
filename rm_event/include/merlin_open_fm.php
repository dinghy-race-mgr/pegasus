<?php
$instructions_htm = "";
if (!empty($params['newentry-instructions']))
{
    $instructions_htm = <<<EOT
    <div class="alert alert-warning alert-dismissible fade show fs-6" role="alert">
        <strong>Holy guacamole!</strong> {$params['newentry-instructions']}.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
EOT;
}

// FIXME - need to add values so that it can be used for edit

$fields_bufr = <<<EOT
<!-- boat section -->
<div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;Boat &hellip;</div>
<div class="row mb-3">
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control-plaintext readonly-style" id="class" name="class" placeholder="boat class &hellip;" value="Merlin Rocket" readonly>
            <label for="floatingInput">Class</label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="sailnumber" name="sailnumber" placeholder="sail number &hellip; value="" required autofocus>
            <label for="floatingInput">Sail Number</label>
            <div class="invalid-feedback">Please enter your sailnumber</div>
        </div>
    </div>
</div>
<div class="form-floating mb-3">
    <input type="text" class="form-control" id="boatname" name="boatname" placeholder="name / sponsor &hellip;" value="" >
    <label for="floatingInput">boat name / sponsor</label>
</div>
<div class="form-floating mb-3">
    <input type="text" class="form-control" id="club" name="club" placeholder="home club &hellip;" value="" >
    <label for="floatingInput">home club</label>
    <div class="invalid-feedback">Please enter your home club name (e.g. Exe SC)</div>
</div>
<div class="row mb-3">
    <div class="col-md-6">
        <div class="form-floating mb-3">      
            <select class="form-select" id="category" name="category" aria-label="Default select example">
                <option>pick a fleet</option>
                <option value="bronze">Bronze</option>
                <option value="silver">Silver</option>
                <option value="gold">Gold</option>
                <option value="platinum">Platinum</option>
                <option value="unknown">Not Sure &hellip;</option>
            </select>
            <label for="floatingSelect">fleet category</label>
            <div class="invalid-feedback">Please pick a fleet from the list</div>
        </div>
    </div>
</div>

<!-- helm section -->
<div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;Helm &hellip;</div>
<div class="row mb-3">
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="helm-name" name="helm-name" placeholder="helm name &hellip;" value="" autofocus>
            <label for="floatingInput">Name</label>
            <div class="invalid-feedback">Please enter the name of the helm (e.g. Ben Ainslie).</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="helm-age" name="helm-age" placeholder="age if under 18 &hellip;" value="" >
            <label for="floatingInput">Age (if under 18)</label>
        </div>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="ph-mobile" name="ph-mobile" placeholder="mobile phone number &hellip;" value="">
            <label for="floatingInput">Mobile</label>
            <div class="invalid-feedback">Please enter a 11 digit phone number (e.g. 07804555666).</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="email" name="email" placeholder="email address &hellip;" value="" >
            <label for="floatingInput">Email</label>
            <div class="invalid-feedback">Please enter a valid email address (e.g. ben@gmail.com).</div>
        </div>
    </div>
</div>
<!-- crew section -->
<div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;Crew &hellip;</div>
<div class="row mb-3">
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="crew-name" name="crew-name" placeholder="crew name &hellip;" value="" autofocus>
            <label for="floatingInput">Name</label>
            <div class="invalid-feedback">Please enter the name of the crew (e.g. Ben Ainslie).</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="crew-age" name="crew-age" placeholder="age if under 18 &hellip;" value="" >
            <label for="floatingInput">Age (if under 18)</label>
        </div>
    </div>
</div>

<!-- admin section -->
<div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;Emergency Contact and Consent &hellip;</div>  
<div class="row mb-3">
    <div class="col-md-8">
        <div class="form-floating">
            <input type="text" class="form-control" id="ph-emer" name="ph-emer" placeholder="emergency contact phone number during event &hellip;" value="">
            <label for="floatingInput">Emergency Telephone Contact</label>
            <div class="invalid-feedback">Please enter a 11 digit emergency contact phone number (e.g. 07804555666).</div>
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
            <label class="form-check-label" for="consent">&nbsp;&nbsp;&nbsp;I consent</label>
            <div class="invalid-feedback">You must agree to the terms and conditions to submit your entry.</div>
        </div>
    </div>
</div>
EOT;

$buttons_bufr = <<<EOT
<div class="mb-3 d-flex justify-content-end">
    <button type="reset" class="btn btn-secondary me-2">Reset</button>
    <button type="reset" class="btn btn-secondary me-2">Cancel</button>
    <button type="submit" class="btn btn-primary me-2">Enter Boat</button>                   
</div>
EOT;

$form_htm = <<<EOT
<!-- form details -->
<div class="container mt-1">
    <div class="row">
        <div class="offset-md-2 col-md-8">
            <form id="entryForm" action="rm_event_sc.php?eid={$params['eventid']}&pagestate=entry"
                  method="post" role="form" autocomplete="off" novalidate>
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
        
        // Custom validation for category field
        let categoryInput = document.getElementById("category");
        if (categoryInput.value === "pick a fleet") {
            categoryInput.setCustomValidity("error");
        } else {
            categoryInput.setCustomValidity("");
        }
        
        // Custom validation for helm name field
        let helmInput = document.getElementById("helm-name");
        if (helmInput.value === "") {
            helmInput.setCustomValidity("error");
        } else {
            helmInput.setCustomValidity("");
        }
        
        // custom validation for ph-mobile field
        let mobileInput = document.getElementById("ph-mobile");
        let mobileRegex = /^[0-9]{11}$/;
        if (!mobileRegex.test(mobileInput.value)) {mobileInput.setCustomValidity("error");} else { mobileInput.setCustomValidity("");}

        // custom validation for email field
        let emailInput = document.getElementById("email");
        let emailRegex = /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/;
        if (!emailRegex.test(emailInput.value)) {emailInput.setCustomValidity("error");} else { emailInput.setCustomValidity("");}
               
        // Custom validation for crew name field
        let crewInput = document.getElementById("crew-name");
        if (crewInput.value === "") {
            crewInput.setCustomValidity("error");
        } else {
            crewInput.setCustomValidity("");
        }
        
        // custom validation for ph-emer field
        let emerInput = document.getElementById("ph-emer");
        let emerRegex = /^[0-9]{11}$/;
        if (!emerRegex.test(mobileInput.value)) {emerInput.setCustomValidity("error");} else { emerInput.setCustomValidity("");}
            
        // Custom validation for consent field
        let consentInput = document.getElementById("consent");
        if (consentInput.value === "") {
            consentInput.setCustomValidity("error");
        } else {
            consentInput.setCustomValidity("");
        }

        form.classList.add("was-validated");
    });
})();
</script>
EOT;



