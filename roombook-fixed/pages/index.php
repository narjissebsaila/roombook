<?php
/*
    pages/index.php — Liste des réservations
*/
require_once "../auth/guard.php";
require_once "../config/database.php";

$isAdmin = ($_SESSION["role"] === "admin");
$salles  = $pdo->query("SELECT * FROM salles ORDER BY nom")->fetchAll();

$pageTitle    = "Réservations";
$pageHeading  = "RoomBook";
$pageSubtitle = $isAdmin ? "Toutes les réservations" : "Vos réservations";

require_once "../includes/header.php";
?>

<?php
/* Génère les <option> heures 00-23 */
function optionsHeures($selected='') {
  $out = '';
  for ($h=0; $h<24; $h++) {
    $v = str_pad($h,2,'0',STR_PAD_LEFT);
    $sel = ($v === $selected) ? ' selected' : '';
    $out .= "<option value=\"$v\"$sel>$v</option>";
  }
  return $out;
}
/* Génère les <option> minutes 00,15,30,45 */
function optionsMinutes($selected='00') {
  $out = '';
  foreach (['00','15','30','45'] as $m) {
    $sel = ($m === $selected) ? ' selected' : '';
    $out .= "<option value=\"$m\"$sel>$m</option>";
  }
  return $out;
}
?>

<style>
.time-picker { display:flex; align-items:center; gap:4px; }
.time-picker select { width:68px; }
.time-picker span { font-weight:bold; font-size:1.1rem; line-height:1; }
</style>

<div class="container-fluid px-4 pb-5">

  <!-- Barre d'outils -->
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h2 class="mb-0 fs-5 fw-bold">
      <i class="bi bi-calendar3 text-primary me-1"></i>
      <?= $isAdmin ? "Toutes les réservations" : "Mes réservations" ?>
    </h2>
    <div class="d-flex gap-2 flex-wrap">
      <input type="text" id="searchInput" class="form-control form-control-sm" style="width:220px"
             placeholder="🔍 Rechercher...">
      <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjouter">
        <i class="bi bi-plus-lg"></i> Nouvelle réservation
      </button>
    </div>
  </div>

  <!-- Tableau -->
  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="reservationTable">
          <thead class="table-primary">
            <tr>
              <?php if ($isAdmin): ?>
                <th>Utilisateur</th><th>Type</th>
              <?php endif; ?>
              <th>Salle</th><th>Date</th><th>Début</th><th>Fin</th>
              <th>Responsable</th><th>Motif</th><th>Statut</th><th>Actions</th>
            </tr>
          </thead>
          <tbody id="tbodyResa">
            <tr><td colspan="<?= $isAdmin ? 10 : 8 ?>" class="text-center py-4 text-muted">
              <div class="spinner-border spinner-border-sm text-primary me-2"></div> Chargement...
            </td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Pagination -->
  <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
    <small class="text-muted" id="paginationInfo"></small>
    <nav><ul class="pagination pagination-sm mb-0" id="pagination"></ul></nav>
  </div>

</div>

<!-- ══════════════════════════════════════
     MODAL — AJOUTER
