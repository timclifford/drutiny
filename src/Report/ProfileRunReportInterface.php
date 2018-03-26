<?php

namespace Drutiny\Report;

use Drutiny\Profile;
use Drutiny\Target\Target;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 */
interface ProfileRunReportInterface {

  /**
   * @param Profile $profile
   * @param Target $target
   * @param array $result_set
   */
  public function __construct(Profile $profile, Target $target, array $result_set);

  /**
   * @param InputInterface $input
   * @param OutputInterface $output
   * @return
   */
  public function render(InputInterface $input, OutputInterface $output);

}
