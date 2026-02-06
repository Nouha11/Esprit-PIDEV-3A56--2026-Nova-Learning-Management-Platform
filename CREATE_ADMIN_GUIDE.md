# Guide: Créer un Compte Administrateur

## Méthode 1: Via Ligne de Commande (Recommandé)

### Créer un admin avec Symfony Console

Créez une commande Symfony pour créer un admin facilement:

```bash
php bin/console make:command app:create-admin
```

Ou utilisez directement la commande SQL:

```bash
php bin/console doctrine:query:sql "INSERT INTO user (username, email, password, role, is_active) VALUES ('admin', 'admin@nova.com', '\$2y\$13\$hashedpasswordhere', 'ROLE_ADMIN', 1)"
```

**Note:** Le mot de passe doit être haché. Utilisez la commande ci-dessous pour générer un hash.

### Générer un mot de passe haché

```bash
php bin/console security:hash-password
```

Entrez votre mot de passe souhaité, copiez le hash généré.

## Méthode 2: Via l'Interface Admin (Si vous avez déjà un admin)

1. Connectez-vous en tant qu'admin
2. Allez sur `/admin/users/new`
3. Remplissez le formulaire:
   - Username: `admin2`
   - Email: `admin2@nova.com`
   - Password: `votre_mot_de_passe`
   - Role: Sélectionnez **Administrator**
   - Cochez "Active Account"
4. Cliquez sur "Create User"

## Méthode 3: Via la Base de Données Directement

### Avec phpMyAdmin ou MySQL Workbench:

1. Ouvrez votre outil de gestion de base de données
2. Sélectionnez votre base de données
3. Exécutez cette requête SQL:

```sql
INSERT INTO user (username, email, password, role, is_active) 
VALUES (
    'admin',
    'admin@nova.com',
    '$2y$13$votre_hash_ici',
    'ROLE_ADMIN',
    1
);
```

### Générer le hash du mot de passe:

**Option A - Via PHP:**
```php
<?php
echo password_hash('votre_mot_de_passe', PASSWORD_BCRYPT);
```

**Option B - Via Symfony Console:**
```bash
php bin/console security:hash-password
```

## Méthode 4: Modifier un Compte Existant

Si vous avez déjà un compte (étudiant ou tuteur):

### Via SQL:
```sql
UPDATE user 
SET role = 'ROLE_ADMIN' 
WHERE username = 'votre_username';
```

### Via phpMyAdmin:
1. Ouvrez la table `user`
2. Trouvez votre utilisateur
3. Modifiez le champ `role` en `ROLE_ADMIN`
4. Sauvegardez

## Méthode 5: Créer une Commande Symfony (Meilleure pratique)

Créez un fichier: `src/Command/CreateAdminCommand.php`

```php
<?php

namespace App\Command;

use App\Entity\users\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create a new admin user',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        // Ask for username
        $usernameQuestion = new Question('Username: ');
        $username = $helper->ask($input, $output, $usernameQuestion);

        // Ask for email
        $emailQuestion = new Question('Email: ');
        $email = $helper->ask($input, $output, $emailQuestion);

        // Ask for password
        $passwordQuestion = new Question('Password: ');
        $passwordQuestion->setHidden(true);
        $password = $helper->ask($input, $output, $passwordQuestion);

        // Create admin user
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setRole('ROLE_ADMIN');
        $user->setIsActive(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('Admin user created successfully!');
        $io->table(
            ['Username', 'Email', 'Role'],
            [[$username, $email, 'ROLE_ADMIN']]
        );

        return Command::SUCCESS;
    }
}
```

Ensuite, exécutez:
```bash
php bin/console app:create-admin
```

## Vérification

Après avoir créé votre admin, vérifiez:

1. **Connectez-vous** sur `/login`
2. **Vérifiez l'accès** à `/admin`
3. **Vous devriez voir** le dashboard admin avec toutes les fonctionnalités

## Rôles Disponibles

- `ROLE_ADMIN` - Accès complet à l'administration
- `ROLE_STUDENT` - Accès étudiant
- `ROLE_TUTOR` - Accès tuteur
- `ROLE_USER` - Accès utilisateur basique

## Dépannage

### "Access Denied"
- Vérifiez que le rôle est bien `ROLE_ADMIN` (pas `admin` ou `ADMIN`)
- Vérifiez que `is_active` est à `1`
- Déconnectez-vous et reconnectez-vous

### "Invalid credentials"
- Vérifiez que le mot de passe est bien haché
- Utilisez `php bin/console security:hash-password` pour générer un nouveau hash

### "User not found"
- Vérifiez que l'utilisateur existe dans la table `user`
- Vérifiez l'orthographe du username/email
