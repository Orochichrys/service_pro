<?php 
require_once("includes/verif_auth.php"); 
require_once("../includes/db.php");
require_once("../includes/fonctions.php");

// Mise à jour du statut par l'ADMIN
if (isset($_POST['update_statut'])) {
    $id_cmd = (int)$_POST['id_commande'];
    $statut = securisation($_POST['statut']);
    
    if ($statut != 'Confirmée' && !empty($id_cmd) && !empty($statut)) {
        $sql = "UPDATE Commande SET statut = :statut WHERE id_commande = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['statut' => $statut, 'id' => $id_cmd]);
    }
    header("Location: commandes.php");
    exit();
}

// Paramètres de pagination
$limite = 10; 
$page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$debut = ($page - 1) * $limite;

// FILTRES HARMONISÉS
$statut_filtre = isset($_GET['statut']) ? securisation($_GET['statut']) : '';
$recherche = isset($_GET['recherche']) ? securisation($_GET['recherche']) : '';
$ville_filtre = isset($_GET['id_ville']) ? (int)$_GET['id_ville'] : 0;

$where = " WHERE 1=1";
$params = [];

if (!empty($statut_filtre) && $statut_filtre != 'Tous les statuts') {
    $where .= " AND c.statut = :statut";
    $params['statut'] = $statut_filtre;
}

if (!empty($recherche)) {
    // Recherche sur le client OU le prestataire OU le titre du service
    $where .= " AND (u_client.nom_utilisateur LIKE :rech OR u_client.prenom_utilisateur LIKE :rech 
                OR u_prest.nom_utilisateur LIKE :rech OR u_prest.prenom_utilisateur LIKE :rech 
                OR p.titre_prestation LIKE :rech OR u_client.tel_utilisateur LIKE :rech)";
    $params['rech'] = "%$recherche%";
}

if ($ville_filtre > 0) {
    $where .= " AND v.id_ville = :id_ville";
    $params['id_ville'] = $ville_filtre;
}

// 1. Compter le total
$sql_count = "SELECT COUNT(DISTINCT c.id_commande) as total 
              FROM Commande c 
              JOIN Utilisateur u_client ON c.id_utilisateur = u_client.id_utilisateur
              JOIN Quartier q ON c.id_quartier = q.id_quartier
              JOIN Ville v ON q.id_ville = v.id_ville
              LEFT JOIN Cibler cb ON c.id_commande = cb.id_commande
              LEFT JOIN Prestation p ON cb.id_prestation = p.id_prestation
              LEFT JOIN Utilisateur u_prest ON p.id_utilisateur = u_prest.id_utilisateur" . $where;
$stmt_count = $conn->prepare($sql_count);
$stmt_count->execute($params);
$total_elements = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_elements / $limite);

// 2. Récupération des commandes
$sql = "SELECT c.*, 
               u_client.nom_utilisateur as client_nom, u_client.prenom_utilisateur as client_prenom, u_client.tel_utilisateur as client_tel,
               MAX(u_prest.nom_utilisateur) as prest_nom, MAX(u_prest.prenom_utilisateur) as prest_prenom,
               q.nom_quartier, v.nom_ville 
        FROM Commande c 
        JOIN Utilisateur u_client ON c.id_utilisateur = u_client.id_utilisateur
        JOIN Quartier q ON c.id_quartier = q.id_quartier
        JOIN Ville v ON q.id_ville = v.id_ville
        LEFT JOIN Cibler cb ON c.id_commande = cb.id_commande
        LEFT JOIN Prestation p ON cb.id_prestation = p.id_prestation
        LEFT JOIN Utilisateur u_prest ON p.id_utilisateur = u_prest.id_utilisateur"
        . $where . 
        " GROUP BY c.id_commande
        ORDER BY FIELD(c.statut, 'En attente', 'Confirmée', 'Terminée', 'Annulée'), c.date_commande DESC 
        LIMIT $limite OFFSET $debut";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des villes pour le filtre
$villes = $conn->query("SELECT * FROM Ville ORDER BY nom_ville")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Commandes - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .badge-status { text-transform: uppercase; font-size: 0.7rem; padding: 0.4rem 0.8rem; }
    </style>
</head>
<body>

