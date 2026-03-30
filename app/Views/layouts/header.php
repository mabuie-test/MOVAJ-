<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>MovaJá</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>
<nav class="topbar navbar navbar-expand-lg">
  <div class="container-fluid px-3 px-lg-4">
    <a class="navbar-brand" href="/"><i class="fa-solid fa-route me-2"></i>MovaJá</a>
    <div class="ms-auto d-flex gap-2">
      <a class="btn btn-sm btn-outline-light" href="/login"><i class="fa-solid fa-right-to-bracket me-1"></i>Entrar</a>
    </div>
  </div>
</nav>
<div class="app-layout">
  <?php require __DIR__ . '/partials/sidebar.php'; ?>
  <main class="app-content">
    <div class="container-fluid px-3 px-lg-4 py-3 py-lg-4">
