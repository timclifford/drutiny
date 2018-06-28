<?php

namespace Drutiny\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheException;
use Psr\Cache\CacheItemInterface;
use Drutiny\Container;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Finder;

class LocalFsCacheItemPool implements CacheItemPoolInterface {

  /**
   * Namespace for the in memory item.
   */
  protected $bin;

  /**
   * Storage location.
   */
  protected $location;

  public function __construct($bin = 'general', $location = FALSE)
  {
    if (!$location) {
      $location = getenv('HOME') . '/.drutiny/cache';
    }
    $this->location = $location;

    $location .= '/' . $bin;

    // Ensure the file system directory structure is present to support the
    // storage system.
    if (!is_dir($location)) {
      $dirs = [];
      while (!is_dir($location)) {
        $dirs[] = $location;
        $location = dirname($location);
      }
      foreach (array_reverse($dirs) as $dir) {
        Container::getLogger()->debug('Making cache directory: ' . $dir);
        if (!mkdir($dir) || !is_dir($dir)) {
          throw new CacheException("Could not create cache directory: $dir.");
        }
      }
    }

    $this->bin = $bin;
  }

  protected function getLocation($key)
  {
    return strtr('location/bin/key.cache', [
      'location' => $this->location,
      'bin' => $this->bin,
      'key' => $key,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getItem($key)
  {
    $logger = Container::getLogger();
    $filepath = $this->getLocation($key);
    if (file_exists($filepath)) {
      $logger->debug('[HIT] Opening cache file: ' . $filepath);
      $data = Yaml::parseFile($filepath);
      $item = new CacheItem(unserialize($data['value']), $key, new \DateTime($data['expiry']), $this);

      // Removed expired item.
      if (!$item->isHit()) {
        $logger->debug("[EXPIRED] Cached item is stale. Removing.");
        $this->delete($item);
        unset($item);
      }
    }
    else {
      $logger->debug('[MISS] No such file: ' . $filepath);
    }

    if (!isset($item)) {
      $item = new CacheItem(FALSE, $key, new \DateTime, $this);
    }
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
    return file_exists($this->getLocation($key));
  }

  /**
   * {@inheritdoc}
   */
  public function clear()
  {
    $finder = new Finder();
    $finder->files()
      ->in($this->location . '/' . $this->bin)
      ->name('*.cache');

    foreach ($finder as $file) {
      if (!unlink($file->getRealPath()) || file_exists($file->getRealPath())) {
        throw new CacheException("Cannot clear cache file: {$file->getRealPath()}");
      }
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteItem($key)
  {
    if ($this->hasItem($key)) {
      unlink($this->getLocation($key));
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
    $data = [
      'value' => serialize($item->get()),
      'expiry' => $item->getExpiry()->format(\DateTime::ATOM),
    ];
    $location = $this->getLocation($item->getKey());
    Container::getLogger()->debug("Writing cached item to filesystem: " . $location);
    file_put_contents($location, Yaml::dump($data));
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
