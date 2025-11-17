<?php
namespace App\Core;

final class Validator {
  public static function requireFields(array $data, array $fields): void {
    foreach ($fields as $f) {
      if (!array_key_exists($f, $data)) {
        throw new \InvalidArgumentException("Missing field: $f", 422);
      }
    }
  }
  public static function nonNegativeNumber($value, string $name): void {
    if (!is_numeric($value) || $value < 0) {
      throw new \InvalidArgumentException("$name must be a non-negative number", 422);
    }
  }
  public static function nonNegativeInt($value, string $name): void {
    if (!is_numeric($value) || (int)$value < 0) {
      throw new \InvalidArgumentException("$name must be a non-negative integer", 422);
    }
  }
}
