<?php

namespace Drutiny\Http;

use Drutiny\Container;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Psr\Http\Message\RequestInterface;
use Kevinrob\GuzzleCache\Storage\Psr6CacheStorage;
use Kevinrob\GuzzleCache\Strategy\PrivateCacheStrategy;
use Symfony\Component\Cache\Adapter\FilesystemAdapter as Cache;
use Symfony\Component\Console\Output\OutputInterface;

class Client extends GuzzleClient {
  public function __construct(array $config = [])
  {
    if (!isset($config['handler'])) {
        $config['handler'] = HandlerStack::create();
    }

    $this->processHandler($config['handler']);


    parent::__construct($config);
  }

  public static function processHandler(HandlerStack &$handler)
  {
    // Deal with Authorization headers (401 Responses).
    $handler->push(Middleware::mapRequest(function (RequestInterface $request) {
      $uri = (string) $request->getUri();
      $host = parse_url($uri, PHP_URL_HOST);
      $creds = Container::credentialManager('http_auth');
      if (isset($creds[$host])) {
        $credential = $creds[$host]['username'] . ':' . $creds[$host]['password'];
        return $request->withHeader('Authorization', 'Basic ' . base64_encode($credential));
      }
      return $request;
    }), 'authorization');

    // Provide a default User-Agent.
    $handler->push(Middleware::mapRequest(function (RequestInterface $request) {
      try {
        $http = Container::credentialManager('http');
      }
      catch (\Drutiny\Credential\CredentialsUnavailableException $e) {
        $http = ['user_agent' => 'Drutiny'];
      }
      $agent = $http['user_agent'];

      return $request->withHeader('User-Agent', $agent);
    }), 'user_agent');

    $handler->unshift(cache_middleware(), 'cache');

    $message_format = __CLASS__ . " HTTP Request\n\n{req_headers}\n\n{res_headers}";
    if (Container::getVerbosity() <= OutputInterface::VERBOSITY_VERY_VERBOSE) {
      $message_format = __CLASS__ . " {code} {phrase} {uri} {error}";
    }

    // Logging HTTP Requests.
    $logger = Middleware::log(
      Container::getLogger(),
      new MessageFormatter($message_format)
    );

    $handler->after('cache', $logger, 'logger');
  }
}

function cache_middleware()
{
  static $middleware;
  if ($middleware) {
    return $middleware;
  }
  $storage = new Psr6CacheStorage(Container::cache('http'));
  $middleware = new CacheMiddleware(new PrivateCacheStrategy($storage));
  return $middleware;
}

?>
