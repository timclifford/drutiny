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
    }
}
