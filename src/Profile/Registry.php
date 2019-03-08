<?php

namespace Drutiny\Profile;

use Drutiny\Config;
use Drutiny\Registry as GlobalRegisry;
use Drutiny\Profile;
use Drutiny\Container;
use Drutiny\PolicySource\UnavailablePolicyException;
use Symfony\Component\Yaml\Yaml;


class Registry extends GlobalRegisry {

  public static function locateProfiles()
  {
    $filepaths = self::get('profile.locations');
    if (!empty($filepaths)) {
      return $filepaths;
    }

    $finder = Config::getFinder()->name('*.profile.yml');

    foreach ($finder as $file) {
      $filepaths[] = $file->getRelativePathname();
    }
    self::add('profile.locations', $filepaths);
    return $filepaths;
  }

  public static function locateProfile($name)
  {
    $finder = Config::getFinder()->name($name . '.profile.yml');

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
    return array_filter(array_map(function ($filepath) {
      try {
        return Profile::loadFromFile($filepath);
      }
      catch (UnavailablePolicyException $e) {
        Container::getLogger()->info($e->getMessage());
      }
      return FALSE;
    }, self::locateProfiles()));
  }

}


 ?>
