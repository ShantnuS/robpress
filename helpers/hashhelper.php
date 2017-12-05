<?php
  class HashHelper {
    // Simple hash checking algorithm to prevent timing attacks, taken from the docs
    public static function hash_equals($a, $b) {
      if (!is_string($a) || !is_string($b)) {
        return false;
      }

      $len = strlen($a);
      if ($len !== strlen($b)) {
          return false;
      }

      $status = 0;
      for ($i = 0; $i < $len; $i++) {
          $status |= ord($a[$i]) ^ ord($b[$i]);
      }
      return $status === 0;
    }
  }
?>
