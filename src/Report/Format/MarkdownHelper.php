<?php

namespace Drutiny\Report\Format;

use Parsedown;

class MarkdownHelper extends Parsedown {
  // Remove Code as a block type. This prevents code blocks occuring
  // from indentation as it becomes confusing in YAML template files.
  protected $unmarkedBlockTypes = array();
}
 ?>
