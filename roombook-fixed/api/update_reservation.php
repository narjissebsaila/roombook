<?php
/*
    api/update_reservation.php
    Modifier une réservation via AJAX (POST)
*/
require_once "../auth/guard.php";
require_once "../config/database.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['ok'=>false,'msg'=>'Méthode invalide']); exit; }

$id               = (int)($_POST['id'] ?? 0);
$salle_id         = $_POST['salle_id']         ?? '';
$date_reservation = $_POST['date_reservation'] ?? '';
$heure_debut      = $_POST['heure_debut']      ?? '';
$heure_fin        = $_POST['heure_fin']        ?? '';
$responsable      = trim($_POST['responsable'] ?? '');
$motif            = trim($_POST['motif']       ?? '');
$statut           = $_POST['statut']           ?? 'en_attente';

$stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ?");
$stmt->execute([$id]);
$existing = $stmt->fetch();
if (!$existing) { echo json_encode(['ok'=>false,'msg'=>'Réservation introuvable.']); exit; }

$isAdmin = ($_SESSION['role'] === 'admin');
if (!$isAdmin && $existing['utilisateur_id'] != $_SESSION['id'])
    { echo json_encode(['ok'=>false,'msg'=>'Accès refusé.']); exit; }

if (!$isAdmin) $statut = $existing['statut'];

if (empty($salle_id)||empty($date_reservation)||empty($heure_debut)||empty($heure_fin)||empty($responsable))
    { echo json_encode(['ok'=>false,'msg'=>'Tous les champs obligatoires doivent être remplis.']); exit; }

if ($heure_debut >= $heure_fin)
    { echo json_encode(['ok'=>false,'msg'=>'L\'heure de début doit être inférieure à l\'heure de fin.']); exit; }

$stmt = $pdo->prepare("SELECT id FROM reservations WHERE salle_id=? AND date_reservation=? AND statut!='annulee' AND heure_debut<? AND heure_fin>? AND id!=?");
$stmt->execute([$salle_id,$date_reservation,$heure_fin,$heure_debut,$id]);
if ($stmt->fetch() && $statut !== 'annulee')
    { echo json_encode(['ok'=>false,'msg'=>'Cette salle est déjà réservée dans ce créneau.']); exit; }

$pdo->prepare("UPDATE reservations SET salle_id=?,date_reservation=?,heure_debut=?,heure_fin=?,responsable=?,motif=?,statut=? WHERE id=?")
    ->execute([$salle_id,$date_reservation,$heure_debut,$heure_fin,$responsable,$motif,$statut,$id]);

echo json_encode(['ok'=>true,'msg'=>'Réservation modifiée avec succès.']);
