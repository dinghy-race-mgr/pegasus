<?php

function under_construction($params=array())
    /*
     FIELDS
        title  :  title for under construction note
        info   :  detail for under construction note

     PARAMS
        none
     */
{
    $html = <<<EOT
        <div class="jumbotron center-block" style="width:60%; margin-top: 60px;">
            <div class="row">
                <div class="col-md-6">
                    <img src="../common/images/web_graphics/uc_hat_t.png" alt="under construction" height="200" width="200">
                </div>
                <div class="col-md-6">
                    <p><b>{title}</b></p>
                    <p>{info}</p>
                </div>
            </div>
            <div>&nbsp;</div>
        </div>
EOT;
    return $html;
}


?>