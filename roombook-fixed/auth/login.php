<?php
session_start();
if (isset($_SESSION["id"])) { header("Location: ../pages/index.php"); exit; }
require_once "../config/database.php";
$erreur = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $mdp   = $_POST["mot_de_passe"] ?? "";
    if ($email === "" || $mdp === "") {
        $erreur = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]); $u = $stmt->fetch();
        if ($u && password_verify($mdp, $u["mot_de_passe"])) {
            session_regenerate_id(true);
            $_SESSION["id"] = $u["id"]; $_SESSION["nom"] = $u["nom"];
            $_SESSION["email"] = $u["email"]; $_SESSION["role"] = $u["role"];
            $_SESSION["type_client"] = $u["type_client"];
            header("Location: ../pages/index.php"); exit;
        }
        $erreur = "Email ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Connexion — RoomBook</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-body">
<div class="login-container">
  <div class="login-card shadow-lg">
    <div class="text-center mb-4">
      <div class="display-5 mb-1"><img src="../includes/logo.png" alt="Logo" width="80" border-radius="50%"></div>
      <h1 class="h3 fw-bold text-primary mb-0">RoomBook</h1>
      <p class="text-muted small">Connectez-vous à votre espace</p>
    </div>

    <?php if ($erreur): ?>
      <div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label fw-semibold">Email</label>
        <input type="email" name="email" class="form-control" required autofocus
               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
      </div>
      <div class="mb-4">
        <label class="form-label fw-semibold">Mot de passe</label>
        <input type="password" name="mot_de_passe" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100 fw-semibold">
        <i class="bi bi-box-arrow-in-right me-1"></i> Se connecter
      </button>
    </form>

    <p class="text-center text-muted small mt-3">
      Pas encore de compte ? <a href="register.php" class="text-primary fw-semibold">S'inscrire</a>
    </p>

    <div class="mt-3 p-3 bg-light rounded border small text-muted">
      <strong class="text-dark">Comptes de démo :</strong><br>
      👨‍💼 admin@roombook.com &nbsp;|&nbsp; 👨‍🏫 prof@roombook.com<br>
      🎓 etudiant@roombook.com &nbsp;— mot de passe : <code>password</code>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
