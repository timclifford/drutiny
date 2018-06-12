<?php

namespace Drutiny\Driver;

use Drutiny\Sandbox\Sandbox;
use Drutiny\Target\TargetInterface;
use Composer\Semver\Comparator;

/**
 * Find the correct drush driver to use.
 */
class DrushRouter {

  /**
   * Derive a drush driver from a given Sandbox.
   *
   * @param Sandbox $sandbox
   * @param array $options
   * @return Drutiny\Driver\DrushDriver
   */
  public static function create(Sandbox $sandbox, $options = [])
  {
    return self::createFromTarget($sandbox->getTarget(), $options);
  }

  /**
   * Derive a drush driver from a given Target.
   */
  public static function createFromTarget(TargetInterface $target, $options = [])
  {
    $binary = trim($target->exec('which drush-launcher || which drush'));
    $output = $target->exec($binary . ' --version');
    preg_match('/Drush Version.+: +([0-9\.a-z]+)/', $output, $match);

    // if (Comparator::greaterThanOrEqualTo($match[1], '9.0.0')) {
    //   // Use Drush 9 Driver
    //   // $driver = new Drush9Driver($sandbox, $binary);
    // }
    // else {
      $driver = DrushDriver::createFromTarget($target, $binary);
    // }

    $driver->setOptions($options);
    return $driver;
  }
}
