.PHONY: up down build install test test-unit test-integration

# Docker commands
up:
	docker-compose up -d

down:
	docker-compose down

build:
	docker-compose build

install:
	docker-compose exec app composer install

# Symfony commands
console:
	docker-compose exec app php bin/console $(cmd)

cache-clear:
	docker-compose exec app php bin/console cache:clear

# Messenger commands
consume-messages:
	docker-compose exec app php bin/console messenger:consume async

# Test commands
test:
	docker-compose exec app php bin/phpunit

test-unit:
	docker-compose exec app php bin/phpunit --testsuite=Unit

test-integration:
	docker-compose exec app php bin/phpunit --testsuite=Integration
