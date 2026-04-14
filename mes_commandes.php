<?php 
session_start();
require_once("includes/db.php");
require_once("includes/function.php");

// 1. Vérification connexion et rôle
if (!isset($_SESSION['user_id']) || !$_SESSION['est_client']) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- TRAITEMENT DES ACTIONS ---

// Action d'annulation de commande par le client
if(isset($_GET['annuler_commande'])){
    $id_cmd = (int)$_GET['annuler_commande'];
    $stmt_cancel = $conn->prepare("UPDATE Commande SET statut = 'Annulée' WHERE id_commande = ? AND id_utilisateur = ? AND statut = 'En attente'");
    $stmt_cancel->execute([$id_cmd, $user_id]);
    if($stmt_cancel->rowCount() > 0) $_SESSION['msg_achats'] = "Commande #$id_cmd annulée.";
    header("Location: mes_achats.php"); exit();
}

// Action de laisser un avis (Update table Cibler)
if(isset($_POST['laisser_avis'])){
    $id_cmd = (int)$_POST['id_commande'];
    $note = (int)$_POST['note'];
    $commentaire = securisation($_POST['commentaire']);
    
    // On met à jour l'évaluation dans la table Cibler liée à cette commande
    $stmt_avis = $conn->prepare("UPDATE Cibler SET note_evaluation = ?, commentaire_evaluation = ? WHERE id_commande = ?");
    if($stmt_avis->execute([$note, $commentaire, $id_cmd])){
        $_SESSION['msg_achats'] = "Merci ! Votre avis a été enregistré avec succès.";
    }
    header("Location: mes_achats.php"); exit();
}

// 2. Mes Achats enrichis avec les données d'évaluation
$achats_sql = "SELECT c.*, p.titre_prestation, p.image_prestation, up.nom_utilisateur as nom_presta, up.prenom_utilisateur as prenom_presta,
               ci.note_evaluation, ci.commentaire_evaluation
               FROM Commande c
               JOIN Cibler ci ON c.id_commande = ci.id_commande
               JOIN Prestation p ON ci.id_prestation = p.id_prestation
               JOIN Utilisateur up ON p.id_utilisateur = up.id_utilisateur
               WHERE c.id_utilisateur = ?
               ORDER BY c.date_commande DESC";
