<?php

namespace Drutiny;

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

}

 ?>
