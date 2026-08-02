FROM wordpress:latest

RUN set -eux; \
	apt-get update; \
	apt-get install -y --no-install-recommends libsqlite3-dev; \
	rm -rf /var/lib/apt/lists/*; \
	docker-php-ext-install pdo_sqlite; \
	a2enmod headers
