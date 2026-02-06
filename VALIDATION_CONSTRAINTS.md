# Contraintes de Validation (Contrôle de Saisie)

## Vue d'ensemble
Ajout de contraintes de validation complètes aux entités User, StudentProfile et TutorProfile en utilisant Symfony Validator.

## User Entity

### Email
- **NotBlank**: Email obligatoire
- **Email**: Format email valide requis
- **Length**: Maximum 180 caractères
- **UniqueEntity**: Email unique dans la base de données

### Password
- **NotBlank**: Mot de passe obligatoire
- **Length**: Minimum 8 caractères, maximum 255 caractères

### Username
- **NotBlank**: Nom d'utilisateur obligatoire
- **Length**: Minimum 3 caractères, maximum 100 caractères
- **Regex**: Seulement lettres, chiffres et underscores (a-zA-Z0-9_)
- **UniqueEntity**: Nom d'utilisateur unique dans la base de données

### Role
- **NotBlank**: Rôle obligatoire
- **Choice**: Doit être l'un de: ROLE_ADMIN, ROLE_STUDENT, ROLE_TUTOR, ROLE_USER

### IsActive
- **NotNull**: Statut actif obligatoire
- **Type**: Doit être un booléen (true/false)

## StudentProfile Entity

### FirstName
- **NotBlank**: Prénom obligatoire
- **Length**: Minimum 2 caractères, maximum 100 caractères
- **Regex**: Seulement lettres, espaces et tirets (supporte les accents)

### LastName
- **NotBlank**: Nom obligatoire
- **Length**: Minimum 2 caractères, maximum 100 caractères
- **Regex**: Seulement lettres, espaces et tirets (supporte les accents)

### Bio
- **Length**: Maximum 1000 caractères
- Optionnel (nullable)

### University
- **NotBlank**: Université obligatoire
- **Length**: Minimum 3 caractères, maximum 100 caractères

### Major
- **Length**: Maximum 100 caractères
- Optionnel (nullable)

### AcademicLevel
- **Choice**: Doit être l'un de: Freshman, Sophomore, Junior, Senior, Graduate, PhD
- Optionnel (nullable)

### ProfilePicture
- **Length**: Maximum 255 caractères
- Optionnel (nullable)

### Interests
- **Length**: Maximum 500 caractères
- Optionnel (nullable)

## TutorProfile Entity

### FirstName
- **NotBlank**: Prénom obligatoire
- **Length**: Minimum 2 caractères, maximum 100 caractères
- **Regex**: Seulement lettres, espaces et tirets (supporte les accents)

### LastName
- **NotBlank**: Nom obligatoire
- **Length**: Minimum 2 caractères, maximum 100 caractères
- **Regex**: Seulement lettres, espaces et tirets (supporte les accents)

### Bio
- **Length**: Maximum 1000 caractères
- Optionnel (nullable)

### Expertise
- **NotBlank**: Expertise obligatoire
- **Length**: Minimum 10 caractères, maximum 1000 caractères

### Qualifications
- **Length**: Maximum 1000 caractères
- Optionnel (nullable)

### YearsOfExperience
- **NotBlank**: Années d'expérience obligatoires
- **Type**: Doit être un entier
- **Range**: Entre 0 et 50 ans

### HourlyRate
- **Positive**: Doit être un nombre positif
- **Range**: Entre 0 et 1000
- Optionnel (nullable)

### IsAvailable
- **NotNull**: Disponibilité obligatoire
- **Type**: Doit être un booléen (true/false)

### ProfilePicture
- **Length**: Maximum 255 caractères
- Optionnel (nullable)

## Utilisation dans les Contrôleurs

Pour valider les entités dans vos contrôleurs, vous pouvez utiliser le ValidatorInterface:

```php
use Symfony\Component\Validator\Validator\ValidatorInterface;

public function create(Request $request, ValidatorInterface $validator): Response
{
    $student = new StudentProfile();
    // ... set properties
    
    $errors = $validator->validate($student);
    
    if (count($errors) > 0) {
        // Handle validation errors
        foreach ($errors as $error) {
            $this->addFlash('error', $error->getMessage());
        }
        return $this->redirectToRoute('form_page');
    }
    
    // Save entity
}
```

## Messages d'Erreur

Tous les messages d'erreur sont en anglais et peuvent être traduits en utilisant le système de traduction de Symfony.

### Exemples de messages:
- "Email is required"
- "Please enter a valid email address"
- "This email is already registered"
- "Password must be at least 8 characters"
- "Username can only contain letters, numbers and underscores"
- "First name can only contain letters, spaces and hyphens"
- "Years of experience must be between 0 and 50"

## Prochaines Étapes Recommandées

1. **Créer des Form Types**: Utiliser les Form Types Symfony pour une validation automatique
2. **Traduction**: Traduire les messages d'erreur en français
3. **Validation Personnalisée**: Ajouter des validateurs personnalisés si nécessaire
4. **Tests**: Écrire des tests unitaires pour valider les contraintes
5. **Frontend**: Ajouter une validation côté client (JavaScript) pour une meilleure UX
6. **API**: Implémenter la validation pour les endpoints API
