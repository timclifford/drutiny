<?php

namespace Drutiny\Target;

use Drutiny\Registry as GlobalRegistry;
use Doctrine\Common\Annotations\AnnotationReader;


class Registry extends GlobalRegistry {

  public static function loadTarget($targetData)
  {
    list($name, $metadata) = Target::parseTarget($targetData);
    return self::getTarget($name, $metadata);
  }

  public static function getAllTargets()
  {
    if ($targets = self::get('drutiny.targets')) {
      return $targets;
    }
    $targets = [];
    foreach (self::getConfig()->Target as $class) {
      $reflect = new \ReflectionClass($class);
      $reader = new AnnotationReader();
      if ($reflect->isAbstract()) {
        continue;
      }
      if (!$reflect->isSubClassOf('\Drutiny\Target\Target')) {
        continue;
      }
      $info = array_filter($reader->getClassAnnotations($reflect), function ($annotation) {
        return $annotation instanceof \Drutiny\Annotation\Target;
      });
      if (empty($info)) {
        continue;
      }
      $name = array_shift($info)->name;
      $targets[$name] = $class;
    }
    self::add('drutiny.targets', $targets);
    return $targets;
  }

  public static function getTarget($name, $options)
  {
    $targets = self::getAllTargets();
    if (isset($targets[$name])) {
      return new $targets[$name]($options);
    }
    throw new \Exception("Cannot find target: $name");
  }
}

 ?>
