<?php
require_once "../auth/guard.php";
require_once "../config/database.php";

$isAdmin = ($_SESSION["role"] === "admin");
$userId  = $_SESSION["id"];

if ($isAdmin) {
    $total = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
} else {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE utilisateur_id = ?");
    $stmt->execute([$userId]); $total = $stmt->fetchColumn();
}

if ($isAdmin) {
    $stmt = $pdo->query("SELECT s.nom, COUNT(*) AS total FROM reservations r JOIN salles s ON r.salle_id=s.id WHERE r.statut='confirmee' GROUP BY s.id ORDER BY total DESC LIMIT 1");
} else {
    $stmt = $pdo->prepare("SELECT s.nom, COUNT(*) AS total FROM reservations r JOIN salles s ON r.salle_id=s.id WHERE r.utilisateur_id=? GROUP BY s.id ORDER BY total DESC LIMIT 1");
    $stmt->execute([$userId]);
}
$salleTendance = $stmt->fetch();

if ($isAdmin) {
    $stmt = $pdo->query("SELECT HOUR(heure_debut) AS heure, COUNT(*) AS total FROM reservations WHERE statut='confirmee' GROUP BY HOUR(heure_debut) ORDER BY total DESC LIMIT 1");
} else {
    $stmt = $pdo->prepare("SELECT HOUR(heure_debut) AS heure, COUNT(*) AS total FROM reservations WHERE utilisateur_id=? GROUP BY HOUR(heure_debut) ORDER BY total DESC LIMIT 1");
    $stmt->execute([$userId]);
}
$heureTendance = $stmt->fetch();

if ($isAdmin) {
    $repartition = $pdo->query("SELECT statut, COUNT(*) AS total FROM reservations GROUP BY statut")->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT statut, COUNT(*) AS total FROM reservations WHERE utilisateur_id=? GROUP BY statut");
    $stmt->execute([$userId]); $repartition = $stmt->fetchAll();
}

$topUsers = [];
if ($isAdmin) {
    $topUsers = $pdo->query("SELECT u.nom, u.email, u.type_client, COUNT(r.id) AS total FROM reservations r JOIN utilisateurs u ON r.utilisateur_id=u.id GROUP BY u.id ORDER BY total DESC LIMIT 10")->fetchAll();
}

$pageTitle   = "Statistiques";
$pageHeading = "RoomBook";
require_once "../includes/header.php";
?>

<div class="container-fluid px-4 pb-5">

  <h2 class="mb-3 fs-5 fw-bold">
    <i class="bi bi-bar-chart text-primary me-1"></i>
    <?= $isAdmin ? "Statistiques globales" : "Mes statistiques" ?>
  </h2>

  <!-- Cartes stat -->
  <div class="row g-3 mb-4">
    <div class="col-sm-4">
      <div class="card shadow-sm border-0 text-center p-4">
        <div class="display-5 fw-bold text-primary"><?= (int)$total ?></div>
        <div class="text-muted small mt-1">Total réservations</div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="card shadow-sm border-0 text-center p-4">
        <div class="display-5 fw-bold text-success">
          <?= $salleTendance ? htmlspecialchars($salleTendance["nom"]) : "—" ?>
        </div>
        <div class="text-muted small mt-1">
          Salle tendance <?= $salleTendance ? "(".(int)$salleTendance["total"]." résas)" : "" ?>
        </div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="card shadow-sm border-0 text-center p-4">
        <div class="display-5 fw-bold text-warning">
          <?= $heureTendance ? (int)$heureTendance["heure"]."h" : "—" ?>
        </div>
        <div class="text-muted small mt-1">Heure la plus demandée</div>
      </div>
    </div>
  </div>

  <!-- Répartition par statut -->
  <?php if (!empty($repartition)):
    $labels = ['en_attente'=>'En attente','confirmee'=>'Confirmées','refusee'=>'Refusées','annulee'=>'Annulées'];
    $colors = ['en_attente'=>'warning','confirmee'=>'success','refusee'=>'danger','annulee'=>'secondary'];
    $totalRep = array_sum(array_column($repartition,'total'));
  ?>
  <div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white fw-semibold border-bottom">
      <i class="bi bi-pie-chart text-primary me-1"></i> Répartition par statut
    </div>
    <div class="card-body">
      <?php foreach ($repartition as $r):
        $pct = $totalRep > 0 ? round($r["total"]*100/$totalRep, 1) : 0;
        $c   = $colors[$r["statut"]] ?? "secondary";
      ?>
      <div class="mb-3">
        <div class="d-flex justify-content-between mb-1">
          <span class="fw-semibold"><?= $labels[$r["statut"]] ?? $r["statut"] ?></span>
          <span class="text-muted small"><?= (int)$r["total"] ?> (<?= $pct ?>%)</span>
        </div>
        <div class="progress" style="height:18px">
          <div class="progress-bar bg-<?= $c ?>" style="width:<?= $pct ?>%"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Top utilisateurs (admin) -->
  <?php if ($isAdmin && !empty($topUsers)): ?>
  <div class="card shadow-sm border-0">
    <div class="card-header bg-white fw-semibold border-bottom">
      <i class="bi bi-trophy text-warning me-1"></i> Top utilisateurs
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Rang</th><th>Nom</th><th>Email</th><th>Type</th><th>Réservations</th></tr>
        </thead>
        <tbody>
        <?php foreach ($topUsers as $i => $u):
          $medal = $i===0?'🥇':($i===1?'🥈':($i===2?'🥉':($i+1)));
          $tl    = ['prof'=>'👨‍🏫 Prof','etudiant'=>'🎓 Étudiant','autre'=>'Autre'];
        ?>
          <tr>
            <td><?= $medal ?></td>
            <td><strong><?= htmlspecialchars($u["nom"]) ?></strong></td>
            <td><?= htmlspecialchars($u["email"]) ?></td>
            <td><?= $tl[$u["type_client"]] ?? "—" ?></td>
            <td><span class="badge bg-primary"><?= (int)$u["total"] ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

</div>

<?php require_once "../includes/footer.php"; ?>
