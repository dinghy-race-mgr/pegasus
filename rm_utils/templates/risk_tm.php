<?php

function risk_form($params = array())     // fixme this is from rm_event
{
//    echo "<pre>".print_r($params,true)."</pre>";
//    exit();

    $datetime = date("Y-m-d H:i");
    $date = date("d-m-Y");

    $instructions_bufr = <<<EOT
        <div class="alert alert-light fade show fs-5 mt-5 text-bg-primary" role="alert" style="border: steelblue 1px solid">
        <b>Instructions</b><br>{instructions}
        </div>
    EOT;

    $admin_fields_bufr = <<<EOT
    <div class="alert alert-light fade show mt-5 mb-2" role="alert" style="border: steelblue 1px solid"><span class="text-primary fs-4">Administration Details</span> 
    <div class="row g-xl-5">
        <div class="col">            
                <input type="text" class="form-control" id="person" name="person" placeholder="person completing form ..." value="" required />        
                <div class="invalid-feedback">please enter your name</div>
        </div>
        <div class="col">          
                <input type="text" class="form-control" id="date" name="date" value="$date" required />
                <div class="invalid-feedback">please enter date of race(s) DD/MM/YYYY </div>
        </div> 
        <div class="col">            
                <input type="text" class="form-control" id="event" name="event" placeholder="e.g Spring Series (2 races)" value="{event}"  required />
                <div class="invalid-feedback">please list the races covered by the assessment </div> 
        </div>
    </div>
    <input type="hidden" name="datetime" placeholder="" value="$datetime">
EOT;

    $question_fields_bufr = "";
    foreach ($params['questions'] as $k=>$question)
    {
        $opt_bufr = "<option value=''>&hellip; pick one</option>";
        foreach($question['options'] as $option)
        {
            if ($option['active'])
            {
                $opt_bufr.= "<option value='{$option['score']}'>{$option['label']}</option>";
            }
        }

        $question_fields_bufr.= <<<EOT
        <div class="row mb-3">
            <div class="input-group">
                <div class="col-1">&nbsp;</div>
                <div class="col-6">
                    <label class="label-style fs-5">{$question['label']} </label>   
                    <div class="text-primary mx-5 fs-6">{$question['info']}</div>
                </div>
                <div class="col-4">                        
                        <select class="form-select" name="q$k" required>
                            $opt_bufr
                        </select>                                
                        <div class="invalid-feedback">Please provide an assessment for this item</div>                          
                </div>                  
            </div>
        </div>
EOT;
    }



    $buttons_bufr = <<<EOT
<div class="mb-3 d-flex justify-content-end">
    <!-- button type="submit" disabled style="display: none" aria-hidden="true"></button --> <!-- hack to stop form submitting on enter-->
    <button type="button" class="btn btn-warning me-5" onclick="history.back()">Cancel</button>
    <button type="submit" class="btn btn-primary me-5">Submit Risk Assessment</button>                   
</div>
EOT;

    $validation_bufr = <<<EOT
<script>

(function ()  {
  'use strict'

  // Fetch all the forms we want to apply custom Bootstrap validation styles to
  const forms = document.querySelectorAll('.needs-validation')

  // Loop over them and prevent submission
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault()
        event.stopPropagation()
      }

      form.classList.add('was-validated')
    }, false)
  })
})()
</script>
EOT;

    $bufr = <<<EOT
<div class="container mt-1">
    <form id="riskForm" action="{script}" method="post" role="form" autocomplete="off" class="needs-validation" novalidate>
        $instructions_bufr
        $admin_fields_bufr
        <hr>
        <span class="text-primary fs-4">Risk Assessment</span>
        $question_fields_bufr  
        <hr>      
        $buttons_bufr
    </form>
    $validation_bufr        
</div>

EOT;

    return $bufr;
}

