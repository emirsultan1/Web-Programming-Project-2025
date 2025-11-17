<?php
namespace App\Core;

use Flight;
use Throwable;

final class ErrorHandler {
  public static function register(): void {
    Flight::map('error', function (Throwable $e) {
      $status = (int)($e->getCode() ?: 500);
      if ($status < 400 || $status > 599) $status = 500;
      http_response_code($status);
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode([
        'error' => [
          'code' => $status,
          'message' => $e->getMessage(),
        ]
      ], JSON_UNESCAPED_UNICODE);
    });
  }
}
