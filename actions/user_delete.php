<?php
/*
    Fichier : actions/user_delete.php
    Rôle    : supprimer un utilisateur (admin uniquement).
    Impossible de se supprimer soi-même.
*/

require_once "../auth/guard_admin.php";
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/utilisateurs.php");
    exit;
}

$id = (int)($_POST["id"] ?? 0);

// Empêcher l'admin de se supprimer lui-même
if ($id === (int)$_SESSION["id"]) {
    header("Location: ../pages/utilisateurs.php");
    exit;
}

$stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
$stmt->execute([$id]);

header("Location: ../pages/utilisateurs.php?success=suppression");
exit;
