# ServicePro - Plateforme de Mise en Relation de Services

ServicePro est une application web moderne permettant de mettre en relation des prestataires de services (plombiers, électriciens, coiffeurs, etc.) avec des clients locaux. La plateforme garantit la qualité des services grâce à un système de validation manuelle par l'administration.

## 🚀 Fonctionnalités Clés

### 👤 Pour les Utilisateurs (Clients)
- **Catalogue Dynamique** : Recherche de services par mots-clés, catégories et villes.
- **Détails Précis** : Consultation des fiches services avec photos, descriptions et tarifs.
- **Système de Commande** : Possibilité de commander une prestation en quelques clics.
- **Avis & Notes** : Évaluation des prestataires après intervention.

### 🛠️ Pour les Prestataires
- **Gestion des Services** : Publication, modification et suppression de prestations.
- **Suivi des États** : Notifications visuelles sur l'état de validation des services (En attente, Validé, Refusé).
- **Tableau de Bord** : Vue d'ensemble des missions et du profil.

### 🛡️ Pour l'Administration (Panel Admin)
- **Modération des Comptes** : Validation des nouveaux prestataires.
- **Modération des Services** : Approbation manuelle de chaque nouvelle publication ou modification pour garantir la qualité.
- **Statistiques** : Suivi du nombre d'utilisateurs, des commandes et du chiffre d'affaires global.

---

## ⚙️ Installation & Configuration

1. **Base de données** :
   - Créez une base de données nommée `servicepro_db`.
   - Importez le fichier `sql/schema.sql` (pour une structure vide) ou utilisez le dump fourni pour avoir des données de test.
2. **Configuration PHP** :
   - Modifiez `includes/db.php` avec vos propres identifiants MySQL (Host : `localhost`, User : `admin`, Pass : `admin` par défaut dans ce projet).
3. **Droits d'upload** :
   - Assurez-vous que le dossier `assets/img/uploads/` dispose des droits d'écriture (ex: `chmod 777`).

### 🔑 Première Utilisation (Admin)
- Si vous partez de zéro avec `schema.sql`, vous devez créer le premier compte administrateur en vous rendant sur :  
  `http://localhost/service_pro/admin/creer_premier_admin.php`

---

## 👥 Comptes de Test (Données incluses)

Si vous utilisez la base de données avec les données de démonstration, voici les identifiants disponibles :

| Rôle | Nom & Prénom | Email | Mot de passe |
| :--- | :--- | :--- | :--- |
| **☕ Admin** | Admin Principal | `admin@servicepro.ci` | `admin123` |
| **🛠️ Prestataire** | Marc Bakayoko | `marc.b@gmail.com` | `Presta123` |
| **🎨 Prestataire** | Awa Coulibaly | `awa.design@yahoo.fr` | `Awa2026*` |
| **👤 Client && Prestataire** | Jean-Luc Koffi | `jlkoffi@outlook.com` | `Client88` |

---

## 🎨 Design & Technologies
- **Backend** : PHP 8.x / PDO MySQL
- **Frontend** : HTML5 / CSS3 / Bootstrap 5 / Bootstrap Icons
- **Responsivité** : Entièrement compatible mobile, tablette et desktop.

---
© 2026 ServicePro - Simplifier le service au quartier.
