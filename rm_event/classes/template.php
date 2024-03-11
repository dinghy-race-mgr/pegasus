<?php
/**
 * html_class.php - class to hold html for output page
 * 
 * basic methods for constructing an html page using the Bootstrap framework
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 * 
 * 
 * @param string $eventid
 * @param 
 * 
 */
     
class TEMPLATE
{
    /**
     * TEMPLATE constructor.
     * @param $collections  array of paths to template files required
     */
    public function __construct($collections)
    {
        foreach($collections as $collection) {
            include ("$collection");
        }
    }

    /**
     * get_template  renders an html template with a fields array which can either control logic in
     *               the template or substitute values in the template ( within {} delimiters
     *
     * @param string  $template   string with name of template to be used
     * @param array   $fields     1-D array of fields to be inserted into template by name
     * @param array   $data       optional array containing data that might be used in template
     * @return string             string containing rendered html code
     */
    public function get_template($template, $fields, $data=array())
    {
        //echo "<pre>".debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[1]['function']." | ".$template."</pre>";

        $func = "$template";
        $html = $func($data);
        foreach ($fields as $field => $value) {
            if (is_array($value))
            {
                $err = "Bugger: $field is an array not a string  [$template: $field, $value]<br>";
                u_writedbg($err, __FILE__, __FUNCTION__, __LINE__); //debug:);
            }
            $html = str_replace("{" . $field . "}", $value, $html);
        }
        return $html;
    }

}

