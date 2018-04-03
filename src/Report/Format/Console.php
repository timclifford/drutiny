<?php

namespace Drutiny\Report\Format;

use Drutiny\AuditResponse\AuditResponse;
use Drutiny\Profile;
use Drutiny\Report\Format;
use Drutiny\Target\Target;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


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

  public function render(Profile $profile, Target $target, array $result)
  {
    $io = new SymfonyStyle($this->input, $this->output);
    $io->title($profile->getTitle());

    $table_rows = [];
    $pass = [];
    foreach ($result as $response) {
      $pass[] = $response->isSuccessful();

      $summary = [];
      foreach (explode(PHP_EOL, $response->getSummary()) as $line) {
        if (strlen($line) < 100) {
          $summary[] = $line;
          continue;
        }
        $words = explode(' ', $line);
        $phrase = [];
        while ($word = array_shift($words)) {
          $compound_line = $phrase;
          $compound_line[] = $word;
          if (strlen(implode(' ', $compound_line)) > 100) {
            $summary[] = implode(' ', $phrase);
            $phrase = ["    "];
          }
          $phrase[] = $word;
        }
        $summary[] = implode(' ', $phrase);
      }
      $summary = implode(PHP_EOL, $summary);

      $table_rows[] = [
        $this->getIcon($response),
        $response->getTitle(),
        $response->getSeverity(),
        $summary . (
          $response->isSuccessful() ? '' : PHP_EOL . PHP_EOL . $response->getRemediation()
        ),
      ];
      $table_rows[] = new TableSeparator();
    }

    $total_tests = count($result);
    $total_pass = count(array_filter($pass));
    $table_rows[] = ['', "$total_pass/$total_tests passed", ''];
    $io->table(['', 'Policy', 'Severity', 'Summary'], $table_rows);
    return '';
  }

  public function renderMultiple(Profile $profile, Target $target, array $results)
  {
    $io = new SymfonyStyle($this->input, $this->output);

    // Set results by policy rather than by site.
    $resultsByPolicy = [];
    foreach ($results as $uri => $siteReport) {
      foreach ($siteReport as $response) {
        $resultsByPolicy[$response->getName()][$uri] = $response;
      }
    }

    $table_rows = [];

    foreach ($resultsByPolicy as $policy => $results) {
      $failed = array_filter($results, function (AuditResponse $response) {
        return !$response->isSuccessful();
      });

      $pass = bcsub(count($results), count($failed));
      $pass_rate = round(bcdiv($pass, count($results)) * 100);

      $policyInfo = reset($results);
      $table_rows[] = [
        '<options=bold>' . $policyInfo->getTitle() . '</>',
        $pass_rate . '% passed'
      ];
      $table_rows[] = [new TableCell($policyInfo->getDescription(), [
        'rowspan' => 2
        ])];

      foreach ($failed as $uri => $response) {
        $table_rows[] = ['- ' . $uri, ''];
      }

      $table_rows[] = new TableSeparator();
    }

    // Remove last table seperator
    array_pop($table_rows);

    $io->title($profile->getTitle());
    $io->table([], $table_rows);
    return '';
  }

  protected function getIcon(AuditResponse $response)
  {
    if ($response->isNotice()) {
      return "ℹ️";
    }
    elseif ($response->hasWarning()) {
      return "⚠️";
    }
    else {
      return $response->isSuccessful() ? "✅" : "❌";
    }
  }
}

 ?>
