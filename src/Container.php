<?php

namespace Drutiny;

use Drutiny\Logger\ConsoleLogger;

class Container {
  static $verbosity;

  public static function getLogger()
  {
    return ConsoleLogger::create(self::getVerbosity());
  }

  /**
   * {@inheritdoc}
   */
  public static function setVerbosity($level)
  {
      self::$verbosity = (int) $level;
  }

  /**
   * {@inheritdoc}
   */
  public static function getVerbosity()
  {
      return self::$verbosity;
  }
}

 ?>
