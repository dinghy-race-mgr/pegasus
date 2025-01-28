<?php

/**
 *
 */
function singularise($str)
{
  return 
    // words that end in s in the singular will have an extra e in the plural, remove that as well
    preg_replace('/se$/', 's', 
                 // remove that standard 's' in the pural
                 preg_replace('/s$/', '', $str));
}

/**
 *
 */
function camel_case($string, $capitalizeFirst = false, $separator = '')
{
  if (strlen($string) > 0)
  {
    $string = str_replace(' ', $separator, ucwords(str_replace('_', ' ', $string)));
    if (!$capitalizeFirst)
    {
      $string[0] = strtolower($string[0]);
    }
  }
  return $string;
}

/**
 *
 */
function getCallbackDescription($callback)
{
  if (is_array($callback))
  {
    if (is_string($callback[0]))
    {
      $callback_description = join('::', $callback);
    }
    else
    {
      // deal with callback that uses object
      $callback_description = get_class($callback[0]) . '::' . $callback[1];
    }
  }
  else
  {
    $callback_description = $callback;
  }
  return $callback_description;
}

/**
 *
 */
function getErrorDescription($error_number)
{
  switch ($error_number) {
  case E_ERROR:
    $error_description = 'Fatal';
    break;
  case E_WARNING:
    $error_description = 'Warning';
    break;
  case E_NOTICE:
    $error_description = 'Notice';
    break;
  case E_STRICT:
    $error_description = 'Strict';
    break;
  case E_USER_ERROR:
    $error_description = 'User Fatal';
    break;
  case E_USER_WARNING:
    $error_description = 'User Warning';
    break;
  case E_USER_NOTICE:
    $error_description = 'User Notice';
    break;
  case E_RECOVERABLE_ERROR:
    $error_description = 'Recoverable';
    break;
  case E_DEPRECATED:
    $error_description = 'Deprecated';
    break;
  case E_USER_DEPRECATED:
    $error_description = 'User Deprecated';
    break;
  default:
    $error_description = 'Unknown';
    break;
  }
  return 'PHP ' . $error_description;
}

/**
 * Allows errors returned from php built-in functions to be caught using exception handling
 * It is possible to pass in a custom exception type, i.e. any class that inherits from Exception 
 * to give the calling code the control over how to deal with different types. it also allows for 
 * exceptions when the calling code might have problems with an unexpected return value (bool)false
 * 
 * @param  string    $function                   - php internal function or array style callback using static classname or object
 * @param  array     $args                       - arguments expected by the php function 
 * @param  string    $exception_type             - default null(throws new Exception())
 * @param  bool      $throw_exception_for_false  - default false
 * @return mixed     result of @call_user_func_array                                       
 * @throws Exception $exception_type
 */
function safeCall($function, Array $args = array(), $exception_type = null, $throw_exception_on_false = false)
{
  if ($exception_type === null)
  {
    $exception_type = 'Exception';
  }

  // reset the error_get_last()
  @trigger_error('safeCall');

  // make the call
  $return = @call_user_func_array($function, $args);

  $last_error = error_get_last();
  if (is_array($last_error))
  {
    extract($last_error);

    if (!($type === E_USER_NOTICE && $message === 'safeCall'))
    {
      $error_prefix = getErrorDescription($type) . ': ' . getCallbackDescription($function) . ': ';

      // reset the error_get_last() so we do not get double reporting in shutdown::logErrors
      @trigger_error('safeCall');

      throw new $exception_type($error_prefix . $message);
    }
  }

  if ($return === false && $throw_exception_on_false === true)
  {
    $error_prefix = getErrorDescription(E_WARNING) . ': ' . getCallbackDescription($function) . ': ';
    throw new $exception_type($error_prefix . 'returned false when $throw_exception_on_false was true');
  }

  return $return;
}

