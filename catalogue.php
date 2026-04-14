<?php 
session_start();
require_once("includes/db.php");
require_once("includes/function.php");

// 1. Récupération des filtres depuis l'URL
$search = isset($_GET['search']) ? securisation($_GET['search']) : "";
$cat_id = isset($_GET['categorie']) ? (int)$_GET['categorie'] : (isset($_GET['cat']) ? (int)$_GET['cat'] : "");
$ville_id = isset($_GET['ville']) ? (int)$_GET['ville'] : "";
$prix_max = isset($_GET['prix_max']) ? (int)$_GET['prix_max'] : 100000;
$tri = isset($_GET['tri']) ? securisation($_GET['tri']) : "recent";

// 2. Données pour les filtres
$categories = $conn->query("SELECT * FROM Categorie ORDER BY nom_categorie")->fetchAll(PDO::FETCH_ASSOC);
$villes = $conn->query("SELECT * FROM Ville ORDER BY nom_ville")->fetchAll(PDO::FETCH_ASSOC);

// 4. LOGIQUE DE PAGINATION
$results_per_page = 9;
$page = isset($_GET['p']) && is_numeric($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page - 1) * $results_per_page;

// 3. Construction de la requête SQL (COUNT pour pagination)
$count_sql = "SELECT COUNT(*) FROM Prestation p 
              JOIN Service s ON p.id_service = s.id_service
              JOIN Categorie c ON s.id_categorie = c.id_categorie
              JOIN Utilisateur u ON p.id_utilisateur = u.id_utilisateur
              LEFT JOIN Quartier q ON u.id_quartier = q.id_quartier
              LEFT JOIN Ville v ON q.id_ville = v.id_ville
              WHERE p.prix_prestation <= :prix AND u.is_validated = 1";

$sql = "SELECT p.*, u.nom_utilisateur, u.prenom_utilisateur, q.nom_quartier, v.nom_ville, s.nom_service, c.nom_categorie 
        FROM Prestation p 
        JOIN Utilisateur u ON p.id_utilisateur = u.id_utilisateur 
        JOIN Service s ON p.id_service = s.id_service
        JOIN Categorie c ON s.id_categorie = c.id_categorie
        LEFT JOIN Quartier q ON u.id_quartier = q.id_quartier
        LEFT JOIN Ville v ON q.id_ville = v.id_ville
        WHERE p.prix_prestation <= :prix AND u.is_validated = 1";

$params = ['prix' => $prix_max];
$where = "";

if(!empty($search)){
    $where .= " AND (p.titre_prestation LIKE :search OR p.description_prestation LIKE :search OR c.nom_categorie LIKE :search)";
    $params['search'] = "%$search%";
}
if(!empty($cat_id)){
    $where .= " AND c.id_categorie = :cat";
    $params['cat'] = $cat_id;
}
if(!empty($ville_id)){
    $where .= " AND v.id_ville = :ville";
    $params['ville'] = $ville_id;
}

$count_stmt = $conn->prepare($count_sql . $where);
$count_stmt->execute($params);
$total_results = $count_stmt->fetchColumn();
$total_pages = ceil($total_results / $results_per_page);

// Gestion du tri
$order_by = "p.datecrea_prestation DESC"; // Par défaut : Plus récents
if($tri == 'prix_asc') $order_by = "p.prix_prestation ASC";
if($tri == 'prix_desc') $order_by = "p.prix_prestation DESC";

$sql .= $where . " ORDER BY $order_by LIMIT $results_per_page OFFSET $offset";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$prestations = $stmt->fetchAll(PDO::FETCH_ASSOC);
$nb_resultats = count($prestations);

