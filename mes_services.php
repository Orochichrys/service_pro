<?php 
session_start();
require_once("includes/db.php");
require_once("includes/function.php");

// 1. Vérification prestataire
if (!isset($_SESSION['user_id']) || !$_SESSION['est_prestataire']) {
    header("Location: mon_profil.php");
    exit();
}
if (isset($_SESSION['is_validated']) && $_SESSION['is_validated'] == 0) {
    $_SESSION['profile_success'] = "Veuillez patienter ! Votre compte prestataire est en attente de validation par l'administration.";
    header("Location: mon_profil.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Traitement des actions
if(isset($_GET['suppr_service'])){
    $id_serv = (int)$_GET['suppr_service'];
    $conn->prepare("DELETE FROM Prestation WHERE id_prestation = ? AND id_utilisateur = ?")->execute([$id_serv, $user_id]);
    $_SESSION['service_msg'] = "Service supprimé avec succès.";
    header("Location: mes_services.php"); exit();
}

// 3. Récupération des services
$sql = "SELECT p.*, c.nom_categorie, c.icone_categorie 
        FROM Prestation p 
        JOIN Service s ON p.id_service = s.id_service
        JOIN Categorie c ON s.id_categorie = c.id_categorie
        WHERE p.id_utilisateur = ?
        ORDER BY p.datecrea_prestation DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$mes_services = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Services - ServicePro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

    <?php include("includes/navbar.php"); ?>

    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold mb-1">Catalogues de mes services</h2>
                        <p class="text-muted small">Gérez vos offres visibles par les clients sur la plateforme.</p>
                    </div>
                    <a href="ajouter_prestation.php" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-plus-circle me-2"></i>Publier une offre
                    </a>
                </div>

                <?php if(isset($_SESSION['service_msg'])): ?>
                    <div class="alert alert-success rounded-4 border-0 shadow-sm mb-4 py-3">
                        <i class="bi bi-check-circle-fill me-2"></i> <?php echo $_SESSION['service_msg']; unset($_SESSION['service_msg']); ?>
                    </div>
                <?php endif; ?>

                <div class="row g-4">
                    <?php if(count($mes_services) > 0): ?>
                        <?php foreach($mes_services as $s): ?>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                                <div class="row g-0">
                                    <div class="col-4">
                                        <img src="<?php echo $s['image_prestation'] ?? 'assets/img/default.jpg'; ?>" 
                                             class="img-fluid h-100" style="object-fit: cover; min-height: 120px;" alt="Service">
                                    </div>
                                    <div class="col-8">
                                        <div class="card-body p-3 h-100 d-flex flex-column">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <small class="text-primary fw-bold text-uppercase" style="font-size: 0.65rem;">
                                                    <i class="bi <?php echo $s['icone_categorie'] ?? 'bi-tag'; ?> me-1"></i> <?php echo $s['nom_categorie']; ?>
                                                </small>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-light rounded-circle p-0" style="width:28px; height:28px;" data-bs-toggle="dropdown">
                                                        <i class="bi bi-three-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-3">
                                                        <li><a class="dropdown-item py-2 small" href="modifier_prestation.php?id=<?php echo $s['id_prestation']; ?>"><i class="bi bi-pencil me-2"></i>Modifier</a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item py-2 small text-danger" href="?suppr_service=<?php echo $s['id_prestation']; ?>" onclick="return confirm('Supprimer définitivement ce service ?')"><i class="bi bi-trash me-2"></i>Supprimer</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <h6 class="fw-bold mb-1"><?php echo $s['titre_prestation']; ?></h6>
                                            <div class="fw-bold mb-2 text-dark"><?php echo number_format($s['prix_prestation'], 0, ',', ' '); ?> F</div>
                                            <div class="mt-auto">
                                                <a href="detail_prestation.php?id=<?php echo $s['id_prestation']; ?>" class="text-muted small text-decoration-none">
                                                    <i class="bi bi-eye me-1"></i> Aperçu public
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5 bg-white rounded-4 shadow-sm">
                            <i class="bi bi-cloud-upload fs-1 text-muted opacity-25 d-block mb-3"></i>
                            <h5 class="fw-bold">Aucun service publié</h5>
                            <p class="text-muted small">Commencez à gagner de l'argent en proposant vos compétences.</p>
                            <a href="ajouter_prestation.php" class="btn btn-primary rounded-pill px-4 mt-3 fw-bold">Publier mon premier service</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include("includes/footer.php");?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
