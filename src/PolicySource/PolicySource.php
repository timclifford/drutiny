<?php

namespace Drutiny\PolicySource;

use Drutiny\Config;
use Drutiny\Container;

class PolicySource {

  /**
   * Load policy by name.
   *
   * @param $name string
   */
  public static function loadPolicyByName($name)
  {
    $list = self::getPolicyList();

    if (!isset($list[$name])) {
      $list = self::getPolicyList(TRUE);
      if (!isset($list[$name])) {
        throw new UnknownPolicyException("$name does not exist.");
      }
      throw new UnavailablePolicyException("$name requires {$list[$name]['class']} but is not available in this environment.");
    }
    $definition = $list[$name];

    try {
      return self::getSource($definition['source'])->load($definition);
    }
    catch (\InvalidArgumentException $e) {
      Container::getLogger()->warning($e->getMessage());
      throw new UnavailablePolicyException("$name requires {$list[$name]['class']} but is not available in this environment.");
    }
  }

  /**
   * Acquire a list of available policies.
   *
   * @return array of policy information arrays.
   */
  public static function getPolicyList($include_invalid = FALSE)
  {
    static $list, $available_list;
    if (!empty($list)) {
      return $list;
    }

    $lists = [];
    foreach (self::getSources() as $source) {
      foreach ($source->getList() as $name => $item) {
        $item['source'] = $source->getName();
        $list[$name] = $item;
      }
    }

    if ($include_invalid) {
      return $list;
    }

    if (!empty($available_list)) {
      return $available_list;
    }

    $available_list = array_filter($list, function ($listedPolicy) {
      return class_exists($listedPolicy['class']);
    });
    return $available_list;
  }

  /**
   * Load all policies as loaded Policy objects.
   */
  public static function loadAll()
  {
    static $list = [];
    if (!empty($list)) {
      return $list;
    }
    foreach (self::getPolicyList() as $definition) {
      try {
        $list[$definition['name']] = self::loadPolicyByName($definition['name']);
      }
      catch (\Exception $e) {
        Container::getLogger()->warning("[{$definition['name']}] " . $e->getMessage());
      }
    }
    return $list;
  }

  /**
   * Load the sources that provide policies.
   *
   * @return array of PolicySourceInterface objects.
   */
  public static function getSources()
  {
    $item = Container::cache(__CLASS__)->getItem('sources');
    if ($item->isHit()) {
      return $item->get();
    }

    // The PolicySource config directive loads in class names that provides
    // policies for Drutiny to use. We need to validate each provided source
    // implements PolicySourceInterface.
    $sources = array_filter(array_map(function ($class) {
      $object = new $class();
      if (!($object instanceof PolicySourceInterface)) {
        return false;
      }
      return $object;
    }, Config::get('PolicySource')));

    // If multiple sources provide the same policy by name, then the policy from
    // the first source in the list will by used.
    usort($sources, function ($a, $b) {
      if ($a->getWeight() == $b->getWeight()) {
        return 0;
      }
      return $a->getWeight() > $b->getWeight() ? 1 : -1;
    });


    Container::cache(__CLASS__)->save(
      $item->set($sources)->expiresAfter(3600)
    );
    return $sources;
  }

  /**
   * Load a single source.
   */
  public static function getSource($name)
  {
    foreach (self::getSources() as $source) {
      if ($source->getName() == $name) {
        return $source;
      }
    }
    throw new \Exception("PolicySource not found: $name.");
  }
}
?>
