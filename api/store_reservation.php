<?php
/*
    api/store_reservation.php
    Enregistrer une nouvelle réservation via AJAX (POST)
*/
require_once "../auth/guard.php";
require_once "../config/database.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['ok'=>false,'msg'=>'Méthode invalide']); exit; }

$utilisateur_id   = $_SESSION['id'];
$salle_id         = $_POST['salle_id']         ?? '';
$date_reservation = $_POST['date_reservation'] ?? '';
$heure_debut      = $_POST['heure_debut']      ?? '';
$heure_fin        = $_POST['heure_fin']        ?? '';
$responsable      = trim($_POST['responsable'] ?? '');
$motif            = trim($_POST['motif']       ?? '');
$statut           = $_POST['statut']           ?? 'en_attente';

if ($_SESSION['role'] !== 'admin') $statut = 'en_attente';

if (empty($salle_id)||empty($date_reservation)||empty($heure_debut)||empty($heure_fin)||empty($responsable))
    { echo json_encode(['ok'=>false,'msg'=>'Tous les champs obligatoires doivent être remplis.']); exit; }

if (!in_array($statut,['en_attente','confirmee','refusee','annulee']))
    { echo json_encode(['ok'=>false,'msg'=>'Statut invalide.']); exit; }

if ($date_reservation < date('Y-m-d'))
    { echo json_encode(['ok'=>false,'msg'=>'La date doit être aujourd\'hui ou dans le futur.']); exit; }

if ($heure_debut >= $heure_fin)
    { echo json_encode(['ok'=>false,'msg'=>'L\'heure de début doit être inférieure à l\'heure de fin.']); exit; }

$stmt = $pdo->prepare("SELECT id FROM reservations WHERE salle_id=? AND date_reservation=? AND statut!='annulee' AND heure_debut<? AND heure_fin>?");
$stmt->execute([$salle_id,$date_reservation,$heure_fin,$heure_debut]);
if ($stmt->fetch() && $statut !== 'annulee')
    { echo json_encode(['ok'=>false,'msg'=>'Cette salle est déjà réservée dans ce créneau.']); exit; }

$pdo->prepare("INSERT INTO reservations (utilisateur_id,salle_id,date_reservation,heure_debut,heure_fin,responsable,motif,statut) VALUES (?,?,?,?,?,?,?,?)")
    ->execute([$utilisateur_id,$salle_id,$date_reservation,$heure_debut,$heure_fin,$responsable,$motif,$statut]);

echo json_encode(['ok'=>true,'msg'=>'Réservation ajoutée avec succès.']);
