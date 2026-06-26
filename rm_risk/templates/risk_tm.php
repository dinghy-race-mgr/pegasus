<?php

function risk_form($params = array())     // fixme this is from rm_event
{
//    echo "<pre>".print_r($params,true)."</pre>";
//    exit();

    $datetime = date("Y-m-d H:i");
    $date = date("d-m-Y");

// instruction block
    $instructions_bufr = <<<EOT
        <div class="alert alert-info fade show fs-6 mt-2" role="alert" style="border: steelblue 1px solid; width: 80%;">
            <b>Instructions</b><br>{instructions}
        </div>
EOT;

    $links_bufr = "";
    if (count($params['links']) > 0)
    {
        $links_htm = "<span class='text-primary'><b>Useful Info &hellip;</b></span>";
        foreach ($params['links'] as $link)
        {
            $links_htm.= <<<EOT
            <a type="button" class="btn btn-md btn-outline-warning mx-3 my-2" href="{$link['link']}" target="_blank">{$link['label']}&nbsp;&nbsp;</a>
EOT;
        }

        $links_bufr = <<<EOT

        <div class="alert alert-light fade show fs-6 mt-2" role="alert" style="border: steelblue 1px solid">
            <div class="row">
                <div class="col">$links_htm</div>
            </div>
        </div>

EOT;
    }


    $admin_fields_bufr = <<<EOT
    <div class="alert alert-light fade show mt-2 mb-2" role="alert" style="border: steelblue 1px solid"><p class="text-primary text-center fs-4"><b>Risk Assessment Form</b></p> 
        <div class="row g-xl-5">
            <div class="col">            
                    <input type="text" class="form-control" id="person" name="person" placeholder="your name here ..." value="" required />        
                    <div class="invalid-feedback">please enter your name</div>
            </div>
            <div class="col">          
                    <input type="text" class="form-control" id="date" name="date" placeholder="event date ..." value="$date" required />
                    <div class="invalid-feedback">please enter date of race(s) DD/MM/YYYY </div>
            </div> 
            <div class="col">            
                    <input type="text" class="form-control" id="event" name="event" placeholder="event name e.g Spring Series (2 races)" value="{event}"  required />
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
        <div class="row mb-1">
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
<div class="container">
    <form id="riskForm" action="{script}" method="post" role="form" autocomplete="off" class="needs-validation" novalidate>
        $instructions_bufr
        $links_bufr
        $admin_fields_bufr
        <hr>
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

    // get list of contact officers
    $contact_opts = "";
    foreach ($contacts as $contact) { $contact_opts.= "<li><a class='dropdown-item' href='#'>$contact</a></li>"; }

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
        $response = <<<EOT
        <div class="text-danger-emphasis" > 
            <span class='fs-2' style="color:#FFE6E6;">Status <b>RED</b> &hellip; <i class="bi-emoji-grimace-fill" style="font-size: 2rem; color: #FFE6E6;"></i></span>
            <br><span class='fs-4 '>{$form['red_response']}</span>
            <br><!-- b>{scores}</b -->
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
            <span class='fs-3' style="color:#FFE5B4;">Status <b>AMBER</b> &hellip; <i class="bi-emoji-neutral-fill" style="font-size: 2rem; color: #FFE5B4;"></i></span>
            <br><span class='fs-4 '>{$form['amber_response']}</span>
            <br><!-- b>{scores}</b -->
        </div>
        <div class="dropdown pt-3 d-md-flex justify-content-md-end">
            <button class="btn btn-secondary dropdown-toggle px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">Need Advice? people to ask &hellip;</button>
            <ul class="dropdown-menu">
                $contact_opts
            </ul>
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
            <br><!-- b>{scores}</b -->
        </div>
EOT;
        $bg_style = "text-bg-success";
        $mitigation_txt = "";  // not required
        $abandoning_txt = "";  // not required
        $results_txt    = "";     // not required
    }

    if (!empty($mitigation_txt))
    {
        $mitigation_txt = <<<EOT
            <div>
                <p class="fs-3 text-primary">MITIGATION - actions to consider</p>
                <p class="ms-5 fs-5">$mitigation_txt</p>
            </div>
            <hr>
EOT;
    }

    if (!empty($abandoning_txt))
    {
        $abandoning_txt = <<<EOT
            <div>
                <p class="fs-3 text-primary">ABANDONING - how to do it</p>
                <p class="fs-6">$abandoning_txt</p>
            </div>
            <hr>
EOT;
    }

    if (!empty($results_txt))
    {
        $results_txt = <<<EOT
            <div>
                <p class="fs-3 text-primary">RESULTS </p>
                <p class="fs-6">$results_txt</p>
            </div>
            <hr>
EOT;
    }

    $htm = <<<EOT
    <main class="" >
        <div class="container nav-margin my-4 ">
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
            <br>
            $abandoning_txt
            <br>
            $results_txt
            <br>                               
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