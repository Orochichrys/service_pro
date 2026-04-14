<?php
// includes/verif_validation.php

$provider_is_blocked = false;

if (isset($_SESSION['user_id']) && isset($_SESSION['est_prestataire']) && $_SESSION['est_prestataire'] == 1) {
    if (!isset($_SESSION['is_validated']) || $_SESSION['is_validated'] == 0) {
        
        // Vérification en direct dans la base de données (au cas où l'admin l'a validé fraîchement)
        // Utilisation globale de la variable $conn déjà présente dans l'app, ou inclusion si manquante
        global $conn;
        if(!isset($conn)) {
            require_once(__DIR__ . "/db.php");
        }
        
        $stmt_val = $conn->prepare("SELECT is_validated FROM Utilisateur WHERE id_utilisateur = ?");
        $stmt_val->execute([$_SESSION['user_id']]);
        $real_validation_status = $stmt_val->fetchColumn();
        
        if ($real_validation_status == 1) {
            $_SESSION['is_validated'] = 1; // Mise à jour de la session sans déconnexion
            $provider_is_blocked = false;
        } else {
            $provider_is_blocked = true;
        }
    }
}
?>
