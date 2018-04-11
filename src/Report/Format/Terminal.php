<?php

namespace Drutiny\Report\Format;

use Drutiny\Profile;
use Drutiny\Registry;
use Drutiny\Target\Target;
use Fiasco\SymfonyConsoleStyleMarkdown\Renderer;
use Symfony\Component\Yaml\Yaml;

class Terminal extends Markdown {
  public function render(Profile $profile, Target $target, array $result)
  {
    $markdown = parent::render($profile, $target, $result);
    return (string) Renderer::createFromMarkdown($markdown);
  }

  public function renderMultiple(Profile $profile, Target $target, array $results)
  {
    $markdown = parent::renderMultiple($profile, $target, $results);
    return (string) Renderer::createFromMarkdown($markdown);
  }
}

 ?>
