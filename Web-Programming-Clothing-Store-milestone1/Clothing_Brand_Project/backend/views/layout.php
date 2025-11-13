<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title><?= htmlspecialchars($title ?? 'Admin') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Minimal Bootstrap just for this page -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
      <a class="navbar-brand" href="/admin/products">Admin · Clothing Store</a>
    </div>
  </nav>

  <main class="container">
    <?= $content ?? '' ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
</body>
</html>
