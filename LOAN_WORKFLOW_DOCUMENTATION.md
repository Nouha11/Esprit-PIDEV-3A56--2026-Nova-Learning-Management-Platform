# Loan Approval Workflow - Documentation

## Vue d'ensemble

Le système de gestion des emprunts implémente un workflow complet d'approbation avec plusieurs statuts et règles métier avancées.

## Statuts des emprunts

### 1. PENDING (En attente)
- **Description**: Demande d'emprunt soumise par l'utilisateur
- **Créé quand**: L'utilisateur soumet le formulaire d'emprunt
- **Actions possibles**: Approuver ou Rejeter (Admin uniquement)

### 2. APPROVED (Approuvé)
- **Description**: Demande approuvée par l'administrateur
- **Créé quand**: Admin clique sur "Approve"
- **Actions possibles**: Marquer comme Actif (Admin)

### 3. REJECTED (Rejeté)
- **Description**: Demande rejetée par l'administrateur
- **Créé quand**: Admin clique sur "Reject" avec raison
- **Actions possibles**: Aucune (état final)

### 4. ACTIVE (Actif)
- **Description**: Livre récupéré par l'utilisateur
- **Créé quand**: Admin confirme que l'utilisateur a récupéré le livre
- **Actions possibles**: Marquer comme Retourné (Admin)

### 5. RETURNED (Retourné)
- **Description**: Livre retourné à la bibliothèque
- **Créé quand**: Admin confirme le retour du livre
- **Actions possibles**: Aucune (état final)

### 6. OVERDUE (En retard)
- **Description**: Emprunt dépassant la date de retour prévue
- **Créé quand**: Calculé automatiquement (14 jours après pickup)
- **Actions possibles**: Marquer comme Retourné

## Workflow complet

```
[Utilisateur soumet demande]
         ↓
    PENDING ──────────→ REJECTED (avec raison)
         ↓
    APPROVED
         ↓
    ACTIVE ──────────→ OVERDUE (si retard)
         ↓
    RETURNED
```

## Règles métier (Business Logic)

### 1. Limite d'emprunts actifs
- **Règle**: Maximum 3 emprunts actifs par utilisateur
- **Vérification**: Lors de l'approbation
- **Action**: Blocage de l'approbation si limite atteinte

### 2. Vérification des retards
- **Règle**: Avertissement si l'utilisateur a des emprunts en retard
- **Vérification**: Lors de l'approbation
- **Action**: Affichage d'un warning (n'empêche pas l'approbation)

### 3. Calcul automatique des retards
- **Règle**: Emprunt considéré en retard après 14 jours
- **Calcul**: `isOverdue()` et `getDaysOverdue()`
- **Affichage**: Badge rouge "En retard"

## Fonctionnalités pour l'administrateur

### Page de gestion (`/admin/loans`)
- **Statistiques**: Compteurs par statut (Pending, Approved, Active, etc.)
- **Filtres**: Onglets pour filtrer par statut
- **Actions rapides**: Approve/Reject/Mark Active/Mark Returned
- **Modals**: Confirmation pour chaque action

### Page de détails (`/admin/loans/{id}`)
- **Informations utilisateur**: Username, email, roles
- **Informations livre**: Titre, auteur, couverture
- **Timeline**: Dates de demande, pickup, retour
- **Bibliothèque**: Nom et adresse si spécifiée
- **Actions contextuelles**: Selon le statut actuel

## Fonctionnalités pour l'utilisateur

### Ma bibliothèque (`/my-library`)
- **Emprunts actifs**: Liste des emprunts PENDING, APPROVED, ACTIVE
- **Badges de statut**: Couleurs différentes par statut
- **Messages contextuels**: 
  - PENDING: "Waiting for approval"
  - APPROVED: "Ready for pickup"
  - ACTIVE: "Book in your possession"
- **Historique**: Derniers 10 emprunts RETURNED ou REJECTED

## Champs de la base de données

### Table `loans`
```sql
- id (int, PK)
- book_id (FK → books)
- user_id (FK → users)
- library_id (FK → library, nullable)
- start_at (datetime) - Date de pickup prévue
- end_at (datetime, nullable) - Date de retour prévue
- status (varchar 20) - Statut actuel
- requested_at (datetime) - Date de la demande
- approved_at (datetime, nullable) - Date d'approbation
- rejection_reason (text, nullable) - Raison du rejet
```

## Méthodes utiles dans l'entité Loan

### `isOverdue(): bool`
Vérifie si l'emprunt est en retard (> 14 jours depuis pickup)

### `getDaysOverdue(): int`
Retourne le nombre de jours de retard

### `getStatusLabel(): string`
Retourne le libellé en français du statut

### `getStatusBadgeClass(): string`
Retourne la classe CSS Bootstrap pour le badge de statut

## Routes principales

### Admin
- `GET /admin/loans` - Liste des emprunts
- `GET /admin/loans/{id}` - Détails d'un emprunt
- `POST /admin/loans/{id}/approve` - Approuver
- `POST /admin/loans/{id}/reject` - Rejeter
- `POST /admin/loans/{id}/mark-active` - Marquer actif
- `POST /admin/loans/{id}/mark-returned` - Marquer retourné

### Utilisateur
- `GET /my-library` - Ma bibliothèque
- `POST /books/{id}/loan` - Créer une demande d'emprunt

## Améliorations futures possibles

1. **Notifications par email**: Alerter l'utilisateur lors des changements de statut
2. **Pénalités de retard**: Calculer des frais pour les retards
3. **Système de réservation**: File d'attente pour les livres populaires
4. **Rappels automatiques**: Email 2 jours avant la date de retour
5. **Historique complet**: Logs de tous les changements de statut
6. **Statistiques avancées**: Graphiques d'utilisation, livres populaires
7. **Blocage automatique**: Empêcher les nouveaux emprunts si retards non résolus

## Sécurité

- **CSRF Protection**: Tous les formulaires utilisent des tokens CSRF
- **Role-based Access**: Seuls les ROLE_ADMIN peuvent gérer les emprunts
- **Validation**: Contraintes Symfony sur tous les champs
- **SQL Injection**: Protection via Doctrine ORM

## Tests recommandés

1. Créer une demande d'emprunt (utilisateur)
2. Approuver la demande (admin)
3. Marquer comme actif (admin)
4. Marquer comme retourné (admin)
5. Tester le rejet avec raison
6. Vérifier la limite de 3 emprunts actifs
7. Tester l'affichage des badges de statut
