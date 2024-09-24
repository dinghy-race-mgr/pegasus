<?php

$form_name = "junior_consent_fm.php";     // important this matches the form-file field in e_form

$instructions_htm = "";
if (!empty($params['instructions']))   // content defined in table e-form
{
    $instructions_htm = <<<EOT
<div class="mt-3">
    <div class="alert alert-warning alert-dismissible fade show fs-6" role="alert">
        {$params['instructions']}.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
EOT;
}

$fields_bufr = <<<EOT
<div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;Parent / Guardian Information &hellip;</div>
<div class="row mb-3">
    <div class="col-md-6">
        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="p-name" name="p-name" placeholder="parent name &hellip;" value="" required autofocus />
            <label for="floatingInput" class="label-style">Parent Name<span class="field-reqd"> *</span></label>
            <div class="invalid-feedback">enter name of parent/guardian</div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="p-mobile" name="p-mobile" placeholder="parent mobile phone number &hellip;" value="" required />
            <label for="floatingInput" class="label-style">Contact Phone Number<span class="field-reqd"> *</span></label>
            <div class="invalid-feedback">please provide contact phone during event (e.g. 07804555666).</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-floating">
            <input type="text" class="form-control" id="p-email" name="p-email" placeholder="parent email address &hellip;" value="" />
            <label for="floatingInput" class="label-style">Email</label>
            <div class="invalid-feedback">enter valid email address (e.g. ben@gmail.com).</div>
        </div>
    </div>
</div>

<div class="form-floating mb-3">
    <input type="text" class="form-control" id="p-address" name="p-address" placeholder="parent address &hellip;" value="" required />
    <label for="floatingInput" class="label-style">Parent Address<span class="field-reqd"> *</span></label>
    <div class="invalid-feedback">enter parent home address</div>
</div>

<div class="form-floating mb-3">
    <input type="text" class="form-control" id="p-altcontact" name="p-altcontact" placeholder="parent name &hellip;" value="" />
    <label for="floatingInput" class="label-style">Alternate Contact Name / Phone</label>
    <div class="invalid-feedback">enter alternate contact name and phone no. </div>
</div>

<div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;Child Information &hellip;</div>

<div class="form-floating mb-3">
    <input type="text" class="form-control" id="c-name" name="c-name" placeholder="child name &hellip; " value="" required />
    <label for="floatingInput" class="label-style">Child Name<span class="field-reqd"> *</span></label>
    <div class="invalid-feedback">enter name of child</div>
</div>

<div class="form-floating mb-3">
    <input type="text" class="form-control" id="c-dob" name="c-dob" placeholder="child birth date &hellip; " value="" required />
    <label for="floatingInput" class="label-style">Child Birth Date dd/mm/yyyy<span class="field-reqd"> *</span></label>
    <div class="invalid-feedback">enter date of birth (e.g. 12/06/2010)</div>
</div>

<div class="form-floating mb-3">
    <br><p class="label-style">&nbsp;&nbsp;Any medical conditions the organisers should be aware of?</p>
    <textarea class="form-control" id="c-medical" name="c-medical" value="" style="height: 80px"></textarea>  
</div>

<div class="form-floating mb-3">
    <br><p class="label-style">&nbsp;&nbsp;Any dietary requirements or allergies the organisers should be aware of?</p>
    <textarea class="form-control" id="c-dietary" name="c-dietary" value="" style="height: 80px"></textarea>  
</div>

<div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;Declarations &hellip;</div>

<div class="row mb-3">     
    <div class="col-md-8">
        I give permission to the event organisers to administer any relevant treatment/or 
        medication to the above named child if necessary. In an emergency situation I authorise the 
        organisers to take my child to hospital and I give my permission for any treatment required to be carried
         out in accordance with the hospital diagnosis. I understand that I shall be notified, as soon as possible, 
         of the hospital visit and any treatment given by the hospital.
    </div>
    <div class="col-md-4">                                 
        <div class="mb-3 ps-5 form-check">
            <input type="checkbox" class="form-check-input " id="c-treatment" name="c-treatment" required />
            <label class="form-check-label" for="c-treatment">&nbsp;&nbsp;&nbsp;YES<span class="field-reqd"> *</span></label>
            <div class="invalid-feedback">consent is required for SYC to accept the entry.</div>
        </div>
    </div>   
