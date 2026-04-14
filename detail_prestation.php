<?php 
session_start();
require_once("includes/db.php");
require_once("includes/fonctions.php");

$id_presta = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id_presta == 0) { redirection("catalogue.php"); }

// 1. Récupération des détails complets
$sql = "SELECT p.*, u.nom_utilisateur, u.prenom_utilisateur, u.email_utilisateur, u.tel_utilisateur, u.date_inscription as date_membre, q.nom_quartier, 
               v.nom_ville, s.nom_service, c.nom_categorie 
        FROM Prestation p 
        JOIN Utilisateur u ON p.id_utilisateur = u.id_utilisateur 
        JOIN Service s ON p.id_service = s.id_service
        JOIN Categorie c ON s.id_categorie = c.id_categorie
        LEFT JOIN Quartier q ON u.id_quartier = q.id_quartier
        LEFT JOIN Ville v ON q.id_ville = v.id_ville
        WHERE p.id_prestation = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id_presta]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$p) { redirection("catalogue.php"); }

// Vérification de visibilité : Seul le proprio, l'admin ou tout le monde si c'est validé
$is_owner = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $p['id_utilisateur']);
$is_admin = (isset($_SESSION['est_admin']) && $_SESSION['est_admin'] == 1);
if ($p['statut_prestation'] !== 'validee' && !$is_owner && !$is_admin) {
    redirection("catalogue.php");
}

// 2. Liste des quartiers pour la modal
$liste_quartiers = $conn->query("SELECT q.*, v.nom_ville FROM Quartier q JOIN Ville v ON q.id_ville = v.id_ville ORDER BY v.nom_ville, q.nom_quartier")->fetchAll(PDO::FETCH_ASSOC);

// 3. Récupération des avis réels
$reviews_sql = "SELECT ci.*, u.nom_utilisateur, u.prenom_utilisateur, cmd.date_commande
                FROM Cibler ci 
                JOIN Commande cmd ON ci.id_commande = cmd.id_commande 
                JOIN Utilisateur u ON cmd.id_utilisateur = u.id_utilisateur
                WHERE ci.id_prestation = ? AND ci.note_evaluation IS NOT NULL 
                ORDER BY cmd.date_commande DESC";
$r_stmt = $conn->prepare($reviews_sql);
$r_stmt->execute([$id_presta]);
$les_avis = $r_stmt->fetchAll(PDO::FETCH_ASSOC);
$nb_avis = count($les_avis);

// 4. Traitement Final de la Commande (depuis la Modal)
$erreur_commande = "";
if (isset($_POST['confirmer_commande']) && isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];
    $id_quartier_mission = (int)$_POST['id_quartier_mission'];
    $est_client = (isset($_SESSION['est_client']) && $_SESSION['est_client'] == 1);

    if (!$est_client) {
        $erreur_commande = "Accès refusé : Seul les détenteurs d'un compte 'Client' peuvent passer commande.";
    } else if ($user_id == $p['id_utilisateur']) {
        $erreur_commande = "Erreur : Vous ne pouvez pas commander votre propre service !";
    } else {
        $montant = $p['prix_prestation'];
        
        if ($id_quartier_mission > 0) {
            $conn->prepare("INSERT INTO Commande (montant_total, id_utilisateur, id_quartier, statut) VALUES (?, ?, ?, 'En attente')")
                 ->execute([$montant, $user_id, $id_quartier_mission]);
            $id_cmd = $conn->lastInsertId();

            $conn->prepare("INSERT INTO Cibler (id_commande, id_prestation, prix_unitaire) VALUES (?, ?, ?)")
                 ->execute([$id_cmd, $id_presta, $montant]);

            $_SESSION['commande_bravo'] = "Demande envoyée ! Le prestataire a bien reçu votre commande.";
            redirection("mon_profil.php");
        } else {
            $erreur_commande = "Veuillez sélectionner un lieu pour l'intervention.";
        }
    }
}

