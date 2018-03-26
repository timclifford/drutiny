<?php

namespace Drutiny\Report\Format;

use Drutiny\Report\ProfileRunReport;
use Drutiny\Report\Format;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class Console extends Format {

  /**
   * The output location or method.
   *
   * @var string
   */
  protected $output;

  /**
   * The output location or method.
   *
   * @var string
   */
  protected $input;

  public function __construct($options) {
    $this->setFormat('console');
    if (!isset($options['output'])) {
      throw new InvalidArgumentException("Console format requires a Symfony\Component\Console\Output\OutputInterface.");
    }
    if (!($options['output'] instanceof OutputInterface)) {
      throw new InvalidArgumentException("Console format requires a Symfony\Component\Console\Output\OutputInterface.");
    }
    $this->output = $options['output'];

    if (!isset($options['input'])) {
      throw new InvalidArgumentException("Console format requires a Symfony\Component\Console\Input\InputInterface.");
    }
    if (!($options['input'] instanceof InputInterface)) {
      throw new InvalidArgumentException("Console format requires a Symfony\Component\Console\Input\InputInterface.");
    }
    $this->input = $options['input'];
  }

  /**
   * Get the profile title.
   */
  public function getOutput()
  {
    return $this->output;
  }

  public function render($profile, $target, $result)
  {
    $report = new ProfileRunReport($profile, $target, $result);
    $report->render($this->input, $this->output);
  }
}

 ?>
