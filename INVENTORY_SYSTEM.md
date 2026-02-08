# Book Inventory Management System

## Vue d'ensemble (Overview)

Le système d'inventaire permet de gérer le nombre de copies physiques de chaque livre dans chaque bibliothèque.

The inventory system manages the number of physical copies of each book in each library.

## Fonctionnalités (Features)

### 1. Gestion Admin (Admin Management)

Dans les pages d'ajout/édition de livres (`/admin/books/new` et `/admin/books/{id}/edit`):

- L'admin sélectionne les bibliothèques où le livre est disponible
- Pour chaque bibliothèque sélectionnée, l'admin peut définir:
  - **Total Copies**: Nombre total de copies dans cette bibliothèque
  - **Available Copies**: Nombre de copies actuellement disponibles pour emprunt

### 2. Affichage Frontend (Frontend Display)

Sur la page des bibliothèques (`/books/{id}/libraries`):

- Affiche toutes les bibliothèques où le livre est disponible
- Pour chaque bibliothèque, montre:
  - **Indicateur de disponibilité**: Point vert (disponible) ou rouge (non disponible)
  - **Badge de statut**: "X/Y copies" (disponibles/total)
  - **Bouton d'emprunt**: Désactivé si aucune copie n'est disponible

### 3. Validation

- Le nombre de copies disponibles ne peut pas dépasser le nombre total
- Les valeurs doivent être positives ou zéro
- Si l'admin entre une valeur invalide, elle est automatiquement corrigée

## Structure de la Base de Données (Database Structure)

### Table: `book_library_inventory`

```sql
CREATE TABLE book_library_inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    library_id INT NOT NULL,
    total_copies INT NOT NULL DEFAULT 0,
    available_copies INT NOT NULL DEFAULT 0,
    UNIQUE KEY unique_book_library (book_id, library_id),
    FOREIGN KEY (book_id) REFERENCES book(id) ON DELETE CASCADE,
    FOREIGN KEY (library_id) REFERENCES library(id) ON DELETE CASCADE
);
```

## Fichiers Modifiés (Modified Files)

### Entités (Entities)
- `src/Entity/Library/BookLibraryInventory.php` - Nouvelle entité pour l'inventaire
- `src/Repository/Library/BookLibraryInventoryRepository.php` - Repository pour les requêtes

### Contrôleurs (Controllers)
- `src/Controller/Admin/Library/AdminBookController.php` - Gestion de l'inventaire dans new/edit
- `src/Controller/Front/Library/BookController.php` - Affichage de l'inventaire

### Templates
- `templates/admin/book/edit.html.twig` - Section d'inventaire ajoutée
- `templates/admin/book/new.html.twig` - Section d'inventaire ajoutée
- `templates/front/book/libraries.html.twig` - Affichage de la disponibilité

### Migrations
- `migrations/Version20260208160000.php` - Création de la table

### Seeds
- `database_seeds/insert_book_inventory_sample.sql` - Données de test

## Installation

1. Exécuter la migration:
```bash
php bin/console doctrine:migrations:migrate
```

2. (Optionnel) Insérer des données de test:
```bash
mysql -u root -p nom_de_la_base < database_seeds/insert_book_inventory_sample.sql
```

## Utilisation (Usage)

### Pour l'Admin:

1. Aller sur `/admin/books/new` ou `/admin/books/{id}/edit`
2. Sélectionner "Physical Book" (décocher "Digital Book")
3. Sélectionner une ou plusieurs bibliothèques (Ctrl/Cmd + clic)
4. Pour chaque bibliothèque sélectionnée, entrer:
   - Le nombre total de copies
   - Le nombre de copies disponibles
5. Sauvegarder

### Pour l'Étudiant:

1. Aller sur la page d'un livre `/books/{id}`
2. Cliquer sur "View Libraries"
3. Voir les bibliothèques avec:
   - Point vert = copies disponibles
   - Point rouge = aucune copie disponible
   - Badge montrant "X/Y copies"
4. Cliquer sur "Request Loan" pour emprunter (si disponible)

## Notes Techniques

- L'inventaire est automatiquement mis à jour lors de l'édition d'un livre
- Les anciens inventaires sont supprimés et recréés lors de la sauvegarde
- La validation côté serveur empêche les valeurs invalides
- JavaScript dynamique affiche/cache les champs d'inventaire selon les bibliothèques sélectionnées
