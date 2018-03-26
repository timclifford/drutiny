<?php

namespace Drutiny\Sandbox;

use Psr\Log\LoggerInterface;

/**
 *
 */
trait LoggerTrait {
  protected $logger;

  /**
   *
   */
  public function setLogger(LoggerInterface $logger) {
    $this->logger = $logger;

    foreach (_loggerPlayback() as $log) {
      list($func, $args) = $log;
      call_user_func_array([$logger, $func], $args);
    }
    return $this;
  }

  /**
   *
   */
  public function logger() {
    if (!$this->logger) {
      return new _loggerRecorder();
    }
    return $this->logger;
  }

}

class _loggerRecorder {
  public function __call($func, $args)
  {
    _loggerPlayback($func, $args);
  }
}

function _loggerPlayback()
{
  static $log = [];

  $args = func_get_args();

  if (empty($args)) {
    $playback = $log;
    $log = [];
    return $playback;
  }
  $log[] = $args;
}
