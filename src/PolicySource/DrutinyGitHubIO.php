<?php

namespace Drutiny\PolicySource;

use Drutiny\Api;
use Drutiny\Policy;

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
      $list[$listedPolicy['name']] = $listedPolicy;
    }
    return $list;
  }

  /**
   * {@inheritdoc}
   */
  public function load(array $definition)
  {
    $endpoint = str_replace('{baseUri}/api/', '', $definition['_links']['self']['href']);
    $policyData = json_decode(Api::getClient()->get($endpoint)->getBody(), TRUE);
    $policyData['filepath'] = $definition['_links']['self']['href'];
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
