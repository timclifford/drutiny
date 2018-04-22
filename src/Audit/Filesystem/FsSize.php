<?php

namespace Drutiny\Audit\Filesystem;

use Drutiny\Audit;
use Drutiny\Sandbox\Sandbox;
use Drutiny\AuditResponse\AuditResponse;

/**
 * Large files
 */
class FsSize extends Audit {

  /**
   * @inheritdoc
   */
  public function audit(Sandbox $sandbox) {
    $path = $sandbox->getParameter('path', '%files');
    $stat = $sandbox->drush(['format' => 'json'])->status();

    $path = strtr($path, $stat['%paths']);

    $size = $sandbox->exec("du -d 0 $path | awk '{print $1}'");

    $max_size = (int) $sandbox->getParameter('max_size', 20);

    // Set the size in MB for rendering
    $sandbox->setParameter('size', ceil($size * 1024 * 1024));
    // Set the actual path.
    $sandbox->setParameter('path', $path);

    // Convert max_size into bytes.
    $max_size = $max_size * 1024 * 1024;

    return $size < $max_size;
  }

}
