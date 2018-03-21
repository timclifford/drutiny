# policy:audit

## Usage
```
 policy:audit [options] [--] <policy> <target>
```

## Examples

### Audit a site for anonymous sessions via Drush
By defalt, Drutiny `<target>` arguments refer to a drush aliases.
As drush may reference remote targets, Drutiny also supports running
policies against remote `<target>`s.

```
drutiny policy:audit Drupal:AnonSession @sitename.dev
```

### Specifying a URL
In multisite scenarios you will need to specific which site to connect to via a `uri` option.

**Note**: `drutiny/http` audits run audits over HTTP and rely on having
a uri that is accessible and valid (e.g. contains protocol and valid
  credentials such as SSL certificates).

```
drutiny policy:audit Drupal:AnonSession @sitename.dev --uri=https://dev.sitename.com/
```

### Policy execution with remediation
Some policies utilize an audit that can remediate a `<target>` if the
policy fails.

```
drutiny policy:audit Drupal:AnonSession @sitename.dev --remediate
```

### Customize policy parameters
A policy may contain parameters that can be customised at the time
of execution. To know what parameters are available, use the
`policy:info` command:

```
$ drutiny policy:info Database:Size

 ------------- ----------------------------------------------------------------------------------------------
  Check         Database size
 ------------- ----------------------------------------------------------------------------------------------
  Description   Large databases can negatively impact your production site, and slow down things
                like database dumps.
 ------------- ----------------------------------------------------------------------------------------------
  Remediable    No
 ------------- ----------------------------------------------------------------------------------------------
  Parameters    max_size:integer
                  The maximum size in megabytes the database should be.
                warning_size:integer
                  The size in megabytes this check will issues a warning at.
 ------------- ----------------------------------------------------------------------------------------------
  Location      /Users/josh.waihi/Sandbox/test-drutiny/vendor/drutiny/drutiny/Policy/databaseSize.policy.yml
 ------------- ----------------------------------------------------------------------------------------------
```

In this policy, the `max_size` and `warning_size` parameters can be specified. These parameters can be passed in at runtime like this:

```
drutiny policy:audit Database:Size @sitename.dev -p max_size=1024 -p warning_size=768
```
