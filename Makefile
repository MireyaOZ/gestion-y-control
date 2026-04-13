USER_ID ?= $(shell id -u)
GROUP_ID ?= $(shell id -g)


.PHONY: install composer-install npm-install dev migrate seed test docker-up docker-down docker-build docker-composer-install docker-npm-install docker-migrate docker-seed docker-test

install:
	composer install
	npm install

composer-install:
	composer install

npm-install:
	npm install

dev:
	npm run dev

migrate:
	php artisan migrate

seed:
	php artisan db:seed

test:
	php artisan test

docker-up:
	USER_ID=$(USER_ID) GROUP_ID=$(GROUP_ID) docker compose up -d --build


docker-down:
	docker compose down


docker-build:
	USER_ID=$(USER_ID) GROUP_ID=$(GROUP_ID) docker compose build


docker-composer-install:
	USER_ID=$(USER_ID) GROUP_ID=$(GROUP_ID) docker compose run --rm app composer install


docker-npm-install:
	USER_ID=$(USER_ID) GROUP_ID=$(GROUP_ID) docker compose run --rm node bash -lc "npm install"


docker-migrate:
	USER_ID=$(USER_ID) GROUP_ID=$(GROUP_ID) docker compose run --rm app php artisan migrate


docker-seed:
	USER_ID=$(USER_ID) GROUP_ID=$(GROUP_ID) docker compose run --rm app php artisan db:seed


docker-test:
	USER_ID=$(USER_ID) GROUP_ID=$(GROUP_ID) docker compose run --rm app php artisan test
