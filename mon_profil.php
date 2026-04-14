<?php 
session_start();
require_once("includes/db.php");
require_once("includes/function.php");

// 1. Vérification connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ---------------------------------------------------------
// 2. TRAITEMENT DES ACTIONS (AVANT TOUTE LECTURE)
// ---------------------------------------------------------

// Action de changement de rôle
if(isset($_GET['devenir_prestataire'])){
    $conn->prepare("UPDATE Utilisateur SET est_prestataire = 1, is_validated = 0 WHERE id_utilisateur = ?")->execute([$user_id]);
    $_SESSION['est_prestataire'] = 1;
    $_SESSION['is_validated'] = 0;
    header("Location: mon_profil.php"); exit();
}

if(isset($_GET['devenir_client'])){
    $conn->prepare("UPDATE Utilisateur SET est_client = 1 WHERE id_utilisateur = ?")->execute([$user_id]);
    $_SESSION['est_client'] = 1;
    header("Location: mon_profil.php"); exit();
}

// Logic de mise à jour du profil complet
if(isset($_POST['update_profile'])){
    $nom = securisation($_POST['nom_utilisateur']);
    $prenom = securisation($_POST['prenom_utilisateur']);
    $email = securisation($_POST['email_utilisateur']);
    $tel = securisation($_POST['tel_utilisateur']);
    $id_quartier = (int)$_POST['id_quartier'];
    $new_pass = $_POST['new_password'];
    
    // Mise à jour des infos de base
    $sql_up = "UPDATE Utilisateur SET nom_utilisateur = ?, prenom_utilisateur = ?, email_utilisateur = ?, tel_utilisateur = ?, id_quartier = ? WHERE id_utilisateur = ?";
    $conn->prepare($sql_up)->execute([$nom, $prenom, $email, $tel, $id_quartier, $user_id]);
    
    // Mise à jour session pour affichage immédiat partout
    $_SESSION['user_nom'] = $nom;
    $_SESSION['user_prenom'] = $prenom;
    $_SESSION['user_tel'] = $tel;
    $_SESSION['user_email'] = $email;

    if(!empty($new_pass)){
        $pass_h = password_hash($new_pass, PASSWORD_DEFAULT);
        $conn->prepare("UPDATE Utilisateur SET password_utilisateur = ? WHERE id_utilisateur = ?")->execute([$pass_h, $user_id]);
    }

    $_SESSION['profile_success'] = "Vos informations ont été mises à jour avec succès !";
    header("Location: mon_profil.php"); exit();
}

