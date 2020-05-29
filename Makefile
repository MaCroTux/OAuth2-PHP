help: ## List a command allowed
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

all: Dockerfile composer-install ## Up containers
	export XDEBUG_HOST=0.0.0.0; docker-compose up -d server

composer-install:
	docker run --rm -it -v $(PWD):/app composer install

up: Dockerfile src public app ## Down containers
	export XDEBUG_HOST=0.0.0.0; docker-compose up -d server
	docker-compose exec server bash -c "chown www-data:www-data /var/www/html/logs -R"

down: Dockerfile src public app ## Up containers
	docker-compose down

restart: down up  ## Reset containers

reset-apache: Dockerfile src public app  ## Reset server
	docker-compose exec server apache2ctl -t

composer: ## Instalar Composer
	docker run --rm \
		--volume ${PWD}/:/app \
		clevyr/prestissimo:latest \
		install --ignore-platform-reqs

