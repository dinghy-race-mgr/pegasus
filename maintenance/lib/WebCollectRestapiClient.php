<?php

require BASE . 'lib/functions.php';
require BASE . 'lib/WebCollectResource.php';

require BASE . 'lib/WebCollectEvent.php';
require BASE . 'lib/WebCollectEventProduct.php';
require BASE . 'lib/WebCollectBooking.php';

require BASE . 'lib/WebCollectMember.php';
require BASE . 'lib/WebCollectAddress.php';
require BASE . 'lib/WebCollectSubscription.php';
require BASE . 'lib/WebCollectFormData.php';

/**
 *
 */
class WebCollectRestapiClient
{
  private $base_url;
  private $end_point;
  private $action;
  private $access_token;
  private $query = array();
  private $organisation_short_name;

  public function __construct()
  {
    $this->base_url = 'https://webcollect.org.uk/api/v1/';
  }

  public function setEndPoint($end_point)
  {
    $this->end_point = $end_point;
    $this->action = null;   // reset when changing endpoint
    $this->query = array(); // reset when changing endpoint
    return $this;
  }

  public function getEndPoint()
  {
    return $this->end_point;
  }

  public function setAction($action)
  {
    $this->action = $action;
    return $this;
  }

  public function getAction()
  {
    return $this->action;
  }

  public function setAccessToken($access_token)
  {
    $this->access_token = $access_token;
    return $this;
  }

  public function getAccessToken()
  {
    return $this->access_token;
  }

  public function setQuery($query = array())
  {
    $this->query = $query;
    return $this;
  }

  public function getQuery()
  {
    return $this->query;
  }

  public function setOrganisationShortName($organisation_short_name)
  {
    $this->organisation_short_name = $organisation_short_name;
    return $this;
  }

  public function getOrganisationShortName()
  {
    return $this->organisation_short_name;
  }
  
  protected function getUrl()
  {
    if (trim($this->getOrganisationShortName()) == '')
    {
      throw new Exception('you must set an orgshortname');
    }
    $url = $this->base_url . $this->getOrganisationShortName() . '/' . $this->getEndPoint();
    if ($this->getAction() !== null)
    {
      $url .= '/' . $this->getAction();
    }
    if (count($this->getQuery()) > 0)
    {
      $url .= '?' .http_build_query($this->getQuery());
    }
    return $url;
  }

  protected function createStream()
  {
    $url = $this->getUrl();
    if (trim($this->getAccessToken()) == '')
    {
      throw new Exception('you must set an access token');
    }
    $options = array(
      'http'=>array(
        'default_socket_timeout' => 10,
        'header' => array('Authorization: Bearer ' . $this->getAccessToken()),
        'ignore_errors' => true, // so we can get the error messages
        )
      );
    
    $context = stream_context_create($options);
    $stream = safeCall('fopen', array($url, 'r', false, $context), null, true);
    $meta = stream_get_meta_data($stream);
    preg_match('#HTTP/\d+\.\d+ (\d+) (.*)$#', $meta['wrapper_data'][0], $matches);
    $response_code = intval($matches[1]);
    if ($response_code != 200)
    {
      $body = stream_get_contents($stream);
      throw new Exception('Received response: "' . $response_code . ' ' . $matches[2] . '". Message was: "' . $body . '"');
    }
    return $stream;
  }

  public function findOne()
  {
    $resources = $this->find(); // no point in passing a callback for a single record
    if (is_array($resources))
    {
      if (count($resources) > 1)
      {
        throw new Exception('you called findOne but there was more that one result');
      }
      if (count($resources) == 1 && isset($resources[0]))
      {
        return $resources[0];
      }
    }
    return null; // explicit to make it clear
  }

  public function find($callback = null)
  {
    if ($callback === null)
    {
      $stream = $this->createStream();
      $resources = array();
      $objects = json_decode(stream_get_contents($stream));
      if ($objects === null || json_last_error() !== JSON_ERROR_NONE) // null is not allowed, should always be an array
      {
        throw new Exception('Invalid json received from WebCollect API');
      }
      if (!is_array($objects)) // expecting an array!
      {
        throw new Exception('Was expecting an array at top level of json object received from WebCollect API');
      }
      foreach ($objects as $object)
      {
        $resources[] = $this->cast($object);
      }
      fclose($stream);
      return $resources;
    }
    else
    {
      if (!is_callable($callback))
      {
        throw new Exception('If you use $client->find($callback) you must provide a valid callback. ' .
                            'The callback you provided to process the objects is not "callable"!');
      }
      $stream = $this->createStream();
      $this->processStream($stream, $callback);
      fclose($stream);
    }
  }

  private function cast(StdClass $object)
  {
    $class = 'WebCollect' . camel_case($this->getEndPoint(), true);
    return new $class($object);
  }

  private function processStream($stream, $callback)
  {
    $head = stream_get_line($stream, 2);
    if ($head == '[]' && feof($stream))
    {
      //empty set..never call the callback
      return;
    }
    if ($head != '[{')
    {
      echo $head ."\n";
      throw new Exception('Not streamable. Must be a json array of objects.');
    }
    // we think it will be a large array of objects
    $json_piece = '{';
    while (!feof($stream) && ($json_piece .= stream_get_line($stream, 32768, '}')) !== false)
    {
      // TODO this is presumptious, next `}` could be more than 32768 chars away
      $json_piece .= '}'; // stream_get_line does not return the delim
      
      // this is the key step, try to decode, if it is a valid object then we know we are at next master array index
      $object = json_decode($json_piece);
      if ($object !== null && json_last_error() == JSON_ERROR_NONE)
      {
        // we sucessfully decoded one object in the master array now do sth meaningful with $object, by calling the provided callback
        $callback($this->cast($object));
        
        $next_char = stream_get_line($stream, 1);
        if ($next_char === ']')
        {
          // should be at end of file
          if ((($tail = stream_get_line($stream, 32768)) !== false && strlen($tail) > 0) || !feof($stream))
          {
            throw new Exception('end of master array but not end of stream, found this: "' . $tail . '"');
          }
          break;
        }
        elseif($next_char === ',')
        {
          // next object in master array..continue
          $json_piece = '';
        }
        else
        {
          throw new Exception('unexpected next char after a complete object "' . $next_char . '"');
        }
      }
      else
      {
        // we expect JSON_ERROR_SYNTAX, because we are trying to decode partial objects
        if (json_last_error() != JSON_ERROR_SYNTAX)
        {
          throw new Exception('Unexpected JSON error: ' . json_last_error());
        }
      }
    }
  }
}
