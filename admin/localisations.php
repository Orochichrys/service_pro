<?php 
require_once("includes/verif_auth.php"); 
require_once("../includes/db.php");
require_once("../includes/fonctions.php");

// 1. TRAITEMENT DES AJOUTS
if (isset($_POST['ajouter_region'])) {
    $nom = securisation($_POST['nom_region']);
    if (!empty($nom)) {
        $conn->prepare("INSERT INTO Region (nom_region) VALUES (:nom)")->execute(['nom' => $nom]);
    }
    header("Location: localisations.php"); exit();
}

if (isset($_POST['ajouter_departement'])) {
    $nom = securisation($_POST['nom_departement']);
    $id_reg = securisation($_POST['id_region']);
    if (!empty($nom) && !empty($id_reg)) {
        $conn->prepare("INSERT INTO Departement (nom_departement, id_region) VALUES (:nom, :id_reg)")->execute(['nom' => $nom, 'id_reg' => $id_reg]);
    }
    header("Location: localisations.php"); exit();
}

if (isset($_POST['ajouter_ville'])) {
    $nom = securisation($_POST['nom_ville']);
    $id_dept = securisation($_POST['id_departement']);
    if (!empty($nom) && !empty($id_dept)) {
        $conn->prepare("INSERT INTO Ville (nom_ville, id_departement) VALUES (:nom, :id_dept)")->execute(['nom' => $nom, 'id_dept' => $id_dept]);
    }
    header("Location: localisations.php"); exit();
}

if (isset($_POST['ajouter_quartier'])) {
    $nom = securisation($_POST['nom_quartier']);
    $id_ville = securisation($_POST['id_ville']);
    if (!empty($nom) && !empty($id_ville)) {
        $conn->prepare("INSERT INTO Quartier (nom_quartier, id_ville) VALUES (:nom, :id_ville)")->execute(['nom' => $nom, 'id_ville' => $id_ville]);
    }
    header("Location: localisations.php"); exit();
}

// 1.5 TRAITEMENT DES MODIFICATIONS
if (isset($_POST['modifier_region'])) {
    $id = securisation($_POST['id_region']);
    $nom = securisation($_POST['nom_region']);
    if (!empty($id) && !empty($nom)) {
        $conn->prepare("UPDATE Region SET nom_region = :nom WHERE id_region = :id")->execute(['nom' => $nom, 'id' => $id]);
    }
    header("Location: localisations.php"); exit();
}

if (isset($_POST['modifier_departement'])) {
    $id = securisation($_POST['id_departement']);
    $nom = securisation($_POST['nom_departement']);
    $id_reg = securisation($_POST['id_region']);
    if (!empty($id) && !empty($nom) && !empty($id_reg)) {
        $conn->prepare("UPDATE Departement SET nom_departement = :nom, id_region = :id_reg WHERE id_departement = :id")->execute(['nom' => $nom, 'id_reg' => $id_reg, 'id' => $id]);
    }
    header("Location: localisations.php"); exit();
}

if (isset($_POST['modifier_ville'])) {
    $id = securisation($_POST['id_ville']);
    $nom = securisation($_POST['nom_ville']);
    $id_dept = securisation($_POST['id_departement']);
    if (!empty($id) && !empty($nom) && !empty($id_dept)) {
        $conn->prepare("UPDATE Ville SET nom_ville = :nom, id_departement = :id_dept WHERE id_ville = :id")->execute(['nom' => $nom, 'id_dept' => $id_dept, 'id' => $id]);
    }
    header("Location: localisations.php"); exit();
}

if (isset($_POST['modifier_quartier'])) {
    $id = securisation($_POST['id_quartier']);
    $nom = securisation($_POST['nom_quartier']);
    $id_ville = securisation($_POST['id_ville']);
    if (!empty($id) && !empty($nom) && !empty($id_ville)) {
        $conn->prepare("UPDATE Quartier SET nom_quartier = :nom, id_ville = :id_ville WHERE id_quartier = :id")->execute(['nom' => $nom, 'id_ville' => $id_ville, 'id' => $id]);
    }
    header("Location: localisations.php"); exit();
}

