<?php
require_once "../auth/guard.php";
require_once "../config/database.php";

$userId = $_SESSION["id"];
$stmt   = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$userId]); $user = $stmt->fetch();
if (!$user) { header("Location: ../auth/logout.php"); exit; }

$stmt = $pdo->prepare("SELECT COUNT(*) AS total, SUM(statut='confirmee') AS confirmees, SUM(statut='en_attente') AS attente, SUM(statut='refusee') AS refusees, SUM(statut='annulee') AS annulees FROM reservations WHERE utilisateur_id=?");
$stmt->execute([$userId]); $stats = $stmt->fetch();

$globalStats = null;
if ($user["role"] === "admin") {
    $globalStats = [
        "total_users"  => $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn(),
        "total_profs"  => $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE type_client='prof'")->fetchColumn(),
        "total_etu"    => $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE type_client='etudiant'")->fetchColumn(),
        "total_salles" => $pdo->query("SELECT COUNT(*) FROM salles")->fetchColumn(),
        "total_resa"   => $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn(),
        "en_attente"   => $pdo->query("SELECT COUNT(*) FROM reservations WHERE statut='en_attente'")->fetchColumn(),
    ];
}

$stmt = $pdo->prepare("SELECT r.*, s.nom AS nom_salle FROM reservations r INNER JOIN salles s ON r.salle_id=s.id WHERE r.utilisateur_id=? ORDER BY r.date_reservation DESC, r.heure_debut DESC LIMIT 5");
$stmt->execute([$userId]); $dernieres = $stmt->fetchAll();

$icons  = ['admin'=>'👨‍💼','prof'=>'👨‍🏫','etudiant'=>'🎓'];
$labels = ['admin'=>'Administrateur','prof'=>'Professeur','etudiant'=>'Étudiant'];
$key    = $user["role"]==="admin" ? "admin" : ($user["type_client"]==="prof" ? "prof" : "etudiant");
$profileIcon  = $icons[$key];
$profileLabel = $labels[$key];

$bgMap = ['admin'=>'bg-danger','prof'=>'bg-success','etudiant'=>'bg-primary'];
$profileBg = $bgMap[$key];

$pageTitle   = "Mon profil";
$pageHeading = "RoomBook";
require_once "../includes/header.php";
?>

