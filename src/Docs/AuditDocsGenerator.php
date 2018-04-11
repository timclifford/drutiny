<?php

namespace Drutiny\Docs;

use Drutiny\Registry;
use Symfony\Component\Yaml\Yaml;

class AuditDocsGenerator extends DocsGenerator {

  public function buildAuditDocumentation($audit_class)
  {
    $registry = new Registry();
    $metadata = $registry->getAuditMedtadata($audit_class);
    $metadata->source = FALSE;
    if ($metadata->reflect->hasMethod('audit')) {
      $method = $metadata->reflect->getMethod('audit');
      if ($method->getStartLine() != $method->getEndLine()) {
        $metadata->source = array_slice(file($method->getFilename()), $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1);
        $metadata->source = implode('', $metadata->source);
      }
    }

    $package = $this->findPackage($metadata->filename);

    $names = explode('\\', $metadata->class);

    $md = ['## ' . array_pop($names)];
    $md[] = $metadata->description;
    $md[] = '';
    $md[] = 'Class: `' . $metadata->class . '`  ';
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

    return implode(PHP_EOL, $md);
  }
}
