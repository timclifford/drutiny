<?php

namespace Drutiny\Report;

use Drutiny\Profile;
use Drutiny\Target\Target;

abstract class Format {

  /**
   * The format the object applies to.
   *
   * @var string
   */
  protected $format;

  abstract public function __construct($options);

  public static function create($format, $options)
  {
    switch ($format) {
      case 'html':
        $format = new Format\HTML($options);
        break;
      case 'json':
        $format = new Format\JSON($options);
        break;
      case 'console':
        $format = new Format\Console($options);
        break;

      default:
        throw new \InvalidArgumentException("Reporting format '$format' is not supported.");
        break;
    }

    return $format;
  }

  /**
   * Get the profile title.
   */
  public function getFormat()
  {
    return $this->format;
  }

  /**
   * Set the title of the profile.
   */
  protected function setFormat($format)
  {
    $this->format = $format;
    return $this;
  }

  abstract public function render(Profile $profile, Target $target, array $result);

  abstract public function renderMultiple(Profile $profile, Target $target, array $results);
}

 ?>
