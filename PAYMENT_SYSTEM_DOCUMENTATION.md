# Payment System Documentation

## Vue d'ensemble

Le système de paiement implémente une validation réaliste des cartes bancaires et un traitement simulé des paiements pour les achats de livres numériques.

## Fonctionnalités

### 1. Validation des cartes bancaires

#### Algorithme de Luhn
- Valide les numéros de carte (13-19 chiffres)
- Vérifie la somme de contrôle selon l'algorithme de Luhn
- Accepte les formats avec espaces ou tirets

#### Validation de la date d'expiration
- Format: MM/YY
- Vérifie que la carte n'est pas expirée
- Compare avec la date actuelle

#### Validation du CVC
- 3 ou 4 chiffres
- Chiffres uniquement

#### Validation du titulaire
- Minimum 3 caractères
- Lettres, espaces, tirets et apostrophes uniquement

### 2. Méthodes de paiement

#### Carte bancaire
- Validation complète des données
- Taux de succès simulé: 95%
- Stockage sécurisé (4 derniers chiffres uniquement)

#### PayPal
- Simulation de redirection PayPal
- Taux de succès simulé: 98%
- Pas de données de carte nécessaires

### 3. Entité Payment

```php
- id (int, PK)
- user_id (FK → users)
- book_id (FK → books)
- amount (decimal 10,2)
- payment_method (varchar 20) - 'credit_card' ou 'paypal'
- status (varchar 20) - PENDING, COMPLETED, FAILED, REFUNDED
- transaction_id (varchar 100, unique) - Format: TXN-XXXXX-9999
- card_last_four (varchar 20, nullable)
- card_holder_name (varchar 50, nullable)
- failure_reason (text, nullable)
- created_at (datetime)
- completed_at (datetime, nullable)
```

### 4. Statuts de paiement

- **PENDING**: Paiement en cours de traitement
- **COMPLETED**: Paiement réussi
- **FAILED**: Paiement échoué (carte invalide, refusée, etc.)
- **REFUNDED**: Paiement remboursé

### 5. Workflow de paiement

```
[Utilisateur sélectionne livre]
         ↓
[Choisit méthode de paiement]
         ↓
[Remplit formulaire de paiement]
         ↓
[Validation côté serveur]
         ↓
    ┌────┴────┐
    ↓         ↓
SUCCÈS    ÉCHEC
    ↓         ↓
Purchase  Erreur affichée
créé      Payment enregistré
    ↓
Redirection vers My Library
```

## Sécurité

### Données sensibles
- **Jamais stockées**: Numéro de carte complet, CVC, date d'expiration
- **Stockées**: 4 derniers chiffres, nom du titulaire
- **Hashées**: Aucune (simulation scolaire)

### Validation
- Toutes les validations côté serveur
- Pas de validation HTML5 (désactivée)
- Messages d'erreur clairs

### CSRF Protection
- Tokens CSRF sur tous les formulaires
- Validation automatique par Symfony

## Routes

### Utilisateur
- `GET /books/{id}/purchase` - Sélection méthode de paiement
- `GET /books/{id}/payment` - Formulaire de paiement
- `POST /books/{id}/payment` - Traitement du paiement
- `GET /my-payments` - Historique des paiements

## Tests de carte

Pour tester le système, utilisez ces numéros de carte valides (algorithme de Luhn):

### Cartes valides
- **Visa**: 4532015112830366
- **Mastercard**: 5425233430109903
- **American Express**: 374245455400126

### Format
- Avec espaces: `4532 0151 1283 0366`
- Avec tirets: `4532-0151-1283-0366`
- Sans séparateurs: `4532015112830366`

### Date d'expiration
- Format: MM/YY
- Exemple valide: `12/25` (décembre 2025)
- Exemple invalide: `01/20` (janvier 2020 - expiré)

### CVC
- 3 chiffres: `123`
- 4 chiffres (Amex): `1234`

### Nom du titulaire
- Minimum 3 caractères
- Exemple: `John Doe`

## Simulation de paiement

### Taux de succès
- **Carte bancaire**: 95% de succès
- **PayPal**: 98% de succès

### Raisons d'échec simulées
- Carte invalide (validation échouée)
- Carte expirée
- CVC invalide
- Nom invalide
- Paiement refusé par la banque (5% aléatoire)

## Historique des paiements

### Page `/my-payments`
- Liste tous les paiements de l'utilisateur
- Statistiques: Total, Réussis, Échoués, Montant total
- Détails: Transaction ID, Livre, Montant, Méthode, Date, Statut
- Raison d'échec affichée au survol

### Informations affichées
- Transaction ID unique
- Livre acheté (titre + auteur)
- Montant payé
- Méthode de paiement
- 4 derniers chiffres de la carte (si carte)
- Date et heure
- Statut avec badge coloré
- Raison d'échec (si applicable)

## Service PaymentService

### Méthodes publiques

#### `validateCardNumber(string $cardNumber): bool`
Valide un numéro de carte avec l'algorithme de Luhn

#### `validateExpiryDate(string $expiry): bool`
Valide la date d'expiration (format MM/YY)

#### `validateCVC(string $cvc): bool`
Valide le code CVC (3-4 chiffres)

#### `validateCardHolder(string $name): bool`
Valide le nom du titulaire

#### `processCreditCardPayment(...): array`
Traite un paiement par carte bancaire
Retourne: `['success' => bool, 'transaction_id' => string, 'errors' => array]`

#### `processPayPalPayment(Payment $payment): array`
Traite un paiement PayPal
Retourne: `['success' => bool, 'transaction_id' => string, 'errors' => array]`

#### `getCardType(string $cardNumber): string`
Détermine le type de carte (Visa, Mastercard, Amex, Discover)

## Améliorations futures possibles

1. **Intégration réelle**: Stripe, PayPal API
2. **Webhooks**: Notifications de paiement asynchrones
3. **Remboursements**: Interface admin pour rembourser
4. **Abonnements**: Paiements récurrents
5. **Multi-devises**: Support EUR, USD, etc.
6. **Factures PDF**: Génération automatique
7. **Emails**: Confirmation de paiement
8. **Retry logic**: Réessayer les paiements échoués
9. **3D Secure**: Authentification forte
10. **Fraud detection**: Détection de fraude

## Notes pour la présentation

- Système complet de validation
- Algorithme de Luhn implémenté
- Stockage sécurisé (pas de données sensibles)
- Historique complet des transactions
- Gestion des échecs avec raisons
- Interface utilisateur claire
- Statistiques de paiement
- Transaction ID unique par paiement
