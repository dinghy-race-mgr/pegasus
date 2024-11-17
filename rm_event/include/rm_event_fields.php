<?php
/*
 * This file holds code snippets for forms
 *
 */

// could treat fields as blocks - e.g. Helm Identification Block, Helm Contact block - and make sure they are responsive to screen size

function form_section_header($title)
{
    $htm = <<<EOT
<div class="form-section w-100 p-1 mb-3" >&nbsp;&nbsp;$title &hellip;</div>
EOT;
    return $htm;
}