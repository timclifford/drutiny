<?php

namespace Drutiny\ExpressionFunction;

use Drutiny\Annotation\ExpressionSyntax;
use Drutiny\Sandbox\Sandbox;
use Composer\Semver\Comparator;
use Doctrine\Common\Annotations\AnnotationReader;

/**
 * @ExpressionSyntax(name = "target")
 */
class TargetExpressionFunction implements ExpressionFunctionInterface {
  static public function compile(Sandbox $sandbox)
  {
    list($sandbox, $parameter, ) = func_get_args();

    $target = $sandbox->getTarget();
    $metadata = $target->getMetadata();

    $parameter = str_replace('"', '', $parameter);

    $value = "<Target Unknown Parameter: $parameter>";

    if (isset($metadata[$parameter])) {
      $value = call_user_func([$target, $metadata[$parameter]]);
    }

    return $value;
  }

  static public function evaluate(Sandbox $sandbox)
  {
    list($sandbox, $parameter, ) = func_get_args();

    $target = $sandbox->getTarget();
    $metadata = $target->getMetadata();

    $value = "";

    if (isset($metadata[$parameter])) {
      $value = call_user_func([$target, $metadata[$parameter]]);
    }

    return $value;
  }
}

 ?>
