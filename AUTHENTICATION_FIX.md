# Correction du Système d'Authentification

## Problème
L'authentification ne fonctionnait pas correctement et ne redirigeait pas après le login.

## Solutions Implémentées

### 1. LoginFormAuthenticator Personnalisé
**Fichier:** `src/Security/LoginFormAuthenticator.php`

- Implémente `AbstractLoginFormAuthenticator`
- Gère l'authentification avec username/email et mot de passe
- Protection CSRF intégrée
- Support du "Remember Me"
- Redirection vers la homepage après succès

**Fonctionnalités:**
```php
- authenticate() : Valide les credentials
- onAuthenticationSuccess() : Redirige vers app_home
- getLoginUrl() : Retourne la route de login
```

### 2. UserProvider Personnalisé
**Fichier:** `src/Security/UserProvider.php`

- Permet la connexion par **username OU email**
- Implémente `UserProviderInterface`
- Charge l'utilisateur depuis la base de données

**Méthodes:**
```php
- loadUserByIdentifier() : Charge user par username ou email
- refreshUser() : Rafraîchit les données utilisateur
- supportsClass() : Vérifie le support de la classe User
```

### 3. UserRepository Amélioré
**Fichier:** `src/Repository/UserRepository.php`

**Nouvelles fonctionnalités:**
- Implémente `PasswordUpgraderInterface` pour le rehashing automatique
- Méthode `findByUsernameOrEmail()` pour recherche flexible
- Méthode `upgradePassword()` pour mise à jour sécurisée du mot de passe

```php
public function findByUsernameOrEmail(string $identifier): ?User
{
    return $this->createQueryBuilder('u')
        ->where('u.username = :identifier OR u.email = :identifier')
        ->setParameter('identifier', $identifier)
        ->getQuery()
        ->getOneOrNullResult();
}
```

### 4. SecurityController Mis à Jour
**Fichier:** `src/Controller/SecurityController.php`

**Changements:**
- Utilise `UserPasswordHasherInterface` au lieu de `password_hash()`
- Hachage sécurisé avec Symfony
- Redirection vers `app_home` au lieu de `app_dashboard`

**Avant:**
```php
$user->setPassword(password_hash($request->request->get('password'), PASSWORD_BCRYPT));
```

**Après:**
```php
$plaintextPassword = $request->request->get('password');
$hashedPassword = $passwordHasher->hashPassword($user, $plaintextPassword);
$user->setPassword($hashedPassword);
```

### 5. Configuration de Sécurité
**Fichier:** `config/packages/security.yaml`

**Changements principaux:**

```yaml
providers:
    app_user_provider:
        id: App\Security\UserProvider  # UserProvider personnalisé

firewalls:
    main:
        custom_authenticator: App\Security\LoginFormAuthenticator
        logout:
            target: app_home  # Redirection après logout
        remember_me:
            always_remember_me: true  # Remember me activé par défaut
```

## Flux d'Authentification

### Login
1. Utilisateur soumet le formulaire `/login`
2. `LoginFormAuthenticator` intercepte la requête
3. Vérifie le token CSRF
4. `UserProvider` charge l'utilisateur par username ou email
5. Vérifie le mot de passe
6. Crée le token d'authentification
7. Redirige vers `/` (homepage)

### Signup
1. Utilisateur remplit le formulaire `/signup/student` ou `/signup/tutor`
2. `SecurityController` crée l'entité User
3. Hash le mot de passe avec `UserPasswordHasherInterface`
4. Crée le profil associé (Student ou Tutor)
5. Persiste en base de données
6. Redirige vers `/login` avec message de succès

### Logout
1. Utilisateur clique sur logout
2. Session détruite
3. Redirection vers `/` (homepage)

## Avantages de Cette Implémentation

### Sécurité
✅ Hachage de mot de passe avec algorithme Symfony (bcrypt)
✅ Protection CSRF sur tous les formulaires
✅ Rehashing automatique des mots de passe
✅ Support du Remember Me sécurisé

### Flexibilité
✅ Connexion par username OU email
✅ UserProvider personnalisable
✅ Authenticator extensible

### Maintenabilité
✅ Code organisé et séparé
✅ Respect des standards Symfony
✅ Facile à tester
✅ Facile à étendre

## Test de l'Authentification

### 1. Créer un Compte
```
1. Aller sur /signup
2. Choisir "Student" ou "Tutor"
3. Remplir le formulaire
4. Cliquer sur "Create Account"
```

### 2. Se Connecter
```
1. Aller sur /login
2. Entrer username OU email
3. Entrer le mot de passe
4. Cocher "Remember me" (optionnel)
5. Cliquer sur "Login"
6. → Redirection vers la homepage (/)
```

### 3. Vérifier la Session
```
- La homepage affiche des boutons personnalisés selon le rôle
- L'utilisateur peut accéder à son dashboard
- Le menu affiche les options appropriées
```

### 4. Se Déconnecter
```
1. Cliquer sur "Logout"
2. → Redirection vers la homepage (/)
3. → Affichage de la version publique
```

## Commandes Utiles

### Nettoyer le Cache
```bash
php bin/console cache:clear
```

### Créer un Utilisateur en CLI (optionnel)
```bash
php bin/console doctrine:query:sql "INSERT INTO user (username, email, password, role, is_active) VALUES ('admin', 'admin@example.com', '\$2y\$13\$hashedpassword', 'ROLE_ADMIN', 1)"
```

### Vérifier la Configuration de Sécurité
```bash
php bin/console debug:firewall
php bin/console debug:security
```

## Dépannage

### Problème: "User not found"
**Solution:** Vérifier que l'utilisateur existe dans la base de données

### Problème: "Invalid credentials"
**Solution:** 
- Vérifier que le mot de passe est correct
- S'assurer que le hachage est fait avec UserPasswordHasher

### Problème: "CSRF token invalid"
**Solution:** 
- Nettoyer le cache
- Vérifier que le token est présent dans le formulaire

### Problème: Pas de redirection après login
**Solution:** 
- Vérifier que LoginFormAuthenticator est configuré
- Nettoyer le cache
- Vérifier les logs Symfony

## Prochaines Améliorations

1. **Limitation des tentatives de connexion**
   - Implémenter un système de rate limiting
   - Bloquer après X tentatives échouées

2. **Authentification à deux facteurs (2FA)**
   - Ajouter support TOTP
   - Email de vérification

3. **OAuth / Social Login**
   - Google
   - Facebook
   - GitHub

4. **Réinitialisation de mot de passe**
   - Email avec lien de réinitialisation
   - Token temporaire

5. **Vérification d'email**
   - Email de confirmation
   - Activation du compte
