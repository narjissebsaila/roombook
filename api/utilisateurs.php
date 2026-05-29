<?php
require_once "../auth/guard_admin.php";
require_once "../config/database.php";
header('Content-Type: application/json');
$action = $_REQUEST['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id === (int)$_SESSION['id']) { echo json_encode(['ok'=>false,'msg'=>'Vous ne pouvez pas vous supprimer.']); exit; }
    $pdo->prepare("DELETE FROM utilisateurs WHERE id=?")->execute([$id]);
    echo json_encode(['ok'=>true,'msg'=>'Utilisateur supprimé.']);exit;
}
// list
$users = $pdo->query("SELECT u.*, COUNT(r.id) AS nb_reservations FROM utilisateurs u LEFT JOIN reservations r ON r.utilisateur_id=u.id GROUP BY u.id ORDER BY u.role,u.nom")->fetchAll();
echo json_encode(['ok'=>true,'rows'=>$users]);
