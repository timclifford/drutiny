<?php

namespace Drutiny\DomainList;

use Drutiny\Config;

class DomainListRegistry {
  public static function loadFromInput($source)
  {
    $loaders = Config::get('DomainList');
    $options = [];

    // Special case for default DomainListYamlFile.
    if (file_exists($source)) {
      $options['filepath'] = $source;
      return new $loaders['YamlFile']($options);
    }

    if (strpos($source, ',') !== FALSE) {
      list($loader, $source) = explode(',', $source, 2);
    }
    else {
      $loader = $source;
      $source = '';
    }

    if (!isset($loaders[$loader])) {
      throw new \Exception("No such DomainList loader known: $loader.");
    }

    foreach (array_filter(explode(',', $source)) as $data) {
      list($key, $value) = explode('=', $data, 2);
      $options[$key] = $value;
    }

    return new $loaders[$loader]($options);
  }
}

 ?>
