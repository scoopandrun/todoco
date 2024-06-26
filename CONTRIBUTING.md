# Comment contribuer

Cette page décrit les règles de contribution pour le projet ToDo & Co.

Afin d'assurer une collaboration fluide, veuillez suivre ces directives :

1. **Clonez le dépôt** : Clonez le dépôt sur votre machine locale en utilisant la commande suivante :

```shell
$ git clone https://github.com/scoopandrun/ocp8.git
```

2. **Créez un issue**: Créez un issue pour décrire le problème rencontré ou la fonctionnalité à implémenter.

3. **Créez une nouvelle branche** : Créez une nouvelle branche pour votre contribution en utilisant un nom descriptif. Cela nous aidera à comprendre le but de vos modifications.

4. **Effectuez vos modifications** : Implémentez vos modifications ou ajoutez de nouvelles fonctionnalités au projet. Assurez-vous que votre code respecte nos normes de codage et conventions.

5. **Testez vos modifications** : Avant de soumettre votre contribution, assurez-vous de tester vos modifications en profondeur pour vous assurer qu'elles fonctionnent comme prévu. [Voir ci-dessous](#tests).

6. **Commit et push** : Une fois satisfait de vos modifications, committez-les avec un message de commit clair et concis. Ensuite, poussez vos modifications.

7. **Créez une pull request** : Rendez-vous sur le dépôt et créez une pull request pour la branche comportant les modifications. Fournissez une description détaillée de vos modifications et de toute information pertinente.

8. **Revue** : La pull request peut être examinée et commentée par l'équipe si nécessaire. Merci de contribuer à une discussion constructive sur les problèmes rencontrés et les corrections à apporter.

9. **Fusion** : Une fois votre pull request approuvée, elle sera fusionnée dans la branche principale.

## Intégration Continue

L'outil GrumPHP est installé et configuré dans le dépôt.  
Lors de chaque commit, GrumPHP lancera PHPStan et PHPUnit pour s'assurer de la qualité du code. Toute erreur empêchera la validation du commit.

Vous pouvez lancer ces outils individuellement à la demande (voir ci-dessous).

GrumPHP peut être lancé manuellement avec la commande suivante :

```shell
$ vendor/bin/grumphp run
```

## Tests

Lorsque vous apportez des modifications, veuillez vous assurer qu'elles sont couvertes par des tests unitaires et/ou fonctionnels (tous les tests se trouvent dans le répertoire `tests`).

Exécutez la suite de tests avec la commande suivante :

```shell
# Tests avec rapport de couverture de code
$ make test

# ou

# Tests sans rapport de couverture de code
$ make test-nocoverage

# ou

$ bin/phpunit
```

Veuillez noter que `make test[-nocoverage]` se charge de réinitialiser la base de données de test avant chaque exécution du test.  
Cependant, vous ne pouvez pas passer d'options PHPUnit à cette commande. Si vous avez besoin de passer des options (par exemple : `--filter=nomDeMonTest`), vous devez utiliser `bin/phpunit --filter=nomDeMonTest`.

## PHPStan

Merci d'utiliser PHPStan pour vous assurer de la syntaxe de votre code avant de faire un commit.

```shell
$ vendor/bin/phpstan
```

## Conventions de codage

Le code doit être lisible et, si nécessaire, documenté.

Veuillez respecter les conventions suivantes :

- Le code doit respecter [PSR-1](https://www.php-fig.org/psr/psr-1) et [PSR-12](https://www.php-fig.org/psr/psr-12).
- L'indentation utilise 4 espaces (conformément à la règle [PSR-12](https://www.php-fig.org/psr/psr-12/#24-indenting)).
- Tous les noms de variables et de fonctions doivent utiliser la notation `camelCase`.
- Utilisez des noms descriptifs pour les variables et les fonctions. Mieux vaut long que cryptique.
- Les routes utilisent la convention suivante :
  - nom du contrôleur : un mot descriptif se référent à la section gérée
  - nom de méthode : court mais descriptif quant à l'action gérée
  - path : doit reprendre le nom du contrôleur (à l'exception du contrôleur `Security`)
  - name : _nom-du-contrôleur_._nom-de-la-méthode_ (ex : `homepage.index`)
- Templates :
  - l'arborescence doit reproduire l'arborescence des contrôleurs
  - l'extension des templates doit être `.html.twig`
  - les noms composés utilisent le kebab-case
  - le noms des templates partiels doit commencer par un underscore
  - les templates de Turbo Stream doivent commencer par un underscore (ce sont des partiels) et avoir l'extension `.stream.html.twig` (ex: `_action.stream.html.twig`)
  - dans la mesure du possible, les template de création et de modification doivent être combiné en un seul template

Merci !
