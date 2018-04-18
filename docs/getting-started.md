# Getting Started
This getting started guide is a tutorial to get you setup with Drutiny and to
show you around the tool.

## Standalone Installation
While Drutiny can be included as a tool inside of any composer project, running
it as a standalone tool is the easiest way to get started. The Drutiny `project-dev`
composer project is a great way to get a comprehensive setup going locally and fast.

```
composer create-project --no-interaction -s dev drutiny/project-dev drutiny
```

**Note**: [Drush](https://www.drush.org/) is also required but not an explicit
dependency so make sure its available before continuing.

## The Executable
Drutiny is controlled from the command-line through a Symfony Console executable
called `drutiny` located in composer's `bin-dir` directory. For `drutiny/project-dev`,
that is located in the `bin` directory in the root of the directory.

```
./bin/drutiny
```

When you run it, it should show you all the commands available from it. **Note**:
composer's default `bin-dir` is `./vendor/bin`.

## Drupal site access via Drush alias
By default, Drutiny depends on Drush alias files to inform Drutiny where the site
is to audit. We call the site a **target**. Make sure your target is accessible via
a drush command. You should be able to execute this command if remote:

```
drush @site.env ssh
```

## Whats in your policy library
The plugins that come with Drutiny contribute domain specific policies that make
up a local library of policies at your disposal to run against your site. To
find out which policies are available, you can use the `policy:list` command.

```
./bin/drutiny policy:list
```

## What does a policy do?
To find out more about a policy, you can use the `policy:info` command and pass
in the **name** of the policy:

```
./bin/drutiny policy:info Drupal:AnonSession
```

## Auditing a Policy
Now that we're setup, lets try run a Drutiny policy against a site with the
`policy:audit` command. We'll do this by passing two arguments: `Drupal:AnonSession`
which is the name of the policy and `@site.env` which should represent
the real Drush alias you'll use.

```
./bin/drutiny policy:audit Drupal:AnonSession @site.env
```

To see the command line actions Drutiny is taking underneath the hood, run the
command again with the `-vvv` option to see the verbose info logged.


## Running multiple policies at once
With a **profile** we can group multiple policies together to audit against a
single target.

### Building a profile
Drutiny comes with a profile generator to help you kick start building a profile.
Lets start with building a demo profile:

```
./bin/drutiny profile:generate -t 'Demo Profile' -f demo.profile.yml -p Drupal:AnonSession -p Drupal:SyslogEnabled
```

The command above will create a new demo profile with two policies and write it
to a file called `demo.profile.yml`.

### Running a profile audit
To run a profile you use `profile:run` and pass in the name of the profile which is
the first part of the profile filename. E.g. `demo` will load in the profile from
`demo.profile.yml`.

```
./bin/drutiny profile:run demo @site.env
```

### Creating a Report
Drutiny can export profile runs to HTML, Markdown, Json or by default, it just
publishes the findings out to the console (see the above command). You can use
the `--format` option (or `-f`) to specify the which format to export to and
the `--report-filename` option (or `-o`) to specify the file to write too.

```
./bin/drutiny profile:run demo @site.env -f html -o demo-report.html
```

Open the HTML report in your browser to view the report. From there you could
also print it to PDF.