══════════════════════════════════════ -->
<div class="modal fade" id="modalAjouter" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nouvelle réservation</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formAjouter" class="row g-3">

          <div class="col-12">
            <label class="form-label fw-semibold">Salle *</label>
            <select name="salle_id" class="form-select" required>
              <option value="">-- Choisir une salle --</option>
              <?php foreach ($salles as $s): ?>
                <option value="<?= $s['id'] ?>">
                  <?= htmlspecialchars($s['nom']) ?> — capacité <?= $s['capacite'] ?> (<?= htmlspecialchars($s['localisation']??'') ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-semibold">Date *</label>
            <input type="date" name="date_reservation" class="form-control"
                   min="<?= date('Y-m-d') ?>" required>
          </div>

          <!-- Heure début : selects manuels -->
          <div class="col-md-4">
            <label class="form-label fw-semibold">Heure début *</label>
            <div class="time-picker">
              <select id="add_h_debut" class="form-select">
                <?= optionsHeures('08') ?>
              </select>
              <span>:</span>
              <select id="add_m_debut" class="form-select">
                <?= optionsMinutes('00') ?>
              </select>
            </div>
            <input type="hidden" name="heure_debut" id="add_heure_debut">
          </div>

          <!-- Heure fin : selects manuels -->
          <div class="col-md-4">
            <label class="form-label fw-semibold">Heure fin *</label>
            <div class="time-picker">
              <select id="add_h_fin" class="form-select">
                <?= optionsHeures('10') ?>
              </select>
              <span>:</span>
              <select id="add_m_fin" class="form-select">
                <?= optionsMinutes('00') ?>
              </select>
            </div>
            <input type="hidden" name="heure_fin" id="add_heure_fin">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Responsable *</label>
            <input type="text" name="responsable" class="form-control"
                   value="<?= htmlspecialchars($_SESSION['nom']) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Motif</label>
            <input type="text" name="motif" class="form-control" placeholder="Cours, TP, réunion...">
          </div>

          <?php if ($isAdmin): ?>
          <div class="col-12">
            <label class="form-label fw-semibold">Statut</label>
            <select name="statut" class="form-select">
              <option value="en_attente">En attente</option>
              <option value="confirmee" selected>Confirmée</option>
              <option value="refusee">Refusée</option>
              <option value="annulee">Annulée</option>
            </select>
          </div>
          <?php else: ?>
          <input type="hidden" name="statut" value="en_attente">
          <div class="col-12">
            <div class="alert alert-info py-2 mb-0">
              <i class="bi bi-info-circle me-1"></i>
              Votre réservation sera soumise en attente de validation.
            </div>
          </div>
          <?php endif; ?>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-primary" id="btnSauvegarderAjouter">
          <i class="bi bi-save me-1"></i> Enregistrer
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════
     MODAL — MODIFIER
══════════════════════════════════════ -->
<div class="modal fade" id="modalModifier" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Modifier la réservation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formModifier" class="row g-3">
          <input type="hidden" name="id" id="edit_id">

          <div class="col-12">
            <label class="form-label fw-semibold">Salle *</label>
            <select name="salle_id" id="edit_salle_id" class="form-select" required>
              <?php foreach ($salles as $s): ?>
                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nom']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-semibold">Date *</label>
            <input type="date" name="date_reservation" id="edit_date" class="form-control" required>
          </div>

          <!-- Heure début modifier -->
          <div class="col-md-4">
            <label class="form-label fw-semibold">Heure début *</label>
            <div class="time-picker">
              <select id="edit_h_debut" class="form-select">
                <?= optionsHeures() ?>
              </select>
              <span>:</span>
              <select id="edit_m_debut" class="form-select">
                <?= optionsMinutes() ?>
              </select>
            </div>
            <input type="hidden" name="heure_debut" id="edit_heure_debut">
          </div>

          <!-- Heure fin modifier -->
          <div class="col-md-4">
            <label class="form-label fw-semibold">Heure fin *</label>
            <div class="time-picker">
              <select id="edit_h_fin" class="form-select">
                <?= optionsHeures() ?>
              </select>
              <span>:</span>
              <select id="edit_m_fin" class="form-select">
                <?= optionsMinutes() ?>
              </select>
            </div>
            <input type="hidden" name="heure_fin" id="edit_heure_fin">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Responsable *</label>
            <input type="text" name="responsable" id="edit_responsable" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Motif</label>
            <input type="text" name="motif" id="edit_motif" class="form-control">
          </div>

          <?php if ($isAdmin): ?>
          <div class="col-12">
            <label class="form-label fw-semibold">Statut</label>
            <select name="statut" id="edit_statut" class="form-select">
              <option value="en_attente">En attente</option>
              <option value="confirmee">Confirmée</option>
              <option value="refusee">Refusée</option>
              <option value="annulee">Annulée</option>
            </select>
          </div>
          <?php endif; ?>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-warning" id="btnSauvegarderModifier">
          <i class="bi bi-save me-1"></i> Enregistrer
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════
     MODAL — CONFIRMER suppression
══════════════════════════════════════ -->
<div class="modal fade" id="modalSupprimer" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="bi bi-trash me-2"></i>Supprimer</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">Confirmer la suppression de cette réservation ?</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-danger" id="btnConfirmerSupprimer">
          <i class="bi bi-trash me-1"></i> Supprimer
        </button>
      </div>
    </div>
  </div>
</div>

<script>
const IS_ADMIN   = <?= $isAdmin ? 'true' : 'false' ?>;
const SESSION_ID = <?= (int)$_SESSION['id'] ?>;

const statutLabels = {
  en_attente: '<span class="badge bg-warning text-dark">En attente</span>',
  confirmee:  '<span class="badge bg-success">Confirmée</span>',
  refusee:    '<span class="badge bg-danger">Refusée</span>',
  annulee:    '<span class="badge bg-secondary">Annulée</span>',
};
const typeLabels = { prof:'👨‍🏫 Prof', etudiant:'🎓 Étudiant', autre:'Autre' };

let currentPage = 1;
let pendingDeleteId = null;

// ── Sync hidden inputs depuis les selects heure ──────────────
function syncTime(hSel, mSel, hiddenId) {
  const h = document.getElementById(hSel).value;
  const m = document.getElementById(mSel).value;
  document.getElementById(hiddenId).value = h + ':' + m;
}
function syncAllTimes() {
  syncTime('add_h_debut',  'add_m_debut',  'add_heure_debut');
  syncTime('add_h_fin',    'add_m_fin',    'add_heure_fin');
  syncTime('edit_h_debut', 'edit_m_debut', 'edit_heure_debut');
  syncTime('edit_h_fin',   'edit_m_fin',   'edit_heure_fin');
}
// Sync à chaque changement de select
['add_h_debut','add_m_debut','add_h_fin','add_m_fin',
 'edit_h_debut','edit_m_debut','edit_h_fin','edit_m_fin'].forEach(id => {
  document.getElementById(id).addEventListener('change', syncAllTimes);
});
syncAllTimes(); // init au chargement

// ── Charger l'heure dans les selects (modal modifier) ────────
function setTimePicker(prefix, timeStr) {
  // timeStr format: "HH:MM" ou "HH:MM:SS"
  if (!timeStr) return;
  const parts = timeStr.split(':');
  const h = parts[0] ? parts[0].padStart(2,'0') : '08';
  const m = parts[1] ? parts[1].substring(0,2) : '00';
  // Trouver la minute la plus proche parmi 00,15,30,45
  const mOptions = ['00','15','30','45'];
  const mVal = mOptions.reduce((prev,curr) =>
    Math.abs(parseInt(curr)-parseInt(m)) < Math.abs(parseInt(prev)-parseInt(m)) ? curr : prev
  );
  document.getElementById(prefix + '_h').value = h;
  document.getElementById(prefix + '_m').value = mVal;
  syncTime(prefix + '_h', prefix + '_m', prefix.replace('edit_','edit_heure_').replace('add_','add_heure_'));
}

// ── showToast ────────────────────────────────────────────────
function showToast(msg, type='success') {
  const id = 'toast_' + Date.now();
  const bg  = type === 'success' ? 'bg-success' : 'bg-danger';
  const icon = type === 'success' ? 'bi-check-circle' : 'bi-x-circle';
  document.getElementById('toastContainer').insertAdjacentHTML('beforeend', `
    <div id="${id}" class="toast align-items-center text-white ${bg} border-0" role="alert">
      <div class="d-flex">
        <div class="toast-body"><i class="bi ${icon} me-2"></i>${msg}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>`);
  const el = document.getElementById(id);
  new bootstrap.Toast(el, {delay:3500}).show();
  el.addEventListener('hidden.bs.toast', () => el.remove());
}

// ── loadTable ────────────────────────────────────────────────
function loadTable(page=1) {
  currentPage = page;
  const search = document.getElementById('searchInput').value.trim();
  const tbody  = document.getElementById('tbodyResa');
  const cols   = IS_ADMIN ? 10 : 8;
  tbody.innerHTML = `<tr><td colspan="${cols}" class="text-center py-4 text-muted">
    <div class="spinner-border spinner-border-sm text-primary me-2"></div> Chargement...</td></tr>`;

  fetch(`../api/reservations.php?action=list&page=${page}&search=${encodeURIComponent(search)}`)
    .then(r => r.json())
    .then(data => {
      if (!data.ok) { showToast(data.msg, 'error'); return; }
      renderTable(data.rows);
      renderPagination(data.page, data.pages, data.total);
    })
    .catch(() => showToast('Erreur de chargement.','error'));
}

// ── renderTable ──────────────────────────────────────────────
function renderTable(rows) {
  const tbody = document.getElementById('tbodyResa');
  if (!rows.length) {
    tbody.innerHTML = `<tr><td colspan="${IS_ADMIN?10:8}" class="text-center py-5 text-muted">
      Aucune réservation trouvée.</td></tr>`;
    return;
  }
  tbody.innerHTML = rows.map(r => {
    const peutModifier = IS_ADMIN || r.utilisateur_id == SESSION_ID;
    const adminCols = IS_ADMIN
      ? `<td>${r.nom_utilisateur??'—'}</td><td>${typeLabels[r.type_client]??'—'}</td>` : '';
    const statSelect = IS_ADMIN
      ? `<select class="form-select form-select-sm statut-select" data-id="${r.id}" style="width:140px">
          ${['en_attente','confirmee','refusee','annulee'].map(s =>
            `<option value="${s}" ${r.statut===s?'selected':''}>${s.replace('_',' ')}</option>`
          ).join('')}
         </select>`
      : (statutLabels[r.statut] ?? r.statut);

    const actions = peutModifier
      ? `<div class="d-flex gap-1 flex-wrap">
           <button class="btn btn-warning btn-sm btn-modifier" data-r='${JSON.stringify(r)}'>
             <i class="bi bi-pencil"></i>
           </button>
           <button class="btn btn-danger btn-sm btn-supprimer" data-id="${r.id}">
             <i class="bi bi-trash"></i>
           </button>
         </div>`
      : '<span class="text-muted">—</span>';

    return `<tr>
      ${adminCols}
      <td><strong>${r.nom_salle}</strong></td>
      <td>${r.date_reservation.split('-').reverse().join('/')}</td>
      <td>${r.heure_debut.substring(0,5)}</td>
      <td>${r.heure_fin.substring(0,5)}</td>
      <td>${r.responsable}</td>
      <td>${r.motif??'—'}</td>
      <td>${statSelect}</td>
      <td>${actions}</td>
    </tr>`;
  }).join('');

  // Statut change (admin)
  document.querySelectorAll('.statut-select').forEach(sel => {
    sel.addEventListener('change', function() {
      const fd = new FormData();
      fd.append('action','update_statut');
      fd.append('id', this.dataset.id);
      fd.append('statut', this.value);
      fetch('../api/reservations.php', {method:'POST', body:fd})
        .then(r=>r.json()).then(d => showToast(d.msg, d.ok?'success':'error'));
    });
  });

  // Modifier
  document.querySelectorAll('.btn-modifier').forEach(btn => {
    btn.addEventListener('click', function() {
      const r = JSON.parse(this.dataset.r);
      document.getElementById('edit_id').value          = r.id;
      document.getElementById('edit_salle_id').value    = r.salle_id;
      document.getElementById('edit_date').value        = r.date_reservation;
      document.getElementById('edit_responsable').value = r.responsable;
      document.getElementById('edit_motif').value       = r.motif??'';
      if (IS_ADMIN) document.getElementById('edit_statut').value = r.statut;

      // Charger les heures dans les selects
      const hd = r.heure_debut.substring(0,5).split(':');
      const hf = r.heure_fin.substring(0,5).split(':');
      document.getElementById('edit_h_debut').value = hd[0];
      document.getElementById('edit_m_debut').value = hd[1] || '00';
      document.getElementById('edit_h_fin').value   = hf[0];
      document.getElementById('edit_m_fin').value   = hf[1] || '00';
      syncTime('edit_h_debut','edit_m_debut','edit_heure_debut');
      syncTime('edit_h_fin',  'edit_m_fin',  'edit_heure_fin');

      new bootstrap.Modal(document.getElementById('modalModifier')).show();
    });
  });

  // Supprimer
  document.querySelectorAll('.btn-supprimer').forEach(btn => {
    btn.addEventListener('click', function() {
      pendingDeleteId = this.dataset.id;
      new bootstrap.Modal(document.getElementById('modalSupprimer')).show();
    });
  });
}

// ── renderPagination ─────────────────────────────────────────
function renderPagination(page, pages, total) {
  document.getElementById('paginationInfo').textContent =
    `${total} réservation(s) — page ${page} / ${pages||1}`;
  const ul = document.getElementById('pagination');
  ul.innerHTML = '';
  for (let i=1; i<=pages; i++) {
    ul.insertAdjacentHTML('beforeend',
      `<li class="page-item ${i===page?'active':''}">
        <button class="page-link" data-p="${i}">${i}</button>
       </li>`);
  }
  ul.querySelectorAll('button').forEach(b =>
    b.addEventListener('click', () => loadTable(+b.dataset.p)));
}

// ── Recherche ────────────────────────────────────────────────
let searchTimer;
document.getElementById('searchInput').addEventListener('keyup', () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => loadTable(1), 300);
});

