# Gestion des Demandes d'Expression de Besoins

Application web moderne pour digitaliser la gestion interne des demandes en matériel, logiciel, services et formation.

## Vue d'ensemble

Cette application permet aux employés (demandeurs) de soumettre des demandes qui sont validées par leur chef hiérarchique (validateur) puis traitées par l'administrateur système. Le système offre une traçabilité complète, des notifications en temps réel, et une gestion efficace du cycle de vie des demandes.

### Rôles utilisateurs

- **Demandeur** : Crée et suit ses demandes, peut les modifier tant qu'elles ne sont pas validées.
- **Validateur** : Approuve ou rejette les demandes de son équipe avec commentaires.
- **Admin** : Vue complète sur toutes les demandes, gère les utilisateurs, les types de besoins, et finalise le traitement.

## Prérequis

- PHP 7.4+ avec support PDO MySQL
- MySQL 5.7+
- Serveur web (Apache avec mod_rewrite ou Nginx)
- XAMPP 7.4+ (recommandé pour développement local)

## Installation

### 1. Cloner ou extraire le projet

```bash
# Sur Windows avec XAMPP
# Placer le dossier dans: C:\xampp\htdocs\gestion_besoins
```

### 2. Configurer la base de données

Ouvrir `config/db.php` et adapter les paramètres:

```php
$host = 'localhost';
$dbname = 'gestion_besoins';
$user = 'root';      // Votre utilisateur MySQL
$pass = '';          // Votre mot de passe MySQL
```

### 3. Créer la base de données

Importer le fichier SQL dans MySQL:

```bash
# Via MySQL Workbench ou phpMyAdmin
# Ouvrir sql/gestion_besoins.sql et exécuter le script
```

Ou via ligne de commande:

```bash
mysql -u root -p < sql/gestion_besoins.sql
```

### 4. Créer le dossier uploads

```bash
mkdir uploads
chmod 755 uploads
```

### 5. Accéder à l'application

```
http://localhost/gestion_besoins/
```

## Identifiants par défaut

L'administrateur est créé automatiquement avec la base de données:

- **Email** : admin@example.com
- **Mot de passe** : admin123
- **Rôle** : Admin

⚠️ **À FAIRE EN PRODUCTION** : Changer immédiatement ce mot de passe après la première connexion.

## Flux de travail

1. **Demandeur crée une demande**
   - Type de besoin, description, urgence, pièce jointe (optionnel)
   - Demande envoyée automatiquement au validateur (chef)

2. **Validateur valide ou rejette**
   - Consulte le tableau de bord "Demandes à valider"
   - Ajoute un commentaire (optionnel)
   - Le demandeur reçoit une notification

3. **Admin finalise le traitement**
   - Vue complète de toutes les demandes
   - Peut changer le statut (En attente → En cours → Traitée)
   - Le demandeur est notifié de chaque changement

## Fonctionnalités principales

### Pour les demandeurs
- ✅ Créer/consulter/modifier ses demandes
- ✅ Ajouter des pièces jointes (PDF, DOC, XLS, images)
- ✅ Suivre l'état de ses demandes
- ✅ Recevoir des notifications
- ✅ Changer son mot de passe depuis son espace connecté

### Pour les validateurs
- ✅ Tableau de bord des demandes à valider
- ✅ Filtrer par statut, date
- ✅ Valider/rejeter avec commentaires
- ✅ Historique des validations

### Pour les administrateurs
- ✅ Vue globale de toutes les demandes
- ✅ Gérer les utilisateurs (créer, modifier, supprimer)
- ✅ Gérer les types de besoins
- ✅ Changer le statut des demandes
- ✅ Filtrer par statut, demandeur, date
- ✅ Accès aux pièces jointes

## Configuration avancée

### Notifications par email (optionnel)

Pour activer les notifications par email:

1. Éditer `config/email.php`:

```php
define('MAIL_ENABLED', true);
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('MAIL_FROM', 'noreply@gestion-besoins.local');
define('MAIL_FROM_NAME', 'Gestion des Besoins');
```

2. Sur Windows/XAMPP, éditer `php.ini`:

```ini
[mail function]
SMTP = smtp.gmail.com
smtp_port = 587
sendmail_from = your-email@gmail.com
```

### Sécurité des uploads

Les fichiers autorisés: PDF, JPG, JPEG, PNG, DOC, DOCX, XLS, XLSX
Taille maximale: 5 MB
Les fichiers sont stockés dans le dossier `uploads/` avec un nom sécurisé.

## Structure du projet

