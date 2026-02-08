# Contrôles de Saisie - Gestion des Livres

## Vue d'ensemble
Ce document explique tous les contrôles de saisie (validations) implémentés pour la gestion des livres dans l'application.

---

## 1. Titre du livre (Title)
**Type:** Texte  
**Obligatoire:** OUI  

### Validations:
- ✅ **NotBlank**: Le champ ne peut pas être vide
- ✅ **Length (min: 3)**: Le titre doit contenir au moins 3 caractères
- ✅ **Length (max: 255)**: Le titre ne peut pas dépasser 255 caractères

**Exemple valide:** "Harry Potter et la Pierre Philosophale"  
**Exemple invalide:** "HP" (trop court)

---

## 2. Description
**Type:** Texte long (textarea)  
**Obligatoire:** NON  

### Validations:
- ✅ **Length (max: 5000)**: La description ne peut pas dépasser 5000 caractères

**Exemple valide:** Une description détaillée du livre  
**Exemple invalide:** Un texte de plus de 5000 caractères

---

## 3. Auteur (Author)
**Type:** Texte  
**Obligatoire:** NON  

### Validations:
- ✅ **Length (min: 2)**: Si fourni, le nom doit contenir au moins 2 caractères
- ✅ **Length (max: 255)**: Le nom ne peut pas dépasser 255 caractères

**Exemple valide:** "J.K. Rowling"  
**Exemple invalide:** "J" (trop court si fourni)

---

## 4. Format du livre (isDigital)
**Type:** Booléen (case à cocher)  
**Obligatoire:** OUI (par défaut: true)  

### Validations:
- ✅ **Type (bool)**: Doit être true (numérique) ou false (physique)

**Valeurs possibles:**
- ☑️ Coché = Livre numérique (PDF/eBook)
- ☐ Décoché = Livre physique

---

## 5. Prix (Price)
**Type:** Nombre décimal  
**Obligatoire:** NON  

### Validations:
- ✅ **PositiveOrZero**: Le prix doit être positif ou zéro (pas de prix négatif)
- ✅ **Regex**: Maximum 2 chiffres après la virgule

**Exemple valide:** 19.99, 25.00, 0  
**Exemple invalide:** -10 (négatif), 19.999 (trop de décimales)

---

## 6. Date de publication (PublishedAt)
**Type:** Date et heure  
**Obligatoire:** NON  

### Validations:
- ✅ **LessThanOrEqual('now')**: La date ne peut pas être dans le futur

**Exemple valide:** 01/01/2023  
**Exemple invalide:** 01/01/2030 (date future)

---

## 7. Image de couverture (CoverImage)
**Type:** Fichier (upload)  
**Obligatoire:** NON  

### Validations:
- ✅ **MaxSize (5M)**: Taille maximale de 5 mégaoctets
- ✅ **MimeTypes**: Formats acceptés uniquement:
  - image/jpeg (.jpg, .jpeg)
  - image/png (.png)
  - image/webp (.webp)

**Exemple valide:** cover.jpg (2MB)  
**Exemple invalide:** document.pdf (mauvais format), image.jpg (10MB - trop grand)

---

## Résumé des contrôles

| Champ | Obligatoire | Validation principale |
|-------|-------------|----------------------|
| Titre | ✅ OUI | 3-255 caractères |
| Description | ❌ NON | Max 5000 caractères |
| Auteur | ❌ NON | 2-255 caractères (si fourni) |
| Format | ✅ OUI | Booléen (Digital/Physique) |
| Prix | ❌ NON | Positif, max 2 décimales |
| Date publication | ❌ NON | Pas dans le futur |
| Image couverture | ❌ NON | Max 5MB, JPEG/PNG/WebP |

---

## Localisation du code

### Entity (src/Entity/Library/Book.php)
Les validations sont définies avec les annotations `#[Assert\...]` au-dessus de chaque propriété.

### Form (src/Form/Library/BookType.php)
Les contraintes supplémentaires (comme le fichier) sont définies dans la méthode `buildForm()`.

### Messages d'erreur
Tous les messages d'erreur sont en français pour faciliter la compréhension de l'utilisateur.

---

## Avantages de ces contrôles

1. **Sécurité**: Empêche l'injection de données invalides
2. **Cohérence**: Garantit que toutes les données respectent le même format
3. **Expérience utilisateur**: Messages d'erreur clairs en français
4. **Intégrité des données**: Évite les erreurs dans la base de données
