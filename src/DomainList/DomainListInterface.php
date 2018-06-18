<?php

namespace Drutiny\DomainList;

use Drutiny\Target\Target;

interface DomainListInterface {

  public function __construct(array $metadata);

  /**
   * @return array list of domains.
   */
  public function getDomains(Target $target, callable $filter);
}

 ?>
