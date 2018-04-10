# Credential Management

Some Drutiny plugins require credentials not provided by a target. Examples of
these types of plugins are Sumologic and Cloudflare who have API endpoints
to talk which require API credentials. For these types of plugins, Drutiny
has a central credential management tool for storing and using credentials.

## Usage

Drutiny has a command called `plugin:setup` which can be used to setup credentials
for a service.

```
./vendor/bin/drutiny plugin:setup sumologic

access_id (string)
Your access ID to connect to the Sumologic API with: : **********
access_key (string)
Your access key to connect to the Sumologic API with: : *******************

 [OK] Credentials for sumologic have been saved.

```

## Using the Credential Manager API
Using the credential manager requires specifying the schema for your
credentials then using the manager to load your credentials when you need them.
Presumably inside an audit.

### Adding credentials to drutiny.config.yml
`drutiny.config.yml` is a file a plugin library can specify to declare config.
The `CredentialSchema` key is used to declare namespaces and arbitrary credentials
for the namespace/

```yaml
CredentialSchema:
  sumologic:
    access_id:
      type: string
      description: Your access ID to connect to the Sumologic API with:
    access_key:
      type: string
      description: Your access key to connect to the Sumologic API with:
```
The above example provides a namespace for `sumologic` and details `access_id`
and `access_key` as credentials to provide. These are the credentials that
`plugin:setup` will ask for.

### Auditing Prerequisites
Usually, credentials are required in order for an audit to function correctly.
Rather than failing an audit because the credentials aren't present to complete
the assessment, instead, you can specify an [Audit Prerequisite](/audits/#audit-prerequisites)
inside your Audit class:

```php

use Drutiny\Credential\Manager;

// ...

  protected function requireApiCredentials()
  {
    return Manager::load('sumologic') ? TRUE : FALSE;
  }
```


### Using the Credentials
If you've created the `CredentialSchema` in `drutiny.config.yml` and setup an Audit
Prerequisite, then you are now ready to use the credentials inside an audit:

```php

use Drutiny\Credential\Manager;

$creds = Manager::load('sumologic');
$client = new Client($creds['access_id'], $creds['access_key']);
```
