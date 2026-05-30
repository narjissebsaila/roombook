<?php
/*
    api/reservations.php
    Endpoints AJAX pour les réservations
    GET    ?action=list           → liste JSON
    POST   action=delete          → supprimer
    POST   action=update_statut   → changer statut (admin)
*/
require_once "../auth/guard.php";
require_once "../config/database.php";

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

// ── DELETE ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT utilisateur_id FROM reservations WHERE id = ?");
    $stmt->execute([$id]);
    $resa = $stmt->fetch();
    if (!$resa) { echo json_encode(['ok'=>false,'msg'=>'Introuvable']); exit; }
    $isAdmin = ($_SESSION['role'] === 'admin');
    if (!$isAdmin && $resa['utilisateur_id'] != $_SESSION['id']) {
        echo json_encode(['ok'=>false,'msg'=>'Accès refusé']); exit;
    }
    $pdo->prepare("DELETE FROM reservations WHERE id = ?")->execute([$id]);
    echo json_encode(['ok'=>true,'msg'=>'Réservation supprimée.']);
    exit;
}

// ── UPDATE STATUT (admin) ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update_statut') {
    if ($_SESSION['role'] !== 'admin') { echo json_encode(['ok'=>false,'msg'=>'Accès refusé']); exit; }
    $id     = (int)($_POST['id'] ?? 0);
    $statut = $_POST['statut'] ?? '';
    $allowed = ['en_attente','confirmee','refusee','annulee'];
    if (!in_array($statut, $allowed)) { echo json_encode(['ok'=>false,'msg'=>'Statut invalide']); exit; }
    $pdo->prepare("UPDATE reservations SET statut=? WHERE id=?")->execute([$statut,$id]);
    echo json_encode(['ok'=>true,'msg'=>'Statut mis à jour.']);
    exit;
}

// ── LIST ─────────────────────────────────────────────────────
$isAdmin = ($_SESSION['role'] === 'admin');
$page    = max(1, (int)($_GET['page'] ?? 1));
$limit   = 10;
$offset  = ($page - 1) * $limit;
$search  = trim($_GET['search'] ?? '');

$where  = $isAdmin ? "1=1" : "r.utilisateur_id = " . (int)$_SESSION['id'];
$params = [];
if ($search !== '') {
    $where .= " AND (s.nom LIKE ? OR r.responsable LIKE ? OR r.motif LIKE ?)";
    $params = array_merge($params, ["%$search%","%$search%","%$search%"]);
}

// total
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM reservations r INNER JOIN salles s ON r.salle_id=s.id WHERE $where");
$stmtCount->execute($params);
$total = (int)$stmtCount->fetchColumn();

// rows
$sql = "SELECT r.*, s.nom AS nom_salle, u.nom AS nom_utilisateur, u.type_client
        FROM reservations r
        INNER JOIN salles s ON r.salle_id = s.id
        LEFT  JOIN utilisateurs u ON r.utilisateur_id = u.id
        WHERE $where
        ORDER BY r.date_reservation DESC, r.heure_debut DESC
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

echo json_encode([
    'ok'    => true,
    'rows'  => $rows,
    'total' => $total,
    'pages' => (int)ceil($total / $limit),
    'page'  => $page,
]);
