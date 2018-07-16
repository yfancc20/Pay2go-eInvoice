# Pay2go-eInvoice
A serial API's of Pay2go's invoice service.

## Getting Started
此 Package 共實作了四種發票 API 操作，分別為「開立」、「折讓」、「作廢」、「查詢」。

### Usage
```
use Yfancc20\Pay2goInvoice\Invoice;

...

$invoice = new Invoice();
```

```
$invoice->create($data);
```

```
$invoice->allow($data);
```

```
$invoice->void($data);
```

```
$invoice->search($data);
```

## Authors

* **Yifan Wu** - *Initial work* - [Github](https://github.com/yfancc20)


## Official Reference
[Pay2go E-Invoice API](https://inv.pay2go.com/Invoice_index/download)
