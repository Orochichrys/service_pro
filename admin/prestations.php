<?php 
require_once("includes/verif_auth.php"); 
require_once("../includes/db.php");

// Actions de validation / refus
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'approuver') {
        $conn->prepare("UPDATE Prestation SET statut_prestation = 'validee' WHERE id_prestation = ?")->execute([$id]);
        $_SESSION['admin_msg'] = "La prestation a été approuvée.";
    } elseif ($action === 'rejeter') {
        $conn->prepare("UPDATE Prestation SET statut_prestation = 'refusee' WHERE id_prestation = ?")->execute([$id]);
        $_SESSION['admin_msg'] = "La prestation a été rejetée.";
    }
    
    redirection("prestations.php");
}

// Récupération des prestations en attente
$sql = "SELECT p.*, u.nom_utilisateur, u.prenom_utilisateur, s.nom_service, c.nom_categorie 
        FROM Prestation p 
        JOIN Utilisateur u ON p.id_utilisateur = u.id_utilisateur 
        JOIN Service s ON p.id_service = s.id_service
        JOIN Categorie c ON s.id_categorie = c.id_categorie
        WHERE p.statut_prestation = 'en_attente'
        ORDER BY p.datecrea_prestation DESC";
$prestations_attente = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation des Services - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include("includes/barre_laterale.php") ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold">Validation des Services</h2>
                <p class="text-muted small">Examinez et approuvez les nouvelles prestations soumises par les prestataires.</p>
            </div>
        </div>

        <?php if(isset($_SESSION['admin_msg'])): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo $_SESSION['admin_msg']; unset($_SESSION['admin_msg']); ?>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="fw-bold mb-0">Prestations en attente d'examen (<?php echo count($prestations_attente); ?>)</h5>
            </div>
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Prestation</th>
                            <th>Prestataire</th>
                            <th>Détails</th>
                            <th>Prix</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($prestations_attente as $p): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center py-2">
                                    <img src="../<?php echo $p['image_prestation'] ?? 'assets/img/default.jpg'; ?>" 
                                         class="rounded me-3 border shadow-sm" style="width: 60px; height: 60px; object-fit: cover;" alt="Aperçu">
                                    <div>
                                        <h6 class="mb-0 small fw-bold text-dark"><?php echo $p['titre_prestation']; ?></h6>
                                        <small class="text-muted text-uppercase" style="font-size: 0.65rem;"><?php echo $p['nom_categorie'] . ' / ' . $p['nom_service']; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-xs bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 25px; height: 25px; font-size: 0.7rem;">
                                        <?php echo strtoupper(substr($p['nom_utilisateur'], 0, 1) . substr($p['prenom_utilisateur'], 0, 1)); ?>
                                    </div>
                                    <small class="fw-bold"><?php echo $p['prenom_utilisateur'] . ' ' . $p['nom_utilisateur']; ?></small>
                                </div>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" data-bs-toggle="modal" data-bs-target="#modal<?php echo $p['id_prestation']; ?>">
                                    <i class="bi bi-info-circle me-1"></i> Voir la description
                                </button>
                                
                                <!-- Modal Description -->
                                <div class="modal fade" id="modal<?php echo $p['id_prestation']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <div class="modal-header border-bottom-0">
                                                <h5 class="modal-title fw-bold">Description de la prestation</h5>
                                                <button type="button" class="btn-close" data-bs-toggle="modal" data-bs-target="#modal<?php echo $p['id_prestation']; ?>"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="text-muted"><?php echo nl2br($p['description_prestation']); ?></p>
                                            </div>
                                            <div class="modal-footer border-top-0">
                                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Fermer</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="fw-bold text-dark"><?php echo number_format($p['prix_prestation'], 0, ',', ' '); ?> F</span></td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="?action=approuver&id=<?php echo $p['id_prestation']; ?>" class="btn btn-sm btn-success rounded-pill px-3 fw-bold">
                                        <i class="bi bi-check me-1"></i> Approuver
                                    </a>
                                    <a href="?action=rejeter&id=<?php echo $p['id_prestation']; ?>" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold" onclick="return confirm('Rejeter ce service ?')">
                                        <i class="bi bi-x me-1"></i> Rejeter
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (count($prestations_attente) === 0): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-emoji-smile fs-1 text-muted opacity-25 d-block mb-3"></i>
                                <h6 class="fw-bold text-muted">Toutes les prestations sont traitées.</h6>
                                <p class="text-muted small">Rien à valider pour le moment.</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
