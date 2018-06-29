<?php

namespace Drutiny\Command;

use Drutiny\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Cache\Adapter\FilesystemAdapter as Cache;

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
    $cache = new Cache('exec', 0, DRUTINY_CACHE_DIRECTORY);
    $cache->setLogger(Container::getLogger());
    $cache->clear();

    $cache = new Cache('http', 0, DRUTINY_CACHE_DIRECTORY);
    $cache->setLogger(Container::getLogger());
    $cache->clear();

    $io = new SymfonyStyle($input, $output);
    $io->success('Cache is cleared.');
  }
}
