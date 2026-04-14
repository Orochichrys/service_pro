<?php 
session_start();
require_once("includes/db.php");
require_once("includes/fonctions.php");

// 1. Vérification si prestataire
if (!isset($_SESSION['user_id']) || !$_SESSION['est_prestataire']) {
    redirection("auth/connexion.php");
}

$user_id = $_SESSION['user_id'];
$id_presta = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 2. Récupération des données actuelles
$stmt = $conn->prepare("SELECT * FROM Prestation WHERE id_prestation = ? AND id_utilisateur = ?");
$stmt->execute([$id_presta, $user_id]);
$presta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$presta) {
    redirection("mon_profil.php");
}

// 3. Récupération des services pour le sélecteur
$services_sql = "SELECT s.*, c.nom_categorie FROM Service s JOIN Categorie c ON s.id_categorie = c.id_categorie ORDER BY c.nom_categorie, s.nom_service";
$liste_services = $conn->query($services_sql)->fetchAll(PDO::FETCH_ASSOC);

// 4. Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titre = securisation($_POST['titre_prestation']);
    $id_service = (int)$_POST['id_service'];
    $prix = (float)$_POST['prix_prestation'];
    $description = securisation($_POST['description_prestation']);
    
    $image_path = $presta['image_prestation']; // Garder l'ancienne par défaut
    
    // IMAGE UPLOAD LOGIC
    $upload_error = "";
    if (isset($_FILES['image_prestation']) && $_FILES['image_prestation']['name'] != "") {
        if ($_FILES['image_prestation']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $filename = $_FILES['image_prestation']['name'];
            $filesize = $_FILES['image_prestation']['size'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if ($filesize > 5 * 1024 * 1024) {
                $upload_error = "L'image est trop volumineuse (max 5Mo).";
            } elseif (!in_array($ext, $allowed)) {
                $upload_error = "Format d'image non autorisé (JPG, PNG, WEBP uniquement).";
            } else {
                $new_name = "service_" . time() . "_" . uniqid() . "." . $ext;
                $upload_dir = "assets/img/uploads/";
                
                if (move_uploaded_file($_FILES['image_prestation']['tmp_name'], $upload_dir . $new_name)) {
                    $image_path = $upload_dir . $new_name;
                } else {
                    $upload_error = "Erreur lors de l'enregistrement de l'image sur le serveur. Vérifiez les droits d'écriture.";
                }
            }
        } else {
            $upload_error = "Erreur lors du transfert de l'image (Code d'erreur : " . $_FILES['image_prestation']['error'] . ").";
        }
    }

    if (empty($upload_error) && !empty($titre) && !empty($id_service) && $prix > 0) {
        $sql = "UPDATE Prestation SET titre_prestation = ?, description_prestation = ?, prix_prestation = ?, image_prestation = ?, id_service = ?, statut_prestation = 'en_attente' 
                WHERE id_prestation = ? AND id_utilisateur = ?";
        $conn->prepare($sql)->execute([$titre, $description, $prix, $image_path, $id_service, $id_presta, $user_id]);
        
        $_SESSION['service_msg'] = "Service mis à jour avec succès ! Il sera réexaminé par l'administration.";
        redirection("mes_services.php");
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Service - ServicePro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="bg-light">

    <?php include("includes/barre_navigation.php"); ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">

                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="mon_profil.php" class="text-decoration-none">Mon Profil</a></li>
                        <li class="breadcrumb-item active">Modifier le Service</li>
                    </ol>
                </nav>

                <div class="card border-0 shadow-sm p-4 p-md-5 rounded-4">
                    <div class="mb-4">
                        <h2 class="fw-bold h3 text-primary">Modifier votre prestation</h2>
                        <p class="text-muted small">Mettez à jour les détails de votre offre.</p>
                    </div>

                    <?php if(!empty($upload_error)): ?>
                        <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $upload_error; ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data">

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase">Titre de l'offre</label>
                            <input type="text" name="titre_prestation" class="form-control form-control-lg rounded-3"
                                value="<?php echo htmlspecialchars($presta['titre_prestation']); ?>" required>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Type de service</label>
                                <select name="id_service" class="form-select rounded-3" required>
                                    <?php foreach($liste_services as $s): ?>
                                    <option value="<?php echo $s['id_service']; ?>" <?php echo ($s['id_service'] == $presta['id_service']) ? 'selected' : ''; ?>>
                                        <?php echo $s['nom_categorie'] . ' - ' . $s['nom_service']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Tarif (FCFA)</label>
                                <div class="input-group">
                                    <input type="number" name="prix_prestation" class="form-control" 
                                        value="<?php echo (int)$presta['prix_prestation']; ?>" step="100" required>
                                    <span class="input-group-text bg-light text-muted">FCFA</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase">Description détaillée</label>
                            <textarea name="description_prestation" class="form-control rounded-3" rows="6"
                                required><?php echo htmlspecialchars($presta['description_prestation']); ?></textarea>
                        </div>

                        <div class="mb-4 p-4 border rounded-4 bg-light">
                            <div class="row align-items-center">
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <img src="<?php echo $presta['image_prestation']; ?>" class="img-fluid rounded shadow-sm border" alt="Aperçu">
                                </div>
                                <div class="col-md-9">
                                    <h6 class="fw-bold mb-1">Changer la photo</h6>
                                    <p class="text-muted small mb-2">Laissez vide pour conserver l'image actuelle.</p>
                                    <input type="file" name="image_prestation" class="form-control form-control-sm" accept="image/*">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-3 mt-5">
                            <button type="submit" class="btn btn-primary px-5 py-3 fw-bold flex-grow-1 shadow-sm rounded-pill">
                                Enregistrer les modifications
                            </button>
                            <a href="mon_profil.php" class="btn btn-light px-4 py-3 fw-bold border rounded-pill">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include("includes/pied_de_page.php");?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
