<?php

/**
 * this is intended to be a proof of concept cli script that you run just like this:
 * php webcollect_rest_api_client/test_client.php
 *
 * Requirements:
 *
 * php >= 5.3.6
 * json extension: http://php.net/manual/en/intro.json.php     PART OF PHP bundle
 * allow_url_fopen=true http://php.net/manual/en/filesystem.configuration.php    PART OF PHP bundle
 * openssl extension: http://uk3.php.net/manual/en/intro.openssl.php   PART of XAMPP
 * openssl will allow you to use fopen with https urls: http://php.net/manual/en/wrappers.http.php
 * 
 */

define('BASE', dirname(__FILE__) . '/');

require BASE . 'lib/WebCollectRestapiClient.php';

// you need to customise these values for your organisation
define('ORGANISATION_SHORT_NAME' , 'STARCROSSYC');

// you can generate the access token from the administrator panel if you have role "creator"
define('ACCESS_TOKEN', '986T9N5ZDSQFDBWAR4DCKOKEZS35YDFEP49KBBWMKJKXMKEHBPZOWUFC6HPZG6CS');

// this is just for the example used below, it should be the email for a member of your webcollect organisation
define('SEARCH_EMAIL', 'mark.elkington@blueyonder.co.uk');

// this is just for the example used below, it should be the unique_id for a member of your webcollect organisation
define('SEARCH_UNIQUE_ID', 'syc_9210');

// this is just for the example used below, it should be the organisation_group_unique_id for a family group in your webcollect organisation
define('SEARCH_ORGANISATION_GROUP_UNIQUE_ID', '1093');

// this is just for the example used below, it should be the webcollect_id for a member of your webcollect organisation
define('SEARCH_WEBCOLLECT_ID', '205883');

// event-short-name for retrieving bookings, get this from the even page on admin UI
define('EVENT_SHORT_NAME', 'event-short-name');


// first a query for a single member by email 
// we do not need live streaming of objects for this (although that would work, see below)


/******************************************************/

echo 'Querying a single member by email' . "\n";

$client = new WebcollectRestapiClient();
$member = $client->setOrganisationShortName(ORGANISATION_SHORT_NAME)           // this is the short name selected when the org was created on webcollect
  ->setAccessToken(ACCESS_TOKEN)                                               // from the admin UI
  ->setEndPoint('member')                                                     
  ->setQuery(array('email' => SEARCH_EMAIL))                                   // filter by email
  ->findOne();                                                                 // return the object (or null), we know it can only be one. find() returns an array

if ($member instanceof WebCollectResource)
{
  $array = json_decode(json_encode($member), true);
  echo "member is --- ".$array['lastname']."<br>";
  
  //echo "<pre>".print_r($member,true)."</pre>";
  echo "<pre>".print_r($array,true)."</pre>";

  // refer to WebCollectMember class for the member->isCurrent() method
  echo $member->email . (($member->isCurrent()) ? ' has ' : ' does not have ') . 'access' . "<br>";
}
else
{
  echo "member: " . SEARCH_EMAIL . " not found in organisation " . ORGANISATION_SHORT_NAME . "<br>";
}


/******************************************************/

echo 'Querying a single member by unique_id' . "\n";

$member = $client->setQuery(array('unique_id' => SEARCH_UNIQUE_ID))  // change query to search my unique_id instead
  ->findOne();                                             // return the object (or null), we know it can only be one. find() returns an array

if ($member instanceof WebCollectResource)
{
  echo "<pre>".print_r($member,true)."</pre>";
}
else
{
  echo "member with unique_id: " . SEARCH_UNIQUE_ID . " not found in organisation " . ORGANISATION_SHORT_NAME . "<br>";
}


/******************************************************/

echo 'Querying a single member by webcollect_id' . "\n";

$member = $client->setQuery(array('webcollect_id' => SEARCH_WEBCOLLECT_ID))  // change query to search my webcollect_id instead
  ->findOne();                                                     // return the object (or null), we know it can only be one. find() returns an array

if ($member instanceof WebCollectResource)
{
  echo "<pre>".print_r($member,true)."</pre>";
}
else
{
  echo "member with webcollect_id: " . SEARCH_WEBCOLLECT_ID . " not found in organisation " . ORGANISATION_SHORT_NAME . "<br>";
}

/******************************************************/

echo 'Querying members by organisation_group_unique_id' . "\n";

$members = $client->setQuery(array('organisation_group_unique_id' => SEARCH_ORGANISATION_GROUP_UNIQUE_ID))
  ->find();     // will likely return more than one member

if (count($members) < 1)
{
  echo "no members with organisation_group_unique_id: " . SEARCH_ORGANISATION_GROUP_UNIQUE_ID . " found in organisation " . ORGANISATION_SHORT_NAME . "\n";
}
else
{
  foreach ($members as $member)
  {
    $array = json_decode(json_encode($member), true);
	// print_r($member);
	echo "--- ".$array['firstname']." ".$array['lastname']."</br>";
  }
}

// Now get data for all members without a query filter
// by calling find('process_member') using a callback

function process_member(WebCollectResource $resource) 
{ 
  // this is called once per member object returned from the api
  // so you can do with the object what you want here
  // this is MUCH more memory efficient than using $client->find() for a large number of objects
  // for demo we just print it
  //print_r($resource); 
  //echo "<pre>".print_r($resource,true)."</pre>";
  $array = json_decode(json_encode($resource), true);
  echo "--- ".$array['firstname']." ".$array['lastname']." : ".$array['form_data']['Allocated_Duties_Club_use_only']." </br>";
}

echo "<hr>" . 'Now streaming all members... ' . "<br>";

$client->setQuery()                                               // clear the query
  ->find('process_member');                                       // if we pass a callback the client will call it with each object...live streaming!

/******************************************************/
/*
echo "\n\n\n" . 'Querying event bookings for one event' . "\n";

$bookings = $client->setEndPoint('event')
  ->setAction('bookings')                                        // only supported action for end_point event right now
  ->setQuery(['short_name' => EVENT_SHORT_NAME])                 // filter by event for all dates
  ->find();                                                      // returns an array of Event objects with their products/bookings

print_r($bookings);

echo "\n\n\n" . 'Querying event bookings by date range' . "\n";
$bookings = $client
  ->setQuery(['start_date' => '29-09-2017',
              'end_date'   => '29-10-2017'])             // filter by date range across all events
  ->find();                                              // returns an array of Event objects with their products/bookings

print_r($bookings);
*/