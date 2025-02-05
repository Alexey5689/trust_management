# Trust management

Доверительное упрвление

## Requirements

-   PHP v8.3

## Запуск

1. Установите зависимости композера: `composer install`
2. Скопируйте `.env.example` из корня в файл `.env` при помощи `cp .env.example .env`
3. Поднимите все контейнеры при помощи `./vendor/bin/sail up -d`
4. Выполните `./vendor/bin/sail artisan key:generate` и `php artisan jwt:secret` для генерации ключей приложения
5. Установите зависимости `./vendor/bin/sail npm i`
6. Выполните `./vendor/bin/sail npm run dev`

## Миграции

`./vendor/bin/sail artisan migrate --seed`
