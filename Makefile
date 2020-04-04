up:
	@echo '************  Create ajudaime network ************'
	@echo '*'
	@echo '*'
	docker network inspect ajudaime_network >/dev/null 2>&1 || docker network create ajudaime_network

	@echo '************  Waking UP Containers ************'
	@echo '*'
	@echo '*'
	docker-compose up -d

	@echo '*'
	@echo '*'
	@echo '************  Configuring env ************'
	@echo '*'
	@echo '*'
	cp .env.dist .env
	@echo '*'
	@echo '*'
	@echo '************  Starting API ************'
	@echo '*'
	@echo '*'
	@echo '************  Installing symfony ************'
	docker exec -it ajudaime-php composer install
	@echo '*'
	@echo '*'
	@echo '************  Create database ************'
	docker exec -it ajudaime-php php bin/console doctrine:database:create --env=dev
	docker exec -it ajudaime-php php bin/console doctrine:database:create --env=test
	@echo '************  Running migrations ************'
	docker exec -it ajudaime-php php bin/console doctrine:migration:migrate --env=dev
	@echo '************  Running migrations ************'
	docker exec -it ajudaime-php php bin/console doctrine:migration:migrate --env=test
	@echo '*'
	@echo '*'
	@echo '*'
	@echo '************  Running tests  ************'
	docker exec -it ajudaime-php ./vendor/bin/simple-phpunit
