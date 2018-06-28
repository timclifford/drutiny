<?php

namespace Drutiny\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Drutiny\Cache\LocalFsCacheItemPool;

/**
 *
 */
class CacheClearCommand extends Command {

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this
      ->setName('cache:clear')
      ->setDescription('Clear the Drutiny cache')
      ;
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $cache = new LocalFsCacheItemPool('exec');
    $cache->clear();

    $cache = new LocalFsCacheItemPool('http');
    $cache->clear();

    $io = new SymfonyStyle($input, $output);
    $io->success('Cache is cleared.');
  }
}
