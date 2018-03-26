<?php

namespace Drutiny\Report\Format;

use Drutiny\Report\Format;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class JSON extends Format {

  /**
   * The output location or method.
   *
   * @var string
   */
  protected $output;

  public function __construct($options) {
    $this->setFormat('json');
    if (isset($options['output'])) {
      $this->setOutput($options['output']);
    }
  }

  /**
   * Get the profile title.
   */
  public function getOutput()
  {
    return $this->output;
  }

  /**
   * Set the title of the profile.
   */
  protected function setOutput($filepath = 'stdout')
  {
    if ($filepath != 'stdout' && !file_exists(dirname($filepath))) {
      throw new \InvalidArgumentException("Cannot write to $filepath. Parent directory doesn't exist.");
    }
    $this->output = $filepath;
    return $this;
  }

  public function render($profile, $target, $result)
  {
    $report = new ProfileRunJsonReport($profile, $target, $result);
    $report->render();
  }
}

 ?>
