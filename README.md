# Pay2go-eInvoice （智付寶 Pay2go 電子發票）
A library of connecting Pay2go's invoice service.

## Getting Started
此 Package 共實作了四種發票 API 操作，分別為「開立」、「折讓」、「作廢」、「查詢」。

## Install
1. Install from composer:
```
composer require yfancc20/pay2go-einvoice:dev-master
```

2. Add the service provider:
In `config/app.php`,
```
    'providers' => [
        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        ...
        ...
        Yfancc20\Pay2goInvoice\Pay2goInvoiceServiceProvider::class,
```

3. Publish the config file.
In your bash of the workspace:
```
php artisan vendor:publish
```

Or you can just copy it manually.
Switch to your project's folder, then
```
cp vendor/yfancc20/pay2go-einvoice/config/pay2goinv.php config/pay2goinv.php
```
Remember that the filename `pay2goinv.php` shouldn't be changed!

### Usage
Initial the *Class*：
```
use Yfancc20\Pay2goInvoice\Invoice;

...

$invoice = new Invoice();
```

#### 開立發票：
```
$invoice->create($data);
```

#### 折讓發票：
```
$invoice->allow($data);
```

#### 作廢發票：
```
$invoice->void($data);
```

#### 查詢發票：
```
$invoice->search($data);
```

## Authors

* **Yifan Wu** - *Initial work* - [Github](https://github.com/yfancc20)


## Official Reference
[Pay2go E-Invoice API](https://inv.pay2go.com/Invoice_index/download)
