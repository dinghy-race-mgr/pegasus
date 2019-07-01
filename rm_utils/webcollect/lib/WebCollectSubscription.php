<?php

class WebCollectSubscription extends WebCollectResource
{

  /**
   *
   * @author Oliver Schonrock <oliver@realtsp.com>
   * @params 
   * @return 
   */
  public function isCurrent()
  {
    $now = time();
    return
      $this->provides_membership &&
      $now >= strtotime($this->start_date) && 
      $now < strtotime($this->end_date) + 24*60*60; // note, this is inclusive the last day 
  }
}

