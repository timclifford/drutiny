<?php

namespace Drutiny\Docs;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drutiny\Registry;
use Drutiny\Profile\ProfileSource;
use Drutiny\PolicySource\PolicySource;
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
         ->generatePolicyAPI($output)
         ->generateProfileAPI($output)
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
      $docs = new AuditDocsGenerator();
      $namespace = explode('\\', $class);
      array_pop($namespace);
      $namespace = implode('\\', $namespace);
      $pages[$namespace][$class] = $docs->buildAuditDocumentation($class);
    }

    $nav = [];
    ksort($pages);
    foreach ($pages as $namespace => $list) {
      ksort($list);
      $filepath = 'audits/' . str_replace('\\', '', $namespace) . '.md';
      $nav_item = strtr($namespace, [
        'Drutiny\\' => '',
        'Audit\\' => '',
        'Audit' => '',
        '\\' => ' '
      ]);
      if (trim($nav_item) == '') {
        $nav_item = 'General';
      }
      $nav[] = [$nav_item => $filepath];
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
    $policies = PolicySource::loadAll();

    $toc = [];
    $pages = [];

    foreach ($policies as $policy) {
      $docs = new PolicyDocsGenerator();
      $package = $this->findPackage($policy->get('filepath'));
      $pages[$package][$policy->get('name')] = $docs->buildPolicyDocumentation($policy);
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

  protected function generatePolicyAPI(OutputInterface $output)
  {
    $policies = PolicySource::loadAll();
    $list = [];

    file_exists('docs/api') || mkdir('docs/api');
    file_exists('docs/api/policy') || mkdir('docs/api/policy');

    foreach ($policies as $policy) {
      $payload = $policy->export();
      $payload['signature'] = hash('sha1', Yaml::dump($payload));
      $list[] = [
        'title' => $payload['title'],
        'name' => $payload['name'],
        'type' => $payload['type'],
        'description' => $payload['description'],
        'signature' => $payload['signature'],
        'class' => $payload['class'],
        '_links' => [
          'self' => [
            'href' => "{baseUri}/api/policy/{$payload['name']}.json",
          ]
        ]
      ];
      file_put_contents("docs/api/policy/{$payload['name']}.json", json_encode($payload));
      $output->writeln("Written docs/api/policy/{$payload['name']}.json");
    }
    file_put_contents("docs/api/policy_list.json", json_encode($list));
    $output->writeln("Written docs/api/policy_list.json");
    return $this;
  }

  protected function generateProfileAPI(OutputInterface $output)
  {
    $profiles = ProfileSource::loadAll();
    $list = [];

    file_exists('docs/api') || mkdir('docs/api');
    file_exists('docs/api/profile') || mkdir('docs/api/profile');

    foreach ($profiles as $profile) {
      $payload = $profile->dump();
      if (!isset($payload['policies'])) {
        $payload['policies'] = [];
      }
      if (!isset($payload['description'])) {
        $payload['description'] = '';
      }
      $payload['signature'] = hash('sha1', Yaml::dump($payload));
      $list[] = [
        'title' => $payload['title'],
        'name' => $payload['name'],
        'description' => $payload['description'],
        'signature' => $payload['signature'],
        'policies' => array_keys($payload['policies']),
        '_links' => [
          'self' => [
            'href' => "{baseUri}/api/profile/{$payload['name']}.json",
          ]
        ]
      ];
      file_put_contents("docs/api/profile/{$payload['name']}.json", json_encode($payload));
      $output->writeln("Written docs/api/profile/{$payload['name']}.json");
    }
    file_put_contents("docs/api/profile_list.json", json_encode($list));
    $output->writeln("Written docs/api/profile_list.json");
    return $this;
  }

  protected function clean()
  {
    $paths = array_filter([
      'docs/policies',
      'docs/audits',
      'docs/api',
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
