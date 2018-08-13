<?php

namespace Drutiny\Driver;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Drutiny\Container;
use Drutiny\Sandbox\Sandbox;
use Drutiny\Target\DrushTargetInterface;
use Drutiny\Target\DrushExecutableTargetInterface;
use Drutiny\Target\TargetInterface;

/**
 *
 */
class Drush9Driver extends DrushDriver {

  public function userInformation($username)
  {
    $params = func_get_args();
    // Older versions of drush passed uid as arg, not option.
    if (is_numeric($params[0])) {
      $params[0] = '--uid=' . $params[0];
    }
    return $this->__call('userInformation', $params);
  }
}
