<?php

namespace Drutiny\PolicySource;

use Drutiny\Api;
use Drutiny\Policy;
use Drutiny\Container;

class DrutinyGitHubIO implements PolicySourceInterface {

  /**
   * {@inheritdoc}
   */
  public function getName()
  {
    return 'drutiny.github.io';
  }

  /**
   * {@inheritdoc}
   */
  public function getList()
  {
    $api = new Api();
    $list = [];
    foreach ($api->getPolicyList() as $listedPolicy) {
      $listedPolicy['filepath'] = Api::BaseUrl . $listedPolicy['_links']['self']['href'];
      $list[$listedPolicy['name']] = $listedPolicy;
    }
    return $list;
  }

  /**
   * {@inheritdoc}
   */
  public function load(array $definition)
  {
    $cache = Container::cache('drutiny.github.io.policy');
    $item  = $cache->getItem($definition['signature']);

    if ($item->isHit()) {
      Container::getLogger()->info("Cache hit for {$definition['name']} from " . $this->getName());
      return new Policy($item->get());
    }
    Container::getLogger()->info("Fetching {$definition['name']} from {Api::BaseUrl}");

    $endpoint = str_replace(parse_url(Api::BaseUrl, PHP_URL_PATH), '', $definition['_links']['self']['href']);
    $policyData = json_decode(Api::getClient()->get($endpoint)->getBody(), TRUE);
    $policyData['filepath'] = $definition['_links']['self']['href'];

    $item->set($policyData)
         // The cache ID (signature) is a hash that changes when the policy
         // metadata changes so we can cache this as long as we like.
         ->expiresAt(new \DateTime('+1 month'));
    $cache->save($item);

    return new Policy($policyData);
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight()
  {
    return -100;
  }
}
