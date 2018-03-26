<?php

namespace Drutiny\Target;

use Drutiny\Sandbox\Sandbox;

/**
 *
 */
abstract class Target {
  private $sandbox;
  private $_data;
  protected $uri;

  public function __construct($target_data)
  {
    // Store target data to be used later when the sandbox is loaded.
    $this->_data = $target_data;
  }

  /**
   *
   */
  public function parse($target_data) {
    return $this;
  }

  /**
   *
   */
  protected function sandbox() {
    return $this->sandbox;
  }

  /**
   *
   */
  public function setSandbox(Sandbox $sandbox) {
    $this->sandbox = $sandbox;
    $this->parse($this->_data);
    return $this;
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
    return $this;
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

}