function risk_response($params = array())
{

    //echo "<pre>".print_r($params,true)."</pre>";

    $risk = $params['risk'];
    $mitigations = $params['data']['mitigations'];
    $contacts = explode("|", $params['data']['form']['contacts']);
    $form = $params['data']['form'];

    // initialise additional information in response
    $abandoning_txt = $form['abandoning'];
    $results_txt = $form['results'];

    // set mitigations text based on most series risks
    $mitigation_txt = "";
    foreach ($mitigations as $mitigation)
    {
        if (strpos($mitigation['tags'], $risk['risk_1']) !== false)
        {
            $mitigation_txt.= " - ".$mitigation['action']."</br>";
            continue;
        }
    }


    if ($risk['state'] == "red")                      // RED response
    {
        // get list of contact officers
        $contact_opts = "";
        foreach ($contacts as $contact) { $contact_opts.= "<li><a class='dropdown-item' href='#'>$contact</a></li>"; }

        $response = <<<EOT
        <div class="text-danger-emphasis" > 
            <span class='fs-2' style="color:#FFE6E6;">Status <b>RED</b> &hellip; <i class="bi-emoji-grimace-fill" style="font-size: 2rem; color: #FFE6E6;"></i></span>
            <br><span class='fs-4 '>{$form['red_response']}</span>
        </div>
        <div class="dropdown pt-3 d-md-flex justify-content-md-end">
            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">People to contact</button>
            <ul class="dropdown-menu">
                $contact_opts
            </ul>
        </div>

EOT;
        $bg_style = "text-bg-danger";


    }
    elseif ($risk['state'] == "amber")               // AMBER response
    {
        $response = <<<EOT
        <div class="text-warning-emphasis" > 
            <span class='fs-3' style="color:#FFE5B4;">Status <b>AMBER</b> &hellip; <i class="bi-emoji-frown-fill" style="font-size: 2rem; color: #FFE5B4;"></i></span>
            <br><span class='fs-4 '>{$form['amber_response']}</span>
        </div>
EOT;
        $bg_style = "text-bg-warning";
    }

    elseif ($risk['state'] == "green")               // GREEN response
    {
        $response = <<<EOT
        <div class="text-success-emphasis" > 
            <span class='fs-2' style="color:aquamarine">Status <b>GREEN</b> &hellip; <i class="bi-emoji-smile-fill" style="font-size: 2rem; color: aquamarine;"></i></span>
            <br><span class='fs-4 '>{$form['green_response']}</span>
        </div>
EOT;
        $bg_style = "text-bg-success";
        $mitigation_txt = "";  // not required
        $abandoning_txt = "";
        $results_txt = "";
    }

    if (!empty($mitigation_txt))
    {
        $mitigation_txt = <<<EOT
            <div>
                <p class="fs-3 text-primary">MITIGATION - actions to consider</p>
                <p class="ps-5">$mitigation_txt</p>
            </div>
            <hr>
EOT;
    }

    if (!empty($abandoning_txt))
    {
        $abandoning_txt = <<<EOT
            <div>
                <p class="fs-3 text-primary">ABANDONING - how to do it</p>
                <p class="ps-5">$abandoning_txt</p>
            </div>
            <hr>
EOT;
    }

    if (!empty($results_txt))
    {
        $results_txt = <<<EOT
            <div>
                <p class="fs-3 text-primary">RESULTS </p>
                <p class="ps-5">$results_txt</p>
            </div>
            <hr>
EOT;
    }

    $htm = <<<EOT
    <main class="" >
        <div class="container nav-margin my-5 ">
            <span class='fs-4 text-primary '>Thank you for completing the risk assessment for today's race &hellip;</span>
            
            <div class="row align-items-md-stretch mt-3">
                <div class="col-md-9">
                    <div class="h-100 p-5 $bg_style rounded-3">
                        $response
                    </div>
                </div>
            </div>
            <br><br>    
            $mitigation_txt
            <br><br>
            $abandoning_txt
            <br><br>
            $results_txt
            <br><br>                                
        </div>    
    </main>
EOT;

    return $htm;

}
function error_report($params = array())
{
    $err_text = array (
        "2" => "The script page state is not recognised",
        "3" => "The system has been unable to produce an assessment for unknown reasons"
    );

    $problem = $err_text[$params['err']];

    $htm = <<<EOT
    <main class="" >
        <div class="container nav-margin">
            <p class='display-5 text-info mb-lg-5'><b>Club Racing - Risk Assessment Form</b></p>           
            <div class="p-5 mb-4 text-bg-secondary border rounded-3">
                <div class="container-fluid py-5">
                    <h1 class="display-6 fw-bold">Oops ... we have a problem</h1>
                    <p class="col-md-10 fs-5">$problem</p>        
                    <p class="col-md-10 fs-5">Please report this problem to a club officer.</p>
                    
                    <p><b>If you have ANY concerns about the likely conditions for the race - please contact a club officer</b></p>
                </div>
            </div>           
        </div>    
    </main>
EOT;

    return $htm;
}