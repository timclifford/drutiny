<?php

namespace Drutiny;

use Symfony\Component\Finder\Finder;

class Config {

  public static function getUserDir()
  {
    return getenv('HOME') . '/.drutiny';
  }

  public static function get($name)
  {
    if (!$config = Registry::get('drutiny.config')) {
      $registry = new Registry();
      $config = $registry->getConfig();
    }
    if (!isset($config->{$name})) {
      throw new \Exception("No such config: $name.");
    }
    return $config->{$name};
  }

  public static function getFinder()
  {
    $directories = array_filter([DRUTINY_LIB, self::getUserDir()], 'is_dir');
    $finder = new Finder();
    $finder->files()
      ->in($directories);
    return $finder;
  }

}

 ?>
