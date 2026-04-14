<?php 
session_start();
require_once("includes/db.php");
require_once("includes/fonctions.php");

// 1. Récupération des catégories (8 max pour l'accueil)
$categories = $conn->query("SELECT * FROM Categorie LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);

// 2. Récupération des prestations récentes (6 max)
$sql = "SELECT p.*, u.nom_utilisateur, u.prenom_utilisateur, q.nom_quartier, v.nom_ville, s.nom_service, c.nom_categorie 
        FROM Prestation p 
        JOIN Utilisateur u ON p.id_utilisateur = u.id_utilisateur 
        JOIN Service s ON p.id_service = s.id_service
        JOIN Categorie c ON s.id_categorie = c.id_categorie
        LEFT JOIN Quartier q ON u.id_quartier = q.id_quartier
        LEFT JOIN Ville v ON q.id_ville = v.id_ville
        WHERE p.statut_prestation = 'validee'
        ORDER BY p.datecrea_prestation DESC 
        LIMIT 6";
$prestations = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Pro - Votre expert à domicile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <?php include("includes/barre_navigation.php"); ?>

    <!-- HERO SECTION -->
    <header class="hero-section text-white d-flex align-items-center">
        <div class="container text-center">
            <h1 class="display-3 fw-bold mb-3">Le service qu'il vous faut, <br>au quartier.</h1>
            <p class="lead mb-5 opacity-75">Trouvez des experts vérifiés pour tous vos besoins quotidiens.</p>

            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <form action="catalogue.php" class="search-box p-2 bg-white rounded-pill d-flex shadow-lg mb-4">
                        <input type="text" name="search" class="form-control border-0 rounded-pill px-4"
                            placeholder="Coiffure, Plomberie, Ménage...">
                        <button class="btn btn-primary rounded-pill px-4 py-2 fw-bold d-flex align-items-center">
                            <i class="bi bi-search me-2"></i> Rechercher
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- SECTION : CATEGORIES -->
    <section class="py-5 bg-section-categories">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h2 class="fw-bold mb-0">Catégories</h2>
                    <p class="text-muted small">Parcourez nos domaines d'expertise</p>
                </div>
                <a href="catalogue.php" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold">
                    <i class="bi bi-grid-3x3-gap me-1"></i> Tout voir
                </a>
            </div>

            <div class="row g-4">
                <?php foreach($categories as $cat): ?>
                <div class="col-6 col-md-3">
                    <a href="catalogue.php?cat=<?php echo $cat['id_categorie']; ?>"
                        class="card h-100 border-0 shadow-sm text-center py-4 text-decoration-none category-item rounded-4">
                        <div class="category-icon mb-3">
                            <i class="bi <?php echo $cat['icone_categorie'] ?? 'bi-folder'; ?> fs-2"></i>
                        </div>
                        <h6 class="text-dark fw-bold mb-0"><?php echo $cat['nom_categorie']; ?></h6>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- SECTION : OFFRES RECENTES (PLUTÔT DANS LE PARCOURS) -->
    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h2 class="fw-bold mb-0">Offres récentes</h2>
                    <p class="text-muted small mb-0">Découvrez les dernières prestations disponibles</p>
                </div>
                <a href="catalogue.php" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold">
                    <i class="bi bi-chevron-right me-1"></i> Explorer plus
                </a>
            </div>
            <div class="row g-4">
                <?php foreach($prestations as $p): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm overflow-hidden service-card rounded-4">
                        <div class="position-relative">
                            <img src="<?php echo $p['image_prestation'] ?? 'assets/img/default.jpg'; ?>"
                                class="card-img-top" alt="<?php echo $p['titre_prestation']; ?>" style="height: 200px; object-fit: cover;">
                            <span class="badge bg-white text-dark position-absolute top-0 end-0 m-3 shadow-sm rounded-pill px-3 py-2 fw-bold">
                                <?php echo number_format($p['prix_prestation'], 0, ',', ' '); ?> F
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <small class="text-primary fw-bold text-uppercase"><?php echo $p['nom_categorie']; ?></small>
                            </div>
                            <h5 class="card-title fw-bold h6"><?php echo $p['titre_prestation']; ?></h5>
                            <p class="card-text text-muted small"><?php echo substr($p['description_prestation'], 0, 80); ?>...</p>
                            
                            <div class="d-flex align-items-center mt-3 pt-3 border-top">
                                <div class="avatar-xs bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width:30px; height:30px; font-size:0.75rem; font-weight: bold;">
                                    <?php echo strtoupper(substr($p['nom_utilisateur'], 0, 1) . substr($p['prenom_utilisateur'], 0, 1)); ?>
                                </div>
                                <div class="ms-1 overflow-hidden">
                                    <small class="d-block fw-bold text-dark text-truncate small"><?php echo $p['prenom_utilisateur'].' '.$p['nom_utilisateur']; ?></small>
                                    <small class="text-muted text-truncate d-block" style="font-size: 0.7rem;">
                                        <i class="bi bi-geo-alt"></i> <?php echo $p['nom_ville'] ?? 'Indéfinie'; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <a href="detail_prestation.php?id=<?php echo $p['id_prestation']; ?>" class="stretched-link"></a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- SECTION : COMMENT CA MARCHE (SIMPLIFIED & COMPACT) -->
    <section class="py-5 bg-light border-top border-bottom">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Comment ça marche ?</h2>
                <div class="bg-primary mx-auto" style="width: 50px; height: 3px; border-radius: 2px;"></div>
            </div>
            <div class="row text-center g-4 justify-content-center">
                <div class="col-md-3">
                    <div class="px-3">
                        <i class="bi bi-search fs-2 text-primary mb-2 d-block"></i>
                        <h6 class="fw-bold mb-1">Recherchez</h6>
                        <p class="text-muted small mb-0">Trouvez un expert près de chez vous.</p>
                    </div>
                </div>
                <div class="col-md-1 d-none d-md-flex align-items-center justify-content-center opacity-25">
                    <i class="bi bi-chevron-right fs-4"></i>
                </div>
                <div class="col-md-3">
                    <div class="px-3">
                        <i class="bi bi-check-circle fs-2 text-primary mb-2 d-block"></i>
                        <h6 class="fw-bold mb-1">Commandez</h6>
                        <p class="text-muted small mb-0">Réservez en quelques clics.</p>
                    </div>
                </div>
                <div class="col-md-1 d-none d-md-flex align-items-center justify-content-center opacity-25">
                    <i class="bi bi-chevron-right fs-4"></i>
                </div>
                <div class="col-md-3">
                    <div class="px-2">
                        <i class="bi bi-star fs-2 text-primary mb-2 d-block"></i>
                        <h6 class="fw-bold mb-1">Évaluez</h6>
                        <p class="text-muted small mb-0">Notez la prestation reçue.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION : POURQUOI NOUS ? (MINIMALIST) -->
    <section class="py-5">
        <div class="container py-4">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <h2 class="fw-bold mb-4">Pourquoi ServicePro ?</h2>
                    <p class="text-muted mb-4 lead small">La simplicité, la sécurité et la rapidité réunies pour vos services à domicile.</p>
                    
                    <ul class="list-unstyled">
                        <li class="mb-3 d-flex align-items-center">
                            <i class="bi bi-patch-check-fill text-success me-3 fs-5"></i>
                            <span class="small fw-bold">Prestataires vérifiés et qualifiés</span>
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                            <i class="bi bi-patch-check-fill text-success me-3 fs-5"></i>
                            <span class="small fw-bold">Prix fixes et sans surprises</span>
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                            <i class="bi bi-patch-check-fill text-success me-3 fs-5"></i>
                            <span class="small fw-bold">Intervention à domicile rapide</span>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 bg-primary-subtle p-4 rounded-4 text-center">
                        <div class="row g-4">
                            <div class="col-6">
                                <h4 class="fw-bold text-primary mb-0">500+</h4>
                                <small class="text-muted fw-bold small text-uppercase">Experts</small>
                            </div>
                            <div class="col-6">
                                <h4 class="fw-bold text-primary mb-0">4.9/5</h4>
                                <small class="text-muted fw-bold small text-uppercase">Note Avis</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION : CTA DEVENIR PRESTATAIRE -->
    <section class="py-5 bg-primary text-white position-relative overflow-hidden">
        <i class="bi bi-tools position-absolute top-0 start-0 opacity-10" style="font-size: 7rem; transform: translate(-10%, -10%) rotate(-15deg);"></i>
        <i class="bi bi-briefcase position-absolute bottom-0 end-0 opacity-10" style="font-size: 9rem; transform: translate(10%, 10%) rotate(15deg);"></i>
        
        <div class="container py-4 text-center position-relative" style="z-index: 2;">
            <h2 class="fw-bold h1 mb-3">Gagnez de l'argent avec ServicePro</h2>
            <p class="lead mb-4 opacity-75 small">Rejoignez notre réseau d'experts et boostez votre activité dès aujourd'hui.</p>
            <a href="auth/inscription.php" class="btn btn-light btn-lg px-5 py-3 fw-bold rounded-pill text-primary border-0 shadow-sm mt-2">Devenir Prestataire</a>
        </div>
    </section>

    <?php include("includes/pied_de_page.php");?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>