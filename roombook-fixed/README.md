# RoomBook

RoomBook est une application web développée en PHP et MySQL permettant la gestion et la réservation de salles au sein d'un établissement. Elle offre une interface d'administration pour gérer les utilisateurs, les salles et les réservations, ainsi qu'un espace utilisateur pour effectuer et suivre les réservations.

## Fonctionnalités

### Authentification
- Inscription des utilisateurs
- Connexion sécurisée
- Déconnexion
- Gestion des sessions
- Mots de passe chiffrés avec `password_hash()`

### Gestion des utilisateurs
- Ajouter un utilisateur
- Modifier un utilisateur
- Supprimer un utilisateur
- Consulter la liste des utilisateurs
- Gestion des rôles (Administrateur / Client)

### Gestion des salles
- Ajouter une salle
- Modifier une salle
- Supprimer une salle
- Consulter les salles disponibles
- Gestion de la capacité, localisation et équipements

### Gestion des réservations
- Créer une réservation
- Modifier une réservation
- Annuler une réservation
- Consulter les réservations
- Gestion des statuts :
  - En attente
  - Confirmée
  - Refusée
  - Annulée

### Statistiques
- Nombre total de réservations
- Salle la plus réservée
- Heure de réservation la plus utilisée
- Répartition des réservations par statut
- Classement des utilisateurs les plus actifs (administrateur)

## Technologies utilisées

- PHP 8+
- MySQL
- PDO
- HTML5
- CSS3
- Bootstrap 5
- JavaScript
- Bootstrap Icons

## Structure du projet

```text
roombook/
│
├── actions/           # Traitements CRUD
├── api/               # API des réservations et salles
├── assets/
│   ├── css/
│   └── js/
├── auth/              # Authentification
├── config/            # Configuration base de données
├── database/          # Scripts SQL
├── includes/          # Composants réutilisables
├── pages/             # Interfaces de l'application
└── database.sql       # Base de données
```

## Installation

### 1. Cloner le projet

```bash
git clone https://github.com/narjissebsaila/roombook.git
```

### 2. Placer le projet dans XAMPP

Copier le dossier dans :

```text
C:\xampp\htdocs\
```

### 3. Créer la base de données

- Ouvrir phpMyAdmin
- Créer une base nommée :

```sql
roombook
```

- Importer le fichier :

```text
database.sql
```

ou

```text
database/roombook.sql
```

### 4. Configurer la connexion

Modifier le fichier :

```php
config/database.php
```

avec vos paramètres MySQL.

### 5. Lancer le projet

Démarrer Apache et MySQL depuis XAMPP puis accéder à :

```text
http://localhost/roombook
```

## Comptes utilisateurs

Les utilisateurs peuvent être créés via l'interface d'administration ou directement dans la base de données.

Les mots de passe doivent être enregistrés avec :

```php
password_hash($motDePasse, PASSWORD_DEFAULT);
```

## Sécurité

- Utilisation de PDO et requêtes préparées
- Protection contre les injections SQL
- Validation des données utilisateur
- Échappement des données affichées avec `htmlspecialchars()`
- Gestion sécurisée des sessions

## Auteur

**Narjisse Bsaila**

Projet académique réalisé dans le cadre de l'apprentissage du développement web avec PHP et MySQL.

## Licence

Projet à but éducatif.
