<?php

class WebCollectResource
{
  /**
   *
   * @author Oliver Schonrock <oliver@realtsp.com>
   * @params 
   * @return 
   */
  public function __construct(StdClass $object)
  {
    foreach (get_object_vars($object) as $var => $value)
    {
      if ($value instanceof StdClass)
      {
        $class = 'WebCollect' . camel_case($var, true);
        $this->$var = new $class($value);
      }
      elseif (is_array($value))
      {
        $class = 'WebCollect' . camel_case(singularise($var), true);
        $child_resources = array();
        foreach ($value as $child_object)
        {
          $child_resources[] = new $class($child_object);
        }
        $this->$var = $child_resources;
      }
      else
      {
        $this->$var = $value;
      }
    }
  }
}

