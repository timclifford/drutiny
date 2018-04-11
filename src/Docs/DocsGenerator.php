<?php

namespace Drutiny\Docs;

use Drutiny\Registry;
use Symfony\Component\Yaml\Yaml;

abstract class DocsGenerator {

  protected function findPackage($filepath)
  {
      $composer = FALSE;
      while ($filepath) {
        $filepath = dirname($filepath);

        if (file_exists($filepath . '/composer.json')) {
          break;
        }
      }

      $json = file_get_contents($filepath . '/composer.json');
      $composer = json_decode($json, TRUE);
      return $composer['name'];
  }
}
