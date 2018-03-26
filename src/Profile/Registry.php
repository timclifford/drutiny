<?php

namespace Drutiny\Profile;

use Drutiny\Registry as GlobalRegisry;
use Drutiny\Profile;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;


class Registry extends GlobalRegisry {

  public static function locateProfiles()
  {
    $filepaths = self::get('profile.locations');
    if (!empty($filepaths)) {
      return $filepaths;
    }

    $finder = new Finder();
    $finder->files();
    $finder->in('.');
    $finder->name('*.profile.yml');

    foreach ($finder as $file) {
      $filepaths[] = $file->getPathname();
    }
    self::add('profile.locations', $filepaths);
    return $filepaths;
  }

  public static function locateProfile($name)
  {
    $finder = new Finder();
    $finder->files();
    $finder->in('.');
    $finder->name($name . '.profile.yml');

    foreach ($finder as $file) {
      return $file->getRealPath();
    }
    throw new \Exception("Could not locate profile: $name.");
  }

  public static function profileNames()
  {
    return array_map(function ($filepath) {
      return pathinfo($filepath, PATHINFO_FILENAME);
    }, self::locateProfiles());
  }

  public static function getProfile($name)
  {
    return Profile::loadFromFile(self::locateProfile($name));
  }

  public static function getAllProfiles()
  {
    return array_map(function ($filepath) {
      return Profile::loadFromFile($filepath);
    }, self::locateProfiles());
  }

}


 ?>
