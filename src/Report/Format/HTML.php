<?php

namespace Drutiny\Report\Format;

class HTML extends JSON {

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

  public function __construct($options)
  {
    parent::__construct($options);
    $this->setFormat('html');
    if (isset($options['template'])) {
      $this->setTemplate($options['template']);
    }
    if (isset($options['content'])) {
      $this->setContent($options['content']);
    }
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


  public function render($profile, $target, $result)
  {
    $report = new ProfileRunHtmlReport($profile, $target, $result);
    $report->render();
  }
}

 ?>