<?php include("includes/barre_laterale.php") ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold h3">Suivi Global des Commandes</h2>
            <div class="d-flex gap-2">
                <span class="badge bg-primary rounded-pill px-3 py-2"><?php echo $total_elements; ?> commandes au total</span>
            </div>
        </div>

        <!-- FILTRES HARMONISÉS -->
        <div class="card border-0 shadow-sm mb-4 rounded-4">
            <div class="card-body p-4">
                <div class="mb-3">
                    <h5 class="fw-bold mb-1">Filtres de recherche</h5>
                    <p class="text-muted small">Ciblez les commandes par client, prestataire, statut ou ville</p>
                </div>
                <form action="" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Recherche</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" name="recherche" class="form-control border-start-0" placeholder="Client, GSM, Service..." value="<?php echo $recherche; ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Statut</label>
                        <select name="statut" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="En attente" <?php echo $statut_filtre == 'En attente' ? 'selected' : ''; ?>>En attente</option>
                            <option value="Confirmée" <?php echo $statut_filtre == 'Confirmée' ? 'selected' : ''; ?>>Confirmée</option>
                            <option value="Terminée" <?php echo $statut_filtre == 'Terminée' ? 'selected' : ''; ?>>Terminée</option>
                            <option value="Annulée" <?php echo $statut_filtre == 'Annulée' ? 'selected' : ''; ?>>Annulée</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Ville de l'intervention</label>
                        <select name="id_ville" class="form-select">
                            <option value="0">Toutes les villes</option>
                            <?php foreach ($villes as $v): ?>
                            <option value="<?php echo $v['id_ville']; ?>" <?php echo ($v['id_ville'] == $ville_filtre) ? 'selected' : ''; ?>>
                                <?php echo $v['nom_ville']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">
                            <i class="bi bi-funnel me-2"></i>Filtrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- LISTE -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="bg-light text-uppercase small fw-bold">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Date</th>
                            <th>Acheteur (Client)</th>
                            <th>Expert (Prestataire)</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Action Admin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($commandes as $c): ?>
                        <tr>
                            <td class="ps-4 fw-bold">#<?php echo $c['id_commande']; ?></td>
                            <td class="small text-muted"><?php echo date('d/m/y H:i', strtotime($c['date_commande'])); ?></td>
                            <td>
                                <div class="fw-bold small"><?php echo $c['client_nom'] . ' ' . $c['client_prenom']; ?></div>
                                <div class="text-muted small"><?php echo $c['nom_quartier']; ?> (<?php echo $c['client_tel']; ?>)</div>
                            </td>
                            <td>
                                <?php if ($c['prest_nom']): ?>
                                    <span class="small fw-bold text-primary"><?php echo $c['prest_nom'] . ' ' . $c['prest_prenom']; ?></span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark fw-normal opacity-50">Non assigné</span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold text-dark"><?php echo number_format($c['montant_total'], 0, ',', ' '); ?> F</td>
                            <td>
                                <?php 
                                $class = "bg-warning-subtle text-warning";
                                if ($c['statut'] == 'Confirmée') $class = "bg-info-subtle text-info";
                                if ($c['statut'] == 'Terminée') $class = "bg-success-subtle text-success";
                                if ($c['statut'] == 'Annulée') $class = "bg-danger-subtle text-danger";
                                ?>
                                <span class="badge badge-status <?php echo $class; ?> rounded-pill"><?php echo $c['statut']; ?></span>
                            </td>
                            <td class="text-end pe-4">
                                <?php if ($c['statut'] != 'Terminée' && $c['statut'] != 'Annulée'): ?>
                                    <form action="" method="POST" class="d-inline">
                                        <input type="hidden" name="id_commande" value="<?php echo $c['id_commande']; ?>">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <select name="statut" class="form-select form-select-sm rounded-pill w-auto" style="font-size: 0.7rem;">
                                                <option value="" disabled selected>Action...</option>
                                                <option value="Terminée">Forcer Terminée</option>
                                                <option value="Annulée">Annuler Commande</option>
                                            </select>
                                            <button type="submit" name="update_statut" class="btn btn-primary btn-sm rounded-circle" title="Vérifier et mettre à jour">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted small"><i class="bi bi-lock-fill"></i> Clôturé</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (count($commandes) === 0): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-search-heart mb-3 fs-1 text-muted opacity-25 d-block"></i>
                                <span class="text-muted fw-bold">Aucune commande trouvée pour ces critères.</span>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer bg-white py-3 border-0">
                <nav>
                    <ul class="pagination pagination-sm mb-0 justify-content-center">
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link rounded-pill mx-1" href="?page=<?php echo $i; ?>&statut=<?php echo $statut_filtre; ?>&recherche=<?php echo $recherche; ?>&id_ville=<?php echo $ville_filtre; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>