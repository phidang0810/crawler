
## Installation
Server Requirements:
- PHP >= 7.1.3
- OpenSSL PHP Extension
- PDO PHP Extension
- Mbstring PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension
- Ctype PHP Extension
- JSON PHP Extension

Before install, make sure you have [Composer](https://getcomposer.org/download/) installed on your machine.

Go to root project directory and run:
```bash
composer install
```
Set access permission and grant the web server write permissions to the storage directories
```bash
chmod -R 775 storage
```
Clear cache on system:
```bash
php artisan cache:clear
```
## Heroku:
 - Install Heroku [CLI](https://devcenter.heroku.com/articles/heroku-cli)
 - Log in using the email address and password you used when creating your Heroku account
 ```bash
 heroku login
 ```
 - Go to your project directory and add a remote to local repository:
 ```bash
 heroku git:remote -a zuri-quoting
 ```
 With *zuri-quoting* is your application name.
 - To push your source code to Heroku run cmd:
 ```bash
 git push heroku master
 ```
 - Finally, please set change APP_ENV to **development** or **production**
 ```bash
 heroku config:set APP_ENV=development
 ```
## Window/Linux Server
Follow **Installation**
## Structure
- Controller/HomeController: get and validate a request from client.
- Basically, we have two services: 
  + **Service/ShippingQuote.php**: receive data from controller and call **Shipment** library to get quote.
  + **Service/QuoteExcel.php**: receive data from controller and call **Laravel-excel** package to export.
  
- Libraries/Shipment:
  + Platform.php is a base class that provided basic method to authenticate, mapping data, send request and return the result.
  + Convey, FC, Manna, Priority are children class, if you want to add new portal, you can add new class extend from Platform and override your method.
**Don't forget add your config into config/crawler.php or anywhere you like**
- For example to use it:
```bash
function getABCquote() {
    $config = config('crawler.convey');     //your config
    $convey = new Convey($config);          //your class
    $convey->mapConfigAndInput($input);     //mapping your config with input data
    
    return $convey->getQuote();
}
```

## How to change account information?
 Open .env file and change variables below:
 ```bash
 CONVEY_USERNAME=your_username_or_email
 CONVEY_PASSWORD=your_password 
  
 MANNA_USERNAME=your_username_or_email
 MANNA_PASSWORD=your_password
  
 FC_USERNAME=your_username_or_email
 FC_PASSWORD=your_password
  
 PRIORITY1_USERNAME=your_username_or_email
 PRIORITY1_PASSWORD=your_password
 
 ```
 On heroku, you can go to your application > setting > Config Variables to manage your account.
 
  Make sure your cache was cleared after changed the config.
  ```bash
  php artisan cache:clear
  ```
##REST API Documentation
http://zuri-quoting.herokuapp.com/api/documentation
## How to test
Go to your domain, fill data and click "Get quote" to get result.
Compare this result that with the that that was returned from FC, Manna, Convey.
