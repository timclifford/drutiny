<?php

namespace DrutinyTests\Audit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Drutiny\Command\ProfileRunCommand;
use Drutiny\Command\ProfileInfoCommand;
use Drutiny\Command\ProfileListCommand;
use Drutiny\Command\PolicyListCommand;
use Drutiny\Command\PolicyAuditCommand;
use Drutiny\Command\PolicyInfoCommand;
use Drutiny\Command\AuditRunCommand;

class ApplicationTest extends TestCase {

  public function testProfileRun()
  {
    $command = new ProfileRunCommand();
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
    $command = new ProfileListCommand();
    $input = new ArrayInput([]);
    $return_code = $command->run($input, $output = new BufferedOutput());

    $this->assertTrue(strlen($output->fetch()) > 0);
    $this->assertTrue($return_code === 0);
  }

  public function testProfileInfo()
  {
    $command = new ProfileInfoCommand();
    $input = new ArrayInput([
      'profile' => 'test',
    ]);
    $return_code = $command->run($input, $output = new BufferedOutput());

    $this->assertTrue(strlen($output->fetch()) > 0);
    $this->assertTrue($return_code === 0);
  }

  public function testPolicyList()
  {
    $command = new PolicyListCommand();
    $input = new ArrayInput([]);
    $return_code = $command->run($input, $output = new BufferedOutput());

    $this->assertTrue(strlen($output->fetch()) > 0);
    $this->assertTrue($return_code === 0);
  }

  public function testPolicyAudit()
  {
    $command = new PolicyAuditCommand();
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
    $command = new PolicyInfoCommand();
    $input = new ArrayInput([
      'policy' => 'Test:Pass',
    ]);
    $return_code = $command->run($input, $output = new BufferedOutput());

    $this->assertTrue(strlen($output->fetch()) > 0);
    $this->assertTrue($return_code === 0);
  }

  public function testAuditRun()
  {
    $command = new AuditRunCommand();
    $input = new ArrayInput([
      'audit' => 'Drutiny\Audit\AlwaysPass',
      'target' => '@none'
    ]);
    $return_code = $command->run($input, $output = new BufferedOutput());

    $this->assertTrue(strlen($output->fetch()) > 0);
    $this->assertTrue($return_code === 0);
  }
}
