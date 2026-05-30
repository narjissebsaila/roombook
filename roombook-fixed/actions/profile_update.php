<?php
/*
    Fichier : actions/profile_update.php
    Rôle    : mettre à jour le profil de l'utilisateur connecté.
*/

require_once "../auth/guard.php";
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/profile.php");
    exit;
}

$id          = (int)$_SESSION["id"];
$nom         = trim($_POST["nom"] ?? "");
$email       = trim($_POST["email"] ?? "");
$telephone   = trim($_POST["telephone"] ?? "");
$departement = trim($_POST["departement"] ?? "");
$bio         = trim($_POST["bio"] ?? "");

if ($nom === "" || $email === "") {
    header("Location: ../pages/profile.php?error=vide");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../pages/profile.php?error=email");
    exit;
}

// Vérifier que l'email n'est pas pris par un autre user
$stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
$stmt->execute([$email, $id]);
if ($stmt->fetch()) {
    header("Location: ../pages/profile.php?error=email");
    exit;
}

$sql = "UPDATE utilisateurs
        SET nom = ?, email = ?, telephone = ?, departement = ?, bio = ?
        WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$nom, $email, $telephone, $departement, $bio, $id]);

// Mettre à jour la session
$_SESSION["nom"]   = $nom;
$_SESSION["email"] = $email;

header("Location: ../pages/profile.php?success=profile");
exit;
