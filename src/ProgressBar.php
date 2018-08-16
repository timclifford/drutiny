<?php

namespace Drutiny;

use Symfony\Component\Console\Helper\ProgressBar as SymfonyProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Drutiny\Container;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * LoggerInterface wrapper around the Symfony ProgressBar.
 */
Class ProgressBar extends AbstractLogger {

  /**
   * Whether the progress bar is rendering or not.
   */
  protected $status = TRUE;

  /**
   * @var \Symfony\Component\Console\Helper\ProgressBar.
   */
  protected $bar;

  /**
   * @var string.
   */
  protected $topic;

  /**
   * @var Symfony\Component\Console\Output\OutputInterface.
   */
  protected $output;

  /**
   * @var int
   */
  protected $steps;

  public function __construct(OutputInterface $output, $steps)
  {
    $this->output = $output;
    $this->steps  = $steps;
  }

  public function start()
  {
    if ($this->status) {
      $this->bar()->start();
    }
    return $this;
  }

  protected function bar()
  {
    if (empty($this->bar)) {
      $progress = new SymfonyProgressBar($this->output, $this->steps);
      $progress->setFormatDefinition('custom', " <comment>%message%</comment>\n %current%/%max% <info>[%bar%]</info> %percent:3s%% %memory:6s%");
      $progress->setFormat('custom');
      $progress->setMessage("Starting...");
      $progress->setBarWidth(80);
      $this->bar = $progress;

      if ($this->status) {
        Container::setLogger($this);
      }
    }

    return $this->bar;
  }

  /**
   * Prevent the ProgressBar from continuing to function.
   */
  public function disable()
  {
    if ($this->status) {
      $this->status = FALSE;
      !empty($this->bar) && $this->bar()->finish();
      Container::setLogger(new ConsoleLogger($this->output));
    }
    return $this;
  }

  /**
   * Set the topic for which each subsequent log message is in context too.
   */
  public function setTopic($topic)
  {
    $this->topic = $topic;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = array())
  {
    $lines = explode(PHP_EOL, $message);
    $message = array_shift($lines);
    if (count($lines) > 1) {
      $message .= '... (showing 1 line of ' . count($lines) . ')';
    }
    if ($this->status && ($level != LogLevel::DEBUG)) {
      $level = strtoupper($level);
      $this->bar()->setMessage("[{$this->topic}][$level] " . $message);
      $this->bar()->display();
    }
    return $this;
  }

  /**
   * Advance() wrapper.
   */
  public function advance($step = 1)
  {
    if ($this->status) {
      $this->bar()->advance($step);
    }
    return $this;
  }

  /**
   * Close up the progress bar.
   */
  public function finish()
  {
    if ($this->status) {
      $this->bar()->setMessage("Done");
      $this->disable();
      echo '';
    }
    return $this;
  }
}
