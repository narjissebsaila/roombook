<?php
/*
    Fichier : actions/salle_update.php
    Rôle    : modifier une salle (admin uniquement).
*/

require_once "../auth/guard_admin.php";
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/salles.php");
    exit;
}

$id           = (int)($_POST["id"]          ?? 0);
$nom          = trim($_POST["nom"]          ?? "");
$capacite     = (int)($_POST["capacite"]    ?? 0);
$localisation = trim($_POST["localisation"] ?? "");
$type_salle   = $_POST["type_salle"]        ?? "cours";
$equipements  = trim($_POST["equipements"]  ?? "");

if ($id <= 0 || $nom === "" || $capacite <= 0) {
    header("Location: ../pages/salle_edit.php?id=$id&error=vide");
    exit;
}

if (!in_array($type_salle, ["cours", "tp", "reunion", "amphi"])) {
    $type_salle = "cours";
}

$sql = "UPDATE salles
        SET nom = ?, capacite = ?, localisation = ?, type_salle = ?, equipements = ?
        WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$nom, $capacite, $localisation, $type_salle, $equipements, $id]);

header("Location: ../pages/salles.php?success=modification");
exit;
