<?php

namespace Drutiny\PolicySource;

use Drutiny\Api;
use Drutiny\Cache;
use Drutiny\Config;
use Drutiny\Container;
use Drutiny\Policy;
use Symfony\Component\Yaml\Yaml;

class LocalFs implements PolicySourceInterface {

  /**
   * {@inheritdoc}
   */
  public function getName()
  {
    return 'localfs';
  }

  /**
   * {@inheritdoc}
   */
  public function getList()
  {
    $cache = Container::cache($this->getName())->getItem('list');
    if ($cache->isHit()) {
      return $cache->get();
    }
    $finder = Config::getFinder()->name('*.policy.yml');

    $list = [];
    foreach ($finder as $file) {
      $policy = Yaml::parse($file->getContents());
      $policy['filepath'] = $file->getPathname();
      $list[$policy['name']] = $policy;
    }
    Container::cache($this->getName())->save(
      $cache->set($list)->expiresAfter(3600)
    );
    return $list;
  }

  /**
   * {@inheritdoc}
   */
  public function load(array $definition)
  {
    return new Policy($definition);
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight()
  {
    return -10;
  }
}
