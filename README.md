# Pay2go-eInvoice （智付寶 Pay2go 電子發票）
A library of connecting Pay2go's invoice service.

## Getting Started
此 Package 共實作了四種發票 API 操作，分別為「開立」、「折讓」、「作廢」、「查詢」，並將結果以 Array 傳回。

## Install
1. Install from composer:
```
composer require yfancc20/pay2go-einvoice:dev-master
```

2. Copy the config file:

(a) In `config/app.php`,
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

After adding the ServiceProvider, publish the config file.
```
php artisan vendor:publish
```

(b) Because the proivder is only for publishing the config, you can also copy it manually without adding the provider.
```
cp vendor/yfancc20/pay2go-einvoice/config/pay2goinv.php config/pay2goinv.php
```

*Remember that the filename `pay2goinv.php` should not be changed!*

## Usage
### 引用、初始化類別：
```
use Yfancc20\Pay2goInvoice\Invoice;
```

### 開立發票：
開立發票共有三種模式，分別為 B2C, 捐贈, B2B。
本 Package 使用欄位 *BuyerType* （非智付寶官方提供欄位）做區分，此欄位為必填。

1. B2C (BuyerType = 0)
```
$data = [
    'BuyerType' => '0', // B2C
    'MerchantOrderNo' => <自訂商品編號>,
    'BuyerName' => <買受人姓名>,
    'CarrierType' => <載具類別>, // 0:手機條碼, 1:自然人憑證條碼載具 2:智付寶載具
    'CarrierNum' => <載具編號>,
    'ItemPrice' => <商品單價>,
    'ItemName' => <商品名稱>,
    'ItemUnit' => <商品單位>,
];
```

2. 捐贈 (BuyerType = 1)
```
// 捐贈
$data = [
    'BuyerType' => '1',
    'MerchantOrderNo' => <自訂商品編號>,
    'BuyerName' => <買受人姓名>,
    'LoveCode' => <愛心碼>,
    'ItemPrice' => <商品單價>,
    'ItemName' => <商品名稱>,
    'ItemUnit' => <商品單位>,
];
```

3. B2B（BuyerType = 2）
```
// B2B
$data = [
    'BuyerType' => '2', // B2B
    'MerchantOrderNo' => <自訂商品編號>,
    'BuyerName' => <買受人姓名>,
    'BuyerUBN' => <買受人統一編號>,
    'ItemPrice' => <商品單價>,
    'ItemName' => <商品名稱>,
    'ItemUnit' => <商品單位>,
];
```

呼叫函式
```
$invoice = new Invoice();
$result = $invoice->create(data);
```

*[補充]*
- 除了上述欄位外，可參考官方API提供其他需要的欄位，例如：*BuyerAddress*(買受人地址), *BuyerEmail*(買受人信箱), *Comment*(發票備註)。
- 商品數量預設為 1。
- 商品單位若不設置，預設為「個」。


#### 折讓發票：
```
$data = [
    'InvoiceNo' => <發票號碼>,
    'MerchantOrderNo' => <自訂商品編號>,
    'ItemName' => <商品名稱>,
    'ItemUnit' => <商品單位>,
    'TotalAmt' => <商品總額>,
    'BuyerEmail' => <買受人信箱>, // 可不填，若填寫則智付寶將寄信通知買受人。
];

$invoice = new Invoice();
$result = $invoice->allow($data);
```
*[補充]*
- 商品數量預設為 1。
- 商品單位若不設置，預設為「個」。

#### 作廢發票：
```
$data = [
    'InvoiceNumber' => <發票號碼>,
    'InvalidReason' => <作廢理由>,
];
$invoice = new Invoice();
$result = $invoice->void($data);
```

#### 查詢發票：
查詢發票的回傳結果有兩種顯示方式，一為直接回傳資料，二為以Post Form的方式導向智付寶發票頁面，可在 `config/pay2goinv.php` 中設定 `DisplayFlag` 的值。
```
// 直接回傳資料
// 'DisplayFlag' => '',

// 導向智付寶發票頁面
// 'DisplayFlag' => '1',
```

查詢發票的查詢方式亦有兩種，一為發票金額＆隨機碼，二為自訂商品編號與總金額，可在 `config/pay2goinv.php` 中設定 `SearchType` 的值。
```
// 發票金額&隨機碼
// 'SearchType' => '0'
// 商品編號&總金額
// 'SearchType' => '1'
```

根據上述兩種方式：
```
// When SearchType is 0
```
$data = [
    'InvoiceNumber' => <發票號碼>,
    'RandomNum' => <隨機碼>,
];
```
// When SearchType is 1
$data = [
    'MerchantOrderNo' => <自訂商品編號>,
    'TotalAmt' => <商品總金額>,
];

$invoice = new Invoice();
$result = $invoice->search($data);
```

#### Notes
- 以上的 `$data` 可接受 Object or Array 兩種型態。
- 除了上述欄位外，其他欄位請參考 [智付寶API](https://inv.pay2go.com/Invoice_index/download)，並自行在`$data`中新增參數即可

## Other Usage:
- 當呼叫過四個函式其中一個後，除了原本回傳的結果外，亦可使用 `getPostData()` 取得類別中實際送出的資料。
```
// Example: (This will get nothing.)
$invoice = new Invoice();
$result = $invoice->getPostData();

// Example: (Cont. This will get the original data you posted to Pay2go.)
$invoice = new Invoice();
$invoice->create($data);
$postData = $invoice->getPostData();
```

- 當呼叫過四個函式其中一個後，除了原本回傳的結果外，亦可使用 `getRawResult()` 取得API呼叫結果後的原始資料。
```
// Example: (This will get nothing.)
$invoice = new Invoice();
$result = $invoice->getRawResult();

// Example: (This will get the raw data you got from Pay2go.)
$invoice = new Invoice();
$invoice->create($data);
$rawResult = $invoice->getRawResult();
```

## Authors

* **Yifan Wu** - *Initial work* - [Github](https://github.com/yfancc20)


## Official Reference
[Pay2go E-Invoice API](https://inv.pay2go.com/Invoice_index/download)
