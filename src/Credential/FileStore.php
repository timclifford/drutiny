<?php

namespace Drutiny\Credential;

use Symfony\Component\Yaml\Yaml;
use Drutiny\Config;
use Drutiny\Container;

class FileStore {

  const filename = ".drutiny_creds.yml";

  protected $namespace;

  public function __construct($ns)
  {
    $this->namespace = $ns;

    // Upgrade check.
    if (file_exists($old_location = getenv('HOME') . '/.drutiny_creds.yml')) {
      $new_location = $this->getCredentialFile('user');
      if (!file_exists($new_location)) {
        Container::getLogger()->warning("Updating Drutiny credential location from $old_location to $new_location.");
        rename($old_location, $new_location);
      }
      if ($new_location != $old_location) {
        throw new \Exception("Upgrade error. Old and new credential files both exist. Please merge $old_location and $new_location.");
      }
    }
  }

  protected function getScopes()
  {
    $paths = array_filter([
      'global' => realpath(DRUTINY_LIB),
      'user' => Config::getUserDir(),
      'local' => realpath(getenv('PWD')),
    ], 'is_dir');

    if (isset($paths['global'], $paths['local']) && ($paths['global'] == $paths['local'])) {
      unset($paths['global']);
    }
    return $paths;
  }

  /**
   * Load all credential files.
   *
   * If different credentials are present in different files, they will be
   * recursively be merged in. Credentials from narrow scopes take precedence.
   */
  protected function loadCredentials()
  {
    static $cache;
    if ($cache) {
      return $cache;
    }

    $yaml = array_map(
      /**
       * Pull the contents from valid filepaths.
       */
      function ($directory) {
        $filepath = implode(DIRECTORY_SEPARATOR, [$directory, FileStore::filename]);

        if (!file_exists($filepath)) {
          return FALSE;
        }
        $content = file_get_contents($filepath);

        if (empty($content)) {
          return FALSE;
        }
        Container::getLogger()->info("Loading credential file: $filepath.");
        return YAML::parse($content);
      }
    , $this->getScopes());

    $creds = [];

    foreach (array_filter($yaml, 'is_array') as $credentials) {
      $creds = array_replace_recursive($creds, $credentials);
    }

    $cache = $creds;

    return $cache;
  }

  /**
   * Return the path of a credential file.
   *
   * @param string $scope
   *   The scope to generate a filepath for. Options: global, user, local.
   *
   * @return string
   *   The path of an existing credential file or the path of
   *   of non-existing credential file by preference of $scope.
   */
  protected function getCredentialFile($scope = 'user')
  {
    $scopes = $this->getScopes();
    $directory = $scopes[$scope] ?: current($scopes);
    return implode(DIRECTORY_SEPARATOR, [$directory, self::filename]);
  }

  /**
   * Open up the credentials store on file.
   */
  public function open()
  {
    $creds = $this->loadCredentials();

    if (!isset($creds[$this->namespace])) {
      throw new CredentialsUnavailableException("Cannot find stored credentials for {$this->namespace}. Please run `plugin:setup {$this->namespace}`.");
    }
    return $creds[$this->namespace];
  }

  /**
   * Write credentials to a credential file.
   *
   * @param array $creds
   * @param string $scope
   */
  public function write($creds, $scope = 'user')
  {
    $filepath = $this->getCredentialFile($scope);

    $set = [];
    if (file_exists($filepath)) {
      if (!$content = file_get_contents($filepath)) {
        throw new CredentialsUnavailableException("Cannot parse stored credentials for {$this->namespace} in $filepath.");
      }
      $set = Yaml::parse($content);
    }

    $set[$this->namespace] = $creds;
    return file_put_contents($filepath, Yaml::dump($set, 4));
  }
}

 ?>
