<?php
namespace App\Core;

final class Paginator {
  public static function fromQuery(array $q, int $max = 100): array {
    $limit  = isset($q['limit'])  ? max(1, min((int)$q['limit'], $max)) : 20;
    $offset = isset($q['offset']) ? max(0, (int)$q['offset']) : 0;
    return [$limit, $offset];
  }
}
