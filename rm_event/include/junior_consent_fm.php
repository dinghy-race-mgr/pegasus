<?php
$instructions_htm = "";
if (!empty($params['instructions']))
{
    $instructions_htm = <<<EOT
<div class="offset-md-2 col-md-8 mt-3">
    <div class="alert alert-warning alert-dismissible fade show fs-6" role="alert">
        {$params['instructions']}.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
EOT;
}

$fields_bufr = <<<EOT
<div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;Parent / Guardian Information &hellip;</div>
<div class="form-floating mb-3">
    <input type="text" class="form-control" id="p-name" name="p-name" placeholder="parent name &hellip;" value="" required autofocus />
    <label for="floatingInput" class="label-style">parent name</label>
    <div class="invalid-feedback">enter name of parent/guardian</div>
</div>
<div class="row mb-3">
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="p-mobile" name="p-mobile" placeholder="parent mobile phone number &hellip;" value="" />
            <label for="floatingInput" class="label-style">Mobile</label>
            <div class="invalid-feedback">11 digit phone number (e.g. 07804555666).</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="p-email" name="p-email" placeholder="parent email address &hellip;" value="" />
            <label for="floatingInput" class="label-style">Email</label>
            <div class="invalid-feedback">enter parent email address (e.g. ben@gmail.com).</div>
        </div>
    </div>
</div>
<div class="form-floating mb-3">
    <input type="text" class="form-control" id="p-address" name="p-address" placeholder="parent address &hellip;" value="" required />
    <label for="floatingInput" class="label-style">parent address</label>
    <div class="invalid-feedback">enter parent home address</div>
</div>
<div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;Child Information &hellip;</div>
<div class="form-floating mb-3">
    <input type="text" class="form-control" id="c-name" name="c-name" placeholder="child name &hellip; " value="" required />
    <label for="floatingInput" class="label-style">Child Name</label>
    <div class="invalid-feedback">enter name of child</div>
</div>
<div class="form-floating mb-3">
    <input type="text" class="form-control" id="c-dob" name="c-dob" placeholder="child birth date &hellip; " value="" required />
    <label for="floatingInput" class="label-style">Child Birth Date dd/mm/yyyy</label>
    <div class="invalid-feedback">enter date of birth</div>
</div>
<div class="form-floating mb-3">
    <br><p class="label-style">&nbsp;&nbsp;Please tell us about any medical conditions or allergies the organisers should be aware of &hellip;</p>
    <textarea class="form-control" id="c-details" name="c-details" value="" style="height: 150px"></textarea>  
</div>
<div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;Consent Confirmation &hellip;</div>
<div class="row mb-3">  
    <div class="col-md-8">
        I declare the child  on this entry from is confident in the water and I give my consent for them to 
        participate in this event. I will ensure that their boat is seaworthy and they will wear a buoyancy 
        aid at all times when afloat.  I give my consent for photos and videos containing images of this 
        child to be used in reports and publicity for this event.
    </div>
    <div class="col-md-4">                  
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="consent" name="consent" required />
            <label class="form-check-label" for="consent">&nbsp;&nbsp;&nbsp;I consent</label>
            <div class="invalid-feedback">You must provide consent for the child to participate in the event.</div>
        </div>
    </div>
</div>
EOT;

$buttons_bufr = <<<EOT
<div class="mb-3 d-flex justify-content-end">
    <button type="cancel" class="btn btn-secondary me-2">Cancel</button>
    <button type="submit" class="btn btn-primary me-2">Submit Consent</button>                   
</div>
EOT;

$form_htm = <<<EOT
<!-- form details -->
<div class="container mt-1">
    <div class="row">
        <div class="offset-md-2 col-md-8">
            <form id="juniorconsentForm" action="rm_event_sc.php?eid={$params['eventid']}&entryid={$params['entryid']}&pagestate=juniorconsent&mode={$params['form-mode']}"
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
    var form = document.getElementById("juniorconsentForm");

    form.addEventListener("submit", function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        // Custom validation for parent name field
        let pnameInput = document.getElementById("p-name");
        if (pnameInput.value === "") {pnameInput.setCustomValidity("error");} else {pnameInput.setCustomValidity("");}
        
        // custom validation for ph-mobile field
        let pmobileInput = document.getElementById("p-mobile");
        let pmobileRegex = /^[0-9 ]+$/;
        if (!pmobileRegex.test(pmobileInput.value)) {pmobileInput.setCustomValidity("error");} else { pmobileInput.setCustomValidity("");}

        // custom validation for parent email field
        let pemailInput = document.getElementById("p-email");
        let pemailRegex = /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/;
        if (!pemailRegex.test(pemailInput.value)) {pemailInput.setCustomValidity("error");} else { pemailInput.setCustomValidity("");}
                     
        // Custom validation for parent address field
        let paddressInput = document.getElementById("p-address");
        if (paddressInput.value === "") {paddressInput.setCustomValidity("error");} else {paddressInput.setCustomValidity("");}
 
        // Custom validation for child name field
        let cnameInput = document.getElementById("c-name");
        if (cnameInput.value === "") {cnameInput.setCustomValidity("error");} else {cnameInput.setCustomValidity("");}
        
        // Custom validation for child dob field
        let cdobInput = document.getElementById("c-dob");
        let cdobRegex = /^(((0[1-9]|[12]\d|3[01])\/(0[13578]|1[02])\/((19|[2-9]\d)\d{2}))|((0[1-9]|[12]\d|30)\/(0[13456789]|1[012])\/((19|[2-9]\d)\d{2}))|((0[1-9]|1\d|2[0-8])\/02\/((19|[2-9]\d)\d{2}))|(29\/02\/((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|(([1][26]|[2468][048]|[3579][26])00))))$/;
        if (!cdobRegex.test(cdobInput.value)) {cdobInput.setCustomValidity("error");} else {cdobInput.setCustomValidity("");}
        
        // Custom validation for consent field
        let consentInput = document.getElementById("consent");
        if (consentInput.value === "") { consentInput.setCustomValidity("error");} else { consentInput.setCustomValidity(""); }

        form.classList.add("was-validated");
    });
})();
</script>
EOT;



