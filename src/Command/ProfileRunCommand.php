<?php

namespace Drutiny\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
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

  const EMOJI_REMEDIATION = "\xE2\x9A\xA0";

  protected $progressBar;

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
        'The target to run the checks against.'
      )
      ->addOption(
        'remediate',
        'r',
        InputOption::VALUE_NONE,
        'Allow failed checks to remediate themselves if available.'
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

    $policyDefinitions = $profile->getAllPolicyDefinitions();

    // Get the URLs.
    $uris = $input->getOption('uri');

    // Setup the progress bar to log updates.
    $steps = count($policyDefinitions) * count($uris);
    $progress = $this->getProgressBar($output, $steps);

    // Setup the target.
    $target = TargetRegistry::loadTarget($input->getArgument('target'));

    $results = [];

    foreach ($uris as $uri) {
      $target->setUri($uri);
      foreach ($policyDefinitions as $policyDefinition) {
        $policy = $policyDefinition->getPolicy();

        ($progress->log)("[$uri] " . $policy->get('title'));

        // Setup the sandbox to run the assessment.
        $sandbox = new Sandbox($target, $policy);
        $sandbox->setLogger(new ConsoleLogger($output));

        $response = $sandbox->run();

        // Attempt remediation.
        if (!$response->isSuccessful() && $input->getOption('remediate')) {
          ($progress->log)(self::EMOJI_REMEDIATION . " Remediating " . $policyDefinition->getTitle());
          $response = $sandbox->remediate();
        }
        $results[$uri][$policyDefinition->getName()] = $response;
        ($progress->advance)();
      }
    }

    ($progress->finish)();

    if (count($results) == 1) {
      $result = current($results);
      $format->render($profile, $target, $result);
    }
    else {
      $format->renderMultiple($profile, $target, $results);
    }
  }

  protected function getProgressBar(OutputInterface $output, $steps)
  {
    $progress = new ProgressBar($output, $steps);
    $progress->setFormatDefinition('custom', " <comment>%message%</comment>\n %current%/%max% <info>[%bar%]</info> %percent:3s%% %memory:6s%");
    $progress->setFormat('custom');
    $progress->setMessage("Starting...");
    $progress->setBarWidth(80);

    if ($output->getVerbosity() > OutputInterface::VERBOSITY_VERY_VERBOSE) {
      $progress = FALSE;
    }
    else {
      $progress->start();
    }

    $logger = new \stdClass;
    $logger->log = function ($msg) use ($progress)
    {
      $progress && $progress->setMessage($msg);
    };

    $logger->advance = function () use ($progress)
    {
      $progress && $progress->advance();
    };

    $logger->finish = function () use ($progress, $output)
    {
      $progress && $progress->setMessage("Done");
      $progress && $progress->finish();
      $progress && $output->writeln('');
    };

    return $logger;
  }

}
