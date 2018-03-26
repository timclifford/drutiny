<?php

namespace Drutiny\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Drutiny\Profile\Registry;
use Drutiny\Profile;

/**
 *
 */
class ProfileListCommand extends Command {

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this
      ->setName('profile:list')
      ->setDescription('Show all profiles available.');
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $render = new SymfonyStyle($input, $output);

    $profiles = Registry::getAllProfiles();

    $render->table(['Profile', 'Name'], array_map(function ($profile) {
      return [$profile->getTitle(), $profile->getName()];
    }, $profiles));

    $render->note("Use drutiny profile:info to view more information about a profile.");
  }

}
