<?php

namespace Drutiny\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;
use Drutiny\Profile\Registry as ProfileRegistry;
use Drutiny\Target\Registry as TargetRegistry;
use Drutiny\Sandbox\Sandbox;
use Drutiny\Logger\ConsoleLogger;
use Drutiny\Report;
use Drutiny\Target\Target;


/**
 *
 */
class ProfileRunCommand extends Command {

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this
      ->setName('profile:run')
      ->setDescription('Run a profile of checks against a target.')
      ->addArgument(
        'profile',
        InputArgument::REQUIRED,
        'The name of the profile to run.'
      )
      ->addArgument(
        'target',
        InputArgument::REQUIRED,
        'The target to run the policy collection against.'
      )
      ->addOption(
        'remediate',
        'r',
        InputOption::VALUE_NONE,
        'Allow failed policy aduits to remediate themselves if available.'
      )
      ->addOption(
        'format',
        'f',
        InputOption::VALUE_OPTIONAL,
        'Specify which output format to render the report (console, html, json). Defaults to console.',
        'console'
      )
      ->addOption(
        'uri',
        'l',
        InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
        'Provide URLs to run against the target. Useful for multisite installs. Accepts multiple arguments.',
        ['default']
      )
      ->addOption(
        'report-filename',
        'o',
        InputOption::VALUE_OPTIONAL,
        'For json and html formats, use this option to write report to file. Defaults to stdout.',
        'stdout'
      )
      ->addOption(
        'exclude-policy',
        'e',
        InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
        'Specify policy names to exclude from the profile that are normally listed.',
        []
      );
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {

    // Setup the check.
    $profile = ProfileRegistry::getProfile($input->getArgument('profile'));

    $filepath = $input->getOption('report-filename');

    // Setup the reporting format.
    $format = $profile->getFormatOption($input->getOption('format'), [
      'output' => $filepath != 'stdout' ? $filepath : $output,
      'input' => $input
    ]);

    // Allow command line omission of policies highlighted in the profile.
    // WARNING: This may remove policy dependants which may make polices behave
    // in strange ways.
    $excluded_policies = $input->getOption('exclude-policy');
    $policyDefinitions = array_filter($profile->getAllPolicyDefinitions(), function ($policy) use ($excluded_policies) {
      return !in_array($policy->getName(), $excluded_policies);
    });

    // Get the URLs.
    $uris = $input->getOption('uri');

    // Setup the progress bar to log updates.
    $steps = count($policyDefinitions) * count($uris);

    $progress = new _CommandProgressBar($output, $steps, ($filepath != 'stdout' || $format->getFormat() == 'console') && $output->getVerbosity() < OutputInterface::VERBOSITY_VERY_VERBOSE);

    // Setup the target.
    $target = TargetRegistry::loadTarget($input->getArgument('target'));

    $results = [];

    foreach ($uris as $uri) {
      $target->setUri($uri);
      foreach ($policyDefinitions as $policyDefinition) {
        $policy = $policyDefinition->getPolicy();

        $progress->log("[$uri] " . $policy->get('title'));

        // Setup the sandbox to run the assessment.
        $sandbox = new Sandbox($target, $policy);
        $sandbox->setLogger(new ConsoleLogger($output));

        $response = $sandbox->run();

        // Attempt remediation.
        if (!$response->isSuccessful() && $input->getOption('remediate')) {
          $progress->log("\xE2\x9A\xA0 Remediating " . $policy->get('title'));
          $response = $sandbox->remediate();
        }
        $results[$uri][$policyDefinition->getName()] = $response;
        $progress->advance();
      }
    }

    $progress->finish();

    $format->render($profile, $target, $results);

    if ($filepath = $input->getOption('report-filename')) {
      $console = new SymfonyStyle($input, $output);
      $console->success('Report written to ' . $filepath);
    }
  }
}

Class _CommandProgressBar {
  protected $status = TRUE;
  protected $bar;

  public function __construct($output, $steps, $enabled = TRUE)
  {
    $this->status = $enabled;
    $progress = new ProgressBar($output, $steps);
    $progress->setFormatDefinition('custom', " <comment>%message%</comment>\n %current%/%max% <info>[%bar%]</info> %percent:3s%% %memory:6s%");
    $progress->setFormat('custom');
    $progress->setMessage("Starting...");
    $progress->setBarWidth(80);
    $this->bar = $progress;
  }

  public function log($message)
  {
    if ($this->status) {
      $this->bar->setMessage($message);
    }
  }

  public function advance()
  {
    if ($this->status) {
      $this->bar->advance();
    }
  }

  public function finish()
  {
    if ($this->status) {
      $this->bar->setMessage("Done");
      $this->bar->finish();
      echo '';
    }
  }
}
