<?php

namespace Drutiny;

use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Psr\Log\LoggerInterface;

class Container {
  static $verbosity;

  public static function getLogger()
  {
    if (Cache::get('container', 'logger') instanceof LoggerInterface) {
      return Cache::get('container', 'logger');
    }
    return new ConsoleLogger(new ConsoleOutput(self::getVerbosity()));
  }

  public static function setLogger(LoggerInterface $logger)
  {
    return Cache::set('container', 'logger', $logger);
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
