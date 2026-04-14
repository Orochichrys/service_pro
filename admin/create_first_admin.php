<?php

// Script de création du premier admin
// Il fonctionnera une seule fois !
require("../includes/db.php");

// Données prédéfinies
$nom = "Admin";
$prenom = "Principal";
$email = "admin@servicepro.ci";
$pass = "admin1234";

// On hash le mot de passe
$pass_hache = password_hash($pass, PASSWORD_DEFAULT);


try {
    // On récupère les données de la table Utilisateur.
    $sql = "
        SELECT *
        FROM Utilisateur
        WHERE est_admin = TRUE;
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Cas : Admin existe déjà
        echo ("<h1 style=\"color:red;\">Sécurité : Le système d'administration est déjà initialisé. Création de compte admin impossible.</h1>");
    } else {
        // Cas : Aucun admin 
        echo ("<h1>Initialisation autorisée : Aucun administrateur détecté. Procédure de création du compte racine activée...</h1>");

        $sql = "
            INSERT INTO Utilisateur
            (nom_utilisateur,
            prenom_utilisateur,
            email_utilisateur,
            password_utilisateur,
            est_admin
            )
            VALUES(
                :nom,
                :prenom,
                :email,
                :pass,
                :admin
            );
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            "nom" => $nom,
            "prenom" => $prenom,
            "email" => $email,
            "pass" => $pass_hache,
            "admin" => TRUE,
        ]);

        echo "
    <h2 style=\"color:green;\">Inscription réussie ! Voici vos informations de connexion :</h2>
    <ul>
        <li><strong>Nom :</strong> $nom</li>
        <li><strong>Prénom :</strong> $prenom</li>
        <li><strong>Email :</strong> $email</li>
        <li><strong>Mot de passe :</strong> $pass</li>
    </ul>
    
    <div style='background-color: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 15px; border-radius: 5px; margin-top: 20px;'>
        <strong>Important pour l'administrateur :</strong><br>
        Ce compte a été créé avec un mot de passe par défaut. 
        Veuillez vous <a href=\"../auth/login.php\">connecter </a> immédiatement et <strong>modifier votre mot de passe</strong> depuis votre profil pour sécuriser l'accès.
       
    </div>
";
    }
} catch (PDOException $e) {
    die("Erreur: " . $e->getMessage());
}
