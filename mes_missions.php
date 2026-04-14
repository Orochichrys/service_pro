<?php 
session_start();
require_once("includes/db.php");
require_once("includes/function.php");

// 1. Vérification prestataire
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

// 2. Traitement des actions prestataire
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action_mission'])) {
    $id_cmd = (int)$_POST['id_commande'];
    $action = $_POST['action_mission'];
    
    // Sécurité: vérifier que la mission appartient bien au prestataire
    $check = $conn->prepare("SELECT ci.id_commande FROM Cibler ci JOIN Prestation p ON ci.id_prestation = p.id_prestation WHERE ci.id_commande = ? AND p.id_utilisateur = ?");
    $check->execute([$id_cmd, $user_id]);
    
    if ($check->fetch()) {
        if ($action == 'Accepter') {
            $conn->prepare("UPDATE Commande SET statut = 'Confirmée' WHERE id_commande = ?")->execute([$id_cmd]);
            $_SESSION['mission_msg'] = "Mission acceptée avec succès !";
        } elseif ($action == 'Annuler') {
            $conn->prepare("UPDATE Commande SET statut = 'Annulée' WHERE id_commande = ?")->execute([$id_cmd]);
            $_SESSION['mission_msg'] = "La mission a été annulée.";
        } elseif ($action == 'Terminer') {
            $conn->prepare("UPDATE Commande SET statut = 'Terminée' WHERE id_commande = ?")->execute([$id_cmd]);
            $_SESSION['mission_msg'] = "Génial ! Mission terminée.";
        }
        
        // CORRECTION : Redirection pour vider le cache POST (Evite l'alerte de re-soumission au rafraîchissement)
        header("Location: mes_missions.php");
        exit();
    }
}

// 3. Récupération des statistiques (Demandes 'En attente' et Chiffre d'affaires 'Terminée')
$s1 = $conn->prepare("SELECT COUNT(c.id_commande) FROM Commande c JOIN Cibler ci ON c.id_commande = ci.id_commande JOIN Prestation p ON ci.id_prestation = p.id_prestation WHERE p.id_utilisateur = ? AND c.statut = 'En attente'");
$s1->execute([$user_id]);
$nb_missions_attente = $s1->fetchColumn();

$s2 = $conn->prepare("SELECT SUM(c.montant_total) FROM Commande c JOIN Cibler ci ON c.id_commande = ci.id_commande JOIN Prestation p ON ci.id_prestation = p.id_prestation WHERE p.id_utilisateur = ? AND c.statut = 'Terminée'");
$s2->execute([$user_id]);
$ca_total = $s2->fetchColumn() ?? 0;