```
gestion_besoins/
├── index.php                    # Page de connexion
├── dashboard_demandeur.php      # Tableau de bord demandeur
├── dashboard_validateur.php     # Tableau de bord validateur
├── dashboard_admin.php          # Tableau de bord admin
├── nouvelle_demande.php         # Créer une demande
├── mes_demandes.php             # Liste des demandes du demandeur
├── traiter_demande.php          # Traiter une demande (admin)
├── admin_users.php              # Gérer les utilisateurs
├── notifications.php            # Notifications internes
├── logout.php                   # Déconnexion
├── classes/
│   ├── Database.php             # Gestion de la connexion BD
│   ├── User.php                 # Gestion des utilisateurs
│   ├── Demande.php              # Gestion des demandes
│   ├── Notification.php         # Gestion des notifications
│   └── EmailHelper.php          # Helpers pour emails
├── config/
│   ├── db.php                   # Configuration BD
│   └── email.php                # Configuration emails
├── includes/
│   ├── header.php               # En-tête avec navigation
│   └── footer.php               # Pied de page
├── sql/
│   └── gestion_besoins.sql      # Schéma et données
├── uploads/                     # Pièces jointes (créer le dossier)
└── README.txt                   # Fichier de projet
```

## Gestion des utilisateurs

### Créer un utilisateur

1. Connexion avec compte admin
2. Menu → Utilisateurs
3. Formulaire "Ajouter un utilisateur"
4. Remplir: Nom, Email, Mot de passe, Rôle, Chef (si demandeur)

### Éditer un utilisateur

1. Cliquer sur "Modifier" dans la liste
2. Éditer les informations
3. Enregistrer

⚠️ Note: Le mot de passe ne peut pas être changé depuis l'édition (pour sécurité).

### Changer son mot de passe

1. Ouvrir le lien "Mot de passe" dans la barre de navigation
2. Saisir le mot de passe actuel
3. Saisir et confirmer le nouveau mot de passe

### Supprimer un utilisateur

1. Cliquer sur "Supprimer" dans la liste
2. Confirmer la suppression
3. Les demandes associées restent en BD mais l'utilisateur est supprimé

## Filtrage et recherche

### Validateur
- Filtrer par statut (En attente, En cours, Validée, Rejetée)
- Filtrer par date de création

### Admin
- Filtrer par statut
- Rechercher par nom de demandeur
- Filtrer par date

## Troubleshooting

### Erreur de connexion à la BD
- Vérifier les identifiants dans `config/db.php`
- Vérifier que MySQL est en cours d'exécution
- Vérifier que la base de données `gestion_besoins` existe

### Les pièces jointes ne s'envoient pas
- Vérifier que le dossier `uploads/` existe et est accessible en écriture
- Sur Windows: clic droit → Propriétés → Onglet Sécurité → Éditer → Donner accès à "Utilisateurs"
- Vérifier la taille du fichier (max 5 MB)
- Vérifier l'extension du fichier

### Les sessions expirent rapidement
- Éditer `php.ini` et modifier:
  ```ini
  session.gc_maxlifetime = 3600  # 1 heure
  ```

### Les notifications n'arrivent pas
- Les notifications internes sont stockées dans la BD et toujours actives
- Les emails ne sont envoyés que si `MAIL_ENABLED = true` dans `config/email.php`

## API interne (pour développeurs)

### Classe User
```php
User::findByEmail($email)      // Trouver un utilisateur par email
User::findById($id)            // Trouver par ID
User::all()                    // Tous les utilisateurs
User::create($nom, $email, $password, $role, $chef_id)  // Créer
```

### Classe Demande
```php
Demande::create($demandeur_id, $type_id, $description, $urgence, $validateur_id)
Demande::findByDemandeur($demandeur_id)   // Demandes d'un demandeur
Demande::findForValidateur($validateur_id) // Demandes à valider
Demande::all()                  // Toutes les demandes
Demande::changerStatut($id, $statut, $admin_id)
```

### Classe Notification
```php
Notification::create($user_id, $message)
Notification::findByUser($user_id)
Notification::countUnread($user_id)
Notification::markAllRead($user_id)
```

## Support et améliorations futures

- [ ] Export des demandes en CSV/Excel
- [ ] Graphiques et statistiques
- [ ] API REST
- [ ] Intégration LDAP/Active Directory
- [ ] Assignation automatique des demandes
- [ ] Notifications SMS

## License

Propriétaire - Tous droits réservés.

## Auteur

Développé avec PHP 7.4+, MySQL, Bootstrap 5.

---

**Version** : 1.0  
**Dernière mise à jour** : 2025-12-03
