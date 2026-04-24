.PHONY: install composer-install npm-install dev migrate seed test

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
