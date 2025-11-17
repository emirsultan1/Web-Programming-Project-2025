<?php
// Redirect /docs -> /docs/ (so assets resolve), then static index.html takes over
\Flight::route('GET /docs', function () {
  header('Location: /docs/', true, 302);
});
