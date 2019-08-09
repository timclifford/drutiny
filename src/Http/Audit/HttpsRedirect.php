<?php

namespace Drutiny\Http\Audit;

use Drutiny\Sandbox\Sandbox;

class HttpsRedirect extends Http {

  /**
   *
   */
  public function audit(Sandbox $sandbox)
  {
    $url = $sandbox->getParameter('url', $uri = $sandbox->getTarget()->uri());
    $url = strtr($url, [
      'https://' => 'http://',
    ]);
    $sandbox->setParameter('url', $url);
    $sandbox->setParameter('expected_location', strtr($url, [
      'http://' => 'https://',
    ]));

    // Ensure the redirect is not followed.
    $options = $sandbox->getParameter('options', []);
    $options['allow_redirects'] = FALSE;
    $sandbox->setParameter('options', $options);

    $res = $this->getHttpResponse($sandbox);

    if (!$res->hasHeader('Location')) {
      return FALSE;
    }
    if ($res->getStatusCode() < 300 || $res->getStatusCode() > 400) {
      return FALSE;
    }
    $headers = $res->getHeader('Location');

    $sandbox->setParameter('location', $headers[0]);

    return strpos($headers[0], 'https://') !== FALSE;
  }
}


 ?>
