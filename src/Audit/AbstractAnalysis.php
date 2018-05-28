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
 * @Param(
 *  name = "not_applicable",
 *  type = "string",
 *  default = "false",
 *  description = "The expression language to evaludate if the analysis is not applicable. See https://symfony.com/doc/current/components/expression_language/syntax.html"
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
    $expressionLanguage = new ExpressionLanguage();
    $variables  = $sandbox->getParameterTokens();
    $sandbox->logger()->info(__CLASS__ . ': ' . Yaml::dump($variables));

    $expression = $sandbox->getParameter('not_applicable', 'true');
    $sandbox->logger()->info(__CLASS__ . ': ' . $expression);
    if ($expressionLanguage->evaluate($expression, $variables)) {
      return self::NOT_APPLICABLE;
    }

    $expression = $sandbox->getParameter('expression', 'true');
    $sandbox->logger()->info(__CLASS__ . ': ' . $expression);

    return $expressionLanguage->evaluate($expression, $variables);
  }
}

 ?>
