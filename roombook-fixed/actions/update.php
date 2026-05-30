<?php
/*
    Fichier : actions/update.php
    Rôle    : modifier une réservation existante.
*/

require_once "../auth/guard.php";
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/index.php");
    exit;
}

$id               = (int)($_POST["id"] ?? 0);
$salle_id         = $_POST["salle_id"]         ?? "";
$date_reservation = $_POST["date_reservation"] ?? "";
$heure_debut      = $_POST["heure_debut"]      ?? "";
$heure_fin        = $_POST["heure_fin"]        ?? "";
$responsable      = trim($_POST["responsable"] ?? "");
$motif            = trim($_POST["motif"]       ?? "");
$statut           = $_POST["statut"]           ?? "en_attente";

// Récupérer la réservation existante pour vérifier les droits
$stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ?");
$stmt->execute([$id]);
$existing = $stmt->fetch();

if (!$existing) {
    header("Location: ../pages/index.php");
    exit;
}

$isAdmin = ($_SESSION["role"] === "admin");

// Vérification des droits
if (!$isAdmin && $existing["utilisateur_id"] != $_SESSION["id"]) {
    header("Location: ../pages/index.php");
    exit;
}

// Un client ne peut pas changer le statut
if (!$isAdmin) {
    $statut = $existing["statut"];
}

// Validation des champs
if (empty($salle_id) || empty($date_reservation) || empty($heure_debut)
    || empty($heure_fin) || empty($responsable)) {
    header("Location: ../pages/edit.php?id=$id&error=vide");
    exit;
}

$statutsAutorises = ["en_attente", "confirmee", "refusee", "annulee"];
if (!in_array($statut, $statutsAutorises)) {
    header("Location: ../pages/edit.php?id=$id&error=statut");
    exit;
}

if ($heure_debut >= $heure_fin) {
    header("Location: ../pages/edit.php?id=$id&error=heure");
    exit;
}

// Conflit horaire (en excluant la réservation actuelle)
$sqlConflit = "
    SELECT id FROM reservations
    WHERE salle_id = ?
      AND date_reservation = ?
      AND statut != 'annulee'
      AND heure_debut < ?
      AND heure_fin   > ?
      AND id != ?
";
$stmt = $pdo->prepare($sqlConflit);
$stmt->execute([$salle_id, $date_reservation, $heure_fin, $heure_debut, $id]);

if ($stmt->fetch() && $statut !== "annulee") {
    header("Location: ../pages/edit.php?id=$id&error=conflit");
    exit;
}

// Mise à jour
$sql = "
    UPDATE reservations
    SET salle_id = ?, date_reservation = ?, heure_debut = ?, heure_fin = ?,
        responsable = ?, motif = ?, statut = ?
    WHERE id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    $salle_id, $date_reservation, $heure_debut, $heure_fin,
    $responsable, $motif, $statut, $id
]);

header("Location: ../pages/index.php?success=modification");
exit;
