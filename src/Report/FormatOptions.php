<?php

namespace Drutiny\Report;

class FormatOptions {

  /**
   * The format the object applies to.
   *
   * @var string
   */
  protected $format;

  /**
   * The content to use when rendering HTML.
   *
   * @var array
   */
  protected $content = [];

  /**
   * The twig template to use to render the report wrapper in HTML.
   *
   * @var string
   */
  protected $template = 'site';

  public static function create($format, $options)
  {
    $object = new static();
    $object->setFormat($format);
    if (isset($options['template'])) {
      $object->setTemplate($options['template']);
    }
    if (isset($options['content'])) {
      $object->setContent($options['content']);
    }
    return $object;
  }

  /**
   * Get the profile title.
   */
  public function getFormat()
  {
    return $this->format;
  }

  /**
   * Set the title of the profile.
   */
  public function setFormat($format)
  {
    $this->format = $format;
    return $this;
  }

  /**
   * Get the profile title.
   */
  public function getTemplate()
  {
    return $this->template;
  }

  /**
   * Set the title of the profile.
   */
  public function setTemplate($template)
  {
    $this->template = $template;
    return $this;
  }

  /**
   * Get the profile title.
   */
  public function getContent()
  {
    return $this->content;
  }

  /**
   * Set the title of the profile.
   */
  public function setContent(array $content)
  {
    $this->content = $content;
    return $this;
  }
}

 ?>
