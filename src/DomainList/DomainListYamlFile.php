<?php

namespace Drutiny\DomainList;

use Symfony\Component\Yaml\Yaml;
use Drutiny\Target\Target;
use Drutiny\Annotation\Param;
/**
 * @Param(
 *   name = "filepath",
 *   description = "Filepath to the YAML file containing the domains.",
 * )
 */
class DomainListYamlFile implements DomainListInterface {

  protected $filepath;

  public function __construct(array $metadata)
  {
    $this->filepath = $metadata['filepath'];
  }

  /**
   * @return array list of domains.
   */
  public function getDomains(Target $target, callable $filter)
  {
    return Yaml::parseFile($this->filepath);
  }
}
