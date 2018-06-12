<?php

namespace Drutiny\Target;

/**
 * Definition of a Target.
 */
interface TargetInterface {

  public function __construct($target_data);

  /**
   * Parse the target data passed in.
   * @param $target_data string to parse.
   */
  public function parse($target_data);

  /**
   * Hook to validate the target is auditable.
   */
  public function validate();

  /**
   * Provide a URI to represent the Target.
   */
  public function uri();

  /**
   * Set the URI
   */
  public function setUri($uri);

  /**
   * Execute a shell command against the target.
   */
  public function exec($command, $args = []);
}
