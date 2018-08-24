<?php

namespace Drutiny\ExpressionFunction;

use Drutiny\Annotation\ExpressionSyntax;
use Drutiny\Sandbox\Sandbox;
use Composer\Semver\Comparator;
use Doctrine\Common\Annotations\AnnotationReader;
use Drutiny\Target\DrushTargetInterface;
use Drutiny\Driver\DrushRouter;

/**
 * @ExpressionSyntax(
 * name = "drupal_module_enabled",
 * usage = "drupal_module_enabled('page_cache')",
 * description = "Returns TRUE if the module is enabled. Otherwise FALSE."
 * )
 */
class DrupalModuleEnabledExpressionFunction implements ExpressionFunctionInterface {
  static public function compile(Sandbox $sandbox)
  {
    list($sandbox, $module, ) = func_get_args();

    $target = $sandbox->getTarget();

    if (!($target instanceof DrushTargetInterface)) {
      return '<invalid_target>';
    }

    return $module . '?enabled?';



    $metadata = $target->getMetadata();

    $parameter = str_replace('"', '', $parameter);

    $value = "<Target Unknown Parameter: $parameter. Available: " . implode(', ', array_keys($metadata)) . ">";

    if (isset($metadata[$parameter])) {
      $value = call_user_func([$target, $metadata[$parameter]]);
    }

    return $value;
  }

  static public function evaluate(Sandbox $sandbox)
  {
    list($sandbox, $module, ) = func_get_args();

    $target = $sandbox->getTarget();

    if (!($target instanceof DrushTargetInterface)) {
      return FALSE;
    }

    $drush = DrushRouter::createFromTarget($target, ['format' => 'json']);
    $list = $drush->pmList();

    if (!isset($list[$module])) {
      return FALSE;
    }

    if ($list[$module]['status'] != 'Enabled') {
      return FALSE;
    }

    return TRUE;
  }
}

 ?>
