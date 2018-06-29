<?php

namespace Drutiny;

use Drutiny\Cache\MemoryCacheItemPool;
use Symfony\Component\Cache\CacheItem;

/**
 * A static cache handler.
 */
class Cache {

  static protected $cache = [];

  static public function set($bin, $cid, $value) {
    Container::getLogger()->warning('Drutiny\Cache class is deprecated. Please use Drutiny\Container::cache() instead.');
    $pool = Container::cache($bin);
    $item = $pool->getItem($cid);
    $item->set($value)
      ->expiresAt(new \DateTime('+1 hour'));
    $pool->save($item);
    return TRUE;
  }

  static public function get($bin, $cid) {
    Container::getLogger()->warning('Drutiny\Cache class is deprecated. Please use Drutiny\Container::cache() instead.');
    $pool = Container::cache($bin);
    return $pool->getItem($cid)->get();
  }

  static public function purge($bin = NULL) {
    Container::getLogger()->warning('Drutiny\Cache class is deprecated. Please use Drutiny\Container::cache() instead.');
    $pool = Container::cache($bin);
    $pool->clear();
    return TRUE;
  }

  static public function delete($bin, $cid) {
    Container::getLogger()->warning('Drutiny\Cache class is deprecated. Please use Drutiny\Container::cache() instead.');
    $pool = Container::cache($bin);
    $pool->deleteItem($cid);
    return TRUE;
  }

}
