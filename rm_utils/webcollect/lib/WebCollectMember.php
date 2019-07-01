<?php

class WebCollectMember extends WebCollectResource
{

  /**
   *
   * @author Oliver Schonrock <oliver@realtsp.com>
   * @params 
   * @return 
   */
  public function isCurrent()
  {
    foreach ($this->subscriptions as $subscription)
    {
      if ($subscription->isCurrent())
      {
        return true;
      }
    }
    return false;
  }
  
}

