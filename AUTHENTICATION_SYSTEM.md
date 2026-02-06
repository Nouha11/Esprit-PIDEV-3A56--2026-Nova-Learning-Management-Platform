# Système d'Authentification - Login & Signup

## Vue d'ensemble
Système complet d'authentification avec choix de rôle (Étudiant ou Tuteur) lors de l'inscription.

## Routes Disponibles

### Pages Publiques
- **`/login`** - Page de connexion
- **`/signup`** - Choix du rôle (Étudiant ou Tuteur)
- **`/signup/student`** - Inscription étudiant
- **`/signup/tutor`** - Inscription tuteur
- **`/logout`** - Déconnexion

### Pages Protégées
- **`/dashboard`** - Redirection automatique selon le rôle
- **`/admin/*`** - Accès ROLE_ADMIN uniquement
- **`/student/*`** - Accès ROLE_STUDENT uniquement
- **`/tutor/*`** - Accès ROLE_TUTOR uniquement
- **`/user/*`** - Accès utilisateurs authentifiés

## Flux d'Inscription

### 1. Page de Choix (/signup)
- Interface avec 2 cartes : Étudiant et Tuteur
- Design moderne avec icônes SVG
- Description des avantages de chaque rôle

### 2. Inscription Étudiant (/signup/student)
**Informations de compte:**
- Username (min 3 caractères)
- Email (format valide)
- Password (min 8 caractères)
- Confirmation du mot de passe

**Informations personnelles:**
- Prénom (min 2 caractères)
- Nom (min 2 caractères)
- Université (obligatoire)
- Spécialité (optionnel)
- Niveau académique (Freshman, Sophomore, Junior, Senior, Graduate, PhD)

**Rôle attribué:** ROLE_STUDENT

### 3. Inscription Tuteur (/signup/tutor)
**Informations de compte:**
- Username (min 3 caractères)
- Email (format valide)
- Password (min 8 caractères)
- Confirmation du mot de passe

**Informations personnelles:**
- Prénom (min 2 caractères)
- Nom (min 2 caractères)
- Expertise (min 10 caractères, obligatoire)
- Qualifications (optionnel)
- Années d'expérience (0-50)
- Tarif horaire (optionnel)

**Rôle attribué:** ROLE_TUTOR
**Disponibilité:** Activée par défaut

## Flux de Connexion

### Page de Login (/login)
- Connexion par username ou email
- Mot de passe
- Option "Se souvenir de moi" (7 jours)
- Protection CSRF
- Affichage des erreurs d'authentification
- Lien vers l'inscription

### Après Connexion (/dashboard)
Redirection automatique selon le rôle:
- **ROLE_ADMIN** → `/admin/users`
- **ROLE_STUDENT** → `/student/dashboard`
- **ROLE_TUTOR** → `/tutor/dashboard`
- **Autre** → `/user/dashboard`

## Sécurité Implémentée

### Hachage des Mots de Passe
- Algorithme: bcrypt
- Hachage automatique lors de l'inscription
- Stockage sécurisé dans la base de données

### Protection CSRF
- Token CSRF sur le formulaire de login
- Token CSRF sur les actions sensibles

### Validation des Données
- Validation côté serveur (Symfony Validator)
- Validation côté client (JavaScript)
- Vérification de la correspondance des mots de passe
- Contraintes de longueur et format

### Contrôle d'Accès
```yaml
access_control:
    - { path: ^/login, roles: PUBLIC_ACCESS }
    - { path: ^/signup, roles: PUBLIC_ACCESS }
    - { path: ^/admin, roles: ROLE_ADMIN }
    - { path: ^/student, roles: ROLE_STUDENT }
    - { path: ^/tutor, roles: ROLE_TUTOR }
    - { path: ^/user, roles: IS_AUTHENTICATED_FULLY }
```

### Remember Me
- Cookie sécurisé
- Durée: 7 jours (604800 secondes)
- Secret: utilise kernel.secret

## Entité User

### Implémente les Interfaces
- `UserInterface` - Interface Symfony pour l'authentification
- `PasswordAuthenticatedUserInterface` - Pour le hachage de mot de passe

### Méthodes Requises
```php
public function getRoles(): array
public function eraseCredentials(): void
public function getUserIdentifier(): string
```

### Propriétés
- `id` - Identifiant unique
- `username` - Nom d'utilisateur (unique)
- `email` - Email (unique)
- `password` - Mot de passe haché
- `role` - Rôle (ROLE_ADMIN, ROLE_STUDENT, ROLE_TUTOR, ROLE_USER)
- `isActive` - Statut actif/inactif

## Configuration Symfony

### security.yaml
- Provider: `app_user_provider` (Entity User)
- Firewall: `main` avec form_login
- Password hasher: bcrypt pour User
- Remember me activé

### User Provider
```yaml
providers:
    app_user_provider:
        entity:
            class: App\Entity\users\User
            property: username
```

## Templates Créés

1. **templates/security/signup_choice.html.twig**
   - Page de choix du rôle
   - Design avec cartes Bootstrap
   - Icônes SVG

2. **templates/security/login.html.twig**
   - Formulaire de connexion
   - Gestion des erreurs
   - Remember me

3. **templates/security/signup_student.html.twig**
   - Formulaire d'inscription étudiant
   - Validation JavaScript
   - Design responsive

4. **templates/security/signup_tutor.html.twig**
   - Formulaire d'inscription tuteur
   - Validation JavaScript
   - Design responsive

## Fonctionnalités

### Validation JavaScript
- Vérification de la correspondance des mots de passe
- Alerte en cas de non-correspondance
- Empêche la soumission du formulaire

### Messages Flash
- Succès: "Account created successfully! Please login."
- Erreur: Affichage des erreurs d'inscription
- Erreur de login: Affichage des erreurs d'authentification

### Redirection Intelligente
- Utilisateurs connectés redirigés depuis /login et /signup
- Redirection automatique après login selon le rôle
- Redirection vers login si non authentifié

## Utilisation

### Pour Tester

1. **Créer un compte étudiant:**
   - Aller sur `/signup`
   - Cliquer sur "Sign Up as Student"
   - Remplir le formulaire
   - Se connecter sur `/login`

2. **Créer un compte tuteur:**
   - Aller sur `/signup`
   - Cliquer sur "Sign Up as Tutor"
   - Remplir le formulaire
   - Se connecter sur `/login`

3. **Se connecter:**
   - Aller sur `/login`
   - Entrer username/email et mot de passe
   - Redirection automatique vers le dashboard approprié

## Prochaines Étapes Recommandées

1. **Lier User aux Profils:**
   - Ajouter relation OneToOne entre User et StudentProfile
   - Ajouter relation OneToOne entre User et TutorProfile

2. **Email de Vérification:**
   - Envoyer email de confirmation
   - Vérifier l'email avant activation

3. **Réinitialisation de Mot de Passe:**
   - Formulaire "Mot de passe oublié"
   - Email avec lien de réinitialisation

4. **Améliorer la Sécurité:**
   - Limiter les tentatives de connexion
   - Captcha sur les formulaires
   - Authentification à deux facteurs

5. **Profil Utilisateur:**
   - Page de profil complète
   - Upload de photo de profil
   - Modification des informations

6. **Tests:**
   - Tests unitaires pour l'authentification
   - Tests fonctionnels pour les formulaires
