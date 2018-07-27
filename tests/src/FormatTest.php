<?php

namespace DrutinyTests\Audit;

use Drutiny\Assessment;
use Drutiny\Container;
use Drutiny\Profile\ProfileSource;
use Drutiny\Sandbox\Sandbox;
use Drutiny\Target\Registry as TargetRegistry;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class FormatTest extends TestCase {

  protected $assessment;
  protected $profile;
  protected $target;

  public function __construct()
  {
    Container::setLogger(new NullLogger());
    $target = TargetRegistry::getTarget('none', '');
    $profile = ProfileSource::loadProfileByName('test');

    $policies = [];
    foreach ($profile->getAllPolicyDefinitions() as $policyDefinition) {
      $policies[] = $policyDefinition->getPolicy();
    }
    $assessment = new Assessment();
    $assessment->assessTarget($target, $policies);

    $this->profile = $profile;
    $this->assessment = $assessment;
    $this->target = $target;

    parent::__construct();
  }

  public function testConsoleFormatException()
  {
    $this->expectException(InvalidArgumentException::class);
    $format = $this->profile->getFormatOption('console');
  }

  public function testConsoleFormat()
  {
    $format = $this->profile->getFormatOption('console', [
      'output' => new BufferedOutput(),
      'input' => new ArrayInput([])
    ]);
    $filepaths = $format->render($this->profile, $this->target, [$this->assessment]);
    $this->assertEmpty($filepaths);
    $this->assertTrue(is_array($filepaths));
  }

  public function testHtmlFormat()
  {
    $buffer = new BufferedOutput();
    $format = $this->profile->getFormatOption('html', [
      'output' => $buffer,
    ]);
    $filepaths = $format->render($this->profile, $this->target, [$this->assessment]);
    $this->assertEmpty($filepaths);

    $output = $buffer->fetch();
    $this->assertTrue(strlen($output) > 0);
  }

  public function testMarkdownFormat()
  {
    $buffer = new BufferedOutput();
    $format = $this->profile->getFormatOption('markdown', [
      'output' => $buffer,
    ]);
    $filepaths = $format->render($this->profile, $this->target, [$this->assessment]);
    $this->assertEmpty($filepaths);

    $output = $buffer->fetch();
    $this->assertTrue(strlen($output) > 0);
  }

  public function testJsonFormat()
  {
    $buffer = new BufferedOutput();
    $format = $this->profile->getFormatOption('json', [
      'output' => $buffer,
    ]);
    $filepaths = $format->render($this->profile, $this->target, [$this->assessment]);
    $this->assertEmpty($filepaths);

    $output = json_decode($buffer->fetch());
    $this->assertTrue(is_object($output));
  }
}
