<?php

namespace Drutiny\Policy;

/**
 * Report on collected data.
 */
class PolicyBase {
  const SEVERITY_NONE = 0;
  const SEVERITY_LOW = 1;
  const SEVERITY_NORMAL = 2;
  const SEVERITY_HIGH = 4;
  const SEVERITY_CRITICAL = 8;

  /**
   * @string Title of the policy. A human readable value.
   */
  protected $title;

  /**
   * @string Name of the policy. A machine readable value.
   */
  protected $name;

  /**
   * @string Reference to a \Drutiny\AuditInterface class.
   */
  protected $class;

  /**
   * @string A description of what a policy should audit.
   */
  protected $description;

  /**
   * @string A description of what a policy should audit.
   */
  protected $type = 'audit';

  /**
   * @array tokens to to replace in tokenized attributes when rendered.
   */
  protected $tokens = [];

  /**
   * @array A convention for categorising a policy.
   */
  protected $tags = [];

  /**
   * @array list of object attributes that may be passed through the renderer.
   */
  protected $renderableProperties = [
    'title',
    'name',
    'description'
  ];

  /**
   *
   */
  public function __construct(array $info) {

    foreach ($info as $key => $value) {
      if (!property_exists($this, $key)) {
        continue;
      }
      $this->{$key} = $value;
    }
  }

  /**
   * Render a property.
   */
  protected function render($markdown, $replacements) {
    $m = new \Mustache_Engine();
    try {
      return $m->render($markdown, $replacements);
    }
    catch (\Mustache_Exception $e) {
      throw new \Exception("Error in $this->name: " . $e->getMessage());
    }
  }

  /**
   * Retrieve a property value and token replacement.
   */
  public function get($property, $replacements = []) {
    if (!isset($this->{$property})) {
      throw new \Exception("Attempt to retrieve unknown property: $property. Available properties: \n" . print_r((array) $this, 1));
    }
    if (in_array($property, $this->renderableProperties)) {
      return $this->render($this->{$property}, $replacements);
    }
    return $this->{$property};
  }

  public function has($property) {
    return isset($this->{$property});
  }

  public function hasTag($tag) {
    return in_array($tag, $this->tags);
  }

  public function getTags() {
    return $this->tags ?: [];
  }
}
