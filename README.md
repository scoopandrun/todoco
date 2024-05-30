# ToDo & Co

[![SymfonyInsight](https://insight.symfony.com/projects/5745b6bc-f698-4abe-9618-404d1da53406/big.svg)](https://insight.symfony.com/projects/5745b6bc-f698-4abe-9618-404d1da53406)

This project is a todo-list exercise for the PHP course at OpenClassrooms.
It is not meant to be used in production nor is it meant to be a showcase.

This repository is primarily a way of sharing code with the tutor.

## Installation

This project uses [Composer](https://getcomposer.org) with PHP `>= 5.6 && < 7.0`.

Configure your database and email server in `/app/config/parameters.yml`.

Clone and install the project.

```shell
# Clone the repository
git clone https://github.com/scoopandrun/ocp8
cd ocp8

# Install the project (with fixtures, default)
make install
# Without fixtures
make install FIXTURES=0
```

Alternatively, you can decompose the steps as follows

```shell
# Install the dependencies
composer install

# Create your database
php bin/console doctrine:database:create

# Execute the migrations
php bin/console doctrine:migrations:migrate

# (Recommended) Load the fixtures to get a starting data set.
php bin/console doctrine:fixtures:load
```

## Default users

By default, the fixtures create 2 permanent users :

- User 1
  - email: user1<span>@</span>example.com
  - password: "password"
- User 2
  - email: user2<span>@</span>example.com
  - password: "password"

You can update the initial users information in the User data fixture (`src/DataFixtures/UserFixtures.php`).
