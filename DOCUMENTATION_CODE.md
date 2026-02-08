# Documentation du Code - Gestion des Livres

## Vue d'ensemble
Ce document explique en détail le fonctionnement du système de gestion des livres.

---

## 1. ENTITÉ BOOK (src/Entity/Library/Book.php)

### Description
L'entité Book représente un livre dans la base de données. C'est une classe PHP qui est mappée à une table SQL grâce à Doctrine ORM.

### Propriétés principales

#### `$id` - Identifiant unique
- **Type**: Integer
- **Obligatoire**: Oui (généré automatiquement)
- **Description**: Clé primaire auto-incrémentée

#### `$title` - Titre du livre
- **Type**: String (255 caractères max)
- **Obligatoire**: Oui
- **Validation**: 3-255 caractères
- **Description**: Le titre complet du livre

#### `$description` - Description
- **Type**: Text (5000 caractères max)
- **Obligatoire**: Non
- **Description**: Description détaillée du contenu du livre

#### `$author` - Auteur
- **Type**: String (255 caractères max)
- **Obligatoire**: Non
- **Validation**: 2-255 caractères si fourni
- **Description**: Nom de l'auteur du livre

#### `$isDigital` - Format numérique
- **Type**: Boolean
- **Obligatoire**: Oui (défaut: true)
- **Description**: true = livre numérique (PDF), false = livre physique

#### `$price` - Prix
- **Type**: Decimal (10,2)
- **Obligatoire**: Non
- **Validation**: Positif, max 2 décimales
- **Description**: Prix de vente du livre en USD

#### `$coverImage` - Image de couverture
- **Type**: String (255 caractères max)
- **Obligatoire**: Non
- **Description**: Chemin relatif vers l'image de couverture

#### `$publishedAt` - Date de publication
- **Type**: DateTimeImmutable
- **Obligatoire**: Non
- **Validation**: Ne peut pas être dans le futur
- **Description**: Date de publication originale du livre

#### `$uploaderId` - ID de l'uploader
- **Type**: Integer
- **Obligatoire**: Non
- **Description**: ID de l'utilisateur qui a ajouté le livre

#### `$createdAt` - Date de création
- **Type**: DateTimeImmutable
- **Obligatoire**: Oui
- **Description**: Date d'ajout du livre dans le système

#### `$updatedAt` - Date de modification
- **Type**: DateTimeImmutable
- **Obligatoire**: Non
- **Description**: Date de dernière modification

---

## 2. CONTRÔLEUR ADMINBOOKCONTROLLER

### Route de base: `/admin/books`
### Sécurité: Accessible uniquement aux utilisateurs avec le rôle `ROLE_ADMIN`

---

### 2.1 INDEX - Liste des livres
**Route**: `GET /admin/books/`  
**Nom**: `admin_books_index`

**Fonctionnement**:
1. Récupère l'utilisateur connecté
2. Charge tous les livres uploadés par cet utilisateur
3. Trie par date de création (plus récent en premier)
4. Affiche la liste dans le template `admin/book/index.html.twig`

**Retour**: Page HTML avec la liste des livres

---

### 2.2 SEARCH - Recherche de livres
**Route**: `GET /admin/books/search`  
**Nom**: `admin_books_search`

**Paramètres**:
- `q` (query string): Terme de recherche

**Fonctionnement**:
1. Vérifie que la requête contient au moins 2 caractères
2. Recherche dans les titres ET les auteurs (insensible à la casse)
3. Limite les résultats à 5 livres maximum
4. Retourne les données au format JSON

**Retour**: JSON avec tableau de livres
```json
[
  {
    "id": 1,
    "title": "Harry Potter",
    "author": "J.K. Rowling",
    "price": "19.99",
    "coverImage": "/uploads/books/cover.jpg",
    "isDigital": true,
    "url": "/admin/books/1"
  }
]
```

---

### 2.3 NEW - Ajouter un nouveau livre
**Route**: `GET/POST /admin/books/new`  
**Nom**: `admin_books_new`

**Fonctionnement GET**:
1. Crée une nouvelle instance de Book
2. Définit l'uploader ID avec l'utilisateur connecté
3. Définit la date de création
4. Affiche le formulaire vide

**Fonctionnement POST**:
1. Valide les données du formulaire
2. Si une date de publication est fournie, met l'heure à minuit
3. Gère l'upload de l'image de couverture:
   - Génère un nom de fichier unique
   - Crée le dossier si nécessaire
   - Déplace le fichier uploadé
   - Enregistre le chemin dans la base
4. Sauvegarde le livre en base de données
5. Redirige vers la page de confirmation

**Retour**: 
- GET: Formulaire HTML
- POST: Redirection vers `admin_books_added`

---

### 2.4 ADDED - Page de confirmation
**Route**: `GET /admin/books/{id}/added`  
**Nom**: `admin_books_added`

**Paramètres**:
- `id`: ID du livre ajouté

