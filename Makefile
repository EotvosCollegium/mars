build:
	npm install
	composer install
	npm run dev
	cp .env.example .env
	php artisan key:generate
	php artisan migrate

seed:
	php artisan db:seed

serve:
	php artisan serve --host=0.0.0.0