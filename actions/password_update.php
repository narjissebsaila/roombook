<?php
/*
    Fichier : actions/password_update.php
    Rôle    : changer le mot de passe de l'utilisateur connecté.
*/

require_once "../auth/guard.php";
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/profile.php");
    exit;
}

$id           = (int)$_SESSION["id"];
$ancien       = $_POST["ancien"] ?? "";
$nouveau      = $_POST["nouveau"] ?? "";
$confirmation = $_POST["confirmation"] ?? "";

if ($ancien === "" || $nouveau === "" || $confirmation === "") {
    header("Location: ../pages/profile.php?error=password");
    exit;
}

if (strlen($nouveau) < 4 || $nouveau !== $confirmation) {
    header("Location: ../pages/profile.php?error=password");
    exit;
}

// Vérifier le mot de passe actuel
$stmt = $pdo->prepare("SELECT mot_de_passe FROM utilisateurs WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user || !password_verify($ancien, $user["mot_de_passe"])) {
    header("Location: ../pages/profile.php?error=password");
    exit;
}

// Mise à jour
$hash = password_hash($nouveau, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?");
$stmt->execute([$hash, $id]);

header("Location: ../pages/profile.php?success=password");
exit;
