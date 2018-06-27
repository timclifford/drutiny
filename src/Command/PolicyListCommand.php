<?php

namespace Drutiny\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Drutiny\Registry;
use Drutiny\Config;
use Drutiny\PolicySource\PolicySource;

/**
 *
 */
class PolicyListCommand extends Command {

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this
      ->setName('policy:list')
      ->setDescription('Show all policies available.')
      ->addOption(
        'filter',
        't',
        InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
        'Filter list by tag'
      );
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $list = PolicySource::getPolicyList();

    $rows = array();
    foreach ($list as $listedPolicy) {
      $rows[] = array(
        'description' => '<options=bold>' . wordwrap($listedPolicy['title'], 50) . '</>',
        'name' => $listedPolicy['name'],
        'source' => $listedPolicy['source'],
      );
    }

    usort($rows, function ($a, $b) {
      $x = [strtolower($a['name']), strtolower($b['name'])];
      sort($x, SORT_STRING);

      return $x[0] == strtolower($a['name']) ? -1 : 1;
    });

    $io = new SymfonyStyle($input, $output);
    $io->table(['Title', 'Name', 'Source'], $rows);
  }

  /**
   *
   */
  protected function formatDescription($text) {
    $lines = explode(PHP_EOL, $text);
    $text = implode(' ', $lines);
    return wordwrap($text, 50);
  }

}
