# Domain Providers

Domain Providers allow Drutiny plugins to provide their own methods for pulling
domains to use over a multisite target. Domain Providers essentially supply the
domains to use in the `--uri` parameter in `profile:run`.

To write your own domain provider you need to do two things:

1. Write a class that implements the `Drutiny\DomainList\DomainListInterface` interface.
2. Register the class in drutiny.config.yml

## Implementing the DomainListInterface

The DomainListInterface requires two methods: `__construct` and `getDomains`.

`__construct` is passed in the metadata required for the class to acquire the
domains it provides and `getDomains` returns an array of domains to use.

As an example, here is the default Yaml filepath domain loader which reads in
a list of domains from a Yaml file.

```php
namespace Drutiny\DomainList;

use Symfony\Component\Yaml\Yaml;
use Drutiny\Target\Target;

class DomainListYamlFile implements DomainListInterface {

  protected $filepath;

  public function __construct(array $metadata)
  {
    $this->filepath = $metadata['filepath'];
  }

  /**
   * @return array list of domains.
   */
  public function getDomains(Target $target)
  {
    return Yaml::parseFile($this->filepath);
  }
}
```

## Specifying required metadata
You domain provider may require metadata such as credentials to authenticate
against a provider source. This metadata can be described as parameters using
Drutiny's annotation system. These annotations are then used in the `profile:run`
help screen to instruct users how to provide the required metadata to the Domain
Provider.

```php
use Drutiny\Annotation\Param;

/**
 * @Param(
 *   name = "email",
 *   description = "Email of Acquia account accessing the Cloud API",
 * )
 * @Param(
 *   name = "key",
 *   description = "API Key which can be obtained from Acquia Cloud account",
 * )
 */
class AcquiaCloudDomainList implements DomainListInterface {

  //...

  public function __construct(array $metadata)
  {
    if (isset($metadata['email'])) {
      $this->email = $metadata['email'];
    }
    if (isset($metadata['key'])) {
      $this->key = $metadata['key'];
    }
```

## Registering the Domain Provider

Registering the class in `drutiny.config.yml` allows the `profile:run`
command to register options to pass in and is required for the domain provider to be usable.

```yaml
DomainList:
  ac: Drutiny\Acquia\AcquiaCloudDomainList
```
