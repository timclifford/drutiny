<?php

namespace Drutiny\Credential;

class Manager {

  public static function load($namespace)
  {
    $store = new FileStore($namespace);
    return $store->open();
  }
}

 ?>