<div class="container-fluid px-4 pb-5">

  <!-- Bannière profil -->
  <div class="rounded-3 p-4 text-white mb-4 d-flex align-items-center gap-4 flex-wrap <?= $profileBg ?>"
       style="background:linear-gradient(135deg,var(--bs-<?= $key==='admin'?'danger':($key==='prof'?'success':'primary') ?>),#1e40af)">
    <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center"
         style="width:90px;height:90px;font-size:3rem"><?= $profileIcon ?></div>
    <div>
      <h3 class="mb-1"><?= htmlspecialchars($user["nom"]) ?></h3>
      <span class="badge bg-white bg-opacity-25 text-white mb-2"><?= $profileLabel ?></span>
      <p class="mb-1 small opacity-90"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($user["email"]) ?></p>
      <?php if ($user["departement"]): ?><p class="mb-1 small opacity-90"><i class="bi bi-building me-1"></i><?= htmlspecialchars($user["departement"]) ?></p><?php endif; ?>
      <?php if ($user["telephone"]): ?><p class="mb-0 small opacity-90"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($user["telephone"]) ?></p><?php endif; ?>
    </div>
  </div>

  <!-- Stats -->
  <div class="row g-3 mb-4">
    <?php if ($user["role"]==="admin" && $globalStats): ?>
      <div class="col-sm-4"><div class="card border-0 shadow-sm text-center p-3"><div class="fs-2 fw-bold text-primary"><?= (int)$globalStats["total_users"] ?></div><div class="text-muted small">Utilisateurs</div></div></div>
      <div class="col-sm-4"><div class="card border-0 shadow-sm text-center p-3"><div class="fs-2 fw-bold text-success"><?= (int)$globalStats["total_salles"] ?></div><div class="text-muted small">Salles</div></div></div>
      <div class="col-sm-4"><div class="card border-0 shadow-sm text-center p-3"><div class="fs-2 fw-bold text-warning"><?= (int)$globalStats["en_attente"] ?></div><div class="text-muted small">En attente</div></div></div>
    <?php else: ?>
      <div class="col-sm-3"><div class="card border-0 shadow-sm text-center p-3"><div class="fs-2 fw-bold text-primary"><?= (int)$stats["total"] ?></div><div class="text-muted small">Total</div></div></div>
      <div class="col-sm-3"><div class="card border-0 shadow-sm text-center p-3"><div class="fs-2 fw-bold text-success"><?= (int)$stats["confirmees"] ?></div><div class="text-muted small">Confirmées</div></div></div>
      <div class="col-sm-3"><div class="card border-0 shadow-sm text-center p-3"><div class="fs-2 fw-bold text-warning"><?= (int)$stats["attente"] ?></div><div class="text-muted small">En attente</div></div></div>
      <div class="col-sm-3"><div class="card border-0 shadow-sm text-center p-3"><div class="fs-2 fw-bold text-danger"><?= (int)$stats["refusees"]+(int)$stats["annulees"] ?></div><div class="text-muted small">Refusées/Annulées</div></div></div>
    <?php endif; ?>
  </div>

  <!-- Dernières réservations -->
  <?php if (!empty($dernieres)): ?>
  <div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white fw-semibold border-bottom"><i class="bi bi-clock-history text-primary me-1"></i> Mes dernières réservations</div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>Salle</th><th>Date</th><th>Horaire</th><th>Motif</th><th>Statut</th></tr></thead>
        <tbody>
        <?php
          $sl = ['en_attente'=>'warning text-dark','confirmee'=>'success','refusee'=>'danger','annulee'=>'secondary'];
          $ll = ['en_attente'=>'En attente','confirmee'=>'Confirmée','refusee'=>'Refusée','annulee'=>'Annulée'];
          foreach ($dernieres as $r): ?>
          <tr>
            <td><strong><?= htmlspecialchars($r["nom_salle"]) ?></strong></td>
            <td><?= date("d/m/Y", strtotime($r["date_reservation"])) ?></td>
            <td><?= substr($r["heure_debut"],0,5) ?> → <?= substr($r["heure_fin"],0,5) ?></td>
            <td><?= htmlspecialchars($r["motif"]??"—") ?></td>
            <td><span class="badge bg-<?= $sl[$r["statut"]]??"secondary" ?>"><?= $ll[$r["statut"]]??$r["statut"] ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="card-footer text-center bg-white border-top">
      <a href="index.php" class="btn btn-sm btn-outline-primary">Voir toutes mes réservations</a>
    </div>
  </div>
  <?php endif; ?>

  <!-- Modifier infos + changer mdp côte à côte -->
  <div class="row g-4">
    <div class="col-md-6">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-white fw-semibold border-bottom"><i class="bi bi-person-gear text-primary me-1"></i> Modifier mes informations</div>
        <div class="card-body">
          <form id="formProfile" class="row g-3">
            <div class="col-12"><label class="form-label fw-semibold">Nom complet</label>
              <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($user["nom"]) ?>" required></div>
            <div class="col-12"><label class="form-label fw-semibold">Email</label>
              <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user["email"]) ?>" required></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Téléphone</label>
              <input type="text" name="telephone" class="form-control" value="<?= htmlspecialchars($user["telephone"]??"") ?>"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Département</label>
              <input type="text" name="departement" class="form-control" value="<?= htmlspecialchars($user["departement"]??"") ?>"></div>
            <div class="col-12"><label class="form-label fw-semibold">Bio</label>
              <textarea name="bio" class="form-control" rows="3" placeholder="Quelques mots..."><?= htmlspecialchars($user["bio"]??"") ?></textarea></div>
            <div class="col-12"><button type="button" class="btn btn-primary w-100" id="btnSauvegarderProfil">
              <i class="bi bi-save me-1"></i> Mettre à jour
            </button></div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-white fw-semibold border-bottom"><i class="bi bi-lock text-danger me-1"></i> Changer mon mot de passe</div>
        <div class="card-body">
          <form id="formMdp" class="row g-3">
            <div class="col-12"><label class="form-label fw-semibold">Mot de passe actuel</label>
              <input type="password" name="ancien" class="form-control" required></div>
            <div class="col-12"><label class="form-label fw-semibold">Nouveau mot de passe</label>
              <input type="password" name="nouveau" class="form-control" required minlength="4"></div>
            <div class="col-12"><label class="form-label fw-semibold">Confirmer</label>
              <input type="password" name="confirmation" class="form-control" required minlength="4"></div>
            <div class="col-12"><button type="button" class="btn btn-danger w-100" id="btnChangerMdp">
              <i class="bi bi-shield-lock me-1"></i> Changer le mot de passe
            </button></div>
          </form>
        </div>
      </div>
    </div>
  </div>

</div>

<script>
function showToast(msg, type='success') {
  const id='t'+Date.now(), bg=type==='success'?'bg-success':'bg-danger';
  document.getElementById('toastContainer').insertAdjacentHTML('beforeend',
    `<div id="${id}" class="toast align-items-center text-white ${bg} border-0" role="alert">
       <div class="d-flex"><div class="toast-body">${msg}</div>
       <button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>
     </div>`);
  const el=document.getElementById(id);
  new bootstrap.Toast(el,{delay:3500}).show();
  el.addEventListener('hidden.bs.toast',()=>el.remove());
}

document.getElementById('btnSauvegarderProfil').addEventListener('click', () => {
  const fd = new FormData(document.getElementById('formProfile'));
  fetch('../actions/profile_update.php', {method:'POST', body:fd})
    .then(r => r.url.includes('success=profile')
      ? showToast('Profil mis à jour avec succès.')
      : r.url.includes('error=email')
        ? showToast("Cet email est déjà utilisé.", 'error')
        : showToast('Mise à jour effectuée.'));
});

document.getElementById('btnChangerMdp').addEventListener('click', () => {
  const fd = new FormData(document.getElementById('formMdp'));
  const n  = fd.get('nouveau'), c = fd.get('confirmation');
  if (n !== c) { showToast("Les mots de passe ne correspondent pas.", 'error'); return; }
  fetch('../actions/password_update.php', {method:'POST', body:fd})
    .then(r => r.url.includes('success=password')
      ? (showToast('Mot de passe modifié.'), document.getElementById('formMdp').reset())
      : showToast("Mot de passe actuel incorrect.", 'error'));
});
</script>

<?php require_once "../includes/footer.php"; ?>
