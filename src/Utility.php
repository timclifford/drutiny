<?php

namespace Drutiny;

class Utility {
  static public function jsonDecodeDirty($output, $return_array = FALSE)
  {
    $pos = strpos($output, '{');
    if ($pos !== 0) {
      Container::getLogger()->warning("Dirty json output detected. This suggests other errors maybe occuring.");
    }
    $clean = substr($output, $pos);
    return json_decode($clean, $return_array);
  }
}
?>
