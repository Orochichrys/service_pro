<?php 
session_start();
require_once("../includes/db.php");
require_once("../includes/fonctions.php");

$erreur = "";

// 1. Traitement de la connexion
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $email = securisation($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM Utilisateur WHERE email_utilisateur = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_utilisateur'])) {
            // Création des sessions
            $_SESSION['user_id'] = $user['id_utilisateur'];
            $_SESSION['user_nom'] = $user['nom_utilisateur'];
            $_SESSION['user_prenom'] = $user['prenom_utilisateur'];
            $_SESSION['est_client'] = $user['est_client'];
            $_SESSION['est_prestataire'] = $user['est_prestataire'];
            $_SESSION['est_admin'] = $user['est_admin'];
            $_SESSION['is_validated'] = $user['is_validated'];

            // Redirection
            if ($user['est_admin']) {
                redirection("../admin/index.php");} else {
                redirection("../index.php");}
            exit();
        } else {
            $erreur = "Email ou mot de passe incorrect.";
        }
    } else {
        $erreur = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - ServicePro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    </head>
<body>

    <div class="container-fluid p-0">
        <div class="row g-0 split-container">
            
            <div class="col-lg-6 image-side d-none d-lg-flex">
                <div class="brand-logo mb-5">
                    <a href="index.php" class="text-white text-decoration-none fw-bold fs-3">SERVICE<span class="text-light opacity-75">PRO</span></a>
                </div>
                <h1 class="display-4 fw-bold mb-4">Content de vous revoir !</h1>
                <p class="lead">Connectez-vous pour accéder à vos demandes de services, gérer vos prestations et suivre vos évaluations.</p>
            </div>

            <div class="col-lg-6 form-side">
                <div class="login-form">
                    <div class="mb-5">
                        <h2 class="fw-bold mb-2">Connexion</h2>
                        <p class="text-muted small">Content de vous revoir !</p>
                        
                        <?php if(!empty($erreur)): ?>
                        <div class="alert alert-danger py-2 small"><?php echo $erreur; ?></div>
                        <?php endif; ?>

                        <?php if(isset($_SESSION['message_bravo'])): ?>
                        <div class="alert alert-success py-2 small"><?php echo $_SESSION['message_bravo']; unset($_SESSION['message_bravo']); ?></div>
                        <?php endif; ?>
                    </div>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Adresse Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" class="form-control py-2 border-start-0 bg-light" name="email" placeholder="nom@exemple.com" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between">
                                <label class="form-label small fw-bold">Mot de passe</label>
                                <a href="#" class="text-primary small text-decoration-none fw-bold">Oublié ?</a>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                <input type="password" class="form-control py-2 border-start-0 bg-light" name="password" id="password_login" placeholder="••••••••" required>
                                <button class="btn btn-light border border-start-0" type="button" onclick="togglePassword('password_login', this)">
                                    <i class="bi bi-eye text-muted"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label small text-muted" for="remember">Se souvenir de moi</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm mb-4 d-flex align-items-center justify-content-center rounded-pill">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Se connecter
                        </button>
                        
                        <div class="text-center">
                            <span class="text-muted small">Pas encore de compte ? </span>
                            <a href="inscription.php" class="text-primary fw-bold text-decoration-none small">Créer un compte gratuitement</a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script src="../assets/js/auth.js"></script>
</body>
</html>