<?php

namespace Drutiny;

use Drutiny\Http\Client;
use Drutiny\Container;
use GuzzleHttp\Exception\ConnectException;

class Api {
  const BaseUrl = 'https://drutiny.github.io/api/v2/en/';

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
    try {
      return json_decode($this->getClient()->get('policy/index.json')->getBody(), TRUE);
    }
    catch (ConnectException $e) {
      Container::getLogger()->warning($e->getMessage());
      return [];
    }
  }

  public function getProfileList()
  {
    try {
      return json_decode($this->getClient()->get('profile/index.json')->getBody(), TRUE);
    }
    catch (ConnectException $e) {
      Container::getLogger()->warning($e->getMessage());
      return [];
    }
  }
}
?>
