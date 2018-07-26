<?php

namespace Drutiny\ExpressionFunction;

use Drutiny\Annotation\ExpressionSyntax;
use Drutiny\Sandbox\Sandbox;
use Composer\Semver\Comparator;

/**
 * @ExpressionSyntax(name = "semver_gt")
 */
class SemverGtExpressionFunction implements ExpressionFunctionInterface {
  static public function compile(Sandbox $sandbox)
  {
    list($sandbox, $v1, $v2) = func_get_args();
    return sprintf('%s > %s', $v1, $v2);
  }

  static public function evaluate(Sandbox $sandbox)
  {
    list($sandbox, $v1, $v2) = func_get_args();
    return Comparator::greaterThan($v1, $v2);
  }
}

 ?>