// ── Ajouter ──────────────────────────────────────────────────
document.getElementById('btnSauvegarderAjouter').addEventListener('click', () => {
  syncAllTimes();
  const fd = new FormData(document.getElementById('formAjouter'));
  fetch('../api/store_reservation.php', {method:'POST', body:fd})
    .then(r=>r.json()).then(d => {
      showToast(d.msg, d.ok?'success':'error');
      if (d.ok) {
        bootstrap.Modal.getInstance(document.getElementById('modalAjouter')).hide();
        document.getElementById('formAjouter').reset();
        syncAllTimes();
        loadTable(1);
      }
    });
});

// ── Modifier ─────────────────────────────────────────────────
document.getElementById('btnSauvegarderModifier').addEventListener('click', () => {
  syncAllTimes();
  const fd = new FormData(document.getElementById('formModifier'));
  fetch('../api/update_reservation.php', {method:'POST', body:fd})
    .then(r=>r.json()).then(d => {
      showToast(d.msg, d.ok?'success':'error');
      if (d.ok) {
        bootstrap.Modal.getInstance(document.getElementById('modalModifier')).hide();
        loadTable(currentPage);
      }
    });
});

// ── Supprimer ────────────────────────────────────────────────
document.getElementById('btnConfirmerSupprimer').addEventListener('click', () => {
  const fd = new FormData();
  fd.append('action','delete'); fd.append('id', pendingDeleteId);
  fetch('../api/reservations.php', {method:'POST', body:fd})
    .then(r=>r.json()).then(d => {
      showToast(d.msg, d.ok?'success':'error');
      bootstrap.Modal.getInstance(document.getElementById('modalSupprimer')).hide();
      if (d.ok) loadTable(currentPage);
    });
});

// ── Init ─────────────────────────────────────────────────────
loadTable(1);
</script>

<?php require_once "../includes/footer.php"; ?>
