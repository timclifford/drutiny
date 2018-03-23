<?php

namespace Drutiny\Docs;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drutiny\Registry;
use Symfony\Component\Finder\Finder;

/**
 * Helper for building checks.
 */
class BuildDocsCommand extends Command {

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this
      ->setName('docs:build')
      ->setDescription('Build docs from source code annotations.');
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->clean()
         ->setup()
         ->buildPolicyLibrary($output)
         ->buildAuditLibrary($output);
  }

  protected function buildAuditLibrary(OutputInterface $output)
  {
    $registry = new Registry();
    $finder = new Finder();
    $finder->files()
      ->in('.')
      ->path('/\/Audit\//')
      ->files()
      ->name('*.php');

    foreach ($finder as $file) {
      require_once $file->getPathname();
    }

    $auditors = array_filter(get_declared_classes(), function ($class) {
      return is_subclass_of($class, '\Drutiny\Audit');
    });

    $pages = [];
    foreach ($auditors as $class) {
      $metadata = $registry->getAuditMedtadata($class);
      $package = $this->findPackage($metadata->filename);

      $md = ['## ' . $metadata->class];
      $md[] = $metadata->description;
      $md[] = '';
      $md[] = 'Extends: `' . $metadata->extends . '`  ';
      $md[] = 'Package: `' . $package . '`';
      $md[] = '';


      if ($metadata->remediable) {
        $md[] = 'This class can **remediate** failed audits.';
        $md[] = '';
      }

      if ($metadata->isAbstract || $metadata->reflect->getMethod('audit')->isAbstract()) {
        $md[] = '**NOTE**: This Audit is **abstract** and cannot be used directly by a policy.';
        $md[] = '';
      }


      $policies = array_filter($registry->policies(), function ($policy) use ($metadata) {
        $class = $policy->get('class');
        if (strpos($class, '\\') === 0) {
          $class = substr($class, 1);
        }
        return $class == $metadata->class;
      });

      if (!empty($policies)) {
        $md[] = '### Policies';
        $md[] = 'These are the policies that use this class:';
        $md[] = '';
        $md[] = 'Name | Title';
        $md[] = '-- | --';
        foreach ($policies as $policy) {
          $md[] = strtr('name | title', [
            'name' => $policy->get('name'),
            'title' => $policy->get('title'),
          ]);
        }

      }

      if (!empty($metadata->params)) {
        $md[] = '';
        $md[] = '### Parameters';
        $md[] = 'Name | Type | Description | Default';
        $md[] = '-- | -- | -- | --';

        foreach ($metadata->params as $name => $param) {
          // $params may not correctly conform so this it just to prevent the
          // php notices.
          $param = array_merge([
            'type' => '',
            'description' => '',
            'default' => '',
          ], (array) $param);

          $md[] = strtr('Name | Type | Description | Default', [
            'Name' => $name,
            'Type' => $param['type'],
            'Description' => $param['description'],
            'Default' => str_replace(PHP_EOL, '<br>', Yaml::dump($param['default'])),
          ]);
        }
      }

      if (!empty($metadata->tokens)) {
        $md[] = '';
        $md[] = '### Tokens';
        $md[] = 'Name | Type | Description | Default';
        $md[] = '-- | -- | -- | --';

        foreach ($metadata->tokens as $name => $param) {
          // $params may not correctly conform so this it just to prevent the
          // php notices.
          $param = array_merge([
            'type' => '',
            'description' => '',
            'default' => '',
          ], (array) $param);

          $md[] = strtr('Name | Type | Description | Default', [
            'Name' => $name,
            'Type' => $param['type'],
            'Description' => $param['description'],
            'Default' => str_replace(PHP_EOL, '<br>', Yaml::dump($param['default'])),
          ]);
        }
      }

      if ($metadata->source) {
        $md[] = '';
        $md[] = '#### Source';
        $md[] = '```php';
        $md[] = $metadata->source;
        $md[] = '```';
      }

      $namespace = explode('\\', $metadata->class);
      array_pop($namespace);
      $namespace = implode('\\', $namespace);
      $pages[$namespace][$metadata->class] = implode(PHP_EOL, $md);
    }

    $nav = [];
    foreach ($pages as $namespace => $list) {
      ksort($list);
      $filepath = 'audits/' . str_replace('\\', '', $namespace) . '.md';
      $nav[] = [$namespace => $filepath];
      file_put_contents("docs/$filepath", implode("\n\n", $list));
      $output->writeln("Written docs/$filepath.");
    }

    $mkdocs = Yaml::parse(file_get_contents('mkdocs.yml'));
    $mkdocs['pages'][4] = ['Audit Library' => $nav];
    file_put_contents('mkdocs.yml', Yaml::dump($mkdocs, 6));
    $output->writeln("Updated mkdocs.yml");

    return $this;
  }

  protected function buildPolicyLibrary(OutputInterface $output)
  {
    $policies = (new \Drutiny\Registry())->policies();

    $toc = [];
    $pages = [];

    foreach ($policies as $policy) {
      $filepath = $policy->get('filepath');
      $package = $this->findPackage($filepath);

      $md = [];
      $md[] = $link = strtr('## title', [
        'title' => $policy->get('title'),
        'name' => $policy->get('name'),
      ]);

      $toc[$policy->get('title')] = [
        'title' => $policy->get('title'),
        'name' => $policy->get('name'),
        'package' => $package,
        'link' => str_replace(' ', '-', trim(preg_replace('/[^a-z0-9 \-]/', '', strtolower($link)))),
      ];

      $md[] = "**Name**: `" . $policy->get('name') . "`  ";
      $md[] = "**Package**: `$package`  ";
      $md[] = "**Class**: `" . $policy->get('class') . "`";
      $md[] = '';
      $md[] = $policy->get('description');

      $audit = (new \Drutiny\Registry)->getAuditMedtadata($policy->get('class'));

      $params = $policy->get('parameters');
      foreach ($audit->params as $param) {
        if (isset($params[$param->name])) {
          $params[$param->name] = array_merge($param->toArray(), $params[$param->name]);
        }
      }

      if (!empty($params)) {
        $md[] = '';
        $md[] = '### Parameters';
        $md[] = 'Name | Type | Description | Default';
        $md[] = '-- | -- | -- | --';

        foreach ($params as $name => $param) {
          // $params may not correctly conform so this it just to prevent the
          // php notices.
          $param = array_merge([
            'type' => '',
            'description' => '',
            'default' => '',
          ], $param);
          $md[] = strtr('Name | Type | Description | Default', [
            'Name' => $name,
            'Type' => $param['type'],
            'Description' => $param['description'],
            'Default' => str_replace(PHP_EOL, '<br>', Yaml::dump($param['default'])),
          ]);
        }
      }

      $tokens = $policy->get('tokens');
      if (!empty($tokens)) {
        $md[] = '';
        $md[] = '### Tokens';
        $md[] = 'Name | Type | Description | Default';
        $md[] = '-- | -- | -- | --';

        foreach ($tokens as $name => $token) {
          $md[] = strtr('Name | Type | Description | Default', [
            'Name' => $name,
            'Type' => $token['type'],
            'Description' => $token['description'],
            'Default' => $token['default'],
          ]);
        }
      }

      $pages[$package][$policy->get('name')] = implode(PHP_EOL, $md);
    }

    $nav = [['Overview' => 'policy-library.md']];
    foreach ($pages as $package => $list) {
      ksort($list);

      $filepath = 'policies/' . str_replace('/', '-', $package) . '.md';
      file_put_contents("docs/$filepath", implode("\n\n", $list));
      $output->writeln("Written docs/$filepath.");
      $nav[] = [$package => $filepath];
    }

    $md = ['# Policy Library'];
    $md[] = '';
    $md[] = 'Title | Name | Package';
    $md[] = '-- | -- | --';
    ksort($toc);
    foreach ($toc as $item) {
      $filepath = 'policies/' . str_replace('/', '-', $item['package']) . '.md';
      $md[] = "[{$item['title']}]($filepath#{$item['link']}) | {$item['name']} | [{$item['package']}](https://github.com/{$item['package']})";
    }
    file_put_contents('docs/policy-library.md', implode(PHP_EOL, $md));

    $mkdocs = Yaml::parse(file_get_contents('mkdocs.yml'));
    $mkdocs['pages'][3] = ['Policy Library' => $nav];
    file_put_contents('mkdocs.yml', Yaml::dump($mkdocs, 6));
    $output->writeln("Updated mkdocs.yml");
    return $this;
  }

  protected function clean()
  {
    $paths = array_filter([
      'docs/policies',
      'docs/audits',
      'docs/img',
      'docs/index.md'
    ], 'file_exists');

    foreach ($paths as $path) {
      exec('rm -rf ' . $path);
    }
    return $this;
  }

  protected function setup()
  {
    copy('README.md', 'docs/index.md');
    mkdir('docs/policies');
    mkdir('docs/audits');
    mkdir('docs/img');
    copy('assets/favicon.ico', 'docs/img/favicon.ico');
    return $this;
  }

  protected function findPackage($filepath)
  {
      $composer = FALSE;
      while ($filepath) {
        $filepath = dirname($filepath);

        if (file_exists($filepath . '/composer.json')) {
          break;
        }
      }

      $json = file_get_contents($filepath . '/composer.json');
      $composer = json_decode($json, TRUE);
      return $composer['name'];
  }

}
