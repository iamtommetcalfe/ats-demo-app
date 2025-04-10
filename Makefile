.PHONY: setup migrate seed dev fresh

setup:
	docker compose build
	docker compose up -d
	docker compose exec app composer install
	docker compose exec app npm install
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan migrate --seed

migrate:
	docker compose exec app php artisan migrate

seed:
	docker compose exec app php artisan db:seed

dev:
	docker compose exec app npm run dev

fresh:
	docker compose exec app php artisan migrate:fresh --seed
