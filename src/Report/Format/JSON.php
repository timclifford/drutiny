<?php

namespace Drutiny\Report\Format;

use Drutiny\Report\Format;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class JSON extends Format {

  /**
   * The output location or method.
   *
   * @var string
   */
  protected $output;

  public function __construct($options) {
    $this->setFormat('json');
    if (isset($options['output'])) {
      $this->setOutput($options['output']);
    }
  }

  /**
   * Get the profile title.
   */
  public function getOutput()
  {
    return $this->output;
  }

  /**
   * Set the title of the profile.
   */
  protected function setOutput($filepath = 'stdout')
  {
    if ($filepath != 'stdout' && !($filepath instanceof OutputInterface) && !file_exists(dirname($filepath))) {
      throw new \InvalidArgumentException("Cannot write to $filepath. Parent directory doesn't exist.");
    }
    $this->output = $filepath;
    return $this;
  }

  public function render($profile, $target, $result)
  {
    $schema = [
      'notices' => 0,
      'warnings' => 0,
      'failures' => 0,
      'passes' => 0,
      'errors' => 0,
      'not_applicable' => 0,
      'remediated' => 0,
      'total' => 0,
    ];

    // Report Title.
    $schema['title'] = $profile->getTitle();
    $schema['domain'] = $target->uri();
    $schema['summary'] = $target->uri();
    $schema['description'] = $profile->getDescription();
    $schema['remediations'] = [];
    $outcomes = [
      'success' => 0,
      'failure' => 0,
      'warning' => 0,
      'error' => 0,
      'not_applicable' => 0,
    ];
    $schema['stats'] = [
      'critical' => $outcomes,
      'high' => $outcomes,
      'normal' => $outcomes,
      'low' => $outcomes,
      'none' => $outcomes,
    ];

    foreach ($result as $response) {
      $var = [
        'status' => $response->isSuccessful(),
        'is_notice' => $response->isNotice(),
        'has_warning' => $response->hasWarning(),
        'has_error' => $response->hasError(),
        'is_not_applicable' => $response->isNotApplicable(),
        'title' => $response->getTitle(),
        'description' => $response->getDescription(),
        'remediation' => $response->getRemediation(),
        'success' => $response->getSuccess(),
        'failure' => $response->getFailure(),
        'warning' => $response->getWarning(),
        'type' => $response->getType(),
        'severity' => $response->getSeverity(),
      ];

      $schema['total']++;

      switch ($response->getType()) {
        case 'data':
        case 'notice':
          $schema['notices']++;
          $var['status_title'] = 'Notice';
          break;

        case 'error':
          $schema['errors']++;
          $var['status_title'] = 'Error';
          $schema['stats'][$var['severity']]['error']++;
          break;

        case 'not-applicable':
          $schema['not_applicable']++;
          $var['status_title'] = 'Not Applicable';
          $schema['stats'][$var['severity']]['not_applicable']++;
          break;

        case 'warning':
          $schema['warnings']++;
          $var['status_title'] = 'Warning';
          $schema['stats'][$var['severity']]['warning']++;
          break;

        case 'success':
          $schema['passes']++;
          if ($response->isRemediated()) {
            $schema['remediated']++;
            $var['status_title'] = 'Remediated';
          }
          else {
            $var['status_title'] = 'Passed';
          }
          $schema['stats'][$var['severity']]['success']++;
          break;

        case 'failure':
          $schema['failures']++;
          $var['status_title'] = 'Failed';
          $schema['remediations'][] = $response->getRemediation();
          $schema['stats'][$var['severity']]['failure']++;
          break;
      }
      $schema['results'][] = $var;
    }

    $schema['stats'] = array_filter($schema['stats'], function ($a) {
      return count(array_filter($a));
    });

    foreach ($schema['stats'] as $severity => $results) {
      $schema['totals'][$severity] = array_sum($results);
    }

    return $schema;
  }
}

 ?>
