<?php

namespace Drutiny;

use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;

class Container {
  static $verbosity;

  public static function getCacheDirectory()
  {
    return Config::getUserDir() . '/cache';
  }

  public static function cache($bin)
  {
    $registry = Config::get('Cache');
    $cache = new \Symfony\Component\Cache\Adapter\ArrayAdapter();
    if (isset($registry[$bin])) {
      $class = $registry[$bin];
      $cache = new $registry[$bin]($bin, 0, self::getCacheDirectory());
    }
    $cache->setLogger(self::getLogger());
    return $cache;
  }

  public static function config($bin)
  {
    return Config::get($bin);
  }

  public static function credentialManager($namespace)
  {
    return \Drutiny\Credential\Manager::load($namespace);
  }

  public static function utility()
  {
    return new Utility;
  }

  public static function getLogger()
  {
    if (!(self::setLogger() instanceof LoggerInterface)) {
      self::setLogger(new ConsoleLogger(new ConsoleOutput(self::getVerbosity())));
    }
    return self::setLogger();
  }

  public static function setLogger(LoggerInterface $logger = null)
  {
    static $device;
    if ($logger instanceof LoggerInterface) {
       $device = $logger;
    }
    return $device;
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
      if (!isset(self::$verbosity)) {
        switch (getenv('SHELL_VERBOSITY')) {
            case -1: return OutputInterface::VERBOSITY_QUIET; break;
            case 1: return OutputInterface::VERBOSITY_VERBOSE; break;
            case 2: return OutputInterface::VERBOSITY_VERY_VERBOSE; break;
            case 3: return OutputInterface::VERBOSITY_DEBUG; break;
            default: return OutputInterface::VERBOSITY_NORMAL; break;
        }
      }
      return self::$verbosity;
  }
}

 ?>