// Générer la query string pour conserver les filtres
$query_params = $_GET;
unset($query_params['p']);
$base_url = "catalogue.php?" . http_build_query($query_params);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue des Services - ServicePro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="bg-light">

    <?php include("includes/navbar.php"); ?>

    <div class="container py-4">
        <div class="row">

            <aside class="col-lg-3 mb-4">
                <div class="card border-0 shadow-sm p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-filter-left"></i> Filtres</h5>
                        <button class="btn btn-outline-primary btn-sm d-lg-none rounded-pill" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                            Afficher
                        </button>
                    </div>

                    <div class="collapse d-lg-block" id="filterCollapse">
                        <form action="" method="GET">
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">MOT-CLÉ</label>
                                <input type="text" name="search" class="form-control" placeholder="Ex: Plombier..." value="<?php echo $search; ?>">
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">CATÉGORIE</label>
                                <select name="categorie" class="form-select">
                                    <option value="">Toutes</option>
                                    <?php foreach($categories as $c): ?>
                                        <option value="<?php echo $c['id_categorie']; ?>" <?php echo ($cat_id == $c['id_categorie']) ? 'selected' : ''; ?>>
                                            <?php echo $c['nom_categorie']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">VILLE</label>
                                <select name="ville" class="form-select">
                                    <option value="">Toute la Côte d'Ivoire</option>
                                    <?php foreach($villes as $v): ?>
                                        <option value="<?php echo $v['id_ville']; ?>" <?php echo ($ville_id == $v['id_ville']) ? 'selected' : ''; ?>>
                                            <?php echo $v['nom_ville']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">PRIX MAX (FCFA)</label>
                                <input type="range" name="prix_max" class="form-range" min="1000" max="100000" step="1000" id="priceRange" value="<?php echo $prix_max; ?>">
                                <div class="d-flex justify-content-between">
                                    <small>1 000</small>
                                    <small id="priceValue" class="fw-bold text-primary"><?php echo number_format($prix_max, 0, ',', ' '); ?></small>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 fw-bold">Appliquer</button>
                            <a href="catalogue.php"
                                class="btn btn-link w-100 btn-sm text-muted mt-2 text-decoration-none">Réinitialiser</a>
                        </form>
                    </div>
                </div>
            </aside>

            <main class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
                    <span class="text-muted"><strong><?php echo $nb_resultats; ?></strong> services trouvés</span>
                    <div class="d-flex align-items-center">
                        <label class="me-2 small text-nowrap">Trier par :</label>
                        <form id="sortForm" method="GET" class="d-inline-block">
                            <!-- On conserve les filtres cachés pour ne pas les perdre au changement de tri -->
                            <?php if(!empty($search)): ?><input type="hidden" name="search" value="<?php echo $search; ?>"><?php endif; ?>
                            <?php if(!empty($cat_id)): ?><input type="hidden" name="categorie" value="<?php echo $cat_id; ?>"><?php endif; ?>
                            <?php if(!empty($ville_id)): ?><input type="hidden" name="ville" value="<?php echo $ville_id; ?>"><?php endif; ?>
                            <input type="hidden" name="prix_max" value="<?php echo $prix_max; ?>">
                            
                            <select name="tri" class="form-select form-select-sm w-auto rounded-3" onchange="this.form.submit()">
                                <option value="recent" <?php echo ($tri == 'recent') ? 'selected' : ''; ?>>Plus récents</option>
                                <option value="prix_asc" <?php echo ($tri == 'prix_asc') ? 'selected' : ''; ?>>Prix croissant</option>
                                <option value="prix_desc" <?php echo ($tri == 'prix_desc') ? 'selected' : ''; ?>>Prix décroissant</option>
                            </select>
                        </form>
                    </div>
                </div>

                <div class="row g-4">
                    <?php foreach($prestations as $p): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="card h-100 border-0 shadow-sm service-card-hover">
                            <div class="position-relative">
                                <img src="<?php echo $p['image_prestation'] ?? 'assets/img/default.jpg'; ?>"
                                    class="card-img-top" alt="<?php echo $p['titre_prestation']; ?>" style="height: 180px; object-fit: cover;">
                                <div class="badge bg-white text-primary position-absolute bottom-0 start-0 m-2 shadow-sm fw-bold">
                                    <?php echo number_format($p['prix_prestation'], 0, ',', ' '); ?> FCFA
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted text-uppercase fw-bold"><i class="bi bi-tag"></i> <?php echo $p['nom_categorie']; ?></small>
                                </div>
                                <h6 class="card-title fw-bold mb-2">
                                    <a href="detail_prestation.php?id=<?php echo $p['id_prestation']; ?>" class="text-decoration-none text-dark stretched-link">
                                        <?php echo $p['titre_prestation']; ?>
                                    </a>
                                </h6>
                                <p class="text-muted small mb-0"><?php echo substr($p['description_prestation'], 0, 60); ?>...</p>

                                <div class="d-flex align-items-center mt-3 pt-3 border-top position-relative" style="z-index: 2;">
                                    <div class="avatar-xs bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width:30px; height:30px; font-size:0.75rem; font-weight: bold;">
                                        <?php echo strtoupper(substr($p['nom_utilisateur'], 0, 1) . substr($p['prenom_utilisateur'], 0, 1)); ?>
                                    </div>
                                    <div class="ms-1 overflow-hidden">
                                        <small class="d-block fw-bold text-dark text-truncate small"><?php echo $p['prenom_utilisateur'].' '.$p['nom_utilisateur']; ?></small>
                                        <small class="text-muted text-truncate d-block" style="font-size: 0.7rem;">
                                            <i class="bi bi-geo-alt"></i> <?php echo $p['nom_ville'] ?? 'Indéfinie'; ?>, <?php echo $p['nom_quartier'] ?? ''; ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if($nb_resultats == 0): ?>
                        <div class="col-12 text-center py-5">
                            <i class="bi bi-search fs-1 text-muted"></i>
                            <h5 class="mt-3">Aucun service ne correspond à votre recherche.</h5>
                            <p class="text-muted">Essayez d'autres filtres ou mots-clés.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if($total_pages > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link shadow-sm" href="<?php echo $base_url . '&p=' . ($page-1); ?>">Précédent</a>
                        </li>
                        
                        <?php for($i=1; $i<=$total_pages; $i++): ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link shadow-sm" href="<?php echo $base_url . '&p=' . $i; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>

                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link shadow-sm" href="<?php echo $base_url . '&p=' . ($page+1); ?>">Suivant</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </main>

        </div>
    </div>

       <?php include("includes/footer.php");?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Script pour mettre à jour la valeur du prix max en temps réel
        const range = document.getElementById('priceRange');
        const value = document.getElementById('priceValue');
        range.addEventListener('input', () => {
            value.textContent = new Intl.NumberFormat().format(range.value);
        });
    </script>
</body>

</html>