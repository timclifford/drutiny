<?php

namespace Drutiny\Profile;

use Drutiny\Policy;
use Drutiny\Registry as GlobalRegistry;

class PolicyDefinition {
  use \Drutiny\Item\ContentSeverityTrait;

  /**
   * The Policy object once instansiated.
   *
   * @var object Drutiny\Policy.
   */
  protected $policy;

  /**
   * Name of the poilcy.
   *
   * @var string
   */
  protected $name;

  /**
   * Weight of the policy in the order of the Profile.
   *
   * @var int
   */
  protected $weight = 0;

  /**
   * A list of PolicyDefinition objects that should be ordered before this one.
   *
   * @var array
   */
  protected $positionAfter = [];

  /**
   * A list of PolicyDefinition objects that should be ordered after this one.
   *
   * @var array
   */
  protected $positionBefore = [];

  /**
   * Parameters to set on the policy.
   *
   * @var array
   */
  protected $parameters = [];

  /**
   * Build a PolicyDefinition from Profile input.
   *
   * @var $name string
   * @var $definition array
   */
  public static function createFromProfile($name, $weight = 0, $definition = [])
  {
    $policy = new static();
    $policy->setName($name)
           ->setWeight($weight);

    if (isset($definition['parameters'])) {
      $policy->setParameters($definition['parameters']);
    }

    if (isset($definition['severity'])) {
      $policy->setSeverity($definition['severity']);
    }

    return $policy;
  }

  /**
   * Get the name of the policy.
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Set the name of the policy.
   */
  public function setName($name)
  {
    $this->name = $name;
    return $this;
  }

  /**
   * Get the weight of the policy.
   */
  public function getWeight()
  {
    return $this->weight;
  }

  /**
   * Set the weight of the policy.
   */
  public function setWeight($weight)
  {
    $this->weight = (int) $weight;
    return $this;
  }

  /**
   * Get the singleton policy for the profile.
   */
  public function getPolicy()
  {
    if (isset($this->policy)) {
      return $this->policy;
    }
    $this->policy = (new GlobalRegistry)->getPolicy($this->getName());
    $this->policy->setSeverity($this->getSeverity());

    foreach ($this->parameters as $param => $value) {
      $info = ['default' => $value];
      $this->policy->addParameter($param, $info);
    }
    return $this->policy;
  }

}

 ?>