**Fonctionnement**:
1. Charge le livre depuis la base
2. Vérifie que l'utilisateur est le propriétaire
3. Affiche une page de succès avec les détails du livre

**Retour**: Page HTML de confirmation

---

### 2.5 SHOW - Afficher un livre
**Route**: `GET /admin/books/{id}`  
**Nom**: `admin_books_show`

**Paramètres**:
- `id`: ID du livre

**Fonctionnement**:
1. Charge le livre depuis la base
2. Vérifie que l'utilisateur est le propriétaire
3. Affiche tous les détails du livre

**Sécurité**: Seul le propriétaire ou un SUPER_ADMIN peut voir

**Retour**: Page HTML avec détails complets

---

### 2.6 EDIT - Modifier un livre
**Route**: `GET/POST /admin/books/{id}/edit`  
**Nom**: `admin_books_edit`

**Paramètres**:
- `id`: ID du livre à modifier

**Fonctionnement GET**:
1. Charge le livre existant
2. Vérifie les permissions
3. Pré-remplit le formulaire avec les données actuelles

**Fonctionnement POST**:
1. Valide les modifications
2. Si une date de publication est fournie, met l'heure à minuit
3. Gère la nouvelle image de couverture:
   - Supprime l'ancienne image si elle existe
   - Upload la nouvelle image
   - Met à jour le chemin
4. Met à jour la date de modification
5. Sauvegarde les changements
6. Redirige vers la page de détails

**Sécurité**: Seul le propriétaire ou un SUPER_ADMIN peut modifier

**Retour**: 
- GET: Formulaire pré-rempli
- POST: Redirection vers `admin_books_show`

---

### 2.7 DELETE - Supprimer un livre
**Route**: `POST /admin/books/{id}/delete`  
**Nom**: `admin_books_delete`

**Paramètres**:
- `id`: ID du livre à supprimer
- `_token`: Token CSRF pour la sécurité

**Fonctionnement**:
1. Charge le livre
2. Vérifie les permissions
3. Valide le token CSRF
4. Supprime l'image de couverture du serveur
5. Supprime le livre de la base de données
6. Redirige vers la liste

**Sécurité**: 
- Token CSRF obligatoire
- Seul le propriétaire ou un SUPER_ADMIN peut supprimer

**Retour**: Redirection vers `admin_books_index`

---

## 3. SÉCURITÉ

### Protection CSRF
Toutes les actions de modification (POST, DELETE) utilisent des tokens CSRF pour prévenir les attaques.

### Contrôle d'accès
- Niveau 1: `#[IsGranted('ROLE_ADMIN')]` sur la classe
- Niveau 2: Vérification du propriétaire dans chaque méthode
- Exception: Les SUPER_ADMIN peuvent tout faire

### Validation des fichiers
- Taille max: 5MB
- Formats acceptés: JPEG, PNG, WebP
- Validation MIME type côté serveur

---

## 4. GESTION DES FICHIERS

### Dossier d'upload
`public/uploads/books/`

### Nommage des fichiers
Format: `{slug}-{uniqid}.{extension}`
Exemple: `harry-potter-63f8a9b2c1d4e.jpg`

### Suppression
Les anciennes images sont automatiquement supprimées lors de:
- Modification avec nouvelle image
- Suppression du livre

---

## 5. FLUX DE DONNÉES

### Ajout d'un livre
```
Utilisateur → Formulaire → Validation → Upload image → 
Base de données → Page de confirmation
```

### Recherche
```
Saisie utilisateur → AJAX → Contrôleur → Base de données → 
JSON → JavaScript → Affichage résultats
```

### Modification
```
Chargement données → Formulaire pré-rempli → Validation → 
Upload nouvelle image → Suppression ancienne → 
Mise à jour BDD → Redirection
```

---

## 6. MESSAGES FLASH

### Types de messages
- `success`: Opération réussie (vert)
- `error`: Erreur (rouge)

### Exemples
- "Book created successfully!"
- "Book updated successfully!"
- "Book deleted successfully!"
- "Failed to upload cover image: [erreur]"

---

## 7. TEMPLATES UTILISÉS

- `admin/book/index.html.twig`: Liste des livres
- `admin/book/new.html.twig`: Formulaire d'ajout
- `admin/book/edit.html.twig`: Formulaire de modification
- `admin/book/show.html.twig`: Détails d'un livre
- `admin/book/added.html.twig`: Page de confirmation

---

## 8. DÉPENDANCES

### Services injectés
- `EntityManagerInterface`: Gestion de la base de données
- `SluggerInterface`: Génération de noms de fichiers sécurisés
- `Request`: Gestion des requêtes HTTP

### Paramètres
- `kernel.project_dir`: Chemin racine du projet
- `booksDirectory`: Dossier d'upload des images

---

Cette documentation couvre tous les aspects du système de gestion des livres pour votre présentation.
