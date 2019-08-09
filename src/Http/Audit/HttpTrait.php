<?php

namespace Drutiny\Http\Audit;

use Drutiny\Sandbox\Sandbox;
use Drutiny\Http\Client;

Trait HttpTrait {

  protected function getHttpResponse(Sandbox $sandbox)
  {
    $url = $sandbox->getParameter('url', $uri = $sandbox->getTarget()->uri());

    // This allows policies to specify urls that still contain a domain.
    $url = strtr($url, [
      ':uri' => $uri,
    ]);

    if ($sandbox->getParameter('force_ssl', FALSE)) {
      $url = strtr($url, [
        'http://' => 'https://',
      ]);
    }

    $sandbox->setParameter('url', $url);

    $method = $sandbox->getParameter('method', 'GET');

    $sandbox->logger()->info(__CLASS__ . ': ' . $method . ' ' . $url);
    $options = $sandbox->getParameter('options', []);

    $status_code = $sandbox->getParameter('status_code');

    // Warm remote caches.
    $client = new Client([
      'cache' => $sandbox->getParameter('use_cache', TRUE)
    ]);

    return $client->request($method, $url, $options);
  }
}


 ?>
