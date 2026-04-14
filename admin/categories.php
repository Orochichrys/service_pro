<?php 
require_once("includes/auth_check.php"); 
require_once("../includes/db.php");
require_once("../includes/function.php");

// Paramètres de pagination
$limite = 6; // 6 catégories par page
$page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$debut = ($page - 1) * $limite;

// 1. AJOUT CATÉGORIE
if (isset($_POST['ajouter_categorie'])) {
    $nom = securisation($_POST['nom_categorie']);
    $icone = securisation($_POST['icone_categorie']);
    
    // Icône par défaut si vide
    if (empty($icone)) {
        $icone = "bi-folder";
    }

    if (!empty($nom)) {
        $sql = "INSERT INTO Categorie (nom_categorie, icone_categorie) VALUES (:nom, :icone)";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['nom' => $nom, 'icone' => $icone]);
    }
    header("Location: categories.php");
    exit();
}

// 1.5 MODIFICATION CATÉGORIE
if (isset($_POST['modifier_categorie'])) {
    $id = securisation($_POST['id_categorie']);
    $nom = securisation($_POST['nom_categorie']);
    $icone = securisation($_POST['icone_categorie']);

    if (empty($icone)) {
        $icone = "bi-folder";
    }

    if (!empty($nom) && !empty($id)) {
        $sql = "UPDATE Categorie SET nom_categorie = :nom, icone_categorie = :icone WHERE id_categorie = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['nom' => $nom, 'icone' => $icone, 'id' => $id]);
    }
    header("Location: categories.php");
    exit();
}

// 2. AJOUT SERVICE
if (isset($_POST['ajouter_service'])) {
    $nom = securisation($_POST['nom_service']);
    $id_cat = securisation($_POST['id_categorie']);
    if (!empty($nom) && !empty($id_cat)) {
        $sql = "INSERT INTO Service (nom_service, id_categorie) VALUES (:nom, :id_cat)";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['nom' => $nom, 'id_cat' => $id_cat]);
    }
    header("Location: categories.php");
    exit();
}

// 3. SUPPRESSION
if (isset($_GET['suppr_cat'])) {
    $id = securisation($_GET['suppr_cat']);
    $conn->prepare("DELETE FROM Categorie WHERE id_categorie = :id")->execute(['id' => $id]);
    header("Location: categories.php");
    exit();
}

if (isset($_GET['suppr_service'])) {
    $id = securisation($_GET['suppr_service']);
    $conn->prepare("DELETE FROM Service WHERE id_service = :id")->execute(['id' => $id]);
    header("Location: categories.php");
    exit();
}

// 2.5 MODIFICATION SERVICE
if (isset($_POST['modifier_service'])) {
    $id = securisation($_POST['id_service']);
    $nom = securisation($_POST['nom_service']);
    $id_cat = securisation($_POST['id_categorie']);

    if (!empty($nom) && !empty($id_cat) && !empty($id)) {
        $sql = "UPDATE Service SET nom_service = :nom, id_categorie = :id_cat WHERE id_service = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['nom' => $nom, 'id_cat' => $id_cat, 'id' => $id]);
    }
    header("Location: categories.php");
    exit();
}

// 4. RÉCUPÉRATION DES DONNÉES

// On compte le total
$total_elements = $conn->query("SELECT COUNT(*) FROM Categorie")->fetchColumn();
$total_pages = ceil($total_elements / $limite);

// On récupère les catégories de la page
$categories_page = $conn->query("SELECT * FROM Categorie ORDER BY nom_categorie LIMIT $limite OFFSET $debut")->fetchAll(PDO::FETCH_ASSOC);

