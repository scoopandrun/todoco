# Path: Makefile

.PHONY: help install test fixtures db clean

.DEFAULT_GOAL = help

CURRENT_DIR = $(shell pwd)

FIXTURES = 1


help: ## Display this help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

vendor: composer.json composer.lock
	composer install

composer.lock: composer.json
	composer update

db:
	php bin/console doctrine:database:drop --force --if-exists
	php bin/console doctrine:database:create
	php bin/console doctrine:migrations:migrate --no-interaction
ifeq ($(FIXTURES),1)
	php bin/console doctrine:fixtures:load --no-interaction
endif

install: vendor db ## Install the project (skip fixtures with FIXTURES=0)
	@echo "Project installed"

fixtures: ## Reset the database and load fixtures
	@echo "Resetting the database"
	php bin/console doctrine:database:drop --force --if-exists
	php bin/console doctrine:database:create
	php bin/console doctrine:migrations:migrate --no-interaction
	@echo "Loading the fixtures"
	php bin/console doctrine:fixtures:load --no-interaction
	@echo "Clearing the cache"
	php bin/console cache:clear
	@echo "Done"

test: ## Launch unit tests
	php bin/console --env=test doctrine:database:drop --force --if-exists
	php bin/console --env=test doctrine:database:create
	php bin/console --env=test doctrine:schema:create
	php bin/console --env=test doctrine:fixtures:load --no-interaction
	php bin/phpunit --coverage-html public/tests --testdox

test-nocoverage: ## Launch unit tests without coverage
	php bin/console --env=test doctrine:database:drop --force --if-exists
	php bin/console --env=test doctrine:database:create
	php bin/console --env=test doctrine:schema:create
	php bin/console --env=test doctrine:fixtures:load --no-interaction
	php bin/phpunit --testdox

clean:
	php bin/console doctrine:database:drop --force --if-exists
	rm -rf var/cache/*
	rm -rf var/logs/*
	rm -rf vendor
