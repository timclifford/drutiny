<?php

namespace Drutiny;

use Drutiny\Http\Client;
use Drutiny\Container;

class Api {
  const BaseUrl = 'https://drutiny.github.io/2.2.x/api/';

  public static function getClient()
  {
    return new Client([
      'base_uri' => self::BaseUrl,
      'headers' => [
        'User-Agent' => 'drutiny/2.2.x',
        'Accept' => 'application/json',
        'Accept-Encoding' => 'gzip'
      ],
      'decode_content' => 'gzip',
      'allow_redirects' => FALSE,
      'connect_timeout' => 10,
      'timeout' => 300,
    ]);
  }

  public function getPolicyList()
  {
    return json_decode($this->getClient()->get('policy_list.json')->getBody(), TRUE);
  }
}
?>