// 2. TRAITEMENT DES SUPPRESSIONS
if (isset($_GET['suppr_region'])) {
    $conn->prepare("DELETE FROM Region WHERE id_region = ?")->execute([securisation($_GET['suppr_region'])]);
    header("Location: localisations.php"); exit();
}
if (isset($_GET['suppr_dept'])) {
    $conn->prepare("DELETE FROM Departement WHERE id_departement = ?")->execute([securisation($_GET['suppr_dept'])]);
    header("Location: localisations.php"); exit();
}
if (isset($_GET['suppr_ville'])) {
    $conn->prepare("DELETE FROM Ville WHERE id_ville = ?")->execute([securisation($_GET['suppr_ville'])]);
    header("Location: localisations.php"); exit();
}
if (isset($_GET['suppr_quartier'])) {
    $conn->prepare("DELETE FROM Quartier WHERE id_quartier = ?")->execute([securisation($_GET['suppr_quartier'])]);
    header("Location: localisations.php"); exit();
}

// 3. RÉCUPÉRATION DES DONNÉES
$regions = $conn->query("SELECT * FROM Region ORDER BY nom_region")->fetchAll(PDO::FETCH_ASSOC);
$depts = $conn->query("SELECT d.*, r.nom_region FROM Departement d JOIN Region r ON d.id_region = r.id_region ORDER BY d.nom_departement")->fetchAll(PDO::FETCH_ASSOC);
$villes = $conn->query("SELECT v.*, d.nom_departement FROM Ville v JOIN Departement d ON v.id_departement = d.id_departement ORDER BY v.nom_ville")->fetchAll(PDO::FETCH_ASSOC);
$quartiers = $conn->query("SELECT q.*, v.nom_ville FROM Quartier q JOIN Ville v ON q.id_ville = v.id_ville ORDER BY q.nom_quartier")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Localisations - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <?php include("includes/barre_laterale.php") ?>

    <div class="main-content">
        <h2 class="fw-bold mb-4">Gestion des Localisations</h2>

        <div class="row">
            <!-- RÉGIONS -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-bold py-3">Régions</div>
                    <div class="card-body">
                        <form action="" method="POST" class="d-flex gap-2 mb-3">
                            <input type="text" name="nom_region" class="form-control form-control-sm" placeholder="Nouvelle région..." required>
                            <button type="submit" name="ajouter_region" class="btn btn-primary btn-sm px-3">Ajouter</button>
                        </form>
                        <div class="table-responsive" style="max-height: 250px;">
                            <table class="table table-sm table-hover align-middle">
                                <tbody class="small">
                                    <?php foreach($regions as $r): ?>
                                    <tr>
                                        <td><?php echo $r['nom_region']; ?></td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <button class="btn text-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editRegionModal" 
                                                        data-id="<?php echo $r['id_region']; ?>" 
                                                        data-nom="<?php echo $r['nom_region']; ?>"
                                                        onclick="remplirRegion(this)"><i class="bi bi-pencil-square"></i></button>
                                                <a href="?suppr_region=<?php echo $r['id_region']; ?>" onclick="return confirm('Attention : Cela supprimera tous les départements et villes associés. Continuer ?')" class="btn text-danger"><i class="bi bi-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DÉPARTEMENTS -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-bold py-3">Départements</div>
                    <div class="card-body">
                        <form action="" method="POST" class="mb-3">
                            <div class="mb-2">
                                <select name="id_region" class="form-select form-select-sm" required>
                                    <option value="">Sélectionner une région...</option>
                                    <?php foreach($regions as $r): ?>
                                    <option value="<?php echo $r['id_region']; ?>"><?php echo $r['nom_region']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="d-flex gap-2">
                                <input type="text" name="nom_departement" class="form-control form-control-sm" placeholder="Nouveau département..." required>
                                <button type="submit" name="ajouter_departement" class="btn btn-primary btn-sm px-3">Ajouter</button>
                            </div>
                        </form>
                        <div class="table-responsive" style="max-height: 250px;">
                            <table class="table table-sm table-hover align-middle">
                                <tbody class="small">
                                    <?php foreach($depts as $d): ?>
                                    <tr>
                                        <td><?php echo $d['nom_departement']; ?> <span class="text-muted opacity-50 ms-2" style="font-size:0.7rem;"><?php echo $d['nom_region']; ?></span></td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <button class="btn text-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editDeptModal" 
                                                        data-id="<?php echo $d['id_departement']; ?>" 
                                                        data-nom="<?php echo $d['nom_departement']; ?>"
                                                        data-reg="<?php echo $d['id_region']; ?>"
                                                        onclick="remplirDept(this)"><i class="bi bi-pencil-square"></i></button>
                                                <a href="?suppr_dept=<?php echo $d['id_departement']; ?>" onclick="return confirm('Supprimer ?')" class="btn text-danger"><i class="bi bi-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- VILLES -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-bold py-3">Villes</div>
                    <div class="card-body">
                        <form action="" method="POST" class="mb-3">
                            <div class="mb-2">
                                <select name="id_departement" class="form-select form-select-sm" required>
                                    <option value="">Sélectionner un département...</option>
                                    <?php foreach($depts as $d): ?>
                                    <option value="<?php echo $d['id_departement']; ?>"><?php echo $d['nom_departement']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="d-flex gap-2">
                                <input type="text" name="nom_ville" class="form-control form-control-sm" placeholder="Nouvelle ville..." required>
                                <button type="submit" name="ajouter_ville" class="btn btn-primary btn-sm px-3">Ajouter</button>
                            </div>
                        </form>
                        <div class="table-responsive" style="max-height: 250px;">
                            <table class="table table-sm table-hover align-middle">
                                <tbody class="small">
                                    <?php foreach($villes as $v): ?>
                                    <tr>
                                        <td><?php echo $v['nom_ville']; ?> <span class="text-muted opacity-50 ms-2" style="font-size:0.7rem;"><?php echo $v['nom_departement']; ?></span></td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <button class="btn text-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editVilleModal" 
                                                        data-id="<?php echo $v['id_ville']; ?>" 
                                                        data-nom="<?php echo $v['nom_ville']; ?>"
                                                        data-dept="<?php echo $v['id_departement']; ?>"
                                                        onclick="remplirVille(this)"><i class="bi bi-pencil-square"></i></button>
                                                <a href="?suppr_ville=<?php echo $v['id_ville']; ?>" onclick="return confirm('Supprimer ?')" class="btn text-danger"><i class="bi bi-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QUARTIERS -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-bold py-3">Quartiers</div>
                    <div class="card-body">
                        <form action="" method="POST" class="mb-3">
                            <div class="mb-2">
                                <select name="id_ville" class="form-select form-select-sm" required>
                                    <option value="">Sélectionner une ville...</option>
                                    <?php foreach($villes as $v): ?>
                                    <option value="<?php echo $v['id_ville']; ?>"><?php echo $v['nom_ville']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="d-flex gap-2">
                                <input type="text" name="nom_quartier" class="form-control form-control-sm" placeholder="Nouveau quartier..." required>
                                <button type="submit" name="ajouter_quartier" class="btn btn-primary btn-sm px-3">Ajouter</button>
                            </div>
                        </form>
                        <div class="table-responsive" style="max-height: 250px;">
                            <table class="table table-sm table-hover align-middle">
                                <tbody class="small">
                                    <?php foreach($quartiers as $q): ?>
                                    <tr>
                                        <td><?php echo $q['nom_quartier']; ?> <span class="text-muted opacity-50 ms-2" style="font-size:0.7rem;"><?php echo $q['nom_ville']; ?></span></td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <button class="btn text-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editQuartierModal" 
                                                        data-id="<?php echo $q['id_quartier']; ?>" 
                                                        data-nom="<?php echo $q['nom_quartier']; ?>"
                                                        data-ville="<?php echo $q['id_ville']; ?>"
                                                        onclick="remplirQuartier(this)"><i class="bi bi-pencil-square"></i></button>
                                                <a href="?suppr_quartier=<?php echo $q['id_quartier']; ?>" onclick="return confirm('Supprimer ?')" class="btn text-danger"><i class="bi bi-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODALS DE MODIFICATION -->
    <!-- Région -->
    <div class="modal fade" id="editRegionModal" tabindex="-1">
        <div class="modal-dialog"><div class="modal-content border-0">
            <div class="modal-header border-bottom-0"><h5 class="fw-bold">Modifier Région</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="" method="POST"><input type="hidden" name="id_region" id="edit_reg_id">
                <div class="modal-body"><label class="form-label small fw-bold">Nom de la région</label><input type="text" name="nom_region" id="edit_reg_nom" class="form-control" required></div>
                <div class="modal-footer border-top-0"><button type="submit" name="modifier_region" class="btn btn-primary w-100">Enregistrer</button></div>
            </form>
        </div></div>
    </div>

    <!-- Département -->
    <div class="modal fade" id="editDeptModal" tabindex="-1">
        <div class="modal-dialog"><div class="modal-content border-0">
            <div class="modal-header border-bottom-0"><h5 class="fw-bold">Modifier Département</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="" method="POST"><input type="hidden" name="id_departement" id="edit_dept_id">
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label small fw-bold">Région associée</label><select name="id_region" id="edit_dept_reg" class="form-select" required>
                        <?php foreach($regions as $r): ?><option value="<?php echo $r['id_region']; ?>"><?php echo $r['nom_region']; ?></option><?php endforeach; ?>
                    </select></div>
                    <label class="form-label small fw-bold">Nom du département</label><input type="text" name="nom_departement" id="edit_dept_nom" class="form-control" required>
                </div>
                <div class="modal-footer border-top-0"><button type="submit" name="modifier_departement" class="btn btn-primary w-100">Enregistrer</button></div>
            </form>
        </div></div>
    </div>

    <!-- Ville -->
    <div class="modal fade" id="editVilleModal" tabindex="-1">
        <div class="modal-dialog"><div class="modal-content border-0">
            <div class="modal-header border-bottom-0"><h5 class="fw-bold">Modifier Ville</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="" method="POST"><input type="hidden" name="id_ville" id="edit_ville_id">
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label small fw-bold">Département associé</label><select name="id_departement" id="edit_ville_dept" class="form-select" required>
                        <?php foreach($depts as $d): ?><option value="<?php echo $d['id_departement']; ?>"><?php echo $d['nom_departement']; ?></option><?php endforeach; ?>
                    </select></div>
                    <label class="form-label small fw-bold">Nom de la ville</label><input type="text" name="nom_ville" id="edit_ville_nom" class="form-control" required>
                </div>
                <div class="modal-footer border-top-0"><button type="submit" name="modifier_ville" class="btn btn-primary w-100">Enregistrer</button></div>
            </form>
        </div></div>
    </div>

    <!-- Quartier -->
    <div class="modal fade" id="editQuartierModal" tabindex="-1">
        <div class="modal-dialog"><div class="modal-content border-0">
            <div class="modal-header border-bottom-0"><h5 class="fw-bold">Modifier Quartier</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="" method="POST"><input type="hidden" name="id_quartier" id="edit_q_id">
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label small fw-bold">Ville associée</label><select name="id_ville" id="edit_q_ville" class="form-select" required>
                        <?php foreach($villes as $v): ?><option value="<?php echo $v['id_ville']; ?>"><?php echo $v['nom_ville']; ?></option><?php endforeach; ?>
                    </select></div>
                    <label class="form-label small fw-bold">Nom du quartier</label><input type="text" name="nom_quartier" id="edit_q_nom" class="form-control" required>
                </div>
                <div class="modal-footer border-top-0"><button type="submit" name="modifier_quartier" class="btn btn-primary w-100">Enregistrer</button></div>
            </form>
        </div></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    function remplirRegion(btn) {
        document.getElementById('edit_reg_id').value = btn.getAttribute('data-id');
        document.getElementById('edit_reg_nom').value = btn.getAttribute('data-nom');
    }
    function remplirDept(btn) {
        document.getElementById('edit_dept_id').value = btn.getAttribute('data-id');
        document.getElementById('edit_dept_nom').value = btn.getAttribute('data-nom');
        document.getElementById('edit_dept_reg').value = btn.getAttribute('data-reg');
    }
    function remplirVille(btn) {
        document.getElementById('edit_ville_id').value = btn.getAttribute('data-id');
        document.getElementById('edit_ville_nom').value = btn.getAttribute('data-nom');
        document.getElementById('edit_ville_dept').value = btn.getAttribute('data-dept');
    }
    function remplirQuartier(btn) {
        document.getElementById('edit_q_id').value = btn.getAttribute('data-id');
        document.getElementById('edit_q_nom').value = btn.getAttribute('data-nom');
        document.getElementById('edit_q_ville').value = btn.getAttribute('data-ville');
    }
    </script>
</body>
</html>
