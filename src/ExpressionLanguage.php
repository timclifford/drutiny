<?php

namespace Drutiny;

use Drutiny\ExpressionFunction\DrutinyExpressionLanguageProvider;
use Drutiny\Sandbox\Sandbox;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

class ExpressionLanguage extends BaseExpressionLanguage
{
    public function __construct(Sandbox $sandbox)
    {
        // prepends the default provider to let users override it easily
        $providers[] = new DrutinyExpressionLanguageProvider($sandbox);

        parent::__construct(NULL, $providers);

        $this->addFunction(ExpressionFunction::fromPhp('in_array'));
        $this->addFunction(ExpressionFunction::fromPhp('array_key_exists'));
        $this->addFunction(ExpressionFunction::fromPhp('count'));
        $this->addFunction(ExpressionFunction::fromPhp('array_filter'));
        $this->addFunction(new ExpressionFunction('filter',
          // Compiler
          function ($array, $property, $match) {
            return sprintf('filter(%s, "%s", "%s")', $array, $property, $match);
          },
          // Evaluator
          function ($array, $property, $match = TRUE) {
            return array_filter($array, function ($value) use ($property, $match) {
              if (!is_array($value)) {
                return FALSE;
              }
              $property_parts = explode('.', $property);
              $ref = $value;
              foreach ($property_parts as $key) {
                if (!isset($ref[$key])) {
                  return FALSE;
                }
                $ref = $ref[$key];
              }
              if ($ref == $match) {
                return TRUE;
              }
              return FALSE;
            });
          }
        ));
    }
}
