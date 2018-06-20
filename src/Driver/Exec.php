<?php

namespace Drutiny\Driver;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Drutiny\Cache;
use Drutiny\Container;

/**
 *
 */
class Exec {

  /**
   * @inheritdoc
   */
  public function exec($command, $args = []) {
    $args['%docroot'] = '';
    $command = strtr($command, $args);
    $watchdog = Container::getLogger();

    if ($output = Cache::get('exec', $command)) {
      $watchdog->debug("Cache hit for: $command");
      return $output;
    }

    $process = new Process($command);
    $process->setTimeout(600);

    $watchdog->info($command);
    $process->run();

    // Executes after the command finishes.
    if (!$process->isSuccessful()) {
      throw new ProcessFailedException($process);
    }

    $output = $process->getOutput();

    $watchdog->debug($output);
    Cache::set('exec', $command, $output);

    return $output;
  }

}
