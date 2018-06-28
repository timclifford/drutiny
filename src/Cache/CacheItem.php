<?php

namespace Drutiny\Cache;

use Psr\Cache\CacheItemInterface;

class CacheItem implements CacheItemInterface {

  /**
   * @var \DateTime The DateTime the item should expire.
   */
  protected $expiry;

  /**
   * @var mixed The cached item value.
   */
  protected $value;

  /**
   * @var string The cached item key.
   */
  protected $key;

  public function __construct($value, $key, \DateTime $expiry)
  {
    $this->value = $value;
    $this->expiry = $expiry;
    $this->key = $key;
  }

  /**
   * Get the expiry object.
   *
   * @return \DateTimeInterface object.
   */
  public function getExpiry()
  {
    return $this->expiry;
  }

  /**
   * {@inheritdoc}
   */
  public function getKey()
  {
    return $this->key;
  }

  /**
   * {@inheritdoc}
   */
  public function get()
  {
    return $this->value;
  }

  /**
   * {@inheritdoc}
   */
  public function isHit()
  {
    return $this->expiry > (new \DateTime());
  }

  /**
   * {@inheritdoc}
   */
  public function set($value)
  {
    $this->value = $value;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function expiresAt($expiration)
  {
    if ($expiration instanceof \DateTimeInterface) {
      $this->expiry = $expiration;
    }
    // If null is passed explicitly, a default value MAY be used.
    elseif ($expiration === NULL) {
      $this->expiry = new \DateTime('+1 hour');
    }
    // Item is cached "forever".
    else {
      $this->expiry = new \DateTime('+1000 years');
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function expiresAfter($time)
  {
    $expiry = new \DateTime();
    if ($time instanceof \DateInterval) {
      $expiry->add($time);
    }
    elseif (is_int($time)) {
      $interval = new \DateInterval('PT' . $time . 'S');
      $expiry->add($interval);
    }
    else {
      $expiry = NULL;
    }
    return $this->expiresAt($expiry);
  }
}

 ?>
