<?php

namespace Drutiny;

use Drutiny\Profile\PolicyDefinition;
use Drutiny\Profile\Registry;
use Drutiny\Report\FormatOptions;
use Symfony\Component\Yaml\Yaml;

class Profile {

  /**
   * Title of the Profile.
   *
   * @var string
   */
  protected $title;

  /**
   * Machine name of the Profile.
   *
   * @var string
   */
  protected $name;


  /**
   * Description of the Profile.
   *
   * @var string
   */
  protected $description;

  /**
   * Filepath location of where the profile file is.
   *
   * @var string
   */
  protected $filepath;

  /**
   * A list of other \Drutiny\Profile\ProfileDefinition objects to include.
   *
   * @var array
   */
  protected $policies = [];

  /**
   * A list of other \Drutiny\Profile objects to include.
   *
   * @var array
   */
  protected $include = [];

  /**
   * If profile is included by another profile then this property points to that profile.
   *
   * @var object Profile.
   */
  protected $parent;


  /**
   * Keyed array of \Drutiny\Report\FormatOptions.
   *
   * @var array
   */
  protected $format = [];

  /**
   * Load a profile from file.
   *
   * @var $filepath string
   */
  public static function loadFromFile($filepath)
  {
    $info = Yaml::parseFile($filepath);
    $name = str_replace('.profile.yml', '', pathinfo($filepath, PATHINFO_BASENAME));

    $profile = new static();
    $profile->setTitle($info['title'])
            ->setName($name)
            ->setFilepath($filepath);

    if (isset($info['description'])) {
      $profile->setDescription($info['description']);
    }

    if (isset($info['policies'])) {
      $weight = array_search($profile->getName(), array_keys($info['policies']));

      foreach ($info['policies'] as $name => $metadata) {
        $profile->addPolicyDefinition(PolicyDefinition::createFromProfile($name, $weight, $metadata));
      }
    }

    if (isset($info['include'])) {
      foreach ($info['include'] as $name) {
        $include = self::loadFromFile(Registry::locateProfile($name));
        $profile->addInclude($include);
      }
    }

    if (isset($info['format'])) {
      foreach ($info['format'] as $format => $options) {
        $profile->addFormatOptions(FormatOptions::create($name, $options));
      }
    }

    return $profile;
  }

  /**
   * Add a FormatOptions to the profile.
   */
  public function addFormatOptions(FormatOptions $options)
  {
    $this->format[$options->getFormat()] = $options;
    return $this;
  }

  /**
   * Add a PolicyDefinition to the profile.
   */
  public function addPolicyDefinition(PolicyDefinition $definition)
  {
    $this->policies[$definition->getName()] = $definition;
    return $this;
  }

  /**
   * Add a PolicyDefinition to the profile.
   */
  public function getPolicyDefinition($name)
  {
    return $this->policies[$name];
  }

  /**
   * Add a PolicyDefinition to the profile.
   */
  public function getAllPolicyDefinitions()
  {
    // Sort the policies by weight.
    uasort($this->policies, function (PolicyDefinition $a, PolicyDefinition $b) {
      // If the weights are equal, sort by name.
      if ($a->getWeight() == $b->getWeight()) {

        $alpha = [$a->getName(), $b->getName()];
        sort($alpha);

        return $alpha[0] == $a->getName() ? -1 : 1;
      }
      return $a->getWeight() > $b->getWeight() ? -1 : 1;
    });
    return $this->policies;
  }

  /**
   * Get the profile title.
   */
  public function getTitle()
  {
    return $this->title;
  }

  /**
   * Set the title of the profile.
   */
  public function setTitle($title)
  {
    $this->title = $title;
    return $this;
  }

  /**
   * Get the profile Name.
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Set the Name of the profile.
   */
  public function setName($name)
  {
    $this->name = $name;
    return $this;
  }

  /**
   * Get the profile Name.
   */
  public function getDescription()
  {
    return $this->description;
  }

  /**
   * Set the Name of the profile.
   */
  public function setDescription($description)
  {
    $this->description = $description;
    return $this;
  }

  /**
   * Get the filepath.
   */
  public function getFilepath()
  {
    return $this->filepath;
  }

  /**
   * Set the Name of the profile.
   */
  public function setFilepath($filepath)
  {
    $this->filepath = $filepath;
    return $this;
  }

  /**
   * Add a Profile to the profile.
   */
  public function addInclude(Profile $profile)
  {
    $profile->setParent($this);
    $this->include[$profile->getName()] = $profile;
    foreach ($profile->getAllPolicyDefinitions() as $policy) {
      $this->addPolicyDefinition($policy);
    }
    return $this;
  }

  /**
   * Return a specific included profile.
   */
  public function getInclude($name)
  {
    return isset($this->include[$name]) ? $this->include[$name] : FALSE;
  }

  /**
   * Return an array of profiles included in this profile.
   */
  public function getIncludes()
  {
    return $this->include;
  }

  /**
   * Add a Profile to the profile.
   */
  public function setParent(Profile $parent)
  {
    // Ensure parent doesn't already have this profile loaded.
    // This prevents recursive looping.
    if (!$parent->getParent($this->getName())) {
      $this->parent = $parent;
      return $this;
    }
    throw new \Exception($this->getName() . ' already found in profile lineage.');
  }

  /**
   * Find a parent in the tree of parent profiles.
   */
  public function getParent($name = NULL)
  {
    if (!$this->parent) {
      return FALSE;
    }
    if ($name) {
      if ($this->parent->getName() == $name) {
        return $this->parent;
      }
      if ($parent = $this->parent->getInclude($name)) {
        return $parent;
      }
      // Recurse up the tree to find if the parent is in the tree.
      return $this->parent->getParent($name);
    }
    return $this->parent;
  }
}

 ?>
