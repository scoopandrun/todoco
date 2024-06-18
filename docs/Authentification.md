# Authentification

L'application ToDo & Co utilise une authentification pour gérer l'accès aux différentes parties de l'application.

## Accès aux parties de l'application

L'intégralité de l'application n'est accessible qu'aux utilisateurs connectés, à l'exception des pages de création de compte (`/signup`) et de connexion (`/login`).

Cette configuration peut être modifiée dans la configuration du composant sécurité de Symfony.

```yaml
# config/packages/security.yaml

security:
  access_control:
    - { path: ^/signup, roles: PUBLIC_ACCESS }
    - { path: ^/login, roles: PUBLIC_ACCESS }
    - { path: ^/, roles: ROLE_USER }
```

## Rôles

L'application utilise 2 rôles pour les utilisateurs : _ROLE_USER_ et _ROLE_ADMIN_.

Ces rôles sont enregistrés en base de données dans le champ `roles` (JSON).

Lors de la création d'un compte par un visiteur, le rôle _ROLE_USER_ lui est attribué.  
Un administrateur a la possibilité d'attribuer le rôle _ROLE_USER_ ou _ROLE_ADMIN_ lorsqu'il/elle crée ou modifie un compte utilisateur.

### _ROLE_USER_

Le rôle _ROLE_USER_ est le rôle de base d'un utilisateur connecté.  
Il permet l'accès aux parties non-administratives de l'application (notamment, la création et la modification de tâches).

### _ROLE_ADMIN_

Le rôle _ROLE_ADMIN_ est le rôle des administrateurs.  
Il permet l'accès aux parties réservées, notamment la gestion des utilisateurs (`/users`).  
Il permet aussi la gestion complète des tâches (supression de toutes les tâches).

### Voters

En dehors de la configuration de `security.yaml`, les autorisations au sein de l'application se font grâce aux voters (`App\Security\Voter`).

Les voters permettent notamment de déterminer qui peut créer/modifier/supprimer des tâches (`TaskVoter`) ou agir sur les comptes utilisateurs (`UserVoter`).

## Enregistrement des utilisateurs

Les visiteurs peuvent créer un compte pour eux-même via la route `/signup` (contrôleur `SecurityController::signup`, template `templates/security/signup.html.twig`).  
Les administrateurs peuvent également créer des comptes pour des tiers via la route `/users/create` (contrôleur `UserController::create`, template `templates/user/edit.html.twig`).

Les utilisateurs sont représentés par l'entité `App\Entity\User` et enregistrés en base de données dans la table `user`.

Une contrainte d'unicité existe sur le nom d'utilisateur et l'adresse e-mail.

```php
// src/Entity/User.php

#[UniqueEntity(fields: ['email'], message: 'Cette adresse email est déjà utilisée.')]
#[UniqueEntity(fields: ['username'], message: "Ce nom d'utilisateur est déjà utilisé.")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
}
```

## Connexion des utlisateurs

L'application ToDo & Co utilise une authentification classique par formulaire (nom d'utilisateur + mot de passe).  
La route qui permet de s'authentifier est `/login` (contrôleur `SecurityController::login`, template `templates/security/login.html.twig`).

## Mot de passe

Une série de contraintes est appliquée sur le mot de passe de l'utilisateur lors de la création d'un compte ou de la mise à jour d'un mot de passe.  
Ces contraintes sont gérées dans `App\Validator\Constraints\PasswordRequirements`.

Il est notamment possible de modifier le nombre de caractères minimum, la force minimum du mot de passe ainsi que d'autres règles.
