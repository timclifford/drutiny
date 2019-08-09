<?php

namespace Drutiny\Http;

use Drutiny\Container;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Kevinrob\GuzzleCache\CacheMiddleware;
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

    // Deal with Authorization headers (401 Responses).
    $config['handler']->push(function (callable $handler) {
        return new RetryWithAuthMiddleware($handler);
    });

    $message_format = __CLASS__ . " HTTP Request\n\n{req_headers}\n\n{res_headers}";
    if (Container::getVerbosity() <= OutputInterface::VERBOSITY_VERY_VERBOSE) {
      $message_format = __CLASS__ . " {code} {phrase} {uri} {error}";
    }

    // Logging HTTP Requests.
    $logger = Middleware::log(
      Container::getLogger(),
      new MessageFormatter($message_format)
    );
    $config['handler']->push($logger);

    // Cache HTTP responses. Add to the bottom so other cache
    // handlers take priority if present.
    if (!isset($config['cache']) || $config['cache']) {
      $config['handler']->unshift(cache_middleware(), 'cache');
    }


    parent::__construct($config);
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
