<?php $current_page = basename($_SERVER['PHP_SELF']); ?>

<!-- Toggler Button for Mobile -->
<button class="btn border-0 shadow d-lg-none position-fixed bg-white text-primary" style="top: 15px; left: 15px; z-index: 1040; width: 45px; height: 45px; padding:0; border-radius: 12px;" onclick="document.getElementById('sidebar').classList.toggle('show')">
    <i class="bi bi-list fs-3"></i>
</button>

<div class="sidebar" id="sidebar">
    <div class="px-4 mb-4 d-flex justify-content-between align-items-center">
        <h4 class="fw-bold text-white mb-0">ADMIN<span class="text-primary">PANEL</span></h4>
        <button class="btn btn-link text-white p-0 d-lg-none" onclick="document.getElementById('sidebar').classList.remove('show')"><i class="bi bi-x-lg"></i></button>
    </div>
    <nav class="mt-4">
        <a href="index.php" class="nav-link-admin <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
        <a href="users.php" class="nav-link-admin <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>"><i class="bi bi-people me-2"></i> Utilisateurs</a>
        <a href="categories.php" class="nav-link-admin <?php echo ($current_page == 'categories.php') ? 'active' : ''; ?>"><i class="bi bi-list-ul me-2"></i> Catégories</a>
        <a href="commandes.php" class="nav-link-admin <?php echo ($current_page == 'commandes.php') ? 'active' : ''; ?>"><i class="bi bi-cart me-2"></i> Commandes</a>
        <a href="localisations.php" class="nav-link-admin <?php echo ($current_page == 'localisations.php') ? 'active' : ''; ?>"><i class="bi bi-geo-alt me-2"></i> Localisations</a>
        <hr class="mx-3 opacity-25">
        <a href="mon_profil.php" class="nav-link-admin <?php echo ($current_page == 'mon_profil.php') ? 'active' : ''; ?>"><i class="bi bi-person-gear me-2"></i> Mon Profil</a>
        <a href="../index.php" class="nav-link-admin"><i class="bi bi-arrow-left me-2"></i> Voir le site</a>
    </nav>
</div>