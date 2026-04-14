<?php 
require_once("includes/verif_auth.php"); 
require_once("../includes/db.php");

// Action de validation
if (isset($_GET['approuver'])) {
    $id = (int)$_GET['approuver'];
    $conn->prepare("UPDATE Utilisateur SET is_validated = 1 WHERE id_utilisateur = ?")->execute([$id]);
    redirection("index.php");
}

// 1. Nombre d'utilisateurs
$sql_users = "SELECT COUNT(*) as total FROM Utilisateur";
$res_users = $conn->query($sql_users)->fetch(PDO::FETCH_ASSOC);
$total_utilisateurs = $res_users['total'];

// 2. Chiffre d'affaires Global (Somme des commandes encaissées/terminées)
$sql_ca = "SELECT SUM(montant_total) as total FROM Commande WHERE statut = 'Terminée'";
$res_ca = $conn->query($sql_ca)->fetch(PDO::FETCH_ASSOC);
$chiffre_affaires = $res_ca['total'] ?? 0;

// 3. Note moyenne (Moyenne des évaluations dans Cibler)
$sql_note = "SELECT AVG(note_evaluation) as moyenne FROM Cibler WHERE note_evaluation IS NOT NULL";
$res_note = $conn->query($sql_note)->fetch(PDO::FETCH_ASSOC);
$note_moyenne = number_format($res_note['moyenne'] ?? 0, 1);

// 4. Nombre de commandes
$sql_commandes = "SELECT COUNT(*) as total FROM Commande";
$res_commandes = $conn->query($sql_commandes)->fetch(PDO::FETCH_ASSOC);
$total_commandes = $res_commandes['total'];

// 5. Liste des prestataires avec pagination
$limite = 5;
$page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$debut = ($page - 1) * $limite;

// On compte total prestataires en attente
$total_elements = $conn->query("SELECT COUNT(*) FROM Utilisateur WHERE est_prestataire = TRUE AND is_validated = 0")->fetchColumn();
$total_pages = ceil($total_elements / $limite);

$sql_prestataires = "
    SELECT * FROM Utilisateur 
    WHERE est_prestataire = TRUE AND is_validated = 0
    ORDER BY date_inscription DESC 
    LIMIT $limite OFFSET $debut
";
$prestataires = $conn->query($sql_prestataires)->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - ServicePro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include("includes/barre_laterale.php") ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Tableau de bord</h2>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3 text-primary">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted small mb-1">Utilisateurs</h6>
                            <h4 class="fw-bold mb-0"><?php echo number_format($total_utilisateurs); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3 text-success">
                            <i class="bi bi-cash-stack fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted small mb-1">Chiffre d'Affaires</h6>
                            <h4 class="fw-bold mb-0"><?php echo number_format($chiffre_affaires, 0, ',', ' '); ?> F</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3 text-warning">
                            <i class="bi bi-star fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted small mb-1">Note Moyenne</h6>
                            <h4 class="fw-bold mb-0"><?php echo $note_moyenne; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle me-3 text-info">
                            <i class="bi bi-cart-check fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted small mb-1">Commandes</h6>
                            <h4 class="fw-bold mb-0"><?php echo number_format($total_commandes); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="fw-bold mb-0">Nouveaux prestataires à valider</h5>
            </div>
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Utilisateur</th>
                            <th>Domaine</th>
                            <th>Date d'inscription</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prestataires as $p): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                        <?php echo substr($p['nom_utilisateur'], 0, 1) . substr($p['prenom_utilisateur'], 0, 1); ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 small fw-bold"><?php echo $p['nom_utilisateur'] . ' ' . $p['prenom_utilisateur']; ?></h6>
                                        <small class="text-muted"><?php echo $p['email_utilisateur']; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>Prestataire</td>
                            <td><?php echo $p['tel_utilisateur']; ?></td>
                            <td><span class="badge bg-warning-subtle text-warning">En attente</span></td>
                            <td class="text-end pe-4">
                                <a href="?approuver=<?php echo $p['id_utilisateur']; ?>" class="btn btn-sm btn-success fw-bold rounded-pill text-white px-3 shadow-sm">
                                    <i class="bi bi-check-circle me-1"></i> Approuver
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (count($prestataires) === 0): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted small">Aucun prestataire récent.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white py-3 border-0">
                <nav>
                        <ul class="pagination pagination-sm mb-0 justify-content-center">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>">Précédent</a>
                            </li>
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Suivant</a>
                            </li>
                        </ul>
                </nav>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>