<?php
/*
    Fichier : actions/store.php
    Rôle    : enregistrer une nouvelle réservation.
*/

require_once "../auth/guard.php";
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/index.php");
    exit;
}

$utilisateur_id   = $_SESSION["id"];
$salle_id         = $_POST["salle_id"]         ?? "";
$date_reservation = $_POST["date_reservation"] ?? "";
$heure_debut      = $_POST["heure_debut"]      ?? "";
$heure_fin        = $_POST["heure_fin"]        ?? "";
$responsable      = trim($_POST["responsable"] ?? "");
$motif            = trim($_POST["motif"]       ?? "");
$statut           = $_POST["statut"]           ?? "en_attente";

// Un client ne peut pas forcer un statut autre que "en_attente"
if ($_SESSION["role"] !== "admin") {
    $statut = "en_attente";
}

// Validation : champs obligatoires
if (
    empty($salle_id) || empty($date_reservation)
    || empty($heure_debut) || empty($heure_fin) || empty($responsable)
) {
    header("Location: ../pages/create.php?error=vide");
    exit;
}

// Validation : statut autorisé
$statutsAutorises = ["en_attente", "confirmee", "refusee", "annulee"];
if (!in_array($statut, $statutsAutorises)) {
    header("Location: ../pages/create.php?error=statut");
    exit;
}

// Validation : date non passée
if ($date_reservation < date("Y-m-d")) {
    header("Location: ../pages/create.php?error=date");
    exit;
}

// Validation : ordre des heures
if ($heure_debut >= $heure_fin) {
    header("Location: ../pages/create.php?error=heure");
    exit;
}

// Validation : conflit horaire
$sqlConflit = "
    SELECT id FROM reservations
    WHERE salle_id = ?
      AND date_reservation = ?
      AND statut != 'annulee'
      AND heure_debut < ?
      AND heure_fin   > ?
";
$stmt = $pdo->prepare($sqlConflit);
$stmt->execute([$salle_id, $date_reservation, $heure_fin, $heure_debut]);

if ($stmt->fetch() && $statut !== "annulee") {
    header("Location: ../pages/create.php?error=conflit");
    exit;
}

// Insertion
$sql = "
    INSERT INTO reservations
    (utilisateur_id, salle_id, date_reservation, heure_debut, heure_fin, responsable, motif, statut)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    $utilisateur_id, $salle_id, $date_reservation,
    $heure_debut, $heure_fin, $responsable, $motif, $statut
]);

header("Location: ../pages/index.php?success=ajout");
exit;
