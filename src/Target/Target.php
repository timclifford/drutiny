<?php

namespace Drutiny\Target;

use Drutiny\Driver\Exec;

/**
 * Basic function of a Target.
 */
abstract class Target implements TargetInterface {

  private $uri = FALSE;

  public final function __construct($target_data)
  {
    // Store target data to be used later when the sandbox is loaded.
    $this->parse($target_data);
  }

  /**
   *
   */
  public function parse($target_data) {
    return $this;
  }

  public function validate()
  {
    return TRUE;
  }

  /**
   *
   */
  public function uri()
  {
    return $this->uri;
  }

  public function setUri($uri)
  {
    $this->uri = $uri;
    if (!$this->validate()) {
      throw new InvalidTargetException(strtr("@uri is an invalid target", [
        '@uri' => $uri
      ]));
    }
    return $this;
  }

  /**
   * @inheritdoc
   * Implements ExecInterface::exec().
   */
  public function exec($command, $args = []) {
    $process = new Exec();
    return $process->exec($command, $args);
  }

  /**
   * Parse a target argument into the target driver and data.
   */
  static public function parseTarget($target)
  {
    $target_name = 'drush';
    $target_data = $target;
    if (strpos($target, ':') !== FALSE) {
      list($target_name, $target_data) = explode(':', $target, 2);
    }
    return [$target_name, $target_data];
  }

  /**
   * Alias for Registry::getTarget().
   */
  static public function getTarget($name, $options = [])
  {
    return Registry::getTarget($name, $options);
  }

}
