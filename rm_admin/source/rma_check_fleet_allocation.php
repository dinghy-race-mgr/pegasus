<?php

echo <<<EOT
<h2>Under Construction</h2>
<p>This page will allow admin to check where each class has been allocated within a race format</p>
<p>Two mutually exclusive script parameters will be supported (format will take precendence if provided):</p>
<ul>
<li> class - will present the fleet and start allocation for a specified class for EACH active race format</li>
<li> format - will present the fleet and start allocation for EACH class in the specified race format - classes without an allocation will be reported first</li>
</ul>
<p> Will be called from the class page and from the race format page</p>

<p>Probably best to implement this as shown here:  https://asprunner.com/phprunner/docs/inserting_button.htm#grid  - see example 4</p>
<p> still need to sort out passing id of format to script</p>

EOT;


?>