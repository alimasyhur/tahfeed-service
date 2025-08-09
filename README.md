<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Migration
```
php artisan migrate:refresh
```


## Seeder
Run Seeder to setup initial data
```
php artisan db:seed InitSeeder 
```

## Setup File Upload Configuration
Create storage link:
```
php artisan storage:link
```

Set permissions:
```
sudo chmod -R 775 storage/app/public/
sudo chown -R www-data:www-data storage/
```

Clear caches:
```
php artisan config:clear
php artisan cache:clear
```

Clear All Caches:
```
# Clear application cache
php artisan cache:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Clear compiled services and packages
php artisan clear-compiled

php artisan optimize
```


