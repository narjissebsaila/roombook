<?php
if (!isset($pageHeading))  $pageHeading  = "RoomBook";
if (!isset($pageSubtitle)) $pageSubtitle = "Gestion des réservations de salles";
$current = basename($_SERVER["PHP_SELF"]);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?> — RoomBook</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="rb-body">

<!-- TOAST container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999" id="toastContainer"></div>

<div class="container-fluid px-4">

    <!-- HEADER -->
    <div class="rb-header d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 rounded-3 mt-3 mb-2">
        <div class="d-flex align-items-center gap-3">
            <div >  <img src="../includes/logo.png" alt="Logo" width="80" style=" border-radius:12px;"></div>
            <div>
                <h1 class="mb-0 fs-4 fw-bold text-white"><?= htmlspecialchars($pageHeading) ?></h1>
                <small class="text-white-50"><?= htmlspecialchars($pageSubtitle) ?></small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-white text-dark px-3 py-2 fs-6">
                👤 <?= htmlspecialchars($_SESSION["nom"]) ?>
                <span class="text-muted ms-1 small">(<?= $_SESSION["role"]==="admin" ? "admin" : ($_SESSION["type_client"]==="prof" ? "prof" : "étudiant") ?>)</span>
            </span>
            <a href="../auth/logout.php" class="btn btn-danger btn-sm"
               onclick="return confirm('Se déconnecter ?')">
                <i class="bi bi-box-arrow-right"></i> Déconnexion
            </a>
        </div>
    </div>

    <!-- NAVBAR -->
    <nav class="rb-navbar d-flex gap-1 flex-wrap mb-3 p-2 bg-white rounded-3 shadow-sm">
        <a href="index.php" class="nav-link rb-nav <?= $current==='index.php' ? 'active' : '' ?>">
            <i class="bi bi-calendar3"></i> Réservations
        </a>
        <a href="salles.php" class="nav-link rb-nav <?= $current==='salles.php' ? 'active' : '' ?>">
            <i class="bi bi-building"></i> Salles
        </a>
        <a href="statistiques.php" class="nav-link rb-nav <?= $current==='statistiques.php' ? 'active' : '' ?>">
            <i class="bi bi-bar-chart"></i> Statistiques
        </a>
        <?php if ($_SESSION["role"] === "admin"): ?>
        <a href="utilisateurs.php" class="nav-link rb-nav <?= $current==='utilisateurs.php' ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Utilisateurs
        </a>
        <?php endif; ?>
        <a href="profile.php" class="nav-link rb-nav <?= $current==='profile.php' ? 'active' : '' ?>">
            <i class="bi bi-gear"></i> Mon profil
        </a>
    </nav>

</div><!-- /container-fluid -->
