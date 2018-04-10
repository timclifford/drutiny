<?php

namespace Drutiny\Credential;

use Symfony\Component\Yaml\Yaml;

class FileStore {

  const filename = ".drutiny_creds.yml";

  protected $namespace;

  public function __construct($ns)
  {
    $this->namespace = $ns;
  }

  public function findCredentialFile()
  {
    $filename = self::filename;
    $paths = array_map(function ($path) use ($filename) {
      return $path . '/' . $filename;
    }, [getenv('HOME'), getenv('PWD')]);

    $files = array_filter($paths, 'file_exists');

    if (!empty($files)) {
      return array_shift($files);
    }

    return array_pop($paths);
  }

  public function open()
  {
    $filepath = $this->findCredentialFile();

    if (!file_exists($filepath)) {
      throw new CredentialsUnavailableException("Cannot find stored credentials for {$this->namespace}. Please run `plugin:setup {$this->namespace}`.");
    }
    if (!$content = file_get_contents($filepath)) {
      throw new CredentialsUnavailableException("Cannot retrieve stored credentials for {$this->namespace} from $filepath. Please run `plugin:setup {$this->namespace}`.");
    }
    if (!$creds = Yaml::parse($content)) {
      throw new CredentialsUnavailableException("Cannot parse stored credentials for {$this->namespace} in $filepath. Please run `plugin:setup {$this->namespace}`.");
    }

    if (!isset($creds[$this->namespace])) {
      throw new CredentialsUnavailableException("Cannot find stored credentials for {$this->namespace} in $filepath. Please run `plugin:setup {$this->namespace}`.");
    }
    return $creds[$this->namespace];
  }

  public function write($creds)
  {
    $filepath = $this->findCredentialFile();

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
