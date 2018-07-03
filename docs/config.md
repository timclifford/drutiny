# Config

`drutiny.config.yml` is a static registry yaml file that can be provided by
drutiny plugin libraries to extend Drutiny's capability.


## Target

The `Target` directive allows you to register additional target types. Targets
are references to audit-able subjects. The Target PHP class is responsible for
parsing in the target reference passed in the command line and allowing the audits
to correctly access the target to be able to audit it.

```yaml
Target:
  - Drutiny\Target\DrushTarget
  - Drutiny\Target\TargetNone
```

## Command
The `Command` directive allows extensions to add additional commands into Drutiny's
console Application. This can be done to utilise the Drutiny framework underneath
the `drutiny` executable.

```yaml
Command:
  - Drutiny\Command\AuditGenerateCommand
  - Drutiny\Command\AuditInfoCommand
```

## Template
The `Template` directive allows extensions to register directories where Drutiny
will look for twig template files when rendering HTML and Markdown files.

```yaml
Template:
  - src/Report/templates/html
  - src/Report/templates/markdown
```

## DomainList
The `DomainList` directive registers domain providers that can provide a list of
domains to run a profile against in multisite mode.

```yaml
DomainList:
  YamlFile: Drutiny\DomainList\DomainListYamlFile
```

Learn more about [Domain Providers](domains.md)

## Format

The `Format` directive allows extensions to add additional export formats on top
of the ones already provided by Drutiny.

```yaml
Format:
  html: Drutiny\Report\Format\HTML
  json: Drutiny\Report\Format\JSON
  markdown: Drutiny\Report\Format\Markdown
  terminal: Drutiny\Report\Format\Terminal
  console: Drutiny\Report\Format\Console
```

## CredentialSchema
The `CredentialSchema` directive allows extensions to provide a schema for
credentials needed for something such as an Audit. This allows the Drutiny
Credential Manager to acquire the credentials from the user on the behalf of
the extension using them.

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

Learn more about the [Credential Manager](creds.md)

## Driver

The `Driver` directive provides access to a driver directly from the [Sandbox](sandbox.md).

```yaml
Driver:
  drush: Drutiny\Driver\DrushRouter::create
```

The key of the directive becomes a callable method and the value is a function
that will be called and the returned value returned through the driver:

```php
<?php
  $sandbox->drush()->pmList();
```

## PolicySource
The `PolicySource` directive declares PHP classes that implement the
 `Drutiny\PolicySource\PolicySourceInterface` interface. Policy sources provide
 policies for Drutiny to execute.

```yaml
PolicySource:
  - Drutiny\PolicySource\DrutinyGitHubIO
  - Drutiny\PolicySource\LocalFs
```

## Cache
The `Cache`directive provides PSR-6 storage classes for mapped cache bins.

```yaml
Cache:
  exec: Symfony\Component\Cache\Adapter\FilesystemAdapter
```
