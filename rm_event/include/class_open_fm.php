<?php

$form_name = "class_open_fm.php";     // important this matches the form-file field in e_form

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

// check if we need to collect fleet category information (e.g. grandmaster) for specified class
$fleets_select_htm = "";
$fleets_validation_js = "";
if (!empty($params['inc_fleets'])) {
    $fleets = explode(",", $params['inc_fleets']);
    $fleets_opt = "";
    foreach ($fleets as $fleet) {
        $fleets_opt .= "<option value='$fleet'></option>";
    }

    $fleets_select_htm .= <<<EOT
    <div class="row mb-3 gx-5">
        <div class="col-md-6">
            <div class="form-floating">      
                <input class="form-control" list="categorylist" id="category" name="category" placeholder="" aria-label="fleet category selection">
                    <datalist id="categorylist">
                    $fleets_opt
                    <option value="unknown"></option>
                    </datalist>
                <label for="category" class="form-label">fleet category</label>
                <div class="invalid-feedback">pick one option from list</div>
            </div>
        </div>
    </div>
EOT;
}

// check if we need to collect crew information (info obtainde from t_class)
$crewname_input_htm = "";
$crewname_validation_js = "";
if ($params['inc_crew']) {
    $crewname_input_htm .= <<<EOT
        <!-- crew section -->
        <div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;Crew &hellip;</div>
        <div class="row mb-3 gx-5">
            <div class="col-md-6">
                <div class="form-floating">
                    <input type="text" class="form-control" id="crew-name" name="crew-name" placeholder="" value="" required>
                    <label for="floatingInput" class="label-style">Name</label>
                    <div class="invalid-feedback">enter crew name (e.g. Dylan Fletcher).</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="crew-age" name="crew-age" placeholder="" value="" >
                    <label for="floatingInput" class="label-style">Age (if under 18)</label>
                </div>
            </div>
        </div -->
EOT;
}


$fields_bufr = <<<EOT
<!-- boat section -->
<div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;Boat &hellip;</div>
<div class="row mb-3 gx-5">
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control-plaintext readonly-style" id="class" name="class" placeholder="" value="{$params['class-name']}" readonly>
            <label for="floatingInput" class="label-style">Class</label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="sailnumber" name="sailnumber" placeholder="" value="" required autofocus>
            <label for="floatingInput" class="label-style">Sail Number<span class="field-reqd"> *</span></label>
            <div class="invalid-feedback">enter your sailnumber</div>
        </div>
    </div>
</div>

<div class="row mb-3 gx-5">
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="boatname" name="boatname" placeholder="" value="" >
            <label for="floatingInput" class="label-style">boat name / sponsor</label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="club" name="club" placeholder="" value="" required>
            <label for="floatingInput" class="label-style">home club</label>
            <div class="invalid-feedback">enter your home club name (e.g. Exe SC)</div>
        </div>
    </div>
</div>

<!-- optional fleet category field -->
$fleets_select_htm

<!-- helm section -->
<div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;Helm &hellip;</div>
<div class="row mb-3 gx-5">
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="helm-name" name="helm-name" placeholder="" value="" required>
            <label for="floatingInput" class="label-style">Name<span class="field-reqd"> *</span></label>
            <div class="invalid-feedback">enter the helm name (e.g. Ben Ainslie).</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-floating">
            <input type="text" class="form-control" id="helm-age" name="helm-age" placeholder="" value="" >
            <label for="floatingInput" class="label-style">Age (if under 18)</label>
        </div>
    </div>
</div>

<div class="row mb-3 gx-5">
    <div class="col-md-6">
        <input type="text" class="form-control" id="ph-mobile" name="ph-mobile" pattern="[0-9]+" minlength="9" maxlength="11" placeholder="" value="" required/>
            <label for="floatingInput" class="label-style">Contact Phone</label>
            <div class="invalid-feedback">enter phone number (e.g. 07804555666)</div>
            <div class="text-primary mx-5">9 to 11 digits no spaces &hellip;</div>
    </div>
    <div class="col-md-6">
        <div class="form-floating">
            <input type="email" class="form-control" id="helm-email" name="helm-email" placeholder="" value="" >
            <label for="floatingInput" class="label-style">Contact Email</label>
            <div class="invalid-feedback">enter valid email address (e.g. ben@gmail.com).</div>
        </div>
    </div>
</div>

<!-- optional crew section -->
$crewname_input_htm

<!-- admin section -->
<div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;Emergency Contact and Consent &hellip;</div>  
<div class="row mb-3 gx-5">
    <div class="col-md-8 gx-5">
        <div class="form-floating">
            <input type="text" class="form-control" id="ph-emer" name="ph-emer" placeholder="" value="" required>
            <label for="floatingInput" class="label-style">Emergency Telephone Contact (during event)<span class="field-reqd"> *</span></label>
            <div class="invalid-feedback">enter name and phone number (e.g. Jenny Smith 07804555666).</div>
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
            <input type="checkbox" class="form-check-input" id="consent" name="consent" required />
            <label class="form-check-label" for="consent">&nbsp;&nbsp;&nbsp;I consent<span class="field-reqd"> *</span></label>
            <div class="invalid-feedback">You must agree to the terms and conditions to submit your entry.</div>
        </div>
    </div>
</div>
EOT;

$buttons_bufr = <<<EOT
<div class="mb-3 d-flex justify-content-end">
    <button type="submit" disabled style="display: none" aria-hidden="true"></button> <!-- hack to stop form submitting on enter-->
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
       
        form.classList.add("was-validated");
    });
})();
</script>
EOT;



