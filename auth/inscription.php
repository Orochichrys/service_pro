<?php 
session_start();
require_once("../includes/db.php");
require_once("../includes/fonctions.php");

// 1. Récupération des quartiers pour le formulaire
$quartiers = $conn->query("SELECT q.*, v.nom_ville FROM Quartier q JOIN Ville v ON q.id_ville = v.id_ville ORDER BY v.nom_ville, q.nom_quartier")->fetchAll(PDO::FETCH_ASSOC);

$erreur = "";

// 2. Traitement du formulaire
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $nom = securisation($_POST['nom']);
    $prenom = securisation($_POST['prenom']);
    $email = securisation($_POST['email']);
    $tel = securisation($_POST['tel']);
    $password = $_POST['password'];
    $role = $_POST['role']; // 'client' ou 'prestataire'
    $id_quartier = isset($_POST['id_quartier']) ? (int)$_POST['id_quartier'] : null;

    if (!empty($nom) && !empty($prenom) && !empty($email) && !empty($password) && !empty($id_quartier) && !empty($tel)) {
        
        // Vérification si l'email existe déjà
        $stmt = $conn->prepare("SELECT id_utilisateur FROM Utilisateur WHERE email_utilisateur = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $erreur = "Désolé, cet email est déjà utilisé par un autre compte.";
        } else {
            // Hachage du mot de passe
            $pass_hash = password_hash($password, PASSWORD_DEFAULT);
            $est_client = ($role == 'client') ? 1 : 0;
            $est_prestataire = ($role == 'prestataire') ? 1 : 0;
            // Nouveaux prestataires à valider=0, clients validés auto=1
            $is_validated = ($role == 'client') ? 1 : 0;

            $sql = "INSERT INTO Utilisateur (nom_utilisateur, prenom_utilisateur, email_utilisateur, tel_utilisateur, password_utilisateur, est_client, est_prestataire, is_validated, id_quartier) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $conn->prepare($sql)->execute([$nom, $prenom, $email, $tel, $pass_hash, $est_client, $est_prestataire, $is_validated, $id_quartier]);

            $_SESSION['message_bravo'] = "Félicitations $prenom ! Votre compte est créé. Connectez-vous.";
            redirection("login.php");
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
    <title>Inscription - ServicePro</title>
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
                <h1 class="display-4 fw-bold mb-4">Rejoignez le réseau n°1 de services en Côte d'Ivoire.</h1>
                <p class="lead text-white-50">Connectez-vous avec des experts locaux ou proposez votre savoir-faire dès aujourd'hui.</p>
            </div>

            <div class="col-lg-6 form-side">
                <div class="register-form">
                    <div class="mb-4 text-center text-lg-start">
                        <h2 class="fw-bold">Créer un compte</h2>
                        <p class="text-muted small">Inscrivez-vous pour commencer l'aventure.</p>
                        <?php if(!empty($erreur)): ?><div class="alert alert-danger py-2 small"><?php echo $erreur; ?></div><?php endif; ?>
                    </div>

                    <form action="" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Nom</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person text-muted"></i></span>
                                    <input type="text" class="form-control py-2 rounded-end-3" name="nom" placeholder="Nom" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Prénom</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-check text-muted"></i></span>
                                    <input type="text" class="form-control py-2 rounded-end-3" name="prenom" placeholder="Prénom" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope text-muted"></i></span>
                                    <input type="email" class="form-control py-2 rounded-end-3" name="email" placeholder="example@email.com" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">N° Téléphone</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone text-muted"></i></span>
                                    <input type="tel" class="form-control py-2 rounded-end-3" name="tel" placeholder="Ex: 0102030405" required>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold d-block">Vous voulez :</label>
                                <div class="btn-group w-100 shadow-sm" role="group">
                                    <input type="radio" class="btn-check" name="role" id="client" value="client" checked>
                                    <label class="btn btn-outline-primary py-2" for="client">Commander</label>
                                    
                                    <input type="radio" class="btn-check" name="role" id="prestataire" value="prestataire">
                                    <label class="btn btn-outline-primary py-2" for="prestataire">Proposer mes services</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold">Votre Lieu de résidence (Quartier)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-geo-alt text-muted"></i></span>
                                    <select class="form-select py-3 rounded-end-3" name="id_quartier" required>
                                        <option value="" selected disabled>Où habitez-vous ?</option>
                                        <?php 
                                        $current_ville = ""; 
                                        foreach($quartiers as $q): 
                                            if($current_ville != $q['nom_ville']){
                                                if($current_ville != "") echo '</optgroup>';
                                                echo '<optgroup label="'.$q['nom_ville'].'">';
                                                $current_ville = $q['nom_ville'];
                                            }
                                        ?>
                                        <option value="<?php echo $q['id_quartier']; ?>"><?php echo $q['nom_quartier']; ?></option>
                                        <?php endforeach; if($current_ville != "") echo '</optgroup>'; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold">Mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock text-muted"></i></span>
                                    <input type="password" class="form-control py-2 border-end-0" name="password" id="password" placeholder="Minimum 6 caractères" required>
                                    <span class="input-group-text bg-white border-start-0 rounded-end-3" style="cursor: pointer;" onclick="togglePassword('password', this)">
                                        <i class="bi bi-eye text-muted"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm rounded-pill mt-3 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-person-plus-fill me-2"></i> Créer mon compte
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="text-center mt-5">
                        <span class="text-muted small">Vous avez déjà un compte ? </span>
                        <a href="connexion.php" class="text-primary fw-bold text-decoration-none small">Se connecter</a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="../assets/js/auth.js"></script>
</body>
</html>