$a_stmt = $conn->prepare($achats_sql);
$a_stmt->execute([$user_id]);
$mes_achats = $a_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Achats - ServicePro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .star-rating { color: #ffc107; cursor: pointer; font-size: 1.5rem; }
        .star-rating .bi-star:hover, .star-rating .bi-star-fill { color: #ffc107; }
        .btn-avis { background: #6f42c1; color: white; border-radius: 50px; font-weight: bold; border: none; padding: 5px 15px; font-size: 0.75rem; transition: 0.3s; }
        .btn-avis:hover { background: #5a32a3; color: white; transform: scale(1.05); }
    </style>
</head>

<body class="bg-light">

    <?php include("includes/navbar.php"); ?>

    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold mb-1">Mes Achats</h2>
                        <p class="text-muted small">Historique de vos commandes et évaluation des prestataires.</p>
                    </div>
                </div>

                <?php if(isset($_SESSION['msg_achats'])): ?>
                    <div class="alert alert-success rounded-4 border-0 shadow-sm mb-4 py-3 animate__animated animate__fadeIn">
                        <i class="bi bi-check-circle-fill me-2"></i> <?php echo $_SESSION['msg_achats']; unset($_SESSION['msg_achats']); ?>
                    </div>
                <?php endif; ?>

                <?php if(count($mes_achats) > 0): ?>
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover">
                                <thead class="bg-white border-bottom text-uppercase small opacity-75 fw-bold">
                                    <tr>
                                        <th class="ps-4 py-3">Service & Expert</th>
                                        <th class="py-3">Date</th>
                                        <th class="py-3 text-center">Statut</th>
                                        <th class="py-3 text-end pe-4">Actions / Avis</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($mes_achats as $cmd): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo $cmd['image_prestation'] ?? 'assets/img/default.jpg'; ?>" 
                                                     class="rounded-3 shadow-sm me-3" style="width: 45px; height: 45px; object-fit: cover;">
                                                <div>
                                                    <h6 class="mb-0 fw-bold small"><?php echo $cmd['titre_prestation']; ?></h6>
                                                    <small class="text-muted small">Par <?php echo $cmd['prenom_presta'] . ' ' . $cmd['nom_presta']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="small opacity-75"><?php echo date('d/m/Y', strtotime($cmd['date_commande'])); ?></td>
                                        <td class="text-center">
                                            <?php 
                                            $cl = "bg-warning-subtle text-warning";
                                            if($cmd['statut'] == 'Confirmée') $cl = "bg-info-subtle text-info";
                                            if($cmd['statut'] == 'Terminée') $cl = "bg-success-subtle text-success";
                                            if($cmd['statut'] == 'Annulée') $cl = "bg-danger-subtle text-danger";
                                            ?>
                                            <span class="badge <?php echo $cl; ?> rounded-pill px-3 fw-bold" style="font-size: 0.7rem;"><?php echo $cmd['statut']; ?></span>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <?php if($cmd['statut'] == 'Terminée'): ?>
                                                <?php if($cmd['note_evaluation']): ?>
                                                    <div class="text-warning">
                                                        <?php for($i=1; $i<=5; $i++): ?>
                                                            <i class="bi bi-star<?php echo ($i <= $cmd['note_evaluation']) ? '-fill' : ''; ?> small"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-avis" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#modalAvis" 
                                                            data-id="<?php echo $cmd['id_commande']; ?>"
                                                            data-service="<?php echo $cmd['titre_prestation']; ?>">
                                                        LAISSER UN AVIS
                                                    </button>
                                                <?php endif; ?>
                                            <?php elseif($cmd['statut'] == 'En attente'): ?>
                                                <a href="?annuler_commande=<?php echo $cmd['id_commande']; ?>" 
                                                   class="text-danger small fw-bold text-decoration-none"
                                                   onclick="return confirm('Annuler votre commande ?')">ANNULER</a>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                        <i class="bi bi-bag-x fs-1 text-muted opacity-25 mb-3 d-block"></i>
                        <h5 class="fw-bold">Aucun achat trouvé</h5>
                        <p class="text-muted small">Allez explorez nos services dès maintenant !</p>
                        <a href="catalogue.php" class="btn btn-primary rounded-pill px-4 mt-3 fw-bold">Découvrir le catalogue</a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- MODAL POUR LAISSER UN AVIS -->
    <div class="modal fade" id="modalAvis" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold mb-0">Donnez votre avis</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body py-4">
                        <input type="hidden" name="id_commande" id="id_commande_modal">
                        <p class="small text-muted mb-4">Votre avis aide les autres clients et valorise le travail de l'expert : <strong id="service_nom_modal"></strong></p>
                        
                        <div class="mb-4">
                            <label class="form-label d-block fw-bold small text-uppercase">Votre Note</label>
                            <div class="star-rating d-flex gap-2">
                                <i class="bi bi-star rating-star" data-value="1"></i>
                                <i class="bi bi-star rating-star" data-value="2"></i>
                                <i class="bi bi-star rating-star" data-value="3"></i>
                                <i class="bi bi-star rating-star" data-value="4"></i>
                                <i class="bi bi-star rating-star" data-value="5"></i>
                            </div>
                            <input type="hidden" name="note" id="note_input" value="5" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Votre Commentaire</label>
                            <textarea name="commentaire" class="form-control rounded-3" rows="3" placeholder="Qualité du travail, ponctualité, amabilité..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Plus tard</button>
                        <button type="submit" name="laisser_avis" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Publier l'avis</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include("includes/footer.php");?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Logique de la modal
        const modalAvis = document.getElementById('modalAvis');
        modalAvis.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('id_commande_modal').value = button.getAttribute('data-id');
            document.getElementById('service_nom_modal').textContent = button.getAttribute('data-service');
        });

        // Logique des étoiles
        const stars = document.querySelectorAll('.rating-star');
        const noteInput = document.getElementById('note_input');

        stars.forEach(star => {
            star.addEventListener('click', function() {
                const val = this.getAttribute('data-value');
                noteInput.value = val;
                
                stars.forEach(s => {
                    if(s.getAttribute('data-value') <= val) {
                        s.classList.replace('bi-star', 'bi-star-fill');
                    } else {
                        s.classList.replace('bi-star-fill', 'bi-star');
                    }
                });
            });
            
            // État par défaut (5 étoiles)
            if(star.getAttribute('data-value') <= 5) star.classList.replace('bi-star', 'bi-star-fill');
        });
    </script>
</body>

</html>
