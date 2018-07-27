<?php

namespace DrutinyTests\Audit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class ApplicationTest extends TestCase {

  public function testProfileRun()
  {
    $command = new \Drutiny\Command\ProfileRunCommand();
    $input = new ArrayInput([
      'profile' => 'test',
      'target' => '@none'
    ]);
    $return_code = $command->run($input, $output = new BufferedOutput());

    $this->assertTrue(strlen($output->fetch()) > 0);
    $this->assertTrue($return_code === 0);
  }

  public function testProfileList()
  {
    $command = new \Drutiny\Command\ProfileListCommand();
    $input = new ArrayInput([]);
    $return_code = $command->run($input, $output = new BufferedOutput());

    $this->assertTrue(strlen($output->fetch()) > 0);
    $this->assertTrue($return_code === 0);
  }

  public function testProfileInfo()
  {
    $command = new \Drutiny\Command\ProfileInfoCommand();
    $input = new ArrayInput([
      'profile' => 'test',
    ]);
    $return_code = $command->run($input, $output = new BufferedOutput());

    $this->assertTrue(strlen($output->fetch()) > 0);
    $this->assertTrue($return_code === 0);
  }

  public function testPolicyList()
  {
    $command = new \Drutiny\Command\PolicyListCommand();
    $input = new ArrayInput([]);
    $return_code = $command->run($input, $output = new BufferedOutput());

    $this->assertTrue(strlen($output->fetch()) > 0);
    $this->assertTrue($return_code === 0);
  }

  public function testPolicyAudit()
  {
    $command = new \Drutiny\Command\PolicyAuditCommand();
    $input = new ArrayInput([
      'policy' => 'Test:Pass',
      'target' => '@none'
    ]);
    $return_code = $command->run($input, $output = new BufferedOutput());

    $this->assertTrue(strlen($output->fetch()) > 0);
    $this->assertTrue($return_code === 0);
  }

  public function testPolicyInfo()
  {
    $command = new \Drutiny\Command\PolicyInfoCommand();
    $input = new ArrayInput([
      'policy' => 'Test:Pass',
    ]);
    $return_code = $command->run($input, $output = new BufferedOutput());

    $this->assertTrue(strlen($output->fetch()) > 0);
    $this->assertTrue($return_code === 0);
  }

  public function testAuditRun()
  {
    $command = new \Drutiny\Command\AuditRunCommand();
    $input = new ArrayInput([
      'audit' => 'Drutiny\Audit\AlwaysPass',
      'target' => '@none'
    ]);
    $return_code = $command->run($input, $output = new BufferedOutput());

    $this->assertTrue(strlen($output->fetch()) > 0);
    $this->assertTrue($return_code === 0);
  }
}
