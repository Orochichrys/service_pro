<?php 
session_start();
require_once("includes/db.php");
require_once("includes/function.php");

// 1. Vérification si prestataire
if (!isset($_SESSION['user_id']) || !$_SESSION['est_prestataire']) {
    header("Location: auth/login.php");
    exit();
}
if (isset($_SESSION['is_validated']) && $_SESSION['is_validated'] == 0) {
    $_SESSION['profile_success'] = "Veuillez patienter ! Votre compte prestataire est en attente de validation par l'administration.";
    header("Location: mon_profil.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Récupération des services pour le sélecteur
$services_sql = "SELECT s.*, c.nom_categorie FROM Service s JOIN Categorie c ON s.id_categorie = c.id_categorie ORDER BY c.nom_categorie, s.nom_service";
$liste_services = $conn->query($services_sql)->fetchAll(PDO::FETCH_ASSOC);

// 3. Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titre = securisation($_POST['titre_prestation']);
    $id_service = (int)$_POST['id_service'];
    $prix = (float)$_POST['prix_prestation'];
    $description = securisation($_POST['description_prestation']);
    
    // IMAGE UPLOAD LOGIC
    $image_path = "assets/img/default.jpg"; // Image par défaut
    
    if (isset($_FILES['image_prestation']) && $_FILES['image_prestation']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image_prestation']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_name = "service_" . time() . "_" . uniqid() . "." . $ext;
            $upload_dir = "assets/img/uploads/";
            
            if (move_uploaded_file($_FILES['image_prestation']['tmp_name'], $upload_dir . $new_name)) {
                $image_path = $upload_dir . $new_name;
            }
        }
    }

    if (!empty($titre) && !empty($id_service) && $prix > 0) {
        $sql = "INSERT INTO Prestation (titre_prestation, description_prestation, prix_prestation, image_prestation, id_service, id_utilisateur) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $conn->prepare($sql)->execute([$titre, $description, $prix, $image_path, $id_service, $user_id]);
        
        $_SESSION['commande_bravo'] = "Félicitations ! Votre nouveau service est en ligne.";
        header("Location: mon_profil.php#services");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Service - ServicePro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .border-dashed {
            border: 2px dashed #dee2e6;
            transition: border-color 0.3s;
        }

        .border-dashed:hover {
            border-color: var(--primary-color);
        }

        .form-control-lg {
            font-size: 1.1rem;
            font-weight: 500;
        }

        .bg-primary-soft {
            background-color: #f3f0ff;
        }
    </style>
</head>

<body class="bg-light">

    <?php include("includes/navbar.php"); ?>

    <div class="container py-5">
        <div class="row">
            <div class="col-12">

                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="mon_profil.php" class="text-decoration-none">Mon Profil</a>
                        </li>
                        <li class="breadcrumb-item active">Nouveau Service</li>
                    </ol>
                </nav>

                <div class="card border-0 shadow-sm p-4 p-md-5 rounded-4">
                    <div class="mb-4">
                        <h2 class="fw-bold h3 text-primary">Publier une prestation</h2>
                        <p class="text-muted small">Partagez votre expertise avec la communauté ServicePro.</p>
                    </div>

                    <form action="" method="POST" enctype="multipart/form-data">

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase">Titre de l'offre</label>
                            <input type="text" name="titre_prestation" class="form-control form-control-lg rounded-3"
                                placeholder="Ex: Installation de climatisation, Coiffure tresses..." required>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Type de service</label>
                                <select name="id_service" class="form-select rounded-3" required>
                                    <option selected disabled>Choisir un service...</option>
                                    <?php foreach($liste_services as $s): ?>
                                    <option value="<?php echo $s['id_service']; ?>"><?php echo $s['nom_categorie'] . ' - ' . $s['nom_service']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Tarif (FCFA)</label>
                                <div class="input-group">
                                    <input type="number" name="prix_prestation" class="form-control" placeholder="0"
                                        step="100" required>
                                    <span class="input-group-text bg-light text-muted">FCFA</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase">Description détaillée</label>
                            <textarea name="description_prestation" class="form-control rounded-3" rows="6"
                                placeholder="Expliquez ce que comprend votre prestation, vos horaires..."
                                required></textarea>
                        </div>

                        <div class="mb-4 p-4 border-dashed rounded-4 text-center bg-light">
                            <i class="bi bi-cloud-arrow-up fs-1 text-primary opacity-50 mb-2 d-block"></i>
                            <h6 class="fw-bold mb-1">Photo d'illustration</h6>
                            <p class="text-muted small mb-3">Formats autorisés : JPG, PNG, WEBP (Max 2Mo)</p>
                            <input type="file" name="image_prestation" class="form-control form-control-sm"
                                accept="image/*">
                        </div>

                        <div class="d-flex gap-3 mt-5">
                            <button type="submit" class="btn btn-primary px-5 py-3 fw-bold flex-grow-1 shadow-sm rounded-pill">
                                Publier mon service
                            </button>
                            <a href="mon_profil.php" class="btn btn-light px-4 py-3 fw-bold border rounded-pill">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include("includes/footer.php");?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>