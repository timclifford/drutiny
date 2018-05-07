<?php

namespace Drutiny\Audit;

use Drutiny\Audit;
use Drutiny\Sandbox\Sandbox;
use Drutiny\Annotation\Param;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Yaml\Yaml;

/**
 * Audit gathered data.
 *
 * @Param(
 *  name = "expression",
 *  type = "string",
 *  default = "true",
 *  description = "The expression language to evaludate. See https://symfony.com/doc/current/components/expression_language/syntax.html"
 * )
 */
abstract class AbstractAnalysis extends Audit {

  /**
   * Gather analysis data to audit.
   */
  abstract protected function gather(Sandbox $sandbox);

  final public function audit(Sandbox $sandbox)
  {
    $this->gather($sandbox);
    $expression = $sandbox->getParameter('expression', 'true');
    $variables  = $sandbox->getParameterTokens();

    $expressionLanguage = new ExpressionLanguage();

    $sandbox->logger()->info(__CLASS__ . ': ' . $expression);
    $sandbox->logger()->info(__CLASS__ . ': ' . Yaml::dump($variables));

    return $expressionLanguage->evaluate($expression, $variables);
  }
}

 ?>
