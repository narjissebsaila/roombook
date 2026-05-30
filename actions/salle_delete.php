<?php
/*
    Fichier : actions/salle_delete.php
    Rôle    : supprimer une salle (admin uniquement).
    Toutes les réservations associées seront supprimées (ON DELETE CASCADE).
*/

require_once "../auth/guard_admin.php";
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/salles.php");
    exit;
}

$id = (int)($_POST["id"] ?? 0);

$stmt = $pdo->prepare("DELETE FROM salles WHERE id = ?");
$stmt->execute([$id]);

header("Location: ../pages/salles.php?success=suppression");
exit;
