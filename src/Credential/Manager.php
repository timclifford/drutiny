<?php

namespace Drutiny\Credential;

class Manager {

  public static function load($namespace)
  {

    $store = new FileStore($namespace);

    try {
      return $store->open();
    }
    catch (CredentialsUnavailableException $e) {
      return FALSE;
    }
  }
}

 ?>
