<?php 
require_once("includes/auth_check.php"); 
require_once("../includes/db.php");
require_once("../includes/function.php");

// Suppression d'un utilisateur
if (isset($_GET['supprimer'])) {
    $id_a_supprimer = securisation($_GET['supprimer']);
    $sql_delete = "DELETE FROM Utilisateur WHERE id_utilisateur = :id AND est_admin = FALSE"; // On ne supprime pas les admins ici par sécurité
    $stmt = $conn->prepare($sql_delete);
    $stmt->execute(['id' => $id_a_supprimer]);
    header("Location: users.php");
    exit();
}

// Validation d'un prestataire
if (isset($_GET['approuver'])) {
    $id = (int)$_GET['approuver'];
    $conn->prepare("UPDATE Utilisateur SET is_validated = 1 WHERE id_utilisateur = ?")->execute([$id]);
    header("Location: users.php");
    exit();
}

// Paramètres de pagination
$limite = 5; // Nombre d'éléments par page
$page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$debut = ($page - 1) * $limite;

// Filtres
$role = isset($_GET['role']) ? $_GET['role'] : '';
$recherche = isset($_GET['recherche']) ? securisation($_GET['recherche']) : '';

// Construction de la condition WHERE
$where = " WHERE 1=1";
$params = [];

if ($role == 'client') {
    $where .= " AND est_client = TRUE";
} else if ($role == 'prestataire') {
    $where .= " AND est_prestataire = TRUE";
}

if (!empty($recherche)) {
    $where .= " AND (nom_utilisateur LIKE :rech OR prenom_utilisateur LIKE :rech OR email_utilisateur LIKE :rech)";
    $params['rech'] = "%$recherche%";
}

// 1. Compter le total pour la pagination
$sql_count = "SELECT COUNT(*) as total FROM Utilisateur u" . $where;
$stmt_count = $conn->prepare($sql_count);
$stmt_count->execute($params);
$total_elements = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_elements / $limite);

// 2. Récupérer les données avec limite
$sql = "SELECT u.*, q.nom_quartier, v.nom_ville 
        FROM Utilisateur u 
        LEFT JOIN Quartier q ON u.id_quartier = q.id_quartier
        LEFT JOIN Ville v ON q.id_ville = v.id_ville" 
        . $where . 
        " ORDER BY date_inscription DESC LIMIT $limite OFFSET $debut";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des villes pour le filtre
$villes = $conn->query("SELECT * FROM Ville ORDER BY nom_ville")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Utilisateurs - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include("includes/sidebar.php") ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold h3">Gestion des Utilisateurs</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-person-plus me-2"></i>Ajouter un admin
            </button>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="mb-3">
                    <h5 class="fw-bold mb-1">Filtres de recherche</h5>
                    <p class="text-muted small">Recherchez par nom, rôle ou localisation</p>
                </div>
                <form action="" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Recherche</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" name="recherche" class="form-control border-start-0" placeholder="Nom, email ou téléphone..." value="<?php echo $recherche; ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Rôle</label>
                        <select name="role" class="form-select">
                            <option value="" <?php echo $role == '' ? 'selected' : ''; ?>>Tous les rôles</option>
                            <option value="client" <?php echo $role == 'client' ? 'selected' : ''; ?>>Clients uniquement</option>
                            <option value="prestataire" <?php echo $role == 'prestataire' ? 'selected' : ''; ?>>Prestataires uniquement</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Ville</label>
                        <select class="form-select">
                            <option value="">Toutes les villes</option>
                            <?php foreach ($villes as $v): ?>
                            <option value="<?php echo $v['id_ville']; ?>"><?php echo $v['nom_ville']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold shadow-sm">
                            <i class="bi bi-funnel me-2"></i> Filtrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Utilisateur</th>
                            <th>Rôle</th>
                            <th>Localisation</th>
                            <th>Date Inscription</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($utilisateurs as $u): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($u['nom_utilisateur'] . ' ' . $u['prenom_utilisateur']); ?>&background=6f42c1&color=fff" class="user-avatar me-3">
                                    <div>
                                        <div class="fw-bold mb-0"><?php echo $u['nom_utilisateur'] . ' ' . $u['prenom_utilisateur']; ?></div>
                                        <small class="text-muted"><?php echo $u['email_utilisateur']; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($u['est_client']): ?>
                                <span class="badge bg-info-subtle text-info">Client</span>
                                <?php endif; ?>
                                <?php if ($u['est_prestataire']): ?>
                                    <?php if ($u['is_validated']): ?>
                                        <span class="badge bg-success-subtle text-success">Prestataire validé</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning">Prestataire en attente</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if ($u['est_admin']): ?>
                                <span class="badge bg-danger-subtle text-danger">Admin</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="small"><?php echo $u['nom_ville'] ?? 'Indéfinie'; ?></div>
                                <div class="text-muted small"><?php echo $u['nom_quartier'] ?? ''; ?></div>
                            </td>
                            <td class="small text-muted"><?php echo date('d/m/Y', strtotime($u['date_inscription'])); ?></td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <?php if ($u['est_prestataire'] && !$u['is_validated']): ?>
                                        <a href="users.php?approuver=<?php echo $u['id_utilisateur']; ?>" class="btn text-success" title="Approuver"><i class="bi bi-check-circle-fill"></i></a>
                                    <?php endif; ?>
                                    <a href="#" class="btn text-primary" title="Modifier"><i class="bi bi-pencil-square"></i></a>
                                    <?php if (!$u['est_admin']): ?>
                                    <a href="users.php?supprimer=<?php echo $u['id_utilisateur']; ?>" 
                                       class="btn text-danger" 
                                       onclick="return confirm('Supprimer cet utilisateur ?')" 
                                       title="Supprimer"><i class="bi bi-trash"></i></a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (count($utilisateurs) === 0): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-people mb-3 fs-1 text-muted opacity-25 d-block"></i>
                                <span class="text-muted fw-bold">Aucun utilisateur trouvé pour ces critères.</span>
                            </td>
                        </tr>
                        <?php endif; ?>
                        </tbody>
                </table>
            </div>
            <div class="card-footer bg-white py-3 border-0">
                <nav>
                        <ul class="pagination pagination-sm mb-0 justify-content-center">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&role=<?php echo $role; ?>&recherche=<?php echo $recherche; ?>">Précédent</a>
                            </li>
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&role=<?php echo $role; ?>&recherche=<?php echo $recherche; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&role=<?php echo $role; ?>&recherche=<?php echo $recherche; ?>">Suivant</a>
                            </li>
                        </ul>
                </nav>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Nouvel Administrateur</h5>
                    <button type="button" class="btn-close" data-bs-toggle="modal"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nom complet</label>
                            <input type="text" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Email</label>
                            <input type="email" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Mot de passe provisoire</label>
                            <input type="password" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2">Créer le compte</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>