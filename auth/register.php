<?php
session_start();
if (isset($_SESSION["id"])) { header("Location: ../pages/index.php"); exit; }
require_once "../config/database.php";
$erreur = null; $succes = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom   = trim($_POST["nom"] ?? ""); $email = trim($_POST["email"] ?? "");
    $mdp   = $_POST["mot_de_passe"] ?? ""; $conf  = $_POST["confirmation"] ?? "";
    $type  = $_POST["type_client"] ?? "etudiant";
    if (!$nom || !$email || !$mdp) { $erreur = "Tous les champs sont obligatoires."; }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $erreur = "Email invalide."; }
    elseif (strlen($mdp) < 4) { $erreur = "Mot de passe trop court (min 4 caractères)."; }
    elseif ($mdp !== $conf) { $erreur = "Les mots de passe ne correspondent pas."; }
    elseif (!in_array($type, ["prof","etudiant"])) { $erreur = "Type invalide."; }
    else {
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email=?"); $stmt->execute([$email]);
        if ($stmt->fetch()) { $erreur = "Cet email est déjà utilisé."; }
        else {
            $pdo->prepare("INSERT INTO utilisateurs (nom,email,mot_de_passe,role,type_client) VALUES (?,?,?,'client',?)")
                ->execute([$nom,$email,password_hash($mdp,PASSWORD_DEFAULT),$type]);
            $succes = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inscription — RoomBook</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-body">
<div class="login-container">
  <div class="login-card shadow-lg">
    <div class="text-center mb-4">
      <div class="display-5 mb-1">📝</div>
      <h1 class="h3 fw-bold text-primary mb-0">Créer un compte</h1>
      <p class="text-muted small">Rejoignez RoomBook</p>
    </div>

    <?php if ($erreur): ?>
      <div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <?php if ($succes): ?>
      <div class="alert alert-success text-center">
        ✅ Compte créé ! <a href="login.php" class="fw-semibold">Se connecter</a>
      </div>
    <?php else: ?>
    <form method="POST" class="row g-3">
      <div class="col-12">
        <label class="form-label fw-semibold">Nom complet</label>
        <input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($_POST['nom']??'') ?>">
      </div>
      <div class="col-12">
        <label class="form-label fw-semibold">Email</label>
        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email']??'') ?>">
      </div>
      <div class="col-12">
        <label class="form-label fw-semibold">Type de compte</label>
        <select name="type_client" class="form-select">
          <option value="etudiant">🎓 Étudiant</option>
          <option value="prof">👨‍🏫 Professeur</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Mot de passe</label>
        <input type="password" name="mot_de_passe" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Confirmer</label>
        <input type="password" name="confirmation" class="form-control" required>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-primary w-100 fw-semibold">
          <i class="bi bi-person-plus me-1"></i> Créer mon compte
        </button>
      </div>
    </form>
    <?php endif; ?>

    <p class="text-center text-muted small mt-3">
      Déjà inscrit ? <a href="login.php" class="text-primary fw-semibold">Se connecter</a>
    </p>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
