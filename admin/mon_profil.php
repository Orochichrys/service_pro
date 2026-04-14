<?php 
require_once("includes/auth_check.php"); 
require_once("../includes/db.php");

$message = "";
$erreur = "";
$user_id = $_SESSION['user_id'];

// Récupérer les données actuelles de l'admin
$stmt = $conn->prepare("SELECT * FROM Utilisateur WHERE id_utilisateur = ?");
$stmt->execute([$user_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Mettre à jour le profil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profil'])) {
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $email = htmlspecialchars($_POST['email']);
    $tel = htmlspecialchars($_POST['tel']);
    
    // Vérifier si l'email existe pas déjà pour un autre compte
    $check_email = $conn->prepare("SELECT id_utilisateur FROM Utilisateur WHERE email_utilisateur = ? AND id_utilisateur != ?");
    $check_email->execute([$email, $user_id]);
    
    if ($check_email->rowCount() > 0) {
        $erreur = "Cet email est déjà utilisé !";
    } else {
        $update = $conn->prepare("UPDATE Utilisateur SET nom_utilisateur = ?, prenom_utilisateur = ?, email_utilisateur = ?, tel_utilisateur = ? WHERE id_utilisateur = ?");
        if ($update->execute([$nom, $prenom, $email, $tel, $user_id])) {
            $message = "Informations mises à jour avec succès !";
            // Update session
            $_SESSION['user_nom'] = $nom;
            $_SESSION['user_prenom'] = $prenom;
            // Reload admin variables
            $admin['nom_utilisateur'] = $nom;
            $admin['prenom_utilisateur'] = $prenom;
            $admin['email_utilisateur'] = $email;
            $admin['tel_utilisateur'] = $tel;
        } else {
            $erreur = "Erreur lors de la mise à jour.";
        }
    }
}

// Mettre à jour le mot de passe
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    
    if (password_verify($old_pass, $admin['password_utilisateur'])) {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE Utilisateur SET password_utilisateur = ? WHERE id_utilisateur = ?");
        if ($update->execute([$hash, $user_id])) {
            $message = "Mot de passe modifié avec succès !";
        } else {
            $erreur = "Erreur système.";
        }
    } else {
        $erreur = "L'ancien mot de passe est incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil Admin - ServicePro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include("includes/sidebar.php") ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Mon Profil Administrateaker</h2>
        <div class="dropdown">
            <button class="btn btn-white shadow-sm dropdown-toggle d-none d-md-block" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle me-1"></i> Admin
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="mon_profil.php">Profil</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php">Déconnexion</a></li>
            </ul>
        </div>
    </div>

    <?php if($message): ?>
        <div class="alert alert-success mt-3"><i class="bi bi-check-circle me-2"></i><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if($erreur): ?>
        <div class="alert alert-danger mt-3"><i class="bi bi-exclamation-triangle me-2"></i><?php echo $erreur; ?></div>
    <?php endif; ?>

    <div class="row g-4 mb-5">
        <!-- Informations personelles -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="fw-bold mb-0">Modifier mes informations</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <input type="hidden" name="update_profil" value="1">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Nom</label>
                                <input type="text" name="nom" class="form-control" value="<?php echo htmlspecialchars($admin['nom_utilisateur'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Prénom</label>
                                <input type="text" name="prenom" class="form-control" value="<?php echo htmlspecialchars($admin['prenom_utilisateur'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Email Officiel</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin['email_utilisateur'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Téléphone (WhatsApp)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
                                    <input type="text" name="tel" class="form-control" value="<?php echo htmlspecialchars($admin['tel_utilisateur'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-12 mt-4 text-end">
                                <button type="submit" class="btn btn-primary shadow-sm"><i class="bi bi-save me-2"></i> Enregistrer les modifications</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sécurité -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="fw-bold mb-0">Sécurité</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <input type="hidden" name="update_password" value="1">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Ancien mot de passe</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Nouveau mot de passe</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-danger shadow-sm"><i class="bi bi-shield-lock me-2"></i> Modifier le mot de passe</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
