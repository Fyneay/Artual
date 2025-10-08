FROM php:8.3-fpm
LABEL authors="Savr"
WORKDIR /var/www
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libgd-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    libldap2-dev \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libpq-dev \
    libonig-dev \
    libzip-dev
RUN apt-get clean && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring gd zip exif pcntl ldap
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY . /var/www
RUN composer install --no-dev --optimize-autoloader

RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www
RUN chmod -R 777 .
RUN chmod -R 777 MCP
COPY --chown=www:www . /var/www
RUN sed -i -e 's/\r$//' MCP/laravel-boost.sh && chmod +x MCP/laravel-boost.sh
USER www
EXPOSE 9000
ENTRYPOINT ["bash","MCP/laravel-boost.sh"]
CMD ["php-fpm"]
