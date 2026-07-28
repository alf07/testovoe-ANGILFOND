# Базовый образ PHP CLI (используем 8.4, но вы можете заменить на 8.3/8.2)
FROM php:8.4-cli

# Аргументы сборки (можно переопределить при сборке)
ARG REPO_URL=https://github.com/alf07/testovoe-ANGILFOND.git
ARG BRANCH=main
ARG APP_ENV=local
ARG APP_DEBUG=true

# Установка системных пакетов, git и расширений PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Клонирование репозитория
RUN git clone --branch ${BRANCH} --depth 1 ${REPO_URL} /var/www/html

# Рабочая директория
WORKDIR /var/www/html

# Копируем .env.example в .env (если есть), иначе создаём
RUN if [ -f .env.example ]; then cp .env.example .env; else touch .env; fi

# Установка зависимостей Composer (без dev для продакшена, но оставляем dev для разработки)
RUN composer install --no-interaction --optimize-autoloader

# Генерация ключа приложения
RUN php artisan key:generate --force

# Настройка прав на storage и bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Открываем порт 8000 для встроенного сервера
EXPOSE 8000

# Запуск сервера Laravel на всех интерфейсах
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]