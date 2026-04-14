<?php 
$current_page = basename($_SERVER['PHP_SELF']); 
require_once(__DIR__ . "/verif_validation.php");
?>

<?php if(isset($provider_is_blocked) && $provider_is_blocked): ?>
<div class="alert alert-warning text-center fw-bold py-2 mb-0 rounded-0 shadow-sm border-0 d-flex align-items-center justify-content-center" style="position: relative; z-index: 1050; background-color: #ff9800; color: white;">
    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i> Votre compte prestataire est en attente de validation.
</div>
<?php endif; ?>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top py-2">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary fs-5" href="index.php">
            SERVICE<span class="text-dark">PRO</span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto mb-0" style="gap: 5px;">
                <li class="nav-item">
                    <a class="nav-link py-2 px-0 px-lg-3 <?php echo ($current_page == 'index.php') ? 'active fw-bold text-primary' : ''; ?>" href="index.php">Accueil</a>
                </li>
                <li class="nav-item <?php echo (!isset($_SESSION['user_id'])) ? 'border-bottom-mobile' : ''; ?>">
                    <a class="nav-link py-2 px-0 px-lg-3 <?php echo ($current_page == 'catalogue.php') ? 'active fw-bold text-primary' : ''; ?>" href="catalogue.php">Explorer</a>
                </li>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown d-none d-lg-block ms-lg-3">
                        <a class="nav-link dropdown-toggle text-dark fw-bold d-flex align-items-center p-0" href="#" id="userMenu" data-bs-toggle="dropdown">
                            <div class="avatar-xs bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width:32px; height:32px; font-size:0.75rem;">
                                <?php echo strtoupper(substr($_SESSION['user_prenom'], 0, 1) . substr($_SESSION['user_nom'], 0, 1)); ?>
                            </div>
                            <span class="ms-2"><?php echo $_SESSION['user_prenom']; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-2 p-2 rounded-4">
                            <?php if(isset($_SESSION['est_admin']) && $_SESSION['est_admin']): ?>
                                <li><a class="dropdown-item py-2 rounded-3 text-primary fw-bold" href="admin/index.php"><i class="bi bi-speedometer2 me-2"></i>Panel Admin</a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>

                            <li><a class="dropdown-item py-2 rounded-3" href="mon_profil.php"><i class="bi bi-person me-2"></i>Mon Profil</a></li>
                            
                            <?php if(isset($_SESSION['est_client']) && $_SESSION['est_client']): ?>
                                <li><a class="dropdown-item py-2 rounded-3" href="mes_commandes.php"><i class="bi bi-bag me-2"></i>Mes Commandes</a></li>
                            <?php endif; ?>

                            <?php if(isset($_SESSION['est_prestataire']) && $_SESSION['est_prestataire']): ?>
                                <li><a class="dropdown-item py-2 rounded-3" href="mes_services.php"><i class="bi bi-shop me-2"></i>Mes Services</a></li>
                                <li><a class="dropdown-item py-2 rounded-3" href="ajouter_prestation.php"><i class="bi bi-plus-circle me-2"></i>Publier un service</a></li>
                            <?php endif; ?>
                            
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 rounded-3 text-danger" href="auth/deconnexion.php"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item mt-3 mt-lg-0">
                        <a class="btn btn-outline-primary btn-md rounded-3 fw-bold d-block w-100 px-lg-4" href="auth/connexion.php">Connexion</a>
                    </li>
                    <li class="nav-item mt-2 mt-lg-0">
                        <a class="btn btn-primary btn-md rounded-3 fw-bold shadow-sm d-block w-100 px-lg-4" href="auth/inscription.php">S'inscrire</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if(isset($_SESSION['user_id'])): ?>
<div class="d-lg-none" style="position: fixed; bottom: 25px; right: 25px; z-index: 2000;">
    <div class="dropup">
        <button class="btn p-0 border-0 shadow-lg rounded-circle" type="button" data-bs-toggle="dropdown" style="width: 60px; height: 60px; background-color: #6f42c1;">
            <div class="text-white fw-bold d-flex align-items-center justify-content-center h-100 w-100 rounded-circle" style="font-size: 1.2rem; border: 2px solid rgba(255,255,255,0.3);">
                <?php echo strtoupper(substr($_SESSION['user_prenom'], 0, 1) . substr($_SESSION['user_nom'], 0, 1)); ?>
            </div>
        </button>
        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mb-3 p-2 rounded-4" style="min-width: 230px;">
            <li class="px-3 py-2 fw-bold text-primary border-bottom mb-2 small text-uppercase">Menu</li>
            
            <?php if(isset($_SESSION['est_admin']) && $_SESSION['est_admin']): ?>
                <li><a class="dropdown-item py-2 rounded-3 text-primary fw-bold" href="admin/index.php"><i class="bi bi-speedometer2 me-2"></i>Panel Admin</a></li>
                <li><hr class="dropdown-divider"></li>
            <?php endif; ?>

            <li><a class="dropdown-item py-2 rounded-3" href="mon_profil.php"><i class="bi bi-person me-2"></i>Mon Profil</a></li>
            
            <?php if(isset($_SESSION['est_client']) && $_SESSION['est_client']): ?>
                <li><a class="dropdown-item py-2 rounded-3" href="mes_commandes.php"><i class="bi bi-bag me-2"></i>Mes Commandes</a></li>
            <?php endif; ?>

            <?php if(isset($_SESSION['est_prestataire']) && $_SESSION['est_prestataire']): ?>
                <li><a class="dropdown-item py-2 rounded-3" href="mes_services.php"><i class="bi bi-shop me-2"></i>Mes Services</a></li>
                <li><a class="dropdown-item py-2 rounded-3" href="ajouter_prestation.php"><i class="bi bi-plus-circle me-2"></i>Publier un service</a></li>
            <?php endif; ?>
            
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item py-2 rounded-3 text-danger" href="auth/deconnexion.php"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</a></li>
        </ul>
    </div>
</div>
<?php endif; ?>


