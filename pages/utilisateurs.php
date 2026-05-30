<?php
/*  pages/utilisateurs.php — Gestion des utilisateurs (admin, AJAX + modals)  */
require_once "../auth/guard_admin.php";
require_once "../config/database.php";

$pageTitle = "Utilisateurs";
require_once "../includes/header.php";
?>

<div class="container-fluid px-4 pb-5">

  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h2 class="mb-0 fs-5 fw-bold">
      <i class="bi bi-people text-primary me-1"></i> Gestion des utilisateurs
    </h2>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterUser">
      <i class="bi bi-person-plus"></i> Nouvel utilisateur
    </button>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-primary">
            <tr>
              <th>Nom</th><th>Email</th><th>Rôle</th>
              <th>Département</th><th>Réservations</th><th>Actions</th>
            </tr>
          </thead>
          <tbody id="tbodyUsers">
            <tr><td colspan="6" class="text-center py-4">
              <div class="spinner-border spinner-border-sm text-primary me-2"></div> Chargement...
            </td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- MODAL AJOUTER USER -->
<div class="modal fade" id="modalAjouterUser" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Nouvel utilisateur</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formAjouterUser" class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Nom *</label>
            <input type="text" name="nom" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Email *</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Mot de passe *</label>
            <input type="password" name="mot_de_passe" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Rôle *</label>
            <select name="role" class="form-select" id="ajouterRole">
              <option value="client">Client</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="col-md-6" id="ajouterTypeDiv">
            <label class="form-label fw-semibold">Type</label>
            <select name="type_client" class="form-select">
              <option value="etudiant">Étudiant</option>
              <option value="prof">Professeur</option>
              <option value="autre">Autre</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Département</label>
            <input type="text" name="departement" class="form-control" placeholder="Informatique...">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button class="btn btn-primary" id="btnSauvegarderUser">
          <i class="bi bi-save me-1"></i> Enregistrer
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL MODIFIER USER -->
<div class="modal fade" id="modalModifierUser" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Modifier l'utilisateur</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formModifierUser" class="row g-3">
          <input type="hidden" name="id" id="edit_user_id">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Nom *</label>
            <input type="text" name="nom" id="edit_user_nom" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Email *</label>
            <input type="email" name="email" id="edit_user_email" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Rôle</label>
            <select name="role" id="edit_user_role" class="form-select">
              <option value="client">Client</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Type</label>
            <select name="type_client" id="edit_user_type" class="form-select">
              <option value="etudiant">Étudiant</option>
              <option value="prof">Professeur</option>
              <option value="autre">Autre</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Département</label>
            <input type="text" name="departement" id="edit_user_dept" class="form-control">
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Nouveau mot de passe <small class="text-muted">(laisser vide = inchangé)</small></label>
            <input type="password" name="mot_de_passe" class="form-control" placeholder="••••••••">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button class="btn btn-warning" id="btnSauvegarderModifierUser">
          <i class="bi bi-save me-1"></i> Enregistrer
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL SUPPRIMER USER -->
<div class="modal fade" id="modalSupprimerUser" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="bi bi-trash me-2"></i>Supprimer</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">Supprimer cet utilisateur ? Ses réservations resteront mais sans référence.</div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button class="btn btn-danger" id="btnConfirmerSupprimerUser">
          <i class="bi bi-trash me-1"></i> Supprimer
        </button>
      </div>
    </div>
  </div>
</div>

<script>
let pendingDeleteUserId = null;
const CURRENT_ID = <?= (int)$_SESSION['id'] ?>;

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

function loadUsers() {
  fetch('../api/utilisateurs.php?action=list')
    .then(r=>r.json()).then(data => {
      const tbody = document.getElementById('tbodyUsers');
      if (!data.ok) { showToast(data.msg,'error'); return; }
      tbody.innerHTML = data.rows.map(u => {
        const roleBadge = u.role==='admin'
          ? '<span class="badge bg-danger">Admin</span>'
          : `<span class="badge bg-secondary">${u.type_client==='prof'?'Prof':u.type_client==='etudiant'?'Étudiant':'Autre'}</span>`;
        const isSelf = u.id == CURRENT_ID;
        return `<tr>
          <td><i class="bi bi-person-circle me-1 text-muted"></i>${u.nom}</td>
          <td>${u.email}</td>
          <td>${roleBadge}</td>
          <td>${u.departement??'—'}</td>
          <td><span class="badge bg-info text-dark">${u.nb_reservations}</span></td>
          <td>
            <div class="d-flex gap-1">
              <button class="btn btn-warning btn-sm btn-edit-user" data-u='${JSON.stringify(u).replace(/'/g,"&#39;")}'>
                <i class="bi bi-pencil"></i>
              </button>
              ${!isSelf ? `<button class="btn btn-danger btn-sm btn-del-user" data-id="${u.id}">
                <i class="bi bi-trash"></i>
              </button>` : '<span class="text-muted small">Vous</span>'}
            </div>
          </td>
        </tr>`;
      }).join('');

      document.querySelectorAll('.btn-edit-user').forEach(btn => {
        btn.addEventListener('click', function() {
          const u = JSON.parse(this.dataset.u);
          document.getElementById('edit_user_id').value    = u.id;
          document.getElementById('edit_user_nom').value   = u.nom;
          document.getElementById('edit_user_email').value = u.email;
          document.getElementById('edit_user_role').value  = u.role;
          document.getElementById('edit_user_type').value  = u.type_client??'autre';
          document.getElementById('edit_user_dept').value  = u.departement??'';
          new bootstrap.Modal(document.getElementById('modalModifierUser')).show();
        });
      });

      document.querySelectorAll('.btn-del-user').forEach(btn => {
        btn.addEventListener('click', function() {
          pendingDeleteUserId = this.dataset.id;
          new bootstrap.Modal(document.getElementById('modalSupprimerUser')).show();
        });
      });
    });
}

// Ajouter
document.getElementById('btnSauvegarderUser').addEventListener('click', () => {
  const fd = new FormData(document.getElementById('formAjouterUser'));
  fetch('../actions/user_store.php', {method:'POST', body:fd})
    .then(r=>r.text()).then(t => {
      // user_store.php redirige, on détecte l'erreur via le contenu
      loadUsers();
      bootstrap.Modal.getInstance(document.getElementById('modalAjouterUser')).hide();
      document.getElementById('formAjouterUser').reset();
      showToast('Utilisateur ajouté avec succès.');
    });
});

// Modifier
document.getElementById('btnSauvegarderModifierUser').addEventListener('click', () => {
  const fd = new FormData(document.getElementById('formModifierUser'));
  fetch('../actions/user_update.php', {method:'POST', body:fd})
    .then(() => {
      bootstrap.Modal.getInstance(document.getElementById('modalModifierUser')).hide();
      showToast('Utilisateur modifié.');
      loadUsers();
    });
});

// Supprimer
document.getElementById('btnConfirmerSupprimerUser').addEventListener('click', () => {
  const fd = new FormData(); fd.append('action','delete'); fd.append('id', pendingDeleteUserId);
  fetch('../api/utilisateurs.php', {method:'POST', body:fd})
    .then(r=>r.json()).then(d => {
      showToast(d.msg, d.ok?'success':'error');
      bootstrap.Modal.getInstance(document.getElementById('modalSupprimerUser')).hide();
      if (d.ok) loadUsers();
    });
});

loadUsers();
</script>

<?php require_once "../includes/footer.php"; ?>