// 4. Récupération des missions
$sql = "SELECT c.*, p.titre_prestation, u_cli.nom_utilisateur as nom_cli, u_cli.prenom_utilisateur as pre_cli, u_cli.tel_utilisateur as tel_cli, q.nom_quartier, v.nom_ville
        FROM Commande c
        JOIN Cibler ci ON c.id_commande = ci.id_commande
        JOIN Prestation p ON ci.id_prestation = p.id_prestation
        JOIN Utilisateur u_cli ON c.id_utilisateur = u_cli.id_utilisateur
        JOIN Quartier q ON c.id_quartier = q.id_quartier
        JOIN Ville v ON q.id_ville = v.id_ville
        WHERE p.id_utilisateur = ?
        ORDER BY FIELD(c.statut, 'En attente', 'Confirmée', 'Terminée', 'Annulée'), c.date_commande DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$missions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Missions - Dashboard Prestataire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .mission-card { transition: all 0.3s ease; border: 1px solid rgba(0,0,0,0.05); }
        .mission-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important; }
        .status-badge { font-size: 0.7rem; padding: 0.4rem 0.8rem; border-radius: 50px; text-uppercase: bold; }
        .bg-waiting { background-color: #fff3cd; color: #856404; }
        .bg-confirmed { background-color: #cce5ff; color: #004085; }
        .bg-done { background-color: #d4edda; color: #155724; }
        .bg-canceled { background-color: #f8d7da; color: #721c24; }
    </style>
</head>

<body class="bg-light">

    <?php include("includes/navbar.php"); ?>

    <div class="container py-5">
        <div class="row mb-5 align-items-center">
            <div class="col-md-6">
                <h2 class="fw-bold mb-1">Tableau de bord Missions</h2>
                <p class="text-muted small mb-0">Gérez vos demandes de services et suivez votre activité.</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <div class="d-inline-flex gap-3">
                    <div class="text-center px-3 border-end">
                        <h4 class="fw-bold mb-0"><?php echo $nb_missions_attente; ?></h4>
                        <small class="text-muted small text-uppercase fw-bold" style="font-size: 0.6rem;">Missions à venir</small>
                    </div>
                    <div class="text-center px-3">
                        <h4 class="fw-bold mb-0 text-primary"><?php echo number_format($ca_total, 0, ',', ' '); ?> F</h4>
                        <small class="text-muted small text-uppercase fw-bold" style="font-size: 0.6rem;">Chiffre d'affaires</small>
                    </div>
                </div>
            </div>
        </div>

        <?php if(isset($_SESSION['mission_msg'])): ?>
            <div class="alert alert-primary rounded-4 shadow-sm border-0 mb-4 py-3">
                <i class="bi bi-info-circle-fill me-2 fs-5"></i> 
                <?php echo $_SESSION['mission_msg']; unset($_SESSION['mission_msg']); ?>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <?php foreach($missions as $m): ?>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm p-4 rounded-4 mission-card h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <?php 
                        $status_class = "bg-waiting";
                        if($m['statut'] == 'Confirmée') $status_class = "bg-confirmed";
                        if($m['statut'] == 'Terminée') $status_class = "bg-done";
                        if($m['statut'] == 'Annulée') $status_class = "bg-canceled";
                        ?>
                        <span class="status-badge <?php echo $status_class; ?> small fw-bold">
                            <?php echo $m['statut']; ?>
                        </span>
                        <span class="text-muted small"><?php echo date('d M Y', strtotime($m['date_commande'])); ?></span>
                    </div>

                    <h5 class="fw-bold mb-1"><?php echo $m['titre_prestation']; ?></h5>
                    <div class="h4 fw-bold text-primary mb-3"><?php echo number_format($m['montant_total'], 0, ',', ' '); ?> F</div>
                    
                    <div class="bg-light p-3 rounded-4 mb-4">
                        <div class="row g-3 mb-2">
                            <div class="col-6">
                                <small class="text-muted d-block fw-bold text-uppercase mb-1" style="font-size: 0.6rem;">Lieu d'intervention</small>
                                <span class="small fw-bold"><i class="bi bi-geo-alt me-1"></i> <?php echo $m['nom_quartier']; ?></span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block fw-bold text-uppercase mb-1" style="font-size: 0.6rem;">Client</small>
                                <span class="small fw-bold"><?php echo $m['pre_cli'].' '.$m['nom_cli']; ?></span>
                            </div>
                        </div>
                        <div class="pt-2 border-top">
                            <small class="text-muted d-block fw-bold text-uppercase mb-1" style="font-size: 0.6rem;">Contact direct</small>
                            <a href="tel:<?php echo $m['tel_cli']; ?>" class="text-decoration-none text-success fw-bold">
                                <i class="bi bi-telephone-fill me-1"></i> <?php echo $m['tel_cli'] ?? 'Non renseigné'; ?>
                            </a>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <form action="" method="POST" class="d-flex gap-2">
                            <input type="hidden" name="id_commande" value="<?php echo $m['id_commande']; ?>">
                            
                            <?php if($m['statut'] == 'En attente'): ?>
                                <button type="submit" name="action_mission" value="Accepter" class="btn btn-primary rounded-pill flex-grow-1 fw-bold shadow-sm py-2">
                                    <i class="bi bi-check-lg me-1"></i> Accepter
                                </button>
                                <button type="submit" name="action_mission" value="Annuler" class="btn btn-outline-danger rounded-pill flex-grow-1 fw-bold py-2" onclick="return confirm('Refuser cette mission ?')">
                                    <i class="bi bi-x-lg me-1"></i> Refuser
                                </button>
                            
                            <?php elseif($m['statut'] == 'Confirmée'): ?>
                                <button type="submit" name="action_mission" value="Terminer" class="btn btn-success rounded-pill w-100 fw-bold shadow-sm py-2">
                                    <i class="bi bi-flag-fill me-1"></i> Marquer comme Terminé
                                </button>
                                <button type="submit" name="action_mission" value="Annuler" class="btn btn-link text-danger text-decoration-none small mt-2 w-100 fw-bold" onclick="return confirm('Annuler la mission confirmée ?')">
                                    <i class="bi bi-trash me-1"></i> Annuler la mission
                                </button>

                            <?php elseif($m['statut'] == 'Terminée'): ?>
                                <div class="alert alert-success py-2 w-100 text-center mb-0 rounded-pill small fw-bold">
                                    <i class="bi bi-check-all me-1"></i> Prestation réalisée
                                </div>
                            
                            <?php else: ?>
                                <div class="alert alert-danger py-2 w-100 text-center mb-0 rounded-pill small fw-bold">
                                    Mission Annulée
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if(count($missions) == 0): ?>
                <div class="col-12 text-center py-5 bg-white rounded-4 shadow-sm border mt-4">
                    <i class="bi bi-inboxes mb-3 fs-1 text-muted opacity-25 d-block"></i>
                    <h5 class="text-muted fw-bold">Aucune mission reçue pour le moment.</h5>
                    <p class="text-muted small">Dès qu'un client passera commande, elle s'affichera ici.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include("includes/footer.php");?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
