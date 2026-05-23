FROM php:8.2-cli

RUN apt-get update && apt-get install -y libsqlite3-dev && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pdo_sqlite

WORKDIR /app
COPY . .

RUN php database/migrate.php

EXPOSE 3000

CMD ["php", "-S", "0.0.0.0:3000", "-t", "/app"]
