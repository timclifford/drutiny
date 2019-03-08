<?php

namespace Drutiny;

use Symfony\Component\Finder\Finder;

class Config {

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
    $directories = [DRUTINY_LIB, getenv('HOME') . '/.drutiny/'];
    $finder = new Finder();
    $finder->files()
      ->in($directories);
    return $finder;
  }

}

 ?>
