<?php
$form_name = "junior_class_open_fm.php";  // important this matches the form-file field in e_form

$instructions_htm = "";
if (!empty($params['instructions']))      // content defined in table e_form
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

// check if we need to collect fleet category information (e.g. grandmaster) for specified class
$fleets_select_htm = "";
$fleets_validation_js = "";
if (!empty($params['inc_fleets'])) {
    $fleets = explode(",", $params['inc_fleets']);
    $fleets_opt = "";
    foreach ($fleets as $fleet) {
        $uc_fleet = ucwords($fleet);
        $fleets_opt .= "<option value='$fleet'>$uc_fleet</span></option>";
    }

    $fleets_select_htm .= <<<EOT
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="form-floating mb-3">      
                    <select class="form-select" id="category" name="category" aria-label="fleet category selection">
                        <option>- pick one -</option>
                        $fleets_opt
                        <option value="unknown">not sure &hellip;</option>
                    </select>
                    <label for="floatingSelect" ><span class="label-style">fleet category</span></label>
                    <div class="invalid-feedback">pick a fleet from the list</div>
                </div>
            </div>
        </div>
EOT;

    $fleets_validation_js.= <<<EOT
        let categoryInput = document.getElementById("category");
        if (categoryInput.value === "- pick one -") {categoryInput.setCustomValidity("error");} else {categoryInput.setCustomValidity("");}
EOT;
}

// check if we need to collect crew information (info obtainde from t_class)
$crewname_input_htm = "";
$crewname_validation_js = "";
if ($params['inc_crew']) {
    $crewname_input_htm .= <<<EOT
        <!-- crew section -->
        <div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;Crew &hellip;</div>
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="form-floating">
                    <input type="text" class="form-control" id="crew-name" name="crew-name" placeholder="crew name &hellip;" value="" required>
                    <label for="floatingInput" class="label-style">Name</label>
                    <div class="invalid-feedback">enter the crew name (e.g. Ben Ainslie).</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating">
                    <input type="text" class="form-control" id="crew-age" name="crew-age" placeholder="age if under 18 &hellip;" value="" required >
                    <label for="floatingInput" class="label-style">Age<span class="field-reqd"> *</span></label>
                </div>
            </div>
        </div -->
EOT;
    // configure validation
    $crewname_validation_js .= <<<EOT
        let crewInput = document.getElementById("crew-name");
        if (crewInput.value === "") {crewInput.setCustomValidity("error");} else {crewInput.setCustomValidity("");}
EOT;
}

$fields_bufr = <<<EOT
<!-- boat section -->
<div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;Boat &hellip;</div>
<div class="row mb-3">
    <div class="col-md-3">
        <div class="form-floating">
            <input type="text" class="form-control-plaintext readonly-style" id="class" name="class" placeholder="boat class &hellip;" value="{$params['class-name']}" readonly>
            <label for="floatingInput" class="label-style">Class</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-floating">
            <input type="text" class="form-control" id="sailnumber" name="sailnumber" placeholder="sail number &hellip;" value="" required autofocus>
            <label for="floatingInput" class="label-style">Sail Number<span class="field-reqd"> *</span></label>
            <div class="invalid-feedback">enter your sailnumber</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-floating">
            <input type="text" class="form-control" id="bownumber" name="bownumber" placeholder="bow / short sail number &hellip;" value="" >
            <label for="floatingInput" class="label-style">Topper Championship Sail No.</label>
            <div class="invalid-feedback">enter your bow or short sail number</div>
        </div>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="boatname" name="boatname" placeholder="name/sponsor &hellip;" value="" >
            <label for="floatingInput" class="label-style">boat name / sponsor</label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="club" name="club" placeholder="home club &hellip;" value="" required>
            <label for="floatingInput" class="label-style">home club<span class="field-reqd"> *</span></label>
            <div class="invalid-feedback">enter home club name (e.g. Exe SC)</div>
        </div>
    </div>
</div>

<!-- optional fleet category field -->
$fleets_select_htm

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
            <input type="text" class="form-control" id="helm-age" name="helm-age" placeholder="age &hellip;" value="" required>
            <label for="floatingInput" class="label-style">Age<span class="field-reqd"> *</span></label>
            <div class="invalid-feedback">enter the helm age (e.g. 16).</div>
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
            <input type="text" class="form-control" id="ph-mobile" name="ph-mobile" placeholder="mobile phone number &hellip;" value="" required>
            <label for="floatingInput" class="label-style">Contact Phone<span class="field-reqd"> *</span></label>
            <div class="invalid-feedback">11 digit phone number (e.g. 07804555666).</div>
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
$crewname_input_htm

<!-- admin section -->
<div class="row mb-3">  
    <div class="col-md-8">
        I undertake that the entered boat has third party insurance of a minimum of &pound;3,000,000  
        and I agree to be bound by the rules of the event as set out in the Notice of Race and Sailing Instructions
    </div>
    <div class="col-md-4">                  
        <div class="mb-3 ps-5 form-check">
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
          
        // Custom validation for sail number field
        let sailnoInput = document.getElementById("sailnumber");
        if (sailnoInput.value === "") {sailnoInput.setCustomValidity("error");} else {sailnoInput.setCustomValidity("");}
     
        // Custom validation for club field
        let clubInput = document.getElementById("club");
        if (clubInput.value === "") {clubInput.setCustomValidity("error");} else {clubInput.setCustomValidity("");}
        
        // Custom validation for (optional) category field
        let categoryInput = document.getElementById("category");
        if (categoryInput.value === "- pick one -") {categoryInput.setCustomValidity("error");} else {categoryInput.setCustomValidity("");}
             
        // Custom validation for helm name field
        let helmInput = document.getElementById("helm-name");
        if (helmInput.value === "") {helmInput.setCustomValidity("error");} else {helmInput.setCustomValidity("");}
             
        // Custom validation for helm age field
        let ageInput = document.getElementById("helm-age");
        let ageRegex = /^[1-9][0-9]?$/;
        if (!ageRegex.test(ageInput.value)) {ageInput.setCustomValidity("error");} else {ageInput.setCustomValidity("");}
        
        // custom validation for ph-mobile field
        let mobileInput = document.getElementById("ph-mobile");
        let mobileRegex = /^(?:\d[- ]*){9,}$/;
        if (!mobileRegex.test(mobileInput.value)) {mobileInput.setCustomValidity("error");} else { mobileInput.setCustomValidity("");}

        // custom validation for email field
        //let emailInput = document.getElementById("helm-email");
        //let emailRegex = /^(?:[\w\.\-]+@([\w\-]+\.)+[a-zA-Z]+)?$/;
        //if (!emailRegex.test(emailInput.value)) {emailInput.setCustomValidity("error");} else { emailInput.setCustomValidity("");}
               
        // Custom validation for (optional) crew name field
        let crewInput = document.getElementById("crew-name");
        if (crewInput.value === "") {crewInput.setCustomValidity("error");} else {crewInput.setCustomValidity("");}
            
        // Custom validation for consent field
        let consentInput = document.getElementById("consent");
        if (consentInput.value === "") {consentInput.setCustomValidity("error");} else {consentInput.setCustomValidity("");}

        form.classList.add("was-validated");
    });
})();
</script>
EOT;