// 5. Récupération du quartier par défaut du client
$user_id = $_SESSION['user_id'] ?? 0;
$user_quartier_id = 0;
if($user_id > 0){
    $stmt_u = $conn->prepare("SELECT id_quartier FROM Utilisateur WHERE id_utilisateur = ?");
    $stmt_u->execute([$user_id]);
    $user_quartier_id = (int)$stmt_u->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $p['titre_prestation']; ?> - ServicePro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
</head>

<body class="bg-light">

    <?php include("includes/barre_navigation.php"); ?>

    <div class="container py-5">
        
        <?php if($erreur_commande): ?>
            <div class="alert alert-danger rounded-4 shadow-sm border-0 mb-4 py-3">
                <i class="bi bi-shield-lock-fill me-2 fs-5"></i> <?php echo $erreur_commande; ?>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <?php if($is_owner && $p['statut_prestation'] == 'en_attente'): ?>
                <div class="col-12">
                    <div class="alert alert-warning rounded-4 border-0 shadow-sm mb-0">
                        <i class="bi bi-clock-history me-2"></i> <strong>Ce service est en cours de vérification.</strong> Il n'est pas encore visible pour les clients.
                    </div>
                </div>
            <?php elseif($is_owner && $p['statut_prestation'] == 'refusee'): ?>
                <div class="col-12">
                    <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Ce service a été refusé par l'administration.</strong> Veuillez le modifier pour qu'il soit réexaminé.
                    </div>
                </div>
            <?php endif; ?>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm overflow-hidden mb-4 rounded-4">
                    <img src="<?php echo $p['image_prestation'] ?? 'assets/img/default.jpg'; ?>"
                        class="img-fluid w-100" alt="Service" style="max-height: 450px; object-fit: cover;">
                </div>

                <div class="card border-0 shadow-sm p-4 mb-4 rounded-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-primary-subtle text-primary mb-2 text-uppercase fw-bold"><?php echo $p['nom_categorie']; ?></span>
                            <h1 class="fw-bold h2 mb-2"><?php echo $p['titre_prestation']; ?></h1>
                            <p class="text-muted small mb-0">
                                <i class="bi bi-geo-alt me-1 text-primary"></i> <?php echo $p['nom_ville']; ?>, <?php echo $p['nom_quartier']; ?>
                            </p>
                        </div>
                        <div class="text-end">
                            <h2 class="text-primary fw-bold mb-0"><?php echo number_format($p['prix_prestation'], 0, ',', ' '); ?> F</h2>
                            <small class="text-muted opacity-75">Tarif fixe</small>
                        </div>
                    </div>

                    <hr class="my-4 opacity-50">

                    <h5 class="fw-bold mb-3">À propos de ce service</h5>
                    <p class="text-secondary lh-lg mb-4">
                        <?php echo nl2br($p['description_prestation']); ?>
                    </p>

                    <div class="row g-3">
                        <div class="col-6 col-md-4">
                            <div class="p-3 border rounded-4 bg-light bg-opacity-50 text-center h-100">
                                <i class="bi bi-truck fs-3 text-primary mb-2 d-block"></i>
                                <span class="small fw-bold">Déplacement inclus</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 border rounded-4 bg-light bg-opacity-50 text-center h-100">
                                <i class="bi bi-shield-check fs-3 text-primary mb-2 d-block"></i>
                                <span class="small fw-bold">Service Garanti</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 border rounded-4 bg-light bg-opacity-50 text-center h-100">
                                <i class="bi bi-clock-history fs-3 text-primary mb-2 d-block"></i>
                                <span class="small fw-bold">Réponse rapide</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm p-4 rounded-4">
                    <h5 class="fw-bold mb-4">Avis des clients (<?php echo $nb_avis; ?>)</h5>
                    <?php if($nb_avis > 0): ?>
                        <?php foreach($les_avis as $avis): ?>
                        <div class="d-flex mb-4 pb-4 border-bottom last-border-0">
                            <div class="flex-shrink-0">
                                <div class="review-avatar shadow-sm">
                                    <?php echo strtoupper(substr($avis['nom_utilisateur'], 0, 1) . substr($avis['prenom_utilisateur'], 0, 1)); ?>
                                </div>
                            </div>
                            <div class="ms-3">
                                <div class="d-flex align-items-center mb-1">
                                    <h6 class="fw-bold mb-0 me-2"><?php echo $avis['prenom_utilisateur'] . ' ' . $avis['nom_utilisateur']; ?></h6>
                                    <div class="text-warning small">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <i class="bi bi-star<?php echo ($i <= $avis['note_evaluation']) ? '-fill' : ''; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <small class="text-muted d-block mb-2">Le <?php echo date('d M Y', strtotime($avis['date_commande'])); ?></small>
                                <p class="text-secondary small mb-0"><?php echo htmlspecialchars($avis['commentaire_evaluation']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted small opacity-75">
                            <i class="bi bi-chat-left-dots fs-3 d-block mb-2"></i>
                            Pas encore d'avis sur ce service.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm p-4 mb-4 sticky-top sticky-custom rounded-4">
                    <div class="text-center mb-4">
                        <div class="avatar-lg mx-auto bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">
                            <?php echo strtoupper(substr($p['nom_utilisateur'], 0, 1) . substr($p['prenom_utilisateur'], 0, 1)); ?>
                        </div>
                        <h5 class="fw-bold mb-1"><?php echo $p['prenom_utilisateur'] . ' ' . $p['nom_utilisateur']; ?></h5>
                        <p class="text-muted small mb-3"><i class="bi bi-geo-alt"></i> <?php echo $p['nom_ville']; ?></p>
                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill small fw-bold">
                            <i class="bi bi-patch-check-fill me-1"></i> Prestataire Vérifié
                        </span>
                    </div>

                    <div class="bg-light rounded-4 p-3 mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small fw-bold">Membre depuis</span>
                            <span class="small fw-bold"><?php echo date('M Y', strtotime($p['date_membre'])); ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small fw-bold">Spécialité</span>
                            <span class="small fw-bold text-primary"><?php echo $p['nom_service']; ?></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php if($_SESSION['user_id'] == $p['id_utilisateur']): ?>
                                <div class="alert alert-primary py-2 text-center rounded-3 mb-3 small fw-bold">
                                    C'est votre propre service.
                                </div>
                                <a href="modifier_prestation.php?id=<?php echo $id_presta; ?>" class="btn btn-outline-primary w-100 py-3 fw-bold mb-3 rounded-pill">
                                    <i class="bi bi-pencil me-2"></i> Modifier mon offre
                                </a>
                            <?php elseif(!(isset($_SESSION['est_client']) && $_SESSION['est_client'] == 1)): ?>
                                <div class="alert alert-warning py-2 text-center rounded-3 mb-3 small">
                                    Vous devez activer votre profil <strong>Client</strong> pour commander.
                                </div>
                                <a href="mon_profil.php" class="btn btn-primary w-100 py-3 fw-bold mb-3 rounded-pill shadow-sm">
                                    <i class="bi bi-person-check me-2"></i> Devenir Client
                                </a>
                            <?php else: ?>
                                <button type="button" class="btn btn-primary w-100 py-3 fw-bold mb-3 shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#modalCommande">
                                    Commander ce service
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="auth/connexion.php" class="btn btn-primary w-100 py-3 fw-bold mb-3 shadow-sm rounded-pill">
                                Se connecter pour commander
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <?php 
                    $contact_link = "mailto:".$p['email_utilisateur'];
                    if(!empty($p['tel_utilisateur'])) $contact_link = "tel:".$p['tel_utilisateur'];
                    ?>
                    <a href="<?php echo $contact_link; ?>" class="btn btn-white border w-100 py-3 rounded-pill fw-bold text-dark mb-3">
                        <i class="bi bi-chat-dots text-primary me-2"></i> Contacter
                    </a>

                    <p class="text-center text-muted mb-0" style="font-size: 0.72rem;">
                        <i class="bi bi-shield-lock text-success me-1"></i> Transaction sécurisée via ServicePro
                    </p>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL DE COMMANDE -->
    <div class="modal fade" id="modalCommande" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold mb-0">Confirmation de commande</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body py-4">
                        <div class="mb-4 text-center">
                            <div class="display-6 fw-bold text-primary"><?php echo number_format($p['prix_prestation'], 0, ',', ' '); ?> F</div>
                            <p class="text-muted small">Montant total à payer au prestataire</p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Lieu de la prestation</label>
                            <select name="id_quartier_mission" class="form-select rounded-3 py-2" required>
                                <option value="" disabled>Choisir un quartier...</option>
                                <?php 
                                $current_ville = "";
                                foreach($liste_quartiers as $q): 
                                    if($current_ville != $q['nom_ville']){
                                        if($current_ville != "") echo "</optgroup>";
                                        echo "<optgroup label=\"".$q['nom_ville']."\">";
                                        $current_ville = $q['nom_ville'];
                                    }
                                ?>
                                    <option value="<?php echo $q['id_quartier']; ?>" <?php echo ($q['id_quartier'] == $user_quartier_id) ? 'selected' : ''; ?>>
                                        <?php echo $q['nom_quartier']; ?>
                                    </option>
                                <?php endforeach; ?>
                                </optgroup>
                            </select>
                            <div class="form-text small mt-2">
                                <i class="bi bi-info-circle"></i> Par défaut, nous utilisons votre quartier enregistré. Vous pouvez le modifier pour cette commande.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="confirmer_commande" class="btn btn-primary rounded-pill px-4 fw-bold">Confirmer la commande</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include("includes/pied_de_page.php");?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>