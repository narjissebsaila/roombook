<?php
/*  pages/salles.php — Gestion des salles (AJAX + modals)  */
require_once "../auth/guard.php";
require_once "../config/database.php";

$isAdmin   = ($_SESSION["role"] === "admin");
$pageTitle = "Salles";
require_once "../includes/header.php";
?>

<div class="container-fluid px-4 pb-5">

  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h2 class="mb-0 fs-5 fw-bold">
      <i class="bi bi-building text-primary me-1"></i> Salles disponibles
    </h2>
    <?php if ($isAdmin): ?>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterSalle">
      <i class="bi bi-plus-lg"></i> Nouvelle salle
    </button>
    <?php endif; ?>
  </div>

  <div id="sallesGrid" class="row g-3">
    <div class="col-12 text-center py-5 text-muted">
      <div class="spinner-border text-primary"></div>
    </div>
  </div>

</div>

<?php if ($isAdmin): ?>
<!-- MODAL AJOUTER -->
<div class="modal fade" id="modalAjouterSalle" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nouvelle salle</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formAjouterSalle" class="row g-3">
          <div class="col-md-8">
            <label class="form-label fw-semibold">Nom *</label>
            <input type="text" name="nom" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Capacité *</label>
            <input type="number" name="capacite" class="form-control" min="1" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Localisation</label>
            <input type="text" name="localisation" class="form-control" placeholder="Bloc A...">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Type</label>
            <select name="type_salle" class="form-select">
              <option value="cours">Cours</option>
              <option value="tp">TP</option>
              <option value="reunion">Réunion</option>
              <option value="amphi">Amphi</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Équipements</label>
            <input type="text" name="equipements" class="form-control" placeholder="Projecteur, Tableau...">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button class="btn btn-primary" id="btnSauvegarderSalle">
          <i class="bi bi-save me-1"></i> Enregistrer
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL MODIFIER -->
<div class="modal fade" id="modalModifierSalle" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Modifier la salle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formModifierSalle" class="row g-3">
          <input type="hidden" name="id" id="edit_salle_id">
          <div class="col-md-8">
            <label class="form-label fw-semibold">Nom *</label>
            <input type="text" name="nom" id="edit_salle_nom" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Capacité *</label>
            <input type="number" name="capacite" id="edit_salle_capacite" class="form-control" min="1" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Localisation</label>
            <input type="text" name="localisation" id="edit_salle_localisation" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Type</label>
            <select name="type_salle" id="edit_salle_type" class="form-select">
              <option value="cours">Cours</option>
              <option value="tp">TP</option>
              <option value="reunion">Réunion</option>
              <option value="amphi">Amphi</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Équipements</label>
            <input type="text" name="equipements" id="edit_salle_equip" class="form-control">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button class="btn btn-warning" id="btnSauvegarderModifierSalle">
          <i class="bi bi-save me-1"></i> Enregistrer
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL SUPPRIMER -->
<div class="modal fade" id="modalSupprimerSalle" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="bi bi-trash me-2"></i>Supprimer</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">Confirmer la suppression de cette salle ?</div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button class="btn btn-danger" id="btnConfirmerSupprimerSalle">
          <i class="bi bi-trash me-1"></i> Supprimer
        </button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
const IS_ADMIN = <?= $isAdmin ? 'true' : 'false' ?>;
let pendingDeleteSalleId = null;

const typeIcons = { cours:'🏫', tp:'💻', reunion:'🤝', amphi:'🎓' };
const typeColors = { cours:'primary', tp:'success', reunion:'warning', amphi:'danger' };

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

