<?php

namespace Drutiny;

use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;

class Container {
  static $verbosity;

  public static function getLogger()
  {
    return new ConsoleLogger(new ConsoleOutput(self::getVerbosity()));
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
