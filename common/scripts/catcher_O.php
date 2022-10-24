<?php

/**
 * @author mark elkington
 * @copyright 2014
 */

// catcher.php - dummy script to receive posted variables

echo "Catcher Page:<br>----------<br>";

foreach ($_REQUEST as $k=>$v)
{
    echo "$k : $v <br>";
}


?>