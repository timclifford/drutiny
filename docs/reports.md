# Working with Reports

Reports are built from running Drutiny profiles, a collection of policies. To
build a report, use the `profile:run` command. The command requires two
arguments: the name of the profile to run and the reference to the target to
audit.

By default, Drutiny expects the target to be a drush alias will all the necessary
credentials to access the Drupal site.

**Note**: Some audits, such as audits from `drutiny/http` and `drutiny/cloudflare`
expect a valid URI to be provided.

### Example usage
```
$ drutiny profile:run test @none
```

The above command runs Drutiny's test profile on nothing (@none is an alias
provided by drush by default.)

## Formats

Drutiny comes with 3 types of report formats: CLI, &nbsp;JSON, &nbsp;HTML.<br>
If you do not specify any report format, the CLI format will be used by default.

Example to run a JSON report by using the "-f" param.

```
./vendor/bin/drutiny profile:run d8 @drupalvm.dev -f json
```

or you can use the HTML report with telling Drutiny, where to store the html.

```
./vendor/bin/drutiny profile:run d8 @drupalvm.dev -f html -o ./report1.html
```

HTML Reports can be customized from the profile run. See the [profile documentation](profiles)
for more detail.