function loadSalles() {
  fetch('../api/salles.php?action=list')
    .then(r=>r.json()).then(data => {
      if (!data.ok) { showToast(data.msg,'error'); return; }
      const grid = document.getElementById('sallesGrid');
      if (!data.rows.length) {
        grid.innerHTML = '<div class="col-12 text-center text-muted py-5">Aucune salle enregistrée.</div>';
        return;
      }
      grid.innerHTML = data.rows.map(s => `
        <div class="col-sm-6 col-lg-4 col-xl-3">
          <div class="card h-100 shadow-sm border-0 room-card">
            <div class="card-body text-center">
              <div class="display-4 mb-2">${typeIcons[s.type_salle]??'🏛️'}</div>
              <h5 class="card-title fw-bold">${s.nom}</h5>
              <span class="badge bg-${typeColors[s.type_salle]??'secondary'} mb-2">${s.type_salle}</span>
              <p class="text-muted small mb-1"><i class="bi bi-people me-1"></i>${s.capacite} places</p>
              <p class="text-muted small mb-1"><i class="bi bi-geo-alt me-1"></i>${s.localisation??'—'}</p>
              <p class="text-muted small fst-italic">${s.equipements??'—'}</p>
              ${IS_ADMIN ? `
              <div class="d-flex gap-2 justify-content-center mt-3">
                <button class="btn btn-warning btn-sm btn-edit-salle" data-s='${JSON.stringify(s)}'>
                  <i class="bi bi-pencil"></i> Modifier
                </button>
                <button class="btn btn-danger btn-sm btn-del-salle" data-id="${s.id}">
                  <i class="bi bi-trash"></i> Supprimer
                </button>
              </div>` : ''}
            </div>
          </div>
        </div>`).join('');

      if (IS_ADMIN) {
        document.querySelectorAll('.btn-edit-salle').forEach(btn => {
          btn.addEventListener('click', function() {
            const s = JSON.parse(this.dataset.s);
            document.getElementById('edit_salle_id').value          = s.id;
            document.getElementById('edit_salle_nom').value         = s.nom;
            document.getElementById('edit_salle_capacite').value    = s.capacite;
            document.getElementById('edit_salle_localisation').value= s.localisation??'';
            document.getElementById('edit_salle_type').value        = s.type_salle;
            document.getElementById('edit_salle_equip').value       = s.equipements??'';
            new bootstrap.Modal(document.getElementById('modalModifierSalle')).show();
          });
        });
        document.querySelectorAll('.btn-del-salle').forEach(btn => {
          btn.addEventListener('click', function() {
            pendingDeleteSalleId = this.dataset.id;
            new bootstrap.Modal(document.getElementById('modalSupprimerSalle')).show();
          });
        });
      }
    });
}

if (IS_ADMIN) {
  document.getElementById('btnSauvegarderSalle').addEventListener('click', () => {
    const fd = new FormData(document.getElementById('formAjouterSalle'));
    fd.append('action','store');
    fetch('../api/salles.php', {method:'POST',body:fd})
      .then(r=>r.json()).then(d => {
        showToast(d.msg, d.ok?'success':'error');
        if (d.ok) {
          bootstrap.Modal.getInstance(document.getElementById('modalAjouterSalle')).hide();
          document.getElementById('formAjouterSalle').reset();
          loadSalles();
        }
      });
  });

  document.getElementById('btnSauvegarderModifierSalle').addEventListener('click', () => {
    const fd = new FormData(document.getElementById('formModifierSalle'));
    fd.append('action','update');
    fetch('../api/salles.php', {method:'POST',body:fd})
      .then(r=>r.json()).then(d => {
        showToast(d.msg, d.ok?'success':'error');
        if (d.ok) {
          bootstrap.Modal.getInstance(document.getElementById('modalModifierSalle')).hide();
          loadSalles();
        }
      });
  });

  document.getElementById('btnConfirmerSupprimerSalle').addEventListener('click', () => {
    const fd = new FormData();
    fd.append('action','delete'); fd.append('id', pendingDeleteSalleId);
    fetch('../api/salles.php', {method:'POST',body:fd})
      .then(r=>r.json()).then(d => {
        showToast(d.msg, d.ok?'success':'error');
        bootstrap.Modal.getInstance(document.getElementById('modalSupprimerSalle')).hide();
        if (d.ok) loadSalles();
      });
  });
}

loadSalles();
</script>

<?php require_once "../includes/footer.php"; ?>
