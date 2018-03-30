<?php

namespace Drutiny\Report\Format;

use Drutiny\Registry;
use TOC\MarkupFixer;
use TOC\TocGenerator;
use Symfony\Component\Yaml\Yaml;

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
    $this->setTemplate(isset($options['template']) ? $options['template'] : 'page');

    if (isset($options['content'])) {
      $this->setContent($options['content']);
    }
    else {
      $this->setContent(Yaml::parseFile(dirname(__FILE__) . '/../../../Profiles/content.default.yml'));
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
    $render = parent::render($profile, $target, $result);
    $parsedown = new \Parsedown();

    foreach ($render['remediations'] as &$remedy) {
      $remedy = $parsedown->text($remedy);
    }

    $markdown_fields = ['description', 'remediation', 'success', 'failure', 'warning'];

    // Render any markdown into HTML for the report.
    foreach ($render['results'] as &$result) {
      foreach ($markdown_fields as $field) {
        $result[$field] = $parsedown->text($result[$field]);
      }
      // Produce an ID for the result that can be used as an HTML ID attribute.
      $result['id'] = preg_replace('/[^0-9a-zA-Z]/', '', $result['title']);

      $results_vars = ['result' => $result];
      $result_render = self::renderTemplate($result['type'], $results_vars);
      $render['output_' . $result['type']][] = $result_render;
      $result['rendered_result'] = $result_render;
    }

    $render['summary_table']  = self::renderTemplate('summary_table', $render);
    $render['appendix_table'] = self::renderTemplate('appendix_table', $render);
    $render['severity_stats'] = self::renderTemplate('severity_stats', $render);

    $engine = new \Mustache_Engine();
    foreach ($this->getContent() as $idx => $section) {
      try {
         $section = '## ' . $section['heading'] . PHP_EOL . $engine->render($section['body'], $render);
      }
      catch (\Mustache_Exception $e) {
        throw new \Exception("Error in " . __CLASS__ . ": " . $e->getMessage());
      }
      $render['sections'][] = $parsedown->text($section);
    }

    // Preperation to generate Toc
    $markupFixer  = new MarkupFixer();
    $tocGenerator = new TocGenerator();

    // Render the site report.
    $content = $markupFixer->fix(
      self::renderTemplate('site', $render)
    );

    // Table of Contents.
    $toc = self::renderTemplate('toc', [
      'table_of_contents' => $tocGenerator->getHtmlMenu($content, 2, 3)
    ]);

    // Render the header/footer etc.
    $render['content'] = $content;
    $content = self::renderTemplate($this->getTemplate(), $render);

    // Hack to fix table styles in bootstrap theme.
    $content = strtr($content, [
      '<table>' => '<table class="table table-hover">',
      '#table_of_contents#' => $toc
    ]);

    return $content;
  }

  /**
   * Render an HTML template.
   *
   * @param string $tpl
   *   The name of the .html.tpl template file to load for rendering.
   * @param array $render
   *   An array of variables to be used within the template by the rendering engine.
   *
   * @return string
   */
  public static function renderTemplate($tpl, array $render) {
    $registry = new Registry();
    $loader = new \Twig_Loader_Filesystem($registry->templateDirs());
    $twig = new \Twig_Environment($loader, array(
      'cache' => sys_get_temp_dir() . '/drutiny/cache',
      'auto_reload' => TRUE,
    ));

    $template = $twig->load($tpl . '.html.twig');
    $contents = $template->render($render);
    return $contents;
  }
}

 ?>
