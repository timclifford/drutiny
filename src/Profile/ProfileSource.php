<?php

namespace Drutiny\Profile;

use Drutiny\Config;
use Drutiny\Container;

class ProfileSource {

  /**
   * Load policy by name.
   *
   * @param $name string
   */
  public static function loadProfileByName($name)
  {
    $list = self::getProfileList();
    $definition = $list[$name];

    return self::getSource($definition['source'])->load($definition);
  }

  /**
   * Acquire a list of available policies.
   *
   * @return array of policy information arrays.
   */
  public static function getProfileList()
  {
    static $list;
    if (!empty($list)) {
      return $list;
    }
    $lists = array_map(function ($source) {
      return array_map(function ($item) use ($source) {
        $item['source'] = $source->getName();
        return $item;
      },
      $source->getList());
    },
    self::getSources());

    $list = call_user_func_array('array_merge', $lists);
    return $list;
  }

  /**
   * Load all policies as loaded Policy objects.
   */
  public static function loadAll()
  {
    $list = [];
    foreach (self::getProfileList() as $definition) {
      $list[$definition['name']] = self::loadProfileByName($definition['name']);
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
      if (!($object instanceof ProfileSourceInterface)) {
        return false;
      }
      return $object;
    }, Config::get('ProfileSource')));

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
    throw new \Exception("ProfileSource not found: $name.");
  }
}
?>
