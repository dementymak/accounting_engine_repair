# Engine Repair Management System
# Система управління ремонтом двигунів

## Description | Опис

Engine repair management system with wire inventory tracking and repair card management.

Система управління ремонтом двигунів з відстеженням інвентаря дроту та управлінням ремонтними картками.

## Requirements | Вимоги

- PHP 8.1 or higher | PHP 8.1 або вище
- Composer
- MySQL 5.7 or higher | MySQL 5.7 або вище
- Node.js & NPM

## Installation | Встановлення

1. Clone the repository | Клонуйте репозиторій
```bash
git clone https://github.com/dementymak/accounting_engine_repair.git
cd accounting_engine_repair
```

2. Install PHP dependencies | Встановіть залежності PHP
```bash
composer install
```

3. Install NPM dependencies | Встановіть залежності NPM
```bash
npm install
```

4. Create environment file | Створіть файл середовища
```bash
cp .env.example .env
```

5. Generate application key | Згенеруйте ключ додатку
```bash
php artisan key:generate
```

6. Configure database in .env | Налаштуйте базу даних в .env
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

7. Run migrations and seeders | Запустіть міграції та сідери
```bash
php artisan migrate --seed
```

8. Build assets | Збудуйте ассети
```bash
npm run build
```

9. Start the development server | Запустіть сервер розробки
```bash
php artisan serve
```

## Default Admin Account | Обліковий запис адміністратора за замовчуванням

- Email: admin@example.com
- Password: password

## Features | Функціонал

- Repair Card Management | Управління ремонтними картками
- Wire Inventory Tracking | Відстеження інвентаря дроту
- Scrap Management | Управління відходами
- Multi-language Support (English, Ukrainian, Polish) | Багатомовна підтримка (англійська, українська, польська)

## Production Deployment | Розгортання на продакшені

1. Set production environment | Встановіть продакшн-середовище
```
APP_ENV=production
APP_DEBUG=false
```

2. Optimize application | Оптимізуйте додаток
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

3. Set proper permissions | Встановіть правильні дозволи
```bash
chmod -R 755 storage bootstrap/cache
```

## Security | Безпека

Remember to properly secure your production environment:
Не забудьте правильно захистити ваше продакшн-середовище:

- Use HTTPS | Використовуйте HTTPS
- Set secure headers | Встановіть захищені заголовки
- Configure proper backup | Налаштуйте правильне резервне копіювання
- Keep all dependencies updated | Тримайте всі залежності оновленими

## Support | Підтримка

For support, please email dementymak@gmail.com 