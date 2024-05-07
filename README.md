# Stock Price Tracker
This is a backend application written in PHP using the Laravel framework. It integrates with the Alpha Vantage API to collect and aggregate real-time stock price data.

## Features
- [Laravel 11.x](https://laravel.com/docs/11.x/)
- [Alpha Vantage API](https://www.alphavantage.co/documentation/)

## Installation
- Local server environment (e.g., XAMPP, WAMP, MAMP) for PHP development.
- Install [Composer](https://getcomposer.org/).
- Install Laravel via Composer.
```bash
composer create-project laravel/laravel stock-price-tracker
```

## Application setup
- Make sure your local server environment is running.
- Configure your [database](https://laravel.com/docs/11.x/database) connection in the .env file.
```plaintext
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_username
   DB_PASSWORD=your_database_password
```
- Install [Guzzle](https://github.com/guzzle/guzzle) via Composer. This will allow us to fetch Alpha Vantage API data.
```bash
composer require guzzlehttp/guzzle
```
- Configure your .env to contain the Alpha Vantage API URL and [key](https://www.alphavantage.co/support/#api-key). Replace "demo" with your actual API key for production use.
```plaintext
ALPHAVANTAGE_API_URL=https://www.alphavantage.co
ALPHAVANTAGE_API_KEY=demo
```
- Generate the application key and migrate the database.
```bash
php artisan key:generate
php artisan migrate:fresh
```
- Run the application
```bash
php artisan serve
```
- Run Laravel's work scheduler to fetch stocks data.
```bash
php artisan schedule:work
```
- (Optional) Install Postman and import the collection file to test the API endpoints.