</div>

<div class="row mb-3">     
    <div class="col-md-8">
        I consent to photos and videos of children named on this entry form being used for publicity and
         reports for this event and for Starcross YC.
    </div>
    <div class="col-md-4">                  
        <div class="mb-3 form-check">
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="c-media" id="c-media-1" value="YES" checked>
              <label class="form-check-label" for="c-media-1">YES</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="c-media" id="c-media-2" value="NO">
              <label class="form-check-label" for="c-media-2">NO</label>
            </div>
        </div>
    </div>   
</div>

<div class="row mb-3">  
    <div class="col-md-8">
        I declare the child  on this entry from is confident in the water and I give my consent for them to 
        participate in this event. I will ensure that their boat is seaworthy and they will wear a buoyancy 
        aid at all times when afloat.  I will ensure contact details of an adult in <i>loco parentis</i> are 
        given to the organisers if I am not present myself.
    </div>
    <div class="col-md-4">                  
        <div class="mb-3 ps-5 form-check">
            <input type="checkbox" class="form-check-input" id="c-confident" name="c-confident" required />
            <label class="form-check-label" for="c-confident">&nbsp;&nbsp;&nbsp;YES<span class="field-reqd"> *</span></label>
            <div class="invalid-feedback">consent is required for SYC to accept the entry.</div>
        </div>
    </div>
</div>
EOT;

$buttons_bufr = <<<EOT
<div class="mb-3 d-flex justify-content-end">
    <button type="cancel" class="btn btn-secondary me-2">Cancel</button>
    <button type="submit" class="btn btn-primary me-2">Submit Form</button>                   
</div>
EOT;

$form_htm = <<<EOT
<!-- form details -->
<div class="container mt-1">
    <div class="row">
        <div class="">
            <form id="juniorconsentForm" action="rm_event_sc.php?eid={$params['eventid']}&entryid={$params['entryid']}&pagestate=juniorconsent&formname=$form_name&mode={$params['form-mode']}"
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
        
        // custom validation for ph-mobile field (at least 9 digits + spaces and dashes)
        let pmobileInput = document.getElementById("p-mobile");
        let pmobileRegex = /^(?:\d[- ]*){9,}$/;
        if (!pmobileRegex.test(pmobileInput.value)) {pmobileInput.setCustomValidity("error");} else { pmobileInput.setCustomValidity("");}

        // custom validation for parent email field
        let pemailInput = document.getElementById("p-email");
        let pemailRegex = /^(?:[\w\.\-]+@([\w\-]+\.)+[a-zA-Z]+)?$/;   
        if (!pemailRegex.test(pemailInput.value)) {pemailInput.setCustomValidity("error");} else { pemailInput.setCustomValidity("");}
                     
        // Custom validation for parent address field
        let paddressInput = document.getElementById("p-address");
        if (paddressInput.value === "") {paddressInput.setCustomValidity("error");} else {paddressInput.setCustomValidity("");}
        
        // Custom validation for alternate contact field - none defined
 
        // Custom validation for child name field
        let cnameInput = document.getElementById("c-name");
        if (cnameInput.value === "") {cnameInput.setCustomValidity("error");} else {cnameInput.setCustomValidity("");}
        
        // Custom validation for child dob field
        let cdobInput = document.getElementById("c-dob");
        let cdobRegex = /^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/;
        if (!cdobRegex.test(cdobInput.value)) {cdobInput.setCustomValidity("error");} else {cdobInput.setCustomValidity("");}
        
        // Custom validation for confident declaration field
        let treatmentInput = document.getElementById("c-treatment");
        if (treatmentInput.value === "") { treatmentInput.setCustomValidity("error");} else { treatmentInput.setCustomValidity(""); }
        
        // Custom validation for confident declaration field
        let confidentInput = document.getElementById("c-confident");
        if (confidentInput.value === "") { confidentInput.setCustomValidity("error");} else { confidentInput.setCustomValidity(""); }

        form.classList.add("was-validated");
    });
})();
</script>
EOT;



