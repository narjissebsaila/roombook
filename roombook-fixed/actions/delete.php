<?php
/*
    Fichier : actions/delete.php
    Rôle    : supprimer une réservation.
    - Admin : peut tout supprimer
    - Client : peut seulement supprimer ses propres réservations
*/

require_once "../auth/guard.php";
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/index.php");
    exit;
}

$id = (int)($_POST["id"] ?? 0);

$stmt = $pdo->prepare("SELECT utilisateur_id FROM reservations WHERE id = ?");
$stmt->execute([$id]);
$resa = $stmt->fetch();

if (!$resa) {
    header("Location: ../pages/index.php");
    exit;
}

$isAdmin = ($_SESSION["role"] === "admin");

if (!$isAdmin && $resa["utilisateur_id"] != $_SESSION["id"]) {
    header("Location: ../pages/index.php");
    exit;
}

$stmt = $pdo->prepare("DELETE FROM reservations WHERE id = ?");
$stmt->execute([$id]);

header("Location: ../pages/index.php?success=suppression");
exit;
