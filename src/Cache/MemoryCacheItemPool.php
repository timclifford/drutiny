<?php

namespace Drutiny\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheItemInterface;

class MemoryCacheItemPool implements CacheItemPoolInterface {

  /**
   * Namespace for the in memory item.
   */
  protected $bin;

  /**
   *
   */
  protected static $storage = [];

  public function __construct($bin = 'general')
  {
    $this->bin = $bin;
  }

  /**
   * {@inheritdoc}
   */
  public function getItem($key)
  {
    $value = isset(self::$storage[$this->bin][$key]) ? self::$storage[$this->bin][$key] : FALSE;
    $item = new CacheItem($value, $key, new \DateTime, $this);
    $item->expiresAfter(3600);
    return $item;
  }

  /**
   * {@inheritdoc}
   */
  public function getItems(array $keys = array())
  {
    $items = [];
    foreach ($keys as $key) {
      $items[$key] = $this->getItem($key);
    }
    return $items;
  }

  /**
   * {@inheritdoc}
   */
  public function hasItem($key)
  {
    return isset(self::$storage[$this->bin][$key]);
  }

  /**
   * {@inheritdoc}
   */
  public function clear()
  {
    self::$storage[$this->bin] = [];
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteItem($key)
  {
    if ($this->hasItem($key)) {
      unset(self::$storage[$this->bin][$key]);
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteItems(array $keys)
  {
    foreach ($keys as $key) {
      $this->deleteItem($key);
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function save(CacheItemInterface $item)
  {
    self::$storage[$this->bin][$item->getKey()] = $item->get();
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function saveDeferred(CacheItemInterface $item)
  {
    return $this->save($item);
  }

  /**
   * {@inheritdoc}
   */
  public function commit()
  {
    return TRUE;
  }
}
