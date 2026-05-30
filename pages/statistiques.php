<?php
require_once "../auth/guard.php";
require_once "../config/database.php";

// APRÈS
$isAdmin = ($_SESSION["role"] === "admin");
$userId  = $_SESSION["id"];

// Total réservations
if ($isAdmin) {
    $total = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
} else {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE utilisateur_id = ?");
    $stmt->execute([$userId]); $total = $stmt->fetchColumn();
}

// ✅ Salle (cours/tp/reunion) la plus réservée — sans filtre statut
if ($isAdmin) {
    $stmt = $pdo->query("SELECT s.nom, s.type_salle, COUNT(*) AS total
        FROM reservations r JOIN salles s ON r.salle_id=s.id
        WHERE s.type_salle IN ('cours','tp','reunion')
        GROUP BY s.id ORDER BY total DESC LIMIT 1");
} else {
    $stmt = $pdo->prepare("SELECT s.nom, s.type_salle, COUNT(*) AS total
        FROM reservations r JOIN salles s ON r.salle_id=s.id
        WHERE r.utilisateur_id=? AND s.type_salle IN ('cours','tp','reunion')
        GROUP BY s.id ORDER BY total DESC LIMIT 1");
    $stmt->execute([$userId]);
}
$salleTop = $stmt->fetch();

// ✅ Amphi le plus réservé — séparé des salles
if ($isAdmin) {
    $stmt = $pdo->query("SELECT s.nom, COUNT(*) AS total
        FROM reservations r JOIN salles s ON r.salle_id=s.id
        WHERE s.type_salle = 'amphi'
        GROUP BY s.id ORDER BY total DESC LIMIT 1");
} else {
    $stmt = $pdo->prepare("SELECT s.nom, COUNT(*) AS total
        FROM reservations r JOIN salles s ON r.salle_id=s.id
        WHERE r.utilisateur_id=? AND s.type_salle = 'amphi'
        GROUP BY s.id ORDER BY total DESC LIMIT 1");
    $stmt->execute([$userId]);
}
$amphiTop = $stmt->fetch();

// ✅ Heure la plus demandée — sans filtre statut
// APRÈS ✅
if ($isAdmin) {
    $stmt = $pdo->query("SELECT HOUR(heure_debut) AS heure, HOUR(heure_fin) AS heure_fin, COUNT(*) AS total
        FROM reservations
        GROUP BY HOUR(heure_debut), HOUR(heure_fin) ORDER BY total DESC LIMIT 1");
} else {
    $stmt = $pdo->prepare("SELECT HOUR(heure_debut) AS heure, HOUR(heure_fin) AS heure_fin, COUNT(*) AS total
        FROM reservations WHERE utilisateur_id=?
        GROUP BY HOUR(heure_debut), HOUR(heure_fin) ORDER BY total DESC LIMIT 1");
    $stmt->execute([$userId]);
}
$heureTendance = $stmt->fetch();

// ✅ Utilisateur le plus actif (admin)
$userTop = null;
if ($isAdmin) {
    $userTop = $pdo->query("SELECT u.nom, u.type_client, COUNT(r.id) AS total
        FROM reservations r JOIN utilisateurs u ON r.utilisateur_id=u.id
        GROUP BY u.id ORDER BY total DESC LIMIT 1")->fetch();
}

// Répartition par statut
if ($isAdmin) {
    $repartition = $pdo->query("SELECT statut, COUNT(*) AS total FROM reservations GROUP BY statut")->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT statut, COUNT(*) AS total FROM reservations WHERE utilisateur_id=? GROUP BY statut");
    $stmt->execute([$userId]); $repartition = $stmt->fetchAll();
}

// Top 10 utilisateurs (admin)
$topUsers = [];
if ($isAdmin) {
    $topUsers = $pdo->query("SELECT u.nom, u.email, u.type_client, COUNT(r.id) AS total
        FROM reservations r JOIN utilisateurs u ON r.utilisateur_id=u.id
        GROUP BY u.id ORDER BY total DESC LIMIT 10")->fetchAll();
}

// ✅ Top 5 salles (admin)
$topSalles = [];
if ($isAdmin) {
    $topSalles = $pdo->query("SELECT s.nom, s.type_salle, COUNT(r.id) AS total
        FROM reservations r JOIN salles s ON r.salle_id=s.id
        GROUP BY s.id ORDER BY total DESC LIMIT 5")->fetchAll();
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
 <!-- APRÈS -->
<div class="row g-3 mb-4">

  <div class="col-6 col-lg-3">
    <div class="card shadow-sm border-0 text-center p-3 h-100">
      <div class="fs-1 mb-1">📋</div>
      <div class="display-6 fw-bold text-primary"><?= (int)$total ?></div>
      <div class="text-muted small mt-1">Total réservations</div>
    </div>
  </div>

  <div class="col-6 col-lg-3">
    <div class="card shadow-sm border-0 text-center p-3 h-100">
      <div class="fs-1 mb-1">🏫</div>
      <div class="fw-bold text-success fs-5">
        <?= $salleTop ? htmlspecialchars($salleTop["nom"]) : "—" ?>
      </div>
      <div class="text-muted small mt-1">
        Salle la plus réservée
        <?php if ($salleTop): ?>
          <span class="badge bg-success ms-1"><?= (int)$salleTop["total"] ?> résas</span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-6 col-lg-3">
    <div class="card shadow-sm border-0 text-center p-3 h-100">
      <div class="fs-1 mb-1">🎓</div>
      <div class="fw-bold text-danger fs-5">
        <?= $amphiTop ? htmlspecialchars($amphiTop["nom"]) : "—" ?>
      </div>
      <div class="text-muted small mt-1">
        Amphi le plus réservé
        <?php if ($amphiTop): ?>
          <span class="badge bg-danger ms-1"><?= (int)$amphiTop["total"] ?> résas</span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-6 col-lg-3">
    <div class="card shadow-sm border-0 text-center p-3 h-100">
      <div class="fs-1 mb-1">🕐</div>
      <div class="fw-bold text-warning fs-5">
        
<?php if ($heureTendance):
  $h  = (int)$heureTendance["heure"];
  $hf = (int)$heureTendance["heure_fin"];
  echo sprintf("%02dh00 – %02dh00", $h, $hf);
else: echo "—"; endif; ?>
      </div>
      <div class="text-muted small mt-1">
        Créneau le plus demandé
        <?php if ($heureTendance): ?>
          <span class="badge bg-warning text-dark ms-1"><?= (int)$heureTendance["total"] ?> résas</span>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>
<!-- ✅ NOUVEAU : Utilisateur le plus actif + Top 5 salles -->
<?php if ($isAdmin && $userTop): ?>
<div class="row g-3 mb-4">
  <div class="col-12 col-md-6">
    <div class="card shadow-sm border-0 p-3 d-flex flex-row align-items-center gap-3">
      <div style="font-size:3rem">🏆</div>
      <div>
        <div class="fw-bold fs-5"><?= htmlspecialchars($userTop["nom"]) ?></div>
        <?php $tl = ['prof'=>'👨‍🏫 Prof','etudiant'=>'🎓 Étudiant','autre'=>'Autre']; ?>
        <div class="text-muted small"><?= $tl[$userTop["type_client"]] ?? "—" ?></div>
        <span class="badge bg-primary mt-1"><?= (int)$userTop["total"] ?> réservations — utilisateur le plus actif</span>
      </div>
    </div>
  </div>

  <?php if (!empty($topSalles)): ?>
  <div class="col-12 col-md-6">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-header bg-white fw-semibold border-bottom small">
        <i class="bi bi-building text-primary me-1"></i> Top salles
      </div>
      <div class="card-body p-2">
        <?php
        $maxT = max(array_column($topSalles, 'total'));
        $icones = ['cours'=>'🏫','tp'=>'🔬','reunion'=>'🤝','amphi'=>'🎓'];
        foreach ($topSalles as $s):
          $pct = $maxT > 0 ? round($s['total']*100/$maxT) : 0;
        ?>
        <div class="mb-2">
          <div class="d-flex justify-content-between small mb-1">
            <span><?= $icones[$s['type_salle']] ?? '🏛️' ?> <?= htmlspecialchars($s['nom']) ?></span>
            <span class="text-muted"><?= (int)$s['total'] ?></span>
          </div>
          <div class="progress" style="height:10px">
            <div class="progress-bar bg-primary" style="width:<?= $pct ?>%"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>
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
