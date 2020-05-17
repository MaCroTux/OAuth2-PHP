help: ## List a command allowed
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

generate_keys: ## Generate ssl keys
	docker-compose exec server bash -c "openssl genrsa -out private.key 2048"
	docker-compose exec server bash -c "openssl rsa -in private.key -pubout -out public.key"

all: Dockerfile generate_keys ## Up containers
	docker-compose up -d server

up: Dockerfile src public app ## Down containers
	docker-compose up -d server
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

