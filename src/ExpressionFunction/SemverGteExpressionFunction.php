<?php

namespace Drutiny\ExpressionFunction;

use Drutiny\Annotation\ExpressionSyntax;
use Drutiny\Sandbox\Sandbox;
use Composer\Semver\Comparator;

/**
 * @ExpressionSyntax(name = "semver_gte")
 */
class SemverGteExpressionFunction implements ExpressionFunctionInterface {

  /**
   * {@inheritdoc}
   */
  static public function compile(Sandbox $sandbox)
  {
    list($sandbox, $v1, $v2) = func_get_args();
    return sprintf('%s >= %s', $v1, $v2);
  }

  /**
   * {@inheritdoc}
   */
  static public function evaluate(Sandbox $sandbox)
  {
    list($sandbox, $v1, $v2) = func_get_args();
    return Comparator::greaterThanOrEqualTo($v1, $v2);
  }
}

 ?>
