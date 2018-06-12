<?php

namespace DrutinyTests\Audit;

use Drutiny\Container;
use Drutiny\Policy;
use Drutiny\Sandbox\Sandbox;
use Drutiny\Target\Registry as TargetRegistry;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class PolicyTest extends TestCase {

  protected $registry;
  protected $targetClass;

  public function __construct()
  {
    Container::setLogger(new NullLogger());
    $this->target = TargetRegistry::getTarget('none', '');
    parent::__construct();
  }

  public function testPass()
  {
    $policy = Policy::load('Test:Pass');
    $sandbox = new Sandbox($this->target, $policy);

    $response = $sandbox->run();
    $this->assertTrue($response->isSuccessful());
  }

  public function testFail()
  {
    $policy = Policy::load('Test:Fail');
    $sandbox = new Sandbox($this->target, $policy);

    $response = $sandbox->run();
    $this->assertFalse($response->isSuccessful());
  }

  public function testError()
  {
    $policy = Policy::load('Test:Error');
    $sandbox = new Sandbox($this->target, $policy);

    $response = $sandbox->run();
    $this->assertFalse($response->isSuccessful());
    $this->assertTrue($response->hasError());
  }

  public function testWarning()
  {
    $policy = Policy::load('Test:Warning');
    $sandbox = new Sandbox($this->target, $policy);

    $response = $sandbox->run();
    $this->assertTrue($response->isSuccessful());
    $this->assertTrue($response->hasWarning());
  }

  public function testNotApplicable()
  {
    $policy = Policy::load('Test:NA');
    $sandbox = new Sandbox($this->target, $policy);

    $response = $sandbox->run();
    $this->assertFalse($response->isSuccessful());
    $this->assertTrue($response->isNotApplicable());
  }

  public function testNotice()
  {
    $policy = Policy::load('Test:Notice');
    $sandbox = new Sandbox($this->target, $policy);

    $response = $sandbox->run();
    $this->assertTrue($response->isSuccessful());
    $this->assertTrue($response->isNotice());
  }

}