// ---------------------------------------------------------
// 3. RÉCUPÉRATION DES INFORMATIONS À JOUR
// ---------------------------------------------------------
$u_stmt = $conn->prepare("SELECT u.*, q.nom_quartier, v.nom_ville 
                         FROM Utilisateur u 
                         LEFT JOIN Quartier q ON u.id_quartier = q.id_quartier 
                         LEFT JOIN Ville v ON q.id_ville = v.id_ville 
                         WHERE u.id_utilisateur = ?");
$u_stmt->execute([$user_id]);
$u = $u_stmt->fetch(PDO::FETCH_ASSOC);

// Synchronisation forcée des données de session
$_SESSION['user_nom'] = $u['nom_utilisateur'];
$_SESSION['user_prenom'] = $u['prenom_utilisateur'];

// Récupération des quartiers pour le formulaire de modification
$quartiers = $conn->query("SELECT q.*, v.nom_ville FROM Quartier q JOIN Ville v ON q.id_ville = v.id_ville ORDER BY v.nom_ville, q.nom_quartier")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - ServicePro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

    <?php include("includes/navbar.php"); ?>

    <div class="container py-5">
        <div class="row">
            <!-- Sidebar Profil -->
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm p-4 text-center rounded-4">
                    <div class="avatar-xl rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm mx-auto mb-3" style="width:100px; height:100px; font-size:2.5rem; font-weight: bold;">
                        <?php echo strtoupper(substr($u['nom_utilisateur'], 0, 1) . substr($u['prenom_utilisateur'], 0, 1)); ?>
                    </div>
                    <h4 class="fw-bold mb-1"><?php echo $u['prenom_utilisateur'] . ' ' . $u['nom_utilisateur']; ?></h4>
                    <p class="text-muted small"><i class="bi bi-geo-alt me-1 text-primary"></i> <?php echo $u['nom_ville'] ?? 'Côté d\'Ivoire'; ?>, <?php echo $u['nom_quartier'] ?? ''; ?></p>
                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <?php if($u['est_client']): ?><span class="badge bg-primary-subtle text-primary py-2 px-3 fw-bold rounded-pill">Client</span><?php endif; ?>
                        <?php if($u['est_prestataire']): ?><span class="badge bg-success-subtle text-success py-2 px-3 fw-bold rounded-pill">Prestataire</span><?php endif; ?>
                    </div>
                    
                    <hr class="my-4 opacity-50">
                    
                    <div class="text-start">
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1 fw-bold text-uppercase" style="font-size: 0.65rem;">Email officiel</small>
                            <p class="small fw-bold mb-0 text-dark"><i class="bi bi-envelope me-1"></i> <?php echo $u['email_utilisateur']; ?></p>
                        </div>
                        <div class="mb-3 border-top pt-3">
                            <small class="text-muted d-block mb-1 fw-bold text-uppercase" style="font-size: 0.65rem;">WhatsApp / Mobile</small>
                            <p class="small fw-bold mb-0 text-success"><i class="bi bi-whatsapp me-1"></i> <?php echo $u['tel_utilisateur'] ?? 'Non renseigné'; ?></p>
                        </div>
                    </div>

                    <div class="mt-2 pt-3 border-top">
                        <?php if(!$u['est_prestataire']): ?>
                            <a href="?devenir_prestataire=1" class="btn btn-primary w-100 rounded-pill mb-2 fw-bold small shadow-sm py-2 text-white">
                                <i class="bi bi-shop me-2"></i> Devenir Prestataire
                            </a>
                        <?php endif; ?>
                        <?php if(!$u['est_client']): ?>
                            <a href="?devenir_client=1" class="btn btn-primary w-100 rounded-pill fw-bold small shadow-sm py-2">
                                <i class="bi bi-person-badge me-2"></i> Devenir Client
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Contenu Principal -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom p-4">
                        <h5 class="fw-bold mb-0"><i class="bi bi-person-gear me-2 text-primary"></i>Paramètres du profil</h5>
                        <p class="text-muted small mb-0 mt-1">Mettez à jour vos informations personnelles et votre sécurité.</p>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php if(isset($_SESSION['profile_success'])): ?>
                            <div class="alert alert-success rounded-4 small py-3 border-0 mb-4 shadow-sm animate__animated animate__fadeIn">
                                <i class="bi bi-check-circle-fill me-2 fs-6"></i> <?php echo $_SESSION['profile_success']; unset($_SESSION['profile_success']); ?>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST">
                            <div class="row g-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Nom</label>
                                    <input type="text" name="nom_utilisateur" class="form-control rounded-3 py-2 bg-light" value="<?php echo $u['nom_utilisateur']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Prénom</label>
                                    <input type="text" name="prenom_utilisateur" class="form-control rounded-3 py-2 bg-light" value="<?php echo $u['prenom_utilisateur']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Adresse Email professionnelle</label>
                                    <input type="email" name="email_utilisateur" class="form-control rounded-3 py-2 bg-light" value="<?php echo $u['email_utilisateur']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">WhatsApp / Téléphone</label>
                                    <input type="tel" name="tel_utilisateur" class="form-control rounded-3 py-2 bg-light" value="<?php echo $u['tel_utilisateur']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Quartier (Localisation)</label>
                                    <select name="id_quartier" class="form-select rounded-3 py-2 bg-light" required>
                                        <?php 
                                        $current_ville = ""; 
                                        foreach($quartiers as $q): 
                                            if($current_ville != $q['nom_ville']){
                                                if($current_ville != "") echo '</optgroup>';
                                                echo '<optgroup label="'.$q['nom_ville'].'">';
                                                $current_ville = $q['nom_ville'];
                                            }
                                        ?>
                                        <option value="<?php echo $q['id_quartier']; ?>" <?php echo ($q['id_quartier'] == $u['id_quartier']) ? 'selected' : ''; ?>>
                                            <?php echo $q['nom_quartier']; ?>
                                        </option>
                                        <?php endforeach; if($current_ville != "") echo '</optgroup>'; ?>
                                    </select>
                                </div>
                                <div class="col-12 mb-4">
                                    <label class="form-label small fw-bold">Nouveau mot de passe (Laisser vide si inchangé)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-lock text-muted"></i></span>
                                        <input type="password" name="new_password" class="form-control rounded-end-3 py-2 border-start-0 bg-light" placeholder="••••••••">
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4 opacity-50">
                            
                            <div class="text-end">
                                <button type="submit" name="update_profile" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm">
                                    <i class="bi bi-save me-2"></i>Enregistrer les modifications
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include("includes/footer.php");?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>