$catalogue = [];
foreach ($categories_page as $cat) {
    $id_cat = $cat['id_categorie'];
    $stmt = $conn->prepare("SELECT * FROM Service WHERE id_categorie = :id ORDER BY nom_service");
    $stmt->execute(['id' => $id_cat]);
    $cat['services'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $catalogue[] = $cat;
}

// Pour le modal d'ajout (toutes les catégories)
$toutes_categories = $conn->query("SELECT * FROM Categorie ORDER BY nom_categorie")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Catégories - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

   <?php include("includes/sidebar.php") ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold h3">Catalogue des Services</h2>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="bi bi-folder-plus me-2"></i>Nouvelle Catégorie
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                    <i class="bi bi-plus-lg me-2"></i>Ajouter un Service
                </button>
            </div>
        </div>

        <div class="row">
            <?php foreach ($catalogue as $cat): ?>
            <div class="col-md-6 col-xl-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                        <h6 class="fw-bold mb-0 text-primary">
                            <i class="bi <?php echo $cat['icone_categorie'] ?? 'bi-folder'; ?> me-2"></i>
                            <?php echo $cat['nom_categorie']; ?>
                        </h6>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" 
                                       data-bs-toggle="modal" 
                                       data-bs-target="#editCategoryModal" 
                                       data-id="<?php echo $cat['id_categorie']; ?>" 
                                       data-nom="<?php echo $cat['nom_categorie']; ?>" 
                                       data-icone="<?php echo $cat['icone_categorie']; ?>"
                                       onclick="remplirModalEdit(this)">Modifier</a></li>
                                <li><a class="dropdown-item text-danger" onclick="return confirm('Attention : Tous les services de cette catégorie seront supprimés. Continuer ?')" href="categories.php?suppr_cat=<?php echo $cat['id_categorie']; ?>">Supprimer</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($cat['services'] as $ser): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span class="small"><?php echo $ser['nom_service']; ?></span>
                                <div class="btn-group">
                                    <button class="btn text-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editServiceModal" 
                                            data-id="<?php echo $ser['id_service']; ?>" 
                                            data-nom="<?php echo $ser['nom_service']; ?>" 
                                            data-cat="<?php echo $ser['id_categorie']; ?>"
                                            onclick="remplirModalService(this)">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <a href="categories.php?suppr_service=<?php echo $ser['id_service']; ?>" class="btn text-danger" onclick="return confirm('Supprimer ce service ?')"><i class="bi bi-trash"></i></a>
                                </div>
                            </li>
                            <?php endforeach; ?>
                            <?php if (count($cat['services']) === 0): ?>
                                <li class="list-group-item px-0 text-muted small">Aucun service.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (count($catalogue) === 0): ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-folder-x fs-1 text-muted"></i>
                <p class="text-muted mt-2">Aucune catégorie pour le moment.</p>
            </div>
            <?php endif; ?>
        </div>
        <div class="mt-4">
            <div class="card border-0 shadow-sm">
                <div class="card-footer bg-white py-3 border-0 rounded-4">
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
    </div>

    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0">
                <div class="modal-header border-bottom-0">
                    <h5 class="fw-bold">Créer une catégorie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nom de la catégorie</label>
                            <input type="text" name="nom_categorie" class="form-control" placeholder="Ex: Informatique..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Icône (Bootstrap Icon)</label>
                            <input type="text" name="icone_categorie" class="form-control" placeholder="Ex: bi-house (laisser vide pour défaut)">
                            <div class="form-text small">Consultez <a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap Icons</a> pour les codes.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="submit" name="ajouter_categorie" class="btn btn-primary w-100">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addServiceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0">
                <div class="modal-header border-bottom-0">
                    <h5 class="fw-bold">Ajouter un service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Catégorie parente</label>
                            <select name="id_categorie" class="form-select" required>
                                <option value="">Choisir une catégorie...</option>
                                <?php foreach ($toutes_categories as $cat_simple): ?>
                                    <option value="<?php echo $cat_simple['id_categorie']; ?>"><?php echo $cat_simple['nom_categorie']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nom du service</label>
                            <input type="text" name="nom_service" class="form-control" placeholder="Ex: Réparation PC..." required>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="submit" name="ajouter_service" class="btn btn-primary w-100">Ajouter au catalogue</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0">
                <div class="modal-header border-bottom-0">
                    <h5 class="fw-bold">Modifier la catégorie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST">
                    <input type="hidden" name="id_categorie" id="edit_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nom de la catégorie</label>
                            <input type="text" name="nom_categorie" id="edit_nom" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Icône (Bootstrap Icon)</label>
                            <input type="text" name="icone_categorie" id="edit_icone" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="submit" name="modifier_categorie" class="btn btn-primary w-100">Sauvegarder les modifications</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editServiceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0">
                <div class="modal-header border-bottom-0">
                    <h5 class="fw-bold">Modifier le service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST">
                    <input type="hidden" name="id_service" id="edit_ser_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Catégorie parente</label>
                            <select name="id_categorie" id="edit_ser_cat" class="form-select" required>
                                <?php foreach ($toutes_categories as $cat_simple): ?>
                                    <option value="<?php echo $cat_simple['id_categorie']; ?>"><?php echo $cat_simple['nom_categorie']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nom du service</label>
                            <input type="text" name="nom_service" id="edit_ser_nom" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="submit" name="modifier_service" class="btn btn-primary w-100">Enregistrer les changements</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function remplirModalEdit(btn) {
        document.getElementById('edit_id').value = btn.getAttribute('data-id');
        document.getElementById('edit_nom').value = btn.getAttribute('data-nom');
        document.getElementById('edit_icone').value = btn.getAttribute('data-icone');
    }

    function remplirModalService(btn) {
        document.getElementById('edit_ser_id').value = btn.getAttribute('data-id');
        document.getElementById('edit_ser_nom').value = btn.getAttribute('data-nom');
        document.getElementById('edit_ser_cat').value = btn.getAttribute('data-cat');
    }
    </script>
</body>
</html>