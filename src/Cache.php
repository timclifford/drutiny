<?php

namespace Drutiny;

use Drutiny\Cache\MemoryCacheItemPool;
use Drutiny\Cache\CacheItem;

/**
 * A static cache handler.
 */
class Cache {

  static protected $cache = [];

  static public function set($bin, $cid, $value) {
    $pool = new MemoryCacheItemPool($bin);
    $item = new CacheItem($value, $cid, new \DateTime('+1 hour'));
    $pool->save($item);
    return TRUE;
  }

  static public function get($bin, $cid) {
    $pool = new MemoryCacheItemPool($bin);
    return $pool->getItem($cid)->get();
  }

  static public function purge($bin = NULL) {
    $pool = new MemoryCacheItemPool($bin);
    $pool->clear();
    return TRUE;
  }

  static public function delete($bin, $cid) {
    $pool = new MemoryCacheItemPool($bin);
    $pool->deleteItem($cid);
    return TRUE;
  }

}
