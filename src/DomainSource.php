<?php

namespace Drutiny;

use Symfony\Component\Console\Input\InputInterface;
use Drutiny\DomainList\DomainListRegistry;
use Drutiny\Target\Registry as TargetRegistry;

class DomainSource {

  public static function loadFromInput(InputInterface $input)
  {
    if (!$source = $input->getOption('domain-source')) {
      return FALSE;
    }

    $options = [];
    foreach ($input->getOptions() as $name => $value) {
      if (strpos($name, 'domain-source-' . $source) === FALSE) {
        continue;
      }
      $options[str_replace('domain-source-' . $source . '-', '', $name)] = $value;
    }
    $domain_loader = DomainListRegistry::loadFromInput($source, $options);

    $target = TargetRegistry::loadTarget($input->getArgument('target'));
    $domains = $domain_loader->getDomains($target);

    $whitelist = $input->getOption('domain-source-whitelist');
    $blacklist = $input->getOption('domain-source-blacklist');

    return array_filter($domains, function ($domain) use ($whitelist, $blacklist) {
      // Whitelist priority.
      if (!empty($whitelist)) {
        foreach ($whitelist as $regex) {
          if (preg_match("/$regex/", $domain)) {
            return TRUE;
          }
        }
        // Did not pass the whitelist.
        return FALSE;
      }
      if (!empty($blacklist)) {
        foreach ($blacklist as $regex) {
          if (preg_match("/$regex/", $domain)) {
            return FALSE;
          }
        }
      }
      return TRUE;
    });
  }
}

 ?>
