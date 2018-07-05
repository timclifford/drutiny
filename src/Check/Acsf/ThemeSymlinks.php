<?php

namespace Drutiny\Check\Acsf;

use Drutiny\Check\Check;
use Drutiny\AuditResponse\AuditResponse;
use Drutiny\Executor\DoesNotApplyException;

/**
 * @Drutiny\Annotation\CheckInfo(
 *  title = "ACSF theme symlinks",
 *  description = "Check symlinks in the theme repository don't link outside of the theme.",
 *  remediation = "Please review the detected symlinks for malicious intent and remove any offending links.",
 *  success = "No out of bounds symlinks found. :links",
 *  failure = "Symlinks found referencing files outside of the theme. :bad_links Other symlinks found: :links",
 *  exception = "Could not determine theme symlinks.",
 *  not_available = "No custom theme is linked.",
 * )
 */
class ThemeSymlinks extends Check {

  /**
   *
   */
  public function check() {
    $root = $this->context->drush->getCoreStatus('root');
    $site = $this->context->drush->getCoreStatus('site');

    $look_out_for = [
      "_POST",
      "exec(",
      "db_query",
      "db_select",
      "db_merge",
      "db_update",
      "db_write_record",
      "->query",
      "drupal_http_request",
      "curl_init",
      "passthru",
      "proc_open",
      "system(",
      "sleep(",
    ];

    // This command is probably more complex then it should be due to wanting to
    // remove the main theme folder prefix.
    //
    // Yields something like:
    //
    // ./zen/template.php:159:    $path = drupal_get_path_alias($_GET['q']);
    // ./zen/template.php:162:    $arg = explode('/', $_GET['q']);.
    $command = "
    set -e
    theme_dir=`realpath {$root}/{$site}/themes/site/`;
    echo \$theme_dir;
    find \$theme_dir -type l -exec realpath {} \; || echo 'nolinks'";
    $code = base64_encode(trim($command));
    $command = "echo $code | base64 --decode | sh";

    $output = (string) $this->context->remoteExecutor->execute($command);

    if (preg_match('/^nolinks$/', $output)) {
      return AuditResponse::AUDIT_SUCCESS;
    }

    // Output from find is a giant string with newlines to seperate the files.
    $rows = explode("\n", $output);
    $rows = array_map('trim', $rows);
    //$rows = array_map('strip_tags', $rows);
    $rows = array_filter($rows);

    $theme_dir = array_shift($rows);
    $bad_links = array_filter($rows, function ($link) use ($theme_dir) {
      return strpos($link, $theme_dir) !== 0;
    });
    $this->setToken('bad_links', '<ul><li>' . implode('</li><li>', $bad_links) . '</li></ul>');

    $links = array_filter($rows, function ($link) use ($theme_dir) {
      return strpos($link, $theme_dir) !== FALSE;
    });
    $this->setToken('links', '<ul><li>' . implode('</li><li>', $links) . '</li></ul>');

    return count($bad_links) === 0;
  }

}
