<?php

namespace Drutiny\Sandbox;

use Drutiny\Audit;
use Drutiny\AuditInterface;
use Drutiny\AuditResponse\AuditResponse;
use Drutiny\Cache;
use Drutiny\Config;
use Drutiny\Container;
use Drutiny\Driver\Exec;
use Drutiny\Policy;
use Drutiny\Assessment;
use Drutiny\RemediableInterface;
use Drutiny\Target\Target;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Run check in an isolated environment.
 */
class Sandbox {
  use ParameterTrait;
  use ReportingPeriodTrait;

  /**
   * @var \Drutiny\Target\Target
   */
  protected $target;

  /**
   * @var \Drutiny\Audit
   */
  protected $audit;

  /**
   * @var \Drutiny\Policy
   */
  protected $policy;

  /**
   * @var \Drutiny\Assessment
   */
  protected $assessment;

  /**
   * Create a new Sandbox.
   *
   * @param string $target
   *   The class name of the target to create.
   * @param Policy $policy
   *
   * @throws \Exception
   */
  public function __construct(Target $target, Policy $policy, Assessment $assessment = NULL)
  {

    $this->target = $target;

    $class = $policy->get('class');
    $audit = new $class($this);
    if (!$audit instanceof AuditInterface) {
      throw new \InvalidArgumentException("$class is not a valid Audit class.");
    }
    $this->audit = $audit;
    $this->policy = $policy;

    // Default reporting period is last 24 hours to the nearest hour.
    $start = new \DateTime(date('Y-m-d H:i:s', strtotime('-24 hours')));
    $end   = clone $start;
    $end->add(new \DateInterval('PT24H'));
    $this->setReportingPeriod($start, $end);

    $this->assessment = isset($assessment) ? $assessment : new Assessment();
  }

  /**
   * Run the check and capture the outcomes.
   */
  public function run()
  {
    $response = new AuditResponse($this->getPolicy());
    $watchdog = Container::getLogger();

    $watchdog->info('Auditing ' . $this->getPolicy()->get('name'));
    try {
      // Ensure policy dependencies are met.
      foreach ($this->getPolicy()->getDepends() as $dependency) {
        // Throws DependencyException if dependency is not met.
        $dependency->execute($this);
      }

      // Run the audit over the policy.
      $outcome = $this->getAuditor()->execute($this);

      // Log the parameters output.
      $watchdog->debug("Tokens:\n" . Yaml::dump($this->getParameterTokens(), 4));

      // Set the response.
      $response->set($outcome, $this->getParameterTokens());
    }
    catch (\Drutiny\Policy\DependencyException $e) {
      $this->setParameter('exception', $e->getMessage());
      $response->set($e->getDependency()->getFailBehaviour(), $this->getParameterTokens());
    }
    catch (\Drutiny\AuditValidationException $e) {
      $this->setParameter('exception', $e->getMessage());
      $watchdog->warning($e->getMessage());
      $response->set(Audit::NOT_APPLICABLE, $this->getParameterTokens());
    }
    catch (\Exception $e) {
      $message = $e->getMessage();
      if (Container::getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
        $message .= PHP_EOL . $e->getTraceAsString();
      }
      $this->setParameter('exception', $message);
      $response->set(Audit::ERROR, $this->getParameterTokens());
    }

    // Omit irrelevant AuditResponses.
    if (!$response->isIrrelevant()) {
      $this->getAssessment()->setPolicyResult($response);
    }
    return $response;
  }

  /**
   * Remediate the check if available.
   */
  public function remediate()
  {
    $response = new AuditResponse($this->getPolicy());
    try {

      // Do not attempt remediation on checks that don't support it.
      if (!($this->getAuditor() instanceof RemediableInterface)) {
        throw new \Exception(get_class($this->getAuditor()) . ' is not remediable.');
      }

      // Make sure remediation does report false positives due to caching.
      Cache::purge();
      $outcome = $this->getAuditor()->remediate($this);
      $response->set($outcome, $this->getParameterTokens());
    }
    catch (\Exception $e) {
      $this->setParameter('exception', $e->getMessage());
      $response->set(Audit::ERROR, $this->getParameterTokens());
    }
    $this->getAssessment()->setPolicyResult($response);
    return $response;
  }

  /**
   *
   */
  public function getAuditor()
  {
    return $this->audit;
  }

  /**
   *
   */
  public function getPolicy()
  {
    return $this->policy;
  }

  public function getAssessment()
  {
    return $this->assessment;
  }

  /**
   *
   */
  public function getTarget()
  {
    return $this->target;
  }

  /**
   * A wrapper function for traits to use.
   */
  public function sandbox()
  {
    return $this;
  }

  /**
   * @param $method
   * @param $args
   * @return mixed
   * @throws \ErrorException
   */
  public function __call($method, $args)
  {
    $config = Config::get('Driver');
    if (!isset($config[$method])) {
      throw new \ErrorException("Unknown method $method on " . get_class($this));
    }
    array_unshift($args, $this);
    return call_user_func_array($config[$method], $args);
  }

  /**
   * Pull the logger from the Container.
   */
  public function logger()
  {
    return Container::getLogger();
  }

  /**
   * Execute a command against the Target.
   * @deprecated
   */
  public function exec()
  {
    $args = func_get_args();
    return call_user_func_array([$this->target, 'exec'], $args);
  }

  /**
   * Execute a local command.
   */
   public function localExec()
   {
     $args = func_get_args();
     $driver = new Exec();
     return call_user_func_array([$driver, 'exec'], $args);
   }
}
