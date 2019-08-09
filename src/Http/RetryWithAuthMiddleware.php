<?php
namespace Drutiny\Http;

use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Psr7;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Drutiny\Container;
use Drutiny\Credential\CredentialsUnavailableException;

/**
 * Middleware that retries requests based on the boolean result of
 * invoking the provided "decider" function.
 */
class RetryWithAuthMiddleware
{
    /** @var callable  */
    private $nextHandler;

    /**
     * @param callable $nextHandler Next handler to invoke.
     */
    public function __construct(callable $nextHandler) {
        $this->nextHandler = $nextHandler;
    }

    /**
     * @param RequestInterface $request
     * @param array            $options
     *
     * @return PromiseInterface
     */
    public function __invoke(RequestInterface $request, array $options)
    {

        $fn = $this->nextHandler;
        return $fn($request, $options)
            ->then(
                $this->onFulfilled($request, $options)
            );
    }

    private function onFulfilled(RequestInterface $request, array $options)
    {
        return function (ResponseInterface $response) use ($request, $options) {
            if (($response->getStatusCode() == 401) && $response->hasHeader('WWW-Authenticate')) {
              return $this->doRetry($request, $options, $response);
            }
            return $response;
        };
    }

    private function doRetry(RequestInterface $request, array $options, ResponseInterface $response = null)
    {
        $uri = (string) $request->getUri();
        $uri = str_replace(parse_url($uri, PHP_URL_SCHEME) . '://', '', $uri);
        try {
          $creds = Container::credentialManager('http_auth');
          if (isset($creds[$uri])) {
            $creds = $creds[$uri];
            return $this($request->withHeader('Authorization', 'Basic ' . base64_encode("{$creds['username']}:{$creds['password']}")), $options);
          }
        }
        catch (CredentialsUnavailableException $e) {
          $style->error($e->getMessage());
        }

        $cache = Container::cache('http_auth')->getItem(strtr($uri, [
          '{' => '',
          '}' => '',
          '(' => '',
          ')' => '',
          '/' => '',
          '\\' => '',
          '@' => '',
          ':' => '',
        ]));

        if ($cache->isHit()) {
          return $this($request->withHeader('Authorization', $cache->get()), $options);
        }

        $style = new SymfonyStyle(new ArgvInput(), new ConsoleOutput());
        $style->warning("HTTP request is blocked by HTTP Authorization: " . $request->getUri());
        $style->warning("Please provide the user name and password to access URI.");
        $style->text("Drutiny HTTP supports basic HTTP Authorization.");
        $username = $style->ask("Please provide the USERNAME for HTTP Authorization");
        $password = $style->ask("Please provide the PASSWORD for HTTP Authorization");

        $cache->set('Basic ' . base64_encode("$username:$password"));
        Container::cache('http_auth')->save($cache);

        return $this($request->withHeader('Authorization', $cache->get()), $options);
      }
}
