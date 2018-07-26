<?php

namespace Drutiny;

use Drutiny\ExpressionFunction\DrutinyExpressionLanguageProvider;
use Drutiny\Sandbox\Sandbox;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;

class ExpressionLanguage extends BaseExpressionLanguage
{
    public function __construct(Sandbox $sandbox)
    {
        // prepends the default provider to let users override it easily
        $providers[] = new DrutinyExpressionLanguageProvider($sandbox);

        parent::__construct(NULL, $providers);
    }
}
