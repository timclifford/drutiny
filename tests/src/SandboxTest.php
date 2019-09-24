<?php

namespace DrutinyTests\Audit;

use Drutiny\Container;
use Drutiny\Policy;
use Drutiny\Sandbox\Sandbox;
use Drutiny\Target\Registry as TargetRegistry;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class SandboxTest extends TestCase {

  protected $sandbox;

  public function __construct()
  {
    Container::setLogger(new NullLogger());
    $target = TargetRegistry::getTarget('none', '');
    $policy = Policy::load('Test:Pass');
    $this->sandbox = new Sandbox($target, $policy);
    parent::__construct();
  }

  public function testReportingPeriod()
  {
    $start = new \DateTimeImmutable('01-02-2000');
    $this->assertEquals($start->format('d-m-Y'), '01-02-2000');

    // Reporting period of 15 minute.
    $this->sandbox->setReportingPeriod($start, $start->add(new \DateInterval('PT15M')));
    $this->assertEquals($this->sandbox->getReportingPeriodInterval()->format('%i'), 15);
    $this->assertEquals($this->sandbox->getReportingPeriodDuration(), 900);
    $this->assertEquals($this->sandbox->getReportingPeriodSteps(), 30);

    // Reporting period of 1 hour.
    $this->sandbox->setReportingPeriod($start, $start->add(new \DateInterval('PT1H')));
    $this->assertEquals($this->sandbox->getReportingPeriodInterval()->format('%h'), 1);
    $this->assertEquals($this->sandbox->getReportingPeriodDuration(), 3600);
    $this->assertEquals($this->sandbox->getReportingPeriodSteps(), 60);

    // Reporting period of 6 hours.
    $this->sandbox->setReportingPeriod($start, $start->add(new \DateInterval('PT6H')));
    $this->assertEquals($this->sandbox->getReportingPeriodInterval()->format('%h'), 6);
    $this->assertEquals($this->sandbox->getReportingPeriodDuration(), 21600);
    $this->assertEquals($this->sandbox->getReportingPeriodSteps(), 300);

    // Reporting period of 24 hours.
    $this->sandbox->setReportingPeriodEnd($start->add(new \DateInterval('PT24H')));
    $this->assertEquals($this->sandbox->getReportingPeriodInterval()->format('%d'), 1);
    $this->assertEquals($this->sandbox->getReportingPeriodDuration(), 86400);
    $this->assertEquals($this->sandbox->getReportingPeriodSteps(), 900);

    // Reporting period of 3 days.
    $this->sandbox->setReportingPeriodEnd($start->add(new \DateInterval('P3D')));
    $this->assertEquals($this->sandbox->getReportingPeriodInterval()->format('%d'), 3);
    $this->assertEquals($this->sandbox->getReportingPeriodDuration(), 259200);
    $this->assertEquals($this->sandbox->getReportingPeriodSteps(), 3600);

    // Reporting period of 10 days, 5 hours and 20 minutes.
    $this->sandbox->setReportingPeriodEnd($start->add(new \DateInterval('P10DT5H20M')));
    $this->assertEquals($this->sandbox->getReportingPeriodDuration(), 883200);
    $this->assertEquals($this->sandbox->getReportingPeriodSteps(), 10800);

    // Assert the getReportingPeriodStart() function works as expected.
    $this->assertEquals($this->sandbox->getReportingPeriodStart(), $start);
    $this->assertEquals($this->sandbox->getReportingPeriodStart()->format('d-m-Y H:i:s'), $start->format('d-m-Y H:i:s'));
  }
}
