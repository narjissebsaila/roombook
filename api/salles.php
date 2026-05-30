<?php
require_once "../auth/guard.php";
require_once "../config/database.php";
header('Content-Type: application/json');
$action = $_REQUEST['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete') {
    if ($_SESSION['role'] !== 'admin') { echo json_encode(['ok'=>false,'msg'=>'Accès refusé']); exit; }
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare("DELETE FROM salles WHERE id=?")->execute([$id]);
    echo json_encode(['ok'=>true,'msg'=>'Salle supprimée.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'store') {
    if ($_SESSION['role'] !== 'admin') { echo json_encode(['ok'=>false,'msg'=>'Accès refusé']); exit; }
    $nom=$_POST['nom']??''; $capacite=(int)($_POST['capacite']??0);
    $localisation=$_POST['localisation']??''; $type_salle=$_POST['type_salle']??'cours'; $equipements=$_POST['equipements']??'';
    if (trim($nom)===''||$capacite<=0){echo json_encode(['ok'=>false,'msg'=>'Nom et capacité obligatoires.']);exit;}
    if (!in_array($type_salle,['cours','tp','reunion','amphi'])) $type_salle='cours';
    $pdo->prepare("INSERT INTO salles (nom,capacite,localisation,type_salle,equipements) VALUES (?,?,?,?,?)")
        ->execute([trim($nom),$capacite,trim($localisation),$type_salle,trim($equipements)]);
    echo json_encode(['ok'=>true,'msg'=>'Salle ajoutée.']);exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    if ($_SESSION['role'] !== 'admin') { echo json_encode(['ok'=>false,'msg'=>'Accès refusé']); exit; }
    $id=(int)($_POST['id']??0); $nom=$_POST['nom']??''; $capacite=(int)($_POST['capacite']??0);
    $localisation=$_POST['localisation']??''; $type_salle=$_POST['type_salle']??'cours'; $equipements=$_POST['equipements']??'';
    if ($id<=0||trim($nom)===''||$capacite<=0){echo json_encode(['ok'=>false,'msg'=>'Données invalides.']);exit;}
    if (!in_array($type_salle,['cours','tp','reunion','amphi'])) $type_salle='cours';
    $pdo->prepare("UPDATE salles SET nom=?,capacite=?,localisation=?,type_salle=?,equipements=? WHERE id=?")
        ->execute([trim($nom),$capacite,trim($localisation),$type_salle,trim($equipements),$id]);
    echo json_encode(['ok'=>true,'msg'=>'Salle modifiée.']);exit;
}
// list
$salles = $pdo->query("SELECT * FROM salles ORDER BY nom")->fetchAll();
echo json_encode(['ok'=>true,'rows'=>$salles]);
