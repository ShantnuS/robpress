<?php

  class FormHelper {

    // Added this method so that if something else needs to be removed all we need to change is this method
    public static function cleanInput($array) {
      foreach ($array as &$input) {
        $input = htmlspecialchars($input);
      }

      return $array;
    }
  }
?>
