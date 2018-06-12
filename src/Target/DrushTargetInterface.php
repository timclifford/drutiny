<?php

namespace Drutiny\Target;

/**
 * @Drutiny\Annotation\Target(
 *  name = "drush"
 * )
 */
interface DrushTargetInterface extends TargetInterface {
  /**
   * Return an array of Drush options from the Target site-alias.
   */
  public function getOptions();

  /**
   * Return the Drush site-alias.
   */
  public function getAlias();

  /**
   * Run a drush command.
   */
  public function runCommand($method, $args, $pipe = '');
}
